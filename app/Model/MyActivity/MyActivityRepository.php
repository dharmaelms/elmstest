<?php

namespace App\Model\MyActivity;

use App\Model\MyActivity;

/**
 * Class MyActivityRepository
 *
 * @package App\Model\MyActivity
 */
class MyActivityRepository implements IMyActivityRepository
{
    /**
     * @inheritdoc
     */
    public function getActiveUsersCount(array $date)
    {
        return MyActivity::whereBetween('date', $date)
                ->distinct('user_id')
                ->get()
                ->count();
    }

    /**
     * @inheritdoc
     */
    public function getFeedActivity($start, $end)
    {
        return MyActivity::where('module', '=', 'contentfeed')->where('action', '=', 'view')->getByTime($start, $end);
    }

    /**
     * @inheritdoc
     */
    public function getActiveUsers(array $date, $limit)
    {
        return MyActivity::whereBetween('date', $date)
                ->distinct('user_id')
                ->skip(0)
                ->orderBy('created_at', 'desc')
                ->take((int)$limit)->get()->toArray();
    }

    /**
     * @inheritdoc
     */
    public function getActiveFeedCount(array $program_ids, array $date)
    {
        $active_feed_ids = MyActivity::whereBetween('date', $date)
                ->distinct('feed_id')
                ->get(['feed_id'])
                ->toArray();
        $active_feed_ids = array_flatten($active_feed_ids);

        if (!empty($program_ids)) {
            $program_ids = array_intersect($program_ids, $active_feed_ids);
        } else {
            $program_ids = $active_feed_ids;
        }
        return count($program_ids);
    }

    /**
     * @inheritdoc
     */
    public function getActiveFeeds(array $date)
    {
        return MyActivity::whereBetween('date', $date)
                ->distinct('feed_id')
                ->skip(0)
                ->orderBy('created_at', 'desc')
                ->get()->toArray();
    }
}
