<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminBaseController;
use App\Model\Common;
use App\Model\Program;
use App\Model\PromoCode;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\ECommerce\ECommercePermission;
use App\Services\Package\IPackageService;
use App\Model\Package\Entity\Package;
use Auth;
use Input;
use Request;
use Session;
use Timezone;
use URL;
use Validator;

class PromoCodeController extends AdminBaseController
{

    protected $layout = 'admin.theme.layout.master_layout';
    protected $userID = 0;
    protected $userGroups = [];
    protected $isAdmin = false;

     /**
     * @var IPackageService
     */
    private $packageService;

    public function __construct(
        Request $request,
        IPackageService $packageService
    ) {
        parent::__construct();
        $input = $request::input();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request::merge($input);

        $this->theme_path = 'admin.theme';
        $this->packageService = $packageService;
    }

    public function getIndex()
    {
        if (!has_admin_permission(ModuleEnum::E_COMMERCE, ECommercePermission::LIST_PROMO_CODE)) {
            return parent::getAdminError();
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/promocode.manage_promocode') => 'promocode',
            trans('admin/promocode.list_promocode') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/promocode.list_promocode');
        $this->layout->pageicon = 'fa fa-code-fork';
        $this->layout->pagedescription = trans('admin/promocode.list_of_promocodes');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'order')
            ->with('submenu', 'promocode');
        $this->layout->footer = view('admin.theme.common.footer');

        if (!is_null(Input::get('filter'))) {
            $filter = Input::get('filter');
        } else {
            $filter = 'ALL';
        }
        Session::put('promocode_filter', $filter);

        $promocodes = PromoCode::getPromoCodes($filter);
        $this->layout->content = view('admin.theme.promocode.listpromocodes')
            ->with('promocodes', $promocodes);
    }

    public function getAddPromocode()
    {
        if (!has_admin_permission(ModuleEnum::E_COMMERCE, ECommercePermission::ADD_PROMO_CODE)) {
            return parent::getAdminError();
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/promocode.manage_promocode') => 'promocode',
            trans('admin/promocode.add_promocode') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/promocode.add_promocode');
        $this->layout->pageicon = 'fa fa-code-fork';
        $this->layout->pagedescription = trans('admin/promocode.add_promocode');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'order')
            ->with('submenu', 'promocode');
        $this->layout->footer = view('admin.theme.common.footer');
        $promocode = PromoCode::generatePromocode();
        $this->layout->content = view('admin.theme.promocode.addpromocode')->with('promocode', $promocode);
    }

    public function postAddPromocode(Request $request)
    {
        if (!has_admin_permission(ModuleEnum::E_COMMERCE, ECommercePermission::ADD_PROMO_CODE)) {
            return parent::getAdminError();
        }

        Request::flash();
        Validator::extend('percentage', function ($attribute, $value, $parameters) {
            $discount_type = Input::get('discount_type');
            if ($discount_type == 'percentage') {
                if (($value >= 0) && ($value <= 100)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        });

        //Date Validation

        Validator::extend('start_date_less_than_end_date', function ($attribute, $value, $parameters) {
            return (strtotime(Request::get('start_date')) < strtotime(Request::get('end_date'))) ? true : false;
        });

        //minimum order Validation

        Validator::extend('unit_minimum_order', function ($attribute, $value, $parameters) {
            if (Request::get('discount_type') == "unit") {
                return ((int)(Request::get('discount_value')) <= (int)(Request::get('minimum_order_amount'))) ? true : false;
            }
            return true;
        });

        $messages = [
            'percentage' => trans('admin/ecommerce.percentage_error'),
            'promocode.regex' => trans('admin/ecommerce.promocode_format_error'),
            'start_date_less_than_end_date' => trans('admin/ecommerce.start_date_less_than_end_date'),
            'unit_minimum_order' => trans('admin/promocode.min_order_discount')
        ];
        $rules = [
            'promocode' => 'required_if:promotype,manual|min:3|max:10|unique:promocodes|Regex:/^([0-9A-Z])+$/',
            'max_redeem_count' => 'Required|Regex:/^([0-9])+$/',
            'discount_value' => 'Required|integer|min:1|percentage|Regex:/^([0-9])+$/',
            'maximum_discount_amount' => 'integer|min:1',
            'minimum_order_amount' => 'integer|min:1|unit_minimum_order',
            'end_date' => 'required|start_date_less_than_end_date'

        ];

        $validation = Validator::make(Request::all(), $rules, $messages);

        if ($validation->fails()) {
            return redirect('cp/promocode/add-promocode')->withInput()->withErrors($validation);
        } elseif ($validation->passes()) {
            $promocode_id = PromoCode::insertPromocode(Request::all());

            Request::flush();
            return redirect('cp/promocode')
                ->with('success', trans('admin/ecommerce.add_promocode_success'));
        }
    }

    public function getGeneratePromocode()
    {
        $promocode = PromoCode::generatePromocode();
        return response()->json([
            'promocode' => $promocode
        ]);
    }

    public function getDeletePromocode($id)
    {
        if (!has_admin_permission(ModuleEnum::E_COMMERCE, ECommercePermission::DELETE_PROMO_CODE)) {
            return parent::getAdminError();
        }

        $redeemed_count = PromoCode::where('id', '=', $id)->value('redeemed_count');
        if ($redeemed_count > 0) {
            return redirect('cp/promocode')
                ->with('error', trans('admin/ecommerce.promocode_error'));
        } else {
            $res = PromoCode::deletePromoCode($id);

            if ($res) {
                return redirect('cp/promocode')
                    ->with('success', trans('admin/ecommerce.delete_success'));
            } else {
                return redirect('cp/promocode')
                    ->with('error', trans('admin/ecommerce.promocode_error'));
            }
        }
    }

    public function getEditPromocode($id)
    {
        if (!has_admin_permission(ModuleEnum::E_COMMERCE, ECommercePermission::EDIT_PROMO_CODE)) {
            return parent::getAdminError();
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/promocode.manage_promocode') => 'promocode',
            trans('admin/promocode.edit_promocode') => '',
        ];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/promocode.edit_promocode');
        $this->layout->pageicon = 'fa fa-code-fork';
        $this->layout->pagedescription = trans('admin/promocode.edit_promocode');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'order')
            ->with('submenu', 'promocode');
        $promocode = PromoCode::getPromoCodeUsingId($id);
        $promocode = $promocode[0];
        $autocode = PromoCode::generatePromocode();
        $this->layout->footer = view('admin.theme.common.footer');
        $this->layout->content = view('admin.theme.promocode.editpromocode')->with('promocode', $promocode)->with('autocode', $autocode);
    }

    public function postEditPromocode($id)
    {
        if (!has_admin_permission(ModuleEnum::E_COMMERCE, ECommercePermission::EDIT_PROMO_CODE)) {
            return parent::getAdminError();
        }

        Request::flash();
        /*Validator::extend('percentage', function ($attribute, $value, $parameters) {
            $discount_type = Input::get('discount_type');
            if ($discount_type == 'percentage') {
                if (($value >= 0) && ($value <= 100)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        });

        Validator::extend('unique_promocodes', function ($attribute, $value, $parameters) {
            $parameters = array_map('intval', $parameters);
            $promocodes = PromoCode::whereNotIn('id', $parameters)->lists('promocode')->all();
            if (in_array($value, $promocodes)) {
                return false;
            } else {
                return true;
            }
        });*/

        //Date Validation

        Validator::extend('start_date_less_than_end_date', function ($attribute, $value, $parameters) {
            return (strtotime(Request::get('start_date')) < strtotime(Request::get('end_date'))) ? true : false;
        });

        //minimum order Validation

        Validator::extend('unit_minimum_order', function ($attribute, $value, $parameters) {
            if (Request::get('discount_type') == "unit") {
                return ((int)(Request::get('discount_value')) <= (int)(Request::get('minimum_order_amount'))) ? true : false;
            }
            return true;
        });

        Validator::extend('redeemed_count', function ($attribute, $value, $parameters) {
            $redeemed_count = Request::get('redeemed_count');
            $max_redeem_count = Request::get('max_redeem_count');
            if (($max_redeem_count < $redeemed_count) && ($max_redeem_count != 0)) {
                return false;
            } else {
                return true;
            }
        });

        $messages = [
            /*'percentage' => trans('admin/ecommerce.percentage_error'),
            'promocode.regex' => trans('admin/ecommerce.promocode_format_error'),
            'unique_promocodes' => trans('admin/ecommerce.unique_promocode_error'),*/
            'redeemed_count' => trans('admin/ecommerce.redeemed_count'),
            'start_date_less_than_end_date' => trans('admin/ecommerce.start_date_less_than_end_date'),
            'unit_minimum_order' => trans('admin/promocode.min_order_discount')
        ];
        $rules = [
            //'promocode' => 'required_if:promotype,manual|min:3|max:10|Regex:/^([0-9A-Z])+$/|unique_promocodes:'.$id,
            'max_redeem_count' => 'Required|redeemed_count|Regex:/^([0-9])+$/',
            'maximum_discount_amount' => 'integer|min:1',
            'minimum_order_amount' => 'integer|min:1|unit_minimum_order',
            'end_date' => 'required|start_date_less_than_end_date'
            //'discount_value' => 'Required|percentage|Regex:/^([0-9])/'
        ];

        $validation = Validator::make(Request::all(), $rules, $messages);

        if ($validation->fails()) {
            return redirect('cp/promocode/edit-promocode/' . $id)->withInput()->withErrors($validation);
        } elseif ($validation->passes()) {
            PromoCode::updatePromocode($id, Request::all());
            Request::flush();
            return redirect('cp/promocode')
                ->with('success', trans('admin/ecommerce.edit_promocode_success'));
        }
    }

    public function getExportPromocode()
    {
        if (!has_admin_permission(ModuleEnum::E_COMMERCE, ECommercePermission::EXPORT_PROMO_CODE)) {
            return parent::getAdminError();
        }

        $promocode_status = Session::get('promocode_status');
        $program_type = Session::get('program_type');

        $promocodes = PromoCode::getAvialablePromocode($promocode_status, $program_type);

        return view('admin.theme.promocode.export_promocode')->with('promocodes', $promocodes);
    }

    /**
     * [getListFeeds - list feeds promocode]
     * @method getListFeeds
     * @return [type]       [description]
     */
    public function getListFeeds()
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            'Manage ' . trans('admin/program.programs') => 'contentfeedmanagement',
            'List ' . trans('admin/program.programs') => '',
        ];
        $viewmode = Input::get('view', 'desktop');
        $relfilter = Input::get('relfilter', 'nonassigned');
        $from = Input::get('from', 'none');
        $relid = Input::get('relid', 'none');
        $subtype = Input::get('subtype', 'all');
        $filters = Input::get('filters', 'all');
        $program_type = Input::get('program_type', 'all');

        $field = Input::get('field', 'relations.active_user_feed_rel');
        if ($viewmode == 'iframe') {
            $this->layout->breadcrumbs = '';
            $this->layout->pagetitle = '';
            $this->layout->pageicon = '';
            $this->layout->pagedescription = '';
            $this->layout->header = '';
            $this->layout->sidebar = '';
            $this->layout->content = view('admin.theme.promocode.__list')
                ->with('relfilter', $relfilter)
                ->with('from', $from)
                ->with('subtype', $subtype)
                ->with('field', $field)
                ->with('filters', $filters)
                ->with('program_type', $program_type)
                ->with('relid', $relid);
            $this->layout->footer = '';
        } else {
            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = 'Manage ' . trans('admin/program.programs');
            $this->layout->pageicon = 'fa fa-rss';
            $this->layout->pagedescription = 'Manage ' . trans('admin/program.programs');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'contentfeeds')
                ->with('submenu', 'listcontentfeeds');
            $this->layout->content = view('admin.theme.programs.listcontentfeed');
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }

    /**
     * [getFeedListAjax list feeds]
     * @method getFeedListAjax
     * @return [type]          [description]
     * @author Rudragoud Patil
     */
    public function getFeedListAjax()
    {
        $relfilter = Input::get('relfilter', 'assigned');
        $promocode_id = Input::get('relid');
        $program_type = Input::get('program_type', 'content_feed');
        $viewmode = Input::get('view', 'desktop');
        $search = Input::get('search');

        $searchKey = isset($search['value']) ? $search['value'] : '';
        $start = (preg_match('/^[0-9]+$/', Input::get('start'))) ? Input::get('start') : 0;
        $limit = (preg_match('/^[0-9]+$/', Input::get('length'))) ? Input::get('length') : 10;

        if ($program_type == "package") {
            $filter_params = [];
            $list = PromoCode::where('id', '=', (int)$promocode_id)->first();
            $promocode_package_rel = isset($list->package_rel) ? $list->package_rel : [];
            
            if ($relfilter == "nonassigned") {
                $filter_params["not_in_ids"] = $promocode_package_rel;
            } else {
                $filter_params["in_ids"] = $promocode_package_rel;
            }

            $filter_params["package_sellability"] = "yes";
            $filter_params["search_key"] = $this->request->input("search.value", null);
            $order_by_columns = [
                null,
                "package_title",
                "package_shortname",
                "package_startdate",
                "package_enddate",
                "status"
            ];

            $order_by_column_index = $this->request->input("order.0.column", 1);
            $filter_params["order_by"] = array_get($order_by_columns, $order_by_column_index, "package_startdate");
            $filter_params["order_by_dir"] = $this->request->get("order.0.dir", "desc");

            $total_count = $this->packageService->getPackagesCount();
            $filtered_count = $this->packageService->getPackagesCount($filter_params);

            $filter_params["start"] = $this->request->input("start", 0);
            $filter_params["limit"] = $this->request->input("length", 10);
            $package_data = $this->packageService->getPackages(
                $filter_params,
                [
                    "package_id", "package_title", "package_shortname", "package_startdate", "package_enddate", "status"
                ]
            )->toArray();

            return response()->json(
                [
                    'recordsTotal' => $total_count,
                    'recordsFiltered' => $filtered_count,
                    'data' => $this->preparePackageDataTable($package_data)
                ]
            );

        } else {

            $orderByArray = ['program_startdate' => 'desc'];

            if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
                if ($order_by[0]['column'] == '1') {
                    $orderByArray = ['program_title' => $order_by[0]['dir']];
                }

                if ($order_by[0]['column'] == '2') {
                    $orderByArray = ['program_startdate' => $order_by[0]['dir']];
                }

                if ($order_by[0]['column'] == '3') {
                    $orderByArray = ['program_enddate' => $order_by[0]['dir']];
                }

                if ($order_by[0]['column'] == '4' || $order_by[0]['column'] == '8') {
                    $orderByArray = ['status' => $order_by[0]['dir']];
                }
            }


            if ($viewmode == 'iframe' && in_array($relfilter, ['assigned', 'nonassigned'])) {
                $totalRecords = Program::getFeedListForPromocodeCount('all', $promocode_id, $program_type, $searchKey, $orderByArray);

                $filteredRecords = Program::getFeedListForPromocodeCount($relfilter, $promocode_id, $program_type, $searchKey, $orderByArray);

                $filtereddata = Program::getFeedListForPromocode($start, $limit, $relfilter, $promocode_id, $program_type, $searchKey, $orderByArray);
            }

            $finaldata = [
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $this->prepareDataTable($filtereddata),
            ];

        }
        return response()->json($finaldata);
    }

    /**
     * [prepareDataTable - return data to datatable format]
     * @method prepareDataTable
     * @param  [type]           $filtereddata [list data from db]
     * @return [type]                         [formated for datatable]
     */
    private function prepareDataTable($filtereddata)
    {
        $dataArr = [];
        if (!empty($filtereddata) && is_array($filtereddata)) {
            foreach ($filtereddata as $key => $value) {
                $program_shortname = (isset($value['program_shortname']) && !empty($value['program_shortname'])) ? $value['program_shortname'] : 'NA';

                $dataArr[] = [
                    '<input type="checkbox" value="' . $value['program_id'] . '">',
                    $value['program_title'],
                    $program_shortname,
                    Timezone::convertFromUTC('@' . $value['program_startdate'], Auth::user()->timezone, config('app.date_format')),
                    Timezone::convertFromUTC('@' . $value['program_enddate'], Auth::user()->timezone, config('app.date_format')),
                    ucfirst(strtolower($value['status'])),
                ];
            }
            return $dataArr;
        }

        return $dataArr;
    }

    /**
     * [preparePackageDataTable - return data to datatable format]
     * @method preparePackageDataTable
     * @param  [type]           $filtereddata [list data from db]
     * @return [type]                         [formated for datatable]
     */
    private function preparePackageDataTable($filtereddata)
    {
        $dataArr = [];
        if (!empty($filtereddata) && is_array($filtereddata)) {
            foreach ($filtereddata as $key => $value) {
                $program_shortname = (isset($value['package_shortname']) && !empty($value['package_shortname'])) ? $value['package_shortname'] : 'NA';

                $dataArr[] = [
                    '<input type="checkbox" value="' . $value['package_id'] . '">',
                    $value['package_title'],
                    $program_shortname,
                    Timezone::convertFromUTC('@' . $value['package_startdate'], Auth::user()->timezone, config('app.date_format')),
                    Timezone::convertFromUTC('@' . $value['package_enddate'], Auth::user()->timezone, config('app.date_format')),
                    ucfirst(strtolower($value['status'])),
                ];
            }
            return $dataArr;
        }

        return $dataArr;
    }


    /**
     * [postAssignPromocode assiagn ffeds to promocode]
     * @method postAssignPromocode
     * @param  [type]              $promocode_id [description]
     * @return [type]                            [description]
     */
    public function postAssignPromocode($promocode_id)
    {
        $program_type = Request::get('program_type');
        $program_id_list = $this->doFeedRel(Request::get('ids'));

        if ($program_type == "package") {
            
            $existing_id_list = PromoCode::where('id', '=', (int)$promocode_id)->first()->package_rel;

            if (empty($program_id_list) && is_array($program_id_list) && $existing_id_list != null) {
                PromoCode::where('id', '=', (int)$promocode_id)->update(['package_rel' => []]);

                foreach ($existing_id_list as $key => $val) {
                    Package::where('package_id', '=', (int)$val)->pull('promocode', $promocode_id);
                }
            } else {
                $program_id_list_new = (is_null($existing_id_list)) ? $program_id_list : array_diff($existing_id_list, $program_id_list);

                $program_id_list_update = (is_array($program_id_list) && !empty($program_id_list)) ? $program_id_list : $program_id_list_new;
                PromoCode::where('id', '=', (int)$promocode_id)->update(['package_rel' => $program_id_list_update]);

                if (!empty($program_id_list_update) && is_array($program_id_list_update)) {
                    foreach ($program_id_list_update as $key => $val) {
                        if (is_array($program_id_list) && !empty($program_id_list)) {
                            Package::where('package_id', '=', (int)$val)->push('promocode', $promocode_id);
                        } else {
                            Package::where('package_id', '=', (int)$val)->pull('promocode', $promocode_id);
                        }
                    }
                }
            }
        } else {
            $existing_id_list = PromoCode::where('id', '=', (int)$promocode_id)->first()->feed_rel;

            if (empty($program_id_list) && is_array($program_id_list) && $existing_id_list != null) {
                PromoCode::where('id', '=', (int)$promocode_id)->update(['feed_rel' => []]);

                foreach ($existing_id_list as $key => $val) {
                    Program::where('program_id', '=', (int)$val)->pull('promocode', $promocode_id);
                }
            } else {
                $program_id_list_new = (is_null($existing_id_list)) ? $program_id_list : array_diff($existing_id_list, $program_id_list);

                $program_id_list_update = (is_array($program_id_list) && !empty($program_id_list)) ? $program_id_list : $program_id_list_new;
                PromoCode::where('id', '=', (int)$promocode_id)->update(['feed_rel' => $program_id_list_update]);

                if (!empty($program_id_list_update) && is_array($program_id_list_update)) {
                    foreach ($program_id_list_update as $key => $val) {
                        if (is_array($program_id_list) && !empty($program_id_list)) {
                            Program::where('program_id', '=', (int)$val)->push('promocode', $promocode_id);
                        } else {
                            Program::where('program_id', '=', (int)$val)->pull('promocode', $promocode_id);
                        }
                    }
                }
            }
        }
        $message = trans('admin/program.' . $program_type) . trans('admin/program.assigned_success');

        return response()->json(['flag' => 'success', 'message' => $message]);
    }

    /**
     * [doFeedRel - make string to int list]
     * @method doFeedRel
     * @param  [type]    $str_ids [description]
     * @return [type]             [description]
     */
    private function doFeedRel($str_ids)
    {
        if (!empty($str_ids)) {
            $int_val = function ($val) {
                return (int)$val;
            };
            return array_map($int_val, explode(',', $str_ids));
        }

        return [];
    }

    /**
     * [getViewPromocode - return promocode details]
     * @method getViewPromocode
     * @param  [type]           $promocode_id [description]
     * @return [type]                         [description]
     * @author Rudragoud Patil
     */
    public function getViewPromocode($promocode_id)
    {
        if (!empty($promocode_id)) {
            $return_promocode = PromoCode::where('id', '=', (int)$promocode_id)->first();
            return view('admin.theme.promocode.__view', ['promocode_detail' => $return_promocode]);
        } else {
            return "<div class='alert-warning text-center md-margin'> Sorry we are not able to find promocode.</div>";
        }
    }

    /**
     * [getPromocodeListAjax promocode list]
     * @method getPromocodeListAjax
     * @return [type]               [description]
     * @author Rudragoud Patil
     */
    public function getPromocodeListAjax()
    {
        $relfilter = Input::get('filter', 'all');
        $promocode_id = Input::get('relid');
        $program_type = Input::get('filters', 'content_feeds');
        $viewmode = Input::get('view', 'desktop');
        $search = Input::get('search');

        Session::put('promocode_status', $relfilter);
        Session::put('program_type', $program_type);

        $searchKey = isset($search['value']) ? $search['value'] : '';
        $start = (preg_match('/^[0-9]+$/', Input::get('start'))) ? Input::get('start') : 0;
        $limit = (preg_match('/^[0-9]+$/', Input::get('length'))) ? Input::get('length') : 10;
        $order_by = Input::get('order');
        $orderByArray = ['promocode' => 'asc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            switch ($order_by[0]['column']) {
                case '1':
                    $orderByArray = ['promocode' => $order_by[0]['dir']];
                    break;
                case '2':
                    $orderByArray = ['start_date' => $order_by[0]['dir']];
                    break;
                case '3':
                    $orderByArray = ['end_date' => $order_by[0]['dir']];
                    break;
                case '4':
                    $orderByArray = ['max_redeem_count' => $order_by[0]['dir']];
                    break;
                case '5':
                    $orderByArray = ['redeemed_count' => $order_by[0]['dir']];
                    break;
                default:
                    # code...
                    break;
            }
        }


        $totalRecords = PromoCode::getPromocodeCount('all', $promocode_id, $program_type, $searchKey, $orderByArray);

        $filteredRecords = PromoCode::getPromocodeCount($relfilter, $promocode_id, $program_type, $searchKey, $orderByArray);

        $filtereddata = PromoCode::getPromocode($start, $limit, $relfilter, $promocode_id, $program_type, $searchKey, $orderByArray);


        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $this->preparePromocodeDataTable($filtereddata),
        ];
        return response()->json($finaldata);
    }

    /**
     * [preparePromocodeDataTable data for datatable.]
     * @method preparePromocodeDataTable
     * @param  [type]                    $filtereddata [description]
     * @return [type]                                  [description]
     * @author Rudragoud Patil
     */
    public function preparePromocodeDataTable($filtereddata)
    {

        $add = has_admin_permission(ModuleEnum::E_COMMERCE, ECommercePermission::ADD_PROMO_CODE);
        $edit = has_admin_permission(ModuleEnum::E_COMMERCE, ECommercePermission::EDIT_PROMO_CODE);
        $delete = has_admin_permission(ModuleEnum::E_COMMERCE, ECommercePermission::DELETE_PROMO_CODE);
        $export = has_admin_permission(ModuleEnum::E_COMMERCE, ECommercePermission::EXPORT_PROMO_CODE);

        $dataArr = [];

        $class_product_type = function ($product_type = 'all') {
            $product_type_class = [
                'success' => 'content_feed',
                'warning' => 'product',
                'info' => 'course',
                'important' => 'package',
                'noclass' => 'all'
            ];
            return array_search($product_type, $product_type_class);
        };

        if (!empty($filtereddata) && is_array($filtereddata)) {
            foreach ($filtereddata as $key => $value) {
                $program_type = (isset($value['program_type'])) ? $value['program_type'] : 'all';
                
                if ($program_type == "package") {
                    $feed_rel = isset($value['package_rel']) ? $value['package_rel'] : '';
                    $feed_count = isset($value['package_rel']) ? count($value['package_rel']) : 0;
                } else {
                    $feed_rel = isset($value['feed_rel']) ? $value['feed_rel'] : '';
                    $feed_count = isset($value['feed_rel']) ? count($value['feed_rel']) : 0;
                }


                $view_url = URL::to('cp/promocode/view-promocode/' . $value['id']);
                $edit_url = URL::to('cp/promocode/edit-promocode/' . $value['id']);
                $delete_url = URL::to('cp/promocode/delete-promocode/' . $value['id']);

                $view = "<a href=" . $view_url . " class='btn btn-circle show-tooltip viewfeed' title='".trans('admin/promocode.view_promocode_details')."'><i class='fa fa-eye'></i></a>";
                if ($edit || $delete) {
                    if ($edit) {
                        $edit = "<a class='btn btn-circle show-tooltip' title='".trans('admin/promocode.edit_promocode')."' href=" . $edit_url . "><i class='fa fa-edit'></i></a>";
                    } else {
                        $edit = '';
                    }
                    if ($delete) {
                        if ($value['redeemed_count'] == 0) {
                            $delete = "<a class='btn btn-circle show-tooltip deletepromocode' title='".trans('admin/promocode.delete_promocode')."' href=" . $delete_url . "><i class='fa fa-trash-o'></i></a>";
                        } else {
                            $delete = "<a class='btn btn-circle show-tooltip' title='".trans('admin/promocode.cant_delete')."' ><i class='fa fa-trash-o'></i></a>";
                        }
                    } else {
                        $delete = "";
                    }
                }

                $url = URL::to("cp/promocode/list-feeds?filter=ACTIVE&amp;view=iframe&amp;subtype=single&amp;from=user&amp;relid=" . $value['id'] . "&program_type=" . $program_type);
                if ($program_type != 'all') {
                    $link = "<a class='show-tooltip userrel badge badge-grey badge-" . $class_product_type($program_type) . "' href=" . $url . " data-key=" . $value['id'] . " data-info='$program_type' data-text=' Apply Promo Code <b>(" . $value['promocode'] . ")</b> to' data-json=" . json_encode($feed_rel) . ">" . $feed_count . "</a>";
                } else {
                    $link = "<a class='show-tooltip badge badge-grey badge-{{$class_product_type($program_type)}}'>A</a>";
                }

                $dataArr[] = [
                    '<input type="checkbox" value="' . $value['id'] . '">',
                    $value['promocode'],
                    Timezone::convertFromUTC('@' . $value['start_date'], Auth::user()->timezone, config('app.date_format')),
                    Timezone::convertFromUTC('@' . $value['end_date'], Auth::user()->timezone, config('app.date_format')),
                    $value['max_redeem_count'],
                    $value['redeemed_count'],
                    $link,
                    ucfirst(strtolower($value['status'])),
                    $view . $edit . $delete
                ];
            }
            return $dataArr;
        }

        return $dataArr;
    }

    public function getMigratePromoCode()
    {
        $data = PromoCode::get();
        $i = 0;
        foreach ($data as $key => $value) {
            PromoCode::where('id', '=', (int)$value->id)->update(
                ['end_date' => (((int)$value->end_date + 24 * 60 * 60) - 1)]
            );

            if (!isset($value->program_type)) {
                $i++;
                PromoCode::where('id', '=', (int)$value->id)->update(['program_type' => 'all', 'feed_rel' => []]);
            } elseif (isset($value->program_type) && $value->program_type === 'all') {
                $i++;
                PromoCode::where('id', '=', (int)$value->id)->update(['program_type' => 'all', 'feed_rel' => []]);
            }
        }

        echo $i . " Promocode migrated successfully.";
        exit(0);
    }
}
