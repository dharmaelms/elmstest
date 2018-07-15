<?php

namespace App\Model\Report;

use App\Model\DirectQuizPerformanceByIndividualQuestionSummary;

class DirectQuizPerformanceByIndividualQuestionSummaryRepository implements IDirectQuizPerformanceByIndividualQuestionSummaryRepository
{

    /**
     * @inheritdoc
     */
    public function getAvgChannelQuesScore($quiz_id = 0)
    {
        return DirectQuizPerformanceByIndividualQuestionSummary::where('quiz_id', '=', (int)$quiz_id)
            ->orderBy('id', 'desc')
            ->first();
    }
}
