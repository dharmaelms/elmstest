<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\PortalBaseController;
use App\Model\Common;
use App\Model\Event;
use App\Model\MyActivity;
use App\Model\OverAllChannelAnalytic;
use App\Services\Event\IEventService;
use App\Services\Program\IProgramService;
use App\Model\Packet;
use App\Model\Program;
use App\Model\SiteSetting;
use App\Model\UserGroup;
use Auth;
use Carbon;
use Input;
use Log;
use Request;
use Webex;

class EventController extends PortalBaseController
{
    /**
     * @var App\Services\Event\IEventService
     */
    private $event_service;
    private $program_service;

    public function __construct(
        IEventService $event_service,
        IProgramService $program_service
    ) {
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
        $this->program_service = $program_service;
        $this->event_service = $event_service;
    }

    public function getLiveJoin($event_id)
    {
        try {
            $event_list = $this->event_service->getAllEventsAssigned();
        } catch (\Exception $e) {
            if (Request::ajax()) {
                return ['status' => false, 'message' => 'No events on this day'];
            } else {
                $event_list = [];
            }
        }
        // Whether event id accessible by user
        if (!in_array($event_id, $event_list)) {
            return parent::getError($this->theme, $this->theme_path, 401, 'You don\'t have access to this event', url('event'));
        }

        $event = Event::where('status', '=', 'ACTIVE')
            ->where('event_id', '=', (int)$event_id)
            ->firstOrFail();

        if ($event->event_type != 'live') {
            return parent::getError($this->theme, $this->theme_path);
        }

        // Redirect check
        if (Input::has('return')) {
            $return = urldecode(Input::get('return'));
        } else {
            $return = 'event';
        }

        // Webex instance object
        $webex = new Webex(
            config('app.webex_servicelayer_url'),
            config('app.webex_appkey'),
            config('app.webex_username'),
            config('app.webex_password')
        );

        if (Auth::user()->uid == $event->event_host_id) {
            $param['hostUsername'] = $event['webex_host_username'];
            $param['hostPassword'] = $event['webex_host_password'];
            $param['sessionKey'] = $event->session_key;
            $param['backURL'] = url('event/live-logout/' . $event_id) . '?return=' . url($return);
            $url = $webex->user('hostjoinurl', $param);
            if ($url['status'] == true) {
                if (Input::get('from') != 'myactivity') {
                    MyActivity::getInsertActivity([
                        'module' => 'event',
                        'action' => 'started',
                        'module_name' => $event->event_name,
                        'module_id' => (int)$event_id,
                        'url' => $return
                    ]);
                    $this->putEntryInToOca($event);
                }
                return redirect($url['data']['hostJoinURL']);
            } else {
                return parent::getError($this->theme, $this->theme_path, 404, 'Error:' . var_export($url['error']), url('event'));
            }
        } else {
            $param['firstname'] = Auth::user()->firstname;
            $param['lastname'] = Auth::user()->lastname;
            $param['email'] = Auth::user()->email;
            $param['sessionKey'] = $event->session_key;
            $param['sessionType'] = $event->session_type;
            $param['backURL'] = url($return);
            $url = $webex->user('attendeejoinurl', $param);
            if ($url['status'] == true) {
                if (Input::get('from') != 'myactivity') {
                    MyActivity::getInsertActivity([
                        'module' => 'event',
                        'action' => 'joined',
                        'module_name' => $event->event_name,
                        'module_id' => (int)$event_id,
                        'url' => $return
                    ]);
                    $this->putEntryInToOca($event);
                }
                return redirect($url['data']['attendeeJoinURL']);
            } else {
                return parent::getError($this->theme, $this->theme_path, 404, 'Error:' . var_export($url['error']), url('event'));
            }
        }
    }

    public function getLiveLogout($event_id)
    {
        $event = Event::where('status', '=', 'ACTIVE')
            ->where('event_id', '=', (int)$event_id)
            ->firstOrFail();

        // Redirect check
        if (Input::has('return')) {
            $return = urldecode(Input::get('return'));
        } else {
            $return = url('event');
        }

        // Webex instance object
        $webex = new Webex(
            config('app.webex_servicelayer_url'),
            config('app.webex_appkey'),
            config('app.webex_username'),
            config('app.webex_password')
        );

        $param['hostUsername'] = $event['webex_host_username'];
        $param['hostPassword'] = $event['webex_host_password'];
        $param['backURL'] = $return;
        $url = $webex->user('hostlogouturl', $param);

        if ($url['status'] == true) {
            return redirect($url['data']['hostLogoutURL']);
        } else {
            return parent::getError($this->theme, $this->theme_path, 404, 'Error:' . var_export($url['error']), url('event'));
        }
    }

    public function getIndex()
    {
        if (SiteSetting::module('General', 'events') != 'on') {
            return parent::getError($this->theme, $this->theme_path, 401);
        }
        try {
            $event_list = $this->event_service->getAllEventsAssigned();
        } catch (\Exception $e) {
            if (Request::ajax()) {
                return ['status' => false, 'message' => 'No events on this day'];
            } else {
                $event_list = [];
            }
        }
        $today = Carbon::today(Auth::user()->timezone);
        $day = Input::get('day', $today->day);
        $month = Input::get('month', $today->month);
        $year = Input::get('year', $today->year);
        $show = Input::get('show', 'today');
        $context = Input::get('context', 'user-dashboard');
        if ($show == 'today' && $context != 'popover') {
            $request_date = $today;
        } else {
            $request_date = Carbon::create($year, $month, $day, 0, 0, 0, Auth::user()->timezone);
        }

        $start_of_month = Carbon::create($year, $month, 1, 0, 0, 0, Auth::user()->timezone);
        $end_of_month = Carbon::create($year, $month, $start_of_month->daysInMonth, 23, 59, 59, Auth::user()->timezone);

        $events = Event::where('status', '=', 'ACTIVE')
            ->whereIn('event_id', $event_list)
            ->where(function ($query) use ($start_of_month, $end_of_month) {
                $query->whereBetween('start_time', [$start_of_month->timestamp, $end_of_month->timestamp])
                    ->orWhereBetween('end_time', [$start_of_month->timestamp, $end_of_month->timestamp]);
            })
            ->orderBy('start_time')
            ->get();
        $cal = $custom_events = [];
        foreach ($events as $value) {
            $start_time = $value->start_time->timezone(Auth::user()->timezone)->copy();
            $end_time = $value->end_time->timezone(Auth::user()->timezone)->copy();
            $cal[$start_time->format('m-d-Y')] = '<span></span>';
            if ($request_date->toDateString() == $start_time->toDateString()) {
                $custom_events[] = $value->event_id;
            }
            if ($start_time->toDateString() != $end_time->toDateString()) {
                do {
                    $start_time = $start_time->addDay();
                    $cal[$start_time->format('m-d-Y')] = '<span></span>';
                    if ($request_date->toDateString() == $start_time->toDateString()) {
                        $custom_events[] = $value->event_id;
                    }
                } while ($start_time->toDateString() != $end_time->toDateString());
            }
        }

        ksort($cal);

        $upcoming_events = Event::where('status', '=', 'ACTIVE')
            ->whereIn('event_id', $event_list)
            ->where('start_time', '>', time())
            ->orderBy('start_time')
            ->get();

        if ($show == 'today') {
            $show_events = $events->filter(function ($item) use ($custom_events) {
                return in_array($item->event_id, $custom_events);
            });
        }

        if ($show == 'starred') {
            $start = Input::get('start', 0);
            $limit = 9;
            $show_events = Event::where('status', '=', 'ACTIVE')
                ->whereIn('event_id', $event_list)
                ->where('users_liked', '=', Auth::user()->uid)
                ->orderBy('start_time', 'desc')
                ->skip((int)$start)
                ->take((int)$limit)
                ->get();
        }

        if ($show == 'custom') {
            $show_events = $events->filter(function ($item) use ($custom_events) {
                return in_array($item->event_id, $custom_events);
            })->sortByDesc('start_time');
        }

        if (Request::ajax()) {
            $context = null;
            $status = true;
            $eventData = [];
            $view = "event.event_ajax_load";
            if (Request::has("context")) {
                if ($show === "today" && Request::get("context") === "user-dashboard") {
                    $context = "user-dashboard";
                    $view = "event.dashboard_today_events";
                }
            }
            if (isset($context) && $context === "user-dashboard") {
                $eventData["calendar_data"] = json_encode($cal);
            }
            if (!$show_events->isEmpty()) {
                $eventData["events"] = $show_events;
                $data = view("{$this->theme_path}.{$view}", $eventData)->render();
            } else {
                $status = false;
                if (isset($context) && $context === "user-dashboard") {
                    $data = view("{$this->theme_path}.{$view}", $eventData)->render();
                } else {
                    $data = "No more events to show";
                }
            }
            if ($show === 'today' && Request::get('context') === 'popover') {
                if (empty($eventData)) {
                    return response()->json(['status' => false, 'message' => 'No events on this day']);
                }
                return response()->json(['status' => true, 'data' => $eventData]);
            }
            return response()->json([
                "status" => $status,
                "data" => $data
            ]);
        } else {
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
            $this->layout->header = view($this->theme_path . '.common.header');
            $this->layout->footer = view($this->theme_path . '.common.footer');
            $this->layout->content = view($this->theme_path . '.event.list_events')
                ->with('show', $show)
                ->with('cal', $cal)
                ->with('events', $show_events)
                ->with('upcoming_events', $upcoming_events);
        }
    }

    public function getCalDates()
    {
        try {
            $event_list = $this->event_service->getAllEventsAssigned();
        } catch (\Exception $e) {
            if (Request::ajax()) {
                return ['status' => false, 'message' => 'No events on this day'];
            } else {
                $event_list = [];
            }
        }

        $month = Input::get('month');
        $year = Input::get('year');

        $start_of_month = Carbon::create($year, $month, 1, 0, 0, 0, Auth::user()->timezone);
        $end_of_month = Carbon::create($year, $month, $start_of_month->daysInMonth, 23, 59, 59, Auth::user()->timezone);

        $events = Event::where('status', '=', 'ACTIVE')
            ->whereIn('event_id', $event_list)
            ->where(function ($query) use ($start_of_month, $end_of_month) {
                $query->whereBetween('start_time', [$start_of_month->timestamp, $end_of_month->timestamp])
                    ->orWhereBetween('end_time', [$start_of_month->timestamp, $end_of_month->timestamp]);
            })
            ->get();

        $cal = [];
        foreach ($events as $value) {
            $start_time = $value->start_time->timezone(Auth::user()->timezone)->copy();
            $end_time = $value->end_time->timezone(Auth::user()->timezone)->copy();
            $cal[$start_time->format('m-d-Y')] = '<span></span>';
            if ($start_time->toDateString() != $end_time->toDateString()) {
                do {
                    $start_time = $start_time->addDay();
                    $cal[$start_time->format('m-d-Y')] = '<span></span>';
                } while ($start_time->toDateString() != $end_time->toDateString());
            }
        }

        return response()->json($cal);
    }

    public function getStarEvent($action, $event_id)
    {
        $this->layout = '';

        Event::where('status', '=', 'ACTIVE')
            ->where('event_id', '=', (int)$event_id)
            ->firstOrFail();

        switch ($action) {
            case 'star':
                Event::where('event_id', '=', (int)$event_id)
                    ->push('users_liked', Auth::user()->uid, true);
                break;

            case 'unstar':
                Event::where('event_id', '=', (int)$event_id)
                    ->pull('users_liked', Auth::user()->uid);
                break;

            default:
                return response()->json([
                    'status' => false,
                    'event_id' => (int)$event_id
                ]);
                break;
        }

        return response()->json([
            'status' => true,
            'event_id' => (int)$event_id
        ]);
    }

    public function putEntryInToOca($event = [])
    {
        if (empty($event)) {
            return false;
        }
        $returnFlag = true;
        $userId = (int)Auth::user()->uid;
        $eventId = (int)$event->event_id;
        try {
            $userChannelIds =  array_get($this->program_service->getAllProgramsAssignedToUser($userId), 'channel_ids', []);
        } catch (\Exception $e) {
            Log::info('No channels are assigned to this user user_id: '.$userId);
            return false;
        }
        if (isset($event->relations['feed_event_rel'])) {
            $channelRel = $event->relations['feed_event_rel'];
            if (empty(array_intersect(array_keys($channelRel), $userChannelIds))) {
                return false;
            }
            foreach ($channelRel as $channelId => $specificChannelRel) {
                if (!in_array($channelId, $userChannelIds)) {
                    continue;
                }
                $data = [];
                $completion = 0;
                $postCompletion = [];
                $itemDetails = [];
                $data['user_id'] = $userId;
                $data['channel_id'] = $channelId;
                $chennelDetails = Program::getProgramDetailsByID($channelId);
                if (is_null($chennelDetails)) {
                    return false;
                }
                $chennelSlug = $chennelDetails->program_slug;
                $postCountChannel = Packet::where('feed_slug', '=', $chennelSlug)
                    ->where('status', '!=', 'DELETED')
                    ->count();
                $isExists = OverAllChannelAnalytic::isExists($channelId, $userId);
                if (!is_null($isExists) || !empty($isExists)) {
                    $existsPostCompletion = $isExists->post_completion;
                    $existsItemDetails = $isExists->item_details;
                }
                foreach ($specificChannelRel as $postId) {
                    $postDeatils = Packet::getPacketByID((int)$postId);
                    $postElement = [];
                    $countEle = 1;
                    if (isset($postDeatils[0]['elements']) && !empty($postDeatils[0]['elements'])) {
                        foreach ($postDeatils[0]['elements'] as $element) {
                            $postElement[] = $element['type'] . '_' . $element['id'];
                        }
                        $countEle = count($postElement);
                    }
                    $postKey = 'p_' . $postId;
                    if (!is_null($isExists) || !empty($isExists)) {
                        if (isset($existsItemDetails[$postKey])) {
                            $tempPostEle = $existsItemDetails[$postKey];
                            $tempPostEle[] = 'event_' . $eventId;
                            $tempPostEle = array_unique($tempPostEle);
                            $viewedCount = count(array_intersect($tempPostEle, $postElement));
                            $existsPostCompletion[$postKey] = round(
                                ($viewedCount / (($countEle > 1) ? $countEle : 1)) * 100,
                                2
                            );
                            $existsItemDetails[$postKey] = $tempPostEle;
                        } else {
                            $tempPostEle = [];
                            $tempPostEle[] = 'event_' . $eventId;
                            $viewedCount = count(array_intersect($tempPostEle, $postElement));
                            $existsPostCompletion[$postKey] = $postCompletion[$postKey] = round(
                                (($viewedCount / (($countEle > 1) ? $countEle : 1)) * 100),
                                2
                            );
                            $existsItemDetails[$postKey] = $itemDetails[$postKey] = $tempPostEle;
                        }
                    } else {
                        $tempPostEle = [];
                        $tempPostEle[] = 'event_' . $eventId;
                        $viewedCount = count(array_intersect($tempPostEle, $postElement));
                        $postCompletion[$postKey] = round(
                            ($viewedCount / (($countEle > 1) ? $countEle : 1)) * 100,
                            2
                        );
                        $itemDetails[$postKey] = $tempPostEle;
                    }
                }
                if (!is_null($isExists) || !empty($isExists)) {
                    $completion = round(
                        (array_sum(array_values($existsPostCompletion))) /
                        (($postCountChannel > 1) ? $postCountChannel : 1),
                        2
                    );
                } else {
                    $completion = round(
                        (array_sum(array_values($postCompletion))) /
                        (($postCountChannel > 1) ? $postCountChannel : 1),
                        2
                    );
                }
                //code need to do here
                $data['user_id'] = $userId;
                $data['channel_id'] = $channelId;
                $data['post_count'] = $postCountChannel;
                if (!is_null($isExists) || !empty($isExists)) {
                    $data['item_details'] = $existsItemDetails;
                    $data['post_completion'] = $existsPostCompletion;
                    $data['completion'] = $completion;
                } else {
                    $data['item_details'] = $itemDetails;
                    $data['post_completion'] = $postCompletion;
                    $data['completion'] = $completion;
                }
                if (!is_null($isExists) || !empty($isExists)) {
                    $data['updated_at'] = time();
                    $res = OverAllChannelAnalytic::updateData(
                        $data,
                        $data['channel_id'],
                        $data['user_id']
                    );
                    if (!$res) {
                        $returnFlag = false;
                    }
                } else {
                    $data['created_at'] = time();
                    $res = OverAllChannelAnalytic::insertData($data);
                    if (!$res) {
                        $returnFlag = false;
                    }
                }
                $data = [];
            }
        }
        if (isset($event->relations['feed_event_rel']) && !empty($event->relations['feed_event_rel'])) {
            $postIds = array_filter(array_collapse($event->relations['feed_event_rel']));
            $channelSlugs = Packet::where('status', '!=', 'DELETED')
                ->whereIn('packet_id', $postIds)
                ->get(['feed_slug', 'elements', 'packet_id']);
            if (!empty($channelSlugs)) {
                $feedSlugs = $channelSlugs->lists('feed_slug')->all();
                $groupedByChannel = $channelSlugs->groupBy('feed_slug');
                $feedSlugs = array_values(array_unique($feedSlugs));
                $channelDetails = Program::whereIn('program_slug', $feedSlugs)
                    ->where('status', '!=', 'DELETED')
                    ->get(['program_id', 'program_slug']);
                if (!empty($channelDetails)) {
                    $channelIdsSlug = $channelDetails->groupBy('program_slug');
                }
                foreach ($channelIdsSlug as $chennelSlug => $channelDetail) {
                    if (!isset($channelDetail[0]->program_id)) {
                        continue;
                    }
                    $channelId = $channelDetail[0]->program_id;
                    if (!in_array($channelId, $userChannelIds)) {
                        continue;
                    }
                    $data = [];
                    $isViewedEle = false;
                    $completion = 0;
                    $postCompletion = [];
                    $itemDetails = [];
                    $data['user_id'] = $userId;
                    $data['channel_id'] = $channelId;
                    $postCountChannel = Packet::where('feed_slug', '=', $chennelSlug)
                        ->where('status', '!=', 'DELETED')
                        ->count();
                    $isExists = OverAllChannelAnalytic::isExists($channelId, $userId);
                    if (!is_null($isExists) || !empty($isExists)) {
                        $existsPostCompletion = $isExists->post_completion;
                        $existsItemDetails = $isExists->item_details;
                    }
                    // if(!empty($userFeedRel) && in_array($channelId, $feedRel)){
                    $specificChannelPostIds = array_pluck($groupedByChannel->get($chennelSlug), 'packet_id');
                    foreach ($specificChannelPostIds as $postId) {
                        $postDeatils = Packet::getPacketByID((int)$postId);
                        $postElement = [];
                        $countEle = 1;
                        if (isset($postDeatils[0]['elements']) && !empty($postDeatils[0]['elements'])) {
                            foreach ($postDeatils[0]['elements'] as $element) {
                                $postElement[] = $element['type'] . '_' . $element['id'];
                            }
                            $countEle = count($postElement);
                        }
                        $postKey = 'p_' . $postId;
                        if (!is_null($isExists) || !empty($isExists)) {
                            if (isset($existsItemDetails[$postKey])) {
                                $tempPostEle = $existsItemDetails[$postKey];
                                $tempPostEle[] = 'event_' . $eventId;
                                $tempPostEle = array_unique($tempPostEle);
                                if (in_array('event_' . $eventId, $tempPostEle)) {
                                    $isViewedEle = true;
                                }
                                $viewedCount = count(array_intersect($tempPostEle, $postElement));
                                $existsPostCompletion[$postKey] = round(
                                    ($viewedCount / (($countEle > 1) ? $countEle : 1)) * 100,
                                    2
                                );
                                $existsItemDetails[$postKey] = $tempPostEle;
                            } else {
                                $tempPostEle = [];
                                $tempPostEle[] = 'event_' . $eventId;
                                $viewedCount = count(array_intersect($tempPostEle, $postElement));
                                $existsPostCompletion[$postKey] = $postCompletion[$postKey] = round(
                                    (($viewedCount / (($countEle > 1) ? $countEle : 1)) * 100),
                                    2
                                );
                                $existsItemDetails[$postKey] = $itemDetails[$postKey] = $tempPostEle;
                            }
                        } else {
                            $tempPostEle = [];
                            $tempPostEle[] = 'event_' . $eventId;
                            $viewedCount = count(array_intersect($tempPostEle, $postElement));
                            $postCompletion[$postKey] = round(
                                ($viewedCount / (($countEle > 1) ? $countEle : 1)) * 100,
                                2
                            );
                            $itemDetails[$postKey] = $tempPostEle;
                        }
                    }
                    if (!is_null($isExists) || !empty($isExists)) {
                        $completion = round(
                            (array_sum(array_values($existsPostCompletion))) /
                            (($postCountChannel > 1) ? $postCountChannel : 1),
                            2
                        );
                    } else {
                        $completion = round(
                            (array_sum(array_values($postCompletion))) /
                            (($postCountChannel > 1) ? $postCountChannel : 1),
                            2
                        );
                    }
                    // }
                    //code need to do here
                    $data['user_id'] = $userId;
                    $data['channel_id'] = $channelId;
                    $data['post_count'] = $postCountChannel;
                    if (!is_null($isExists) || !empty($isExists)) {
                        $data['item_details'] = $existsItemDetails;
                        $data['post_completion'] = $existsPostCompletion;
                        $data['completion'] = $completion;
                    } else {
                        $data['item_details'] = $itemDetails;
                        $data['post_completion'] = $postCompletion;
                        $data['completion'] = $completion;
                    }
                    if (!is_null($isExists) || !empty($isExists)) {
                        $data['updated_at'] = time();
                        if ($data['completion'] >= 100) {
                            if (isset($isExists->completed_at) && !empty($isExists->completed_at) && !$isViewedEle) {
                                $data['completed_at'] = $isExists->completed_at;
                                $data['completed_at'][] = time();
                            } else {
                                $data['completed_at'] = [time()];
                            }
                        }
                        $res = OverAllChannelAnalytic::updateData(
                            $data,
                            $data['channel_id'],
                            $data['user_id']
                        );
                        if (!$res) {
                            $returnFlag = false;
                        }
                    } else {
                        $data['created_at'] = time();
                        if ($data['completion'] >= 100) {
                            $data['completed_at'] = [time()];
                        }
                        $res = OverAllChannelAnalytic::insertData($data);
                        if (!$res) {
                            $returnFlag = false;
                        }
                    }
                    $data = [];
                }
            }
        }
        return $returnFlag;
    }
}
