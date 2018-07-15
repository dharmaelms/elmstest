<?php

namespace App\Model\Survey\Repository;

/**
 * Interface ISurveyRepository
 *
 * @package App\Model\Survey\Repository
 */
interface ISurveyRepository
{
    /**
     * @param array $filter_params
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSurveys($filter_params, $orderBy);

    /**
     * @return array $survey_ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSurveyByIds($survey_ids);

    /**
     * @param int $survey_id
     * @param array $relation_ary
     * @param array $input_ids
     */
    public function UpdateSurveyRelations($survey_id, $relation_ary, $input_ids);

    /**
     * @param int $survey_id
     * @param array $relation_ary
     */
    public function UnsetSurveyRelations($survey_id, $relation_ary);

    /**
     * @param int $survey_id
     */
    public function DeleteSurvey($survey_id);

    /**
     * @param array $filter_params
     * @param array $orderBy
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSurveyCount($filter_params, $orderBy);

    /**
     * @param int survey_id
     * @param array $field_name
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSurveyFieldById($survey_id, $field_name);

    /**
     * @param int survey_id
     * @param array $field_name
     * @param array input_ids
     */
    public function pushSurveyRelations($survey_id, $field_name, $input_ids);

    /**
     * @param int survey_id
     * @param array $field_name
     * @param array $input_ids
     */
    public function pullSurveyRelations($survey_id, $field_name, $input_ids);

    /**
     * @param array $input_data
     */
    public function insertSurvey($input_data);

    /**
     * @param int sid
     * @param array $sdata
     */
    public function updateSurvey($sid, $sdata);

    /**
     * @param int sid
     * @param array $arrname
     */
    public function unassignPost($sid, $arrname);

    /**
     * @param int survey_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSurveyQuestionsCount($survey_id);

    /**
     * Method to get all Surveys  created by username
     *
     * @param array $usernames
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSurveysByUsername($usernames = []);

    /**
     * @return array
     */
    public function getAllSurveysAssigned();

    /**
     * @param int $page
     * @param int $limit
     * @param array $survey_ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getIncompleteSurveysBySurveyId($page, $limit, $survey_ids);
}
