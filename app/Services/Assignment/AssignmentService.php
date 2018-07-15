<?php

namespace App\Services\Assignment;

use App\Model\Assignment\Repository\IAssignmentRepository;
use App\Services\Program\IProgramService;
use App\Model\Post\IPostRepository;
use Auth;

/**
 * Class AssignmentService
 *
 * @package App\Services\Assignment
 */
class AssignmentService implements IAssignmentService
{
    /**
    * @var \App\Model\Post\IPostRepository
    */
    private $post_repository;

    /**
     * @var \App\Services\Program\IProgramService
     */
    private $program_service;

    private $assignment_repo;

    public function __construct(
        IAssignmentRepository $assignment_repo,
        IProgramService $program_service,
        IPostRepository $post_repository
    )
    {
    
        $this->assignment_repo = $assignment_repo;
        $this->program_service = $program_service;
        $this->post_repository = $post_repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignments($filter_params, $orderBy = ['survey_title' => 'desc'])
    {
        return $this->assignment_repo->getAssignments($filter_params, $orderBy);
    }

    /**
     * {@inheritdoc}
     */
    public function insertAssignment($input_data)
    {
        $this->assignment_repo->insertAssignment($input_data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateAssignment($aid, $data)
    {
        $this->assignment_repo->updateAssignment($aid, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getNextSequence()
    {
        return $this->assignment_repo->getNextSequence();
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignmentByIds($assignment_ids)
    {
        return $this->assignment_repo->getAssignmentByIds($assignment_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllAssignments()
    {
        return $this->assignment_repo->getAllAssignments();
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignmentCount($filter_params, $orderBy = ['name' => 'desc'])
    {
        return $this->assignment_repo->getAssignmentCount($filter_params, $orderBy);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAssignment($assignment_id)
    {
        return $this->assignment_repo->deleteAssignment($assignment_id);
    }

    /**
     * {@inheritdoc}
     */
    public function updateAssignmentRelations($assignment_id, $relation_ary, $input_ids)
    {
        return $this->assignment_repo->updateAssignmentRelations($assignment_id, $relation_ary, $input_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function unsetAssignmentRelations($assignment_id, $relation_ary)
    {
        return $this->assignment_repo->unsetAssignmentRelations($assignment_id, $relation_ary);
    }

    /**
     * {@inheritdoc}
     */
    public function unassignPost($aid, $array_name)
    {
        $this->assignment_repo->unassignPost($aid, $array_name);
    }
    /**
     * {@inheritdoc}
     */
    public function getAllAssignmentsAssigned()
    {
        //Through user and usergroup assigned
        $assigned_assignments = $this->assignment_repo->getAllAssignmentsAssigned();
        //Through Posts assigned
        $program_assignments = $this->getProgramAssignments();
        //User created assignments
        $created_assignments = $this->assignment_repo->getAssignments(['created_by' => Auth::user()->username, 'status' => 'ACTIVE'], [])->keyBy('id')->keys()->toArray();
        $assignment['assignment_list'] = array_unique(array_merge($assigned_assignments, array_flatten(array_get($program_assignments, 'feed_assignment_list', [])), $created_assignments), SORT_REGULAR);
        return array_merge($assignment, $program_assignments);
    }
    public function getProgramAssignments()
    {
        try{
            $assignment = ['feed_assignment_list' => [], 'seq_assignments' => []];
            $program_slugs = $this->program_service->getUserProgramSlugs();
            if (!empty($program_slugs)) {
                $packet_data = $this->post_repository->getAssignmentsRelatedPosts($program_slugs)->all();
                $array_assignment_ids = $sequential_access_assignment_ids = [];
                foreach ($packet_data as $value) {
                    $feed_slug = array_get($value, 'feed_slug');
                    $assignment_ids = array_get($value, 'assignment_ids', []);
                    $array_assignment_ids[$feed_slug][] = $assignment_ids;
                    if (array_get($value, 'sequential_access') == 'yes') {
                        $sequential_access_assignment_ids[] = array_get($value, 'assignment_ids');
                    }
                }
                $feed_assignments = [];
                foreach ($array_assignment_ids as $key => $post_assignments) {
                    $feed_assignments[$key] = array_flatten($post_assignments);
                }
                $assignment['feed_assignment_list'] = $feed_assignments;
                $assignment['seq_assignments'] = array_flatten($sequential_access_assignment_ids);
            }
            return $assignment;
        } catch (PostNotFoundException $e) {
            $assignment = ['feed_assignment_list' => [], 'seq_assignments' => []];
        } catch (NoProgramAssignedException $e) {
            $assignment = ['feed_assignment_list' => [], 'seq_assignments' => []];
        } finally {
            return $assignment;
        }

    }

    /**
     * {@inheritdoc}
     */
    public function getAssignmentsByUsername($userNames = [])
    {
        return $this->assignment_repo->getAssignmentsByUsername($userNames);
    }

    public function getAssignmentFieldById($assignment_id, $field_name)
    {
        return $this->assignment_repo->getAssignmentFieldById($assignment_id, $field_name);
    }

    public function getTotalAssignedUsers($assignment_id)
    {
        return $this->assignment_repo->getTotalAssignedUsers($assignment_id);
    }
}
