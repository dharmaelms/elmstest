<?php
namespace App\Model\QuizAttemptData\Repository;
use Illuminate\Support\Collection;

/**
 * interface IQuizAttemptDataRepository
 * @package App\Model\QuizAttemptData\Repository
 */
interface IQuizAttemptDataRepository
{
    /**
     * Method to get all the questions in the attempt
     *
     * @param int $attempt_id
     * @return \App\Model\QuizAttemptData
     */
    public function getAttemptData($attempt_id);

    /**
     * Method to get next/previous question for the attempt
     *
     * @param int $attempt_id
     * @param array $question_id
     * @param int $section_id
     * @return \App\Model\QuizAttemptData
     */
    public function getQuestion($attempt_id, $question_id, $section_id = '');

    /**
     * Method to insert questions
     *
     * @param Collection $attempt
     * @param Collection $questions
     * @return boolean
     */
    public function copyQuestions($attempt, $questions);

    /**
     * Method to insert questions
     *
     * @param collection $question
     * @param collection $attempt
     * @param int $section_id optional
     * @return boolean
     */
    public function insertQuestion($question, $attempt, $section_id = '');

    /**
     * Method to save user response
     *
     * @param collection $attempt
     * @param int $page
     * @param int $next_page
     * @param string $answer
     * @param boolean $reviewed
     * @param int $time_spend
     * @return Response
     */
    public function saveAnswer($attempt, $page, $next_page, $answer, $reviewed, $time_spend, $section);

    /**
     * getAttemptDataByIds
     * @param  array  $attempt_ids
     * @return collection
     */
    public function getAttemptDataByIds($attempt_ids);

}
