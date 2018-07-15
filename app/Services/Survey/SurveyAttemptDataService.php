<?php

namespace App\Services\Survey;

use App\Model\Survey\Repository\ISurveyAttemptDataRepository;

/**
 * Class SurveyAttemptDataService
 *
 * @package App\Services\Survey
 */
class SurveyAttemptDataService implements ISurveyAttemptDataService
{
    private $survey_attempt_data;

    public function __construct(
        ISurveyAttemptDataRepository $survey_attempt_data
    ) {
        $this->survey_attempt_data = $survey_attempt_data;
    }
    /**
     * {@inheritdoc}
     */
    public function getSurveyAttempt($attempt_id)
    {
        //TODO
    }
    /**
     * {@inheritdoc}
     */
    public function getSurveyAttemptData($survey_id, $user_ids)
    {
        return $this->survey_attempt_data->getSurveyAttemptData($survey_id, $user_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getNextSequence()
    {
        return $this->survey_attempt_data->getNextSequence();
    }

    /**
     * {@inheritdoc}
     */
    public function insertData($data)
    {
        return $this->survey_attempt_data->insertData($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getSurveyAttemptDataByUserIdAndSurveyId($survey_id, $user_id)
    {
        return $this->survey_attempt_data->getSurveyAttemptDataByUserIdAndSurveyId($survey_id, $user_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getSurveyAttemptByUserIdAndSurveyIds($user_id, $survey_ids)
    {
        return $this->survey_attempt_data->getSurveyAttemptByUserIdAndSurveyIds($user_id, $survey_ids)->pluck('survey_id')->unique();
    }

    /**
     * {@inheritdoc}
     */
    public function getSurveyAnswers($survey_id, $question_id)
    {
        return $this->survey_attempt_data->getSurveyAnswers($survey_id, $question_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserResponse($survey_id, $user_ids)
    {
        return $this->survey_attempt_data->getUserResponse($survey_id, $user_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescAnswers($survey_id, $user_ids)
    {
        return $this->survey_attempt_data->getDescAnswers($survey_id, $user_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserTextByQuestion($survey_id, $question_id, $user_ids, $start, $limit)
    {
        return $this->survey_attempt_data->getUserTextByQuestion($survey_id, $question_id, $user_ids, $start, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function getRespondedUsers($survey_id, $question_id, $choice_index, $user_ids, $start, $limit)
    {
        return $this->survey_attempt_data->getRespondedUsers($survey_id, $question_id, $choice_index, $user_ids, $start, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public  function  getAttemptedSurveysBySurveyIds($survey_ids)
    {
        return $this->survey_attempt_data->getAttemptedSurveysBySurveyIds($survey_ids)->keyBy('survey_id');
    }
}
