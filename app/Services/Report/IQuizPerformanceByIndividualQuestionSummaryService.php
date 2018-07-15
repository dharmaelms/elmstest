<?php

namespace App\Services\Report;

/**
 * Interface IQuizPerformanceByIndividualQuestionSummaryService
 * @package App\Services\Report
 */
interface IQuizPerformanceByIndividualQuestionSummaryService
{
    /**
     * getAvgChannelQuesScore
     * @param  integer $quiz_id
     * @param  integer $channel_id
     * @return array
     */
    public function getAvgChannelQuesScore($quiz_id, $channel_id);
}
