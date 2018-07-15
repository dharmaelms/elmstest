<?php

namespace App\Model\Event;

/**
 * Interface IEventRepository
 *
 * @package App\Model\Event
 */
interface IEventRepository
{
    /**
     * Method to get all events assigned to user
     *
     * @return array
     */
    public function getAllEvents();

    /**
     * Method to get upcoming events
     *
     * @param $event_ids
     * @param int $start records to skip
     * @param int $limit no of records to get
     *
     * @return array
     */
    public function getUpcomingEvents($event_ids, $start, $limit);

    /**
     * Method to get all events  created by username
     *
     * @param array $usernames
     * @return Object
     */
    public function getEventsByUsername($usernames = []);

    /**
     * Method to get user hosting events
     *
     * @param  int $user_id
     * @return  array
     */
    public function getHostingEvents($user_id);

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
     * Method to get event by event_id
     *
     * @param int $event_id
     * @return App\Model\Event
     */
    public function find($event_id);

    /**
     * Method to query the event list using filters
     *
     * @param $filter
     * @return collection
     */
    public function getSessions($filter);

    /**
     * Method to list events with filters
     *
     * @param $filter array
     * @return mixed
     */
    public function getEventLists($filter);

    /**
     * Method to show the storage size
     *
     * @param $filter array
     * @return mixed
     */
    public function getStorageReport($filter);

    /**
     * Method to get events with recordings
     * which are not downloaded
     *
     * @return mixed
     */
    public function getEventsWithRecordings();

    /**
     * Method to get downloaded recordings those are not uploaded
     *
     *
     * @return mixed
     */
    public function getDownloadedEvents();

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
     * @inheritdoc
     */
    public function countActiveEvents($event_ids);
}
