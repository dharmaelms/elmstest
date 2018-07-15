<?php

namespace App\Model\Event;

use Auth;
use Carbon\Carbon;
use App\Exceptions\Event\EventNotFoundException;
use App\Exceptions\Event\NoEventAssignedException;
use App\Model\Event;

/**
 * Class AnnouncementRepository
 *
 * @package App\Model\Announcement
 */
class EventRepository implements IEventRepository
{

    /**
     * {@inheritdoc}
     */
    public function getAllEvents()
    {
        $event_ids = array_unique(Event::userEventRel()['event_list']);
        if (empty($event_ids)) {
            throw new NoEventAssignedException;
        }
        return $event_ids;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpcomingEvents($event_ids, $page, $limit)
    {
        $upcoming_events = Event::where('status', '=', 'ACTIVE')
            ->whereIn('event_id', $event_ids)
            ->where('end_time', '>', time())
            ->orderBy('start_time')
            ->skip(($page * $limit) - $limit)
            ->take((int)$limit)
            ->get();

        if ($upcoming_events->isEmpty()) {
            throw new EventNotFoundException;
        }
        return $upcoming_events;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventsByUsername($usernames = [])
    {
        return Event::whereIn('created_by', $usernames)
                ->where('status', '=', 'ACTIVE')
                ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getHostingEvents($user_id)
    {
        return Event::where('event_host_id', '=', $user_id)
                    ->active()
                    ->get(['event_id'])->pluck('event_id')->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllActiveEvents()
    {
        return Event::active()->get(['event_id'])->pluck('event_id')->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getNewEventCount(array $event_ids, array $date)
    {
        $query = Event::where('status', '=', 'ACTIVE');
        if (!empty($event_ids)) {
            $query->whereIn('event_id', $event_ids);
        }
        $query->whereBetween('created_at', $date);
        return $query->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getEventsDataUsingIDS($ids)
    {
        return Event::whereIn('event_id', $ids)->get()->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function find($event_id)
    {
        return Event::where('event_id', (int)$event_id)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function getSessions($filter)
    {
        $query = Event::where('event_type', 'live')
            ->when(isset($filter['event_ids']), function ($query) use ($filter) {
                return $query->whereIn('event_id', $filter['event_ids']);
            })
            ->when(isset($filter['event_host']), function ($query) use ($filter) {
                return $query->where('webex_host_username', $filter['event_host']);
            })
            ->when(isset($filter['event_type']), function ($query) use ($filter) {
                return $query->where('session_type', $filter['event_type']);
            })
            ->where(
                'start_time',
                '>=',
                Carbon::createFromFormat(
                    'd-m-Y',
                    $filter['start_date'],
                    Auth::user()->timezone
                )->startOfDay()->timestamp
            )
            ->where(
                'end_time',
                '<=',
                Carbon::createFromFormat(
                    'd-m-Y',
                    $filter['end_date'],
                    Auth::user()->timezone
                )->endOfDay()->timestamp
            );
        if (isset($filter['search']) && !empty($filter['search'])) {
            $query->where('event_name', 'like', '%' . $filter['search'] . '%');
        }
        return $query->Active()->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getEventLists($filters)
    {
        return Event::when(isset($filters['event_ids']), function ($query) use ($filters) {
            return $query->whereIn('event_id', $filters['event_ids']);
        })
            ->where('event_type', 'live')
            ->where('cron_flag', 1)
            ->Active()
            ->get(['session_key', 'event_name']);
    }

    /**
     * {@inheritdoc}
     */
    public function getStorageReport($filter)
    {
        return Event::when(isset($filter['event_host']), function ($query) use ($filter) {
            return $query->where('webex_host_username', $filter['event_host']);
        })
            ->where('recordings', 'exists', true)
            ->Active()
            ->get(['recordings']);
    }

    /**
     * {@inheritdoc}
     */
    public function getEventsWithRecordings()
    {
        return Event::where('event_type', 'live')
            ->where(function ($query){
                $query->where('recording_downloaded', 'exists', true)
                    ->where('recording_downloaded', false);
            })
            ->Active()
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getDownloadedEvents()
    {
        return Event::where('event_type', 'live')
            ->where(function ($query){
                $query->where('recording_downloaded', 'exists', true)
                      ->where('recording_downloaded', true);
            })
             ->where(function ($query){
                 $query->where('recording_uploaded', 'exists', true)
                       ->where('recording_uploaded', false);
             })
            ->Active()
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getNewEvents(array $event_ids, array $date, $start, $limit)
    {
        $query = Event::where('status', '=', 'ACTIVE');
        if (!empty($event_ids)) {
            $query->whereIn('event_id', $event_ids);
        }
        $query->whereBetween('created_at', $date);
        return $query->skip((int)$start)
                    ->take($limit)
                    ->orderBy('created_at', 'desc')
                    ->get(['event_name', 'event_type']);
    }
   
    /**
     * @param  array $event_ids
     * @return integer
     */
    public function countActiveEvents($event_ids)
    {
        if (!empty($event_ids)) {
            return Event::whereIn('event_id', $event_ids)->where('status', 'ACTIVE')->count();
        } else {
            return Event::where('status', 'ACTIVE')->count();
        }
    }
}
