<?php
namespace App\Http\Controllers\Admin;

use App\Events\Elastic\Items\ItemsAdded;
use App\Events\Elastic\Quizzes\QuizAdded;
use App\Events\Elastic\Quizzes\QuizAssigned;
use App\Events\Elastic\Quizzes\QuizEdited;
use App\Events\Elastic\Quizzes\QuizRemoved;
use App\Enums\Program\ChannelPermission;
use App\Helpers\Common\DateTimeHelper;
use App\Helpers\Quiz\QuizHelper;
use App\Http\Controllers\AdminBaseController;
use App\Http\Validators\Quiz\QuizValidator;
use App\Model\Common;
use App\Model\NotificationLog;
use App\Model\Packet;
use App\Model\Program;
use App\Model\Question;
use App\Model\QuestionBank;
use App\Model\QuestionbankImportHistory;
use App\Model\Quiz;
use App\Model\QuizAttempt;
use App\Model\QuizAttemptData;
use App\Model\Section;
use App\Model\SiteSetting;
use App\Model\User;
use App\Model\UserGroup;
use App\Enums\Quiz\CutoffFormatType as QCFT;
use Auth;
use Carbon;
use DB;
use Exception;
use App\Exceptions\ApplicationException;
use Illuminate\Database\Eloquent\ModelNotFoundException as ModelNotFoundException;
use Illuminate\Http\Request;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\Program\ElementType;
use App\Enums\Assessment\AssessmentPermission;
use App\Enums\Course\CoursePermission;
use App\Enums\RolesAndPermissions\PermissionType;
use App\Enums\RolesAndPermissions\Contexts;
use App\Services\Program\IProgramService;
use App\Services\Quiz\IQuizService;
use App\Services\User\IUserService;
use App\Services\QuizAttempt\IQuizAttemptService;
use Input;
use Log;
use Redirect;
use Session;
use stdClass;
use Timezone;
use URL;
use Validator;

class AssessmentController extends AdminBaseController
{
    protected $layout = 'admin.theme.layout.master_layout';

    /**
     * @var IProgramService
     */
    private $programService;

    /**
     * @var IProgramService
     */
    private $quizService;

    /**
     * @var IUserService
     */
    private $userService;

    public function __construct(
        IProgramService $programService,
        IQuizService $quizService,
        IUserService $userService,
        IQuizAttemptService $quiz_attempt_service
    ) {
        parent::__construct();

        $this->programService = $programService;
        $this->quizService = $quizService;
        $this->userService = $userService;
        $this->quiz_attempt_service = $quiz_attempt_service;
    }

    public function getListQuiz()
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assessment.title_manage_quiz') => ''
        ];
        if (Input::get('view', 'desktop') == 'iframe') {
            $from = Input::get('from');
            $this->layout->pagetitle = '';
            $this->layout->pageicon = '';
            $this->layout->pagedescription = '';
            $this->layout->header = '';
            $this->layout->sidebar = '';
            $this->layout->footer = '';
            $this->layout->content = view('admin.theme.assessment.list_quizzes_iframe')
                ->with('from', $from);
        } else {
            $list_quiz_permission_data_with_flag = $this->roleService->hasPermission(
                Auth::user()->uid,
                ModuleEnum::ASSESSMENT,
                PermissionType::ADMIN,
                AssessmentPermission::LIST_QUIZ,
                null,
                null,
                true
            );

            if (!get_permission_flag($list_quiz_permission_data_with_flag)) {
                return parent::getAdminError();
            }

            $list_quiz_permission_data = get_permission_data($list_quiz_permission_data_with_flag);

            $filter_params = has_system_level_access($list_quiz_permission_data)?
                [] : ["in_ids" => get_instance_ids($list_quiz_permission_data, Contexts::PROGRAM)];

            //Role based access
            $feeds = Program::getAllProgramByIDOrSlug('content_feed', '', $filter_params);

            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = trans('admin/assessment.title_manage_quiz');
            $this->layout->pageicon = 'fa fa-pencil-square-o';
            $this->layout->pagedescription = trans('admin/assessment.list_of_quizzes');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'assessment')
                ->with('submenu', 'quiz');
            $this->layout->content = view('admin.theme.assessment.list_quizzes')
                ->with('feeds', $feeds);
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }

    public function getListQuizAjax()
    {
        $has_list_quiz_permission = false;
        $viewmode = Input::get('view', 'desktop');
        $from = null;

        $list_quiz_permission_data_with_flag = [];
        switch ($viewmode) {
            case "desktop":
                $list_quiz_permission_data_with_flag = $this->roleService->hasPermission(
                    Auth::user()->uid,
                    ModuleEnum::ASSESSMENT,
                    PermissionType::ADMIN,
                    AssessmentPermission::LIST_QUIZ,
                    null,
                    null,
                    true
                );

                $has_list_quiz_permission = get_permission_flag($list_quiz_permission_data_with_flag);
                break;
            case "iframe":
                $from = Input::get('from', 'none');
                switch ($from) {
                    case "post":
                        $program_type = $this->request->get("program_type", null);
                        $program_slug = $this->request->get("program_slug", null);
                        $post_slug = $this->request->get("post_slug", null);
                        try {
                            $program = $this->programService->getProgramBySlug($program_type, $program_slug);
                            $this->programService->getProgramPostBySlug(
                                $program_type,
                                $program_slug,
                                $post_slug
                            );

                            if ($program_type == "course") {
                                $has_list_quiz_permission = has_admin_permission(
                                    ModuleEnum::COURSE,
                                    CoursePermission::MANAGE_COURSE_POST
                                );
                                $from = $program_type;
                            } else {
                                $list_quiz_permission_data_with_flag = $this->roleService->hasPermission(
                                    $this->request->user()->uid,
                                    ModuleEnum::CHANNEL,
                                    PermissionType::ADMIN,
                                    ChannelPermission::MANAGE_CHANNEL_POST,
                                    Contexts::PROGRAM,
                                    $program->program_id,
                                    true
                                );

                                $has_list_quiz_permission = get_permission_flag($list_quiz_permission_data_with_flag);
                            }
                        } catch (ApplicationException $e) {
                            Log::error($e->getTraceAsString());
                        }
                        break;
                }
                break;
        }
       
        if (!$has_list_quiz_permission) {
            return response()->json(
                [
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ]
            );
        }

        $filter_params = [];

        if ($viewmode == "iframe" && $from == "course") {
            $filter_params = [];
        } else {
            $list_quiz_permission_data = get_permission_data($list_quiz_permission_data_with_flag);
            if (!has_system_level_access($list_quiz_permission_data)) {
                $filter_params["in_ids"] = get_user_accessible_elements(
                    $list_quiz_permission_data,
                    ElementType::ASSESSMENT
                );
            }
        }

        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = "";
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];
        $filter = Input::get('filter');

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            switch ($order_by[0]['column']) {
                case '1':
                    $orderByArray = ['quiz_name' => $order_by[0]['dir']];
                    break;
                case '2':
                    $orderByArray = ['created_at' => $order_by[0]['dir']];
                    break;
                case '4':
                    $orderByArray = ['created_at' => $order_by[0]['dir']];
                    break;
            }
        }

        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }
        if (isset($search['value'])) {
            $searchKey = trim(strip_tags($search['value']));
        }
        if ($viewmode != 'iframe') {
            $totalRecords = Quiz::where('status', '=', 'ACTIVE')
                ->count();

            $filteredRecords = Quiz::search($searchKey)
                ->where('status', '=', 'ACTIVE')
                ->when(
                    array_has($filter_params, "in_ids"),
                    function ($query) use ($filter_params) {
                        return $query->whereIn('quiz_id', $filter_params["in_ids"]);
                    }
                )->filterByType($filter)
                ->count();

            $filtereddata = Quiz::search($searchKey)
                ->where('status', '=', 'ACTIVE')
                ->when(
                    array_has($filter_params, "in_ids"),
                    function ($query) use ($filter_params) {
                        return $query->whereIn('quiz_id', $filter_params["in_ids"]);
                    }
                )->orderBy(key($orderByArray), $orderByArray[key($orderByArray)])
                ->filterByType($filter)
                ->skip((int)$start)
                ->take((int)$limit)
                ->get();
        } else {
            $totalRecords = Quiz::where('status', '=', 'ACTIVE')
                ->where('questions.0', 'exists', true)
                ->count();

            $filteredRecords = Quiz::search($searchKey)
                ->where('status', '=', 'ACTIVE')
                ->where('questions.0', 'exists', true)
                ->when(
                    array_has($filter_params, "in_ids"),
                    function ($query) use ($filter_params) {
                        return $query->whereIn('quiz_id', $filter_params["in_ids"]);
                    }
                )->count();

            $filtereddata = Quiz::search($searchKey)
                ->where('status', '=', 'ACTIVE')
                ->where('questions.0', 'exists', true)
                ->when(
                    array_has($filter_params, "in_ids"),
                    function ($query) use ($filter_params) {
                        return $query->whereIn('quiz_id', $filter_params["in_ids"]);
                    }
                )->orderBy(key($orderByArray), $orderByArray[key($orderByArray)])
                ->skip((int)$start)
                ->take((int)$limit)
                ->get();
        }
        // Quiz edit is allowed till these many minutes of start time. default 240 minutes
        $quiz_edit_till = SiteSetting::module('General', 'edit_quiz_till');
        $dataArr = [];

        $feeds = Program::getAllContentFeeds();
        foreach ($filtereddata as $value) {
            $quiz_start_time = $value->start_time;
            $can_edit = 0;
            $sec_count = Section::getSectionInQuizCount($value->quiz_id);
            $now = Carbon::now();
            if (isset($quiz_start_time) && !empty($quiz_start_time) && $quiz_start_time->gt($now)) {
                if (($quiz_start_time->diffInMinutes($now)) > $quiz_edit_till) {
                    $can_edit = 1;
                }
            }
            //checking quiz is in production or test(beta). If it is beta edit can be allowed
            if (isset($value->is_production) && $value->is_production == 0) {
                $can_edit = 1;
            }

            $feed_rel = (isset($value->relations['feed_quiz_rel']) && !empty($value->relations['feed_quiz_rel'])) ? $value->relations['feed_quiz_rel'] : [];
            $feed_rel = array_filter($feed_rel);
            $channel_ids = array_keys($feed_rel);
            $program_ids = $this->programService->getOnlyProgramIds($channel_ids)->toArray();
            $program_ids = array_flip($program_ids);
            $feed_rel = array_intersect_key($feed_rel, $program_ids);
            $feed_value = [];
            foreach ($feed_rel as $key => $fvalue) {
                $feed_value[$key] = $fvalue;
            }
            $user_rel = (isset($value->relations['active_user_quiz_rel']) && !empty($value->relations['active_user_quiz_rel'])) ? $value->relations['active_user_quiz_rel'] : [];
            $usergrp_rel = (isset($value->relations['active_usergroup_quiz_rel']) && !empty($value->relations['active_usergroup_quiz_rel'])) ? $value->relations['active_usergroup_quiz_rel'] : [];
           
            if (!is_admin_role(Auth::user()->role)) {
                $user_rel = array_values(array_intersect(get_user_ids($list_quiz_permission_data), $user_rel));
                $usergrp_rel = array_values(array_intersect(get_user_group_ids($list_quiz_permission_data), $usergrp_rel));
            }
            
            $delete_rel = (!empty($feed_rel) || !empty($user_rel) || !empty($usergrp_rel)) ? 'disabled' : '';
            $actions = '';
            $mapping = '';
            $is_attempted = $this->quiz_attempt_service->hasAttemptes($value->quiz_id);
            if (!(isset($value->type) && ($value->type === "QUESTION_GENERATOR"))) {
                if ($is_attempted) {
                    $actions .= '<a class="btn btn-circle show-tooltip ajax" title="' . trans('admin/assessment.quiz_reports') . '" href="' . URL::to("/cp/assessment/report-quiz/" . $value->quiz_id) . '?start=' . $start . '&limit=' . $limit . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-bar-chart-o"></i></a>';
                }
            }

            if (has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::EDIT_QUIZ)) {
                if ($can_edit == 1 && !$is_attempted) {
                    if (isset($value->type) && ($value->type === "QUESTION_GENERATOR")) {
                        $actions .= '<a class="btn btn-circle show-tooltip ajax" title="' . trans('admin/manageweb.action_edit') . '" href="' . URL::to("/cp/assessment/edit-quiz/" . $value->quiz_id) . '?type=QUESTION_GENERATOR&start=' . $start . '&limit=' . $limit . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-edit"></i></a>';
                    } else {
                        $actions .= '<a class="btn btn-circle show-tooltip ajax" title="' . trans('admin/manageweb.action_edit') . '" href="' . URL::to("/cp/assessment/edit-quiz/" . $value->quiz_id) . '?start=' . $start . '&limit=' . $limit . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-edit"></i></a>';
                    }
                } else {
                    if (!$is_attempted) {
                        $no_edit = str_replace('$quiz_edit_till', $quiz_edit_till, trans('admin/manageweb.no_action_edit'));
                    } else {
                        $no_edit = str_replace('$quiz_edit_till', $quiz_edit_till, trans('admin/assessment.quiz_attempted'));
                    }
                    $actions .= '<a class="btn btn-circle show-tooltip" title="' . $no_edit . '"><i class="fa fa-edit"></i></a>';
                }
            }

            if (has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::DELETE_QUIZ)) {
                if ((!empty($feed_rel) || !empty($user_rel) || !empty($usergrp_rel))) {
                    $actions .= '<a class="btn btn-circle show-tooltip ajax" title="' . trans("admin/assessment.quiz_in_use") . '"><i class="fa fa-trash-o"></i></a>';
                } else {
                    $class = "";
                    $title = trans('admin/manageweb.action_delete');
                    $actions .= '<a class="btn btn-circle show-tooltip ajax deletequiz" ' . $class . ' title="' . $title . '" href="' . URL::to("/cp/assessment/delete-quiz/" . $value->quiz_id) . '?start=' . $start . '&limit=' . $limit . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-trash-o"></i></a>';
                }
            }

            //Quiz export for questionbank
            if (has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::EXPORT_QUESTION_BANK)) {
                if (count($value->questions) > 0) {
                    $actions .= '<a class="btn btn-circle show-tooltip" title="' . trans('admin/assessment.quiz_export_with_questions') . '" href="' . URL::to("/cp/assessment/quiz-question-mapping/" . $value->quiz_id) . '?start=' . $start . '&limit=' . $limit . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-bank"></i></a>';
                } else {
                    $actions .= '<a class="btn btn-circle show-tooltip" title="' . trans('admin/assessment.no_questions_to_export_quiz') . '"><i class="fa fa-bank"></i></a>';
                }
            }

            //         quiz export for channels
            if (has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::EXPORT_QUIZ)) {
                if (count($feed_rel) > 0) {
                    $actions .= '<a class="btn btn-circle show-tooltip" title="' . trans('admin/assessment.quiz_export_with_channel') . '" href="' . URL::to("/cp/assessment/channel-post-mapping/" . $value->quiz_id) . '?start=' . $start . '&limit=' . $limit . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '"><i class="fa fa fa-rss"></i></a>';
                } else {
                    $actions .= '<a class="btn btn-circle show-tooltip" title="' . trans('admin/assessment.no_channels_to_export_quiz') . '"><i class="fa fa-rss"></i></a>';
                }
            }

            if (isset($value->type) && $value->type === "QUESTION_GENERATOR") {
                $mapping = '<a href="#" class="btn btn-circle show-tooltip concept-mapping" data-id="' . $value->quiz_id . '" data-title="' . trans('admin/assessment.generate_key_map') . '"><i class="glyphicon glyphicon-transfer"></i></a>';
            }

            if ($viewmode == 'iframe') {
                $temparr = [
                    '<input type="checkbox" value="' . $value->quiz_id . '">',
                    str_limit($value->quiz_name, 40) . "&nbsp;&nbsp;" . ((isset($value->type) && ($value->type === "QUESTION_GENERATOR")) ? "<span class=\"label label-warning\" style=\"font-weight:bold;\">" . trans('assessment.question_generator') . "</span>" : "") . ((isset($value->is_production) && ($value->is_production == 0)) ? "<span class=\"label label-info\" style=\"font-weight:bold;\">" . trans('admin/assessment.beta') . "</span>" : ""),
                    $value->created_at->timezone(Auth::user()->timezone)->format(config('app.date_format')),
                ];
            } else {
                $temparr = [
                    '<input type="checkbox" value="' . $value->quiz_id . '">',
                    '<span class="show-tooltip" title="' . $value->quiz_name . '">'. str_limit(
                        $value->quiz_name,
                        40
                    )  .' </span>&nbsp;&nbsp;' . ((isset($value->type) && ($value->type === "QUESTION_GENERATOR")) ? "<span class=\"label label-warning\" style=\"font-weight:bold;\">" . trans('assessment.question_generator') . "</span>" : "") . ((isset($value->is_production) && ($value->is_production == 0)) ? "<span class=\"label label-info\" style=\"font-weight:bold;\">" . trans('admin/assessment.beta') . "</span>" : ""),
                    "<a href='" . ((isset($value->is_sections_enabled) && $value->is_sections_enabled) ? 'javascript:void;' : URL::to('/cp/assessment/quiz-questions/' . $value->quiz_id)) . "' title='" . trans('admin/assessment.assign_ques') . "' class='badge show-tooltip " . ((isset($value->is_sections_enabled) && $value->is_sections_enabled) ? "NA" : ((count($value->questions) > 0) ? 'badge-success' : 'badge-grey')) . "'>" . ((isset($value->is_sections_enabled) && $value->is_sections_enabled) ? count($value->questions) : count($value->questions)) . "</a>",
                    "<a href='" . ((isset($value->is_sections_enabled) && $value->is_sections_enabled) ? URL::to('/cp/section/list-section/' . $value->quiz_id) : 'javascript:void;') . "' title='" . trans('admin/assessment.list_section') . "' class='badge show-tooltip " . ((isset($value->is_sections_enabled) && $value->is_sections_enabled) ? (($sec_count > 0) ? 'badge-success' : 'badge-grey') : "NA") . "'>" . ((isset($value->is_sections_enabled) && $value->is_sections_enabled) ? $sec_count : "NA") . "</a>",
                    $value->created_at->timezone(Auth::user()->timezone)->format(config('app.date_format')),
                    $actions . $mapping,

                ];
            }

            if ($viewmode != 'iframe') {
                if (has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_POST)) {
                    if (count($feeds) < 1) {
                        $content_feeds = "<a href='#' title='" . trans('admin/assessment.no_channel') . "' class='badge show-tooltip " . ((count($feed_rel) > 0) ? 'badge-success' : 'badge-grey') . "' data-key='" . $value->quiz_id . "' data-info='feed' data-text='Assign " . trans('admin/program.program') . " to <b>" . htmlentities('"' . $value->quiz_name . '"', ENT_QUOTES) . "</b>' data-json='" . json_encode($feed_value) . "'>" . count($feed_rel) . "</a>";
                    } elseif (count($value->questions) > 0) {
                        $content_feeds = "<a href='' title='" . trans('admin/assessment.assign_channel') . "' class='quizrel badge show-tooltip " . ((count($feed_rel) > 0) ? 'badge-success' : 'badge-grey') . "' data-key='" . $value->quiz_id . "' data-info='feed' data-text='Assign " . trans('admin/program.program') . " to <b>" . htmlentities('"' . $value->quiz_name . '"', ENT_QUOTES) . "</b>' data-json='" . json_encode($feed_value) . "'>" . count($feed_rel) . "</a>";
                    } else {
                        $content_feeds = "<a href='#' title='" . trans('admin/assessment.add_quest_to_assign_channel') . "' class='badge show-tooltip " . ((count($feed_rel) > 0) ? 'badge-success' : 'badge-grey') . "'>" . count($feed_rel) . "</a>";
                    }
                } else {
                    $content_feeds = "<a href='#' title='" . trans('admin/assessment.assign_channel_permission_denied') . "' class='badge show-tooltip " . ((count($feed_rel) > 0) ? 'badge-success' : 'badge-grey') . "'>" . count($feed_rel) . "</a>";
                }

                if (has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::QUIZ_ASSIGN_USER)) {
                    if (count($value->questions) > 0) {
                        $users = "<a href='" . URL::to('/cp/usergroupmanagement?filter=ACTIVE&view=iframe&from=quiz&relid=' . $value->quiz_id) . "' title='" . trans('admin/assessment.assign_user') . "' class='quizrel badge show-tooltip " . ((count($user_rel) > 0) ? 'badge-success' : 'badge-grey') . "' data-key='" . $value->quiz_id . "' data-info='user' data-text='Assign User(s) to <b>" . htmlentities('"' . $value->quiz_name . '"', ENT_QUOTES) . "</b>' data-json='" . json_encode($user_rel) . "'>" . count($user_rel) . "</a>";
                    } else {
                        $users = "<a href='#' title='" . trans('admin/assessment.add_ques_to_user') . "' class='badge show-tooltip " . ((count($user_rel) > 0) ? 'badge-success' : 'badge-grey') . "'>" . count($user_rel) . "</a>";
                    }
                } else {
                    $users = "<a href='#' title=\"" . trans('admin/assessment.no_permi_to_assign_user') . " \" class='badge show-tooltip " . ((count($user_rel) > 0) ? 'badge-success' : 'badge-grey') . "'>" . count($user_rel) . "</a>";
                }
                if (has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::QUIZ_ASSIGN_USER_GROUP)) {
                    if (count($value->questions) > 0) {
                        $user_groups = "<a href='" . URL::to('/cp/usergroupmanagement/user-groups?filter=ACTIVE&view=iframe&from=quiz&relid=' . $value->quiz_id) . "' title='" . trans('admin/assessment.assign_usergroup') . "' class='quizrel badge show-tooltip " . ((count($usergrp_rel) > 0) ? 'badge-success' : 'badge-grey') . "' data-key='" . $value->quiz_id . "' data-info='usergroup' data-text='Assign User Group(s) to <b>" . htmlentities('"' . $value->quiz_name . '"', ENT_QUOTES) . "</b>' data-json='" . json_encode($usergrp_rel) . "'>" . count($usergrp_rel) . "</a>";
                    } else {
                        $user_groups = "<a href='#' title='" . trans('admin/assessment.add_ques_to_usergroup') . "' class='badge show-tooltip " . ((count($usergrp_rel) > 0) ? 'badge-success' : 'badge-grey') . "'>" . count($usergrp_rel) . "</a>";
                    }
                } else {
                    $user_groups = "<a href='#' title=\"" . trans('admin/assessment.no_permi_to_assign_usergroup') . "\"  class='badge show-tooltip " . ((count($usergrp_rel) > 0) ? 'badge-success' : 'badge-grey') . "'>" . count($usergrp_rel) . "</a>";
                }
                array_splice($temparr, 5, 0, [$content_feeds, $users, $user_groups]);
            }
            $dataArr[] = $temparr;
        }
        if ($viewmode == 'iframe') {
            $totalRecords = $filteredRecords;
        }
        $finaldata = [
            'recordsTotal' => $filteredRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr
        ];
        return response()->json($finaldata);
    }

    public function getAddQuiz()
    {
        if (!has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::ADD_QUIZ)) {
            return parent::getAdminError();
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assessment.title_manage_quiz') => 'assessment/list-quiz',
            trans('admin/assessment.add_quiz') => ''
        ];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/assessment.add_quiz');
        $this->layout->pageicon = 'fa fa-pencil-square-o';
        $this->layout->pagedescription = trans('admin/assessment.add_quiz');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'assessment')
            ->with('submenu', 'quiz');
        $this->layout->content = view('admin.theme.assessment.add_quiz');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function postAddQuiz(Request $request)
    {
        if (!has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::ADD_QUIZ)) {
            return parent::getAdminError();
        }
        Validator::extend('check_quiz_slug', function ($attribute, $value, $parameters) {
            $exists = Quiz::where('slug', '=', $parameters[0])->where('status', '!=', 'DELETED')->count();
            if ($exists) {
                return false;
            }
            return true;
        });
        Validator::extend('valid_percentage', function ($attribute, $value, $parameters) use ($request) {
            if ($request->has('cut_off_format') && $request->cut_off_format == QCFT::PERCENTAGE) {
                return $value <=100 ? true : false;
            }
            return true;
        });
        Input::flash();
        $quiz_slug = Quiz::getQuizSlug(Input::get('quiz_name'));

        $rules = [
            'quiz_name' => 'required|max:512|check_quiz_slug:' . $quiz_slug,
            'attempts' => 'required|integer',
            // 'question_per_page' => 'required|integer',
            'start_date' => 'required',
            'negative_mark_attempted_question' => 'Regex:/^([0-9])+$/|numeric|max:100',
            'negative_mark_un_attempted_question' => 'Regex:/^([0-9])+$/|numeric|max:100',
            'cut_off' => 'required_with:cut_off_format|Regex:/^([0-9])+$/|numeric|min:1|valid_percentage',
            'cut_off_format' => 'required_with:cut_off|in:'.QCFT::toString(),
        ];

        $messages = [
            'start_date.required' => trans('admin/assessment.date_required'),
            'quiz_name.check_quiz_slug' => trans('admin/assessment.quiz_exits'),
            'cut_off.valid_percentage' => trans('admin/assessment.cut_off_max'),
        ];

        $validation = Validator::make($request->all(), $rules, $messages);
        $validation->sometimes("end_date", "required", function () use (&$request) {
            return !$request->has("practice_quiz");
        });

        $validation->sometimes("duration", "required", function () use (&$request) {
            return $request->has("practice_quiz");
        });

        if ($validation->fails()) {
            return redirect('cp/assessment/add-quiz')
                ->withInput()
                ->withErrors($validation);
        } else {
            $quiz_id = Quiz::getNextSequence();
            $start_time = $end_time = $duration = 0;

            // Quiz start time
            if ($request->has('start_date')) {
                $start_time = Timezone::convertToUTC($request->start_date, Auth::user()->timezone, 'U');
                if ($request->has('start_time')) {
                    $temp = explode(':', trim($request->start_time));
                    $start_time += (($temp[0] * 60) + $temp[1]) * 60;
                }
            }
            // Quiz end time
            if ($request->has('end_date')) {
                $end_time = Timezone::convertToUTC($request->end_date, Auth::user()->timezone, 'U');
                if ($request->has('end_time')) {
                    $temp = explode(':', trim($request->end_time));
                    $end_time += (($temp[0] * 60) + $temp[1]) * 60;
                }
            }
            $error = false;
            // Quiz duration
            if ($request->has('duration')) {
                $temp = explode(':', trim($request->duration));
                $duration = ($temp[0] * 60) + $temp[1];
                if ($duration <= 0 && !$request->has("practice_quiz")) {
                    // $duration = 5;
                    $error = true;
                    $validation->getMessageBag()->add('duration', 'Duration should not be a zero');
                }
            }

            // Endtime & duration check
            if ($request->has('start_date') && $request->has('end_date')) {
                if ($start_time > $end_time) {
                    $error = true;
                    $validation->getMessageBag()->add('end_date', 'Expiry date should be always higher than schedule date');
                } elseif (($end_time - $start_time) < ($duration * 60)) {
                    $error = true;
                    $validation->getMessageBag()->add('duration', 'Given duration can not fit in this scheduled time');
                }
                /*if ($error) {
                    return redirect('cp/assessment/add-quiz/')
                        ->withInput()
                        ->withErrors($validation);
                }*/
            }
            if ($error) {
                return redirect('cp/assessment/add-quiz/')
                    ->withInput()
                    ->withErrors($validation);
            }
            $qdata = [
                'quiz_id' => $quiz_id,
                'quiz_name' => trim(strip_tags($request->quiz_name)),
                'slug' => $quiz_slug,
                'quiz_description' => $request->quiz_description,
                'keywords' => ($request->has('keywords')) ? array_map('trim', explode(',', strip_tags($request->keywords))) : [],
                'attempts' => (int)round($request->attempts),
                'start_time' => (int)$start_time,
                'end_time' => (int)$end_time,
                'duration' => (int)round($duration),
                'is_score_display' => ($request->score_display == "on") ? true : false,
                'practice_quiz' => ($request->has('practice_quiz')) ? true : false,
                'shuffle_questions' => ($request->has('shuffle_questions')) ? true : false,
                "is_sections_enabled" => ($request["enable_sections"] === "TRUE") ? true : false,
                "is_timed_sections" => ($request["timed_sections"] === 'TRUE') ? true : false,
                'page_layout' => new stdClass,
                'question_per_page' => 1,
                'total_mark' => 0,
                'concept_tagging' => 0,
                'attempt_neg_mark' => (float)$request->negative_mark_attempted_question,
                'un_attempt_neg_mark' => (float)$request->negative_mark_un_attempted_question,
                'editor_images' => $request->input('editor_images', []),
                'relations' => [
                    'active_user_quiz_rel' => [],
                    'inactive_user_quiz_rel' => [],
                    'active_usergroup_quiz_rel' => [],
                    'inactive_usergroup_quiz_rel' => [],
                    'feed_quiz_rel' => new stdClass
                ],
                'review_options' => [
                    'the_attempt' => ($request->has('review_the_attempt')) ? true : false,
                    'whether_correct' => ($request->has('review_the_attempt') && $request->has('review_whether_correct')) ? true : false,
                    'marks' => ($request->has('review_whether_correct') && $request->has('review_marks')) ? true : false,
                    'rationale' => ($request->has('review_whether_correct') && $request->has('review_rationale')) ? true : false,
                    'correct_answer' => ($request->has('review_whether_correct') && $request->has('review_correct_answer')) ? true : false,
                ],
                'reference_cut_off' => $request->reference_cut_off,
                'status' => 'ACTIVE',
                'is_production' => (int)($request['environment_selected'] === "is_beta") ? 0 : 1,
                'created_by' => Auth::user()->username,
                'created_at' => time(),
                'updated_by' => '',
                'updated_at' => time()
            ];
            if ($request->has('pass_criteria')) {
                $qdata['pass_criteria'] = $request->pass_criteria;
            }
            if ($request->has('cut_off') && $request->cut_off > 0) {
                $qdata['cut_off'] = (int)$request->cut_off;
                if ($request->has('pass_criteria') &&
                    ($request->pass_criteria == 'QUIZ_ONLY' || $request->pass_criteria == 'QUIZ_AND_SECTIONS')
                ) {
                    if ($qdata['is_sections_enabled']) {
                        $qdata['pass_criteria'] = $request->pass_criteria;
                    } else {
                        $qdata['pass_criteria'] = 'QUIZ_ONLY';
                    }
                } else {
                    $qdata['pass_criteria'] = 'QUIZ_ONLY';
                }
                $qdata['cut_off_format'] = $request->cut_off_format;
                $qdata['cut_off_mark'] = $request->cut_off_format == 'mark' ? $request->cut_off : 0;
            }
            if (!$qdata["is_sections_enabled"]) {
                $qdata["questions"] = [];
            }

            if (Quiz::insert($qdata)) {
                if (config('elastic.service')) {
                    event(new QuizAdded($quiz_id));
                }
                return redirect('cp/assessment/success-quiz/' . $quiz_id)->with('success', trans('admin/assessment.quiz_success'));
            } else {
                return redirect('cp/assessment/list-quiz')
                    ->with('error', trans('admin/assessment.problem_while_creating_new_quiz'));
            }
        }
    }

    public function postAddQuestionGenerator(Request $request, Quiz $quiz)
    {
        if ($request->has("r-q-g-display-start-date")) {
            $tmpData1 = $request["r-q-g-display-start-date"];
            $displayStartDate = Timezone::convertToUTC(
                $request->input("r-q-g-display-start-date"),
                Auth::user()->timezone,
                "U"
            );
            if ($request->has("r-q-g-display-start-time")) {
                $displayStartDate += DateTimeHelper::convertTimeStringToSeconds(
                    $request->input("r-q-g-display-start-time")
                );
            }

            $request["r-q-g-display-start-date"] = $displayStartDate;
        }

        if ($request->has("r-q-g-display-end-date")) {
            $tmpData2 = $request["r-q-g-display-end-date"];
            $displayEndDate = Timezone::convertToUTC(
                $request->input("r-q-g-display-end-date"),
                Auth::user()->timezone,
                "U"
            );
            if ($request->has("r-q-g-display-end-time")) {
                $displayEndDate += DateTimeHelper::convertTimeStringToSeconds(
                    $request->input("r-q-g-display-end-time")
                );
            }

            $request["r-q-g-display-end-date"] = $displayEndDate;
        }

        $validator = QuizValidator::getQuizValidator("QUESTION_GENERATOR", "add", $request->all());
        if ($validator->passes()) {
            $quiz = QuizHelper::getQuestionGeneratorData($request);
            $quiz['quiz_id'] = Quiz::getNextSequence();
            $quiz['created_by'] = Auth::user()->username;
            $quiz['created_at'] = time();
            Quiz::insert($quiz);
            if (config('elastic.service')) {
                event(new QuizAdded($quiz['quiz_id']));
            }
            return redirect("cp/assessment/success-quiz/{$quiz['quiz_id']}")
                ->with("success", trans("admin/assessment.quiz_success"));
        } else {
            if (isset($tmpData1)) {
                $request["r-q-g-display-start-date"] = $tmpData1;
            }
            if (isset($tmpData2)) {
                $request["r-q-g-display-end-date"] = $tmpData2;
            }
            return redirect("cp/assessment/add-quiz")
                ->with("type", "QUESTION_GENERATOR")->withInput()->withErrors($validator);
        }
    }

    public function postEditQuestionGenerator(Request $request, $questionGeneratorId = null)
    {
        try {
            if (is_null($questionGeneratorId)) {
                throw new Exception();
            }
            $questionGenerator = Quiz::getQuizById($request["q-g-uid"]);
            if ($request->has("r-q-g-display-start-date")) {
                $tmpData1 = $request["r-q-g-display-start-date"];
                $displayStartDate = Timezone::convertToUTC(
                    $request->input("r-q-g-display-start-date"),
                    Auth::user()->timezone,
                    "U"
                );
                if ($request->has("r-q-g-display-start-time")) {
                    $displayStartDate += DateTimeHelper::convertTimeStringToSeconds(
                        $request->input("r-q-g-display-start-time")
                    );
                }

                $request["r-q-g-display-start-date"] = $displayStartDate;
            }

            if ($request->has("r-q-g-display-end-date")) {
                $tmpData2 = $request["r-q-g-display-end-date"];
                $displayEndDate = Timezone::convertToUTC(
                    $request->input("r-q-g-display-end-date"),
                    Auth::user()->timezone,
                    "U"
                );
                if ($request->has("r-q-g-display-end-time")) {
                    $displayEndDate += DateTimeHelper::convertTimeStringToSeconds(
                        $request->input("r-q-g-display-end-time")
                    );
                }

                $request["r-q-g-display-end-date"] = $displayEndDate;
            }

            $validator = QuizValidator::getQuizValidator(
                "QUESTION_GENERATOR",
                "edit",
                $request->all(),
                [],
                ["quiz_id" => $questionGenerator->quiz_id]
            );
            if ($validator->passes()) {
                $data['quiz_name'] = trim(strip_tags($request["r-q-g-name"]));
                $data['slug'] = Quiz::getQuizSlug($request['r-q-g-name']);
                $data['quiz_description'] = $request["r-q-g-instructions"];
                $data['keywords'] = ($request->has("r-q-g-keywords")) ? array_map("trim", explode(',', strip_tags($request["r-q-g-keywords"]))) : [];
                $data['total_question_limit'] = $request["r-q-g-total-question-limit"];
                if ($request->has('r-q-g-enable-sections')) {
                    $data['is_sections_enabled'] = ($request["r-q-g-enable-sections"] === "TRUE") ? true : false;
                }
                if ((!$questionGenerator->is_sections_enabled) && (!isset($questionGenerator->questions))) {
                    $data['questions'] = [];
                }
                $data['concept_tagging'] = (int)0;
                $data['start_time'] = (int)$request["r-q-g-display-start-date"];
                $data['end_time'] = (int)$request["r-q-g-display-end-date"];
                $data['is_production'] = (int)($request['r-q-g-is_production'] === 'is_beta') ? 0 : 1;
                Quiz::where('quiz_id', (int)$questionGenerator->quiz_id)->update($data);
                if (config('elastic.service')) {
                    event(new QuizEdited($questionGenerator->quiz_id));
                }
                return redirect("cp/assessment/list-quiz")
                    ->with("success", trans("admin/assessment.quiz_updated_successfully"));
            } else {
                return redirect("cp/assessment/edit-quiz/{$questionGenerator->quiz_id}?type=QUESTION_GENERATOR")
                    ->withInput()
                    ->withErrors($validator);
            }
        } catch (Exception $e) {
            return redirect('cp/assessment/list-quiz')
                ->with("error", "Something went wrong. Please try again later.");
        }
    }

    public function getSuccessQuiz(Request $request, $qid)
    {
        if (!has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::ADD_QUIZ)) {
            return parent::getAdminError();
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assessment.title_manage_quiz') => 'assessment/list-quiz',
            trans('admin/assessment.success_quiz') => ''
        ];

        if (!is_numeric($qid)) {
            abort(404);
        }

        // Checking whether given quiz is available in db
        $quiz = Quiz::where('quiz_id', '=', (int)$qid)->firstOrFail();
        $feeds = Program::getAllContentFeeds();
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/assessment.quiz');
        $this->layout->pageicon = 'fa fa-question-circle';
        $this->layout->pagedescription = trans('admin/assessment.quiz_success');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'assessment')
            ->with('submenu', 'quiz');
        $this->layout->content = view('admin.theme.assessment.success_quiz')
            ->with("quiz", $quiz)
            ->with("feeds", $feeds);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getEditQuiz(Request $request, $qid)
    {
        $edit_quiz_permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ASSESSMENT,
            PermissionType::ADMIN,
            AssessmentPermission::EDIT_QUIZ,
            null,
            null,
            true
        );

        $edit_quiz_permission_data = get_permission_data($edit_quiz_permission_data_with_flag);

        if (!is_element_accessible($edit_quiz_permission_data, ElementType::ASSESSMENT, $qid)) {
            return parent::getAdminError();
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assessment.title_manage_quiz') => 'assessment/list-quiz',
            trans('admin/assessment.edit_quiz') => ''
        ];

        if (!is_numeric($qid)) {
            abort(404);
        }

        $type = $request->input("type", null);

        // Checking whether given quiz is available in db
        $quiz = Quiz::where('quiz_id', '=', (int)$qid)->firstOrFail();
        $cut_off_readonly = false;
        if (isset($quiz->is_sections_enabled) && $quiz->is_sections_enabled && isset($quiz->questions) && !empty($quiz->questions)) {
            $cut_off_readonly = true;
        }
        $attempt = QuizAttempt::where('quiz_id', '=', (int)$qid)
            ->get();
        if ((is_null($quiz) || (!$attempt->isEmpty()) && !$quiz->beta)) {
            abort(404);
        }

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/assessment.edit_quiz');
        $this->layout->pageicon = 'fa fa-pencil-square-o';
        $this->layout->pagedescription = '';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'assessment')
            ->with('submenu', 'quiz');
        $this->layout->content = view('admin.theme.assessment.edit_quiz')
            ->with('quiz', $quiz)
            ->with('cut_off_readonly', $cut_off_readonly)
            ->with("type", $type);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function postEditQuiz(Request $request)
    {
        $permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ASSESSMENT,
            PermissionType::ADMIN,
            AssessmentPermission::EDIT_QUIZ,
            null,
            null,
            true
        );

        if (!is_element_accessible(
            get_permission_data($permission_data_with_flag),
            ElementType::ASSESSMENT,
            $request->_q
        )) {
            return parent::getAdminError();
        }

        Validator::extend('check_quiz_slug', function ($attribute, $value, $parameters) {
            $quizes[] = (int)$parameters[1];
            $exists = Quiz::where('slug', '=', $parameters[0])
                ->where('type', '!=', 'QUESTION_GENERATOR')
                ->where('status', '!=', 'DELETED')
                ->whereNotIn('quiz_id', $quizes)
                ->count();
            if ($exists) {
                return false;
            }
            return true;
        });

        // Checking whether given quiz is available in db
        $quiz = Quiz::where('quiz_id', '=', (int)$request->_q)->firstOrFail();
        $quiz_slug = Quiz::getQuizSlug(Input::get('quiz_name'));
        $cut_off_rule = 'required_with:cut_off_format|Regex:/^([0-9])+$/|numeric|min:1|valid_percentage';
        $cut_off_format = isset($quiz->cut_off_format) && !empty($quiz->cut_off_format);
        if ($cut_off_format) {
            $cut_off_rule = 'required:cut_off_format|Regex:/^([0-9])+$/|numeric|min:1|valid_percentage';
        }
        $cut_off_formats = QCFT::toString();
        $cut_off_format_rule = 'required_with:cut_off|in:'.$cut_off_formats;
        $cut_off_readonly = false;
        if (isset($quiz->is_sections_enabled) && $quiz->is_sections_enabled && isset($quiz->questions) && !empty($quiz->questions)) {
            $cut_off_readonly = true;
            $cut_off_format_rule = 'in:'.$cut_off_formats;
        }
        Validator::extend('valid_percentage', function ($attribute, $value, $parameters) use ($request, $cut_off_readonly, $cut_off_format) {
            if (($request->has('cut_off_format') && $request->cut_off_format == QCFT::PERCENTAGE) || $cut_off_format) {
                return $value <=100 ? true : false;
            }
            return true;
        });
        $tmp_data = $quiz_slug . ',' . $quiz->quiz_id;
        $rules = [
            'quiz_name' => 'required|max:512|check_quiz_slug:' . $tmp_data,
            'attempts' => 'required|integer',
            // 'question_per_page' => 'required|integer',
            'start_date' => 'required',
            'negative_mark_attempted_question' => 'Regex:/^([0-9])+$/|numeric|max:100',
            'negative_mark_un_attempted_question' => 'Regex:/^([0-9])+$/|numeric|max:100',
            'cut_off' => $cut_off_rule,
            'cut_off_format' => $cut_off_format_rule,
        ];

        $messages = [
            'start_date.required' => trans('admin/assessment.date_required'),
            'quiz_name.check_quiz_slug' => trans('admin/assessment.quiz_exits'),
            'cut_off.valid_percentage' => trans('admin/assessment.cut_off_max'),
        ];

        $validation = Validator::make(Input::all(), $rules, $messages);

        $validation->sometimes("end_date", "required", function () use (&$request) {
            return !$request->has("practice_quiz");
        });

        /* $validation->sometimes("duration", "required", function() use (&$request){
             return !$request->has("practice_quiz");
         });*/

        if ($validation->fails()) {
            return redirect('cp/assessment/edit-quiz/' . $quiz->quiz_id)
                ->withInput()
                ->withErrors($validation);
        } else {
            // Initialize the values
            $start_time = (!empty($quiz->start_time)) ? $quiz->start_time->getTimestamp() : '';
            $end_time = (!empty($quiz->end_time)) ? $quiz->end_time->getTimestamp() : '';
            $duration = $quiz->duration;
            // Quiz start time
            if ($request->has('start_date')) {
                $start_time = Timezone::convertToUTC($request->start_date, Auth::user()->timezone, 'U');
                if ($request->has('start_time')) {
                    $temp = explode(':', trim($request->start_time));
                    $start_time += (($temp[0] * 60) + $temp[1]) * 60;
                }
            }
            // Quiz end time
            if ($request->has('end_date')) {
                $end_time = Timezone::convertToUTC($request->end_date, Auth::user()->timezone, 'U');
                if ($request->has('end_time')) {
                    $temp = explode(':', trim($request->end_time));
                    $end_time += (($temp[0] * 60) + $temp[1]) * 60;
                }
            }
            // Quiz duration
            $error = false;
            if ($request->has('duration')) {
                $temp = explode(':', trim($request->duration));
                $duration = ($temp[0] * 60) + $temp[1];
                if ($duration <= 0 && !$request->has("practice_quiz")) {
                    $error = true;
                    $validation->getMessageBag()->add('duration', 'duration can not be zero');
                }
            }
            // Endtime & duration check
            if ($request->has('start_date') && $request->has('end_date')) {
                if ($start_time > $end_time) {
                    $error = true;
                    $validation->getMessageBag()->add('end_date', 'Expiry date should be always higher than schedule date');
                } elseif (($end_time - $start_time) < ($duration * 60)) {
                    $error = true;
                    $validation->getMessageBag()->add('duration', 'Given duration can not fit in this scheduled time');
                }
                if ($error) {
                    return redirect('cp/assessment/edit-quiz/' . $quiz->quiz_id)
                        ->withInput()
                        ->withErrors($validation);
                }
            }

            // Editer images
            $editer_images = $quiz->editor_images;
            if (is_array($editer_images)) {
                $editer_images = array_merge($editer_images, $request->input('editor_images', []));
            } else {
                $editer_images = $request->input('editor_images');
            }
            $qdata = [
                'quiz_name' => trim(strip_tags($request->quiz_name)),
                'slug' => $quiz_slug,
                'quiz_description' => $request->quiz_description,
                'keywords' => ($request->has('keywords')) ? array_map('trim', explode(',', strip_tags($request->keywords))) : [],
                'attempts' => (int)round($request->attempts),
                'start_time' => (int)$start_time,
                'end_time' => (int)$end_time,
                'duration' => (int)round($duration),
                'is_score_display' => ($request->score_display == "on") ? true : false,
                'concept_tagging' => 0,
                'practice_quiz' => ($request->has('practice_quiz')) ? true : false,
                'shuffle_questions' => ($request->has('shuffle_questions')) ? true : false,
                'question_per_page' => 1,//(int) round($request->question_per_page),
                'attempt_neg_mark' => (float)$request->negative_mark_attempted_question,
                'un_attempt_neg_mark' => (float)$request->negative_mark_un_attempted_question,
                'review_options' => [
                    'the_attempt' => ($request->has('review_the_attempt')) ? true : false,
                    'whether_correct' => ($request->has('review_the_attempt') && $request->has('review_whether_correct')) ? true : false,
                    'marks' => ($request->has('review_whether_correct') && $request->has('review_marks')) ? true : false,
                    'rationale' => ($request->has('review_whether_correct') && $request->has('review_rationale')) ? true : false,
                    'correct_answer' => ($request->has('review_whether_correct') && $request->has('review_correct_answer')) ? true : false,
                ],
                'reference_cut_off' => $request->reference_cut_off,
                'editor_images' => $editer_images,
                'is_production' => (int)($request['environment_selected'] === "is_beta") ? 0 : 1,
                'updated_by' => Auth::user()->username,
                'updated_at' => time()
            ];
            if ($request->has('enable_sections')) {
                $qdata['is_sections_enabled'] = $request->enable_sections === "TRUE" ? true : false;
            } else {
                if (!empty($quiz->questions)) {
                    $qdata['is_sections_enabled'] = isset($quiz->is_sections_enabled) ? $quiz->is_sections_enabled : false;
                } else {
                    $qdata['is_sections_enabled'] = false;
                }
            }
            if ($request->has('timed_sections')) {
                $qdata["is_timed_sections"] = $request->enable_sections === "TRUE" ? ($request->timed_sections === 'TRUE' ? true : false) : false;
            } else {
                if (!empty($quiz->questions)) {
                    $qdata["is_timed_sections"] = $qdata["is_sections_enabled"] ? ((isset($quiz->is_timed_sections)) ? $quiz->is_timed_sections : false) : false;
                } else {
                    $qdata['is_timed_sections'] = $qdata["is_sections_enabled"] ? ((isset($quiz->is_timed_sections) && $quiz->is_timed_sections) ? (!$request->has('timed_sections') ? false : true) : false) : false;
                }
            }
            if ($request->has('pass_criteria')) {
                $qdata['pass_criteria'] = $request->pass_criteria;
            }
            if ($request->has('cut_off') && $request->cut_off > 0) {
                $qdata['cut_off'] = (int)$request->cut_off;
                if ($request->has('pass_criteria') && ($request->pass_criteria == 'QUIZ_ONLY' || $request->pass_criteria == 'QUIZ_AND_SECTIONS')) {
                    $qdata['pass_criteria'] = $request->pass_criteria;
                } else {
                    $qdata['pass_criteria'] = 'QUIZ_ONLY';
                }
                $qdata['cut_off_format'] = (!$cut_off_readonly && $request->has('cut_off_format')) ? $request->cut_off_format : $quiz->cut_off_format;
                $qdata['cut_off_mark'] = QuizHelper::getCutOffMark($request->cut_off, $quiz->total_mark, $qdata['cut_off_format']);
            }
            if (!$qdata['is_sections_enabled']) {
                $qdata['questions'] = $quiz->questions ? $quiz->questions : [];
            }
            if (Input::get('post_slug')) {
                $post_slug = Input::get('post_slug');
                if (Quiz::where('quiz_id', '=', (int)$quiz->quiz_id)
                    ->update($qdata)
                ) {
                    if (config('elastic.service')) {
                        event(new QuizEdited($quiz->quiz_id));
                    }
                    return redirect('cp/contentfeedmanagement/elements/' . $post_slug);
                }
            } elseif (Quiz::where('quiz_id', '=', (int)$quiz->quiz_id)
                ->update($qdata)
            ) {
                if (config('elastic.service')) {
                    event(new QuizEdited($quiz->quiz_id));
                }
                return redirect('cp/assessment/list-quiz')
                    ->with('success', trans('admin/assessment.quiz_updated_successfully'));
            } else {
                return redirect('cp/assessment/list-quiz')
                    ->with('error', trans('admin/assessment.problem_while_updating_quiz'));
            }
        }
    }

    public function postAssignQuiz($action = null, $key = null)
    {
        $msg = null;

        $quiz = Quiz::where('quiz_id', '=', (int)$key)->first();
        if (empty($quiz)) {
            return response()->json(['flag' => 'error', 'message' => 'Invalid quiz']);
        }

        $ids = (!empty(Input::get('ids')) ? explode(',', Input::get('ids')) : []);
        if (Input::get('empty') != true) {
            if (empty($ids) || !is_array($ids)) {
                return response()->json(['flag' => 'error', 'message' => 'No checkboxes are selected']);
            }
        }
        $ids = array_map('intval', $ids);

        switch ($action) {
            case 'user':
                $assign_user_quiz_permission_data_with_flag = $this->roleService->hasPermission(
                    Auth::user()->uid,
                    ModuleEnum::ASSESSMENT,
                    PermissionType::ADMIN,
                    AssessmentPermission::QUIZ_ASSIGN_USER,
                    null,
                    null,
                    true
                );

                $assign_user_quiz_permission_data = get_permission_data($assign_user_quiz_permission_data_with_flag);
                $has_assign_quiz_to_user_permission = is_element_accessible(
                    $assign_user_quiz_permission_data,
                    ElementType::ASSESSMENT,
                    $key
                );

                if ($has_assign_quiz_to_user_permission) {
                    $has_assign_quiz_to_user_permission = are_users_exist_in_context(
                        $assign_user_quiz_permission_data,
                        $ids
                    );
                }

                if (!$has_assign_quiz_to_user_permission) {
                    return response()->json([
                        'flag' => 'error',
                        'message' => trans("admin/assessment.no_permi_to_assign_user")
                    ]);
                }

                $arrname = 'active_user_quiz_rel';
                
                $quiz_relation = isset($quiz->relations[$arrname]) ? $quiz->relations[$arrname] : [];
                if (!is_admin_role(Auth::user()->role)) {
                    /* If the user is a ProgramAdmin/ContentAuthor */
                    /* $manageable_ids = Uids which belongs to PA/CA users */
                    $manageable_ids = array_values(array_intersect(get_user_ids($assign_user_quiz_permission_data), $quiz_relation));
                    /* $dedupe_ids => Uids which are in the relation and are assigned by Site Admin */
                    $dedupe_ids = array_diff($quiz_relation, $manageable_ids);
                    
                    /* Note: when $dedupe_ids is empty, It means $manageable_ids and $quiz_relation contains same uids then assigning $manageable_ids to $dedupe_ids */
                    /* Below code is to remove the relations from the user tables */
                    if (empty($dedupe_ids)) {
                        $dedupe_ids = $manageable_ids;
                    }
                } else {
                    /* If the user is Site Admin  */
                    /* $manageable_ids => quiz user rel ie. "active_user_quiz_rel" */
                    $manageable_ids = $quiz_relation;
                    /* $dedupe_ids => quiz user rel ie. "active_user_quiz_rel" */
                    $dedupe_ids = $quiz_relation;
                }
                if (isset($dedupe_ids) && !empty($dedupe_ids)) {
                    $delete = array_diff($manageable_ids, $ids);
                    $add = array_diff($ids, $dedupe_ids);
                    
                    if (!is_admin_role(Auth::user()->role)) {
                        /* $ids => taking the array difference of ( quiz_user_rel+selected uids as the input) and $delete */
                        $ids = array_values(array_diff(array_unique(array_merge($quiz_relation, $add)), $delete));
                    }
                } else {
                    $delete = [];
                    $add = $ids;
                }

                $notify_flag = true;
                $notify_ids_d = $delete;
                foreach ($delete as $value) {
                    User::removeUserRelation($value, ['user_quiz_rel'], $quiz->quiz_id);
                    // Notifications
                    if (config('app.notifications.assessment.unassign_user') && $notify_flag) {
                        $notify_flag = false;
                        NotificationLog::getInsertNotification(
                            $notify_ids_d,
                            'Assessment',
                            trans('admin/assessment.notify_unassign_user', ['name' => $quiz->quiz_name])
                        );
                    }
                }
                $notify_flag = true;
                $notify_ids = $add;
                foreach ($add as $value) {
                    User::addUserRelation($value, ['user_quiz_rel'], $quiz->quiz_id);
                    // Notifications
                    if (config('app.notifications.assessment.assign_user') && $notify_flag) {
                        $notify_flag = false;
                        NotificationLog::getInsertNotification(
                            $notify_ids,
                            'Assessment',
                            trans('admin/assessment.notify_assign_user', ['name' => $quiz->quiz_name])
                        );
                    }
                }
                $msg = trans('admin/user.user_assigned');
                break;

            case 'usergroup':
                $permission_data_with_flag = $this->roleService->hasPermission(
                    Auth::user()->uid,
                    ModuleEnum::ASSESSMENT,
                    PermissionType::ADMIN,
                    AssessmentPermission::QUIZ_ASSIGN_USER_GROUP,
                    null,
                    null,
                    true
                );

                $permission_data = get_permission_data($permission_data_with_flag);
                $has_assign_permission = is_element_accessible($permission_data, ElementType::ASSESSMENT, $key);

                if ($has_assign_permission) {
                    $has_assign_permission = are_user_groups_exist_in_context(
                        $permission_data,
                        $ids
                    );
                }

                if (!$has_assign_permission) {
                    return response()->json([
                        'flag' => 'error',
                        'message' => trans("admin/assessment.no_permi_to_assign_usergroup")
                    ]);
                }

                $arrname = 'active_usergroup_quiz_rel';

                $quiz_relation = isset($quiz->relations[$arrname]) ? $quiz->relations[$arrname] : [];
                if (!is_admin_role(Auth::user()->role)) {
                    /* If the user is a ProgramAdmin/ContentAuthor */
                    /* $manageable_ids = Uids which belongs to PA/CA users */
                    $manageable_ids = array_values(array_intersect(get_user_group_ids($permission_data), $quiz_relation));
                    /* $dedupe_ids => Uids which are in the relation and are assigned by Site Admin */
                    $dedupe_ids = array_diff($quiz_relation, $manageable_ids);
                    
                    /* Note: when $dedupe_ids is empty, It means $manageable_ids and $quiz_relation contains same uids then assigning $manageable_ids to $dedupe_ids */
                    /* Below code is to remove the relations from the user tables */
                    if (empty($dedupe_ids)) {
                        $dedupe_ids = $manageable_ids;
                    }
                } else {
                    /* If the user is Site Admin  */
                    /* $manageable_ids => quiz usergroup rel ie. "active_usergroup_quiz_rel" */
                    $manageable_ids = $quiz_relation;
                    /* $dedupe_ids => quiz usergroup rel ie. "active_usergroup_quiz_rel" */
                    $dedupe_ids = $quiz_relation;
                }
                if (isset($dedupe_ids) && !empty($dedupe_ids)) {
                    $delete = array_diff($manageable_ids, $ids);
                    $add = array_diff($ids, $dedupe_ids);
                    
                    if (!is_admin_role(Auth::user()->role)) {
                        /* $ids => taking the array difference of ( quiz_usergroup_rel+selected uids as the input) and $delete */
                        $ids = array_values(array_diff(array_unique(array_merge($quiz_relation, $add)), $delete));
                    }
                } else {
                    $delete = [];
                    $add = $ids;
                }

                $ugs = UserGroup::where('status', '=', 'ACTIVE')->get();
                $notify_ids_ary = [];
                foreach ($delete as $value) {
                    UserGroup::removeUserGroupRelation($value, ['usergroup_quiz_rel'], $quiz->quiz_id);
                    // Notifications
                    if (config('app.notifications.assessment.unassign_usergroup')) {
                        $ug = $ugs->whereLoose('ugid', $value);
                        if (!$ug->isEmpty()) {
                            if (isset($ug->first()->relations['active_user_usergroup_rel'])) {
                                $notify_ids_ary = array_merge($notify_ids_ary, $ug->first()->relations['active_user_usergroup_rel']);
                                /*foreach ($ug->first()->relations['active_user_usergroup_rel'] as $value) {
                                    Notification::getInsertNotification(
                                        (int) $value,
                                        'Assessment',
                                        trans('admin/assessment.notify_unassign_user', ['name' => $quiz->quiz_name])
                                    );
                                }*/
                            }
                        }
                    }
                }
                if (!empty($notify_ids_ary)) {
                    NotificationLog::getInsertNotification(
                        $notify_ids_ary,
                        'Assessment',
                        trans('admin/assessment.notify_unassign_user', ['name' => $quiz->quiz_name])
                    );
                }
                $notify_ids_ary = [];
                foreach ($add as $value) {
                    UserGroup::addUserGroupRelation($value, ['usergroup_quiz_rel'], $quiz->quiz_id);
                    // Notifications
                    if (config('app.notifications.assessment.assign_usergroup')) {
                        $ug = $ugs->whereLoose('ugid', $value);
                        if (!$ug->isEmpty()) {
                            if (isset($ug->first()->relations['active_user_usergroup_rel'])) {
                                $notify_ids_ary = array_merge($notify_ids_ary, $ug->first()->relations['active_user_usergroup_rel']);
                                /*foreach ($ug->first()->relations['active_user_usergroup_rel'] as $value) {
                                    Notification::getInsertNotification(
                                        (int) $value,
                                        'Assessment',
                                        trans('admin/assessment.notify_assign_user', ['name' => $quiz->quiz_name])
                                    );
                                }*/
                            }
                        }
                    }
                }
                if (!empty($notify_ids_ary)) {
                    NotificationLog::getInsertNotification(
                        $notify_ids_ary,
                        'Assessment',
                        trans('admin/assessment.notify_assign_user', ['name' => $quiz->quiz_name])
                    );
                }
                $msg = trans('admin/user.usergroup_assigned');
                break;

            case 'feed':
                $feed = Input::get('feed');
                $program = Program::getAllProgramByIDOrSlug('content_feed', $feed)->first();

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

                $arrname = 'feed_quiz_rel.' . $program->program_id;

                if (!empty($program)) {
                    if (isset($quiz->relations['feed_quiz_rel'][$program->program_id]) &&
                        !empty($quiz->relations['feed_quiz_rel'][$program->program_id])
                    ) {
                        $deletepackets = array_diff($quiz->relations['feed_quiz_rel'][$program->program_id], $ids);
                        $addpackets = array_diff($ids, $quiz->relations['feed_quiz_rel'][$program->program_id]);
                    } else {
                        $deletepackets = [];
                        $addpackets = $ids;
                    }

                    foreach ($deletepackets as $value) {
                        $p = Packet::where('packet_id', '=', $value)
                            ->where('status', '!=', 'DELETED')
                            ->first();

                        if (!empty($p)) {
                            $temp_element = [];
                            foreach ($p->elements as $element) {
                                if ($element['type'] == 'assessment' && $element['id'] == $quiz->quiz_id) {
                                } else {
                                    $temp_element[] = $element;
                                }
                            }

                            Packet::where('packet_id', '=', $value)
                                ->update(['elements' => $temp_element, 'updated_at' => time()]);
                            if (config('elastic.service')) {
                                event(new ItemsAdded($value));
                            }
                        }
                    }

                    foreach ($addpackets as $value) {
                        $p = Packet::where('packet_id', '=', $value)
                            ->where('status', '!=', 'DELETED')
                            ->first();
                        $i = 1;
                        $temp = [];
                        $insert = true;
                        if (!empty($p->elements)) {
                            foreach ($p->elements as $element) {
                                $element['order'] = $i;
                                $temp[] = $element;
                                $i++;
                                if ($element['type'] == 'assessment' && $element['id'] == (int)$quiz->quiz_id) {
                                    $insert = false;
                                }
                            }
                        }
                        if ($insert == true) {
                            $e['type'] = 'assessment';
                            $e['order'] = $i;
                            $e['id'] = (int)$quiz->quiz_id;
                            $e['name'] = $quiz->quiz_name;
                            $temp[] = $e;
                        }
                        DB::collection('packets')
                            ->where('packet_id', '=', $value)
                            ->push('elements', $temp, true);
                        if (config('elastic.service')) {
                            event(new ItemsAdded($value));
                        }
                    }
                    $msg = trans('admin/program.channel_assigned_success');
                } else {
                    return response()->json(['flag' => 'error', 'message' => 'Invalid feed']);
                }

                break;

            default:
                return response()->json(['flag' => 'error', 'message' => 'Wrong action parameter']);
                break;
        }

        Quiz::where('quiz_id', '=', (int)$quiz->quiz_id)
            ->unset('relations.' . $arrname);
        if (!empty($ids)) {
            if (Quiz::where('quiz_id', '=', (int)$quiz->quiz_id)
                ->update(['relations.' . $arrname => $ids,
                    'updated_at' => time()])
            ) {
                if ($action == 'user' || $action == 'usergroup') {
                    if (config('elastic.service')) {
                        event(new QuizAssigned($quiz->quiz_id));
                    }
                }
                return response()->json(['flag' => 'success', 'message' => $msg]);
            } else {
                return response()->json(['flag' => 'error']);
            }
        } else {
            if ($action == 'user' || $action == 'usergroup') {
                if (config('elastic.service')) {
                    event(new QuizAssigned($quiz->quiz_id));
                }
            }
            return response()->json(['flag' => 'success', 'message' => $msg]);
        }
    }

    public function getDeleteQuiz($qid)
    {
        $delete_quiz_permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ASSESSMENT,
            PermissionType::ADMIN,
            AssessmentPermission::DELETE_QUIZ,
            null,
            null,
            true
        );

        if (!is_numeric($qid)) {
            abort(404);
        }

        $delete_quiz_permission_data = get_permission_data($delete_quiz_permission_data_with_flag);

        if (!is_element_accessible($delete_quiz_permission_data, ElementType::ASSESSMENT, $qid)) {
            return parent::getAdminError();
        }

        $start = Input::get('start', 0);
        $limit = Input::get('limit', 10);
        $search = Input::get('search', '');
        $order_by = Input::get('order_by', '3 desc');

        // Checking whether given quiz is available in db
        $quiz = Quiz::where('quiz_id', '=', (int)$qid)->firstOrFail();
        // Check user relations
        if (isset($quiz->relations['active_user_quiz_rel']) && !empty($quiz->relations['active_user_quiz_rel'])) {
            return redirect('cp/assessment/list-quiz')
                ->with('error', trans('admin/assessment.quiz_with_user'));
        }
        // Check usergroup relations
        if (isset($quiz->relations['active_usergroup_quiz_rel']) &&
            !empty($quiz->relations['active_usergroup_quiz_rel'])
        ) {
            return redirect('cp/assessment/list-quiz')
                ->with('error', trans('admin/assessment.quiz_with_usergroup'));
        }
        // check feed relations
        if (isset($quiz->relations['feed_quiz_rel']) && !empty($quiz->relations['feed_quiz_rel'])) {
            return redirect('cp/assessment/list-quiz')
                ->with('error', trans('admin/assessment.quiz_with_channels'));
        }
        //check Question relation
        if (isset($quiz->questions) && !empty($quiz->questions)) {
            return redirect('cp/assessment/list-quiz')
                ->with('error', trans('admin/assessment.quiz_with_question'));
        }

        if (Quiz::where('quiz_id', '=', (int)$quiz->quiz_id)->update(['status' => 'DELETED'])) {
            if (config('elastic.service')) {
                event(new QuizRemoved($quiz->quiz_id));
            }
            $totalRecords = Quiz::where('status', '=', 'ACTIVE')->count();
            if ($totalRecords <= $start) {
                $start -= $limit;
                if ($start < 0) {
                    $start = 0;
                }
            }
            return redirect('cp/assessment/list-quiz?start=' . $start . '&limit=' . $limit . '&search=' . $search . '&order_by=' . $order_by)
                ->with('success', trans('admin/assessment.quiz_deleted_success'));
        } else {
            return redirect('cp/assessment/list-quiz?start=' . $start . '&limit=' . $limit . '&search=' . $search . '&order_by=' . $order_by)
                ->with('error', trans('admin/assessment.problem_while_deleting_quiz'));
        }
    }

    public function getQuizQuestions($qid)
    {
        $edit_quiz_permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ASSESSMENT,
            PermissionType::ADMIN,
            AssessmentPermission::EDIT_QUIZ,
            null,
            null,
            true
        );

        $edit_quiz_permission_data = get_permission_data($edit_quiz_permission_data_with_flag);

        if (!is_element_accessible($edit_quiz_permission_data, ElementType::ASSESSMENT, $qid)) {
            return parent::getAdminError();
        }

        if (!is_numeric($qid)) {
            abort(404);
        }

        // Checking whether given quiz is available in db
        $quiz = Quiz::where('quiz_id', '=', (int)$qid)->firstOrFail();

        $attempt = QuizAttempt::where('quiz_id', '=', (int)$qid)->count();

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assessment.title_manage_quiz') => 'assessment/list-quiz',
            trans('admin/assessment.quiz_question') => ''
        ];

        $questions = Question::where('status', '=', 'ACTIVE')
            ->whereIn('question_id', array_map('intval', (isset($quiz->questions) ? $quiz->questions : [])))
            ->get(['question_id', 'question_name', 'question_text', 'difficulty_level', 'default_mark'])
            ->toArray();

        // Calcualte to print the questions order same as stored in db
        $qtemp = [];
        foreach ($questions as $value) {
            $qtemp[$value['question_id']] = $value;
        }
        $questions = $qtemp;

        $questionbank = '';
        $qbank_questions = '';
        // Question bank contents
        if (Input::has('qbank') || empty($questions)) {
            $questionbank = QuestionBank::orderBy('created_at', 'desc')
                ->where('status', '=', 'ACTIVE')
                ->get(['question_bank_id', 'question_bank_name', 'questions', 'draft_questions']);
            $select_ques = Question::getQuizsQuestions((int)$qid);
            $select_qesids = $select_ques->lists('question_id')->all();
            if (is_numeric(Input::get('qbank')) && Input::get('qbank') != "0") {
                $selected_qbank = QuestionBank::where(
                    'question_bank_id',
                    '=',
                    (int)Input::get('qbank')
                )->first(['questions']);
                $qtype = Input::get('qtype');
                $qdifficult = Input::get('qdifficulty');
                $qlimit = Input::get('qlimit');
                $qrandam = Input::get('qrandam');
                $tags = Input::get('qtags');
                if (Input::has('randmize')) {
                    $randmization = Input::get('randmize');
                } else {
                    $randmization = null;
                }
                $qbank_questions = Question::filterQuestion(
                    $select_qesids,
                    $selected_qbank,
                    $tags,
                    $qdifficult,
                    $qtype,
                    (int)$qlimit,
                    $randmization
                );
            }
        }
        $qb_error = "";
        if (Input::get('qbank') === "") {
            $qb_error = "The question bank field is required.";
        }
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = 'Quiz : ' . $quiz->quiz_name;
        $this->layout->pageicon = 'fa fa-file-text';
        $this->layout->pagedescription = strip_tags($quiz->quiz_description);
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'assessment')
            ->with('submenu', 'quiz');
        $this->layout->content = view('admin.theme.assessment.manage_quiz_questions')
            ->with('quiz', $quiz)
            ->with('attempt', $attempt)
            ->with('questions', $questions)
            ->with('questionbank', $questionbank)
            ->with('qbank_questions', $qbank_questions)
            ->with('qb_error', $qb_error);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function postAddQuizQuestions($qid)
    {
        $edit_quiz_permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ASSESSMENT,
            PermissionType::ADMIN,
            AssessmentPermission::EDIT_QUIZ,
            null,
            null,
            true
        );

        $edit_quiz_permission_data = get_permission_data($edit_quiz_permission_data_with_flag);

        if (!is_element_accessible($edit_quiz_permission_data, ElementType::ASSESSMENT, $qid)) {
            return parent::getAdminError();
        }

        if (!is_numeric($qid)) {
            abort(404);
        }

        $quiz = Quiz::where('quiz_id', '=', (int)$qid)->firstOrFail();
        $attempt = QuizAttempt::where('quiz_id', '=', (int)$qid)->count();

        if ($attempt > 0 && !$quiz->beta) {
            return redirect('cp/assessment/quiz-questions/' . $quiz->quiz_id);
        }

        $quiz_questions = $quiz->questions;
        $layout = $quiz->page_layout;

        $qb_questions = Input::get('qb_questions');
        $qbank_id = Input::get('_qb', 0);

        $total_questions_count = count($quiz_questions) + count($qb_questions);
        if (isset($quiz->type) && $quiz->type == "QUESTION_GENERATOR") {
            $limit_questions = isset($quiz->total_question_limit) ? (int)$quiz->total_question_limit : 0;
            if ($limit_questions < $total_questions_count) {
                $remain_limit = $limit_questions - count($quiz_questions);
                if ($remain_limit > 0) {
                    //.'. You can able to select '.$remain_limit." Question(s) more only";
                    $msg_lang = trans('admin/assessment.no_of_ques_more_selected');
                    $msg_find = ['XX'];
                    $msg_replace = [$remain_limit];
                    $msg = str_replace($msg_find, $msg_replace, $msg_lang);
                } else {
                    // $msg = "You are not able to assign any more questions ";
                    //.'. You can able to select '.$remain_limit." Question(s) more only";
                    $msg_lang = trans('admin/assessment.no_of_ques_more_selected');
                    $msg_find = ['XX'];
                    $msg_replace = [$limit_questions];
                    $msg = str_replace($msg_find, $msg_replace, $msg_lang);
                }
                return redirect('cp/assessment/quiz-questions/' . $quiz->quiz_id . '?qbank=' . $qbank_id)
                    ->with('error', $msg);
            }
        }

        if (!empty($qb_questions)) {
            // Converting array values into integer
            $qb_questions = array_map('intval', $qb_questions);
            foreach ($qb_questions as $value) {
                // questions field
                if (!in_array($value, $quiz_questions)) {
                    array_push($quiz_questions, $value);
                }

                // page_layout field
                if (is_array($layout) && !empty($layout)) {
                    $last_key = key(array_slice($layout, -1, 1, true));
                } else {
                    $last_key = 0;
                }

                if (isset($layout[$last_key]) && count($layout[$last_key]) < $quiz->question_per_page) {
                    $layout[$last_key][] = (int)$value;
                } else {
                    $layout[] = [(int)$value];
                }
            }
            // Total marks for this quiz
            $total = Quiz::getTotalMarks($quiz_questions);
            $quiz_data = [
                    'questions' => $quiz_questions,
                    'concept_tagging' => 0, //added for concept mapping cron
                    'page_layout' => $layout,
                    'total_mark' => $total
                ];
            if (isset($quiz->cut_off) && $quiz->cut_off > 0) {
                $quiz_data['cut_off_mark'] = QuizHelper::getCutOffMark($quiz->cut_off, $total, $quiz->cut_off_format);
            }
            if (Quiz::where('quiz_id', '=', (int)$quiz->quiz_id)
                ->update($quiz_data)
            ) {
                // Updating the quiz_id in selected questions quizzes field
                if (is_array($qb_questions) && !empty($qb_questions)) {
                    Question::updateQuizQuestions($quiz->quiz_id, $qb_questions);
                }
                //filter added
                $filter = "&randmize=" . Input::get('randmize')
                    . "&qlimit=" . Input::get('qlimit')
                    . "&qtags=" . Input::get('qtags')
                    . "&qdifficulty=" . Input::get('qdifficulty')
                    . "&qtype=" . Input::get('qtype');
                //dd($filter);
                return redirect('cp/assessment/quiz-questions/' . $quiz->quiz_id . '?qbank=' . $qbank_id . $filter)
                    ->with('success', trans('admin/assessment.add_question_success'));
            } else {
                return redirect('cp/assessment/quiz-questions/' . $quiz->quiz_id . '?qbank=0')
                    ->with('error', trans('admin/assessment.problem_while_adding_question'));
            }
        } else {
            return redirect('cp/assessment/quiz-questions/' . $quiz->quiz_id . '?qbank=0')
                ->with('error', trans('admin/assessment.no_ques_selected'));
        }
    }

    public function getRemoveQuizQuestion($qid)
    {
        $edit_quiz_permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ASSESSMENT,
            PermissionType::ADMIN,
            AssessmentPermission::EDIT_QUIZ,
            null,
            null,
            true
        );

        $edit_quiz_permission_data = get_permission_data($edit_quiz_permission_data_with_flag);

        if (!is_element_accessible($edit_quiz_permission_data, ElementType::ASSESSMENT, $qid)) {
            return parent::getAdminError();
        }

        $question = (int)Input::get('question');

        if (!is_numeric($qid) && !is_numeric($question)) {
            abort(404);
        }

        $quiz = Quiz::where('quiz_id', '=', (int)$qid)->firstOrFail();
        $attempt = QuizAttempt::where('quiz_id', '=', (int)$qid)->count();
        if ($attempt > 0 && !$quiz->beta) {
            return redirect('cp/assessment/quiz-questions/' . $quiz->quiz_id);
        }

        $quiz_questions = $quiz->questions;
        $layout = [];
        if (!empty($question) && in_array($question, $quiz_questions)) {
            if (($key = array_search($question, $quiz_questions)) !== false) {
                unset($quiz_questions[$key]);
            }
            $quiz_questions = array_values($quiz_questions);

            $layout = $quiz->page_layout;
            foreach ($layout as $page => $value) {
                if (($qkey = array_search($question, $value)) !== false) {
                    unset($layout[$page][$qkey]);
                }
            }
        }
        $filter = "&randmize=" . Input::get('randmize')
            . "&qlimit=" . Input::get('qlimit')
            . "&qtags=" . Input::get('qtags')
            . "&qdifficulty=" . Input::get('qdifficulty')
            . "&qtype=" . Input::get('qtype');
        if (Input::has('qbank') && is_numeric(Input::get('qbank'))) {
            $return = 'cp/assessment/quiz-questions/' . $quiz->quiz_id . '?qbank=' . Input::get('qbank') . $filter;
        } else {
            $return = 'cp/assessment/quiz-questions/' . $quiz->quiz_id;
        }

        // Total marks for this quiz
        $total = Quiz::getTotalMarks($quiz_questions);
        $quiz_data = [
                'questions' => array_filter($quiz_questions),
                'concept_tagging' => 0, //added for concept mapping cron
                'page_layout' => array_filter($layout),
                'total_mark' => $total
            ];
        if (isset($quiz->cut_off) && $quiz->cut_off > 1) {
            $quiz_data['cut_off_mark'] = QuizHelper::getCutOffMark($quiz->cut_off, $total, $quiz->cut_off_format);
        }
        if (Quiz::where('quiz_id', '=', (int)$quiz->quiz_id)
            ->update($quiz_data)
        ) {
            // Updating the quiz_id in selected questions quizzes field
            if (!empty($question)) {
                Question::removeQuizQuestions($quiz->quiz_id, [$question]);
            }
            return redirect($return)
                ->with('success', trans('admin/assessment.question_removed_success'));
        } else {
            return redirect($return)
                ->with('error', trans('admin/assessment.problem_while_removing_ques'));
        }
    }

    public function postAjaxBulkDeleteQuizQuestions($quiz_id)
    {
        $edit_quiz_permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ASSESSMENT,
            PermissionType::ADMIN,
            AssessmentPermission::EDIT_QUIZ,
            null,
            null,
            true
        );
        $edit_quiz_permission_data = get_permission_data($edit_quiz_permission_data_with_flag);
        if (!is_element_accessible($edit_quiz_permission_data, ElementType::ASSESSMENT, $quiz_id)) {
            return parent::getAdminError();
        }
        $layout = [];
        $selected_ids = array_filter(array_map("intval", explode(',', Input::get("delete-ids"))));
        $quiz = Quiz::where('quiz_id', '=', (int)$quiz_id)->firstOrFail();
        $quiz_questions = $quiz->questions;
        $question_ids = array_diff($quiz_questions, $selected_ids);
        $delete_ids = array_intersect($selected_ids, $quiz_questions);
        
        if (!empty($question_ids)) {
            $layout = $quiz->page_layout;
            foreach ($layout as $page => $value) {
                foreach ($selected_ids as $id => $val) {
                    if (in_array($val, $value)) {
                        unset($layout[$page]);
                    }
                }
            }
        }
        /* Total marks for this quiz */
        $total = Quiz::getTotalMarks($question_ids);
        $quiz_data = [
                'questions' => !empty($question_ids) ? $question_ids : [],
                'concept_tagging' => 0, //added for concept mapping cron
                'page_layout' => array_filter($layout),
                'total_mark' => $total
            ];
        
        if (isset($quiz->cut_off) && $quiz->cut_off >= 1) {
            $quiz_data['cut_off_mark'] = QuizHelper::getCutOffMark($quiz->cut_off, $total, $quiz->cut_off_format);
        }
        if (Quiz::where('quiz_id', '=', (int)$quiz->quiz_id)->update($quiz_data)) {
            /* Updating the quiz_id in selected questions quizzes field */
            if (!empty($delete_ids)) {
                Question::removeQuizQuestions($quiz->quiz_id, $delete_ids);
            }
        }
        return response()
               ->json([
                    'status' => 'success',
                    'message' => trans("admin/assessment.questions_removed_success")
               ]);
    }
    
    public function postQuizQuestionAjax($qid)
    {
        $edit_quiz_permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ASSESSMENT,
            PermissionType::ADMIN,
            AssessmentPermission::EDIT_QUIZ,
            null,
            null,
            true
        );

        $edit_quiz_permission_data = get_permission_data($edit_quiz_permission_data_with_flag);

        if (!is_element_accessible($edit_quiz_permission_data, ElementType::ASSESSMENT, $qid)) {
            return parent::getAdminError();
        }

        if (!is_numeric($qid)) {
            abort(404);
        }

        $quiz = Quiz::where('quiz_id', '=', (int)$qid)->firstOrFail();
        $attempt = QuizAttempt::where('quiz_id', '=', (int)$qid)->count();
        //echo $attempt; die;

        if ($attempt > 0) {
            return response()
                ->json([
                    'status' => 'failure',
                    'message' => 'You cannot add or remove questions because this quiz has been attempted'
                ]);
        }

        $action = Input::get('action');

        switch ($action) {
            case 'sort':
                $layout = json_decode(Input::get('ids', []), true);
                if (!empty($layout)) {
                    $page = [];
                    $page = QuizHelper::pageList($layout);
                    if (Quiz::where('quiz_id', '=', (int)$quiz->quiz_id)
                        ->update(['page_layout' => $page, 'concept_tagging' => 0])
                    ) {
                        return response()
                            ->json(['status' => 'success']);
                    } else {
                        return response()
                            ->json([
                                'status' => 'failure',
                                'message' => 'Error while updating the record'
                            ]);
                    }
                }
                break;

            case 'add-page':
                if (Quiz::where('quiz_id', '=', (int)$quiz->quiz_id)->push('page_layout', [])) {
                    return response()
                        ->json(['status' => 'success']);
                } else {
                    return response()
                        ->json([
                            'status' => 'failure',
                            'message' => 'Error while updating the record'
                        ]);
                }
                break;

            default:
                return response()
                    ->json([
                        'status' => 'failure',
                        'message' => 'Unknown action'
                    ]);
                break;
        }
    }

    public function getListQuestionbank()
    {
        if (!has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::LIST_QUESTION_BANK)) {
            return parent::getAdminError();
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assessment.manage_question_bank') => ''
        ];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/assessment.manage_question_bank');
        $this->layout->pageicon = 'fa fa-bank';
        $this->layout->pagedescription = trans('admin/assessment.list_of_qb');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'assessment')
            ->with('submenu', 'questionbank');
        $this->layout->content = view('admin.theme.assessment.list_questionbanks');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getListQuestionbankAjax()
    {
        
        if (!has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::LIST_QUESTION_BANK)) {
            return parent::getAdminError();
        }

        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = "";
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == "1") {
                $orderByArray = ['question_bank_name' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == "3") {
                $orderByArray = ['created_at' => $order_by[0]['dir']];
            }
        }

        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }
        if (isset($search['value'])) {
            $searchKey = trim(strip_tags($search['value']));
        }

        $totalRecords = QuestionBank::where('status', '=', 'ACTIVE')
            ->count();
        $filteredRecords = QuestionBank::search($searchKey)
            ->where('status', '=', 'ACTIVE')
            ->count();
        $filtereddata = QuestionBank::search($searchKey)
            ->where('status', '=', 'ACTIVE')
            ->orderBy(key($orderByArray), $orderByArray[key($orderByArray)])
            ->skip((int)$start)
            ->take((int)$limit)
            ->get();
        $dataArr = [];
        foreach ($filtereddata as $value) {
            $user_rel = (isset($value->relations['active_user_questionbank_rel']) && !empty($value->relations['active_user_questionbank_rel'])) ? $value->relations['active_user_questionbank_rel'] : [];
            $usergrp_rel = (isset($value->relations['active_usergroup_questionbank_rel']) && !empty($value->relations['active_usergroup_questionbank_rel'])) ? $value->relations['active_usergroup_questionbank_rel'] : [];
            $delete_rel = (!empty($user_rel) || !empty($usergrp_rel)) ? 'disabled' : '';
            $actions = '';
            if (has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::EDIT_QUESTION_BANK)) {
                $actions .= '<a class="btn btn-circle show-tooltip ajax" title="' . trans('admin/manageweb.action_edit') . '" href="' . URL::to("/cp/assessment/edit-questionbank/" . $value->question_bank_id) . '?start=' . $start . '&limit=' . $limit . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-edit"></i></a>';
            }
            if (has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::DELETE_QUESTION_BANK)) {
                $actions .= '<a class="btn btn-circle show-tooltip ajax deleteqbank" title="' . trans('admin/manageweb.action_delete') . '" href="' . URL::to("/cp/assessment/delete-questionbank/" . $value->question_bank_id) . '?start=' . $start . '&limit=' . $limit . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ' . $delete_rel . '><i class="fa fa-trash-o"></i></a>';
            }
            //export question bank
            if (has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::EXPORT_QUESTION_BANK)) {
                if (count($value->questions) > 0) {
                    $actions .= '<a class="btn btn-circle show-tooltip ajax" title="' . trans('admin/assessment.export_question_bank') . '" href="' . URL::to("/cp/assessment/question-bank-mapping/" . $value->question_bank_id) . '?start=' . $start . '&limit=' . $limit . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ' . $delete_rel . '><i class="fa fa-bank"></i></a>';
                } else {
                    $actions .= '<a class="btn btn-circle show-tooltip" title="' . trans('admin/assessment.no_questions_to_export_question_bank') . '"><i class="fa fa-bank"></i></a>';
                }
            }
            //<a class="btn btn-circle show-tooltip" id= "export_link" title="Question bank Export" href="{{URL::to('/cp/assessment/question-bank-mapping/')}}"><i class="fa fa-users"></i></a>
            $temparr = [
                '<input type="checkbox" value="' . $value->question_bank_id . '">',
                $value->question_bank_name,
                "<a href='" . URL::to('/cp/assessment/questionbank-questions/' . $value->question_bank_id) . "' title='" . trans('admin/assessment.manage_question') . "' class='badge show-tooltip " . ((count($value->questions) > 0) ? 'badge-success' : 'badge-grey') . "'>" . count($value->questions) . "</a>",
                Timezone::convertFromUTC('@' . $value->created_at, Auth::user()->timezone, config('app.date_format')),
                /*$users,
                $user_groups,*/
                $actions
            ];
            $dataArr[] = $temparr;
        }
        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr
        ];
        return response()->json($finaldata);
    }

    public function getAddQuestionbank()
    {
        if (!has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::ADD_QUESTION_BANK)) {
            return parent::getAdminError();
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assessment.manage_question_bank') => 'assessment/list-questionbank',
            trans('admin/assessment.add_question_bank') => ''
        ];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/assessment.add_question_bank');
        $this->layout->pageicon = 'fa fa-file-text';
        $this->layout->pagedescription = trans('admin/assessment.add_question_bank');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'assessment')
            ->with('submenu', 'questionbank');
        $this->layout->content = view('admin.theme.assessment.add_questionbank');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function postAddQuestionbank(Request $request)
    {
        if (!has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::ADD_QUESTION_BANK)) {
            return parent::getAdminError();
        }

        $rules = [
            'question_bank_name' => 'unique:questionbanks|required|min:3|max:512'
        ];

        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return redirect('cp/assessment/add-questionbank')
                ->withInput()
                ->withErrors($validation);
        } else {
            $question_bank_slug = QuestionBank::getQuestionBankNameSlug(Input::get('question_bank_name'));
            $qb_exist_id = QuestionBank::where('question_bank_slug', '=', $question_bank_slug)
                ->value('question_bank_id');


            if (is_null($qb_exist_id)) {
                $qbank_id = QuestionBank::getNextSequence();
                $qbankdata = [
                    'question_bank_id' => $qbank_id,
                    'question_bank_name' => trim(strip_tags($request->question_bank_name)),
                    'question_bank_slug' => $question_bank_slug,
                    'question_bank_description' => $request->question_bank_description,
                    'keywords' => ($request->has('keywords')) ? array_map('trim', explode(',', strip_tags($request->keywords))) : [],
                    'questions' => [],
                    'draft_questions' => [],
                    'editor_images' => $request->input('editor_images', []),
                    'relations' => [
                        'active_user_questionbank_rel' => [],
                        'inactive_user_questionbank_rel' => [],
                        'active_usergroup_questionbank_rel' => [],
                        'inactive_usergroup_questionbank_rel' => []
                    ],
                    'status' => 'ACTIVE',
                    'created_by' => Auth::user()->username,
                    'created_at' => time(),
                    'updated_by' => '',
                    'updated_at' => time()
                ];

                $qbank_data = QuestionBank::insert($qbankdata);
                if ($qbank_data) {
                    return redirect('cp/assessment/success-questionbank/' . $qbank_id);
                } else {
                    return redirect('cp/assessment/list-questionbank')
                        ->with('error', trans('admin/assessment.problem_while_creating_newquestionbank'));
                }
            } else {
                $validation->getMessageBag()->add('question_bank_name', trans('admin/assessment.questionbank_exist'));
                return Redirect::back()->withInput()->withErrors($validation);
            }
        }
    }

    public function getSuccessQuestionbank($qbid)
    {
        if (!has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::ADD_QUESTION_BANK)) {
            return parent::getAdminError();
        }

        if (!is_numeric($qbid)) {
            abort(404);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assessment.manage_question_bank') => 'assessment/list-questionbank',
            trans('admin/assessment.success_question_bank') => ''
        ];

        // Checking whether given question bank is available in db
        QuestionBank::where('question_bank_id', '=', (int)$qbid)->firstOrFail(['question_bank_id']);

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/assessment.questionbank');
        $this->layout->pageicon = 'fa fa-file-text';
        $this->layout->pagedescription = trans('admin/assessment.qb_success');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'assessment')
            ->with('submenu', 'questionbank');
        $this->layout->content = view('admin.theme.assessment.success_questionbank')
            ->with('questionbank_id', $qbid);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getEditQuestionbank($qbid)
    {
        if (!has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::EDIT_QUESTION_BANK)) {
            return parent::getAdminError();
        }

        if (!is_numeric($qbid)) {
            abort(404);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assessment.manage_question_bank') => 'assessment/list-questionbank',
            trans('admin/assessment.edit_question_bank') => ''
        ];

        // Checking whether given question bank is available in db
        $question_bank = QuestionBank::where('question_bank_id', '=', (int)$qbid)->firstOrFail();

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/assessment.edit_question_bank');
        $this->layout->pageicon = 'fa fa-file-text';
        $this->layout->pagedescription = trans('admin/assessment.edit_question_bank');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'assessment')
            ->with('submenu', 'questionbank');
        $this->layout->content = view('admin.theme.assessment.edit_questionbank')
            ->with('questionbank', $question_bank);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function postEditQuestionbank(Request $request)
    {
        if (!has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::EDIT_QUESTION_BANK)) {
            return parent::getAdminError();
        }

        // Checking whether given question bank is available in db
        $question_bank = QuestionBank::where('question_bank_id', '=', (int)$request->_qb)->firstOrFail();

        $rules = [
            'question_bank_name' => 'unique:questionbanks|required|min:3|max:512'
        ];

        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return redirect('cp/assessment/edit-questionbank/' . $question_bank->question_bank_id)
                ->withInput()
                ->withErrors($validation);
        } else {
            // Editer images
            $editer_images = $question_bank->editor_images;
            if (is_array($editer_images)) {
                $editer_images = array_merge($editer_images, $request->input('editor_images', []));
            } else {
                $editer_images = $request->input('editor_images');
            }


            // check the slug exists or not
            $question_bank_slug = QuestionBank::getQuestionBankNameSlug(Input::get('question_bank_name'));
            $qb_exist_id = QuestionBank::where('question_bank_slug', '=', $question_bank_slug)
                ->value('question_bank_id');

            if (is_null($qb_exist_id)) {
                $qbankdata = [
                    'question_bank_name' => trim(strip_tags($request->question_bank_name)),
                    'question_bank_slug' => $question_bank_slug,
                    'question_bank_description' => $request->question_bank_description,
                    'keywords' => ($request->has('keywords')) ? array_map('trim', explode(',', strip_tags($request->keywords))) : [],
                    'editor_images' => $editer_images,
                    'updated_by' => Auth::user()->username,
                    'updated_at' => time()
                ];

                if (QuestionBank::where('question_bank_id', '=', $question_bank->question_bank_id)->update($qbankdata)) {
                    return redirect('cp/assessment/list-questionbank')
                        ->with('success', trans('admin/assessment.questionbank_update_success'));
                } else {
                    return redirect('cp/assessment/list-questionbank')
                        ->with('error', trans('admin/assessment.problem_while_updating_questionbank'));
                }
            } else {
                $question_bank_slug = QuestionBank::getQuestionBankNameSlug(Input::get('question_bank_name'));
                $existing_qb_slug = QuestionBank::getQuestionBankNameSlug($question_bank->question_bank_name);

                if ($question_bank_slug == $existing_qb_slug) {
                    $qbankdata = [
                        'question_bank_name' => trim(strip_tags($request->question_bank_name)),
                        'question_bank_slug' => $question_bank_slug,
                        'question_bank_description' => $request->question_bank_description,
                        'keywords' => ($request->has('keywords')) ? array_map('trim', explode(',', strip_tags($request->keywords))) : [],
                        'editor_images' => $editer_images,
                        'updated_by' => Auth::user()->username,
                        'updated_at' => time()
                    ];

                    if (QuestionBank::where('question_bank_id', '=', $question_bank->question_bank_id)
                        ->update($qbankdata)
                    ) {
                        return redirect('cp/assessment/list-questionbank')
                            ->with('success', trans('admin/assessment.questionbank_update_success'));
                    }
                } else {
                    Validator::extend('question_bank_slug', function ($attribute, $value, $parameters) {
                        $question_bank_slug = QuestionBank::getQuestionBankNameSlug(Input::get('question_bank_name'));
                        $returnval = QuestionBank::where('question_bank_slug', '=', $question_bank_slug)
                            ->value('question_bank_id');
                        if (empty($returnval)) {
                            return true;
                        }
                        return false;
                    });

                    $messages = [
                        'question_bank_slug' => trans('admin/assessment.questionbank_exist'),
                    ];
                    $rules = [
                        'question_bank_name' => 'Required|question_bank_slug',
                    ];

                    $validation = Validator::make(Input::all(), $rules, $messages);
                    if ($validation->fails()) {
                        return Redirect::back()->withInput()->withErrors($validation);
                    }
                }
            }
        }
    }

    public function postAssignQuestionbank($action = null, $key = null)
    {
        $qbank = QuestionBank::where('question_bank_id', '=', (int)$key)->first();

        if (empty($qbank)) {
            return response()->json(['flag' => 'error', 'message' => 'Invalid questionbank']);
        }

        $ids = (!empty(Input::get('ids')) ? explode(',', Input::get('ids')) : []);
        if (Input::get('empty') != true) {
            if (empty($ids) || !is_array($ids)) {
                return response()->json(['flag' => 'error', 'message' => 'No checkboxes are selected']);
            }
        }

        $ids = array_map('intval', $ids);

        switch ($action) {
            case 'user':
                $arrname = 'active_user_questionbank_rel';

                if (isset($qbank->relations[$arrname]) && !empty($qbank->relations[$arrname])) {
                    $delete = array_diff($qbank->relations[$arrname], $ids);
                    $add = array_diff($ids, $qbank->relations[$arrname]);
                } else {
                    $delete = [];
                    $add = $ids;
                }

                foreach ($delete as $value) {
                    User::removeUserRelation($value, ['user_questionbank_rel'], $qbank->question_bank_id);
                }

                foreach ($add as $value) {
                    User::addUserRelation($value, ['user_questionbank_rel'], $qbank->question_bank_id);
                }

                break;

            case 'usergroup':
                $arrname = 'active_usergroup_questionbank_rel';

                if (isset($qbank->relations[$arrname]) && !empty($qbank->relations[$arrname])) {
                    $delete = array_diff($qbank->relations[$arrname], $ids);
                    $add = array_diff($ids, $qbank->relations[$arrname]);
                } else {
                    $delete = [];
                    $add = $ids;
                }

                foreach ($delete as $value) {
                    UserGroup::removeUserGroupRelation(
                        $value,
                        ['usergroup_questionbank_rel'],
                        $qbank->question_bank_id
                    );
                }

                foreach ($add as $value) {
                    UserGroup::addUserGroupRelation($value, ['usergroup_questionbank_rel'], $qbank->question_bank_id);
                }

                break;

            default:
                return response()->json(['flag' => 'error', 'message' => 'Wrong action parameter']);
                break;
        }

        QuestionBank::where('question_bank_id', '=', $qbank->question_bank_id)->unset('relations.' . $arrname);
        if (QuestionBank::where('question_bank_id', '=', $qbank->question_bank_id)
            ->update(['relations.' . $arrname => $ids,
                'updated_at' => time()])
        ) {
            return response()->json(['flag' => 'success']);
        } else {
            return response()->json(['flag' => 'error']);
        }
    }

    public function getDeleteQuestionbank($qbid)
    {
        if (!has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::DELETE_QUESTION_BANK)) {
            return parent::getAdminError();
        }

        if (!is_numeric($qbid)) {
            abort(404);
        }

        $start = Input::get('start', 0);
        $limit = Input::get('limit', 10);
        $search = Input::get('search', '');
        $order_by = Input::get('order_by', '3 desc');

        // Checking whether given question bank is available in db
        $qb = QuestionBank::where('question_bank_id', '=', (int)$qbid)->firstOrFail(['question_bank_id', 'questions']);
        $quiz = Quiz::whereIn('questions', $qb->questions)->get(['quiz_id']);

        // Redirect check
        if (Input::has('return')) {
            $return = urldecode(Input::get('return')) . '?start=' . $start . '&limit=' . $limit . '&search=' . $search . '&order_by=' . $order_by;
        } else {
            $return = 'cp/assessment/list-questionbank?start=' . $start . '&limit=' . $limit . '&search=' . $search . '&order_by=' . $order_by;
        }

        if ($quiz->isEmpty()) {
            if (QuestionBank::where('question_bank_id', '=', (int)$qb->question_bank_id)->update(['status' => 'DELETED'])) {
                $totalRecords = QuestionBank::where('status', '=', 'ACTIVE')->count();
                if ($totalRecords <= $start) {
                    $start -= $limit;
                    if ($start < 0) {
                        $start = 0;
                    }
                }
                return redirect($return)
                    ->with('success', trans('admin/assessment.questionbank_delete_success'));
            } else {
                return redirect($return)
                    ->with('error', trans('admin/assessment.problem_while_deleting_questionbank'));
            }
        } else {
            return redirect($return)
                ->with('error', trans('admin/assessment.ques_with_quiz'));
        }
    }

    public function getQuestionbankQuestions($qbid)
    {
        if (!has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::LIST_QUESTION_BANK)) {
            return parent::getAdminError();
        }

        if (!is_numeric($qbid)) {
            abort(404);
        }

        // Checking whether given question bank is available in db
        $qb = QuestionBank::where('question_bank_id', '=', (int)$qbid)->firstOrFail();

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assessment.manage_question_bank') => 'assessment/list-questionbank',
            trans('admin/assessment.question') => ''
        ];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = 'Question bank : ' . $qb->question_bank_name;
        $this->layout->pageicon = 'fa fa-bank';
        $this->layout->pagedescription = strip_tags($qb->question_bank_description);
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'assessment')
            ->with('submenu', 'questionbank');
        $this->layout->content = view('admin.theme.assessment.manage_questionbank_questions')
            ->with('qbid', $qbid);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getQuestionbankQuestionsAjax($qbid)
    {
        if (!has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::LIST_QUESTION_BANK)) {
            return parent::getAdminError();
        }

        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $filter = Input::get('filter', 'ALL');
        $filterCondition = ($filter != 'ALL' && $filter == 'COPIED') ? true : false;
        $searchKey = "";
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            switch ($order_by[0]['column']) {
                case '1':
                    $orderByArray = ['question_name' => $order_by[0]['dir']];
                    break;
                case '2':
                    $orderByArray = ['question_text' => $order_by[0]['dir']];
                    break;
                case '3':
                    $orderByArray = ['question_type' => $order_by[0]['dir']];
                    break;
                case '4':
                    $orderByArray = ['default_mark' => $order_by[0]['dir']];
                    break;
                /*case '5':
                    $orderByArray = ['practice_question' => $order_by[0]['dir']];
                    break;*/
                case '5':
                    $orderByArray = ['difficulty_level' => $order_by[0]['dir']];
                    break;
                case '6':
                    $orderByArray = ['created_at' => $order_by[0]['dir']];
                    break;
            }
        }

        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }

        if (isset($search['value'])) {
            $searchKey = trim(strip_tags($search['value']));
        }

        $questionbank = QuestionBank::where('question_bank_id', '=', (int)$qbid)->firstOrFail();

        $totalRecords = count($questionbank->questions);
        if ($filter == 'ALL') {
            $filteredRecords = Question::search($searchKey)
                ->whereIn('status', ['ACTIVE', 'DRAFT'])
                ->whereIn('question_id', $questionbank->questions)
                ->count();
        } else {
            $filteredRecords = Question::search($searchKey)
                ->whereIn('status', ['ACTIVE', 'DRAFT'])
                ->where('parent_question_id', 'exists', $filterCondition)
                ->whereIn('question_id', $questionbank->questions)
                ->count();
        }
        if ($filter == 'ALL') {
            $filtereddata = Question::search($searchKey)
                ->whereIn('status', ['ACTIVE', 'DRAFT'])
                ->whereIn('question_id', $questionbank->questions)
                ->orderBy(key($orderByArray), $orderByArray[key($orderByArray)])
                ->skip((int)$start)
                ->take((int)$limit)
                ->get();
        } else {
            $filtereddata = Question::search($searchKey)
                ->whereIn('status', ['ACTIVE', 'DRAFT'])
                ->whereIn('question_id', $questionbank->questions)
                ->where('parent_question_id', 'exists', $filterCondition)
                ->orderBy(key($orderByArray), $orderByArray[key($orderByArray)])
                ->skip((int)$start)
                ->take((int)$limit)
                ->get();
        }
        $dataArr = [];
        foreach ($filtereddata as $value) {
            if ($value->status == 'DRAFT') {
                $value->question_name .= ' <span class="label label-warning">Draft</span>';
            }
            $return = urlencode('/cp/assessment/questionbank-questions/' . $qbid);
            $quiz_rel = '';
            if (!empty($value->quizzes)) { //checking question is attempted by any user
                $quiz_attempted = QuizAttemptData::whereIn('quiz_id', $value->quizzes)
                    ->where('question_id', $value->question_id)
                    ->count();
                if ($quiz_attempted > 0) {
                    $quiz_rel = 'disabled';
                }
            }
            $actions = '';

            if (has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::EDIT_QUESTION)) {
                $productionQuizArr = Quiz::getOnlyProductionQuiz($value->quizzes);
                $attempt = QuizAttemptData::whereIn('quiz_id', $productionQuizArr)
                    ->where('question_id', '=', (int)$value->question_id)
                    ->count();
                if ($attempt != 0) {
                    $actions .= '<a class="btn btn-circle show-tooltip" title="' . trans('admin/assessment.question_cant_be_updated_because_it_has_been_attempted') . '"><i class="fa fa-edit"></i></a>';
                } else {
                    $actions .= '<a class="btn btn-circle show-tooltip ajax" title="' . trans('admin/manageweb.action_edit') . '" href="' . (($value->question_type === "MCQ") ? URL::to("/cp/assessment/edit-question/" . $value->question_id) : URL::route("get-edit-question", ["question_bank_id" => $questionbank->_id, "question_id" => $value->_id])) . "?return=" . $return . '&start=' . $start . '&limit=' . $limit . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '&qbid=' . $qbid . '" ><i class="fa fa-edit"></i></a>';
                }
            }

            if (has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::DELETE_QUESTION)) {
                $actions .= '<a class="btn btn-circle show-tooltip ajax deletequestion" title="' . trans('admin/manageweb.action_delete') . '" href="' . (($value->question_type === "MCQ" ? URL::to("/cp/assessment/delete-question/" . $value->question_id) : URL::route("delete-question", ["question_bank_id" => $questionbank->_id, "question_id" => $value->_id])) . "?return=" . $return) . '&start=' . $start . '&limit=' . $limit . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '&qbid=' . $qbid . '" ' . $quiz_rel . '><i class="fa fa-trash-o"></i></a>';
            }

            /* Below code is to check the quiz is beta or alpha */
            if (!empty($value->quizzes)) { //checking question is attempted by any user
                $productionQuizArr = Quiz::getOnlyProductionQuiz($value->quizzes);
                $betaQuizArr = Quiz::getOnlyBetaQuiz($value->quizzes);
                if (!empty($productionQuizArr) || (!empty($productionQuizArr) && !empty($betaQuizArr))) {
                    $actions .= '<a class="btn btn-circle show-tooltip" title="' . trans('admin/assessment.view_editRationale') . '" href="' . URL::to("/cp/assessment/edit-rationale/" . $value->question_id . '/' . $qbid) . '" ><i class="fa fa-eye"></i></a>';
                }
            }

            //copy question button
            $actions .= '<a class="btn btn-circle show-tooltip copy-single" title="' . trans('admin/manageweb.action_copy') . '" href="#" data-question-id="' . $value->question_id . '"><i class="fa fa-files-o"></i></a>';
            $copy = isset($value->parent_question_id) ? " <span class='label label-success'>" . trans('admin/assessment.copy') . "</span>" : '';
            $temparr = [
                '<input type="checkbox" value="' . $value->question_id . '">',
                isset($value->question_name) ? str_limit($value->question_name, 20) . $copy : '',
                str_limit(html_entity_decode($value->question_text), 40),
                $value->question_type,
                $value->default_mark,
                $value->difficulty_level,
                $value->created_at->timezone(Auth::user()->timezone)->format(config('app.date_format')),
                $actions
            ];
            $dataArr[] = $temparr;
        }
        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr
        ];
        return response()->json($finaldata);
    }

    public function getAddQuestion($qtype = "mcq")
    {
        if (!has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::ADD_QUESTION)) {
            return parent::getAdminError();
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assessment.manage_question_bank') => 'assessment/list-questionbank',
            trans('admin/assessment.add_question') => ''
        ];

        $qtype = (!in_array($qtype, ['mcq', 'descriptive'])) ? 'mcq' : $qtype;
        $questionbank = QuestionBank::orderBy('created_at', 'desc')
            ->where('status', '=', 'ACTIVE')
            ->get();

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/assessment.add_question');
        $this->layout->pageicon = 'fa fa-file-text';
        $this->layout->pagedescription = trans('admin/assessment.add_question');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'assessment')
            ->with('submenu', 'questionbank');
        // checking for question type to load the respective form
        switch (strtoupper($qtype)) {
            case 'MCQ':
                $this->layout->content = view('admin.theme.assessment.question.mcq.add_mcq_question')
                    ->with('questionbank', $questionbank);
                break;
            case 'DESCRIPTIVE':
                $this->layout->content = view('admin.theme.assessment.question.descriptive.add')
                    ->with('questionbank', $questionbank);
                break;

            default:
                abort(404);
                break;
        }
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function postAddQuestion(Request $request)
    {
        if (!has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::ADD_QUESTION)) {
            return parent::getAdminError();
        }

        if (!isset($request->question_bank)) {
            abort(404);
        }

        $messages = [];

        if ($request->has('draft')) {
            $status = 'DRAFT';
        } else {
            $status = 'ACTIVE';
        }

        $rules = [
            'question_text' => 'required',
            'question_bank' => 'required|not_in:null',
            'default_mark' => 'required|numeric|min:0|regex:/^\d*(\.\d{2})?$/'
        ];
        switch (strtoupper($request->_qtype)) {
            case 'MCQ':
                $rules['answer.0'] = 'required';
                $rules['answer.1'] = 'required';
                $rules['correct_answer'] = 'required';
                $messages['answer.0.required'] = 'Choice 1 answer field is required';
                $messages['answer.1.required'] = 'Choice 2 answer field is required';
                break;
        }

        $validation = Validator::make(Input::all(), $rules, $messages);
        if ($validation->fails()) {
            return redirect("cp/assessment/add-question/{$request->_qtype}")
                ->withInput()
                ->withErrors($validation);
        }

        // Checking whether given question bank is available in db
        $qb = QuestionBank::where('question_bank_id', '=', (int)$request->question_bank)->firstOrFail();
        $question_id = Question::getNextSequence();

        switch (strtoupper($request->_qtype)) {
            case 'MCQ':
                $answers = [];
                $ans_chk_dupe = [];
                for ($i = 0; $i < count($request->answer); $i++) {
                    if ((trim($request->answer[$i]) != '' && $request->answer[$i] != null)) {
                        $ans_chk_dupe[] = $ans['answer'] = trim($request->answer[$i]);
                        $ans["answer_media"] = (isset($request->question_dam_media_answer[$i])) ? $request->question_dam_media_answer[$i] : [];
                        $ans['correct_answer'] = ((int)$request->correct_answer == $i) ? true : false;
                        $ans['rationale'] = trim($request->rationale[$i]);
                        $ans["rationale_media"] = (isset($request->question_dam_media_rationale[$i])) ? $request->question_dam_media_rationale[$i] : [];
                        $answers[] = $ans;
                    } elseif ((int)$request->correct_answer == $i) {
                        // Throw error if selected the correct answer has empty answer
                        $validation->getMessageBag()
                            ->add(
                                'answer.' . (int)$request->correct_answer,
                                'Choice ' . ((int)$request->correct_answer + 1) . ' answer field is required'
                            );
                        return redirect('cp/assessment/add-question')
                            ->withInput()
                            ->withErrors($validation);
                    }
                }
                if (empty($ans_chk_dupe) || (count($ans_chk_dupe) != count(array_unique($ans_chk_dupe)))) {
                    $validation->getMessageBag()
                        ->add(
                            'answer_dupe',
                            'Do not duplicate answers'
                        );
                    return redirect('cp/assessment/add-question')
                        ->withInput()
                        ->withErrors($validation);
                }
                $qdata = [
                    'question_id' => $question_id,
                    'question_name' => 'Q' . sprintf("%07d", $question_id),
                    'question_text' => $request->question_text,
                    'question_text_media' => (!is_null($request->question_dam_media_question_text)) ? $request->question_dam_media_question_text : [],
                    'question_type' => 'MCQ',
                    'keywords' => ($request->has('keywords')) ? array_map('trim', explode(',', strip_tags($request->keywords))) : [],
                    'default_mark' => ($request->default_mark > 0) ? QuizHelper::roundOfNumber($request->default_mark) : 1,
                    'difficulty_level' => strtoupper($request->input('difficulty_level', 'easy')),
                    'practice_question' => ($request->has('practice_question')) ? true : false,
                    'shuffle_answers' => ($request->has('shuffle_answers')) ? true : false,
                    'answers' => $answers,
                    'status' => $status,
                    'question_bank' => $qb->question_bank_id,
                    'quizzes' => [],
                    'editor_images' => ($request->has('editor_images')) ? $request->editor_images : [],
                    'created_by' => Auth::user()->username,
                    'created_at' => time(),
                    'updated_by' => '',
                    'updated_at' => time()
                ];
                break;

            default:
                abort(404);
                break;
        }

        // Redirect check
        if (Input::has('return')) {
            $return = urldecode(Input::get('return'));
        } else {
            $return = 'cp/assessment/list-questionbank';
        }
        if (Question::insert($qdata)) {
            // Appending the question entry into QB
            $questions = $qb->questions;
            array_push($questions, $question_id);
            // Appending the draft question entry into QB
            $draft_questions = (!empty($qb->draft_questions)) ? $qb->draft_questions : [];
            if ($status == 'DRAFT') {
                array_push($draft_questions, $question_id);
            }
            $qbank = [];
            $qbank['questions'] = $questions;
            $qbank['draft_questions'] = $draft_questions;
            $qbank['updated_by'] = Auth::user()->username;
            $qbank['updated_at'] = time();
            QuestionBank::where('question_bank_id', '=', $qb->question_bank_id)
                ->update($qbank);
            if ($status == 'DRAFT') {
                return redirect($return)
                    ->with('success', trans('admin/assessment.question_saved_success'));
            } else {
                return redirect('cp/assessment/success-question/' . $question_id . '?qb=' . $qb->question_bank_id);
            }
        } else {
            return redirect($return)
                ->with('error', trans('admin/assessment.problem_while_creating_newquestion'));
        }
    }

    public function getSuccessQuestion($qid)
    {
        if (!has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::ADD_QUESTION)) {
            return parent::getAdminError();
        }

        if (!is_numeric($qid)) {
            abort(404);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assessment.manage_question_bank') => 'assessment/list-questionbank',
            trans('admin/assessment.success_question') => ''
        ];

        Question::where('question_id', '=', (int)$qid)->firstOrFail(['question_id']);

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/assessment.question');
        $this->layout->pageicon = 'fa fa-file-text';
        $this->layout->pagedescription = trans('admin/assessment.question_success');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'assessment')
            ->with('submenu', 'questionbank');
        $this->layout->content = view('admin.theme.assessment.success_question');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getEditQuestion($qid)
    {
        try {
            if (!has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::EDIT_QUESTION)) {
                return parent::getAdminError();
            }

            if (!is_numeric($qid)) {
                abort(404);
            }

            $crumbs = [
                trans('admin/dashboard.dashboard') => 'cp',
                trans('admin/assessment.manage_question_bank') => 'assessment/list-questionbank',
                trans('admin/assessment.edit_question') => ''
            ];

            $qtype = (!in_array(Input::get('qtype'), ['mcq'])) ? 'mcq' : Input::get('qtype');
            $question = Question::where('question_id', '=', (int)$qid)->whereIn('status', ['ACTIVE','DRAFT'])->firstOrFail();
            $quizzes = Quiz::whereIn('quiz_id', $question->quizzes)
                ->where('is_production', 1)
                ->where('status', 'ACTIVE')
                ->count();
            $attempt = 0;
            if ($quizzes > 0) {
                $attempt = QuizAttemptData::where('question_id', '=', (int)$qid)->count();
                if ($attempt) {
                    abort(404);
                }
            }

            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = trans('admin/assessment.edit_question');
            $this->layout->pageicon = 'fa fa-file-text';
            $this->layout->pagedescription = trans('admin/assessment.edit_question');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'assessment')
                ->with('submenu', 'questionbank');
            switch (strtoupper($qtype)) {
                case 'MCQ':
                    $this->layout->content = view('admin.theme.assessment.question.mcq.edit_mcq_question')
                        ->with('question', $question)
                        ->with('attempt', $attempt);
                    break;

                default:
                    abort(404);
                    break;
            }
            $this->layout->footer = view('admin.theme.common.footer');
        } catch (Exception $e) {
            if ($e instanceof ModelNotFoundException) {
                return response()->view('errors.204', ['message' => trans('admin/assessment.question_deleted')]);
            } else {
                abort(404);
            }
        }
    }

    public function postEditQuestion(Request $request)
    {
        if (!has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::EDIT_QUESTION)) {
            return parent::getAdminError();
        }
        $question = Question::where('question_id', '=', (int)$request->_q)->firstOrFail();
        $quizzes = Quiz::whereIn('quiz_id', $question->quizzes)
            ->where('is_production', 1)
            ->where('status', 'ACTIVE')
            ->count();
        $attempt = 0;
        if ($quizzes > 0) {
            //for question attempt check
            $attempt = QuizAttemptData::where('question_id', '=', (int)$request->_q)->count();
        }
        if ($attempt > 0) { //if question attempted redirect back with error message
            return redirect('cp/assessment/edit-question/' . $question->question_id)
                ->withInput()
                ->with('attempted', ["You can't edit."]);
        }
        $messages = [];
        $rules = [
            'question_text' => 'required',
            'question_bank' => 'sometimes|required|not_in:null',
            'default_mark' => 'required|numeric|min:0|regex:/^\d*(\.\d{2})?$/'
        ];

        switch (strtoupper($request->_qtype)) {
            case 'MCQ':
                $rules['answer.0'] = 'required';
                $rules['answer.1'] = 'required';
                $rules['correct_answer'] = 'required';
                $messages['answer.0.required'] = 'Choice 1 answer field is required';
                $messages['answer.1.required'] = 'Choice 2 answer field is required';
                break;
        }

        $validation = Validator::make(Input::all(), $rules, $messages);
        if ($validation->fails()) {
            return redirect('cp/assessment/edit-question/' . $question->question_id)
                ->withInput()
                ->withErrors($validation);
        }

        // Editer images
        $editer_images = $question->editor_images;
        if (is_array($editer_images)) {
            $editer_images = array_merge($editer_images, $request->input('editor_images', []));
        } else {
            $editer_images = $request->input('editor_images');
        }

        switch (strtoupper($request->_qtype)) {
            case 'MCQ':
                $ans_chk_dupe = [];
                $answers = [];
                $answer_check = false;
                for ($i = 0; $i < count($request->answer); $i++) {
                    if ((trim($request->answer[$i]) != '' && $request->answer[$i] != null)) {
                        $ans = [];
                        $ans_chk_dupe[] = $ans['answer'] = trim($request->answer[$i]);
                        $ans['correct_answer'] = ((int)$request->correct_answer == $i) ? true : false;
                        $ans["answer_media"] = (isset($request->question_dam_media_answer[$i])) ? $request->question_dam_media_answer[$i] : [];
                        $ans['rationale'] = trim($request->rationale[$i]);
                        $ans["rationale_media"] = (isset($request->question_dam_media_rationale[$i])) ? $request->question_dam_media_rationale[$i] : [];
                        $answers[] = $ans;
                        if ($ans['correct_answer'] == true) {
                            $answer_check = true;
                        }
                    }
                }

                // Check if selected correct answer has valid answer (Not empty)
                if ($answer_check == false) {
                    // Throw error if selected the correct answer has empty answer
                    $validation->getMessageBag()
                        ->add(
                            'correct_answer.required',
                            'Correct answer field is required'
                        );
                    return redirect('cp/assessment/edit-question/' . $question->question_id)
                        ->withInput()
                        ->withErrors($validation);
                }
                if (empty($ans_chk_dupe) || (count($ans_chk_dupe) != count(array_unique($ans_chk_dupe)))) {
                    $validation->getMessageBag()
                        ->add(
                            'answer_dupe',
                            'Do not duplicate answers'
                        );
                    return redirect('cp/assessment/add-question')
                        ->withInput()
                        ->withErrors($validation);
                }

                $qdata = [
                    'question_text' => $request->question_text,
                    'question_text_media' => (!is_null($request->question_dam_media_question_text)) ? $request->question_dam_media_question_text : [],
                    'question_type' => 'MCQ',
                    'keywords' => ($request->has('keywords')) ? explode(',', strip_tags($request->keywords)) : [],
                    'default_mark' => ($request->default_mark > 0) ? QuizHelper::roundOfNumber($request->default_mark) : 1,
                    'difficulty_level' => strtoupper($request->input('difficulty_level', 'easy')),
                    'practice_question' => ($request->has('practice_question')) ? true : false,
                    'shuffle_answers' => ($request->has('shuffle_answers')) ? true : false,
                    'answers' => $answers,
                    'status' => 'ACTIVE',
                    'editor_images' => $editer_images,
                    'updated_by' => Auth::user()->username,
                    'updated_at' => time()
                ];
                break;

            default:
                abort(404);
                break;
        }

        // Redirect check
        if (Input::has('return')) {
            $return = urldecode(Input::get('return'));
        } else {
            $return = 'cp/assessment/list-questionbank';
        }

        if (Question::where('question_id', '=', (int)$question->question_id)
            ->update($qdata)
        ) {
            QuestionBank::where('question_bank_id', '=', (int)$question->question_bank)
                ->pull('draft_questions', (int)$question->question_id);
            return redirect($return)
                ->with('success', trans('admin/assessment.ques_update_success'));
        } else {
            return redirect($return)
                ->with('error', trans('admin/assessment.problem_while_updating_question'));
        }
    }

    /**
     * this method is used to duplicate questions
     *
     * @return \Illuminate\Http\Response
     */
    public function postCopyQuestions()
    {
        try {
            $questions = Input::get('questions');
            $questions = array_map('intval', $questions);
            $questionData = Question::questionAllDetails($questions)->first();
            $username = Auth::user()->username;
            $now = Carbon::now()->timestamp;
            $question = new Question();
            $question_id = Question::getNextSequence();
            $data = [
                'parent_question_id' => $questionData->question_id,
                'question_id' => $question_id,
                'question_name' => 'Q' . sprintf("%07d", $question_id),
                'question_text' => $questionData->question_text,
                'question_text_media' => $questionData->question_text_media,
                'question_type' => $questionData->question_type,
                'keywords' => $questionData->keywords,
                'default_mark' => $questionData->default_mark,
                'difficulty_level' => $questionData->difficulty_level,
                'shuffle_answers' => $questionData->shuffle_answers,
                'answers' => $questionData->answers,
                'status' => $questionData->status,
                'question_bank' => $questionData->question_bank,
                'quizzes' => [],
                'editor_images' => $questionData->editor_images,
                'created_by' => $username,
                'updated_by' => $username,
                'created_at' => time(),
                'updated_at' => time(),
            ];
            Question::insert($data);
            QuestionBank::where('question_bank_id', (int)$questionData->question_bank)
                ->push('questions', $question_id, true);
            return response()->json(['status' => true, 'question_id' => $question_id]);
        } catch (Exception $e) {
            Log::critical('Error ' . $e->getMessage() . ' File ' . $e->getFile() . ' At line ' . $e->getLine());
            return response()->json(['status' => false]);
        }
    }

    public function getDeleteQuestion($qid)
    {
        if (!has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::DELETE_QUESTION)) {
            return parent::getAdminError();
        }

        if (!is_numeric($qid)) {
            abort(404);
        }

        $start = Input::get('start', 0);
        $limit = Input::get('limit', 10);
        $qbid = Input::get('qbid');
        $search = Input::get('search', '');
        $order_by = Input::get('order_by', '7 desc');

        // Checking whether given question bank is available in db
        $q = Question::where('question_id', '=', (int)$qid)->where('status', 'ACTIVE')
            ->firstOrFail(['question_id', 'question_bank']);
        $quiz = Quiz::where('questions', '=', (int)$qid)->get(['quiz_id']);

        // Redirect check
        if (Input::has('return')) {
            $return = urldecode(Input::get('return')) . '?start=' . $start . '&limit=' . $limit . '&search=' . $search . '&order_by=' . $order_by;
        } else {
            $return = 'cp/assessment/list-questionbank';
        }

        if ($quiz->isEmpty()) {
            if (Question::where('question_id', '=', (int)$q->question_id)
                ->update(['status' => 'DELETED'])
            ) {
                QuestionBank::where('question_bank_id', '=', (int)$q->question_bank)
                    ->pull('draft_questions', (int)$q->question_id);
                QuestionBank::removeQuestion($q->question_id, $q->question_bank);
                $questionbank = QuestionBank::where('question_bank_id', '=', (int)$qbid)->firstOrFail();
                $totalRecords = count($questionbank->questions);
                if ($totalRecords <= $start) {
                    $start -= $limit;
                    if ($start < 0) {
                        $start = 0;
                    }
                }
                $return = urldecode(Input::get('return')) . '?start=' . $start . '&limit=' . $limit . '&search=' . $search . '&order_by=' . $order_by;
                return redirect($return)
                    ->with('success', trans('admin/assessment.question_delete_success'));
            } else {
                return redirect($return)
                    ->with('error', trans('admin/assessment.problem_while_deleting_question'));
            }
        } else {
            return redirect($return)
                ->with('error', trans('admin/assessment.ques_associated_with_quiz'));
        }
    }

    public function getReportQuiz($quiz_id, $AttemptedList = null)
    {
        if (!is_numeric($quiz_id)) {
            abort(404);
        }
        set_time_limit(300);
        
        // Checking whether given quiz is available in db
        $quiz = Quiz::where('quiz_id', '=', (int)$quiz_id)
            ->firstOrFail();

        $filter['pr'] = $filter['cf'] = $filter['ug'] = $filter['user'] = collect();//pr => parent relations
        $user = [];
        $relations = $quiz->relations;
        if (!empty($relations)) {
            // Get list of content feed
            $cf_parent_relations = $cf_user = $cf_usergroup = $pr_user = $pr_usergroup = [];
            if (!empty($relations['feed_quiz_rel'])) {
                $filter['cf'] = Program::whereIn(
                    'program_id',
                    array_map('intval', array_keys($relations['feed_quiz_rel']))
                )->get();
                $filter['cf']->filter(function ($item) use (&$cf_user, &$cf_usergroup, &$cf_parent_relations) {
                    if (!empty($item->relations['active_user_feed_rel'])) {
                        $cf_user[$item->program_id] = $item->relations['active_user_feed_rel'];
                    }
                    if (!empty($item->relations['active_usergroup_feed_rel'])) {
                        $cf_usergroup[$item->program_id] = $item->relations['active_usergroup_feed_rel'];
                    }
                    if (!empty($item->parent_relations['active_parent_rel'])) {
                        $cf_parent_relations[$item->program_id] = $item->parent_relations['active_parent_rel'];
                    }
                });
                if (!empty($cf_parent_relations)) {
                    $parent_ids = call_user_func_array('array_merge', $cf_parent_relations);
                    $filter['pr'] = Program::whereIn('program_id', array_map('intval', $parent_ids))->get();
                    $filter['pr']->filter(function ($item) use (&$pr_user, &$pr_usergroup) {
                        if (!empty($item->relations['active_user_feed_rel'])) {
                            $pr_user[$item->program_id] = $item->relations['active_user_feed_rel'];
                        }
                        if (!empty($item->relations['active_usergroup_feed_rel'])) {
                            $pr_usergroup[$item->program_id] = $item->relations['active_usergroup_feed_rel'];
                        }
                    });
                }
            }
            // Get list of active usergroups
            $ug = $ug_user = [];
            if (!empty($relations['active_usergroup_quiz_rel'])) {
                $ug = array_merge($ug, $relations['active_usergroup_quiz_rel']);
            }
            if (!empty($cf_usergroup)) {
                $ug = array_merge($ug, call_user_func_array('array_merge', $cf_usergroup));
            }
            if (!empty($pr_usergroup)) {
                $ug = array_merge($ug, call_user_func_array('array_merge', $pr_usergroup));
            }

            if (!empty($ug)) {
                $filter['ug'] = UserGroup::whereIn('ugid', array_map('intval', $ug))
                    ->get();
                $filter['ug']->filter(function ($item) use (&$ug_user) {
                    if (!empty($item->relations['active_user_usergroup_rel'])) {
                        $ug_user[$item->ugid] = $item->relations['active_user_usergroup_rel'];
                    }
                });
            }
            // Get list of active users
            if (!empty($relations['active_user_quiz_rel'])) {
                $user = array_merge($user, $relations['active_user_quiz_rel']);
            }
            if (!empty($cf_user)) {
                $user = array_merge($user, call_user_func_array('array_merge', $cf_user));
            }
            if (!empty($pr_user)) {
                $user = array_merge($user, call_user_func_array('array_merge', $pr_user));
            }
            if (!empty($ug_user)) {
                $user = array_merge($user, call_user_func_array('array_merge', $ug_user));
            }
            if (!empty($user)) {
                $filter['user'] = User::whereIn('uid', array_map('intval', $user))
                    ->get(['uid', 'firstname', 'lastname', 'username']);
            }
        }
        
        $attempts = QuizAttempt::where('quiz_id', '=', (int)$quiz_id);

        $show = Input::get('type', 'all');
        switch ($show) {
            case 'all':
                $attempts->whereIn('user_id', array_map('intval', $user));
                break;

            case 'user':
                $attempts->where('user_id', '=', (int)Input::get('value', 0));
                break;

            case 'ug':
                if (isset($ug_user[Input::get('value', 0)])) {
                    $attempts->whereIn('user_id', array_map('intval', $ug_user[Input::get('value', 0)]));
                } else {
                    $attempts->whereIn('user_id', []);
                }
                break;

            case 'cf':
                $u = [];
                if (isset($cf_user[Input::get('value', 0)])) {
                    $u = array_merge($u, $cf_user[Input::get('value')]);
                }
                if (isset($cf_usergroup[Input::get('value', 0)])) {
                    foreach ($cf_usergroup[Input::get('value')] as $value) {
                        $u = array_merge($u, $ug_user[$value]);
                    }
                }
                $attempts->whereIn('user_id', array_map('intval', $u));
                break;

            case 'package':
                $u = [];
                if (isset($pr_user[Input::get('value', 0)])) {
                    $u = array_merge($u, $pr_user[Input::get('value')]);
                }
                if (isset($pr_usergroup[Input::get('value', 0)])) {
                    foreach ($pr_usergroup[Input::get('value')] as $value) {
                        $u = array_merge($u, $ug_user[$value]);
                    }
                }
                $attempts->whereIn('user_id', array_map('intval', $u));
                break;
        }

        $status = Input::get('status', 'close');
        if ($status == 'close' || $status != 'open') {
            $attempts->where('status', '=', 'CLOSED');
        }
        if ($status == 'open') {
            $attempts->where('status', '=', 'OPENED');
        }
        $report = $attempts->get();

        /* below line is for exporting the Attamepted users and unattempted users*/
        if ($AttemptedList == "true" || $AttemptedList == "false") {
            (!$report->isEmpty()) ? $total_marks = '/ ' . $report[0]['total_mark'] : $total_marks = " ";
            if ($AttemptedList == "true") {
                $filename = "QuizReportForAttempted.csv";
                $header = [
                    'Quiz Name',
                    'FullName',
                    'UserName',
                    'Email',
                    'Status',
                    'Started On',
                    'Completed On',
                    'Time Taken',
                    "Marks $total_marks",
                    'Score'
                ];
                $title = ["Report Title: List of Attempted Users"];
            } else {
                $filename = "QuizReportForUnattempted.csv";
                $header = ['Quiz Name', 'FullName', 'UserName', 'Email', 'Quiz Start Date', 'Quiz End Date'];
                $title = ["Report Title: List of Unattempted Users"];
            }
            switch (Input::get('type')) {
                case 'all':
                    $uid = array_unique($user);
                    $filter = User::whereIn('uid', array_map('intval', $uid))->get(['uid', 'firstname', 'lastname', 'username', 'email']);
                    $not_attemped = $this->notAttempedUserReport(
                        $quiz,
                        $filter,
                        $report,
                        $filename,
                        $header,
                        $title,
                        $AttemptedList
                    );

                    break;
                case 'user':
                    $user = Input::get('value');
                    $filter = User::where('uid', '=', (int)$user)->get(['uid', 'firstname', 'lastname', 'username', 'email']);
                    $not_attemped = $this->notAttempedUserReport(
                        $quiz,
                        $filter,
                        $report,
                        $filename,
                        $header,
                        $title,
                        $AttemptedList
                    );

                    break;
                case 'ug':
                    if (Input::get('value', 0) != null) {
                        $ug_users = $ug_user[Input::get('value', 0)];
                    } else {
                        $ug_users = [];
                    }
                    $filter = User::whereIn('uid', $ug_users)->get(['uid', 'firstname', 'lastname', 'username', 'email']);
                    $not_attemped = $this->notAttempedUserReport(
                        $quiz,
                        $filter,
                        $report,
                        $filename,
                        $header,
                        $title,
                        $AttemptedList
                    );

                    break;
                case 'cf':
                    $cf_related_users = $u;
                    $filter = User::whereIn('uid', $cf_related_users)->get(['uid', 'firstname', 'lastname', 'username', 'email']);
                    $not_attemped = $this->notAttempedUserReport(
                        $quiz,
                        $filter,
                        $report,
                        $filename,
                        $header,
                        $title,
                        $AttemptedList
                    );

                    break;
                case 'package':
                    $pr_related_users = $u;
                    $filter = User::whereIn('uid', $pr_related_users)->get(['uid', 'firstname', 'lastname', 'username', 'email']);
                    $not_attemped = $this->notAttempedUserReport(
                        $quiz,
                        $filter,
                        $report,
                        $filename,
                        $header,
                        $title,
                        $AttemptedList
                    );

                    break;
            }
        }
        /* ends here*/

        if ($quiz->attempts != 1) {
            $result = [];
            switch (Input::get('tries', 'all')) {
                case 'first':
                    foreach ($filter['user'] as $value) {
                        $result[] = $report->whereLoose('user_id', (int)$value->uid)
                            ->sortBy('attempt_id')
                            ->first();
                    }
                    $report = collect(array_filter($result));
                    break;

                case 'last':
                    foreach ($filter['user'] as $value) {
                        $result[] = $report->whereLoose('user_id', (int)$value->uid)
                            ->sortByDesc('attempt_id')
                            ->first();
                    }
                    $report = collect(array_filter($result));
                    break;

                case 'high':
                    foreach ($filter['user'] as $value) {
                        $result[] = $report->whereLoose('user_id', (int)$value->uid)
                            ->sortByDesc('obtained_mark')
                            ->first();
                    }
                    $report = collect(array_filter($result));
                    break;

                case 'low':
                    foreach ($filter['user'] as $value) {
                        $result[] = $report->whereLoose('user_id', (int)$value->uid)
                            ->sortBy('obtained_mark')
                            ->first();
                    }
                    $report = collect(array_filter($result));
                    break;
            }
        }

        if (!empty($report)) {
            $userIds = $report->pluck('user_id')->all();
            if (!empty($userIds)) {
                $userDetails = User::whereIn('uid', $userIds)->get(['uid', 'firstname', 'lastname', 'username', 'email']);
            } else {
                $userDetails = collect([]);
            }
            if (!empty($userDetails)) {
                $filter['user'] = $userDetails;
            }
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assessment.title_manage_quiz') => 'assessment/list-quiz',
            trans('admin/assessment.quiz_report') => ''
        ];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = 'Quiz : ' . $quiz->quiz_name;
        $this->layout->pageicon = 'fa fa-file-text';
        $this->layout->pagedescription = '';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'assessment')
            ->with('submenu', 'quiz');
        $this->layout->content = view('admin.theme.assessment.quiz_attempt_report')
            ->with('quiz', $quiz)
            ->with('report', $report->sortByDesc('started_on'))
            ->with('filter', $filter);
        $this->layout->footer = view('admin.theme.common.footer');
    }


// import quize code
    public function getImportQuestionbank($quiz_id = null)
    {
        if ($quiz_id != '' && !has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::IMPORT_QUIZ)) {
            return parent::getAdminError();
        }

        if ($quiz_id == '' && !has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::IMPORT_QUESTION_BANK)) {
            return parent::getAdminError();
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assessment.manage_question_bank') => 'assessment/list-questionbank',
            trans('admin/assessment.import_question_bank') => ''
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/assessment.import_question_bank');
        $this->layout->pageicon = 'fa fa-bank';
        $this->layout->pagedescription = trans('admin/assessment.import_qb_in_bulk');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'assessment')
            ->with('submenu', 'questionbank');
        $this->layout->content = view('admin.theme.assessment.import_questionbanks')->with('quiz_id', $quiz_id);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /*
    Downloads question bank bulk import template
     */
    public function getQuestionBankBulkimportTemplate()
    {
        try {
            return $this->downloadTemplate('questionbanks_bulk_import');
        } catch (Exception $e) {
            Log::error('Error ' . $e->getMessage() . ' File ' . $e->getFile() . ' At line ' . $e->getLine());
            return redirect('cp/assessment/import-questionbank')
                ->with("error", "Something went wrong. Please try again later.");
        }
    }

    public function postImportQuestionbank($quiz_id = null)
    {
        try {
            ini_set('max_execution_time', 300);
            $file = Input::file('csvfile');

            if (Input::hasFile('csvfile')) {
                $extension = strtolower($file->getClientOriginalExtension());
            } else {
                $extension = '';
            }

            $messages = [
                'csvfile.required' => trans('admin/assessment.required'),
                'extension.in' => trans('admin/assessment.csv_file_required')
            ];

            $validation = Validator::make(
                [
                    'csvfile' => $file,
                    'extension' => $extension,
                ],
                [
                    'csvfile' => 'required',
                    'extension' => 'in:csv',
                ],
                $messages
            );

            if ($validation->fails()) {
                return redirect('cp/assessment/import-questionbank')->withInput()->withErrors($validation);
            } elseif ($validation->passes()) {
                $csv = [];
                $error_rows = [];
                $i = 1;
                if (($fileopen = fopen($file, "r")) !== false) {
                    $header = [];
                    while (($row = fgetcsv($fileopen, 0, ',', '"')) !== false) {
                        if ($i == 1) {
                            $header = $row;
                            $default_file_template = config('app.upload_templates.questionbanks_bulk_import');
                            $default_file_open = fopen($default_file_template, "r");
                            $default_template_header = fgetcsv($default_file_open, 0, ',', '"');
                            if (!empty(array_diff($header, $default_template_header))) {
                                $error = trans('admin/assessment.import_column_error');
                                return redirect('cp/assessment/import-questionbank/' . $quiz_id)->with('error', $error);
                            }
                        } else {
                            if (count($header) == count($row)) {
                                $csv[] = array_combine($header, $row);
                            } else {
                                $error_rows[] = $row;
                            }
                        }
                        $i++;
                    }
                }
                fclose($fileopen);
                $repeated_questionbanks = [];
                if (isset($csv) && !empty($csv)) {
                    Validator::extend('contains', function ($attribute, $value, $parameters) {
                        $difficulty_level = strtolower(trim(strip_tags($value)));
                        if (in_array($difficulty_level, ['easy', 'medium', 'difficult'])) {
                            return true;
                        } else {
                            return false;
                        }
                    });

                    Validator::extend('correct_answer', function ($attribute, $value, $parameters) {
                        $duplicate_answer = array_count_values($parameters);
                        $correct_answers = isset($duplicate_answer[1]) ? $duplicate_answer[1] : 2;
                        if ($correct_answers > 1) {
                            return false;
                        } else {
                            return true;
                        }
                    });

                    Validator::extend('choice', function ($attribute, $value, $parameters) {
                        $array = array_count_values($value);
                        $error = true;
                        foreach ($array as $key => $val) {
                            if ($key) {
                                if ($val > 1) {
                                    $error = false;
                                }
                            }
                        }
                        return $error;
                    });

                    $errorFlag = 0;
                    $success_count = 0;
                    $failed_count = 0;
                    $error_array[] = $header;
                    $no_of_records = count($csv);
                    $qb_questions = [];

                    /* Encoding the string to handle the UTF - 8 data
                     * TODO:
                     *     : Needs to check if any special characters
                     **/
                    array_walk_recursive($csv, function (&$item, $key) {
                        return (is_string($item)) ? ((!preg_match('//u', $item)) ? $item = utf8_encode($item) : '' ) : '';
                    });

                    foreach ($csv as $value) {
                        $correct_answer_array = [];
                        if (!empty($value['Correct Answer1'])) {
                            $correct_answer_array[] = $value["Correct Answer1"];
                        }
                        if (!empty($value['Correct Answer2'])) {
                            $correct_answer_array[] = $value["Correct Answer2"];
                        }
                        if (!empty($value['Correct Answer3'])) {
                            $correct_answer_array[] = $value["Correct Answer3"];
                        }
                        if (!empty($value['Correct Answer4'])) {
                            $correct_answer_array[] = $value["Correct Answer4"];
                        }
                        if (!empty($value['Correct Answer5'])) {
                            $correct_answer_array[] = $value["Correct Answer5"];
                        }
                        if (!empty($value['Correct Answer6'])) {
                            $correct_answer_array[] = $value["Correct Answer6"];
                        }
                        $correct_answer_array = implode(',', $correct_answer_array);

                        $rules = [
                            'question_bank_name' => 'required|min:2|max:512',
                            'default_mark' => 'sometimes|numeric|min:0|regex:/^(?!\.?$)\d{0,6}(\.\d{0,2})?$/',
                            'question_text' => 'required',
                            'difficulty_level' => 'required|contains',
                            'shuffle_answers' => 'in:1',
                            /* This is for sending entire "Answer" array to the validator and checking the duplication */
                            'answers' => "required|choice",
                            'answer1' => "required",
                            'answer2' => "required",
                            'answer3' => "required_with:correct_answer3",
                            'answer4' => "required_with:correct_answer4",
                            'answer5' => "required_with:correct_answer5",
                            'answer6' => "required_with:correct_answer6",
                            'correct_answer1' => "in:1|required_without_all:correct_answer2,correct_answer3,correct_answer4,correct_answer5,correct_answer6|correct_answer:{$correct_answer_array}",
                            'correct_answer2' => "required_without_all:correct_answer1,correct_answer3,correct_answer4,correct_answer5,correct_answer6|correct_answer:{$correct_answer_array}|in:1",
                            'correct_answer3' => "in:1|required_without_all:correct_answer1,correct_answer2,correct_answer4,correct_answer5,correct_answer6|correct_answer:{$correct_answer_array}",
                            'correct_answer4' => "in:1|required_without_all:correct_answer1,correct_answer2,correct_answer3,correct_answer5,correct_answer6|correct_answer:{$correct_answer_array}",
                            'correct_answer5' => "in:1|required_without_all:correct_answer1,correct_answer2,correct_answer3,correct_answer4,correct_answer6|correct_answer:{$correct_answer_array}",
                            'correct_answer6' => "in:1|required_without_all:correct_answer1,correct_answer2,correct_answer3,correct_answer4,correct_answer5|correct_answer:{$correct_answer_array}",
                        ];

                        $messages = [
                            'contains' => trans('admin/assessment.difficulty_level_error'),
                            'in' => 'The :attribute must be 1',
                            'correct_answer' => 'Set any one of the choices as the Correct answers',
                            'choice' => ':attribute field is duplicated'
                        ];

                        $validation_array = [];
                        if (isset($value['Question Bank Name *']) &&
                            isset($value['Question Text *']) &&
                            isset($value['Difficulty Level *']) &&
                            isset($value['Answer1 *']) &&
                            isset($value['Answer2 *'])
                        ) {
                            $validation_array = [
                                'question_bank_name' => $value['Question Bank Name *'],
                                'default_mark' => $value['Default Mark'],
                                'question_text' => $value['Question Text *'],
                                'difficulty_level' => $value['Difficulty Level *'],
                                "answers" => [
                                    'answer1' => $value['Answer1 *'],
                                    'answer2' => $value['Answer2 *'],
                                    'answer3' => $value['Answer3'],
                                    'answer4' => $value['Answer4'],
                                    'answer5' => $value['Answer5'],
                                    'answer6' => $value['Answer6'],
                                ],
                                'answer1' => $value['Answer1 *'],
                                'answer2' => $value['Answer2 *'],
                                'answer3' => $value['Answer3'],
                                'answer4' => $value['Answer4'],
                                'answer5' => $value['Answer5'],
                                'answer6' => $value['Answer6'],
                                'correct_answer1' => $value['Correct Answer1'],
                                'correct_answer2' => $value['Correct Answer2'],
                                'correct_answer3' => $value['Correct Answer3'],
                                'correct_answer4' => $value['Correct Answer4'],
                                'correct_answer5' => $value['Correct Answer5'],
                                'correct_answer6' => $value['Correct Answer6'],
                            ];
                        } else {
                            $error = trans('admin/assessment.import_column_error');
                            return redirect('cp/assessment/import-questionbank/' . $quiz_id)->with('error', $error);
                        }


                        $validation = Validator::make($validation_array, $rules, $messages);

                        if ($validation->fails()) {
                            $errorFlag = 1;
                            $failed_count = $failed_count + 1;
                            $messages = $validation->errors();
                            $messages = implode("\n", $messages->all());
                            $error_array_index = array_values($value);
                            array_push($error_array_index, $messages);
                            $error_array[] = $error_array_index;
                        } elseif ($validation->passes()) {
                            $question_bank_slug = QuestionBank::getQuestionBankNameSlug($value['Question Bank Name *']);
                            $qbank_name = QuestionBank::where('question_bank_slug', '=', $question_bank_slug)
                                ->whereNotIn("status", ["DELETED"])
                                ->value('question_bank_name');
                            $question_id = Question::getNextSequence();
                            $qbank_id = null;
                            if ($qbank_name) {
                                $qb_details = QuestionBank::where('question_bank_name', '=', $qbank_name)
                                    ->whereNotIn("status", ["DELETED"])
                                    ->get()
                                    ->toArray();
                                $qbank_id = $qb_details[0]["question_bank_id"];
                            } else {
                                $question_name = trim(strip_tags($value['Question Bank Name *']));

                                if (in_array($question_name, $repeated_questionbanks)) {
                                    $qbank_id = QuestionBank::where('question_bank_name', '=', $question_name)
                                        ->whereNotIn("status", ["DELETED"])
                                        ->orderBy('created_at', 'desc')
                                        ->value('question_bank_id');
                                } else {
                                    $qbank_id = QuestionBank::getNextSequence();
                                    $repeated_questionbanks[] = $question_name;
                                    $qbankdata = [
                                        'question_bank_id' => (int)$qbank_id,
                                        'question_bank_name' => $question_name,
                                        'question_bank_slug' => $question_bank_slug,
                                        'question_bank_description' => $value['Question Bank Description'],
                                        'keywords' => array_map(
                                            'trim',
                                            explode(',', strip_tags($value['Question Bank Keywords']))
                                        ),
                                        'questions' => [],
                                        'draft_questions' => [],
                                        'editor_images' => [],
                                        'relations' => [
                                            'active_user_questionbank_rel' => [],
                                            'inactive_user_questionbank_rel' => [],
                                            'active_usergroup_questionbank_rel' => [],
                                            'inactive_usergroup_questionbank_rel' => []
                                        ],
                                        'status' => 'ACTIVE',
                                        'created_by' => Auth::user()->username,
                                        'created_at' => time(),
                                        'updated_by' => Auth::user()->username,
                                        'updated_at' => time()
                                    ];
                                    $res = QuestionBank::insert($qbankdata);
                                }
                            }
                            if ($qbank_id) {
                                $qb_questions[] = $question_id;
                                $correct_answer_flag = 1;
                                $answers = [];
                                if ((trim($value['Answer1 *']) != '' && $value['Answer1 *'] != null)) {
                                    $ans1['answer'] = $this->replaceText($value['Answer1 *']);
                                    $ans1['correct_answer'] = ($value['Correct Answer1'] != 1 ? false : true);
                                    $ans1['rationale'] = $this->replaceText($value['Answer1 Rationale']);
                                    $answers[] = $ans1;
                                }
                                if ((trim($value['Answer2 *']) != '' && $value['Answer2 *'] != null)) {
                                    $ans2['answer'] = $this->replaceText($value['Answer2 *']);
                                    $ans2['correct_answer'] = ($value['Correct Answer2'] != 1 ? false : true);
                                    $ans2['rationale'] = $this->replaceText($value['Answer2 Rationale']);
                                    $answers[] = $ans2;
                                }
                                if ((trim($value['Answer3']) != '' && $value['Answer3'] != null)) {
                                    $ans3['answer'] = $this->replaceText($value['Answer3']);
                                    $ans3['correct_answer'] = ($value['Correct Answer3'] != 1 ? false : true);
                                    $ans3['rationale'] = $this->replaceText($value['Answer3 Rationale']);
                                    $answers[] = $ans3;
                                }
                                if ((trim($value['Answer4']) != '' && $value['Answer4'] != null)) {
                                    $ans4['answer'] = $this->replaceText($value['Answer4']);
                                    $ans4['correct_answer'] = ($value['Correct Answer4'] != 1 ? false : true);
                                    $ans4['rationale'] = $this->replaceText($value['Answer4 Rationale']);
                                    $answers[] = $ans4;
                                }
                                if ((trim($value['Answer5']) != '' && $value['Answer5'] != null)) {
                                    $ans5['answer'] = $this->replaceText($value['Answer5']);
                                    $ans5['correct_answer'] = ($value['Correct Answer5'] != 1 ? false : true);
                                    $ans5['rationale'] = $this->replaceText($value['Answer5 Rationale']);
                                    $answers[] = $ans5;
                                }
                                if ((trim($value['Answer6']) != '' && $value['Answer6'] != null)) {
                                    $ans6['answer'] = $this->replaceText($value['Answer6']);
                                    $ans6['correct_answer'] = ($value['Correct Answer6'] != 1 ? false : true);
                                    $ans6['rationale'] = $this->replaceText($value['Answer6 Rationale']);
                                    $answers[] = $ans6;
                                }

                                $qdata = [
                                    'question_id' => (int)$question_id,
                                    'question_name' => 'Q' . sprintf("%07d", $question_id),
                                    'question_text' => $value['Question Text *'],
                                    'question_type' => 'MCQ',
                                    'keywords' => array_map('trim', explode(',', strip_tags($value['Question Keyword']))),
                                    'default_mark' => ($value['Default Mark'] > 0 ? QuizHelper::roundOfNumber($value['Default Mark']) : 1),
                                    'difficulty_level' => strtoupper(trim(strip_tags($value['Difficulty Level *']))),
                                    'practice_question' => false,
                                    'shuffle_answers' => ($value['Shuffle Answers *'] != 1 ? false : true),
                                    'answers' => $answers,
                                    'status' => 'ACTIVE',
                                    'question_bank' => (int)$qbank_id,
                                    'quizzes' => [],
                                    'editor_images' => [],
                                    'created_by' => Auth::user()->username,
                                    'created_at' => time(),
                                    'updated_by' => Auth::user()->username,
                                    'updated_at' => time()
                                ];
                                if (Question::insert($qdata)) {
                                    if (QuestionBank::where('question_bank_id', '=', (int)$qbank_id)
                                        ->push('questions', (int)$question_id)
                                    ) {
                                        $success_count = $success_count + 1;
                                    }
                                }
                            }
                        }
                    }

                    if ($errorFlag == 1) {
                        if ($failed_count == $no_of_records) {
                            $status = 'FAILED';
                        } else {
                            $status = 'PARTIAL';
                        }
                    } else {
                        $status = 'SUCCESS';
                    }

                    $filename = $file->getClientOriginalName();

                    //adding import history to the db
                    QuestionbankImportHistory::getInsertHistory(
                        $filename,
                        $success_count,
                        $failed_count,
                        $status,
                        $no_of_records
                    );

                    //code to add questions to quiz table
                    if ($quiz_id != '' && is_numeric($quiz_id)) {
                        $quiz = Quiz::where('quiz_id', '=', (int)$quiz_id)->first();

                        if (!empty($quiz)) {
                            $attempt = QuizAttempt::where('quiz_id', '=', (int)$quiz_id)->count();

                            if ($attempt == 0) {
                                $quiz_questions = $quiz->questions;
                                $layout = $quiz->page_layout;

                                if (!empty($qb_questions)) {
                                    // Converting array values into integer
                                    $qb_questions = array_map('intval', $qb_questions);
                                    foreach ($qb_questions as $value) {
                                        // questions field
                                        if (!in_array($value, $quiz_questions)) {
                                            array_push($quiz_questions, $value);
                                        }

                                        // page_layout field
                                        if (is_array($layout) && !empty($layout)) {
                                            $last_key = key(array_slice($layout, -1, 1, true));
                                        } else {
                                            $last_key = 0;
                                        }

                                        if (isset($layout[$last_key]) &&
                                            count($layout[$last_key]) < $quiz->question_per_page
                                        ) {
                                            $layout[$last_key][] = (int)$value;
                                        } else {
                                            $layout[] = [(int)$value];
                                        }
                                    }

                                    // Total marks for this quiz
                                    $total = Quiz::getTotalMarks($quiz_questions);
                                    if (Quiz::where('quiz_id', '=', (int)$quiz->quiz_id)
                                        ->update([
                                            'questions' => $quiz_questions,
                                            'page_layout' => $layout,
                                            'total_mark' => $total
                                        ])
                                    ) {
                                        // Updating the quiz_id in selected questions quizzes field
                                        if (is_array($qb_questions) && !empty($qb_questions)) {
                                            Question::updateQuizQuestions($quiz->quiz_id, $qb_questions);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ($errorFlag == 1) {
                        Session::put('questionbank_error_report', $error_array);
                        Session::put('errorflag', 'dummytext');

                        return redirect('cp/assessment/import-questionbank/' . $quiz_id);
                    } else {
                        Session::forget('questionbank_error_report');
                        Session::forget('errorflag');
                        $success = trans('admin/assessment.import_success');
                        return redirect('cp/assessment/import-questionbank/' . $quiz_id)->with('success', $success);
                    }
                } else {
                    $error = trans('admin/assessment.import_error');
                    return redirect('cp/assessment/import-questionbank/' . $quiz_id)->with('error', $error);
                }
            }
        } catch (Exception $e) {
            $status["flag"] = false;
            $status["error_info"] = trans("admin/exception.{$e->getCode()}");
            return Redirect::back()->with("error", trans("admin/assessment.unknown_exception"));
        }
    }

    public function getExportErrorReport()
    {
        $questionbank_error_report = Session::get('questionbank_error_report');
        if ($questionbank_error_report) {
            $filename = "error.csv";
            $fp = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . $filename);
            foreach ($questionbank_error_report as $line) {
                fputcsv($fp, $line);
            }
            exit;
        }
    }

    public function getQuestionbankImportHistory()
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assessment.manage_question_bank') => 'assessment/list-questionbank',
            trans('admin/assessment.import_qb_history') => ''
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/assessment.questionbank_import_history');
        $this->layout->pageicon = 'fa fa-bank';
        $this->layout->pagedescription = trans('admin/assessment.list_of_imported_qb_filter');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'assessment')
            ->with('submenu', 'questionbank');
        $this->layout->footer = view('admin.theme.common.footer');
        $this->layout->content = view('admin.theme.assessment.questionbankimport_history');
    }

    public function getAjaxQuestionbankImportHistory()
    {
        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = "";
        $viewmode = Input::get('view', 'desktop');
        $order_by = Input::get('order', 'created_at');
        $orderByArray = ['created_at' => 'desc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            switch ($order_by[0]['column']) {
                case '5':
                    $orderByArray = ['created_by' => $order_by[0]['dir']];
                    break;

                case '6':
                    $orderByArray = ['created_at' => $order_by[0]['dir']];
                    break;
            }
        }
        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }
        if (isset($search['value'])) {
            $searchKey = trim(strip_tags($search['value']));
        }
        $totalRecords = QuestionbankImportHistory::count();
        if (!empty($searchKey)) {
            $filteredRecords = QuestionbankImportHistory::orWhere('filename', 'like', '%' . $searchKey . '%')
                ->orWhere('created_by', 'like', '%' . $searchKey . '%')
                ->get()
                ->count();

            $filtereddatas = QuestionbankImportHistory::orWhere('filename', 'like', '%' . $searchKey . '%')
                ->orWhere('created_by', 'like', '%' . $searchKey . '%')
                ->orderBy(key($orderByArray), $orderByArray[key($orderByArray)])
                ->skip((int)$start)
                ->take((int)$limit)
                ->get();
        } else {
            $filteredRecords = QuestionbankImportHistory::count();

            $filtereddatas = QuestionbankImportHistory::orderBy(key($orderByArray), $orderByArray[key($orderByArray)])
                ->skip((int)$start)
                ->take((int)$limit)
                ->get();
        }

        $dataArr = [];
        foreach ($filtereddatas as $filtereddata) {
            $temp = [];
            $temp[] = $filtereddata->filename;
            $temp[] = $filtereddata->no_of_records;
            $temp[] = $filtereddata->success_count;
            $temp[] = $filtereddata->failed_count;
            $temp[] = ucwords(strtolower($filtereddata->status));
            $temp[] = $filtereddata->created_by;
            $temp[] = Timezone::convertFromUTC(
                '@' . $filtereddata->created_at,
                Auth::user()->timezone,
                config('app.date_format')
            );
            $dataArr[] = $temp;
        }
        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr
        ];
        return response()->json($finaldata);
    }

    //Quiz - question mapping
    public function getQuizQuestionMapping($quiz_id)
    {
        $QuizAttributes = config('app.QuizExportQuizFields');
        $quiz = Quiz::getQuiz($QuizAttributes, $quiz_id);
        $questiondetails = [];
        $export_quiz_list = null;

        if (isset($quiz[0]['questions']) && !empty($quiz[0]['questions'])) {
            foreach ($quiz as $key => $val) {
                if (isset($val['updated_by']) && !empty($val['updated_by'])) {
                    $updated_by = $val['updated_by'];
                } else {
                    $updated_by = $val['created_by'];
                }
                $updated_at = (isset($val['updated_at'])) ? $val['updated_at'] : $val['created_at'];
                $export_quiz_list[] = $this->getQuestionsData(
                    array_pull($quiz[$key], 'questions'),
                    $val['quiz_name'],
                    $updated_by,
                    $updated_at
                );
            }
        } else {
            return redirect('cp/assessment/list-quiz')->with('warning', 'No data to export');
        }
        $headers = ['Question Id', 'Questions', 'Question Bank Name', 'Quiz Name', 'Updated At', 'Updated by'];
        array_pull($export_quiz_list, '_id');
        $this->writeCsv($headers, $export_quiz_list);
        unset($export_quiz_list);
        exit();
    }

    public function getQuestionsData($questions, $quizname, $updated_by, $update_at)
    {
        $questionsDetails = Question::questionDetails($questions);
        $questionbank_list = array_unique(array_column($questionsDetails, 'question_bank'));
        $question_bank_name_list = QuestionBank::getQuestionbankDetails($questionbank_list);
        $export_question_list = null;
        foreach ($questionsDetails as $val) {
            array_pull($questionsDetails, '_id');
            $each_question['question_bank'] = $this->getQuestionBankName($question_bank_name_list, $val['question_bank']);
            $val['question_text'] = htmlspecialchars_decode(
                trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($val['question_text']))),
                ENT_QUOTES
            );
            $valueNew = array_merge(
                $val,
                [
                    'quiz_name' => $quizname,
                    'updated_at' => $update_at,
                    'updated_by' => $updated_by,
                    'question_bank' => $each_question['question_bank']
                ]
            );
            $export_question_list[] = $valueNew;
        }
        unset($questionsDetails);
        return $export_question_list;
    }

    private function getQuestionBankName(&$question_bank_name_list, $question_bank_id)
    {
        foreach ($question_bank_name_list as $qb_list) {
            $filter = $qb_list->where('question_bank_id', $question_bank_id)->get();
            $bank_collection = $filter->all();
        }
        return $bank_collection[0]->question_bank_name;
    }

    public function writeCsv($header, $export_list)
    {
        $filename = "QuizExport.csv";
        $fp = fopen('php://output', 'w');
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename=' . $filename);
        fputcsv($fp, $header);

        /* writing data's into csv */
        foreach ($export_list as $line) {
            foreach ($line as $row) {
                unset($row['_id']);
                array_walk($row, function (&$item, $key) {
                    (is_string($item)) ? $item = html_entity_decode(strip_tags(mb_convert_encoding($item, 'ASCII', 'UTF-8'))) : '';
                });
                fputcsv($fp, $row);
            }
        }
    }

    /* QuestionBank export mapping*/
    public function getQuestionBankMapping($qbid)
    {
        $QuestionBank = QuestionBank::getQuestionBank($qbid);
        $listofQuestion = Question::questionAllDetails($QuestionBank->questions);
        $title = ["Report Title : Question Bank Export"];
        $filename = "QuestionBankExport.csv";
        $headers = ['Question Bank Name', 'Question Bank Description', 'Question Bank Keywords', 'Default Mark', 'Difficulty Level', 'Shuffle Answers', 'Question Text', 'Answer1', 'Correct Answer1', 'Answer1 Rationale', 'Answer2', 'Correct Answer2', 'Answer2 Rationale', 'Answer3', 'Correct Answer3', 'Answer3 Rationale', 'Answer4', 'Correct Answer4', 'Answer4 Rationale', 'Answer5', 'Correct Answer5', 'Answer5 Rationale', 'Answer6', 'Correct Answer6', 'Answer6 Rationale', 'Question Keyword', 'Number of Times Question Used', 'Updated At'];
        if (!$listofQuestion->isEmpty()) {
            foreach ($listofQuestion as $value) {
                $i = 0;
                $list[$i++] = $QuestionBank->question_bank_name;
                $list[$i++] = htmlspecialchars_decode(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($QuestionBank->question_bank_description))), ENT_QUOTES);
                $list[$i++] = implode(",", $QuestionBank->keywords);
                $list[$i++] = $value['default_mark'];
                $list[$i++] = $value['difficulty_level'];
                $list[$i++] = $value['shuffle_answers'];
                $list[$i++] = htmlspecialchars_decode(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($value['question_text']))), ENT_QUOTES);
                foreach ($value['answers'] as $each_answer) {
                    $list[$i++] = htmlspecialchars_decode(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($each_answer['answer']))), ENT_QUOTES);
                    $list[$i++] = htmlspecialchars_decode(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($each_answer['correct_answer']))), ENT_QUOTES);
                    $list[$i++] = htmlspecialchars_decode(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($each_answer['rationale']))), ENT_QUOTES);
                }
                while ($i < 25) {
                    $list[$i++] = '';
                }
                $list[$i++] = implode(", ", $value['keywords']);
                $list[$i++] = count($value['quizzes']);
                $list[$i++] = $QuestionBank->updated_at;
                $export_list[] = $list;
            }
        } else {
            return redirect('cp/assessment/list-questionbank')->with('warning', 'No data to export');
        }
        $this->csv($filename, $export_list, $headers, $title);
    }

    /* Quiz - channel, post mapping */
    public function getChannelPostMapping($quiz_id)
    {
        $export_list = [];
        $quiz_fields = config('app.QuizExportforChannel');
        $Quiz_details = Quiz::getQuiz($quiz_fields, $quiz_id);
        if (array_key_exists('relations', $Quiz_details[0]) &&
            isset($Quiz_details[0]['relations']) &&
            !empty($Quiz_details[0]['relations']['feed_quiz_rel'])
        ) {
            foreach ($Quiz_details as $key => $quiz) {
                $channelId = [];
                $relations = $quiz['relations'];
                $postlist = $relations['feed_quiz_rel'];
                $channelList = array_keys($postlist);
                $channelArray = Program::getTypebyID($channelList);
                if (is_array($channelArray) && isset($channelArray) && !empty($channelArray)) {
                    foreach ($channelArray as $v) {
                        //to get only channel id, excluding product/package ids
                        $channelId[] = $v['program_id'];
                        unset($v['_id']);
                    }
                }
                $finalList = $this->searchValuesByKeys($channelId, $postlist);
                $array = array_flatten($finalList);
                $post_details = Packet::getPacketList($array);
                foreach ($post_details as $post) {
                    $feed_title = Program::getCFTitle($post['feed_slug']);
                    $list['quiz_name'] = $quiz['quiz_name'];
                    $list['feed_name'] = $feed_title;
                    $list['post_name'] = $post['packet_title'];
                    $updated_at = (isset($quiz['updated_at']) && !empty($quiz['updated_at'])) ? $quiz['updated_at'] : $quiz['created_at'];
                    $updated_at =  Timezone::convertFromUTC('@' . $updated_at, Auth::user()->timezone, config('app.date_ymd_his'));
                    $list['updated_at'] = $updated_at;
                    $list['updated_by'] = (isset($quiz['updated_by']) && !empty($quiz['updated_by'])) ? $quiz['updated_by'] : $quiz['created_by'];
                    $export_list[] = $list;
                }
            }
        } else {
            return redirect('cp/assessment/list-quiz')->with('warning', 'No data to export');
        }
        $title = ["Report Title: Quiz Export with Channel"];
        $headers = ['Quiz Name', 'Channel Name', 'Post Name', 'Updated At', 'Updated By'];
        $filename = "Quiz-ChannelExport.csv";
        $this->csv($filename, $export_list, $headers, $title);
        die;
    }

    //TODO: Muni: Shouldn't this be a private method?
    public function searchValuesByKeys($keysArr, $array)
    {
        $finalList = [];
        if (is_array($keysArr) && isset($keysArr) && !empty($keysArr)) {
            foreach ($keysArr as $key => $val) {
                $channelData[] = $array[$val];
            }
            $finalList = array_combine($keysArr, $channelData);
        }
        return $finalList;
    }

    /* Fetching Not - Attempted/Attempted user report for the individual quiz */
    public function notAttempedUserReport($quiz, $filter, $report, $filename, $header, $title, $AttemptedList)
    {
        /* All the users */
        $users = $filter->toArray();
        $uid = array_column($users, 'uid');

        /*  Users who have already attempted the quiz*/
        $attemped_users = $report;
        // $attempted_uid = array_column($attemped_users, 'user_id');
        $attempted_uid = $attemped_users->lists('user_id')->all();

        $userList = [];
        if ($AttemptedList == "true") {
            if (isset($attemped_users) && !empty($attemped_users)) {
                foreach ($attemped_users as $key => $AttemptUser) {
                    $userDetails = User::where('uid', '=', (int)$AttemptUser['user_id'])->get()->toArray();
                    $AttemptedUserList['quiz_name'] = $quiz['quiz_name'];
                    $AttemptedUserList['fullname'] = $userDetails[0]['firstname'] . ' ' . $userDetails[0]['lastname'];
                    $AttemptedUserList['username'] = $userDetails[0]['username'];
                    $AttemptedUserList['email'] = $userDetails[0]['email'];
                    $AttemptedUserList['status'] = $AttemptUser['status'];
                    $AttemptedUserList['started_on'] = Timezone::convertFromUTC('@' . strtotime($AttemptUser['started_on']), Auth::user()->timezone, config('app.date_time_format'));
                    $AttemptedUserList['completed_on'] = (!empty($AttemptUser['completed_on'])) ? Timezone::convertFromUTC('@' . strtotime($AttemptUser['completed_on']), Auth::user()->timezone, config('app.date_time_format')) : 'In progress';
                    if ($AttemptUser['completed_on'] != "") {
                        //$AttemptedUserList['time_taken'] = $AttemptUser['started_on']->diffInMinutes($AttemptUser['completed_on']). ' mins';
                        $AttemptedUserList['time_taken'] = $AttemptUser['completed_on']->diffForHumans($AttemptUser['started_on'], true);
                    } else {
                        $AttemptedUserList['time_taken'] = ' ';
                    }

                    $AttemptedUserList['marks'] = ($AttemptedUserList['completed_on'] === "In progress" ? ' ' : $AttemptUser['obtained_mark']);
                    $AttemptedUserList['scores'] = ($AttemptedUserList['completed_on'] === "In progress" ? ' ' : round(($AttemptUser['obtained_mark'] / (($AttemptUser['total_mark'] >= 1) ? $AttemptUser['total_mark'] : 1)) * 100, 1) . '%');
                    $userList[] = $AttemptedUserList;
                }
            } else {
                $userList = [];
            }
        } else {
            $not_attempted_users = array_diff($uid, $attempted_uid);
            if (isset($not_attempted_users) && !empty($not_attempted_users)) {
                foreach ($users as $key => $user) {
                    if (in_array($user['uid'], $not_attempted_users, true)) {
                        $NotAttemptedUser[$key] = [];
                        $NotAttemptedUser[$key]['quiz_name'] = $quiz['quiz_name'];
                        $NotAttemptedUser[$key]['FullName'] = $user['firstname'] . ' ' . $user['lastname'];
                        $NotAttemptedUser[$key]['Username'] = $user['username'];
                        $NotAttemptedUser[$key]['email'] = $user['email'];
                        $NotAttemptedUser[$key]['start_time'] = (!empty($quiz['start_time']) || $quiz['start_time'] != 0) ? Timezone::convertFromUTC('@' . strtotime($quiz['start_time']), Auth::user()->timezone, config('app.date_time_format')) : " ";
                        $NotAttemptedUser[$key]['end_time'] = (!empty($quiz['end_time']) || $quiz['end_time'] != 0) ? Timezone::convertFromUTC('@' . strtotime($quiz['end_time']), Auth::user()->timezone, config('app.date_time_format')) : " ";
                        $userList = $NotAttemptedUser;
                    }
                }
            } else {
                $userList = [];
            }
        }
        $this->csv($filename, $userList, $header, $title);
    }

    /* This below csv function is for the exporting Quiz - channel, question banks */
    public function csv($filename, $export_list, $header, $title)
    {
        $fp = fopen('php://output', 'w');
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename=' . $filename);
        fputcsv($fp, $title);
        fputcsv($fp, $header);
        /* writing data's into csv */
        array_walk_recursive($export_list, function (&$item, $key) {
            (is_string($item)) ? $item = html_entity_decode($item) : '';
        });
        foreach ($export_list as $line) {
            fputcsv($fp, $line);
        }
        exit();
    }

    public function getEditRationale($qid, $qbid)
    {
        try {
            $crumbs = [
                trans('admin/dashboard.dashboard') => 'cp',
                trans('admin/assessment.manage_question_bank') => 'assessment/list-questionbank',
                trans('admin/assessment.view_editRationale') => ''
            ];

            $qtype = (!in_array(Input::get('qtype'), ['mcq'])) ? 'mcq' : Input::get('qtype');
            $question = Question::where('question_id', '=', (int)$qid)->where('status', 'ACTIVE')->firstOrFail();
            $attempt = QuizAttemptData::where('question_id', '=', (int)$qid)->count();

            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = trans('admin/assessment.view_editRationale');
            $this->layout->pageicon = 'fa fa-file-text';
            $this->layout->pagedescription = trans('admin/assessment.view_question');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'assessment')
                ->with('submenu', 'questionbank');
            switch (strtoupper($qtype)) {
                case 'MCQ':
                    $this->layout->content = view('admin.theme.assessment.question.mcq.view_mcq_question')
                        ->with('question', $question)
                        ->with('attempt', $attempt)
                        ->with('qbid', $qbid);
                    break;

                default:
                    abort(404);
                    break;
            }
            $this->layout->footer = view('admin.theme.common.footer');
        } catch (Exception $e) {
            if ($e instanceof ModelNotFoundException) {
                return response()->view('errors.204', ['message' => trans('admin/assessment.question_deleted')]);
            } else {
                abort(404);
            }
        }
    }

    public function postEditRationale(Request $request)
    {
        if (!has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::EDIT_QUESTION)) {
            return parent::getAdminError();
        }

        $question = Question::where('question_id', '=', (int)$request->_q)->firstOrFail();
        $attempt = QuizAttemptData::where('question_id', '=', (int)$request->_q)->count();
        switch (strtoupper($request->_qtype)) {
            case 'MCQ':
                $answers = [];
                if (is_array($question->answers) && !empty($question->answers)) {
                    for ($i = 0; $i < count($question->answers); $i++) {
                        if (isset($question->answers[$i]['answer']) &&
                            (trim($question->answers[$i]['answer']) != '' &&
                                $question->answers[$i]['answer'] != null)
                        ) {
                            $ans = [];
                            $ans_chk_dupe[] = $ans['answer'] = trim($question->answers[$i]['answer']);
                            $ans['correct_answer'] = $question->answers[$i]['correct_answer'];
                            $ans["answer_media"] = (isset($question->question_dam_media_answer[$i])) ? $question->question_dam_media_answer[$i] : [];
                            $ans['rationale'] = trim($request->rationale[$i]);
                            $ans["rationale_media"] = (isset($request->question_dam_media_rationale[$i])) ? $request->question_dam_media_rationale[$i] : [];
                            $answers[] = $ans;
                            if ($ans['correct_answer'] == true) {
                                $answer_check = true;
                            }
                        }
                    }
                }
                $qdata = [
                    'answers' => $answers,
                    'updated_by' => Auth::user()->username,
                    'updated_at' => time()
                ];
                break;

            default:
                abort(404);
                break;
        }

        // Redirect check
        if (Input::has('return')) {
            $return = urldecode(Input::get('return'));
        } else {
            $return = 'cp/assessment/questionbank-questions/' . $question->question_bank;
        }

        if (Question::where('question_id', '=', (int)$question->question_id)
            ->update($qdata)
        ) {
            QuizAttemptData::where('question_id', $question->question_id)->update($qdata);
        }
        return redirect($return)
            ->with('success', trans('admin/assessment.rationale_update_success'));
    }

    public function replaceText($string)
    {
        return trim(preg_replace('#<script(.*?)>(.*?)</script>#is', '', $string));
    }
}
