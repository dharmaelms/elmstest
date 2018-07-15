<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Assignment\AssignmentPermission;
use App\Enums\Assignment\SubmissionType as ST;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\Program\ChannelPermission;
use App\Enums\Program\ElementType;
use App\Enums\RolesAndPermissions\Contexts;
use App\Enums\RolesAndPermissions\PermissionType;
use App\Events\Elastic\Assignment\AssignmentAssigned;
use App\Events\Elastic\Items\ItemsAdded;
use App\Http\Controllers\AdminBaseController;
use App\Model\Common;
use App\Model\Program;
use App\Services\Assignment\IAssignmentAttemptService;
use App\Services\Assignment\IAssignmentService;
use App\Services\Post\IPostService;
use App\Services\Program\IProgramService;
use App\Services\User\IUserService;
use App\Services\UserGroup\IUserGroupService;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Input;
use Log;
use Redirect;
use App\Libraries\Timezone;
use URL;
use Validator;
use ZipArchive;

/**
 * Class AssignmentController
 * @package App\Http\Controllers\Admin
 */
class AssignmentController extends AdminBaseController
{
    /**
     * @var IAssignmentService
     */
    private $assignment_service;

    /**
     * @var IUserService
     */
    private $user_service;

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
     * @var IAssignmentService
     */
    private $assignment_attempt_service;
    /**
     * @var string
     */
    protected $layout = 'admin.theme.layout.master_layout';

    /**
     * AssignmentController constructor.
     * @param Request $request
     * @param IAssignmentService $assignment_service
     * @param IUserService $user_service
     * @param IUserGroupService $usergroup_service
     * @param IProgramService $program_service
     * @param IPostService $post_service
     * @param IAssignmentAttemptService $assignment_attempt_service
     */
    public function __construct(
        Request $request,
        IAssignmentService $assignment_service,
        IUserService $user_service,
        IUserGroupService $usergroup_service,
        IProgramService $program_service,
        IPostService $post_service,
        IAssignmentAttemptService $assignment_attempt_service
    ) {
        parent::__construct();
        $this->assignment_service = $assignment_service;
        $this->user_service = $user_service;
        $this->usergroup_service = $usergroup_service;
        $this->program_service = $program_service;
        $this->post_service = $post_service;
        $this->assignment_attempt_service = $assignment_attempt_service;
    }

    /**
     *
     */
    public function getIndex()
    {
        $permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ASSIGNMENT,
            PermissionType::ADMIN,
            AssignmentPermission::LIST_ASSIGNMENT,
            null,
            null,
            true
        );
        $has_list_assignment_permission = get_permission_flag($permission_data_with_flag);
        if (!$has_list_assignment_permission) {
            return parent::getAdminError();
        }
        
        $list_assignment_permission_data = get_permission_data($permission_data_with_flag);
        $filter_params = has_system_level_access($list_assignment_permission_data)?
                [] : ["in_ids" => get_instance_ids($list_assignment_permission_data, Contexts::PROGRAM)];
         $feeds = $this->program_service->getAllProgramByIDOrSlug('content_feed', '', $filter_params);
                 
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assignment.manage_assignment') => ''
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = 'Manage Assignment';
        $this->layout->pageicon = 'fa fa-file-text';
        $this->layout->pagedescription = 'List of Assignment';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'assignment');
        $this->layout->content = view('admin.theme.assignment.list_assignment')
                                ->with('feeds', $feeds);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     *
     */
    public function getAddAssignment()
    {
        if (!has_admin_permission(ModuleEnum::ASSIGNMENT, AssignmentPermission::ADD_ASSIGNMENT)) {
            return parent::getAdminError();
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assignment.manage_assignment') => 'assignment/list-assignment',
            trans('admin/assignment.add') => ''
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = 'Add Assignment';
        $this->layout->pageicon = 'fa fa-file-text';
        $this->layout->pagedescription = 'Add Assignment';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'assignment');
        $this->layout->content = view('admin.theme.assignment.add_assignment');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * @param $assignment_id
     */
    public function getEditAssignment($assignment_id)
    {
        $edit_assignment_permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ASSIGNMENT,
            PermissionType::ADMIN,
            AssignmentPermission::EDIT_ASSIGNMENT,
            null,
            null,
            true
        );
        $edit_assignment_permission_data = get_permission_data($edit_assignment_permission_data_with_flag);
        if (!is_element_accessible($edit_assignment_permission_data, ElementType::ASSIGNMENT, $assignment_id)) {
            return parent::getAdminError();
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assignment.manage_assignment') => 'assignment/list-assignment',
            trans('admin/assignment.edit') => ''
        ];
        $assignment = $this->assignment_service->getAssignmentByIds((int)$assignment_id)->first();
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = 'Edit Assignment';
        $this->layout->pageicon = 'fa fa-file-text';
        $this->layout->pagedescription = 'Edit Assignment';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'assignment');
        $this->layout->content = view('admin.theme.assignment.edit_assignment')
            ->with('assignment', $assignment);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function upsertAssignment(Request $request)
    {
        if (is_null($request->_a)) {
            if (!has_admin_permission(ModuleEnum::ASSIGNMENT, AssignmentPermission::ADD_ASSIGNMENT)) {
                return parent::getAdminError();
            }
        } else {
            $permission_data_with_flag = $this->roleService->hasPermission(
                Auth::user()->uid,
                ModuleEnum::ASSIGNMENT,
                PermissionType::ADMIN,
                AssignmentPermission::EDIT_ASSIGNMENT,
                null,
                null,
                true
            );
            if (!is_element_accessible(get_permission_data($permission_data_with_flag), ElementType::ASSIGNMENT, $request->_a)) {
                return parent::getAdminError();
            }
        }

        $validation = $this->getValidations($request->input());
        if ($validation->fails()) {
            if (is_null($request->_a)) {
                return Redirect::route("get-add-assignment")
                    ->withInput()
                    ->withErrors($validation);
            } else {
                return Redirect::route('get-edit-assignment', ["assignment_id" => (int)$request->_a])
                    ->withInput()
                    ->withErrors($validation);
            }
        } else {
            $date_validation = $this->getDateValidations($request->input(), $validation);
            if ($date_validation['error']) {
                if (is_null($request->_a)) {
                    return Redirect::route("get-add-assignment")
                        ->withInput()
                        ->withErrors($validation);
                } else {
                    return Redirect::route('get-edit-assignment', ["assignment_id" => (int)$request->_a])
                        ->withInput()
                        ->withErrors($validation);
                }
            }
            $start_time = $date_validation['start_time'];
            $end_time = $date_validation['end_time'];
            $cutoff_time = $date_validation['cutoff_time'];
            $data = [];
            $data['name'] = $request->assignment_title;
            $data['description'] = $request->assignment_description;
            $data['start_time'] = (int)$start_time;
            $data['end_time'] = (int)$end_time;
            $data['cutoff_time'] = (int)$cutoff_time;
            $data['status'] = 'ACTIVE';
            $data['submission_type'] = $request->submission_type;
            $data['template_file_id'] = !empty($request->template_file_id) ? $request->template_file_id : '';
            $data['template_file_name'] = !empty($request->template_file_name) ? $request->template_file_name : '';
            $data['max_no_file_allowed'] =  ($request->submission_type == "file_submission") ? (int)$request->max_files : "";
            $data['grade'] = empty($request->grade) ? 100 : (int)$request->grade;
            $data['grade_cutoff'] = (int)$request->grade_cutoff;
            $data['resubmit'] = config('app.assignment_resubmission');
            if (is_null($request->_a)) {
                $data['created_at'] = time();
                $data['created_by'] = Auth::user()->username;
                $this->assignment_service->insertAssignment($data);
                $success_msg = "Assignment added successfully";
            } else {
                $data['updated_at'] = time();
                $data['updated_by'] = Auth::user()->username;
                $this->assignment_service->updateAssignment($request->_a, $data);
                $success_msg = "Assignment updated successfully";
            }
            return redirect('cp/assignment/list-assignment')
                ->with('success', $success_msg);
        }
    }

    /**
     * @param $input
     * @return \Illuminate\Validation\Validator
     */
    public function getValidations($input)
    {
        $rules = [
            'assignment_title' => 'required|min:1|max:512',
            'start_date' => 'required',
            'end_date' => 'required',
            'cutoff_date' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'cutoff_time' => 'required',
            'max_files' => 'required_if:submission_type,file_submission',
            'grade' => 'Between:1,100|required_with:grade_cutoff',
            'grade_cutoff' => 'compare_grade_cutoff'
        ];
        $messages = [
            'assignment_title.required' => trans('admin/assignment.assignment_title_required'),
            'start_date.required' => trans('admin/assignment.start_date_required'),
            'end_date.required' => trans('admin/assignment.end_date_required'),
            'cutoff_date.required' => trans('admin/assignment.cutoff_date_required'),
            'start_time.required' => trans('admin/assignment.start_time_required'),
            'end_time.required' => trans('admin/assignment.end_time_required'),
            'cutoff_time.required' =>  trans('admin/assignment.cutoff_time_required'),
            'max_files.required_if' =>  trans('admin/assignment.max_files_required'),
            'grade.required_with' => trans('admin/assignment.grade_required'),
            'grade.Between' => trans('admin/assignment.grade_required'),
            'grade_cutoff.compare_grade_cutoff' => trans('admin/assignment.compare_grade_cutoff')
        ];
        Validator::extendImplicit('compare_grade_cutoff', function ($attribute, $value, $parameters, \Illuminate\Validation\Validator $validator) {
            $input = $validator->getData();
            if (!empty($input['grade_cutoff'])) {
                if ($input['grade'] <= $input['grade_cutoff']) {
                    return false;
                }
            }
            return true;
        });
        return Validator::make($input, $rules, $messages);
    }

    /**
     * @param $input
     * @param \Illuminate\Validation\Validator $validation
     * @return array
     */
    public function getDateValidations($input, $validation)
    {
        $error = false;
        $start_time = $end_time = $cutoff_time = 0;
        // Assignment start time
        $start_time = Timezone::convertToUTC($input['start_date'], Auth::user()->timezone, 'U');
        $temp = explode(':', trim($input['start_time']));
        $start_time += (($temp[0] * 60) + $temp[1]) * 60;
        // Assignment end time
        $end_time = Timezone::convertToUTC($input['end_date'], Auth::user()->timezone, 'U');
        $temp = explode(':', trim($input['end_time']));
        $end_time += (($temp[0] * 60) + $temp[1]) * 60;
        // Assignment cutoff time
        $cutoff_time = Timezone::convertToUTC($input['cutoff_date'], Auth::user()->timezone, 'U');
        $temp = explode(':', trim($input['cutoff_time']));
        $cutoff_time += (($temp[0] * 60) + $temp[1]) * 60;
        if ($start_time >= $end_time) {
            $error = true;
            $validation->getMessageBag()->add('end_date', trans('admin/assignment.end_date_higher'));
        }
        if ($end_time >= $cutoff_time) {
            $error = true;
            $validation->getMessageBag()->add('cutoff_date', trans('admin/assignment.cutoff_date_higher'));
        }
        return [
            'validation' => $validation,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'cutoff_time' => $cutoff_time,
            'error' => $error
        ];
    }

    /**
     * @param $assignment_id
     */
    public function reviewAssignment($assignment_id, $user_id)
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assignment.manage_assignment') => 'assignment/list-assignment',
            trans('admin/assignment.review_breadcrumb') => ''
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $assignment_id = (int)$assignment_id;
        $filter_params = [
            "id" => [$assignment_id]
        ];
        $assignment = $this->assignment_service->getAssignments($filter_params)->first();

        $params = [
            "assignment_id" => (int)$assignment_id,
            "user_id" => (int) $user_id
        ];
        $assignment_attempt = $this->assignment_attempt_service->getAllAttempts($params)->first();
        $this->layout->pagetitle = 'Review Assignment';
        $this->layout->pageicon = 'fa fa-file-text';
        $this->layout->pagedescription = 'Review Assignment';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'assignment');
        $this->layout->content = view('admin.theme.assignment.review_assignment')
                                    ->with('assignment', $assignment)
                                    ->with('assignment_attempt', $assignment_attempt)
                                    ->with('user_id', $user_id);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     *
     */
    public function getGradeAssignment($assignment_id, $submission_type)
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assignment.manage_assignment') => 'assignment/list-assignment',
            trans('admin/assignment.grade_listing') => ''
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $excluded_user_id = array_unique(array_filter($this->user_service->getSuperAdminIds()));
        $assignment_name = $this->assignment_service->getAssignmentFieldById($assignment_id, ['name'])->first()->name;
        $yet_to_grade_count = $this->assignment_attempt_service->getSummaryCount($assignment_id, "=", ST::YET_TO_REVIEW, $excluded_user_id);
        $submitted_users = $this->assignment_attempt_service->getSummaryCount($assignment_id, "!=", ST::SAVE_AS_DRAFT, $excluded_user_id);
        $total_assigned_users = $this->assignment_service->getTotalAssignedUsers($assignment_id);
        $not_submitted_users = $this->assignment_attempt_service->getNotSubmittedUsersCount($assignment_id, $total_assigned_users, $excluded_user_id);
        $this->layout->pagetitle = 'Manage Grade';
        $this->layout->pageicon = 'fa fa-file-text';
        $this->layout->pagedescription = 'Grading Assignment';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'assignment');
        $this->layout->content = view('admin.theme.assignment.grade_assignment')
            ->with('assignment_id', $assignment_id)
            ->with('name', $assignment_name)
            ->with('yet_to_grade_count', $yet_to_grade_count)
            ->with('submitted_users', $submitted_users)
            ->with('not_submitted_users', $not_submitted_users)
            ->with('total_assigned_users', $total_assigned_users)
            ->with('submission_type', $submission_type);
        $this->layout->footer = view('admin.theme.common.footer');
    }

        /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGradeListAjax(Request $request)
    {
        $filters = [];
        $list_assignment_permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ASSIGNMENT,
            PermissionType::ADMIN,
            AssignmentPermission::LIST_ASSIGNMENT,
            null,
            null,
            true
        );
        $list_assignment_permission_data = get_permission_data($list_assignment_permission_data_with_flag);
        if (!has_system_level_access($list_assignment_permission_data)) {
                $filters["id"] = get_user_accessible_elements(
                    $list_assignment_permission_data,
                    ElementType::ASSIGNMENT
                );
        }

        $has_list_assignment_permission = get_permission_flag($list_assignment_permission_data_with_flag);
        if (!$has_list_assignment_permission) {
            return response()->json(
                [
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ]
            );
        }

        $grading_user_list = collect([]);
        $excluded_user_id = array_unique(array_filter($this->user_service->getSuperAdminIds()));
        $filters += [
            "search" => isset($request->search['value']) ? $request->search['value'] : '',
            "submission_type" => $request->submission_type,
            "assignment_id" => (int)$request->assignment_id,
            "excluded_user_id" => $excluded_user_id,
        ];
        $all_users_grading_count = $this->assignment_attempt_service->getUserGradeCount($filters);
        if ($request->has('start') && $request->has('length')) {
            $filters += [
                'start' => $request->start,
                'limit' => $request->length,
            ];
            $order_by = $request->order;
            $orderByArray = ['name' => 'desc'];
            if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
                if ($order_by[0]['column'] == '4') {
                    $orderByArray = ['submitted_at.0' => $order_by[0]['dir']];
                }
                $user_grade = $this->assignment_attempt_service->getAllAttempts($filters, $orderByArray);
                $uids = $user_grade->lists('user_id')->toArray();
                $user_details = $this->user_service->getUserColumnsbyId($uids, ['uid', 'username', 'email']);

                $user_grading_list = $user_grade->transform(function ($each_data) use (&$filters, &$order_by, &$user_details, &$request) {
                    $up_text_disabled = $title = '';
                    $user_data = $user_details->whereIn('uid', [$each_data->user_id])->first();
                    if (!is_null($user_data)) {
                        $submission_status = ucfirst(strtolower(str_replace('_', ' ', $each_data->submission_status)));
                        
                        if ($request->submission_type != ST::REVIEWED) {
                            $each_data->grade = "NA";
                        }
                        $grade = '<a class="btn btn-circle" href="'. URL::route('review-assignment', ['assignment_id' => $each_data->assignment_id, 'user_id' => $each_data->user_id]) .'">' .$each_data->grade. '</a>';
                        $submitted_at = array_get($each_data, 'submitted_at.0');

                        $uploaded_text = isset($each_data->uploaded_text) ? $each_data->uploaded_text : '';
                        if (empty($uploaded_text)) {
                            $text_href = "#";
                            $text_title = trans('admin/assignment.no_text_to_show');
                        } else {
                            $text_href = URL::route('online-text', ['assignment_id' => $each_data->assignment_id, 'user_id' => $each_data->user_id]);
                            $text_title = trans('admin/assignment.online_text');
                        }
                        $online_text = '<a class="btn btn-circle show-tooltip fa fa-file-text-o showonlinetext" href="'. $text_href .'" title="' . $text_title . '" ></a>';

                        if (empty($each_data->uploaded_file)) {
                            $href = "#";
                            $file_title = trans('admin/assignment.no_files_to_download');
                        } else {
                            $href = URL::Route('uploaded-files', ['assignment_id' => $each_data->assignment_id, 'user_id' => $each_data->user_id]);
                            $file_title = trans('admin/assignment.download');
                        }
                        $file = '<a class="btn btn-circle show-tooltip fa fa-download" style="cursor: pointer;" href="'.$href.'" title="' . $file_title . '" ></a>';
                        
                        $comments = isset($each_data->review_comments) ? $each_data->review_comments : '';
                        if (empty($comments)) {
                            $c_href = "#";
                            $c_title = trans('admin/assignment.no_comments');
                        } else {
                            $c_href = URL::route('review-comments', ['assignment_id' => $each_data->assignment_id, 'user_id' => $each_data->user_id]);
                            $c_title = trans('admin/assignment.comments');
                        }
                        $review_comments =  '<a class="btn btn-circle show-tooltip fa fa-comments reviewcomments" href="'. $c_href .'" title ="'. $c_title .'" ></a>';

                        /* Action buttons */
                        /* REVIEW */
                        $review = '<a class="btn btn-circle show-tooltip reviewassignment" title="' . trans('admin/assignment.review') . '" href="' . URL::route('review-assignment', ['assignment_id' => $each_data->assignment_id, 'user_id' => $each_data->user_id]) . '" ><i class="fa fa-edit"></i></a>';
                        /* Action buttons */
                        
                        /* Data construction for final array*/
                        $each_user_grade_list = [
                            'username' => $user_data->username,
                            'email' => $user_data->email,
                            'status' => $submission_status,
                            'grade' => $grade,
                            'submitted_date' => Timezone::convertFromUTC('@' . $submitted_at, Auth::user()->timezone, config('app.date_format')),
                            'online_text' => $online_text,
                            'file' => $file,
                            'comments' => $review_comments,
                            'actions' => $review, //$report,//. $export,// Since we its completed
                        ];
                        return $each_user_grade_list;
                    } else {
                        return;
                    }
                })->filter();
            }
        }
        return response()->json([
            'recordsTotal' => $user_grading_list->count(),
            'recordsFiltered' => $all_users_grading_count,
            'data' => $user_grading_list->values()
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListAssignmentAjax(Request $request)
    {
        $filters = [];
        $list_assignment_permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ASSIGNMENT,
            PermissionType::ADMIN,
            AssignmentPermission::LIST_ASSIGNMENT,
            null,
            null,
            true
        );
        $list_assignment_permission_data = get_permission_data($list_assignment_permission_data_with_flag);
        if (!has_system_level_access($list_assignment_permission_data)) {
                $filters["id"] = get_user_accessible_elements(
                    $list_assignment_permission_data,
                    ElementType::ASSIGNMENT
                );
        }

        $has_list_assignment_permission = get_permission_flag($list_assignment_permission_data_with_flag);
        if (!$has_list_assignment_permission) {
            return response()->json(
                [
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ]
            );
        }

        $assignment_list = collect([]);
        $filters += [
            "search" => isset($request->search['value']) ? $request->search['value'] : '',
            "status" => 'ACTIVE',
        ];
        $all_assignment_count = $this->assignment_service->getAssignmentCount($filters);
        if ($request->has('start') && $request->has('length')) {
            $filters += [
                'start' => $request->start,
                'limit' => $request->length,
            ];
            $order_by = $request->order;
            $orderByArray = ['name' => 'desc'];
            if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
                if ($order_by[0]['column'] == '1') {
                    $orderByArray = ['name' => $order_by[0]['dir']];
                }
                if ($order_by[0]['column'] == '7') {
                    $orderByArray = ['created_at' => $order_by[0]['dir']];
                }
                if ($order_by[0]['column'] == '8') {
                    $orderByArray = ['end_time' => $order_by[0]['dir']];
                }
                if ($order_by[0]['column'] == '9') {
                    $orderByArray = ['cutoff_time' => $order_by[0]['dir']];
                }
                $assignments = $this->assignment_service->getAssignments($filters, $orderByArray);
                $excluded_user_id = $this->user_service->getSuperAdminIds();
                $assignment_list = $assignments->transform(function ($assignment) use (&$filters, &$order_by, &$excluded_user_id) {
                    $assignment_title = '<div>'.$assignment->name.'</div>';
                    
                    $submitted_users = $this->assignment_attempt_service->getSummaryCount($assignment->id, "!=", ST::SAVE_AS_DRAFT, $excluded_user_id);
                    $needs_grading_count = $this->assignment_attempt_service->getSummaryCount($assignment->id, "!=", ST::REVIEWED, $excluded_user_id);

                    $needs_grading = '<a href="' . URL::route("get-grade-assignment", ['assignment_id' => $assignment->id, 'submission_type' => ST::YET_TO_REVIEW ]) . '" class="badge ' . (($needs_grading_count > 0) ? "badge-success" : "badge-grey") . '" data-key="' . $assignment->id . '" data-info="user" data-text="Manage Users for ' . htmlentities($assignment->name, ENT_QUOTES) . '" data-json="' . json_encode($needs_grading_count) . '">' . $needs_grading_count . '</a>';

                    $assignment_post_data = (is_null($assignment->post_id)) ? [] : [$assignment->post_id];
                    $post_count = 0;
                    if (has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_POST)) {
                        $post_title = '';
                        $channel_name = '';
                        if (!empty($assignment->post_id)) {
                            try {
                                $post_id = $assignment->post_id;
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
                            $post_rel ="<a href='#' style='cursor:not-allowed' class='badge badge-success' data-key='" . $assignment->id . "' data-info='feed' data-text='Manage " . trans('admin/assignment.posts') . " for " . htmlentities($assignment->name, ENT_QUOTES) . "' data-json='" . json_encode($assignment_post_data) . "' title = '".htmlentities($post_title, ENT_QUOTES)."' >" . $post_count."</a> || <a href='". URL::route('unassign-post', ['assignment_id' => $assignment->id, 'from' => 'unassign']) ."' class = 'assignment-post-unassign' data-key='" . $assignment->id . "' data-postname='" . $post_title . "' data-channelname='" . $channel_name . "'>Un-assign</a>";
                        } else {
                            $post_rel = "<a href='' class='assignment-post-assign badge " . (($post_count > 0) ? "badge-success" : "badge-grey") . "' data-key='" . $assignment->id . "' data-info='feed' data-text='Manage " . trans('admin/assignment.posts') . " for " . htmlentities($assignment->name, ENT_QUOTES) . "' data-json='" . json_encode($assignment_post_data) . "' title = '".htmlentities($post_title, ENT_QUOTES)."' >" . $post_count."</a>";
                        }
                    } else {
                        $post_rel = "<a style='cursor:not-allowed' href='#' title=\"" . trans('admin/assignment.no_per_to_assign_channels') . "\"  class='badge show-tooltip " . ($post_count > 0 ? 'badge-success' : 'badge-grey') . "'>" . $post_count . "</a>";
                    }

                    if (has_admin_permission(ModuleEnum::ASSIGNMENT, AssignmentPermission::ASSIGNMENT_ASSIGN_USER)) {
                        $users = '<a href="' . URL::to("/cp/usergroupmanagement?filter=ACTIVE&view=iframe&from=assignment&relid=" . $assignment->id) . '" class="assignment-assign badge ' . ((count($assignment->users) > 0) ? "badge-success" : "badge-grey") . '" data-key="' . $assignment->id . '" data-info="user" data-text="Manage Users for ' . htmlentities($assignment->name, ENT_QUOTES) . '" data-json="' . json_encode($assignment->users) . '">' . count($assignment->users) . '</a>';
                    } else {
                        $users = "<a style='cursor:not-allowed' href='#' title=\"" . trans('admin/assignment.no_per_to_assign_users') . "\"  class='badge show-tooltip " . ((count($assignment->users) > 0) ? 'badge-success' : 'badge-grey') . "'>" . count($assignment->users) . "</a>";
                    }
                    if (has_admin_permission(ModuleEnum::ASSIGNMENT, AssignmentPermission::ASSIGNMENT_ASSIGN_USER_GROUP)) {
                        $usergroups = '<a href="' . URL::to("/cp/usergroupmanagement/user-groups?filter=ACTIVE&view=iframe&from=assignment&relid=" . $assignment->id) . '" class="assignment-assign badge ' . ((count($assignment->usergroups) > 0) ? "badge-success" : "badge-grey") . '" data-key="' . $assignment->id . '" data-info="usergroup" data-text="Manage User Groups for ' . htmlentities($assignment->name, ENT_QUOTES) . '" data-json="' . json_encode($assignment->usergroups) . '">' . count($assignment->usergroups) . '</a>';
                    } else {
                        $usergroups = "<a style='cursor:not-allowed' href='#' title=\"" . trans('admin/assignment.no_per_to_assign_usergroups') . "\"  class='badge show-tooltip " . ((count($assignment->usergroups) > 0) ? 'badge-success' : 'badge-grey') . "'>" . count($assignment->usersgroups) . "</a>";
                    }

                /* Action buttons */
                        /* EDIT */
                    if (has_admin_permission(ModuleEnum::ASSIGNMENT, AssignmentPermission::EDIT_ASSIGNMENT)) {
                        $edit = '<a class="btn btn-circle show-tooltip editassignment" title="' . trans('admin/assignment.edit') . '" href="' . URL::route('get-edit-assignment', ['assignment_id' => $assignment->id]) . '?start=' . $filters['start'] . '&limit=' . $filters['limit'] . '&search=' . $filters['search'] . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-edit"></i></a>';
                    } else {
                        $edit = '<a style="cursor:not-allowed" class="btn btn-circle show-tooltip ajax" title="' . trans("admin/assignment.no_perm_to_edit") . '"><i class="fa fa-edit"></i></a>';
                    }
                    /* DELETE*/
                    if (has_admin_permission(ModuleEnum::ASSIGNMENT, AssignmentPermission::DELETE_ASSIGNMENT)) {
                        if ((isset($assignment->users) && !empty($assignment->users)) ||
                            (isset($assignment->usergroups) && !empty($assignment->usergroups)) ||
                            !is_null($assignment->post_id)) {
                            $delete = '<a class="btn btn-circle show-tooltip ajax" title="' . trans("admin/assignment.assignment_in_use") . '"><i class="fa fa-trash-o"></i></a>';
                        } else {
                            $delete = '<a class="btn btn-circle show-tooltip deleteassignment" title="' . trans('admin/assignment.delete') . '" href="' . URL::route('get-delete-assignment', ['assignment_id' => $assignment->id]) . '?start=' . $filters['start'] . '&limit=' . $filters['limit'] . '&search=' . $filters['search'] . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-trash-o"></i></a>';
                        }
                    } else {
                        $delete = '<a style="cursor:not-allowed" class="btn btn-circle show-tooltip ajax" title="' . trans("admin/assignment.no_perm_to_delete") . '"><i class="fa fa-trash-o"></i></a>';
                    }
                    /* Action buttons */

                    /* Data construction for final array*/
                    $each_assignment = [
                        'assignment_title' => $assignment_title,
                        'submitted_users' => $submitted_users,
                        'needs_grading' => $needs_grading,
                        'posts' => $post_rel,
                        'users' => $users,
                        'usergroups' => $usergroups,
                        'start_time' => Timezone::convertFromUTC('@' . $assignment->start_time, Auth::user()->timezone, config('app.date_format')),
                        'end_time' => Timezone::convertFromUTC('@' . $assignment->end_time, Auth::user()->timezone, config('app.date_format')),
                        'cutoff_time' => Timezone::convertFromUTC('@' . $assignment->cutoff_time, Auth::user()->timezone, config('app.date_format')),
                        'actions' => $edit. $delete, //$report,//. $export,// Since we its completed
                    ];
                    return $each_assignment;
                });
            }
        }
        return response()->json([
            'recordsTotal' => $assignment_list->count(),
            'recordsFiltered' => $all_assignment_count,
            'data' => $assignment_list
        ]);
    }

    /**
     * @param $assignment_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteAssignment($assignment_id)
    {
        if (!is_numeric($assignment_id)) {
            abort(404);
        }
        $delete_assignment_permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ASSIGNMENT,
            PermissionType::ADMIN,
            AssignmentPermission::DELETE_ASSIGNMENT,
            null,
            null,
            true
        );
        $delete_assignment_permission_data = get_permission_data($delete_assignment_permission_data_with_flag);
        if (!is_element_accessible($delete_assignment_permission_data, ElementType::ASSIGNMENT, $assignment_id)) {
            return parent::getAdminError();
        }
        $start = Input::get('start', 0);
        $limit = Input::get('limit', 10);
        $search = Input::get('search', '');
        $order_by = Input::get('order_by', '7 desc');
        $assignment = $this->assignment_service->deleteAssignment($assignment_id);
        // Redirect check
        if (Input::has('return')) {
            $return = urldecode(Input::get('return')) . '?start=' . $start . '&limit=' . $limit . '&search=' . $search . '&order_by=' . $order_by;
        } else {
             $return = 'get-list';
        }
        if ($assignment) {
            return Redirect::route($return)
                ->with('success', trans('admin/assignment.assignment_delete'));
        } else {
            return Redirect::route($return)
                ->with('error', trans('admin/assignment.problem_while_deleting_question'));
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function postAssignAssignments()
    {
        $action = Input::get('from');
        $key = Input::get('key');
        $msg = null;
        $assignment = $this->assignment_service->getAssignmentByIds((int)$key)->first();
        if (empty($assignment)) {
            return response()->json(['flag' => 'error', 'message' => trans("admin/assignment.invalid_assignment")]);
        }
        $ids = (!empty(Input::get('ids')) ? explode(',', Input::get('ids')) : []);
        if (Input::get('empty') != true) {
            if (empty($ids) || !is_array($ids)) {
                return response()->json(['flag' => 'error', 'message' => trans("admin/assignment.no_checkbox_selected")]);
            }
        }
        $ids = array_map('intval', $ids);
        switch ($action) {
            case 'user':
                $assign_user_assignment_permission_data_with_flag = $this->roleService->hasPermission(
                    Auth::user()->uid,
                    ModuleEnum::ASSIGNMENT,
                    PermissionType::ADMIN,
                    AssignmentPermission::ASSIGNMENT_ASSIGN_USER,
                    null,
                    null,
                    true
                );

                $assign_user_assignment_permission_data = get_permission_data($assign_user_assignment_permission_data_with_flag);
                $has_assign_assignment_to_user_permission = is_element_accessible(
                    $assign_user_assignment_permission_data,
                    ElementType::ASSIGNMENT,
                    $key
                );
                if ($has_assign_assignment_to_user_permission) {
                    $has_assign_assignment_to_user_permission = are_users_exist_in_context(
                        $assign_user_assignment_permission_data,
                        $ids
                    );
                }
                if (!$has_assign_assignment_to_user_permission) {
                    return response()->json([
                        'flag' => 'error',
                        'message' => trans("admin/assignment.no_per_to_assign_users")
                    ]);
                }
                $arrname = 'users';
                $assignment_relation = isset($assignment->$arrname) ? $assignment->$arrname : [];
                if (!is_admin_role(Auth::user()->role)) {
                    /* If the user is a ProgramAdmin/ContentAuthor */
                    /* $manageable_ids = Uids which belongs to PA/CA users */
                    $manageable_ids = array_values(array_intersect(get_user_ids($assign_user_assignment_permission_data), $assignment_relation));
                    /* $dedupe_ids => Uids which are in the relation and are assigned by Site Admin */
                    $dedupe_ids = array_diff($assignment_relation, $manageable_ids);
                    
                    /* Note: when $dedupe_ids is empty, It means $manageable_ids and $users_relation contains same uids then assigning $manageable_ids to $dedupe_ids */
                    /* Below code is to remove the relations from the user tables */
                    if (empty($dedupe_ids)) {
                        $dedupe_ids = $manageable_ids;
                    }
                } else {
                    /* If the user is Site Admin  */
                    /* $manageable_ids => assignment user rel ie. "users" */
                    $manageable_ids = $assignment_relation;
                    /* $dedupe_ids => assignment user rel ie. "users" */
                    $dedupe_ids = $assignment_relation;
                }
                if (isset($dedupe_ids) && !empty($dedupe_ids)) {
                    $delete = array_diff($manageable_ids, $ids);
                    $add = array_diff($ids, $dedupe_ids);
                    
                    if (!is_admin_role(Auth::user()->role)) {
                        /* $ids => taking the array difference of ( users+selected uids as the input) and $delete */
                        $ids = array_values(array_diff(array_unique(array_merge($assignment_relation, $add)), $delete));
                    }
                } else {
                    $delete = [];
                    $add = $ids;
                }
                foreach ($delete as $value) {
                    $this->user_service->removeUserAssignment($value, ['assignment'], $assignment->id);
                }
                foreach ($add as $value) {
                    $this->user_service->addUserAssignment($value, ['assignment'], $assignment->id);
                }
                $msg = trans('admin/user.user_assigned');
                break;

            case 'usergroup':
                $permission_data_with_flag = $this->roleService->hasPermission(
                    Auth::user()->uid,
                    ModuleEnum::ASSIGNMENT,
                    PermissionType::ADMIN,
                    AssignmentPermission::ASSIGNMENT_ASSIGN_USER_GROUP,
                    null,
                    null,
                    true
                );
                $permission_data = get_permission_data($permission_data_with_flag);
                $has_assign_permission = is_element_accessible($permission_data, ElementType::ASSIGNMENT, $key);
                if ($has_assign_permission) {
                    $has_assign_permission = are_user_groups_exist_in_context(
                        $permission_data,
                        $ids
                    );
                }
                if (!$has_assign_permission) {
                    return response()->json([
                        'flag' => 'error',
                        'message' => trans("admin/assignment.no_per_to_assign_usergroups")
                    ]);
                }
                $arrname = 'usergroups';
                $assignment_relation = isset($assignment->$arrname) ? $assignment->$arrname : [];
                if (!is_admin_role(Auth::user()->role)) {
                    /* If the user is a ProgramAdmin/ContentAuthor */
                    /* $manageable_ids = Uids which belongs to PA/CA users */
                    $manageable_ids = array_values(array_intersect(get_user_group_ids($permission_data), $assignment_relation));
                    /* $dedupe_ids => Uids which are in the relation and are assigned by Site Admin */
                    $dedupe_ids = array_diff($assignment_relation, $manageable_ids);
                    
                    /* Note: when $dedupe_ids is empty, It means $manageable_ids and $assignment_relation contains same uids then assigning $manageable_ids to $dedupe_ids */
                    /* Below code is to remove the relations from the user tables */
                    if (empty($dedupe_ids)) {
                        $dedupe_ids = $manageable_ids;
                    }
                } else {
                    /* If the user is Site Admin  */
                    /* $manageable_ids => assignment usergroup rel ie. "usergroups" */
                    $manageable_ids = $assignment_relation;
                    /* $dedupe_ids => assignment usergroup rel ie. "usergroups" */
                    $dedupe_ids = $assignment_relation;
                }
                if (isset($dedupe_ids) && !empty($dedupe_ids)) {
                    $delete = array_diff($manageable_ids, $ids);
                    $add = array_diff($ids, $dedupe_ids);
                    if (!is_admin_role(Auth::user()->role)) {
                        /* $ids => taking the array difference of ( usergroups+selected uids as the input) and $delete */
                        $ids = array_values(array_diff(array_unique(array_merge($assignment_relation, $add)), $delete));
                    }
                } else {
                    $delete = [];
                    $add = $ids;
                }
                foreach ($delete as $value) {
                    $this->usergroup_service->removeUserGroupAssignment($value, ['assignment'], $assignment->id);
                }
                foreach ($add as $value) {
                    $this->usergroup_service->addUserGroupAssignment($value, ['assignment'], $assignment->id);
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
                    $this->getUnassignPost($assignment->id);
                    $packet_col = $this->post_service->getPostByID($ids, 'packet_id')->first();
                    $index_i = 1;
                    $temp = [];
                    $insert = true;
                    if (!empty($packet_col->elements)) {
                        foreach ($packet_col->elements as $element) {
                            $index_i++;
                            if (in_array('assignment', $element) && $element['type'] == 'assignment' && $element['id'] == (int)$assignment->id) {
                                $insert = false;
                            }
                        }
                    }
                    if ($insert == true) {
                        $this->post_service->pushRelations($ids, 'assignment_ids', [$assignment->id]);
                        $element_ary['type'] = 'assignment';
                        $element_ary['order'] = $index_i;
                        $element_ary['id'] = (int)$assignment->id;
                        $element_ary['name'] = $assignment->name;
                        $temp[] = $element_ary;
                    }
                    $this->post_service->pushRelations($ids, 'elements', $temp);
                        
                    if (config('elastic.service')) {
                        event(new ItemsAdded($ids));
                    }
                    $msg = trans('admin/assignment.post_assigned_success');
                } else {
                    return response()->json(['flag' => 'error', 'message' => trans("admin/assignment.invalid_feed")]);
                }
                break;

            default:
                return response()->json(['flag' => 'error', 'message' => trans("admin/assignment.wrong_action_param")]);
                break;
        }

        //Un-setting the relation before updating
        $this->assignment_service->UnsetAssignmentRelations($assignment->id, $arrname);
        if (!empty($ids)) {
            $updated = $this->assignment_service->updateAssignmentRelations($assignment->id, $arrname, $ids);
            if ($updated) {
                if ($action == 'user' || $action == 'usergroup') {
                    if (config('elastic.service')) {
                        event(new AssignmentAssigned($assignment->id));
                    }
                }
                return response()->json(['flag' => 'success', 'message' => $msg]);
            } else {
                return response()->json(['flag' => 'error']);
            }
        } else {
            if ($action == 'user' || $action == 'usergroup') {
                if (config('elastic.service')) {
                    event(new AssignmentAssigned($assignment->id));
                }
            }
            return response()->json(['flag' => 'success', 'message' => $msg]);
        }
    }

    /**
     * @param $assignment_id
     * @param $from
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function getUnassignPost($assignment_id, $from = null)
    {
        $flag = "failed";
        $msg = "";
        try {
            $packets = $this->post_service->getPostByID($assignment_id, 'assignment_ids')->get(['packet_id', 'elements', 'assignment_ids'])->first();
            if (!is_null($packets)) {
                $elements = $packets->elements;
                $new_elements = [];
                foreach ($elements as $element) {
                    if ($element['type'] != 'assignment' || ( $element['type'] == 'assignment' && $element['id'] != $assignment_id)) {
                        $new_elements[] = $element;
                    }
                }
                $this->post_service->pullRelations($packets->packet_id, 'assignment_ids', $assignment_id);
                $this->post_service->updateRelationsByID($packets->packet_id, 'elements', $new_elements);
                $this->assignment_service->unassignPost($assignment_id, 'post_id');
                
                $msg = trans("admin/assignment.successfully_un_assigned");
                $flag = 'success';
            } else {
                $msg = trans("admin/assignment.not_asso_to_any_posts");
                $flag = 'failed';
            }
        } catch (Exception $e) {
            $msg = $e->getMessage();
            Log::error("Assignment un assign : ".$e->getMessage());
        }
        if (!empty($from)) {
              return Redirect::route('get-list')
                    ->with('success', trans('admin/assignment.unasssign_post'));
        } else {
            return response()->json(['flag' => $flag, 'message' => $msg]);
        }
    }

    /**
     * @param Request $request
     * @param $assignment_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postReviewAssignment(Request $request, $assignment_id, $user_id)
    {
        try {
            $assignment_id = (int)$assignment_id;
            $rules = [
                'grade' => 'Required',
            ];
            $messages = [
                'grade.required' => trans('admin/assignment.grade_require')
            ];
            $validation = Validator::make(Input::all(), $rules, $messages);
            if ($validation->fails()) {
                return Redirect::route('review-assignment', ['assignment_id' => $assignment_id, 'user_id' => $user_id]);
            } else {
                $assignment = $this->assignment_service->getAssignments(["id" => [$assignment_id]])->first();
                $grade_cutoff = (int)$assignment->grade_cutoff;
                $assignment_attempt = $this->assignment_attempt_service->getAllAttempts(["assignment_id" => $assignment_id, "user_id" => $user_id])->first();
                $attempt_assignment_id = $assignment_attempt->id;
                $review_comments = html_entity_decode($request->review_comments);
                $grade = (int)$request->grade;
                if (empty($grade_cutoff) && ($grade >= 0)) {
                    $pass = true;
                } elseif (($grade >= $grade_cutoff)) {
                    $pass = true;
                } else {
                    $pass = false;
                }
                $data = [
                    "submission_status" => ST::REVIEWED,
                    "review_comments" => $review_comments,
                    "grade" => $grade,
                    "pass" => $pass,
                    "reviewed_by" => Auth::user()->username,
                    "reviewed_at" => time(),
                ];
                $this->assignment_attempt_service->updateData($attempt_assignment_id, $user_id, $data);
                return Redirect::route("get-grade-assignment", ['assignment_id' => $assignment_id, 'submission_type' => ST::REVIEWED])->with('success', trans('admin/assignment.review_assignment'));
            }
        } catch (Exception $e) {
            Log::error($e->getMessage() . ' at line '. $e->getLine(). ' in file '. $e->getFile());
            return Redirect::route("review-assignment", ['assignment_id' => $assignment_id, 'user_id' => $user_id])->with('error', trans('admin/assignment.error_message'));
        }
    }

    /**
     * @param $assignment_id
     * @param $user_id
     * @return  array
     */
    public function getReviewComments($assignment_id, $user_id)
    {
        $assignment_id = (int)$assignment_id;
        $params = [
            "assignment_id" => (int)$assignment_id,
            "user_id" => (int) $user_id
        ];
        $assignment_attempt = $this->assignment_attempt_service->getAllAttempts($params)->first();

        return response()->json([
            'data' => isset($assignment_attempt->review_comments) ? $assignment_attempt->review_comments : ''
        ]);
    }

    /**
     * @param $assignment_id
     * @param $user_id
     * @return  array
     */
    public function getOnlineText($assignment_id, $user_id)
    {
        $assignment_id = (int)$assignment_id;
        $params = [
            "assignment_id" => (int)$assignment_id,
            "user_id" => (int) $user_id
        ];
        $assignment_attempt = $this->assignment_attempt_service->getAllAttempts($params)->first();

        return response()->json([
            'data' => isset($assignment_attempt->uploaded_text) ? $assignment_attempt->uploaded_text : ''
        ]);
    }
}
