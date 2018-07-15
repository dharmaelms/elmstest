<?php

namespace App\Model\Report;

use App\Model\QuizPerformanceTillDate;

class TillQuizPerformanceRepository implements ITillQuizPerformanceRepository
{
    /**
     * {@inheritdoc}
     */
    public function findIndChannelPerformance($channel_id, $start)
    {
        return QuizPerformanceTillDate::raw(function ($c) use ($channel_id, $start) {
            return $c->aggregate([
                [
                    '$match' => [
                        'channel_id' => $channel_id
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$quiz_id',
                        'avg_score' => ['$avg' => '$score'],
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
}
