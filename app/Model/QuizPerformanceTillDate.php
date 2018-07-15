<?php

namespace App\Model;

use Moloquent;

class QuizPerformanceTillDate extends Moloquent
{
    protected $collection = 'quiz_performance_till_date';
    public $timestamps = false;
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'create_date'];

    public static function getInsertData($data = [])
    {
        if (!empty($data)) {
            $data['id'] = self::getNextSequence();
            $data['create_date'] = time();

            return self::insert($data);
        } else {
            return false;
        }
    }

    public static function isExist($user_id = 0, $quiz_id = 0)
    {
        return self::where('user_id', '=', (int)$user_id)
            ->where('quiz_id', '=', (int)$quiz_id)
            ->first();
    }

    public static function getQuizPerfWOQtype()
    {
        return self::where('is_practice', 'exists', false)->get();
    }

    public static function getUserChannelPracticePerformance($user_id, $channel_ids = [], $quiz_type = true, $quiz_criteria = 'score', $order_by = -1)
    {
        $resultset = QuizPerformanceTillDate::raw(function ($collection) use ($user_id, $channel_ids, $quiz_type, $quiz_criteria, $order_by) {
            return $collection->aggregate(
                [
                    '$match' => [
                        'is_practice' => true,
                        'channel_id' => ['$in' => array_values($channel_ids)],
                        'user_id' => $user_id,
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$channel_id',
                        ],
                        'channel_avg' => ['$avg' => '$' . $quiz_criteria],
                        'speed' => ['$avg' => '$speed_s'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['channel_avg' => -1],
                ]
            );
        });
        return $resultset;
    }

    public static function getChannelsPracticeQuizAvg($channel_ids = [], $quiz_type = true, $quiz_criteria = 'score', $order_by = -1)
    {
        $resultset = QuizPerformanceTillDate::raw(function ($c) use ($channel_ids, $quiz_type, $quiz_criteria, $order_by) {
            return $c->aggregate(
                [
                    '$match' => [
                        'is_practice' => true,
                        'channel_id' => ['$in' => array_values($channel_ids)],
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$channel_id',
                            'quiz_id' => '$quiz_id',
                        ],
                        'quiz_avg' => ['$avg' => '$' . $quiz_criteria],
                        'speed' => ['$avg' => '$speed_s'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$_id.channel_id',
                        ],
                        'channel_avg' => ['$avg' => '$quiz_avg'],
                        'speed' => ['$avg' => '$speed'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['channel_avg' => $order_by],
                ]
            );
        });

        return $resultset;
    }

    public static function getUserChannelMockPerformance($user_id, $channel_ids = [], $quiz_type = false, $quiz_criteria = 'score', $order_by = -1)
    {
        $resultset = QuizPerformanceTillDate::raw(function ($collection) use ($user_id, $channel_ids, $quiz_type, $quiz_criteria, $order_by) {
            return $collection->aggregate(
                [
                    '$match' => [
                        'is_practice' => false,
                        'channel_id' => ['$in' => array_values($channel_ids)],
                        'user_id' => $user_id,

                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$channel_id',
                        ],
                        'channel_avg' => ['$avg' => '$' . $quiz_criteria],
                        'speed' => ['$avg' => '$speed_s'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['channel_avg' => -1],
                ]
            );
        });
        return $resultset;
    }

    public static function getChannelsMockQuizAvg($channel_ids = [], $quiz_type = false, $quiz_criteria = 'score')
    {
        $resultset = QuizPerformanceTillDate::raw(function ($c) use ($channel_ids, $quiz_type, $quiz_criteria) {
            return $c->aggregate(
                [
                    '$match' => [
                        'is_practice' => false,
                        'channel_id' => ['$in' => array_values($channel_ids)]
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$channel_id',
                            'quiz_id' => '$quiz_id',
                        ],
                        'quiz_avg' => ['$avg' => '$' . $quiz_criteria],
                        'speed' => ['$avg' => '$speed_s'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$_id.channel_id',
                        ],
                        'channel_avg' => ['$avg' => '$quiz_avg'],
                        'speed' => ['$avg' => '$speed'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['channel_avg' => 1],
                ]
            );
        });

        return $resultset;
    }

    public static function getSpecificChannelUserMockPerformance($user_id, $channel_id, $quiz_type = false, $quiz_criteria = 'score', $order_by = -1)
    {
        $resultset = QuizPerformanceTillDate::raw(function ($c) use ($user_id, $channel_id, $quiz_type, $quiz_criteria, $order_by) {
            return $c->aggregate(
                [
                    '$match' => [
                        'is_practice' => false,
                        'user_id' => $user_id,
                        'channel_id' => $channel_id,
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'quiz_id' => '$quiz_id',
                        ],
                        'quiz_name' => ['$addToSet' => '$quiz_name'],
                        'quiz_avg' => ['$avg' => '$' . $quiz_criteria],
                        'speed' => ['$avg' => '$speed_s'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['quiz_avg' => $order_by],
                ]
            );
        });

        return $resultset;
    }

    public static function getSpecificChannelMockAvgPerformance($channel_id, $quiz_type = false, $quiz_criteria = 'score', $order_by = -1)
    {
        $resultset = QuizPerformanceTillDate::raw(function ($c) use ($channel_id, $quiz_type, $quiz_criteria, $order_by) {

            return $c->aggregate(
                [
                    '$match' => [
                        'is_practice' => false,
                        'channel_id' => $channel_id,
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'quiz_id' => '$quiz_id',
                        ],
                        'quiz_name' => ['$addToSet' => '$quiz_name'],
                        'quiz_avg' => ['$avg' => '$' . $quiz_criteria],
                        'speed' => ['$avg' => '$speed_s'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['quiz_avg' => $order_by],
                ]
            );
        });

        return $resultset;
    }

    public static function getSpecificChannelUserPracticePerformance($user_id, $channel_id, $quiz_type = true, $quiz_criteria = 'score', $order_by = -1)
    {
        $resultset = QuizPerformanceTillDate::raw(function ($c) use ($user_id, $channel_id, $quiz_type, $quiz_criteria, $order_by) {

            return $c->aggregate(
                [
                    '$match' => [
                        'is_practice' => true,
                        'user_id' => $user_id,
                        'channel_id' => $channel_id,
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'quiz_id' => '$quiz_id',
                        ],
                        'quiz_name' => ['$addToSet' => '$quiz_name'],
                        'quiz_avg' => ['$avg' => '$' . $quiz_criteria],
                        'speed' => ['$avg' => '$speed_s'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['quiz_avg' => $order_by],
                ]
            );
        });

        return $resultset;
    }

    public static function getSpecificChannelPracticeAvgPerformance($channel_id, $quiz_type = true, $quiz_criteria = 'score', $order_by = -1)
    {
        $resultset = QuizPerformanceTillDate::raw(function ($c) use ($channel_id, $quiz_type, $quiz_criteria, $order_by) {

            return $c->aggregate(
                [
                    '$match' => [
                        'channel_id' => $channel_id,
                        'is_practice' => true,
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'quiz_id' => '$quiz_id',
                        ],
                        'quiz_name' => ['$addToSet' => '$quiz_name'],
                        'quiz_avg' => ['$avg' => '$' . $quiz_criteria],
                        'speed' => ['$avg' => '$speed_s'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['quiz_avg' => $order_by],
                ]
            );
        });

        return $resultset;
    }

    public static function getAgregationValues($quizIds = [], $userId = 0)
    {
        if (!empty($quizIds) && $userId > 0) {
            $resultset = QuizPerformanceTillDate::raw(function ($c) use ($quizIds, $userId) {
                return $c->aggregate(
                    [
                        '$match' => [
                            'quiz_id' => ['$in' => array_values($quizIds)],
                            'user_id' => $userId,
                            'type' => ['$ne' => "QUESTION_GENERATOR"],
                        ],
                    ],
                    [
                        '$group' => [
                            '_id' => [
                                'user_id' => '$user_id',
                            ],
                            'score' => ['$avg' => '$score'],
                            'accuracy' => ['$avg' => '$accuracy'],
                            'speed_h' => ['$avg' => '$speed_h'],
                            'speed_m' => ['$avg' => '$speed_m'],
                            'speed_s' => ['$avg' => '$speed_s'],
                            'count' => ['$sum' => 1],
                        ],
                    ]
                );
            });
            return $resultset;
        }
    }
}
