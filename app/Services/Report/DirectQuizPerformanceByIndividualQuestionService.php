<?php

namespace App\Services\Report;

use App\Model\Report\IDirectQuizPerformanceByIndividualQuestionRepository;

class DirectQuizPerformanceByIndividualQuestionService implements IDirectQuizPerformanceByIndividualQuestionService
{
    private $dir_quiz_per_ind_repo;

    public function __construct(
        IDirectQuizPerformanceByIndividualQuestionRepository $dir_quiz_per_ind_repo
    ) {
        $this->dir_quiz_per_ind_repo = $dir_quiz_per_ind_repo;
    }
   
   /**
     * @inheritdoc
     */
    public function getSearchCountQuizUser($quiz_id = 0)
    {
        return $this->dir_quiz_per_ind_repo->getSearchCountQuizUser($quiz_id);
    }

    /**
     * @inheritdoc
     */
    public function getChannelQuizperformanceWithPagenation(
        $start = 0,
        $limit = 10,
        $orderby = ['created_at' => 'desc'],
        $search = null,
        $quiz_id = 0
    ) {
        return $this->dir_quiz_per_ind_repo->getChannelQuizperformanceWithPagenation(
            $start,
            $limit,
            $orderby,
            $search,
            $quiz_id
        );
    }
}
