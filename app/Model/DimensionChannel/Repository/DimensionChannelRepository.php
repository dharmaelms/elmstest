<?php

namespace App\Model\DimensionChannel\Repository;

use App\Model\DimensionChannel;

/**
 * class DimensionChannelRepository
 * @package App\Model\DimensionChannel\Repository
 */
class DimensionChannelRepository implements IDimensionChannelRepository
{
    /**
     * {@inheritdoc}
     */
    public function getChannelsDetails($channel_ids)
    {
        return DimensionChannel::whereIn('channel_id', $channel_ids)
            ->orderBy('channel_name', 'asc')
            ->get(['post_count', 'post_ids', 'channel_id', 'items', 'channel_slug', 'channel_name', 'short_name'])
            ->toArray();
    }

    /**
     * @inheritdoc
     */
    public function isExist($channel_id = 0)
    {
        return DimensionChannel::where('channel_id', '=', (int)$channel_id)->first();
    }

    /**
     * @inheritdoc
     */
    public function getChannelSlugsNameAndIds()
    {
        return DimensionChannel::get(['channel_slug', 'channel_id', 'channel_name', 'short_name'])
                    ->toArray();
    }

    /**
     * @inheritdoc
     */
    public function getChannelsFullName($search_key, $channel_ids, $limit, $start = 0, $sort_by = 'channel_name')
    {
        $query = DimensionChannel::where('channel_id', '>', 0);
        if (!empty($channel_ids)) {
            $query->whereIn('channel_id', $channel_ids);
        }
        if (!empty($search_key)) {
            $query->where('channel_name', 'like', "%" . $search_key . "%");
        }
        if ($sort_by == 'channel_name') {
            $query->orderBy('channel_name', 'asc')
                ->orderBy('short_name', 'asc');
        } elseif ($sort_by == 'channel_id') {
            $query->orderBy('channel_id', 'desc');
        }
        
        if ($limit > 0) {
            $query->skip((int)$start)
                ->take((int)$limit);
        }
        return $query->get();
    }
}
