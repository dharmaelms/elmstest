<?php

namespace App\Services\Report;

use App\Model\Report\IDimensionUserRepository;

class DimensionUserService implements IDimensionUserService
{
    private $dim_user_repo;

    public function __construct(IDimensionUserRepository $dim_user_repo)
    {
        $this->dim_user_repo = $dim_user_repo;
    }
    /**
     * @inheritdoc
     */
    public function getSpecificUserDetail($user_id = 0)
    {
        return $this->dim_user_repo->getSpecificUserDetail($user_id);
    }

    /**
     * @inheritdoc
     */
    public function getUserNameListByids($user_ids, $start, $limit)
    {
        return $this->dim_user_repo->getUserNameListByids($user_ids, $start, $limit);
    }

    /**
     * @inheritdoc
     */
    public function getChannelsUsers($channel_ids)
    {
        $user_details = $this->dim_user_repo->getChannelsUsers($channel_ids);
        return $user_details->lists('user_id');
    }

    /**
     * @inheritdoc
     */
    public function getChannelsUserIds($channel_ids)
    {
        return $this->dim_user_repo->getChannelsUserIds($channel_ids);
    }
}
