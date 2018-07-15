<?php

namespace App\Services\AccessRequest;

/**
 * Interface IAccessRequestService
 * @package App\Services\AccessRequest
 */
interface IAccessRequestService
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
