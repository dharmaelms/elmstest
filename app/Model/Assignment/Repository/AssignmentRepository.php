<?php

namespace App\Model\Assignment\Repository;

use App\Model\Assignment\Entity\Assignment;
use App\Model\UserGroup\Repository\IUserGroupRepository;
use App\Model\Post\IPostRepository;
use App\Model\Program\IProgramRepository;
use Auth;

/**
 * Class AssignmentRepository
 *
 * @package App\Model\Assignment\Repository
 */
class AssignmentRepository implements IAssignmentRepository
{
    /**
     * @var \App\Model\UserGroup\Repository\IUserGroupRepository
     */
    private $usergroup_repo;

    /**
     * @var \App\Model\Post\IPostRepository
     */
    private $post_repository;

    /**
     * @var \App\Model\Program\IProgramRepository
     */
    private $program_repo;

    public function __construct(
        IUserGroupRepository $usergroup_repo,
        IPostRepository $post_repository,
        IProgramRepository $program_repo
    ) {
    
        $this->usergroup_repo = $usergroup_repo;
        $this->post_repository = $post_repository;
        $this->program_repo = $program_repo;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignments($filter_params, $orderBy)
    {
        return Assignment::filter($filter_params, $orderBy)->get();
    }

        /**
     * {@inheritdoc}
     */
    public function getAllAssignmentsAssigned()
    {
       if (!is_admin_role(Auth::user()->role)) {
            $assigned_assignments = array_get(Auth::user(), 'attributes.assignment', []);
            $assigned_ug = array_get(Auth::user(), 'attributes.relations.active_usergroup_user_rel', []);
            if (!empty($assigned_ug)) {
                $usergroup_assignments = $this->usergroup_repo->get(['ugid' => $assigned_ug])->map(function ($group) {
                    return array_get($group, 'attributes.assignment', []);
                });
                $assigned_assignments = array_merge($assigned_assignments, array_flatten(array_filter($usergroup_assignments->all())));
            }
        }else {
            $assigned_assignments = $this->getAssignments(['status' => 'ACTIVE'], '')->pluck('id')->all();
        }
        return $assigned_assignments;
    }
    /**
     * {@inheritdoc}
     */
    public function insertAssignment($input_data)
    {
        $input_data['id'] = Assignment::getNextSequence();
        Assignment::insert($input_data);
    }
    /**
     * {@inheritdoc}
     */
    public function getAllAssignments()
    {
        return Assignment::filter()->get();
    }

    /**
     * {@inheritdoc}
     */
    public function updateAssignment($aid, $data)
    {
        Assignment::where('id', '=', (int)$aid)
                ->update($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getNextSequence()
    {
        return Assignment::getNextSequence();
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignmentByIds($assignment_ids)
    {
        return Assignment::getAssignmentByIds($assignment_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignmentCount($filter_params, $orderBy)
    {
        return Assignment::filter($filter_params, $orderBy)->count();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAssignment($assignment_id)
    {
        return Assignment::where('id', '=', (int)$assignment_id)
                    ->update(
                        [
                            'status' => 'DELETED',
                            'updated_at' => time()
                        ]
                    );

    }

    /**
     * {@inheritdoc}
     */
    public function updateAssignmentRelations($assignment_id, $relation_ary, $input_ids)
    {
        return Assignment::updateAssignmentRelations($assignment_id, $relation_ary, $input_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function unsetAssignmentRelations($assignment_id, $relation_ary)
    {
        return Assignment::unsetAssignmentRelations($assignment_id, $relation_ary);
    }

    /**
     * {@inheritdoc}
     */
    public function unassignPost($sid, $arrname)
    {
        Assignment::unassignPost($sid, $arrname);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignmentsByUsername($usernames = [])
    {
        return Assignment::whereIn('created_by', $usernames)
                ->where('status', '=', 'ACTIVE')
                ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignmentFieldById($assignment_id, $field_name)
    {
        if (is_array($assignment_id)) {
            return Assignment::whereIn("id", $assignment_id)->get($field_name);
        } else {
            return Assignment::where("id", '=', (int)$assignment_id)->get($field_name);
        }
    }

    public function getTotalAssignedUsers($assignment_id)
    {
        $relations = Assignment::where('id', '=', (int)$assignment_id)
                                ->where(function ($q) {
                                    $q->Where('users', 'exists', true)
                                    ->orWhere('usergroups', 'exists', true)
                                    ->orWhere('post_id', 'exists', true);
                                })
                                ->get(['users', 'usergroups', 'post_id'])->first();
        if (!empty($relations)) {
            $c_user = $c_ug = $ug_uids = [];
            /* To get all the assignment_rel */
            $users = isset($relations->users) ? $relations->users : [];
            $usergroups = isset($relations->usergroups) ? $relations->usergroups : [];
            $post_id = !is_null($relations->post_id) ? [$relations->post_id] : [];
            /* To get all the assignment's channel rel */
            if (!empty($post_id)) {
                $channel_slug = $this->post_repository->getFeedSlugsByPacketIds($post_id);
                $channel_relations = $this->program_repo->getProgramsBySlugs($channel_slug, ['relations'])->first();
                $c_user =array_get($channel_relations->relations, 'active_user_feed_rel', []);
                $c_ug = array_get($channel_relations->relations, 'active_usergroup_feed_rel', []);
            }
            /* To get all the assignment's usergroups rel */
            $usergroups = array_unique(array_merge($usergroups, $c_ug));
            if(!empty($usergroups)) {
                $ug_uids = $this->usergroup_repo->getUsersByUserGroupIds($usergroups);
                $ug_uids = array_flatten($ug_uids);
            }
            /* To get all the users which are assigned directly and indirectly */
            $users = array_unique(array_filter(array_merge($users, $c_user, $ug_uids)));
            $count = count($users);
        } else {
            $count = 0;
        }
        return $count;
    }
}
