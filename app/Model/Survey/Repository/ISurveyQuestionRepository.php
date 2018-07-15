<?php

namespace App\Model\Survey\Repository;

/**
 * Interface ISurveyQuestionRepository
 *
 * @package App\Model\Survey\Repository
 */
interface ISurveyQuestionRepository
{
    /**
     * @return integer $survey_id
     * @return collection
     */
    public function getSurveyQuestions($filter_params, $orderBy);

    public function getSurveyQuestionsById($question_ids);

    public function updateSurveyQuestions($question_id, $input_data);

    public function DeleteSurveyQuestion($question_id);

    public function getSurveyQuestionCount($filter_params, $orderBy);

    /**
     * @param integer $survey_id
     * @return collection
     */
    public function getQuestionBySurveyId($survey_id);

    public function insertSurveyQuestions($data);
}
