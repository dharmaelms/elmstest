<?php

namespace App\Services\Assignment;


/**
 * Interface IAssignmentService
 *
 * @package App\Services\Assignment
 */
Interface IAssignmentService
{
    /**
     * @param $filter_params
     * @param $orderBy
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAssignments($filter_params, $orderBy = ['survey_title' => 'desc']);

    /**
     * @return array
     */
    public function getAllAssignmentsAssigned();

    /**
     * @param $input_data
     * @return mixed
     */
    public function insertAssignment($input_data);

    /**
     * @param $aid
     * @param $data
     * @return mixed
     */
    public function updateAssignment($aid, $data);

    public function getNextSequence();

    /**
     * @param $assignment_ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAssignmentByIds($assignment_ids);

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllAssignments();

    /**
     * @param $filter_params
     * @param $orderBy
     * @return mixed
     */
    public function getAssignmentCount($filter_params, $orderBy = ['name' => 'desc']);

    /**
     * @param $assignment_id
     * @return mixed
     */
    public function deleteAssignment($assignment_id);

    /**
     * @param $assignment_id
     * @param $relation_ary
     * @param $input_ids
     * @return mixed
     */
    public function updateAssignmentRelations($assignment_id, $relation_ary, $input_ids);

    /**
     * @param $assignment_id
     * @param $relation_ary
     * @return mixed
     */
    public function unsetAssignmentRelations($assignment_id, $relation_ary);

    /**
     * @param $aid
     * @param $array_name
     * @return mixed
     */
    public function unassignPost($aid, $array_name);

    /**
     * Method to get all Assignments created by username
     *
     * @param array $userNames
     * @return array
     */
    public function getAssignmentsByUsername($userNames = []);

    /**
     * @param int assignment_id
     * @param array $field_name
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAssignmentFieldById($assignment_id, $field_name);

    /**
     * @param int assignment_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTotalAssignedUsers($assignment_id);
}
