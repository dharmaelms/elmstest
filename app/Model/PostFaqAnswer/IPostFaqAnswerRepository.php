<?php

namespace App\Model\PostFaqAnswer;

/**
 * Interface IPostFaqAnswerRepository
 * @package App\Model\PostFaqAnswer
 */
interface IPostFaqAnswerRepository
{
	/**
     * getAnswersByQuestionID
     * @param int question_id
     * @param int user_id
     */
	public function getAnswersByQuestionID($question_id, $user_id);

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
