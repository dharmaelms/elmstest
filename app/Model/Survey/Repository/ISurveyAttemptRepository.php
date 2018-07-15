<?php

namespace App\Model\Survey\Repository;

/**
 * Interface ISurveyAttemptRepository
 *
 * @package App\Model\Survey\Repository
 */
interface ISurveyAttemptRepository
{
    /**
     * @param integer $attempt_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSurveyAttempt($attempt_id);

    /**
     * @param array $data
     * @return boolean
     */
    public function insertData($data);

    /**
     * @return int
     */
    public function getNextSequence();

    /**
     * @param array $params
     * @return boolean
     */
    public function getSurveyAttemptBySurveyIdAndUserId($params);

    /**
     * @param array $survey_id
     * @param array $user_id
     * @param array $status
     * @param array $completed_on
     * @return boolean
     */
    public function updateData($survey_id, $user_id, $status, $completed_on);

    /**
     * @param array $user_id
     * @param array $survey_ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSurveyAttemptByUserIdAndSurveyIds($user_id, $survey_ids);
}
