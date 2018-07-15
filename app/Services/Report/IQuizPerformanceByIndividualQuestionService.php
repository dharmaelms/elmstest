<?php

namespace App\Services\Report;

interface IQuizPerformanceByIndividualQuestionService
{
    /**
     * getSearchCountChannel
     * @param  integer $quiz_id
     * @param  integer $channel_id
     * @return array
     */
    public function getSearchCountChannel($quiz_id, $channel_id);

    /**
     * getChannelQuizPerformanceWithPagination
     * @param  integer $start
     * @param  integer $limit
     * @param  array   $order_by
     * @param  string  $search
     * @param  integer $quiz_id
     * @param  integer $channel_id
     * @param  integer $user_id
     * @return array
     */
    public function getChannelQuizPerformanceWithPagination(
        $start = 0,
        $limit = 10,
        $order_by = ['created_at' => 'desc'],
        $search = null,
        $quiz_id = 0,
        $channel_id = 0,
        $user_id = 0
    );
}
