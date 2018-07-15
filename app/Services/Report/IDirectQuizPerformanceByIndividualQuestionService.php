<?php

namespace App\Services\Report;

interface IDirectQuizPerformanceByIndividualQuestionService
{
    /**
     * getSearchCountQuizUser
     * @param  integer $quiz_id
     * @return array
     */
    public function getSearchCountQuizUser($quiz_id = 0);

    /**
     * getChannelQuizperformanceWithPagenation
     * @param  integer $start
     * @param  integer $limit
     * @param  array   $orderby
     * @param  [type]  $search
     * @param  integer $quiz_id
     * @return array
     */
    public function getChannelQuizperformanceWithPagenation(
        $start = 0,
        $limit = 10,
        $orderby = ['created_at' => 'desc'],
        $search = null,
        $quiz_id = 0
    );
}
