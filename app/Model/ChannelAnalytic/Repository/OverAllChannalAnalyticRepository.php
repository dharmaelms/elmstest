<?php

namespace App\Model\ChannelAnalytic\Repository;

use App\Model\OverAllChannelAnalytic;

/**
 * class OverAllChannalAnalyticRepository.php
 * @package App\Model\ChannelAnalytic\Repository
 */
class OverAllChannalAnalyticRepository implements IOverAllChannalAnalyticRepository
{
    /**
     * {@inheritdoc}
     */
    public function getSpecificChannelUserCompletion($channel_id, $user_ids)
    {
        return OverAllChannelAnalytic::where('channel_id', '=', (int)$channel_id)
            ->whereIn('user_id', $user_ids)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserChannelCompletionDetails($channel_id, $user_id)
    {
        return OverAllChannelAnalytic::where('user_id', '=', (int)$user_id)
            ->whereIn('channel_id', $channel_id)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function findChannelPerformanceOrComp(array $channel_ids, $start, $is_completion)
    {
        if (!empty($channel_ids)) {
            $match = [
                'channel_id' => ['$in' => $channel_ids]
            ];
        } else {
            $match = [
                'channel_id' => ['$gte' => 1]
            ];
        }
        $field_name = 'score';
        if ($is_completion) {
            $field_name = 'completion';
        }
        return OverAllChannelAnalytic::raw(function ($c) use ($channel_ids, $match, $start, $field_name) {
            return $c->aggregate([
                [
                    '$sort' => ['_id' => -1],
                ],
                [
                    '$match' => $match
                ],
                [
                    '$group' => [
                        '_id' => '$channel_id',
                        'channel_avg' => ['$avg' => '$'.$field_name],
                        'count' => ['$sum' => 1]
                    ]
                ],
                [
                    '$skip' => $start,
                ]
            ]);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function findIndChannelCompletion($channel_id, $post_ids, $start, $limit)
    {
        $avg_query['_id'] = '$channel_id';
        foreach ($post_ids as $post_id) {
            $avg_query['post_'.$post_id] =  ['$avg' => '$post_completion.p_'.$post_id];
        }
        $array = [
            '$group' => $avg_query
        ];
        $result = OverAllChannelAnalytic::raw(function ($c) use ($channel_id, $start, $limit, $array) {
            return $c->aggregate([
                [
                    '$match' => [
                        'channel_id' => $channel_id
                    ]
                ],
                $array
            ]);
        });
        $result = $result->first()->toArray();
        if (!empty($result)) {
            unset($result['_id']);
            krsort($result);
            if ($limit > 0) {
                $result = array_slice($result, $start, $limit);
            }
        } else {
            $result = [];
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function findUsersPerformanceAndCompletion(array $user_ids, array $channel_ids, array $order_by, $start, $limit)
    {
        $match = [];
        if (!empty($channel_ids) && !empty($user_ids)) {
            $match = [
                'channel_id' => ['$in' => $channel_ids],
                'user_id' => ['$in' => $user_ids]
            ];
        } elseif (!empty($channel_ids)) {
            $match = [
                'channel_id' => ['$in' => $channel_ids]
            ];
        } elseif (!empty($user_ids)) {
            $match = [
                'user_id' => ['$in' => $user_ids]
            ];
        }
        if (empty($match)) {
            $match = [
                'user_id' => ['$exists' => true]
            ];
        }
        $order_field = key($order_by);
        $order_by_value = $order_by[$order_field];
        if ($order_field != 'user_name') {
            $sort = [
                '$sort' => [$order_field => ($order_by_value == 'asc' || $order_by_value == 1)  ? 1 : -1]
            ];
        } else {
            $sort = [
                '$sort' => ['performance' => -1],
            ];
        }
        return OverAllChannelAnalytic::raw(function ($c) use ($match, $sort, $start, $limit) {
            return $c->aggregate([
                [
                    '$match' => $match
                ],
                [
                    '$group' => [
                        '_id' => '$user_id',
                        'performance' => ['$avg' => '$score'],
                        'completion' => ['$avg' => '$completion'],
                        'channel_ids' => ['$addToSet' => '$channel_id'],
                        'count' => ['$sum' => 1]
                    ]
                ],
                $sort,
                [
                    '$skip' => $start
                ],
                [
                    '$limit' => $limit
                ]
            ]);
        });
    }

    public function usersPerformanceCount(array $user_ids, array $channel_ids)
    {
        return OverAllChannelAnalytic::where(function ($q) use ($user_ids, $channel_ids) {
            $q->whereIn('channel_id', $channel_ids)
            ->whereIn('user_id', $user_ids);
        })
        ->groupBy('user_id')
        ->pluck('user_id');
    }

    /**
     * {@inheritdoc}
     */
    public function findUserChannelPerformanceOrComp(array $channel_ids, $user_id, $is_completion)
    {
        $field_name = 'score';
        if ($is_completion) {
            $field_name = 'completion';
        }
        return OverAllChannelAnalytic::raw(function ($c) use ($channel_ids, $user_id, $field_name) {
            return $c->aggregate([
                [
                    '$match' => [
                        'channel_id' => ['$in' => $channel_ids],
                        'user_id' => $user_id
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$channel_id',
                        'channel_avg' => ['$avg' => '$'.$field_name],
                        'count' => ['$sum' => 1]
                    ]
                ],
                [
                    '$sort' => ['_id' => -1],
                ]
            ]);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function findCompletedChannels($date_timestamp, $is_completed, $get_count, $start, $limit)
    {
        if ($is_completed) {
            $query = OverAllChannelAnalytic::where('completion', '>=', 100);
            if (is_array($date_timestamp)) {
                $query->where(function ($q) use ($date_timestamp) {
                    $q->whereBetween('completed_at.0', [$date_timestamp['start_date'], $date_timestamp['end_date']]);
                });
            }
        } else {
            $query = OverAllChannelAnalytic::where('completion', '<', 100);
            if (is_array($date_timestamp)) {
                $query->where(function ($q) use ($date_timestamp) {
                    $q->whereBetween('created_at', [$date_timestamp['start_date'], $date_timestamp['end_date']])
                    ->orWhereBetween('updated_at', [$date_timestamp['start_date'], $date_timestamp['end_date']]);
                });
            }
        }
        if (!is_null($start) && !is_null($limit)) {
            $query->skip((int)$start)
                ->take((int)$limit);
        }
        if ($get_count) {
            return $query->count();
        } else {
            return $query->get(['user_id', 'channel_id', 'created_at', 'completed_at']);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function isExists($channelID, $userID)
    {
        return OverAllChannelAnalytic::isExists($channelID, $userID);
    }

    /**
     * {@inheritdoc}
     */
    public function insertData($data)
    {
        return OverAllChannelAnalytic::insertData($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateData($data, $channel_id, $user_id)
    {
        return OverAllChannelAnalytic::updateData($data, $channel_id, $user_id);
    }
}
