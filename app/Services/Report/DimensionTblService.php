<?php namespace App\Services\Report;

use App\Model\Announcement;
use App\Model\DimensionAnnouncements;
use App\Model\DimensionChannel;
use App\Model\DimensionUser;
use App\Model\Packet;
use App\Model\Program;
use App\Model\User;
use App\Model\UserGroup;
use App\Model\UserGroup\Repository\IUserGroupRepository;
use App\Model\Quiz\IQuizRepository;
use App\Services\Program\IProgramService;
use Carbon\Carbon;
use Config;
use Exception;
use Log;

/**
 * Class DimensionTblService
 * @package App\Services\Report
 */
class DimensionTblService implements IDimensionTblService
{
    /**
     * @var IMongoBulkInUpService
     */
    private $mongo_service;

    /**
     * @var IQuizRepository;
     */
    private $quiz_repository;

    /**
     * @var IProgramService
     */
    private $program_serv;

    /**
     * @var IUserGroupRepository
     */
    private $ug_repository;

    /**
     * DimensionTblService constructor.
     * @param IMongoBulkInUpService $mongo_service
     */
    public function __construct(
        IMongoBulkInUpService $mongo_service,
        IProgramService $program_serv,
        IQuizRepository $quiz_repository,
        IUserGroupRepository $ug_repository
    ) {
        $this->mongo_service = $mongo_service;
        $this->program_serv = $program_serv;
        $this->quiz_repository = $quiz_repository;
        $this->ug_repository = $ug_repository;
    }

    /**
     *inheritdoc
     */
    public function dimensionUser()
    {
        set_time_limit(3600);
        $cronId = $this->mongo_service->cronLog('dimension user', 'start');
        try {
            $batch_limit = config('app.bulk_insert_limit');
            $total_no_users = User::getActiveUsersDetails(true);
            $total_batchs = intval($total_no_users / $batch_limit);
            $record_set = 0;
            $programs = [];
            do {
                $start = $record_set * $batch_limit;
                $bulk_update = [];
                $users = User::getActiveUsersDetails(false, $start, $batch_limit);
                foreach ($users as $user) {
                    $data = [];
                    $data['channelIds'][] = array_get($user, 'relations.user_feed_rel', []);
                    $data['quizIds'][] = array_get($user, 'relations.user_quiz_rel', []);
                    $data['groupIds'][] = array_get($user, 'relations.active_usergroup_user_rel', []);
                    $data['mediaIds'][] = array_get($user, 'relations.user_media_rel', []);
                    try {
                        $programs = $this->program_serv->getAllProgramsAssignedToUser($user['uid']);
                    } catch (Exception $e) {
                        $programs['channel_ids'] = [];
                    }
                    $ug_ids = array_flatten($data['groupIds']);
                    if (!empty($ug_ids)) {
                        $ug_details = $this->ug_repository->get(['ugid' => $ug_ids], ['relations']);
                        $ug_quiz_ids = [];
                        $ug_details->map(function ($item) use (&$ug_quiz_ids) {
                            $ug_quiz_ids[] = array_get($item->relations, 'usergroup_quiz_rel', []);
                        });
                        $data['quizIds'][] = array_flatten($ug_quiz_ids);
                    }
                    $temp = [
                        'user_id' => $user['uid'],
                        'status' => $user['status'],
                        'user_name' => $user['username'],
                        'usergroup_ids' => $ug_ids,
                        'channel_ids' => array_values(array_map('intval', array_unique($programs['channel_ids']))),
                        'quiz_ids' => array_flatten($data['quizIds']),
                        'media_ids' => array_flatten($data['mediaIds'])
                    ];
                    $bulk_update[] = [
                        ['user_id' => $user['uid']],
                        ['$set' => $temp],
                        ['multi' => false, 'upsert' => true]
                    ];
                }
                if (count($bulk_update) > 0) {
                    $this->mongo_service->mongoBulkUpdateProcess($bulk_update, 'dim_users');
                }
                unset($bulk_update);
                $record_set++;
            } while ($record_set <= $total_batchs);
            $this->mongo_service->cronLog('dimension user', 'success', $cronId);
        } catch (Exception $e) {
            Log::error('While dimension user table populate ::'.$e->getMessage());
            $this->mongo_service->cronLog('dimension user', 'failed: '.$e->getMessage(), $cronId);
        }
    }

    /**
     *inheritdoc
     */
    public function dimensionChannel()
    {
        $cronId = $this->mongo_service->cronLog('dimension channel', 'start');
        try {
            $channel_slugs = [];
            $channelIds = [];
            $channel_name = [];
            $post_ids = [];
            $post_count = 0;
            $items_detail = [];
            $bulk_ary_up = [];
            $channel_details = Program::getProgramsForReports();
            foreach ($channel_details as $specific_channel) {
                array_push($channel_slugs, $specific_channel['program_slug']);
                array_push($channelIds, $specific_channel['program_id']);
                array_push($channel_name, $specific_channel['program_title']);
                $post_details = Packet::getAllPackets($specific_channel['program_slug']);
                $post_ids = array_pluck($post_details, 'packet_id');
                $data = [
                    'channel_id' => $specific_channel['program_id'],
                    'channel_name' => $specific_channel['program_title'],
                    'channel_slug' => $specific_channel['program_slug'],
                    'post_count' => count($post_ids),
                    'post_ids' => $post_ids,
                    'program_type' => $specific_channel['program_type'],
                    'program_sub_type' => isset($specific_channel['program_sub_type']) ?
                        $specific_channel['program_sub_type'] : '',
                    'parent_id' => isset($specific_channel['parent_id']) ?
                        $specific_channel['parent_id'] : 0,
                    'user_count' => DimensionUser::where('channel_ids', '=', $specific_channel['program_id'])
                        ->count(),
                    'short_name' => array_get($specific_channel, 'program_shortname', ''),
                    'update_date' => time(),
                ];
                $temp = [];
                $temp = [
                    ['channel_id' => $specific_channel['program_id']],
                    ['$set' => $data],
                    ['multi' => false, 'upsert' => true,]
                ];
                $bulk_ary_up[] = $temp;
                if (count($bulk_ary_up) > (int)Config::get('app.bulk_insert_limit')) {
                    $res = $this->mongo_service->mongoBulkUpdateProcess($bulk_ary_up, 'dim_channels');
                    if ($res) {
                        $bulk_ary_up = [];
                    }
                }
                $items_detail = [];
                $post_count = 0;
                $post_ids = [];
            }
            if (count($bulk_ary_up) > 0) {
                $this->mongo_service->mongoBulkUpdateProcess($bulk_ary_up, 'dim_channels');
            }
            $this->mongo_service->cronLog('dimension channel', 'success', $cronId);
        } catch (Exception $e) {
            Log::error('While dimension channel table populate ::'.$e->getMessage());
            $this->mongo_service->cronLog('dimension channel', 'failed: '.$e->getMessage(), $cronId);
        }
    }

    /**
     * inheritdoc
     */
    public function dimensionAnnouncement($start_date = 0, $end_date = 0)
    {
        $cronId = $this->mongo_service->cronLog('dimension announcement', 'start');
        try {
            if ($start_date > 0 && $end_date > 0) {
                $announce_details = Announcement::getInBTWCreateAndUpdateAnnouncement($start_date, $end_date);
            } else {
                $announce_details = Announcement::getInBTWCreateAndUpdateAnnouncement();
            }
            foreach ($announce_details as $announcement) {
                if (!empty($announcement)) {
                    $user_ids = [];
                    $cf_ids = [];
                    $ugids = [];
                    switch ($announcement['announcement_for']) {
                        case 'public':
                            $user_ids = User::getUserids()->toArray();
                            break;
                        case 'registerusers':
                            $user_ids = User::getUserids()->toArray();
                            break;
                        case 'users':
                            if (isset($announcement['relations']['active_user_announcement_rel']) && !empty($announcement['relations']['active_user_announcement_rel'])) {
                                $user_ids = $announcement['relations']['active_user_announcement_rel'];
                            }
                            break;
                        case 'usergroup':
                            if (isset($announcement['relations']['active_usergroup_announcement_rel']) && !empty($announcement['relations']['active_usergroup_announcement_rel'])) {
                                $ugids = $announcement['relations']['active_usergroup_announcement_rel'];
                            }
                            break;
                        case 'cfusers':
                            if (isset($announcement['relations']['active_contentfeed_announcement_rel']) && !empty($announcement['relations']['active_contentfeed_announcement_rel'])) {
                                $cf_ids = $announcement['relations']['active_contentfeed_announcement_rel'];
                            }
                            break;
                        case 'users & usergroup':
                            if (isset($announcement['relations']['active_user_announcement_rel']) && !empty($announcement['relations']['active_user_announcement_rel'])) {
                                $user_ids = $announcement['relations']['active_user_announcement_rel'];
                            }
                            if (isset($announcement['relations']['active_usergroup_announcement_rel']) && !empty($announcement['relations']['active_usergroup_announcement_rel'])) {
                                $ugids = $announcement['relations']['active_usergroup_announcement_rel'];
                            }
                            break;
                        case 'usergroup & cfusers':
                            if (isset($announcement['relations']['active_usergroup_announcement_rel']) && !empty($announcement['relations']['active_usergroup_announcement_rel'])) {
                                $ugids = $announcement['relations']['active_usergroup_announcement_rel'];
                            }
                            if (isset($announcement['relations']['active_contentfeed_announcement_rel']) && !empty($announcement['relations']['active_contentfeed_announcement_rel'])) {
                                $cf_ids = $announcement['relations']['active_contentfeed_announcement_rel'];
                            }
                            break;
                        case 'users & cfusers':
                            if (isset($announcement['relations']['active_user_announcement_rel']) && !empty($announcement['relations']['active_user_announcement_rel'])) {
                                $user_ids = $announcement['relations']['active_user_announcement_rel'];
                            }
                            if (isset($announcement['relations']['active_contentfeed_announcement_rel']) && !empty($announcement['relations']['active_contentfeed_announcement_rel'])) {
                                $cf_ids = $announcement['relations']['active_contentfeed_announcement_rel'];
                            }
                            break;
                        case 'users & usergroup & cfusers':
                            if (isset($announcement['relations']['active_user_announcement_rel']) && !empty($announcement['relations']['active_user_announcement_rel'])) {
                                $user_ids = $announcement['relations']['active_user_announcement_rel'];
                            }
                            if (isset($announcement['relations']['active_usergroup_announcement_rel']) && !empty($announcement['relations']['active_usergroup_announcement_rel'])) {
                                $ugids = $announcement['relations']['active_usergroup_announcement_rel'];
                            }
                            if (isset($announcement['relations']['active_contentfeed_announcement_rel']) && !empty($announcement['relations']['active_contentfeed_announcement_rel'])) {
                                $cf_ids = $announcement['relations']['active_contentfeed_announcement_rel'];
                            }
                            break;
                        default:
                            $user_ids = [];
                            $ugids = [];
                            $cf_ids = [];
                            break;
                    }
                    if (!empty($cf_ids)) {
                        $cf_ids = array_unique($cf_ids);
                        foreach ($cf_ids as $cf_id) {
                            $program = Program::getProgramDetailsByID($cf_id)->toArray();
                            if (isset($program['relations']['active_usergroup_feed_rel']) && !empty($program['relations']['active_usergroup_feed_rel'])) {
                                $ugids = array_merge($ugids, $program['relations']['active_usergroup_feed_rel']);
                            }
                            if (isset($program['relations']['active_user_feed_rel']) && !empty($program['relations']['active_user_feed_rel'])) {
                                $user_ids = array_merge($user_ids, $program['relations']['active_user_feed_rel']);
                            }
                        }
                    }
                    if (!empty($ugids)) {
                        $ugids = array_unique($ugids);
                        foreach ($ugids as $ugid) {
                            $usergorup = UserGroup::getActiveUserGroupsUsingID($ugid);
                            if (isset($usergorup[0]['relations']['active_user_usergroup_rel']) && !empty($usergorup[0]['relations']['active_user_usergroup_rel'])) {
                                $user_ids = array_merge($user_ids, $usergorup[0]['relations']['active_user_usergroup_rel']);
                            }
                        }
                    }
                }
                $total_no_user_list = [];
                $total_no_user = 0;

                if (!empty($user_ids)) {
                    $user_ids = array_unique($user_ids);
                    $total_no_user = count($user_ids);
                    $total_no_user_list = $user_ids;
                } else {
                    $total_no_user = 0;
                    $total_no_user_list = [];
                }
                $total_no_user_list = array_values($total_no_user_list);
                $ary_diff = [];
                $nof_user_viewed = [];
                $nof_user_n_viewed = [];
                if (isset($announcement['readers']['user']) && !empty($announcement['readers']['user'])) {
                    $nof_user_viewed = $announcement['readers']['user'];
                    $no_of_user_viewd = count($nof_user_viewed);
                    if (!empty($nof_user_viewed)) {
                        $ary_diff = array_diff($total_no_user_list, $nof_user_viewed);
                    }
                } else {
                    $nof_user_n_viewed = $total_no_user_list;
                    $no_of_user_viewd = 0;
                    $ary_diff = [];
                }
                if (!empty($ary_diff)) {
                    foreach ($ary_diff as $value) {
                        array_push($nof_user_n_viewed, $value);
                    }
                    $no_of_user_not_viewd = count($ary_diff);
                } else {
                    $nof_user_n_viewed = $total_no_user_list;
                    $no_of_user_not_viewd = count($total_no_user_list);
                }

                if (DimensionAnnouncements::getCheckAnnouncementExist($announcement['announcement_id'])) {
                    $data = [
                        'view_user_count' => $no_of_user_viewd,
                        'not_view_user_count' => $no_of_user_not_viewd,
                        'no_of_user_viewed_list' => $nof_user_viewed,
                        'no_of_user_not_viewed_list' => $nof_user_n_viewed,
                        'update_date' => time(),
                    ];
                    if (isset($announcement['updated_at']) && is_string($announcement['updated_at'])) {
                        $data['updated_at'] = strtotime($announcement['updated_at']);
                    } else {
                        $data['updated_at'] = $end_date;
                    }

                    DimensionAnnouncements::getupdatedata($data, $announcement['announcement_id']);
                } else {
                    $data = [
                        'id' => DimensionAnnouncements::getNextSequence(),
                        'announcement_id' => $announcement['announcement_id'],
                        'announcement_title' => $announcement['announcement_title'],
                        'view_user_count' => $no_of_user_viewd,
                        'not_view_user_count' => $no_of_user_not_viewd,
                        'no_of_user_viewed_list' => $nof_user_viewed,
                        'no_of_user_not_viewed_list' => $nof_user_n_viewed,
                        'create_date' => time(),
                    ];
                    if (isset($announcement['created_at']) && is_string($announcement['created_at'])) {
                        $data['created_at'] = strtotime($announcement['created_at']);
                    } else {
                        $data['created_at'] = $announcement['created_at'];
                    }
                    if (isset($announcement['updated_at']) && is_string($announcement['updated_at'])) {
                        $data['updated_at'] = strtotime($announcement['updated_at']);
                    } else {
                        $data['updated_at'] = isset($announcement['updated_at']) ? $announcement['updated_at'] : time();
                    }
                    DimensionAnnouncements::getInsertData($data);
                }
            }
            $this->mongo_service->cronLog('dimension announcement', 'success', $cronId);
        } catch (Exception $e) {
            Log::error('While dimension announcement table populate ::'.$e->getMessage());
            $this->mongo_service->cronLog('dimension announcement', 'failed: '.$e->getMessage(), $cronId);
        }
    }

    /**
     *inheritdoc
     */
    public function dimensionChannelUserQuiz()
    {
        $cronId = $this->mongo_service->cronLog('dimension channel user quiz', 'start');
        try {
            $channel_user = [];
            $channel_quiz = [];
            $temp_user = [];
            $temp_quiz = [];
            $bulk_ary_up = [];
            $channel_details = DimensionChannel::getChannelSlugsNameAndIds();
            foreach ($channel_details as $channel_id) {
                $temp_user = [];
                $res_userid = DimensionUser::getUserIdByChannel($channel_id['channel_id']);
                foreach ($res_userid as $user_id) {
                    array_push($temp_user, $user_id['user_id']);
                }
                $channel_user['c_' . $channel_id['channel_id']] = $temp_user;
            }
            $quiz_rel = $this->quiz_repository->getChannelRelation();
            $temp_quiz = [];
            foreach ($quiz_rel as $quiz_rel_feed) {
                if (isset($quiz_rel_feed['relations']['feed_quiz_rel']) &&
                    !empty($quiz_rel_feed['relations']['feed_quiz_rel'])
                ) {
                    $channel_quiz_rel = collect(array_get($quiz_rel_feed, 'relations.feed_quiz_rel', []));
                    $channel_ids = $channel_quiz_rel->filter(function ($value, $key) {
                        if (!empty($value)) {
                            return $key;
                        } else {
                            return false;
                        }
                    })->keys()->all();
                    if (empty($channel_ids)) {
                        continue;
                    }
                    foreach ($channel_ids as $channel_id) {
                        $key_ch = 'c_' . $channel_id;
                        if (array_key_exists($key_ch, $channel_quiz)) {
                            $temp_quiz = $channel_quiz[$key_ch];
                            array_push($temp_quiz, $quiz_rel_feed['quiz_id']);
                            $channel_quiz[$key_ch] = $temp_quiz;
                        } else {
                            $temp_quiz = [];
                            array_push($temp_quiz, $quiz_rel_feed['quiz_id']);
                            $channel_quiz[$key_ch] = $temp_quiz;
                        }
                    }
                }
            }
            foreach ($channel_details as $channel_detail) {
                $key_make = 'c_' . $channel_detail['channel_id'];
                if (array_key_exists($key_make, $channel_quiz)) {
                    $channel_detail['quiz_ids'] = $channel_quiz[$key_make];
                }
                if (array_key_exists($key_make, $channel_user)) {
                    $channel_detail['user_ids'] = $channel_user[$key_make];
                }
                if (isset($channel_detail['_id'])) {
                    unset($channel_detail['_id']);
                }
                $channel_detail['update_date'] = time();
                $temp = [];
                $temp = [
                    ['channel_id' => $channel_detail['channel_id']],
                    ['$set' => $channel_detail],
                    ['multi' => false, 'upsert' => true]
                ];
                $bulk_ary_up[] = $temp;
                $channel_detail = [];
                if (count($bulk_ary_up) > (int)Config::get('app.bulk_insert_limit')) {
                    $res = $this->mongo_service->mongoBulkUpdateProcess($bulk_ary_up, 'dim_channels_user_quiz');
                    if ($res) {
                        $bulk_ary_up = [];
                    }
                }
            }
            if (count($bulk_ary_up) > 0) {
                $this->mongo_service->mongoBulkUpdateProcess($bulk_ary_up, 'dim_channels_user_quiz');
            }
            $this->mongo_service->cronLog('dimension channel user quiz', 'success', $cronId);
        } catch (Exception $e) {
            Log::error('While dimension Channel User Quiz table populate ::'.$e->getMessage());
            $this->mongo_service->cronLog('dimension channel user quiz', 'failed: '.$e->getMessage(), $cronId);
        }
    }
}
