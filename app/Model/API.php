<?php

namespace App\Model;

use Carbon;
use Moloquent;
use Request;
use URL;

class API extends Moloquent
{
    protected $table = '';
    public $timestamps = false;

    public static function generateTokens()
    {
        $refreshtoken = self::generateRandomToken(41); // passing 41 will return 48 chars of token
        $accesstoken = self::generateRandomToken(41);
        while (true) {
            $return = User::where('access_token', '=', $accesstoken)
                ->orwhere('access_token', '=', $refreshtoken)
                ->orwhere('refresh_token', '=', $accesstoken)
                ->orwhere('refresh_token', '=', $refreshtoken)->count();

            if ($return == 0) {
                break;
            } else {
                $refreshtoken = self::generateRandomToken(41);
                $accesstoken = self::generateRandomToken(41);
            }
        }

        return ['refreshtoken' => $refreshtoken, 'accesstoken' => $accesstoken];
    }

    public static function generateRandomToken($len)
    {
        $chars = '012345abcdefghijklmnopABCDEFGHIJKLMNOPqrstuvwxyzQRSTUVWXYZ6789';
        srand((double)microtime() * 1000000);
        $i = 0;
        $pass = '';
        while ($i <= $len) {
            $num = rand() % 34;
            $tmp = substr($chars, $num, 1);
            $pass = $pass . $tmp;
            ++$i;
        }
        // NEW STUFF HERE
        $salt = md5('LiNK-+!S#$treT%^');
        // generate an md5 of $pass + our salt
        $verification_md5 = md5($pass . $salt);
        // $verification_code = the first 6 characters of that md5
        $verification_code = substr($verification_md5, 0, 6);
        $pass = $pass . $verification_code;

        return $pass;
    }

    public static function updateUser($data)
    {
        $users = User::where('uid', '=', (int)$data['user_id'])
            ->push(
                'api',
                ['access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'],
                    'user_device_id' => $data['user_device_id'],
                    'token_created_at' => $data['created_at'],
                ]
            );
        if (!empty($data['user_device_id'])) {
            User::where('uid', '=', (int)$data['user_id'])->update(['user_device_id' => $data['user_device_id']]);
        }
        if ($users) {
            return 1;
        } else {
            return 0;
        }
    }

    public static function fetchUserInfo($access_token)
    {
        return User::where('api.access_token', '=', $access_token)->get()->toArray();
    }

    public static function removeAccessToken($access_token)
    {
        return User::where('api.access_token', '=', $access_token)
            ->pull('api', ['access_token' => $access_token]);
    }

    public static function elementDetails($element_id = null, $element_type = null)
    {
        $data = [];
        switch ($element_type) {
            case 'media':
                $asset = Dam::getDAMSAssetsUsingAutoID((int)$element_id);
                $asset = $asset[0];
                $type = [
                    'element_type' => $element_type,
                ];
                $data['asset'] = array_merge($asset, $type);
                return $data;
                break;

            case 'assessment':
                $assessment = $quiz = Quiz::where('quiz_id', '=', (int)$element_id)
                    ->where('status', '=', 'ACTIVE')
                    ->firstOrFail();
                // print_r($assessment); die;
                if (isset($assessment->users_liked)) {
                    $users_liked = $assessment->users_liked;
                } else {
                    $users_liked = [];
                }
                $asset = [
                    'id' => $assessment->quiz_id,
                    'name' => $assessment->quiz_name,
                    'description' => $assessment->quiz_description,
                    'short_description' => '',
                    'type' => $element_type,
                    'asset_type' => '',
                    'users_liked' => $users_liked,
                    'element_type' => $element_type,
                    'attempts' => $assessment->attempts,
                    'start_time' => $assessment->start_time,
                    'end_time' => $assessment->end_time,
                    'duration' => $assessment->duration,
                    'practice_quiz' => $assessment->practice_quiz,
                    'total_mark' => $assessment->total_mark,
                    'created_by' => $assessment->created_by,
                    'created_at' => $assessment->created_at,
                    'the_attempt' => $assessment->review_options['the_attempt'],
                    'whether_correct' => $assessment->review_options['whether_correct'],
                    'marks' => $assessment->review_options['marks'],
                    'rationale' => $assessment->review_options['rationale'],
                    'correct_answer' => $assessment->review_options['correct_answer'],
                    'sectioned_quiz' => $assessment->is_sections_enabled,
                    'quiz_type' => $assessment->type,
                ];
                $data['asset'] = $asset;
                return $data;
                break;

            case 'event':
                $event = Event::getEventsAssetsUsingAutoID($element_id);
                $event = $event[0];
                if (isset($event['users_liked'])) {
                    $users_liked = $event['users_liked'];
                } else {
                    $users_liked = [];
                }
                $end_date_label = $end_time_label = $end_time = $event_host_id = 0;
                $location = $speakers = $event_host_name = '';
                if ($event['event_type'] = 'general') {
                    if (isset($event['end_date_label']) && $event['end_date_label'] != '') {
                        $end_date_label = $event['end_date_label'];
                    }
                    if (isset($event['end_time_label']) && $event['end_time_label'] != '') {
                        $end_time_label = $event['end_time_label'];
                    }
                    if (isset($event['end_time']) && $event['end_time'] != '') {
                        $end_time = $event['end_time'];
                    }
                    if (isset($event['location']) && $event['location'] != '') {
                        $location = $event['location'];
                    }
                    if (isset($event['speakers']) && $event['speakers'] != '') {
                        $speakers = $event['speakers'];
                    }
                    if (isset($event['event_host_id']) && $event['event_host_id'] != '') {
                        $event_host_id = $event['event_host_id'];
                    }
                    if (isset($event['event_host_name']) && $event['event_host_name'] != '') {
                        $event_host_name = $event['event_host_name'];
                    }
                }

                $asset = [
                    'id' => $event['event_id'],
                    'name' => $event['event_name'],
                    'description' => $event['event_description'],
                    'short_description' => '',
                    'type' => $element_type,
                    'asset_type' => '',
                    'users_liked' => $users_liked,
                    'element_type' => $element_type,
                    'event_type' => $event['event_type'],
                    'event_cycle' => $event['event_cycle'],
                    'speakers' => $speakers,
                    'event_host_id' => $event_host_id,
                    'event_host_name' => $event_host_name,
                    'start_date_label' => $event['start_date_label'],
                    'start_time_label' => $event['start_time_label'],
                    'start_time' => $event['start_time'],
                    'end_date_label' => $end_date_label,
                    'end_time_label' => $end_time_label,
                    'end_time' => $end_time,
                    'location' => $location,
                    'created_at' => $event['created_at'],
                ];
                $data['asset'] = $asset;
                return $data;
                break;
            case 'flashcard':
                $element_asset = FlashCard::getFlashcardsAssetsUsingAutoID((int)$element_id);
                $asset = [
                    'id' => $element_asset[0]['card_id'],
                    'name' => $element_asset[0]['title'],
                    'description' => $element_asset[0]['description'],
                    'short_description' => '',
                    'type' => $element_type,
                    'asset_type' => '',
                    'element_type' => ''];
                $asset['message'] = trans('mobile.flas_card_not_support');
                $data['asset'] = $asset;
                return $data;
                break;
            default:
                return 'Feature not supported';
        }
    }

    public static function getUserPacketsUsingSlugs($program_slugs, $skip, $take)
    {
        return Packet::whereIn('feed_slug', $program_slugs)
            ->where('status', '=', 'ACTIVE')
            ->where('packet_publish_date', '<=', time())
            ->orderby('packet_publish_date', 'desc')
            ->skip((int)$skip)
            ->take($take)
            ->get()
            ->toArray();
    }

    public static function getMediaDetails($key = null)
    {
        $asset = Dam::getDAMSMediaUsingID($key);
        $data = [];
        $data['video_cover_image'] = '';
        if (empty($asset) || !$key) {
            return '';
        }
        $asset = $asset[0];
        $data['file'] = 0;
        if (isset($asset) && !empty($asset)) {
            $data['type'] = $asset['type'];
            switch ($asset['type']) {
                case 'video':
                    if ($asset['asset_type'] == 'file') {
                        $data['file'] = 1;
                        if (isset($asset['akamai_details'])) {
                            if (isset($asset['akamai_details']['delivery_html5_url'])) {
                                $data['forret'] = $asset['akamai_details']['delivery_html5_url'];
                                $data['video_cover_image'] = URL::to('/media_image/' . $asset['_id'] . '?compress=1');
                            } elseif (isset($asset['akamai_details']['stream_success_html5'])) {
                                $data['forret'] = $asset['akamai_details']['stream_success_html5'];
                                $data['video_cover_image'] = URL::to('/media_image/' . $asset['_id'] . '?compress=1');
                            } elseif (!isset($asset['akamai_details']['code']) || $asset['akamai_details']['code'] != 200) {
                                $data['forret'] = 'Error in syncing the file. Please contact';
                            } else {
                                $data['forret'] = 'File is being proccessed please wait.';
                            }
                            //elseif(isset($asset['akamai_details']['code']) && $asset['akamai_details']['code'] == 200 && isset($asset['akamai_details']['stream_success_flash'])){}
                        } else {
                            $data['forret'] = 'File is not synced with Video Server';
                        }
                    } else {
                        $data['forret'] = $asset['url'];
                    }
                    break;

                case 'image':
                    if ($asset['asset_type'] == 'file') {
                        $data['file'] = 1;
                        $data['forret'] = URL::to('/media_image/' . $asset['_id']);
                    } else {
                        $data['forret'] = $asset['url'];
                    }
                    break;
                case 'document':
                    if ($asset['asset_type'] == 'file') {
                        $data['file'] = 1;
                        $data['forret'] = URL::to('/media_image/' . $asset['_id']);
                    } else {
                        $data['forret'] = $asset['url'];
                    }
                    break;

                case 'audio':
                    if ($asset['asset_type'] == 'file') {
                        $data['file'] = 1;
                        $data['forret'] = URL::to('/media_image/' . $asset['_id']);
                    } else {
                        $data['forret'] = $asset['url'];
                    }
                    break;
            }

            return $data;
        } else {
            return '';
        }
    }

    public static function getPacketsUsingSlugs($program_slugs, $take, $skip)
    {
        return Packet::whereIn('feed_slug', $program_slugs)
            ->where('status', '=', 'ACTIVE')
            ->where('packet_publish_date', '<=', time())
            ->orderby('packet_publish_date', 'desc')
            ->take($take)
            ->skip((int)$skip)
            ->get()
            ->toArray();
    }

    public static function pluckElementActivity($uid, $packet_id, $element_id, $element_type)
    {
        return MyActivity::where('user_id', '=', (int)$uid)
            ->where('module', '=', 'element')
            ->where('packet_id', '=', (int)$packet_id)
            ->where('element_type', '=', $element_type)
            ->where('action', '=', 'view')
            ->where('module_id', '=', (int)$element_id)
            ->value('module_id');
    }

    public static function getDAMSAssetsUsingAutoID($id = 'all')
    {
        if ($id == 'all') {
            return Dam::get()->toArray();
        } else {
            return Dam::where('id', '=', $id)->get()->toArray();
        }
    }

    public static function postQuestion($user_data, $feed_slug, $packet_slug, $packet_id, $question)
    {
        foreach ($user_data as $udata) {
            $uname = $udata['username'];
            $ufullname = $udata['firstname'] . ' ' . $udata['lastname'];
            $uid = $udata['uid'];
        }
        $id = PacketFaq::uniqueId();
        PacketFaq::insert([
            'id' => (int)$id,
            'packet_id' => (int)$packet_id,
            'user_id' => $uid,
            'username' => $uname,
            'created_by_name' => $ufullname,
            'question' => htmlentities($question),
            'access' => 'private',
            'like_count' => 0,
            'status' => 'UNANSWERED',
            'created_at' => time(),
        ]);
        Packet::getUpdatePacketFaq($packet_id);

        $feed = Program::pluckFeedName($feed_slug);

        $feed = $feed[0];
        $array = [
            'module' => 'Mobile-QAs',
            'action' => 'posted',
            'module_name' => 'question',
            'module_id' => (int)$id,
            'feed_id' => (int)$feed->program_id,
            'feed_name' => $feed->program_title,
            'packet_id' => (int)$packet_id,
            'packet_name' => Packet::pluckPacketName($packet_slug),
            'url' => '',
        ];
        self::getLogActivity($array, $uid);

        return true;
    }

    public static function getUserQuestions($user_name, $packet_id, $skip, $records_per_page)
    {
        return PacketFaq::where('packet_id', '=', (int)$packet_id)
            ->where('username', '=', $user_name)
            ->where('status', '!=', 'DELETED')
            ->orderby('created_at', 'desc')
            ->skip((int)$skip)
            ->take($records_per_page)
            ->get()
            ->toArray();
    }

    public static function getLogActivity($array2 = [], $user_id)
    {
        $now = time();
        $array1 = [
            'DAYOW' => date('l', $now),
            'DOM' => (int)date('j', $now),
            'DOW' => (int)date('w', $now),
            'DOY' => (int)date('z', $now),
            'MOY' => (int)date('n', $now),
            'WOY' => (int)date('W', $now),
            'YEAR' => (int)date('Y', $now),
            'user_id' => (int)$user_id,
            'date' => $now,
            'is_mobile' => 1,
        ];
        $array3 = array_merge($array1, $array2);
        MyActivity::insert($array3);
    }

    public static function updateFavouriteCount($uid, $slug, $packet_slug, $packet_id, $action)
    {
        $feed = Program::pluckFeedName($slug);

        $feed = $feed[0];
        if ($action == 'true') {
            Packet::where('packet_id', '=', (int)$packet_id)->increment('favourited_count');
            User::where('uid', '=', (int)$uid)->push('favourited_packets', (int)$packet_id, true);
            $array = [
                'module' => 'packet',
                'action' => 'favourited',
                'module_name' => Packet::pluckPacketName($slug),
                'module_id' => (int)$packet_id,
                'feed_id' => (int)$feed['program_id'],
                'feed_name' => $feed['program_title'],
                'url' => 'program/packet/' . $slug,
            ];
        } else {
            Packet::where('packet_id', '=', (int)$packet_id)->decrement('favourited_count');
            User::where('uid', '=', (int)$uid)->pull('favourited_packets', (int)$packet_id);
            $array = [
                'module' => 'packet',
                'action' => 'unfavourited',
                'module_name' => Packet::pluckPacketName($slug),
                'module_id' => (int)$packet_id,
                'feed_id' => (int)$feed['program_id'],
                'feed_name' => $feed['program_title'],
                'url' => 'program/packet/' . $slug,
            ];
        }

        self::getLogActivity($array, $uid);

        return 1;
    }

    public static function updateElementLikedCount($uid, $feed_slug, $packet_slug, $packet_id, $element_id, $element_name, $element_type, $action)
    {
        $feed = Program::pluckFeedName($feed_slug);
        $feed = $feed[0];
        if ($action == 'true') {
            switch ($element_type) {
                case 'media':
                    Dam::where('id', '=', (int)$element_id)->increment('liked_count');
                    Dam::where('id', '=', (int)$element_id)->push('users_liked', (int)$uid, true);
                    break;

                case 'assessment':
                    Quiz::where('quiz_id', '=', (int)$element_id)->increment('liked_count');
                    Quiz::where('quiz_id', '=', (int)$element_id)->push('users_liked', (int)$uid, true);
                    break;
                case 'event':
                    Event::where('event_id', '=', (int)$element_id)->increment('liked_count');
                    Event::where('event_id', '=', (int)$element_id)->push('users_liked', (int)$uid, true);
                    break;
            }

            $array = [
                'module' => 'element',
                'action' => 'liked',
                'module_name' => $element_name,
                'module_id' => (int)$element_id,
                'element_type' => $element_type,
                'packet_id' => (int)$packet_id,
                'packet_name' => Packet::pluckPacketName($feed_slug),
                'feed_id' => (int)$feed['program_id'],
                'feed_name' => $feed['program_title'],
                'url' => 'program/packet/' . $feed_slug,
            ];
        } else {
            switch ($element_type) {
                case 'media':
                    Dam::where('id', '=', (int)$element_id)->decrement('liked_count');
                    Dam::where('id', '=', (int)$element_id)->pull('users_liked', (int)$uid, true);
                    break;
                case 'assessment':
                    Quiz::where('quiz_id', '=', (int)$element_id)->decrement('liked_count');
                    Quiz::where('quiz_id', '=', (int)$element_id)->pull('users_liked', (int)$uid, true);
                    break;
                case 'event':
                    Event::where('event_id', '=', (int)$element_id)->decrement('liked_count');
                    Event::where('event_id', '=', (int)$element_id)->pull('users_liked', (int)$uid, true);
                    break;
            }

            $array = [
                'module' => 'element',
                'action' => 'unliked',
                'module_name' => $element_name,
                'module_id' => (int)$element_id,
                'element_type' => $element_type,
                'packet_id' => (int)$packet_id,
                'packet_name' => Packet::pluckPacketName($feed_slug),
                'feed_id' => (int)$feed['program_id'],
                'feed_name' => $feed['program_title'],
                'url' => 'program/packet/' . $feed_slug,
            ];
        }

        self::getLogActivity($array, $uid);

        return true;
    }

    public static function userQuizRel($user)
    {
        // Users
        if (isset($user['relations']['user_quiz_rel']) && !empty($user['relations']['user_quiz_rel'])) {
            $user_quiz = $user['relations']['user_quiz_rel'];
        } else {
            $user_quiz = [];
        }
        // User groups
        if (isset($user['relations']['active_usergroup_user_rel']) && !empty($user['relations']['active_usergroup_user_rel'])) {
            $usergroup = UserGroup::where('status', '=', 'ACTIVE')
                ->whereIn('ugid', $user['relations']['active_usergroup_user_rel'])
                ->get()
                ->toArray();
            $usergroup_quiz = $assigned_feed = [];
            foreach ($usergroup as $value) {
                // Quiz assigned to user group
                if (!empty($value['relations']['usergroup_quiz_rel'])) {
                    $usergroup_quiz = array_merge($usergroup_quiz, $value['relations']['usergroup_quiz_rel']);
                }
                // CF assigned to user group
                if (!empty($value['relations']['usergroup_feed_rel'])) {
                    $assigned_feed = array_merge($assigned_feed, $value['relations']['usergroup_feed_rel']);
                }
            }
        } else {
            $usergroup_quiz = $assigned_feed = [];
        }

        // Content feeds
        $feed_quiz_list = $feed_quiz = [];

        // CF assigned directly to user
        if (isset($user['relations']['user_feed_rel']) && !empty($user['relations']['user_feed_rel'])) {
            $assigned_feed = array_merge($assigned_feed, $user['relations']['user_feed_rel']);
        }

        $time = time();
        $feed = Program::where('status', '=', 'ACTIVE')
            ->whereIn('program_id', $assigned_feed)
            ->where('program_startdate', '<=', $time)
            ->where('program_enddate', '>=', $time)
            ->orderby('program_title')
            ->get()
            ->toArray();

        $feed_list = [];
        foreach ($feed as $value) {
            $feed_list[] = $value['program_slug'];
        }

        if (!empty($feed_list)) {
            $packet = Packet::where('status', '=', 'ACTIVE')
                ->whereIn('feed_slug', $feed_list)
                ->get();
            $feed_quiz_list = $feed_quiz = [];
            foreach ($packet as $p) {
                foreach ($p->elements as $key => $value) {
                    if ($value['type'] == 'assessment') {
                        $feed_quiz[] = $value['id'];
                        $feed_quiz_list[$p->feed_slug][] = $value['id'];
                    }
                }
            }
        }

        // Get quiz list for this user
        return [
            'quiz_list' => array_merge($user_quiz, $usergroup_quiz, $feed_quiz),
            'feed_list' => $feed,
            'feed_quiz_list' => $feed_quiz_list,
        ];
    }

    public static function userAttempts($uid, $quiz_id)
    {
        return QuizAttempt::where('user_id', '=', (int)$uid)
            ->where('quiz_id', '=', (int)$quiz_id)
            ->where('status', '=', 'CLOSED')
            ->get();
    }

    public static function getReadAnnouncement($user_id = null, $aids = null, $start = 0, $limit = 5, $device = 'mobile & web')
    {
        if ($device == 'mobile') {
            $temp = 'Web only';
        } elseif ($device == 'web') {
            $temp = 'Mobile only';
        } else {
            $temp = 'both';
        }
        if (!is_null($user_id) && !is_null($aids)) {
            return Announcement::where('status', '=', 'ACTIVE')
                ->where('announcement_device', '!=', $temp)
                ->whereIN('readers.user', [$user_id])
                ->Where(function ($query) use ($aids) {
                    $query->orwhere('announcement_for', '=', 'public')
                        ->orwhere('announcement_for', '=', 'registerusers')
                        ->orwhereIn('announcement_id', $aids);
                })
                ->where('schedule', '<', time())
                ->where('expire_date', '>=', time())
                ->orderBy('schedule', 'desc')->skip((int)$start)->take((int)$limit)->get()->toArray();
        } else {
            return [];
        }
    }

    public static function updateQALikedCount($uid, $liked, $qid, $packet_id, $slug, $packet_name, $feed_slug)
    {
        $feed = Program::pluckFeedName($feed_slug);
        $feed = $feed[0];
        if ($liked == 'TRUE') {
            PacketFaq::where('id', '=', (int)$qid)->increment('like_count');
            PacketFaq::where('id', '=', (int)$qid)->push('users_liked', (int)$uid, true);
            $array = [
                'module' => 'QAs',
                'action' => 'liked',
                'module_name' => 'question',
                'module_id' => (int)$qid,
                'feed_id' => (int)$feed['program_id'],
                'feed_name' => $feed['program_title'],
                'packet_id' => (int)$packet_id,
                'packet_name' => $packet_name,
                'url' => Request::path(),
            ];
        } else {
            PacketFaq::where('id', '=', (int)$qid)->decrement('like_count');
            PacketFaq::where('id', '=', (int)$qid)->pull('users_liked', (int)$uid, true);
            $array = [
                'module' => 'QAs',
                'action' => 'unliked',
                'module_name' => 'question',
                'module_id' => (int)$qid,
                'feed_id' => (int)$feed['program_id'],
                'feed_name' => $feed['program_title'],
                'packet_id' => (int)$packet_id,
                'packet_name' => $packet_name,
                'url' => Request::path(),
            ];
        }
        self::getLogActivity($array, $uid);

        return PacketFaq::where('id', '=', (int)$qid)->value('like_count');
    }

    public static function getPublishOnDisplay($utc_time = 0, $tz)
    {
        // if(Auth::check()) {
        //     $tz = Auth::user()->timezone;
        // } else {
        //     $tz = config('app.default_timezone');
        // }
        // $date = Carbon::createFromFormat('U', (int)$utc_time, $tz);
        $date = Carbon::createFromTimestampUTC((int)$utc_time)->timezone($tz);
        if ($date->isToday()) {
            return 'Today';
        } elseif ($date->isYesterday()) {
            return 'Yesterday';
        } else {
            return $date->format(config('app.date_format'));
        }
    }

    public static function getPublicAnnouncementIDs()
    {
        $pub_annnoun_ids = [];
        $announcements = Announcement::where('status', '=', 'ACTIVE')
            ->Where('announcement_for', '=', 'public')
            ->where('schedule', '<', time())
            ->where('expire_date', '>=', time())
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        if (isset($announcements) && !empty($announcements)) {
            foreach ($announcements as $each) {
                $pub_annnoun_ids[] = $each['announcement_id'];
            }
        }

        return $pub_annnoun_ids;
    }
}
