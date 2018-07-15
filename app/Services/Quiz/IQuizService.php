<?php

namespace App\Services\Quiz;

/**
 * Interface IQuizService
 * @package App\Services\Quiz
 */
interface IQuizService
{
    /**
     * Method to get assessments with page and limit
     *
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getQuizzes($page, $limit);

    /**
     * Method to get all assessments by program slug
     *
     * @param string $slug
     * @param int $page
     * @param int $limit
     * @return
     */
    public function getQuizzesByProgram($page, $limit, $slug);

    /**
     * Method to get all assessments  created by username
     *
     * @param array $usernames
     * @return array 
     */
    public function getQuizzesByUsername($usernames = []);

    /**
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
     * Method to send quiz reminder email notification to users
     * @param email template name
     */
    public function sendReminderEmailNotification($email_template_slug, $quiz_details);

    /**
     * @param array $ids
     * @return array
     */
    public function getQuizDataUsingIDS($ids);

    /**
     * @param  array $quiz_ids
     * @return integer
     */
    public function countActiveQuizzes($quiz_ids);
}
