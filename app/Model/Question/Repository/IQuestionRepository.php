<?php

namespace App\Model\Question\Repository;

/**
 * Interface IQuestionRepository
 * @package App\Model\Question\Repository
 */
interface IQuestionRepository extends IBaseRepository
{
    /**
     * @param $mediaId
     * @return mixed
     */
    public function getByMedia($mediaId);

    /**
     * @param $key
     * @param $values
     * @return mixed
     */
    public function getByAttribute($key, $values);

    /**
     * getQuestionsText
     * @param  integer $question_ids
     * @return mixed
     */
    public function getQuestionsText($question_ids);

    /**
     * getQuestions
     * @param  integer $question_ids
     * @return mixed
     */
    public function getQuestions($question_ids);
}
