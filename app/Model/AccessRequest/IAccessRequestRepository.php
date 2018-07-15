<?php

namespace App\Model\AccessRequest;

/**
 * Interface IAccessRequestRepository
 * @package App\Model\AccessRequest
 */
interface IAccessRequestRepository
{
    /**
     * getAccessRequestCount
     * @param  array  $program_ids
     * @param  array  $date
     * @return integer
     */
    public function getAccessRequestCount(array $program_ids, array $date);

    /**
     * getAccessRequestCount
     * @param  array  $program_ids
     * @param  array  $date
     * @param  integer $start
     * @param  integer  $limit
     * @return integer
     */
    public function getAccessRequests(array $program_ids, array $date, $start, $limit);
}
