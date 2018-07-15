<?php
namespace App\Http\Controllers\Admin;

use App\Enums\Module\Module as ModuleEnum;
use App\Enums\Event\EventPermission;
use App\Enums\Program\ElementType;
use App\Enums\Report\ReportPermission;
use App\Enums\RolesAndPermissions\PermissionType;
use App\Exceptions\Event\EventNotFoundException;
use App\Http\Controllers\AdminBaseController;
use App\Libraries\Helpers;
use App\Model\Common;
use App\Model\Event;
use App\Model\EventReport\EventsAttendeeHistory;
use App\Model\EventReport\EventsHistory;
use App\Model\WebExHost\Repository\IWebExHostRepository;
use App\Model\Event\IEventRepository;
use App\Services\EventReport\IEventReportService;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use League\Csv\Writer;
use Log;
use URL;
use Webex;

class EventReportController extends AdminBaseController
{
    protected $layout = 'admin.theme.layout.master_layout';

    /**
     * @var \App\Model\Event\IEventRepository
     */
    private $event_repository;

    /**
     * @var \App\Model\WebExHost\Repository\IWebExHostRepository
     */
    private $webExHostRepository;

    /**
     * @var \App\Services\EventReport\IEventReportService
     */
    private $eventReportService;

    /**
     * EventReportController constructor.
     *
     * @param IWebExHostRepository $webExHostRepository
     */
    public function __construct(
        IEventRepository $event_repository,
        IWebExHostRepository $webExHostRepository,
        IEventReportService $eventReportService
    ) {
        parent::__construct();
        $this->event_repository = $event_repository;
        $this->webExHostRepository = $webExHostRepository;
        $this->eventReportService = $eventReportService;
    }

    public function getReport()
    {
        if (!has_admin_permission(ModuleEnum::REPORT, ReportPermission::VIEW_REPORT)) {
            return parent::getAdminError($this->theme_path);
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/event.manage_report') => '',
        ];
        $hosts = $this->webExHostRepository->getWebExHosts();
        $total_storage_limit = $hosts->sum('storage_limit');
        $events = $this->getListEvents();
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/event.manage_report');
        $this->layout->pageicon = 'fa fa-calendar';
        $this->layout->pagedescription = trans('admin/event.report');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'report')
            ->with('submenu', 'webexreport');
        $this->layout->content = view('admin.theme.eventreport.list')
            ->with('hosts', $hosts)
            ->with('events', $events)
            ->with('total_storage_limit', $total_storage_limit);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getEventHistory(Request $request)
    {
        try {
            $filters = [
                "event_host" => $request->event_host,
                "event_type" => $request->event_type,
                "start_date" => $request->start_date,
                "end_date" => $request->end_date,
                "search" => (isset($request->search['value']) && !empty($request->search['value']) ) ? $request->search['value'] : '',
            ];
            $attendees = [];
            
            $permission_data_with_flag = $this->roleService->hasPermission(
                Auth::user()->uid,
                ModuleEnum::EVENT,
                PermissionType::ADMIN,
                EventPermission::LIST_EVENT,
                null,
                null,
                true
            );
            $permission_data = get_permission_data($permission_data_with_flag);
            if (!has_system_level_access($permission_data)) {
                $filters["event_ids"] = get_user_accessible_elements($permission_data, ElementType::EVENT);
            }
            
            if ($request->has('start') && $request->has('length')) {
                $start = $request->start;
                $limit = $request->length;
                $order_by = $request->order;
                $orderByArray = ['event_name' => "desc"];
                if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
                    if ($order_by[0]['column'] == '0') {
                        $orderByArray = ['event_name' => $order_by[0]['dir']];
                    }
                    if ($order_by[0]['column'] == '1') {
                        $orderByArray = ['host_name' => $order_by[0]['dir']];
                    }
                    if ($order_by[0]['column'] == '2') {
                        $orderByArray = ['total_participants' => $order_by[0]['dir']];
                    }
                    if ($order_by[0]['column'] == '3') {
                        $orderByArray = ['duration' => $order_by[0]['dir']];
                    }
                    if ($order_by[0]['column'] == '4') {
                        $orderByArray = ['start_time' => $order_by[0]['dir']];
                    }
                    if ($order_by[0]['column'] == '5') {
                        $orderByArray = ['end_time' => $order_by[0]['dir']];
                    }
                    if ($order_by[0]['column'] == '7') {
                        $orderByArray = ['session_type' => $order_by[0]['dir']];
                    }
                    $attendees = $this->eventReportService->eventReport($filters, $start, $limit, $orderByArray);
                    $attendees = $attendees->transform(function ($item, $key) {
                        return [
                        'event_name' =>  $item->event_name,
                        'host_name' => $item->host_name,
                        'total_participants' => $item->total_participants,
                        'duration' => $item->duration,
                        'start_time' => $item->start_time,
                        'end_time' => $item->end_time,
                        'host_id' => $item->host_id,
                        'session_type' => $item->session_type,
                        'confID' => $item->strconfid, /*Converting confid int64 to string */
                        ];
                    });
                }
            }
            $overall_attendees = $this->eventReportService->eventReport($filters)->sortByDesc('start_time');
            if ($request->has('download')) {
                $csv = Writer::createFromFileObject(new \SplTempFileObject());
                //we insert the CSV header
                $csv->insertOne([ trans('admin/event.event_name'),
                    trans('admin/event.host_name'),
                    trans('admin/event.total_attendees'),
                    trans('admin/event.duration_in_minutes'),
                    trans('admin/event.start_time'),
                    trans('admin/event.end_time'),
                    trans('admin/event.host_id'),
                    trans('admin/event.session_type')
                ]);

                // the data into the CSV
                $overall_attendees->each(function ($event) use ($csv) {
                    $csv->insertOne([ $event->event_name,
                        $event->host_name,
                        $event->total_participants,
                        $event->duration,
                        Carbon::parse($event->start_time)->format('d/m/y G:i'),
                        Carbon::parse($event->end_time)->format('d/m/y G:i'),
                        $event->host_id,
                        $event->session_type
                    ]);
                });
                
                // The file is downloadable
                $csv->output('webex-summary.csv');
                exit;
            } else {
                $finaldata =  [
                    'recordsTotal' => $attendees->count(),
                    'recordsFiltered' => $overall_attendees->count(),
                    'data' => $attendees->toArray()
                ];
                return response()->json($finaldata);
            }
        } catch (EventNotFoundException $e) {
            Log::debug('No event found');
        } catch (\Exception $e) {
            Log::error($e->getTraceAsString());
        }

        /* When there is no records, case handled for csv and datatable */
        if ($request->has('download')) {
            $csv = Writer::createFromFileObject(new \SplTempFileObject());
            $csv->insertOne([
                trans('admin/event.no_records_found')
            ]);
            $csv->output('webex-summary.csv');
            exit;
        } else {
            $finaldata =  [
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ];
            return response()->json($finaldata);
        }
    }

    public function getAttendeesDetails(Request $request)
    {
        try {
            $filters = [
                "search" => $request->search['value']
            ];
            $filtered_attendees_data = [];
            if ($request->has('start') && $request->has('length')) {
                $filters += [
                    "start" => isset($request->start) ? $request->start : '',
                    "limit" => isset($request->length) ? $request->length : '' ,
                ];
            
                if ($request->has('order')) {
                    $order_by = $request->order;
                    $orderByArray = ['attendee_name' => "desc"];
                    if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
                        if ($order_by[0]['column'] == '0') {
                            $orderByArray = ['attendee_name' => $order_by[0]['dir']];
                        }
                        if ($order_by[0]['column'] == '1') {
                            $orderByArray = ['attendee_type' => $order_by[0]['dir']];
                        }
                        if ($order_by[0]['column'] == '2') {
                            $orderByArray = ['attendee_email' => $order_by[0]['dir']];
                        }
                        if ($order_by[0]['column'] == '3') {
                            $orderByArray = ['duration' => $order_by[0]['dir']];
                        }
                        if ($order_by[0]['column'] == '4') {
                            $orderByArray = ['start_time' => $order_by[0]['dir']];
                        }
                        if ($order_by[0]['column'] == '5') {
                            $orderByArray = ['end_time' => $order_by[0]['dir']];
                        }
                        $filtered_attendees_data = $this->eventReportService->attendeeReport($request->session_id, $filters, $orderByArray);
                    }
                }
            }

            $all_attendees = $this->eventReportService->attendeeReport($request->session_id);
            $event_host = $all_attendees->lists("host_name")->unique()->first();
            $webex_host = $all_attendees->lists("host_id")->unique()->first();
            $session_type = $all_attendees->lists("session_type")->unique()->first();
            $event_name = $all_attendees->lists("event_name")->unique()->first();
            $total_attendees = $all_attendees->count();

            if ($request->has('download')) {
                $csv = Writer::createFromFileObject(new \SplTempFileObject());

                /* we insert the CSV header */
                $csv->insertOne([
                    trans('admin/event.event_name'),
                    $event_name,
                ]);
                $csv->insertOne([
                    trans('admin/event.host_name'),
                    $event_host,
                ]);
                $csv->insertOne([
                    trans('admin/event.webex_host'),
                    $webex_host,
                ]);
                $csv->insertOne([
                    trans('admin/event.session_type'),
                    $session_type,
                ]);
                $csv->insertOne([]);
                $csv->insertOne([ trans('admin/event.total_no_participants') . $total_attendees ]);
                $csv->insertOne([]);
                $csv->insertOne([
                    trans('admin/event.attendee_name'),
                    trans('admin/event.attendee_type'),
                    trans('admin/event.attendee_email'),
                    trans('admin/event.duration_in_minutes'),
                    trans('admin/event.start_time'),
                    trans('admin/event.end_time')
                ]);

                /* the data into the CSV */
                $all_attendees->each(function ($event) use ($csv) {
                    $csv->insertOne([ $event['attendee_name'],
                        $event['attendee_type'],
                        $event['attendee_email'],
                        $event['duration'],
                        Carbon::parse($event['start_time'])->format('d/m/y G:i'),
                        Carbon::parse($event['end_time'])->format('d/m/y G:i')
                    ]);
                });
                /* The file is downloadable */
                $csv->output('attendee-details.csv');
                exit;
            } else {
                $finaldata =  [
                    'recordsTotal' => $filtered_attendees_data->count(),
                    'recordsFiltered' => $all_attendees->count(),
                    'data' => $filtered_attendees_data->toArray()
                ];
                return response()->json($finaldata);
            }
        } catch (EventNotFoundException $e) {
            Log::debug('No event found');
        } catch (\Exception $e) {
            Log::error($e->getTraceAsString());
        }

        /* When there is no records, case handled for csv and datatable */
        if ($request->has('download')) {
            $csv = Writer::createFromFileObject(new \SplTempFileObject());
            $csv->insertOne([
            trans('admin/event.no_records_found')
            ]);
            $csv->output('attendee-details.csv');
            exit;
        } else {
            $finaldata =  [
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => []
            ];
            return response()->json($finaldata);
        }
    }

    public function getListEvents()
    {
        $filters = [];
        $permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::EVENT,
            PermissionType::ADMIN,
            EventPermission::LIST_EVENT,
            null,
            null,
            true
        );
        $permission_data = get_permission_data($permission_data_with_flag);
        if (!has_system_level_access($permission_data)) {
            $filters["event_ids"] = get_user_accessible_elements($permission_data, ElementType::EVENT);
        }
        return $this->event_repository->getEventLists($filters);
    }

    public function getStorage(Request $request)
    {
        try {
            $events = $this->event_repository->getStorageReport(array_filter($request->all()));
            $total = $events->sum(function ($event) {
                return array_sum(array_pluck($event->recordings, 'size'));
            });
            return ['status' => true, 'data' => Helpers::formatSizeUnits($total*1024*1024)];
        } catch (EventNotFoundException $e) {
            Log::debug('No event found');
        } catch (\Exception $e) {
            Log::error($e->getTraceAsString());
        }
        return ['status' => false, 'message' => trans('admin/event.no_attendees')];
    }

    /**
     * Method to generate the report for WebEx attendees
     *
     * @return void
     */
    public function getCron()
    {
        Log::info("WebEx report cron Called");

        // WebEx instance object
        $webex = new Webex(
            config('app.webex_servicelayer_url'),
            config('app.webex_appkey'),
            config('app.webex_username'),
            config('app.webex_password')
        );

        $events = Event::where(function ($q) {
            $q->orWhere('report_cron_flag', 'exists', false)
            ->orWhere('report_cron_flag', '!=', 1);
        })
           ->where('end_time', '<', Carbon::now()->subHours(3)->timestamp)
           ->where('event_type', 'live')
           ->Active()
           ->get([
                'session_key',
                'session_type',
                'webex_host_username',
                'webex_host_password',
                'event_id',
                'event_name',
                'event_host_name'
            ]);
        if (!$events->isEmpty()) {
            $events->each(function ($event) use ($webex) {
                Log::info('Event cron updating '. $event->event_name. '(' . $event->event_id . ')');
                $param = [];
                $param['sessionKey'] = $event->session_key;
                $param['hostUsername'] = $event->webex_host_username;
                $param['hostPassword'] = $event->webex_host_password;
                $param['start'] = 1;
                $param['limit'] = 500;
                $summary = $webex->attendee_summary($event->session_type, $param);
                if (array_get($summary, 'status')) {
                    if ($event->session_type == 'TC') {
                        $meetingHistory = array_get($summary, 'data.trainingSessionHistory', []);
                    } else {
                        $meetingHistory = array_get($summary, 'data.meetingUsageHistory', []);
                    }
                    EventsHistory::where('session_key', $event->session_key)->delete();
                    $usage = [];
                    if (!array_key_exists('0', $meetingHistory)) {
                        $meetingData[0] = $meetingHistory;
                    } else {
                        $meetingData = $meetingHistory;
                    }
                    foreach ($meetingData as $key => $history) {
                        $usage[] = [
                            'event_name' => $history['confName'],
                            'host_name' => $event->event_host_name,
                            'total_participants' => (int)($history['totalParticipants']),
                            'duration' => $history['duration'],
                            'start_time' => $event->session_type == 'TC' ? Carbon::parse($history['sessionStartTime'])->timezone(config('app.default_timezone'))->format('m/d/Y G:i:s') : Carbon::parse($history['meetingStartTime'])->timezone(config('app.default_timezone'))->format('m/d/Y G:i:s'),
                            'end_time' => $event->session_type == 'TC' ? Carbon::parse($history['sessionEndTime'])->timezone(config('app.default_timezone'))->format('m/d/Y G:i:s') : Carbon::parse($history['meetingEndTime'])->timezone(config('app.default_timezone'))->format('m/d/Y G:i:s'),
                            'host_id' => $event->webex_host_username,
                            'host_email' => $history['hostEmail'],
                            'session_type' => $event->session_type == 'TC' ? 'Training Center' : 'Meeting Center',
                            'session_key' => (int)($event->session_key),
                            'confID' => (int)($history['confID']),
                            'timezone' => $history['timezone'],
                        ];
                    }
                    DB::collection('events_history')->raw(function ($collection) use ($usage) {
                        return $collection->insertMany(array_values($usage), ['continueOnError' => true]);
                    });
                    Log::info($history['confName']); /* Event name */
                    Log::info('Event summary inserted for '. $event->event_name);
                } else {
                    Log::info('No summary found for '.$event->event_name);
                }

                $attendees = $webex->attendee_details($event->session_type, $param);
                if (array_get($attendees, 'status')) {
                    if ($event->session_type == 'TC') {
                        $attendeesHistory = array_get($attendees, 'data.trainingAttendeeHistory', []);
                    } else {
                        $attendeesHistory = array_get($attendees, 'data.meetingAttendeeHistory', []);
                    }
                    EventsAttendeeHistory::where('session_key', $event->session_key)->delete();
                    $summary = [];
                    if (!array_key_exists('0', $attendeesHistory)) {
                        $attendeeData[0] = $attendeesHistory;
                    } else {
                        $attendeeData = $attendeesHistory;
                    }
                    foreach ($attendeeData as $key => $attendee) {
                        $summary[] = [
                            'host_id' => $event->webex_host_username,
                            'host_name' => $event->event_host_name,
                            'session_type' => $event->session_type == 'TC' ? 'Training Center' : 'Meeting Center',
                            'session_key' => (int)($event->session_key),
                            'confID' => (int)($attendee['confID']),
                            'event_name' => $attendee['confName'],
                            'attendee_name' => $event->session_type == 'TC' ? $attendee['attendeeName'] : $attendee['name'],
                            'attendee_type' => $attendee['participantType'],
                            'attendee_email' => $event->session_type == 'TC' ? array_get($attendee, 'attendeeEmail', '') : array_get($attendee, 'email', ''),
                            'duration' => (int)($attendee['duration']),
                            'start_time' => $event->session_type == 'TC' ? Carbon::parse($attendee['startTime'])->timezone(config('app.default_timezone'))->format('m/d/Y G:i:s') : Carbon::parse($attendee['joinTime'])->timezone(config('app.default_timezone'))->format('m/d/Y G:i:s'),
                            'end_time' => $event->session_type == 'TC' ? Carbon::parse($attendee['endTime'])->timezone(config('app.default_timezone'))->format('m/d/Y G:i:s') : Carbon::parse($attendee['leaveTime'])->timezone(config('app.default_timezone'))->format('m/d/Y G:i:s'),
                            
                        ];
                    }
                    DB::collection('events_attendee_history')->raw(function ($collection) use ($summary) {
                        return $collection->insertMany(array_values($summary), ['continueOnError' => true]);
                    });
                    Log::info($attendee['confName']); /* Event name */
                    Log::info('Event attendee summary inserted for '. $event->event_name);
                } else {
                    Log::info('No attendee summary found for '.$event->event_name);
                }

                if (array_get($summary, 'status')  && array_get($attendees, 'status')) {
                    Event::where('event_id', (int)$event->event_id)->update(['report_cron_flag' => 1]);
                } elseif (array_get($summary, 'status') == false  || array_get($attendees, 'status' == false)) {
                    $summary_err_msg = array_get($summary, 'error.0');
                    $att_err_msg = array_get($attendees, 'error.0');

                    if (($summary_err_msg == "Sorry, no record found") && ($att_err_msg == "Sorry, no record found")) {
                        Event::where('event_id', (int)$event->event_id)->update(['report_cron_flag' => 1]);
                    }
                }
            });
        } else {
            Log::info("No event found on recording and report cron");
        }
        Log::info('Webex report cron completed');
        exit(0);
    }
}
