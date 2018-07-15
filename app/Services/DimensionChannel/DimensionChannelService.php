<?php

namespace App\Services\DimensionChannel;

use App\Model\DimensionChannel\Repository\IDimensionChannelRepository;

/**
 * class IDimensionChannelService
 * @package App\Services\DimensionChannel
 */
class DimensionChannelService implements IDimensionChannelService
{
    private $dim_channel_repo;

    public function __construct(IDimensionChannelRepository $dim_channel_repo)
    {
        $this->dim_channel_repo = $dim_channel_repo;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelsDetails($channel_ids = [])
    {
        if (!empty($channel_ids)) {
            return $this->dim_channel_repo->getChannelsDetails($channel_ids);
        } else {
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isExist($channel_id = 0)
    {
        return $this->dim_channel_repo->isExist($channel_id);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getChannelSlugsNameAndIds()
    {
        return $this->dim_channel_repo->getChannelSlugsNameAndIds();
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelsFullName($search_key, $channel_ids, $limit, $start = 0, $sort_by = 'channel_name')
    {
        return $this->dim_channel_repo->getChannelsFullName($search_key, $channel_ids, $limit, $start, $sort_by);
    }
}
