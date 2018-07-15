<?php

namespace App\Services\Survey;

use App\Model\Survey\Repository\ISurveyQuestionRepository;

/**
 * Class SurveyQuestionService
 *
 * @package App\Services\Survey
 */
class SurveyQuestionService implements ISurveyQuestionService
{
    private $survey_questions;

    public function __construct(
        ISurveyQuestionRepository $survey_questions
    ) {
        $this->survey_questions = $survey_questions;
    }
    /**
     * {@inheritdoc}
     */
    public function getSurveyQuestions($filter_params, $orderBy = ['title' => 'desc'])
    {
        return $this->survey_questions->getSurveyQuestions($filter_params, $orderBy);
    }
    /**
     * {@inheritdoc}
     */
    public function getSurveyQuestionsById($question_ids)
    {
        return $this->survey_questions->getSurveyQuestionsById($question_ids);
    }
    /**
     * {@inheritdoc}
     */
    public function updateSurveyQuestions($question_id, $input_data)
    {
        return $this->survey_questions->updateSurveyQuestions($question_id, $input_data);
    }
    /**
     * {@inheritdoc}
     */
    public function DeleteSurveyQuestion($question_id)
    {
        return $this->survey_questions->DeleteSurveyQuestion($question_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getSurveyQuestionCount($filter_params, $orderBy = ['title' => 'desc'])
    {
        return $this->survey_questions->getSurveyQuestionCount($filter_params, $orderBy);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestionBySurveyId($survey_id)
    {
        return $this->survey_questions->getQuestionBySurveyId($survey_id);
    }
    /**
     * {@inheritdoc}
     */
    public function insertSurveyQuestions($data)
    {
        return $this->survey_questions->insertSurveyQuestions($data);
    }
}
