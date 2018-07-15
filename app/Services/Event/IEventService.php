<?php

namespace App\Services\Event;

/**
 * Interface IEventService
 *
 * @package App\Services\Event
 */
interface IEventService
{
    /**
     * Helper to get upcoming events
     *
     * @param int $page page no
     * @param int $limit no of results in each page
     *
     * @return array
     */
    public function getUpcomingEvents($page, $limit);

    /**
     * Method to get all events created by username
     *
     * @param array $usernames
     * @return array
     */
    public function getEventsByUsername($usernames = []);

    /**
     * Method to get all event ids assigend to user
     *
     * @return array
     */
    public function getAllEventsAssigned();

    /**
     * Method to get all event ids from assigend programs
     *
     * @return array
     */
    public function getProgramEvents();

    /**
     * getNewEventCount
     * @param  array  $event_ids
     * @param  array  $date
     * @return integer
     */
    public function getNewEventCount(array $event_ids, array $date);

    /**
     * @param array $ids
     * @return array
     */
    public function getEventsDataUsingIDS($ids);

    /**
     * getNewEvents
     * @param  array  $event_ids
     * @param  array  $date
     * @param  integer $start
     * @param  integer  $limit
     * @return collection
     */
    public function getNewEvents(array $event_ids, array $date, $start, $limit);

    /**
     * @param  array $event_ids
     * @return integer
     */
    public function countActiveEvents($event_ids);
}
