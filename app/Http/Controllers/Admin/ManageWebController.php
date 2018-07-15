<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminBaseController;
use App\Model\Common;
use App\Model\Faq;
use App\Model\ManageWebModel;
use App\Model\NewsLetter;
use App\Model\StaticPage;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\ManageSite\ManageSitePermission;
use Auth;
use Input;
use Timezone;
use URL;
use Validator;

class ManageWebController extends AdminBaseController
{
    protected $layout = 'admin.theme.layout.master_layout';

    //start Faq Operation
    public function __construct()
    {
        $this->theme_path = 'admin.theme';
    }

    public function getIndex()
    {
        $list_faq = has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::LIST_FAQ);
        if ($list_faq == false) {
            return parent::getAdminError($this->theme_path);
        }
        $faq = Faq::getActiveFaq();
        if (!is_null(Input::get('filter'))) {
            $filter = Input::get('filter');
        } else {
            $filter = 'ACTIVE';
        }
        $start_serv = 0;
        $length_page_serv = 10;

        if (!is_null(Input::get('start_serv')) && !is_null(Input::get('length_page_serv'))) {
            $start_serv = (int)Input::get('start_serv');
            $length_page_serv = (int)Input::get('length_page_serv');
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/manageweb.manage_sites') => 'manageweb',
            trans('admin/manageweb.faq') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pageicon = 'fa fa-wrench';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'web')
            ->with('submenu', 'faq');
        $this->layout->pagetitle = trans('admin/manageweb.manage_site');
        $this->layout->pagedescription = trans('admin/manageweb.list_of_faq');

        $this->layout->content = view('admin.theme.manageweb.faq')
            ->with('filter', $filter)
            ->with('start_serv', $start_serv)
            ->with('length_page_serv', $length_page_serv)
            ->with('faqs', $faq);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getFaqListAjax()
    {
        $list_faq =  has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::LIST_FAQ);
        if ($list_faq == false) {
            return parent::getAdminError($this->theme_path);
        }

        $view_faq =  has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::VIEW_FAQ);
        $edit_faq =  has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::EDIT_FAQ);
        $delete_faq =  has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::DELETE_FAQ);

        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'asc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '1') {
                $orderByArray = ['question' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '2') {
                $orderByArray = ['answer' => $order_by[0]['dir']];
            }
        }
        if (isset($search['value'])) {
            $searchKey = $search['value'];
        }
        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }

        $filter = Input::get('filter');
        $filter = strtoupper($filter);
        if (!in_array($filter, ['ACTIVE', 'INACTIVE'])) {
            $filter = 'ACTIVE';
        }

        $totalRecords = Faq::getFaqSearchCount($searchKey, $filter);
        $filteredRecords = Faq::getFaqSearchCount($searchKey, $filter);
        $filtereddata = Faq::getFaqwithPagenation($filter, $start, $limit, $orderByArray, $searchKey);
        $dataArr = [];
        foreach ($filtereddata as $key => $value) {
            $for_view_ancor = '';
            $for_edit_ancor = '';
            $for_delete_ancor = '';
            if ($view_faq == true) {
                $for_view_ancor = '<a class="btn btn-circle show-tooltip ajax" title="' . trans('admin/manageweb.action_view') . '" href="' . URL::to('/cp/manageweb/view-faq/' . $value['faq_id']) . '" ><i class="fa fa-eye"></i></a>';
            }
            if ($edit_faq == true) {
                $for_edit_ancor = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/manageweb.action_edit') . '" href="' . URL::to('/cp/manageweb/edit-faq/' . $value['faq_id']) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-edit"></i></a>';
            }
            if ($delete_faq == true) {
                $for_delete_ancor = '<a class="btn btn-circle show-tooltip deletemedia" title="' . trans('admin/manageweb.action_delete') . '" href="' . URL::to('/cp/manageweb/delete-faq/' . $value['faq_id']) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-trash-o"></i></a>';
            }
            $type = '';
            $namedata = '';
            $temparr = [
                '<input type="checkbox" value="' . $value['faq_id'] . '">',
                $value['question'],
                $value['answer'],
                $value['status'],
                $for_view_ancor . $for_edit_ancor . $for_delete_ancor,
            ];
            $dataArr[] = $temparr;
        }
        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr,
        ];

        return response()->json($finaldata);
    }

    public function postFaqUpload()
    {
        $add_faq =  has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::ADD_FAQ);
        if ($add_faq == false) {
            return parent::getAdminError($this->theme_path);
        }
        $rules = [
            'question' => 'Required|Min:5',
            'answer' => 'Required|Min:5',
        ];
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return redirect('cp/manageweb/add-faq')->withInput()
                ->withErrors($validation);
        } else {
            $addfaqjson = [
                'faq_id' => Faq::getMaxFaqId(),
                'question' => trim(strip_tags(Input::get('question'))),
                'answer' => Input::get('answer'),
                'status' => Input::get('status'),
                'created_at' => time(),
                'updated_at' => time()
            ];
            $upload = Faq::addFaq($addfaqjson);
            if (!$upload) {
                return redirect('cp/manageweb/add-faq')
                    ->with('error', trans('admin/manageweb.error_add_faq'));
            } else {
                // return redirect('cp/manageweb/')
                //                                 ->with('success',trans('admin/manageweb.success_add_faq'));
                $crumbs = [
                    trans('admin/dashboard.dashboard') => 'cp',
                    trans('admin/manageweb.manage_sites') => 'manageweb',
                    trans('admin/manageweb.success_message') => '',
                ];
                $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
                $this->layout->pageicon = 'fa fa-comments';
                $this->layout->header = view('admin.theme.common.header');
                $this->layout->sidebar = view('admin.theme.common.sidebar')
                    ->with('mainmenu', 'web')
                    ->with('submenu', 'faq');
                $this->layout->pagetitle = trans('admin/manageweb.faq_added_successfully');
                $this->layout->pagedescription = trans('admin/manageweb.faq_added_successfully');

                $this->layout->content = view('admin.theme.manageweb.faqsucesspage')->with('key', $addfaqjson['faq_id'])->with('question', $addfaqjson['question']);
                $this->layout->footer = view('admin.theme.common.footer');
            }
        }
    }

    public function postFaqUpdate($key = null)
    {
        $edit_faq =  has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::EDIT_FAQ);
        if ($edit_faq == false) {
            return parent::getAdminError($this->theme_path);
        }
        if (!is_null($key)) {
            $rules = [
                'question' => 'Required|Min:3',
                'answer' => 'Required|Min:3',
            ];
        }
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return redirect('cp/manageweb/edit-faq/' . $key)->withInput()
                ->withErrors($validation);
        } else {
            $addfaqjson = [
                'question' => trim(strip_tags(Input::get('question'))),
                'answer' => Input::get('answer'),
                'status' => Input::get('status'),
                'updated_at' => time()
            ];
            $update = Faq::updateFaq($key, $addfaqjson);
            if (is_null($update)) {
                return redirect('cp/manageweb/edit-faq' . $key)
                    ->with('error', trans('admin/manageweb.error_update_faq'));
            } else {
                /*return redirect('cp/manageweb/')
                                        ->with('success',trans('admin/manageweb.success_update_faq'));                    */
                $crumbs = [
                    trans('admin/dashboard.dashboard') => 'cp',
                    trans('admin/manageweb.manage_sites') => 'manageweb',
                    trans('admin/manageweb.success_message') => '',
                ];
                $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
                $this->layout->pageicon = 'fa fa-comments';
                $this->layout->header = view('admin.theme.common.header');
                $this->layout->sidebar = view('admin.theme.common.sidebar')
                    ->with('mainmenu', 'web')
                    ->with('submenu', 'faq');
                $this->layout->pagetitle = trans('admin/manageweb.faq_edited_successfully');
                $this->layout->pagedescription = trans('admin/manageweb.faq_edited_successfully');
                $this->layout->content = view('admin.theme.manageweb.faqsucesspage')->with('key', $key)->with('question', Input::get('question'));
                $this->layout->footer = view('admin.theme.common.footer');
            }
        }
    }

    public function getAddFaq()
    {
        $add_faq =  has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::ADD_FAQ);
        if ($add_faq == false) {
            return parent::getAdminError($this->theme_path);
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/manageweb.manage_site') => 'manageweb',
            trans('admin/manageweb.add_faq') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pageicon = 'fa fa-wrench';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'web')
            ->with('submenu', 'faq');
        $this->layout->pagetitle = trans('admin/manageweb.add_faq');
        $this->layout->pagedescription = trans('admin/manageweb.add_faq');
        $this->layout->pageicon = 'fa fa-comments';
        $this->layout->content = view('admin.theme.manageweb.addfaq');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getEditFaq($key = null)
    {
        $edit_faq =  has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::EDIT_FAQ);
        if ($edit_faq == false) {
            return parent::getAdminError($this->theme_path);
        }
        if (!is_null($key)) {
            $faq = Faq::getOneFaq($key);

            if (!is_null(Input::get('filter'))) {
                $filter = Input::get('filter');
            } else {
                $filter = 'ACTIVE';
            }

            if (!is_null(Input::get('start')) && !is_null(Input::get('limit'))) {
                $start_serv = (int)Input::get('start');
                $length_page_serv = (int)Input::get('limit');
            } else {
                $start_serv = 0;
                $length_page_serv = 10;
            }
            $crumbs = [
                trans('admin/dashboard.dashboard') => 'cp',
                trans('admin/manageweb.manage_sites') => 'manageweb',
                trans('admin/manageweb.faq') => '',
            ];
            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pageicon = 'fa fa-comments';
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'web')
                ->with('submenu', 'faq');
            $this->layout->pagetitle = trans('admin/manageweb.edit_faq');
            $this->layout->pagedescription = trans('admin/manageweb.edit_faq');
            $this->layout->content = view('admin.theme.manageweb.editfaq')
                ->with('filter', $filter)
                ->with('start_serv', $start_serv)
                ->with('length_page_serv', $length_page_serv)
                ->with('faq', $faq[0]);
            $this->layout->footer = view('admin.theme.common.footer');
        } else {
            return redirect('cp/manageweb/' . $key)
                ->with('error', trans('admin/manageweb.error_edit_faq'));
        }
    }

    public function getDeleteFaq($key = null)
    {
        $delete_faq =  has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::DELETE_FAQ);
        if ($delete_faq == false) {
            return parent::getAdminError($this->theme_path);
        }
        if ($key) {
            $deleted = Faq::singleDelete($key);

            $start = (int)Input::get('start', 0);
            $limit = (int)Input::get('limit', 10);
            $search = Input::get('search', '');
            $order_by = Input::get('order_by', '1 desc');
            $filter = Input::get('filter', 'ACTIVE');

            $totalRecords = Faq::getFaqSearchCount(null, $filter);
            if ($totalRecords <= $start) {
                $start -= $limit;
                if ($start < 0) {
                    $start = 0;
                }
            }
            if (is_null($deleted)) {
                return redirect('cp/manageweb/' . $key)
                    ->with('error', trans('admin/manageweb.error_delete_faq'));
            } else {
                return redirect('cp/manageweb?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $search . '&order_by=' . $order_by)
                    ->with('success', trans('admin/manageweb.success_delete_faq'));
            }
        } else {
            return;
        }
    }

    public function postBulkDeleteFaq()
    {
        // $ary = json_decode(Input::get('pages_delete'));
        $delete_faq = has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::DELETE_FAQ);
        if ($delete_faq == false) {
            return parent::getAdminError($this->theme_path);
        }
        $ary = explode(',', Input::get('ids'));
        $str = '';
        if (is_null($ary)) {
            return 'yes we got 0 delete object';
        } else {
            foreach ($ary as $value) {
                $res = Faq::singleDelete($value);
            }

            $msg = trans('admin/manageweb.success_delete_faq');

            return redirect('/cp/manageweb')
                ->with('success', $msg);
        }
    }

    public function getViewFaq($key = null)
    {
        $view_faq =  has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::VIEW_FAQ);
        if ($view_faq == false) {
            return parent::getAdminError($this->theme_path);
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/manageweb.manage_sites') => 'manageweb',
            trans('admin/manageweb.view_faq') => '',
        ];
        $faq = Faq::getOneFaq($key);

        return view('admin.theme.manageweb.viewfaq')->with('faq', $faq[0]);
    }

    //end Faq Operation

    //static pages Operation's here
    public function getStaticPages()
    {
        $list_staticpage =  has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::LIST_STATICPAGE);
        if ($list_staticpage == false) {
            return parent::getAdminError($this->theme_path);
        }
        $opration = Input::get('opration');
        if (!is_null(Input::get('filter'))) {
            $filter = Input::get('filter');
        } else {
            $filter = 'ACTIVE';
        }
        $start_serv = 0;
        $length_page_serv = 10;

        if (!is_null(Input::get('start_serv')) && !is_null(Input::get('length_page_serv'))) {
            $start_serv = (int)Input::get('start_serv');
            $length_page_serv = (int)Input::get('length_page_serv');
        }
        if ($opration == 'add') {
            $crumbs = [
                trans('admin/dashboard.dashboard') => 'cp',
                trans('admin/manageweb.manage_sites') => 'manageweb',
                trans('admin/manageweb.manage_static_pages') => '',
            ];
            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pageicon = 'fa fa-wrench';
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'web')
                ->with('submenu', 'staticpages');
            $this->layout->pagetitle = trans('admin/manageweb.manage_static_page');
            $this->layout->pagedescription = trans('admin/manageweb.manage_static_page');
            $this->layout->content = view('admin.theme.manageweb.addstaticpages');
            $this->layout->footer = view('admin.theme.common.footer');
        } else {
            $res = ManageWebModel::where('manageweb_area', '=', 'static page')->get();
            $crumbs = [
                trans('admin/dashboard.dashboard') => 'cp',
                trans('admin/manageweb.manage_sites') => 'manageweb',
                trans('admin/manageweb.manage_static_pages') => '',
            ];
            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pageicon = 'fa fa-wrench';
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'web')
                ->with('submenu', 'staticpages');
            $this->layout->pagetitle = trans('admin/manageweb.manage_static_page');
            $this->layout->pagedescription = trans('admin/manageweb.manage_static_page');

            $this->layout->content = view('admin.theme.manageweb.managestaticpage')
                ->with('filter', $filter)
                ->with('start_serv', $start_serv)
                ->with('length_page_serv', $length_page_serv)
                ->with('staticpages', $res);
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }

    public function postUploadStaticpage()
    {
        $add_staticpage = has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::ADD_STATICPAGE);
        if ($add_staticpage == false) {
            return parent::getAdminError($this->theme_path);
        }
        $rules = [
            'title' => 'Required|Min:3',
            'meta_key' => 'Required|Min:3',
            'meta_description' => 'Required|Min:5',
            'static_page_content' => 'Required|Min:10',
        ];
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return redirect('/cp/manageweb/static-pages?opration=add')->withInput()
                ->withErrors($validation);
        } else {
            $slug = self::makeSlug(Input::get('title'));
            if (is_null($slug)) {
                return redirect('cp/manageweb/static-pages?opration=add')
                    ->with('error', trans('admin/manageweb.error_add_static_page_slug'))
                    ->withInput()
                    ->withErrors($validation);
            }
            $addstaticpagejson = [
                'staticpagge_id' => StaticPage::getMaxStaticpaggeId(),
                'title' => trim(strip_tags(Input::get('title'))),
                'slug' => $slug,
                'metakey' => trim(strip_tags(Input::get('meta_key'))),
                'meta_description' => trim(strip_tags(Input::get('meta_description'))),
                'content' => Input::get('static_page_content'),
                'editor_images' => Input::get('editor_images', []),
                'status' => Input::get('status'),
                'created_at' => time(),
                'updated_at' => time()
            ];
            $upload = StaticPage::addStaticPage($addstaticpagejson);
            if (!$upload) {
                return redirect('cp/manageweb/static-pages?opration=add')
                    ->with('error', trans('admin/manageweb.error_add_static_page'));
            } else {
                // return redirect('cp/manageweb/static-pages')
                //                         ->with('success',trans('admin/manageweb.success_add_static_page'));
                $crumbs = [
                    trans('admin/dashboard.dashboard') => 'cp',
                    trans('admin/manageweb.manage_sites') => 'manageweb',
                    trans('admin/manageweb.success_message') => '',
                ];
                $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
                $this->layout->pageicon = 'fa fa-wrench';
                $this->layout->header = view('admin.theme.common.header');
                $this->layout->sidebar = view('admin.theme.common.sidebar')
                    ->with('mainmenu', 'web')
                    ->with('submenu', 'staticpages');
                $this->layout->pagetitle = trans('admin/manageweb.static_page_added_success');
                $this->layout->pagedescription = trans('admin/manageweb.static_page_added_success');

                $this->layout->content = view('admin.theme.manageweb.staticpagesucess')->with('key', $addstaticpagejson['staticpagge_id'])->with('title', $addstaticpagejson['title']);
                $this->layout->footer = view('admin.theme.common.footer');
            }
        }
    }

    public function getViewStaticPage($key = null)
    {
        $view_staticpage = has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::VIEW_STATICPAGE);
        if ($view_staticpage == false) {
            return parent::getAdminError($this->theme_path);
        }
        if ($key) {
            $crumbs = [
                trans('admin/dashboard.dashboard') => 'cp',
                trans('admin/manageweb.manage_sites') => 'manageweb',
                trans('admin/manageweb.view_static_pages') => '',
            ];
            $staticpages = StaticPage::getOneStaticPage($key);

            return view('admin.theme.manageweb.viewstaticpage')->with('staticpage', $staticpages[0]);
        } else {
            return;
        }
    }

    public function getEditStaticPage($key = null)
    {
        $edit_staticpage = has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::EDIT_STATICPAGE);
        if ($edit_staticpage == false) {
            return parent::getAdminError($this->theme_path);
        }
        if ($key) {
            if (!is_null(Input::get('filter'))) {
                $filter = Input::get('filter');
            } else {
                $filter = 'ACTIVE';
            }

            if (!is_null(Input::get('start')) && !is_null(Input::get('limit'))) {
                $start_serv = (int)Input::get('start');
                $length_page_serv = (int)Input::get('limit');
            } else {
                $start_serv = 0;
                $length_page_serv = 10;
            }
            $default_static_page_ids = [1, 2];
            $crumbs = [
                trans('admin/dashboard.dashboard') => 'cp',
                trans('admin/manageweb.manage_sites') => 'manageweb',
                trans('admin/manageweb.edit_static_page') => '',
            ];
            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $staticpages = StaticPage::getOneStaticPage($key);
            $this->layout->pageicon = 'fa fa-wrench';
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'web')
                ->with('submenu', 'staticpages');
            $this->layout->pagetitle = trans('admin/manageweb.edit_static_page');
            $this->layout->pagedescription = trans('admin/manageweb.edit_static_page');
            $this->layout->content = view('admin.theme.manageweb.editstaticpage')
                ->with('filter', $filter)
                ->with('start_serv', $start_serv)
                ->with('length_page_serv', $length_page_serv)
                ->with('staticpage', $staticpages[0])
                ->with('default_static_page_ids', $default_static_page_ids);
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }

    public function postEditStaticPageUpdate($key = null)
    {
        $edit_staticpage = has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::EDIT_STATICPAGE);
        if ($edit_staticpage == false) {
            return parent::getAdminError($this->theme_path);
        }

        if ($key) {
            $rules = [
                'title' => 'Required|Min:3',
                'meta_key' => 'Required|Min:3',
                'meta_description' => 'Required|Min:5',
                'static_page_content' => 'Required|Min:10',
            ];
            $validation = Validator::make(Input::all(), $rules);
            // echo $validation->fails();
            // print_r($validation);
            // die;
            if ($validation->fails()) {
                return redirect('/cp/manageweb/edit-static-page/' . $key)->withInput()
                    ->withErrors($validation);
            } else {
                $staticpage = StaticPage::getOneStaticPage($key);
                $editer_images = $staticpage[0]->editor_images;
                if (is_array($editer_images)) {
                    $editer_images = array_merge($editer_images, Input::get('editor_images', []));
                } else {
                    $editer_images = Input::get('editor_images', []);
                }
                $addstaticpagejson = [
                    'title' => trim(strip_tags(Input::get('title'))),
                    'metakey' => trim(strip_tags(Input::get('meta_key'))),
                    'meta_description' => trim(strip_tags(Input::get('meta_description'))),
                    'content' => Input::get('static_page_content'),
                    'editor_images' => $editer_images,
                    'status' => Input::get('status'),
                    'updated_at' => time()
                ];
                $update = StaticPage::updateStaticPage($key, $addstaticpagejson);
                if ($update <= 0) {
                    return redirect('/cp/manageweb/edit-static-page/' . $key)
                        ->with('error', trans('admin/manageweb.error_edit_static_page'));
                } else {
                    /*return redirect('cp/manageweb/static-pages')
                                                          ->with('success',trans('admin/manageweb.success_edit_static_page'));                    */
                    $crumbs = [
                        trans('admin/dashboard.dashboard') => 'cp',
                        trans('admin/manageweb.manage_sites') => 'manageweb',
                        trans('admin/manageweb.success_message') => '',
                    ];
                    $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
                    $this->layout->pageicon = 'fa fa-wrench';
                    $this->layout->header = view('admin.theme.common.header');
                    $this->layout->sidebar = view('admin.theme.common.sidebar')
                        ->with('mainmenu', 'web')
                        ->with('submenu', 'staticpages');
                    $this->layout->pagetitle = trans('admin/manageweb.static_page_edited_success');
                    $this->layout->pagedescription = trans('admin/manageweb.static_page_edited_success');
                    $this->layout->content = view('admin.theme.manageweb.staticpagesucess')->with('key', $key)->with('title', Input::get('title'));
                    $this->layout->footer = view('admin.theme.common.footer');
                }
            }
        } else {
            return;
        }
    }

    public function getDelete($key = null)
    {
        $delete_staticpage = has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::DELETE_STATICPAGE);
        if ($delete_staticpage == false) {
            return parent::getAdminError($this->theme_path);
        }
        if ($key) {
            $deleted = StaticPage::singleDelete($key);

            $start = (int)Input::get('start', 0);
            $limit = (int)Input::get('limit', 10);
            $search = Input::get('search', '');
            $order_by = Input::get('order_by', '1 desc');
            $filter = Input::get('filter', 'ACTIVE');

            $totalRecords = StaticPage::getStaticPageSearchCount($search, $filter);
            if ($totalRecords <= $start) {
                $start -= $limit;
                if ($start < 0) {
                    $start = 0;
                }
            }

            if (is_null($deleted)) {
                return redirect('cp/manageweb/static-pages/' . $key)
                    ->with('error', trans('admin/manageweb.error_delete_static_page'));
            } else {
                return redirect('cp/manageweb/static-pages?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $search . '&order_by=' . $order_by)
                    ->with('success', trans('admin/manageweb.success_delete_static_page'));
            }
        } else {
            return;
        }
    }

    public function postBulkDelete()
    {
        $delete_staticpage = has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::DELETE_STATICPAGE);
        if ($delete_staticpage == false) {
            return parent::getAdminError($this->theme_path);
        }
        $ary = explode(',', Input::get('ids'));
        $str = '';
        if (is_null($ary)) {
            return 'yes we got 0 delete object';
        } else {
            foreach ($ary as $value) {
                $res = StaticPage::singleDelete($value);
                $str .= $value . '::' . $res;
            }
            // print_r($str);
            // return "Your selected Static Pages are Deleted ";
            $msg = trans('admin/manageweb.success_delete_static_page');

            return redirect('/cp/manageweb/static-pages')
                ->with('success', $msg);
        }
    }

    public function getStaticpagesListAjax()
    {
        $list_staticpage = has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::LIST_STATICPAGE);
        if ($list_staticpage == false) {
            return parent::getAdminError($this->theme_path);
        }

        $view_staticpage = has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::VIEW_STATICPAGE);
        $edit_staticpage = has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::EDIT_STATICPAGE);
        $delete_staticpage = has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::DELETE_STATICPAGE);

        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'asc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '1') {
                $orderByArray = ['title' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '2') {
                $orderByArray = ['metakey' => $order_by[0]['dir']];
            }
        }
        if (isset($search['value'])) {
            $searchKey = $search['value'];
        }
        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }
        $filter = Input::get('filter');
        $filter = strtoupper($filter);
        if (!in_array($filter, ['ACTIVE', 'INACTIVE'])) {
            $filter = 'ACTIVE';
        }
        $totalRecords = StaticPage::getStaticPageSearchCount($searchKey, $filter);
        $filteredRecords = StaticPage::getStaticPageSearchCount($searchKey, $filter);
        $filtereddata = StaticPage::getStaticPagewithPagenation($filter, $start, $limit, $orderByArray, $searchKey);
        $dataArr = [];
        $default_static_page_ids = [1, 2];
        foreach ($filtereddata as $key => $value) {
            $for_view_ancor = '';
            $for_edit_ancor = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/manageweb.edit_static_page') . '"  ><i class="fa fa-edit"></i></a> ';
            $for_delete_ancor = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/manageweb.cant_delete_default_static_page') . '"  ><i class="fa fa-trash-o"></i></a> ';
            if ($view_staticpage == true) {
                $for_view_ancor = '<a class="btn btn-circle show-tooltip ajax" title="' . trans('admin/manageweb.view_details') . '"  href="' . URL::to('/cp/manageweb/view-static-page/' . $value['staticpagge_id']) . '" ><i class="fa fa-eye"></i></a>';
            }
            if ($edit_staticpage == true) {
                $for_edit_ancor = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/manageweb.edit_static_page') . '"  href="' . URL::to('/cp/manageweb/edit-static-page/' . $value['staticpagge_id']) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-edit"></i></a> ';
            }
            if ($delete_staticpage == true && !in_array($value['staticpagge_id'], $default_static_page_ids)) {
                $for_delete_ancor = '<a class="btn btn-circle show-tooltip deletemedia" title="' . trans('admin/manageweb.delete_static_page') . '"  href="' . URL::to('/cp/manageweb/delete/' . $value['staticpagge_id']) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-trash-o"></i></a>';
            }
            $type = '';
            $namedata = '';
            $temparr = [
                '<input type="checkbox" value="' . $value['staticpagge_id'] . '">',
                $value['title'],
                $value['metakey'],
                $value['status'],
                $for_view_ancor . $for_edit_ancor . $for_delete_ancor,
            ];
            $dataArr[] = $temparr;
        }
        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr,
        ];

        return response()->json($finaldata);
    }
    //static pages Operation's here end here
    //start News letter
    public function getNewsLetter()
    {
        $list_newsletter = has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::LIST_NEWSLETTER);
        if ($list_newsletter == false) {
            return parent::getAdminError($this->theme_path);
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/manageweb.manage_sites') => 'manageweb',
            trans('admin/manageweb.newsletter') => '',
        ];
        // $res = NewsLetter::where('manageweb_area','=','news letter')->where('status','=','ACTIVE')->get();
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pageicon = 'fa fa-newspaper-o';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'web')
            ->with('submenu', 'newsletter');
        $this->layout->pagetitle = trans('admin/manageweb.view_newsletter');
        $this->layout->pagedescription = trans('admin/manageweb.manage_newsletter');

        $this->layout->content = view('admin.theme.manageweb.newsletter');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getUploadTempnl()
    {
        $addnewsletterjson = [
            'newsletter_id' => NewsLetter::getMaxNewsletterId(),
            'email_id' => Input::get('email_id'),
            'user_status' => Input::get('user_status'),
            'subscribed_on' => Timezone::convertToUTC(date('d-m-Y', time()), Auth::user()->timezone, 'U'),
            'status' => Input::get('status'),
        ];
        $upload = NewsLetter::addNewsLetter($addnewsletterjson);
        echo $upload;
        die;
    }

    public function getNewsLettersListAjax()
    {
        $list_newsletter = has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::LIST_NEWSLETTER);
        if ($list_newsletter == false) {
            return parent::getAdminError($this->theme_path);
        }
        $delete_newsletter = has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::DELETE_NEWSLETTER);

        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'asc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '1') {
                $orderByArray = ['email_id' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '2') {
                $orderByArray = ['user_status' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '3') {
                $orderByArray = ['subscribed_on' => $order_by[0]['dir']];
            }
        }
        if (isset($search['value'])) {
            $searchKey = $search['value'];
        }
        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }
        $filter = Input::get('filter');
        $filter = strtoupper($filter);
        if (!in_array($filter, ['ACTIVE', 'INACTIVE'])) {
            $filter = 'ACTIVE';
        }
        $totalRecords = NewsLetter::getNewsLetterSearchCount($searchKey, $filter);
        $filteredRecords = NewsLetter::getNewsLetterSearchCount($searchKey, $filter);
        $filtereddata = NewsLetter::getNewsLetterwithPagenation($filter, $start, $limit, $orderByArray, $searchKey);
        $dataArr = [];
        foreach ($filtereddata as $key => $value) {
            $for_delete_ancor = '';

            if ($delete_newsletter == true) {
                $for_delete_ancor = '<a class="btn btn-circle show-tooltip deletemedia" title="' . trans('admin/manageweb.action_delete') . '" href="' . URL::to('/cp/manageweb/delete-nl/' . $value['newsletter_id']) . '" ><i class="fa fa-trash-o"></i></a>';
            }
            $type = '';
            $namedata = '';
            $temparr = [
                '<input type="checkbox" value="' . $value['newsletter_id'] . '">',
                $value['email_id'],
                $value['user_status'],
                Timezone::convertFromUTC('@' . (int)$value['subscribed_on'], Auth::user()->timezone, config('app.date_format')),
                $for_delete_ancor,
            ];
            $dataArr[] = $temparr;
        }
        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr,
        ];

        return response()->json($finaldata);
    }

    public function getDeleteNl($key = null)
    {
        $delete_newsletter = has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::DELETE_NEWSLETTER);
        if ($delete_newsletter == false) {
            return parent::getAdminError($this->theme_path);
        }
        if ($key) {
            $addnljson = [
                'status' => 'INACTIVE',
            ];
            $deleted = NewsLetter::updateNewsLetter($key, $addnljson);
            if (is_null($deleted)) {
                return redirect('cp/manageweb/news-letter')
                    ->with('error', trans('admin/manageweb.error_delete_nl'));
            } else {
                return redirect('cp/manageweb/news-letter')
                    ->with('success', trans('admin/manageweb.success_delete_nl'));
            }
        } else {
            return redirect('cp/manageweb/news-letter')
                ->with('error', trans('admin/manageweb.error_delete_nl'));
        }
    }

    public function postBulkDeleteNl()
    {
        $delete_newsletter = has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::DELETE_NEWSLETTER);
        if ($delete_newsletter == false) {
            return parent::getAdminError($this->theme_path);
        }
        $ary = explode(',', Input::get('ids'));
        $deleted = null;
        if (is_null($ary)) {
            return redirect('cp/manageweb/news-letter')
                ->with('error', trans('admin/manageweb.error_delete_nl'));
        } else {
            foreach ($ary as $value) {
                if (!$value == '') {
                    $addnljson = [
                        'status' => 'INACTIVE',
                    ];
                    $deleted = NewsLetter::updateNewsLetter($value, $addnljson);
                }
            }
            if (is_null($deleted)) {
                return redirect('cp/manageweb/news-letter')
                    ->with('error', trans('admin/manageweb.error_delete_nl'));
            } else {
                return redirect('cp/manageweb/news-letter')
                    ->with('success', trans('admin/manageweb.success_delete_nl'));
            }
        }
    }

    public function getExportNewsletter()
    {
        $export_newsletter = has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::EXPORT_NEWSLETTER);
        if ($export_newsletter == false) {
            return parent::getAdminError($this->theme_path);
        }
        $res = NewsLetter::get();

        return view('admin.theme.manageweb.exportnewsletter')->with('newsletters', $res);
    }

    public static function makeSlug($title)
    {
        $slug = strtolower(stripslashes(trim($title)));   // Convert all the text to lower case
        $slug = str_replace(' - ', '-', $slug);   // Replace any ' - ' sign with spaces on both sides to '-'
        $slug = str_replace(' & ', '-and-', $slug);   // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace('& ', '-and-', $slug);    // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace("'", '', $slug);  // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace('\\', '', $slug); // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace('/', '-', $slug); // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace(', ', '-', $slug);    // Replace any comma and a space to -
        $slug = str_replace('.com', 'dotcom', $slug); // Remove any dot and a space
        $slug = str_replace('.', '', $slug);  // Remove any dot and a space
        $slug = str_replace('   ', '-', $slug);   // replace space to -
        $slug = str_replace('  ', '-', $slug);    // replace space to -
        $slug = str_replace(' ', '-', $slug); // replace space to -
        $slug = str_replace('!', '', $slug);  // remove !
        $slug = str_replace('#', '', $slug);  // remove #
        $slug = str_replace('$', '', $slug);  // remove $
        $slug = str_replace(':', '', $slug);  // remove :
        $slug = str_replace(';', '', $slug);  // remove ;
        $slug = str_replace('[', '', $slug);  // remove [
        $slug = str_replace(']', '', $slug);  // remove ]
        $slug = str_replace('(', '', $slug);  // remove (
        $slug = str_replace(')', '', $slug);  // remove )
        $slug = str_replace('\n', '', $slug); // remove \n
        $slug = str_replace('\r', '', $slug); // remove \r
        $slug = str_replace('?', '', $slug);  // remove ?
        $slug = str_replace('`', '', $slug);  // remove `
        $slug = str_replace('%', '', $slug);  // remove %
        $slug = str_replace('&#39;', '', $slug);  // remove &#39; = '
        $slug = str_replace('&39;', '', $slug);   // remove &39; = '
        $slug = str_replace('&39', '', $slug);    // remove &39; = '
        $slug = str_replace('&quot;', '-', $slug);
        $slug = str_replace('\"', '-', $slug);
        $slug = str_replace('"', '-', $slug);
        $slug = str_replace('&lt;', '-', $slug);
        $slug = str_replace('&gt;', '-', $slug);
        $slug = str_replace('<', '', $slug);
        $slug = str_replace('>', '', $slug);

        $exist = StaticPage::getTitleExist($slug);
        if (!is_null($exist)) {
            return;
        }

        return $slug;
    }
}
