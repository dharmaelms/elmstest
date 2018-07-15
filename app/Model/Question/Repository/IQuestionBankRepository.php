<?php

namespace App\Model\Question\Repository;

/**
 * Interface IQuestionBankRepository
 * @package App\Model\Question\Repository
 */
interface IQuestionBankRepository extends IBaseRepository
{
    /**
     * @param int $start
     * @param string $orderBy
     * @param string $orderByDir
     * @param null $limit
     * @return mixed
     */
    public function getActiveQuestionBanks($start = 0, $orderBy = "created_at", $orderByDir = "desc", $limit = null);

    /**
     * @param $questionBankId
     * @param $question
     * @return mixed
     */
    public function assignQuestion($questionBankId, $question);

    /**
     * @param $questionBankId
     * @param $question
     * @return mixed
     */
    public function unassignQuestion($questionBankId, $question);
}
