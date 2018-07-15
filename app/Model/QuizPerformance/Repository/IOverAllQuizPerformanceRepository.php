<?php

namespace App\Model\QuizPerformance\Repository;

interface IOverAllQuizPerformanceRepository
{
    /**
     * findIndChannelPerformance
     * @param  int $channel_id
     * @param  int $user_id
     * @param  int $start
     * @return array
     */
    public function findIndChannelPerformance($channel_id, $user_id, $start);

    /**
     * findDirectQuizPerformance
     * @param  array  $quiz_ids
     * @param  integer $start
     * @param  integer $limit
     * @return array
     */
    public function findDirectQuizPerformance(array $quiz_ids, $start, $limit);

    /**
     * findQuizPerformance
     * @param  array  $quiz_ids
     * @param  integer $user_id
     * @param  integer $start
     * @return array
     */
    public function findQuizPerformance(array $quiz_ids, $user_id, $start);
}
