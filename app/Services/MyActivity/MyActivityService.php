<?php

namespace App\Services\MyActivity;

use App\Model\MyActivity\IMyActivityRepository;

/**
 * Class MyActivityService
 *
 * @package App\Services\MyActivity
 */
class MyActivityService implements IMyActivityService
{
    /**
     * $my_activity_repo
     * @var App\Model\MyActivity\IMyActivityRepository
     */
    private $my_activity_repo;

    public function __construct(IMyActivityRepository $my_activity_repo)
    {
        $this->my_activity_repo = $my_activity_repo;
    }
    
    /**
     * @inheritdoc
     */
    public function getActiveUsersCount(array $date)
    {
        return $this->my_activity_repo->getActiveUsersCount($date);
    }

    /**
     * @inheritdoc
     */
    public function getFeedActivity($start, $end)
    {
        return $this->my_activity_repo->getFeedActivity($start, $end);
    }

    /**
     * @inheritdoc
     */
    public function getActiveUsers(array $date, $limit)
    {
        return $this->my_activity_repo->getActiveUsers($date, $limit);
    }

    public function getActiveFeedCount(array $program_ids, array $date)
    {
        return $this->my_activity_repo->getActiveFeedCount($program_ids, $date);
    }

    public function getActiveFeeds(array $date)
    {
        return $this->my_activity_repo->getActiveFeeds($date);
    }
}
