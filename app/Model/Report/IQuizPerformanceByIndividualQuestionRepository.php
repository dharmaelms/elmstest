<?php

namespace App\Model\Report;

interface IQuizPerformanceByIndividualQuestionRepository
{

    /**
     * getSearchCountChannel
     * @param  integer $quiz_id
     * @param  integer $channel_id
     * @return array
     */
    public function getSearchCountChannel($quiz_id, $channel_id);

    /**
     * getChannelQuizperformanceWithPagenation
     * @param  integer $start
     * @param  integer $limit
     * @param  array $orderby
     * @param  string $search
     * @param  integer $quiz_id
     * @param  integer $channel_id
     * @param  integer $user_id
     * @return array
     */
    public function getChannelQuizperformanceWithPagenation(
        $start = 0,
        $limit = 10,
        $orderby = ['created_at' => 'desc'],
        $search = null,
        $quiz_id = 0,
        $channel_id = 0,
        $user_id = 0
    );

}
