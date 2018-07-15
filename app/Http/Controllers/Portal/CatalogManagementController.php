<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\PortalBaseController;
use App\Model\Packet;
use App\Model\Program;
use App\Model\SiteSetting;
use App\Model\Banners;
use App\Services\Catalog\AccessControl\IAccessControlService;
use App\Services\Catalog\CatList\ICatalogService;
use App\Services\Catalog\Pricing\IPricingService;
use App\Services\Catalog\Promocode\IPromoCodeService;
use App\Services\Country\ICountryService;
use App\Model\Package\Entity\Package;
use App\Model\User\Entity\UserEnrollment;
use App\Enums\User\UserEntity;
use Auth;
use Common;
use Input;
use Session;

/**
 * Class CatalogManagementController
 * @package App\Http\Controllers\Portal
 */
class CatalogManagementController extends PortalBaseController
{
    /**
     * @var ICatalogService
     */
    protected $catSer;
    /**
     * @var IPricingService
     */
    protected $pricingSer;
    /**
     * @var ICountryService|null
     */
    protected $countryService = null;
    /**
     * @var IPromoCodeService
     */
    protected $promoCodeService;

    /**
     * CatalogManagementController constructor.
     * @param ICatalogService $catService
     * @param IAccessControlService $accessControlService
     * @param IPricingService $priceService
     * @param ICountryService $countryService
     * @param IPromoCodeService $promoService
     */
    public function __construct(
        ICatalogService $catService,
        IAccessControlService $accessControlService,
        IPricingService $priceService,
        ICountryService $countryService,
        IPromoCodeService $promoService
    ) {
    
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout_frontend';
        $this->theme_path = 'portal.theme.' . $this->theme;
        $this->catSer = $catService;
        $this->pricingSer = $priceService;
        $this->accSer = $accessControlService;
        $this->countryService = $countryService;
        $this->promoCodeService = $promoService;
    }

    /**
     *
     */
    public function getIndex()
    {
        $filter_category_slug = null;
        $child_slug = '';
        $category_slug = Input::get('category_name');
        $product_type = Input::get('program_type');
        $search_string = Input::get('search_item');
        $filter_data = $this->catSer->getAllCategoryWithSellable($filter_category_slug);
        if (!$filter_data->isEmpty()) {
            $filter_data->sortByDesc('created_at');
        }
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $crumbs = [
            'Home' => '/',
            'Catalog' => '',
        ];
        $this->layout->breadcrumbs = Common::getEFEBreadCrumbs($crumbs);
        $data_for_parent = $data = $this->catSer->getAllCategoryWithSellable($category_slug, trim($search_string), $product_type);
        if (!$data->isEmpty()) {
            if ($data->count() == 1 && !is_null($data->first()->parents)) {
                $category_details = $this->catSer->getCategoryAndSubCategory($data->first()->parents);
                $data_for_parent = $category_details;
                $parent_slug = $category_details->first()->slug;
                Input::merge(['category_name' => $parent_slug]);
                $child_slug = array_get($data->first(), 'slug', '');
            }
        }
        $banners=Banners::getAllBanners("ACTIVE", "category");
        Session::flash('catalog', false);
        Session::flash('menubar', false);
        
        $this->layout->content = view(
            $this->theme_path . '.catalog.catalog_list',
            [
                'category_list' => $data, /*RHS display data */
                'filter_data' => $filter_data,
                'category_slug' => $category_slug,
                'banners' => $banners,
                'parent_details' => $data_for_parent,  /* LHS Filter data*/
                'child_slug' => $child_slug
            ]
        );
    }

    /**
     * @param $slug
     */
    public function getCourse($slug, $type = null)
    {
        $p_detail = $this->mCourseData($slug, $type);
        $sub_type = 'single';
        $channel_count = 0;
        
        if ($type == 'package') {
            $sub_type = 'collection';
            $channel_count = Package::getchildrencount($slug);
            $inputData = [
                'sellable_id' => $p_detail['basic']['package_id'],
                'sellable_type' => 'package'
            ];
        } else {
            $inputData = [
                'sellable_id' => $p_detail['basic']['program_id'],
                'sellable_type' => $p_detail['basic']['program_type']
            ];
        }

        $p_phy_details = $this->pricingSer->getPricing($inputData);

        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');

        if (isset($type) && ($type == 'package')) {
            $crumbs = [
                'Home' => '/',
                'Catalog' => 'catalog',
                $p_detail['basic']['package_title'] => ''
            ];
        } else {
            $crumbs = [
                'Home' => '/',
                'Catalog' => 'catalog',
                $p_detail['basic']['program_title'] => ''
            ];
        }
        $sort_by = SiteSetting::module('General', 'sort_by');
        
        if (isset($type) && ($type == 'package')) {
            $posts = [];
        } else {
            $posts = Packet::getPacketsUsingSlug($p_detail['basic']['program_slug'], $sort_by);
        }

        $this->layout->breadcrumbs = Common::getEFEBreadCrumbs($crumbs);
        $tabs = array_get($p_detail, 'tabs', '');
        if (!Auth::check() && !empty($tabs)) {
            $modified_tabs = [];
            foreach ($tabs as $tab) {
                $temp = $tab;
                $temp['description'] = $this->catSer->replacementForMedia(
                    array_get($tab, 'description', '')
                );
                $modified_tabs[] = $temp;
            }
            $tabs = $modified_tabs;
        }

        if (isset($type) && ($type == 'package')) {
            $promocode = $this->getPackagePromocode($slug);
        } else {
            $promocode = $this->getPromocode($slug);
        }
        
        $this->layout->content = view(
            $this->theme_path . '.catalog.course',
            ['p_det_basic' => $p_detail['basic'],
                'p_det_tabs' => $tabs,
                'p_det_subscription' => $p_detail['subscription'],
                'p_det_posts_list' => $p_detail['posts_list'],
                'p_det_related_program' => $p_detail['related_program'],
                'p_phy_details' => $p_phy_details,
                'p_cat_desc' => array_get($p_detail, 'cat_details', ''),
                'posts' => $posts,
                'is_buyed' => array_get($p_detail, 'buy_status'),
                'slug' => $slug,
                'type' => $type,
                'program_sub_type' => $sub_type,
                'channel_count' => $channel_count,
                'currency_symbol' => $this->getCurrencySymbol(config('app.site_currency')),
                'promocode_details' => $promocode
            ]
        );
    }

    /**
     * @param $slug
     * @return array
     */
    protected function mCourseData($slug, $type)
    {
        $p_detail = [
            'basic' => null,
            'subscription' => null,
            'posts_list' => null,
            'related_program' => null
        ];
        
        if (isset($type) && ($type == "package")) {
            $s_data = [
                'category_ids',
                'package_cover_media',
                'tabs',
                'package_sellability',
                'package_access'
            ];
            
            $r_data = $this->catSer->getPackage($slug, $s_data);
            
            if (!empty($r_data)) {
                foreach ($r_data as $value) {
                    $p_detail['cat_details'] = '';
                    if (isset($value['category_ids']) && !empty($value['category_ids'])) {
                        $p_cat_id = $value['category_ids'][0];
                        $p_detail['cat_details'] = $this->getCategoryDetails($p_cat_id);
                    }
                    $p_detail['basic'] = $value;
                    if (isset($value['tabs'])) {
                        $p_detail['tabs'] = $value['tabs'];
                    } else {
                        $p_detail['tabs'] = null;
                    }

                    $p_detail['related_program'] = $this->getRelPackage($value);
                    $p_detail['subscription'] = $this->getPackageSubscription($value);
                    $p_detail['posts_list'] = [];
                    $p_detail['buy_status'] = $this->getPackageBuyStatus($value['package_id']);
                }
            }
        } else {
            $s_data = [
                'program_categories',
                'program_cover_media',
                'tabs',
                'program_sellability',
                'program_access',
                'program_sub_type'
            ];
            $r_data = $this->catSer->getCourse($slug, $s_data);
            if (!empty($r_data)) {
                foreach ($r_data as $value) {
                    $p_detail['cat_details'] = '';
                    if (isset($value['program_categories'][0])) {
                        $p_cat_id = $value['program_categories'][0];
                        $p_detail['cat_details'] = $this->getCategoryDetails($p_cat_id);
                    }
                    $p_detail['basic'] = $value;
                    if (isset($value['tabs'])) {
                        $p_detail['tabs'] = $value['tabs'];
                    } else {
                        $p_detail['tabs'] = null;
                    }
                    $p_detail['related_program'] = $this->getRelProgram($value);
                    $p_detail['subscription'] = $this->getSubscription($value);
                    $p_detail['posts_list'] = $this->getPostPreview($value);
                    $p_detail['buy_status'] = $this->getBuyStatus($value['program_id'], $value['program_type']);
                }
            }
        }
        return $p_detail;
    }

    /**
     * @param $p_id
     * @return null|string
     */
    private function getBuyStatus($p_id, $p_type)
    {
        if (Auth::check()) {
            $u_id = Auth::user()->uid;
            $data = [];
            if (isset($p_type) && $p_type == "content_feed") {
                $data = UserEnrollment::where('user_id', $u_id)
                            ->where('entity_type', UserEntity::PROGRAM)
                            ->where('entity_id', $p_id)
                            ->active()
                            ->first();
            } elseif (isset($p_type) && $p_type == "course") {
                $course = Program::getCourseBatchList($p_id);
                $course_ids = array_column($course, 'program_id');
                $data = UserEnrollment::where('user_id', $u_id)
                            ->where('entity_type', UserEntity::BATCH)
                            ->whereIn('entity_id', $course_ids)
                            ->active()
                            ->get()->toArray();
                $data = array_column($data, 'entity_id');
            }
            if (!empty($data)) {
                return $data;
            }
            return 'disable';
        } else {
            return null;
        }
    }

    /**
     * @param $p_id
     * @return null|string
     */
    private function getPackageBuyStatus($p_id)
    {
        if (Auth::check()) {
            $u_id = Auth::user()->uid;

            $data = UserEnrollment::filter(
                [
                    "user_id" => $u_id,
                    "entity_type" => UserEntity::PACKAGE,
                    "entity_id" => $p_id
                ]
            )->active()->first();

            if (!empty($data)) {
                return $data;
            }
            return 'disable';
        } else {
            return null;
        }
    }

    /**
     * @param $value
     * @return array|null
     */
    private function getRelProgram($value)
    {
        if (isset($value['program_categories'])
            && !empty($value['program_categories'])
        ) {
            $data = $this->catSer->getRelatedCourse($value['program_categories']);
            $tempArray = [];
            if (!empty($data)) {
                $i = 0;
                foreach ($data as $key => $eachValue) {
                    if ($value['program_slug'] != $eachValue['program_slug']) {
                        $tempArray[$i] = $eachValue;
                        $i++;
                    }
                }
            }
            return $tempArray;
        } else {
            return null;
        }
    }

    /**
     * @param $value
     * @return array|null
     */
    private function getRelPackage($value)
    {
        if (isset($value['category_ids'])
            && !empty($value['category_ids'])
        ) {
            $data = $this->catSer->getRelatedPackage($value['category_ids']);
            $tempArray = [];
            if (!empty($data)) {
                $i = 0;
                foreach ($data as $key => $eachValue) {
                    $tempArray[$i] = $eachValue;
                    $i++;
                }
            }
            return $tempArray;
        } else {
            return null;
        }
    }

    /**
     * @param $value
     * @return null
     */
    private function getSubscription($value)
    {
        if (!empty(array_get($value, 'program_id')) && !empty(array_get($value, 'program_type'))) {
            $sub_data = $this->pricingSer->getPricing([
                'sellable_id' => $value['program_id'],
                'sellable_type' => $value['program_type']
            ]);
            if (!empty($sub_data)) {
                if (isset($sub_data['subscription']) && !empty($sub_data['subscription'])) {
                    return $sub_data['subscription'];
                }
                if (isset($sub_data['vertical']) && !empty($sub_data['vertical'])) {
                    return $sub_data['vertical'];
                }
            }
        } else {
            return null;
        }
    }

    /**
     * @param $value
     * @return null
     */
    private function getPackageSubscription($value)
    {
        if (!empty(array_get($value, 'package_id'))) {
            $sub_data = $this->pricingSer->getPricing(
                [
                    'sellable_id' => $value['package_id'],
                    'sellable_type' => 'package'
                ]
            );
            if (!empty($sub_data)) {
                if (isset($sub_data['subscription']) && !empty($sub_data['subscription'])) {
                    return $sub_data['subscription'];
                }
                if (isset($sub_data['vertical']) && !empty($sub_data['vertical'])) {
                    return $sub_data['vertical'];
                }
            }
        } else {
            return null;
        }
    }

    /**
     * @param $value
     * @return mixed|null
     */
    private function getPostPreview($value)
    {
        $post_data = $this->catSer->getPostList($value['program_slug']);
        if (!empty($post_data)) {
            return $post_data;
        } else {
            return null;
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    private function getCategoryDetails($id)
    {
        return $this->catSer->getCategoryDetails($id);
    }

    /**
     * @param $currency
     * @return string
     */
    protected function getCurrencySymbol($currency)
    {
        $data = $this->countryService->countryByCurrencyName($currency, ['name', 'currency_symbol']);
        if (!$data->isEmpty()) {
            foreach ($data->toArray() as $key => $value) {
                return $value['currency_symbol'];
            }
        } else {
            return '&#x20B9;';
        }
    }

    /**
     * @param $p_slug
     * @return mixed
     */
    public function getPromocode($p_slug)
    {
        return $this->promoCodeService->applicablePromoCodeList($p_slug);
    }

    /**
     * @param $p_slug
     * @return mixed
     */
    public function getPackagePromocode($p_slug)
    {
        return $this->promoCodeService->applicablePackagePromoCodeList($p_slug);
    }
}
