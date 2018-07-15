<?php

namespace App\Model\Report;

interface IQuizPerformanceByIndividualQuestionSummaryRepository
{
    /**
     * getAvgChannelQuesScore
     * @param  integer $quiz_id
     * @param  integer $channel_id
     * @return array
     */
    public function getAvgChannelQuesScore($quiz_id, $channel_id);
}
