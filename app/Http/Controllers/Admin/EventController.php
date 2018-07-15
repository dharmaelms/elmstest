<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Program\ChannelPermission;
use App\Events\Elastic\Items\ItemsAdded;
use App\Events\Elastic\Events\EventAdded;
use App\Events\Elastic\Events\EventAssigned;
use App\Events\Elastic\Events\EventRemoved;
use App\Events\Elastic\Events\EventEdited;
use App\Http\Controllers\AdminBaseController;
use App\Model\Common;
use App\Model\DeletedEventsRecordings;
use App\Model\Event;
use App\Model\NotificationLog;
use App\Model\Packet;
use App\Model\Program;
use App\Model\User;
use App\Model\UserGroup;
use App\Model\WebexHost;
use App\Model\WebExHost\Repository\WebExHostRepository;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\Event\EventPermission;
use App\Enums\RolesAndPermissions\PermissionType;
use App\Enums\RolesAndPermissions\Contexts;
use App\Enums\Program\ElementType;
use App\Services\DeletedEventsRecordings\IDeletedEventsRecordingsService;
use App\Services\Program\IProgramService;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ApplicationException;
use App\Enums\Course\CoursePermission;
use Auth;
use Carbon;
use DateTime;
use DateTimeZone;
use DB;
use Illuminate\Http\Request as Request;
use Input;
use stdClass;
use Timezone;
use URL;
use Validator;
use Webex;
use Session;
class EventController extends AdminBaseController
{
    protected $layout = 'admin.theme.layout.master_layout';
    /**
     * @var IProgramService
     * @var WebExHostRepository
     */
    private $programService;
    private $webexHostRepository;
    private $deletedEventsRecordingsService;

    /**
     * EventController constructor.
     * @param IProgramService $programService
     * @param WebExHostRepository $webexHostRepository
     */
    public function __construct(IProgramService $programService, WebExHostRepository $webexHostRepository, IDeletedEventsRecordingsService $deletedEventsRecordingsService)
    {
        parent::__construct();
        $this->programService = $programService;
        $this->webexHostRepository = $webexHostRepository;
        $this->deletedEventsRecordingsService = $deletedEventsRecordingsService;
    }

    public function getIndex()
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/event.manage_event') => ''
        ];

        if (Input::get('view', 'desktop') == 'iframe') {
            $from = Input::get('from');
            $this->layout->pagetitle = '';
            $this->layout->pageicon = '';
            $this->layout->pagedescription = '';
            $this->layout->header = '';
            $this->layout->sidebar = '';
            $this->layout->footer = '';
            $this->layout->content = view('admin.theme.event.list_events_iframe')
                ->with('from', $from);
        } else {
            $permission_data_with_flag = $this->roleService->hasPermission(
                Auth::user()->uid,
                ModuleEnum::EVENT,
                PermissionType::ADMIN,
                EventPermission::LIST_EVENT,
                null,
                null,
                true
            );

            $has_list_event_permission = get_permission_flag($permission_data_with_flag);
            if (!$has_list_event_permission) {
                return parent::getAdminError();
            }

            $list_event_permission_data = get_permission_data($permission_data_with_flag);
            $filter_params = has_system_level_access($list_event_permission_data)?
                [] : ["in_ids" => get_instance_ids($list_event_permission_data, Contexts::PROGRAM)];

            //Role based access
            $feeds = Program::getAllProgramByIDOrSlug('content_feed', '', $filter_params);

            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = trans('admin/event.manage_event');
            $this->layout->pageicon = 'fa fa-calendar';
            $this->layout->pagedescription = trans('admin/event.live_and_general_event_collect');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'event');
            $this->layout->content = view('admin.theme.event.list_events')
                ->with('start_serv', Input::get('start_serv', 0))
                ->with('length_page_serv', Input::get('length_page_serv', 10))
                ->with('feeds', $feeds);
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }

    public function getListIndexAjax()
    {
        $has_list_event_permission = false;

        $filter_params = [];
        $viewmode = Input::get('view', 'desktop');
        $from = null;

        $permission_data_with_flag = [];
        switch ($viewmode) {
            case "desktop":
                $permission_data_with_flag = $this->roleService->hasPermission(
                    Auth::user()->uid,
                    ModuleEnum::EVENT,
                    PermissionType::ADMIN,
                    EventPermission::LIST_EVENT,
                    null,
                    null,
                    true
                );

                $has_list_event_permission = get_permission_flag($permission_data_with_flag);
                break;
            case "iframe":
                $from = Input::get('from', 'none');
                switch ($from) {
                    case "post":
                        $program_type = $this->request->get("program_type", null);
                        $program_slug = $this->request->get("program_slug", null);
                        $post_slug = $this->request->get("post_slug", null);
                        try {
                            $program = $this->programService->getProgramBySlug($program_type, $program_slug);
                            $this->programService->getProgramPostBySlug(
                                $program_type,
                                $program_slug,
                                $post_slug
                            );

                            if ($program_type == "course") {
                                $has_list_event_permission = has_admin_permission(
                                    ModuleEnum::COURSE,
                                    CoursePermission::MANAGE_COURSE_POST
                                );
                                $from = $program_type;
                            } else {
                                $permission_data_with_flag = $this->roleService->hasPermission(
                                    $this->request->user()->uid,
                                    ModuleEnum::CHANNEL,
                                    PermissionType::ADMIN,
                                    ChannelPermission::MANAGE_CHANNEL_POST,
                                    Contexts::PROGRAM,
                                    $program->program_id,
                                    true
                                );

                                $has_list_event_permission = get_permission_flag($permission_data_with_flag);
                            }
                        } catch (ApplicationException $e) {
                            Log::error($e->getTraceAsString());
                        }
                        break;
                }
                break;
        }
       

        if (!$has_list_event_permission) {
            return response()->json(
                [
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ]
            );
        }

        if ($viewmode == "iframe" && $from == "course") {
            $filter_params = [];
        } else {
            $permission_data = get_permission_data($permission_data_with_flag);
            if (!has_system_level_access($permission_data)) {
                $filter_params["in_ids"] = get_user_accessible_elements($permission_data, ElementType::EVENT);
            }

        }

        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = "";
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            switch ($order_by[0]['column']) {
                case '1':
                    $orderByArray = ['event_name' => $order_by[0]['dir']];
                    break;
                case '2':
                    $orderByArray = ['event_type' => $order_by[0]['dir']];
                    break;
                case '3':
                    $orderByArray = ['start_time' => $order_by[0]['dir']];
                    break;
                case '4':
                    $orderByArray = ['duration' => $order_by[0]['dir']];
                    break;
                case '5':
                    $orderByArray = ['created_at' => $order_by[0]['dir']];
                    break;
            }
        }

        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }
        if (isset($search['value'])) {
            $searchKey = trim(strip_tags($search['value']));
        }

        if ($viewmode != 'iframe') {
            $totalRecords = Event::type(Input::get('show'))
                ->when(
                    array_has($filter_params, "in_ids"),
                    function ($query) use ($filter_params) {
                        return $query->whereIn("event_id", $filter_params["in_ids"]);
                    }
                )->search($searchKey)
                ->where('status', '=', 'ACTIVE')
                ->count();

            $filteredRecords = Event::type(Input::get('show'))
                ->when(
                    array_has($filter_params, "in_ids"),
                    function ($query) use ($filter_params) {
                        return $query->whereIn("event_id", $filter_params["in_ids"]);
                    }
                )->search($searchKey)
                ->where('status', '=', 'ACTIVE')
                ->count();

            $filtereddata = Event::type(Input::get('show'))
                ->when(
                    array_has($filter_params, "in_ids"),
                    function ($query) use ($filter_params) {
                        return $query->whereIn("event_id", $filter_params["in_ids"]);
                    }
                )->search($searchKey)
                ->where('status', '=', 'ACTIVE')
                ->orderBy(key($orderByArray), $orderByArray[key($orderByArray)])
                ->skip((int)$start)
                ->take((int)$limit)
                ->get();
        } else {
            $totalRecords = Event::type(Input::get('show'))
                ->when(
                    array_has($filter_params, "in_ids"),
                    function ($query) use ($filter_params) {
                        return $query->whereIn("event_id", $filter_params["in_ids"]);
                    }
                )->search($searchKey)
                ->where('status', '=', 'ACTIVE')
                ->count();

            $filteredRecords = Event::type(Input::get('show'))
                ->when(
                    array_has($filter_params, "in_ids"),
                    function ($query) use ($filter_params) {
                        return $query->whereIn("event_id", $filter_params["in_ids"]);
                    }
                )->search($searchKey)
                ->where('status', '=', 'ACTIVE')
                ->count();

            $filtereddata = Event::type(Input::get('show'))
                ->when(
                    array_has($filter_params, "in_ids"),
                    function ($query) use ($filter_params) {
                        return $query->whereIn("event_id", $filter_params["in_ids"]);
                    }
                )->search($searchKey)
                ->where('status', '=', 'ACTIVE')
                ->orderBy(key($orderByArray), $orderByArray[key($orderByArray)])
                ->skip((int)$start)
                ->take((int)$limit)
                ->get();
        }
        $dataArr = [];
        $feeds = Program::getAllContentFeeds();
        foreach ($filtereddata as $value) {
            $feed_rel = (isset($value->relations['feed_event_rel']) && !empty($value->relations['feed_event_rel'])) ? array_filter($value->relations['feed_event_rel']) : [];
            $feed_value = [];
            foreach ($feed_rel as $key => $fvalue) {
                $feed_value[$key] = $fvalue;
            }
            $user_rel = (isset($value->relations['active_user_event_rel']) && !empty($value->relations['active_user_event_rel'])) ? $value->relations['active_user_event_rel'] : [];
            $usergrp_rel = (isset($value->relations['active_usergroup_event_rel']) && !empty($value->relations['active_usergroup_event_rel'])) ? $value->relations['active_usergroup_event_rel'] : [];
            
            if(!is_admin_role(Auth::user()->role)) {
                $user_rel = array_values(array_intersect(get_user_ids($permission_data), $user_rel));
                $usergrp_rel = array_values(array_intersect(get_user_group_ids($permission_data), $usergrp_rel));  
            }
            
            $delete_rel = (!empty($feed_rel) || !empty($user_rel) || !empty($usergrp_rel)) ? 'disabled' : '';
            $actions = '';

            
            if (has_admin_permission(ModuleEnum::EVENT, EventPermission::VIEW_EVENT)) {
                if (!empty($value->recordings)) {
                    $font_aws = "fa fa-play-circle-o";
                } else {
                    $font_aws = "fa fa-eye";
                }
                $actions .= '<a class="btn btn-circle show-tooltip ajax view-event" title="' . trans('admin/manageweb.action_view') . '" href="' . URL::to("/cp/event/view-event-ajax/" . $value->event_id) . '" ><i class="'.$font_aws.'"></i></a>';
            }

            if (has_admin_permission(ModuleEnum::EVENT, EventPermission::EDIT_EVENT)) {
                $actions .= '<a class="btn btn-circle show-tooltip ajax" title="' . trans('admin/manageweb.action_edit') . '" href="' . URL::to("/cp/event/edit-event/" . $value->event_id) . '?start=' . $start . '&limit=' . $limit . '&show=' . Input::get('show') . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-edit"></i></a>';
            }

            if (has_admin_permission(ModuleEnum::EVENT, EventPermission::DELETE_EVENT)) {
                if (!empty($feed_rel) || !empty($user_rel) || !empty($usergrp_rel)) {
                     $actions .= '<a class="btn btn-circle show-tooltip" 
                     title="'.trans('admin/manageweb.event_delete').'"><i class="fa fa-trash-o"></i></a>';
                } elseif (!empty($value->recordings)) {
                    $actions .= '<a class="btn btn-circle show-tooltip" 
                     title="'.trans('admin/manageweb.event_record_delete').'"><i class="fa fa-trash-o"></i></a>';
                 } else {
                    $actions .= '<a class="btn btn-circle show-tooltip ajax delete-event" 
                    title="' . trans('admin/manageweb.action_delete') . '" 
                    href="' . URL::to("/cp/event/delete-event/" . $value->event_id) . '?start=' . $start . 
                    '&limit=' . $limit . '&show=' . Input::get('show') . '&search=' . $searchKey . 
                    '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '">
                    <i class="fa fa-trash-o"></i></a>';
                 }
            }

            $temparr = [
                '<input type="checkbox" value="' . $value->event_id . '">',
                str_limit($value->event_name, 40),
                $value->event_type,
                $value->start_time->timezone(Auth::user()->timezone)->format('d-m-Y H:i'),
                $value->duration,
                Timezone::convertFromUTC('@' . $value->created_at, Auth::user()->timezone, config('app.date_format')),
                $actions
            ];
            if ($viewmode != 'iframe') {
                if (has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_POST)) {
                    if (count($feeds) < 1) {
                        $content_feeds = "<a href='#' title='" . trans('admin/event.no_channel') . "'  class=' badge " . ((count($feed_rel) > 0) ? 'badge-success' : 'badge-grey') . "' data-key='" . $value->event_id . "' data-info='feed' data-text='No " . trans('admin/program.programs') . "' data-json='" . json_encode($feed_value) . "'>" . count($feed_rel) . "</a>";
                    } else {
                        $content_feeds = "<a href='' class='eventrel badge " . ((count($feed_rel) > 0) ? "badge-success" : "badge-grey") . "' data-key='" . $value->event_id . "' data-info='feed' data-text='Manage " . trans('admin/program.programs') . " for " . htmlentities($value->event_name, ENT_QUOTES) . "' data-json='" . json_encode($feed_value) . "'>" . count($feed_rel) . "</a>";
                    }
                } else {
                    $content_feeds = "<a href='#' title='" . trans('admin/event.no_per_to_assign_channels') . "'  class='badge show-tooltip " . ((count($feed_rel) > 0) ? 'badge-success' : 'badge-grey') . "'>" . count($feed_rel) . "</a>";
                }

                if (has_admin_permission(ModuleEnum::EVENT, EventPermission::ASSIGN_USER)) {
                    $users = '<a href="' . URL::to("/cp/usergroupmanagement?filter=ACTIVE&view=iframe&from=event&relid=" . $value->event_id) . '" class="eventrel badge ' . ((count($user_rel) > 0) ? "badge-success" : "badge-grey") . '" data-key="' . $value->event_id . '" data-info="user" data-text="Manage Users for ' . htmlentities($value->event_name, ENT_QUOTES) . '" data-json="' . json_encode($user_rel) . '">' . count($user_rel) . '</a>';
                } else {
                    $users = "<a href='#' title=\"" . trans('admin/event.no_per_to_assign_users') . "\"  class='badge show-tooltip " . ((count($user_rel) > 0) ? 'badge-success' : 'badge-grey') . "'>" . count($user_rel) . "</a>";
                }

                if (has_admin_permission(ModuleEnum::EVENT, EventPermission::ASSIGN_USER_GROUP)) {
                    $user_groups = '<a href="' . URL::to("/cp/usergroupmanagement/user-groups?filter=ACTIVE&view=iframe&from=event&relid=" . $value->event_id) . '" class="eventrel badge ' . ((count($usergrp_rel) > 0) ? "badge-success" : "badge-grey") . '" data-key="' . $value->event_id . '" data-info="usergroup" data-text="Manage User Groups for ' . htmlentities($value->event_name, ENT_QUOTES) . '" data-json="' . json_encode($usergrp_rel) . '">' . count($usergrp_rel) . '</a>';
                } else {
                    $user_groups = "<a href='#' title=\"" . trans('admin/event.no_per_to_assign_usersgroups') . "\"  class='badge show-tooltip " . ((count($usergrp_rel) > 0) ? 'badge-success' : 'badge-grey') . "'>" . count($usergrp_rel) . "</a>";
                }
                array_splice($temparr, 6, 0, [$content_feeds, $users, $user_groups]);
            } else {
                array_pop($temparr);
            }
            $dataArr[] = $temparr;
        }
        if ($viewmode == 'iframe') {
            $totalRecords = $filteredRecords;
        }
        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr
        ];
        return response()->json($finaldata);
    }

    public function getViewEventAjax($eid = null)
    {
        $this->layout = null;

        $permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::EVENT,
            PermissionType::ADMIN,
            EventPermission::VIEW_EVENT,
            null,
            null,
            true
        );

        if (!is_numeric($eid)) {
            abort(404);
        }

        if (!is_element_accessible(
            get_permission_data($permission_data_with_flag),
            ElementType::EVENT,
            $eid
        )) {
            return parent::getAdminError();
        }

        $event = Event::where('event_id', '=', (int)$eid)->firstOrFail();
        $deletedRecordingds = $this->deletedEventsRecordingsService->getEventDetails($eid);
        return view('admin.theme.event.view_event_body')
            ->with('event', $event)
            ->with('deletedRecordingds', $deletedRecordingds);
    }

    public function getAddEvent()
    {
        if (!has_admin_permission(ModuleEnum::EVENT, EventPermission::ADD_EVENT)) {
            return parent::getAdminError();
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/event.manage_event') => 'event',
            trans('admin/event.add_event') => ''
        ];

        /* for below code where('super_admin', '!=', true) is not working, hence re-written it
            $user = User::where('status', '=', 'ACTIVE')->where('super_admin', '!=', true)
                ->get(['uid','username', 'firstname', 'lastname', 'email']);
        */
        $user = User::where('status', '=', 'ACTIVE')->get(['uid', 'username', 'firstname', 'lastname', 'email', 'super_admin']);

        $host = WebexHost::where('status', '=', 'ACTIVE')
            ->get()
            ->toArray();


        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/event.add_event');
        $this->layout->pageicon = 'fa fa-calendar';
        $this->layout->pagedescription = trans('admin/event.add_event');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'event');
        $this->layout->content = view('admin.theme.event.add_event')
            ->with('user', $user)
            ->with('tz', Webex::webex_time_zone_list_array())
            ->with('compare', Webex::webex_mdl_to_webex_tz_array())
            ->with('host', $host);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function postAddEvent(Request $request)
    {


        if (!has_admin_permission(ModuleEnum::EVENT, EventPermission::ADD_EVENT)) {
            return parent::getAdminError();
        }

        $rules = [
            'event_type' => 'required|in:live,general',
            'event_cycle' => 'required|in:single,recurring',
            'event_name' => 'required|min:3|max:512',
            'event_short_description' => 'max:2500',
            'start_date' => 'required|date_format:"d-m-Y"',
            'start_time' => 'required|date_format:"H:i"'
        ];

        $messages = [];

        if ($request->event_type == 'live') {
            $rules['event_host'] = 'required|integer';
            $messages['event_host.integer'] = 'The event host field is required';

            $rules['duration'] = 'required|date_format:"H:i"|checkDuration';
            $messages['check_duration'] = 'The duration cannot be set to zero';
            $rules['timezone'] = 'required';
            $rules['session_type'] = 'required|in:MC,TC';
            $rules['webex_host'] = 'required|integer';
            $messages['webex_host.integer'] = 'The webex host field is required';
        }

        if ($request->event_type == 'general') {
            if ($request->event_cycle == 'single') {
                $rules['end_date'] = 'required|date_format:"d-m-Y"';
                $rules['end_time'] = 'required|date_format:"H:i"';
            }
        }

        Validator::extend('checkDuration', function ($attribute, $value, $parameters) {
            if (isset($value) && !empty($value) && "0:00" == trim($value)) {
                return false;
            } else {
                return true;
            }
        });


        $validation = Validator::make(Input::all(), $rules, $messages);
        if ($validation->fails()) {
            return redirect('cp/event/add-event')
                ->withInput()
                ->withErrors($validation);
        } else {
            $event_id = Event::getNextSequence();

            // Initialize the values
            $start_time = $end_time = $duration = 0;

            // Event duration
            if ($request->has('duration')) {
                $temp = explode(':', trim($request->duration));
                $duration = ($temp[0] * 60) + $temp[1];
            }

            $event_data = [
                'event_id' => $event_id,
                'event_type' => trim($request->event_type),
                'event_cycle' => trim($request->event_cycle),
                'event_name' => trim(htmlspecialchars(strip_tags($request->event_name))),
                'event_description' => $request->event_description,
                'event_short_description' => trim(strip_tags($request->event_short_description)),
                'keywords' => ($request->has('keywords')) ? array_map('trim', explode(',', strip_tags($request->keywords))) : []
            ];

            if ($request->event_type == 'live') {
                $user = User::where('status', '=', 'ACTIVE')
                    ->where('uid', '=', (int)$request->event_host)
                    ->first(['firstname', 'lastname']);

                $event_data['speakers'] = ($request->has('speakers')) ? array_map('trim', explode(',', strip_tags($request->speakers))) : [];
                $event_data['event_host_id'] = (int)$request->event_host;
                $event_data['event_host_name'] = trim($user->firstname) . ' ' . trim($user->lastname);

                $host = WebexHost::where('webex_host_id', '=', (int)$request->webex_host)
                    ->firstOrFail();

                $event_data['webex_host_id'] = $host['webex_host_id'];
                $event_data['webex_host_username'] = $host['username'];
                $event_data['webex_host_password'] = $host['password'];

                // Webex instance object
                $webex = new Webex(
                    config('app.webex_servicelayer_url'),
                    config('app.webex_appkey'),
                    config('app.webex_username'),
                    config('app.webex_password')
                );

                $event_data['start_date_label'] = trim($request->start_date);
                $event_data['start_time_label'] = trim($request->input('start_time', '0:00'));

                $session_time = $event_data['start_date_label'] . ' ' . $event_data['start_time_label'];
                $tz = $webex->timezone($session_time, (int)$request->timezone);

                $event_data['start_time'] = (int)Timezone::convertToUTC($session_time, $tz['timezone'], 'U');

                if ($event_data['start_time'] < time()) {
                    $validation->getMessageBag()->add('start_date', 'Start date should be greater than the current time');
                    return redirect('cp/event/add-event')
                        ->withInput()
                        ->withErrors($validation);
                }

                $event_data['duration'] = (int)$duration;
                $event_data['session_type'] = trim($request->session_type);
                $event_data['webex_timezone'] = (int)$request->timezone;
                $event_data['webex_timezone_label'] = Webex::webex_time_zone_list_array()[$event_data['webex_timezone']];
                $event_data['open_time'] = 15;
                $event_data['end_time'] = (int)$event_data['start_time'] + ($event_data['duration'] * 60);

                // Merge tz array with original data
                $event_data = array_merge($event_data, $tz);

                $scheduled = $this->getCheckAvailability($event_data['webex_host_id'], $event_data['start_time'], $event_data['end_time']);
                if (!empty($scheduled)) {
                    $validation->getMessageBag()->add('start_time', trans('admin/event.event_scheduled'));
                    return redirect('cp/event/add-event')
                        ->withInput()
                        ->withErrors($validation);
                }
                //check-availability code ends here

                // Webex parameters
                $param = [];
                $param['hostUsername'] = $host['username'];
                $param['hostPassword'] = $host['password'];
                $param['confName'] = $event_data['event_name'];
                $param['agenda'] = $event_data['event_short_description'];
                $param['startDate'] = Carbon::parse($event_data['start_date_label'] . ' ' . $event_data['start_time_label'])->format('m/d/Y G:i:s');
                $param['timeZoneID'] = $event_data['webex_timezone'];
                $param['duration'] = $duration;
                $param['openTime'] = $event_data['open_time'];
                $param['listing'] = 'PRIVATE';

                $response = $webex->create_session($event_data, $param);

                if (isset($response['status']) && $response['status'] === true) {
                    $event_data['session_key'] = $response['data']['sessionKey'];
                    $event_data['recordings'] = [];
                } else {
                    if (is_array($response['error'])) {
                        $error_msg = implode(',', $response['error']);
                    } else {
                        $error_msg = $response['error'];
                    }
                    Session::put('error', $error_msg);
                    return redirect('cp/event/add-event')
                            ->withInput();
                }
            }

            if ($request->event_type == 'general') {
                // Event start time
                if ($request->has('start_date')) {
                    $start_time = Timezone::convertToUTC($request->start_date, Auth::user()->timezone, 'U');
                    if ($request->has('start_time')) {
                        $temp = explode(':', trim($request->start_time));
                        $start_time += (($temp[0] * 60) + $temp[1]) * 60;
                    }
                }

                // Event end time
                if ($request->has('end_date')) {
                    $end_time = Timezone::convertToUTC($request->end_date, Auth::user()->timezone, 'U');
                    if ($request->has('end_time')) {
                        $temp = explode(':', trim($request->end_time));
                        $end_time += (($temp[0] * 60) + $temp[1]) * 60;
                    }
                }

                // Endtime check
                if ($request->has('start_date') && $request->has('end_date')) {
                    if ($start_time >= $end_time) {
                        $validation->getMessageBag()->add('end_date', 'End date should be always higher than start date');
                        return redirect('cp/event/add-event')
                            ->withInput()
                            ->withErrors($validation);
                    }
                    if (floor(abs($end_time - $start_time) / 86400) > config('app.general_event_max_days')) {
                        $validation->getMessageBag()->add('end_date', trans('admin/event.general_event_max_days'));
                        return redirect('cp/event/add-event')
                            ->withInput()
                            ->withErrors($validation);
                    }
                }

                if ($request->event_cycle == 'single') {
                    $event_data['start_date_label'] = trim($request->start_date);
                    $event_data['start_time_label'] = trim($request->input('start_time', '0:00'));
                    $event_data['start_time'] = (int)$start_time;
                    $event_data['end_date_label'] = trim($request->end_date);
                    $event_data['end_time_label'] = trim($request->input('end_time', '0:00'));
                    $event_data['end_time'] = (int)$end_time;
                    $event_data['location'] = trim(strip_tags($request->location));
                }
            }

            $event_data['relations'] = [
                'active_user_event_rel' => [],
                'inactive_user_event_rel' => [],
                'active_usergroup_event_rel' => [],
                'inactive_usergroup_event_rel' => [],
                'feed_event_rel' => new stdClass
            ];
            $event_data['editor_images'] = $request->input('editor_images', []);
            $event_data['status'] = 'ACTIVE';
            $event_data['created_by'] = Auth::user()->username;
            $event_data['created_at'] = time();
            $event_data['updated_by'] = '';
            $event_data['updated_at'] = time();

            if (Event::insert($event_data)) {
                if (config('elastic.service')) {
                    event(new EventAdded($event_id));
                }
                return redirect('cp/event/success-event/' . $event_id)
                    ->with('success', trans('admin/event.event_add_success'));
            } else {
                return redirect('cp/event/')
                    ->with('error', trans('admin/event.problem_while_creating_new_event'));
            }
        }
    }

    public function getSuccessEvent($eid)
    {
        if (!has_admin_permission(ModuleEnum::EVENT, EventPermission::ADD_EVENT)) {
            return parent::getAdminError();
        }

        if (!is_numeric($eid)) {
            abort(404);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/event.manage_event') => 'event',
            trans('admin/event.success') => ''
        ];

        // Checking whether given event is available in db
        Event::where('event_id', '=', (int)$eid)->firstOrFail(['event_id']);

        $permission_data_with_flag = $this->roleService->hasPermission(
                Auth::user()->uid,
                ModuleEnum::EVENT,
                PermissionType::ADMIN,
                EventPermission::LIST_EVENT,
                null,
                null,
                true
        );

        $has_list_event_permission = get_permission_flag($permission_data_with_flag);
        if (!$has_list_event_permission) {
            return parent::getAdminError();
        }

        $list_event_permission_data = get_permission_data($permission_data_with_flag);
        $filter_params = has_system_level_access($list_event_permission_data)?
            [] : ["in_ids" => get_instance_ids($list_event_permission_data, Contexts::PROGRAM)];

        //Role based access
        $feeds = Program::getAllProgramByIDOrSlug('content_feed', '', $filter_params);
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = 'Event';
        $this->layout->pageicon = 'fa fa-calendar';
        $this->layout->pagedescription = trans('admin/event.event_create_success');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'event');
        $this->layout->content = view('admin.theme.event.success_event')
            ->with('event_id', $eid)
            ->with('feeds', $feeds);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getEditEvent($eid)
    {
        $edit_event_permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::EVENT,
            PermissionType::ADMIN,
            EventPermission::EDIT_EVENT,
            null,
            null,
            true
        );

        if (!is_element_accessible(
            get_permission_data($edit_event_permission_data_with_flag),
            ElementType::EVENT,
            $eid
        )) {
            return parent::getAdminError();
        }

        if (!is_numeric($eid)) {
            abort(404);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/event.manage_event') => 'event',
            trans('admin/event.edit_event') => ''
        ];

        // Checking whether given event is available in db
        $event = Event::where('event_id', '=', (int)$eid)->firstOrFail();
        $user = User::where('status', '=', 'ACTIVE')
            ->get(['uid', 'username', 'firstname', 'lastname', 'email']);
        $host = WebexHost::where('status', '=', 'ACTIVE')
            ->get()
            ->toArray();

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/event.edit_event');
        $this->layout->pageicon = 'fa fa-calendar';
        $this->layout->pagedescription = trans('admin/event.edit_event');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'event');
        $this->layout->content = view('admin.theme.event.edit_event')
            ->with('event', $event)
            ->with('user', $user)
            ->with('tz', Webex::webex_time_zone_list_array())
            ->with('compare', Webex::webex_mdl_to_webex_tz_array())
            ->with('host', $host);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function postEditEvent(Request $request)
    {
        $edit_event_permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::EVENT,
            PermissionType::ADMIN,
            EventPermission::EDIT_EVENT,
            null,
            null,
            true
        );

        if (!is_element_accessible(
            get_permission_data($edit_event_permission_data_with_flag),
            ElementType::EVENT,
            $request->_eid
        )) {
            return parent::getAdminError();
        }

        // Checking whether given question bank is available in db
        $event = Event::where('event_id', '=', (int)$request->_eid)->firstOrFail();

        $rules = [
            'event_name' => 'required|min:3|max:512',
            'event_short_description' => 'max:2500',
            'start_date' => 'required|date_format:"d-m-Y"',
            'start_time' => 'required|date_format:"H:i"'
        ];

        $messages['check_duration'] = 'The duration cannot be set to zero';

        if ($request->event_type == 'live') {
            $rules['event_host'] = 'required|integer';
            $messages['event_host.integer'] = 'The event host field is required';
            $rules['duration'] = 'required|date_format:"H:i"|checkDuration';
            $rules['timezone'] = 'required';
        }

        if ($request->event_type == 'general') {
            if ($request->event_cycle == 'single') {
                $rules['end_date'] = 'required|date_format:"d-m-Y"';
                $rules['end_time'] = 'required|date_format:"H:i"';
            }
        }

        //Custom validation for duration
        Validator::extend('checkDuration', function ($attribute, $value, $parameters) {
            if (isset($value) && !empty($value) && "0:00" == trim($value)) {
                return false;
            } else {
                return true;
            }
        });

        $validation = Validator::make(Input::all(), $rules, $messages);
        if ($validation->fails()) {
            return redirect('cp/event/edit-event/' . (int)$request->_eid)
                ->withInput()
                ->withErrors($validation);
        } else {
            // Initialize the values
            $start_time = $end_time = $duration = 0;

            // Event duration
            if ($request->has('duration')) {
                $temp = explode(':', trim($request->duration));
                $duration = ($temp[0] * 60) + $temp[1];
            }

            $event_data = [
                'event_cycle' => $event['event_cycle'],
                'event_name' => trim(htmlspecialchars(strip_tags($request->event_name))),
                'event_description' => $request->event_description,
                'event_short_description' => trim(strip_tags($request->event_short_description)),
                'keywords' => ($request->has('keywords')) ? array_map('trim', explode(',', strip_tags($request->keywords))) : []
            ];

            if ($request->event_type == 'live') {
                $user = User::where('status', '=', 'ACTIVE')
                    ->where('uid', '=', (int)$request->event_host)
                    ->first(['firstname', 'lastname']);

                $event_data['speakers'] = ($request->has('speakers')) ? array_map('trim', explode(',', strip_tags($request->speakers))) : [];
                $event_data['event_host_id'] = (int)$request->event_host;
                $event_data['event_host_name'] = trim($user->firstname) . ' ' . trim($user->lastname);

                // Webex instance object
                $webex = new Webex(
                    config('app.webex_servicelayer_url'),
                    config('app.webex_appkey'),
                    config('app.webex_username'),
                    config('app.webex_password')
                );

                $event_data['start_date_label'] = trim($request->start_date);
                $event_data['start_time_label'] = trim($request->input('start_time', '0:00'));

                $session_time = $event_data['start_date_label'] . ' ' . $event_data['start_time_label'];
                $tz = $webex->timezone($session_time, (int)$request->timezone);

                $event_data['start_time'] = (int)Timezone::convertToUTC($session_time, $tz['timezone'], 'U');

                if ($event_data['start_time'] < time()) {
                    $validation->getMessageBag()->add('start_date', 'Start date should be greater than the current time');
                    return redirect('cp/event/edit-event/' . (int)$request->_eid)
                        ->withInput()
                        ->withErrors($validation);
                }

                $event_data['duration'] = (int)$duration;
                $event_data['session_type'] = $event->session_type;
                $event_data['webex_timezone'] = (int)$request->timezone;
                $event_data['webex_timezone_label'] = Webex::webex_time_zone_list_array()[$event_data['webex_timezone']];
                $event_data['end_time'] = (int)$event_data['start_time'] + ($event_data['duration'] * 60);

                // Merge tz array with original data
                $event_data = array_merge($event_data, $tz);

                $host = WebexHost::where('webex_host_id', '=', (int)$event->webex_host_id)
                    ->firstOrFail();

                // Webex parameters
                $param = [];
                $param['hostUsername'] = $host['username'];
                $param['hostPassword'] = $host['password'];
                $param['sessionKey'] = $event->session_key;
                $param['confName'] = $event_data['event_name'];
                $param['agenda'] = $event_data['event_short_description'];
                $param['startDate'] = Carbon::parse($event_data['start_date_label'] . ' ' . $event_data['start_time_label'])->format('m/d/Y G:i:s');
                $param['timeZoneID'] = $event_data['webex_timezone'];
                $param['duration'] = $duration;
                $param['listing'] = 'PRIVATE';
                if (($request->st_time != $request->start_time) || ($request->st_date != $request->start_date)) {
                    $scheduled = $this->getCheckAvailability($host['webex_host_id'], $event_data['start_time'], $event_data['end_time']);

                    if (!empty($scheduled)) {
                        $validation->getMessageBag()->add('start_time', trans('admin/event.event_scheduled'));
                        return redirect('cp/event/edit-event/' . (int)$request->_eid)
                            ->withInput()
                            ->withErrors($validation);
                    }
                }
                $response = $webex->update_session($event_data, $param);

                if (isset($response['status']) && $response['status'] !== true) {
                    if (is_array($response['error'])) {
                        $error_msg = implode(',', $response['error']);
                    } else {
                        $error_msg = $response['error'];
                    }
                    Session::put('error', $error_msg);
                    return redirect('cp/event/edit-event/' . (int)$request->_eid)
                            ->withInput();
                }
            }

            if ($request->event_type == 'general') {
                // Event start time
                if ($request->has('start_date')) {
                    $start_time = Timezone::convertToUTC($request->start_date, Auth::user()->timezone, 'U');
                    if ($request->has('start_time')) {
                        $temp = explode(':', trim($request->start_time));
                        $start_time += (($temp[0] * 60) + $temp[1]) * 60;
                    }
                }

                // Event end time
                if ($request->has('end_date')) {
                    $end_time = Timezone::convertToUTC($request->end_date, Auth::user()->timezone, 'U');
                    if ($request->has('end_time')) {
                        $temp = explode(':', trim($request->end_time));
                        $end_time += (($temp[0] * 60) + $temp[1]) * 60;
                    }
                }

                // Endtime check
                if ($request->has('start_date') && $request->has('end_date')) {
                    if ($start_time >= $end_time) {
                        $validation->getMessageBag()->add('end_date', 'End date should be always higher than start date');
                        return redirect('cp/event/edit-event/' . (int)$request->_eid)
                            ->withInput()
                            ->withErrors($validation);
                    }
                    if (floor(abs($end_time - $start_time) / 86400) > config('app.general_event_max_days')) {
                        $validation->getMessageBag()->add('end_date', trans('admin/event.general_event_max_days'));
                        return redirect('cp/event/edit-event/' . (int)$request->_eid)
                            ->withInput()
                            ->withErrors($validation);
                    }
                }

                if ($request->event_cycle == 'single') {
                    $event_data['start_date_label'] = trim($request->start_date);
                    $event_data['start_time_label'] = trim($request->input('start_time', '0:00'));
                    $event_data['start_time'] = (int)$start_time;
                    $event_data['end_date_label'] = trim($request->end_date);
                    $event_data['end_time_label'] = trim($request->input('end_time', '0:00'));
                    $event_data['end_time'] = (int)$end_time;
                    $event_data['location'] = trim(strip_tags($request->location));
                }
            }

            $event_data['updated_by'] = Auth::user()->username;
            $event_data['updated_at'] = time();
            $event_data['cron_flag'] = 0;
            $event_data['report_cron_flag'] = 0;

//         item page redirection
            if (Input::get('post_slug')) {
                //echo "hai"; die;
                $post_slug = Input::get('post_slug');
                if (Event::where('event_id', '=', (int)$event->event_id)
                    ->update($event_data)
                ) {
                    if (config('elastic.service')) {
                        event(new EventEdited($event->event_id));
                    }
                    return redirect('cp/contentfeedmanagement/elements/' . $post_slug);
                }
            } elseif (Event::where('event_id', '=', (int)$event->event_id)
                ->update($event_data)
            ) {
                if (config('elastic.service')) {
                    event(new EventEdited($event->event_id));
                }
                return redirect('cp/event/')
                    ->with('success', trans('admin/event.event_updated'));
            } else {
                return redirect('cp/event/')
                    ->with('error', trans('admin/event.problem_while_updating_event'));
            }
        }
    }

    public function postAssignEvent($action = null, $key = null)
    {
        $event = Event::where('event_id', '=', (int)$key)->first();
        if (empty($event)) {
            return response()->json(['flag' => 'error', 'message' => 'Invalid event']);
        }

        $ids = (!empty(Input::get('ids')) ? explode(',', Input::get('ids')) : []);
        
        if (Input::get('empty') != true) {
            if (empty($ids) || !is_array($ids)) {
                return response()->json(['flag' => 'error', 'message' => 'No checkboxes are selected']);
            }
        }

        $ids = array_map('intval', $ids);

        switch ($action) {
            case 'user':
                $permission_data_with_flag = $this->roleService->hasPermission(
                    Auth::user()->uid,
                    ModuleEnum::EVENT,
                    PermissionType::ADMIN,
                    EventPermission::ASSIGN_USER,
                    null,
                    null,
                    true
                );

                $permission_data = get_permission_data($permission_data_with_flag);
                $has_assign_event_permission = is_element_accessible(
                    $permission_data,
                    ElementType::EVENT,
                    $key
                );

                if ($has_assign_event_permission) {
                    $has_assign_event_permission = are_users_exist_in_context(
                        $permission_data,
                        $ids
                    );
                }

                if (!$has_assign_event_permission) {
                    return response()->json(
                        [
                            'flag' => 'error',
                            'message' => trans("admin/event.no_per_to_assign_users")
                        ]
                    );
                }

                $arrname = 'active_user_event_rel';
                
                $event_relation = isset($event->relations[$arrname]) ? $event->relations[$arrname] : [];
                if( !is_admin_role(Auth::user()->role) ) {
                    /* If the user is a ProgramAdmin/ContentAuthor */
                    /* $manageable_ids = Uids which belongs to PA/CA users */
                    $manageable_ids = array_values(array_intersect(get_user_ids($permission_data), $event_relation)); 
                    /* $dedupe_ids => Uids which are in the event relation and are assigned by Site Admin */
                    $dedupe_ids = array_diff($event_relation, $manageable_ids);
                   
                    /* Note: when $dedupe_ids is empty, It means $manageable_ids and $event_relation contains same uids then assigning $manageable_ids to $dedupe_ids */
                    /* Below code is to remove the relations from the user tables */
                    if(empty($dedupe_ids)) {
                        $dedupe_ids = $manageable_ids;
                    }
                    
                 } else {
                     /* If the user is Site Admin  */
                     /* $manageable_ids => event user rel ie. "active_user_event_rel" */
                     $manageable_ids = $event_relation;
                     /* $dedupe_ids => event user rel ie. "active_user_event_rel" */
                     $dedupe_ids = $event_relation;
                 }
               
                if (isset($dedupe_ids) && !empty($dedupe_ids)) {
                    $delete = array_diff($manageable_ids, $ids);
                    $add = array_diff($ids, $dedupe_ids);
                    
                    if(!is_admin_role(Auth::user()->role)) {
                        /* $ids => taking the array difference of ( event_user_rel+selected uids as the input) and $delete */
                        $ids = array_values(array_diff(array_unique(array_merge($event_relation, $add)), $delete));
                    }
                    
                } else {
                    $delete = [];
                    $add = $ids;
                }

                foreach ($delete as $value) {
                    User::removeUserRelation($value, ['user_event_rel'], $event->event_id);
                    // Notifications
                    /*if (config('app.notifications.event.unassign_user') && !empty($delete)) {
                        Notification::getInsertNotification(
                            $delete,
                            'Event',
                            trans('admin/event.notify_unassign_user', ['name'=>$event->event_name])
                        );
                    }*/
                }
                if (config('app.notifications.event.unassign_user') && !empty($delete)) {
                    NotificationLog::getInsertNotification(
                        $delete,
                        'Event',
                        trans('admin/event.notify_unassign_user', ['name' => $event->event_name])
                    );
                }

                foreach ($add as $value) {
                    User::addUserRelation($value, ['user_event_rel'], $event->event_id);
                    // Notifications
                    /*if (config('app.notifications.event.assign_user')) {
                        Notification::getInsertNotification(
                            (int) $value,
                            'Event',
                            trans('admin/event.notify_assign_user', ['name'=>$event->event_name])
                        );
                    }*/
                }
                // Notifications log
                if (config('app.notifications.event.assign_user') && !empty($add)) {
                    NotificationLog::getInsertNotification(
                        $add,
                        'Event',
                        trans('admin/event.notify_assign_user', ['name' => $event->event_name])
                    );
                }
                $msg = trans('admin/user.user_assigned');
                break;
            case 'usergroup':
                $permission_data_with_flag = $this->roleService->hasPermission(
                    Auth::user()->uid,
                    ModuleEnum::EVENT,
                    PermissionType::ADMIN,
                    EventPermission::ASSIGN_USER_GROUP,
                    null,
                    null,
                    true
                );

                $permission_data = get_permission_data($permission_data_with_flag);
                $has_assign_event_permission = is_element_accessible(
                    $permission_data,
                    ElementType::EVENT,
                    $key
                );

                if ($has_assign_event_permission) {
                    $has_assign_event_permission = are_user_groups_exist_in_context(
                        $permission_data,
                        $ids
                    );
                }

                if (!$has_assign_event_permission) {
                    return response()->json(
                        [
                            'flag' => 'error',
                            'message' => trans("admin/event.no_per_to_assign_usersgroups")
                        ]
                    );
                }

                $arrname = 'active_usergroup_event_rel';
                
                $event_relation = isset($event->relations[$arrname]) ? $event->relations[$arrname] : [];
                if( !is_admin_role(Auth::user()->role) ) {
                    /* If the user is a ProgramAdmin/ContentAuthor */
                    /* $manageable_ids = Uids which belongs to PA/CA users */
                    $manageable_ids = array_values(array_intersect(get_user_group_ids($permission_data), $event_relation)); 
                    /* $dedupe_ids => Uids which are in the relation and are assigned by Site Admin */
                    $dedupe_ids = array_diff($event_relation, $manageable_ids);
                    
                    /* Note: when $dedupe_ids is empty, It means $manageable_ids and $event_relation contains same uids then assigning $manageable_ids to $dedupe_ids */
                    /* Below code is to remove the relations from the user tables */
                    if(empty($dedupe_ids)) {
                        $dedupe_ids = $manageable_ids;
                    }
                    
                 } else {
                     /* If the user is Site Admin  */
                     /* $manageable_ids => event usergroup rel ie. "active_usergroup_event_rel" */
                     $manageable_ids = $event_relation;
                     /* $dedupe_ids => event usergroup rel ie. "active_usergroup_event_rel" */
                     $dedupe_ids = $event_relation;
                 }
               
                if (isset($dedupe_ids) && !empty($dedupe_ids)) {
                    $delete = array_diff($manageable_ids, $ids);
                    $add = array_diff($ids, $dedupe_ids);
                    
                    if(!is_admin_role(Auth::user()->role)) {
                        /* $ids => taking the array difference of ( event_usergroups_rel+selected uids as the input) and $delete */
                        $ids = array_values(array_diff(array_unique(array_merge($event_relation, $add)), $delete));
                    }
                    
                } else {
                    $delete = [];
                    $add = $ids;
                }

                $ugs = UserGroup::where('status', '=', 'ACTIVE')->whereIn('ugid', $ids)->get();
                $notify_ids_ary = [];
                foreach ($delete as $value) {
                    UserGroup::removeUserGroupRelation($value, ['usergroup_event_rel'], $event->event_id);
                    // Notifications
                    if (config('app.notifications.event.unassign_usergroup')) {
                        $ug = $ugs->whereLoose('ugid', $value);
                        if (!$ug->isEmpty()) {
                            if (isset($ug->first()->relations['active_user_usergroup_rel'])) {
                                $notify_ids_ary = array_merge($notify_ids_ary, $ug->first()->relations['active_user_usergroup_rel']);
                                /* foreach ($ug->first()->relations['active_user_usergroup_rel'] as $value) {

                                     Notification::getInsertNotification(
                                         (int) $value,
                                         'Event',
                                         trans('admin/event.notify_unassign_user', ['name'=>$event->event_name])
                                     );
                                 }*/
                            }
                        }
                    }
                }
                if (!empty($notify_ids_ary)) {
                    NotificationLog::getInsertNotification(
                        $notify_ids_ary,
                        'Event',
                        trans('admin/event.notify_unassign_user', ['name' => $event->event_name])
                    );
                }
                $notify_ids_ary = [];
                foreach ($add as $value) {
                    UserGroup::addUserGroupRelation($value, ['usergroup_event_rel'], $event->event_id);
                    // Notifications
                    if (config('app.notifications.event.assign_usergroup')) {
                        $ug = $ugs->whereLoose('ugid', $value);
                        if (!$ug->isEmpty()) {
                            if (isset($ug->first()->relations['active_user_usergroup_rel'])) {
                                $notify_ids_ary = array_merge($notify_ids_ary, $ug->first()->relations['active_user_usergroup_rel']);
                                /*foreach ($ug->first()->relations['active_user_usergroup_rel'] as $value) {
                                    Notification::getInsertNotification(
                                        (int) $value,
                                        'Event',
                                        trans('admin/event.notify_assign_user', ['name'=>$event->event_name])
                                    );
                                }*/
                            }
                        }
                    }
                }
                if (!empty($notify_ids_ary)) {
                    NotificationLog::getInsertNotification(
                        $notify_ids_ary,
                        'Event',
                        trans('admin/event.notify_assign_user', ['name' => $event->event_name])
                    );
                }
                $msg = trans('admin/user.usergroup_assigned');
                break;
            case 'feed':
                $feed = Input::get('feed');
                $program = Program::getAllProgramByIDOrSlug('content_feed', $feed)->first();

                if (!$this->roleService->hasPermission(
                    $this->request->user()->uid,
                    ModuleEnum::CHANNEL,
                    PermissionType::ADMIN,
                    ChannelPermission::MANAGE_CHANNEL_POST,
                    Contexts::PROGRAM,
                    $program->program_id
                )) {
                    return response()->json(
                        [
                            'flag' => 'error',
                            'message' => trans("admin/program.no_permission_to_manage_posts")
                        ]
                    );
                }

                $arrname = 'feed_event_rel.' . $program->program_id;

                if (!empty($program)) {
                    if (isset($event->relations['feed_event_rel'][$program->program_id]) && !empty($event->relations['feed_event_rel'][$program->program_id])) {
                        $deletepackets = array_diff($event->relations['feed_event_rel'][$program->program_id], $ids);
                        $addpackets = array_diff($ids, $event->relations['feed_event_rel'][$program->program_id]);
                    } else {
                        $deletepackets = [];
                        $addpackets = $ids;
                    }

                    foreach ($deletepackets as $value) {
                        $p = Packet::where('packet_id', '=', $value)
                            ->where('status', '!=', 'DELETED')
                            ->first();
                        if (!empty($p)) {
                            $temp_element = [];
                            foreach ($p->elements as $element) {
                                if ($element['type'] == 'event' && $element['id'] == $event->event_id) {
                                } else {
                                    $temp_element[] = $element;
                                }
                            }
                            Packet::where('packet_id', '=', $value)
                                ->update(['elements' => $temp_element, 'updated_at' => time()]);
                            if (config('elastic.service')) {
                                event(new ItemsAdded($value));
                            }
                        }
                    }

                    foreach ($addpackets as $value) {
                        $p = Packet::where('packet_id', '=', $value)
                            ->where('status', '!=', 'DELETED')
                            ->first();
                        $i = 1;
                        $temp = [];
                        $insert = true;
                        if (!empty($p->elements)) {
                            foreach ($p->elements as $element) {
                                $element['order'] = $i;
                                $temp[] = $element;
                                $i++;
                                if ($element['type'] == 'event' && $element['id'] == (int)$event->event_id) {
                                    $insert = false;
                                }
                            }
                        }
                        if ($insert == true) {
                            $e['type'] = 'event';
                            $e['order'] = $i;
                            $e['id'] = (int)$event->event_id;
                            $e['name'] = $event->event_name;
                            $temp[] = $e;
                        }
                        DB::collection('packets')
                            ->where('packet_id', '=', $value)
                            ->push('elements', $temp, true);
                        if (config('elastic.service')) {
                            event(new ItemsAdded($value));
                        }
                    }
                    $msg = trans('admin/program.channel_assigned_success');
                } else {
                    return response()->json(['flag' => 'error', 'message' => 'Invalid feed']);
                }

                break;
            default:
                return response()->json(['flag' => 'error', 'message' => 'Wrong action parameter']);
                break;
        }

        Event::where('event_id', '=', $event->event_id)
            ->unset('relations.' . $arrname);
        if (!empty($ids)) {
            if (Event::where('event_id', '=', (int)$event->event_id)
                ->update(['relations.' . $arrname => $ids,
                    'updated_at' => time()])
            ) {
                if ($action == 'user' || $action == 'usergroup') {
                    if (config('elastic.service')) {
                        event(new EventAssigned($event->event_id));
                    }
                }
                return response()->json(['flag' => 'success', 'message' => $msg]);
            } else {
                return response()->json(['flag' => 'error']);
            }
        } else {
            if ($action == 'user' || $action == 'usergroup') {
                if (config('elastic.service')) {
                    event(new EventAssigned($event->event_id));
                }
            }
            return response()->json(['flag' => 'success', 'message' => $msg]);
        }
    }

    public function getDeleteEvent($eid)
    {
        $delete_event_permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::EVENT,
            PermissionType::ADMIN,
            EventPermission::DELETE_EVENT,
            null,
            null,
            true
        );

        if (!is_element_accessible(
            get_permission_data($delete_event_permission_data_with_flag),
            ElementType::EVENT,
            $eid
        )) {
            return parent::getAdminError();
        }

        if (!is_numeric($eid)) {
            abort(404);
        }

        // Checking whether given event is available in db
        $event = Event::where('event_id', '=', (int)$eid)->firstOrFail();

        $start = (int)Input::get('start', 0);
        $limit = (int)Input::get('limit', 10);
        $search = Input::get('search', '');
        $order_by = Input::get('order_by', '5 desc');
        $show = Input::get('show', 'all');

        // Delete webex session
        if ($event->event_type == 'live') {
            $host = WebexHost::where('webex_host_id', '=', (int)$event->webex_host_id)
                ->firstOrFail();

            // Webex parameters
            $param = [];
            $param['hostUsername'] = $host['username'];
            $param['hostPassword'] = $host['password'];
            $param['sessionKey'] = $event->session_key;

            // Webex instance object
            $webex = new Webex(
                config('app.webex_servicelayer_url'),
                config('app.webex_appkey'),
                config('app.webex_username'),
                config('app.webex_password')
            );

            $webex->delete_session($event->session_type, $param);
            if (array_get($event, 'recordings')) {
                foreach($event->recordings as $recording) {
                    $webex->recording('delete', ['recordingID' => $recording['recordingID']]);
                }
            }
        }

        // Check user relations
        if (isset($event->relations['active_user_event_rel']) && !empty($event->relations['active_user_event_rel'])) {
            return redirect('cp/event')
                ->with('error', trans('admin/event.event_with_user'));
        }
        // Check usergroup relations
        if (isset($event->relations['active_usergroup_event_rel']) && !empty($event->relations['active_usergroup_event_rel'])) {
            return redirect('cp/event')
                ->with('error', trans('admin/event.event_with_usergroup'));
        }
        // check feed relations
        if (isset($event->relations['feed_event_rel']) && !empty(array_flatten($event->relations['feed_event_rel']))) {
            return redirect('cp/event')
                ->with('error', trans('admin/event.event_with_channel'));
        }

        if (Event::where('event_id', '=', (int)$event->event_id)->update(['status' => 'DELETED'])) {
            if (config('elastic.service')) {
                event(new EventRemoved($event->event_id));
            }
            $totalRecords = Event::type($show)
                ->where('status', '=', 'ACTIVE')
                ->count();
            if ($totalRecords <= $start) {
                $start -= $limit;
                if ($start < 0) {
                    $start = 0;
                }
            }
            return redirect('cp/event/?start=' . $start . '&limit=' . $limit . '&show=' . $show . '&search=' . $search . '&order_by=' . $order_by)
                ->with('success', trans('admin/event.event_deleted_success'));
        } else {
            return redirect('cp/event/?start=' . $start . '&limit=' . $limit . '&show=' . $show . '&search=' . $search . '&order_by=' . $order_by)
                ->with('error', trans('admin/event.problem_while_deleting_the_event'));
        }
    }

    public function getCheckAvailability($id, $start_time, $end_time)
    {
        $event_exists = Event::orWhere(function ($event) use ($id, $start_time, $end_time) {
            $event->where('webex_host_id', '=', (int)$id)
                ->where('start_time', '<=', $start_time)
                ->where('end_time', '>=', $start_time)
                ->where('status', '!=', 'DELETED');
        })->orwhere(function ($event) use ($id, $start_time, $end_time) {
            $event->where('webex_host_id', '=', (int)$id)
                ->where('start_time', '<', $end_time)
                ->where('end_time', '>', $end_time)
                ->where('status', '!=', 'DELETED');
        })->orwhere(function ($event) use ($id, $start_time, $end_time) {
            $event->where('webex_host_id', '=', (int)$id)
                ->where('start_time', '>', $start_time)
                ->where('end_time', '<', $end_time)
                ->where('status', '!=', 'DELETED');
        })->orwhere(function ($event) use ($id, $start_time, $end_time) {
            $event->where('webex_host_id', '=', (int)$id)
                ->where('start_time', '>', $start_time)
                ->where('start_time', '<', $end_time)
                ->where('end_time', '>', $end_time)
                ->where('status', '!=', 'DELETED');
        })
            ->get()->toArray();

        return $event_exists;
    }

// show availability code
    public function getShowAvailability($id, $date, $timezone)
    {
        $first_date = strtotime($date);
        $second_date = strtotime('+23 hour +59 minutes +59 seconds', strtotime($date));

        $data = [];
        $data['host_required'] = 0;
        if ($id == 0) {
            $data['host_required'] = trans('admin/event.host_required');
        } else {
            $host_name = WebexHost::where('webex_host_id', '=', (int)$id)->value('name');

            $availability = Event::where('webex_host_id', '=', (int)$id)->where('status', '!=', 'DELETED')->where('webex_timezone', '=', (int)$timezone)->where('start_time', '>=', (int)$first_date)->where('start_time', '<=', (int)$second_date)->orderby('start_time', 'asc')->get()->toArray();


            if (empty($availability)) {
                $data['no_schedule'] = trans('admin/event.no_schedule');
            } else {
                $output = '';
                $output .= "<table class='table table-advance'>";
                $output .= "<thead><tr><strong><center>
                         " . $host_name . " </strong> on <strong> " . $availability[0]['start_date_label'] . " </strong> is scheduled for the below events" . "</center>
                        </tr></thead>";
                $output .= "<tr>
                            <td width='140px'><strong>Event name </strong></td>
                            <td width='140px'><strong>Start time</strong></td>
                            <td width='140px'><strong>End time</strong></td>
                            <td width='140px'><strong>Duration</strong></td>
                        </tr>";

                foreach ($availability as $info) {
                    $st = $info['start_time'];
                    $UTC_time = new DateTime($st, new DateTimeZone("UTC"));
                    $Local_time = $UTC_time;
                    $Local_time->setTimezone(new DateTimeZone($info['timezone']));
                    $start_time = $Local_time->format(" g:i a");

                    $et = $info['end_time'];
                    $UTC_time = new DateTime($et, new DateTimeZone("UTC"));
                    $Local_time = $UTC_time;
                    $Local_time->setTimezone(new DateTimeZone($info['timezone']));
                    $end_time = $Local_time->format(" g:i a");

                    $output .= "<tr>
                                <td>" . $info['event_name'] . "</td>
                                <td>" . $start_time . "</td>
                                <td>" . $end_time . "</td>
                                <td>" . $info['duration'] . " min</td>
                            </tr>";
                }
                $output .= "</table>";

                $data['scheduled_events'] = $output;
            }
        }
        return json_encode($data);
    }

    public function getDeleteRecord(Request $request, $record_id)
    {
        $webex = new Webex(
            config('app.webex_servicelayer_url'),
            config('app.webex_appkey'),
            config('app.webex_username'),
            config('app.webex_password')
        );
        $event = Event::where('event_id', (int)$request->event_id)->first();
        
        $recording = collect($event->recordings)
                        ->where('recordingID', (int)$record_id)
                        ->collapse()
                        ->toArray();
        $recording['deleted_at'] = time();                
        $recording['deleted_by'] = Auth::user()->username;
        $event_id = $event->event_id;
        $deletedRecordingds = $this->deletedEventsRecordingsService->getEventDetails($event_id);
        
        $web_hosts_details = $this->webexHostRepository->getWebHostDetails($event->webex_host_id);
        $response = $webex->recording('forceDelete', 
            [
                'recordingID' => $record_id, 
                'hostUsername' => $web_hosts_details->username, 
                'hostPassword'=> $web_hosts_details->password
            ]
        );
        $recordings = collect($event->recordings)->filter(function ($record) use ($record_id) {
            return $record['recordingID'] != $record_id;
        });
        Event::where('event_id', (int)$request->event_id)->update(['recordings' => $recordings->toArray()]);
        if (!empty($deletedRecordingds)) {
            $existing_recordings =  collect($deletedRecordingds->recordings)->toArray();
            $recording_ids = array_column($existing_recordings, 'recordingID');
            $updated_recordings =  collect($deletedRecordingds->recordings)->push($recording)->toArray();
            if (!in_array($record_id, $recording_ids)) {
                $this->deletedEventsRecordingsService->updateDeletedRecordings($event_id, $updated_recordings);
            }
        } else {
            $recording_delete_id = $this->deletedEventsRecordingsService->getNextSequence();
            $data = [
                "deleted_recording_id" => $recording_delete_id,
                "event_id" => $event_id,
                "recordings" => [$recording]
            ];
            $this->deletedEventsRecordingsService->insertEventsDetails($data);
        }
        return $response;
    }
}
