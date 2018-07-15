<?php
namespace App\Http\Controllers\Portal;

use App\Enums\Assignment\SubmissionType;
use App\Exceptions\Announcement\AnnouncementNotFoundException;
use App\Exceptions\Category\CategoryNotFoundException;
use App\Exceptions\Dams\MediaNotFoundException;
use App\Exceptions\Event\EventNotFoundException;
use App\Exceptions\Event\NoEventAssignedException;
use App\Exceptions\Post\NoPostAssignedException;
use App\Exceptions\Post\PostNotFoundException;
use App\Exceptions\Program\NoProgramAssignedException;
use App\Exceptions\Program\ProgramNotFoundException;
use App\Exceptions\Quiz\NoQuizAssignedException;
use App\Exceptions\Quiz\QuizNotFoundException;
use App\Exceptions\User\RelationNotFoundException;
use App\Http\Controllers\PortalBaseController;
use App\Model\Announcement;
use App\Model\Common;
use App\Model\Dam;
use App\Model\Dams\Repository\IDamsRepository;
use App\Model\Dashboard;
use App\Model\FactChannelSummary;
use App\Model\FactChannelUserQuiz;
use App\Model\MyActivity;
use App\Model\OverAllChannelAnalytic;
use App\Model\OverAllQuizPerformance;
use App\Model\Packet;
use App\Model\Program;
use App\Model\Quiz;
use App\Model\QuizAttempt;
use App\Model\SiteSetting;
use App\Model\TransactionDetail;
use App\Model\UserGroup;
use App\Services\Announcement\IAnnouncementService;
use App\Services\Assignment\IAssignmentAttemptService;
use App\Services\Assignment\IAssignmentService;
use App\Services\Event\IEventService;
use App\Services\Post\IPostService;
use App\Services\Program\IProgramService;
use App\Services\Quiz\IQuizService;
use App\Services\Survey\ISurveyAttemptService;
use App\Services\User\IUserService;
use Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Log;
use Response;

class DashboardController extends PortalBaseController
{

    /**
     * @var \App\Services\Announcement\IAnnouncementService
     */
    public $announcement_service;

    /**
     * @var \App\Services\Program\IProgramService
     */
    public $program_service;

    /**
     * @var \App\Services\Post\IPostService
     */
    public $post_service;

    /**
     * @var \App\Services\Quiz\IQuizService
     */
    public $quiz_service;

    /**
     * @var \App\Services\Event\IEventService
     */
    public $event_service;

    /** @var string */
    public $site;

    /*
    * @var IDamsRepository
    */
    public $dams_repository;
    /**
     * @var IUserService
     */
    private $userService;

    /**
     * @var \App\Services\Survey\ISurveyAttemptService
     */
    private $survey_attempt_service;

    /**
     * @var \App\Services\Assignment\IAssignmentAttemptService
     */
    private $assignment_attempt_service;

    /**
     * @var \App\Services\Assignment\IAssignmentService
     */
    private $assignment_service;

    /**
     * DashboardController constructor.
     * @param IUserService $userService
     * @param IAnnouncementService $announcement_service
     * @param IProgramService $program_service
     * @param IPostService $post_service
     * @param IQuizService $quiz_service
     * @param IEventService $event_service
     * @param IDamsRepository $dams_repository
     * @param IAssignmentAttemptService $assignment_attempt_service
     * @param IAssignmentService $assignment_service
     */
    public function __construct(
        IUserService $userService,
        IAnnouncementService $announcement_service,
        IProgramService $program_service,
        IPostService $post_service,
        IQuizService $quiz_service,
        IEventService $event_service,
        IDamsRepository $dams_repository,
        ISurveyAttemptService $survey_attempt_service,
        IAssignmentAttemptService $assignment_attempt_service,
        IAssignmentService $assignment_service
    ) {

        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
        $this->announcement_service = $announcement_service;
        $this->program_service = $program_service;
        $this->post_service = $post_service;
        $this->quiz_service = $quiz_service;
        $this->event_service = $event_service;
        $this->dams_repository = $dams_repository;
        $this->survey_attempt_service = $survey_attempt_service;
        $this->assignment_attempt_service = $assignment_attempt_service;
        $this->assignment_service = $assignment_service;
        if (config('app.ecommerce')) {
            $this->site = 'external';
        } else {
            $this->site = 'internal';
        }
        $this->userService = $userService;
    }

    private function doTheme()
    {
        //Setup of Theme
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        if (Auth::check() && config('app.show_complete_functionalities')) {
            $continuewrleftforhome = MyActivity::getContinueWrULeft(Auth::user()->uid);
            if (isset($continuewrleftforhome) && !empty($continuewrleftforhome) && $continuewrleftforhome > 0) {
                $continuewrleftforhome = $continuewrleftforhome[0]['url'];
            } else {
                $continuewrleftforhome = null;
            }
            $this->layout->header = view($this->theme_path . '.common.header')
                ->with('continuewrleftforhome', $continuewrleftforhome);
        } else {
            $this->layout->header = view($this->theme_path . '.common.header');
        }
        //Setup of theme ends
    }

    public function getIndex()
    {
            $template = "getTemplate" . config('app.dashboard_template');
            $this->$template();
            return;
    }

    /**
     * @SuppressWarnings("unused")
     */
    private function getTemplate1()
    {
        $this->doTheme();
        $crumbs = [
            'Dashboard' => '',
        ];
        $this->layout->breadcrumbs = Common::getEFEBreadCrumbs($crumbs);
        $announce_list_id = [];
        $user_id = Auth::user()->uid;
        if (isset(Auth::user()->relations)) {
            $relations = Auth::user()->relations;
            foreach ($relations as $key => $value) {
                if ($key == 'active_usergroup_user_rel') {
                    $agl = UserGroup::getAnnouncementList($value);
                    foreach ($agl as $key3 => $value3) {
                        if (isset($value3['relations']['usergroup_announcement_rel'])) {
                            foreach ($value3['relations']['usergroup_announcement_rel'] as $key4 => $value4) {
                                $announce_list_id[] = $value4;
                            }
                        }
                    }
                }
                if ($key == 'user_feed_rel') {
                    $acfl = Program::getAnnouncementList($value);
                    foreach ($acfl as $key6 => $value6) {
                        if (isset($value6['relations']['contentfeed_announcement_rel'])) {
                            foreach ($value6['relations']['contentfeed_announcement_rel'] as $key7 => $value7) {
                                $announce_list_id[] = $value7;
                            }
                        }
                    }
                }
                if ($key == 'user_announcement_rel') {
                    if (!empty($value)) {
                        foreach ($value as $key5 => $value5) {
                            $announce_list_id[] = $value5;
                        }
                    }
                }
            }
        }
        $announce_list_id = array_unique($announce_list_id);
        $buff_announcement = [];
        $announcements = Announcement::getNotReadAnnouncementForHead(Auth::user()->uid, $announce_list_id, 0, 5);
        if (is_null($announcements) || empty($announcements) || count($announcements) < 5) {
            $buff_announcement = Announcement::getAnnouncementsforscroll($announce_list_id, 0, 5);
        }
        $carry_media = [];
        $carry_cf = [];
        foreach ($announcements as $key => $value) {
            $temp_cf = [];
            if (isset($value['media_assigned']) && !empty($value['media_assigned']) && $value['media_assigned'] != 'yet to fix') {
                $for_media = $this->getMediaDetails($value['media_assigned'], '_id');
                $carry_media[$key]['media_yet_read'] = $for_media;
            } elseif (isset($value['relations']['active_media_announcement_rel']) && !empty($value['relations']['active_media_announcement_rel'])) {
                $for_media = $this->getMediaDetails($value['relations']['active_media_announcement_rel'][0]);
                $carry_media[$key]['media_yet_read'] = $for_media;
            }
            if (isset($value['relations']['active_contentfeed_announcement_rel']) && !empty($value['relations']['active_contentfeed_announcement_rel'])) {
                foreach ($value['relations']['active_contentfeed_announcement_rel'] as $key_cf => $value_cf) {
                    $temp_cf[] = $value_cf;
                }
                if (!empty($temp_cf)) {
                    $carry_cf[$key]['cf_4_home'] = $this->getCFTitlesAry($temp_cf);
                }
            }
        }
        foreach ($buff_announcement as $key1 => $value1) {
            $temp_cf = [];
            if (isset($value1['media_assigned']) && !empty($value1['media_assigned']) && $value1['media_assigned'] != 'yet to fix') {
                $for_media = $this->getMediaDetails($value1['media_assigned'], '_id');
                $carry_media[$key1]['media_read'] = $for_media;
            } elseif (isset($value1['relations']['active_media_announcement_rel']) && !empty($value1['relations']['active_media_announcement_rel'])) {
                $for_media = $this->getMediaDetails($value1['relations']['active_media_announcement_rel'][0]);
                $carry_media[$key1]['media_read'] = $for_media;
            }
            if (isset($value1['relations']['active_contentfeed_announcement_rel']) && !empty($value1['relations']['active_contentfeed_announcement_rel'])) {
                foreach ($value1['relations']['active_contentfeed_announcement_rel'] as $key_cf => $value_cf) {
                    $temp_cf[] = $value_cf;
                }
                if (!empty($temp_cf)) {
                    $carry_cf[$key1]['cf_4_home'] = $this->getCFTitlesAry($temp_cf);
                }
            }
        }
        if (is_null($announcements) || $announcements <= 0) {
            return 'no more records';
        } else {
            $sub_program_slugs = TransactionDetail::getProgramIds(Auth::user()->uid, null, 'all');
            $program_slugs = Program::getCategoryRelatedProgramSlugs([], [], $sub_program_slugs);
            $packets = Packet::getPacketElementsUsingSlug($program_slugs);
            $packet_ids = [];
            $new_packets = [];
            foreach ($packets as $packet) {
                $elements_count = count($packet['elements']);
                $activity_count = count(MyActivity::getPacketElementDetails(Auth::user()->uid, $packet['packet_id']));
                $packet_ids[] = (int)$packet['packet_id'];
                if ($activity_count == 0) {
                    $new_packets[] = $packet['packet_id'];
                }
            }
            $packets = Packet::getPacketsUsingIdsSortBy($packet_ids, 'new_to_old', 6, 0);
            $favorites = Auth::user()->favourited_packets;
            if (empty($favorites)) {
                $favorites = [];
            }

            /**
             * Purpose : To get the data for my activity(which includes quiz performance, channel performance)
             */
            $quizPerformance = null;
            $currentMonth = Carbon::now()->timestamp;
            $lastMonth = Carbon::now()->subMonth()->timestamp;
            $quizPerformanceQuery = FactChannelUserQuiz::where("user_id", Auth::user()->uid)
                ->Where(function ($query) use ($lastMonth, $currentMonth) {
                    $query->whereBetween("created_at", [$lastMonth, $currentMonth]);
                    $query->orWhere(function ($query) use ($lastMonth, $currentMonth) {
                        $query->whereBetween("updated_at", [$lastMonth, $currentMonth]);
                    });
                });

            $quizPerformanceDataFlag = !($quizPerformanceQuery->get()->isEmpty());
            if ($quizPerformanceDataFlag) {
                $quizPerformance = round($quizPerformanceQuery->avg("quiz_avg_percent"));
            }

            $avgChannelCompletion = null;
            $avgChannelCompletionQuery = FactChannelSummary::where("user_id", Auth::user()->uid)
                ->where("time_line", "last month");
            $avgChannelCompletionDataFlag = !($avgChannelCompletionQuery->get()->isEmpty());
            if ($avgChannelCompletionDataFlag) {
                $avgChannelCompletion = round($avgChannelCompletionQuery->avg("completion"));
            }

            $totalChannelCount = FactChannelUserQuiz::raw(function ($collection) {
                $tmpQuery1 = [
                    ["\$match" => ["user_id" => ["\$eq" => Auth::user()->uid]]],
                    ["\$group" => ["_id" => "\$channel_id", "total_percentage" => ["\$avg" => "\$quiz_avg_percent"]]],
                    ["\$match" => ["total_percentage" => ["\$lt" => 50]]],
                    ["\$group" => ["_id" => null, "total_count" => ["\$sum" => 1]]]
                ];
                return $collection->aggregate($tmpQuery1);
            });

            $totalChannelCount = Collection::make($totalChannelCount["result"])->first()["total_count"];

            $quizPerformanceByChannel = null;
            if ($totalChannelCount > 0) {
                $quizPerformanceByChannel = FactChannelUserQuiz::raw(function ($collection) {
                    return $collection->aggregate([
                        ["\$match" => ["user_id" => ["\$eq" => Auth::user()->uid]]],
                        ["\$group" => ["_id" => "\$channel_id", "total_percentage" => ["\$avg" => "\$quiz_avg_percent"]]],
                        ["\$match" => ["total_percentage" => ["\$lt" => 50]]],
                        ["\$sort" => ["total_percentage" => 1]],
                        ["\$limit" => 3]
                    ]);
                });
                $quizPerformanceByChannel = Collection::make($quizPerformanceByChannel["result"]);
                $quizPerformanceByChannel = $quizPerformanceByChannel->map(function ($item) {
                    $item["name"] = Program::where("program_id", $item["_id"])->first()->program_title;
                    return $item;
                });
                $quizPerformanceByChannel = $quizPerformanceByChannel->all();
            }

            $myRecentActivity = [
                "quiz_perfomance" => [
                    "data_flag" => $quizPerformanceDataFlag,
                    "percentage" => $quizPerformance
                ],
                "channel_completion" => [
                    "data_flag" => $avgChannelCompletionDataFlag,
                    "percentage" => $avgChannelCompletion
                ],
                "areas_of_improvement" => [
                    "channel_count" => $totalChannelCount,
                    "quiz_performance" => $quizPerformanceByChannel
                ]
            ];

            $this->layout->content = view($this->theme_path . '.common.postlogin', ['general' => SiteSetting::module('General'), 'lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')])
                ->with('announcements', $announcements)
                ->with('buff_announcement', $buff_announcement)
                ->with('carry_cf', $carry_cf)
                ->with('carry_media', $carry_media)
                ->with('packets', $packets)
                ->with('favorites', $favorites)
                ->with('myRecentActivity', $myRecentActivity);
        }
    }

    private function getMediaDetails($key = null, $_id = null)
    {
        $query_field = null;
        if (!is_null($key) && $_id == '_id') {
            $query_field = "_id";
        } elseif (!is_null($key)) {
            $query_field = "id";
        } else {
            return '';
        }

        try {
            $media = $this->dams_repository->getMedia($key, $query_field);
        } catch (MediaNotFoundException $e) {
            return trans('admin/exception'.$e->getcode());
        }
        $uniconf_id = config('app.uniconf_id');
        $kaltura_url = config('app.kaltura_url');
        $partnerId = config('app.partnerId');

        $kaltura = $kaltura_url . 'index.php/kwidget/cache_st/1389590657/wid/_' . $partnerId . '/uiconf_id/' . $uniconf_id . '/entry_id/';

        return view('portal.theme.default.announcement.viewmedia')
            ->with('kaltura', $kaltura)
            ->with('media', $media);
    }

    private function getCFTitlesAry($cf_ids = [])
    {
        $titles = [];
        foreach ($cf_ids as $key => $value) {
            $titles[] = Program::getCFTitleID($value);
        }

        return $titles;
    }

    public function getTemplate2($limit = 4)
    {
        $this->doTheme();
        $crumbs = [
            'Dashboard' => '',
        ];
        $this->layout->breadcrumbs = Common::getEFEBreadCrumbs($crumbs);
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $quizzes = [];
        $channels = [];
        $uids = [];
        $channels_id = [];
        $user_quiz_rel = $this->quiz_service->getAllQuizzesAssigned();
        $quiz_list = $user_quiz_rel['quiz_list'];
        $quizAnalyticsGrouped = [];
        $channelAnalyticsGrouped = [];


        $quiz_list = array_map('intval', array_unique($quiz_list));

        $attempted = QuizAttempt::where('user_id', '=', (int)Auth::user()->uid)
            ->whereIn('quiz_id', $quiz_list)
            ->where('status', 'CLOSED')
            ->get();

        $attempt['list'] = $attempt['detail'] = [];
        foreach ($attempted as $value) {
            $attempt['list'][] = (int)$value->quiz_id;
            $attempt['detail'][$value->quiz_id][] = $value;
        }
        $attempt['list'] = array_unique($attempt['list']);
        $unattempted = array_diff($quiz_list, $attempt['list']);
        //getting today's quizzes
        $quizzes = Quiz::where('end_time', '>=', Carbon::now(Auth::user()->timezone)->getTimestamp())
            ->where('end_time', '<=', Carbon::tomorrow(Auth::user()->timezone)->getTimestamp() - 1)
            ->where('status', 'ACTIVE')
            ->whereIn('quiz_id', $unattempted)
            ->orderBy('end_time', 'ASC')
            ->get();
        if ($quizzes->count() < $limit) {
            //getting practice quiz with no time limit
            $quizzes_no_time = Quiz::where('end_time', '=', 0)->where('status', 'ACTIVE')->whereIn('quiz_id', $unattempted)->orderBy('created_at', 'DESC')->take($limit - $quizzes->count())->get();
            $quizzes = $quizzes->merge($quizzes_no_time);
            if ($quizzes->count() < $limit) {
                //getting upcoming quizzes from tomorrow
                $quizzes_upcoming = Quiz::where('end_time', '>=', Carbon::tomorrow(Auth::user()->timezone)->getTimestamp())
                    ->where('status', 'ACTIVE')
                    ->whereIn('quiz_id', $unattempted)
                    ->orderBy('end_time', 'ASC')
                    ->take($limit - $quizzes->count())
                    ->get();
                $quizzes = $quizzes->merge($quizzes_upcoming);
            }
        }
        if (!empty($quizzes)) {
            $quizIdsForAnalytics = array_filter($quizzes->lists('quiz_id')->all());
            $quizAnalytics = OverAllQuizPerformance::getQuizAnalytics(
                $quizIdsForAnalytics,
                (int)Auth::user()->uid
            );
            if (!empty($quizAnalytics)) {
                $quizAnalyticsGrouped = $quizAnalytics->groupBy('quiz_id');
            }
        } else {
            $quizAnalytics = [];
        }
        $channels = Auth::user()->relations;

        if (isset($channels['active_usergroup_user_rel']) &&
            is_array($channels['active_usergroup_user_rel'])
        ) {
            foreach ($channels['active_usergroup_user_rel'] as $ugids) {
                $ugids_details = UserGroup::getActiveUserGroupsUsingID($ugids);
                if (isset($ugids_details[0]['relations']['usergroup_feed_rel'])) {
                    foreach ($ugids_details[0]['relations']['usergroup_feed_rel'] as $key => $value) {
                        $uids[] = $value;
                    }
                }
                //collection start
                if (isset($ugids_details[0]['relations']['usergroup_parent_feed_rel']) && !empty($ugids_details[0]['relations']['usergroup_parent_feed_rel'])) {
                    foreach ($ugids_details[0]['relations']['usergroup_parent_feed_rel'] as $key => $value) {
                        $progarm = Program::getProgramDetailsByID($value);
                        if (isset($progarm['child_relations']['active_channel_rel']) && !empty($progarm['child_relations']['active_channel_rel'])) {
                            foreach ($progarm['child_relations']['active_channel_rel'] as $value1) {
                                $uids[] = $value1;
                            }
                        }
                    }
                }
                //collection end
            }
        }

        if (isset($channels['active_usergroup_user_rel']) &&
            is_array($channels['active_usergroup_user_rel']) &&
            isset($channels['user_feed_rel']) && is_array($channels['user_feed_rel'])
        ) {
            $channels_id = array_unique(array_merge($uids, $channels['user_feed_rel']));
            if (isset($channels['user_package_feed_rel'])) {
                $channels_id = array_unique(array_merge($channels_id, $channels['user_package_feed_rel']));
            }
        } elseif (isset($channels['active_usergroup_user_rel']) && is_array($channels['active_usergroup_user_rel'])) {
            $channels_id = $uids;
        } elseif (isset($channels['user_feed_rel']) && is_array($channels['user_feed_rel'])) {
            $channels_id = $channels['user_feed_rel'];
            if (isset($channels['user_package_feed_rel'])) {
                $channels_id = array_unique(array_merge($channels_id, $channels['user_package_feed_rel']));
            }
        } elseif (isset($channels['user_course_rel']) && !empty($channels['user_course_rel'])) {
            $channels_id = $channels['user_course_rel'];
        } elseif (isset($channels['user_package_feed_rel']) && !empty($channels['user_package_feed_rel'])) {
            $channels_id = $channels['user_package_feed_rel'];
        }
        $programs = Dashboard::getLatestUpdatedChannels($channels_id, $limit);
        if (!empty($programs)) {
            $reportChannelIds = $programs->lists('program_id')->all();
        } else {
            $reportChannelIds = [];
        }
        if (!empty($reportChannelIds)) {
            $channelAnalytics = OverAllChannelAnalytic::getChannelAnalytics(
                array_filter($reportChannelIds),
                (int)Auth::user()->uid
            );
            if (!empty($channelAnalytics)) {
                $channelAnalyticsGrouped = $channelAnalytics->groupBy('channel_id');
            } else {
                $channelAnalyticsGrouped = [];
            }
        } else {
            $channelAnalytics = [];
        }

        $sub_program_slugs = TransactionDetail::getProgramIds(Auth::user()->uid, null, 'all');
        $program_slugs = Program::getCategoryRelatedProgramSlugs([], [], $sub_program_slugs);
        $packets = Packet::getPacketElementsUsingSlug($program_slugs);
        $packet_ids = [];
        $new_packets = [];
        foreach ($packets as $packet) {
            $elements_count = count($packet['elements']);
            $activity_count = count(MyActivity::getPacketElementDetails(Auth::user()->uid, $packet['packet_id']));
            $packet_ids[] = (int)$packet['packet_id'];
            if ($activity_count == 0) {
                $new_packets[] = $packet['packet_id'];
            }
        }
        $packets = Packet::getPacketsUsingIdsSortBy($packet_ids, 'new_to_old', $limit, 0);
        $favorites = Auth::user()->favourited_packets;
        if (empty($favorites)) {
            $favorites = [];
        }
        $announce_list_id = [];
        $user_id = Auth::user()->uid;
        if (isset(Auth::user()->relations)) {
            $relations = Auth::user()->relations;
            foreach ($relations as $key => $value) {
                if ($key == 'active_usergroup_user_rel') {
                    $agl = UserGroup::getAnnouncementList($value);
                    foreach ($agl as $key3 => $value3) {
                        if (isset($value3['relations']['usergroup_announcement_rel'])) {
                            foreach ($value3['relations']['usergroup_announcement_rel'] as $key4 => $value4) {
                                $announce_list_id[] = $value4;
                            }
                        }
                    }
                }
                if ($key == 'user_feed_rel') {
                    $acfl = Program::getAnnouncementList($value);
                    foreach ($acfl as $key6 => $value6) {
                        if (isset($value6['relations']['contentfeed_announcement_rel'])) {
                            foreach ($value6['relations']['contentfeed_announcement_rel'] as $key7 => $value7) {
                                $announce_list_id[] = $value7;
                            }
                        }
                    }
                }
                if ($key == 'user_announcement_rel') {
                    if (!empty($value)) {
                        foreach ($value as $key5 => $value5) {
                            $announce_list_id[] = $value5;
                        }
                    }
                }
            }
        }
        $announce_list_id = array_unique($announce_list_id);
        $buff_announcement = [];
        $announcements = Announcement::getNotReadAnnouncementForHead(Auth::user()->uid, $announce_list_id, 0, 3);
        if (is_null($announcements) || empty($announcements) || count($announcements) < 3) {
            $buff_announcement = Announcement::getAnnouncementsforscroll($announce_list_id, 0, 3);
        }
        $carry_media = [];
        $carry_cf = [];
        foreach ($announcements as $key => $value) {
            $temp_cf = [];
            if (isset($value['media_assigned']) && !empty($value['media_assigned']) && $value['media_assigned'] != 'yet to fix') {
                $for_media = $this->getMediaDetails($value['media_assigned'], '_id');
                $carry_media[$key]['media_yet_read'] = $for_media;
            } elseif (isset($value['relations']['active_media_announcement_rel']) && !empty($value['relations']['active_media_announcement_rel'])) {
                $for_media = $this->getMediaDetails($value['relations']['active_media_announcement_rel'][0]);
                $carry_media[$key]['media_yet_read'] = $for_media;
            }
            if (isset($value['relations']['active_contentfeed_announcement_rel']) && !empty($value['relations']['active_contentfeed_announcement_rel'])) {
                foreach ($value['relations']['active_contentfeed_announcement_rel'] as $key_cf => $value_cf) {
                    $temp_cf[] = $value_cf;
                }
                if (!empty($temp_cf)) {
                    $carry_cf[$key]['cf_4_home'] = $this->getCFTitlesAry($temp_cf);
                }
            }
        }
        foreach ($buff_announcement as $key1 => $value1) {
            $temp_cf = [];
            if (isset($value1['media_assigned']) && !empty($value1['media_assigned']) && $value1['media_assigned'] != 'yet to fix') {
                $for_media = $this->getMediaDetails($value1['media_assigned'], '_id');
                $carry_media[$key1]['media_read'] = $for_media;
            } elseif (isset($value1['relations']['active_media_announcement_rel']) && !empty($value1['relations']['active_media_announcement_rel'])) {
                $for_media = $this->getMediaDetails($value1['relations']['active_media_announcement_rel'][0]);
                $carry_media[$key1]['media_read'] = $for_media;
            }

            if (isset($value1['relations']['active_contentfeed_announcement_rel']) && !empty($value1['relations']['active_contentfeed_announcement_rel'])) {
                foreach ($value1['relations']['active_contentfeed_announcement_rel'] as $key_cf => $value_cf) {
                    $temp_cf[] = $value_cf;
                }
                if (!empty($temp_cf)) {
                    $carry_cf[$key1]['cf_4_home'] = $this->getCFTitlesAry($temp_cf);
                }
            }
        }
        $this->layout->content = view($this->theme_path . '.dashboard.template1.dashboard', ['general' => SiteSetting::module('General'), 'lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')])
            ->with('quizzes', $quizzes)
            ->with('quiz_list', $quiz_list)
            ->with('programs', $programs)
            ->with('packets', $packets)
            ->with('favorites', $favorites)
            ->with('quizAnalytics', $quizAnalyticsGrouped)
            ->with('announcements', $announcements)
            ->with('buff_announcement', $buff_announcement)
            ->with('channelAnalytics', $channelAnalyticsGrouped);
    }

    /**
     * this function is used to get announcements
     * @param  integer $page page number
     * @param  integer $limit no of records to retrieve
     * @return \Illuminate\Http\JsonResponse response
     */
    public function getAnnouncements($page = 1, $limit = 10)
    {
        try {
            $announcements = $this->announcement_service->getAnnouncements($page, $limit);
            return Response::json(['results' => $announcements]);
        } catch (AnnouncementNotFoundException $e) {
            return Response::json(['status' => false, 'message' => 'No announcement found']);
        } catch (RelationNotFoundException $e) { //for no relations
            return Response::json(['status' => false, 'message' => 'No announcement found']);
        } catch (Exception $e) {
            Log::warning($e->getMessage());
            return Response::json(['status' => false, 'message' => 'No announcement found']);
        }
    }

    /**
     * this function is used to get events assigned to users on given date
     * @param  integer $page page number
     * @param  integer $limit no of records to retrieve
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEvents($page, $limit)
    {
        try {
            $result = $this->event_service->getUpcomingEvents($page, $limit);
            return Response::json(['status' => true, 'results' => $result]);
        } catch (EventNotFoundException $e) {
            Log::info('Event Not Found');
            return Response::json(['status' => false, 'message' => trans("dashboard.$this->site.no_events")]);
        } catch (NoEventAssignedException $e) {
            Log::info('Event Not Found');
            return Response::json(['status' => false, 'message' => trans("dashboard.$this->site.no_events")]);
        } catch (NoProgramAssignedException $e) {
            Log::info('Event Not Found');
            return Response::json(['status' => false, 'message' => trans("dashboard.$this->site.no_events")]);
        }
    }

    /**
     * this function used to get programs data
     *
     * @param  integer $page page number
     * @param  integer $limit no of records to retrieve
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getPrograms($page = 1, $limit = 4, $posts = false, $order = false)
    {
        try {
            return $this->program_service->getUserPrograms($page, $limit, $posts, $order);
        } catch (ProgramNotFoundException $e) {
            return ['status' => false, 'message' => trans("dashboard.$this->site.no_channels")];
        } catch (NoProgramAssignedException $e) {
            return ['status' => false, 'message' => trans("dashboard.$this->site.no_channels")];
        } catch (RelationNotFoundException $e) {
            return ['status' => false, 'message' => trans("dashboard.$this->site.no_channels")];
        }
    }

    /**
     * this function is used to get posts with pagination
     *
     * @param  integer $page page number
     * @param  integer $limit no of records to retrieve
     * @return \Illuminate\Http\JsonResponse|array
     */
    public function getPosts($page = 1, $limit = 5, $slug = 'all')
    {
        try {
            if ($slug == 'all') {
                return $this->post_service->getPosts($page, $limit);
            } else {
                return $this->post_service->getPostsDataBySlug($page, $limit, [$slug]);
            }
        } catch (PostNotFoundException $e) {
            return ['status' => false, 'message' => trans("dashboard.$this->site.no_posts")];
        } catch (NoPostAssignedException $e) {
            return ['status' => false, 'message' => trans("dashboard.$this->site.no_posts")];
        } catch (RelationNotFoundException $e) {
            return ['status' => false, 'message' => trans("dashboard.$this->site.no_posts")];
        } catch (NoProgramAssignedException $e) {
            return ['status' => false, 'message' => trans("dashboard.$this->site.no_posts")];
        }
    }

    /**
     * this function is used to get posts with pagination
     *
     * @param  integer $page page number
     * @param  integer $limit no of records to retrieve
     * @return \Illuminate\Http\JsonResponse|array
     */
    public function getPostDetails($page = 1, $limit = 5, $slug = 'all')
    {
        try {
            if ($slug == 'all') {
                return $this->post_service->getPostsForAll($page, $limit);
            } else {
                return $this->post_service->getPostsBySlug($page, $limit, [$slug]);
            }
        } catch (PostNotFoundException $e) {
            return ['status' => false, 'message' => trans("dashboard.$this->site.no_posts")];
        } catch (NoPostAssignedException $e) {
            return ['status' => false, 'message' => trans("dashboard.$this->site.no_posts")];
        } catch (RelationNotFoundException $e) {
            return ['status' => false, 'message' => trans("dashboard.$this->site.no_posts")];
        }
    }

    public function getAssessments($page = 1, $limit = 5, $slug = 'all')
    {
        try {
            if ($slug == 'all') {
                $result = $this->quiz_service->getQuizzes($page, $limit);
                return ['status' => true, 'result' => $result];
            } else {
                $result = $this->quiz_service->getQuizzesByProgram($page, $limit, $slug);
                return ['status' => true, 'result' => $result];
            }
        } catch (NoQuizAssignedException $e) {
            return ['status' => false, 'message' => trans("dashboard.$this->site.no_quiz")];
        } catch (QuizNotFoundException $e) {
            return ['status' => false, 'message' => trans("dashboard.$this->site.no_assessments")];
        } catch (RelationNotFoundException $e) {
            return ['status' => false, 'message' => trans("dashboard.$this->site.no_quiz")];
        } catch (NoPostAssignedException $e) {
            return ['status' => false, 'message' => trans("dashboard.$this->site.no_assessments")];
        } catch (NoProgramAssignedException $e) {
            return ['status' => false, 'message' => trans("dashboard.$this->site.no_assessments")];
        }
    }

    public function getTemplate3()
    {
        $crumbs = [
            'Dashboard' => '',
        ];
        $this->layout->breadcrumbs = Common::getEFEBreadCrumbs($crumbs);
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        if (Auth::check() && config('app.show_complete_functionalities')) {
            $continuewrleftforhome = MyActivity::getContinueWrULeft(Auth::user()->uid);
            if (isset($continuewrleftforhome) && !empty($continuewrleftforhome) && $continuewrleftforhome > 0) {
                $continuewrleftforhome = $continuewrleftforhome[0]['url'];
            } else {
                $continuewrleftforhome = null;
            }
            $this->layout->header = view($this->theme_path . '.dashboard.headers.header1', ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')])
                ->with('continuewrleftforhome', $continuewrleftforhome);
        } else {
            $this->layout->header = view($this->theme_path . '.dashboard.headers.header1', ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]);
        }
        $this->layout->content = view($this->theme_path . '.dashboard.template3.dashboard', ['site' => $this->site, 'general' => SiteSetting::module('General'), 'lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]);
    }

    public function getTemplate4()
    {
        $crumbs = [
            'Dashboard' => '',
        ];
        $this->layout->breadcrumbs = Common::getEFEBreadCrumbs($crumbs);
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        if (Auth::check() && config('app.show_complete_functionalities')) {
            $continuewrleftforhome = MyActivity::getContinueWrULeft(Auth::user()->uid);
            if (isset($continuewrleftforhome) && !empty($continuewrleftforhome) && $continuewrleftforhome > 0) {
                $continuewrleftforhome = $continuewrleftforhome[0]['url'];
            } else {
                $continuewrleftforhome = null;
            }
            $this->layout->header = view($this->theme_path . '.dashboard.headers.header1', ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')])
                ->with('continuewrleftforhome', $continuewrleftforhome);
        } else {
            $this->layout->header = view($this->theme_path . '.dashboard.headers.header1', ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]);
        }
        $this->layout->content = view($this->theme_path . '.dashboard.template4.dashboard', ['site' => $this->site, 'general' => SiteSetting::module('General'), 'lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]);
    }

    public function getTemplate5()
    {
        $crumbs = [
            'Dashboard' => '',
        ];
        if (Auth::check() && config('app.show_complete_functionalities')) {
            $continuewrleftforhome = MyActivity::getContinueWrULeft(Auth::user()->uid);
            if (isset($continuewrleftforhome) && !empty($continuewrleftforhome) && $continuewrleftforhome > 0) {
                $continuewrleftforhome = $continuewrleftforhome[0]['url'];
            } else {
                $continuewrleftforhome = null;
            }
            $this->layout->header = view($this->theme_path . '.dashboard.headers.header1', ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')])
                ->with('continuewrleftforhome', $continuewrleftforhome);
        } else {
            $this->layout->header = view($this->theme_path . '.dashboard.headers.header1', ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]);
        }
        $this->layout->breadcrumbs = Common::getEFEBreadCrumbs($crumbs);
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->content = view($this->theme_path . '.dashboard.template5.dashboard', ['site' => $this->site, 'general' => SiteSetting::module('General'), 'lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]);
    }

    public function getTemplate6()
    {
        try {
            $data = ['status' => true, 'results' => $this->program_service->getCategoryWiseChannels()];
        } catch (NoProgramAssignedException $e) {
            $data = ['status' => false, 'message' => trans("dashboard.$this->site.no_channels")];
        } catch (CategoryNotFoundException $e) {
            $data = ['status' => false, 'message' => trans("dashboard.$this->site.no_channels")];
        } catch (RelationNotFoundException $e) {
            $data = ['status' => false, 'message' => trans("dashboard.$this->site.no_channels")];
        }

        if (Auth::check() && config('app.show_complete_functionalities')) {
            $continuewrleftforhome = MyActivity::getContinueWrULeft(Auth::user()->uid);
            if (isset($continuewrleftforhome) && !empty($continuewrleftforhome) && $continuewrleftforhome > 0) {
                $continuewrleftforhome = $continuewrleftforhome[0]['url'];
            } else {
                $continuewrleftforhome = null;
            }
            $this->layout->header = view($this->theme_path . '.dashboard.headers.header1', ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')])
                ->with('continuewrleftforhome', $continuewrleftforhome);
        } else {
            $this->layout->header = view($this->theme_path . '.dashboard.headers.header1', ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]);
        }
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->content = view($this->theme_path . '.dashboard.template6.dashboard', ['data' => $data, 'site' => $this->site, 'general' => SiteSetting::module('General'), 'lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]);
    }

    public function getTemplate7()
    {
        try {
            $data = ['status' => true, 'results' => $this->program_service->getCategoryWiseChannels()];
        } catch (NoProgramAssignedException $e) {
            $data = ['status' => false, 'message' => trans("dashboard.$this->site.no_channels")];
        } catch (CategoryNotFoundException $e) {
            $data = ['status' => false, 'message' => trans("dashboard.$this->site.no_channels")];
        } catch (RelationNotFoundException $e) {
            $data = ['status' => false, 'message' => trans("dashboard.$this->site.no_channels")];
        }
        $crumbs = [
            'Dashboard' => '',
        ];
        if (Auth::check() && config('app.show_complete_functionalities')) {
            $continuewrleftforhome = MyActivity::getContinueWrULeft(Auth::user()->uid);
            if (isset($continuewrleftforhome) && !empty($continuewrleftforhome) && $continuewrleftforhome > 0) {
                $continuewrleftforhome = $continuewrleftforhome[0]['url'];
            } else {
                $continuewrleftforhome = null;
            }
            $this->layout->header = view($this->theme_path . '.dashboard.headers.header1', ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')])
                ->with('continuewrleftforhome', $continuewrleftforhome);
        } else {
            $this->layout->header = view($this->theme_path . '.dashboard.headers.header1', ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]);
        }
        $this->layout->breadcrumbs = Common::getEFEBreadCrumbs($crumbs);
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->content = view($this->theme_path . '.dashboard.template7.dashboard', ['data' => $data, 'site' => $this->site, 'general' => SiteSetting::module('General'), 'lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]);
    }

    public function getTemplate9()
    {
        $crumbs = [
            'Dashboard' => '',
        ];
        $this->layout->breadcrumbs = Common::getEFEBreadCrumbs($crumbs);
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        if (Auth::check()) {
            $continuewrleftforhome = MyActivity::getContinueWrULeft(Auth::user()->uid);
            if (isset($continuewrleftforhome) && !empty($continuewrleftforhome) && $continuewrleftforhome > 0) {
                $continuewrleftforhome = $continuewrleftforhome[0]['url'];
            } else {
                $continuewrleftforhome = null;
            }
            $this->layout->header = view($this->theme_path . '.dashboard.headers.header1', ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')])
                ->with('continuewrleftforhome', $continuewrleftforhome);
        } else {
            $this->layout->header = view($this->theme_path . '.dashboard.headers.header1', ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]);
        }
        $this->layout->content = view($this->theme_path . '.dashboard.template9.dashboard', ['site' => $this->site, 'general' => SiteSetting::module('General'), 'lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]);
    }

    public function getTemplate8()
    {
        $crumbs = [
            'Dashboard' => '',
        ];
        $this->layout->breadcrumbs = Common::getEFEBreadCrumbs($crumbs);
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        if (Auth::check()) {
            $continuewrleftforhome = MyActivity::getContinueWrULeft(Auth::user()->uid);
            if (isset($continuewrleftforhome) && !empty($continuewrleftforhome) && $continuewrleftforhome > 0) {
                $continuewrleftforhome = $continuewrleftforhome[0]['url'];
            } else {
                $continuewrleftforhome = null;
            }
            $this->layout->header = view($this->theme_path . '.dashboard.headers.header1', ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')])
                ->with('continuewrleftforhome', $continuewrleftforhome);
        } else {
            $this->layout->header = view($this->theme_path . '.dashboard.headers.header1', ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]);
        }
        $this->layout->content = view($this->theme_path . '.dashboard.template8.dashboard', ['site' => $this->site, 'general' => SiteSetting::module('General'), 'lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]);
    }
    public function getTemplate10($page = 1, $limit = 4)
    {
        $crumbs = [
            'Dashboard' => '',
        ];
        $this->layout->breadcrumbs = Common::getEFEBreadCrumbs($crumbs);
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        if (Auth::check()) {
            $continuewrleftforhome = MyActivity::getContinueWrULeft(Auth::user()->uid);
            if (isset($continuewrleftforhome) && !empty($continuewrleftforhome) && $continuewrleftforhome > 0) {
                $continuewrleftforhome = $continuewrleftforhome[0]['url'];
            } else {
                $continuewrleftforhome = null;
            }
            $this->layout->header = view($this->theme_path . '.dashboard.headers.header1', ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')])
                ->with('continuewrleftforhome', $continuewrleftforhome);
        } else {
            $this->layout->header = view($this->theme_path . '.dashboard.headers.header1', ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]);
        }
        $programs = $this->getPrograms(1, 12, false, true);
        $posts = $this->getPosts($page, $limit);
        $quizzes = $this->getAssessments($page, $limit);
        $this->layout->content = view($this->theme_path . '.dashboard.template10.dashboard', ['site' => $this->site, 'general' => SiteSetting::module('General'), 'lhs_menu_settings' => SiteSetting::module('LHSMenuSettings'), 'programs' => $programs, 'posts' => $posts, 'quizzes' => $quizzes]);
    }

    public function getTemplate11()
    {
        try {
            $data = ['status' => true, 'results' => $this->program_service->getCategoryWiseChannels()];
        } catch (NoProgramAssignedException $e) {
            $data = ['status' => false, 'message' => trans("dashboard.$this->site.no_channels")];
        } catch (CategoryNotFoundException $e) {
            $data = ['status' => false, 'message' => trans("dashboard.$this->site.no_channels")];
        } catch (RelationNotFoundException $e) {
            $data = ['status' => false, 'message' => trans("dashboard.$this->site.no_channels")];
        }
        $crumbs = [
            'Dashboard' => '',
        ];
        if (Auth::check() && config('app.show_complete_functionalities')) {
            $continuewrleftforhome = MyActivity::getContinueWrULeft(Auth::user()->uid);
            if (isset($continuewrleftforhome) && !empty($continuewrleftforhome) && $continuewrleftforhome > 0) {
                $continuewrleftforhome = $continuewrleftforhome[0]['url'];
            } else {
                $continuewrleftforhome = null;
            }
            $this->layout->header = view($this->theme_path . '.dashboard.headers.header1', ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')])
                ->with('continuewrleftforhome', $continuewrleftforhome);
        } else {
            $this->layout->header = view($this->theme_path . '.dashboard.headers.header1', ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]);
        }
        $this->layout->breadcrumbs = Common::getEFEBreadCrumbs($crumbs);
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->content = view($this->theme_path . '.dashboard.template11.dashboard', ['data' => $data, 'site' => $this->site, 'general' => SiteSetting::module('General'), 'lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]);
    }

    public function getNextPrograms($page)
    {
        $programs = $this->getPrograms($page, 12, false, true);
        if (count(array_get($programs, 'results', [])) > 0) {
            $status = true;
        } else {
            $status = false;
        }
        return response()->json([
                'status' => $status,
                'data' => view($this->theme_path . '.dashboard.template10.programs', ['programs' => $programs])->render(),
            ]);
    }

    public function getMorePrograms($page)
    {
        $programs = $this->getPrograms($page, 12, false, true);
        if (count(array_get($programs, 'results', [])) > 0) {
            $status = true;
        } else {
            $status = false;
        }
        return response()->json([
                'status' => $status,
                'data' => view($this->theme_path . '.dashboard.template13.programs', ['programs' => $programs])->render(),
            ]);
    }
    public function getActiveProgramsTotalCount()
    {
        try {
            return $this->program_service->getActiveProgramsTotalCount();
        } catch (ProgramNotFoundException $e) {
            return Response::json(['status' => false, 'message' => trans("dashboard.$this->site.no_channels")]);
        } catch (NoProgramAssignedException $e) {
            return Response::json(['status' => false, 'message' => trans("dashboard.$this->site.no_channels")]);
        } catch (Exception $e) {
            Log::warning('Error :' . $e->getMessage());
            return Response::json(['status' => false, 'message' => trans("dashboard.$this->site.no_channels")]);
        }
    }

    public function getActiveProgramsCount($page = 1, $limit = 8)
    {
        try {
            return $this->program_service->getActiveProgramsCount($page, $limit);
        } catch (ProgramNotFoundException $e) {
            return Response::json(['status' => false, 'message' => trans("dashboard.$this->site.no_channels")]);
        } catch (NoProgramAssignedException $e) {
            return Response::json(['status' => false, 'message' => trans("dashboard.$this->site.no_channels")]);
        }
    }

    public function getCategoryChannel()
    {
        try {
            return Response::json(['status' => true, 'results' => $this->program_service->getCategoryWiseChannels()]);
        } catch (NoProgramAssignedException $e) {
            return Response::json(['status' => false, 'message' => trans("dashboard.$this->site.no_channels")]);
        }
    }

    public function getTemplate12()
    {
        $crumbs = [
            'Dashboard' => '',
        ];
        $this->layout->breadcrumbs = Common::getEFEBreadCrumbs($crumbs);
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        if (Auth::check() && config('app.show_complete_functionalities')) {
            $continuewrleftforhome = MyActivity::getContinueWrULeft(Auth::user()->uid);
            if (isset($continuewrleftforhome) && !empty($continuewrleftforhome) && $continuewrleftforhome > 0) {
                $continuewrleftforhome = $continuewrleftforhome[0]['url'];
            } else {
                $continuewrleftforhome = null;
            }
            $this->layout->header = view(
                $this->theme_path . '.dashboard.headers.header1',
                ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]
            )->with('continuewrleftforhome', $continuewrleftforhome);
        } else {
            $this->layout->header = view(
                $this->theme_path . '.dashboard.headers.header1',
                ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]
            );
        }
        try {
            $surveys = $this->survey_attempt_service->getAllIncompleteSurveys(1, 4);
        } catch (Exception $e) {
            $surveys = [];
        }

        //Assignment
        try {
            try {
                $user_assignment_rel = collect($this->assignment_service->getAllAssignmentsAssigned());
            } catch (Exception $e) {
                Log::info($e);
                $user_assignment_rel = collect(['seq_assignments' => [], 'assignment_list' => [], 'feed_assignment_list' => []]);
            }
            $seqAssignments = $user_assignment_rel->get('seq_assignments', []);

            $assignment_list = array_unique($user_assignment_rel->get('assignment_list', []));

            // code for completed assignments
            $attempted_data = $this->assignment_attempt_service->getAssignmentAttemptByUserAndAssignmentIds(
                    (int)Auth::user()->uid,
                    $assignment_list
            );
            $attempt = [];
            $draft = [];
            $assignment_details = collect([]);
            foreach ($attempted_data as $value) {
                if($value->submission_status == SubmissionType::SAVE_AS_DRAFT){
                    $draft[] = $value->assignment_id;
                } else {
                    $attempt[] = $value->assignment_id;
                }
            }
            $count['attempted'] = count($attempt);

            // code for unattempted assignments
            $myUAAssignmentIds = [];
            $myUAAssignmentIds = array_diff($assignment_list, $attempt);
            $nonSquAssignments = array_diff($myUAAssignmentIds, $seqAssignments);
            $assignment_details = $nonSquAssignments;
            $filter_params = [
                "id" => $nonSquAssignments,
                "status" => "ACTIVE",
                "cutoff_time" => time(),
                "start" => 0,
                "limit" => 4,
                "start_time" => time(),
            ];
            $assignment_details = $this->assignment_service->getAssignments($filter_params, ["start_time" => "desc"]);
        } catch (Exception $e) {
            $assignment_details = collect([]);
        }
            $this->layout->content = view(
                $this->theme_path . '.dashboard.template12.dashboard',
                [
                'site' => $this->site,
                'general' => SiteSetting::module('General'),
                'lhs_menu_settings' => SiteSetting::module('LHSMenuSettings'),
                'surveys' => $surveys,
                'assignments' => $assignment_details
                ]
            );
    }

    public function getTemplate13($page = 1, $limit = 4)
    {
        $crumbs = [
            'Dashboard' => '',
        ];
        $this->layout->breadcrumbs = Common::getEFEBreadCrumbs($crumbs);
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        if (Auth::check()) {
            $continuewrleftforhome = MyActivity::getContinueWrULeft(Auth::user()->uid);
            if (isset($continuewrleftforhome) && !empty($continuewrleftforhome) && $continuewrleftforhome > 0) {
                $continuewrleftforhome = $continuewrleftforhome[0]['url'];
            } else {
                $continuewrleftforhome = null;
            }
            $this->layout->header = view(
                $this->theme_path . '.dashboard.headers.header1',
                ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]
            )->with('continuewrleftforhome', $continuewrleftforhome);
        } else {
            $this->layout->header = view(
                $this->theme_path . '.dashboard.headers.header1',
                ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]
            );
        }
        $programs = $this->getPrograms(1, 12, false, true);
        $posts = $this->getPosts($page, $limit);
        $quizzes = $this->getAssessments($page, $limit);
        $this->layout->content = view(
            $this->theme_path . '.dashboard.template13.dashboard',
            ['site' => $this->site, 'general' => SiteSetting::module('General'),
            'lhs_menu_settings' => SiteSetting::module('LHSMenuSettings'),
            'programs' => $programs, 'posts' => $posts, 'quizzes' => $quizzes]
        );
    }

    public function getTemplate14()
    {
        try {
            $data = ['status' => true, 'results' => $this->program_service->getCategoryWiseChannels()];
        } catch (NoProgramAssignedException $e) {
            $data = ['status' => false, 'message' => trans("dashboard.$this->site.no_channels")];
        } catch (CategoryNotFoundException $e) {
            $data = ['status' => false, 'message' => trans("dashboard.$this->site.no_channels")];
        } catch (RelationNotFoundException $e) {
            $data = ['status' => false, 'message' => trans("dashboard.$this->site.no_channels")];
        }
        $crumbs = [
            'Dashboard' => '',
        ];
        if (Auth::check() && config('app.show_complete_functionalities')) {
            $continuewrleftforhome = MyActivity::getContinueWrULeft(Auth::user()->uid);
            if (isset($continuewrleftforhome) && !empty($continuewrleftforhome) && $continuewrleftforhome > 0) {
                $continuewrleftforhome = $continuewrleftforhome[0]['url'];
            } else {
                $continuewrleftforhome = null;
            }
            $this->layout->header = view(
                $this->theme_path . '.dashboard.headers.header1',
                ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]
            )->with('continuewrleftforhome', $continuewrleftforhome);
        } else {
            $this->layout->header = view(
                $this->theme_path . '.dashboard.headers.header1',
                ['lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]
            );
        }
        $this->layout->breadcrumbs = Common::getEFEBreadCrumbs($crumbs);
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->content = view(
            $this->theme_path . '.dashboard.template14.dashboard',
            ['data' => $data, 'site' => $this->site,
            'general' => SiteSetting::module('General'),
            'lhs_menu_settings' => SiteSetting::module('LHSMenuSettings')]
        );
    }

    public function getSurveys($page = 1, $limit = 5)
    {
        try {
            return ['status' => true, 'result' => $this->survey_attempt_service->getAllIncompleteSurveys($page, $limit)];
        } catch (NoProgramAssignedException $e) {
            return ['status' => false, 'message' => trans("dashboard.$this->site.no_surveys")];
        }
    }

    public function getAssignments($page = 1, $limit = 5)
    {
        try {
            $user_assignment_rel = collect($this->assignment_service->getAllAssignmentsAssigned());
        } catch (\Exception $e) {
            Log::info($e);
            $user_assignment_rel = collect(['seq_assignments' => [], 'assignment_list' => [], 'feed_assignment_list' => []]);
        }
        $seqAssignments = $user_assignment_rel->get('seq_assignments', []);

        $assignment_list = array_unique($user_assignment_rel->get('assignment_list', []));

        // code for completed assignments
        $attempted_data = $this->assignment_attempt_service->getAssignmentAttemptByUserAndAssignmentIds(
                (int)Auth::user()->uid,
                $assignment_list
        );
        $attempt = [];
        $draft = [];
        foreach ($attempted_data as $value) {
            if($value->submission_status == SubmissionType::SAVE_AS_DRAFT){
                $draft[] = $value->assignment_id;
            } else {
                $attempt[] = $value->assignment_id;
            }
        }
        $count['attempted'] = count($attempt);

        // code for unattempted assignments
        $myUAAssignmentIds = [];
        $myUAAssignmentIds = array_diff($assignment_list, $attempt);
        $nonSquAssignments = array_diff($myUAAssignmentIds, $seqAssignments);
        $filter_params = [
            "id" => $nonSquAssignments,
            "status" => "ACTIVE",
            "cutoff_time" => time(),
            "start" => (int)$page-1,
            "limit" => (int)$limit,
            "start_time" => time(),
        ];
        $assignment_details = $this->assignment_service->getAssignments($filter_params, ["start_time" => "desc"]);
        foreach ($assignment_details as $value) {
            $row = new \stdClass;
            $row->id = $value->id;
            $row->assignment_name = $value->name;
            $row->start_time = $value->start_time->timezone(Auth::user()->timezone)->format('d M Y');
            $row->end_time = $value->end_time->timezone(Auth::user()->timezone)->format('d M Y');
            $row->cutoff_time = $value->cutoff_time->timezone(Auth::user()->timezone)->format('d M Y');
            if (empty($value->start_time)) {
                $row->start_time = 0;
            }
            if (empty($value->end_time)) {
                $row->end_time = 0;
            }
            if (empty($value->cutoff_time)) {
                $row->cutoff_time = 0;
            }
            $data[] = $row;
        }
        if (empty($data)) {
            return ['status' => false, 'message' => trans("dashboard.$this->site.no_assignments")];
        }
        return ['status' => true, 'result' => $data];

    }
}
