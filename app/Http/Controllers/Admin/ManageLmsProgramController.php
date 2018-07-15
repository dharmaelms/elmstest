<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminBaseController;
use App\Model\Category;
use App\Model\Common;
use App\Model\ManageAttribute;
use App\Model\ManageLmsProgram;
use App\Model\SiteSetting;
use App\Model\User;
use Auth;
use Input;
use Redirect;
use Request;
use Timezone;
use URL;
use Validator;

//use Crypt;

class ManageLmsProgramController extends AdminBaseController
{
    protected $layout = 'admin.theme.layout.master_layout';

    /**
     * @no param
     * @return \Illuminate\Http\Response
     */
    public function __construct(Request $request)
    {
        //$this->theme_path = 'admin.theme';
        $input = $request::input();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request::merge($input);

        $this->theme_path = 'admin.theme';
    }

    /**
     *Display a listing of the lms programs.
     */
    public function getIndex()
    {

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/lmscourse.lmscourse') => '',

        ];

        $site_url = SiteSetting::module('Lmsprogram', 'site_url');
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pageicon = 'fa fa-wrench';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'lmscourse');

        $this->layout->pagetitle = trans('admin/lmscourse.manage_course');
        $this->layout->pagedescription = '';
        if (!empty($site_url)) {
            $this->layout->content = view('admin.theme.lmsprogram.list_lmsprogram');
        } else {
            $this->layout->content = view('admin.theme.lmsprogram.error_lmsprogram');
        }
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     *Displays creation of lms programs.
     */
    public function getCreateLmsprogram()
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/lmscourse.lmscourse') => 'lmscoursemanagement',
            trans('admin/lmscourse.create_course') => '',
        ];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pageicon = 'fa fa-wrench';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'lmscourse');

        $this->layout->pagetitle = trans('admin/lmscourse.create_course');
        $this->layout->pagedescription = '';
        $sort_order = ManageLmsProgram::where('status', '!=', 'DELETED')->max('sort_order');
        $sort_order = $sort_order + 1;
        $this->layout->content = view('admin.theme.lmsprogram.createlmsprogram')->with('sort_order', $sort_order);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getEditLmsprogram($id)
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/lmscourse.lmscourse') => 'lmscoursemanagement',
            trans('admin/lmscourse.update_course') => '',
        ];

        if (!is_null(Input::get('filter'))) {
            $filter = Input::get('filter');
        }
        $start = 0;
        $limit = 10;

        if (!is_null(Input::get('start')) && !is_null(Input::get('limit'))) {
            $start = (int)Input::get('start');
            $limit = (int)Input::get('limit');
        }
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/lmscourse.edit_course');
        $this->layout->pageicon = 'fa fa-group';
        $this->layout->pagedescription = trans('admin/lmscourse.edit_lms_program');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'lmscourse');
        $this->layout->footer = view('admin.theme.common.footer');
        $lmsprogram = ManageLmsProgram::getLmsProgramUsingID($id);
        $lmsprogram = $lmsprogram[0];
        $sort_order = ManageLmsProgram::where('status', '!=', 'DELETED')->max('sort_order');
        $this->layout->content = view('admin.theme.lmsprogram.editlmsprogram')
            ->with('filter', $filter)
            ->with('start', $start)
            ->with('limit', $limit)
            ->with('lmsprogram', $lmsprogram)
            ->with('sort_order', $sort_order);
    }

    /*To insert lms program*/
    public function postAddLmsprogram()
    {
        $id = 0;
        $data = Input::all();
        $varianttype = 'batch';
        $custom_rules = ManageAttribute::getRules($varianttype);
        $title = ManageLmsProgram::checkProgramTitle($data['program_title'], $id);
        $lower = ManageLmsProgram::checkLowerTitle($data['title_lower'], $id);

        Validator::extend('datecheck', function ($attribute, $value, $parameters) {
            $feed_start_date = Input::get('program_startdate');
            $feed_end_date = Input::get('program_enddate');

            if ((strtotime($feed_start_date) < strtotime($feed_end_date))) {
                return true;
            }

            return false;
        });
        Validator::extend('displaydatecheck', function ($attribute, $value, $parameters) {
            $feed_display_start_date = Input::get('program_display_startdate');
            $feed_display_end_date = Input::get('program_display_enddate');
            if ((strtotime($feed_display_start_date) < strtotime($feed_display_end_date))) {
                return true;
            }

            return false;
        });
        Validator::extend('displaystartdatecheck', function ($attribute, $value, $parameters) {
            $feed_start_date = Input::get('program_startdate');
            $feed_display_start_date = Input::get('program_display_startdate');
            if ((strtotime($feed_display_start_date) >= strtotime($feed_start_date))) {
                return true;
            }

            return false;
        });
        Validator::extend('displayenddatecheck', function ($attribute, $value, $parameters) {
            $feed_end_date = Input::get('program_enddate');
            $feed_display_end_date = Input::get('program_display_enddate');
            if ((strtotime($feed_display_end_date) <= strtotime($feed_end_date))) {
                return true;
            }

            return false;
        });
        $messages = [
            'displaystartdatecheck' => trans('admin/lmscourse.disp_start_date_great_than_start_date'),
            'displayenddatecheck' => trans('admin/lmscourse.disp_end_date_less_than_end_date'),
            'displaydatecheck' => trans('admin/lmscourse.disp_end_date_greater_than_disp_start_date'),
            'datecheck' => trans('admin/lmscourse.date_check'),
        ];
        $rules = [
            'program_title' => 'Required',
            'title_lower' => 'Required',

            'program_startdate' => 'Required',
            'program_enddate' => 'Required|datecheck',
            'program_display_startdate' => 'Required|displaystartdatecheck',
            'program_display_enddate' => 'Required|displaydatecheck|displayenddatecheck',

        ];
        if (!empty($custom_rules) && SiteSetting::module('Lmsprogram', 'more_batches') == 'on') {
            $rules += $custom_rules;
        }
        $validation = Validator::make(Input::all(), $rules, $messages);
        if ($validation->fails()) {
            return Redirect::back()->withInput()->withErrors($validation);
        } elseif ($title > 0) {
            $error = trans('admin/lmscourse.uniquetitle_alert');
            return Redirect::back()->withInput()->with('programtitle_exist', $error);
        } elseif ($lower > 0) {
            $error = trans('admin/lmscourse.uniqueshortname_alert');
            return Redirect::back()->withInput()->with('titlelower_exist', $error);
        } else {
            $mediaid = Input::get('banner', '');
            $slug = Category::getCategorySlug($data['program_title']);
            $name = Auth::user()->firstname . ' ' . Auth::user()->lastname;
            $username = Auth::user()->username;
            $record = ManageLmsProgram::addLmsprogram(Input::all(), $slug, $name, $username, $varianttype, $mediaid);
            $success = trans('admin/lmscourse.addlmscourse_success');
            return redirect('cp/lmscoursemanagement/')->with('success', $success);
        }
    }

    public function getRemoveLmsprogram($id = null)
    {
        ManageLmsProgram::deleteLmsProgram($id);

        $success = trans('admin/lmscourse.removelmsprogram_success');

        $start = (int)Input::get('start', 0);
        $limit = (int)Input::get('limit', 10);
        $search = Input::get('search', '');
        $order_by = Input::get('order_by', '2 desc');
        $filter = Input::get('filter', 'ALL');

        $totalRecords = ManageLmsProgram::getLmsprogramCount();
        if ($totalRecords <= $start) {
            $start -= $limit;
            if ($start < 0) {
                $start = 0;
            }
        }
        $redirect = '/cp/lmscoursemanagement?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $search . '&order_by=' . $order_by;

        return redirect($redirect)->with('success', $success);
    }

    public function postBulkDelete()
    {
        $attributes_checked = Input::get('ids');
        $attributes_checked = explode(',', trim($attributes_checked, ','));

        foreach ($attributes_checked as $attribute) {
            ManageLmsProgram::deleteLmsProgram($attribute);
        }
        $success = trans('admin/lmscourse.removemultiplelmsprogram_success');

        return redirect('cp/lmscoursemanagement/')->with('success', $success);
    }

    public function getAddBatch($id)
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/lmscourse.lmscourse') => 'lmscoursemanagement',
            trans('admin/lmscourse.add_batch') => '',
        ];

        $course = ManageLmsProgram::where('program_id', '=', (int)$id)->value('program_title');

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pageicon = 'fa fa-wrench';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'lmscourse');

        $this->layout->pagetitle = trans('admin/lmscourse.add_batch');

        $this->layout->pagedescription = '';
        $sort_order = ManageLmsProgram::getBatchSort($id);

        $this->layout->content = view('admin.theme.lmsprogram.createbatch')->with('id', $id)->with('sort_order', $sort_order)->with('course', $course);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function postAddBatch()
    {
        $id = 0;
        $data = Input::all();
        $varianttype = 'batch';
        $rules = ManageAttribute::getRules($varianttype);
        $validation = Validator::make(Input::all(), $rules);
        $batch = ManageLmsProgram::checkBatchName($id, $data, $data['program_id']);
        if ($validation->fails()) {
            return Redirect::back()->withInput()->withErrors($validation);
        } elseif ($batch > 0) {
            $error = trans('admin/lmscourse.uniquebatch_alert');
            return Redirect::back()->withInput()->with('batchname', $error);
        } else {
            ManageLmsProgram::addBatch(Input::all());
            $success = trans('admin/lmscourse.addbatch_success');
            return redirect('cp/lmscoursemanagement/')->with('success', $success);
        }
    }

    public function postEditLmsprogram($id)
    {

        $data = Input::all();
        $title = ManageLmsProgram::checkProgramTitle($data['program_title'], $id);
        $lower = ManageLmsProgram::checkLowerTitle($data['title_lower'], $id);

        Validator::extend('datecheck', function ($attribute, $value, $parameters) {
            $feed_start_date = Input::get('program_startdate');
            $feed_end_date = Input::get('program_enddate');

            if ((strtotime($feed_start_date) < strtotime($feed_end_date))) {
                return true;
            }

            return false;
        });
        Validator::extend('displaydatecheck', function ($attribute, $value, $parameters) {
            $feed_display_start_date = Input::get('program_display_startdate');
            $feed_display_end_date = Input::get('program_display_enddate');
            if ((strtotime($feed_display_start_date) < strtotime($feed_display_end_date))) {
                return true;
            }

            return false;
        });
        Validator::extend('displaystartdatecheck', function ($attribute, $value, $parameters) {
            $feed_start_date = Input::get('program_startdate');
            $feed_display_start_date = Input::get('program_display_startdate');
            if ((strtotime($feed_display_start_date) >= strtotime($feed_start_date))) {
                return true;
            }

            return false;
        });
        Validator::extend('displayenddatecheck', function ($attribute, $value, $parameters) {
            $feed_end_date = Input::get('program_enddate');
            $feed_display_end_date = Input::get('program_display_enddate');
            if ((strtotime($feed_display_end_date) <= strtotime($feed_end_date))) {
                return true;
            }

            return false;
        });
        Validator::extend('statuscheck', function ($attribute, $value, $parameters) {
            $status = Input::get('status');

            $enrolcount = ManageLmsProgram::checkEnrolUserCount($parameters[0]);
            if ($status == 'active') {
                return true;
            }
            if ($status == 'inactive' && $enrolcount > 0) {
                return false;
            }

            return true;
        });
        $messages = [
            'displaystartdatecheck' => trans('admin/lmscourse.disp_start_date_great_than_start_date'),
            'displayenddatecheck' => trans('admin/lmscourse.disp_end_date_less_than_end_date'),
            'displaydatecheck' => trans('admin/lmscourse.disp_end_date_greater_than_disp_start_date'),
            'datecheck' => trans('admin/lmscourse.date_check'),
            'statuscheck' => 'You cannot de activate this course since its has registered users',

        ];

        $rules = [
            'program_title' => 'Required',
            'title_lower' => 'Required',
            'program_startdate' => 'Required',
            'program_enddate' => 'Required|datecheck',
            'program_display_startdate' => 'Required|displaystartdatecheck',
            'program_display_enddate' => 'Required|displaydatecheck|displayenddatecheck',
            'status' => 'Required|statuscheck:' . $id . '',
        ];

        $validation = Validator::make(Input::all(), $rules, $messages);
        if ($validation->fails()) {
            return Redirect::back()->withInput()->withErrors($validation);
        } elseif ($title > 0) {
            $error = trans('admin/lmscourse.uniquetitle_alert');
            return Redirect::back()->withInput()->with('programtitle_exist', $error);
        } elseif ($lower > 0) {
            $error = trans('admin/lmscourse.uniqueshortname_alert');
            return Redirect::back()->withInput()->with('titlelower_exist', $error);
        } else {
            $slug = Category::getCategorySlug($data['program_title']);
            $name = Auth::user()->firstname . ' ' . Auth::user()->lastname;
            $username = Auth::user()->username;
            $curval = Input::get('curval');
            $nextval = Input::get('sort_order');
            if ($curval != $nextval) {
                ManageLmsProgram::sortBanners($id, $curval, $nextval);
            }
            $mediaid = Input::get('banner', '');
            $record = ManageLmsProgram::editLmsProgram(Input::all(), $slug, $name, $username, $id, $mediaid);
            $success = trans('admin/lmscourse.updatelmscourse_success');
            return redirect('cp/lmscoursemanagement/')->with('success', $success);
        }
    }


    public function getManageBatch()
    {

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            'Manage ' . trans('admin/program.programs') => 'contentfeedmanagement',
            'List ' . trans('admin/program.programs') => '',
        ];
        $viewmode = Input::get('view', 'iframe');
        $relfilter = Input::get('relfilter', 'all');
        $from = Input::get('from', 'none');
        $relid = Input::get('relid', 'none');
        $pid = Input::get('pid');
        if ($viewmode == 'iframe') {
            $this->layout->breadcrumbs = '';
            $this->layout->pagetitle = '';
            $this->layout->pageicon = '';
            $this->layout->pagedescription = '';
            $this->layout->header = '';
            $this->layout->sidebar = '';
            $this->layout->content = view('admin.theme.lmsprogram.listbatchiframe')
                ->with('relfilter', $relfilter)
                ->with('from', $from)
                ->with('relid', $relid)
                ->with('pid', $pid);
            $this->layout->footer = '';
        }
    }

    public function getBatchListAjax()
    {
        $pid = $_GET['pid'];

        $start = 0;
        $limit = 10;
        $viewmode = Input::get('view', 'desktop');
        if ($viewmode != 'iframe') {
            $finaldata = [
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ];

            return response()->json($finaldata);
        }
        $search = Input::get('search');

        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['product_type' => 'desc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '1') {
                $orderByArray = ['product_type' => $order_by[0]['dir']];
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
        $filter = strtolower($filter);
        if (!in_array($filter, ['active', 'in-active'])) {
            $filter = 'all';
        } else {
            $filter = strtoupper($filter);
        }
        $relfilter = Input::get('relfilter', 'all');
        $from = Input::get('from', 'none');
        $relid = Input::get('relid', 'none');
        $relinfo = [$from => $relid];
        $totalRecords = ManageLmsProgram::getbatchcounts(null, $pid);
        $filteredRecords = $totalRecords;
        $filtereddata = ManageLmsProgram::getBatchListAjaxs($pid);
        $last_order = ManageLmsProgram:: getBatchMaxOrder($pid);
        $dataArr = [];
        foreach ($filtereddata as $key => $value) {
            $nxtval = $value['sort_order'] + 1;
            $preval = $value['sort_order'] - 1;
            $sortorder = $value['sort_order'] . '&nbsp;&nbsp;&nbsp;';

            if ($value['sort_order'] == 1) {
                $sortorder .= '<a class="btn btn-circle show-tooltip order" id="orderdownonly" title="' . trans('admin/lmscourse.click_to_change_display_order') . '"  data-toggle="modal"
href="' . URL::to('cp/lmscoursemanagement/batch-sort-order/' . $value['id'] . '/' . $value['sort_order'] . '/' . $nxtval . '/' . $pid) . ' "
value="' . $value['sort_order'] . '" >
<i class="fa fa-caret-down"></i></a>';
            } elseif ($value['sort_order'] == $last_order) {
                $sortorder .= '<a class="btn btn-circle show-tooltip order" id="orderuponly" title="' . trans('admin/lmscourse.click_to_change_display_order') . '"  data-toggle="modal"
href="' . URL::to('cp/lmscoursemanagement/batch-sort-order/' . $value['id'] . '/' . $value['sort_order'] . '/' . $preval . '/' . $pid) . '"
value="' . $value['sort_order'] . '" ><i class="fa fa-caret-up"></i></a>';
            } else {
                $sortorder .= '<a class="btn btn-circle show-tooltip order" id="orderuponly" title="' . trans('admin/lmscourse.click_to_change_display_order') . '"   data-toggle="modal"
href="' . URL::to('cp/lmscoursemanagement/batch-sort-order/' . $value['id'] . '/' . $value['sort_order'] . '/' . $preval . '/' . $pid) . '"
value="' . $value['sort_order'] . '" ><i class="fa fa-caret-up"></i></a>';
                $sortorder .= '<a class="btn btn-circle show-tooltip order" id="orderdownonly" title="' . trans('admin/lmscourse.click_to_change_display_order') . '"   data-toggle="modal"
href="' . URL::to('cp/lmscoursemanagement/batch-sort-order/' . $value['id'] . '/' . $value['sort_order'] . '/' . $nxtval . '/' . $pid) . '"
value="' . $value['sort_order'] . '" >
<i class="fa fa-caret-down"></i></a>';
            }


            $temparr = [];
            $varianttype = 'batch';
            $headers = ManageAttribute::getVariants($varianttype);
            if (!empty($headers)) {
                foreach ($headers as $header) {
                    $temparr[] = $value[$header['attribute_name']];
                }
            }
            $delete = '<a class="btn btn-circle show-tooltip deletelms" title="' . trans('admin/lmscourse.action_delete') . '"
                href="' . URL::to('cp/lmscoursemanagement/remove-batch/' . $value['id'] . '/' . $pid) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '">
                <i class="fa fa-trash-o"></i></a>';
            $edit = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/lmscourse.action_edit') . '"
                href="' . URL::to('cp/lmscoursemanagement/edit-batch/' . $value['id'] . '/' . $pid) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '">
                <i class="fa fa-edit"></i></a>';
            $temparr[] = $sortorder;
            $temparr[] = $edit . '' . $delete;
            $dataArr[] = $temparr;
        }

        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr,
        ];

        return response()->json($finaldata);
    }

    public function getRemoveBatch($id, $pid)
    {
        ManageLmsProgram::deletebatch($id, $pid);
        $redirect = '/cp/lmscoursemanagement/';
        $success = trans('admin/lmscourse.removebatch_success');
        return redirect($redirect)->with('success', $success);
    }

    public function getEditBatch($id, $pid)
    {
        $course = ManageLmsProgram::where('program_id', '=', (int)$pid)->value('program_title');
        $batch = ManageLmsProgram::getBatchUsingID($id, $pid);
        $batch = $batch[0]['variant'][0];
        $sort_order = ManageLmsProgram::getBatchMaxOrder($pid);
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/lmscourse.lmscourse') => 'lmscoursemanagement',
            trans('admin/lmscourse.edit_batch') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/lmscourse.edit_batch');
        $this->layout->pageicon = 'fa fa-group';
        $this->layout->pagedescription = trans('admin/lmscourse.edit_batch');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'lmscourse');
        $this->layout->content = view('admin.theme.lmsprogram.editbatch')
            ->with('id', $id)
            ->with('pid', $pid)
            ->with('sort_order', $sort_order)
            ->with('course', $course)
            ->with(['batch' => $batch]);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function postEditBatch($id, $pid)
    {

        $varianttype = 'batch';
        $rules = ManageAttribute::getRules($varianttype);
        $validation = Validator::make(Input::all(), $rules);
        $batch = ManageLmsProgram::checkBatchName($id, Input::all(), $pid);
        if ($validation->fails()) {
            return Redirect::back()->withInput()->withErrors($validation);
        } elseif ($batch > 0) {
            $error = trans('admin/lmscourse.uniquebatch_alert');
            return Redirect::back()->withInput()->with('batchname', $error);
        } else {
            $record = ManageLmsProgram::updateBatch(Input::all(), $id, $pid);
            $success = trans('admin/lmscourse.updatebatch_success');
            return redirect('cp/lmscoursemanagement/')->with('success', $success);
        }
    }

    public function getSortOrder($id, $curval, $nextval)
    {
        ManageLmsProgram::sortBanners($id, $curval, $nextval);
        return redirect('cp/lmscoursemanagement');
    }

    public function getBatchSortOrder($id, $curval, $nextval, $pid)
    {
        ManageLmsProgram::sortBatchList($id, $curval, $nextval, $pid);
        //return redirect('cp/lmscoursemanagement/manage-batch?pid='.$pid.'');
        return redirect('cp/lmscoursemanagement/');
    }

    public function getLmsprogramListAjaxNew()
    {
        $variant_type = 'batch';
        $setting = SiteSetting::module('Lmsprogram')->setting;
        $variants = ManageAttribute::getVariants($variant_type);

        $filter = 'ALL';
        $start = 0;
        $limit = 10;
        $orderByArray = ['sort_order' => 'asc'];
        $searchKey = '';

        $search = Input::get('search');
        if (isset($search['value'])) {
            $searchKey = $search['value'];
        }

        $totalRecords = ManageLmsProgram::getLmsprogramListCount('');
        $filteredRecords = ManageLmsProgram::getLmsprogramListCount($searchKey);
        
        $lmscourse = ManageLmsProgram::getFilteredLmsprogramsWithPagination($filter, $start, $limit, $orderByArray, $searchKey);
        $last_order = ManageLmsProgram::where('status', '!=', 'DELETED')->max('sort_order');
        $delete = $edit = '';
        $dataArr = [];

        foreach ($lmscourse as $value) {
            $nxtval = $value['sort_order'] + 1;
            $preval = $value['sort_order'] - 1;
            $variant = ManageLmsProgram::getvariantrelation($value['program_id']);
            $variantcount = ManageLmsProgram::getvariantcount($value['program_id']);
            if ($setting['more_batches'] == 'on' && !empty($variants)) {
                if ($value['status'] == 'active') {
                    $add = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/lmscourse.add_batch') . '"
href="' . URL::to('cp/lmscoursemanagement/add-batch/' . $value['program_id']) . '?start=' . $start . '&limit=' . $limit . '
&filter=' . $filter . '&search=' . $searchKey . '"><i class="fa fa-plus"></i></a>';
                } else {
                    $add = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/lmscourse.cant_assign_to_inactive_course_batch') . '"
><i class="fa fa-plus"></i></a>';
                }
            } else {
                $add = '';
            }

            $edit = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/lmscourse.action_edit') . '"
href="' . URL::to('cp/lmscoursemanagement/edit-lmsprogram/' . $value['program_id']) . '?start=' . $start . '&limit=' . $limit . '
&filter=' . $filter . '&search=' . $searchKey . '"><i class="fa fa-edit"></i></a>';


            $startdate = 'N/A';
            if ($value['program_startdate'] != null) {
                $startdate = Timezone::convertFromUTC('@' . $value['program_startdate'], Auth::user()->timezone, config('app.date_format'));
            }
            $enddate = 'N/A';
            if ($value['program_enddate'] != null) {
                $enddate = Timezone::convertFromUTC('@' . $value['program_enddate'], Auth::user()->timezone, config('app.date_format'));
            }
            if ($variantcount > 0) {
                $assign = '<a class="badge badge-success details-control">' . $variantcount . '</a>';
                $delete = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/lmscourse.cant_delete_since_batches_associated') . '" ><i class="fa fa-trash-o"></i></a>';
                $checkbox = '<input type="checkbox" value="' . $value['program_id'] . '" disabled="disabled">';
            } else {
                $assign = "<a class='badge badge-grey details-control'>0</a>";

                $delete = '<a class="btn btn-circle show-tooltip deletelmsprogram" title="' . trans('admin/lmscourse.action_delete') . '"
                href="' . URL::to('cp/lmscoursemanagement/remove-lmsprogram/' . $value['program_id']) . '?start=' . $start . '&limit=' . $limit . '
                &filter=' . $filter . '&search=' . $searchKey . '"><i class="fa fa-trash-o"></i></a>';

                $checkbox = '<input type="checkbox" value="' . $value['program_id'] . '">';
            }


            $sortorder = $value['sort_order'] . '&nbsp;&nbsp;&nbsp;';
            if ($value['sort_order'] == 1) {
                $sortorder .= '<a class="btn btn-circle show-tooltip order" id="orderdownonly" title="' . trans('admin/lmscourse.click_to_change_display_order') . '"  data-toggle="modal"
href="' . URL::to('cp/lmscoursemanagement/sort-order/' . $value['program_id'] . '/' . $value['sort_order'] . '/' . $nxtval) . ' " value="' . $value['sort_order'] . '" >
<i class="fa fa-caret-down"></i></a>';
            } elseif ($value['sort_order'] == $last_order) {
                $sortorder .= '<a class="btn btn-circle show-tooltip order" id="orderuponly" title="' . trans('admin/lmscourse.click_to_change_display_order') . '"  data-toggle="modal"
href="' . URL::to('cp/lmscoursemanagement/sort-order/' . $value['program_id'] . '/' . $value['sort_order'] . '/' . $preval) . '" value="' . $value['sort_order'] . '" >
<i class="fa fa-caret-up"></i></a>';
            } else {
                $sortorder .= '<a class="btn btn-circle show-tooltip order" id="orderuponly" title="' . trans('admin/lmscourse.click_to_change_display_order') . '"   data-toggle="modal"
href="' . URL::to('cp/lmscoursemanagement/sort-order/' . $value['program_id'] . '/' . $value['sort_order'] . '/' . $preval) . '"
value="' . $value['sort_order'] . '" ><i class="fa fa-caret-up"></i></a>';
                $sortorder .= '<a class="btn btn-circle show-tooltip order" id="orderdownonly" title="' . trans('admin/lmscourse.click_to_change_display_order') . '"   data-toggle="modal"
href="' . URL::to('cp/lmscoursemanagement/sort-order/' . $value['program_id'] . '/' . $value['sort_order'] . '/' . $nxtval) . '"
value="' . $value['sort_order'] . '" ><i class="fa fa-caret-down"></i></a>';
            }
            $batch = [];
            $bpid = $value['program_id'];
            $batchfiltereddata = ManageLmsProgram::getBatchListAjaxs($bpid);
            $batchlast_order = ManageLmsProgram:: getBatchMaxOrder($bpid);
            $i = 0;

            foreach ($batchfiltereddata as $batchkey => $batchvalue) {
                $bnxtval = $batchvalue['sort_order'] + 1;
                $bpreval = $batchvalue['sort_order'] - 1;
                $bsortorder = $batchvalue['sort_order'] . '&nbsp;&nbsp;&nbsp;';

                if ($batchvalue['sort_order'] == 1) {
                    $bsortorder .= '<a class="btn btn-circle show-tooltip order" id="orderdownonly" title="' . trans('admin/lmscourse.click_to_change_display_order') . '"  data-toggle="modal"
href="' . URL::to('cp/lmscoursemanagement/batch-sort-order/' . $batchvalue['id'] . '/' . $batchvalue['sort_order'] . '/' . $bnxtval . '/' . $bpid) . ' "
value="' . $batchvalue['sort_order'] . '" ><i class="fa fa-caret-down"></i></a>';
                } elseif ($batchvalue['sort_order'] == $batchlast_order) {
                    $bsortorder .= '<a class="btn btn-circle show-tooltip order" id="orderuponly" title="' . trans('admin/lmscourse.click_to_change_display_order') . '"  data-toggle="modal"
href="' . URL::to('cp/lmscoursemanagement/batch-sort-order/' . $batchvalue['id'] . '/' . $batchvalue['sort_order'] . '/' . $bpreval . '/' . $bpid) . '"
value="' . $batchvalue['sort_order'] . '" ><i class="fa fa-caret-up"></i></a>';
                } else {
                    $bsortorder .= '<a class="btn btn-circle show-tooltip order" id="orderuponly" title="' . trans('admin/lmscourse.click_to_change_display_order') . '"  data-toggle="modal"
href="' . URL::to('cp/lmscoursemanagement/batch-sort-order/' . $batchvalue['id'] . '/' . $batchvalue['sort_order'] . '/' . $bpreval . '/' . $bpid) . '"
value="' . $batchvalue['sort_order'] . '" ><i class="fa fa-caret-up"></i></a>';
                    $bsortorder .= '<a class="btn btn-circle show-tooltip order" id="orderdownonly" title="' . trans('admin/lmscourse.click_to_change_display_order') . '"  data-toggle="modal"
href="' . URL::to('cp/lmscoursemanagement/batch-sort-order/' . $batchvalue['id'] . '/' . $batchvalue['sort_order'] . '/' . $bnxtval . '/' . $bpid) . '"
value="' . $batchvalue['sort_order'] . '" ><i class="fa fa-caret-down"></i></a>';
                }
                $bedit = '';
                if (!empty($variants) && $setting['more_batches'] == 'on') {
                    $bedit = '<a class="btn btn-circle show-tooltip"  title="' . trans('admin/lmscourse.action_edit') . '"
    href="' . URL::to('cp/lmscoursemanagement/edit-batch/' . $batchvalue['id'] . '/' . $bpid) . '"><i class="fa fa-edit"></i></a>';
                }
                $batch_count = ManageLmsProgram::getBatchUsersCount($batchvalue['id'], $bpid);

                if (sizeof($batch_count) == 0) {
                    if ($value['status'] == 'active') {
                        $usercount = '<a class="categoryrels badge badge-success" href="' . URL::to('/cp/lmscoursemanagement/enrol-user?view=iframe
&pid=' . $value['program_id'] . '&bid=' . $batchvalue['id'] . '&cid=' . $batchvalue['lmscourseid'] . '') . '"
title="Assign Users" class="show-tooltip feedrel badge badge-grey" data-key="' . $batchvalue['batchname'] . '"
data-cid="' . $batchvalue['lmscourseid'] . '" data-bid="' . $batchvalue['id'] . '" data-pid="' . $bpid . '"
data-info="contentfeed" data-text="Assign User(s) to <b>' . htmlentities('"' . $batchvalue['batchname'] . '"', ENT_QUOTES) . '</b>"
data-json="">' . 0 . '</a>';
                    } else {
                        $usercount = '<a class="categoryrelss badge badge-grey"
        title="You cant assign users to inactive course batch" class="show-tooltip feedrel badge badge-grey">0</a>';
                    }
                    $bdelete = '<a class="btn btn-circle show-tooltip deletebatch" title="' . trans('admin/lmscourse.action_delete') . '"
                href="' . URL::to('cp/lmscoursemanagement/remove-batch/' . $batchvalue['id'] . '/' . $bpid) . '"><i class="fa fa-trash-o"></i></a>';
                } else {
                    if ($value['status'] == 'active') {
                        $usercount = '<a class="categoryrels badge badge-success" href="' . URL::to('/cp/lmscoursemanagement/enrol-user?view=iframe
&pid=' . $value['program_id'] . '&bid=' . $batchvalue['id'] . '&cid=' . $batchvalue['lmscourseid'] . '') . '"
title="Assign Users" class="show-tooltip feedrel badge badge-grey" data-key="' . $batchvalue['batchname'] . '"
data-cid="' . $batchvalue['lmscourseid'] . '" data-bid="' . $batchvalue['id'] . '" data-pid="' . $bpid . '"
data-info="contentfeed" data-text="Assign User(s) to <b>' . htmlentities('"' . $batchvalue['batchname'] . '"', ENT_QUOTES) . '</b>"
data-json="' . json_encode($batch_count) . '">' . sizeof($batch_count) . '</a>';
                    } else {
                        $usercount = '<a class="categoryrelss badge badge-grey"
        title="You cant assign users to inactive course batch" class="show-tooltip feedrel badge badge-grey"></a>';
                    }
                    $bdelete = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/lmscourse.cant_delete_since_user_associated') . '" ><i class="fa fa-trash-o"></i></a>';
                }

                $username = Auth::user()->username;
                $batch[$i]['batchname'] = '<a href="' . $setting['site_url'] . '/login/index.php?id=' . $batchvalue['lmscourseid'] . '&username=' . $username . '">' . $batchvalue['batchname'] . '</a>';

                $batch[$i]['startdate'] = $batchvalue['startdate'];
                $batch[$i]['enddate'] = $batchvalue['enddate'];
                $batch[$i]['sortorder'] = $bsortorder;
                $batch[$i]['batchusers'] = $usercount;
                $batch[$i]['actions'] = $bdelete . ' ' . $bedit;
                $i++;
            }
            $temparr = [
                'id' => $value['program_id'],
                'checkbox' => $checkbox,
                'coursename' => $value['program_title'],
                'startdate' => $startdate,
                'enddate' => $enddate,
                'displayorder' => $sortorder,
                'batches' => $assign,
                'status' => $value['status'],
                'actions' => $add . '' . $edit . '' . $delete,
                'count' => $variantcount,
                'batch' => $batch,
            ];
            array_push($dataArr, $temparr);
        }
       

        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr,
        ];

        return json_encode($finaldata);
    }


    public function getEnrolUser()
    {

        $relfilter = Input::get('relfilter', 'nonassigned');
        $from = Input::get('from', 'none');
        $relid = Input::get('relid', 'none');

        $viewmode = Input::get('view', 'iframe');
        $pid = Input::get('pid');
        $bid = Input::get('bid');
        $cid = Input::get('cid');
        if ($viewmode == 'iframe') {
            $this->layout->breadcrumbs = '';
            $this->layout->pagetitle = '';
            $this->layout->pageicon = '';
            $this->layout->pagedescription = '';
            $this->layout->header = '';
            $this->layout->sidebar = '';
            $this->layout->footer = '';
            $this->layout->content = view('admin.theme.lmsprogram.listenroluseriframe')
                ->with('cid', $cid)
                ->with('bid', $bid)
                ->with('relfilter', $relfilter)
                ->with('pid', $pid);
        }
    }

    public function getUserListAjax()
    {
        $cid = Input::get('cid');
        $bid = Input::get('bid');
        $pid = Input::get('pid');
        $relfilter = Input::get('relfilter');

        $start = 0;
        $limit = 10;
        $viewmode = Input::get('view', 'desktop');
        $search = Input::get('search');
        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '1') {
                $orderByArray = ['username' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '2') {
                $orderByArray = ['firstname' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '3') {
                $orderByArray = ['email' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '4') {
                $orderByArray = ['created_at' => $order_by[0]['dir']];
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


        $relfilter = Input::get('relfilter', 'nonassigned');
        $from = Input::get('from', 'lmscourse');

        if ($viewmode == 'iframe' && in_array($relfilter, ['assigned', 'nonassigned'])) {
            $relinfo = [$from => $cid];

            $totalRecords = User::getLmsUsersCount('', '', $relinfo);
            $filteredRecords = User::getLmsUsersCount($relfilter, $searchKey, $relinfo);
            $filtereddata = User::getLmsUsersWithPagination(
                $relfilter,
                $start,
                $limit,
                $orderByArray,
                $searchKey,
                $relinfo
            );
        }
        $totalRecords = $filteredRecords;
        $dataArr = [];
        foreach ($filtereddata as $key => $value) {
            $checkbox = '<input type="checkbox" value="' . $value['uid'] . '">';
            $temparr = [
                $checkbox,
                $value['username'],
                $value['firstname'] . ' ' . $value['lastname'],
                $value['email'],
                Timezone::convertFromUTC('@' . $value['created_at'], Auth::user()->timezone, config('app.date_format')),
                $value['status'],
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

    public function postAssignFeed($action = null, $key = null, $cid, $bid, $pid)
    {
        $arrname = 'active_user_feed_rel';
        $ids = Input::get('ids');
        $empty = Input::get('empty');
        if ($ids) {
            $ids = explode(',', $ids);
        } else {
            $ids = [];
        }

        $lmsprogram = ManageLmsProgram::where('program_id', '=', (int)$pid)->where('variant.id', '=', (int)$bid)->get(['variant.$.id'])->toArray();
        if (isset($lmsprogram[0]['variant'][0]['active_user_feed_rel'])) {
            $deleted = array_diff($lmsprogram[0]['variant'][0]['active_user_feed_rel'], $ids);
            if (!empty($deleted)) {
                foreach ($deleted as $value1) {
                    User::removeLmsUserRelation($value1, ['lms_course_rel'], (int)$cid);
                }
            }
        }
        $ids = array_values($ids);
        $deleted = array_values($deleted);
        foreach ($ids as &$value) {
            $value = (int)$value;
            $now = time();
            User::addLmsUserRelation($value, ['lms_course_rel'], $cid);
        }
        ManageLmsProgram::updateFeedRelation($pid, $arrname, $ids, $bid, $cid);
        ManageLmsProgram::enrolUser($cid, $bid, $pid); //moodle enrollments here
        return response()->json(['flag' => 'success']);
    }
}
