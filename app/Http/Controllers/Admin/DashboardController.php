<?php namespace App\Http\Controllers\Admin;

use App\Enums\Announcement\AnnouncementPermission;
use App\Enums\DAMS\DAMSPermission;
use App\Enums\Assessment\AssessmentPermission;
use App\Enums\Event\EventPermission;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\Program\ChannelPermission;
use App\Enums\Program\ElementType;
use App\Enums\Report\Reports;
use App\Enums\RolesAndPermissions\PermissionType;
use App\Enums\RolesAndPermissions\Contexts;
use App\Http\Controllers\AdminBaseController;
use App\Libraries\Helpers;
use App\Model\AccessRequest;
use App\Model\Announcement;
use App\Model\ChannelFaq;
use App\Model\Common;
use App\Model\Event;
use App\Model\MyActivity;
use App\Model\OverAllChannelAnalytic;
use App\Model\Packet;
use App\Model\PacketFaq;
use App\Model\Program;
use App\Model\QuestionBank;
use App\Model\Quiz;
use Request;
use App\Model\Role;
use App\Model\User;
use App\Model\UserGroup;
use App\Services\AccessRequest\IAccessRequestService;
use App\Services\Announcement\IAnnouncementService;
use App\Services\Event\IEventService;
use App\Services\MyActivity\IMyActivityService;
use App\Services\Post\IPostService;
use App\Services\PostFaq\IPostFaqService;
use App\Services\ProgramFaq\IProgramFaqService;
use App\Services\Program\IProgramService;
use App\Services\Quiz\IQuizService;
use App\Services\User\IUserService;
use App\Services\DAMS\IDAMsService;
use Auth;
use Carbon;
use Exception;
use File;
use Input;
use Timezone;
use URL;

class DashboardController extends AdminBaseController
{
    private $user_service;

    private $program_service;

    private $post_service;

    private $event_service;

    private $announce_service;

    private $myactivity_service;

    private $postfaq_service;

    private $program_faq_service;

    private $quiz_service;

    private $accessrequests_serv;

    private $dams_service;

    protected $layout = 'admin.theme.layout.master_layout';

    public function __construct(
        IUserService $user_service,
        IProgramService $program_service,
        IPostService $post_service,
        IEventService $event_service,
        IAnnouncementService $announce_service,
        IMyActivityService $myactivity_service,
        IProgramFaqService $program_faq_service,
        IPostFaqService $postfaq_service,
        IQuizService $quiz_service,
        IAccessRequestService $accessrequests_serv,
        IDAMsService $dams_service
    ) {
        parent::__construct();
        $this->user_service = $user_service;
        $this->program_service = $program_service;
        $this->post_service = $post_service;
        $this->event_service = $event_service;
        $this->announce_service = $announce_service;
        $this->myactivity_service = $myactivity_service;
        $this->program_faq_service = $program_faq_service;
        $this->postfaq_service = $postfaq_service;
        $this->quiz_service = $quiz_service;
        $this->accessrequests_serv = $accessrequests_serv;
        $this->dams_service = $dams_service;
    }

    public function getIndex()
    {
        $start_date = Input::get('start', '');
        $end_date = Input::get('end', '');
        $date_timestamp = Helpers::validateDates($start_date, $end_date);
        $this->layout->pagetitle = trans('admin/dashboard.dashboard');
        $this->layout->pageicon = 'fa fa-tachometer';
        $this->layout->pagedescription = trans('admin/dashboard.manage_user_and_content');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'dashboard');
        $this->layout->content = view('admin.theme.common.home')
            ->with('inventory', $this->getTotalInventory())
            ->with('start', $date_timestamp['start_date'])
            ->with('end', $date_timestamp['end_date']);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getStaticReportAjax()
    {
        $start_date = Input::get('start', '');
        $end_date = Input::get('end', '');
        $date_timestamp = Helpers::validateDates($start_date, $end_date);
        $start = $date_timestamp['start_date'];
        $end = $date_timestamp['end_date'];
        $statistic_ary = [];
        $program_ids = $announcement_ids = $event_ids = [];
        $date = [$start, $end];
        $program_ids = $this->permittedChannelLists();
        $announcement_ids = $this->permittedAnnouncementLists();
        $event_ids = $this->permittedEventLists();
        $item_ids = $this->permittedItemsLists();
        $statistic_ary['accessrequests'] = 0;
        $statistic_ary['PacketFaqunAns'] = 0;
        $statistic_ary['ChannelFaqunAns'] = 0;
        $statistic_ary['Packets'] = 0;
        $statistic_ary['newfeeds'] = 0;
        $statistic_ary['Announcements'] = 0;
        $statistic_ary['newevents'] = 0;
        $statistic_ary['newitems'] = 0;
        if (is_array($item_ids)) {
            $statistic_ary['newitems'] =  $this->dams_service->getItemsCount($item_ids, $date);
        }
        if (is_array($program_ids)) {
            if (!config('app.ecommerce')) {
                $statistic_ary['accessrequests'] =  $this->accessrequests_serv->getAccessRequestCount($program_ids, $date);
            }
            $statistic_ary['PacketFaqunAns'] = $this->postfaq_service->getUnAnsPostsQusCount($program_ids, $date);
            $statistic_ary['ChannelFaqunAns'] = $this->program_faq_service->getUnAnsChannelsQusCount($program_ids, $date);
            $statistic_ary['Packets'] = $this->post_service->getNewPostCount($program_ids, $date);
            $statistic_ary['newfeeds'] =  $this->program_service->getNewProgramsCount($program_ids, $date);
        }
        if (is_array($announcement_ids)) {
            $statistic_ary['Announcements'] = $this->announce_service->getNewAnnouncementCount($announcement_ids, $date);
        }
        if (is_array($event_ids)) {
            $statistic_ary['newevents'] = $this->event_service->getNewEventCount($event_ids, $date);
        }
        $statistic_ary['activefeeds'] =  $this->myactivity_service->getActiveFeedCount($program_ids, $date);
        $statistic_ary['users'] = $this->user_service->getNewUsersCount($date);
        $statistic_ary['activeuser'] = $this->myactivity_service->getActiveUsersCount($date);
        $finaldata = [
            "statistic_ary" => $statistic_ary
        ];
        return response()->json($finaldata);
    }

    public function getActiveUsersReport($startDateR = '', $endDateR = '')
    {
        try {
            if ($startDateR != '' || $endDateR != '') {
                $startDateObj = Carbon::createFromFormat('Y-m-d H', $startDateR . ' 0', Auth::user()->timezone);
                $endDateObj = Carbon::createFromFormat('Y-m-d H', $endDateR . ' 0', Auth::user()->timezone)->addDay();
                $startDate = $startDateObj->timestamp;
                $endDate = $endDateObj->timestamp;
            } else {
                $startDateObj = Carbon::yesterday(Auth::user()->timezone);
                $endDateObj = Carbon::today(Auth::user()->timezone);
                $startDate = $startDateObj->timestamp;
                $endDate = $endDateObj->timestamp;
            }
            $report = MyActivity::getUserGeneralActivity($startDate, $endDate);
            if (empty($report)) {
                return response()->json(['Please check date rande, If those are correct, then no user found']);
            }
            $uids = call_user_func_array('array_merge', $report);
            $userRecords = User::whereIn('uid', $uids)
                ->get(['firstname', 'uid', 'lastname', 'email', 'role', 'relations.active_usergroup_user_rel']);
            $userRecordsGrouped = $userRecords->groupBy('uid');
            $roles = Role::get(['rid', 'name'])->groupBy('rid');
            $ugs = UserGroup::get()->groupBy('ugid');
            $data = [];
            $data[] = ['User Activity'];
            $header[] = 'First name';
            $header[] = "Last name";
            $header[] = 'Email id';
            $header[] = 'Role';
            $header[] = 'User groups name';
            $header[] = 'Sign In Count';

            $data[] = $header;
            $reportRepet = MyActivity::getUserGeneralActivity($startDate, $endDate);
            if (!empty($reportRepet)) {
                $uidsLocal = call_user_func_array('array_merge', $reportRepet);
                if (empty($uidsLocal)) {
                    return response()->json(['No user found']);
                }
                $uidsLocal = array_unique($uidsLocal);
                foreach ($uidsLocal as $uid) {
                    if (is_null($userRecordsGrouped->get($uid))) {
                        continue;
                    }
                    $userActivityCount = MyActivity::getUserGeneralActivityCount($startDate, $endDate, $uid);
                    $tempRow = [];
                    $tempRow[] = isset($userRecordsGrouped->get($uid)[0]->firstname) ?
                        $userRecordsGrouped->get($uid)[0]->firstname : '';
                    $tempRow[] = isset($userRecordsGrouped->get($uid)[0]->lastname) ?
                        $userRecordsGrouped->get($uid)[0]->lastname : '';
                    $tempRow[] = isset($userRecordsGrouped->get($uid)[0]->email) ?
                        $userRecordsGrouped->get($uid)[0]->email : '';
                    $tempRow[] = isset($roles->get((int)$userRecordsGrouped->get($uid)[0]->role)[0]->name) ?
                        $roles->get((int)$userRecordsGrouped->get($uid)[0]->role)[0]->name : '';

                    if (isset($userRecordsGrouped->get($uid)[0]->relations['active_usergroup_user_rel']) &&
                        !empty($userRecordsGrouped->get($uid)[0]->relations['active_usergroup_user_rel'])
                    ) {
                        $groupName = [];
                        foreach ($userRecordsGrouped->get($uid)[0]->relations['active_usergroup_user_rel'] as $ugid) {
                            $groupName[] = $ugs->get((int)$ugid)[0]->usergroup_name;
                        }
                        $tempRow[] = implode(', ', $groupName);
                    } else {
                        $tempRow[] = '';
                    }
                    $tempRow[] = $userActivityCount;
                    $data[] = $tempRow;
                }
            }
            if (!empty($data)) {
                $filename = "dailyuseractivity.csv";
                $fp = fopen('php://output', 'w');
                header('Content-type: application/csv');
                header('Content-Disposition: attachment; filename=' . $filename);
                foreach ($data as $row) {
                    fputcsv($fp, $row);
                }
                exit;
            }
        } catch (Exception $e) {
            return response()->json(['No user found, Please try after some time']);
        }
    }

    public function getCourseCompletionReport($startDateR = '', $endDateR = '')
    {
        set_time_limit(300);
        try {
            if ($startDateR != '' || $endDateR != '') {
                $startDateObj = Carbon::createFromFormat('Y-m-d H', $startDateR . ' 0', Auth::user()->timezone);
                $endDateObj = Carbon::createFromFormat('Y-m-d H', $endDateR . ' 0', Auth::user()->timezone)->addDay();
                $startDate = $startDateObj->timestamp;
                $endDate = $endDateObj->timestamp;
                $report = OverAllChannelAnalytic::whereBetween('completed_at.0', [$startDate, $endDate])
                    ->where('completion', '>=', 100)
                    ->get(['user_id', 'channel_id', 'created_at', 'completed_at']);
            } else {
                $report = OverAllChannelAnalytic::where('completion', '>=', 100)
                      ->get(['user_id', 'channel_id', 'created_at', 'completed_at']);
            }
            if ($report->isEmpty()) {
                return response()->json(['Please check date range, If those are correct, then no records found']);
            }
            $header[] = 'Username';
            $header[] = 'First name';
            $header[] = 'Last name';
            $header[] = 'Email id';
            $header[] = 'User groups name';
            $header[] = 'Course Name';
            $header[] = 'Completion Date';
            $header[] = 'Status';
            $filename = "CourseCompletionReport.csv";
            $fp = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . $filename);
            fputcsv($fp, ['Course Completion Report']);
            fputcsv($fp, $header);
            foreach ($report as $rep) {
                $tempRow = [];
                $user = User::where('uid', '=', (int)$rep->user_id)->get()->first();
                if (empty($user)) {
                    continue;
                }
                $program = Program::where('program_id', '=', $rep->channel_id)->get()->first();
                if (empty($program)) {
                    continue;
                }
                $tempRow[] = $user->username;
                $tempRow[] = $user->firstname;
                $tempRow[] = $user->lastname;
                $tempRow[] = $user->email;
                if (isset($user->relations['active_usergroup_user_rel']) &&
                    !empty($user->relations['active_usergroup_user_rel'])
                ) {
                    $groupName = [];
                    foreach ($user->relations['active_usergroup_user_rel'] as $ugid) {
                        $group = UserGroup::where('ugid', '=', (int)$ugid)->get()->first();
                        $groupName[] = $group['usergroup_name'];
                    }
                    $tempRow[] = implode(', ', $groupName);
                } else {
                    $tempRow[] = '';
                }
                $tempRow[] = $program['program_title'];
                if (isset($rep['completed_at'][0])) {
                    $date = $rep['completed_at'][0];
                } elseif (isset($rep['updated_at'])) {
                    $date = $rep['updated_at'];
                } else {
                    $date = $rep['created_at'];
                }
                $tempRow[] = Timezone::convertFromUTC('@' . $date, Auth::user()->timezone);
                $tempRow[] = 'Completed';
                fputcsv($fp, $tempRow);
            }
            fclose($fp);
            exit;
        } catch (Exception $e) {
            return response()->json(['No records found, Please try after some time']);
        }
    }

    public function getCourseInprogressReport($startDateR = '', $endDateR = '')
    {
        set_time_limit(300);
        try {
            if ($startDateR != '' || $endDateR != '') {
                $startDateObj = Carbon::createFromFormat('Y-m-d H', $startDateR . ' 0', Auth::user()->timezone);
                $endDateObj = Carbon::createFromFormat('Y-m-d H', $endDateR . ' 0', Auth::user()->timezone)->addDay();
                $startDate = $startDateObj->timestamp;
                $endDate = $endDateObj->timestamp;
                $report = OverAllChannelAnalytic::orwhereBetween('created_at', [$startDate, $endDate])
                    ->orWhereBetween('updated_at', [$startDate, $endDate])
                    ->where('completion', '<', 100)
                    ->get(['user_id', 'channel_id', 'created_at']);
            } else {
                $report = OverAllChannelAnalytic::where('completion', '!=', 100)
                      ->get(['user_id', 'channel_id', 'created_at']);
            }
            if ($report->isEmpty()) {
                return response()->json(['Please check date range, If those are correct, then no records found']);
            }
            $header[] = 'Username';
            $header[] = 'First name';
            $header[] = 'Last name';
            $header[] = 'Email id';
            $header[] = 'User groups name';
            $header[] = 'Course Name';
            $header[] = 'Status';
            $filename = "CourseInprogressReport.csv";
            $fp = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . $filename);
            fputcsv($fp, ['Course In progress Report']);
            fputcsv($fp, $header);
            foreach ($report as $rep) {
                $tempRow = [];
                $user = User::where('uid', '=', (int)$rep->user_id)->get()->first();
                if (empty($user)) {
                    continue;
                }
                $program = Program::where('program_id', '=', $rep->channel_id)->get()->first();
                if (empty($program)) {
                    continue;
                }
                $tempRow[] = $user->username;
                $tempRow[] = $user->firstname;
                $tempRow[] = $user->lastname;
                $tempRow[] = $user->email;
                if (isset($user->relations['active_usergroup_user_rel']) &&
                    !empty($user->relations['active_usergroup_user_rel'])
                ) {
                    $groupName = [];
                    foreach ($user->relations['active_usergroup_user_rel'] as $ugid) {
                        $group = UserGroup::where('ugid', '=', (int)$ugid)->get()->first();
                        $groupName[] = $group['usergroup_name'];
                    }
                    $tempRow[] = implode(', ', $groupName);
                } else {
                    $tempRow[] = '';
                }
                $tempRow[] = $program['program_title'];
                $tempRow[] = 'In Progress';
                fputcsv($fp, $tempRow);
            }
            exit;
        } catch (Exception $e) {
            return response()->json(['No records found, Please try after some time']);
        }
    }

    public function getUserChannelDureationReport($startDateR = '', $endDateR = '')
    {
        set_time_limit(300);
        try {
            if ($startDateR != '' || $endDateR != '') {
                $startDateObj = Carbon::createFromFormat('Y-m-d H', $startDateR . ' 0', Auth::user()->timezone);
                $endDateObj = Carbon::createFromFormat('Y-m-d H', $endDateR . ' 0', Auth::user()->timezone)->addDay();
                $startDate = $startDateObj->timestamp;
                $endDate = $endDateObj->timestamp;
                $reports = OverAllChannelAnalytic::getUserChannelActivityDuration($startDate, $endDate);
            } else {
                $reports = OverAllChannelAnalytic::getUserChannelActivityDuration(0, 0);
            }
            $data = [];
            $data[] = ['User Channel Dureation Report'];
            $header[] = 'First name';
            $header[] = 'Last name';
            $header[] = 'Channel name';
            $header[] = 'Actual duration(In hours)';
            $header[] = 'Actual duration(In days)';
            $data[] = $header;
            if (!$reports->isEmpty()) {
                $user_ids = $reports->lists('user_id')->unique();
                $channel_ids = $reports->lists('channel_id')->unique();
                $user_details = User::whereIn('uid', $user_ids)
                    ->get(['firstname', 'uid', 'lastname',])->keyBy('uid');
                $channel_details = Program::whereIn('program_id', $channel_ids)
                    ->get(['program_id', 'program_title'])->keyBy('program_id');
                foreach ($reports as $report) {
                    $user_id = $report->user_id;
                    $channel_id = $report->channel_id;
                    if (!$user_details->has($user_id) || !$channel_details->has($channel_id)) {
                        continue;
                    }
                    if (is_null($report->updated_at)) {
                        $duration = 'one time';
                        $duration_day = 'one day';
                    } else {
                        $start = Carbon::createFromTimestamp($report->created_at);
                        $end = Carbon::createFromTimestamp($report->updated_at);
                        $duration = $start->diffInHours($end);
                        $duration_day = $start->diffInDays($end);
                    }
                    $data[] = [
                        $user_details->get($user_id)->firstname,
                        $user_details->get($user_id)->lastname,
                        $channel_details->get($channel_id)->program_title,
                        $duration,
                        $duration_day
                    ];
                }
                if (!empty($data)) {
                    $filename = "UserChannelDureationReport.csv";
                    $fp = fopen('php://output', 'w');
                    header('Content-type: application/csv');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    foreach ($data as $row) {
                        fputcsv($fp, $row);
                    }
                    exit;
                }
            } else {
                return response()->json(['Please check date range, If those are correct, then no records found']);
            }
        } catch (Exception $e) {
            return response()->json(['Please try after some time'. $e->getMessage()]);
        }
    }

    public function permittedChannelLists()
    {
        $list_channel_permission_info_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::CHANNEL,
            PermissionType::ADMIN,
            ChannelPermission::LIST_CHANNEL,
            Contexts::PROGRAM,
            null,
            true
        );
        $has_list_permission = get_permission_flag($list_channel_permission_info_with_flag);
        if (!$has_list_permission) {
            return null;
        } else {
              $list_permission_data = get_permission_data($list_channel_permission_info_with_flag);
            return has_system_level_access($list_permission_data)?
                [] : get_instance_ids($list_permission_data, Contexts::PROGRAM);
        }
    }

    public function permittedAnnouncementLists()
    {
        $list_permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ANNOUNCEMENT,
            PermissionType::ADMIN,
            AnnouncementPermission::LIST_ANNOUNCEMENT,
            null,
            null,
            true
        );

        $list_permission_data = get_permission_data($list_permission_data_with_flag);
        return has_system_level_access($list_permission_data)?
            [] : (!empty(get_user_accessible_announcements($list_permission_data))?
                     get_user_accessible_announcements($list_permission_data) : null);
    }

    public function permittedEventLists()
    {
        $permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::EVENT,
            PermissionType::ADMIN,
            EventPermission::LIST_EVENT,
            null,
            null,
            true
        );
        if (!get_permission_flag($permission_data_with_flag)) {
            return null;
        }
        $list_event_permission_data = get_permission_data($permission_data_with_flag);
        return has_system_level_access($list_event_permission_data)?
            [] : (!empty(get_user_accessible_elements($list_event_permission_data, ElementType::EVENT)) ? get_user_accessible_elements($list_event_permission_data, ElementType::EVENT) : null);
    }

    public function permittedQuizLists()
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
        if (!get_permission_flag($list_quiz_permission_data_with_flag)) {
                    return null;
        }

        $list_quiz_permission_data = get_permission_data($list_quiz_permission_data_with_flag);
        return has_system_level_access($list_quiz_permission_data)? [] : get_user_accessible_elements(
            $list_quiz_permission_data,
            ElementType::ASSESSMENT
        );
    }
   
    public function permittedPostsLists()
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
        return has_system_level_access($list_media_permission_data)? [] : get_user_program_posts($list_media_permission_data, ElementType::MEDIA);
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
        return has_system_level_access($list_media_permission_data)?
            [] : (!empty(get_user_accessible_elements($list_media_permission_data, ElementType::MEDIA))?
                get_user_accessible_elements($list_media_permission_data, ElementType::MEDIA) : null);
    }

    /* All the below method to display the data in table format */

    public function getNewPosts($start_date = '', $end_date = '')
    {
        $date_timestamp = Helpers::validateDates($start_date, $end_date);
        $data = [];
        $start = 0;
        $limit = config('app.limit_items_dropdown');
        $program_ids = $this->permittedChannelLists();
        $post_details = $this->post_service->getNewPosts(
            $program_ids,
            [$date_timestamp['start_date'], $date_timestamp['end_date']],
            $start,
            $limit
        );
        $feed_slugs = $post_details->pluck('feed_slug')->unique()->toArray();
        if (!empty($feed_slugs)) {
            $programs_details = $this->program_service->getProgramsBySlugs(
                $feed_slugs,
                ['program_title', 'program_slug']
            )->pluck('program_title', 'program_slug');
            $post_details->each(function ($item) use (&$data, $programs_details) {
                $channel_name = $programs_details->get($item->feed_slug);
                $data[]  = [
                    $channel_name,
                    $item->packet_title
                ];
            });
        }

        if (Request::Ajax()) {
            return response()->json([
                'message' => $data,
                'status' => "success"
            ]);
        }
    }

    public function getNewPostExport($start_date = '', $end_date = '')
    {
        try {
            $date_timestamp = Helpers::validateDates($start_date, $end_date);
            $program_ids = $this->permittedChannelLists();
            $count = $this->post_service->getNewPostCount(
                $program_ids,
                [$date_timestamp['start_date'], $date_timestamp['end_date']]
            );
            $header[] = trans('admin/dashboard.program_name');
            $header[] = trans('admin/dashboard.posts');
            $filename = "New-PostsExport.csv";
            $title = ["Report Title: New Posts Export"];
            $file_pointer = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . str_replace(" ", "", $filename));
            fputcsv($file_pointer, $title);
            fputcsv($file_pointer, [
                trans('admin/reports.selected_date_ranges'),
                Timezone::convertFromUTC('@' . $date_timestamp['start_date'], Auth::user()->timezone) .
                ' to ' .
                Timezone::convertFromUTC('@' . $date_timestamp['end_date'], Auth::user()->timezone)
            ]);
            fputcsv($file_pointer, $header);
            $batch_limit = config('app.bulk_insert_limit');
            $total_batchs = intval($count / $batch_limit);
            $record_set = 0;
            do {
                $start = $record_set * $batch_limit;
                $post_details = $this->post_service->getNewPosts(
                    $program_ids,
                    [$date_timestamp['start_date'], $date_timestamp['end_date']],
                    $start,
                    $batch_limit
                );
                $feed_slugs = $post_details->pluck('feed_slug')->unique()->toArray();
                if (!empty($feed_slugs)) {
                    $programs_details = $this->program_service->getProgramsBySlugs(
                        $feed_slugs,
                        ['program_title', 'program_slug']
                    )->pluck('program_title', 'program_slug');
                    $post_details->each(function ($item) use ($programs_details, &$tempRow, &$file_pointer) {
                        $channel_name = $programs_details->get($item->feed_slug);
                        fputcsv($file_pointer, [
                            $channel_name,
                            $item->packet_title
                        ]);
                    });
                }
                $record_set++;
            } while ($record_set <= $total_batchs);
            fclose($file_pointer);
            exit;
        } catch (Exception $e) {
            return response()->json(['No records found, Please try after some time']);
        }
    }

    public function getNewItems($start_date = '', $end_date = '')
    {
        $date_timestamp = Helpers::validateDates($start_date, $end_date);
        $data = [];
        $start = 0;
        $limit = config('app.limit_items_dropdown');
        $item_ids = $this->permittedItemsLists();
        if (!is_null($item_ids)) {
            $this->dams_service->getNewItems(
                $item_ids,
                [$date_timestamp['start_date'], $date_timestamp['end_date']],
                $start,
                $limit
            )->each(function ($item) use (&$data) {
                $data[]  = [
                    $item->name,
                    $item->type
                ];
            });
        }

        if (Request::Ajax()) {
            return response()->json([
              'message' => $data,
              'status' => "success"
            ]);
        }
    }

    public function getNewItemsExport($start_date = '', $end_date = '')
    {
        try {
            $date_timestamp = Helpers::validateDates($start_date, $end_date);
            $item_ids = $this->permittedItemsLists();
            $count = $this->dams_service->getItemsCount($item_ids, [$date_timestamp['start_date'], $date_timestamp['end_date']]);
            $header[] = trans('admin/dashboard.asset');
            $header[] = trans('admin/dashboard.assets_type');
            $filename = "New-Digital-AssetsExport.csv";
            $title = ["Report Title:","New Digital Assets Export"];
            $file_pointer = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . str_replace(" ", "", $filename));
            fputcsv($file_pointer, $title);
            fputcsv($file_pointer, [
                trans('admin/reports.selected_date_ranges'),
                Timezone::convertFromUTC('@' . $date_timestamp['start_date'], Auth::user()->timezone) .
                ' to ' .
                Timezone::convertFromUTC('@' . $date_timestamp['end_date'], Auth::user()->timezone)
            ]);
            fputcsv($file_pointer, $header);
            $batch_limit = config('app.bulk_insert_limit');
            $total_batchs = intval($count / $batch_limit);
            $record_set = 0;
            do {
                $start = $record_set * $batch_limit;
                $media_details = $this->dams_service->getNewItems(
                    $item_ids,
                    [$date_timestamp['start_date'], $date_timestamp['end_date']],
                    $start,
                    $batch_limit
                );
                foreach ($media_details as $media) {
                    $tempRow = [];
                    $tempRow[] = $media->name;
                    $tempRow[]  = $media->type;
                    fputcsv($file_pointer, $tempRow);
                }
                $record_set++;
            } while ($record_set <= $total_batchs);
            fclose($file_pointer);
            exit;
        } catch (Exception $e) {
            return response()->json(['No records found, Please try after some time']);
        }
    }

    public function getNewUsers($start_date = '', $end_date = '')
    {
        $date_timestamp = Helpers::validateDates($start_date, $end_date);
        $data = [];
        $start = 0;
        $limit = config('app.limit_items_dropdown');
        $this->user_service->getNewUsers(
            [$date_timestamp['start_date'], $date_timestamp['end_date']],
            $start,
            $limit
        )->each(function ($item) use (&$data) {
            $data[]  = [
                $item->fullname,
                $item->username,
                $item->email
            ];
        });

        if (Request::Ajax()) {
            return response()->json([
              'message' => $data,
              'status' => "success"
            ]);
        }
    }

    public function getNewUsersExport($start_date = '', $end_date = '')
    {
        try {
            $date_timestamp = Helpers::validateDates($start_date, $end_date);
            $count = $this->user_service->getNewUsersCount([$date_timestamp['start_date'], $date_timestamp['end_date']]);
            $header[] = trans('admin/dashboard.user_fullname');
            $header[] = trans('admin/dashboard.username');
            $header[] = trans('admin/dashboard.user_email');
            $filename = "New-UsersExport.csv";
            $title = ["Report Title: New Users Export"];
            $file_pointer = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . str_replace(" ", "", $filename));
            fputcsv($file_pointer, $title);
            fputcsv($file_pointer, [
                trans('admin/reports.selected_date_ranges'),
                Timezone::convertFromUTC('@' . $date_timestamp['start_date'], Auth::user()->timezone) .
                ' to ' .
                Timezone::convertFromUTC('@' . $date_timestamp['end_date'], Auth::user()->timezone)
            ]);
            fputcsv($file_pointer, $header);
            $batch_limit = config('app.bulk_insert_limit');
            $total_batchs = intval($count / $batch_limit);
            $record_set = 0;
            do {
                $start = $record_set * $batch_limit;
                $user_details = $this->user_service->getNewUsers(
                    [$date_timestamp['start_date'], $date_timestamp['end_date']],
                    $start,
                    $batch_limit
                );
                foreach ($user_details as $user) {
                    $tempRow = [];
                    $tempRow[] = $user->fullname;
                    $tempRow[] = $user->username;
                    $tempRow[] = $user->email;
                    fputcsv($file_pointer, $tempRow);
                }
                $record_set++;
            } while ($record_set <= $total_batchs);
            fclose($file_pointer);
            exit;
        } catch (Exception $e) {
            return response()->json(['No records found, Please try after some time']);
        }
    }

    public function getAccessRequest($start_date = '', $end_date = '')
    {
        if (!config('app.ecommerce')) {
            $date_timestamp = Helpers::validateDates($start_date, $end_date);
            $data = [];
            $start = 0;
            $limit = config('app.limit_items_dropdown');
            $program_ids = $this->permittedChannelLists();
            $this->accessrequests_serv->getAccessRequests(
                $program_ids,
                [$date_timestamp['start_date'], $date_timestamp['end_date']],
                $start,
                $limit
            )->each(function ($item) use (&$data) {
                $data[]  = [
                    $item->program_title,
                    $item->user_name
                ];
            });
        
            if (Request::Ajax()) {
                return response()->json([
                  'message' => $data,
                  'status' => "success"
                ]);
            }
        } else {
            return response()->json([
                'message' =>  "No record found",
                'status' => "failed"
            ]);
        }
    }

    public function getAccessRequestExport($start_date = '', $end_date = '')
    {
        try {
            $date_timestamp = Helpers::validateDates($start_date, $end_date);
            $program_ids = $this->permittedChannelLists();
            $count = $this->accessrequests_serv->getAccessRequestCount(
                $program_ids,
                [$date_timestamp['start_date'],$date_timestamp['end_date']]
            );
            $header[] = trans('admin/dashboard.channel_name');
            $header[] = trans('admin/dashboard.list_of_users');
            $filename = "Access RequeExport.csv";
            $title = ["Report Title: Access Request Export"];
            $batch_limit = config('app.bulk_insert_limit');
            $file_pointer = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . str_replace(" ", "", $filename));
            fputcsv($file_pointer, $title);
            fputcsv($file_pointer, [
                    trans('admin/reports.selected_date_ranges'),
                    Timezone::convertFromUTC('@' . $date_timestamp['start_date'], Auth::user()->timezone) .
                    ' to ' .
                    Timezone::convertFromUTC('@' . $date_timestamp['end_date'], Auth::user()->timezone)
            ]);
            fputcsv($file_pointer, $header);
            $total_batches = intval($count / $batch_limit);
            $record_set = 0;
            do {
                $start = $record_set * $batch_limit;
                $this->accessrequests_serv->getAccessRequests(
                    $program_ids,
                    [$date_timestamp['start_date'], $date_timestamp['end_date']],
                    $start,
                    $batch_limit
                )->each(function ($item) use (&$file_pointer) {
                    fputcsv($file_pointer, [
                        $item->program_title,
                        $item->user_name
                    ]);
                });
                $record_set++;
            } while ($record_set <= $total_batches);
            fclose($file_pointer);
            exit;
        } catch (Exception $e) {
            return response()->json(['No records found, Please try after some time']);
        }
    }

    public function getNewAnnouncements($start_date = '', $end_date = '')
    {
        $date_timestamp = Helpers::validateDates($start_date, $end_date);
        $data = [];
        $start = 0;
        $limit = config('app.limit_items_dropdown');
        $announcement_ids = $this->permittedAnnouncementLists();
        if (!is_null($announcement_ids)) {
            $this->announce_service->getNewAnnouncements(
                $announcement_ids,
                [$date_timestamp['start_date'], $date_timestamp['end_date']],
                $start,
                $limit
            )->each(function ($item) use (&$data) {
                $data[]  = [
                    $item->announcement_title,
                    $item->announcement_for
                ];
            });
        }
        
        if (Request::Ajax()) {
            return response()->json([
              'message' => $data,
              'status' => "success"
            ]);
        }
    }

    public function getNewAnnouncementsExport($start_date = '', $end_date = '')
    {
        try {
            $date_timestamp = Helpers::validateDates($start_date, $end_date);
            $announcement_ids = $this->permittedAnnouncementLists();
            $count = $this->announce_service->getNewAnnouncementCount(
                $announcement_ids,
                [$date_timestamp['start_date'], $date_timestamp['end_date']]
            );
            $header[] = trans('admin/dashboard.announcement_name');
            $header[] = trans('admin/dashboard.send_to');
            $filename = "New-AnnouncementsExport.csv";
            $title = ["Report Title: New Announcements Export"];
            $batch_limit = config('app.bulk_insert_limit');
            $file_pointer = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . str_replace(" ", "", $filename));
            fputcsv($file_pointer, $title);
            fputcsv($file_pointer, [
                    trans('admin/reports.selected_date_ranges'),
                    Timezone::convertFromUTC('@' . $date_timestamp['start_date'], Auth::user()->timezone) .
                    ' to ' .
                    Timezone::convertFromUTC('@' . $date_timestamp['end_date'], Auth::user()->timezone)
            ]);
            fputcsv($file_pointer, $header);
            $total_batches = intval($count / $batch_limit);
            $record_set = 0;
            do {
                $start = $record_set * $batch_limit;
                $this->announce_service->getNewAnnouncements(
                    $announcement_ids,
                    [$date_timestamp['start_date'], $date_timestamp['end_date']],
                    $start,
                    $batch_limit
                )->each(function ($item) use (&$file_pointer) {
                    fputcsv($file_pointer, [
                        $item->announcement_title,
                        $item->announcement_for
                    ]);
                });
                $record_set++;
            } while ($record_set <= $total_batches);
            fclose($file_pointer);
            exit;
        } catch (Exception $e) {
            return response()->json(['No records found, Please try after some time']);
        }
    }

    public function getActiveUsers($start_date = '', $end_date = '')
    {
        $date_timestamp = Helpers::validateDates($start_date, $end_date);
        $data = [];
        $start = 0;
        $limit = config('app.limit_items_dropdown');
        $usre_ids = $this->myactivity_service->getActiveUsers(
            [$date_timestamp['start_date'], $date_timestamp['end_date']],
            $limit
        );
        $uids= array_flatten($usre_ids);
        $this->user_service->getUsersDetails(['user_ids' => $uids], $start, $limit)
            ->each(function ($item) use (&$data) {
                $data[]  = [
                    $item->fullname,
                    $item->username,
                    $item->email
                ];
            });
        
        if (Request::Ajax()) {
            return response()->json([
              'message' => $data,
              'status' => "success"
            ]);
        }
    }

    public function getActiveUsersExport($start_date = '', $end_date = '')
    {
        try {
            $date_timestamp = Helpers::validateDates($start_date, $end_date);
            $count = $this->myactivity_service->getActiveUsersCount([$date_timestamp['start_date'], $date_timestamp['end_date']]);
            $header[] = trans('admin/dashboard.user_fullname');
            $header[] = trans('admin/dashboard.username');
            $header[] = trans('admin/dashboard.user_email');
            $filename = "Active-UsersExport.csv";
            $title = ["Report Title: Active Users Export"];
            $file_pointer = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . str_replace(" ", "", $filename));
            fputcsv($file_pointer, $title);
            fputcsv($file_pointer, [
                    trans('admin/reports.selected_date_ranges'),
                    Timezone::convertFromUTC('@' . $date_timestamp['start_date'], Auth::user()->timezone) .
                    ' to ' .
                    Timezone::convertFromUTC('@' . $date_timestamp['end_date'], Auth::user()->timezone)
            ]);
            fputcsv($file_pointer, $header);
            $batch_limit = config('app.bulk_insert_limit');
            $total_batches = intval($count / $batch_limit);
            $record_set = 0;
            $user_ids = $this->myactivity_service->getActiveUsers(
                [$date_timestamp['start_date'], $date_timestamp['end_date']],
                $batch_limit
            );
            $uids= array_flatten($user_ids);
            do {
                $start = $record_set * $batch_limit;
                $user_details = $this->user_service->getUsersDetails(['user_ids' => $uids], $start, $batch_limit);
                foreach ($user_details as $user) {
                     $tempRow = [];
                     $tempRow[] = $user->username;
                     $tempRow[] = $user->fullname;
                     $tempRow[] = $user->email;
                     fputcsv($file_pointer, $tempRow);
                }
                $record_set++;
            } while ($record_set <= $total_batches);
            fclose($file_pointer);
            exit;
        } catch (Exception $e) {
            return response()->json(['No records found, Please try after some time']);
        }
    }

    public function getNewEvents($start_date = '', $end_date = '')
    {
        $date_timestamp = Helpers::validateDates($start_date, $end_date);
        $data = [];
        $start = 0;
        $limit = config('app.limit_items_dropdown');
        $event_ids = $this->permittedEventLists();
        $this->event_service->getNewEvents(
            $event_ids,
            [$date_timestamp['start_date'], $date_timestamp['end_date']],
            $start,
            $limit
        )->each(function ($item) use (&$data) {
            $data[]  = [
                $item->event_name,
                $item->event_type
            ];
        });

        if (Request::Ajax()) {
            return response()->json([
              'message' => $data,
              'status' => "success"
            ]);
        }
    }

    public function getNewEventsExport($start_date = '', $end_date = '')
    {
        try {
            $date_timestamp = Helpers::validateDates($start_date, $end_date);
            $event_ids = $this->permittedEventLists();
            $count = $this->event_service->getNewEventCount(
                $event_ids,
                [$date_timestamp['start_date'], $date_timestamp['end_date']]
            );
            $header[] = trans('admin/dashboard.event_name');
            $header[] = trans('admin/dashboard.event_type');
            $filename = "New-EventsExport.csv";
            $title = ["Report Title: New Events Export"];
            $file_pointer = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . str_replace(" ", "", $filename));
            fputcsv($file_pointer, $title);
            fputcsv($file_pointer, [
                    trans('admin/reports.selected_date_ranges'),
                    Timezone::convertFromUTC('@' . $date_timestamp['start_date'], Auth::user()->timezone) .
                    ' to ' .
                    Timezone::convertFromUTC('@' . $date_timestamp['end_date'], Auth::user()->timezone)
            ]);
            fputcsv($file_pointer, $header);
            $batch_limit = config('app.bulk_insert_limit');
            $total_batches = intval($count / $batch_limit);
            $record_set = 0;
            do {
                $start = $record_set * $batch_limit;
                $this->event_service->getNewEvents(
                    $event_ids,
                    [$date_timestamp['start_date'], $date_timestamp['end_date']],
                    $start,
                    $batch_limit
                )->each(function ($item) use (&$file_pointer) {
                    fputcsv($file_pointer, [
                        $item->event_name,
                        $item->event_type
                    ]);
                });
                $record_set++;
            } while ($record_set <= $total_batches);
            fclose($file_pointer);
            exit;
        } catch (Exception $e) {
            return response()->json(['No records found, Please try after some time']);
        }
    }

    public function getNewChannels($start_date = '', $end_date = '')
    {
        $date_timestamp = Helpers::validateDates($start_date, $end_date);
        $data = [];
        $start = 0;
        $limit = config('app.limit_items_dropdown');
        $program_ids = $this->permittedChannelLists();
        $this->program_service->getNewPrograms(
            $program_ids,
            [$date_timestamp['start_date'], $date_timestamp['end_date']],
            $start,
            $limit
        )->each(function ($item) use (&$data) {
            $data[]  = [
                $item->program_title,
                Carbon::parse($item->created_at)->format('d/m/Y')
            ];
        });

        if (Request::Ajax()) {
            return response()->json([
              'message' => $data,
              'status' => "success"
            ]);
        }
    }

    public function getNewChannelsExport($start_date = '', $end_date = '')
    {
        try {
            $date_timestamp = Helpers::validateDates($start_date, $end_date);
            $start = 0;
            $program_ids = $this->permittedChannelLists();
            $count = $this->program_service->getNewProgramsCount(
                $program_ids,
                [$date_timestamp['start_date'], $date_timestamp['end_date']]
            );
            $header[] = trans('admin/dashboard.channel_name');
            $header[] = trans('admin/dashboard.created_date');
            $filename = "New-ChannelsExport.csv";
            $title = ["Report Title: New Channels Export"];
            $batch_limit = config('app.bulk_insert_limit');
            $file_pointer = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . str_replace(" ", "", $filename));
            fputcsv($file_pointer, $title);
            fputcsv($file_pointer, [
                    trans('admin/reports.selected_date_ranges'),
                    Timezone::convertFromUTC('@' . $date_timestamp['start_date'], Auth::user()->timezone) .
                    ' to ' .
                    Timezone::convertFromUTC('@' . $date_timestamp['end_date'], Auth::user()->timezone)
            ]);
            fputcsv($file_pointer, $header);
            $total_batches = intval($count / $batch_limit);
            $record_set = 0;
            do {
                $start = $record_set * $batch_limit;
                $this->program_service->getNewPrograms(
                    $program_ids,
                    [$date_timestamp['start_date'], $date_timestamp['end_date']],
                    $start,
                    $batch_limit
                )->each(function ($item) use (&$file_pointer) {
                    fputcsv($file_pointer, [
                        $item->program_title,
                        Carbon::parse($item->created_at)->format('d/m/Y')
                    ]);
                });
                $record_set++;
            } while ($record_set <= $total_batches);
            fclose($file_pointer);
            exit;
        } catch (Exception $e) {
            return response()->json(['No records found, Please try after some time']);
        }
    }

    public function getActiveChannels($start_date = '', $end_date = '')
    {
        $date_timestamp = Helpers::validateDates($start_date, $end_date);
        $data = [];
        $start = 0;
        $limit = config('app.limit_items_dropdown');
        $all_program_ids = $this->myactivity_service->getActiveFeeds(
            [$date_timestamp['start_date'], $date_timestamp['end_date']],
            $limit
        );
        $all_program_ids = array_flatten($all_program_ids);
        $assigned_program_ids = $this->permittedChannelLists();

        if (!empty($assigned_program_ids)) {
            $program_ids = array_intersect($assigned_program_ids, $all_program_ids);
        } else {
            $program_ids = $all_program_ids;
        }

        if ($program_ids) {
            $data = $this->program_service->getActiveProgramsDetails(
                $program_ids,
                $start,
                $limit
            );
        }
        if (Request::Ajax()) {
            return response()->json([
              'message' => $data,
              'status' => "success"
            ]);
        }
    }
    public function getActiveChannelsExport($start_date = '', $end_date = '')
    {
        try {
            $date_timestamp = Helpers::validateDates($start_date, $end_date);
            $start = 0;
            $limit = config('app.limit_items_dropdown');
            $program_ids = $this->myactivity_service->getActiveFeeds(
                [$date_timestamp['start_date'], $date_timestamp['end_date']],
                $limit
            );
            $program_ids = array_flatten($program_ids);
            $count = $this->myactivity_service->getActiveFeedCount($program_ids, [$date_timestamp['start_date'], $date_timestamp['end_date']]);
            $header[] = trans('admin/dashboard.channel_name');
            $header[] = trans('admin/dashboard.created_date');
            $filename = "Active-ChannelsExport.csv";
            $title = ["Report Title: Active Channels Export"];
            $batch_limit = config('app.bulk_insert_limit');
            $file_pointer = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . str_replace(" ", "", $filename));
            fputcsv($file_pointer, $title);
            fputcsv($file_pointer, [
                    trans('admin/reports.selected_date_ranges'),
                    Timezone::convertFromUTC('@' . $date_timestamp['start_date'], Auth::user()->timezone) .
                    ' to ' .
                    Timezone::convertFromUTC('@' . $date_timestamp['end_date'], Auth::user()->timezone)
            ]);
            fputcsv($file_pointer, $header);
            $total_batches = intval($count / $batch_limit);
            $record_set = 0;
            do {
                $start = $record_set * $batch_limit;

                $data = $this->program_service->getActiveProgramsDetails(
                    $program_ids,
                    $start,
                    $batch_limit
                );
                foreach ($data as $value) {
                    $tempRow = [];
                    $tempRow[] = $value[0];
                    $tempRow[] = $value[1];
                    fputcsv($file_pointer, $tempRow);
                }
                $record_set++;
            } while ($record_set <= $total_batches);
            fclose($file_pointer);
            exit;
        } catch (Exception $e) {
            return response()->json(['No records found, Please try after some time']);
        }
    }

    public function getTotalInventory()
    {
        $users = [
            'active' => $this->user_service->countActiveUsers(),
            'in_active' => $this->user_service->countInActiveUsers()
        ];
        $channels = [
            'active' => $this->ChannelsCountForInventory("Active"),
            'in_active' => $this->ChannelsCountForInventory("InActive")
        ];
        $quizzes = [
            'active' => $this->QuizzesCountForInventory(),
            'in_active' => trans('admin/dashboard.not_applicable')
        ];
        $events = [
            'active' => $this->EventsCountForInventory(),
            'in_active' => trans('admin/dashboard.not_applicable')
        ];
        $posts = [
            'active' => $this->PostsCountForInventory("Active"),
            'in_active' => $this->PostsCountForInventory("InActive")
        ];
        $items = [
            'image' => $this->ItemsCountForInventory('image'),
            'audio' => $this->ItemsCountForInventory('audio'),
            'video' => $this->ItemsCountForInventory('video'),
            'document' => $this->ItemsCountForInventory('document'),
            'scorm' => $this->ItemsCountForInventory('scorm')
        ];

        return [
            "users" => $users,
            "channels" => $channels,
            "quizzes" => $quizzes,
            "events" => $events,
            "posts" => $posts,
            "items" => $items
        ];
    }

    public function ChannelsCountForInventory($status)
    {
        $program_ids = $this->permittedChannelLists();
        if ($status == "Active") {
            return $this->program_service->countActiveChannels($program_ids);
        }
        if ($status == "InActive") {
            return $this->program_service->countInActiveChannels($program_ids);
        }
    }

    public function PostsCountForInventory($status)
    {
        $items = $this->permittedPostsLists();
        if ($status == "Active") {
            return $this->post_service->countActivePosts($items);
        }
        if ($status == "InActive") {
            return $this->post_service->countInActivePosts($items);
        }
    }

    public function EventsCountForInventory()
    {
        $event_ids = $this->permittedEventLists();
        return $this->event_service->countActiveEvents($event_ids);
    }

    public function QuizzesCountForInventory()
    {
        $quizzes_ids = $this->permittedQuizLists();
        return $this->quiz_service->countActiveQuizzes($quizzes_ids);
    }

    public function ItemsCountForInventory($item_type)
    {
        $item_ids = $this->permittedItemsLists();
        return $this->dams_service->countActiveItems($item_ids, $item_type);
    }

    public function getUserGroupCompletionReport($ugid, $startDateR = '', $endDateR = '')
    {
        set_time_limit(300);
        try {
            $usergroup_data = UserGroup::getUserGroupsUsingID((int)$ugid);
            $user_rel = array_values($usergroup_data[0]['relations']['active_user_usergroup_rel']);
            $feed_rel = array_values($usergroup_data[0]['relations']['usergroup_feed_rel']);

            if ($startDateR != '' || $endDateR != '') {
                $startDateObj = Carbon::createFromFormat('Y-m-d H', $startDateR . ' 0', Auth::user()->timezone);
                $endDateObj = Carbon::createFromFormat('Y-m-d H', $endDateR . ' 0', Auth::user()->timezone)->addDay();
                $startDate = $startDateObj->timestamp;
                $endDate = $endDateObj->timestamp;
                
                $report = OverAllChannelAnalytic::whereBetween('completed_at.0', [$startDate, $endDate])
                        ->where('completion', '>=', 100)
                        ->whereIn('channel_id', $feed_rel)
                        ->whereIn('user_id', $user_rel)
                        ->get(['user_id', 'channel_id', 'created_at','completed_at'])->toArray();
            } else {
                $report = OverAllChannelAnalytic::where('completion', '>=', 100)
                        ->whereIn('channel_id', $feed_rel)
                        ->whereIn('user_id', $user_rel)
                        ->get(['user_id', 'channel_id', 'created_at','completed_at'])->toArray();
            }
            if (empty($report)) {
                return response()->json(['Please check date range, If those are correct, then no records found']);
            }
            $header[] = 'Username';
            $header[] = 'First name';
            $header[] = 'Last name';
            $header[] = 'Email id';
            $header[] = 'Course Name';
            $header[] = 'Completion Date';
            $header[] = 'Status';
            $filename = "UserGroupCompletionReport.csv";
            $fp = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . $filename);
            fputcsv($fp, ['User Group Completion Report']);
            fputcsv($fp, $header);
            foreach ($report as $rep) {
                $tempRow = [];
                $user = User::where('uid', '=', (int)$rep['user_id'])->get()->first();
                if (empty($user)) {
                    continue;
                }
                $program = Program::where('program_id', '=', $rep['channel_id'])->get()->first();
                if (empty($program)) {
                    continue;
                }
                $tempRow[] = $user->username;
                $tempRow[] = $user->firstname;
                $tempRow[] = $user->lastname;
                $tempRow[] = $user->email;
                $tempRow[] = $program['program_title'];
                if (isset($rep['completed_at'][0])) {
                    $date = $rep['completed_at'][0];
                } elseif (isset($rep['updated_at'])) {
                    $date = $rep['updated_at'];
                } else {
                    $date = $rep['created_at'];
                }
                $tempRow[] = Timezone::convertFromUTC('@' . $date, Auth::user()->timezone);
                $tempRow[] = 'Completed';
                fputcsv($fp, $tempRow);
            }
            fclose($fp);
            exit;
        } catch (Exception $e) {
            return response()->json(['No records found, Please try after some time']);
        }
    }

    public function getUserGroupInprogressReport($ugid, $startDateR = '', $endDateR = '')
    {
        set_time_limit(300);
        try {
            $usergroup_data = UserGroup::getUserGroupsUsingID((int)$ugid);
            $user_rel = array_values($usergroup_data[0]['relations']['active_user_usergroup_rel']);
            $feed_rel = array_values($usergroup_data[0]['relations']['usergroup_feed_rel']);

            if ($startDateR != '' || $endDateR != '') {
                $startDateObj = Carbon::createFromFormat('Y-m-d H', $startDateR . ' 0', Auth::user()->timezone);
                $endDateObj = Carbon::createFromFormat('Y-m-d H', $endDateR . ' 0', Auth::user()->timezone)->addDay();
                $startDate = $startDateObj->timestamp;
                $endDate = $endDateObj->timestamp;
                
                $report = OverAllChannelAnalytic::orwhereBetween('created_at', [$startDate, $endDate])
                    ->orWhereBetween('updated_at', [$startDate, $endDate])
                    ->where('completion', '<', 100)
                    ->whereIn('channel_id', $feed_rel)
                    ->whereIn('user_id', $user_rel)
                    ->get(['user_id','channel_id','created_at'])
                    ->toArray();
            } else {
                $report = OverAllChannelAnalytic::where('completion', '!=', 100)
                      ->whereIn('channel_id', $feed_rel)
                      ->whereIn('user_id', $user_rel)
                      ->get(['user_id', 'channel_id', 'created_at'])->toArray();
            }
            if (empty($report)) {
                return response()->json(['Please check date range, If those are correct, then no records found']);
            }
            $header[] = 'Username';
            $header[] = 'First name';
            $header[] = 'Last name';
            $header[] = 'Email id';
            $header[] = 'Course Name';
            $header[] = 'Status';
            $filename = "UserGroupInprogressReport.csv";
            $fp = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . $filename);
            fputcsv($fp, ['User Group In progress Report']);
            fputcsv($fp, $header);
            foreach ($report as $rep) {
                $tempRow = [];
                $user = User::where('uid', '=', (int)$rep['user_id'])->get()->first();
                if (empty($user)) {
                    continue;
                }
                $program = Program::where('program_id', '=', $rep['channel_id'])->get()->first();
                if (empty($program)) {
                    continue;
                }
                $tempRow[] = $user->username;
                $tempRow[] = $user->firstname;
                $tempRow[] = $user->lastname;
                $tempRow[] = $user->email;
                $tempRow[] = $program['program_title'];
                $tempRow[] = 'In Progress';
                fputcsv($fp, $tempRow);
            }
            exit;
        } catch (Exception $e) {
            return response()->json(['No records found, Please try after some time']);
        }
    }

    public function getCourseCompletion($channel_id, $startDateR = '', $endDateR = '')
    {
        set_time_limit(300);
        try {
            $program_data = collect(Program::getProgramDetailsByID((int)$channel_id));
            $user_rel = array_values($program_data['relations']['active_user_feed_rel']);
            $user_group_rel = array_values($program_data['relations']['active_usergroup_feed_rel']);

            $ug_user_ids = [];
            $usergroup_data = UserGroup::getUsergroupDetails($user_group_rel);
            $usergroup_data->map(function ($usergroup_data) use (&$ug_user_ids) {
                           $ug_user_ids[] = array_get($usergroup_data->relations, 'active_user_usergroup_rel', []);
            });
            $user_ids = array_merge(head($ug_user_ids), $user_rel);

            if ($startDateR != '' || $endDateR != '') {
                $startDateObj = Carbon::createFromFormat('Y-m-d H', $startDateR . ' 0', Auth::user()->timezone);
                $endDateObj = Carbon::createFromFormat('Y-m-d H', $endDateR . ' 0', Auth::user()->timezone)->addDay();
                $startDate = $startDateObj->timestamp;
                $endDate = $endDateObj->timestamp;
                $report = OverAllChannelAnalytic::whereBetween('completed_at.0', [$startDate, $endDate])
                        ->where('completion', '>=', 100)
                        ->whereIn('channel_id', [(int)$channel_id])
                        ->whereIn('user_id', $user_ids)
                        ->get(['user_id', 'channel_id', 'created_at','completed_at'])->toArray();
            } else {
                $report = OverAllChannelAnalytic::where('completion', '>=', 100)
                        ->whereIn('channel_id', [(int)$channel_id])
                        ->whereIn('user_id', $user_ids)
                        ->get(['user_id', 'channel_id', 'created_at','completed_at'])->toArray();
            }
            $rep_users = array_pluck($report, 'user_id');
            
            $header[] = 'Username';
            $header[] = 'First name';
            $header[] = 'Last name';
            $header[] = 'Email id';
            $header[] = 'User groups name';
            $header[] = 'Completion Date';
            $header[] = 'Status';
            $filename = "CourseCompletionReport.csv";
            $fp = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . $filename);
            fputcsv($fp, ['Course Completion Report']);
            fputcsv($fp, $header);
            $users = User::whereIn('uid', array_filter($rep_users))->get()->keyBy('uid');
            foreach ($report as $rep) {
                $tempRow = [];
                $user = $users->get($rep['user_id'])->toArray();
                if (empty($user)) {
                    continue;
                }
                $tempRow[] = $user['username'];
                $tempRow[] = $user['firstname'];
                $tempRow[] = $user['lastname'];
                $tempRow[] = $user['email'];
                if (isset($user['relations']['active_usergroup_user_rel']) &&
                    !empty($user['relations']['active_usergroup_user_rel'])
                ) {
                    $user_ug = $usergroup_data->whereIn('ugid', $user['relations']['active_usergroup_user_rel']);
                    if (!$user_ug->isEmpty()) {
                        $tempRow[] = implode(', ', $user_ug->pluck('usergroup_name')->all());
                    }
                } else {
                    $tempRow[] = '';
                }
                if (isset($rep['completed_at'][0])) {
                    $date = $rep['completed_at'][0];
                } elseif (isset($rep['updated_at'])) {
                    $date = $rep['updated_at'];
                } else {
                    $date = $rep['created_at'];
                }
                $tempRow[] = Timezone::convertFromUTC('@' . $date, Auth::user()->timezone);
                $tempRow[] = 'Completed';
                fputcsv($fp, $tempRow);
            }
            fclose($fp);
            exit;
        } catch (Exception $e) {
            return response()->json(['No records found, Please try after some time']);
        }
    }

    public function getCourseInprogress($channel_id, $startDateR = '', $endDateR = '')
    {
        set_time_limit(300);
        try {
            $program_data = collect(Program::getProgramDetailsByID((int)$channel_id));
            $user_rel = array_values($program_data['relations']['active_user_feed_rel']);
            $user_group_rel = array_values($program_data['relations']['active_usergroup_feed_rel']);

            $ug_user_ids = [];
            $usergroup_data = UserGroup::getUsergroupDetails($user_group_rel);
            $usergroup_data->map(function ($usergroup_data) use (&$ug_user_ids) {
                           $ug_user_ids[] = array_get($usergroup_data->relations, 'active_user_usergroup_rel', []);
            });
            $user_ids = array_merge(head($ug_user_ids), $user_rel);

            if ($startDateR != '' || $endDateR != '') {
                $startDateObj = Carbon::createFromFormat('Y-m-d H', $startDateR . ' 0', Auth::user()->timezone);
                $endDateObj = Carbon::createFromFormat('Y-m-d H', $endDateR . ' 0', Auth::user()->timezone)->addDay();
                $startDate = $startDateObj->timestamp;
                $endDate = $endDateObj->timestamp;
                $report = OverAllChannelAnalytic::orwhereBetween('created_at', [$startDate, $endDate])
                        ->orWhereBetween('updated_at', [$startDate, $endDate])
                        ->where('completion', '<', 100)
                        ->whereIn('channel_id', [(int)$channel_id])
                        ->whereIn('user_id', $user_ids)
                        ->get(['user_id','channel_id','created_at'])
                        ->toArray();
            } else {
                $report = OverAllChannelAnalytic::where('completion', '!=', 100)
                      ->whereIn('channel_id', [(int)$channel_id])
                      ->whereIn('user_id', $user_ids)
                      ->get(['user_id', 'channel_id', 'created_at'])->toArray();
            }
            $rep_users = array_pluck($report, 'user_id');
            
            $header[] = 'Username';
            $header[] = 'First name';
            $header[] = 'Last name';
            $header[] = 'Email id';
            $header[] = 'User groups name';
            $header[] = 'Status';
            $filename = "CourseInprogressReport.csv";
            $fp = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . $filename);
            fputcsv($fp, ['Course Completion Report']);
            fputcsv($fp, $header);
            $users = User::whereIn('uid', array_filter($rep_users))->get()->keyBy('uid');
            foreach ($report as $rep) {
                $tempRow = [];
                $user = $users->get($rep['user_id'])->toArray();
                if (empty($user)) {
                    continue;
                }
                $tempRow[] = $user['username'];
                $tempRow[] = $user['firstname'];
                $tempRow[] = $user['lastname'];
                $tempRow[] = $user['email'];
                if (isset($user['relations']['active_usergroup_user_rel']) &&
                    !empty($user['relations']['active_usergroup_user_rel'])
                ) {
                    $user_ug = $usergroup_data->whereIn('ugid', $user['relations']['active_usergroup_user_rel']);
                    if (!$user_ug->isEmpty()) {
                        $tempRow[] = implode(', ', $user_ug->pluck('usergroup_name')->all());
                    }
                } else {
                    $tempRow[] = '';
                }
                
                $tempRow[] = 'In Progress';
                fputcsv($fp, $tempRow);
            }
            fclose($fp);
            exit;
        } catch (Exception $e) {
            return response()->json(['No records found, Please try after some time']);
        }
    }
}