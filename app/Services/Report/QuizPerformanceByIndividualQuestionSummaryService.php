<?php

namespace App\Services\Report;

use App\Model\Report\IQuizPerformanceByIndividualQuestionSummaryRepository;

/**
 * Class QuizPerformanceByIndividualQuestionSummaryService
 * @package App\Services\Report
 */
class QuizPerformanceByIndividualQuestionSummaryService implements IQuizPerformanceByIndividualQuestionSummaryService
{
    /**
     * @var IQuizPerformanceByIndividualQuestionSummaryRepository
     */
    private $quiz_per_sum_repo;

    /**
     * QuizPerformanceByIndividualQuestionSummaryService constructor.
     * @param IQuizPerformanceByIndividualQuestionSummaryRepository $quiz_per_sum_repo
     */
    public function __construct(
        IQuizPerformanceByIndividualQuestionSummaryRepository $quiz_per_sum_repo
    ) {
        $this->quiz_per_sum_repo = $quiz_per_sum_repo;
    }
    
    /**
     * @inheritdoc
     */
    public function getAvgChannelQuesScore($quiz_id, $channel_id)
    {
        return $this->quiz_per_sum_repo->getAvgChannelQuesScore($quiz_id, $channel_id);
    }
}
