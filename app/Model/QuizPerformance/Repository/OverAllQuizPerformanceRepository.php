<?php

namespace App\Model\QuizPerformance\Repository;

use App\Model\OverAllQuizPerformance;
use App\Model\DimensionChannelUserQuiz;
use App\Model\Quiz\IQuizRepository;

class OverAllQuizPerformanceRepository implements IOverAllQuizPerformanceRepository
{
    /**
     * @var  IQuizRepository $quiz_repository
     */
    private $quiz_repository;

    public function __construct(IQuizRepository $quiz_repository)
    {
        $this->quiz_repository = $quiz_repository;
    }

    /**
     * {@inheritdoc}
     */
    public function findIndChannelPerformance($channel_id, $user_id, $start)
    {
        $channel_details =  DimensionChannelUserQuiz::getUserByChannelId($channel_id);
        $channel_details = array_get($channel_details, '0', []);
        $quiz_ids = [];
        $result = [];
        $quiz_ids = array_get($channel_details, 'quiz_ids', []);
        if (!empty($quiz_ids)) {
            $quiz_ids = $this->quiz_repository->findQuizzesByQuizids($quiz_ids)
                ->lists('quiz_id')
                ->all();
            if (!empty($quiz_ids)) {
                $result = $this->findQuizPerformance($quiz_ids, $user_id, $start);
            }
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function findDirectQuizPerformance(array $quiz_ids, $start, $limit)
    {
        $quizzes = $this->quiz_repository->getUserRelQuizzes();
        $all_quiz_ids = $quizzes->pluck('quiz_id')->all();
        if (!empty($quiz_ids)) {
            $direct_q_ids = array_intersect($quiz_ids, $all_quiz_ids);
        } else {
            $direct_q_ids = $all_quiz_ids;
        }
        $quiz_results = $this->findQuizPerformance($direct_q_ids, 0, $start);
        if (!empty($quiz_results) && $limit > 0) {
            $quiz_results = $quiz_results->slice(0, $limit); 
        }
        return $quiz_results;
    }

    /**
     * {@inheritdoc}
     */
    public function findQuizPerformance(array $quiz_ids, $user_id, $start)
    {
        if (!empty($quiz_ids)) {
            $match['quiz_id'] =['$in' => $quiz_ids];
            if ($user_id > 0) {
                $match['user_id'] = (int) $user_id;
            }
            return OverAllQuizPerformance::raw(function ($c) use ($match, $start) {
                return $c->aggregate([
                    [
                        '$match' => $match
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
                        '$skip' => (int)$start,
                    ]
                ]);
            });
        } else {
            return ['result' => []];
        }
    }

    public function findUserQuizzesPerformance($user_id, $quiz_ids, $is_practice, $criteria)
    {
        $match['quiz_id'] = ['$in' => array_values($quiz_ids)];
        if (!is_null($is_practice)) {
            $match['is_practice'] = $is_practice;
        }
        if ($user_id > 0) {
            $match['user_id'] = $user_id;
        }
        return OverAllQuizPerformance::raw(function ($collection) use ($match, $criteria) {
            return $collection->aggregate([
                [
                    '$match' => $match,
                ],
                [
                    '$group' => [
                        '_id' => [
                            'quiz_id' => '$quiz_id',
                        ],
                        'avg' => ['$avg' => '$' . $criteria],
                        'speed' => ['$avg' => '$speed_s'],
                        'count' => ['$sum' => 1],
                    ]
                ],
                [
                    '$project' => [
                        'quiz_id' => '$_id.quiz_id',
                        'avg' => 1,
                        'speed' => 1,
                        'count' => 1,
                        '_id' => -1
                    ]
                ]
            ]);
        });
    }
}
