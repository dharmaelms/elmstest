<?php

namespace App\Model;

use Moloquent;

class ChannelCompletionTillDate extends Moloquent
{
    protected $collection = 'channel_completion_till_date';

    public $timestamps = false;

    protected $casts = [
            'user_id' => 'int'
        ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['create_date', 'update_date'];

    public static function isExists($userID = 0, $channelID = 0)
    {
        if ($channelID > 0 && $userID > 0) {
            return ChannelCompletionTillDate::where('channel_id', '=', (int)$channelID)
                ->where('user_id', '=', (int)$userID)
                ->first();
        } else {
            return [];
        }
    }

    public static function getUserChannelsCompletion($channel_ids = [], $user_id = 0, $order_by = -1)
    {
        $resultset = ChannelCompletionTillDate::raw(function ($c) use ($channel_ids, $user_id, $order_by) {
            return $c->aggregate([
                [
                    '$match' => [
                        'user_id' => $user_id,
                        'channel_id' => ['$in' => array_values($channel_ids)]
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$channel_id'
                        ],
                        'completion' => ['$avg' => '$completion'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['completion' => $order_by],
                ]
            ]);
        });

        return $resultset;
    }

    public static function getChannelsAvgCompletion($channel_ids = [], $order_by = -1)
    {
        $resultset = ChannelCompletionTillDate::raw(function ($c) use ($channel_ids, $order_by) {
            return $c->aggregate([
                [
                    '$match' => [
                        'channel_id' => ['$in' => array_values($channel_ids)]
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$channel_id'
                        ],
                        'completion' => ['$sum' => '$completion'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['completion' => $order_by],
                ]
            ]);
        });

        return $resultset;
    }

    public static function getSpecificUserChannelsCompletion($channel_id = 0, $user_id = 0)
    {
        return self::where('channel_id', '=', (int)$channel_id)
            ->where('user_id', '=', $user_id)
            ->get(['post_completion']);
    }

    public static function getSpecificChannelsAvgCompletion($channel_id = 0, $post_key = '', $user_channel = [])
    {
        return self::where('channel_id', '=', (int)$channel_id)
            ->whereIn('user_id', $user_channel)
            ->sum('post_completion.' . $post_key);
    }
}
