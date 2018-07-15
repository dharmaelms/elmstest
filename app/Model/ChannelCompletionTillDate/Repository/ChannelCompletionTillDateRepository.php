<?php

namespace App\Model\ChannelCompletionTillDate\Repository;

use App\Model\ChannelCompletionTillDate;

/**
 * class ChannelCompletionTillDateRepository
 * @package App\Model\ChannelCompletionTillDate\Repository
 */
class ChannelCompletionTillDateRepository implements IChannelCompletionTillDateRepository
{
    /**
     * {@inheritdoc}
     */
    public function getSpecificChannelUserCompletion($channel_id, $user_ids)
    {
        return ChannelCompletionTillDate::where('channel_id', '=', (int)$channel_id)
            ->whereIn('user_id', $user_ids)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserChannelCompletionDetails($channel_id, $user_id)
    {
        return ChannelCompletionTillDate::where('user_id', '=', (int)$user_id)
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
        return ChannelCompletionTillDate::raw(function ($c) use ($channel_ids, $match, $start, $field_name) {
            return $c->aggregate([
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
                    '$sort' => ['_id' => -1],
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
        $result = ChannelCompletionTillDate::raw(function ($c) use ($channel_id, $start, $limit, $array) {
            return $c->aggregate([
                [
                    '$match' => [
                        'channel_id' => $channel_id
                    ]
                ],
                $array
            ]);
        });
        $result = array_get($result, 'result.0', []);
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
}
