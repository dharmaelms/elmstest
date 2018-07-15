<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Module\Module as ModuleEnum;
use App\Enums\Program\ChannelPermission;
use App\Enums\Program\ElementType;
use App\Enums\Survey\SurveyType;
use App\Enums\RolesAndPermissions\Contexts;
use App\Enums\RolesAndPermissions\PermissionType;
use App\Enums\Survey\SurveyPermission;
use App\Events\Elastic\Items\ItemsAdded;
use App\Events\Elastic\Survey\SurveyAssigned;
use App\Http\Controllers\AdminBaseController;
use App\Model\Common;
use App\Model\Survey\Entity\SurveyQuestion;
use App\Services\Post\IPostService;
use App\Services\Program\IProgramService;
use App\Services\Survey\ISurveyAttemptDataService;
use App\Services\Survey\ISurveyQuestionService;
use App\Services\Survey\ISurveyService;
use App\Services\User\IUserService;
use App\Services\UserGroup\IUserGroupService;
use Auth;
use Carbon;
use Exception;
use Illuminate\Http\Request;
use Input;
use Log;
use Timezone;
use URL;
use Validator;
use App\Model\Program;
use File;

/**
 * Class SurveyController
 * @package App\Http\Controllers\Admin
 */
class SurveyController extends AdminBaseController
{
    /**
     * @var ISurveyAttemptDataService
     */
    private $survey_attempt_data;

    /**
     * @var ISurveyQuestionService
     */
    private $survey_questions;

    /**
     * @var IUserService
     */
    private $user_service;

    /**
     * @var string
     */
    protected $layout = 'admin.theme.layout.master_layout';

    /**
     * @var ISurveyService
     */
    private $survey_service;

    /**
     * @var IUserGroupService
     */
    private $usergroup_service;

    /**
     * @var IProgramService
     */
    private $program_service;

    /**
     * @var IPostService
     */
    private $post_service;
    /**
     * SurveyController constructor.
     * @param Request $request
     * @param ISurveyAttemptDataService $survey_attempt_data_service
     * @param ISurveyQuestionService $survey_questions
     * @param ISurveyService $survey_service
     * @param IUserService $user_service
     * @param IUserGroupService $usergroup_service
     * @param IProgramService $program_service
     * @param IPostService $post_service
     */
    public function __construct(
        Request $request,
        ISurveyAttemptDataService $survey_attempt_data_service,
        ISurveyQuestionService $survey_questions,
        ISurveyService $survey_service,
        IUserService $user_service,
        IUserGroupService $usergroup_service,
        IProgramService $program_service,
        IPostService $post_service
    ) {
        parent::__construct();
        $input = $request->all();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request->merge($input);
        $this->survey_attempt_data = $survey_attempt_data_service;
        $this->survey_questions = $survey_questions;
        $this->user_service = $user_service;
        $this->survey_service = $survey_service;
        $this->usergroup_service = $usergroup_service;
        $this->program_service = $program_service;
        $this->post_service = $post_service;
        $this->theme = config('app.portal_theme_name');
        $this->theme_path = 'portal.theme.' . $this->theme;
    }

    /**
     *
     */
    public function getIndex()
    {
        $this->getListSurvey();
    }

    /**
     *
     */
    public function getListSurvey()
    {
        $permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::SURVEY,
            PermissionType::ADMIN,
            SurveyPermission::LIST_SURVEY,
            null,
            null,
            true
        );
        $has_list_survey_permission = get_permission_flag($permission_data_with_flag);
        if (!$has_list_survey_permission) {
            return parent::getAdminError();
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/survey.manage_survey') => ''
        ];
        $list_survey_permission_data = get_permission_data($permission_data_with_flag);
        $filter_params = has_system_level_access($list_survey_permission_data)?
                [] : ["in_ids" => get_instance_ids($list_survey_permission_data, Contexts::PROGRAM)];

        //Role based access
        $feeds = $this->program_service->getAllProgramByIDOrSlug('content_feed', '', $filter_params);
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = 'Manage Survey';
        $this->layout->pageicon = 'fa fa-file-text';
        $this->layout->pagedescription = 'List of Survey';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'survey')
            ->with('submenu', 'manage survey');
        $this->layout->content = view('admin.theme.survey.list_survey')
            ->with('feeds', $feeds);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListSurveyAjax(Request $request)
    {
        $list_survey_permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::SURVEY,
            PermissionType::ADMIN,
            SurveyPermission::LIST_SURVEY,
            null,
            null,
            true
        );
        $list_survey_permission_data = get_permission_data($list_survey_permission_data_with_flag);
        if (!has_system_level_access($list_survey_permission_data)) {
                $filters["id"] = get_user_accessible_elements(
                    $list_survey_permission_data,
                    ElementType::SURVEY
                );
        }

        $has_list_survey_permission = get_permission_flag($list_survey_permission_data_with_flag);
        if (!$has_list_survey_permission) {
            return response()->json(
                [
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ]
            );
        }

        $survey_list = collect([]);
        $filters = [
            "search" => isset($request->search['value']) ? $request->search['value'] : '',
            "status" => 'ACTIVE',
        ];
        $all_survey_count = $this->survey_service->getSurveyCount($filters);
        if ($request->has('start') && $request->has('length')) {
            $filters += [
                'start' => $request->start,
                'limit' => $request->length,
            ];
            $order_by = $request->order;
            $orderByArray = ['survey_title' => 'desc'];
            if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
                if ($order_by[0]['column'] == '1') {
                    $orderByArray = ['survey_title' => $order_by[0]['dir']];
                }
                if ($order_by[0]['column'] == '6') {
                    $orderByArray = ['created_at' => $order_by[0]['dir']];
                }
                if ($order_by[0]['column'] == '7') {
                    $orderByArray = ['end_time' => $order_by[0]['dir']];
                }
                $surveys = $this->survey_service->getSurveys($filters, $orderByArray);
                $attempted_surveys = $this->survey_attempt_data->getAttemptedSurveysBySurveyIds($surveys->pluck('id')->all());
                $survey_list = $surveys->transform(function ($survey) use (&$filters, &$order_by, &$attempted_surveys) {
                    $post_count = 0;
                    $question_count = count($survey->survey_question);
                    $survey_title = '<div>'.$survey->survey_title.'</div>';
                    if (has_admin_permission(ModuleEnum::SURVEY, SurveyPermission::LIST_SURVEY_QUESTION)) {
                        if ($question_count <= 0) {
                            $survey_question = '<a style="cursor:pointer" class="badge badge-grey show-tooltip survey-question" href="' . URL::to('cp/survey/survey-questions/' .$survey->id). '">0</a>';
                        } else {
                            $survey_question = '<a style="cursor:pointer" class="badge badge-success show-tooltip survey-question" href="' . URL::to('cp/survey/survey-questions/' .$survey->id). '">' .$question_count. '</a>';
                        }
                    } else {
                        $survey_question = '<a style="cursor:not-allowed" class="show-tooltip ajax" title="' . trans("admin/survey.no_perm_to_assign_survey_ques") . '">' .$question_count.'</a>';
                    }

                    $survey_post_data = (is_null($survey->post_id)) ? [] : [$survey->post_id];
                    if (has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_POST)) {
                        if ((isset($survey->users) && !empty($survey->users) ) || (isset($survey->usergroups)  && !empty($survey->usergroups))) {
                            $post_rel = "<a style='cursor:not-allowed' href='#' title=\"" . trans('admin/survey.cant_assign_post') . "\"  class='badge show-tooltip " . ((count($survey_post_data) > 0) ? 'badge-success' : 'badge-grey') . "'>" . count($survey_post_data) . "</a>";
                        } else {
                            $post_title = '';
                            $post_count = 0;
                            if (!empty($survey->post_id)) {
                                try {
                                    $post_id = $survey->post_id;
                                    $pack_detail = $this->post_service->getPostByID($post_id, 'packet_id')->get()->first();
                                    if (!is_null($pack_detail)) {
                                        $post_count = 1;
                                        $post_title = $pack_detail->packet_title;
                                        $channel_name = Program::getCFTitle($pack_detail->feed_slug);
                                    }
                                } catch (Exception $e) {
                                    $post_count = 0;
                                    $channel_name = '';
                                    $post_title = '';
                                    Log::error('Post not found :: '. $e->getMessage());
                                }
                            }

                            if ($post_count >= 1) {
                                $post_rel ="<a href='#' style='cursor:not-allowed' class='badge badge-success' data-key='" . $survey->id . "' data-info='feed' data-text='Manage " . trans('admin/survey.posts') . " for " . htmlentities($survey->survey_title, ENT_QUOTES) . "' data-json='" . json_encode($survey_post_data) . "' title = '".htmlentities($post_title, ENT_QUOTES)."' >" . $post_count ."</a> || <a href='". URL::to('/cp/survey/unassign-post/' .$survey->id. '/unassign') ."'' class = 'survey-post-unassign' data-key='" . $survey->id . "' data-postname='" . $post_title . "' data-channelname='" . $channel_name . "'>Un-assign</a>";
                            } else {
                                $post_rel = "<a href='' class='survey-post-assign badge " . (($post_count > 0) ? "badge-success" : "badge-grey") . "' data-key='" . $survey->id . "' data-info='feed' data-text='Manage " . trans('admin/survey.posts') . " for " . htmlentities($survey->survey_title, ENT_QUOTES) . "' data-json='" . json_encode($survey_post_data) . "' title = '".htmlentities($post_title, ENT_QUOTES)."' >" . $post_count ."</a>";
                            }
                        }
                    } else {
                        $post_rel = "<a style='cursor:not-allowed' href='#' title=\"" . trans('admin/survey.no_per_to_assign_posts') . "\"  class='badge show-tooltip " . (($post_count > 0) ? 'badge-success' : 'badge-grey') . "'>" . $post_count . "</a>";
                    }

                    if (has_admin_permission(ModuleEnum::SURVEY, SurveyPermission::SURVEY_ASSIGN_USER)) {
                        if (!is_null($survey->post_id)) {
                            $users = "<a style='cursor:not-allowed' href='#' title=\"" . trans('admin/survey.cant_assign_users') . "\"  class='badge show-tooltip " . ((count($survey->users) > 0) ? 'badge-success' : 'badge-grey') . "'>" . count($survey->users) . "</a>";
                        } else {
                            $users = '<a href="' . URL::to("/cp/usergroupmanagement?filter=ACTIVE&view=iframe&from=survey&relid=" . $survey->id) . '" class="survey-assign badge ' . ((count($survey->users) > 0) ? "badge-success" : "badge-grey") . '" data-key="' . $survey->id . '" data-info="user" data-text="Manage Users for ' . htmlentities($survey->survey_title, ENT_QUOTES) . '" data-json="' . json_encode($survey->users) . '">' . count($survey->users) . '</a>';
                        }
                    } else {
                        $users = "<a style='cursor:not-allowed' href='#' title=\"" . trans('admin/survey.no_per_to_assign_users') . "\"  class='badge show-tooltip " . ((count($survey->users) > 0) ? 'badge-success' : 'badge-grey') . "'>" . count($survey->users) . "</a>";
                    }
                    if (has_admin_permission(ModuleEnum::SURVEY, SurveyPermission::SURVEY_ASSIGN_USER_GROUP)) {
                        if (!is_null($survey->post_id)) {
                            $usergroups = "<a style='cursor:not-allowed' href='#' title=\"" . trans('admin/survey.cant_assign_usergroups') . "\"  class='badge show-tooltip " . ((count($survey->usersgroups) > 0) ? 'badge-success' : 'badge-grey') . "'>" . count($survey->usersgroups) . "</a>";
                        } else {
                            $usergroups = '<a href="' . URL::to("/cp/usergroupmanagement/user-groups?filter=ACTIVE&view=iframe&from=survey&relid=" . $survey->id) . '" class="survey-assign badge ' . ((count($survey->usergroups) > 0) ? "badge-success" : "badge-grey") . '" data-key="' . $survey->id . '" data-info="usergroup" data-text="Manage User Groups for ' . htmlentities($survey->survey_title, ENT_QUOTES) . '" data-json="' . json_encode($survey->usergroups) . '">' . count($survey->usergroups) . '</a>';
                        }
                    } else {
                        $usergroups = "<a style='cursor:not-allowed' href='#' title=\"" . trans('admin/survey.no_per_to_assign_usersgroups') . "\"  class='badge show-tooltip " . ((count($survey->usergroups) > 0) ? 'badge-success' : 'badge-grey') . "'>" . count($survey->usersgroups) . "</a>";
                    }

                /* Action buttons */
                        /* EDIT */
                    if (has_admin_permission(ModuleEnum::SURVEY, SurveyPermission::EDIT_SURVEY)) {
                        $edit = '<a class="btn btn-circle show-tooltip editsurvey" title="' . trans('admin/survey.edit') . '" href="' . URL::to('cp/survey/edit-survey/' . $survey->id) . '?start=' . $filters['start'] . '&limit=' . $filters['limit'] . '&search=' . $filters['search'] . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-edit"></i></a>';
                    } else {
                        $edit = '<a style="cursor:not-allowed" class="btn btn-circle show-tooltip ajax" title="' . trans("admin/survey.no_perm_to_edit") . '"><i class="fa fa-edit"></i></a>';
                    }
                    /* DELETE*/
                    if (has_admin_permission(ModuleEnum::SURVEY, SurveyPermission::DELETE_SURVEY)) {
                        if ((isset($survey->users) && !empty($survey->users)) ||
                            (isset($survey->usergroups) && !empty($survey->usergroups)) ||
                            !is_null($survey->post_id)) {
                            $delete = '<a class="btn btn-circle show-tooltip ajax" title="' . trans("admin/survey.survey_in_use") . '"><i class="fa fa-trash-o"></i></a>';
                        } else {
                            $delete = '<a class="btn btn-circle show-tooltip deletesurvey" title="' . trans('admin/survey.delete') . '" href="' . URL::to('cp/survey/delete-survey/' . $survey->id) . '?start=' . $filters['start'] . '&limit=' . $filters['limit'] . '&search=' . $filters['search'] . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-trash-o"></i></a>';
                        }
                    } else {
                        $delete = '<a style="cursor:not-allowed" class="btn btn-circle show-tooltip ajax" title="' . trans("admin/survey.no_perm_to_delete") . '"><i class="fa fa-trash-o"></i></a>';
                    }
                    /* REPORT*/
                    $report = "";
                    $export = "";
                    if (has_admin_permission(ModuleEnum::SURVEY, SurveyPermission::REPORT_SURVEY) && $attempted_surveys->has($survey->id) && $question_count >= 1) {
                        $report = '<a class="btn btn-circle show-tooltip reportsurvey" title="' . trans('admin/survey.report') . '" href="' . URL::to('cp/survey/survey-report/' . $survey->id) . ' "><i class="fa fa-bar-chart-o"></i></a>';
                    }
                    /* EXPORT */
                    if (has_admin_permission(ModuleEnum::SURVEY, SurveyPermission::EXPORT_SURVEY)) {
                        $export = '<a class="btn btn-circle show-tooltip exportsurvey" title="' . trans('admin/survey.export') . '" href="' . URL::to('cp/survey/export-survey/' . $survey->id) . '?start=' . $filters['start'] . '&limit=' . $filters['limit'] . '&search=' . $filters['search'] . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-download"></i></a>';
                    }
                    /* Action buttons */

                    /* Data construction for final array*/
                    $each_survey = [
                        'survey_title' => $survey_title,
                        'survey_questions' => $survey_question,
                        'posts' => $post_rel,
                        'users' => $users,
                        'usergroups' => $usergroups,
                        'survey_id' => $survey->id,
                        'created_at' => Timezone::convertFromUTC('@' . $survey->created_at, Auth::user()->timezone, config('app.date_format')),
                        'end_time' => Timezone::convertFromUTC('@' . $survey->end_time, Auth::user()->timezone, config('app.date_format')),
                        'actions' => $edit. $delete. $report,//. $export,// Since we its completed
                    ];
                    return $each_survey;
                });
            }
        }
        return response()->json([
            'recordsTotal' => $survey_list->count(),
            'recordsFiltered' => $all_survey_count,
            'data' => $survey_list
        ]);
    }

    /**
     *
     */
    public function getAddSurvey()
    {
        if (!has_admin_permission(ModuleEnum::SURVEY, SurveyPermission::ADD_SURVEY)) {
            return parent::getAdminError();
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/survey.manage_survey') => 'survey/list-survey',
            trans('admin/survey.add') => ''
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = 'Add Survey';
        $this->layout->pageicon = 'fa fa-file-text';
        $this->layout->pagedescription = 'Add Survey';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'survey')
            ->with('submenu', 'manage survey');
        $this->layout->content = view('admin.theme.survey.add_survey');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse|void
     */
    public function postAddSurvey(Request $request)
    {
        if (!has_admin_permission(ModuleEnum::SURVEY, SurveyPermission::ADD_SURVEY)) {
            return parent::getAdminError();
        }
        $rules = [
            'survey_title' => 'required|max:512',
            'start_date' => 'required',
            'end_date' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
        ];
        $messages = [
            'start_date.required' => trans('admin/survey.start_date_required'),
            'survey_title.required' => trans('admin/survey.survey_title_required'),
            'end_date.required' => trans('admin/survey.end_date_required'),
            'start_time.required' => trans('admin/survey.start_time_required'),
            'end_time.required' => trans('admin/survey.end_time_required'),
        ];
        $validation = Validator::make($request->all(), $rules, $messages);
        if ($validation->fails()) {
            return redirect('cp/survey/add-survey')
                ->withInput()
                ->withErrors($validation);
        } else {
            $start_time = $end_time = 0;
            // Survey start time
            if ($request->has('start_date')) {
                $start_time = Timezone::convertToUTC($request->start_date, Auth::user()->timezone, 'U');
                if ($request->has('start_time')) {
                    $temp = explode(':', trim($request->start_time));
                    $start_time += (($temp[0] * 60) + $temp[1]) * 60;
                }
            }
            // Survey end time
            if ($request->has('end_date')) {
                $end_time = Timezone::convertToUTC($request->end_date, Auth::user()->timezone, 'U');
                if ($request->has('end_time')) {
                    $temp = explode(':', trim($request->end_time));
                    $end_time += (($temp[0] * 60) + $temp[1]) * 60;
                }
            }
            $error = false;

            if ($request->has('start_date') && $request->has('end_date')) {
                if ($start_time >= $end_time) {
                    $error = true;
                    $validation->getMessageBag()->add('end_date', trans('admin/survey.end_date_higher'));
                }
            }
            if ($error) {
                return redirect('cp/survey/add-survey/')
                    ->withInput()
                    ->withErrors($validation);
            }
            $data = [
                    'survey_title' => $request->survey_title,
                    'description' => $request->survey_description,
                    'start_time' => (int)$start_time,
                    'end_time' => (int)$end_time,
                    'display_report' => (!is_null($request->display_report)) ? true : false,
                    'status' => 'ACTIVE',
                    'created_by' => Auth::user()->username,
                    'created_at' => time(),
                ];
            $this->survey_service->insertSurvey($data); /* inserting the survey */
            return redirect('cp/survey/list-survey')
            ->with('success', trans('admin/survey.survey_created'));
        }
    }

    /**
     * @param $sid
     */
    public function getEditSurvey($sid)
    {
        $edit_survey_permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::SURVEY,
            PermissionType::ADMIN,
            SurveyPermission::EDIT_SURVEY,
            null,
            null,
            true
        );
        $edit_survey_permission_data = get_permission_data($edit_survey_permission_data_with_flag);
        if (!is_element_accessible($edit_survey_permission_data, ElementType::SURVEY, $sid)) {
            return parent::getAdminError();
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/survey.manage_survey') => 'survey/list-survey',
            trans('admin/survey.edit_survey') => ''
        ];
        $survey = $this->survey_service->getSurveyByIds((int)$sid)->first();
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = 'Edit Survey';
        $this->layout->pageicon = 'fa fa-file-text';
        $this->layout->pagedescription = 'Edit Survey';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'survey')
            ->with('submenu', 'manage survey');
        $this->layout->content = view('admin.theme.survey.edit_survey')
            ->with('survey', $survey);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse|void
     */
    public function postEditSurvey(Request $request)
    {
        $permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::SURVEY,
            PermissionType::ADMIN,
            SurveyPermission::EDIT_SURVEY,
            null,
            null,
            true
        );
        if (!is_element_accessible(
            get_permission_data($permission_data_with_flag),
            ElementType::SURVEY,
            $request->_s
        )) {
            return parent::getAdminError();
        }
        $rules = [
            'survey_title' => 'required|max:512',
            'start_date' => 'required',
            'end_date' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
        ];
        $messages = [
            'start_date.required' => trans('admin/survey.start_date_required'),
            'survey_title.required' => trans('admin/survey.survey_title_required'),
            'end_date.required' => trans('admin/survey.end_date_required'),
            'start_time.required' => trans('admin/survey.start_time_required'),
            'end_time.required' => trans('admin/survey.end_time_required'),
        ];
        $validation = Validator::make($request->all(), $rules, $messages);
        if ($validation->fails()) {
            return redirect('cp/survey/edit-survey/' . $request->_s)
                ->withInput()
                ->withErrors($validation);
        } else {
            $start_time = $end_time = 0;
            // Survey start time
            if ($request->has('start_date')) {
                $start_time = Timezone::convertToUTC($request->start_date, Auth::user()->timezone, 'U');
                if ($request->has('start_time')) {
                    $temp = explode(':', trim($request->start_time));
                    $start_time += (($temp[0] * 60) + $temp[1]) * 60;
                }
            }
            // Survey end time
            if ($request->has('end_date')) {
                $end_time = Timezone::convertToUTC($request->end_date, Auth::user()->timezone, 'U');
                if ($request->has('end_time')) {
                    $temp = explode(':', trim($request->end_time));
                    $end_time += (($temp[0] * 60) + $temp[1]) * 60;
                }
            }
            $error = false;
            if ($request->has('start_date') && $request->has('end_date')) {
                if ($start_time >= $end_time) {
                    $error = true;
                    $validation->getMessageBag()->add('end_date', trans('admin/survey.end_date_higher'));
                }
            }
            if ($error) {
                return redirect('cp/survey/edit-survey/'.$request->_s)
                    ->withInput()
                    ->withErrors($validation);
            }
            $sdata = [
                'survey_title' => $request->survey_title,
                'description' => $request->survey_description,
                'start_time' => (int)$start_time,
                'end_time' => (int)$end_time,
                'display_report' => ($request->display_report == "on") ? true : false,
                'status' => 'ACTIVE',
                'updated_by' => Auth::user()->username,
                'updated_at' => time(),
            ];
            $this->survey_service->updateSurvey($request->_s, $sdata);
            return redirect('cp/survey/list-survey')
                ->with('success', trans('admin/survey.survey_updated'));
        }
    }

    /**
     * @param null $action
     * @param null $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function postAssignSurvey($action = null, $key = null)
    {
        $msg = null;
        $survey = $this->survey_service->getSurveyByIds((int)$key)->first();
        if (empty($survey)) {
            return response()->json(['flag' => 'error', 'message' => trans("admin/survey.invalid_survey")]);
        }
        $ids = (!empty(Input::get('ids')) ? explode(',', Input::get('ids')) : []);
        if (Input::get('empty') != true) {
            if (empty($ids) || !is_array($ids)) {
                return response()->json(['flag' => 'error', 'message' => trans("admin/survey.no_checkbox_selected")]);
            }
        }
        $ids = array_map('intval', $ids);
        switch ($action) {
            case 'user':
                $assign_user_survey_permission_data_with_flag = $this->roleService->hasPermission(
                    Auth::user()->uid,
                    ModuleEnum::SURVEY,
                    PermissionType::ADMIN,
                    SurveyPermission::SURVEY_ASSIGN_USER,
                    null,
                    null,
                    true
                );

                $assign_user_survey_permission_data = get_permission_data($assign_user_survey_permission_data_with_flag);
                $has_assign_survey_to_user_permission = is_element_accessible(
                    $assign_user_survey_permission_data,
                    ElementType::SURVEY,
                    $key
                );
                if ($has_assign_survey_to_user_permission) {
                    $has_assign_survey_to_user_permission = are_users_exist_in_context(
                        $assign_user_survey_permission_data,
                        $ids
                    );
                }
                if (!$has_assign_survey_to_user_permission) {
                    return response()->json([
                        'flag' => 'error',
                        'message' => trans("admin/survey.no_per_to_assign_users")
                    ]);
                }
                $arrname = 'users';
                $survey_relation = isset($survey->$arrname) ? $survey->$arrname : [];
                if (!is_admin_role(Auth::user()->role)) {
                    /* If the user is a ProgramAdmin/ContentAuthor */
                    /* $manageable_ids = Uids which belongs to PA/CA users */
                    $manageable_ids = array_values(array_intersect(get_user_ids($assign_user_survey_permission_data), $survey_relation));
                    /* $dedupe_ids => Uids which are in the relation and are assigned by Site Admin */
                    $dedupe_ids = array_diff($survey_relation, $manageable_ids);

                    /* Note: when $dedupe_ids is empty, It means $manageable_ids and $users_relation contains same uids then assigning $manageable_ids to $dedupe_ids */
                    /* Below code is to remove the relations from the user tables */
                    if (empty($dedupe_ids)) {
                        $dedupe_ids = $manageable_ids;
                    }
                } else {
                    /* If the user is Site Admin  */
                    /* $manageable_ids => survey user rel ie. "users" */
                    $manageable_ids = $survey_relation;
                    /* $dedupe_ids => survey user rel ie. "users" */
                    $dedupe_ids = $survey_relation;
                }
                if (isset($dedupe_ids) && !empty($dedupe_ids)) {
                    $delete = array_diff($manageable_ids, $ids);
                    $add = array_diff($ids, $dedupe_ids);

                    if (!is_admin_role(Auth::user()->role)) {
                        /* $ids => taking the array difference of ( users+selected uids as the input) and $delete */
                        $ids = array_values(array_diff(array_unique(array_merge($survey_relation, $add)), $delete));
                    }
                } else {
                    $delete = [];
                    $add = $ids;
                }
                foreach ($delete as $value) {
                    $this->user_service->removeUserSurvey($value, ['survey'], $survey->id);
                }
                foreach ($add as $value) {
                    $this->user_service->addUserSurvey($value, ['survey'], $survey->id);
                }
                $msg = trans('admin/user.user_assigned');
                break;

            case 'usergroup':
                $permission_data_with_flag = $this->roleService->hasPermission(
                    Auth::user()->uid,
                    ModuleEnum::SURVEY,
                    PermissionType::ADMIN,
                    SurveyPermission::SURVEY_ASSIGN_USER_GROUP,
                    null,
                    null,
                    true
                );
                $permission_data = get_permission_data($permission_data_with_flag);
                $has_assign_permission = is_element_accessible($permission_data, ElementType::SURVEY, $key);
                if ($has_assign_permission) {
                    $has_assign_permission = are_user_groups_exist_in_context(
                        $permission_data,
                        $ids
                    );
                }
                if (!$has_assign_permission) {
                    return response()->json([
                        'flag' => 'error',
                        'message' => trans("admin/survey.no_per_to_assign_usergroups")
                    ]);
                }
                $arrname = 'usergroups';
                $survey_relation = isset($survey->$arrname) ? $survey->$arrname : [];
                if (!is_admin_role(Auth::user()->role)) {
                    /* If the user is a ProgramAdmin/ContentAuthor */
                    /* $manageable_ids = Uids which belongs to PA/CA users */
                    $manageable_ids = array_values(array_intersect(get_user_group_ids($permission_data), $survey_relation));
                    /* $dedupe_ids => Uids which are in the relation and are assigned by Site Admin */
                    $dedupe_ids = array_diff($survey_relation, $manageable_ids);

                    /* Note: when $dedupe_ids is empty, It means $manageable_ids and $survey_relation contains same uids then assigning $manageable_ids to $dedupe_ids */
                    /* Below code is to remove the relations from the user tables */
                    if (empty($dedupe_ids)) {
                        $dedupe_ids = $manageable_ids;
                    }
                } else {
                    /* If the user is Site Admin  */
                    /* $manageable_ids => survey usergroup rel ie. "usergroups" */
                    $manageable_ids = $survey_relation;
                    /* $dedupe_ids => survey usergroup rel ie. "usergroups" */
                    $dedupe_ids = $survey_relation;
                }
                if (isset($dedupe_ids) && !empty($dedupe_ids)) {
                    $delete = array_diff($manageable_ids, $ids);
                    $add = array_diff($ids, $dedupe_ids);
                    if (!is_admin_role(Auth::user()->role)) {
                        /* $ids => taking the array difference of ( usergroups+selected uids as the input) and $delete */
                        $ids = array_values(array_diff(array_unique(array_merge($survey_relation, $add)), $delete));
                    }
                } else {
                    $delete = [];
                    $add = $ids;
                }
                $ugs = $this->usergroup_service->getUserGroups(['status' => ['ACTIVE']]);
                foreach ($delete as $value) {
                    $this->usergroup_service->removeUserGroupSurvey($value, ['survey'], $survey->id);
                }
                foreach ($add as $value) {
                    $this->usergroup_service->addUserGroupSurvey($value, ['survey'], $survey->id);
                }
                $msg = trans('admin/user.usergroup_assigned');
                break;

            case 'feed':
                $ids = $ids[0];
                $feed = Input::get('feed');
                $program = $this->program_service->getAllProgramByIDOrSlug('content_feed', $feed)->first();
                if (!$this->roleService->hasPermission(
                    $this->request->user()->uid,
                    ModuleEnum::CHANNEL,
                    PermissionType::ADMIN,
                    ChannelPermission::MANAGE_CHANNEL_POST,
                    Contexts::PROGRAM,
                    $program->program_id
                )) {
                    return response()->json([
                    'flag' => 'error',
                    'message' => trans("admin/program.no_permission_to_manage_posts")
                    ]);
                }
                $arrname = 'post_id';
                if (!empty($program)) {
                    $this->getUnassignPost($survey->id);
                    $packet_col = $this->post_service->getPostByID($ids, 'packet_id')->first();
                    $index_i = 1;
                    $temp = [];
                    $insert = true;
                    if (!empty($packet_col->elements)) {
                        foreach ($packet_col->elements as $element) {
                            $index_i++;
                            if (in_array('survey', $element) && $element['type'] == 'survey' && $element['id'] == (int)$survey->id) {
                                $insert = false;
                            }
                        }
                    }
                    if ($insert == true) {
                        $this->post_service->pushRelations($ids, 'survey_ids', [$survey->id]);
                        $element_ary['type'] = 'survey';
                        $element_ary['order'] = $index_i;
                        $element_ary['id'] = (int)$survey->id;
                        $element_ary['name'] = $survey->survey_title;
                        $temp[] = $element_ary;
                    }
                    $this->post_service->pushRelations($ids, 'elements', $temp);

                    if (config('elastic.service')) {
                        event(new ItemsAdded($ids));
                    }
                    $msg = trans('admin/survey.post_assigned_success');
                } else {
                    return response()->json(['flag' => 'error', 'message' => trans("admin/survey.invalid_feed")]);
                }
                break;

            default:
                return response()->json(['flag' => 'error', 'message' => trans("admin/survey.wrong_action_param")]);
                break;
        }

        $this->survey_service->UnsetSurveyRelations($survey->id, $arrname); //Unsetting the relation before updating
        if (!empty($ids)) {
            $updated = $this->survey_service->UpdateSurveyRelations($survey->id, $arrname, $ids);
            if ($updated) {
                if ($action == 'user' || $action == 'usergroup') {
                    if (config('elastic.service')) {
                        event(new SurveyAssigned($survey->id));
                    }
                }
                return response()->json(['flag' => 'success', 'message' => $msg]);
            } else {
                return response()->json(['flag' => 'error']);
            }
        } else {
            if ($action == 'user' || $action == 'usergroup') {
                if (config('elastic.service')) {
                    event(new SurveyAssigned($survey->id));
                }
            }
            return response()->json(['flag' => 'success', 'message' => $msg]);
        }
    }

    /**
     * @param $sid
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function getDeleteSurvey($sid)
    {
        if (!is_numeric($sid)) {
            abort(404);
        }
        $delete_survey_permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::SURVEY,
            PermissionType::ADMIN,
            SurveyPermission::DELETE_SURVEY,
            null,
            null,
            true
        );
        $delete_survey_permission_data = get_permission_data($delete_survey_permission_data_with_flag);
        if (!is_element_accessible($delete_survey_permission_data, ElementType::SURVEY, $sid)) {
            return parent::getAdminError();
        }
        $start = Input::get('start', 0);
        $limit = Input::get('limit', 10);
        $search = Input::get('search', '');
        $order_by = Input::get('order_by', '7 desc');
        $survey = $this->survey_service->DeleteSurvey($sid);
        // Redirect check
        if (Input::has('return')) {
            $return = urldecode(Input::get('return')) . '?start=' . $start . '&limit=' . $limit . '&search=' . $search . '&order_by=' . $order_by;
        } else {
            $return = 'cp/survey/list-survey';
        }
        if ($survey) {
            return redirect($return)
                ->with('success', trans('admin/survey.survey_delete'));
        } else {
            return redirect($return)
                ->with('error', trans('admin/survey.problem_while_deleting_question'));
        }
    }

    /**
     * @param $sid
     */
    public function getSurveyQuestions($sid)
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            'Manage Survey' => 'survey/list-survey',
            'Survey questions' => ''
        ];

        $question_count = $this->survey_service->getSurveyQuestionsCount($sid);
        $survey_name = $this->survey_service->getSurveyFieldById($sid, ['survey_title'])->first()->survey_title;
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = 'Survey Questions';
        $this->layout->pageicon = 'fa fa-file-text';
        $this->layout->pagedescription = 'Survey Questions';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'survey')
                ->with('submenu', 'manage survey');
        $this->layout->content = view('admin.theme.survey.manage_survey_questions')
                ->with('sid', (int)$sid)
                ->with('question_count', $question_count)
                ->with('survey_name', $survey_name);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListSurveyQuestions(Request $request)
    {
        $has_list_user_permission = has_admin_permission(ModuleEnum::SURVEY, SurveyPermission::LIST_SURVEY_QUESTION);
        if (!$has_list_user_permission) {
            return response()->json(
                [
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ]
            );
        }
        $question_list = collect([]);
        $filters = [
            "search" => isset($request->search['value']) ? $request->search['value'] : '',
            "status" => 'ACTIVE',
            "survey_id" => $request->survey_id
        ];

        $all_questions_count = $this->survey_questions->getSurveyQuestionCount($filters);
        $survey_start_date = $this->survey_service->getSurveyFieldById((int)$request->survey_id, ['start_time'])->first();
        if ($request->has('start') && $request->has('length')) {
            $filters += [
                'start' => $request->start,
                'limit' => $request->length,
            ];
            $order_by = $request->order;
            $orderByArray = ['title' => 'desc'];
            if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
                if ($order_by[0]['column'] == '0') {
                    $orderByArray = ['created_at' => $order_by[0]['dir']];
                }
                if ($order_by[0]['column'] == '1') {
                    $orderByArray = ['order_by' => $order_by[0]['dir']];
                }
                $questions = $this->survey_questions->getSurveyQuestions($filters, $orderByArray);
                $question_list = $questions->transform(function ($questions) use (&$filters, &$order_by, $survey_start_date) {
                    $questions_title = '<div>'. $questions->title .'</div>';
                    /* Action buttons */
                    /* EDIT */
                    if (has_admin_permission(ModuleEnum::SURVEY, SurveyPermission::EDIT_SURVEY_QUESTION)) {
                        $edit = '<a class="btn btn-circle show-tooltip editsurvey" title="' . trans('admin/survey.edit') . '" href="' . URL::to('cp/survey/edit-question/' . $questions->id .'/'. $questions->survey_id) . '?start=' . $filters['start'] . '&limit=' . $filters['limit'] . '&search=' . $filters['search'] . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-edit"></i></a>';
                    } else {
                        $edit = '<a style="cursor:not-allowed" class="btn btn-circle show-tooltip ajax" title="' . trans("admin/survey.no_perm_to_edit") . '"><i class="fa fa-edit"></i></a>';
                    }
                    /* DELETE*/
                    if (has_admin_permission(ModuleEnum::SURVEY, SurveyPermission::DELETE_SURVEY_QUESTION)) {
                        $delete = '<a class="btn btn-circle show-tooltip deletequestion" title="' . trans('admin/survey.delete') . '" href="' . URL::to('cp/survey/delete-question/' . $questions->id .'/'. $questions->survey_id) . '?start=' . $filters['start'] . '&limit=' . $filters['limit'] . '&search=' . $filters['search'] . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-trash-o"></i></a>';
                    } else {
                        $delete = '<a style="cursor:not-allowed" class="btn btn-circle show-tooltip ajax" title="' . trans("admin/survey.no_perm_to_delete") . '"><i class="fa fa-trash-o"></i></a>';
                    }
                    /* Action buttons */

                    /* Data construction for final array*/
                    $each_questions = [
                        'order_by' => $questions->order_by,
                        'question_title' => $questions_title,
                        'type' => $questions->type,
                        'mandatory' => ($questions->is_mandatory == true) ? $questions->is_mandatory : false,
                        'actions' => $edit . $delete,
                    ];
                    return $each_questions;
                });
            }
        }

        $finaldata = [
            'recordsTotal' => $question_list->count(),
            'recordsFiltered' => $all_questions_count,
            'data' => $question_list
        ];
        return response()->json($finaldata);
    }

    /**
     * @param $sid
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function getAddQuestion($sid)
    {
        if (!has_admin_permission(ModuleEnum::SURVEY, SurveyPermission::ADD_SURVEY_QUESTION)) {
            return parent::getAdminError();
        }

        $question_count = $this->survey_service->getSurveyQuestionsCount($sid);
        $survey_name = $this->survey_service->getSurveyFieldById($sid, ['survey_title'])->first()->survey_title;
        if ($question_count >= 25) {
            return redirect('cp/survey/survey-questions/'.$sid)
                    ->with('error', trans("admin/survey.max_question_msg"));
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans("admin/survey.manage_survey_questions") => 'survey/survey-questions/'.$sid,
            trans("admin/survey.add_question") => ''
        ];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = 'Add question';
        $this->layout->pageicon = 'fa fa-file-text';
        $this->layout->pagedescription = 'Add question';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'survey')
            ->with('submenu', 'manage question bank');
        $this->layout->content = view('admin.theme.survey.add_question')
            ->with("sid", (int)$sid)
            ->with("survey_name", $survey_name)
            ->with("question_count", $question_count+1);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * @param $survey_id
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse|void
     */
    public function postAddQuestion($survey_id, Request $request)
    {
        if (!has_admin_permission(ModuleEnum::SURVEY, SurveyPermission::ADD_SURVEY_QUESTION)) {
            return parent::getAdminError();
        }
        $rules = [
            'question_name' => 'required|max:512',
            'question_type' => 'required',
            'choice' => 'checkChoice',
        ];
        $messages = [
            'question_name.required' => trans('admin/survey.question_name_requ'),
            'question_type.required' => trans('admin/survey.question_type_requ'),
            'choice.checkChoice' => trans('admin/survey.choice_required'),
        ];
        Validator::extendImplicit('checkChoice', function ($attribute, $value, $parameters, $validator) {
            $input = $validator->getData();
            if ($input['question_type'] == "MCQ-SINGLE" || $input['question_type'] == "MCQ-MULTIPLE") {
                $choice = array_filter($input['choice']);
                if (empty($choice) || count($choice) < 2) {
                    return false;
                }
            }
            return true;
        });

        $validation = Validator::make($request->all(), $rules, $messages);
        if ($validation->fails()) {
            return redirect('cp/survey/add-question/'.$survey_id)
                ->withInput()
                ->withErrors($validation);
        } else {
            if ($request->question_type == 'RATE-5') {
                $choices = [
                    '1 (Low)',
                    '2',
                    '3',
                    '4',
                    '5 (High)',
                ];
            } else {
                $choices = (isset($request->choice) && !empty($request->choice)) ? array_filter($request->choice) : [];
            }
            if($request->order_by == "" || $request->order_by == null){
                $request->order_by = 1;
            }
            $question_id = SurveyQuestion::getNextSequence();
            $data = [
                'id' => $question_id,
                'survey_id' => (int)$survey_id,
                'title' => $request->question_name,
                'type' =>  $request->question_type,
                'choices' => $choices,
                'status' => 'ACTIVE',
                'is_others' => ($request->others == "on") ? true : false,
                'is_mandatory' => ($request->is_mandatory == "on") ? true : false,
                'order_by' => (int) $request->order_by,
                'created_at' => time(),
            ];

            $inserted = $this->survey_questions->insertSurveyQuestions($data);
            if ($inserted) {
                $this->survey_service->pushSurveyRelations($survey_id, ['survey_question'], $question_id);
            }

            return redirect('cp/survey/survey-questions/'.$survey_id)
                    ->with('success', trans("admin/survey.question_add_success"));
        }
    }

    /**
     * @param $question_id
     * @param $survey_id
     */
    public function getEditQuestion($question_id, $survey_id)
    {
        if (!has_admin_permission(ModuleEnum::SURVEY, SurveyPermission::EDIT_SURVEY_QUESTION)) {
            return parent::getAdminError();
        }
        if (!is_numeric($question_id)) {
            abort(404);
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans("admin/survey.manage_survey_questions") => 'survey/survey-questions/'. $survey_id,
            trans("admin/survey.edit_question") => ''
        ];
        $question = $this->survey_questions->getSurveyQuestionsById($question_id)->first();
        $survey_name = $this->survey_service->getSurveyFieldById($survey_id, ['survey_title'])->first()->survey_title;
        $type = ["MCQ-SINGLE" => "Single answer",
                "MCQ-MULTIPLE" => "Multiple answers",
                "DESCRIPTIVE" => "Text",
                "RATE-5" => "Range 1(Low)-5(High)"
            ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = 'Edit question';
        $this->layout->pageicon = 'fa fa-file-text';
        $this->layout->pagedescription = 'Edit question';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'survey')
            ->with('submenu', 'manage question bank');
        $this->layout->content = view('admin.theme.survey.edit_question')
            ->with('question', $question)
            ->with('survey_id', $survey_id)
            ->with('type', $type)
            ->with('survey_name', $survey_name);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * @param $question_id
     * @param $survey_id
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse|void
     */
    public function postEditQuestion($question_id, $survey_id, Request $request)
    {
        if (!has_admin_permission(ModuleEnum::SURVEY, SurveyPermission::EDIT_SURVEY_QUESTION)) {
            return parent::getAdminError();
        }
        $question = $this->survey_questions->getSurveyQuestionsById($question_id)->first();
        $rules = [
            'question_name' => 'required|max:512',
            'q_type' => 'required',
            'choice' => 'checkChoice',
        ];
        $messages = [
            'question_name.required' => trans('admin/survey.question_name_requ'),
            'q_type.required' => trans('admin/survey.question_type_requ'),
            'choice.checkChoice' => trans('admin/survey.choice_required'),
        ];
        Validator::extendImplicit('checkChoice', function ($attribute, $value, $parameters, $validator) {
            $input = $validator->getData();
            if ($input['q_type'] == "MCQ-SINGLE" || $input['q_type'] == "MCQ-MULTIPLE") {
                $choice = array_filter($input['choice']);
                if (empty($choice) || count($choice) < 2) {
                    return false;
                }
            }
            return true;
        });
        $validation = Validator::make($request->all(), $rules, $messages);
        if ($validation->fails()) {
            return redirect('cp/survey/edit-question/'.$question_id. '/'.$survey_id)
                ->withInput()
                ->withErrors($validation);
        } else {
            if ($request->question_type == 'RATE-5') {
                $choices = [
                  '1 (Low)',
                  '2',
                  '3',
                  '4',
                  '5 (High)',
                ];
            } else {
                $choices = (isset($request->choice) && !empty($request->choice)) ? array_filter($request->choice) : [];
            }

            if($request->order_by == "" || $request->order_by == null){
                $request->order_by = 1;
            }
            $survey_ques = [
                'title' => $request->question_name,
                'type' =>  $request->q_type,
                'choices' => $choices,
                'is_others' => ($request->others == "on") ? true : false,
                'is_mandatory' => ($request->is_mandatory == "on") ? true : false,
                'order_by' => (int) $request->order_by,
                'updated_at' => time(),
                'updated_by' => Auth::user()->username,
            ];
            $this->survey_questions->updateSurveyQuestions($question_id, $survey_ques);
            return redirect('cp/survey/survey-questions/'.$survey_id)
                    ->with('success', trans("admin/survey.question_edit_success"));
        }
    }

    /**
     * @param $question_id
     * @param $survey_id
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function getDeleteQuestion($question_id, $survey_id)
    {
        if (!is_numeric($question_id)) {
            abort(404);
        }
        $delete_survey_ques_permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::SURVEY,
            PermissionType::ADMIN,
            SurveyPermission::DELETE_SURVEY_QUESTION,
            null,
            null,
            true
        );
        $delete_survey_ques_permission_data = get_permission_data($delete_survey_ques_permission_data_with_flag);
        if (!is_element_accessible($delete_survey_ques_permission_data, ElementType::SURVEY, $question_id)) {
            return parent::getAdminError();
        }

        $start = Input::get('start', 0);
        $limit = Input::get('limit', 10);
        $search = Input::get('search', '');
        $order_by = Input::get('order_by', '7 desc');
        $survey_ques = $this->survey_questions->DeleteSurveyQuestion($question_id);
        if ($survey_ques) {
            $this->survey_service->pullSurveyRelations($survey_id, ['survey_question'], $question_id);
        }
        // Redirect check
        if (Input::has('return')) {
            $return = urldecode(Input::get('return')) . '?start=' . $start . '&limit=' . $limit . '&search=' . $search . '&order_by=' . $order_by;
        } else {
            $return = 'cp/survey/survey-questions/'.$survey_id;
        }

        if ($survey_ques) {
            return redirect($return)
                ->with('success', trans('admin/survey.survey_ques_delete'));
        } else {
            return redirect($return)
                ->with('error', trans('admin/survey.problem_while_deleting_question'));
        }
    }

    /**
     * @param $survey_id
     * @param $from
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnassignPost($survey_id, $from = null)
    {
        $flag = "failed";
        $msg = "";
        try {
            $packets = $this->post_service->getPostByID($survey_id, 'survey_ids')->get(['packet_id', 'elements', 'survey_ids'])->first();
            if (!is_null($packets)) {
                $elements = $packets->elements;
                $new_elements = [];
                foreach ($elements as $element) {
                    if ($element['type'] != 'survey' || ( $element['type'] == 'survey' && $element['id'] != $survey_id)) {
                        $new_elements[] = $element;
                    }
                }
                $this->post_service->pullRelations($packets->packet_id, 'survey_ids', $survey_id);
                $this->post_service->updateRelationsByID($packets->packet_id, 'elements', $new_elements);
                $this->survey_service->unassignPost($survey_id, 'post_id');

                $msg = trans("admin/survey.successfully_un_assigned");
                $flag = 'success';
            } else {
                $msg = trans("admin/survey.not_asso_to_any_posts");
                $flag = 'failed';
            }
        } catch (Exception $e) {
            $msg = $e->getMessage();
            Log::error("Survey un assign : ".$e->getMessage());
        }

        if (!empty($from)) {
              return redirect('cp/survey/list-survey')
                    ->with('success', trans('admin/survey.unasssign_post'));
        } else {
            return response()->json(['flag' => $flag, 'message' => $msg]);
        }
    }

    /**
     * @param $surveyID
     */
    public function getSurveyReport($surveyID)
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            'Manage Survey' => 'survey/list-survey',
            'Survey Report' => ''
        ];
        $survey = $this->survey_service->getSurveyByIds($surveyID)->first();
        $total_user_count = $this->getTotalAssignedUsers($surveyID);
        $survey_question_details = $this->survey_questions->getQuestionBySurveyId((int)$surveyID)->keyBy('id');
        $user_responses = $this->survey_attempt_data->getUserResponse((int)$surveyID, $total_user_count)->groupBy('question_id');
        $desc_answers = $this->survey_attempt_data->getDescAnswers($surveyID, $total_user_count)->keyBy('_id');
        $s_attempt_details = $this->survey_attempt_data->getSurveyAttemptData($surveyID, $total_user_count);
        $attempt_users = $s_attempt_details->pluck('user_id')->unique()->count();
        $unattempt_users = $this->getUnattemptedUserIds($surveyID, null);
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = '';
        $this->layout->pageicon = '';
        $this->layout->pagedescription = 'Survey Report';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'survey')
            ->with('submenu', 'manage survey');
        $this->layout->content = view('admin.theme.survey.survey_report')
            ->with('survey', $survey)
            ->with('survey_question_details', $survey_question_details)
            ->with('user_responses', $user_responses)
            ->with('desc_answers', $desc_answers)
            ->with('user_count', $attempt_users)
            ->with('total_users', count($total_user_count))
            ->with('unattempt_users', $unattempt_users);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getTextReportAjax()
    {
        $survey_id = Input::get('survey_id', '');
        $question_id = Input::get('question_id', '');
        $page = Input::get('page', 0);
        $limit = 10;
        $start = $page * $limit;
        $total_user_count = $this->getTotalAssignedUsers($survey_id);
        $otherResponses = $this->survey_attempt_data->getUserTextByQuestion($survey_id, $question_id, $total_user_count, $start, $limit);
        $user_ids = $otherResponses->pluck('user_id')->all();
        $user_details = $this->user_service->getUsersByUserIds(
                $start,
                $limit,
                ['uid', 'username', 'firstname', 'lastname', 'email'],
                $user_ids,
                ['created_at' => 'desc'],
                ""
            )->keyBy('uid');
        $data = [];
        foreach ($otherResponses as $otherResponse) {
            $user_detail = $user_details->get($otherResponse->user_id);
            if (!is_null($user_detail)) {
                $data[]= [
                    $user_detail->username,
                    $otherResponse->other_text
                ];
            }
        }
        return response()->json(
            [
                'message' => $data,
                'status' => true
            ]
        );
    }

    public function getUnattemptedUserIds($surveyID, $filter)
    {
        $total_user_count = $this->getTotalAssignedUsers($surveyID);
        $s_attempt_details = $this->survey_attempt_data->getSurveyAttemptData($surveyID, $total_user_count);
        $attempt_users = $s_attempt_details->pluck('user_id')->unique()->toArray();
        if ($filter == null) {
            $unattempt_user_count = (count($total_user_count) - count($attempt_users));
            if($unattempt_user_count < 0) {
                $unattempt_user_count = 0;
            }
            return $unattempt_user_count;
        } else {
            $user_ids = array_diff($total_user_count, $attempt_users);
            return $user_ids;
        }
    }

    public function getUnattemptedUserDetails()
    {
        $survey_id = Input::get('survey_id', '');
        $page = Input::get('page', 0);
        $limit = 10;
        $start = $page * $limit;
        $user_ids = $this->getUnattemptedUserIds($survey_id, "user_ids");
        $user_details = $this->user_service->getUsersByUserIds(
                $start,
                $limit,
                ['uid', 'username', 'firstname', 'lastname', 'email'],
                $user_ids,
                ['created_at' => 'desc'],
                ""
            )->keyBy('uid');
        $data = [];
        foreach ($user_details as $user) {
            $data[] = [
                $user->username,
                $user->fullname,
                $user->email
            ];
        }
        return response()->json(
            [
                'message' => $data,
                'status' => "success"
            ]
        );
    }

    public function getAttemptedUserDetails()
    {
        $survey_id = Input::get('survey_id', '');
        $question_id = Input::get('question_id', '');
        $choice_index = Input::get('choice_index', '');
        $page = Input::get('page', 0);
        $limit = 10;
        $start = $page * $limit;
        $total_user_count = $this->getTotalAssignedUsers($survey_id);
        $users = $this->survey_attempt_data->getRespondedUsers($survey_id, $question_id, $choice_index, $total_user_count, $start, $limit);
        $user_ids = $users->pluck('user_id')->toArray();
        $user_details = $this->user_service->getUsersByUserIds(
                $start,
                $limit,
                ['uid', 'username', 'firstname', 'lastname', 'email'],
                $user_ids,
                ['created_at' => 'desc'],
                ""
            )->keyBy('uid');
        $data = [];
        foreach ($user_details as $user) {
            $data[] = [
                $user->username,
                $user->fullname,
                $user->email
            ];
        }
        return response()->json(
            [
                'message' => $data,
                'status' => "success"
            ]
        );
    }

    public function getDetailReport($surveyID)
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            'Manage Survey' => 'survey/list-survey',
            'Survey Report' => '../survey-report/'.$surveyID,
            'Detailed Report' => '',
        ];
        $survey = $this->survey_service->getSurveyByIds($surveyID)->first();
        $direct_users = !is_null($survey->users) ? $survey->users : [];
        $user_groups = !is_null($survey->usergroups) ? $survey->usergroups : [];
        $packet_id = $survey->post_id;
        $feed_details = $this->post_service->getPacketByID($packet_id);
        $feed_slug = $feed_details['feed_slug'];
        $feed_relations = $this->program_service->getProgramIdBySlug($feed_slug);
        $feed_users = $feed_usergroups =  $users = $usergroupids = [];
        if (!is_null($feed_relations)) {
            $feed_users = array_get($feed_relations->relations, 'active_user_feed_rel', []);
            $feed_usergroups = array_get($feed_relations->relations, 'active_usergroup_feed_rel', []);
        }
        $usergroupids = array_merge($user_groups, $feed_usergroups);
        if (isset($usergroupids)) {
            $users = $this->user_service->getUsersByUGids($usergroupids)->pluck('uid')->all();
        }
        $total_users_list = array_unique(array_merge($direct_users, $feed_users, $users));
        $s_attempt_details = $this->survey_attempt_data->getSurveyAttemptData($surveyID, $total_users_list);
        $attempt_users = $s_attempt_details->pluck('user_id')->unique()->count();
        $unattempt_users = $this->getUnattemptedUserIds($surveyID, null);
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = '';
        $this->layout->pageicon = '';
        $this->layout->pagedescription = 'Detailed report';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar');
        $this->layout->content = view('admin.theme.survey.detailed_report')
                                    ->with('survey', $survey)
                                    ->with('packet_title', $feed_details)
                                    ->with('channel_name', $feed_relations)
                                    ->with('total_users', count($total_users_list))
                                    ->with('attempt_users', $attempt_users)
                                    ->with('unattempt_users', $unattempt_users);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getUserReportAjax(Request $request)
    {
        $surveyID = $request->survey_id;
        $search_key = array_get($request->search, 'value', '');
        $start = $request->start;
        $limit = $request->length;
        $order_by = $request->order;
        $orderByArray = ['username' => 'asc'];
        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '0') {
                $orderByArray = ['username' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '2') {
                $orderByArray = ['email' => $order_by[0]['dir']];
            }
        }
        $total_user_count = $this->getTotalAssignedUsers($surveyID);
        $s_attempt_details = $this->survey_attempt_data->getSurveyAttemptData($surveyID, $total_user_count);
        $attempt_users = $s_attempt_details->pluck('user_id')->unique()->toArray();
        $user_details = $this->user_service->getUsersByUserIds(
                $start,
                $limit,
                ['uid', 'username', 'firstname', 'lastname', 'email'],
                $attempt_users,
                $orderByArray,
                $search_key
            );
        $user_list = $user_details->transform(function ($user) use($surveyID) {
            $username = $user->username;
            $fullname = $user->fullname;
            $email = $user->email;
            $view_details = '<a class="show_report" href="' . URL::to('cp/survey/attempted-data/unattempted/'.$surveyID.'/'.$user->uid). '" data-url="' . URL::to('cp/survey/attempted-data/unattempted/'.$surveyID.'/'.$user->uid). '" data-username = "'. $username .'"> View details</a>';
            $each_user = [
                    'username' => $username,
                    'fullname' => $fullname,
                    'email' => $email,
                    'actions' => $view_details,
            ];
            return $each_user;
        });
        return response()->json([
            'recordsTotal' => $user_list->count(),
            'recordsFiltered' => $user_list->count(),
            'data' => $user_list
        ]);
    }

    public function getAttemptedData($packet_slug, $survey_id, $user_id)
    {
        try {
            $survey_id = (int)$survey_id;
            $user_id = (int)$user_id;
            $survey_attempt_data = $this->survey_attempt_data->getSurveyAttemptDataByUserIdAndSurveyId($survey_id, $user_id)->keyBy('question_id');
            $question_ids = $survey_attempt_data->keys()->toArray();
            $question_details = $this->survey_questions->getSurveyQuestionsById($question_ids)->keyBy('id');
            $answers = [];
            foreach ($question_details as $question_id => $value) {
                $question_type = $value->type;
                $question_choices = $value->choices;
                switch ($question_type) {
                    case SurveyType::SINGLE_ANSWER:
                        $attempted_data = $survey_attempt_data[$question_id];
                        $other_text = $attempted_data->other_text;
                        $user_answer = array_values($attempted_data->user_answer);
                        $user_answer = implode('', $user_answer);
                        $answer = array_get($question_choices, $user_answer);
                        $answers['radio_'.$question_id] = [$answer];
                        $answers['radio_textarea_'.$question_id] = $other_text;
                        break;
                    case SurveyType::MULTIPLE_ANSWER:
                        $attempted_data = $survey_attempt_data[$question_id];
                        $other_text = $attempted_data->other_text;
                        $user_answer = array_values($attempted_data->user_answer);
                        $multiple_answers = [];
                        foreach ($user_answer as $value) {
                            $multiple_answers[] = array_get($question_choices, $value);
                        }
                        $answers['checkbox_'.$question_id] = $multiple_answers;
                        $answers['checkbox_textarea_'.$question_id] = $other_text;
                        break;
                    case SurveyType::RANGE:
                        $attempted_data = $survey_attempt_data[$question_id];
                        $other_text = $attempted_data->other_text;
                        $user_answer = array_values($attempted_data->user_answer);
                        $user_answer = implode('', $user_answer);
                        $answer = array_get($question_choices, $user_answer);
                        $answers['rate_'.$question_id] = [$answer];
                        $answers['rate_textarea_'.$question_id] = $other_text;
                        break;
                    case SurveyType::DESCRIPTIVE:
                        $attempted_data = $survey_attempt_data[$question_id];
                        $answers['textarea_'.$question_id] = $attempted_data->other_text;
                        break;

                    default:
                        Log::error(trans('survey.error_question_type'));
                        break;
                }
            }
            $data = $this->surveyQuestionDetails($survey_id);
            $survey = array_get($data, 'survey');
            $survey_question_data = array_get($data, 'survey_questions', []);
            $success = 0;
            $this->layout->pagetitle = '';
            $this->layout->pageicon = '';
            $this->layout->pagedescription = '';
            $this->layout->header = '';
            $this->layout->sidebar = '';
            $this->layout->content = view('admin.theme.survey.user_report')
                ->with('survey', $survey)
                ->with('survey_questions', $survey_question_data)
                ->with('survey_answer', $answers)
                ->with('packet_slug', $packet_slug)
                ->with('success', $success);
        } catch(Exception $e) {
            Log::error('Attempted data not found :: '. $e->getMessage());
            return parent::getError($this->theme, $this->theme_path, 401);
        }
    }
    public function surveyQuestionDetails($survey_id)
    {
        //Get survey details
        $survey = $this->survey_service->getSurveyByIds($survey_id)->first();
        //Get survey question details
        $survey_question_details = $this->survey_questions->getQuestionBySurveyId($survey_id);
        return [
            'survey' => $survey,
            'survey_questions' => $survey_question_details,
        ];
    }

    public function getSurveyReportExport($surveyID)
    {
        try {
            $survey = $this->survey_service->getSurveyByIds($surveyID)->first();
            $survey_question_details = $this->survey_questions->getQuestionBySurveyId((int)$surveyID)->keyBy('id')->sortBy('id');
            $direct_users = !is_null($survey->users) ? $survey->users : [];
            $user_groups = !is_null($survey->usergroups) ? $survey->usergroups : [];
            $packet_id = $survey->post_id;
            $feed_details = $this->post_service->getPacketByID($packet_id);
            $feed_slug = $feed_details['feed_slug'];
            $feed_relations = $this->program_service->getProgramIdBySlug($feed_slug);
            $feed_users = $feed_usergroups =  $users = $usergroupids = [];
            if (!is_null($feed_relations)) {
                $feed_users = array_get($feed_relations->relations, 'active_user_feed_rel', []);
                $feed_usergroups = array_get($feed_relations->relations, 'active_usergroup_feed_rel', []);
            }
            $usergroupids = array_merge($user_groups, $feed_usergroups);
            if (isset($usergroupids)) {
                $users = $this->user_service->getUsersByUGids($usergroupids)->pluck('uid')->all();
            }
            $total_users = array_unique(array_merge($direct_users, $feed_users, $users));
            $attempt_details = $this->survey_attempt_data->getSurveyAttemptData($surveyID, $total_users)->groupBy('user_id');
            $attempt_users = $attempt_details->keys()->unique()->toArray();
            $unattempt_users = $this->getUnattemptedUserIds($surveyID, null);
            $count = count($attempt_users);
            $header[] = trans('admin/dashboard.user_fullname');
            $header[] = trans('admin/dashboard.user_email');
            $order_of_question = [];
            foreach ($survey_question_details as $questions) {
                $header[] = html_entity_decode($questions->title);
                $order_of_question[] = $questions->id;
            }
            $filename = "User_Survey_Export.csv";
            $file_pointer = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . str_replace(" ", "", $filename));
            fputcsv($file_pointer, [trans('admin/survey.survey_name'), $survey->survey_title]);
            if ($feed_details) {
                fputcsv($file_pointer, [trans('admin/reports.channel_name'), $feed_relations->program_title]);
                fputcsv($file_pointer, [trans('admin/reports.post_name'), $feed_details['packet_title']]);
            }
            fputcsv($file_pointer, [trans('admin/survey.total_assigned_users'), count($total_users)]);
            fputcsv($file_pointer, [trans('admin/survey.no_of_user_responded'), $count]);
            fputcsv($file_pointer, [trans('admin/survey.users_not_responded'), $unattempt_users]);
            fputcsv($file_pointer, $header);
            $batch_limit = config('app.bulk_insert_limit');
            $total_batchs = intval($count / $batch_limit);
            $record_set = 0;
            do {
                $start = $record_set * $batch_limit;
                $user_details = $this->user_service->getUsersByUserIds(
                        $start,
                        $batch_limit,
                        ['uid', 'username', 'firstname', 'lastname', 'email'],
                        $attempt_users,
                        ['created_at' => 'desc'],
                        ""
                    )->keyBy('uid');

                foreach ($attempt_details as $uid => $user_attempt) {
                    $user_detail = $user_details->get($uid);
                    $user_attempt = $user_attempt->keyBy('question_id');
                    $choice_text = [];
                    foreach ($order_of_question as $question_id) {
                        $each_question = $user_attempt->get($question_id);
                        if(!is_null($each_question)) {
                             $choice_details = array_get($survey_question_details->get($each_question->question_id), 'choices', []);
                            $users_res = array_get($each_question, 'user_answer', []);
                            $choice = [];
                            foreach ($users_res as $index) {
                                $choice[] =  $choice_details[$index];
                            }
                            $user_response = "";
                            $user_response = implode(' , ', $choice);
                            if(isset($each_question->other_text) && !empty($each_question->other_text)) {
                                if(!empty($user_response)) {
                                    $user_response .= " , ";
                                }
                                $user_response .= html_entity_decode($each_question->other_text);
                            }
                            $choice_text[] = $user_response;
                        } else {
                            $choice_text[] = '';
                        }
                    }
                    $row = [
                        $user_detail->fullname,
                        $user_detail->email
                    ];
                    $row = array_merge($row, $choice_text);
                    fputcsv($file_pointer, $row);
                }
                $record_set++;
            } while ($record_set <= $total_batchs);
            fclose($file_pointer);
            exit();
        } catch (Exception $e) {
            return response()->json(['No records found, Please try after some time']);
        }
    }

    public function getTotalAssignedUsers($surveyID)
    {
        $survey = $this->survey_service->getSurveyByIds($surveyID)->first();
        $direct_users = !is_null($survey->users) ? $survey->users : [];
        $user_groups = !is_null($survey->usergroups) ? $survey->usergroups : [];
        $packet_id = $survey->post_id;
        $feed_details = $this->post_service->getPacketByID($packet_id);
        $feed_slug = $feed_details['feed_slug'];
        $feed_relations = $this->program_service->getProgramIdBySlug($feed_slug);
        $feed_users = $feed_usergroups =  $users = $usergroupids = [];
        if (!is_null($feed_relations)) {
            $feed_users = array_get($feed_relations->relations, 'active_user_feed_rel', []);
            $feed_usergroups = array_get($feed_relations->relations, 'active_usergroup_feed_rel', []);
        }
        $usergroupids = array_merge($user_groups, $feed_usergroups);
        if (isset($usergroupids)) {
            $users = $this->user_service->getUsersByUGids($usergroupids)->pluck('uid')->all();
        }
        $total_users = array_unique(array_merge($direct_users, $feed_users, $users));
        return $total_users;
    }
}
