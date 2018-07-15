<?php
namespace App\Model\Assignment\Repository;

use App\Model\Assignment\Entity\AssignmentAttempt;
use App\Enums\Assignment\SubmissionType as ST;

/**
 * Class AssignmentAttemptRepository
 *
 * @package App\Model\Assignment\Repository
 */
class AssignmentAttemptRepository implements IAssignmentAttemptRepository
{
    /**
     * {@inheritdoc}
     */
    public function getAllAttempts($filter_params, $orderBy)
    {
        return AssignmentAttempt::filter($filter_params, $orderBy)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getNextSequence()
    {
        return AssignmentAttempt::getNextSequence();
    }

    /**
     * {@inheritdoc}
     */
    public function insertData($data = [])
    {
        return AssignmentAttempt::insert($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateData($attempt_assignment_id,  $user_id, $data = [])
    {
        return AssignmentAttempt::where('id', '=', $attempt_assignment_id)->where('user_id', '=', (int)$user_id)->update($data);
    }

    /**
    * {@inheritdoc}
    */
    public function getSubmittedUserCount($assignment_id)
    {
        return AssignmentAttempt::where('assignment_id', '=', $assignment_id)->count();
    }
    /**
    * {@inheritdoc}
    */
    public function getSummaryCount($assignment_id, $symbol, $value, $excluded_user_id)
    {
        return AssignmentAttempt::where('assignment_id', '=', (int)$assignment_id)->where('submission_status', $symbol, $value)->whereNotIn('user_id', $excluded_user_id)->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignmentAttemptByUserAndAssignmentIds($user_id, $assignment_ids)
    {
        return AssignmentAttempt::where('user_id', '=', $user_id)
            ->whereIn('assignment_id', $assignment_ids)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserGradeCount($filters)
    {
        return AssignmentAttempt::filter($filters)->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getNotSubmittedUsersCount($assignment_id, $total_assigned_users, $excluded_user_id)
    {
        $submitted_users = $this->getSummaryCount($assignment_id, "!=", ST::SAVE_AS_DRAFT, $excluded_user_id);
        $not_submitted_users = $total_assigned_users - $submitted_users;
        return $not_submitted_users;
    }
}
