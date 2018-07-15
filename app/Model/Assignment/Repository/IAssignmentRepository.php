<?php

namespace App\Model\Assignment\Repository;

/**
 * Interface IAssignmentRepository
 *
 * @package App\Model\Assignment\Repository
 */
interface IAssignmentRepository
{
    /**
     * @param $filter_params
     * @param $orderBy
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAssignments($filter_params, $orderBy);

     /**
     * @return array
     */
    public function getAllAssignmentsAssigned();

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllAssignments();

    /**
     * @param array $input_data
     */
    public function insertAssignment($input_data);

    /**
     * @param int aid
     * @param array $data
     */
    public function updateAssignment($aid, $data);

    /**
     * @return mixed
     */
    public function getNextSequence();

    /**
     * @param int|array assignment_ids
     * @param \Illuminate\Database\Eloquent\Collection
     */
    public function getAssignmentByIds($assignment_ids);

    /**
     * @param array $filter_params
     * @param array $orderBy
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAssignmentCount($filter_params, $orderBy);

    /**
     * @param int $assignment_id
     */
    public function deleteAssignment($assignment_id);

    /**
     * @param int $assignment_id
     * @param array $relation_ary
     * @param array $input_ids
     */
    public function updateAssignmentRelations($assignment_id, $relation_ary, $input_ids);

    /**
     * @param int $assignment_id
     * @param array $relation_ary
     */
    public function unsetAssignmentRelations($assignment_id, $relation_ary);

    /**
     * @param int aid
     * @param array $arrname
     */
    public function unassignPost($aid, $arrname);

    /**
     * Method to get all Assignments  created by username
     *
     * @param array $usernames
     * @return Object
     */
    public function getAssignmentsByUsername($usernames = []);

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
