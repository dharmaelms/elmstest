<?php

namespace App\Model\Report;

interface IDirectQuizPerformanceByIndividualQuestionSummaryRepository
{
    /**
     * getAvgChannelQuesScore
     * @param  integer $quiz_id
     * @return array
     */
    public function getAvgChannelQuesScore($quiz_id = 0);
}
