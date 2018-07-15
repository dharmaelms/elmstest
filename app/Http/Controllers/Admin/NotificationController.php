<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminBaseController;
use App\Model\Notification;
use App\Model\NotificationLog;
use App\Model\QuizAttempt;
use App\Model\QuizReminderLog;
use App\Model\SiteSetting;
use App\Model\User;
use App\Services\Post\IPostService;
use App\Services\Program\IProgramService;
use App\Services\Report\MongoBulkInUpService;
use App\Services\Quiz\IQuizService;
use App\Services\User\IUserService;
use App\Services\UserGroup\IUserGroupService;
use Carbon;
use Exception;
use Log;
use Timezone;
use URL;

class NotificationController extends AdminBaseController
{
    /**
     * @var MongoBulkInUpService
     */
    public $mongoService;

    /**
     * $program_service
     * @var App\Services\Program\IProgramService
     */
    private $program_service;

    /**
     * @var \App\Model\User\IUserService
     */
    private $user_service;

    /**
     * @var \App\Services\Quiz\IQuizService
     */
    private $quiz_service;

    /**
     * @var array
     */
    private $notifications_ary;

    /**
     * @var App\Services\Post\IPostService;
     */
    private $post_service;

    /**
     * @var App\Services\UserGroup\IUserGroupService
     */
    private $ug_service;
    /**
     * Create instance
     * @param MongoBulkInUpService $mongoService
     */
    public function __construct(
        MongoBulkInUpService $mongoService,
        IPostService $post_service,
        IProgramService $program_service,
        IQuizService $quiz_service,
        IUserGroupService $ug_service,
        IUserService $user_service
    ) {
        $this->mongoService = $mongoService;
        $this->notifications_ary = [];
        $this->post_service = $post_service;
        $this->program_service = $program_service;
        $this->quiz_service = $quiz_service;
        $this->ug_service = $ug_service;
        $this->user_service = $user_service;
    }

    /**
     * Push the notifications from notificationlog table
     * to notifications table
     */
    public function pushNotificationByCron()
    {
        try {
            $notifyLogs = NotificationLog::getGetUnProcessNotifications();
            if (empty($notifyLogs)) {
                return true;
            }
            $notifyMaxId = Notification::getMaxID();
            $data = [];
            foreach ($notifyLogs as $notifyLog) {
                $userIds = $notifyLog->user_ids;
                foreach ($userIds as $uid) {
                    $data[] = [
                        'notification_id' => $notifyMaxId,
                        'from_module' => $notifyLog->from_module,
                        'message' => $notifyLog->message,
                        'user_id' => (int)$uid,
                        'is_read' => false,
                        'created_at' => time()
                    ];
                    $notifyMaxId += 1;
                }
                if (count($data) > (int)config('app.bulk_insert_limit')) {
                    $this->mongoService->mongoBulkInsertProcess($data, 'notifications');
                    $data = [];
                }
                NotificationLog::deleteNotification($notifyLog->_id);
            }
            if (!empty($data)) {
                $this->mongoService->mongoBulkInsertProcess($data, 'notifications');
            }
        } catch (Exception $e) {
            Log::info('Cron - Push Notification By Cron failed - ' . $e->getMessage());
        }
    }

    /**
     * Moving old records from notifications collections to
     * notification archive collections.
     */
    public function flushNotificationToArchive()
    {
        try {
            $limitPriod = (int)SiteSetting::module('Notifications and Announcements', 'flush_notifications_days_limit');
            $time_line = Carbon::today()->subDay((int)$limitPriod)->timestamp;
            Notification::getDeleteFlushedNotification($time_line);
        } catch (Exception $e) {
            Log::info('Cron - Flush Notification To Arc failed - ' . $e->getMessage());
        }
    }

    /**
     * sendQuizReminder
     * @param  array $date
     * @return void
     */
    public function sendQuizReminder($date = null)
    {
        $reminders = SiteSetting::module('QuizReminders')->setting;
        $specific_day_reminder = 0;
        foreach ($reminders as $key => $reminder) {
            if (array_get($reminder, 'reminder_status') == 'on') {
                if (!is_array($date)) {
                    $reminder_day = array_get($reminder, 'reminder_day', 0);
                    $date = [
                        'start' => Carbon::today(config('app.default_timezone'))
                            ->addDays($reminder_day)->getTimestamp(),
                        'end' => Carbon::today(config('app.default_timezone'))
                            ->addDays($reminder_day)->endOfDay()->getTimestamp()
                    ];
                } else {
                    $specific_day_reminder++;
                }
                $quiz_type = array_get($reminder, 'quiz_type', []);
                if (in_array('on', $quiz_type)) {
                    $this->prepareQuizReminder($date, $quiz_type);
                    $this->notificationForGeneralQuizzes($key);
                    $this->notificationForQuestionGeneratorQuiz($key);
                    $this->notificationForPrograms($key);
                    $this->notifications_ary = [];
                }
                if ($specific_day_reminder == 1) {
                    break;
                }
            }
            $date = null;
        }
    }

    /**
     * prepareQuizReminder
     * @param  array  $date
     * @param  array  $reminder_filter
     * @return void
     */
    public function prepareQuizReminder(array $date, array $reminder_filter)
    {
        $expire_program_ids = $subscribed_pids = [];
        $programs_quizzes = $programs_details = collect([]);
        $sub_user_ids = $user_ids = $ug_ids = $quiz_ids = [];
        /* Below lines of code to get the expiried subscribed channels */
        $user_subs_programs = $this->user_service->getExpireSubscribedChannels($date)->toArray();
        if (!empty($user_subs_programs)) {
            $sub_user_ids = array_pluck($user_subs_programs, 'uid');
            $subscribed_pids = array_unique(
                call_user_func_array('array_merge', array_pluck($user_subs_programs, 'program_id'))
            );
        }
        /* Below lines of code to get the expiried internal channels */
        $expire_program = $this->program_service->getAboutExpirePrograms($date);
        if (!empty($expire_program)) {
            $expire_program_ids = $expire_program->lists('program_id');
        }
        $p_ids = array_unique(array_merge($expire_program_ids->toArray(), $subscribed_pids));
        if (!empty($p_ids)) {
            $programs_quizzes = collect($this->program_service->getProgramPacketsQuizzs($p_ids));
            $programs_quizzes = $programs_quizzes->keyBy('program_id');
            $programs_details = collect($this->program_service->getProgramsDetailsById($p_ids)->toArray());
            $program_slugs = array_pluck($programs_details, 'program_slug');
            $packet_details = $this->post_service->getPacketsAssessement($program_slugs);
            if (!empty($programs_quizzes)) {
                foreach ($programs_quizzes as $program_quizzes) {
                    $user_relations = array_get($program_quizzes, 'user_relations', []);
                    if (!empty($user_relations)) {
                        $temp = call_user_func_array('array_merge', $user_relations);
                        if (!empty($temp)) {
                            $user_ids = array_merge($user_ids, $temp);
                        }
                    }
                    $ug_relations = array_get($program_quizzes, 'ug_relations', []);
                    if (!empty($ug_relations)) {
                        $temp = call_user_func_array('array_merge', $ug_relations);
                        if (!empty($temp)) {
                            $ug_ids = array_merge($ug_ids, $temp);
                        }
                    }
                    if (!empty(array_get($program_quizzes, 'quiz_ids', []))) {
                        $quiz_ids = array_merge($quiz_ids, array_get($program_quizzes, 'quiz_ids', []));
                    }
                }
            }
            if (!empty($sub_user_ids)) {
                $user_ids = array_unique(array_merge($user_ids, $sub_user_ids));
            }
            if (!empty($quiz_ids)) {
                $quiz_details = $this->quiz_service->getQuizzesByIds(
                    array_unique($quiz_ids),
                    $reminder_filter
                )->keyBy('quiz_id');
            }
            $user_details = $this->user_service->getUsersByUidUGid($user_ids, $ug_ids)->toArray();
            $user_subs_programs = collect($user_subs_programs)->keyBy('uid');
            foreach ($user_details as $uid => $user_detail) {
                $subscribed_prog_user = [];
                $users_ugids = array_get($user_detail, 'relations.active_usergroup_user_rel', []);
                $subscribed_prog = $user_subs_programs->get($uid);
                if (!is_null($subscribed_prog) && !empty($subscribed_prog)) {
                    $subscribed_prog_user = array_get($subscribed_prog, 'program_id', []);
                }
                $user_rel_program = $programs_quizzes->filter(function ($value, $key) use ($uid, $users_ugids, $subscribed_prog_user) {
                    if (isset($value['user_relations']) && !empty($value['user_relations'])) {
                        $user_p_ids = call_user_func_array('array_merge', $value['user_relations']);
                        if (in_array($uid, $user_p_ids)) {
                            return $value;
                        }
                    }
                    if (isset($value['ug_relations']) && !empty($value['ug_relations'])) {
                        $ug_p_ids = call_user_func_array('array_merge', $value['ug_relations']);
                        if (!empty(array_intersect($users_ugids, $ug_p_ids))) {
                            return $value;
                        }
                    }
                    if (in_array($key, $subscribed_prog_user)) {
                        return $value;
                    }
                    return false;
                });
                
                $attempted_quizzes = QuizAttempt::getAttemptedQuizzIdsByUID($uid)->keyBy('quiz_id');
                foreach ($user_rel_program as $key => $value) {
                    $slug = array_get($programs_details->get((int)$key), 'program_slug');
                    $packet_detail = $packet_details->get($slug);
                    $quizzes_name = [];
                    foreach ($value['quiz_ids'] as $qid) {
                        if ($attempted_quizzes->has((int)$qid)) {
                            continue;
                        }
                        if (isset($quiz_details->get((int)$qid)->quiz_name)) {
                            foreach ($packet_detail as $each_pack) {
                                if (in_array($qid, array_get($each_pack, 'quiz_ids'))) {
                                    $quizzes_name[$quiz_details->get((int)$qid)->quiz_name] = [
                                        array_get($each_pack, 'packet_name', ''),
                                        array_get($each_pack, 'packet_slug', ''),
                                        (int)$qid,
                                        isset($quiz_details->get((int)$qid)->type) ?
                                            $quiz_details->get((int)$qid)->type : 'general'
                                    ];
                                    break;
                                }
                            }
                        }
                    }
                    if (!empty($quizzes_name)) {
                        $this->setNotifications(
                            [
                                $uid,
                                array_get($user_detail, 'email'),
                                array_get($user_detail, 'firstname'),
                                array_get($user_detail, 'lastname')
                            ],
                            $quizzes_name,
                            [(int)$key, array_get($programs_details->get((int)$key), 'program_title')],
                            Timezone::convertFromUTC(
                                array_get($date, 'end', time()),
                                array_get($user_detail, 'timezone', config('app.default_timezone'))
                            )
                        );
                    }
                }
            }
        }
        //Direct quizzes reminder notifications
        $quiz_ids = [];
        $ug_details = $user_details = collect([]);
        $direct_x_quizzes = $this->quiz_service->getAboutExpireQuizzes($date, $reminder_filter)->toArray();
        if (!empty($direct_x_quizzes)) {
            $direct_x_quizzes = collect($direct_x_quizzes);
            $quiz_ids = $direct_x_quizzes->keys()->toArray();
            $user_ids = $direct_x_quizzes->lists('relations.active_user_quiz_rel')->collapse()->unique();
            $ug_ids = $direct_x_quizzes->lists('relations.active_usergroup_quiz_rel')->collapse()->unique();
            if (!$user_ids->isEmpty()) {
                $user_details = collect($this->user_service->getUsersByUidUGid($user_ids, $ug_ids)->toArray());
            }
            if (!$ug_ids->isEmpty()) {
                $ug_details = $this->ug_service->getUsergroupsByIds($ug_ids)->keyBy('ugid');
            }

            if (!empty($user_details)) {
                foreach ($user_details as $uid => $user_detail) {
                    $users_ugids = array_get($user_detail, 'relations.active_usergroup_user_rel', []);
                    $users_feeds = array_get($user_detail, 'relations.user_feed_rel', []);
                    $ug_feeds = collect([]);
                    if (!empty($users_ugids)) {
                        $ug_details->filter(function ($value, $key) use ($users_ugids, &$ug_feeds) {
                            if (in_array($key, $users_ugids) && !empty(array_get($value->relations, 'usergroup_feed_rel', []))) {
                                $ug_feeds = $ug_feeds->merge(array_get($value->relations, 'usergroup_feed_rel', []));
                            }
                        });
                        if (!$ug_feeds->isEmpty()) {
                            $users_feeds =array_merge($users_feeds, $ug_feeds->toArray());
                        }
                        if (!empty($users_feeds)) {
                            $users_feeds = array_map('intval', $users_feeds);
                        }
                    }
                    $users_dir_quizzes = $direct_x_quizzes->filter(function ($value, $key) use ($uid, $users_ugids, $users_feeds) {
                        $quiz_feeds = array_get($value, 'relations.feed_quiz_rel', []);
                        $is_feeds_match = true;
                        if (!empty($quiz_feeds) && !empty($users_feeds)) {
                            $quiz_feeds = array_keys($quiz_feeds);
                            $is_feeds_match = empty(array_intersect($quiz_feeds, $users_feeds));
                            if (!$is_feeds_match) {
                                return false;
                            }
                        }
                        if (in_array($uid, array_get($value, 'relations.active_user_quiz_rel', []))) {
                            return $value;
                        }
                        if (!empty(array_intersect($users_ugids, array_get($value, 'relations.active_usergroup_quiz_rel', [])))) {
                            return $value;
                        }
                        return false;
                    });

                    if (!$users_dir_quizzes->isEmpty()) {
                        $attempted_quizzes = QuizAttempt::getAttemptedQuizzIdsByUID($uid)->keyBy('quiz_id');
                        foreach ($users_dir_quizzes as $qid => $users_dir_quiz) {
                            if ($attempted_quizzes->has((int)$qid)) {
                                continue;
                            }
                            //quiz end time and date
                            $this->setNotifications(
                                [
                                    $uid,
                                    array_get($user_detail, 'email'),
                                    array_get($user_detail, 'firstname'),
                                    array_get($user_detail, 'lastname')
                                ],
                                [
                                    array_get($users_dir_quiz, 'quiz_name', ''),
                                    (int)$qid,
                                    array_get($users_dir_quiz, 'type', 'general'),
                                ],
                                [],
                                Timezone::convertFromUTC(
                                    strtotime(array_get($users_dir_quiz, 'end_time', '')),
                                    array_get($user_detail, 'timezone', config('app.default_timezone')),
                                    config('app.date_ymd_his')
                                )
                            );
                        }
                    }
                }
            }
        }
        unset($expire_program_ids);
        unset($subscribed_pids);
        unset($programs_quizzes);
        unset($programs_details);
        unset($sub_user_ids);
        unset($user_ids);
        unset($ug_ids);
        unset($quiz_ids);
        unset($user_subs_programs);
        unset($expire_program);
        unset($direct_x_quizzes);
        unset($user_details);
        unset($quiz_feeds);
    }

    /**
     * setNotifications
     * @param array $user
     * @param array $quizzes
     * @param array $program
     * @param String $quiz_end_time
     */
    public function setNotifications($user, $quizzes, $program, $quiz_end_time)
    {
        $this->notifications_ary[] = [
            'user' => $user,
            'quiz_name' => $quizzes,
            'program' => $program,
            'end_time' => $quiz_end_time,
            'module' => 'Assessment'
        ];
    }

    /**
     * notificationForPrograms
     * @param  String $reminder_name
     * @return void
     */
    public function notificationForPrograms($reminder_name)
    {
        $program_quizzes = $notification_log = $reminder_log = [];
        foreach ($this->notifications_ary as $notify_data) {
            if (!empty($notify_data['program'])) {
                $program_quizzes['user_id'] = array_get($notify_data, 'user.0');
                $program_quizzes['email_id'] = array_get($notify_data, 'user.1');
                $program_quizzes['firstname'] = array_get($notify_data, 'user.2');
                $program_quizzes['lastname'] = array_get($notify_data, 'user.3');
                $program_quizzes['program_id'] = array_get($notify_data, 'program.0');
                $program_quizzes['program_name'] = array_get($notify_data, 'program.1');
                $program_quizzes['program_end_time'] = $notify_data['end_time'];
                $program_quizzes['module'] = $notify_data['module'];
                $program_quizzes['reminder_type'] = $reminder_name;
               
                $i = 1;
                $program_quizzes['quiz_and_post_list'] = [];
                $program_quizzes['quiz_id'] = [];
                $program_quizzes['post_slug'] = [];
                foreach ($notify_data['quiz_name'] as $quiz_name => $quiz_data) {
                    $program_quizzes['quiz_name_'.$i] = $quiz_name;
                    $program_quizzes['quiz_type_'.$i] = array_get($quiz_data, '3');
                    $program_quizzes['post_name_'.$i] = array_get($quiz_data, '0');
                    $program_quizzes['post_slug_'.$i] = array_get($quiz_data, '1');
                    $program_quizzes['quiz_id'][] = array_get($quiz_data, '2');
                    $program_quizzes['quiz_id_'.$i] = array_get($quiz_data, '2');
                    $program_quizzes['quiz_and_post_list'][] = trans(
                        'admin/assessment.quiz_name_and_post_name',
                        [
                            'number' => $i,
                            'quiz_name' => "<a href='". URL::to('/program/packet/' .$program_quizzes['post_slug_'.$i]. '/element/' .$program_quizzes['quiz_id_'.$i]. '/assessment') ."' >" . $program_quizzes['quiz_name_'.$i] . "</a>",
                            'post_name' => $program_quizzes['post_name_'.$i],
                            'quiz_type' => str_replace("_", " ", strtoupper($program_quizzes['quiz_type_'.$i]))
                        ]
                    );
                    $i++;
                }

                $notification_log[] = $this->setNotificationsLog(
                    [$program_quizzes['user_id']],
                    'Assessment',
                    trans(
                        'admin/assessment.quiz_reminders_assigned_through_program',
                        [
                            'reminder name' => $program_quizzes['reminder_type'],
                            'program name' => $program_quizzes['program_name'],
                            'program end date' => $program_quizzes['program_end_time'],
                            'quiz_and_post_list' => implode("\n", $program_quizzes['quiz_and_post_list'])
                        ]
                    )
                );

                $reminder_log[] = $this->setReminderLog(
                    $program_quizzes['reminder_type'],
                    $program_quizzes['module'],
                    (is_array($program_quizzes['quiz_id'])) ? $program_quizzes['quiz_id'] : [$program_quizzes['quiz_id']],
                    [$program_quizzes['user_id']],
                    (!isset($program_quizzes['program_id']) ) ?  '' : $program_quizzes['program_id']
                );

                $notify_by_email = SiteSetting::module('QuizReminders', $program_quizzes['reminder_type']);
                if ($notify_by_email['notify_by_mail'] == 'on') {
                    $this->quiz_service->sendReminderEmailNotification('reminder-notification-for-quiz-assigned-through-program', $program_quizzes);
                }
            }
        }
        $this->mongoService->mongoBulkInsertProcess($notification_log, 'notifications_log');
        $this->mongoService->mongoBulkInsertProcess($reminder_log, 'quiz_reminder_log');
    }

    /**
     * notificationForGeneralQuizzes
     * @param  String $reminder_name
     * @return void
     */
    public function notificationForGeneralQuizzes($reminder_name)
    {
        $general_quiz_data = $notification_log = $reminder_log = [];
        $base_url = config('app.url');
        foreach ($this->notifications_ary as $notify_data) {
            if (empty($notify_data['program']) && array_get($notify_data, 'quiz_name.2') == "general") {
                $general_quiz_data['user_id'] = array_get($notify_data, 'user.0');
                $general_quiz_data['email_id'] = array_get($notify_data, 'user.1');
                $general_quiz_data['firstname'] =  array_get($notify_data, 'user.2');
                $general_quiz_data['lastname'] =  array_get($notify_data, 'user.3');
                $general_quiz_data['quiz_name'] = array_get($notify_data, 'quiz_name.0');
                $general_quiz_data['quiz_id'] = array_get($notify_data, 'quiz_name.1');
                $general_quiz_data['quiz_type'] = array_get($notify_data, 'quiz_name.2');
                $general_quiz_data['quiz_end_time'] = $notify_data['end_time'];
                $general_quiz_data['module'] = $notify_data['module'];
                $general_quiz_data['reminder_type'] = $reminder_name;
                
                $notification_log[] = $this->setNotificationsLog(
                    [$general_quiz_data['user_id']],
                    'Assessment',
                    trans(
                        'admin/assessment.general_quiz_reminder_to_a_user',
                        [
                            'reminder name' => $general_quiz_data['reminder_type'],
                            'quiz name' => '<a href="' .$base_url . '/assessment/detail/' . $general_quiz_data['quiz_id']. '">' .$general_quiz_data['quiz_name']. '</a>',
                            'quiz end date' => $general_quiz_data['quiz_end_time']
                        ]
                    )
                );

                $reminder_log[] = $this->setReminderLog(
                    $general_quiz_data['reminder_type'],
                    $general_quiz_data['module'],
                    (is_array($general_quiz_data['quiz_id'])) ? $general_quiz_data['quiz_id'] : [$general_quiz_data['quiz_id']],
                    [$general_quiz_data['user_id']],
                    ''
                );

                $notify_by_email = SiteSetting::module('QuizReminders', $general_quiz_data['reminder_type']);
                if ($notify_by_email['notify_by_mail'] == 'on') {
                        $this->quiz_service->sendReminderEmailNotification('reminder-notification-for-quiz-assigned-directly-to-user', $general_quiz_data);
                }
            }
        }
        $this->mongoService->mongoBulkInsertProcess($notification_log, 'notifications_log');
        $this->mongoService->mongoBulkInsertProcess($reminder_log, 'quiz_reminder_log');
    }

    /**
     * notificationForQuestionGeneratorQuiz
     * @param  String $reminder_name
     * @return void
     */
    public function notificationForQuestionGeneratorQuiz($reminder_name)
    {
        $question_generator_data = $notification_log = $reminder_log = [];
        $base_url = config('app.url');
        foreach ($this->notifications_ary as $notify_data) {
            if (empty($notify_data['program']) && array_get($notify_data, 'quiz_name.2') == "QUESTION_GENERATOR") {
                $question_generator_data['user_id'] = array_get($notify_data, 'user.0');
                $question_generator_data['email_id'] = array_get($notify_data, 'user.1');
                $question_generator_data['firstname'] = array_get($notify_data, 'user.2');
                $question_generator_data['lastname'] = array_get($notify_data, 'user.3');
                $question_generator_data['quiz_name'] = array_get($notify_data, 'quiz_name.0');
                $question_generator_data['quiz_id'] = array_get($notify_data, 'quiz_name.1');
                $question_generator_data['quiz_type'] = array_get($notify_data, 'quiz_name.2');
                $question_generator_data['quiz_end_time'] = $notify_data['end_time'];
                $question_generator_data['module'] = $notify_data['module'];
                $question_generator_data['reminder_type'] = $reminder_name;

                $notification_log[] = $this->setNotificationsLog(
                    [$question_generator_data['user_id']],
                    'Assessment',
                    trans(
                        'admin/assessment.question_generator_reminder_to_a_user',
                        [
                            'reminder name' => $question_generator_data['reminder_type'],
                            'quiz name' => '<a href="' .$base_url . '/assessment/detail/' . $question_generator_data['quiz_id']. '">' .$question_generator_data['quiz_name']. '</a>',
                            'quiz end date' => $question_generator_data['quiz_end_time']
                        ]
                    )
                );
                
                $reminder_log[] = $this->setReminderLog(
                    $question_generator_data['reminder_type'],
                    $question_generator_data['module'],
                    (is_array($question_generator_data['quiz_id'])) ? $question_generator_data['quiz_id'] : [$question_generator_data['quiz_id']],
                    [$question_generator_data['user_id']],
                    ''
                );

                $notify_by_email = SiteSetting::module('QuizReminders', $question_generator_data['reminder_type']);
                if ($notify_by_email['notify_by_mail'] == 'on') {
                        $this->quiz_service->sendReminderEmailNotification('reminder-notification-for-question-generator-directly-to-user', $question_generator_data);
                }
            }
        }
        $this->mongoService->mongoBulkInsertProcess($notification_log, 'notifications_log');
        $this->mongoService->mongoBulkInsertProcess($reminder_log, 'quiz_reminder_log');
    }

    public function setNotificationsLog($user_ids = [], $from_module = '', $message = '')
    {
        if (!empty($user_ids) && $from_module != '' && $message != '') {
            $notificationjson = [];
            $notificationjson['user_ids'] = $user_ids;
            $notificationjson['from_module'] = $from_module;
            $notificationjson['message'] = $message;
            $notificationjson['is_send'] = false;
            $notificationjson['created_at'] = time();

            return $notificationjson;
        }
        return null;
    }

    public function setReminderLog($reminder_type, $module, $quiz_id, $user_ids, $program_id)
    {
        $reminder_log = [];
            $reminder_log['reminder_type'] = $reminder_type;
            $reminder_log['module']        =  $module;
            $reminder_log['quiz_id']       =  $quiz_id;
            $reminder_log['user_ids']      =  $user_ids;
            $reminder_log['program_id']    =  $program_id;
            $reminder_log['created_at']    =   time();
            return $reminder_log;
    }
}
