<?php

namespace App\Services\Survey;

use App\Exceptions\Post\PostNotFoundException;
use App\Exceptions\Program\NoProgramAssignedException;
use App\Model\Survey\Repository\ISurveyRepository;
use App\Model\UserGroup\Repository\IUserGroupRepository;
use App\Services\Program\IProgramService;
use App\Model\Post\IPostRepository;
use App\Model\Program;
use Auth;

/**
 * Class SurveyService
 *
 * @package App\Services\Survey
 */
class SurveyService implements ISurveyService
{
    /**
     * @var \App\Model\Post\IPostRepository
     */
    private $post_repository;

    /**
     * @var \App\Model\UserGroup\Repository\IUserGroupRepository
     */
    private $usergroup_repository;

    /**
     * @var \App\Services\Program\IProgramService
     */
    private $program_service;

    private $survey_repo;

    public function __construct(
        ISurveyRepository $survey_repo,
        IUserGroupRepository $usergroup_repository,
        IProgramService $program_service,
        IPostRepository $post_repository
    ) {
        $this->survey_repo = $survey_repo;
        $this->usergroup_repository = $usergroup_repository;
        $this->program_service = $program_service;
        $this->post_repository = $post_repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getSurveys($filter_params, $orderBy = ['survey_title' => 'desc'])
    {
        return $this->survey_repo->getSurveys($filter_params, $orderBy);
    }

    /**
     * {@inheritdoc}
     */
    public function getSurveyByIds($survey_ids)
    {
        return $this->survey_repo->getSurveyByIds($survey_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function UpdateSurveyRelations($survey_id, $relation_ary, $input_ids)
    {
        return $this->survey_repo->UpdateSurveyRelations($survey_id, $relation_ary, $input_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function UnsetSurveyRelations($survey_id, $relation_ary)
    {
        return $this->survey_repo->UnsetSurveyRelations($survey_id, $relation_ary);
    }

    /**
     * {@inheritdoc}
     */
    public function DeleteSurvey($survey_id)
    {
        return $this->survey_repo->DeleteSurvey($survey_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getSurveyCount($filter_params, $orderBy = ['survey_title' => 'desc'])
    {
        return $this->survey_repo->getSurveyCount($filter_params, $orderBy);
    }

    /**
     * {@inheritdoc}
     */
    public function getSurveyFieldById($survey_id, $field_name)
    {
        return $this->survey_repo->getSurveyFieldById($survey_id, $field_name);
    }

    /**
     * {@inheritdoc}
     */
    public function pushSurveyRelations($survey_id, $field_name, $input_ids)
    {
        return $this->survey_repo->pushSurveyRelations($survey_id, $field_name, $input_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function pullSurveyRelations($survey_id, $field_name, $input_ids)
    {
        return $this->survey_repo->pullSurveyRelations($survey_id, $field_name, $input_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function insertSurvey($input_data)
    {
        $this->survey_repo->insertSurvey($input_data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateSurvey($sid, $data)
    {
        $this->survey_repo->updateSurvey($sid, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function unassignPost($sid, $array_name)
    {
        $this->survey_repo->unassignPost($sid, $array_name);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllSurveysAssigned()
    {
        $assigned_surveys = $this->survey_repo->getAllSurveysAssigned();
        $program_surveys = $this->getProgramSurveys();
        $survey['survey_list'] = array_unique(array_merge($assigned_surveys, array_flatten(array_get($program_surveys, 'feed_survey_list', []))), SORT_REGULAR);
        return array_merge($survey, $program_surveys);
    }

    public function getProgramSurveys()
    {
        $survey = ['feed_survey_list' => [], 'seq_surveys' => []];
        try {
            $program_slugs = $this->program_service->getUserProgramSlugs();
            if (!empty($program_slugs)) {
                $packet_data = $this->post_repository->getSurveysRelatedPosts($program_slugs)->all();
                $array_survey_ids = $sequential_access_survey_ids = [];
                foreach ($packet_data as $value) {
                    $feed_slug = array_get($value, 'feed_slug');
                    $survey_ids = array_get($value, 'survey_ids', []);
                    $array_survey_ids[$feed_slug][] = $survey_ids;
                    if (array_get($value, 'sequential_access') == 'yes') {
                        $sequential_access_survey_ids[] = array_get($value, 'survey_ids');
                    }
                }
                $feed_surveys = [];
                foreach ($array_survey_ids as $key => $post_surveys) {
                    $feed_surveys[$key] = array_flatten($post_surveys);
                }
                $survey['feed_survey_list'] = $feed_surveys;
                $survey['seq_surveys'] = array_flatten($sequential_access_survey_ids);
            }
        } catch (PostNotFoundException $e) {
            $survey = ['feed_survey_list' => [], 'seq_surveys' => []];
        } catch (NoProgramAssignedException $e) {
            $survey = ['feed_survey_list' => [], 'seq_surveys' => []];
        } finally {
            return $survey;
        }
    }
    /**
     * {@inheritdoc}
     */
    public function getSurveyQuestionsCount($survey_id)
    {
        $survey_ques = $this->survey_repo->getSurveyQuestionsCount($survey_id)->first();
        return count($survey_ques['survey_question']);
    }

    /**
     * {@inheritdoc}
     */
    public function getSurveysByUsername($usernames = [])
    {
        return $this->survey_repo->getSurveysByUsername($usernames);
    }
}
