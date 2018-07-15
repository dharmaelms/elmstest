<?php
namespace App\Services\QuizAttempt;

use App\Exceptions\Quiz\AttemptNotAllowedException;
use Illuminate\Support\Collection;

/**
 * Interface IQuizAttemptService
 * @package App\Services\QuizAttempt
 */
interface IQuizAttemptService
{
    /**
     * Method to create new attempt for given quiz.
     *
     * @param int $quiz_id primary key
     * @return int
     */
    public function createAttempt($quiz_id);

    /**
     * Method to validate quiz, user quiz relation and expiry date
     *
     * @param $quiz_id
     */
    public function validateQuiz($quiz_id);

    /**
     * Method to validate is quiz is assigned to user or not
     *
     * @param int $quiz_id
     */
    public function isQuizAssignedToUser($quiz_id);

    /**
     * Method to validate the quiz expiry date
     *
     * @param int $quiz_id
     */
    public function isQuizExpiredOrNot($quiz_id);

    /**
     * Method to validate no of attempts allowed for quiz
     *
     * @param Collection $quiz
     * @param Collection $attempts
     * @return boolean
     * @throws AttemptNotAllowedException
     */
    public function validateQuizAttempt($quiz, $attempts);

    /**
     * Method to create new attempt
     *
     * @param Collection $quiz
     * @return int
     */
    public function createNewAttempt($quiz);

    /**
     * Method to copy questions to attempt data from quiz/sections
     *
     * @param Collection $attempt
     */
    public function copyAttemptQuestions($attempt);

    /**
     * Method to save user answer
     *
     * @param object $request
     * @param int $attempt_id
     * @return array
     */
    public function saveAnswer($request, $attempt_id);

    /**
     * Method to get next question
     *
     * @param int $attempt_id
     * @param int $page
     * @param int $section
     * @return array
     */
    public function nextQuestion($attempt_id, $page, $section);

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
