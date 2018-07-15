<?php

namespace App\Services\Report;

use App\Model\Report\IDirectQuizPerformanceByIndividualQuestionSummaryRepository;

class DirectQuizPerformanceByIndividualQuestionSummaryService implements IDirectQuizPerformanceByIndividualQuestionSummaryService
{
    private $dir_quiz_per_sum_repo;

    public function __construct(
        IDirectQuizPerformanceByIndividualQuestionSummaryRepository $dir_quiz_per_sum_repo
    ) {
        $this->dir_quiz_per_sum_repo = $dir_quiz_per_sum_repo;
    }
    /**
     * @inheritdoc
     */
    public function getAvgChannelQuesScore($quiz_id = 0)
    {
        return $this->dir_quiz_per_sum_repo->getAvgChannelQuesScore($quiz_id);
    }
}
