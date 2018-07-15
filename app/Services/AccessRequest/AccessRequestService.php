<?php

namespace App\Services\AccessRequest;

use App\Model\AccessRequest\IAccessRequestRepository;

/**
 * class AccessRequestService
 * @package App\Services\AccessRequest
 */
class AccessRequestService implements IAccessRequestService
{
    private $accessrequest_repo;

    public function __construct(IAccessRequestRepository $accessrequest_repo)
    {
        $this->accessrequest_repo = $accessrequest_repo;
    }

    /**
     * @inheritdoc
     */
    public function getAccessRequestCount(array $program_ids, array $date)
    {
        return $this->accessrequest_repo->getAccessRequestCount($program_ids, $date);
    }

    /**
     * @inheritdoc
     */
    public function getAccessRequests(array $program_ids, array $date, $start, $limit)
    {
        return $this->accessrequest_repo->getAccessRequests($program_ids, $date, $start, $limit);
    }
}
