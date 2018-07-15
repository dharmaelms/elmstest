<?php

namespace App\Model\Report;

use App\Model\QuizPerformanceByIndividualQuestionSummary;

class QuizPerformanceByIndividualQuestionSummaryRepository implements IQuizPerformanceByIndividualQuestionSummaryRepository
{
    /**
     * @inheritdoc
     */
    public function getAvgChannelQuesScore($quiz_id, $channel_id)
    {
        return QuizPerformanceByIndividualQuestionSummary::where('quiz_id', '=', (int)$quiz_id)
            ->where('channel_id', '=', (int)$channel_id)
            ->orderBy('id', 'desc')
            ->first();
    }
}
