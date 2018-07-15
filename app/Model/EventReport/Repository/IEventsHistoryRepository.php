<?php
namespace App\Model\EventReport\Repository;

interface IEventsHistoryRepository
{
    /**
     * Method to get sessions details by session key
     *
     * @param array $sessions
     * @return mixed
     */
    public function sessionDetails($sessions, $start = false, $limit = false, $sortby = []);
}
