<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Assessment\AssessmentPermission;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\Announcement\AnnouncementPermission;
use App\Enums\DAMS\DAMSPermission;
use App\Enums\Program\ElementType;
use App\Enums\Report\ReportPermission;
use App\Enums\RolesAndPermissions\PermissionType;
use App\Enums\RolesAndPermissions\Contexts;
use App\Http\Controllers\AdminBaseController;
use App\Model\ChannelAnalytic\Repository\IOverAllChannalAnalyticRepository;
use App\Model\Common;
use App\Model\CronLog;
use App\Model\SiteSetting;
use App\Services\DimensionChannel\IDimensionChannelService;
use App\Services\Report\IDimensionUserService;
use App\Services\Report\IDimensionAnnouncementsService;
use App\Services\Report\ITillContentReportService;
use App\Services\ScormActivity\IScormActivityService;
use App\Services\User\IUserService;
use Auth;
use Carbon;
use DB;
use Input;
use Request;
use Timezone;
use URL;

class ReportController extends AdminBaseController
{
    protected $layout = 'admin.theme.layout.master_layout';
    
    const FILE_NAME_CHAR_LIMIT  = 20;
    const START_DATE = 'start_date';
    const END_DATE = 'end_date';
    const MAIN_MENU = 'mainmenu';
    const REPORT = 'report';
    const SUB_MENU = 'submenu';
    const TITLE = 'title';
    const CONTENT_FEEDS = 'contentfeeds';
    const CONTENET__FEEDS = 'content_feeds';
    const ADMIN_REPORT = 'adminreport';
    const CP = 'cp';
    const REPORTS = 'reports';
    const DATA = 'data';
    const XAXIS = 'xaxis';
    const ID = 'id';
    const QUIZ_COUNT_ROW = 'quiz_count_row';
    const QUIZ_COUNT = 'quiz_count';
    const AVG = 'avg';
    const RANGE = 'range';
    const _TO_ = ' to ';
    const DATE_RANGE = 'date_range';
    const CANNEL_ID = 'channel_id';
    const NAME = 'name';
    const QUIZ_NAME = 'quiz_name';
    const QUIZ_LIST = 'quiz_list';
    const QUIZ_ID = 'quiz_id';
    const TOTAL_MARK = 'total_mark';
    const QUIZ_MAX_TIME = 'quiz_max_time';
    const QUIZ_IDS = 'ques_ids';
    const QUIZ_TITLE = 'ques_title';
    const BAR_CHART_FONT = 'fa fa-bar-chart-o';
    const SEARCH = 'search';
    const ORDER = 'order';
    const CREATED_AT = 'created_at';
    const DESC = 'desc';
    const COLUMN = 'column';
    const TIME_TAKEN = 'time_taken';
    const VALUE = 'value';
    const START = 'start';
    const LENGTH = 'length';
    const STATUS_FILTER = 'status_filter';
    const DIR = 'dir';
    const USER_NAME = 'user_name';
    const SCORE = 'score';
    const POST_ROW_COUNT = 'post_row_count';
    const USER_REPORT = 'userreport';
    const LIST_OF_USERS = 'List Of Users';
    const FROM = 'from';
    const TO = 'to';
    const COMPLETION = 'completion';
    const PERFORMANCE = 'performance';
    const FILTEREDDATA = 'filteredData';
    const TOTAL_COUNT = 'total_count';
    const RECORDS_TOTAL = 'recordsTotal';
    const RECORDS_FILTERED = 'recordsFiltered';
    const USER_ID = 'user_id';
    const AVG_DATA = 'avg_data';
    const LABELS = 'labels';
    const IDS = 'ids';
    const AVG_CH = 'avg_ch';
    const AVG_AVG = 'avg_avg';
    const VALUES = 'values';
    const VALUES_AVG = 'values_avg';
    const ID_LIST = 'id_list';
    const INTVAL = 'intval';
    const ANNOUNCEMENT_TITLE = 'announcement_title';
    const CREATE_DATE = 'create_date';
    const MODULE_NAME = 'module_name';
    const STATUS = 'status';
    const UPDATE_DATE = 'update_date';
    const END = 'end';
    const TOTAL_TIME = 'total_time';
    const QUES_TEXT = 'ques_text';
    const QUES_LIST = 'ques_list';

    /**
     * @var IOverAllChannalAnalyticRepository
     */
    private $chnl_analytic_repo;
    private $dim_channel_service;
    private $dim_user_service;
    private $dim_announce_service;
    private $scorm_activity_service;
    private $till_content_service;
    private $user_service;
    public function __construct(
        Request $request,
        IDimensionChannelService $dim_channel_service,
        IDimensionUserService $dim_user_service,
        IDimensionAnnouncementsService $dim_announce_service,
        IOverAllChannalAnalyticRepository $chnl_analytic_repo,
        IScormActivityService $scorm_activity_service,
        ITillContentReportService $till_content_service,
        IUserService $user_service
    ) {
        parent::__construct();
        $input = $request::input();
        array_walk($input, function ($i) {
            (is_string($i)) ? $i = strip_tags($i) : '';
        });
        $request::merge($input);
        $this->theme_path = 'admin.theme';
        $this->chnl_analytic_repo = $chnl_analytic_repo;
        $this->dim_channel_service = $dim_channel_service;
        $this->dim_user_service = $dim_user_service;
        $this->dim_announce_service = $dim_announce_service;
        $this->scorm_activity_service = $scorm_activity_service;
        $this->till_content_service = $till_content_service;
        $this->user_service = $user_service;
    }

    /**
     * getIndex
     * @return void
     */
    public function getIndex()
    {
        $this->getAdminReports();
    }

    /**
     * getAdminReports
     * @return blade/view
     */
    public function getAdminReports()
    {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::VIEW_REPORT)) {
            return parent::getAdminError($this->theme_path);
        }
        $content_feeds = $this->permittedChannelLists(false);
        $general = SiteSetting::module('General');
        $crumbs = [
            trans('admin/dashboard.dashboard') => static::CP,
            trans('admin/reports.admin_reports') => static::REPORTS,
            trans('admin/reports.channel_performance_report') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $title = trans('admin/reports.channel_perf');
        $this->layout->pagetitle = trans('admin/reports.admin_reports');
        $this->layout->pageicon = static::BAR_CHART_FONT;
        $this->layout->pagedescription = trans('admin/reports.manage_reports');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with(static::MAIN_MENU, static::REPORT)
            ->with(static::SUB_MENU, static::ADMIN_REPORT);
        $this->layout->content = view('admin.theme.reports.channelperformance')
            ->with(static::TITLE, $title)
            ->with(static::CONTENT_FEEDS, $content_feeds)
            ->with('general', $general);
    }

    /**
     * getAjaxChannelPerformance
     * @param  integer $page
     * @return json
     */
    public function getAjaxChannelPerformance($page = 0)
    {
        $channel_ids = $this->permittedChannelLists(true);
        $limit = config('app.limit_bars_chart');
        $start = (int)$page * $limit;
        if ($start > 1) {
            $start = $start - 1;
        }
        $result = $this->till_content_service->prepareChannelPerformance($channel_ids, $start, $limit);
        $finalOutput = [
            static::TITLE => trans('admin/reports.channel_perf'),
            static::DATA => array_get($result, static::DATA, []),
            static::XAXIS => array_get($result, static::XAXIS, []),
            static::ID => array_get($result, static::ID, []),
            static::QUIZ_COUNT_ROW => array_get($result, static::QUIZ_COUNT, []),
            static::AVG => 0
        ];
        return response()->json($finalOutput);
    }

    /**
     * getIndividualChannelPerformance
     * @param  integer $channel_id
     * @param  string  $name
     * @return blade/view
     */
    public function getIndividualChannelPerformance($channel_id = 0, $name = '')
    {
        $general = SiteSetting::module('General');
        $channel_id = (int)$channel_id;
        if ($channel_id <= 0) {
            return parent::getAdminError($this->theme_path);
        }
        $admin_id = Auth::user()->uid;
        if (!$this->roleService->hasPermission(
            $admin_id,
            ModuleEnum::REPORT,
            PermissionType::ADMIN,
            ReportPermission::VIEW_REPORT,
            Contexts::PROGRAM,
            (int)$channel_id,
            true
        )) {
            return parent::getAdminError($this->theme_path);
        }
        $content_feeds = $this->permittedChannelLists(false);
        $crumbs = [
            trans('admin/dashboard.dashboard') => static::CP,
            trans('admin/reports.admin_reports') => static::REPORTS,
            trans('admin/reports.indiv_channel_perf_report') => '',
        ];
        if ($name == '') {
             $name = $this->till_content_service->getChannelNameById($channel_id, true);
        }
        $title = trans('admin/reports.indiv_channel_perf_report');
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/reports.admin_reports');
        $this->layout->pageicon = static::BAR_CHART_FONT;
        $this->layout->pagedescription = trans('admin/reports.manage_reports');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with(static::MAIN_MENU, static::REPORT)
            ->with(static::SUB_MENU, static::ADMIN_REPORT);
        $this->layout->content = view('admin.theme.reports.individualchannelperformance')
            ->with(static::TITLE, $title)
            ->with(static::CANNEL_ID, $channel_id)
            ->with(static::NAME, $name)
            ->with(static::CONTENT_FEEDS, $content_feeds)
            ->with('general', $general);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * getAjaxIndividualChannelPerformance
     * @param  integer $page
     * @param  integer $channel_id
     * @return json
     */
    public function getAjaxIndividualChannelPerformance($page = 0, $channel_id = 0)
    {
        $admin_id = Auth::user()->uid;
        if (!$this->roleService->hasPermission(
            $admin_id,
            ModuleEnum::REPORT,
            PermissionType::ADMIN,
            ReportPermission::VIEW_REPORT,
            Contexts::PROGRAM,
            (int)$channel_id,
            true
        )) {
            return response()->json([]);
        }
        $channel_id = (int)$channel_id;
        $result = [];
        if ($channel_id >= 1) {
            $limit = config('app.limit_bars_chart');
            $start = (int)$page * $limit;
            if ($start > 0) {
                $start = $start - 1;
            }
            $result = $this->till_content_service->prepareIndividualChannelPerformance((int)$channel_id, $start, $limit);
        }
        $finalOutput = [
            static::TITLE => trans('admin/reports.indiv_channel_perf'),
            static::DATA => array_get($result, static::DATA, []),
            static::XAXIS => array_get($result, static::XAXIS, []),
            static::ID => array_get($result, static::ID, []),
            static::AVG => 0,
        ];
        return response()->json($finalOutput);
    }

    /**
     * getQuizPerformanceByQuestion
     * @param  integer $quiz_id
     * @param  integer $channel_id
     * @return blade/view
     */
    public function getQuizPerformanceByQuestion($quiz_id = 0, $channel_id = 0)
    {
        $admin_id = Auth::user()->uid;
        if (!$this->roleService->hasPermission(
            $admin_id,
            ModuleEnum::REPORT,
            PermissionType::ADMIN,
            ReportPermission::VIEW_REPORT,
            Contexts::PROGRAM,
            (int)$channel_id,
            true
        )) {
            return parent::getAdminError($this->theme_path);
        }
        if ($quiz_id > 0) {
            $crumbs = [
                trans('admin/dashboard.dashboard') => static::CP,
                trans('admin/reports.admin_reports') => static::REPORTS,
                trans('admin/reports.quiz_performance_by_question_report') => '',
            ];
            $result = $this->till_content_service->getQuizQuestionsDetails($quiz_id);
            $title = trans('admin/reports.quiz_performance_by_question');
            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = trans('admin/reports.admin_reports');
            $this->layout->pageicon = static::BAR_CHART_FONT;
            $this->layout->pagedescription = trans('admin/reports.admin_reports');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with(static::MAIN_MENU, static::REPORT)
                ->with(static::SUB_MENU, static::ADMIN_REPORT);
            $this->layout->content = view('admin.theme.reports.quizperformancebyquestion')
                ->with(static::TITLE, $title)
                ->with(static::QUES_LIST, array_get($result, static::QUIZ_TITLE, []))
                ->with(static::QUIZ_ID, $quiz_id)
                ->with(static::QUIZ_NAME, array_get($result, static::QUIZ_NAME, ''))
                ->with(static::TOTAL_MARK, array_get($result, static::TOTAL_MARK, 0))
                ->with(static::QUIZ_MAX_TIME, array_get($result, static::QUIZ_MAX_TIME, 0))
                ->with(static::CANNEL_ID, $channel_id)
                ->with(static::QUIZ_IDS, array_get($result, static::QUIZ_IDS, []));
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }

    /**
     * postAjaxQuizPerformanceByQuestion
     * @return json
     */
    public function postAjaxQuizPerformanceByQuestion()
    {
        $channel_id = (int)Input::get(static::CANNEL_ID, 0);
        $user_id = Auth::user()->uid;
        if (!$this->roleService->hasPermission(
            $user_id,
            ModuleEnum::REPORT,
            PermissionType::ADMIN,
            ReportPermission::VIEW_REPORT,
            Contexts::PROGRAM,
            (int)$channel_id,
            true
        )) {
            return response()->json([]);
        }
        $start = 0;
        $limit = 10;
        $search = Input::get(static::SEARCH, '');
        $search_key = '';
        $order_by = Input::get(static::ORDER);
        $order_by_array = [static::CREATED_AT => static::DESC];
        $ques_ids = Input::get(static::QUIZ_IDS, []);
        $temp_avg = [];
        if (isset($order_by[0][static::COLUMN]) && isset($order_by[0][static::DIR])) {
            if ($order_by[0][static::COLUMN] == '0') {
                $order_by_array = [static::USER_NAME => $order_by[0][static::DIR]];
            }
            if ($order_by[0][static::COLUMN] == '1') {
                $order_by_array = [static::SCORE => $order_by[0][static::DIR]];
            }
            if ($order_by[0][static::COLUMN] == '2') {
                $order_by_array = [static::TIME_TAKEN => $order_by[0][static::DIR]];
            }
        }
        if (isset($search[static::VALUE])) {
            $search_key = $search[static::VALUE];
        } else {
            $search_key = '';
        }
        if (preg_match('/^[0-9]+$/', Input::get(static::START, 0))) {
            $start = Input::get(static::START);
        }
        if (preg_match('/^[0-9]+$/', Input::get(static::LENGTH, 10))) {
            $limit = Input::get(static::LENGTH);
        }
        $status_filter = Input::get(static::STATUS_FILTER);
        $status_filter = strtoupper($status_filter);
        $quiz_id = (int)Input::get(static::QUIZ_ID, 0);
        $result = $this->till_content_service->prepareQuizPerformanceByQuestion(
            $start,
            $limit,
            $order_by_array,
            $search_key,
            $quiz_id,
            $channel_id,
            $ques_ids
        );
        return response()->json($result);
    }

    /**
     * getChannelCompletion
     * @return blade/view
     */
    public function getChannelCompletion()
    {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::VIEW_REPORT)) {
            return parent::getAdminError($this->theme_path);
        }
        $content_feeds = $this->permittedChannelLists(false);
        $general = SiteSetting::module('General');
        $crumbs = [
            trans('admin/dashboard.dashboard') => static::CP,
            trans('admin/reports.admin_reports') => static::REPORTS,
            trans('admin/reports.channel_compl_report') => '',
        ];
        $title = trans('admin/reports.channel_compl');
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/reports.admin_reports');
        $this->layout->pageicon = static::BAR_CHART_FONT;
        $this->layout->pagedescription = trans('admin/reports.manage_reports');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with(static::MAIN_MENU, static::REPORT)
            ->with(static::SUB_MENU, static::ADMIN_REPORT);
        $this->layout->content = view('admin.theme.reports.channelcompletion')
            ->with(static::TITLE, $title)
            ->with(static::CONTENT_FEEDS, $content_feeds)
            ->with('general', $general);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * getAjaxChannelCompletion
     * @param  integer $page
     * @return json
     */
    public function getAjaxChannelCompletion($page = 0)
    {
        $channel_ids = $this->permittedChannelLists(true);
        $limit = config('app.limit_bars_chart');
        $start = $page * (int)$limit;
        if ($start > 0) {
            $start = $start - 1;
        }
        $result = $this->till_content_service->prepareChannelCompletion($channel_ids, $start, $limit);
        $finalOutput = [
            static::TITLE => trans('admin/reports.channel_compl_report'),
            static::DATA => array_get($result, static::DATA, []),
            static::XAXIS => array_get($result, static::XAXIS, []),
            static::ID => array_get($result, static::ID, []),
            static::AVG => 0,
            static::POST_ROW_COUNT => array_get($result, static::POST_ROW_COUNT, []),
        ];
        return response()->json($finalOutput);
    }

    /**
     * getIndividualChannelCompletion
     * @param  integer $channel_id
     * @return blade/view
     */
    public function getIndividualChannelCompletion($channel_id = 0)
    {
        $general = SiteSetting::module('General');
        $admin_id = Auth::user()->uid;
        $channel_id = (int) $channel_id;
        if (!$this->roleService->hasPermission(
            $admin_id,
            ModuleEnum::REPORT,
            PermissionType::ADMIN,
            ReportPermission::VIEW_REPORT,
            Contexts::PROGRAM,
            $channel_id,
            true
        )) {
            return parent::getAdminError($this->theme_path);
        }
        $content_feeds = $this->permittedChannelLists(false);
        $crumbs = [
            trans('admin/dashboard.dashboard') => static::CP,
            trans('admin/reports.admin_reports') => static::REPORTS,
            trans('admin/reports.indiv_channel_comp_report') => '',
        ];
        $name = $this->till_content_service->getChannelNameById($channel_id, true);
        $title = trans('admin/reports.indiv_channel_comp');
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/reports.admin_reports');
        $this->layout->pageicon = static::BAR_CHART_FONT;
        $this->layout->pagedescription = trans('admin/reports.admin_reports');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with(static::MAIN_MENU, static::REPORT)
            ->with(static::SUB_MENU, static::ADMIN_REPORT);
        $this->layout->content = view('admin.theme.reports.individualchannelcompletion')
            ->with(static::TITLE, $title)
            ->with(static::CANNEL_ID, $channel_id)
            ->with(static::NAME, $name)
            ->with(static::CONTENT_FEEDS, $content_feeds)
            ->with('general', $general);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * getAjaxIndividualChannelCompletion
     * @param  integer $page
     * @param  integer $channel_id
     * @return json
     */
    public function getAjaxIndividualChannelCompletion($page = 0, $channel_id = 0)
    {
        $channel_id = (int)$channel_id;
        $admin_id = Auth::user()->uid;
        if (!$this->roleService->hasPermission(
            $admin_id,
            ModuleEnum::REPORT,
            PermissionType::ADMIN,
            ReportPermission::VIEW_REPORT,
            Contexts::PROGRAM,
            (int)$channel_id,
            true
        )) {
            return response()->json([]);
        }
        $result = [];
        if ($channel_id >= 1) {
            $limit = config('app.limit_bars_chart');
            $start = $page * (int)$limit;
            $result = $this->till_content_service->prepareIndividualChannelCompletion($channel_id, $start, $limit);
        }
        $finalOutput = [
            static::TITLE => trans('admin/reports.indiv_channel_comp'),
            static::DATA => array_get($result, static::DATA, []),
            static::XAXIS => array_get($result, static::XAXIS, []),
            static::AVG => 0,
        ];
        return response()->json($finalOutput);
    }

    /**
     * getUserReports
     * @return blade/view
     */
    public function getUserReports()
    {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::VIEW_REPORT)) {
            return parent::getAdminError($this->theme_path);
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => static::CP,
            trans('admin/reports.user_reports') => 'reports/user-reports',
            trans('admin/reports.user_lists') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $title = static::LIST_OF_USERS;
        $this->layout->pagetitle = trans('admin/reports.user_reports');
        $this->layout->pageicon = static::BAR_CHART_FONT;
        $this->layout->pagedescription = trans('admin/reports.user_reports');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with(static::MAIN_MENU, static::REPORT)
            ->with(static::SUB_MENU, static::USER_REPORT);
        $this->layout->content = view('admin.theme.reports.listofuserreport')
            ->with(static::TITLE, $title);
    }

    /**
     * anyAjaxUserList
     * @return json
     */
    public function anyAjaxUserList()
    {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::VIEW_REPORT)) {
            return parent::getAdminError($this->theme_path);
        }
        $channel_ids = $this->permittedChannelLists(true);
        $user_ids = [];
        if (empty($channel_ids)) {
            $content_feeds = $this->dim_channel_service->getChannelSlugsNameAndIds();
            if (!empty($content_feeds)) {
                $channel_ids = array_pluck($content_feeds, 'channel_id');
            }
            unset($content_feeds);
        }
        if (!empty($channel_ids)) {
            $user_ids = $this->dim_user_service->getChannelsUserIds($channel_ids)->toArray();
        }
        $start = 0;
        $limit = 10;
        $search = Input::get(static::SEARCH, '');
        $search_key = '';
        $order_by = Input::get(static::ORDER);
        $order_by_array = [static::CREATED_AT => static::DESC];
        if (isset($order_by[0][static::COLUMN]) && isset($order_by[0][static::DIR])) {
            if ($order_by[0][static::COLUMN] == '0') {
                $order_by_array = [static::USER_NAME => $order_by[0][static::DIR]];
            }
            if ($order_by[0][static::COLUMN] == '2') {
                $order_by_array = [static::COMPLETION => $order_by[0][static::DIR]];
            }
            if ($order_by[0][static::COLUMN] == '1') {
                $order_by_array = [static::PERFORMANCE => $order_by[0][static::DIR]];
            }
        }
        if (isset($search[static::VALUE])) {
            $search_key = $search[static::VALUE];
        } else {
            $search_key = '';
        }
        if (preg_match('/^[0-9]+$/', Input::get(static::START))) {
            $start = Input::get(static::START);
        }
        if (preg_match('/^[0-9]+$/', Input::get(static::LENGTH))) {
            $limit = Input::get(static::LENGTH);
        }
        $status_filter = Input::get(static::STATUS_FILTER);
        $status_filter = strtoupper($status_filter);
        $result = $this->till_content_service->prepareUserCompletionAndPerformance(
            $user_ids,
            $channel_ids,
            $order_by_array,
            $search_key,
            (int)$start,
            (int)$limit
        );
        $finalData = [
            static::RECORDS_TOTAL => array_get($result, 'total_count', 0),
            static::RECORDS_FILTERED => array_get($result, 'total_count', 0),
            static::DATA => array_get($result, 'data', []),
        ];
        return response()->json($finalData);
    }

    public function getAssignedUsersList()
    {
        $channel_ids = $this->permittedChannelLists(true);
        $assigned_user_ids = $result = $data = [];
        $filters = [
                "start" => Request::all()['start'],
                "limit" => Request::all()['length'],
                "search" => Request::all()['search']['value'],
            ];
        if (empty($channel_ids)) {
            $content_feeds = $this->dim_channel_service->getChannelSlugsNameAndIds();
            if (!empty($content_feeds)) {
                $channel_ids = array_pluck($content_feeds, 'channel_id');
            }
            unset($content_feeds);
        }
        if (!empty($channel_ids)) {
            $assigned_user_ids = $this->dim_user_service->getChannelsUserIds($channel_ids)->toArray();
        }
        $ovca_users = $this->chnl_analytic_repo->usersPerformanceCount($assigned_user_ids, $channel_ids)->toArray();
        $user_ids = array_diff($assigned_user_ids, $ovca_users);
        $count = count($user_ids);
        $result = $this->user_service->getListOfActiveUsers(
            $user_ids,
            $filters['start'],
            $filters['limit'],
            $filters['search']
        )->each(function ($item) use (&$data) {
            $data[] = [
                $item->username,
                $item->fullname,
                $item->email
            ];
        });
        return response()->json([
            'recordsTotal' => $count,
            'recordsFiltered' => $count,
            'data' => $data
        ]);
    }

    public function getExportAssignedUsers()
    {
        try {
            $channel_ids = $this->permittedChannelLists(true);
            $assigned_user_ids = $data = [];
            $search = '';
            if (empty($channel_ids)) {
                $content_feeds = $this->dim_channel_service->getChannelSlugsNameAndIds();
                if (!empty($content_feeds)) {
                    $channel_ids = array_pluck($content_feeds, 'channel_id');
                }
                unset($content_feeds);
            }
            if (!empty($channel_ids)) {
                $assigned_user_ids = $this->dim_user_service->getChannelsUserIds($channel_ids)->toArray();
            }
            $ovca_users = $this->chnl_analytic_repo->usersPerformanceCount($assigned_user_ids, $channel_ids)->toArray();
            $user_ids = array_diff($assigned_user_ids, $ovca_users);
            $count = count($user_ids);
            $header[] = trans('admin/dashboard.username');
            $header[] = trans('admin/dashboard.user_fullname');
            $header[] = trans('admin/dashboard.user_email');
            $filename = 'YetToStartUsers.csv';
            $title = ["Report Title: Users not viewed any items"];
            $file_pointer = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . str_replace(" ", "", $filename));
            fputcsv($file_pointer, $title);
            fputcsv($file_pointer, $header);

            $batch_limit = config('app.bulk_insert_limit');
            $total_batchs = intval($count / $batch_limit);
            $record_set = 0;
            do {
                $start = $record_set * $batch_limit;
                if ($start >= 1) {
                    $start--;
                }
                $user_details = $this->user_service->getListOfActiveUsers($user_ids, $start, $batch_limit, $search);
                if ($user_details->isEmpty()) {
                    return response()->json("No more records found.");
                } else {
                    foreach ($user_details as $user) {
                         $tempRow = [];
                         $tempRow[] = $user->username;
                         $tempRow[] = $user->fullname;
                         $tempRow[] = $user->email;
                         fputcsv($file_pointer, $tempRow);
                    }
                }
                 $record_set++;
            } while ($record_set <= $total_batchs);
            fclose($file_pointer);
            exit;
        } catch (Exception $e) {
            return response()->json(['No records found, Please try after some time']);
        }
    }
    
    /**
     * getUserLeaderboard
     * @param  Request $req
     * @return blade/view
     */
    public function getUserLeaderboard(Request $req)
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => static::CP,
            trans('admin/reports.manage_reports') => static::REPORTS,
            trans('admin/reports.leaderboard') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $title = trans('admin/reports.leaderboard');
        $this->layout->pagetitle = trans('admin/reports.leaderboard');
        $this->layout->pageicon = static::BAR_CHART_FONT;
        $this->layout->pagedescription = trans('admin/reports.leaderboard');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with(static::MAIN_MENU, static::REPORT)
            ->with(static::SUB_MENU, 'leaderboard');
        $this->layout->content = view('playlyfe.admin_leaderboard')
            ->with(static::TITLE, $title);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * getGamificationSettings
     * @param  Request $req
     * @return blade/view
     */
    public function getGamificationSettings(Request $req)
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => static::CP,
            trans('admin/reports.gamification') => 'gamification',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $title = trans('admin/reports.gamification_settings');
        $this->layout->pagetitle = trans('admin/reports.gamification_settings');
        $this->layout->pageicon = static::BAR_CHART_FONT;
        $this->layout->pagedescription = trans('admin/reports.gamification_settings');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with(static::MAIN_MENU, 'web')
            ->with(static::SUB_MENU, 'siteconfig');
        $this->layout->content = view('playlyfe.admin_settings')
            ->with(static::TITLE, $title);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * postGamificationSettings
     * @param  Request $req
     * @return Input
     */
    public function postGamificationSettings(Request $req)
    {
        $pl = App::make(\App\Services\Playlyfe\IPlaylyfeService::class);
        $pl->patchActions(Input::all());
        return back()->withInput();
    }

    /**
     * getUserPerformanceReport
     * @param  integer $user_id
     * @return blade/view
     */
    public function getUserPerformanceReport($user_id = 0)
    {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::VIEW_REPORT)) {
            return parent::getAdminError($this->theme_path);
        }
        $user_name = '';
        $content_feeds = [];
        $permission_data = $this->permittedUserChannelLists((int)$user_id, false);
        $user_name = array_get($permission_data, static::USER_NAME, '');
        $content_feeds = array_get($permission_data, static::CONTENET__FEEDS, []);
        $crumbs = [
            trans('admin/dashboard.dashboard') => static::CP,
            trans('admin/reports.user_reports') => 'reports/user-reports',
            trans('admin/reports.user_channel_perf_report') => '',
        ];
        $title = trans('admin/reports.specific_user_channel_perf_report', ['user_name' =>$user_name]);
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/reports.user_reports');
        $this->layout->pageicon = static::BAR_CHART_FONT;
        $this->layout->pagedescription = trans('admin/reports.user_reports');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with(static::MAIN_MENU, static::REPORT)
            ->with(static::SUB_MENU, static::USER_REPORT);
        $this->layout->content = view('admin.theme.reports.userperformance')
            ->with(static::TITLE, $title)
            ->with(static::USER_ID, $user_id)
            ->with(static::USER_NAME, $user_name)
            ->with(static::CONTENT_FEEDS, $content_feeds);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * getAjaxUserChannelPerformance
     * @param  integer $page
     * @param  integer $user_id
     * @return json
     */
    public function getAjaxUserChannelPerformance($page = 0, $user_id = 0)
    {
        $permission_data = $this->permittedUserChannelLists((int)$user_id, true);
        $channel_ids = array_get($permission_data, static::CONTENET__FEEDS, []);
        $limit = 10;
        $start = (int)$page * $limit;
        if ($start > 0) {
            $start = $start - 1;
        }
        $user_id = (int)$user_id;
        $result = $this->till_content_service->prepareUserChannelPerformance($channel_ids, $user_id, $start, $limit);
        $finaloutput = [
            static::DATA => array_get($result, static::DATA, []),
            static::AVG_DATA => array_get($result, static::AVG_DATA, []),
            static::XAXIS => array_get($result, static::LABELS, []),
            static::ID => array_get($result, static::IDS, []),
        ];
        return response()->json($finaloutput);
    }

    /**
     * getIndividualChannelUserPerformance
     * @param  integer $channel_id
     * @param  integer $user_id
     * @param  string  $name
     * @return blade/view
     */
    public function getIndividualChannelUserPerformance($channel_id = 0, $user_id = 0, $name = '')
    {
        $admin_id = Auth::user()->uid;
        if (!$this->roleService->hasPermission(
            $admin_id,
            ModuleEnum::REPORT,
            PermissionType::ADMIN,
            ReportPermission::VIEW_REPORT,
            Contexts::PROGRAM,
            (int)$channel_id,
            true
        )) {
            return parent::getAdminError($this->theme_path);
        }
        $user_name = '';
        $content_feeds = [];
        $permission_data = $this->permittedUserChannelLists((int)$user_id, false);
        $user_name = array_get($permission_data, static::USER_NAME, '');
        $content_feeds = array_get($permission_data, static::CONTENET__FEEDS, []);
        $crumbs = [
            trans('admin/dashboard.dashboard') => static::CP,
            trans('admin/reports.reports') => static::REPORTS,
            trans('admin/reports.user_reports') => 'user-reports',
            trans('admin/reports.indiv_channel_user_perf') => '',
        ];
        $channel_name = '';
        if ($name == '') {
            $channel_name = $this->till_content_service->getChannelNameById((int)$channel_id);
        }
        $title = trans(
            'admin/reports.specific_user_ind_channel_perf_report',
            ['user_name' =>$user_name, 'channel' => $channel_name]
        );
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/reports.user_reports');
        $this->layout->pageicon = static::BAR_CHART_FONT;
        $this->layout->pagedescription = trans('admin/reports.user_reports');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with(static::MAIN_MENU, static::REPORT)
            ->with(static::SUB_MENU, static::USER_REPORT);
        $this->layout->content = view('admin.theme.reports.individualchanneluserperformance')
            ->with(static::TITLE, $title)
            ->with(static::CANNEL_ID, $channel_id)
            ->with(static::USER_ID, $user_id)
            ->with(static::NAME, $channel_name)
            ->with(static::USER_NAME, $user_name)
            ->with(static::CONTENT_FEEDS, $content_feeds);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * getAjaxIndividualChannelUserPerformance
     * @param  integer $page
     * @param  integer $channel_id
     * @param  integer $user_id
     * @return json
     */
    public function getAjaxIndividualChannelUserPerformance($page = 0, $channel_id = 0, $user_id = 0)
    {
        $admin_id = Auth::user()->uid;
        if (!$this->roleService->hasPermission(
            $admin_id,
            ModuleEnum::REPORT,
            PermissionType::ADMIN,
            ReportPermission::VIEW_REPORT,
            Contexts::PROGRAM,
            (int)$channel_id,
            true
        )) {
            return response()->json([]);
        }
        $limit = 10;
        $start = (int)$page * $limit;
        if ($start > 1) {
            $start = $start - 1;
        }
        $result = $this->till_content_service->prepareUserIndChannelPerformance(
            (int)$channel_id,
            (int)$user_id,
            (int)$start,
            (int)$limit
        );
        $finaloutput = [
            static::DATA => array_get($result, static::DATA, []),
            static::AVG_DATA => array_get($result, static::AVG_DATA, []),
            static::XAXIS => array_get($result, static::LABELS, []),
            static::IDS => array_get($result, static::IDS, [])
        ];
        return response()->json($finaloutput);
    }

    /**
     * getUserQuizPerformanceByQuestion
     * @param  integer $quiz_id
     * @param  integer $channel_id
     * @param  integer $user_id
     * @return blade/view
     */
    public function getUserQuizPerformanceByQuestion($quiz_id = 0, $channel_id = 0, $user_id = 0)
    {
        $admin_id = Auth::user()->uid;
        if (!$this->roleService->hasPermission(
            $admin_id,
            ModuleEnum::REPORT,
            PermissionType::ADMIN,
            ReportPermission::VIEW_REPORT,
            Contexts::PROGRAM,
            (int)$channel_id,
            true
        )) {
            return parent::getAdminError($this->theme_path);
        }
        if ($quiz_id > 0) {
            $crumbs = [
                trans('admin/dashboard.dashboard') => static::CP,
                trans('admin/reports.admin_reports') => static::REPORTS,
                trans('admin/reports.quiz_performance_by_question') => '',
            ];
            $result = $this->till_content_service->getQuizQuestionsDetails($quiz_id);
            $quiz_name = array_get($result, static::QUIZ_NAME, '');
            $title = '"' . $quiz_name . '" Performance By Question';
            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = trans('admin/reports.admin_reports');
            $this->layout->pageicon = static::BAR_CHART_FONT;
            $this->layout->pagedescription = trans('admin/reports.admin_reports');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with(static::MAIN_MENU, static::REPORT)
                ->with(static::SUB_MENU, static::ADMIN_REPORT);
            $this->layout->content = view('admin.theme.reports.userquizperformancebyquestion')
                ->with(static::TITLE, $title)
                ->with(static::QUES_LIST, array_get($result, static::QUIZ_TITLE, []))
                ->with(static::QUIZ_ID, $quiz_id)
                ->with(static::QUIZ_NAME, array_get($result, static::QUIZ_NAME, ''))
                ->with(static::TOTAL_MARK, array_get($result, static::TOTAL_MARK, 0))
                ->with(static::QUIZ_MAX_TIME, array_get($result, static::QUIZ_MAX_TIME, 0))
                ->with(static::CANNEL_ID, $channel_id)
                ->with(static::QUIZ_IDS, array_get($result, static::QUIZ_IDS, []))
                ->with(static::USER_ID, $user_id);
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }

    /**
     * postAjaxUserQuizPerformanceByQuestion
     * @return json
     */
    public function postAjaxUserQuizPerformanceByQuestion()
    {
        $channel_id = (int)Input::get(static::CANNEL_ID, 0);
        $admin_id = Auth::user()->uid;
        if (!$this->roleService->hasPermission(
            $admin_id,
            ModuleEnum::REPORT,
            PermissionType::ADMIN,
            ReportPermission::VIEW_REPORT,
            Contexts::PROGRAM,
            (int)$channel_id,
            true
        )) {
            return response()->json([]);
        }
        $start = 0;
        $limit = 10;
        $search = Input::get(static::SEARCH, '');
        $search_key = '';
        $order_by = Input::get(static::ORDER);
        $order_by_array = [static::CREATED_AT => static::DESC];
        $ques_ids = Input::get(static::QUIZ_IDS, []);
        $user_id = (int)Input::get(static::USER_ID, 0);
        if (isset($order_by[0][static::COLUMN]) && isset($order_by[0][static::DIR])) {
            if ($order_by[0][static::COLUMN] == '0') {
                $order_by_array = [static::USER_NAME => $order_by[0][static::DIR]];
            }
            if ($order_by[0][static::COLUMN] == '1') {
                $order_by_array = [static::SCORE => $order_by[0][static::DIR]];
            }
            if ($order_by[0][static::COLUMN] == '2') {
                $order_by_array = [static::TIME_TAKEN => $order_by[0][static::DIR]];
            }
        }
        if (isset($search[static::VALUE])) {
            $search_key = $search[static::VALUE];
        } else {
            $search_key = '';
        }
        if (preg_match('/^[0-9]+$/', Input::get(static::START, 0))) {
            $start = Input::get(static::START);
        }
        if (preg_match('/^[0-9]+$/', Input::get(static::LENGTH, 10))) {
            $limit = Input::get(static::LENGTH);
        }
        $status_filter = Input::get(static::STATUS_FILTER);
        $status_filter = strtoupper($status_filter);
        $quiz_id = (int)Input::get(static::QUIZ_ID, 0);

        $finaldata = $this->till_content_service->prepareQuizPerformanceByQuestion(
            $start,
            $limit,
            $order_by_array,
            $search_key,
            $quiz_id,
            $channel_id,
            $ques_ids,
            $user_id
        );
        return response()->json($finaldata);
    }

    /**
     * getUserCompletionReport
     * @param  integer $user_id
     * @return blade/view
     */
    public function getUserCompletionReport($user_id = 0)
    {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::VIEW_REPORT)) {
            return parent::getAdminError($this->theme_path);
        }
        if ($user_id > 0) {
            $user_name = '';
            $content_feeds = [];
            $permission_data = $this->permittedUserChannelLists((int)$user_id, false);
            $user_name = array_get($permission_data, static::USER_NAME, '');
            $content_feeds = array_get($permission_data, static::CONTENET__FEEDS, []);
            $title = trans('admin/reports.specific_user_channel_compl_report', ['user_name' => $user_name]);
            $crumbs = [
                trans('admin/dashboard.dashboard') => static::CP,
                trans('admin/reports.user_reports') => 'reports/user-reports',
                trans('admin/reports.user_channel_compl_report') => '',
            ];
            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = trans('admin/reports.user_reports');
            $this->layout->pageicon = static::BAR_CHART_FONT;
            $this->layout->pagedescription = trans('admin/reports.user_reports');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with(static::MAIN_MENU, static::REPORT)
                ->with(static::SUB_MENU, static::USER_REPORT);
            $this->layout->content = view('admin.theme.reports.usercompletion')
                ->with(static::TITLE, $title)
                ->with(static::USER_ID, $user_id)
                ->with(static::USER_NAME, $user_name)
                ->with(static::CONTENT_FEEDS, $content_feeds);
            $this->layout->footer = view('admin.theme.common.footer');
        } else {
            return parent::getAdminError($this->theme_path);
        }
    }

    /**
     * getAjaxUserChannelCompletion
     * @param  integer $page
     * @param  integer $user_id
     * @return json
     */
    public function getAjaxUserChannelCompletion($page = 0, $user_id = 0)
    {
        $permission_data = $this->permittedUserChannelLists((int)$user_id, true);
        $channel_ids = array_get($permission_data, static::CONTENET__FEEDS, []);
        $limit = 10;
        $start = $page * $limit;
        if ($start > 0) {
            $start = $start - 1;
        }
        $result = $this->till_content_service->prepareUserChannelCompletion(
            $channel_ids,
            (int)$user_id,
            (int)$start,
            (int)$limit
        );
        $finaloutput = [
            static::DATA => array_get($result, static::DATA, []),
            static::AVG_DATA => array_get($result, static::AVG_DATA, []),
            static::XAXIS => array_get($result, static::LABELS, []),
            static::ID => array_get($result, static::IDS, []),
        ];
        return response()->json($finaloutput);
    }

    /**
     * getIndividualChannelUserCompletion
     * @param  integer $channel_id
     * @param  integer $user_id
     * @param  string  $name
     * @return blade/view
     */
    public function getIndividualChannelUserCompletion($channel_id = 0, $user_id = 0, $name = '')
    {
        $admin_id = Auth::user()->uid;
        if (!$this->roleService->hasPermission(
            $admin_id,
            ModuleEnum::REPORT,
            PermissionType::ADMIN,
            ReportPermission::VIEW_REPORT,
            Contexts::PROGRAM,
            (int)$channel_id,
            true
        )) {
            return parent::getAdminError($this->theme_path);
        }
        $user_name = '';
        $content_feeds = [];
        $permission_data = $this->permittedUserChannelLists((int)$user_id, false);
        $user_name = array_get($permission_data, static::USER_NAME, '');
        $content_feeds = array_get($permission_data, static::CONTENET__FEEDS, []);
        $crumbs = [
            trans('admin/dashboard.dashboard') => static::CP,
            trans('admin/reports.manage_reports') => static::REPORTS,
            trans('admin/reports.user_reports') => 'user-reports',
            trans('admin/reports.indiv_channel_user_comp') => '',
        ];
        $channel_name = '';
        if ($name == '') {
            $channel_name = $this->till_content_service->getChannelNameById((int)$channel_id);
        }
        $title = trans(
            'admin/reports.specific_user_ind_channel_compl_report',
            ['user_name' =>$user_name, 'channel' => $channel_name]
        );
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/reports.user_reports');
        $this->layout->pageicon = static::BAR_CHART_FONT;
        $this->layout->pagedescription = trans('admin/reports.user_reports');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with(static::MAIN_MENU, static::REPORT)
            ->with(static::SUB_MENU, static::USER_REPORT);
        $this->layout->content = view('admin.theme.reports.individualchannelusercompletion')
            ->with(static::TITLE, $title)
            ->with(static::CANNEL_ID, $channel_id)
            ->with(static::USER_ID, $user_id)
            ->with(static::NAME, $channel_name)
            ->with(static::USER_NAME, $user_name)
            ->with(static::CONTENT_FEEDS, $content_feeds);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * getAjaxIndividualChannelUserCompletion
     * @param  integer $page
     * @param  integer $channel_id
     * @param  integer $user_id
     * @return json
     */
    public function getAjaxIndividualChannelUserCompletion($page = 0, $channel_id = 0, $user_id = 0)
    {
        $admin_id = Auth::user()->uid;
        $channel_id = (int)$channel_id;
        if (!$this->roleService->hasPermission(
            $admin_id,
            ModuleEnum::REPORT,
            PermissionType::ADMIN,
            ReportPermission::VIEW_REPORT,
            Contexts::PROGRAM,
            $channel_id,
            true
        )) {
            return response()->json([]);
        }
        $limit = 10;
        $start = $page * $limit;
        $result = $this->till_content_service->prepareUserIndChannelCompletion(
            (int)$channel_id,
            (int)$user_id,
            (int)$start,
            (int)$limit
        );
        $finaloutput = [
            static::DATA => array_get($result, static::VALUES, []),
            static::AVG_DATA => array_get($result, static::VALUES_AVG, []),
            static::XAXIS => array_get($result, static::LABELS, [])
        ];
        return response()->json($finaloutput);
    }

    /**
     * getAnnouncementViewed
     * @return blade/view
     */
    public function getAnnouncementViewed()
    {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::VIEW_REPORT)) {
            return parent::getAdminError($this->theme_path);
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => static::CP,
            trans('admin/reports.admin_reports') => static::REPORTS,
            trans('admin/reports.announcement_report') => '',
        ];
        $general = SiteSetting::module('General');
        $range = $this->validateDates();
        $title = trans('admin/reports.announ_viewed');
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/reports.admin_reports');
        $this->layout->pageicon = static::BAR_CHART_FONT;
        $this->layout->pagedescription = trans('admin/reports.admin_reports');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with(static::MAIN_MENU, static::REPORT)
            ->with(static::SUB_MENU, static::ADMIN_REPORT);
        $this->layout->content = view('admin.theme.reports.announcementviewed')
            ->with(static::START_DATE, array_get($range, static::START_DATE))
            ->with(static::END_DATE, array_get($range, static::END_DATE))
            ->with(static::TITLE, $title)
            ->with('general', $general);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * postAjaxAnnouncementViewedCount
     * @return json
     */
    public function postAjaxAnnouncementViewedCount()
    {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::VIEW_REPORT)) {
            return response()->json([]);
        }
        $user_ids = Input::get(static::USER_ID);
        $user_names = [];
        $start = 0;
        $limit = 200;
        if (is_array($user_ids)) {
            $user_names_obj = $this->dim_user_service->getUserNameListByids(array_map(static::INTVAL, $user_ids), $start, $limit);
            $user_names = $user_names_obj->pluck(static::USER_NAME);
        }
        return response()->json($user_names);
    }

    /**
     * getAjaxAnnouncementViewed
     * @param  integer $page
     * @param  integer $limit
     * @return json
     */
    public function getAjaxAnnouncementViewed($page = 0, $limit = 10)
    {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::VIEW_REPORT)) {
            return response()->json([]);
        }
        $announcement_ids = $this->permittedAnnouncements();
        $start = $page * $limit;
        if ($start > 0) {
            $start = $start - 1;
        }
        $labels = [];
        $not_viewed_list = [];
        $viewed_list = [];
        $range = Input::get(static::RANGE);
        $start_date = '';
        $end_date = '';
        if ($range) {
            $range = explode(static::_TO_, $range);
            if ($range && is_array($range) && !empty($range) && count($range) > 1) {
                $start_date = array_get($range, 0);
                $end_date = array_get($range, 1);
            }
        }
        $range = $this->validateDates($start_date, $end_date);
        $start_date = array_get($range, static::START_DATE, time());
        $end_date = array_get($range, static::END_DATE, time());
        $st_diff = Carbon::createFromTimestamp($start_date, Auth::user()->timezone);
        $end_diff = Carbon::createFromTimestamp($end_date, Auth::user()->timezone);
        $date_range = (int)$st_diff->diffInDays($end_diff);
        $announce_res = $this->dim_announce_service->getAnnouncements(
            $announcement_ids,
            $start,
            $limit,
            $start_date,
            $end_date
        );
        foreach ($announce_res as $key => $announcement) {
            $labels[] = html_entity_decode($announcement[static::ANNOUNCEMENT_TITLE]);
            $not_viewed_list[] = array_get(
                $announcement,
                'no_of_user_not_viewed_list',
                array_get($announcement, 'no_of_user_not_viewd_list', 0)
            );
            $viewed_list[] = array_get(
                $announcement,
                'no_of_user_viewed_list',
                array_get($announcement, 'no_of_user_viewd_list', 0)
            );
        }
        $finaloutput = [
            static::TITLE => trans('admin/reports.announ_viewed'),
            static::XAXIS => $labels,
            'not_viewed_list' => $not_viewed_list,
            'viewed_list' => $viewed_list,
        ];
        return response()->json($finaloutput);
    }

    /**
     * getCronLogsReports
     * @return blade/view
     */
    public function getCronLogsReports()
    {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::VIEW_REPORT)) {
            return parent::getAdminError($this->theme_path);
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => static::CP,
            trans('admin/reports.reports') => static::REPORTS,
            trans('admin/reports.cron_log_reports') => '',
        ];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $title = trans('admin/reports.reports_cron_logs');
        $this->layout->pagetitle = trans('admin/reports.reports_cron_logs');
        $this->layout->pageicon = static::BAR_CHART_FONT;
        $this->layout->pagedescription = trans('admin/reports.user_reports');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with(static::MAIN_MENU, static::REPORT)
            ->with(static::SUB_MENU, 'cronlogsreports');
        $this->layout->content = view('admin.theme.reports.reportscronlogs')
            ->with(static::TITLE, $title);
    }

    /**
     * anyAjaxCronLogsReports
     * @return json
     */
    public function anyAjaxCronLogsReports()
    {
        $start = 0;
        $limit = 10;
        $search = Input::get(static::SEARCH, '');
        $search_key = '';
        $order_by = Input::get(static::ORDER);
        $order_by_array = [static::CREATE_DATE => static::DESC];
        if (isset($order_by[0][static::COLUMN]) && isset($order_by[0][static::DIR])) {
            if ($order_by[0][static::COLUMN] == '0') {
                $order_by_array = [static::MODULE_NAME => $order_by[0][static::DIR]];
            }
            if ($order_by[0][static::COLUMN] == '1') {
                $order_by_array = [static::STATUS => $order_by[0][static::DIR]];
            }
            if ($order_by[0][static::COLUMN] == '2') {
                $order_by_array = [static::CREATE_DATE => $order_by[0][static::DIR]];
            }
        }
        if (isset($search[static::VALUE])) {
            $search_key = $search[static::VALUE];
        } else {
            $search_key = '';
        }
        if (preg_match('/^[0-9]+$/', Input::get(static::START))) {
            $start = Input::get(static::START);
        }
        if (preg_match('/^[0-9]+$/', Input::get(static::LENGTH))) {
            $limit = Input::get(static::LENGTH);
        }
        $status_filter = Input::get(static::STATUS_FILTER);
        $status_filter = strtoupper($status_filter);
        $totalRecords = CronLog::getCronLogsSearchCount();
        $filteredRecords = CronLog::getCronLogsSearchCount();
        $filtereddata = CronLog::getCronLogswithPagenation($start, $limit, $order_by_array, $search_key);
        $dataArr = [];
        foreach ($filtereddata as $key => $cronlog) {
            if (isset($cronlog[static::UPDATE_DATE]) && !empty($cronlog[static::UPDATE_DATE])) {
                $update_date = Timezone::convertFromUTC('@' . $cronlog[static::UPDATE_DATE], Auth::user()->timezone, 'd-m-y H:i:s');
            } else {
                $update_date = '-';
            }

            $temp = [
                $cronlog[static::MODULE_NAME],
                $cronlog[static::STATUS],
                Timezone::convertFromUTC('@' . $cronlog[static::CREATE_DATE], Auth::user()->timezone, 'd-m-y H:i:s'),
                $update_date,
            ];
            array_push($dataArr, $temp);
        }
        $finaldata = [
            static::RECORDS_TOTAL => $totalRecords,
            static::RECORDS_FILTERED => $filteredRecords,
            static::DATA => $dataArr,
        ];
        return response()->json($finaldata);
    }

    /**
     * getCsvPerformanceReport
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse export report as csv file
     */
    public function getCsvPerformanceReport()
    {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::EXPORT_REPORT)) {
            return parent::getAdminError($this->theme_path);
        }
        $channel_ids = $this->permittedChannelLists(true, ReportPermission::EXPORT_REPORT);
        $data = [];
        $title = trans('admin/reports.channel_performance_report');
        $data[] = [$title];
        $data[] = [trans('admin/reports.duration'), trans('admin/reports.till_date')];
        $data[] = [
            trans('admin/reports.course_name'),
            trans('admin/reports.short_name'),
            trans('admin/reports.avg_scores')
        ];
        $result = $this->till_content_service->prepareChannelPerformance($channel_ids, 0, 0);
        $perf_vaues = array_get($result, static::DATA, []);
        $channel_names = array_get($result, static::XAXIS, []);
        $short_names = array_get($result, 'short_names', []);
        foreach ($channel_names as $key => $each_channel) {
            $data[] = [
                $each_channel,
                array_get($short_names, $key, ''),
                array_get($perf_vaues, $key, 0)
            ];
        }
        if (!empty($data)) {
            $filename = $title;
            $this->writeCsv($data, $filename);
        }
        exit();
    }

    /**
     * getCsvSinglePerformanceReport
     * @param  integer $channel_id
     * @param  string  $name
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse export report as csv file
     */
    public function getCsvSinglePerformanceReport($channel_id = 0, $name = '')
    {
        $channel_id = (int)$channel_id;
        $admin_id = Auth::user()->uid;
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::EXPORT_REPORT) &&
            !$this->roleService->hasPermission(
                $admin_id,
                ModuleEnum::REPORT,
                PermissionType::ADMIN,
                ReportPermission::EXPORT_REPORT,
                Contexts::PROGRAM,
                (int)$channel_id,
                true
            )
        ) {
            return parent::getAdminError($this->theme_path);
        }
        $name = html_entity_decode($this->till_content_service->getChannelNameById($channel_id));
        $data = [];
        $data[] = [trans('admin/reports.specific_channel_perf', ['channel' => $name])];
        $data[] = [trans('admin/reports.duration'), trans('admin/reports.till_date')];
        $data[] = [trans('admin/reports.quiz_name'), trans('admin/reports.avg_scores')];
        $result = $this->till_content_service->prepareIndividualChannelPerformance((int)$channel_id, 0, 0);
        $quizzes_performance = array_get($result, static::DATA, []);
        $quizzes_name = array_get($result, static::XAXIS, []);
        foreach ($quizzes_performance as $key => $quiz_performance) {
            $data[] = [
                array_get($quizzes_name, $key, ''),
                $quiz_performance
            ];
        }
        if (!empty($data)) {
            $filename = trans(
                'admin/reports.specific_channel_perf',
                ['channel' => preg_replace('/(\.)+/', '', str_limit(preg_replace('/([(|)])+/', '_', $name), static::FILE_NAME_CHAR_LIMIT))]
            );
            $this->writeCsv($data, $filename);
        }
        exit();
    }

    /**
     * getCsvCompletionReport
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse export report as csv file
     */
    public function getCsvCompletionReport()
    {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::EXPORT_REPORT)) {
            return parent::getAdminError($this->theme_path);
        }
        $channel_ids = $this->permittedChannelLists(true, ReportPermission::EXPORT_REPORT);
        $result = $this->till_content_service->prepareChannelCompletion($channel_ids, 0, 0);
        $data = [];
        $title = trans('admin/reports.channel_compl_report');
        $data[] = [$title];
        $data[] = [trans('admin/reports.duration'), trans('admin/reports.till_date')];
        $data[] = [
            trans('admin/reports.course_name'),
            trans('admin/reports.short_name'),
            trans('admin/reports.avg_compl')
        ];
        $channel_completions = array_get($result, static::DATA, []);
        $channel_names = array_get($result, static::XAXIS, []);
        $short_names = array_get($result, 'short_names', []);
        foreach ($channel_completions as $key => $channel_completion) {
            $data[] = [
                array_get($channel_names, $key, ''),
                array_get($short_names, $key, ''),
                $channel_completion
            ];
        }
        if (!empty($data)) {
            $filename = $title;
            $this->writeCsv($data, $filename);
        }
        exit();
    }

    /**
     * getCsvSingleCompletionReport
     * @param  integer $channel_id
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse export report as csv file
     */
    public function getCsvSingleCompletionReport($channel_id = 0)
    {
        $admin_id = Auth::user()->uid;
        $channel_id = (int)$channel_id;
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::EXPORT_REPORT) &&
            !$this->roleService->hasPermission(
                $admin_id,
                ModuleEnum::REPORT,
                PermissionType::ADMIN,
                ReportPermission::EXPORT_REPORT,
                Contexts::PROGRAM,
                (int)$channel_id,
                true
            )
        ) {
            return parent::getAdminError($this->theme_path);
        }
        $post_completions = [];
        $post_names = [];
        $result = $this->till_content_service->prepareIndividualChannelCompletion($channel_id, 0, 0);
        $post_completions = array_get($result, static::DATA, []);
        $post_names = array_get($result, static::XAXIS, []);
        $channel_name = html_entity_decode($this->till_content_service->getChannelNameById($channel_id, true));
        $data[] = [trans('admin/reports.specific_channel_compl', ['channel' => $channel_name])];
        $data[] = [trans('admin/reports.duration'), trans('admin/reports.till_date')];
        $data[] = [trans('admin/reports.post_name'), trans('admin/reports.avg_compl')];
        foreach ($post_completions as $key => $post_completion) {
            $data[] = [
                array_get($post_names, $key, ''),
                $post_completion
            ];
        }
        if (!empty($data)) {
            $filename = trans(
                'admin/reports.specific_channel_compl',
                ['channel' => preg_replace('/(\.)+/', '', str_limit(preg_replace('/([(|)])+/', '_', $channel_name), static::FILE_NAME_CHAR_LIMIT))]
            );
            $this->writeCsv($data, $filename);
        }
        exit();
    }

    /**
     * getCsvAnnouncementReport
     * @param  integer $page
     * @param  integer $limit
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse export report as csv file
     */
    public function getCsvAnnouncementReport($page = 0, $limit = 10)
    {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::EXPORT_REPORT)) {
            return parent::getAdminError($this->theme_path);
        }
        $announcement_ids = $this->permittedAnnouncements();
        $start = $page * $limit;
        if ($start > 0) {
            $start = $start - 1;
        }
        $labels = [];
        $not_viewed_list = [];
        $viewed_list = [];
        $range = Input::get(static::RANGE);
        $start_date = '';
        $end_date = '';
        if ($range) {
            $range = explode(static::_TO_, $range);
            if ($range && is_array($range) && !empty($range) && count($range) > 1) {
                $start_date = array_get($range, 0);
                $end_date = array_get($range, 1);
            }
        }
        $date_range = $this->validateDates($start_date, $end_date);
        $start_date = array_get($date_range, static::START_DATE, time());
        $end_date = array_get($date_range, static::END_DATE, time());
        $st_diff = Carbon::createFromTimestamp($start_date, Auth::user()->timezone);
        $end_diff = Carbon::createFromTimestamp($end_date, Auth::user()->timezone);
        $date_range = (int)$st_diff->diffInDays($end_diff);
        $announce_res = $this->dim_announce_service->getAnnouncements(
            $announcement_ids,
            0,
            0,
            $start_date,
            $end_date
        );
        $data = [];
        $data[] = ['AnnouncementReport'];
        $data[] = ['Duration', $range[0], $range[1]];
        $data[] = ['Announcement Name', 'Viewed', 'Not Viewed'];
        foreach ($announce_res as $key => $announcement) {
            $not_viewed = array_get(
                $announcement,
                'no_of_user_not_viewed_list',
                array_get($announcement, 'no_of_user_not_viewd_list', [])
            );
            $viewed = array_get(
                $announcement,
                'no_of_user_viewed_list',
                array_get($announcement, 'no_of_user_viewd_list', [])
            );
            $temp = [html_entity_decode($announcement[static::ANNOUNCEMENT_TITLE]), count($viewed), count($not_viewed)];
            $data[] = $temp;
        }
        if (!empty($data)) {
            $filename = 'AnnouncementReport';
            $this->writeCsv($data, $filename);
        }
        exit();
    }

    /**
     * getCsvUserPerformanceReport
     * @param  integer $user_id
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse export report as csv file
     */
    public function getCsvUserPerformanceReport($user_id = 0)
    {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::EXPORT_REPORT)) {
            return parent::getAdminError($this->theme_path);
        }
        $user_id = (int)$user_id;
        $user_name = '';
        $permission_data = $this->permittedUserChannelLists($user_id, true, ReportPermission::EXPORT_REPORT);
        $channel_ids = array_get($permission_data, static::CONTENET__FEEDS, []);
        $user_name = array_get($permission_data, static::USER_NAME, '');
        $result = $this->till_content_service->prepareUserChannelPerformance($channel_ids, $user_id, 0, 0);
        $data = [];
        $data[] = [trans('admin/reports.specific_user_channel_perf_report', ['user_name' =>$user_name])];
        $data[] = [trans('admin/reports.duration'), trans('admin/reports.till_date')];
        $data[] = [
            trans('admin/reports.course_name'),
            trans('admin/reports.specific_user_score', ['user_name' => $user_name]),
            trans('admin/reports.avg_scores')
        ];
        $user_perf = array_get($result, static::DATA, []);
        $avg_perf = array_get($result, static::AVG_DATA, []);
        $channel_names = array_get($result, static::LABELS, []);
        foreach ($channel_names as $key => $channel_name) {
            $data[] = [
                $channel_name,
                array_get($user_perf, $key, 0),
                array_get($avg_perf, $key, 0)
            ];
        }
        if (!empty($data)) {
            $filename = trans('admin/reports.specific_user_channel_perf_report', ['user_name' =>$user_name]);
            $this->writeCsv($data, $filename);
        }
        exit();
    }

    /**
     * getCsvUserSinglePerformanceReport
     * @param  integer $channel_id
     * @param  integer $user_id
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse export report as csv file
     */
    public function getCsvUserSinglePerformanceReport($channel_id = 0, $user_id = 0)
    {
        $admin_id = Auth::user()->uid;
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::EXPORT_REPORT) &&
            !$this->roleService->hasPermission(
                $admin_id,
                ModuleEnum::REPORT,
                PermissionType::ADMIN,
                ReportPermission::EXPORT_REPORT,
                Contexts::PROGRAM,
                (int)$channel_id,
                true
            )
        ) {
            return parent::getAdminError($this->theme_path);
        }
        $user_id = (int)$user_id;
        $user_details = $this->dim_user_service->getSpecificUserDetail((int)$user_id);
        $user_name = '';
        if (!is_null($user_details)) {
            $user_name = $user_details->user_name;
        }
        $channel_name = html_entity_decode($this->till_content_service->getChannelNameById((int)$channel_id));
        $result = $this->till_content_service->prepareUserIndChannelPerformance(
            (int)$channel_id,
            (int)$user_id,
            0,
            0
        );
        $user_perf = array_get($result, static::DATA, []);
        $avg_perf = array_get($result, static::AVG_DATA, []);
        $quiz_names = array_get($result, static::LABELS, []);
        $data = [];
        $data[] = [
            trans(
                'admin/reports.specific_user_ind_channel_perf_report',
                ['user_name' =>$user_name, 'channel' => $channel_name]
            )
        ];
        $data[] = [trans('admin/reports.duration'), trans('admin/reports.till_date')];
        $data[] = [
            trans('admin/reports.quiz_name'),
            trans('admin/reports.specific_user_score', ['user_name' => $user_name]),
            trans('admin/reports.avg_scores')
        ];
        foreach ($quiz_names as $key => $quiz_name) {
            $data[] = [
                $quiz_name,
                array_get($user_perf, $key, 0),
                array_get($avg_perf, $key, 0)
            ];
        }

        if (!empty($data)) {
            $filename =  trans(
                'admin/reports.specific_user_ind_channel_perf_report',
                ['user_name' =>$user_name, 'channel' => str_limit($channel_name, static::FILE_NAME_CHAR_LIMIT)]
            );
            $this->writeCsv($data, $filename);
        }
        exit();
    }

    /**
     * getCsvUserCompletionReport
     * @param  integer $user_id
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse export report as csv file
     */
    public function getCsvUserCompletionReport($user_id = 0)
    {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::EXPORT_REPORT)) {
            return parent::getAdminError($this->theme_path);
        }
        $user_id = (int)$user_id;
        $user_name = '';
        $permission_data = $this->permittedUserChannelLists($user_id, true, ReportPermission::EXPORT_REPORT);
        $channel_ids = array_get($permission_data, static::CONTENET__FEEDS, []);
        $user_name = array_get($permission_data, static::USER_NAME, '');
        $result = $this->till_content_service->prepareUserChannelCompletion($channel_ids, $user_id, 0, 0);
        $user_compl = array_get($result, static::DATA, []);
        $avg_compl = array_get($result, static::AVG_DATA, []);
        $channel_names = array_get($result, static::LABELS, []);
        $data = [];
        $data[] = [trans('admin/reports.specific_user_channel_compl_report', ['user_name' => $user_name])];
        $data[] = [trans('admin/reports.duration'), trans('admin/reports.till_date')];
        $data[] = [
            trans('admin/reports.course_name'),
            trans('admin/reports.specific_user_compl', ['user_name' => $user_name]),
            trans('admin/reports.avg_compl')
        ];
        foreach ($channel_names as $key => $channel_name) {
            $data[] = [
                $channel_name,
                array_get($user_compl, $key, 0),
                array_get($avg_compl, $key, 0)
            ];
        }
        if (!empty($data)) {
            $filename = trans('admin/reports.specific_user_channel_compl_report', ['user_name' => $user_name]);
            $this->writeCsv($data, $filename);
        }
        exit();
    }

    /**
     * getCsvUserSingleCompletionReport
     * @param  integer $channel_id
     * @param  integer $user_id
     * @return  \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse export report as csv file
     */
    public function getCsvUserSingleCompletionReport($channel_id = 0, $user_id = 0)
    {
        $admin_id = Auth::user()->uid;
        $channel_id = (int)$channel_id;
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::EXPORT_REPORT) &&
            !$this->roleService->hasPermission(
                $admin_id,
                ModuleEnum::REPORT,
                PermissionType::ADMIN,
                ReportPermission::EXPORT_REPORT,
                Contexts::PROGRAM,
                $channel_id,
                true
            )
        ) {
            return parent::getAdminError($this->theme_path);
        }
        $user_id = (int)$user_id;
        $channel_name = '';
        $user_name = '';
        $result = $this->till_content_service->prepareUserIndChannelCompletion((int)$channel_id, (int)$user_id, 0, 0);
        $user_details = $this->dim_user_service->getSpecificUserDetail((int)$user_id);
        $user_name = '';
        if (!is_null($user_details)) {
            $user_name = $user_details->user_name;
        }
        $channel_name = html_entity_decode($this->till_content_service->getChannelNameById((int)$channel_id));
        $data = [];
        $data[] = [
            trans(
                'admin/reports.specific_user_ind_channel_compl_report',
                ['user_name' =>$user_name, 'channel' => $channel_name]
            )
        ];
        $data[] = [trans('admin/reports.specific_user_channel_compl_report', ['user_name' => $user_name])];
        $data[] = [trans('admin/reports.duration'), trans('admin/reports.till_date')];
        $data[] = [
            trans('admin/reports.post_name'),
            trans('admin/reports.specific_user_compl', ['user_name' => $user_name]),
            trans('admin/reports.avg_compl')
        ];
        $user_compl = array_get($result, static::VALUES, []);
        $avg_compl = array_get($result, static::VALUES_AVG, []);
        $post_names = array_get($result, static::LABELS, []);
        foreach ($post_names as $key => $post_name) {
            $data[] = [
                $post_name,
                array_get($user_compl, $key, 0),
                array_get($avg_compl, $key, 0)
            ];
        }
        if (!empty($data)) {
            $filename = trans(
                'admin/reports.specific_user_ind_channel_compl_report',
                ['user_name' =>$user_name, 'channel' => str_limit($channel_name, static::FILE_NAME_CHAR_LIMIT)]
            );
            $this->writeCsv($data, $filename);
        }
        exit();
    }

    /**
     * getDirectQuizUser
     * @return blade/view
     */
    public function getDirectQuizUser()
    {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::VIEW_REPORT)) {
            return parent::getAdminError($this->theme_path);
        }
        $general = SiteSetting::module('General');
        $permission_chk = $this->permittedDirectQuizzes();
        if (is_null($permission_chk)) {
            return parent::getAdminError($this->theme_path);
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => static::CP,
            trans('admin/reports.admin_reports') => static::REPORTS,
            trans('admin/reports.direct_quiz_user') => '',
        ];
        $title = trans('admin/reports.direct_quiz_user');
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/reports.admin_reports');
        $this->layout->pageicon = static::BAR_CHART_FONT;
        $this->layout->pagedescription = trans('admin/reports.manage_reports');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with(static::MAIN_MENU, static::REPORT)
            ->with(static::SUB_MENU, static::ADMIN_REPORT);
        $this->layout->content = view('admin.theme.reports.directquizuser')
            ->with(static::TITLE, $title)
            ->with('general', $general);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * getAjaxDirectQuizUser
     * @param  integer $start
     * @return json
     */
    public function getAjaxDirectQuizUser($start = 0)
    {
        $finaloutput = [
            static::TITLE => trans('admin/reports.direct_quiz_user'),
            static::DATA => [],
            static::XAXIS => [],
            static::ID => [],
            static::AVG => 0,
        ];
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::VIEW_REPORT)) {
            return $finaloutput;
        }
        $permission_chk = $this->permittedDirectQuizzes();
        $quiz_ids = [];
        if (is_null($permission_chk)) {
            return $finaloutput;
        } else {
            $quiz_ids = $permission_chk;
        }
        $limit = config('app.limit_bars_chart');
        $start = $start * $limit;
        if ($start > 0) {
            $start--;
        }
        $result = $this->till_content_service->prepareDirectQuizPerformance(
            $quiz_ids,
            $start,
            $limit
        );
        $finaloutput = [
            static::TITLE => trans('admin/reports.direct_quiz_user'),
            static::DATA => array_get($result, static::DATA, []),
            static::XAXIS => array_get($result, static::XAXIS, []),
            static::ID => array_get($result, static::IDS, []),
            static::AVG => 0,
        ];

        return response()->json($finaloutput);
    }

    /**
     * getDirectQuizPerformanceByQuestion
     * @param  integer $quiz_id
     * @return blade/view
     */
    public function getDirectQuizPerformanceByQuestion($quiz_id = 0)
    {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::VIEW_REPORT)) {
            return parent::getAdminError($this->theme_path);
        }
        $edit_quiz_permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ASSESSMENT,
            PermissionType::ADMIN,
            AssessmentPermission::LIST_QUIZ,
            null,
            null,
            true
        );
        $edit_quiz_permission_data = get_permission_data($edit_quiz_permission_data_with_flag);
        if (!is_element_accessible($edit_quiz_permission_data, ElementType::ASSESSMENT, $quiz_id)) {
            return parent::getAdminError();
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => static::CP,
            trans('admin/reports.admin_reports') => static::REPORTS,
            trans('admin/reports.direct_quiz_performance_by_question') => '',
        ];
        if ($quiz_id > 0) {
            $result = $this->till_content_service->getQuizQuestionsDetails($quiz_id);
            $title = trans(
                'admin/reports.specific_quiz_perf_by_ques',
                ['quiz_name'=> array_get($result, static::QUIZ_NAME, '')]
            );
            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = trans('admin/reports.admin_reports');
            $this->layout->pageicon = static::BAR_CHART_FONT;
            $this->layout->pagedescription = trans('admin/reports.admin_reports');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with(static::MAIN_MENU, static::REPORT)
                ->with(static::SUB_MENU, static::ADMIN_REPORT);
            $this->layout->content = view('admin.theme.reports.directquizperformancebyquestion')
                ->with(static::TITLE, $title)
                ->with(static::QUES_LIST, array_get($result, static::QUIZ_TITLE, []))
                ->with(static::QUIZ_ID, $quiz_id)
                ->with(static::QUIZ_NAME, array_get($result, static::QUIZ_NAME, ''))
                ->with(static::TOTAL_MARK, array_get($result, static::TOTAL_MARK, 0))
                ->with(static::TOTAL_TIME, array_get($result, static::QUIZ_MAX_TIME, 0))
                ->with(static::QUIZ_IDS, array_get($result, static::QUIZ_IDS, []));
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }

    /**
     * postAjaxDirectQuizPerformanceByQuestion
     * @return json
     */
    public function postAjaxDirectQuizPerformanceByQuestion()
    {
        $start = 0;
        $limit = 10;
        $search = Input::get(static::SEARCH, '');
        $search_key = '';
        $order_by = Input::get(static::ORDER);
        $order_by_array = [static::CREATED_AT => static::DESC];
        $ques_ids = Input::get(static::QUIZ_IDS, []);
        $total_time = Input::get(static::TOTAL_TIME, 10);
        $temp_avg = [];
        if (isset($order_by[0][static::COLUMN]) && isset($order_by[0][static::DIR])) {
            if ($order_by[0][static::COLUMN] == '0') {
                $order_by_array = [static::USER_NAME => $order_by[0][static::DIR]];
            }
            if ($order_by[0][static::COLUMN] == '1') {
                $order_by_array = [static::SCORE => $order_by[0][static::DIR]];
            }
            if ($order_by[0][static::COLUMN] == '2') {
                $order_by_array = ['mark' => $order_by[0][static::DIR]];
            }
            if ($order_by[0][static::COLUMN] == '3') {
                $order_by_array = [static::TIME_TAKEN => $order_by[0][static::DIR]];
            }
        }
        if (isset($search[static::VALUE])) {
            $search_key = $search[static::VALUE];
        } else {
            $search_key = '';
        }
        if (preg_match('/^[0-9]+$/', Input::get(static::START, 0))) {
            $start = Input::get(static::START);
        }
        if (preg_match('/^[0-9]+$/', Input::get(static::LENGTH, 10))) {
            $limit = Input::get(static::LENGTH);
        }
        $status_filter = Input::get(static::STATUS_FILTER);
        $status_filter = strtoupper($status_filter);
        $quiz_id = (int)Input::get(static::QUIZ_ID, 0);
        $result = $this->till_content_service->prepareDirectQuizPerformanceByQues(
            $start,
            $limit,
            $order_by_array,
            $search_key,
            $quiz_id,
            $ques_ids
        );
        $finaldata = [
            static::RECORDS_TOTAL => array_get($result, static::RECORDS_TOTAL, 0),
            static::RECORDS_FILTERED => array_get($result, static::RECORDS_FILTERED, 0),
            static::DATA => array_get($result, static::DATA, []),
        ];
        return response()->json($finaldata);
    }

    /**
     * getCSVDirectQuizUser
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse export report as csv file
     */
    public function getCSVDirectQuizUser()
    {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::EXPORT_REPORT)) {
            return parent::getAdminError($this->theme_path);
        }
        $permission_chk = $this->permittedDirectQuizzes();
        $quiz_ids = [];
        if (is_null($permission_chk)) {
            return parent::getAdminError($this->theme_path);
        } else {
            $quiz_ids = $permission_chk;
        }
        $result = $this->till_content_service->prepareDirectQuizPerformance($quiz_ids, 0, 0);
        $data = [];
        $data[] = [trans('admin/reports.direct_quiz_user')];
        $data[] = [trans('admin/reports.duration'), 'Till date'];
        $data[] = [
            trans('admin/reports.quiz_id'),
            trans('admin/reports.quiz_name'),
            trans('admin/reports.avg_scores'),
        ];
        $quizzes_performance = array_get($result, static::DATA, []);
        $quizzes_name = array_get($result, static::XAXIS, []);
        $quizzes_id = array_get($result, static::IDS, []);
        foreach ($quizzes_performance as $key => $quiz_performance) {
            $data[] = [
                array_get($quizzes_id, $key, 0),
                array_get($quizzes_name, $key, 0),
                $quiz_performance
            ];
        }
        if (!empty($data)) {
            $filename = str_limit(trans('admin/reports.direct_quiz_user'), static::FILE_NAME_CHAR_LIMIT);
            $this->writeCsv($data, $filename);
        }
        exit;
    }

    /**
     * getCsvQuizPerformanceByQuestion
     * @param  string  $quiz_name
     * @param  integer $quiz_id
     * @param  integer $channel_id
     * @param  string  $total_mark
     * @param  string  $total_time
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse export report as csv file
     */
    public function getCsvQuizPerformanceByQuestion(
        $quiz_name = '',
        $quiz_id = 0,
        $channel_id = 0,
        $total_mark = '',
        $total_time = ''
    ) {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::EXPORT_REPORT)) {
            return parent::getAdminError($this->theme_path);
        }
        $ques_ids_str = Input::get(static::QUIZ_IDS, '');
        if (is_string($ques_ids_str)) {
            $ques_ids = explode(',', $ques_ids_str);
        } else {
            $ques_ids = [];
        }
        $ques_ids = array_map(static::INTVAL, $ques_ids);
        $result = $this->till_content_service->prepareQuizPerformanceByQuestion(
            0,
            0,
            [static::CREATED_AT => static::DESC],
            '',
            $quiz_id,
            $channel_id,
            $ques_ids
        );
        $ques_text = array_get($result, static::QUES_TEXT, '');
        $result_data = array_get($result, static::DATA, [[]]);
        $data = [];
        $quiz_name = str_limit($quiz_name, static::FILE_NAME_CHAR_LIMIT);
        $title = trans(
            'admin/reports.specific_quiz_perf_by_ques',
            ['quiz_name'=> array_get($result, static::QUIZ_NAME, '')]
        );
        $data[] = [$title];
        $header[] = trans('admin/reports.user_name');
        $header[] = trans('admin/reports.score_total_mark', ['total_mark' => $total_mark]);
        $header[] = trans('admin/reports.score');
        $header[] = trans('admin/reports.time_taken_value', ['time_taken' => $total_time]);
        foreach ($ques_text as $ques) {
            $header[] = htmlspecialchars_decode(trim(preg_replace("/&#?[a-z0-9]{2,8};/i", "", strip_tags($ques))), ENT_QUOTES);
        }
        $data[] = $header;
        $data = array_merge($data, $result_data);
        if (!empty($data)) {
            $filename = str_limit($title, static::FILE_NAME_CHAR_LIMIT);
            $this->writeCsv($data, $filename);
        }
        exit();
    }

    /**
     * getCsvDirectQuizPerformanceByQuestion
     * @param  string $quiz_name
     * @param  string $total_time
     * @param  string $total_mark
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse export report as csv file
     */
    public function getCsvDirectQuizPerformanceByQuestion($quiz_name = '', $total_time = '', $total_mark = '')
    {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::EXPORT_REPORT)) {
            return parent::getAdminError($this->theme_path);
        }
        $ques_ids_str = Input::get(static::QUIZ_IDS, '');
        if (is_string($ques_ids_str)) {
            $ques_ids = explode(',', $ques_ids_str);
        } else {
            $ques_ids = [];
        }
        $quiz_id = (int)Input::get(static::QUIZ_ID, 0);
        $ques_ids = array_map(static::INTVAL, $ques_ids);
        $result = $this->till_content_service->prepareDirectQuizPerformanceByQues(
            0,
            0,
            [static::CREATED_AT => static::DESC],
            '',
            $quiz_id,
            $ques_ids
        );
        $ques_text = array_get($result, static::QUES_TEXT, '');
        $result_data = array_get($result, static::DATA, [[]]);
        $data = [];
        $quiz_name = str_limit($quiz_name, static::FILE_NAME_CHAR_LIMIT);
        $title = trans(
            'admin/reports.specific_quiz_perf_by_ques',
            ['quiz_name'=> array_get($result, static::QUIZ_NAME, '')]
        );
        $data[] = [$title];
        $header[] = trans('admin/reports.user_name');
        $header[] = trans('admin/reports.score_total_mark', ['total_mark' => $total_mark]);
        $header[] = trans('admin/reports.score');
        $header[] = trans('admin/reports.time_taken_value', ['time_taken' => $total_time]);
        foreach ($ques_text as $ques) {
            $header[] = htmlspecialchars_decode(trim(preg_replace("/&#?[a-z0-9]{2,8};/i", "", strip_tags($ques))), ENT_QUOTES);
        }
        $data[] = $header;
        $data = array_merge($data, $result_data);
        if (!empty($data)) {
            $filename = str_limit($title, static::FILE_NAME_CHAR_LIMIT);
            $this->writeCsv($data, $filename);
        }
        exit();
    }

    /**
     * validateDates convert date string to timestamp and validate with threshold date limits
     * @param  string $start_date date string
     * @param  string $end_date date string
     * @return array Array of start and end date as timestamp
     */
    private function validateDates($start_date = '', $end_date = '')
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

    /**
     * writeCsv write operation into csv file format
     * @param  array $data What data need to write into file
     * @param  string $file_name Name of the file
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    private function writeCsv($data, $file_name)
    {
        try {
            if (!empty($data)) {
                $file_pointer = fopen('php://output', 'w');
                header('Content-type: application/csv');
                header('Content-Disposition: attachment; filename=' . $file_name . '.csv');
                foreach ($data as $row) {
                    fputcsv($file_pointer, $row);
                }
                exit;
            }
        } catch (Exception $e) {
            Log::error('write Csv' . $e->getMessage());
        }
    }

    /**
     * permittedChannelLists
     * @param  boolean $is_need_id_r_details
     * @param  integer  $permission
     * @return array
     */
    private function permittedChannelLists($is_need_id_r_details = true, $permission = ReportPermission::VIEW_REPORT)
    {
        //TODO: Roles and permissions needs to be implemented for course and batches.
        $admin_id = Auth::user()->uid;
        $list_channel_permission_info_with_flag = $this->roleService->hasPermission(
            $admin_id,
            ModuleEnum::REPORT,
            PermissionType::ADMIN,
            $permission,
            null,
            null,
            true
        );
        $list_permission_data = get_permission_data($list_channel_permission_info_with_flag);
        $channel_ids = has_system_level_access($list_permission_data)?
                                         [] : get_instance_ids($list_permission_data, Contexts::PROGRAM);
        if ($is_need_id_r_details) {
            $content_feeds = $channel_ids;
        } else {
            if (!empty($channel_ids)) {
                $content_feeds = $this->dim_channel_service->getChannelsDetails($channel_ids);
            } else {
                $content_feeds = $this->dim_channel_service->getChannelSlugsNameAndIds();
            }
        }
        return $content_feeds;
    }

    /**
     * permittedUserChannelLists
     * @param  integer  $user_id
     * @param  boolean $is_need_id_r_details
     * @param  string $permission
     * @return array
     */
    private function permittedUserChannelLists($user_id, $is_need_id_r_details = true, $permission = ReportPermission::VIEW_REPORT)
    {
        //TODO: Roles and permissions needs to be implemented for course and batches
        $admin_id = Auth::user()->uid;
        $list_channel_permission_info_with_flag = $this->roleService->hasPermission(
            $admin_id,
            ModuleEnum::REPORT,
            PermissionType::ADMIN,
            $permission,
            Contexts::PROGRAM,
            null,
            true
        );
        $list_permission_data = get_permission_data($list_channel_permission_info_with_flag);
        $channel_ids = has_system_level_access($list_permission_data)?
                                         [] : get_instance_ids($list_permission_data, Contexts::PROGRAM);
        $user_details = $this->dim_user_service->getSpecificUserDetail((int)$user_id);
        $user_name = '';
        $content_feeds = [];
        if (!is_null($user_details) && !empty($user_details->channel_ids)) {
            $user_name = $user_details->user_name;
            if (!empty($channel_ids)) {
                $channel_ids = array_intersect($user_details->channel_ids, $channel_ids);
            } else {
                $channel_ids = $user_details->channel_ids;
            }
            if ($is_need_id_r_details) {
                $content_feeds = $channel_ids;
            } else {
                $content_feeds = $this->dim_channel_service->getChannelsDetails($channel_ids);
            }
        }
        return [
            static::CONTENET__FEEDS => $content_feeds,
            static::USER_NAME => $user_name
        ];
    }

    /**
     * permittedDirectQuizzes
     * @return null\array
     */
    private function permittedDirectQuizzes()
    {
        $list_quiz_permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ASSESSMENT,
            PermissionType::ADMIN,
            AssessmentPermission::LIST_QUIZ,
            null,
            null,
            true
        );
        $has_list_quiz_permission = get_permission_flag($list_quiz_permission_data_with_flag);
        if (!$has_list_quiz_permission) {
            return null;
        }
        $quiz_ids = [];
        $list_quiz_permission_data = get_permission_data($list_quiz_permission_data_with_flag);
        if (!has_system_level_access($list_quiz_permission_data)) {
            $quiz_ids = get_user_accessible_elements(
                $list_quiz_permission_data,
                ElementType::ASSESSMENT
            );
        }
        return $quiz_ids;
    }

    /**
     * permittedAnnouncements
     * @return array
     */
    private function permittedAnnouncements()
    {
        $permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ANNOUNCEMENT,
            PermissionType::ADMIN,
            AnnouncementPermission::LIST_ANNOUNCEMENT,
            null,
            null,
            true
        );
        $list_permission_data = get_permission_data($permission_data_with_flag);
        if (!has_system_level_access($list_permission_data)) {
            $program_announcement_id = get_user_accessible_announcements($list_permission_data);
        } else {
            $program_announcement_id = [];
        }
    }

    /**
     * getChannelFullName
     * @return array
     */
    public function getChannelFullName()
    {
        $search_key = Input::get('query', '');
        $search_key = array_get($search_key, 'term', '');
        $channel_ids = $this->permittedChannelLists(true);
        $programs = $this->dim_channel_service->getChannelsFullName($search_key, $channel_ids, (int)config('app.limit_items_dropdown'));
        $data = [];
        foreach ($programs as $program) {
            if (isset($program->short_name) && $program->short_name != '') {
                $text = str_limit($program->title, (int)config('app.char_limit_dropdown')).'  ('.str_limit($program->subname, (int)config('app.char_limit_dropdown')).')';
            } else {
                $text = str_limit($program->title, (int)config('app.char_limit_dropdown'));
            }
            $data[] = [
                "id" => $program->channel_id,
                "text" => $text
            ];
        }
        return response()->json($data);
    }

    /**
     * getScormReports
     * @return blade/view
     */
    public function getScormReports()
    {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::VIEW_REPORT) || (SiteSetting::module('General', 'scorm_reports') != 'on')) {
            return parent::getAdminError($this->theme_path);
        }
        $general = SiteSetting::module('General');
        $crumbs = [
            trans('admin/dashboard.dashboard') => static::CP,
            trans('admin/reports.admin_reports') => static::REPORTS,
            trans('admin/reports.scorm_report') => '',
        ];
        $title = trans('admin/reports.announ_viewed');
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/reports.admin_reports');
        $this->layout->pageicon = static::BAR_CHART_FONT;
        $this->layout->pagedescription = trans('admin/reports.admin_reports');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar');
        $this->layout->content = view('admin.theme.reports.scorm_reports')
                                    ->with('general', $general);
        $this->layout->footer = view('admin.theme.common.footer');
    }


    /**
     * postAjaxScormReports
     * @param  integer $page
     * @param  integer $limit
     * @return json
     */
    public function postAjaxScormReports($page = 0, $limit = 10)
    {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::VIEW_REPORT) || (SiteSetting::module('General', 'scorm_reports') != 'on')) {
            return response()->json([]);
        }
        $assigned_items = $this->permittedItemsLists();

        if (!empty($assigned_items)) {
            $finalData = $this->scorm_activity_service->getScormDetailsForAdmin($assigned_items, $page, $limit);
        } elseif (empty($assigned_items) && is_admin_role(Auth::user()->role)) {
            $finalData = $this->scorm_activity_service->getScormDetailsForAdmin($assigned_items, $page, $limit);
        } else {
            $finalData = [];
        }
        return response()->json($finalData);
    }

    public function getExportScormReports()
    {
        $assigned_items = $this->permittedItemsLists();

        if (!empty($assigned_items)) {
            $scorm_details = $this->scorm_activity_service->getScormDetailsForAdmin($assigned_items, $no_set =  null, $limit = null);
        } elseif (empty($assigned_items) && is_admin_role(Auth::user()->role)) {
            $scorm_details = $this->scorm_activity_service->getScormDetailsForAdmin($assigned_items, $no_set =  null, $limit = null);
        } else {
            $scorm_details = [];
        }

        $filename = "ScormReports.csv";
        $fp = fopen('php://output', 'w');
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename=' . $filename);
        $data = [];
        if (!empty($scorm_details)) {
            $header[] = trans('admin/reports.name');
            $header[] = trans('admin/reports.completed_in_percentage');
            $header[] = trans('admin/reports.incomplete_in_percentage');
            $header[] = trans('admin/reports.not_started');
            $header[] = trans('admin/reports.time_spent');
            $header[] = trans('admin/reports.avg_scores');
            $header[] = trans('admin/reports.number_of_users');
            $data[] = $header;
            $data = array_merge($data, $scorm_details);
           
            foreach ($data as $row) {
                fputcsv($fp, $row);
            }
        } else {
            $data[] =  trans('reports.no_more_records');
            fputcsv($fp, $data);
        }
        fclose($fp);
        exit;
    }

    public function permittedItemsLists()
    {
        $list_media_permission_data_with_flag = $this->roleService->hasPermission(
            $this->request->user()->uid,
            ModuleEnum::DAMS,
            PermissionType::ADMIN,
            DAMSPermission::LIST_MEDIA,
            null,
            null,
            true
        );
        if (!get_permission_flag($list_media_permission_data_with_flag)) {
            return null;
        }

        $list_media_permission_data = get_permission_data($list_media_permission_data_with_flag);
        return has_system_level_access($list_media_permission_data)? [] : get_user_accessible_elements($list_media_permission_data, ElementType::MEDIA);
    }
}
