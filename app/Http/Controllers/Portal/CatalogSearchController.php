<?php
namespace App\Http\Controllers\Portal;

use App\Http\Controllers\PortalBaseController;
use App\Model\Common;
use App\Model\SiteSetting;
use App\Services\Catalog\CatList\ICatalogService;
use Input;
use Session;

class CatalogSearchController extends PortalBaseController
{

    protected $catSer;

    public function __construct(ICatalogService $catService)
    {
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
        $this->catSer = $catService;
    }

    public function getIndex()
    {
        $this->doTheme();
        $this->getData();
        return;
    }

    public function getData()
    {
        $crumbs = [
            'Home' => '/',
            'Catalog' => 'catalog',
            'Search' => ''
        ];
        $this->layout->breadcrumbs = Common::getEFEBreadCrumbs($crumbs);
        $pageNo = 0;
        $records_per_page = SiteSetting::module('Search', 'results_per_page', 10);
        if ($records_per_page == '') {
            $records_per_page = 10;
        }

        if (Input::get('p_type')) {
            $p_type = [Input::get('p_type')];
        } else {
            $p_type = ['content_feed', 'product', 'course'];
        }

        $search = Input::get('cat_search');
        if ($search == '') {
            $s_data = [];
            $s_count = 0;
            $search_count = 0;
            $type_count = 0;
        } else {
            $s_data = $this->catSer->getSearchedData($search, $p_type, $pageNo, $records_per_page);
            $s_count = $this->catSer->getSearchedCount($search, $p_type);
            
            $p_data = $this->catSer->getPackageSearchedData($search, $pageNo, $records_per_page);
            $p_count = $this->catSer->getPackageSearchedCount($search);

            $search_count = $this->catSer->getSearchedCount($search, ['content_feed', 'product']);
            $type_count = $this->catSer->getSearchedCount($search, ['content_feed']);
        }

        Session::put('s_count', $s_count);
        Session::put('p_count', $p_count);

        Input::flash();
        $this->layout->content = view($this->theme_path . '.search.catalog_search', ['s_data' => $s_data, 'p_data'=> $p_data, 'p_count' => $p_count, 's_count' => $s_count, 'records_per_page' => $records_per_page, 'search_count' => $search_count, 'type_count' => $type_count]);
    }

    private function doTheme()
    {
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->header = view($this->theme_path . '.common.header');
    }

    public function getNextData()
    {
        if (Input::get('cat_search')) {
            $pageNo = 0;
            $records_per_page = SiteSetting::module('Search', 'results_per_page', 10);
            if ($records_per_page == '') {
                $records_per_page = 10;
            }

            if (preg_match('/^[0-9]+$/', Input::get('pageno'))) {
                $pageNo = Input::get('pageno');
            }

            if (Input::get('p_type')) {
                $p_type = [Input::get('p_type')];
            } else {
                $p_type = ['content_feed', 'product', 'course'];
            }

            $search = Input::get('cat_search');

            $s_data = $this->catSer->getSearchedData($search, $p_type, $pageNo, $records_per_page);
            $p_data = $this->catSer->getPackageSearchedData($search, $pageNo, $records_per_page);
            $p_count = Session::get('p_count');
            $s_count = Session::get('s_count');

            if (!empty($s_data) || !empty($p_data)) {
                return response()->json([
                    'status' => true,
                    'data' => view($this->theme_path . '.search.catalogsearch_ajax_load', ['s_data' => $s_data, 'p_data' => $p_data, 'search' => $search])->render(),
                    'count' => count($s_data) + count($p_data),
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'data' => '<div class="col-md-12 center l-gray"><p><strong>' . trans('pagination.no_more_records') . '</strong></p></div>',
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Please enter a Search keyword',
            ]);
        }
    }

    public function getSuggestData()
    {
        $search = Input::get('cat_search');
        $s_data = $this->catSer->getSuggestionData($search);
        $p_data = $this->catSer->getPackageSuggestionData($search);
        $s_data = array_merge($s_data, $p_data);
        if (!empty($s_data)) {
            return response()->json(
                [
                    'status' => true,
                    'data' => $s_data
                ]
            );
        } else {
            return response()->json(
                [
                    'status' => false,
                    'data' => [],
                ]
            );
        }
    }
}
