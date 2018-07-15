<?php
    namespace App\Model\EventReport\Repository;

    interface IEventsAttendeeHistoryRepository
    {
        /**
         * Method to return attendee history of the session
         *
         * @param $confID
         * @param $search
         * @return mixed
         */
        public function attendeeDetails($confID, $filter = [], $sortby);
    }
