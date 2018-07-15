<?php
namespace App\Services\EventReport;

use App\Exceptions\Event\EventNotFoundException;
use App\Model\Event\IEventRepository;
use App\Model\EventReport\Repository\IEventsAttendeeHistoryRepository;
use App\Model\EventReport\Repository\IEventsHistoryRepository;

class EventReportService implements IEventReportService
{

    /**
     * @var \App\Model\Event\IEventRepository
     */
    private $events_repository;

    /**
     * @var \App\Model\EventReport\Repository\IEventsAttendeeHistoryRepository
     */
    private $events_attendee_repository;

    /**
     * @var \App\Model\EventReport\Repository\IEventsHistoryRepository
     */
    private $events_history_repository;


    /**
     * WebExReportService constructor.
     *
     * @param IEventRepository $event_repository
     * @param IEventsAttendeeHistoryRepository $event_attendee_repository
     * @param IEventsHistoryRepository $event_history_repository
     */
    public function __construct(
        IEventRepository $events_repository,
        IEventsAttendeeHistoryRepository $events_attendee_repository,
        IEventsHistoryRepository $events_history_repository
    ) {
        $this->events_repository = $events_repository;
        $this->events_attendee_repository = $events_attendee_repository;
        $this->events_history_repository = $events_history_repository;
    }

    /**
     * {@inheritdoc}
     */
    public function eventReport($query, $start = false, $limit = false, $sortBy = [])
    {
        $events = $this->events_repository->getSessions(array_filter($query));
        if (!$events->isEmpty()) {
            $sessions = $this->events_history_repository->sessionDetails($events->pluck('session_key')->all(), $start, $limit, $sortBy);
            if ($sessions->isEmpty()) {
                throw new EventNotFoundException();
            } else {
                return $sessions;
            }
        } else {
            throw new EventNotFoundException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attendeeReport($confID, $filter = [], $sortBy = [])
    {
        $all_attendees = $this->events_attendee_repository->attendeeDetails($confID, $filter, $sortBy);

        if ($all_attendees->isEmpty()) {
            throw new EventNotFoundException();
        } else {
            return $all_attendees;
        }
    }
}
