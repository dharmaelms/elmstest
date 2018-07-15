<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminBaseController;
use App\Model\Common;
use App\Model\SiteSetting;
use App\Services\Package\IPackageService;
use App\Services\Program\IProgramService;
use App\Services\Report\IExportReportService;
use App\Services\User\IUserService;
use App\Services\UserGroup\IUserGroupService;
use Auth;
use Carbon;
use Exception;
use Input;
use Log;
use Timezone;

class ExportReportController extends AdminBaseController
{
    protected $layout = 'admin.theme.layout.master_layout';

    private $export_service;

    private $program_service;

    private $user_service;

    private $ug_service;

    private $package_service;

    const QUERY = 'query';

    const START_DATE = 'start_date';

    const END_DATE = 'end_date';

    const IS_UG_REPORT = 'is_ug_report';

    const FALSE = 'false';

    const SEARCH = 'search';

    const APP_LIMIT_ITEMS_DROP_DOWN = 'app.limit_items_dropdown';

    const APP_CHAR_LIMIT_DROP_DOWN = 'app.char_limit_dropdown';

    const ADMIN_REPORTS_ERROR_WHILE_EXPORT = 'admin/reports.error_while_export';

    const CP_EXPORT_REPORTS = 'cp/exportreports';

    const ERROR = 'error';

    const ALL_TIME = 'all_time';

    const ALL_TIME_LABLE = 'admin/reports.all_time';

    const RESULT = 'result';

    const ADMIN_REPORTS_NO_RECORD_FOUND_IN_THIS_COMBINATION = 'admin/reports.no_record_found_in_this_combi';

    const ADMIN_REPORTS_USER_ACT_COURSE = 'admin/reports.user_act_course';

    const ADMIN_REPORTS_REPORT_NAME = 'admin/reports.report_name';

    const COURSE = 'course';

    public function __construct(
        IExportReportService $export_service,
        IProgramService $program_service,
        IUserService $user_service,
        IUserGroupService $ug_service,
        IPackageService $package_service
    ) {
        $this->export_service = $export_service;
        $this->program_service = $program_service;
        $this->user_service = $user_service;
        $this->ug_service = $ug_service;
        $this->package_service = $package_service;
    }

    /**
     * getIndex Open the export reports view file
     * @return void
     */
    public function getIndex()
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/reports.admin_reports') => 'reports',
            trans('admin/reports.export_reports') => '',
        ];
        $start_date = Carbon::today()->subDays((int)config('app.default_date_range_selected'))->timestamp;
        $end_date = time();
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/reports.admin_reports');
        $this->layout->pageicon = 'fa fa-bar-chart-o';
        $this->layout->pagedescription = trans('admin/reports.manage_reports');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'report')
            ->with('submenu', 'exportreport');
        $this->layout->content = view('admin.theme.reports.exportreports')
            ->with('title', trans('admin/reports.channel_perf'))
            ->with(static::START_DATE, $start_date)
            ->with(static::END_DATE, $end_date);
    }

    /**
     * getUserActivityByCourse  User's activity of program as specific course/channel/packages's
     * channel in between specified date ranges,
     * Eg. completion status, last score, actual duration and certificate status
     * @param  int $channel_id selected channel id
     * @param  string $date_range Enrolled in between dates
     * @param  int $package_id selected package
     * @param  string $filter_by direct channel reports or package's channel
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse export report as csv file
     */
    public function getUserActivityByCourse($channel_id, $date_range, $package_id, $filter_by)
    {
        try {
            set_time_limit(300);
            $start_date = Input::get(static::START_DATE, '');
            $end_date = Input::get(static::END_DATE, '');
            if (!is_numeric($channel_id) || $channel_id <= 0 || $date_range == 'null' || $filter_by == 'null') {
                return redirect(static::CP_EXPORT_REPORTS)
                    ->with(static::ERROR, trans(static::ADMIN_REPORTS_ERROR_WHILE_EXPORT));
            }
            $date_timestamp = $date_range;
            if ($date_timestamp != static::ALL_TIME) {
                $date_timestamp = $this->validateDates($start_date, $end_date);
            }
            $result = $this->export_service->prepareUserActivityByCourse(
                $channel_id,
                $date_timestamp,
                $package_id,
                $filter_by,
                $date_range
            );
            if ($result == false) {
                return redirect(static::CP_EXPORT_REPORTS)
                    ->with(static::ERROR, trans(static::ADMIN_REPORTS_NO_RECORD_FOUND_IN_THIS_COMBINATION));
            }
            exit;
        } catch (Exception $e) {
            Log::error('Export-Report UserActivityByCourse :: ' . $e->getMessage());
            return redirect(static::CP_EXPORT_REPORTS)
                ->with(static::ERROR, trans(static::ADMIN_REPORTS_ERROR_WHILE_EXPORT));
        }
    }

    /**
     * getCourseActivityByUser Specific user's, specified dates enrolled course/channel activities.
     * Eg. completion status, last score, actual duration and enrollment date
     * @param  string $date_range Enrolled in between dates
     * @param  int $user_id selected user id
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse export report as csv file
     */
    public function getCourseActivityByUser($user_id, $date_range)
    {
        try {
            if ($user_id == 'null' || $user_id <= 0 || $date_range == 'null') {
                return redirect(static::CP_EXPORT_REPORTS)
                    ->with(static::ERROR, trans(static::ADMIN_REPORTS_ERROR_WHILE_EXPORT));
            }
            $start_date = Input::get(static::START_DATE, '');
            $end_date = Input::get(static::END_DATE, '');
            $date_timestamp = $date_range;
            if ($date_timestamp != static::ALL_TIME) {
                $date_timestamp = $this->validateDates($start_date, $end_date);
            }
            $result = $this->export_service->prepareCourseActivityByUser($user_id, $date_timestamp, $date_range);
            if ($result == false) {
                return redirect(static::CP_EXPORT_REPORTS)
                    ->with(static::ERROR, trans(static::ADMIN_REPORTS_NO_RECORD_FOUND_IN_THIS_COMBINATION));
            }
            exit;
        } catch (Exception $e) {
            Log::error('Export-Report CourseActivityByUser :: ' . $e->getMessage());
            return redirect(static::CP_EXPORT_REPORTS)
                ->with(static::ERROR, trans(static::ADMIN_REPORTS_ERROR_WHILE_EXPORT));
        }
    }

    /**
     * getCourseActivityByGroup Specific channel's and specified usergroup's each user's activities Eg. completion status, last score, actual duration and enrollment date
     * @param  int $group_id selected group id
     * @param  int $channel_id selected channel id
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse export report as csv file
     */
    public function getCourseActivityByGroup($group_id, $channel_id, $package_id, $filter_by)
    {
        try {
            if (!is_numeric($group_id)
                || $group_id <= 0
                || !is_numeric($channel_id)
                || $channel_id <= 0
                || $filter_by == 'null'
            ) {
                return redirect(static::CP_EXPORT_REPORTS)
                    ->with(static::ERROR, trans(static::ADMIN_REPORTS_ERROR_WHILE_EXPORT));
            }
            $report = $this->export_service->prepareCourseActivityByGroup($group_id, $channel_id, $package_id, $filter_by);
            if ($report == false) {
                return redirect(static::CP_EXPORT_REPORTS)
                    ->with(static::ERROR, trans(static::ADMIN_REPORTS_NO_RECORD_FOUND_IN_THIS_COMBINATION));
            }
            exit;
        } catch (Exception $e) {
            Log::error('Export-Report CourseActivityByGroup :: ' . $e->getMessage());
            return redirect(static::CP_EXPORT_REPORTS)
                ->with(static::ERROR, trans(static::ADMIN_REPORTS_ERROR_WHILE_EXPORT));
        }
    }

    /**
     * getGroupSummary  In specified date creatd usergroup's summary reports Eg.total users, active and inactive users and count of assigned programs
     * @param  string $create_date_range Created in between dates
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse export report as csv file
     */
    public function getGroupSummary($create_date_range)
    {
        try {
            if ($create_date_range == 'null') {
                return redirect(static::CP_EXPORT_REPORTS)
                    ->with(static::ERROR, trans(static::ADMIN_REPORTS_ERROR_WHILE_EXPORT));
            }
            $start_date = Input::get(static::START_DATE, '');
            $end_date = Input::get(static::END_DATE, '');
            $date_timestamp = $create_date_range;
            if ($date_timestamp != static::ALL_TIME) {
                $date_timestamp = $this->validateDates($start_date, $end_date);
            }
            $result = $this->export_service->prepareGroupSummary($date_timestamp, $create_date_range);
            if ($result == false) {
                return redirect(static::CP_EXPORT_REPORTS)
                    ->with(static::ERROR, trans(static::ADMIN_REPORTS_NO_RECORD_FOUND_IN_THIS_COMBINATION));
            }
            exit;
        } catch (Exception $e) {
            Log::error('Export-Report GroupSummary :: ' . $e->getMessage());
            return redirect(static::CP_EXPORT_REPORTS)
                ->with(static::ERROR, trans(static::ADMIN_REPORTS_ERROR_WHILE_EXPORT));
        }
    }

    /**
     * getGroupDetails Specified usergroup's users details Eg. user name and status
     * @param  int $group_id selected group id
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse export report as csv file
     */
    public function getGroupDetails($group_id)
    {
        try {
            if (!is_numeric($group_id) || $group_id <= 0) {
                return redirect(static::CP_EXPORT_REPORTS)
                    ->with(static::ERROR, trans(static::ADMIN_REPORTS_ERROR_WHILE_EXPORT));
            }
            $result = $this->export_service->prepareDetailedByGroup($group_id);
            if ($result == false) {
                return redirect(static::CP_EXPORT_REPORTS)
                    ->with(static::ERROR, trans(static::ADMIN_REPORTS_NO_RECORD_FOUND_IN_THIS_COMBINATION));
            }
            exit;
        } catch (Exception $e) {
            Log::error('Export-Report Group details :: ' . $e->getMessage());
            return redirect(static::CP_EXPORT_REPORTS)
                ->with(static::ERROR, trans(static::ADMIN_REPORTS_ERROR_WHILE_EXPORT));
        }
    }

    /**
     * getPostLevelCompletion Post level completion of selected channel's, In between selected dates activities of users
     * Eg. fullname, over all completion percentage, and each post completion percentage.
     * @param  int $channel_id selected channel id
     * @param  string $date_range Enrolled in between dates
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse export report as csv file
     */
    public function getPostLevelCompletion($channel_id, $date_range, $package_id, $filter_by)
    {
        try {
            if (!is_numeric($channel_id) || $channel_id <= 0 || $date_range == 'null' || $filter_by == 'null') {
                return redirect(static::CP_EXPORT_REPORTS)
                    ->with(static::ERROR, trans(static::ADMIN_REPORTS_ERROR_WHILE_EXPORT));
            }
            $start_date = Input::get(static::START_DATE, '');
            $end_date = Input::get(static::END_DATE, '');
            if ($date_range == static::ALL_TIME) {
                $date_timestamp = static::ALL_TIME;
            } else {
                $date_timestamp = $this->validateDates($start_date, $end_date);
            }

            $result = $this->export_service->preparePostLevelCompletion(
                $channel_id,
                $date_timestamp,
                $package_id,
                $filter_by,
                $date_range
            );
            
            if ($result == false) {
                return redirect(static::CP_EXPORT_REPORTS)
                    ->with(static::ERROR, trans(static::ADMIN_REPORTS_NO_RECORD_FOUND_IN_THIS_COMBINATION));
            }
            exit;
        } catch (Exception $e) {
            Log::error('Export-Report PostLevelCompletion :: ' . $e->getMessage());
            return redirect(static::CP_EXPORT_REPORTS)
                ->with(static::ERROR, trans(static::ADMIN_REPORTS_ERROR_WHILE_EXPORT));
        }
    }

    /**
     * getProgramsCompletion get users all completed|inprogress channel reports
     * @param  String $date_range
     * @param  String $report
     * @return CSV file
     */
    public function getProgramsCompletion($date_range, $report)
    {
        try {
            if ($date_range == 'null') {
                return redirect(static::CP_EXPORT_REPORTS)
                    ->with(static::ERROR, trans(static::ADMIN_REPORTS_ERROR_WHILE_EXPORT));
            }
            $start_date = Input::get(static::START_DATE, '');
            $end_date = Input::get(static::END_DATE, '');
            $date_timestamp = $date_range;
            if ($date_timestamp != static::ALL_TIME) {
                $date_timestamp = $this->validateDates($start_date, $end_date);
            }
            $result = $this->export_service->prepareProgramsCompletion(
                $date_timestamp,
                $date_range,
                ($report == 'course_completed')
            );
            if ($result == false) {
                return redirect(static::CP_EXPORT_REPORTS)
                    ->with(static::ERROR, trans(static::ADMIN_REPORTS_NO_RECORD_FOUND_IN_THIS_COMBINATION));
            }
            exit;
        } catch (Exception $e) {
            Log::error('Export-Report CourseActivityByUser :: ' . $e->getMessage());
            return redirect(static::CP_EXPORT_REPORTS)
                ->with(static::ERROR, trans(static::ADMIN_REPORTS_ERROR_WHILE_EXPORT));
        }
    }

    /**
     * postUsers get list of user as full name using search keys
     * @return \Illuminate\Http\JsonResponse List of the user associate with specified channel
     */
    public function postUsers()
    {
        $search_key = Input::get(static::SEARCH, '');
        $user_details = $this->user_service->getAllUsersBySearchKey(
            $search_key,
            0,
            config(static::APP_LIMIT_ITEMS_DROP_DOWN)
        );
        $data = [];
        foreach ($user_details as $user_detail) {
            $data[] = [
                "id" => $user_detail->uid,
                'text' => str_limit(
                    $user_detail->username.' ('.$user_detail->fullname.')',
                    (int)config(static::APP_CHAR_LIMIT_DROP_DOWN)
                )
            ];
        }

        return response()->json($data);
    }

    /**
     * postUsergroups get list of usergroup  using search keys / selected channel
     * @return \Illuminate\Http\JsonResponse List of the usergroup associate with specified channel
     */
    public function postUsergroups()
    {
        $search_key = Input::get(static::SEARCH, '');
        $channel_id = Input::get('channel_id', 0);
        $report = Input::get(static::IS_UG_REPORT, static::FALSE);
        $package_id = Input::get('package_id');
        $is_package = Input::get('is_package');
        $ug_ids = [];
        if ($report == 'true') {
            $ug_details = $this->ug_service->getUsergroupIdName(
                'ALL',
                $search_key,
                0,
                config(static::APP_LIMIT_ITEMS_DROP_DOWN)
            );
        } else {
            if ($is_package == 'true' && !is_null($package_id)) {
                $package_details = $this->package_service->getPackages(
                    ["in_ids" => [(int)$package_id]],
                    ['user_group_ids']
                )->first();
                $ug_ids = array_get(
                    $package_details->toArray(),
                    'user_group_ids',
                    []
                );
            } else {
                $program_details = $this->program_service->getProgramById((int)$channel_id);
                $ug_ids = array_get(
                    $program_details->toArray(),
                    'relations.active_usergroup_feed_rel',
                    []
                );
            }
            if (!empty($ug_ids)) {
                $ug_details = $this->ug_service->getUsergroupIdName(
                    $ug_ids,
                    $search_key,
                    0,
                    config(static::APP_LIMIT_ITEMS_DROP_DOWN)
                );
            } else {
                $ug_details = [];
            }
        }
        $data = [];
        foreach ($ug_details as $ug_detail) {
            $data[] = [
                "id" => $ug_detail->ugid,
                'text' => str_limit(trim(html_entity_decode($ug_detail->usergroup_name)), (int)config(static::APP_CHAR_LIMIT_DROP_DOWN))
            ];
        }
        return response()->json($data);
    }

    /**
     * postChannels get the list of channels using search keys
     * @return \Illuminate\Http\JsonResponse Searched channel list
     */
    public function postChannels()
    {
        $search_key = Input::get(static::QUERY, '');
        $search_key = array_get($search_key, 'term', '');
        $is_ug_channel_report = Input::get('is_ug_channel_report', static::FALSE);
        $programs = $this->program_service->getProgramsBySearch(
            $search_key,
            $is_ug_channel_report == 'course_act_ug',
            0,
            config(static::APP_LIMIT_ITEMS_DROP_DOWN)
        );
        $data = [];
        foreach ($programs as $program) {
            if (isset($program->program_shortname) && $program->program_shortname != '') {
                $text = str_limit($program->title, (int)config('app.char_limit_dropdown')).' (' .str_limit($program->shortname, (int)config('app.char_limit_dropdown')). ')';
            } else {
                $text = str_limit($program->title, (int)config('app.char_limit_dropdown'));
            }
            $data[] = [
                "id" => $program->program_id,
                "text" => $text
            ];
        }
        return response()->json($data);
    }

    /**
     * postPackages get the list of packages using search keys
     * @return \Illuminate\Http\JsonResponse Searched package lists
     */
    public function postPackages()
    {
        $search_key = Input::get(static::QUERY, '');
        $search_key = array_get($search_key, 'term', '');
        $is_ug_channel_report = Input::get('is_ug_channel_report', static::FALSE);
        $packages = $this->package_service->getPackagesBySearch(
            $search_key,
            $is_ug_channel_report == 'course_act_ug',
            0,
            config(static::APP_LIMIT_ITEMS_DROP_DOWN)
        );
        $data = [];
        foreach ($packages as $package) {
            if (isset($package->program_shortname) && $package->program_shortname != '') {
                $text = str_limit($package->package_title, (int)config('app.char_limit_dropdown')).
                ' ('.str_limit($package->package_shortname, (int)config('app.char_limit_dropdown')).')';
            } else {
                $text = str_limit($package->package_title, (int)config('app.char_limit_dropdown'));
            }
            $data[] = [
                "id" => $package->package_id,
                "text" => $text
            ];
        }
        return response()->json($data);
    }

    /**
     * postPackageChannels get the list of channels using search keys and associated with selected packages
     * @return \Illuminate\Http\JsonResponse Search channel lists associate with specified package
     */
    public function postPackageChannels()
    {
        $search_key = Input::get(static::SEARCH, '');
        $package_id = Input::get('package_id', '');
        $programs = $this->program_service->getPackageProgramsBySearch(
            $search_key,
            $package_id,
            0,
            config(static::APP_LIMIT_ITEMS_DROP_DOWN)
        );
        $data = [];
        foreach ($programs as $program) {
            if (isset($program->program_shortname) && $program->program_shortname != '') {
                $text = str_limit($program->title, (int)config('app.char_limit_dropdown')).' (' . str_limit($program->shortname, (int)config('app.char_limit_dropdown')).' )';
            } else {
                $text = str_limit($program->title, (int)config('app.char_limit_dropdown'));
            }
            $data[] = [
                "id" => $program->program_id,
                "text" => $text
            ];
        }
        return response()->json($data);
    }

    /**
     * postListCourses get the list of courses using search keys
     * @return \Illuminate\Http\JsonResponse Searched list of courses
     */
    public function postListCourses()
    {
        $search_key = Input::get(static::QUERY, '');
        $search_key = array_get($search_key, 'term', '');
        $programs = $this->program_service->getCoursesBySearchKey(
            $search_key,
            0,
            0,
            config(static::APP_LIMIT_ITEMS_DROP_DOWN)
        );
        $data = [];
        foreach ($programs as $program) {
            if (isset($program->program_shortname) && $program->program_shortname != '') {
                $text = str_limit($program->title, (int)config('app.char_limit_dropdown')).' ('.str_limit($program->shortname, (int)config('app.char_limit_dropdown')).')';
            } else {
                $text = str_limit($program->title, (int)config('app.char_limit_dropdown'));
            }
            $data[] = [
                "id" => $program->program_id,
                "text" => $text
            ];
        }
        return response()->json($data);
    }

    /**
     * postListCourseBatch get the list of batches using search keys and associated with selected course
     * @return \Illuminate\Http\JsonResponse description
     */
    public function postListCourseBatch()
    {
        $search_key = Input::get(static::SEARCH, '');
        $course_id = Input::get('course_id', '');
        $data = [];
        if ($course_id != '' && (int)$course_id > 0) {
            $programs = $this->program_service->getCoursesBySearchKey(
                $search_key,
                (int)$course_id,
                0,
                config(static::APP_LIMIT_ITEMS_DROP_DOWN)
            );
            foreach ($programs as $program) {
                $data[] = [
                    "id" => $program->program_id,
                    "text" => str_limit($program->title, (int)config(static::APP_CHAR_LIMIT_DROP_DOWN))
                ];
            }
        }
        return response()->json($data);
    }

    /**
     * validateDates convert date string to timestamp and validate with threshold date limits
     * @param  string $start_date date string
     * @param  string $end_date date string
     * @return array Array of start and end date as timestamp
     */
    private function validateDates($start_date, $end_date)
    {
        $default_start_date = Carbon::today()->subDays((int)config('app.default_date_range_selected'))->getTimestamp();
        $default_end_date = time();
        if (!is_null($start_date) && $start_date != '' && !is_null($end_date) && $end_date != '') {
            $start_date_temp = (int)Timezone::convertToUTC($start_date, Auth::user()->timezone, "U");
            $end_date_temp = (int)Timezone::convertToUTC($end_date, Auth::user()->timezone, "U");
            $start_obj = Carbon::createFromTimestampUTC($start_date_temp);
            $end_obj = Carbon::createFromTimestampUTC($end_date_temp);
            $diff_days = $start_obj->diffInDays($end_obj);
            if ($diff_days <= (int)config('app.max_date_range_selected')) {
                $default_start_date = $start_date_temp;
                //add end of the day
                $default_end_date = $end_date_temp + (24 * 60 * 60);
            }
        }
        return [static::START_DATE => $default_start_date, static::END_DATE => $default_end_date];
    }
}
