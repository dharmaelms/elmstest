<?php
namespace App\Http\Controllers\Admin\Courses;

use App\Http\Controllers\AdminBaseController;
use App\Model\Common;
use App\Model\Program;
use App\Model\SiteSetting;
use App\Services\Courses\Popular\PopularService;
use App\Services\Courses\Upcoming\UpcomingService;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\HomePage\HomePagePermission;
use Auth;
use Input;
use Request;
use Timezone;
use URL;

class UpcomingCoursesController extends AdminBaseController
{
    protected $layout = 'admin.theme.layout.master_layout';
    protected $upcomingSer;
    protected $popularSer;

    public function __construct(Request $request, UpcomingService $Upcoming, PopularService $Popular)
    {
        $this->theme_path = 'admin.theme';
        $this->upcomingSer = $Upcoming;
        $this->popularSer = $Popular;
    }

    public function getIndex()
    {
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::LIST_UPCOMING_COURSES)) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/courses.manage_upcoming_courses') => 'upcomingcourses',
            trans('admin/courses.list_upcoming_courses') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/courses.manage_upcoming_courses');
        $this->layout->pageicon = 'fa fa-book';
        $this->layout->pagedescription = trans('admin/courses.manage_upcoming_courses');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'homepage')
            ->with('submenu', 'upcomingcourses');
        $sitesetting = $homepage_setting = SiteSetting::module('Homepage');
        $this->layout->content = view('admin.theme.courses.listupcomingcourses')->with('UpcomingCourses', $sitesetting['setting']['UpcomingCourses']);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getUpcomingCoursesAjax()
    {
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::LIST_UPCOMING_COURSES)) {
            return parent::getAdminError($this->theme_path);
        }

        $start = 0;
        $limit = 10;

        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }

        $totalRecords = $this->upcomingSer->getUpcomingCount();
        $filteredRecords = $this->upcomingSer->getUpcomingCount();
        $filtereddata = $this->upcomingSer->getUpcomingCourses($start, $limit);

        $dataArr = [];
        $SiteSetting = SiteSetting::module('Homepage');
        $UpcomingCourses = $SiteSetting['setting']['UpcomingCourses'];

        foreach ($filtereddata as $key => $value) {
            if (!isset($value['parent_id']) || $value['parent_id'] == 0) {
                $program_title = Program::getCFTitle($value['program_slug']);

                if ($value['program_type'] == 'content_feed') {
                    if ($value['program_sub_type'] == 'collection') {
                        $program_type = 'Package';
                    } else {
                        $program_type = 'Channel';
                    }
                } elseif ($value['program_type'] == 'course') {
                    $program_type = 'Course';
                } else {
                    $program_type = 'Product';
                }

                if ($UpcomingCourses['configuration'] == 'automated') {
                    $delete = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/courses.automated_delete') . '" ><i class="fa fa-trash-o"></i></a>';
                } else {
                    if (has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::DELETE_UPCOMING_COURSES)) {
                        $delete = '<a class="btn btn-circle show-tooltip deletecourse" title="' . trans('admin/courses.action_delete') . '" href="' . URL::to('cp/upcomingcourses/delete-course/' . $value['program_id']) . '" ><i class="fa fa-trash-o"></i></a>';
                    } else {
                        $delete = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/courses.permission_delete') . '" ><i class="fa fa-trash-o"></i></a>';
                    }
                }
                $temparr = [
                    "<div>" . $program_title . "</div>",
                    $program_type,
                    Timezone::convertFromUTC('@' . $value['program_display_startdate'], Auth::user()->timezone, config('app.date_format')),
                    Timezone::convertFromUTC('@' . $value['program_display_enddate'], Auth::user()->timezone, config('app.date_format')),
                    $delete,
                ];

                $dataArr[] = $temparr;
            }
        }
        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr,
        ];

        return response()->json($finaldata);
    }

    public function getCourseIframe()
    {
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::ADD_UPCOMING_COURSES) && !has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::ADD_POPULAR_COURSES)) {
            return parent::getAdminError($this->theme_path);
        }

        $from = Input::get('from', 'none');
        $this->layout->breadcrumbs = '';
        $this->layout->pagetitle = '';
        $this->layout->pageicon = '';
        $this->layout->pagedescription = '';
        $this->layout->header = '';
        $this->layout->sidebar = '';
        $this->layout->content = view('admin.theme.courses.listcoursesiframe')->with('from', $from);
        $this->layout->footer = '';
    }

    public function getCourseIframeAjax()
    {
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::ADD_UPCOMING_COURSES) && !has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::ADD_POPULAR_COURSES)) {
            return parent::getAdminError($this->theme_path);
        }

        $start = 0;
        $limit = 10;
        $viewmode = Input::get('view', 'desktop');
        $from = Input::get('from', 'none');

        if ($viewmode != 'iframe') {
            $finaldata = [
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ];

            return response()->json($finaldata);
        } else {
            $search = Input::get('search');
            $searchKey = '';
            $order_by = Input::get('order');
            $orderByArray = ['created_at' => 'desc'];

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
            if (isset($search['value'])) {
                $searchKey = $search['value'];
            }

            if (preg_match('/^[0-9]+$/', Input::get('start'))) {
                $start = Input::get('start');
            }
            if (preg_match('/^[0-9]+$/', Input::get('length'))) {
                $limit = Input::get('length');
            }

            $type = Input::get('course_type', 'channel');

            if ($viewmode == 'iframe') {
                if ($from == 'upcoming') {
                    $program_ids = $this->upcomingSer->getInsertedCourses();
                } elseif ($from == 'popular') {
                    $program_ids = $this->popularSer->getInsertedCourses();
                } else {
                    $finaldata = [
                        'recordsTotal' => 0,
                        'recordsFiltered' => 0,
                        'data' => [],
                    ];
                    return response()->json($finaldata);
                }
                $totalRecords = $this->upcomingSer->getCourseCount($program_ids, $type, $searchKey);
                $filteredRecords = $this->upcomingSer->getCourseCount($program_ids, $type, $searchKey);
                $filtereddata = $this->upcomingSer->getCourseWithTypeAndPagination($program_ids, $type, $start, $limit, $orderByArray, $searchKey);
            }

            $dataArr = [];

            foreach ($filtereddata as $key => $value) {
                $program_title = $value['program_title'];
                if (isset($value['program_shortname']) && !empty($value['program_shortname'])) {
                    $program_shortname = $value['program_shortname'];
                } else {
                    $program_shortname = 'NA';
                }

                $temparr = [
                    '<input type="checkbox" value="' . $value['program_id'] . '">',
                    $program_title,
                    $program_shortname,
                    Timezone::convertFromUTC('@' . $value['program_startdate'], Auth::user()->timezone, config('app.date_format')),
                    Timezone::convertFromUTC('@' . $value['program_enddate'], Auth::user()->timezone, config('app.date_format')),
                    ucfirst(strtolower($value['status'])),
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
    }

    public function getAddProgram($action = null)
    {
        $ids = Input::get('ids');
        if ($ids) {
            $ids = explode(',', $ids);
        } else {
            $ids = [];
        }

        if ($action == 'upcoming') {
            if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::ADD_UPCOMING_COURSES)) {
                return parent::getAdminError($this->theme_path);
            }
            $records_added = $this->upcomingSer->getUpcomingCount();
            $SiteSetting = SiteSetting::module('Homepage');
            $UpcomingCourses = $SiteSetting['setting']['UpcomingCourses'];
            if (count($ids) > ((int)$UpcomingCourses['records_per_course'] - (int)$records_added)) {
                $msg = str_replace(':value', $UpcomingCourses['records_per_course'], trans('admin/courses.overflow_error'));
                return response()->json(['flag' => 'overflow', 'message' => $msg]);
            } else {
                $result = $this->upcomingSer->getInsertCourses($ids);
            }
        } elseif ($action == 'popular') {
            if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::ADD_POPULAR_COURSES)) {
                return parent::getAdminError($this->theme_path);
            }
            $records_added = $this->popularSer->getPopularCount();
            $SiteSetting = SiteSetting::module('Homepage');
            $PopularCourses = $SiteSetting['setting']['PopularCourses'];
            if (count($ids) > ((int)$PopularCourses['records_per_course'] - (int)$records_added)) {
                $msg = str_replace(':value', $PopularCourses['records_per_course'], trans('admin/courses.overflow_error'));
                return response()->json(['flag' => 'overflow', 'message' => $msg]);
            } else {
                $result = $this->popularSer->getInsertCourses($ids);
            }
        } else {
            $msg = trans('admin/user.missing_params');
            return response()->json(['flag' => 'error', 'message' => $msg]);
        }

        if ($result) {
            return response()->json(['flag' => 'success']);
        } else {
            return response()->json(['flag' => 'error']);
        }
    }

    public function getDeleteCourse($program_id)
    {
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::DELETE_UPCOMING_COURSES)) {
            return parent::getAdminError($this->theme_path);
        }

        $result = $this->upcomingSer->getDeleteCourse($program_id);
        if ($result) {
            $success = trans('admin/courses.delete_success');
            return redirect('cp/upcomingcourses')->with('success', $success);
        } else {
            $error = trans('admin/courses.delete_error');
            return redirect('cp/upcomingcourses')->with('error', $success);
        }
    }
}
