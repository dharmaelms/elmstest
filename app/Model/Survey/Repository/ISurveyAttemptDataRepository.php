<?php

namespace App\Model\Survey\Repository;

/**
 * Interface ISurveyAttemptDataRepository
 *
 * @package App\Model\Survey\Repository
 */
interface ISurveyAttemptDataRepository
{
    /**
     * @param integer $attempt_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSurveyAttempt($attempt_id);

    /**
     * @param integer $survey_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSurveyAttemptData($survey_id, $user_ids);

    /**
     * @return int
     */
    public function getNextSequence();

    /**
     * @param array $data
     * @return boolean
     */
    public function insertData($data);

    /**
     * @param integer $survey_id
     * @param integer $user_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSurveyAttemptDataByUserIdAndSurveyId($survey_id, $user_id);

    /**
     * @param array $user_id
     * @param array $survey_ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSurveyAttemptByUserIdAndSurveyIds($user_id, $survey_ids);

    /**
     * @param array $survey_id
     * @param array $question_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSurveyAnswers($survey_id, $question_id);

     /**
     * @param array $survey_id
     * @param array $user_ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
     public function getUserResponse($survey_id, $user_ids);

     /**
     * @param $survey_id
     * @param array $user_ids
     * @return \Illuminate\Database\Eloquent\Collection
     */

     public function getDescAnswers($survey_id, $user_ids);

    /**
     * @param $survey_id
     * @param $question_id
     * @param array $user_ids
     * @param $start
     * @param $limit
     * @return mixed
     */
    public function getUserTextByQuestion($survey_id, $question_id, $user_ids, $start, $limit);

    /**
     * @param $survey_ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public  function  getAttemptedSurveysBySurveyIds($survey_ids);

    /**
     * @param $survey_id
     * @param $question_id
     * @param $choice_index
     * @param $start
     * @param $limit
     * @param array $user_ids
     * @return user_ids
     */
    public function getRespondedUsers($survey_id, $question_id, $choice_index, $user_ids, $start, $limit);
}
