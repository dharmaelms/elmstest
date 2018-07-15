<?php

namespace App\Model\Quiz;

/**
 * Interface IQuizRepository
 *
 * @package App\Model\Quiz
 */
interface IQuizRepository
{
    /**
     * Method to get user quizzes
     * @param  int $page page number
     * @param  int $limit no of results
     * @param  array $quiz_ids
     * @return array
     */
    public function getUserQuizzes($page, $limit, $quiz_ids);

    /**
     * Method to get all quizzes assigned to user
     * @return array
     */
    public function getAllQuizzesAssignedToUser();

    /**
     * Method to get all attempted quizzes to user
     *
     * @param  $quiz_ids
     * @return array
     */
    public function getAttemptedQuizzes($quiz_ids);

    /**
     * Method to get quizzes active quizzes
     *
     * @param array $quiz_ids unattempted quiz ids
     * @return array list of quiz_id
     */
    public function getActiveQuizzes($quiz_ids);

    /**
     * Method to get quizzes ends today
     *
     * @param array $quiz_ids unattempted quiz ids
     * @return array list of quiz_id
     */
    public function getQuizEndsToday($quiz_ids, $column = []);

    /**
     * Method to get quizzes with no end time
     *
     * @param array $quiz_ids unattempted quiz ids
     * @return array list of quiz_id
     */
    public function getQuizzesWithNoEndTime($quiz_ids, $column = []);

    /**
     * Method to get quizzes ends from tomorrow
     *
     * @param array $quiz_ids unattempted quiz ids
     * @return array list of quiz_id
     */
    public function getQuizzesEndsFromTomorrow($quiz_ids, $column = []);

    /**
     * Method to get quizzes with pagination
     *
     * @param  array $quiz_ids
     * @param  int $start
     * @param  int $limit
     * @return  array quizzes details
     */
    public function getQuizzes($quiz_ids, $start, $limit);


    /**
     * Method to paginate results
     *
     * @param object $data
     * @param int $page
     * @param int $limit
     *
     * @return object
     */
    public function paginateData($data, $page, $limit);

    /**
     * Method to get all assessments  created by username
     *
     * @param array $usernames
     * @return Object
     */
    public function getQuizzesByUsername($usernames = []);

    /**
     * Method to get all active quizzes
     */
    public function activeQuizzes();

    /**
     * Method to get quiz details by primary key
     *
     * @param int $quiz_id
     * @return \App\Model\Quiz
     * @throw QuizNotFoundException
     */
    public function find($quiz_id);

    /*
     * getQuizzesByIds
     * @param  array $quiz_ids
     * @param  array $reminder_filter
     * @return array
     */
    public function getQuizzesByIds($quiz_ids, $reminder_filter);

    /**
     * getAboutExpireQuizzes
     * @param  array $date
     * @param  array $reminder_filter
     * @return array
     */
    public function getAboutExpireQuizzes($date, $reminder_filter);

     /**
     * Method to the list of not attempted quizzes
     *
     * @param  array $quiz_ids as keys, and user_ids as a array values
     * @return  array quizzes details
     */
    public function getNotAttemptedQuizzes($quiz_id, $quiz_user_ids);

    /**
     * Method to get all the quizzes except question_generator by channel id's
     *
     * @param  int $channel_ids
     * @return  collection quizzes details
     */
    public function getQuizChannel($channelId);

    /**
     * @param array $ids
     * @return array
     */
    public function getQuizDataUsingIDS($ids);

        /**
     * getChannelRelation get channel related quizzes
     * @return array
     */
    public function getChannelRelation();

    /**
     * getUserRelQuizzes get all user related quizzes
     * @return collection
     */
    public function getUserRelQuizzes();

    /**
     * findQuizzesByQuizids
     * @param  array $quiz_ids
     * @param  array $order_by
     * @return collection
     */
    public function findQuizzesByQuizids($quiz_ids, $order_by = []);

    /**
     * Method to get quiz by attribute
     * @param  string $field
     * @param  array $value
     * @return App\Model\Quiz
     */
    public function findByAttribute($field, $value);

    /**
     * @param  array $quiz_ids
     * @return integer
     */
    public function countActiveQuizzes($quiz_ids);
}
