<?php

namespace App\Services\Survey;

/**
 * Interface ISurveyQuestionService
 *
 * @package App\Services\Survey
 */
/**
 * Interface ISurveyQuestionService
 * @package App\Services\Survey
 */
interface ISurveyQuestionService
{
    /**
     * @param $filter_params
     * @param array $orderBy
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSurveyQuestions($filter_params, $orderBy = ['title' => 'desc']);

    /**
     * @param $question_ids
     * @return mixed
     */
    public function getSurveyQuestionsById($question_ids);

    /**
     * @param $question_id
     * @param $input_data
     * @return mixed
     */
    public function updateSurveyQuestions($question_id, $input_data);

    /**
     * @param $question_id
     * @return mixed
     */
    public function DeleteSurveyQuestion($question_id);

    /**
     * @param $filter_params
     * @param array $orderBy
     * @return mixed
     */
    public function getSurveyQuestionCount($filter_params, $orderBy = ['title' => 'desc']);

    /**
     * @param $data
     * @return mixed
     */
    public function insertSurveyQuestions($data);

    /**
     * @param integer $survey_id
     * @return collection
     */
    public function getQuestionBySurveyId($survey_id);
}
