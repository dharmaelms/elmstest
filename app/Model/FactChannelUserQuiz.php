<?php

namespace App\Model;

use Carbon;
use Moloquent;

class FactChannelUserQuiz extends Moloquent
{
    protected $collection = 'fact_channels_user_quiz';
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'create_date', 'update_date'];

    protected $casts = [
        'channel_id' => 'int'
    ];
    
    public static function getUserAgregationWithTimeline($user_id = 0, $date_range = 15)
    {
        $fifteen_day = $date_range * 24 * 60 * 60;
        $buffer_time = 6 * 60 * 60;
        $time_line = time() - ($fifteen_day + $buffer_time);

        if ($user_id > 0) {
            return self::where('user_id', '=', $user_id)
                ->whereBetween('create_date', [$time_line, time()])
                ->avg('quiz_avg_percent');
        } else {
            return 0;
        }
    }

    public static function getUserAgregationWithdaterange($user_id = 0, $start_date, $end_date)
    {
        if ($user_id > 0) {
            return self::where('user_id', '=', $user_id)
                ->where(function ($query) use ($start_date, $end_date) {
                    $query->orwhereBetween('created_at', [$start_date, $end_date])
                        ->orwhereBetween('updated_at', [$start_date, $end_date]);
                })
                ->avg('quiz_avg_percent');
        } else {
            return 0;
        }
    }

    public static function getQuizAndChannelAgregationWithTimeline($channel_id = 0, $quiz_id = 0, $date_range = 15)
    {
        $fifteen_day = $date_range * 24 * 60 * 60;
        $buffer_time = 6 * 60 * 60;
        $time_line = time() - ($fifteen_day + $buffer_time);

        if ($channel_id > 0 && $quiz_id > 0) {
            return self::where('channel_id', '=', $channel_id)
                ->where('quiz_id', '=', (int)$quiz_id)
                ->whereBetween('create_date', [$time_line, time()])
                ->avg('quiz_avg_percent');
        } else {
            return 0;
        }
    }


    public static function getUserAgregation($user_id = 0)
    {
        if ($user_id > 0) {
            return self::where('user_id', '=', $user_id)
                ->avg('quiz_avg_percent');
            // ->get()->toArray();
        } else {
            return 0;
        }
    }

    public static function getChannelAreaOfImprovement($user_id = 0, $channel_id = 0, $margin = 50)
    {
        if ($user_id > 0 && $channel_id > 0) {
            return self::where('user_id', '=', $user_id)
                ->where('channel_id', '=', $channel_id)
                ->where('quiz_avg_percent', '<=', $margin)
                ->get()
                ->toarray();
        } else {
            return [];
        }
    }

    public static function getAllAreaOfImprovement($user_id = 0, $channel_id = 0, $date_range = 15)
    {
        if ($user_id > 0 && $channel_id > 0) {
            return self::where('user_id', '=', $user_id)
                ->where('channel_id', '=', $channel_id)
                // ->whereBetween('create_date', array($time_line, time()))
                ->avg('quiz_avg_percent');
        } else {
            return 0;
        }
    }

    public static function getUserAgregationwithTL($user_id = 0, $date_range = 15)
    {
        $time_line = Carbon::today()->subDay((int)$date_range)->timestamp;
        if ($user_id > 0) {
            return self::where('user_id', '=', $user_id)
                ->where('created_at', '>=', $time_line)
                ->avg('quiz_avg_percent');
        } else {
            return 0;
        }
    }

    public static function getULPerformanceWithTLRAW($start_date = 0, $end_date = 0)
    {

        $resultset = FactChannelUserQuiz::raw(function ($c) use ($start_date, $end_date) {
            return $c->aggregate(
                [
                    '$match' => [
                        '$or' => [
                            [
                                'updated_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ],
                            [
                                'created_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ]
                        ]
                    ],
                ],
                [
                    '$group' => [
                        '_id' => '$user_id',
                        'quiz_avg' => ['$avg' => '$quiz_avg_percent'],
                        'count' => ['$sum' => 1],

                    ]
                ]
            );
        });

        return $resultset;
    }

    public static function getChannelQuizReportRaw($start_date = 0, $end_date = 0, $start = 0, $limit = 7, $from = 0, $to = 100)
    {
        $resultset = FactChannelUserQuiz::raw(function ($collection) use ($start_date, $end_date, $start, $limit, $from, $to) {
            return $collection->aggregate(
                [
                    '$match' => [
                        '$or' => [
                            [
                                'updated_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ],
                            [
                                'created_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ]
                        ]
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$channel_id',
                            'quiz_id' => '$quiz_id'
                        ],
                        'quiz_avg' => ['$avg' => '$quiz_avg_percent'],
                        'count' => ['$sum' => 1],

                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$_id.channel_id',
                        'channel_quiz_avg' => ['$avg' => '$quiz_avg'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$match' => [
                        'channel_quiz_avg' => ['$gte' => $from, '$lte' => $to]
                    ],
                ],
                [
                    '$group' => [
                        '_id' => '$_id',
                        'channel_quiz_avg' => ['$avg' => '$channel_quiz_avg'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['_id' => 1],
                ],
                [
                    '$skip' => $start,
                ],
                [
                    '$limit' => $limit,
                ]
            );
        });

        return $resultset;
    }

    public static function getCSVChannelQuizReportRaw($start_date, $end_date, $from = 0, $to = 100)
    {
        $resultset = FactChannelUserQuiz::raw(function ($collection) use ($start_date, $end_date, $from, $to) {
            return $collection->aggregate(
                [
                    '$match' => [
                        '$or' => [
                            [
                                'updated_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ],
                            [
                                'created_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ]
                        ]
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$channel_id',
                            'quiz_id' => '$quiz_id'
                        ],
                        'quiz_avg' => ['$avg' => '$quiz_avg_percent'],
                        'count' => ['$sum' => 1],

                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$_id.channel_id',
                        'channel_quiz_avg' => ['$avg' => '$quiz_avg'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$match' => [
                        'channel_quiz_avg' => ['$gte' => $from, '$lte' => $to]
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$_id',
                        ],
                        'channel_quiz_res' => ['$addToSet' => '$channel_quiz_avg'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['_id' => 1]
                ]
            );
        });

        return $resultset;
    }

    public static function getChannelQuizAgregationWithTimelineRaw($start, $limit, $channel_id, $start_date, $end_date, $from = 0, $to = 100)
    {
        $resultset = FactChannelUserQuiz::raw(function ($collection) use ($start, $limit, $channel_id, $start_date, $end_date, $from, $to) {
            return $collection->aggregate(
                [
                    '$match' => [
                        '$or' => [
                            [
                                'updated_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ],
                            [
                                'created_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ]
                        ],
                        'channel_id' => $channel_id,
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$channel_id',
                            'quiz_id' => '$quiz_id',
                        ],
                        'quiz_name' => ['$addToSet' => '$quiz_name'],
                        'quiz_avg' => ['$avg' => '$quiz_avg_percent'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$match' => [
                        'quiz_avg' => ['$gte' => $from, '$lte' => $to]
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$_id.channel_id',
                            'quiz_id' => '$_id.quiz_id',
                        ],
                        'quiz_name' => ['$addToSet' => '$quiz_name'],
                        'quiz_avg' => ['$addToSet' => '$quiz_avg'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['_id.quiz_id' => 1],
                ],
                [
                    '$skip' => $start,
                ],
                [
                    '$limit' => $limit,
                ]
            );
        });

        return $resultset;
    }

    public static function getChannelQuizAgregationAvgRaw($channel_id, $start_date, $end_date)
    {
        $resultset = FactChannelUserQuiz::raw(function ($collection) use ($channel_id, $start_date, $end_date) {
            return $collection->aggregate(
                [
                    '$match' => [
                        '$or' => [
                            [
                                'updated_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ],
                            [
                                'created_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ]
                        ],
                        'channel_id' => $channel_id,
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$channel_id',
                            'quiz_id' => '$quiz_id',
                        ],
                        'quiz_name' => ['$addToSet' => '$quiz_name'],
                        'quiz_avg' => ['$avg' => '$quiz_avg_percent'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$_id.channel_id',
                        'channel_avg' => ['$avg' => '$quiz_avg'],
                    ]
                ]
            );
        });
        return $resultset;
    }

    public static function getCSVIndividualChannelQuizReportRaw($start_date, $end_date, $from = 0, $to = 100, $channel_id)
    {
        $resultset = FactChannelUserQuiz::raw(function ($collection) use ($start_date, $end_date, $from, $to, $channel_id) {
            return $collection->aggregate(
                [
                    '$match' => [
                        '$or' => [
                            [
                                'updated_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ],
                            [
                                'created_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ]
                        ],
                        'channel_id' => $channel_id,
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$channel_id',
                            'quiz_id' => '$quiz_id',
                        ],
                        'quiz_name' => ['$addToSet' => '$quiz_name'],
                        'quiz_avg' => ['$avg' => '$quiz_avg_percent'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$match' => [
                        'quiz_avg' => ['$gte' => $from, '$lte' => $to]
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$_id.channel_id',
                            'quiz_id' => '$_id.quiz_id',
                        ],
                        'quiz_name' => ['$addToSet' => '$quiz_name'],
                        'quiz_avg' => ['$addToSet' => '$quiz_avg'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['_id.quiz_id' => 1],
                ]
            );
        });
        return $resultset;
    }

    public static function getUserChannelQuizAgregationRaw($start, $limit, $user_id, $start_date, $end_date, $from = 0, $to = 100, $channel_ids = [])
    {
        $resultset = FactChannelUserQuiz::raw(function ($collection) use ($start, $limit, $user_id, $start_date, $end_date, $from, $to, $channel_ids) {
            return $collection->aggregate(
                [
                    '$match' => [
                        '$or' => [
                            [
                                'updated_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ],
                            [
                                'created_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ]
                        ],
                        'channel_id' => ['$in' => array_values($channel_ids)],
                        'user_id' => $user_id
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$channel_id',
                        ],
                        'quiz_avg' => ['$avg' => '$quiz_avg_percent'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$match' => [
                        'quiz_avg' => ['$gte' => $from, '$lte' => $to]
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$_id.channel_id',
                        ],
                        'channel_avg' => ['$avg' => '$quiz_avg'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['_id' => 1],
                ],
                [
                    '$skip' => $start,
                ],
                [
                    '$limit' => $limit,
                ]
            );
        });

        return $resultset;
    }

    public static function getCSVUserChannelQuizAgregationRaw($user_id, $start_date, $end_date, $from = 0, $to = 100, $channel_ids = [])
    {
        $resultset = FactChannelUserQuiz::raw(function ($c) use ($user_id, $start_date, $end_date, $from, $to, $channel_ids) {
            return $c->aggregate(
                [
                    '$match' => [
                        '$or' => [
                            [
                                'updated_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ],
                            [
                                'created_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ]
                        ],
                        'channel_id' => ['$in' => array_values($channel_ids)],
                        'user_id' => $user_id

                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$channel_id',
                        ],
                        'quiz_avg' => ['$avg' => '$quiz_avg_percent'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$match' => [
                        'quiz_avg' => ['$gte' => $from, '$lte' => $to]
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$_id.channel_id',
                        ],
                        'channel_avg' => ['$avg' => '$quiz_avg'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['_id.channel_id' => 1],
                ]
            );
        });

        return $resultset;
    }

    public static function getChannelsQuizAvgPerfRaw($start, $limit, $start_date, $end_date, $channel_ids = [])
    {
        $resultset = FactChannelUserQuiz::raw(function ($c) use ($start, $limit, $start_date, $end_date, $channel_ids) {
            return $c->aggregate(
                [
                    '$match' => [
                        '$or' => [
                            [
                                'updated_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ],
                            [
                                'created_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ]
                        ],
                        'channel_id' => ['$in' => array_values($channel_ids)],

                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$channel_id',
                            'quiz_id' => '$quiz_id',
                        ],
                        'quiz_avg' => ['$avg' => '$quiz_avg_percent'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$_id.channel_id',
                        ],
                        'channel_avg' => ['$avg' => '$quiz_avg'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['_id.channel_id' => 1],
                ],
                [
                    '$skip' => $start,
                ],
                [
                    '$limit' => $limit,
                ]
            );
        });

        return $resultset;
    }

    public static function getCSVChannelsQuizAvgPerfRaw($start_date, $end_date, $channel_ids = [])
    {
        $resultset = FactChannelUserQuiz::raw(function ($c) use ($start_date, $end_date, $channel_ids) {
            return $c->aggregate(
                [
                    '$match' => [
                        '$or' => [
                            [
                                'updated_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ],
                            [
                                'created_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ]
                        ],
                        'channel_id' => ['$in' => array_values($channel_ids)],

                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$channel_id',
                            'quiz_id' => '$quiz_id',
                        ],
                        'quiz_avg' => ['$avg' => '$quiz_avg_percent'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$_id.channel_id',
                        ],
                        'channel_avg' => ['$avg' => '$quiz_avg'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['_id.channel_id' => 1],
                ]
            );
        });

        return $resultset;
    }

    public static function getAllQuizByUserAndChannelRaw($start, $limit, $user_id, $start_date, $end_date, $from = 0, $to = 100, $channel_id)
    {
        $resultset = FactChannelUserQuiz::raw(function ($c) use ($start, $limit, $user_id, $start_date, $end_date, $from, $to, $channel_id) {
            return $c->aggregate(
                [
                    '$match' => [
                        '$or' => [
                            [
                                'updated_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ],
                            [
                                'created_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ]
                        ],
                        'user_id' => $user_id,
                        'channel_id' => $channel_id,
                        'quiz_avg_percent' => ['$gte' => $from, '$lte' => $to]
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'quiz_id' => '$quiz_id',
                        ],
                        'quiz_name' => ['$addToSet' => '$quiz_name'],
                        'quiz_avg' => ['$avg' => '$quiz_avg_percent'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['_id.quiz_id' => 1],
                ],
                [
                    '$skip' => $start,
                ],
                [
                    '$limit' => $limit,
                ]
            );
        });

        return $resultset;
    }

    public static function getCSVAllQuizByUserAndChannelRaw($user_id, $start_date, $end_date, $from = 0, $to = 100, $channel_id)
    {
        $resultset = FactChannelUserQuiz::raw(function ($c) use ($user_id, $start_date, $end_date, $from, $to, $channel_id) {
            return $c->aggregate(
                [
                    '$match' => [
                        '$or' => [
                            [
                                'updated_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ],
                            [
                                'created_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ]
                        ],
                        'user_id' => $user_id,
                        'channel_id' => $channel_id,
                        'quiz_avg_percent' => ['$gte' => $from, '$lte' => $to]
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'quiz_id' => '$quiz_id',
                            // 'quiz_name' => '$quiz_name',
                        ],
                        'quiz_name' => ['$addToSet' => '$quiz_name'],
                        'quiz_avg' => ['$avg' => '$quiz_avg_percent'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['_id.quiz_id' => 1],
                ]
            );
        });

        return $resultset;
    }

    public static function getCSVChannelQuizAgregationWithTimelineRaw($channel_id, $start_date, $end_date, $from = 0, $to = 100)
    {
        $resultset = FactChannelUserQuiz::raw(function ($c) use ($channel_id, $start_date, $end_date, $from, $to) {
            return $c->aggregate(
                [
                    '$match' => [
                        '$or' => [
                            [
                                'updated_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ],
                            [
                                'created_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ]
                        ],
                        'channel_id' => $channel_id,
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$channel_id',
                            'quiz_id' => '$quiz_id',
                        ],
                        'quiz_name' => ['$addToSet' => '$quiz_name'],
                        'quiz_avg' => ['$avg' => '$quiz_avg_percent'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$match' => [
                        'quiz_avg' => ['$gte' => $from, '$lte' => $to]
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'channel_id' => '$_id.channel_id',
                            'quiz_id' => '$_id.quiz_id',
                        ],
                        'quiz_name' => ['$addToSet' => '$quiz_name'],
                        'quiz_avg' => ['$addToSet' => '$quiz_avg'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$sort' => ['_id.quiz_id' => 1],
                ]
            );
        });

        return $resultset;
    }

    public static function getUserChannelPerformanceRaw($user_id, $start_date, $end_date, $channel_ids = [], $quiz_type = 'mock', $quiz_criteria = 'quiz_avg_percent', $order_by = -1)
    {
        $resultset = FactChannelUserQuiz::raw(function ($collection) use ($user_id, $start_date, $end_date, $channel_ids, $quiz_type, $quiz_criteria, $order_by) {
            if ($quiz_type == 'all') {
                return $collection->aggregate(
                    [
                        '$match' => [
                            '$or' => [
                                [
                                    'updated_at' => [
                                        '$gte' => $start_date,
                                        '$lte' => $end_date
                                    ]
                                ],
                                [
                                    'created_at' => [
                                        '$gte' => $start_date,
                                        '$lte' => $end_date
                                    ]
                                ]
                            ],
                            'channel_id' => ['$in' => array_values($channel_ids)],
                            'user_id' => $user_id
                        ],
                    ],
                    [
                        '$group' => [
                            '_id' => [
                                'channel_id' => '$channel_id',
                            ],
                            'channel_avg' => ['$avg' => '$' . $quiz_criteria],
                            'count' => ['$sum' => 1],
                        ]
                    ],
                    [
                        '$sort' => ['channel_avg' => -1],
                    ]
                );
            } else {
                return $collection->aggregate(
                    [
                        '$match' => [
                            '$and' => [
                                ['$or' => [
                                    ['updated_at' => [
                                        '$gte' => (int)$start_date,
                                        '$lte' => (int)$end_date
                                    ],
                                        'created_at' => [
                                            '$gte' => (int)$start_date,
                                            '$lte' => (int)$end_date
                                        ]]
                                ],],
                                ['$or' => [
                                    ['quiz_type' => $quiz_type,
                                        'quiz_type' => [
                                            '$exists' => false
                                        ]]
                                ],]
                            ],
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
                            'count' => ['$sum' => 1],
                        ]
                    ],
                    [
                        '$sort' => ['channel_avg' => -1],
                    ]
                );
            }
        });
        return $resultset;
    }

    public static function getUserChannelMockPerformance($user_id, $start_date, $end_date, $channel_ids = [], $quiz_type = 'mock', $quiz_criteria = 'quiz_avg_percent', $order_by = -1)
    {
        $resultset = FactChannelUserQuiz::raw(function ($collection) use ($user_id, $start_date, $end_date, $channel_ids, $quiz_type, $quiz_criteria, $order_by) {
            return $collection->aggregate(
                [
                    '$match' => [
                        '$and' => [
                            ['$or' => [
                                ['updated_at' => [
                                    '$gte' => (int)$start_date,
                                    '$lte' => (int)$end_date
                                ],
                                    'created_at' => [
                                        '$gte' => (int)$start_date,
                                        '$lte' => (int)$end_date
                                    ]]
                            ],
                                '$or' => [
                                    ['quiz_type' => 'mock',
                                        'quiz_type' => [
                                            '$exists' => false
                                        ]]
                                ],]
                        ],
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
                        'speed' => ['$avg' => '$speed'],
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

    public static function getChannelsMockQuizAvg($start_date, $end_date, $channel_ids = [], $quiz_type = 'mock', $quiz_criteria = 'quiz_avg_percent')
    {
        $resultset = FactChannelUserQuiz::raw(function ($c) use ($start_date, $end_date, $channel_ids, $quiz_type, $quiz_criteria) {
            return $c->aggregate(
                [
                    '$match' => [
                        '$and' => [
                            ['$or' => [
                                ['updated_at' => [
                                    '$gte' => (int)$start_date,
                                    '$lte' => (int)$end_date
                                ],
                                    'created_at' => [
                                        '$gte' => (int)$start_date,
                                        '$lte' => (int)$end_date
                                    ]]
                            ],
                                '$or' => [
                                    ['quiz_type' => 'mock',
                                        'quiz_type' => [
                                            '$exists' => false
                                        ]]
                                ],]
                        ],
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
                        'speed' => ['$avg' => '$speed'],
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

    public static function getUserChannelPracticePerformance($user_id, $start_date, $end_date, $channel_ids = [], $quiz_type = 'practice', $quiz_criteria = 'quiz_avg_percent', $order_by = -1)
    {
        $resultset = FactChannelUserQuiz::raw(function ($collection) use ($user_id, $start_date, $end_date, $channel_ids, $quiz_type, $quiz_criteria, $order_by) {
            return $collection->aggregate(
                [
                    '$match' => [
                        '$or' => [
                            ['updated_at' => [
                                '$gte' => (int)$start_date,
                                '$lte' => (int)$end_date
                            ],
                                'created_at' => [
                                    '$gte' => (int)$start_date,
                                    '$lte' => (int)$end_date
                                ]]
                        ],
                        'quiz_type' => 'practice',
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
                        'speed' => ['$avg' => '$speed'],
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

    public static function getChannelsPracticeQuizAvg($start_date, $end_date, $channel_ids = [], $quiz_type = 'practice', $quiz_criteria = 'quiz_avg_percent', $order_by = -1)
    {
        $resultset = FactChannelUserQuiz::raw(function ($c) use ($start_date, $end_date, $channel_ids, $quiz_type, $quiz_criteria, $order_by) {
            return $c->aggregate(
                [
                    '$match' => [
                        '$or' => [
                            [
                                'updated_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ],
                            [
                                'created_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ]
                        ],
                        'quiz_type' => 'practice',
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
                        'speed' => ['$avg' => '$speed'],
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

    public static function getSpecificChannelUserMockPerformance($user_id, $start_date, $end_date, $channel_id, $quiz_type = 'mock', $quiz_criteria = 'quiz_avg_percent', $order_by = -1)
    {
        $resultset = FactChannelUserQuiz::raw(function ($c) use ($user_id, $start_date, $end_date, $channel_id, $quiz_type, $quiz_criteria, $order_by) {
            return $c->aggregate(
                [
                    '$match' => [
                        '$and' => [
                            ['$or' => [
                                ['updated_at' => [
                                    '$gte' => (int)$start_date,
                                    '$lte' => (int)$end_date
                                ],
                                    'created_at' => [
                                        '$gte' => (int)$start_date,
                                        '$lte' => (int)$end_date
                                    ]]
                            ], '$or' => [
                                ['quiz_type' => $quiz_type,
                                    'quiz_type' => [
                                        '$exists' => false
                                    ]]
                            ],]
                        ],
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
                        'speed' => ['$avg' => '$speed'],
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

    public static function getSpecificChannelMockAvgPerformance($channel_id, $start_date, $end_date, $quiz_type = 'mock', $quiz_criteria = 'quiz_avg_percent', $order_by = -1)
    {
        $resultset = FactChannelUserQuiz::raw(function ($c) use ($channel_id, $start_date, $end_date, $quiz_type, $quiz_criteria, $order_by) {

            return $c->aggregate(
                [
                    '$match' => [
                        '$and' => [
                            ['$or' => [
                                ['updated_at' => [
                                    '$gte' => (int)$start_date,
                                    '$lte' => (int)$end_date
                                ],
                                    'created_at' => [
                                        '$gte' => (int)$start_date,
                                        '$lte' => (int)$end_date
                                    ]]
                            ],
                                '$or' => [
                                    ['quiz_type' => 'mock',
                                        'quiz_type' => [
                                            '$exists' => false
                                        ]]
                                ],]
                        ],
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
                        'speed' => ['$avg' => '$speed'],
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

    public static function getSpecificChannelUserPracticePerformance($user_id, $start_date, $end_date, $channel_id, $quiz_type = 'practice', $quiz_criteria = 'quiz_avg_percent', $order_by = -1)
    {
        $resultset = FactChannelUserQuiz::raw(function ($c) use ($user_id, $start_date, $end_date, $channel_id, $quiz_type, $quiz_criteria, $order_by) {

            return $c->aggregate(
                [
                    '$match' => [
                        '$or' => [
                            [
                                'updated_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ],
                            [
                                'created_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ]
                        ],
                        'quiz_type' => 'practice',
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
                        'speed' => ['$avg' => '$speed'],
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

    public static function getSpecificChannelPracticeAvgPerformance($channel_id, $start_date, $end_date, $quiz_type = 'practice', $quiz_criteria = 'quiz_avg_percent', $order_by = -1)
    {
        $resultset = FactChannelUserQuiz::raw(function ($c) use ($channel_id, $start_date, $end_date, $quiz_type, $quiz_criteria, $order_by) {

            return $c->aggregate(
                [
                    '$match' => [
                        '$or' => [
                            [
                                'updated_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ],
                            [
                                'created_at' => [
                                    '$gte' => $start_date,
                                    '$lte' => $end_date
                                ]
                            ]
                        ],
                        'channel_id' => $channel_id,
                        'quiz_type' => 'practice',
                    ],
                ],
                [
                    '$group' => [
                        '_id' => [
                            'quiz_id' => '$quiz_id',
                        ],
                        'quiz_name' => ['$addToSet' => '$quiz_name'],
                        'quiz_avg' => ['$avg' => '$' . $quiz_criteria],
                        'speed' => ['$avg' => '$speed'],
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
}
