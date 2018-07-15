<?php

namespace App\Model\Survey\Repository;

use App\Model\Survey\Entity\Survey;
use App\Model\UserGroup\Repository\IUserGroupRepository;
use App\Model\Post\IPostRepository;
use Auth;
use Carbon;

/**
 * Class SurveyRepository
 *
 * @package App\Model\Survey\Repository
 */
class SurveyRepository implements ISurveyRepository
{
    /**
     * @var App\Model\UserGroup\Repository\IUserGroupRepository
     */
    private $usergroup_repo;

    /**
     * @var App\Model\Post\IPostRepository
     */
    private $post_repository;

    public function __construct(
        IUserGroupRepository $usergroup_repo,
        IPostRepository $post_repository
    )
    {
        $this->usergroup_repo = $usergroup_repo;
        $this->post_repository = $post_repository;
    }
    /**
     * {@inheritdoc}
     */
    public function getSurveys($filter_params, $orderBy)
    {
        return Survey::filter($filter_params, $orderBy)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getSurveyByIds($survey_ids)
    {
        return Survey::getSurveyByIds($survey_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function UpdateSurveyRelations($survey_id, $relation_ary, $input_ids)
    {
        return Survey::UpdateSurveyRelations($survey_id, $relation_ary, $input_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function UnsetSurveyRelations($survey_id, $relation_ary)
    {
        return Survey::UnsetSurveyRelations($survey_id, $relation_ary);
    }

    /**
     * {@inheritdoc}
     */
    public function DeleteSurvey($survey_id)
    {
        return Survey::DeleteSurvey($survey_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getSurveyCount($filter_params, $orderBy)
    {
        return Survey::filter($filter_params, $orderBy)->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getSurveyFieldById($survey_id, $field_name)
    {
        return Survey::getSurveyFieldById($survey_id, $field_name);
    }

    /**
     * {@inheritdoc}
     */
    public function pushSurveyRelations($survey_id, $field_name, $input_ids)
    {
        return Survey::pushSurveyRelations($survey_id, $field_name, $input_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function pullSurveyRelations($survey_id, $field_name, $input_ids)
    {
        return Survey::pullSurveyRelations($survey_id, $field_name, $input_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function insertSurvey($input_data)
    {
        Survey::insertSurvey($input_data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateSurvey($sid, $sdata)
    {
        Survey::updateSurvey($sid, $sdata);
    }

    /**
     * {@inheritdoc}
     */
    public function unassignPost($sid, $arrname)
    {
        Survey::unassignPost($sid, $arrname);
    }

    /**
     * {@inheritdoc}
     */
    public function getSurveyQuestionsCount($survey_id)
    {
        return Survey::getSurveyByIds($survey_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getSurveysByUsername($usernames = [])
    {
        return Survey::whereIn('created_by', $usernames)
                ->where('status', '=', 'ACTIVE')
                ->get();
    }
    /**
     * {@inheritdoc}
     */
    public function getAllSurveysAssigned()
    {
       if (!is_admin_role(Auth::user()->role)) {
            $assigned_surveys = array_get(Auth::user(), 'attributes.survey', []);
            $assigned_ug = array_get(Auth::user(), 'attributes.relations.active_usergroup_user_rel', []);
            if (!empty($assigned_ug)) {
                $usergroup_surveys = $this->usergroup_repo->get(['ugid' => $assigned_ug])->map(function ($group) {
                    return array_get($group, 'attributes.survey', []);
                });
                $assigned_surveys = array_merge($assigned_surveys, array_flatten(array_filter($usergroup_surveys->all())));
            }
        }else {
            $assigned_surveys = $this->getSurveys(['status' => 'ACTIVE'], '')->pluck('id')->all();
        }
        return $assigned_surveys;
    }

    /**
     * {@inheritdoc}
     */
    public function getIncompleteSurveysBySurveyId($page, $limit, $survey_ids)
    {
        return Survey::whereIn('id', $survey_ids)
                    ->where('status', 'ACTIVE')
                    ->where('end_time', '>=', time())
                    ->skip((int)$page)
                    ->limit((int)$limit)
                    ->get();
    }
}
