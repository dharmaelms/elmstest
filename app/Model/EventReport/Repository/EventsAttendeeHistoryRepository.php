<?php
namespace App\Model\EventReport\Repository;

use App\Model\EventReport\EventsAttendeeHistory;

/**
 * Class WebExAttendeeHistoryRepository
 *
 * @package App\Model\EventReport\Repository
 */
class EventsAttendeeHistoryRepository implements IEventsAttendeeHistoryRepository
{
    public function attendeeDetails($confID, $filter = [], $sortby)
    {
        $query = EventsAttendeeHistory::where('confID', (int)$confID)
                ->project(['_id' => 0]);
        if (isset($filter['search']) && !empty($filter['search'])) {
            $query->where('attendee_name', 'like', '%' . $filter['search'] . '%');
        }
        if (isset($sortby) && !empty($sortby)) {
            $key = key($sortby);
            $value = $sortby[$key];
            $query->orderBy($key, $value);
        }
        if (isset($filter['limit']) && !empty($filter['limit'])) {
            $query->skip((int)$filter['start'])->take((int)$filter['limit']);
        }
        return $query->get(['host_id', 'host_name', 'event_name', 'attendee_name', 'attendee_type', 'attendee_email', 'duration', 'start_time', 'end_time', 'session_type']);
    }
}
