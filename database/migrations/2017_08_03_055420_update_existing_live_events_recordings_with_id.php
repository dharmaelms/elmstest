<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Model\Event;
use App\Model\EventReport\EventsHistory;
use App\Model\EventReport\EventsAttendeeHistory;
use Illuminate\Support\Facades\Schema;

class UpdateExistingLiveEventsRecordingsWithId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Log::info("Webinar cron Called");

        // WebEx instance object
        $webex = new Webex(
            config('app.webex_servicelayer_url'),
            config('app.webex_appkey'),
            config('app.webex_username'),
            config('app.webex_password')
        );

        $events = Event::where('event_type', 'live')
            ->where('start_time', '>=', \Carbon\Carbon::now()->subDays(100)->startOfDay()->timestamp)
            ->where('end_time', '<=' ,\Carbon\Carbon::now()->timestamp)
            ->Active()
            ->get(['session_key', 'session_type', 'webex_host_username', 'webex_host_password', 'event_id', 'event_name', 'event_host_name']);
        if (!$events->isEmpty()) {
            $events->each(function ($event)  use($webex){
                Log::info('Event cron updating '. $event->event_name. '(' . $event->event_id . ')');
                $param = [];
                $param['sessionKey'] = $event->session_key;
                $param['hostUsername'] = $event->webex_host_username;
                $param['hostPassword'] = $event->webex_host_password;
                $response = $webex->recording("read", $param);
                if (array_get($response,'status', false)) {
                    if(array_get($response, 'data.recording')) {
                        Event::where('event_id', (int)$event->event_id)->update(['recordings' => $response['data']['recording']]);
                        Log::info("Event $event->event_name updated with recordings");
                    }
                } else {
                    Log::info('No recording found for '.$event->event_name);
                }
                $param['start'] = 1;
                $param['limit'] = 500;
                $summary = $webex->attendee_summary($event->session_type, $param);
                if(array_get($summary, 'status')) {
                    if ($event->session_type == 'TC') {
                        $meetingHistory = array_get($summary, 'data.trainingSessionHistory', []);
                    } else {
                        $meetingHistory = array_get($summary, 'data.meetingUsageHistory', []);
                    }
                    EventsHistory::where('session_key', $event->session_key)->delete();
                    $usage = [];
                    if(!array_key_exists('0', $meetingHistory)) {
                        $meetingData[0] = $meetingHistory;
                    } else {
                        $meetingData = $meetingHistory;
                    }
                    foreach ($meetingData as $key => $history) {
                        $usage[] = [
                            'host_email' => $history['hostEmail'],
                            'host_id' => $event->webex_host_username,
                            'session_type' => $event->session_type == 'TC' ? 'Training Center' : 'Meeting Center',
                            'session_key' => (int)($event->session_key),
                            'confID' => (int)($history['confID']),
                            'event_name' => $history['confName'],
                            'host_name' => $event->event_host_name,
                            'start_time' => $event->session_type == 'TC' ? $history['sessionStartTime'] : $history['meetingStartTime'],
                            'end_time' => $event->session_type == 'TC' ? $history['sessionEndTime'] : $history['meetingEndTime'],
                            'duration' => $history['duration'],
                            'total_participants' => (int)($history['totalParticipants']),
                            'timezone' => $history['timezone'],
                        ];
                    }
                    DB::collection('events_history')->raw(function ($collection) use ($usage) {
                        return $collection->batchInsert($usage, ['continueOnError' => true]);
                    });
                    Log::info($usage);
                    Log::info('Event summary inserted for '. $event->event_name);
                } else {
                    Log::info('No summary found for '.$event->event_name);
                }

                $attendees = $webex->attendee_details($event->session_type, $param);
                if(array_get($attendees, 'status')) {
                    if ($event->session_type == 'TC') {
                        $attendeesHistory = array_get($attendees, 'data.trainingAttendeeHistory', []);
                    } else {
                        $attendeesHistory = array_get($attendees, 'data.meetingAttendeeHistory', []);
                    }
                    EventsAttendeeHistory::where('session_key', $event->session_key)->delete();
                    $summary = [];
                    if(!array_key_exists('0', $attendeesHistory)) {
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
                            'start_time' => $event->session_type == 'TC' ? $attendee['startTime'] : $attendee['joinTime'],
                            'end_time' => $event->session_type == 'TC' ? $attendee['endTime'] : $attendee['leaveTime'],
                            'duration' => (int)($attendee['duration']),
                        ];
                    }
                    DB::collection('events_attendee_history')->raw(function ($collection) use ($summary) {
                        return $collection->batchInsert($summary, ['continueOnError' => true]);
                    });
                    Log::info($summary);
                    Log::info('Event attendee summary inserted for '. $event->event_name);
                } else {
                    Log::info('No attendee summary found for '.$event->event_name);
                }
                Event::where('event_id', (int)$event->event_id)->update(['cron_flag' => 1]);
            });
        } else {
            Log::info("No event found on recording and report cron");
        }
        Log::info('Event cron completed');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Log::debug('Migration for recording id started - down');

        Event::where('event_type', 'live')->unset('recordings.recordingID');
        Schema::drop('events_history');
        Schema::drop('events_attendee_history');
        Log::debug('Migration for recording id completed - down');
    }
}
