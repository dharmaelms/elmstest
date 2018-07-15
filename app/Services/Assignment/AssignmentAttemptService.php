<?php

namespace App\Services\Assignment;

use App\Model\Assignment\Repository\IAssignmentAttemptRepository;

/**
 * Class AssignmentService
 *
 * @package App\Services\Assignment
 */
class AssignmentAttemptService implements IAssignmentAttemptService
{

    private $assignment_attempt_repo;

    public function __construct(
        IAssignmentAttemptRepository $assignment_repo
    )
    {
        $this->assignment_attempt_repo = $assignment_repo;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllAttempts($filter_params, $orderBy = ['assignment_title' => 'desc'])
    {
        return $this->assignment_attempt_repo->getAllAttempts($filter_params, $orderBy);
    }

    /**
     * {@inheritdoc}
     */
    public function getNextSequence()
    {
        return $this->assignment_attempt_repo->getNextSequence();
    }

    /**
     * {@inheritdoc}
     */
    public function insertData($data)
    {
        return $this->assignment_attempt_repo->insertData($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateData($attempt_assignment_id,  $user_id, $data)
    {
        return $this->assignment_attempt_repo->updateData($attempt_assignment_id,  $user_id, $data);
    }

    /**
    * {@inheritdoc}
    */
    public function getSubmittedUserCount($assignment_id)
    {
        return $this->assignment_attempt_repo->getSubmittedUserCount($assignment_id);
    }

    /**
    * {@inheritdoc}
    */
    public function getSummaryCount($assignment_id, $symbol, $value, $excluded_user_id)
    {
        return $this->assignment_attempt_repo->getSummaryCount($assignment_id, $symbol, $value, $excluded_user_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignmentAttemptByUserAndAssignmentIds($user_id, $assignment_ids)
    {
        return $this->assignment_attempt_repo->getAssignmentAttemptByUserAndAssignmentIds($user_id, $assignment_ids);
    }

     /**
     * {@inheritdoc}
     */
    public function getUserGradeCount($filters)
    {
        return $this->assignment_attempt_repo->getUserGradeCount($filters);
    }

     /**
     * {@inheritdoc}
     */
    public function getNotSubmittedUsersCount($assignment_id, $total_assigned_users, $excluded_user_id)
    {
        return $this->assignment_attempt_repo->getNotSubmittedUsersCount($assignment_id, $total_assigned_users, $excluded_user_id);
    }
}
