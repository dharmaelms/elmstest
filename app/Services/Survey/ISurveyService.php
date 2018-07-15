<?php

namespace App\Services\Survey;

/**
 * Interface ISurveyService
 * @package App\Services\Survey
 */
interface ISurveyService
{
    /**
     * param array $filter_params
     * @param $filter_params
     * @param $orderBy
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSurveys($filter_params, $orderBy);

    /**
     * @param $survey_ids
     * @return array $survey_ids
     */
    public function getSurveyByIds($survey_ids);

    /**
     * @param $survey_id
     * @param $relation_ary
     * @param $input_ids
     * @return mixed
     */
    public function UpdateSurveyRelations($survey_id, $relation_ary, $input_ids);

    /**
     * @param $survey_id
     * @param $relation_ary
     * @return mixed
     */
    public function UnsetSurveyRelations($survey_id, $relation_ary);

    /**
     * @param $survey_id
     * @return mixed
     */
    public function DeleteSurvey($survey_id);

    /**
     * @param $filter_params
     * @param $orderBy
     * @return mixed
     */
    public function getSurveyCount($filter_params, $orderBy = ['survey_title' => 'desc']);

    /**
     * @param $survey_id
     * @param $field_name
     * @return mixed
     */
    public function getSurveyFieldById($survey_id, $field_name);

    /**
     * @param $survey_id
     * @param $field_name
     * @param $input_ids
     * @return mixed
     */
    public function pushSurveyRelations($survey_id, $field_name, $input_ids);

    /**
     * @param $survey_id
     * @param $field_name
     * @param $input_ids
     * @return mixed
     */
    public function pullSurveyRelations($survey_id, $field_name, $input_ids);

    /**
     * @param $input_data
     * @return mixed
     */
    public function insertSurvey($input_data);

    /**
     * @param $sid
     * @param $data
     * @return mixed
     */
    public function updateSurvey($sid, $data);

    /**
     * @param $sid
     * @param $array_name
     * @return mixed
     */
    public function unassignPost($sid, $array_name);

    /**
     * @param $survey_id
     */
    public function getSurveyQuestionsCount($survey_id);

     /**
     * Method to get all Surveys created by username
     *
     * @param array $usernames
     * @return array
     */
    public function getSurveysByUsername($usernames = []);

    /**
     * @return array
     */
    public function getAllSurveysAssigned();
}
