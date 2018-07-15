<?php
namespace App\Model\QuizAttempt\Repository;

/**
 * interface IQuizAttempt
 */
interface IQuizAttemptRepository
{
    /**
     * Method to get attempt details by attempt_id
     *
     * @param int $attempt_id primary key
     * @return \App\Model\QuizAttempt
     */
    public function find($attempt_id);

    /**
     * Method to get all the attempts of given quiz id and user id
     *
     * @param int $quiz_id
     * @param int $user_id
     * @return \App\Model\QuizAttempt
     */
    public function findAllAttempts($quiz_id, $user_id);

    /**
     * Method to create new attempt with given data
     *
     * @param object $data
     * @return int $attempt_id
     */
    public function newAttempt($data);

    /**
     * @param $attempt
     * @param $question_id
     * @param $answer
     * @param $reviewed
     * @return mixed
     */
    public function updateDetails($attempt, $question_id, $answer, $reviewed);

    /**
     * getAttemptDetailsByTime get attempt details in between time specified
     * @param  integer $start_date
     * @param  integer $end_date
     * @return collection
     */
    public function getAttemptDetailsByTime($start_date, $end_date);

    /**
     * getMaxAttempts
     * @param  integer $start_date
     * @param  integer $end_date
     * @return array
     */
    public function getMaxAttempts($start_date, $end_date);

    /**
     * getAttemptDetailsByIds
     * @param  integer $attempt_ids
     * @return collection
     */
    public function getAttemptDetailsByIds($attempt_ids);

    /**
     * @param int $quiz_id
     * @param int $uid
     * @return collection
     */
    public function getClosedQuizzes($quiz_id, $uid);

    /**
     * @param  int $quiz_id
     * @return boolean
     */
    public function hasAttemptes($quiz_id);
}
