<?php namespace App\Services\Report;

/**
 * Interface IReportService
 * @package App\Services\Report
 */
interface IReportService
{

    /**
     * @param int $orderBy
     * @return mixed
     */
    public function getChannelCompletionTillDate($orderBy);

    /**
     * @param int $channelID
     * @param int $orderBy
     * @return mixed
     */
    public function getSpecificChannelCompletionTillDate($channelID, $orderBy);

    /**
     * @param  [string] $typeQuizzes     [description]
     * @param  [string] $criteria [description]
     * @param  [int] $orderBy         [description]
     */
    public function getChannelPerformanceTillDate($typeQuizzes, $criteria, $orderBy);

    /**
     * @param  [int] $channelID       [description]
     * @param  [string] $typeQuizzes     [description]
     * @param  [string] $criteria [description]
     * @param  [int] $orderBy         [description]
     */
    public function getSpecificChannelPerformanceTillDate($channelID, $typeQuizzes, $criteria, $orderBy);

    /**
     * @param  [string] $typeQuizzes     [description]
     * @param  [string] $criteria [description]
     * @param  [int] $orderBy         [description]
     */
    public function getDirectQuizPerformanceTillDate($typeQuizzes, $criteria, $orderBy);

    /**
     * @param  [integer] $user_id
     * @param  [array] $quiz_ids
     * @param  [collection] $channel_user_quiz
     * @param  [String] $criteria
     * @param  [boolean] $is_practice
     * @return array
     */
    public function processChannelPerformance($user_id, $quiz_ids, $channel_user_quiz, $criteria, $is_practice);

    /**
     * @param  [integer] $user_id
     * @param  [array] $quiz_ids
     * @param  [string] $criteria
     * @param  [boolean] $is_practice
     * @return [array]
     */
    public function processIndChannelperformance($user_id, $quiz_ids, $criteria, $is_practice);

    /**
     * @param  [array] $quiz_ids
     * @param  [integer] $user_id
     * @param  [String] $typeQuizzes
     * @param  [String] $criteria
     * @return [array]
     */
    public function processQuizzesPerformance($quiz_ids, $user_id, $typeQuizzes, $criteria);

}
