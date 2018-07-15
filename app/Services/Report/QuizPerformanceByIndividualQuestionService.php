<?php

namespace App\Services\Report;

use App\Model\Report\IQuizPerformanceByIndividualQuestionRepository;

/**
 * Class QuizPerformanceByIndividualQuestionService
 * @package App\Services\Report
 */
class QuizPerformanceByIndividualQuestionService implements IQuizPerformanceByIndividualQuestionService
{
    /**
     * @var IQuizPerformanceByIndividualQuestionRepository
     */
    private $quiz_per_repo;

    /**
     * QuizPerformanceByIndividualQuestionService constructor.
     * @param IQuizPerformanceByIndividualQuestionRepository $quiz_per_repo
     */
    public function __construct(
        IQuizPerformanceByIndividualQuestionRepository $quiz_per_repo
    ) {
        $this->quiz_per_repo = $quiz_per_repo;
    }

    /**
     * @inheritdoc
     */
    public function getSearchCountChannel($quiz_id, $channel_id)
    {
        return $this->quiz_per_repo->getSearchCountChannel($quiz_id, $channel_id);
    }

    /**
     * @inheritdoc
     */
    public function getChannelQuizPerformanceWithPagination(
        $start = 0,
        $limit = 10,
        $order_by = ['created_at' => 'desc'],
        $search = null,
        $quiz_id = 0,
        $channel_id = 0,
        $user_id = 0
    ) {
        return $this->quiz_per_repo->getChannelQuizperformanceWithPagenation(
            $start,
            $limit,
            $order_by,
            $search,
            $quiz_id,
            $channel_id,
            $user_id
        );
    }
}
