<?php

namespace App\Services\Report;

interface IDirectQuizPerformanceByIndividualQuestionSummaryService
{
    /**
     * getAvgChannelQuesScore
     * @param  integer $quiz_id
     * @return array
     */
    public function getAvgChannelQuesScore($quiz_id = 0);
}
