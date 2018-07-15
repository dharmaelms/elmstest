<?php

namespace App\Model\Assignment\Repository;


/**
 * Interface IAssignmentAttemptRepository
 *
 * @package App\Model\Assignment\Repository
 */
Interface IAssignmentAttemptRepository
{
    /**
     * @param $filter_params
     * @param $orderBy
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllAttempts($filter_params, $orderBy);

    /**
     * @return int
     */
    public function getNextSequence();

    /**
     * @param array $data
     * @return boolean
     */
    public function insertData($data);

    /**
     * @param int $attempt_assignment_id
     * @param int $user_id
     * @param array $data
     * @return boolean
     */
    public function updateData($attempt_assignment_id,  $user_id, $data);
	/**
     * @param $assignment_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSubmittedUserCount($assignment_id);

    /**
     * @param $assignment_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSummaryCount($assignment_id, $symbol, $value, $excluded_user_id);

    /**
     * @param array $user_id
     * @param array $assignment_ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAssignmentAttemptByUserAndAssignmentIds($user_id, $assignment_ids);

    /**
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserGradeCount($filters);

    /**
     * @param array $assignment_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNotSubmittedUsersCount($assignment_id, $total_assigned_users, $excluded_user_id);
}
