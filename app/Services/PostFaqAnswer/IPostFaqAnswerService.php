<?php

namespace App\Services\PostFaqAnswer;

/**
 * Interface IPostFaqAnswerService
 * @package App\Services\PostFaqAnswer
 */
interface IPostFaqAnswerService
{
    /**
     * getAnswersByQuestionID
     * @param int question_id
     */
    public function getAnswersByQuestionID($question_id = null, $user_id = null);

    /**
     * getAnswersByAnswerID
     * @param int question_id
     */
    public function getAnswersByAnswerID($question_id);

    /**
     * InsertRecord
     * @param int question_id
     */
    public function InsertRecord($insertarr);

    /**
     * DeleteRecord
     * @param int question_id
     */
    public function DeleteRecord($question_id);

}
