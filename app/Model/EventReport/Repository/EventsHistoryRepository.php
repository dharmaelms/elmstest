<?php
namespace App\Model\EventReport\Repository;

use App\Model\EventReport\EventsHistory;

/**
 * Class EventHistoryRepository
 *
 * @package App\Model\EventReport\Repository
 */
class EventsHistoryRepository implements IEventsHistoryRepository
{
    public function sessionDetails($sessions, $start = false, $limit = false, $sortby = ['event_name' => 'desc'])
    {
        $history = EventsHistory::whereIn('session_key', $sessions)
            ->project(['_id' => 0]);
        if ($limit) {
            $history->skip((int)$start)->take((int)$limit);
        }
        if (isset($sortby) && !empty($sortby)) {
            $key = key($sortby);
            $value = $sortby[$key];
            $history->orderBy($key, $value);
        }
        return $history->get(['event_name', 'host_name', 'total_participants', 'duration', 'start_time', 'end_time', 'host_id', 'session_type','session_key', 'confID']);
    }
}
