<?php

namespace App\Model\MyActivity;

/**
 * Interface IMyActivityRepository
 * @package App\Model\MyActivity
 */
interface IMyActivityRepository
{
    /**
     * getActiveUsersCount
     * @param  array  $date
     * @return integer
     */
    public function getActiveUsersCount(array $date);

    /**
     * getFeedActivity
     * @param  integer $start
     * @param  integer $end
     * @return collection
     */
    public function getFeedActivity($start, $end);

    /**
     * getActiveUsersCount
     * @param  array  $date
     * @param  integer  $limit
     * @return integer
     */
    public function getActiveUsers(array $date, $limit);

    /**
     * getActiveFeedCount
     * @param  array  $date
     * @return integer
     */
    public function getActiveFeedCount(array $program_ids, array $date);

    /**
     * getActiveUsersCount
     * @param  array  $date
     * @return integer
     */
    public function getActiveFeeds(array $date);
}
