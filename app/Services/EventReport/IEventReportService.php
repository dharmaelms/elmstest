<?php
    namespace App\Services\EventReport;

interface IEventReportService
{
    /**
         * Method to return details of event
         *
         * @param $query
         * @param $start
         * @param $limit
         * @return mixed
         */
    public function eventReport($query, $start = false, $limit = false, $sortby = []);

    /**
         * Method to return attendees details of a session
         *
         * @param $confID
         * @param $search
         * @return mixed
         */
    public function attendeeReport($confID, $search, $sortby);
}
