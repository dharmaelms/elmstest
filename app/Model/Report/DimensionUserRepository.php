<?php

namespace App\Model\Report;

use App\Model\DimensionUser;

class DimensionUserRepository implements IDimensionUserRepository
{
    /**
     * {@inheritdoc}
     */
    public function getSpecificUserDetail($user_id = 0)
    {
        return DimensionUser::where('user_id', '=', (int)$user_id)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserNameListByids($user_ids, $start, $limit)
    {
        return DimensionUser::whereIn('user_id', $user_ids)->skip((int)$start)->take((int)$limit)->get(['user_name', 'user_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelsUsers($channel_ids)
    {
        if (!empty($channel_ids)) {
            return DimensionUser::whereIn('channel_ids', $channel_ids)->get();
        } else {
            return DimensionUser::get();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelsUserIds($channel_ids)
    {
        if (!empty($channel_ids)) {
            return DimensionUser::whereIn('channel_ids', $channel_ids)->pluck('user_id');
        } else {
            return DimensionUser::pluck('user_id');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUsersByChannelids($channel_ids, $order_by, $search, $start, $limit)
    {
        $query = DimensionUser::where('user_id', 'exists', true);
        if (!empty($channel_ids)) {
            $query->whereIn('channel_ids', $channel_ids);
        }
        if ($search != '') {
            $query->where('user_name', 'like', '%'.$search.'%');
        }
        if (!empty($order_by)) {
            $query->GetOrderBy($order_by);
        }
        return $query->skip((int)$start)->take((int)$limit)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalUsersCount()
    {
        return DimensionUser::count();
    }
}
