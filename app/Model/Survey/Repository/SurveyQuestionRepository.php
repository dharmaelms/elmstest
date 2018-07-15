<?php

namespace App\Model\Survey\Repository;

use App\Model\Survey\Entity\SurveyQuestion;

/**
 * Class SurveyQuestionRepository
 *
 * @package App\Model\Survey\Repository
 */
class SurveyQuestionRepository implements ISurveyQuestionRepository
{
    /**
     * {@inheritdoc}
     */
    public function getSurveyQuestions($filter_params, $orderBy)
    {
        return SurveyQuestion::filter($filter_params, $orderBy)->get();
    }

    public function getSurveyQuestionsById($question_ids)
    {
        return SurveyQuestion::getSurveyQuestionsById($question_ids);
    }

    public function updateSurveyQuestions($question_id, $input_data)
    {
        return SurveyQuestion::updateSurveyQuestions($question_id, $input_data);
    }

    public function DeleteSurveyQuestion($question_id)
    {
        return SurveyQuestion::DeleteSurveyQuestion($question_id);
    }

    public function getSurveyQuestionCount($filter_params, $orderBy)
    {
        return SurveyQuestion::filter($filter_params, $orderBy)->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestionBySurveyId($survey_id)
    {
        return SurveyQuestion::where('survey_id', '=', $survey_id)
                    ->where('status', '=', 'ACTIVE')
                    ->orderBy('order_by', 'asc')
                    ->get();
    }

    public function insertSurveyQuestions($data)
    {
        return SurveyQuestion::insertSurveyQuestions($data);
    }
}
