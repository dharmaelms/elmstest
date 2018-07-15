<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Assignment\AssignmentPermission;
use App\Enums\Program\ChannelPermission;
use App\Enums\RolesAndPermissions\Contexts;
use App\Enums\RolesAndPermissions\PermissionType;
use App\Enums\RolesAndPermissions\SystemRoles;
use App\Enums\User\UserEntity;
use App\Enums\User\UserPermission;
use App\Enums\User\UserStatus;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\Program\ElementType;
use App\Enums\Announcement\AnnouncementPermission;
use App\Enums\Assessment\AssessmentPermission;
use App\Enums\Event\EventPermission;
use App\Enums\UserGroup\UserGroupPermission;
use App\Enums\Survey\SurveyPermission;
use App\Events\Auth\Registered;
use App\Events\Elastic\Users\UsersAssigned;
use App\Events\Elastic\Users\UserGroupAssigned;
use App\Events\User\EntityEnrollmentByAdminUser;
use App\Events\User\EntityEnrollmentThroughUserGroup;
use App\Events\User\EntityUnenrollmentByAdminUser;
use App\Events\User\EntityUnenrollmentThroughUserGroup;
use App\Exceptions\User\UserNotFoundException;
use App\Helpers\User\UserListHelper;
use App\Http\Controllers\AdminBaseController;
use App\Model\Common;
use App\Model\CustomFields\Entity\CustomFields;
use App\Model\Email;
use App\Model\ImportLog\Entity\UsergroupLog;
use App\Model\ImportLog\Entity\UserLog;
use App\Model\NotificationLog;
use App\Model\Program;
use App\Model\Role;
use App\Model\SiteSetting;
use App\Model\Transaction;
use App\Model\TransactionDetail;
use App\Model\User;
use App\Model\UserGroup;
use App\Model\UserimportHistory;
use App\Services\Playlyfe\IPlaylyfeService;
use App\Services\Program\IProgramService;
use App\Services\UserGroup\IUserGroupService;
use App\Exceptions\UserGroup\UserGroupNotFoundException;
use App\Exceptions\ApplicationException;
use App\Services\CustomFields\ICustomService;
use App\Services\User\IUserService;
use Auth;
use Config;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Input;
use PHPExcel;
use PHPExcel_IOFactory;
use Redirect;
use Request;
use Session;
use Timezone;
use URL;
use Validator;

class UserGroupController extends AdminBaseController
{
    protected $layout = 'admin.theme.layout.master_layout';
    protected $notFoundUserGroup;
    private $user_service; //

    /**
     * @var IProgramService
     */
    private $programService;

    /**
     * @var IUserGroupService
     */
    private $userGroupService;

    /**
     * UserGroupController constructor
     * @param Request $request
     * @param IProgramService $programService
     */
    public function __construct(
        Request $request,
        IProgramService $programService,
        IUserGroupService $userGroupService,
        IUserService $user_service,
        ICustomService $custom_field_service
    ) {
        parent::__construct();

        $input = $request::input();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request::merge($input);

        $this->theme_path = 'admin.theme';

        $this->notFoundUserGroup = [];
        $this->programService = $programService;
        $this->userGroupService = $userGroupService;
        $this->user_service = $user_service;
        $this->custom_field_service = $custom_field_service;
    }

    public function getIndex()
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/user.manage_user') => 'usergroupmanagement',
            trans('admin/user.list_users') => '',
        ];
        if (!is_null(Input::get('filter'))) {
            $filter = Input::get('filter');
        } else {
            $filter = 'ACTIVE';
        }
        $start_serv = 0;
        $length_page_serv = 10;

        if (!is_null(Input::get('start_serv')) && !is_null(Input::get('length_page_serv'))) {
            $start_serv = (int)Input::get('start_serv');
            $length_page_serv = (int)Input::get('length_page_serv');
        }

        $viewmode = Input::get('view', 'desktop');
        $relfilter = Input::get('relfilter', 'all');
        $from = Input::get('from', 'none');
        $relid = Input::get('relid', 'none');
        if ($viewmode == 'iframe') {
            $this->layout->pagetitle = '';
            $this->layout->pageicon = '';
            $this->layout->pagedescription = '';
            $this->layout->header = '';
            $this->layout->sidebar = '';
            $this->layout->footer = '';
            $this->layout->content = view('admin.theme.users.listusersiframe')
                ->with('relfilter', $relfilter)
                ->with('from', $from)
                ->with('relid', $relid);
        } else {
            if (!$this->roleService->hasPermission(
                $this->request->user()->uid,
                ModuleEnum::USER,
                PermissionType::ADMIN,
                UserPermission::LIST_USER
            )) {
                return parent::getAdminError($this->theme_path);
            }

            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = trans('admin/user.manage_user');
            $this->layout->pageicon = 'fa fa-user';
            $this->layout->pagedescription = trans('admin/user.list_of_users');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'users_groups')
                ->with('submenu', 'user');
            $this->layout->footer = view('admin.theme.common.footer');
            $this->layout->content = view('admin.theme.users.listusers')
                ->with('filter', $filter)
                ->with('start_serv', $start_serv)
                ->with('length_page_serv', $length_page_serv);
        }
    }

    public function getUserListAjax()
    {
        $has_list_user_permission = false;
        $viewmode = Input::get('view', 'desktop');
        $from = $relid = null;
        $filter_params = [];
        switch ($viewmode) {
            case "desktop":
                    $has_list_user_permission = has_admin_permission(ModuleEnum::USER, UserPermission::LIST_USER);
                break;
            case "iframe":
                $from = Input::get('from', 'none');
                $relid = Input::get('relid', 'none');
                switch ($from) {
                    case "contentfeed":
                        $has_list_user_permission = $this->roleService->hasPermission(
                            $this->request->user()->uid,
                            ModuleEnum::CHANNEL,
                            PermissionType::ADMIN,
                            ChannelPermission::CHANNEL_ASSIGN_USER,
                            Contexts::PROGRAM,
                            $relid
                        );
                        break;
                    case 'quiz':
                        $assign_user_quiz_permission_data_with_flag = $this->roleService->hasPermission(
                            Auth::user()->uid,
                            ModuleEnum::ASSESSMENT,
                            PermissionType::ADMIN,
                            AssessmentPermission::QUIZ_ASSIGN_USER,
                            null,
                            null,
                            true
                        );

                        $quiz_assign_user_permission_data =
                            get_permission_data($assign_user_quiz_permission_data_with_flag);

                        $has_list_user_permission = is_element_accessible(
                            $quiz_assign_user_permission_data,
                            ElementType::ASSESSMENT,
                            $relid
                        );

                        if (!has_system_level_access($quiz_assign_user_permission_data)) {
                            $filter_params['assignable_user_ids'] = get_user_ids($quiz_assign_user_permission_data);
                        }

                        break;
                    case 'event':
                        $assign_user_event_permission_data_with_flag = $this->roleService->hasPermission(
                            Auth::user()->uid,
                            ModuleEnum::EVENT,
                            PermissionType::ADMIN,
                            EventPermission::ASSIGN_USER,
                            null,
                            null,
                            true
                        );

                        $event_assign_user_permission_data =
                            get_permission_data($assign_user_event_permission_data_with_flag);

                        $has_list_user_permission = is_element_accessible(
                            $event_assign_user_permission_data,
                            ElementType::EVENT,
                            $relid
                        );

                        $assignable_user_ids = null;
                        if (!has_system_level_access($event_assign_user_permission_data)) {
                            $filter_params['assignable_user_ids'] = get_user_ids($event_assign_user_permission_data);
                        }
                        break;
                    case 'usergroup':
                        $has_list_user_permission = has_admin_permission(
                            ModuleEnum::USER_GROUP,
                            UserGroupPermission::USER_GROUP_ASSIGN_USER
                        );
                        break;
                    case 'announcement':
                        $assign_user_announcement_permission_data_with_flag = $this->roleService->hasPermission(
                            Auth::user()->uid,
                            ModuleEnum::ANNOUNCEMENT,
                            PermissionType::ADMIN,
                            AnnouncementPermission::ASSIGN_USER,
                            null,
                            null,
                            true
                        );
                        $has_list_user_permission =
                            get_permission_flag($assign_user_announcement_permission_data_with_flag);
                        $announcement_assign_permission_data =
                            get_permission_data($assign_user_announcement_permission_data_with_flag);
   
                        if (!has_system_level_access($announcement_assign_permission_data)) {
                            $filter_params['assignable_user_ids'] = get_user_ids($announcement_assign_permission_data);
                        }
                        break;
                    case 'survey':
                        $assign_user_survey_permission_data_with_flag = $this->roleService->hasPermission(
                            Auth::user()->uid,
                            ModuleEnum::SURVEY,
                            PermissionType::ADMIN,
                            SurveyPermission::SURVEY_ASSIGN_USER,
                            null,
                            null,
                            true
                        );

                        $survey_assign_user_permission_data =
                            get_permission_data($assign_user_survey_permission_data_with_flag);

                        $has_list_user_permission = is_element_accessible(
                            $survey_assign_user_permission_data,
                            ElementType::SURVEY,
                            $relid
                        );

                        $assignable_user_ids = null;
                        if (!has_system_level_access($survey_assign_user_permission_data)) {
                            $filter_params['assignable_user_ids'] = get_user_ids($survey_assign_user_permission_data);
                        }
                        break;
                    case 'assignment':
                        $assign_user_assignment_permission_data_with_flag = $this->roleService->hasPermission(
                            Auth::user()->uid,
                            ModuleEnum::ASSIGNMENT,
                            PermissionType::ADMIN,
                            AssignmentPermission::ASSIGNMENT_ASSIGN_USER,
                            null,
                            null,
                            true
                        );

                        $assignment_assign_user_permission_data =
                            get_permission_data($assign_user_assignment_permission_data_with_flag);

                        $has_list_user_permission = is_element_accessible(
                            $assignment_assign_user_permission_data,
                            ElementType::ASSIGNMENT,
                            $relid
                        );

                        $assignable_user_ids = null;
                        if (!has_system_level_access($assignment_assign_user_permission_data)) {
                            $filter_params['assignable_user_ids'] = get_user_ids($assignment_assign_user_permission_data);
                        }
                        break;
                }
                break;
        }

        if (!$has_list_user_permission) {
            return response()->json(
                [
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ]
            );
        }

        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '1') {
                $orderByArray = ['username' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '2') {
                $orderByArray = ['firstname' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '3') {
                $orderByArray = ['email' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '4') {
                $orderByArray = ['created_at' => $order_by[0]['dir']];
            }
        }
        if (isset($search['value'])) {
            $searchKey = $search['value'];
        }

        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }

        $filter = Input::get('filter');
        if (!in_array($filter, ['ACTIVE', 'IN-ACTIVE'])) {
            $filter = 'ALL';
        }
        Session::put('userfilter', $filter);

        $relfilter = Input::get('relfilter', 'assigned');
        if ($viewmode == 'iframe' && in_array($relfilter, ['assigned', 'nonassigned']) &&
            in_array($from, ['contentfeed', 'usergroup', 'quiz', 'announcement', 'dams', 'event', 'questionbank', 'survey', 'assignment'])
            && preg_match('/^\d+$/', $relid)) {
            if ((Input::get("disable_filter", null) !== null) && (Input::get("disable_filter") === "TRUE")) {
                $relid = "none";
                $relfilter = "ALL";
            }
            $relinfo = [$from => $relid];
            $filteredRecords = User::getUsersCount(
                $relfilter,
                $searchKey,
                $relinfo,
                [],
                $filter_params
            );
            $filtereddata = User::getUsersWithPagination(
                $relfilter,
                $start,
                $limit,
                $orderByArray,
                $searchKey,
                $relinfo,
                [],
                $filter_params
            );
        } else {
            $users_to_be_excluded = [Auth::user()->uid];
            $filteredRecords = User::getUsersCount($filter, $searchKey, [], $users_to_be_excluded, []);
            $filtereddata = User::getUsersWithPagination(
                $filter,
                $start,
                $limit,
                $orderByArray,
                $searchKey,
                [],
                $users_to_be_excluded,
                []
            );
        }

        $totalRecords = $filteredRecords;
        $dataArr = [];
        foreach ($filtereddata as $key => $value) {
            if (Auth::user()->uid != $value->uid) {
                $checkbox = '<input type="checkbox" value="' . $value->uid . '">';
            } else {
                $checkbox = '';
            }
            if ($viewmode == 'iframe' && $value->role == "2") {
                $checkbox = '';
            }
            $user_delete = $this->roleService->hasPermission(
                $this->request->user()->uid,
                ModuleEnum::USER,
                PermissionType::ADMIN,
                UserPermission::DELETE_USER
            );

            if ($user_delete == false) {
                $delete = '';
            } else {
                $delete = '<a class="btn btn-circle show-tooltip deleteuser" title="' . trans('admin/manageweb.action_delete') . '" href="' . URL::to('cp/usergroupmanagement/delete-user/' . $value->uid) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-trash-o"></i></a>';
            }

            $user_edit = $this->roleService->hasPermission(
                $this->request->user()->uid,
                ModuleEnum::USER,
                PermissionType::ADMIN,
                UserPermission::EDIT_USER
            );
            if ($user_edit == false) {
                $edit = '';
            } else {
                $edit = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/manageweb.action_edit') . '" href="' . URL::to('cp/usergroupmanagement/edit-user/' . $value->uid) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-edit"></i></a>';
            }

            $user_view = $this->roleService->hasPermission(
                $this->request->user()->uid,
                ModuleEnum::USER,
                PermissionType::ADMIN,
                UserPermission::VIEW_USER
            );
            if ($user_view == false) {
                $view = '';
            } else {
                $view = '<a class="btn btn-circle show-tooltip viewuser" title="' . trans('admin/manageweb.action_view') . '" href="' . URL::to('/cp/usergroupmanagement/user-details/' . $value->uid) . '" ><i class="fa fa-eye"></i></a>';
            }

            $temparr = [
                $checkbox,
                $value->username,
                $value->firstname . ' ' . $value->lastname,
                $value->email,
                Timezone::convertFromUTC('@' . $value->created_at, Auth::user()->timezone, config('app.date_format')),
                $value->status,
                $view . $edit . $delete,
            ];

            if ($viewmode != 'iframe') {
                $ugids = User::getAssignedUsergroups($value->uid);
                $prgm_ids = User::getAssignedContentFeed($value->uid);
                $admin_flag = false;
                if (!empty($value->role)) {
                    $role_info = $this->roleService->getRoleDetails((int)$value->role);
                    $admin_flag = array_get($role_info, 'is_admin_role', '');
                }

                $assign_usergroup = $this->roleService->hasPermission(
                    $this->request->user()->uid,
                    ModuleEnum::USER_GROUP,
                    PermissionType::ADMIN,
                    UserGroupPermission::USER_GROUP_ASSIGN_USER
                );
                if ($assign_usergroup == false) {
                    $userGroupCount = '<a class="badge badge-grey" title="' . trans('admin/user.no_usergroup_permission') . '"><i class="fa fa-times"></i></a>';
                } elseif ($value->status == 'IN-ACTIVE') {
                    $userGroupCount = '<a class="badge badge-grey" title="' . trans('admin/user.cannot_assign_usergroup_to_inactive_user') . '" ><i class="fa fa-times"></i></a>';
                } elseif ($admin_flag == true) {
                    $userGroupCount = '<a class="badge badge-info" title="' . trans('admin/user.admin_assign_usergroup_note') . '">NA</a>';
                } else {
                    $userGroupCount = "<a href='" . URL::to('/cp/usergroupmanagement/user-groups?filter=ACTIVE&view=iframe&from=user&relid=' . $value->uid) . "' class='userrel badge badge-grey' data-key='" . $value->uid . "' data-info='usergroup' data-text='Assign User Group(s) to  <b>" . htmlentities('"' . $value->username . '"', ENT_QUOTES) . "</b>' data-json=''>" . 0 . '</a>';
                    if (isset($ugids) && !empty($ugids) && $ugids != 'default') {
                        $userGroupCount = "<a href='" . URL::to('/cp/usergroupmanagement/user-groups?filter=ACTIVE&view=iframe&from=user&relid=' . $value->uid) . "' class='userrel badge badge-success' data-key='" . $value->uid . "' data-info='usergroup' data-text='Assign User Group(s) to  <b>" . htmlentities('"' . $value->username . '"', ENT_QUOTES) . "</b>' data-json='" . json_encode($ugids) . "'>" . count($ugids) . '</a>';
                    }
                }

                $assign_contentfeed = $this->roleService->hasPermission(
                    $this->request->user()->uid,
                    ModuleEnum::CHANNEL,
                    PermissionType::ADMIN,
                    ChannelPermission::CHANNEL_ASSIGN_USER
                );
                if ($assign_contentfeed == false) {
                    $contentFeedCount = '<a class="badge badge-grey" title="' . trans('admin/user.assign_channel_permission_denied') . '" ><i class="fa fa-times"></i></a>';
                } elseif ($value->status == 'IN-ACTIVE') {
                    $contentFeedCount = '<a class="badge badge-grey" title="' . trans('admin/user.cant_assign_channels_to_inactive_user') . '" ><i class="fa fa-times"></i></a>';
                } elseif ($admin_flag == true) {
                    $contentFeedCount = '<a class="badge badge-info" title="' . trans('admin/user.admin_assign_channel_note') . '" >NA</a>';
                } else {
                    $contentFeedCount = "<a href='" . URL::to('/cp/contentfeedmanagement?filter=ACTIVE&view=iframe&subtype=single&from=user&relid=' . $value->uid) . "' class='userrel badge badge-grey' data-key='" . $value->uid . "' data-info='contentfeed' data-text='Assign " . trans('admin/program.program') . ' to  <b>' . htmlentities('"' . $value->username . '"', ENT_QUOTES) . "</b>' data-json=''>" . 0 . '</a>';
                    if (isset($prgm_ids) && !empty($prgm_ids) && $prgm_ids != 'default') {
                        $contentFeedCount = "<a href='" . URL::to('/cp/contentfeedmanagement?filter=ACTIVE&view=iframe&subtype=single&from=user&relid=' . $value->uid) . "' class='userrel badge badge-success' data-key='" . $value->uid . "' data-info='contentfeed' data-text='Assign " . trans('admin/program.program') . ' to  <b>' . htmlentities('"' . $value->username . '"', ENT_QUOTES) . "</b>' data-json='" . json_encode($prgm_ids) . "'>" . count($prgm_ids) . '</a>';
                    }
                }

                if (isset($value->relations['active_usergroup_user_rel']) && !empty($value->relations['active_usergroup_user_rel'])) {
                    $groudFeedsCount = User::getUserGroupFeedsCount($value->uid, $value->relations['active_usergroup_user_rel']);
                } else {
                    $groudFeedsCount = 0;
                }

                if ($groudFeedsCount > 0 && $admin_flag == false) {
                    $groupFeeds = '<a href="' . URL::to('/cp/usergroupmanagement/usergroup-feeds/' . $value->uid) . '" data-text="Group ' . trans('admin/user.channel') . ' assigned to <b>' . htmlentities('"' . $value->username . '"', ENT_QUOTES) . '</b>" class="groupfeeds badge badge-success">' . $groudFeedsCount . '</a>';
                } elseif ($admin_flag == true) {
                    $groupFeeds = '<a class="badge badge-info" title="' .trans('admin/user.group_channel_tool_tip') . '" >NA</a>';
                } else {
                    $groupFeeds = '<a href="' . URL::to('/cp/usergroupmanagement/usergroup-feeds/' . $value->uid) . '" data-text="Group ' . trans('admin/user.channel') . ' assigned to <b>' . htmlentities('"' . $value->username . '"', ENT_QUOTES) . '</b>" class="groupfeeds badge badge-grey">' . $groudFeedsCount . '</a>';
                }

                array_splice($temparr, 6, 0, [$userGroupCount, $contentFeedCount, $groupFeeds]);
            } else {
                array_pop($temparr);
            }
            $dataArr[] = $temparr;
        }
        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr,
        ];

        return response()->json($finaldata);
    }

    public function getUserList(UserListHelper $user_list_helper)
    {
        $totalCount = 0;
        $filteredCount = 0;
        $data = [];

        $filter_params = [];
        $filter_params["start"] = Input::get("start", 0);
        $filter_params["limit"] = Input::get("length", 10);
        $order_by_data = Input::get("order", []);
        $search_data = Input::get('search');

        $order_by_column_index = null;
        if (!empty($order_by_data[0]["column"])) {
            $order_by_column_index = $order_by_data[0]["column"];
        }

        if (!empty($order_by_data[0]["dir"])) {
            $filter_params["order_by_dir"] = $order_by_data[0]["dir"];
        } else {
            $filter_params["order_by_dir"] = "desc";
        }

        if (!empty($search_data['value'])) {
            $filter_params["search_key"] = $search_data['value'];
        } else {
            $filter_params["search_key"] = null;
        }

        $filter_params["status"] = [UserStatus::ACTIVE];

        $module = Input::get("module");

        if (in_array($module, ["program"])) {
            switch ($module) {
                case "program":
                    $program_id = Input::get("instance_id");
                    if (!$this->roleService->hasPermission(
                        $this->request->user()->uid,
                        ModuleEnum::CHANNEL,
                        PermissionType::ADMIN,
                        ChannelPermission::CHANNEL_ASSIGN_USER,
                        Contexts::PROGRAM,
                        $program_id
                    )) {
                        break;
                    }

                    $filter_params["enrollment_status"] = Input::get("enrollment_status", "ASSIGNED");
                    $columns = ["", "username", "firstname", "email", "role", "created_at", "status"];
                    $filter_params["order_by"] = !is_null($order_by_column_index)? $columns[$order_by_column_index] :
                                                    "created_at";
                    $users_data = $this->programService->getProgramUsers($program_id, $filter_params);
                    $program_context_details = $this->roleService->getContextDetails(Contexts::PROGRAM, true);

                    foreach ($users_data["data"] as $user) {
                        $user_system_level_role = $this->roleService->getRoleDetails($user["role"]);
                        $tmpArray = [
                            (($this->request->user()->uid !== $user["id"]) &&
                                (!array_get($user_system_level_role, 'is_admin_role')))?
                                $user_list_helper->generateUserListCheckbox($user["id"]): "",
                            $user["username"],
                            $user["firstname"]." ". $user["lastname"],
                            $user["email"],
                            (($this->request->user()->uid !== $user["id"]) &&
                                (!array_get($user_system_level_role, 'is_admin_role')))?
                                $user_list_helper->generateRolesDropdown(
                                    $program_context_details["roles"],
                                    $user["id"],
                                    (isset($user["role_id"])? $user["role_id"] : null)
                                ) : (isset($user["role_name"])? $user["role_name"] : ""),
                            Timezone::convertFromUTC(
                                "@" . $user["created_at"],
                                Auth::user()->timezone,
                                config("app.date_format")
                            ),
                            $user["status"],
                        ];

                        $data[] = $tmpArray;
                    }

                    $totalCount = $users_data["total_users_count"];
                    $filteredCount = $users_data["filtered_users_count"];

                    break;
                default:
                    break;
            }
        }

        return response()->json(
            [
                'recordsTotal' => $totalCount,
                'recordsFiltered' => $filteredCount,
                'data' => $data,
            ]
        );
    }

    public function getUserDetails($uid = null)
    {
        $user = has_admin_permission(ModuleEnum::USER, UserPermission::VIEW_USER);
        if ($user == false) {
            return parent::getAdminError($this->theme_path);
        }
        $user = User::getUsersUsingID($uid);
        if (empty($user) || !$uid) {
            $msg = trans('admin/user.missing_user');

            return redirect('/cp/usergroupmanagement/')
                ->with('error', $msg);
        }
        $user = $user[0];

        return view('admin.theme.users.userdetails')->with('user', $user);
    }

    public function getUsergroupFeeds($uid)
    {
        if (!$uid) {
            $msg = trans('admin/user.missing_user');

            return redirect('/cp/usergroupmanagement/')
                ->with('error', $msg);
        }
        $groupfeeds = User::getUserGroupFeeds($uid);

        return view('admin.theme.users.usergroup_feeds')->with('groupfeeds', $groupfeeds);
    }

    public function getAddUser()
    {
        if (!has_admin_permission(ModuleEnum::USER, UserPermission::ADD_USER)) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/user.manage_user') => 'usergroupmanagement',
            trans('admin/user.add_user') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/user.add_user');
        $this->layout->pageicon = 'fa fa-user';
        $this->layout->pagedescription = trans('admin/user.add_user');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'users_groups')
            ->with('submenu', 'user');
        $this->layout->footer = view('admin.theme.common.footer');

        $context_data = $this->roleService->getContextDetails(Contexts::SYSTEM, true);

        $userCustomField = CustomFields::getUserActiveCustomField($program_type = 'user', $program_sub_type = '', $status = 'ACTIVE');

        $this->layout->content = view('admin.theme.users.adduser')->with('context_data', $context_data)->with('timezones', Timezone::get())->with('frequent_tz', Timezone::frequent())->with('userCustomField', $userCustomField);
    }

    public function postAddUser(IPlaylyfeService $playlyfe)
    {
        if (!has_admin_permission(ModuleEnum::USER, UserPermission::ADD_USER)) {
            return parent::getAdminError($this->theme_path);
        }

        Input::flash();

        $username = Input::get('username');
        $email = Input::get('email');
        $firstname = Input::get('firstname');
        $lastname = Input::get('lastname');

        $system_context_details = $this->roleService->getContextDetails(Contexts::SYSTEM, true);
        $system_context_role_ids = [];
        if (!empty($system_context_details["roles"])) {
            $system_context_role_ids = collect($system_context_details["roles"])->pluck("id")->toArray();
        }

        Input::merge(['username' => strtolower(trim($username))]);
        Input::merge(['email' => strtolower($email)]);
        Input::merge(['firstname' => trim($firstname)]);
        Input::merge(['lastname' => trim($lastname)]);

        $allowed_role_ids_string = implode(",", $system_context_role_ids);
        $rules = [
            'firstname' => 'Required|Min:3|Max:30|Regex:/^[[:alpha:]]+(?:[-_ ]?[[:alpha:]]+)*$/',
            'lastname' => 'Max:30|Regex:/^([A-Za-z\'. ])+$/',
            'email' => 'Required|email|unique:users',
            'mobile' => 'numeric|digits:10',
            'username' => 'Required|Min:3|Max:93|unique:users|checkUserNameRegex|checkusername:'. strtolower(trim($username)) . '',
            'password' => 'Required|Min:6|Max:24|Regex:/^[0-9A-Za-z!@#$%_.-]{6,24}$/|Confirmed',
            'password_confirmation' => 'Required',
            'role' => "in:{$allowed_role_ids_string}",
        ];

        $messages = [
            'password.regex' => trans('admin/user.password_regex_msg'),
            'firstname.required' => trans('admin/user.first_name_required'),
            'firstname.min' => trans('admin/user.first_name_min'),
            'firstname.max' => trans('admin/user.first_name_max'),
            'firstname.regex' => trans('admin/user.first_name_special_characters'),
            'lastname.regex' => trans('admin/user.last_name_special_characters'),
            'lastname.max' => trans('admin/user.last_name_max'),
            'checkUserNameRegex' => 'Symbolic characters not allowed',
            'checkusername' => trans('admin/user.check_new_username'),
        ];

        $userCustomField = CustomFields::getUserCustomField($program_type = 'user', $program_sub_type = '');
        if (is_array($userCustomField) && !empty($userCustomField)) {
            foreach ($userCustomField as $values) {
                if ($values['mark_as_mandatory'] == 'yes') {
                    $rules[$values['fieldname']] = 'Required';
                    $messages[$values['fieldname'] . '.required'] = trans(
                        'admin/user.custom_field_required',
                        ['label' => ucwords($values['fieldlabel'])]
                    );
                }
            }
        }

        Validator::extend('checkUserNameRegex', function ($attribute, $value, $parameters) {
            if (isset($value) && !empty($value)) {
                (strpos($value, '@') !== false) ? $pattern = "/^(?!.*?[._]{2})[a-zA-Z0-9]+[a-zA-Z0-9(\._\)?-]+[a-zA-Z0-9]+@[a-zA-Z0-9]+([\.]?[a-z]{2,6}+)*$/" : $pattern = "/^[a-zA-Z0-9._]*$/";
                if (preg_match($pattern, $value)) {
                    return true;
                } else {
                    return false;
                }
            }
        });

        Validator::extend('checkusername', function ($attribute, $value, $parameters) {
            $username = strtolower($parameters[0]);
            $return_val = User::where('username', 'like', $username)->get(['uid'])->toArray();
            if (empty($return_val)) {
                return true;
            }
            return false;
        });

        $validation = Validator::make(Input::all(), $rules, $messages);
        if ($validation->fails()) {
            return redirect('cp/usergroupmanagement/add-user')->withInput()->withErrors($validation);
        } elseif ($validation->passes()) {
            // User custom field code
            $List = $this->customFieldData($program_type = 'user', $program_sub_type = '');
            $uid = User::getInsertUsers(Input::all(), $List);

            if (!is_null($uid)) {
                event(new Registered($uid, Input::get("role")));
            }
            //Playlyfe integration code starts here.
            //Code added by Muniraju N.

            $playlyfeEvent = [
                "type" => "create-user",
                "data" => [
                    "user_id" => $uid,
                    "player_id" => $username,
                    "player_alias" => $firstname,
                ],
            ];

            $playlyfe->processEvent($playlyfeEvent);

            //Playlyfe integration code ends here.

            Input::flush();

            return redirect('cp/usergroupmanagement/manage-user/' . $uid)
                ->with('success', trans('admin/user.adduser_success'));
        }
    }

    /* code to get all the custom field and their values in an array */
    public function customFieldData($program_type, $program_sub_type)
    {
        $userCustomField = CustomFields::getUserActiveCustomField($program_type, $program_sub_type);
        $inputList = Input::all();
        $customField = [];
        foreach ($userCustomField as $key => $user) {
            $customField[$user['fieldname']] = null;
        }
        $userlist = array_intersect_key($inputList, $customField);
        return $userlist;
    }

    /* ends here */

    public function getManageUser($uid = null)
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/user.manage_user') => 'usergroupmanagement',
            trans('admin/user.add_user_success') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $asset = User::getActiveUserUsingID($uid);
        if (empty($asset) || !$uid) {
            $msg = trans('admin/user.missing_params');

            return redirect('/cp/usergroupmanagement/')
                ->with('error', $msg);
        }
        $asset = $asset[0];
        $this->layout->pagetitle = trans('admin/user.manage_user');
        $this->layout->pageicon = 'fa fa-user';
        $this->layout->pagedescription = trans('admin/user.manage_user');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'users_groups')
            ->with('submenu', 'user');
        $this->layout->footer = view('admin.theme.common.footer');
        $this->layout->content = view('admin.theme.users.manageuser')->with('uid', $uid)->with('asset', $asset);
    }

    public function getDeleteUser($uid)
    {
        $user = has_admin_permission(ModuleEnum::USER, UserPermission::DELETE_USER);
        if ($user == false) {
            return parent::getAdminError($this->theme_path);
        }
        $user = User::getUserDetailsByID((int)$uid);
        $admin_check = $user->toArray();
        if (isset($admin_check['super_admin']) && $admin_check['super_admin'] == true) {
            return parent::getAdminError($this->theme_path);
        }
        $usersessions = $user->session_ids;
        if ($usersessions && is_array($usersessions)) {
            foreach ($usersessions as $sessionid) {
                if (file_exists(config('session.files') . '/' . $sessionid)) {
                    unlink(config('session.files') . '/' . $sessionid);
                }
            }
        }

        $user->update(['session_ids' => []]);
        User::getDelete($uid, $user['username'], $user['email']);
        $this->roleService->unmapUserAndRole($uid);

        $success = trans('admin/user.user_delete');

        $start_serv = (int)Input::get('start', 0);
        $length_page_serv = (int)Input::get('limit', 10);
        $search = Input::get('search', '');
        $order_by = Input::get('order_by', '4 desc');
        $filter = Input::get('filter', 'all');

        $totalRecords = User::getUsersCount($filter, $search);
        if ($totalRecords <= $start_serv) {
            $start_serv -= $length_page_serv;
            if ($start_serv < 0) {
                $start_serv = 0;
            }
        }

        return redirect('cp/usergroupmanagement?start=' . $start_serv . '&limit=' . $length_page_serv . '&filter=' . $filter . '&search=' . $search . '&order_by=' . $order_by)->with('success', $success);
    }

    public function postBulkDelete()
    {
        $user = has_admin_permission(ModuleEnum::USER, UserPermission::DELETE_USER);
        if ($user == false) {
            return parent::getAdminError($this->theme_path);
        }
        $keys = Input::get('uids');
        if (!$keys) {
            $msg = trans('admin/user.missing_params');

            return redirect('/cp/usergroupmanagement/')
                ->with('error', $msg);
        }
        $keys = rtrim($keys, ',');
        $keys = explode(',', $keys);
        foreach ($keys as $uid) {
            $usersdata = User::getUserDetailsByID((int)$uid);
            $usersessions = $usersdata->session_ids;
            if ($usersessions && is_array($usersessions)) {
                foreach ($usersessions as $sessionid) {
                    if (config('session.files') && file_exists(config('session.files') . '/' . $sessionid)) {
                        unlink(config('session.files') . '/' . $sessionid);
                    }
                }
            }
            $usersdata->update(['session_ids' => []]);
            User::getDelete($uid, $usersdata['username'], $usersdata['email']);
            $this->roleService->unmapUserAndRole($uid);
        }
        $success = trans('admin/user.user_bulk_delete');

        return redirect('/cp/usergroupmanagement/')
            ->with('success', $success);
    }

    public function postBulkActivate()
    {
        $user = has_admin_permission(ModuleEnum::USER, UserPermission::EDIT_USER);
        if ($user == false) {
            return parent::getAdminError($this->theme_path);
        }
        $keys = Input::get('uids');
        if (!$keys) {
            $msg = trans('admin/user.missing_params');

            return redirect('/cp/usergroupmanagement/')
                ->with('error', $msg);
        }
        $keys = rtrim($keys, ',');
        $keys = explode(',', $keys);
        foreach ($keys as $uid) {
            User::getActivate($uid);
        }
        $success = trans('admin/user.user_bulk_activate');

        return redirect('/cp/usergroupmanagement/')
            ->with('success', $success);
    }

    public function postBulkInactivate()
    {
        $user = has_admin_permission(ModuleEnum::USER, UserPermission::EDIT_USER);
        if ($user == false) {
            return parent::getAdminError($this->theme_path);
        }
        $keys = Input::get('uids');
        if (!$keys) {
            $msg = trans('admin/user.missing_params');

            return redirect('/cp/usergroupmanagement/')
                ->with('error', $msg);
        }
        $keys = rtrim($keys, ',');
        $keys = explode(',', $keys);
        foreach ($keys as $uid) {
            $userdata = User::getUserDetailsByID((int)$uid);
            $usersessions = $userdata->session_ids;
            if ($usersessions && is_array($usersessions)) {
                foreach ($usersessions as $sessionid) {
                    if (file_exists(config('session.files') . '/' . $sessionid)) {
                        unlink(config('session.files') . '/' . $sessionid);
                    }
                }
            }
            $userdata->update(['session_ids' => []]);
            User::getInactivate($uid);
        }
        $success = trans('admin/user.user_bulk_in_activate');

        return redirect('/cp/usergroupmanagement/')
            ->with('success', $success);
    }

    public function getExportUsers()
    {
        try {
            $user = has_admin_permission(ModuleEnum::USER, UserPermission::EXPORT_USERS);
            if ($user == false) {
                return parent::getAdminError($this->theme_path);
            }
            $status = Session::get('userfilter');
            $customFields = CustomFields::getUserCustomFieldArr('user', '', ['fieldlabel']);
            $this->createCustomFieldArr($customFields, 'fieldlabel');
            $customFields = array_flatten($customFields);

            $customFieldArr = [];
            foreach ($customFields as $key => $val) {
                $customFieldArr[$val] = null;
            }
            unset($customFields);

            $user_req_details = [
                    'username',
                    'firstname',
                    'lastname',
                    'email',
                    'mobile',
                    'created_at',
                    'status',
                    'app_registration',
                    'nda_status',
                    'nda_response_time'
            ];
            $customfields_name = CustomFields::getUserCustomFieldArr('user', '', ['fieldname']);
            $this->createCustomFieldArr($customfields_name, 'fieldname');
            $customfields_name = array_flatten($customfields_name);
            $customFieldName = [];
            foreach ($customfields_name as $key => $value) {
                 $customFieldName[$value] = null;
            }

            $columns = array_merge($user_req_details, array_keys($customFieldName));
            $total_users_count = User::getUsersCount('ALL', null, [], [Auth::user()->uid], []);
            $header[] = trans('admin/user.username');
            $header[] = trans('admin/user.first_name');
            $header[] = trans('admin/user.email_id');
            $header[] = trans('admin/user.mobile_number');
            $header[] = trans('admin/user.created_on');
            $header[] = trans('admin/user.status');
            $header[] = trans('admin/user.registration_source');
            if (SiteSetting::module('UserSetting', 'nda_acceptance') == 'on') {
                $header[] = trans('admin/user.nda_status');
                $header[] = trans('admin/user.nda_response_time');
            }
            if (is_array($customFieldArr) && !empty($customFieldArr) ) {
                foreach ($customFieldArr as $key=>$val) {
                    $header[] = $key;
                }
            }
            $filename = "Userslist.csv";
            $file_pointer = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . str_replace(" ", "", $filename));
            fputcsv($file_pointer, $header);
            $batch_limit = config('app.bulk_insert_limit');
            $total_batches = intval($total_users_count / $batch_limit);
            $record_set = 0 ;
            do {
                $start = $record_set * $batch_limit;
                $users = $this->user_service->getAllUsersData($columns, $start, $batch_limit)->toArray();
                $this->usersWithCustomFields($users, $customFieldArr);
                foreach ($users as $key=>$user) {
                    $tempRow = [];
                    if (!empty(array_get($user, 'username'))) {
                        $tempRow[] = array_get($user, 'username');
                        $tempRow[] = array_get($user, 'firstname');
                        $tempRow[] = array_get($user, 'email');
                        $tempRow[] = array_get($user, 'mobile');
                        $tempRow[] = Timezone::convertFromUTC('@'.array_get($user, 'created_at'), Auth::user()->timezone, Config('app.date_time_format'));
                        $tempRow[] = array_get($user, 'status');
                        $tempRow[] = (isset($user['app_registration']) && $user['app_registration'] == true) ? 'APP' : 'WEB';
                        if(array_key_exists('nda_status', $user)) {
                            if ($user['nda_status'] == NDA::DECLINED) {
                                $tempRow[] = trans('admin/user.nda_disagreed');
                            } elseif ($user['nda_status'] == NDA::ACCEPTED) {
                                $tempRow[] = trans('admin/user.nda_agreed');
                            } elseif ($user['nda_status'] == NDA::NO_RESPONSE) {
                                $tempRow[] = trans('admin/user.nda_no_response');
                            }
                        }
                        if(array_key_exists('nda_response_time', $user)) {
                            $tempRow[] = Timezone::convertFromUTC("@".$user['nda_response_time'],Auth::user()->timezone,Config('app.date_time_format'));
                        } else {
                            $tempRow[] = '';
                        }
                        if(is_array($customFieldArr) && !empty($customFieldArr)) {
                            foreach ($customFieldArr as $key=>$fields) {
                                if (array_key_exists($key,$user)) {
                                    $tempRow[] = $user[$key];
                                }
                            }
                        }
                    }
                    fputcsv($file_pointer, $tempRow);
                }
                $record_set++;
            } while ($record_set <= $total_batches);
            fclose($file_pointer);
            exit;
        } catch (Exception $e) {
            return response()->json(['No records found, Please try after some time']);
        }       
    }

    /*
    Name : createCustomFieldArr
    Purpose: to remove _id from nested array
     */
    public static function createCustomFieldArr(&$arr, $field = '')
    {
        if (is_array($arr)) {
            foreach ($arr as $key => &$val) {
                foreach ($val as $key => $value) {
                    if ($key == "_id") {
                        unset($val['_id']);
                    }
                }
            }
        }
    }

    /*
    Name : usersWithCustomFields
    Purpose: To Prepare the final data for export
     */
    public function usersWithCustomFields(&$users, $customFields = [])
    {
        if (is_array($users)) {
            $i = 0;
            $mandatoryColsArr = ['uid', 'firstname', 'lastname', 'role', 'username', 'email', 'mobile', 'created_at', 'status', 'app_registration'];
            $pushedArr = [];

            if (SiteSetting::module('UserSetting', 'nda_acceptance') == 'on') {
                array_push($mandatoryColsArr, 'nda_status', 'nda_response_time');
            }
            foreach ($users as $key => $value) {
                $pushedArr = $mandatoryColsArr;
                $userlist = array_intersect_key($users[$i], $customFields);
                if (is_array($userlist) && !empty($userlist)) {
                    $a = array_merge($pushedArr, array_keys($userlist));
                    $users[$i] = array_only($users[$i], $a);
                } else {
                    $users[$i] = array_only($users[$i], $mandatoryColsArr);
                }

                $i++;
            }
        }
    }

    /*
     * Purpose: Attach the custom fields with Users header fields and
    Force the download to the browser
     */

    public function getImportAddUsersExcel()
    {
        if (!has_admin_permission(ModuleEnum::USER, UserPermission::IMPORT_USERS)) {
            return parent::getAdminError($this->theme_path);
        }

        $downloadUserXls = [trans('admin/user.first_name') . '*', trans('admin/user.last_name'), trans('admin/user.email') . '*', trans('admin/user.mobile'), trans('admin/user.username') . '*', trans('admin/user.password') . '*', trans('admin/user.usergroup')];
        // get the custom fields array and merge it to userxlsreport
        $customFields = CustomFields::getUserCustomFieldArr('user', '', ['fieldlabel', 'mark_as_mandatory']);
        $this->createCustomFieldArr($customFields, 'fieldlabel');
        if (is_array(($customFields)) && !empty($customFields)) {
            $i = 0;
            foreach ($customFields as $key => $val) {
                if ('yes' == $customFields[$i]['mark_as_mandatory']) {
                    array_push($downloadUserXls, $customFields[$i]['fieldlabel'] . '*');
                } else {
                    array_push($downloadUserXls, $customFields[$i]['fieldlabel']);
                }
                $i++;
            }
        }

        // add option at the last
        array_push($downloadUserXls, trans('admin/user.option') . '*');

        $excelObj = new PHPExcel();
        $excelObj->setActiveSheetIndex(0);
        $excelObj->getActiveSheet()->setTitle('Excel upload');
        $excelObj->getActiveSheet()->fromArray($downloadUserXls, null, 'A1');
        $filename = 'users_bulk_import.xls'; //save our workbook as this file name
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); //mime type
        header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name
        header('Cache-Control: max-age=0'); //no cache
        //save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
        //if you want to save it as .XLSX Excel 2007 format
        $objWriter = PHPExcel_IOFactory::createWriter($excelObj, 'Excel2007');
        //force user to download the Excel file without writing it to server's HD
        $objWriter->save('php://output');
        exit;
    }

    public function getImportUsers()
    {
        if (!has_admin_permission(ModuleEnum::USER, UserPermission::IMPORT_USERS)) {
            return parent::getAdminError($this->theme_path);
        }
        $userxlsreport = Session::get('userxlsreport');
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/user.manage_user') => 'usergroupmanagement',
            trans('admin/user.import_user_bulk') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/user.import_user');
        $this->layout->pageicon = 'fa fa-user';
        $this->layout->pagedescription = trans('admin/user.import_user_in_bulk');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'users_groups')
            ->with('submenu', 'user');
        $this->layout->footer = view('admin.theme.common.footer');
        $grouplist = $this->getUserGroupList();
        try {
            $groupListWithUserCount = [];
            if (is_array($grouplist)) {
                foreach ($grouplist as $list) {
                    $ugid = $list['ugid'];
                    $groupListWithUserCount[$list['usergroup_name']] = count($this->getUserCountfromUsergroup((int)$ugid));
                }
            }
        } catch (Exception $e) {
            // catch Exception
            // log::
        }
        
        $context_details = $this->roleService->getContextDetails(Contexts::SYSTEM, true);
        $roles = array_get($context_details, 'roles', []);
        $this->layout->content = view('admin.theme.users.importbulkusers')->with('roles', $roles)->with('timezones', Timezone::get())->with('frequent_tz', Timezone::frequent())->with('groupListWithUserCount', $groupListWithUserCount);
    }

    public function postImportUsers()
    {
        if (!has_admin_permission(ModuleEnum::USER, UserPermission::IMPORT_USERS)) {
            return parent::getAdminError($this->theme_path);
        }

        ini_set('max_execution_time', 300);

        $rules = [
            'xlsfile' => 'Required|allowexcel'
        ];
        $niceNames = [
            'xlsfile' => 'import file',
        ];
        Validator::extend('allowexcel', function ($attribute, $value, $parameters) {
            $mime = $value->getMimeType();
            if (in_array($mime, ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.oasis.opendocument.text', 'application/vnd.ms-excel', 'application/zip', 'application/vnd.ms-office','application/octet-stream'])) {
                return true;
            }
            return false;
        });

        $messages = [];
        $messages += [
            'xlsfile.allowexcel' => 'Please upload only XLS file',
        ];

        $validator = Validator::make(Input::all(), $rules, $messages);
        $validator->setAttributeNames($niceNames);
        if ($validator->fails()) {
            Session::flash('errorflag', 'error');
            return redirect('cp/usergroupmanagement/import-users')->withInput()
                ->withErrors($validator);
        } else {
            $xlsfile = Input::file('xlsfile');
            $user_bulkimport_path = Config::get('app.user_bulkimport_path');

            $objPHPExcel = PHPExcel_IOFactory::load($xlsfile);
            $sheet = $objPHPExcel->getActiveSheet();
            $rows = $sheet->getHighestDataRow();

            $highestColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestDataColumn();
            $errorFlag = 0;
            $isEmpty = 0;
            $success_count = 0;
            $failed_count = 0;
            $emailTemp = [];
            $timezone = implode(',', Timezone::get());
            $add = trans('admin/user.add');
            $update = trans('admin/user.update');
            $errorData = null;
            $setRulesForUserCustomFields = $customFieldDataFromExcel = [];

            /* read the rows of the excel sheet one by one */
            for ($i = 1; $i <= $rows; ++$i) {
                $rowData = $sheet->rangeToArray('A' . $i . ":$highestColumn" . $i, null, true, false);
                if ($i == 1) {
                    /* $filteredCols = array_filter($rowData[0], function($v){
                    return $v !==null;});*/

                    $filteredCols = $rowData[0];

                    //get user custom fields
                    $userCustomFields = CustomFields::getUserCustomFieldArr('user', '', ['fieldlabel', 'mark_as_mandatory']);
                    $this->createCustomFieldArr($userCustomFields);
                    $setRulesForUserCustomFields = $userCustomFields; // 2 keys : 1) fieldname, 2) mark_as_mandatory

                    $list = [];
                    if (is_array($userCustomFields)) {
                        foreach ($userCustomFields as $key => $val) {
                            $list[$val['fieldlabel']] = null; // create a array of keys of custom fields
                        }
                    }
                    $custom_fields_array = array_keys($list);
                    unset($userCustomFields);

                    if (is_array($filteredCols)) {
                        // replace * sign with ''
                        $filteredCols = array_map(function ($val) {
                            return str_replace("*", '', $val);
                        }, $filteredCols);
                        $filteredCols = array_filter($filteredCols);
                        $filteredCols = array_flip($filteredCols); // form the keys
                    }
                    $customFieldDataFromExcel = array_intersect_key($filteredCols, $list); // get the custom field data only from excel sheet
                    unset($list);
                    //get add user first row with custom fields
                    $firstRowAddUserXls = trans('admin/user.first_name') .', '. trans('admin/user.last_name').', '. trans('admin/user.email').', '. trans('admin/user.mobile').', '. trans('admin/user.username').', '. trans('admin/user.password').', '. trans('admin/user.usergroup');
                    $add_comma_separated_header = str_replace(" ", '', trim(strtolower($firstRowAddUserXls)));
                    $add_first_row_array = explode(",", $add_comma_separated_header);
                    $add_user_first_row = array_merge($add_first_row_array, $custom_fields_array);
                    array_push($add_user_first_row, strtolower(trans('admin/user.option')));
                    //get update user first row with custom fields
                    $firstRowUpdateUserXls = trans('admin/user.serial_no') .', '. trans('admin/user.first_name') .', '. trans('admin/user.last_name').', '. trans('admin/user.email').', '. trans('admin/user.mobile').', '. trans('admin/user.username').', '. trans('admin/user.usergroup');
                     $update_user_comma_separated_header = str_replace(" ", '', trim(strtolower($firstRowUpdateUserXls)));
                    $update_first_row_array = explode(",", $update_user_comma_separated_header);
                    $update_user_first_row = array_merge($update_first_row_array, $custom_fields_array);
                    array_push($update_user_first_row, strtolower(trans('admin/user.option')));

                    $userData_WithoutCustomField = array_except($filteredCols, array_keys($customFieldDataFromExcel));

                    $comma_separated = implode(", ", array_keys($userData_WithoutCustomField));

                    // trim the inner space bw words
                    $comma_separated = str_replace(" ", '', trim(strtolower($comma_separated)));
                    $comma_separated = str_replace("*", '', $comma_separated);
                    $cols = explode(",", $comma_separated); // get the columns 1st row of the excel sheet

                    if (is_array($cols)) {
                        foreach ($customFieldDataFromExcel as $key => $value) {
                            $cols[] = $key;
                        }
                    }

                    /* Excel sheet cannot be uploaded without mandatory cols*/
                    $mandatoryCols = ["username", "email", "option", "firstname"];

                    foreach ($mandatoryCols as $val) {
                        if (in_array($val, $cols)) {
                        } else {
                            return Redirect::back()->with("error", trans('admin/user.invalid_template'));
                        }
                    }
                    /* Move option field after custom fields*/
                    $last_column_key = key(array_slice($cols, -1, 1, true));
                    $option_column_key = array_search("option", $cols);
                    $this->moveOptionElement($cols, $option_column_key, $last_column_key);
                }

                $emailTemp = array_merge($emailTemp, $rowData);
                $excelRowData = [];

                if (!empty($rowData) && $i > 1) {
                    $isEmpty = 1;
                    $rowData = $rowData[0];

                    $rowData = Common::trimArray($rowData, false);

                    /* remove option field from array and add it at the bottom */
                    $key = array_search("option", $cols);
                    unset($cols[$key]);
                    array_push($cols, 'option');
                    if (count($cols) == count($rowData)) {
                        $excelRowData = array_combine($cols, $rowData);
                        $emailTemp[$i - 1] = $excelRowData;
                        $excelRowData['usergroup'] = (strpos($excelRowData['usergroup'], ",")) ? explode(",", strtolower($excelRowData['usergroup'])) : [strtolower($excelRowData['usergroup'])];
                        $option_data = strtolower($excelRowData['option']);
                        $registered_user_role_id = $this->roleService->getRoleDetails(SystemRoles::REGISTERED_USER)["id"];
                        $excelRowData['role'] = $registered_user_role_id;
                        // for add add rules for custom fields
                        if ($option_data == "add") {
                            $errorData = $this->validateRulesExcel($excelRowData, $setRulesForUserCustomFields);
                        } else {
                            $errorData = $this->validateRulesExcel($excelRowData);
                        }
                        if ($errorData != false) {
                            $errorFlag = 1;
                            $errors = '';
                            $emailTemp[$i - 1]['record_status'] = 'Failed';

                            foreach ($errorData->all() as $message) {
                                $errors .= $message;
                            }
                            $emailTemp[$i - 1]['errors'] = $errors;
                            $failed_count = $failed_count + 1;
                        } else {
                            switch (strtolower($excelRowData['option'])) {
                                case "$add":
                                    $updateFlag = 0;
                                    $uid = null;
                                    if (!array_key_exists("password", $excelRowData)) {
                                        continue;
                                    }

                                    $uid = $this->createNewUser($excelRowData, $customFieldDataFromExcel);
                                    break;

                                case "$update":
                                    $updateFlag = 1;
                                    $user = null;
                                    if (array_key_exists("password", $excelRowData)) {
                                        continue;
                                    }

                                    $user = $this->updateImportUser($excelRowData, $customFieldDataFromExcel);
                                    break;

                                default:
                                    $updateFlag = '';
                                    break;
                            }
                            if (isset($uid) || isset($user) || ((1 === $updateFlag) && (is_null($user)))) {
                                $success_count = $success_count + 1;
                                $emailTemp[$i - 1]['record_status'] = 'Success';
                                $emailTemp[$i - 1]['errors'] = '';
                            } else {
                                $errorFlag = 1;
                                $failed_count = $failed_count + 1;
                                $emailTemp[$i - 1]['record_status'] = 'Failed';
                                $emailTemp[$i - 1]['errors'] = '';
                            }
                        }
                    } else {
                        return redirect('cp/usergroupmanagement/import-users')
                            ->with('error', trans('admin/user.bulk_import_update_column_error'));
                    }
                }
            }
            $no_of_records = $i - 2;
            $filename = $xlsfile->getClientOriginalName();
            if ($errorFlag) {
                if ($failed_count == $no_of_records) {
                    $status = 'FAILED';
                } else {
                    $status = 'PARTIAL';
                }
            } else {
                $status = 'SUCCESS';
            }

            $result = $emailTemp;
            unset($emailTemp[0]);
            $imported_records = view(
                $this->theme_path . '.users.import_email',
                [
                    'users' => $emailTemp,
                    'status' => $status,
                    'customFieldDataFromExcel' => $customFieldDataFromExcel
                ]
            )->render();

            /*sending mail to admin starts here*/
            $site_name = config('app.site_name');
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
            $headers .= 'From:' . $site_name . "\r\n";
            $to = config('app.site_admin_email');

            $base_url = config('app.url');
            $login_url = '<a href="' . $base_url . '/auth/login">Click here to login</a>';
            $site_admin_name = config('app.site_admin_name');
            $date_time = Timezone::convertFromUTC('@' . time(), config('app.default_timezone'), config('app.date_format'));

            $name = 'admin-bulkimport-template';
            $email_details = Email::getEmail($name);

            $subject = $email_details[0]['subject'];
            $subject_find = ['<SITE NAME>'];
            $subject_replace = [$site_name];
            $subject = str_replace($subject_find, $subject_replace, $subject);

            $body = $email_details[0]['body'];
            $find = ['<SITE ADMIN NAME>', '<CREATED BY>', '<SITE NAME>', '<DATETIME>', '<LOGIN URL>', '<USERS>'];
            $replace = [$site_admin_name, Auth::user()->username, $site_name, $date_time, $login_url, $imported_records];
            $body = str_replace($find, $replace, $body);
            Common::sendMailHtml($body, $subject, $to);

            /*sending mail to admin ends here*/

            //adding import history to the db
            UserimportHistory::getInsertHistory($filename, $success_count, $failed_count, $status, $no_of_records);

            // Check directory exits
            if (!is_dir($user_bulkimport_path)) {
                if (!mkdir($user_bulkimport_path, 0777, true)) {
                    die('Failed to create Role folder');
                }
                chmod($user_bulkimport_path, 0777);
            }

            // Check directory is writable
            if (is_writable($user_bulkimport_path)) {
                $xlsfile->move($user_bulkimport_path, $xlsfile->getClientOriginalName() . '.' . $xlsfile->getClientOriginalExtension());
            } else {
                die('Role directory is not writable');
            }

            if ($errorFlag) {
                Session::put('userxlsreport', $result);
                Session::flash('errorflag', 1);
                return redirect('cp/usergroupmanagement/import-users');
            } else {
                Input::flush();
                Session::forget('userxlsreport');
                Session::forget('errorflag');

                if ($isEmpty == 0) {
                    return redirect('cp/usergroupmanagement/import-users')
                        ->with('error', trans('admin/user.bulk_import_empty'));
                } elseif ($updateFlag == 0) {
                    return redirect('cp/usergroupmanagement/import-users')
                        ->with('success', trans('admin/user.bulk_import_success'));
                } else {
                    return redirect('cp/usergroupmanagement/import-users')
                        ->with('success', trans('admin/user.bulk_import_update_success'));
                }
            }
        }
    }

    public function validateRulesExcel($excelrowData, $setRulesForUserCustomFields = null)
    {
        $username = array_get($excelrowData, 'username');
        $excelrowData['username'] = strtolower(array_get($excelrowData, 'username'));
        $excelrowData['email'] = strtolower(array_get($excelrowData, 'email'));

        $rules = [
            'firstname' => 'Required|Min:3|Max:30|Regex:/^[[:alpha:]]+(?:[-_ ]?[[:alpha:]]+)*$/',
            'lastname' => 'Max:15|Regex:/^([A-Za-z\'. ])+$/',
            'mobile' => 'numeric|digits:10',
            'option' => 'Required|checkOptionAdd|checkOptionUpdate',
        ];

        if (is_array($setRulesForUserCustomFields)) {
            foreach ($setRulesForUserCustomFields as $key => $values) {
                if ($values['mark_as_mandatory'] == 'yes') {
                    $rules[$values['fieldlabel']] = 'Required';
                }
            }
        }
        
        switch (strtolower($excelrowData['option'])) {
            case 'add':
                $rules['password'] = 'Required|Regex:/^[0-9A-Za-z!@#$%_.-]{6,24}$/';
                $rules['usergroup'] = 'checkusergroup';
                $rules['username'] = 'Required|Min:3|Max:93|unique:users|checkUserNameRegex';
                $rules['email'] = 'Required|email|unique:users';
                break;

            case 'update':
                $rules['usergroup'] = 'checkusergroup|checkUsergroupForInvalidUser|checkExistsUsergroupForInvalidUser';
                $rules['username'] = 'Required|Min:3|Max:93|checkUserNameRegex';
                $rules['email'] = 'Required|email';
                break;
        }

        Validator::extend('checkUserNameRegex', function ($attribute, $value, $parameters) {
            if (isset($value) && !empty($value)) {
                (strpos($value, '@') !== false) ? $pattern = "/^(?!.*?[._]{2})[a-zA-Z0-9]+[a-zA-Z0-9(\._\)?-]+[a-zA-Z0-9]+@[a-zA-Z0-9]+([\.]?[a-z]{2,6}+)*$/" : $pattern = "/^[a-zA-Z0-9._]*$/";
                if (preg_match($pattern, $value)) {
                    return true;
                } else {
                    return false;
                }
            }
        });

        Validator::extend('checkusergroup', function ($attribute, $value, $parameters) {
            $flag = true;
            if (is_array($value)) {
                $cnt = count($value);
                $this->notFoundUserGroup = [];
                for ($i = 0; $i < $cnt; $i++) {
                    $val = trim($value[$i]);
                    if (!empty($val) && UserGroup::getUserGroupCount(trim($val)) <= 0) {
                        $flag = false;
                        array_push($this->notFoundUserGroup, $val);
                    }
                }
            }
            return $flag;
        });

        Validator::extend('checkUsergroupForInvalidUser', function ($attribute, $value, $parameters) use ($username) {
            $userGroupNames = array_map('strtolower', $value);
            $flag = true;
            $active_usergroup_user_rel = User::getActiveUsergroupUserRel($username);
            if (!is_null($active_usergroup_user_rel)) {
                $active_usergroup_user_rel = $active_usergroup_user_rel->toArray();
                $active_usergroup_user_rel = array_get($active_usergroup_user_rel, 'relations.active_usergroup_user_rel', []);
            } else {
                $active_usergroup_user_rel = [];
            }
            $user_group_id = array_column(UserGroup::getUserGroupIDByUserGroupName($userGroupNames), 'ugid');
            if (User::getActiveUserCount($username) == 0) {
                if (!empty($active_usergroup_user_rel) && !empty($user_group_id)) {
                    $flag = empty(array_diff($user_group_id, $active_usergroup_user_rel));
                } elseif (!empty($active_usergroup_user_rel) xor !empty($user_group_id)) {
                    $flag = false;
                }
            }
            return $flag;
        });

        Validator::extend('checkExistsUsergroupForInvalidUser', function ($attribute, $value, $parameters) use ($username) {
            $userGroupNames = array_map('strtolower', $value);
            $flag = true;
            $arr_unique_usergroup = array_unique($userGroupNames);
            $arr_duplicates_usergroupnames = array_diff_assoc($userGroupNames, $arr_unique_usergroup);
            if (User::getActiveUserCount($username) == 0 && !empty($arr_duplicates_usergroupnames)) {
                $flag = false;
            }
            return $flag;
        });
        
        Validator::extend('checkOptionAdd', function ($attribute, $value, $parameters) use ($excelrowData) {
            $flag = true;
            if (array_key_exists('password', $excelrowData)) {
                if ((!empty($value)) && (strtolower($value) != "add")) {
                    $flag = false;
                }
            }
            return $flag;
        });

        Validator::extend('checkOptionUpdate', function ($attribute, $value, $parameters) use ($excelrowData) {
            $flag = true;
            if (!array_key_exists('password', $excelrowData)) {
                if ((!empty($value)) && (strtolower($value) != "update")) {
                    $flag = false;
                }
            }
            return $flag;
        });

        $message = [
            'checkUserNameRegex' => 'Symbolic characters not allowed',
            'checkusergroup' => ":attribute does not exist ", //trim($this->notFoundUserGroup,",")."
        ];

        Validator::replacer('checkusergroup', function ($message, $attribute, $rule, $parameters) {
            $noUserGroup = implode(",", $this->notFoundUserGroup);
            return str_replace($attribute, $noUserGroup, $message);
        });
        return $this->customValidate($excelrowData, $rules, $message);
    }

    public function customValidate($input, $rules, $messages = [])
    {
        $validation = Validator::make($input, $rules, $messages);
        if ($validation->fails()) {
            return $validation->messages();
        } else {
            return false;
        }
    }

    /*
    Function :createNewUser
    input    : row data from excel sheet
    Visbiliity: public
    return : uid
    Description: create the new users
     */
    public function createNewUser($rowData, $customFieldDataFromExcel)
    {

        try {
            // check if the username is active in the system
            if (array_key_exists('username', $rowData) && User::checkIfUserNameExists(strtolower($rowData['username'])) <= 0) {
                $rowData['username'] = strtolower($rowData['username']);
                $rowData['email'] = (array_key_exists('email', $rowData)) ? strtolower($rowData['email']) : "";

                $userData_WithoutCustomField = array_except($rowData, array_keys($customFieldDataFromExcel));
                $userData_WithCustomField = array_only($rowData, array_keys($customFieldDataFromExcel));
                $userData_WithCustomField = $this->ReplaceCustomFieldLabel($userData_WithCustomField);
                $uid = User::getInsertUsers($rowData, $userData_WithCustomField, 'true', $rowData['role'], Input::get('timezone', config('app.default_timezone')));

                if (!is_null($uid)) {
                    event(new Registered($uid, $rowData['role']));
                }

                $userGroup = Common::trimArray($rowData['usergroup'], false);
                // collect all the group id(s) of the userGroup
                $ugid = $this->collectUserGroupIds($userGroup);

                if ($ugid['ugidResFlag']) {
                    unset($ugid['ugidResFlag']);
                    if (is_array(($ugid))) {
                        foreach ($ugid as $id) {
                            User::addUserRelation($uid, ['active_usergroup_user_rel'], $id); // add into relation.active_usergroup_user_rel users collection
                            UserGroup::addUserGroupRelation($id, ['active_user_usergroup_rel'], $uid);
                            $role_info = $this->roleService->getRoleDetails(SystemRoles::LEARNER, ['context']);
                            $role_id = array_get($role_info, 'id');
                            $context_info = $this->roleService->getContextDetails(Contexts::PROGRAM, false);
                            $context_id = array_get($context_info, 'id');
                            $usergroup_details = UserGroup::getActiveUserGroupsUsingID($id);

                            foreach ($usergroup_details as $key => $value) {
                                $feed_rel_ids = array_get($value, 'relations.usergroup_feed_rel', []);
                                if (!empty($feed_rel_ids)) {
                                    foreach ($feed_rel_ids as $instance_id) {
                                        event(new EntityEnrollmentThroughUserGroup($uid, UserEntity::PROGRAM, $instance_id, $id));
                                        $this->roleService->mapUserAndRole((int)$uid, $context_id, $role_id, $instance_id);
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $duplicate[] = $rowData['username'];
                return null;
            }
            return $uid;
        } catch (Exception $e) {
            //$e
        }
    }

    /*
    input    : row data from excel sheet
    Visbiliity: public
    return : not null key/value
     */
    public function filterRowData($val)
    {
        $val = trim($val);
        return $val != '';
    }

    /*
    Function :updateImportUser
    input    : row data from excel sheet
    Visbiliity: public
    return : uid
    Description: update the user along
     */
    public function updateImportUser($rowData, $customFieldDataFromExcel)
    {
        try {
            // check for the active username
            $updateStatus = false;
            if (array_key_exists('username', $rowData) && User::checkIfUserNameExists(strtolower($rowData['username'])) > 0) {
                $rowData['username'] = strtolower($rowData['username']);
                $rowData['email'] = (array_key_exists('email', $rowData)) ? strtolower($rowData['email']) : "";
                // if data exists, we update a existing user
                $uid = (int)User::getIdBy($rowData, 'email');

                $userGroup = Common::trimArray($rowData['usergroup'], false);

                $ugid = $this->collectUserGroupIds($userGroup); //collect all the group id(s) of the userGroup

                // query to get the active_user_usergroup_rel from users
                $assignedusergroup = (array)User::getAssignedUsergroups($uid); // get the already usergroup of user from db
                // query to get the active_user_usergroup_rel from usersgroup
                //$assigneduserstogroup =(array) UserGroup::getAssignedUsers($ugid);

                $checkFlagStatus = array_pull($ugid, 'ugidResFlag');
                $diff_of_ids = array_diff($assignedusergroup, $ugid); // get the diff if the usergroup is deleted and uploaded

                $user_now = User::fetchingRelationsArray($uid);
                (is_null($user_now)) ? (User::testRelations($uid, 'active_usergroup_user_rel', 'relations')) : "";

                if (is_array($diff_of_ids) && !empty($diff_of_ids) && count($diff_of_ids) > 0) {
                    // remove the user for the group, as the data coming from excel sheet has changed
                    foreach ($diff_of_ids as $val) {
                        //remove the user from the relation.active_user_usergroup_rel in usergroup collection
                        UserGroup::removeUserGroupRelation($val, ['active_user_usergroup_rel'], $uid);
                    }
                }
                if (empty($ugid) && count($ugid) <= 0) {
                    // if the usergroup column is empty, null the relations.active_usergroup_user_rel in users collection
                    User::emptyUserRelation($uid, 'active_usergroup_user_rel');
                }

                $userData_WithoutCustomField = array_except($rowData, array_keys($customFieldDataFromExcel));
                $userData_WithCustomField = array_only($rowData, array_keys($customFieldDataFromExcel));
                $userData_WithCustomField = $this->ReplaceCustomFieldLabel($userData_WithCustomField);
                $updateStatus = User::updateInsertUsers($uid, $userData_WithoutCustomField, $userData_WithCustomField);
                if ($checkFlagStatus) {
                    // update into relation table
                    User::emptyUserRelation($uid, 'active_usergroup_user_rel'); // null the active_usergroup_user_rel in relations
                    foreach ($ugid as $id) {
                        User::updateUserRelation($uid, 'active_usergroup_user_rel', $id); // update relation.active_usergroup_user_rel in users table
                        UserGroup::addUserGroupRelation($id, ['active_user_usergroup_rel'], $uid); // update relation. active_usergroup_user_rel in userGroup table
                        UserGroup::updateUserGroupRelation($id, 'active_user_usergroup_rel', $uid);
                    }
                    //return $uid;
                }
                return $updateStatus;
            } else {
                // $duplicate[] = $rowData['email'];
                return null;
            }
        } catch (Exception $e) {
            //$e
        }
    }

    /*
    Function :collectUserGroupIds
    input    : row data from excel sheet
    Visbiliity: private
    return : (Array)ugid
    Description: collect the usergroup ids
     */

    private function collectUserGroupIds($userGroup)
    {
        if (is_array($userGroup)) {
            $ugidResFlag = false;
            $ugid = [];

            foreach ($userGroup as $userGroupName) {
                if (UserGroup::getUserGroupCount($userGroupName) > 0) {
                    if (UserGroup::getUserGroupId($userGroupName) > 0) {
                        array_push($ugid, UserGroup::getUserGroupId($userGroupName));
                        $ugidResFlag = true;
                    } else {
                        $ugidResFlag = false;
                    }
                } else {
                    $ugidResFlag = false;
                }
            }
        }
        $ugid['ugidResFlag'] = $ugidResFlag;
        return $ugid;
    }

    public function getBulkImportErrorReport()
    {
        $userxlsreport = Session::get('userxlsreport');
        if ($userxlsreport) {
            $excelObj = new PHPExcel();
            $excelObj->setActiveSheetIndex(0);
            $excelObj->getActiveSheet()->setTitle('Excel upload report');
            $excelObj->getActiveSheet()->fromArray($userxlsreport, null, 'A1');
            $filename = 'UserErrorReport.xls'; //save our workbook as this file name
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); //mime type
            header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name
            header('Cache-Control: max-age=0'); //no cache
            //save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
            //if you want to save it as .XLSX Excel 2007 format
            $objWriter = PHPExcel_IOFactory::createWriter($excelObj, 'Excel2007');
            //force user to download the Excel file without writing it to server's HD
            $objWriter->save('php://output');
        }
        exit;
    }

    public function getUserimportHistory()
    {
        $user = has_admin_permission(ModuleEnum::USER, UserPermission::IMPORT_USERS);

        if ($user == false) {
            return parent::getAdminError($this->theme_path);
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/user.manage_user') => 'usergroupmanagement',
            trans('admin/user.import_history_user') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/user.import_user_history');
        $this->layout->pageicon = 'fa fa-user';
        $this->layout->pagedescription = trans('admin/user.list_of_imported_users_file');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'users_groups')
            ->with('submenu', 'user');
        $this->layout->footer = view('admin.theme.common.footer');
        $import_history = UserimportHistory::getImportedHistory();
        $this->layout->content = view('admin.theme.users.userimporthistory')
            ->with('import_history', $import_history);
    }

    public function getEditUser($uid)
    {
        $user = has_admin_permission(ModuleEnum::USER, UserPermission::EDIT_USER);
        if ($user == false) {
            return parent::getAdminError($this->theme_path);
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/user.manage_users') => 'usergroupmanagement',
            trans('admin/user.edit_user') => '',
        ];

        $session_arr = (is_array(Session::get('session_arr'))) ? Session::get('session_arr') : [];
        if (!is_null(Input::get('filter'))) {
            $filter = Input::get('filter');
        } else {
            $filter = 'ACTIVE';
        }
        $start_serv = 0;
        $length_page_serv = 10;
        $search_ser = '';

        if (!is_null(Input::get('start')) && !is_null(Input::get('limit'))) {
            $start_serv = (int)Input::get('start');
            $length_page_serv = (int)Input::get('limit');
        }
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/user.edit_user');
        $this->layout->pageicon = 'fa fa-user';
        $this->layout->pagedescription = trans('admin/user.edit_user');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'users_groups')
            ->with('submenu', 'user');
        $this->layout->footer = view('admin.theme.common.footer');
        $context_details = $this->roleService->getContextDetails(Contexts::SYSTEM, true);
        $user = User::getUsersUsingID($uid);
        $user = $user[0];
        try {
            $user_system_context_role = $this->roleService->getUserSystemContextRole($user["uid"]);
            $user["role"] = $user_system_context_role->rid;
        } catch (ApplicationException $e) {
            Log::error($e->getMessage());
            $user["role"] = $context_details['roles']['registered-user']['id'];
        }

        /* $CustomField gives us the record from the custom table */
        $CustomField = CustomFields::getUserActiveCustomField($program_type = 'user', $program_sub_type = '', $status = 'ACTIVE');
        $list = [];

        if (is_array($CustomField)) {
            foreach ($CustomField as $key => $val) {
                $list[$val['fieldname']] = null;
            }
        }

        /* $customFieldList variable contains the custom fields of and individual user ie. present in the $user   */
        $customFieldList = array_intersect_key($user, $list);
        $newcustomfield = array_diff_key($list, $customFieldList);

        /* for new user records where we dont have a custom fields in user table
        assign back customFieldList = newcustomfield
         */
        if (empty($customFieldList)) {
            $customFieldList = $newcustomfield;
        }

        if (isset($user['super_admin']) && $user['super_admin'] == true) {
            return parent::getAdminError($this->theme_path);
        }

        $this->layout->content = view('admin.theme.users.edituser')
            ->with(['roles' => $context_details["roles"], 'user' => $user])/*
                                    ->with('filter',$filter)
                                    ->with('start_serv',$start_serv)
                                    ->with('length_page_serv',$length_page_serv)
                                    ->with('search_ser',$search_ser)*/

            ->with('timezones', Timezone::get())->with('frequent_tz', Timezone::frequent())->
            with('CustomField', $CustomField)->with('newcustomfield', $newcustomfield)->with('customFieldList', $customFieldList)
            ->with('session_arr', $session_arr);
    }

    public function postEditUser($uid)
    {
        if (!has_admin_permission(ModuleEnum::USER, UserPermission::EDIT_USER)) {
            return parent::getAdminError($this->theme_path);
        }

        Input::flash();
        $session_arr = [];
        $email = Input::get('email');
        $firstname = Input::get('firstname');
        $lastname = Input::get('lastname');

        Input::merge(['email' => strtolower($email)]);
        Input::merge(['firstname' => trim($firstname)]);
        Input::merge(['lastname' => trim($lastname)]);

        $rules = [
            'firstname' => 'Required|Min:3|Max:30|Regex:/^[[:alpha:]]+(?:[-_ ]?[[:alpha:]]+)*$/',
            'lastname' => 'Max:30|Regex:/^([A-Za-z\'. ])+$/',
            'email' => 'Required|email',
            'mobile' => 'numeric|digits:10',
            //'username' => 'Required|Min:3|Max:35|unique:users|checkUserNameRegex',
            //'username' => 'Required|Min:3|Max:25|unique:users|Regex:/^[[:alnum:]]+(?:[-_ ]?[[:alnum:]]+)*$/',
            'password' => '|Min:6|Max:24|Regex:/^[0-9A-Za-z!@#$%_.-]{6,24}$/|Confirmed',
            'password_confirmation' => 'required_with:password',
            'role' => 'not_in:select',
        ];
        $messages = [
            'password.regex' => trans('admin/user.password_regex_msg'),
            'firstname.required' => trans('admin/user.first_name_required'),
            'firstname.min' => trans('admin/user.first_name_min'),
            'firstname.max' => trans('admin/user.first_name_max'),
            'firstname.regex' => trans('admin/user.first_name_special_characters'),
            'lastname.regex' => trans('admin/user.last_name_special_characters'),
            'lastname.max' => trans('admin/user.last_name_max'),
        ];

        $userCustomField = CustomFields::getUserActiveCustomField($program_type = 'user', $program_sub_type = '');
        if (is_array($userCustomField) && !empty($userCustomField)) {
            foreach ($userCustomField as $values) {
                if ($values['mark_as_mandatory'] == 'yes') {
                    $rules[$values['fieldname']] = 'Required';
                    $messages[$values['fieldname'] . '.required'] = trans(
                        'admin/user.custom_field_required',
                        ['label' => ucwords($values['fieldlabel'])]
                    );
                }

                if ($values['mark_as_mandatory'] == 'yes') {
                    if (empty(Input::get($values['fieldname']))) {
                        array_push($session_arr, $values['fieldname']);
                    }
                }
            }
        }

        Session::put('session_arr', $session_arr);
        $validation = Validator::make(Input::all(), $rules, $messages);
        if ($validation->fails()) {
            return Redirect::back()->withInput()->withErrors($validation);
        } elseif ($validation->passes()) {
            //checking uniqueness of username and email except for the current id
            // User name is not suppose to be changed.
            $user_email = User::pluckUserEmail($uid, Input::get('email'));
            if (!empty($user_email)) {
                $error = str_replace(':attribute', 'email', trans('admin/user.user_error'));

                return Redirect::back()->with('email_exist', $error);
            }
            $user = User::getUserDetailsByID((int)$uid);
            if (Input::get('status') == 'IN-ACTIVE') {
                $usersessions = $user->session_ids;
                if ($usersessions && is_array($usersessions)) {
                    foreach ($usersessions as $sessionid) {
                        if (file_exists(config('session.files') . '/' . $sessionid)) {
                            unlink(config('session.files') . '/' . $sessionid);
                        }
                    }
                }
                $user->update(['session_ids' => []]);
            }
            $username = $user->username; // Resetting the same username since username is not suppose to be changed. The reason why i am changing it here and not changing it in model is the function may be used by some other module.

            $List = $this->customFieldData($program_type = 'user', $program_sub_type = '');
            User::getUpdateUser($uid, Input::all(), $List);
            $this->roleService->updateUserSystemContextRole($uid, Input::get("role"));
            Input::flush();
            $success = trans('admin/user.edit_user_success');

            return redirect('cp/usergroupmanagement')->with('success', $success);
        }
    }

    public function getUserGroups()
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/user.manage_user_group') => 'usergroupmanagement/user-groups',
            trans('admin/user.list_user_group') => '',
        ];
        $viewmode = Input::get('view', 'desktop');
        $relfilter = Input::get('relfilter', 'all');
        $from = Input::get('from', 'none');
        $relid = Input::get('relid', 'none');

        if ($viewmode == 'iframe') {
            $this->layout->breadcrumbs = '';
            $this->layout->pagetitle = '';
            $this->layout->pageicon = '';
            $this->layout->pagedescription = '';
            $this->layout->header = '';
            $this->layout->sidebar = '';
            $this->layout->footer = '';
            $this->layout->content = view('admin.theme.users.listusergroupsiframe')
                ->with('relfilter', $relfilter)
                ->with('from', $from)
                ->with('relid', $relid);
        } else {
            if (!has_admin_permission(ModuleEnum::USER_GROUP, UserGroupPermission::LIST_USER_GROUP)) {
                return parent::getAdminError($this->theme_path);
            }

            if (!is_null(Input::get('filter'))) {
                $filter = Input::get('filter');
            } else {
                $filter = 'ACTIVE';
            }
            $start_serv = 0;
            $length_page_serv = 10;
            if (!is_null(Input::get('start_serv')) && !is_null(Input::get('length_page_serv'))) {
                $start_serv = (int)Input::get('start_serv');
                $length_page_serv = (int)Input::get('length_page_serv');
            }
            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = trans('admin/user.manage_user_group');
            $this->layout->pageicon = 'fa fa-group';
            $this->layout->pagedescription = trans('admin/user.list_user_group');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'users_groups')
                ->with('submenu', 'group');
            $this->layout->footer = view('admin.theme.common.footer');
            $this->layout->content = view('admin.theme.users.listusergroups')
                ->with('filter', $filter)
                ->with('start_serv', $start_serv)
                ->with('length_page_serv', $length_page_serv);
        }
    }

    public function getUsergroupListAjax()
    {
        $start = 0;
        $limit = 10;
        $viewmode = Input::get('view', 'desktop');
        $from = $relid = null;
        $has_permission = false;
        $filter_params = [];

        switch ($viewmode) {
            case "desktop":
                    $has_permission = has_admin_permission(
                        ModuleEnum::USER_GROUP,
                        UserGroupPermission::LIST_USER_GROUP
                    );
                break;
            case "iframe":
                $from = Input::get('from', 'none');
                $relid = Input::get('relid', 'none');
                switch ($from) {
                    case 'quiz':
                        $assign_user_group_quiz_permission_data_with_flag = $this->roleService->hasPermission(
                            Auth::user()->uid,
                            ModuleEnum::ASSESSMENT,
                            PermissionType::ADMIN,
                            AssessmentPermission::QUIZ_ASSIGN_USER_GROUP,
                            null,
                            null,
                            true
                        );

                        $quiz_assign_user_group_permission_data =
                            get_permission_data($assign_user_group_quiz_permission_data_with_flag);

                        $has_permission = is_element_accessible(
                            $quiz_assign_user_group_permission_data,
                            ElementType::ASSESSMENT,
                            $relid
                        );

                        if (!has_system_level_access($quiz_assign_user_group_permission_data)) {
                            $filter_params['assignable_user_group_ids'] =
                                get_user_group_ids($quiz_assign_user_group_permission_data);
                        }
                        break;
                    case 'event':
                        $assign_user_group_event_permission_data_with_flag = $this->roleService->hasPermission(
                            Auth::user()->uid,
                            ModuleEnum::EVENT,
                            PermissionType::ADMIN,
                            EventPermission::ASSIGN_USER_GROUP,
                            null,
                            null,
                            true
                        );

                        $event_assign_user_group_permission_data =
                            get_permission_data($assign_user_group_event_permission_data_with_flag);

                        $has_permission = $has_permission = is_element_accessible(
                            $event_assign_user_group_permission_data,
                            ElementType::EVENT,
                            $relid
                        );

                        if (!has_system_level_access($event_assign_user_group_permission_data)) {
                            $filter_params['assignable_user_group_ids'] =
                                get_user_group_ids($event_assign_user_group_permission_data);
                        }
                        break;
                    case "user":
                        $has_permission = has_admin_permission(
                            ModuleEnum::USER_GROUP,
                            UserGroupPermission::USER_GROUP_ASSIGN_USER
                        );
                        break;
                    case "contentfeed":
                            $has_permission = $this->roleService->hasPermission(
                                $this->request->user()->uid,
                                ModuleEnum::CHANNEL,
                                PermissionType::ADMIN,
                                ChannelPermission::CHANNEL_ASSIGN_USER_GROUP,
                                Contexts::PROGRAM,
                                $relid
                            );
                        break;
                    case "announcement":
                        $assign_user_group_announcement_permission_data_with_flag = $this->roleService->hasPermission(
                            Auth::user()->uid,
                            ModuleEnum::ANNOUNCEMENT,
                            PermissionType::ADMIN,
                            AnnouncementPermission::ASSIGN_USERGROUP,
                            null,
                            null,
                            true
                        );
                        $has_permission = get_permission_flag($assign_user_group_announcement_permission_data_with_flag);
                        $annoucement_assign_permission_data = get_permission_data($assign_user_group_announcement_permission_data_with_flag);
   
                        if (!has_system_level_access($annoucement_assign_permission_data)) {
                            $filter_params['assignable_user_group_ids'] = get_user_group_ids($annoucement_assign_permission_data);
                        }
                        break;
                    case 'survey':
                        $assign_user_group_survey_permission_data_with_flag = $this->roleService->hasPermission(
                            Auth::user()->uid,
                            ModuleEnum::SURVEY,
                            PermissionType::ADMIN,
                            SurveyPermission::SURVEY_ASSIGN_USER_GROUP,
                            null,
                            null,
                            true
                        );

                        $survey_assign_user_group_permission_data =
                            get_permission_data($assign_user_group_survey_permission_data_with_flag);

                        $has_permission = $has_permission = is_element_accessible(
                            $survey_assign_user_group_permission_data,
                            ElementType::SURVEY,
                            $relid
                        );

                        if (!has_system_level_access($survey_assign_user_group_permission_data)) {
                            $filter_params['assignable_user_group_ids'] =
                                get_user_group_ids($survey_assign_user_group_permission_data);
                        }
                        break;
                    case 'assignment':
                        $assign_user_group_assignment_permission_data_with_flag = $this->roleService->hasPermission(
                            Auth::user()->uid,
                            ModuleEnum::ASSIGNMENT,
                            PermissionType::ADMIN,
                            AssignmentPermission::ASSIGNMENT_ASSIGN_USER_GROUP,
                            null,
                            null,
                            true
                        );

                        $assignment_assign_user_group_permission_data =
                            get_permission_data($assign_user_group_assignment_permission_data_with_flag);

                        $has_permission = $has_permission = is_element_accessible(
                            $assignment_assign_user_group_permission_data,
                            ElementType::ASSIGNMENT,
                            $relid
                        );

                        if (!has_system_level_access($assignment_assign_user_group_permission_data)) {
                            $filter_params['assignable_user_group_ids'] =
                                get_user_group_ids($assignment_assign_user_group_permission_data);
                        }
                        break;
                }
                break;
        }

        if (!$has_permission) {
            return response()->json(
                [
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ]
            );
        }

        $search = Input::get('search');
        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '1') {
                $orderByArray = ['usergroup_name' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '2') {
                $orderByArray = ['usergroup_email' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '3') {
                $orderByArray = ['parent_usergroup' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '4') {
                $orderByArray = ['created_at' => $order_by[0]['dir']];
            }
        }
        if (isset($search['value'])) {
            $searchKey = $search['value'];
        }

        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }

        $filter = Input::get('filter');
        if (!in_array($filter, ['ACTIVE', 'IN-ACTIVE'])) {
            $filter = 'ALL';
        }

        $relfilter = Input::get('relfilter', 'assigned');

        if ($viewmode == 'iframe' &&
            in_array($relfilter, ['assigned', 'nonassigned']) &&
            in_array($from, ['course', 'contentfeed', 'user', 'quiz', 'announcement', 'dams', 'event', 'questionbank', 'survey', 'assignment'])
            && preg_match('/^\d+$/', $relid)
        ) {
            if ((Input::get("disable_filter", null) !== null) && (Input::get("disable_filter") === "TRUE")) {
                $relid = "none";
                $relfilter = "ALL";
            }

            $relinfo = [$from => $relid];
            $filteredRecords = UserGroup::getUserGroupsCount($relfilter, $searchKey, $relinfo, $filter_params);
            $filtereddata = UserGroup::getUserGroupsWithPagination($relfilter, $start, $limit, $orderByArray, $searchKey, $relinfo, $filter_params);
        } else {
            $filteredRecords = UserGroup::getUserGroupsCount($filter, $searchKey);
            $filtereddata = UserGroup::getUserGroupsWithPagination($filter, $start, $limit, $orderByArray, $searchKey);
        }

        $usergroup_edit =  has_admin_permission(ModuleEnum::USER_GROUP, UserGroupPermission::EDIT_USER_GROUP);
        $usergroup_delete = has_admin_permission(ModuleEnum::USER_GROUP, UserGroupPermission::DELETE_USER_GROUP);
        $usergroup_view = has_admin_permission(ModuleEnum::USER_GROUP, UserGroupPermission::VIEW_USER_GROUP);
        $assign_user = has_admin_permission(ModuleEnum::USER_GROUP, UserGroupPermission::USER_GROUP_ASSIGN_USER);
        $assign_contentfeed = has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::CHANNEL_ASSIGN_USER_GROUP);

        $totalRecords = $filteredRecords;
        $dataArr = [];
        foreach ($filtereddata as $key => $value) {
            if ($usergroup_edit == false) {
                $edit = '';
            } else {
                $edit = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/manageweb.action_edit') . '" href="' . URL::to('cp/usergroupmanagement/edituser-group/' . $value->ugid) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-edit"></i></a>';
            }

            if ($usergroup_delete == false) {
                $delete = '';
            } else {
                $delete = '<a class="btn btn-circle show-tooltip deleteusergroup" title="' . trans('admin/manageweb.action_delete') . '" href="' . URL::to('cp/usergroupmanagement/deleteuser-group/' . $value->ugid) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-trash-o"></i></a>';
            }

            if ($usergroup_view == false) {
                $view = '';
            } else {
                $view = '<a class="btn btn-circle show-tooltip viewusergroup" title="' . trans('admin/manageweb.action_view') . '" href="' . URL::to('/cp/usergroupmanagement/usergroup-details/' . $value->ugid) . '" ><i class="fa fa-eye"></i></a>';
            }
            
            $temparr = [
                '<input type="checkbox" value="' . $value->ugid . '">',
                $value->usergroup_name,
                $value->usergroup_email,
                Timezone::convertFromUTC($value->created_at, Auth::user()->timezone, config('app.date_format')),
                $value->status,
                $view . $edit . $delete,
            ];
            if ($viewmode != 'iframe') {
                $uids = UserGroup::getAssignedUsers($value->ugid);
                $prgm_ids = $this->userGroupService->getUserGroupChannels($value->ugid);

                if ($assign_user == false) {
                    $userCount = '<a class="badge badge-grey" title="' . trans('admin/user.no_user_permission') . '" ><i class="fa fa-times"></i></a>';
                } elseif ($value->status == 'IN-ACTIVE') {
                    $userCount = '<a class="badge badge-grey" title="' . trans('admin/user.cannot_assign_user_to_inactive_usergroup') . '"><i class="fa fa-times"></i></a>';
                } else {
                    $userCount = "<a href='" . URL::to('/cp/usergroupmanagement?filter=ACTIVE&view=iframe&from=usergroup&relid=' . $value->ugid) . "' class='usergrouprel badge badge-grey' data-key='" . $value->ugid . "' data-info='user' data-text='Assign  user(s)  to <b>" . htmlentities('"' . $value->usergroup_name . '"', ENT_QUOTES) . "</b>' data-json=''>" . 0 . '</a>';
                    if (isset($uids) && !empty($uids) && $uids != 'default') {
                        $userCount = "<a href='" . URL::to('/cp/usergroupmanagement?filter=ACTIVE&view=iframe&from=usergroup&relid=' . $value->ugid) . "' class='usergrouprel badge badge-success' data-key='" . $value->ugid . "' data-info='user' data-text='Assign  user(s)  to <b>" . htmlentities('"' . $value->usergroup_name . '"', ENT_QUOTES) . "</b>' data-json='" . json_encode($uids) . "'>" . count($uids) . '</a>';
                    }
                }

                if ($assign_contentfeed == false) {
                    $contentFeedCount = '<a class="badge badge-grey" title="' . trans('admin/user.u_dont_have_permi_to_assign_channel') . '" ><i class="fa fa-times"></i></a>';
                } elseif ($value->status == 'IN-ACTIVE') {
                    $contentFeedCount = '<a class="badge badge-grey" title="' . trans('admin/user.cant_assign_channels_to_inactive_usergroup') . '" ><i class="fa fa-times"></i></a>';
                } else {
                    $contentFeedCount = "<a href='" . URL::to('/cp/contentfeedmanagement?filter=ACTIVE&view=iframe&subtype=single&from=usergroup&relid=' . $value->ugid) . "' class='usergrouprel badge badge-grey' data-key='" . $value->ugid . "' data-info='contentfeed' data-text='Assign " . trans('admin/user.channel') . ' to <b>' . htmlentities('"' . $value->usergroup_name . '"', ENT_QUOTES) . "</b>' data-json=''>" . 0 . '</a>';
                    if (isset($prgm_ids) && !empty($prgm_ids) && $prgm_ids != 'default') {
                        $contentFeedCount = "<a href='" . URL::to('/cp/contentfeedmanagement?filter=ACTIVE&view=iframe&subtype=single&from=usergroup&relid=' . $value->ugid) . "' class='usergrouprel badge badge-success' data-key='" . $value->ugid . "' data-info='contentfeed' data-text='Assign " . trans('admin/user.channel') . ' to <b>' . htmlentities('"' . $value->usergroup_name . '"', ENT_QUOTES) . "</b>' data-json='" . json_encode($prgm_ids) . "'>" . count($prgm_ids) . '</a>';
                    }
                }
                array_splice($temparr, 5, 0, [$userCount, $contentFeedCount]);
            } else {
                array_pop($temparr);
            }
            $dataArr[] = $temparr;
        }
        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr,
        ];

        return response()->json($finaldata);
    }

    public function getUsergroupDetails($ugid = null)
    {
        if (!has_admin_permission(ModuleEnum::USER_GROUP, UserGroupPermission::VIEW_USER_GROUP)) {
            return parent::getAdminError($this->theme_path);
        }

        $usergroup = UserGroup::getUserGroupsUsingID($ugid);
        if (empty($usergroup) || !$ugid) {
            $msg = trans('admin/user.missing_usergroup');

            return redirect('/cp/usergroupmanagement/user-groups')
                ->with('error', $msg);
        }
        $usergroup = $usergroup[0];

        $usergroup['created_at'] = Timezone::convertFromUTC($usergroup['created_at'], Auth::user()->timezone, 'Y-m-d H:i:s');

        return view('admin.theme.users.usergroupdetails')->with('usergroup', $usergroup);
    }

    public function getAdduserGroup()
    {
        if (!has_admin_permission(ModuleEnum::USER_GROUP, UserGroupPermission::ADD_USER_GROUP)) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/user.manage_user_group') => 'usergroupmanagement/user-groups',
            trans('admin/user.add_user_group') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/user.add_user_group');
        $this->layout->pageicon = 'fa fa-group';
        $this->layout->pagedescription = trans('admin/user.add_user_group');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'users_groups')
            ->with('submenu', 'group');
        $this->layout->footer = view('admin.theme.common.footer');
        $usergroups = UserGroup::getAllUserGroupNames();
        $this->layout->content = view('admin.theme.users.addusergroup')->with('usergroups', $usergroups);
    }

    public function postAdduserGroup()
    {
        if (!has_admin_permission(ModuleEnum::USER_GROUP, UserGroupPermission::ADD_USER_GROUP)) {
            return parent::getAdminError($this->theme_path);
        }

        Input::flash();
        $usergroup_name = Input::get('usergroup_name');
        $usergroup_desc = Input::merge(['description' => html_entity_decode(Input::get('description'))]);
        Input::merge(['usergroup_name' => strtolower($usergroup_name)]);
        $rules = [
            'usergroup_name' => 'Required|unique:usergroups,ug_name_lower|Min:3|Max:60|Regex:/^([A-Za-z0-9 :_()+-])+$/',
            'usergroup_email' => 'email',
            'description' => 'Min:0|Max:250',
        ];

        $messages = [];
        $messages += [
            'usergroup_name.regex' => trans('admin/user.user_group_name_regex_msg'),
          ];
          
        $validation = Validator::make(Input::all(), $rules, $messages);
        if ($validation->fails()) {
            Input::merge(['usergroup_name' => $usergroup_name]);

            return redirect('cp/usergroupmanagement/adduser-group')->withInput()->withErrors($validation);
        } elseif ($validation->passes()) {
            Input::merge(['usergroup_name' => $usergroup_name]);
            Input::merge(['ug_name_lower' => strtolower($usergroup_name)]);
            Input::merge(['description' => htmlentities(html_entity_decode(Input::get('description')), ENT_QUOTES, "UTF-8")]);
            
            $ugid = UserGroup::getInsertUserGroup(Input::all());
            Input::flush();

            return redirect('cp/usergroupmanagement/manageuser-group/' . $ugid)
                ->with('success', trans('admin/user.addusergroup_success'));
        }
    }

    public function getManageuserGroup($ugid = null)
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/user.manage_user_group') => 'usergroupmanagement/user-groups',
            trans('admin/user.add_usergroup_success') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $asset = UserGroup::getUserGroupsUsingID($ugid);
        if (empty($asset) || !$ugid) {
            $msg = trans('admin/user.missing_params');

            return redirect('/cp/usergroupmanagement/user-groups')
                ->with('error', $msg);
        }

        $asset = $asset[0];
        $this->layout->pagetitle = trans('admin/user.manage_user_group');
        $this->layout->pageicon = 'fa fa-group';
        $this->layout->pagedescription = trans('admin/user.manage_user_group');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'users_groups')
            ->with('submenu', 'group');
        $this->layout->footer = view('admin.theme.common.footer');
        $this->layout->content = view('admin.theme.users.manageusergroup')->with('ugid', $ugid)->with('asset', $asset);
    }

    public function getDeleteuserGroup($ugid)
    {
        if (!has_admin_permission(ModuleEnum::USER_GROUP, UserGroupPermission::DELETE_USER_GROUP)) {
            return parent::getAdminError($this->theme_path);
        }

        $res = ['success' => ''];
        if (UserGroup::getDelete($ugid) == false) {
            $res = ['error' => trans('admin/user.cannot_delete_ug')];
        } else {
            $res = ['success' => trans('admin/user.usergroup_delete')];
        }

        $start_serv = (int)Input::get('start', 0);
        $length_page_serv = (int)Input::get('limit', 10);
        $search = Input::get('search', '');
        $order_by = Input::get('order_by', '4 desc');
        $filter = Input::get('filter', 'all');

        $totalRecords = UserGroup::getUserGroupsCount($filter, $search);
        if ($totalRecords <= $start_serv) {
            $start_serv -= $length_page_serv;
            if ($start_serv < 0) {
                $start_serv = 0;
            }
        }

        return redirect('cp/usergroupmanagement/user-groups?start=' . $start_serv . '&limit=' . $length_page_serv . '&filter=' . $filter . '&search=' . $search . '&order_by=' . $order_by)
            ->with(key($res), $res[key($res)]);
    }

    public function postBulkUsergroupDelete()
    {
        if (!has_admin_permission(ModuleEnum::USER_GROUP, UserGroupPermission::DELETE_USER_GROUP)) {
            return parent::getAdminError($this->theme_path);
        }

        $keys = Input::get('ugids');
        if (!$keys) {
            $msg = trans('admin/user.missing_params');

            return redirect('/cp/usergroupmanagement/user-groups')
                ->with('error', $msg);
        }
        $keys = rtrim($keys, ',');
        $keys = explode(',', $keys);
        $error = 0;
        foreach ($keys as $ugid) {
            if (UserGroup::getDelete($ugid) == false) {
                $error = 1;
            }
        }
        $res = ['success' => ''];
        if ($error) {
            $res = ['warning' => trans('admin/user.cannot_delete_ug_warning')];
        } else {
            $res = ['success' => trans('admin/user.usergroup_bulk_delete')];
        }

        return redirect('/cp/usergroupmanagement/user-groups')
            ->with(key($res), $res[key($res)]);
    }

    public function getEdituserGroup($ugid)
    {
        if (!has_admin_permission(ModuleEnum::USER_GROUP, UserGroupPermission::EDIT_USER_GROUP)) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/user.manage_user_group') => 'usergroupmanagement/user-groups',
            trans('admin/user.edit_user_group') => '',
        ];
        if (!is_null(Input::get('filter'))) {
            $filter = Input::get('filter');
        } else {
            $filter = 'ACTIVE';
        }
        $start_serv = 0;
        $length_page_serv = 10;

        if (!is_null(Input::get('start')) && !is_null(Input::get('limit'))) {
            $start_serv = (int)Input::get('start');
            $length_page_serv = (int)Input::get('limit');
        }
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/user.edit_user_group');
        $this->layout->pageicon = 'fa fa-group';
        $this->layout->pagedescription = trans('admin/user.edit_user_group');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'users_groups')
            ->with('submenu', 'group');
        $this->layout->footer = view('admin.theme.common.footer');
        $usergroup = UserGroup::getUserGroupsUsingID($ugid);
        $usergroup = $usergroup[0];
        $usergroup_names = UserGroup::getAllUserGroupNames($ugid);

        $this->layout->content = view('admin.theme.users.editusergroup')
            ->with('filter', $filter)
            ->with('start_serv', $start_serv)
            ->with('length_page_serv', $length_page_serv)
            ->with(['usergroup' => $usergroup, 'usergroup_names' => $usergroup_names]);
    }

    public function postEdituserGroup($ugid)
    {
        if (!has_admin_permission(ModuleEnum::USER_GROUP, UserGroupPermission::EDIT_USER_GROUP)) {
            return parent::getAdminError($this->theme_path);
        }

        Input::flash();
        $usergroup_desc = Input::merge(['description' => html_entity_decode(Input::get('description'))]);
        $rules = [
            'usergroup_name' => 'Required|Min:3|Max:60|Regex:/^([a-zA-Z0-9 :_()+-])+$/',
            'usergroup_email' => 'email',
            'description' => 'Min:0|Max:250',
        ];
 
        $messages = [];
        $messages += [
            'usergroup_name.regex' => trans('admin/user.user_group_name_regex_msg'),
         ];

        $validation = Validator::make(Input::all(), $rules, $messages);

        if ($validation->fails()) {
            return Redirect::back()->withInput()->withErrors($validation);
        } elseif ($validation->passes()) {
            //checking uniqueness of username and email except for the current id
            $group_name = UserGroup::pluckGroupNameLower($ugid, Input::get('usergroup_name'));
            $group_email = UserGroup::pluckGroupEmail($ugid, Input::get('usergroup_email'));
            if (!empty($group_name)) {
                $error = str_replace(':attribute', 'name', trans('admin/user.usergroup_error'));

                return Redirect::back()->with('name_exist', $error);
            } elseif (!empty($group_email)) {
                $error = str_replace(':attribute', 'email', trans('admin/user.usergroup_error'));

                return Redirect::back()->with('email_exist', $error);
            }

            $usergroup_name = Input::get('usergroup_name');
            Input::merge(['ug_name_lower' => strtolower($usergroup_name)]);
            Input::merge(['description' => htmlentities(html_entity_decode(Input::get('description')), ENT_QUOTES, "UTF-8")]);
            UserGroup::getUpdateUserGroup($ugid, Input::all());
            Input::flush();
            $success = trans('admin/user.edit_user_group_success');

            return redirect('cp/usergroupmanagement/user-groups')->with('success', $success);
        }
    }

    public function postAssignUser($action = null, $key = null)
    {
        $msg = null;

        switch ($action) {
            case "usergroup":
                $has_permission = has_admin_permission(
                    ModuleEnum::USER_GROUP,
                    UserGroupPermission::USER_GROUP_ASSIGN_USER
                );

                if (!$has_permission) {
                    $msg = trans("admin/user.no_permission_to_add_users_to_user_group");
                    return response()->json(['flag' => 'error', 'message' => $msg]);
                }
                break;
            case "contentfeed":
                $has_permission = has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::CHANNEL_ASSIGN_USER);

                if (!$has_permission) {
                    $msg = trans("admin/user.no_permission_to_assign_user_to_channel");
                    return response()->json(['flag' => 'error', 'message' => $msg]);
                }
                break;
        }

        $asset = User::getActiveUserUsingID($key);
        $role_info = $this->roleService->getRoleDetails(SystemRoles::LEARNER, ['context']);
        $context_info = $this->roleService->getContextDetails(Contexts::PROGRAM, false);
        $role_id = array_get($role_info, 'id', '');
        $context_id = array_get($context_info, 'id', '');
        $ids = Input::get('ids');
        $empty = Input::get('empty');
        if ($ids) {
            $ids = explode(',', $ids);
        } else {
            $ids = [];
        }
        if (!$empty) {
            if (empty($asset) || !$key || !in_array($action, ['usergroup', 'contentfeed']) || !is_array($ids) || empty($ids)) {
                $msg = trans('admin/user.missing_params');

                return response()->json(['flag' => 'error', 'message' => $msg]);
            }
        }
        if ($action == 'contentfeed') {
            $arrname = 'user_feed_rel';
            $msg = trans('admin/program.channel_assigned_success');
        }
        if ($action == 'usergroup') {
            $arrname = 'active_usergroup_user_rel';
            $msg = trans('admin/user.usergroup_assigned');
        }
        $asset = $asset[0];
        $deleted = [];
        if (isset($asset['relations'])) {
            if ($action == 'contentfeed' && isset($asset['relations']['user_feed_rel'])) {
                // Code to remove relations from program collection
                $deleted = array_diff($asset['relations']['user_feed_rel'], $ids);
                $ids = array_diff($ids, $asset['relations']['user_feed_rel']);
                foreach ($deleted as $value1) {
                    try {
                        event(new EntityUnenrollmentByAdminUser($asset["uid"], UserEntity::PROGRAM, $value1));
                        $this->roleService->unmapUserAndRole((int)$asset['uid'], $context_id, $value1);
                    } catch (UserNotFoundException $e) {
                        Log::info(trans('admin/user.user_not_found', ['id' => (int)$asset['uid']]));
                    }
                    Program::removeFeedRelation($value1, ['active_user_feed_rel'], (int)$asset['uid']);
                    TransactionDetail::updateStatusByLevel('user', (int)$asset['uid'], (int)$value1, ['status' => 'inactive']);
                    // Also inactive the user transaction

                    $program_title = Program::where('program_id', (int)$value1)->value('program_title');
                    // Send Notifications to the user
                    if (Config::get('app.notifications.user.unassign_feed')) {
                        $notif_msg = trans('admin/notifications.unassign_feed_user', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'feed' => $program_title]);
                        NotificationLog::getInsertNotification([(int)$key], trans('admin/user.user'), $notif_msg);
                    }
                    if (config('elastic.service')) {
                        event(new UsersAssigned($value1));
                    }
                }
            }
            if ($action == 'usergroup' && isset($asset['relations']['active_usergroup_user_rel'])) {
                // Code to remove relations from usergroup collection
                $deleted = array_diff($asset['relations']['active_usergroup_user_rel'], $ids);
                $ids = array_diff($ids, $asset['relations']['active_usergroup_user_rel']);
                foreach ($deleted as $value2) {
                    try {
                        $usergroup_info = $this->userGroupService->getUserGroupDetails($value2);
                        $usergroup_rel = $usergroup_info->relations;
                        $feed_rel_ids = array_get($usergroup_rel, 'usergroup_feed_rel', '');
                    } catch (UserGroupNotFoundException $e) {
                        Log::info(trans('admin/user.usergroup_not_found', ['id' => $value2]));
                    }

                    if (!empty($feed_rel_ids)) {
                        foreach ($feed_rel_ids as $instance_id) {
                            event(new EntityUnenrollmentThroughUserGroup(
                                $asset["uid"],
                                UserEntity::PROGRAM,
                                $instance_id,
                                $value2
                            ));

                            $this->roleService->unmapUserAndRole((int)$asset['uid'], $context_id, $instance_id);
                        }
                    }

                    UserGroup::removeUserGroupRelation($value2, ['active_user_usergroup_rel'], (int)$asset['uid']);
                    $groupname = UserGroup::where('ugid', (int)$value2)->value('usergroup_name');
                    // Send Notifications to the user
                    if (Config::get('app.notifications.user.unassign_usergroup')) {
                        $notif_msg = trans('admin/notifications.unassign_usergroup', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'groupname' => $groupname]);
                        NotificationLog::getInsertNotification([(int)$key], trans('admin/user.user'), $notif_msg);
                    }
                    if (config('elastic.service')) {
                        event(new UserGroupAssigned($value2));
                    }
                }
            }
        }
        $ids = array_values($ids); //  This is to reset the index. [Problem: when using array_diff, the keys gets altered which converts normal array to associative array.]
        $deleted = array_values($deleted); //  This is to reset the index. [Problem: when using array_diff, the keys gets altered which converts normal array to associative array.]
        foreach ($ids as &$value) {
            $value = (int)$value;
            $now = time();
            if ($action == 'contentfeed') {
                Program::addFeedRelation($value, ['active_user_feed_rel'], $asset['uid']);

                event(new EntityEnrollmentByAdminUser($asset["uid"], UserEntity::PROGRAM, $value));

                $this->roleService->mapUserAndRole($asset['uid'], $context_id, $role_id, $value);

                $trans_id = Transaction::uniqueTransactionId();
                $programdetails = Program::getProgramDetailsByID($value)->toArray();
                $email = '';
                if (isset($asset['email'])) {
                    $email = $asset['email'];
                }
                $transaction = [
                    'DAYOW' => date('l', $now),
                    'DOM' => (int)date('j', $now),
                    'DOW' => (int)date('w', $now),
                    'DOY' => (int)date('z', $now),
                    'MOY' => (int)date('n', $now),
                    'WOY' => (int)date('W', $now),
                    'YEAR' => (int)date('Y', $now),
                    'trans_level' => 'user',
                    'id' => (int)$asset['uid'],
                    'created_date' => time(),
                    'email' => $email,
                    'trans_id' => (int)$trans_id,
                    'full_trans_id' => 'ORD' . sprintf('%07d', (string)$trans_id),
                    'access_mode' => 'assigned_by_admin',
                    'added_by' => Auth::user()->username,
                    'added_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                    'created_at' => time(),
                    'updated_at' => time(),
                    'type' => 'subscription',
                    'status' => 'COMPLETE', // This is transaction status
                ];

                $transaction_details = [
                    'trans_level' => 'user',
                    'id' => (int)$asset['uid'],
                    'trans_id' => (int)$trans_id,
                    'program_id' => $programdetails['program_id'],
                    'program_slug' => $programdetails['program_slug'],
                    'type' => 'content_feed',
                    'program_title' => $programdetails['program_title'],
                    'duration' => [ // Using the same structure from duration master
                        'label' => 'Forever',
                        'days' => 'forever',
                    ],
                    'start_date' => '', // Empty since the duration is forever
                    'end_date' => '', // Empty since the duration is forever
                    'created_at' => time(),
                    'updated_at' => time(),
                    'status' => 'COMPLETE',
                ];
                // Add record to user transaction table
                Transaction::insert($transaction);
                TransactionDetail::insert($transaction_details);

                // Send Notifications to the user
                if (Config::get('app.notifications.user.assign_feed')) {
                    $notif_msg = trans('admin/notifications.assign_feed_user', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'feed' => $programdetails['program_title']]);
                    NotificationLog::getInsertNotification([(int)$key], trans('admin/user.user'), $notif_msg);
                }
                if (config('elastic.service')) {
                    event(new UsersAssigned($value));
                }
            } elseif ($action == 'usergroup') {
                try {
                    $usergroup_info = $this->userGroupService->getUserGroupDetails($value);
                    $usergroup_rel = $usergroup_info->relations;
                    $feed_rel_ids = array_get($usergroup_rel, 'usergroup_feed_rel', '');
                } catch (UserGroupNotFoundException $e) {
                    Log::info(trans('admin/user.usergroup_not_found', ['id' =>  $value]));
                }
                
                if (!empty($feed_rel_ids)) {
                    foreach ($feed_rel_ids as $instance_id) {
                        event(new EntityEnrollmentThroughUserGroup(
                            $asset["uid"],
                            UserEntity::PROGRAM,
                            $instance_id,
                            $value
                        ));

                        $this->roleService->mapUserAndRole((int)$asset['uid'], $context_id, $role_id, $instance_id);
                    }
                }

                UserGroup::addUserGroupRelation($value, ['active_user_usergroup_rel'], (int)$asset['uid']);

                $groupname = UserGroup::where('ugid', (int)$value)->value('usergroup_name');
                // Send Notifications to the user
                if (Config::get('app.notifications.user.assign_usergroup')) {
                    $notif_msg = trans('admin/notifications.assign_usergroup', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'groupname' => $groupname]);
                    NotificationLog::getInsertNotification([(int)$key], trans('admin/user.user'), $notif_msg);
                }
                if (config('elastic.service')) {
                    event(new UserGroupAssigned($value));
                }
            }
        }
        if (!empty($ids)) {
            User::updateUserRelation($key, $arrname, $ids);
        }
        User::where('uid', (int)$key)->pull('relations.' . $arrname, $deleted, true);
        if (!empty($deleted)) {
            User::where('uid', (int)$key)->update(['updated_at' => time()]);
        }

        return response()->json(['flag' => 'success', 'message' => $msg]);
    }

    public function postAssignUsergroup($action = null, $key = null)
    {
        switch ($action) {
            case "user":
                $has_permission = has_admin_permission(
                    ModuleEnum::USER_GROUP,
                    UserGroupPermission::USER_GROUP_ASSIGN_USER
                );

                if (!$has_permission) {
                    $msg = trans("admin/user.no_permission_to_add_users_to_user_group");
                    return response()->json(['flag' => 'error', 'message' => $msg]);
                }
                break;
            case "contentfeed":
                    $has_permission = has_admin_permission(
                        ModuleEnum::CHANNEL,
                        ChannelPermission::CHANNEL_ASSIGN_USER_GROUP
                    );

                if (!$has_permission) {
                    $msg = trans("admin/user.no_permission_to_assign_channels_to_user_group");
                    return response()->json(['flag' => 'error', 'message' => $msg]);
                }
                break;
        }

        $asset = UserGroup::getActiveUserGroupsUsingID($key);

        $role_info = $this->roleService->getRoleDetails(SystemRoles::LEARNER, ['context']);
        $context_info = $this->roleService->getContextDetails(Contexts::PROGRAM, false);
        $role_id = array_get($role_info, 'id', '');
        $context_id = array_get($context_info, 'id', '');
        $relations = array_get(head($asset), 'relations', '');
        $users_ids = array_get($relations, 'active_user_usergroup_rel', '');
        $instance_ids = array_get($asset, '0.relations.usergroup_feed_rel', '');
        $package_ids =  array_get($asset, '0.package_ids', '');
        $ids = Input::get('ids');
        $empty = Input::get('empty');
        if ($ids) {
            $ids = explode(',', $ids);
        } else {
            $ids = [];
        }
        if (!$empty) {
            if (empty($asset) || !$key || !in_array($action, ['user', 'contentfeed']) || !is_array($ids) || empty($ids)) {
                $msg = trans('admin/user.missing_params');

                return response()->json(['flag' => 'error', 'message' => $msg]);
            }
        }
        if ($action == 'contentfeed') {
            $arrname = 'usergroup_feed_rel';
            $msg = trans('admin/program.channel_assigned_success');
        }
        if ($action == 'user') {
            $arrname = 'active_user_usergroup_rel';
            $msg = trans('admin/user.user_assigned');
        }
        $asset = $asset[0];
        $deleted = [];
        if (isset($asset['relations'])) {
            if ($action == 'contentfeed' && isset($asset['relations']['usergroup_feed_rel'])) {
                // Code to remove relations from program collection
                $deleted = array_diff($asset['relations']['usergroup_feed_rel'], $ids);
                $ids = array_diff($ids, $asset['relations']['usergroup_feed_rel']);
                //$users=Usergroup::where('ugid', (int)$key)->value('active_user_usergroup_rel');
                foreach ($deleted as $value1) {
                    if (!empty($users_ids)) {
                        foreach ($users_ids as $user_id) {
                            try {
                                event(new EntityUnenrollmentThroughUserGroup(
                                    $user_id,
                                    UserEntity::PROGRAM,
                                    $value1,
                                    $asset["ugid"]
                                ));

                                $this->roleService->unmapUserAndRole((int)$user_id, $context_id, $value1);
                            } catch (UserNotFoundException $e) {
                                Log::info(trans('admin/user.user_not_found', ['id' => $user_id]));
                            }
                        }
                    }
                    Program::removeFeedRelation($value1, ['active_usergroup_feed_rel'], (int)$asset['ugid']);
                    TransactionDetail::updateStatusByLevel('usergroup', (int)$asset['ugid'], (int)$value1, ['status' => 'inactive']);
                    // Also inactive the user/usergroup transaction
                    $program_title = Program::where('program_id', (int)$value1)->value('program_title');
                    // Send Notifications to the user
                    if (Config::get('app.notifications.usergroup.unassign_feed')) {
                        $notif_msg = trans('admin/notifications.unassign_feed_usergroup', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'feed' => $program_title, 'groupname' => $asset['usergroup_name']]);
                        if (isset($asset['relations']['active_user_usergroup_rel'])) {
                            $notify_user_ids_ary = [];
                            $notify_user_ids_ary = $asset['relations']['active_user_usergroup_rel'];
                            // foreach ($asset['relations']['active_user_usergroup_rel'] as $uid) {
                            if (!empty($notify_user_ids_ary)) {
                                NotificationLog::getInsertNotification($notify_user_ids_ary, trans('admin/user.usergroup'), $notif_msg);
                            }
                            // }
                        }
                    }
                    if (config('elastic.service')) {
                        event(new UsersAssigned($value1));
                    }
                }
            }
            if ($action == 'user' && isset($asset['relations']['active_user_usergroup_rel'])) {
                // Code to remove relations from user collection
                $deleted = array_diff($asset['relations']['active_user_usergroup_rel'], $ids);
                $ids = array_diff($ids, $asset['relations']['active_user_usergroup_rel']);
                $notify_user_ids_ary = [];
                foreach ($deleted as $value2) {
                    if (!empty($instance_ids)) {
                        foreach ($instance_ids as $instance_id) {
                            try {
                                event(new EntityUnenrollmentThroughUserGroup(
                                    $value2,
                                    UserEntity::PROGRAM,
                                    $instance_id,
                                    $asset["ugid"]
                                ));

                                $this->roleService->unmapUserAndRole((int)$value2, $context_id, $instance_id);
                            } catch (UserNotFoundException $e) {
                                Log::info(trans('admin/user.user_not_found', ['id' => $value2]));
                            }
                        }
                    }
                    if (!empty($package_ids)) {
                        foreach ($package_ids as $package_id) {
                            try {
                                event(
                                    new EntityUnenrollmentThroughUserGroup(
                                        $value2,
                                        UserEntity::PACKAGE,
                                        $package_id,
                                        $asset["ugid"]
                                    )
                                );
                            } catch (UserNotFoundException $e) {
                                Log::info(trans('admin/user.user_not_found', ['id' => $value2]));
                            }
                        }
                    }
                    
                    User::removeUserRelation($value2, ['active_usergroup_user_rel'], (int)$asset['ugid']);
                    $notify_user_ids_ary[] = (int)$value2;
                    // Send Notifications to the user
                }
                if (Config::get('app.notifications.usergroup.unassign_user') && !empty($notify_user_ids_ary)) {
                    $notif_msg = trans('admin/notifications.unassign_usergroup', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'groupname' => $asset['usergroup_name']]);
                    NotificationLog::getInsertNotification($notify_user_ids_ary, trans('admin/user.usergroup'), $notif_msg);
                }
            }
        }
        $ids = array_values($ids); //  This is to reset the index. [Problem: when using array_diff, the keys gets altered which converts normal array to associative array.]
        $deleted = array_values($deleted); //  This is to reset the index. [Problem: when using array_diff, the keys gets altered which converts normal array to associative array.]
        foreach ($ids as &$value) {
            $value = (int)$value;
            $now = time();
            if ($action == 'contentfeed') {
                if (!empty($users_ids)) {
                    foreach ($users_ids as $user_id) {
                        event(new EntityEnrollmentThroughUserGroup(
                            $user_id,
                            UserEntity::PROGRAM,
                            $value,
                            $asset["ugid"]
                        ));
                        $this->roleService->mapUserAndRole($user_id, $context_id, $role_id, $value);
                    }
                }
                Program::addFeedRelation($value, ['active_usergroup_feed_rel'], (int)$asset['ugid']);
                $trans_id = Transaction::uniqueTransactionId();
                $programdetails = Program::getProgramDetailsByID($value)->toArray();
                $transaction = [
                    'DAYOW' => date('l', $now),
                    'DOM' => (int)date('j', $now),
                    'DOW' => (int)date('w', $now),
                    'DOY' => (int)date('z', $now),
                    'MOY' => (int)date('n', $now),
                    'WOY' => (int)date('W', $now),
                    'YEAR' => (int)date('Y', $now),
                    'trans_level' => 'usergroup',
                    'id' => (int)$asset['ugid'],
                    'created_date' => time(),
                    'trans_id' => (int)$trans_id,
                    'full_trans_id' => 'ORD' . sprintf('%07d', (string)$trans_id),
                    'access_mode' => 'assigned_by_admin',
                    'added_by' => Auth::user()->username,
                    'added_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                    'created_at' => time(),
                    'updated_at' => time(),
                    'type' => 'subscription',
                    'status' => 'COMPLETE', // This is transaction status
                ];

                $transaction_details = [
                    'trans_level' => 'usergroup',
                    'id' => (int)$asset['ugid'],
                    'trans_id' => (int)$trans_id,
                    'program_id' => $programdetails['program_id'],
                    'program_slug' => $programdetails['program_slug'],
                    'type' => 'content_feed',
                    'program_title' => $programdetails['program_title'],
                    'duration' => [ // Using the same structure from duration master
                        'label' => 'Forever',
                        'days' => 'forever',
                    ],
                    'start_date' => '', // Empty since the duration is forever
                    'end_date' => '', // Empty since the duration is forever
                    'created_at' => time(),
                    'updated_at' => time(),
                    'status' => 'COMPLETE',
                ];
                // Add record to user transaction table
                Transaction::insert($transaction);
                TransactionDetail::insert($transaction_details);

                // Send Notifications to the user
                if (Config::get('app.notifications.usergroup.assign_feed')) {
                    $notif_msg = trans('admin/notifications.assign_feed_usergroup', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'feed' => $programdetails['program_title'], 'groupname' => $asset['usergroup_name']]);
                    if (isset($asset['relations']['active_user_usergroup_rel']) && !empty($asset['relations']['active_user_usergroup_rel'])) {
                        NotificationLog::getInsertNotification($asset['relations']['active_user_usergroup_rel'], trans('admin/user.usergroup'), $notif_msg);
                        /* foreach ($asset['relations']['active_user_usergroup_rel'] as $uid) {
                    Notification::getInsertNotification((int) $uid, trans('admin/user.usergroup'), $notif_msg);
                    }*/
                    }
                }
                if (config('elastic.service')) {
                    event(new UsersAssigned($value));
                }
            }
            if ($action == 'user') {
                User::addUserRelation($value, ['active_usergroup_user_rel'], $asset['ugid']);
                if (!empty($instance_ids)) {
                    foreach ($instance_ids as $instance_id) {
                        event(new EntityEnrollmentThroughUserGroup(
                            $value,
                            UserEntity::PROGRAM,
                            $instance_id,
                            $asset["ugid"]
                        ));
                        $this->roleService->mapUserAndRole($value, $context_id, $role_id, $instance_id);
                    }
                }
                
                if (!empty($package_ids)) {
                    foreach ($package_ids as $package_id) {
                        event(
                            new EntityEnrollmentThroughUserGroup(
                                $value,
                                UserEntity::PACKAGE,
                                $package_id,
                                $asset["ugid"]
                            )
                        );
                    }
                }
                // Send Notifications to the user
                if (Config::get('app.notifications.usergroup.assign_user')) {
                    $notif_msg = trans('admin/notifications.assign_usergroup', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'groupname' => $asset['usergroup_name']]);
                    NotificationLog::getInsertNotification([(int)$value], trans('admin/user.usergroup'), $notif_msg);
                }
            }
        }
        if (!empty($ids)) {
            UserGroup::updateUserGroupRelation($key, $arrname, $ids);
        }
        UserGroup::where('ugid', (int)$key)->pull('relations.' . $arrname, $deleted, true);
        if ($action == 'user') {
            if (config('elastic.service')) {
                event(new UserGroupAssigned((int)$key));
            }
        }
        return response()->json(['flag' => 'success', 'message' => $msg]);
    }

    private function getUserGroupList($ugid = null)
    {
        return UserGroup::getAllUserGroupNames($ugid);
    }

    private function getUserCountfromUsergroup($ugid = null)
    {
        $res = UserGroup::getAssignedUsers($ugid);
        return $res;
    }

    /*
    Name : updateUsersWithCustomFields
    Purpose: To Prepare the final users data for export for update
     */
    public function updateUsersWithCustomFields(&$users, $customFields = [])
    {

        if (is_array($users)) {
            $i = 0;
            $mandatoryColsArr = ['uid', 'firstname', 'lastname', 'role', 'username', 'email', 'mobile', 'created_at', 'status', 'app_registration', 'relations'];
            $pushedArr = [];
            foreach ($users as $key => $value) {
                $pushedArr = $mandatoryColsArr;
                $userlist = array_intersect_key($users[$i], $customFields);
                if (is_array($userlist) && !empty($userlist)) {
                    $a = array_merge($pushedArr, array_keys($userlist));
                    $users[$i] = array_only($users[$i], $a);
                } else {
                    $users[$i] = array_only($users[$i], $mandatoryColsArr);
                }
                //Usergroup data
                if (!empty(array_get($users, $i.".relations.active_usergroup_user_rel", []))) {
                    $userGroupNames = implode(
                        ",",
                        UserGroup::getUserGroupNames($users[$i]["relations"]["active_usergroup_user_rel"])
                    );
                    $users[$i]['usergroupnames'] = $userGroupNames;
                }

                $i++;
            }
        }
    }

    /*
    @name    : getImportUpdateUsers
    @purpose : to attach the users data with the custom fields for update
     */
    public function getImportUpdateUsers()
    {
        try {
            $total_users_count = User::getUsersCount('ALL', null, [], [Auth::user()->uid], []);
            /* get custom fields */
            $customFields = CustomFields::getUserCustomFieldArr('user', '', ['fieldlabel', 'mark_as_mandatory']);
            $this->createCustomFieldArr($customFields, 'fieldlabel');
            $customFieldArr = [];
            $customFieldInUsersData = [];
            $i = 0;
            foreach ($customFields as $key => $val) {
                if ('yes' == $customFields[$i]['mark_as_mandatory']) {
                    array_push($customFieldArr, $customFields[$i]['fieldlabel'] . '*');
                } else {
                    array_push($customFieldArr, $customFields[$i]['fieldlabel']);
                }

                $customFieldInUsersData[$customFields[$i]['fieldlabel']] = null;

                $i++;
            }
            unset($customFields); // do not need this array further, release the memory
            $batch_limit = config('app.bulk_insert_limit');
            $total_batchs = intval($total_users_count / $batch_limit);
            $record_set = 0 ;
            $excelObj = new PHPExcel();
            $excelObj->setActiveSheetIndex(0);
            $excelObj->getActiveSheet()->setTitle('Excel upload');
            $header = [];
            $header[] = trans('admin/user.serial_no');
            $header[] = trans('admin/user.first_name');
            $header[] = trans('admin/user.last_name');
            $header[] = trans('admin/user.email');
            $header[] = trans('admin/user.mobile');
            $header[] = trans('admin/user.username');
            $header[] = trans('admin/user.usergroup');
            if (is_array($customFieldArr) && !empty($customFieldArr)) {
                foreach ($customFieldArr as $title) {
                    $header[] = $title;
                }
            }
            $header[] = trans('admin/user.option');
            $filename = 'updatebulkuserlist.xls'; //save our workbook as this file name
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); //mime type
            header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name
            header('Cache-Control: max-age=0'); //no cache
            //save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
            //if you want to save it as .XLSX Excel 2007 format
            $count = 1;
            $excelObj->getActiveSheet()->fromArray($header, null, 'A'.$count);
            $objWriter = PHPExcel_IOFactory::createWriter($excelObj, 'Excel2007');
            $user_s_number = 0;
            do {
                $start = $record_set * $batch_limit;
                $users = User::getUsersWithPagination(
                    'ALL',
                    $start,
                    $batch_limit,
                    ['created_at' => 'desc'],
                    null,
                    [],
                    [Auth::user()->uid],
                    []
                )->toArray();
                $this->updateUsersWithCustomFields($users, $customFieldInUsersData);
                foreach ($users as $user_detail) {
                    $group_name = isset($user_detail['usergroupnames']) ? $user_detail['usergroupnames'] : '';
                    $user_s_number++;
                    $row = [];
                    $row[] = $user_s_number;
                    $row[] = $user_detail['firstname'];
                    $row[] = $user_detail['lastname'];
                    $row[] = $user_detail['email'];
                    $row[] = array_get($user_detail, 'mobile', '');
                    $row[] = $user_detail['username'];
                    $row[] = $group_name;
                    if (is_array($customFieldInUsersData) && !empty($customFieldInUsersData)) {
                        foreach ($customFieldInUsersData as $key => $fields) {
                            if (array_key_exists($key, $user_detail)) {
                                $row[] = $user_detail[$key];
                            } else {
                                $row[] = '';
                            }
                        }
                    }
                    $row[] = 'update';
                    $count++;
                    $excelObj->getActiveSheet()->fromArray($row, null, 'A'.$count);
                    $objWriter = PHPExcel_IOFactory::createWriter($excelObj, 'Excel2007');
                }
                $record_set++;
            } while ($record_set <= $total_batchs);
            $objWriter->save('php://output');
        } catch (Exception $e) {
            Log::error('While export user ::'.$e->getMessage());
        }
        exit;
    }

    /**
     * Method to show user log report
     * @return nothing loads view page user log report
     */
    public function getUserImportReport()
    {
        $crumbs = [
            'Dashboard' => 'cp',
            trans('admin/user.import_users_in_bulk') . ' Reports' => '',
            'User' . ' ' . trans('admin/program.import_report') => '',
        ];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/user.import_users_in_bulk') . ' Reports';
        $this->layout->pageicon = 'fa fa-bar-chart-o';
        $this->layout->pagedescription = trans('admin/user.import_users_in_bulk') . ' Reports';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'import-reports')
            ->with('submenu', 'user-import-report');
        $this->layout->content = view('admin.theme.users.user_report');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * Method to get user log records
     * @return array user log records
     */
    public function getUserImportList()
    {
        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];

        if (isset($search['value'])) {
            $searchKey = $search['value'];
        }

        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }

        $filter = Input::get('filter');
        $filters = Input::get('filters');

        if (!in_array($filter, ['SUCCESS', 'FAILURE'])) {
            $filter = 'ALL';
        } else {
            $filter = strtoupper($filter);
        }

        if (!in_array($filters, ['ADD', 'UPDATE'])) {
            $filters = 'ALL';
        } else {
            $filters = strtoupper($filters);
        }

        $created_date = Input::get('created_date');

        if ($searchKey != '') {
            $filter = 'ALL';
            $created_date = '';
        }
        $totalRecords = UserLog::getUserImportCount('ALL', null, null, 'ALL');
        $filteredRecords = UserLog::getUserImportCount($filter, $searchKey, $created_date, $filters);
        $filtereddata = UserLog::getUserImportRecords($filter, $start, $limit, $orderByArray, $searchKey, $created_date, $filters);

        $totalRecords = $filteredRecords;
        $dataArr = [];
        foreach ($filtereddata as $key => $value) {
            if ($value['status'] == 'SUCCESS') {
                $user = User::getUserDetailsByID($value['userid']);
                $username = $user['username'];
                $fullname = $user['firstname'] . ' ' . $user['lastname'];
                $email = $user['email'];
                $error = 'N/A';
            } else {
                $username = $value['username'];
                $fullname = $value['firstname'] . ' ' . $value['lastname'];
                $email = $value['email'];
                $error = $value['error_msgs'];
            }
            $date = Timezone::convertFromUTC($value['created_at'], Auth::user()->timezone, config('app.date_format'));
            $temparr = [$username, $fullname, $email, $error, $date, $value['status'], $value['action']];
            $dataArr[] = $temparr;
        }
        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr,
        ];

        return response()->json($finaldata);
    }

    /**
     * Method to export users
     * @return csv file with users
     */
    public function getUserExport($status = 'ALL', $action = 'ALL', $created_date = null)
    {
        $reports = UserLog::getUserExportRecords($status, $created_date, $action);
        $custom_fields = CustomFields::getUserCustomFieldArr('user', '', ['fieldname', 'fieldname']);
        if (!empty($reports)) {
            $data = [];
            $data[] = ['UserReport'];
            $header[] = 'firstname';
            $header[] = 'lastname';
            $header[] = 'email';
            $header[] = 'mobile';
            $header[] = 'username';
            $header[] = 'role';
            /*custom fields starts here*/
            if (!empty($custom_fields)) {
                foreach ($custom_fields as $custom) {
                    $header[] = $custom['fieldname'];
                }
            }
            /*custom fields ends here*/
            $header[] = 'createdate';
            $header[] = 'status';
            $header[] = 'errormessage';
            $header[] = trans('admin/program.action');
            $data[] = $header;
            foreach ($reports as $report) {
                $tempRow = [];
                if ($report['status'] == 'SUCCESS') {
                    $user = User::where('uid', '=', (int)$report['userid'])->get()->first();
                    $user['role'] = Role::pluckRoleName($user['role']);
                    $record = $user;
                    $error = '';
                } else {
                    $record = $report;
                    $error = $report['error_msgs'];
                }
                $tempRow[] = $record['firstname'];
                $tempRow[] = $record['lastname'];
                $tempRow[] = $record['email'];
                $tempRow[] = $record['mobile'];
                $tempRow[] = $record['username'];
                $tempRow[] = $record['role'];
                /*custom fields starts here*/
                if (!empty($custom_fields)) {
                    foreach ($custom_fields as $custom) {
                        if (isset($record[$custom['fieldname']])) {
                            $tempRow[] = $record[$custom['fieldname']];
                        } else {
                            $tempRow[] = '';
                        }
                    }
                }
                /*custom fields ends here*/
                $tempRow[] = Timezone::convertFromUTC($report['created_at'], Auth::user()->timezone, config('app.date_format'));
                $tempRow[] = $report['status'];
                $tempRow[] = $error;
                $tempRow[] = $report['action'];
                $data[] = $tempRow;
            }
            if (!empty($data)) {
                $filename = "UserReport.csv";
                $fp = fopen('php://output', 'w');
                header('Content-type: application/csv');
                header('Content-Disposition: attachment; filename=' . $filename);
                foreach ($data as $row) {
                    fputcsv($fp, $row);
                }
                exit;
            }
        }
    }

    /**
     * Method to import users
     * @return nothing
     */
    public function getErpImportUsers()
    {
        try {
            if (config('app.ftp_enabled')) {
                $connect = UserLog::getValidateFtp();
                if ($connect) {
                    $ftp_connection_details = $this->user_service->getFtpConnectionDetails();
                    if (in_array('add', $ftp_connection_details['dir_list'])) {
                        $ftp_dir_details = $this->user_service->getFtpDirDetails($ftp_connection_details);
                        if (in_array($ftp_dir_details['local_file'], $ftp_dir_details['file_list'])) {
                            $csv_file_data = $this->user_service->getUserImportFileColumns($ftp_connection_details, $ftp_dir_details);
                            /*---Validate headers ends here---*/
                            if ($csv_file_data['count'] > 0) {
                                $message = trans('admin/program.invalid_headers');
                                $error = trans('admin/program.invalid_headers');
                                $this->user_service->postUserLog($error, $status = 'FAILURE', $slug = 'erp-user-import-failure-template', $action = 'ADD', $cron = 0);
                            } else {
                                $i = 0;
                                foreach ($csv_file_data['csvFile'] as $line) {
                                    $data = str_getcsv($line);
                                    $i++;
                                    if ($i > 1) {
                                        $valid_data = $this->user_service->validateRulesErp(array_combine($csv_file_data['head'], $data));
                                        if ($valid_data) {
                                            $error_msg = '';
                                            foreach ($valid_data->all() as $k) {
                                                $error_msg .= $k;
                                            }
                                            /*---Insert data in user log collection with failure---*/
                                            $logdata = array_combine($csv_file_data['head'], $data);
                                            $logdata = $this->user_service->getUserFailedLogData($logdata, $error_msg, $status = 'FAILURE', $action = 'ADD', $cron = 0);
                                            UserLog::insertErpUserLog($logdata);
                                        } else {
                                            /*---Insert data in user & log collection with success---*/
                                            $result_data = array_combine($csv_file_data['head'], $data);
                                            //dd($result_data);
                                            $uid = $this->user_service->prepareUserData($result_data, $cron = 0);

                                            $groupid = Usergroup::getUserGroupId(strtolower($result_data['usergroup']));
                                            $role_info = $this->roleService->getRoleDetails(SystemRoles::LEARNER, ['context']);
                                            $role_id = array_get($role_info, 'id');
                                            $context_info = $this->roleService->getContextDetails(Contexts::PROGRAM, false);
                                            $context_id = array_get($context_info, 'id');
                                            $usergroup_details = UserGroup::getActiveUserGroupsUsingID($groupid);

                                            foreach ($usergroup_details as $key => $value) {
                                                $feed_rel_ids = array_get($value, 'relations.usergroup_feed_rel', []);
                                                if (!empty($feed_rel_ids)) {
                                                    foreach ($feed_rel_ids as $instance_id) {
                                                        event(new EntityEnrollmentThroughUserGroup($uid, UserEntity::PROGRAM, $instance_id, $groupid));
                                                        $this->roleService->mapUserAndRole((int)$uid, $context_id, $role_id, $instance_id);
                                                    }
                                                }
                                            }

                                            $input['userid'] = $uid;

                                            $user = User::where("uid", $uid)->first();

                                            $system_context_with_roles = $this->roleService->getContextDetails(
                                                Contexts::SYSTEM,
                                                true
                                            );
                                            $system_roles = array_get($system_context_with_roles, "roles");
                                            $system_context_role_ids = array_pluck($system_roles, "id");

                                            if (in_array($user->role, $system_context_role_ids)) {
                                                $this->roleService->mapUserAndRole(
                                                    $uid,
                                                    $system_context_with_roles["id"],
                                                    $user->role
                                                );
                                            } else {
                                                $registered_role =
                                                    $this->roleService->getRoleDetails(SystemRoles::REGISTERED_USER);
                                                $this->roleService->mapUserAndRole(
                                                    $uid,
                                                    $system_context_with_roles["id"],
                                                    $registered_role["id"]
                                                );

                                                $user->role = $registered_role["id"];
                                                $user->save();
                                            }

                                            $logdata = $this->user_service->getUserSuccessLogData($input, $status = 'SUCCESS', $action = 'ADD', $cron = 0);
                                            UserLog::insertErpUserLog($logdata);
                                            $groupid = Usergroup::getUserGroupId(strtolower($result_data['usergroup']));
                                            $this->user_service->createUserGroup($groupid, $uid, $result_data, $cron = 0);
                                        }
                                    }
                                }
                                $this->user_service->userImportEmail($status = 'SUCCESS', 'erp-user-import-success-template', '', 'ADD');
                                $message = trans('admin/program.cron_success');
                            }
                            //process records ends here
                            $new_file = $this->user_service->getFileName($ftp_dir_details['local_file'], $action = 'add');
                            rename($ftp_connection_details['path'] . $ftp_dir_details['local_file'], $ftp_connection_details['path'] . $new_file);
                        } else {
                            ftp_close($ftp_connection_details['conn_id']); //ftp connection close
                            $message = trans('admin/program.invalid_file');
                            $error = trans('admin/program.invalid_file');
                            $this->user_service->postUserLog($error, $status = 'FAILURE', $slug = 'erp-user-import-failure-template', $action = 'ADD', $cron = 0);
                        }
                    } else {
                        ftp_close($ftp_connection_details['conn_id']); //ftp connection close
                        $message = trans('admin/program.invalid_add_dir');
                        $error = trans('admin/program.invalid_add_dir');
                        $this->user_service->postUserLog($error, $status = 'FAILURE', $slug = 'erp-user-import-failure-template', $action = 'ADD', $cron = 0);
                    }
                } else {
                    $message = trans('admin/program.invalid_ftp');
                    $error = trans('admin/program.invalid_ftp');
                    $this->user_service->postUserLog($error, $status = 'FAILURE', $slug = 'erp-user-import-failure-template', $action = 'ADD', $cron = 0);
                }
            } else {
                $message = trans('admin/program.cron_ftp_disabled');
            }
            $this->layout->pagetitle = trans('admin/user.import_user_cron');
            $this->layout->pageicon = 'fa fa-cloud-download';
            $this->layout->pagedescription = trans('admin/user.import_user_cron');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->content = view('admin.theme.programs.import_cron')->with('message', $message);
            $this->layout->footer = view('admin.theme.common.footer');
        } catch (Exception $e) {
            Log::error($e->getFile(). trans('admin/program.error_line', ['error_line_no'=> $e->getLine()]).trans('admin/program.message', ['error_message' => $e->getMessage()]));
            $this->layout->pagetitle = trans('admin/user.import_user_cron');
            $this->layout->pageicon = 'fa fa-cloud-download';
            $this->layout->pagedescription = trans('admin/user.import_user_cron');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->content = view('admin.theme.programs.import_cron')->with('message', $e->getMessage());
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }
    public function getImportAddUserTemplate()
    {
        $data = [];
        $data[] = 'firstname*';
        $data[] = 'lastname';
        $data[] = 'email*';
        $data[] = 'mobile';
        $data[] = 'username*';
        $data[] = 'password*';
        $data[] = 'usergroup';
        $data[] = 'role*';
        $data[] = 'address';
        $data[] = 'landmark';
        $data[] = 'country';
        $data[] = 'state';
        $data[] = 'city';
        $data[] = 'pincode';
        $data[] = 'phone';

        $custom_fields = CustomFields::getUserCustomFieldArr('user', '', ['fieldlabel', 'fieldlabel', 'mark_as_mandatory']);
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom) {
                if ($custom['mark_as_mandatory'] == 'yes') {
                    $data[] = array_get($custom, 'fieldlabel') . '*';
                } else {
                    $data[] = array_get($custom, 'fieldlabel');
                }
            }
        }

        $file = config('app.user_import_file');
        header('Content-type: application/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file);
        $fp = fopen('php://output', 'w');
        fputcsv($fp, $data);
        die;
    }

    /**
     * Method to download update users csv file
     * @returns update users csv file with fields as headers
     */
    public function getImportUpdateUserTemplate()
    {
        $this->user_service->downloadUpdateUserTemplate();
    }

    /**
     * Method to update bulk users
     * @returns nothing updates user data
     */
    public function getErpUpdateUsers()
    {
        try {
            if (config('app.ftp_enabled')) {
                $connect = UserLog::getValidateFtp();
                if ($connect) {
                    $ftp_connection_details = $this->user_service->getFtpConnectionDetails();
                    if (in_array('update', $ftp_connection_details['dir_list'])) {
                        $ftp_dir_details = $this->user_service->getFtpDirUpdateDetails($ftp_connection_details);
                        if (in_array($ftp_dir_details['local_file'], $ftp_dir_details['file_list'])) {
                            $csv_file_data = $this->user_service->getUserImportUpdateFileColumns($ftp_connection_details, $ftp_dir_details);
                            /*---Validate headers ends here---*/
                            if ($csv_file_data['count'] > 0) {
                                $error = trans('admin/program.invalid_headers');
                                $message = trans('admin/program.invalid_headers');
                                $this->user_service->postUserLog($error, $status = 'FAILURE', $slug = 'erp-user-update-failure-template', $action = 'UPDATE', $cron = 0);
                            } else {
                                $i = 0;
                                foreach ($csv_file_data['csvFile'] as $line) {
                                    $data = str_getcsv($line);
                                    $i++;
                                    if ($i > 1) {
                                        $valid_data = $this->user_service->validateUpdateRules(array_combine($csv_file_data['head'], $data));
                                        if ($valid_data) {
                                            $error_msg = '';
                                            foreach ($valid_data->all() as $k) {
                                                $error_msg .= $k;
                                            }
                                            $logdata = array_combine($csv_file_data['head'], $data);
                                            $logdata = $this->user_service->getMissingFields($logdata, $csv_file_data['fields']);
                                            $logdata = $this->user_service->getUserFailedLogData($logdata, $error_msg, $status = 'FAILURE', $action = 'UPDATE', $cron = 0);
                                            UserLog::insertErpUserLog($logdata);
                                        } else {
                                            $user_data = array_combine($csv_file_data['head'], $data);
                                            $user = User::where("username", $user_data['username'])->first();
                                            $uid = $this->user_service->getUserIdByUserName($user_data['username']);
                                            //update user data
                                            $this->user_service->updateUserDetails($uid, $user_data);

                                            $updated_user = User::where("uid", $uid)->first();

                                            $system_context_with_roles = $this->roleService->getContextDetails(
                                                Contexts::SYSTEM,
                                                true
                                            );
                                            $system_roles = array_get($system_context_with_roles, "roles");
                                            $system_context_role_ids = array_pluck($system_roles, "id");

                                            if (in_array($user->role, $system_context_role_ids)) {
                                                $this->roleService->updateUserSystemContextRole(
                                                    $uid,
                                                    $updated_user->role
                                                );
                                            } else {
                                                $this->roleService->updateUserSystemContextRole(
                                                    $uid,
                                                    $user->role
                                                );

                                                $updated_user->role = $user->role;
                                            }
                                            //insert success log in user log table
                                            $input['userid'] = $uid;
                                            $logdata = $this->user_service->getUserSuccessLogData($input, $status = 'SUCCESS', $action = 'UPDATE', $cron = 0);
                                            UserLog::insertErpUserLog($logdata);
                                        }
                                    }
                                }
                                $this->user_service->userImportEmail($status = 'SUCCESS', 'erp-user-update-success-template', '', 'UPDATE');
                                $message = trans('admin/program.cron_success');
                            }
                            //process records ends here
                            $new_file = $this->user_service->getFileName($ftp_dir_details['local_file'], $action = 'UPDATE');
                            rename($ftp_connection_details['path'] . $ftp_dir_details['local_file'], $ftp_connection_details['path'] . $new_file);
                        } else {
                            ftp_close($ftp_connection_details['conn_id']);
                            $message = trans('admin/program.invalid_file');
                            $error = trans('admin/program.invalid_file');
                            $this->user_service->postUserLog($error, $status = 'FAILURE', $slug = 'erp-user-update-failure-template', $action = 'UPDATE', $cron = 0);
                        }
                    } else {
                        ftp_close($ftp_connection_details['conn_id']); //ftp connection close
                        $message = trans('admin/program.invalid_update_dir');
                        $error = trans('admin/program.invalid_update_dir');
                        $this->user_service->postUserLog($error, $status = 'FAILURE', $slug = 'erp-user-update-failure-template', $action = 'UPDATE', $cron = 0);
                    }
                } else {
                    $message = trans('admin/program.invalid_ftp');
                    $error = trans('admin/program.invalid_ftp');
                    $this->user_service->postUserLog($error, $status = 'FAILURE', $slug = 'erp-user-update-failure-template', $action = 'UPDATE', $cron = 0);
                }
            } else {
                $message = trans('admin/program.cron_ftp_disabled');
            }
            $this->layout->pagetitle = trans('admin/user.update_user_cron');
            $this->layout->pageicon = 'fa fa-cloud-download';
            $this->layout->pagedescription = trans('admin/user.update_user_cron');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->content = view('admin.theme.programs.import_cron')->with('message', $message);
            $this->layout->footer = view('admin.theme.common.footer');
        } catch (Exception $e) {
            Log::error($e->getFile(). trans('admin/program.error_line', ['error_line_no'=> $e->getLine()]).trans('admin/program.message', ['error_message' => $e->getMessage()]));
            $this->layout->pagetitle = trans('admin/user.update_user_cron');
            $this->layout->pageicon = 'fa fa-cloud-download';
            $this->layout->pagedescription = trans('admin/user.update_user_cron');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->content = view('admin.theme.programs.import_cron')->with('message', $e->getMessage());
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }

    /**
     * Method to assign & unassign user to user group
     * @returns nothing mapps user to user group
     */
    public function getAssignUserToUsergroup()
    {
        try {
            if (config('app.ftp_enabled')) {
                $connect = UserLog::getValidateFtp();
                if ($connect) {
                    $ftp_connection_details = $this->user_service->getFtpConnectionDetails();
                    if (in_array('update', $ftp_connection_details['dir_list'])) {
                        $ftp_dir_details = $this->user_service->getUserUsergroupUpdateFtpDetails($ftp_connection_details);
                        if (in_array($ftp_dir_details['local_file'], $ftp_dir_details['file_list'])) {
                            $csv_file_data = $this->user_service->getUserUsergroupUpdateFileColumns($ftp_connection_details, $ftp_dir_details);
                            /*---Validate headers ends here---*/
                            if ($csv_file_data['count'] > 0) {
                                $error = trans('admin/program.invalid_headers');
                                $message = trans('admin/program.invalid_headers');
                                $this->user_service->postUserGroupLog($error, $status = 'FAILURE', $slug = 'erp-assign-user-usergroup-failure-template', $action = 'UPDATE', $cron = 0);
                            } else {
                                $i = 0;
                                foreach ($csv_file_data['csvFile'] as $line) {
                                    $data = str_getcsv($line);
                                    $i++;
                                    if ($i > 1) {
                                        $valid_data = $this->user_service->validateUserUsergroupRules(array_combine($csv_file_data['head'], $data));
                                        if ($valid_data) {
                                            $error_msg = '';
                                            foreach ($valid_data->all() as $k) {
                                                $error_msg .= $k;
                                            }
                                            $logdata = array_combine($csv_file_data['head'], $data);
                                            $logdata = $this->user_service->getUserGroupFailedData($logdata, $error_msg, $status = 'FAILURE', $action = 'UPDATE', $cron = 0);
                                            UsergroupLog::insertErpUserGroupLog($logdata);
                                        } else {
                                            $user_data = array_combine($csv_file_data['head'], $data);
                                            $uid = $this->user_service->getUserIdByUserName($user_data['username']);
                                            $gid = $this->user_service->getUserGroupIdByGroupName($user_data['usergroup']);
                                            $this->user_service->updateUserUserGroupRelations($uid, $gid, $user_data['operation']); //update user & ug table

                                            if ($user_data["operation"] === "assign") {
                                                $this->createUserEntityRelationsThroughUserGroups($uid, $gid);
                                            } elseif ($user_data["operation"] === "unassign") {
                                                $this->removeUserEntitiesAssignedThroughUserGroup($uid, $gid);
                                            }

                                            $input['groupid'] = $gid;
                                            $input['userid'] = $uid;
                                            $input['operation'] = $user_data['operation'];
                                            $logdata = $this->user_service->getUserGroupSuccessData($input, $status = 'SUCCESS', $action = 'UPDATE', $cron = 0);
                                            UsergroupLog::insertErpUserGroupLog($logdata); //insert success log in user log table
                                        }
                                    }
                                }
                                $this->user_service->sendUserGroupEmail($status = 'SUCCESS', 'erp-assign-user-usergroup-success-template', '', 'UPDATE');
                                $message = trans('admin/program.cron_success');
                            }
                            //process records ends here
                            $new_file = $this->user_service->getFileName($ftp_dir_details['local_file'], $action = 'UPDATE');
                            rename($ftp_connection_details['path'] . $ftp_dir_details['local_file'], $ftp_connection_details['path'] . $new_file);
                        } else {
                            ftp_close($ftp_connection_details['conn_id']);
                            $message = trans('admin/program.invalid_file');
                            $error = trans('admin/program.invalid_file');
                            $this->user_service->postUserGroupLog($error, $status = 'FAILURE', $slug = 'erp-assign-user-usergroup-failure-template', $action = 'UPDATE', $cron = 0);
                        }
                    } else {
                        ftp_close($ftp_connection_details['conn_id']); //ftp connection close
                        $message = trans('admin/program.invalid_update_dir');
                        $error = trans('admin/program.invalid_update_dir');
                        $this->user_service->postUserGroupLog($error, $status = 'FAILURE', $slug = 'erp-assign-user-usergroup-failure-template', $action = 'UPDATE', $cron = 0);
                    }
                } else {
                    $message = trans('admin/program.invalid_ftp');
                    $error = trans('admin/program.invalid_ftp');
                    $this->user_service->postUserGroupLog($error, $status = 'FAILURE', $slug = 'erp-assign-user-usergroup-failure-template', $action = 'UPDATE', $cron = 0);
                }
            } else {
                $message = trans('admin/program.cron_ftp_disabled');
            }
            $this->layout->pagetitle = trans('admin/user.assign_user_usergroup_cron');
            $this->layout->pageicon = 'fa fa-cloud-download';
            $this->layout->pagedescription = trans('admin/assign_user_usergroup_cron');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->content = view('admin.theme.programs.import_cron')->with('message', $message);
            $this->layout->footer = view('admin.theme.common.footer');
        } catch (Exception $e) {
            Log::error($e->getFile(). trans('admin/program.error_line', ['error_line_no'=> $e->getLine()]).trans('admin/program.message', ['error_message' => $e->getMessage()]));
            $this->layout->pagetitle = trans('admin/user.assign_user_usergroup_cron');
            $this->layout->pageicon = 'fa fa-cloud-download';
            $this->layout->pagedescription = trans('admin/assign_user_usergroup_cron');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->content = view('admin.theme.programs.import_cron')->with('message', $e->getMessage());
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }

    private function createUserEntityRelationsThroughUserGroups($user_id, $user_group_id)
    {
        $program_context_data = $this->roleService->getContextDetails(Contexts::PROGRAM);
        $learner_role = $this->roleService->getRoleDetails(SystemRoles::LEARNER);

        try {
            $user_group = UserGroup::findOrFail($user_group_id);
            $user_group_array = $user_group->toArray();
            $user_group_channel_ids = array_get($user_group_array, "relations.usergroup_feed_rel", []);
            $user_group_package_ids = array_get($user_group_array, "package_ids", []);

            foreach ($user_group_channel_ids as $channel_id) {
                event(
                    new EntityEnrollmentThroughUserGroup(
                        $user_id,
                        UserEntity::PROGRAM,
                        $channel_id,
                        $user_group_id
                    )
                );

                $this->roleService->mapUserAndRole(
                    $user_id,
                    $program_context_data["id"],
                    $learner_role["id"],
                    $channel_id
                );
            }

            foreach ($user_group_package_ids as $package_id) {
                event(
                    new EntityEnrollmentThroughUserGroup(
                        $user_id,
                        UserEntity::PACKAGE,
                        $package_id,
                        $user_group_id
                    )
                );

                try {
                    $package = Program::findOrFail($package_id);
                    $package_channel_ids = array_get($package->toArray(), "program_ids", []);
                    foreach ($package_channel_ids as $channel_id) {
                        $this->roleService->mapUserAndRole(
                            $user_id,
                            $program_context_data["id"],
                            $learner_role["id"],
                            $channel_id
                        );
                    }
                } catch (ModelNotFoundException $e) {
                    Log::notice("Could not find package with id {$package_id}");
                    Log::error($e->getTraceAsString());
                }
            }
        } catch (ModelNotFoundException $e) {
            Log::notice("Could not find user group with id {$user_group_id}");
            Log::error($e->getTraceAsString());
        }
    }

    private function removeUserEntitiesAssignedThroughUserGroup($user_id, $user_group_id)
    {
        $program_context_data = $this->roleService->getContextDetails(Contexts::PROGRAM);

        try {
            $user_group = UserGroup::findOrFail($user_group_id);
            $user_group_array = $user_group->toArray();
            $user_group_channel_ids = array_get($user_group_array, "relations.usergroup_feed_rel", []);
            $user_group_package_ids = array_get($user_group_array, "package_ids", []);

            foreach ($user_group_channel_ids as $channel_id) {
                event(
                    new EntityUnenrollmentThroughUserGroup(
                        $user_id,
                        UserEntity::PROGRAM,
                        $channel_id,
                        $user_group_id
                    )
                );

                $this->roleService->unmapUserAndRole(
                    $user_id,
                    $program_context_data["id"],
                    $channel_id
                );
            }

            foreach ($user_group_package_ids as $package_id) {
                event(
                    new EntityUnenrollmentThroughUserGroup(
                        $user_id,
                        UserEntity::PACKAGE,
                        $package_id,
                        $user_group_id
                    )
                );

                try {
                    $package = Program::findOrFail($package_id);
                    $package_channel_ids = array_get($package->toArray(), "program_ids", []);
                    foreach ($package_channel_ids as $channel_id) {
                        $this->roleService->unmapUserAndRole(
                            $user_id,
                            $program_context_data["id"],
                            $channel_id
                        );
                    }
                } catch (ModelNotFoundException $e) {
                    Log::notice("Could not find package with id {$package_id}");
                    Log::error($e->getTraceAsString());
                }
            }
        } catch (ModelNotFoundException $e) {
            Log::notice("Could not find user group with id {$user_group_id}");
            Log::error($e->getTraceAsString());
        }
    }

    /**
     * Method to download user-usergroup mapping csv file
     * @returns csv file with fields as headers
     */
    public function getUpdateUserUgTemplate()
    {
        $this->user_service->downloadUserUserGroupTemplate();
    }

    public function getUserUsergroupReport()
    {
        $crumbs = [
            'Dashboard' => 'cp',
            trans('admin/user.import_users_in_bulk') . ' Reports' => '',
            trans('admin/user.user_usergroup_report') => '',
        ];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/user.import_users_in_bulk') . ' Reports';
        $this->layout->pageicon = 'fa fa-bar-chart-o';
        $this->layout->pagedescription = trans('admin/user.import_users_in_bulk') . ' Reports';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'import-reports')
            ->with('submenu', 'user-usergroup-report');
        $this->layout->content = view('admin.theme.users.user_ug_report');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * Method to get user-usergroup log records
     * @return array user-usergroup log records
     */
    public function getUserUgImportList()
    {
        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];

        if (isset($search['value'])) {
            $searchKey = $search['value'];
        }

        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }

        $filter = Input::get('filter');
        $filters = Input::get('filters');

        if (!in_array($filter, ['SUCCESS', 'FAILURE'])) {
            $filter = 'ALL';
        } else {
            $filter = strtoupper($filter);
        }

        if (!in_array($filters, ['assign', 'unassign'])) {
            $filters = 'all';
        } else {
            $filters = strtolower($filters);
        }

        $created_date = Input::get('created_date');

        if ($searchKey != '') {
            $filter = 'ALL';
            $created_date = '';
        }
        $totalRecords = UsergroupLog::getUserUgImportCount('ALL', null, null, 'all');
        $filteredRecords = UsergroupLog::getUserUgImportCount($filter, $searchKey, $created_date, $filters);
        $filtereddata = UsergroupLog::getUserUgImportRecords($filter, $start, $limit, $orderByArray, $searchKey, $created_date, $filters);

        $totalRecords = $filteredRecords;
        $dataArr = [];
        foreach ($filtereddata as $key => $value) {
            if ($value['status'] == 'SUCCESS') {
                $user = User::getUserDetailsByID($value['userid']);
                $username = $user['username'];
                $usergroup = UserGroup::where('ugid', '=', (int)$value['groupid'])->value('usergroup_name');
                $error = 'N/A';
            } else {
                $username = $value['username'];
                $usergroup = $value['usergroup'];
                $error = $value['error_msgs'];
            }
            $date = Timezone::convertFromUTC($value['created_at'], Auth::user()->timezone, config('app.date_format'));
            $temparr = [$username, $usergroup, $value['operation'], $error, $date, $value['status']];
            $dataArr[] = $temparr;
        }
        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr,
        ];

        return response()->json($finaldata);
    }

    /**
     * Method to export user-usergroup log records
     * @return csv file with user-usergroup log records
     */
    public function getUserUgExport($status = 'ALL', $operation = 'all', $created_date = null)
    {
        $this->user_service->getUserUgExport($status, $operation, $created_date);
    }

     /**
     * Method to move 'option' filed after custom fields in bulk update user
     */
    public function moveOptionElement(&$cols, $a, $b)
    {
        $out = array_splice($cols, $a, 1);
        array_splice($cols, $b, 0, $out);
    }

    /**
     * Method used to go to view of upload file
     * @return view
     */
    public function getImportUserToUsergroup()
    {
        if (!has_admin_permission(ModuleEnum::USER, UserPermission::IMPORT_USERS)) {
            return parent::getAdminError($this->theme_path);
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/user.manage_user_group') => 'usergroupmanagement/user-groups',
            trans('admin/user.import_bulk_user_usergroup') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/user.manage_user_group');
        $this->layout->pageicon = 'fa fa-group';
        $this->layout->pagedescription = trans('admin/user.import_user_in_bulk');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'users_groups')
            ->with('submenu', 'group');
        $this->layout->footer = view('admin.theme.common.footer');
        $context_details = $this->roleService->getContextDetails(Contexts::SYSTEM, true);
        $roles = array_get($context_details, 'roles', []);
        $this->layout->content = view('admin.theme.users.import_bulk_user_usergroup')->with('roles', $roles)->with('timezones', Timezone::get())->with('frequent_tz', Timezone::frequent());
    }

    /*
    @name    : getUserToUsergroup1
    @purpose : Download xls file with username and usergroup columns 
    */
    public function getUserToUsergroup1()
    {

        $downloadUserXls = [trans('admin/user.username') . '*', trans('admin/user.usergroup'). '*'];
        $excelObj = new PHPExcel();
        $excelObj->setActiveSheetIndex(0);
        $excelObj->getActiveSheet()->setTitle('Excel upload');
        $excelObj->getActiveSheet()->fromArray($downloadUserXls, null, 'A1');
        $filename = trans('admin/user.users_usergroup');//save our workbook as this file name
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); //mime type
        header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name
        header('Cache-Control: max-age=0'); //no cache
        //save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
        //if you want to save it as .XLSX Excel 2007 format
        $objWriter = PHPExcel_IOFactory::createWriter($excelObj, 'Excel2007');
        //force user to download the Excel file without writing it to server's HD
        $objWriter->save('php://output');
        exit;
    }

    /*
    @name    : getUserToUsergroup2
    @purpose : Download xls file with username and usergroup columns,add existing user names in username column 
    */
    public function getUserToUsergroup2()
    {
        $users = $this->user_service->getActiveUsers([Auth::user()->uid]);
        ob_end_clean();
        return view('admin.theme.users.import_bulk_user_usergroup2')->with('users', $users);
    }


    public function validateUserUsergroupRulesExcel($excelrowData)
    {
        $username = array_get($excelrowData, 'username', '');
        $usergroup = array_get($excelrowData, 'usergroup', []);
        $rules = [
            'username' => 'Required|Min:3|Max:93|checkusernameregex|
            checkuserexists|checkactiveuser|checkusergroupexists|checkadminexists',
            'usergroup' => 'Required|checkusergroup|checkexistsusergroupforactiveuser|
            checkactiveusergroupforuser|checkexistusergroupforuser',
        ];
        
        Validator::extend('checkusernameregex', function ($attribute, $value, $parameters) {
            if (isset($value) && !empty($value)) {
                (strpos($value, '@') !== false) ?
                $pattern = "/^(?!.*?[._]{2})[a-zA-Z0-9]+[a-zA-Z0-9(\._\)?-]+[a-zA-Z0-9]+@[a-zA-Z0-9]+([\.]?[a-z]{2,6}+)*$/"
                : $pattern = "/^[a-zA-Z0-9._]*$/";
                if (preg_match($pattern, $value)) {
                    return true;
                } else {
                    return false;
                }
            }
        });

        Validator::extend('checkusergroup', function ($attribute, $value, $parameters) {
            $flag = true;
            if (is_array($value)) {
                $cnt = count($value);
                $this->notFoundUserGroup = [];
                for ($i = 0; $i < $cnt; $i++) {
                    $val = trim($value[$i]);
                    if (!empty($val) && $this->userGroupService->getUserGroupCount(trim($val)) <= 0) {
                        $flag = false;
                        array_push($this->notFoundUserGroup, $val);
                    }
                }
            }
            return $flag;
        });

        Validator::extend('checkexistsusergroupforactiveuser', function ($attribute, $value, $parameters) use ($username) {
            $userGroupNames = array_map('strtolower', $value);
            $flag = true;
            $arr_unique_usergroup = array_unique($userGroupNames);
            $arr_duplicates_usergroupnames = array_diff_assoc($userGroupNames, $arr_unique_usergroup);
            if ($this->user_service->getActiveUserCount($username) != 0 && !empty($arr_duplicates_usergroupnames)) {
                $flag = false;
            }
            return $flag;
        });

        Validator::extend('checkactiveusergroupforuser', function ($attribute, $value, $parameters) use ($username) {
            $userGroupNames = array_map('strtolower', $value);
            $flag = true;
            $arr_unique_usergroup = array_unique($userGroupNames);
            $inactive_usergroup_count = $this->userGroupService->getInActiveUserGroupCount($arr_unique_usergroup);
            if ($this->user_service->getActiveUserCount($username) != 0 && $inactive_usergroup_count > 0) {
                $flag = false;
            }
            return $flag;
        });

        Validator::extend('checkexistusergroupforuser', function ($attribute, $value, $parameters) use ($username) {
            $userGroupNames = array_map('strtolower', $value);
            $flag = true;
            $arr_unique_usergroup = array_unique($userGroupNames);
            $uid = $this->user_service->getUserIdByUserName($username);
            $active_user_usergroup_rel = $this->userGroupService->getUserUsergroupRelation($userGroupNames);
            $active_user_usergroup_rel = array_flatten($active_user_usergroup_rel);
            if ($this->user_service->getActiveUserCount($username) != 0 && in_array($uid, $active_user_usergroup_rel)) {
                $flag = false;
            }
            return $flag;
        });
        
        Validator::extend('checkuserexists', function ($attribute, $value, $parameters) {
            $flag = true;
            $uid = $this->user_service->getUserIdByUserName($value);
            if (is_null($uid)) {
                $flag = false;
            }
            return $flag;
        });

        Validator::extend('checkactiveuser', function ($attribute, $value, $parameters) {
            $flag = true;
            $uid = $this->user_service->getUserIdByUserName($value);
            if (!is_null($uid)) {
                $count =  $this->user_service->getActiveUserCount(strtolower($value));
                if ($count == 0) {
                    $flag = false;
                }
                return $flag;
            }
            return $flag;
        });
        
        Validator::extend('checkusergroupexists', function ($attribute, $value, $parameters) use ($usergroup) {
            $flag = true;
            if (empty(reset($usergroup))) {
                $flag = false;
            }
            return $flag;
        });
        
        Validator::extend('checkadminexists', function ($attribute, $value, $parameters) use ($usergroup) {
            $flag = true;
            $uid = $this->user_service->getUserIdByUserName($value);
            $admin_user_ids = $this->user_service->getAdminUsers();
            if (in_array($uid, $admin_user_ids)) {
                $flag = false;
            }
            return $flag;
        });

        $message = [
            'checkusernameregex' => trans('admin/user.check_new_username_regex'),
            'checkusergroup' => trans('admin/user.check_usergroup'),
            'checkexistsusergroupforactiveuser' => trans('admin/user.check_exists_usergroup'),
            'checkactiveusergroupforuser' => trans('admin/user.cannot_assign_user_to_inactive_usergroup'),
            'checkexistusergroupforuser' => trans('admin/user.usergroup_reassign'),
            'checkuserexists' => trans('admin/user.check_username'),
            'checkactiveuser' => trans('admin/user.cannot_assign_usergroup_to_inactive_user'),
            'checkusergroupexists' => trans('admin/user.usergroup_require'),
            'checkadminexists' => trans('admin/user.assign_admin'),
        ];

        Validator::replacer('checkusergroup', function ($message, $attribute, $rule, $parameters) {
            $noUserGroup = implode(",", $this->notFoundUserGroup);
            return str_replace($attribute, $noUserGroup, $message);
        });
        return $this->customValidate($excelrowData, $rules, $message);
    }

    /**
    * @param $rowData
    * @return boolean
    */
    public function updateImportUserToUsergroup($rowData, $customFieldDataFromExcel)
    {
        $updateStatus = false;
        $rowData['username'] = strtolower(array_get($rowData, 'username', ''));
        $uid = (int)$this->user_service->getIdBy($rowData, 'username');
        $userGroup = Common::trimArray($rowData['usergroup'], false);
        $ugid = $this->collectUserGroupIds($userGroup);
        $assignedusergroup = (array)$this->user_service->getAssignedUsergroups($uid);
        $checkFlagStatus = array_pull($ugid, 'ugidResFlag');
        $diff_of_ids = array_diff($assignedusergroup, $ugid);
        if ($checkFlagStatus) {
            // update into relation table
            foreach ($ugid as $id) {
                // update relation.active_usergroup_user_rel in users table
                $this->user_service->updateUserRelation($uid, 'active_usergroup_user_rel', $id);
                // update relation. active_usergroup_user_rel in userGroup table
                $this->userGroupService->addUserGroupRelation($id, ['active_user_usergroup_rel'], $uid);
                $this->userGroupService->updateUserGroupRelation($id, 'active_user_usergroup_rel', $uid);
                $role_info = $this->roleService->getRoleDetails(SystemRoles::LEARNER, ['context']);
                $role_id = array_get($role_info, 'id');
                $context_info = $this->roleService->getContextDetails(Contexts::PROGRAM, false);
                $context_id = array_get($context_info, 'id');
                $usergroup_details = UserGroup::getActiveUserGroupsUsingID($id);

                foreach ($usergroup_details as $key => $value) {
                    $feed_rel_ids = array_get($value, 'relations.usergroup_feed_rel', []);
                    if (!empty($feed_rel_ids)) {
                        foreach ($feed_rel_ids as $instance_id) {
                            event(new EntityEnrollmentThroughUserGroup($uid, UserEntity::PROGRAM, $instance_id, $id));
                            $this->roleService->mapUserAndRole((int)$uid, $context_id, $role_id, $instance_id);
                        }
                    }
                }
            }
            $updateStatus = true;
        }
        return $updateStatus;
    }

    /**
     * Method used to upload file and update data
     * @return nothing
     */
    public function postImportUserToUsergroup()
    {
        if (!has_admin_permission(ModuleEnum::USER, UserPermission::IMPORT_USERS)) {
            return parent::getAdminError($this->theme_path);
        }

        ini_set('max_execution_time', 300);

        $rules = [
            'xlsfile' => 'Required|allowexcel'
        ];
        $niceNames = [
            'xlsfile' => 'import file',
        ];
        Validator::extend('allowexcel', function ($attribute, $value, $parameters) {
            $mime = $value->getMimeType();
            if (in_array($mime, ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.oasis.opendocument.text', 'application/vnd.ms-excel',
                'application/zip', 'application/vnd.ms-office','application/octet-stream'])) {
                return true;
            }
            return false;
        });

        $messages = [];
        $messages += [
            'xlsfile.allowexcel' => 'Please upload only XLS file',
        ];

        $validator = Validator::make(Input::all(), $rules, $messages);
        $validator->setAttributeNames($niceNames);
        if ($validator->fails()) {
            Session::flash('errorflag', 'error');
            return redirect('cp/usergroupmanagement/import-user-to-usergroup')->withInput()
                ->withErrors($validator);
        } else {
            $xlsfile = Input::file('xlsfile');
            $user_bulkimport_path = Config::get('app.user_bulkimport_path');

            $objPHPExcel = PHPExcel_IOFactory::load($xlsfile);
            $sheet = $objPHPExcel->getActiveSheet();
            $rows = $sheet->getHighestRow();
            $highestColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestDataColumn();
            $errorFlag = 0;
            $isEmpty = 0;
            $success_count = 0;
            $failed_count = 0;
            $emailTemp = [];
            $timezone = implode(',', Timezone::get());
            $errorData = null;
            $customFieldDataFromExcel = [];

            /* read the rows of the excel sheet one by one */
            for ($i = 1; $i <= $rows; ++$i) {
                $rowData = $sheet->rangeToArray('A' . $i . ":$highestColumn" . $i, null, true, false);
                if ($i == 1) {
                    $filteredCols = $rowData[0];
                    if (is_array($filteredCols)) {
                        // replace * sign with ''
                        $filteredCols = array_map(function ($val) {
                            return str_replace("*", '', $val);
                        }, $filteredCols);
                        $filteredCols = array_filter($filteredCols);
                    }
                    $filteredCols = array_map('strtolower', $filteredCols);
                    
                    /* Excel sheet cannot be uploaded without mandatory cols*/
                    $mandatoryCols = [trans('admin/user.username') , trans('admin/user.usergroup')];
                    $mandatoryCols = array_map('strtolower', $mandatoryCols);
                    $additional_column = array_diff($filteredCols, $mandatoryCols);
                    if (!empty($additional_column)) {
                        return Redirect::back()->with("error", trans('admin/user.invalid_template'));
                    }
                }

                $emailTemp = array_merge($emailTemp, $rowData);
                $excelRowData = [];

                if (!empty($rowData) && $i > 1) {
                    $isEmpty = 1;
                    $rowData = $rowData[0];
                    $rowData = Common::trimArray($rowData, false);
                    if (count($mandatoryCols) == count($rowData)) {
                        $excelRowData = array_combine($filteredCols, $rowData);
                        $emailTemp[$i - 1] = $excelRowData;
                        $excelRowData['usergroup'] = (strpos($excelRowData['usergroup'], ",")) ? explode(",", strtolower($excelRowData['usergroup'])) : [strtolower($excelRowData['usergroup'])];
                        $errorData = $this->validateUserUsergroupRulesExcel($excelRowData);
                        if ($errorData != false) {
                                $errorFlag = 1;
                                $errors = '';
                                $emailTemp[$i - 1]['record_status'] = 'Failed';

                            foreach ($errorData->all() as $message) {
                                $errors .= $message;
                            }
                                $emailTemp[$i - 1]['errors'] = $errors;
                                $failed_count = $failed_count + 1;
                        } else {
                            $user = null;
                            $user = $this->updateImportUserToUsergroup($excelRowData, $customFieldDataFromExcel);
                            if (isset($user)) {
                                $success_count = $success_count + 1;
                                $emailTemp[$i - 1]['record_status'] = 'Success';
                                $emailTemp[$i - 1]['errors'] = '';
                                $updateFlag = 1;
                            } else {
                                $errorFlag = 1;
                                $failed_count = $failed_count + 1;
                                $emailTemp[$i - 1]['record_status'] = 'Failed';
                                $emailTemp[$i - 1]['errors'] = '';
                            }
                        }
                    } else {
                        return redirect('cp/usergroupmanagement/import-user-to-usergroup')
                            ->with('error', trans('admin/user.bulk_import_update_column_error'));
                    }
                }
            }
            $no_of_records = $i - 2;
            $filename = $xlsfile->getClientOriginalName();
            if ($errorFlag) {
                if ($failed_count == $no_of_records) {
                    $status = 'FAILED';
                } else {
                    $status = 'PARTIAL';
                }
            } else {
                $status = 'SUCCESS';
            }

            $result = $emailTemp;
            unset($emailTemp[0]);

            //adding import history to the db
            UserimportHistory::getInsertHistory($filename, $success_count, $failed_count, $status, $no_of_records);

            // Check directory exits
            if (!is_dir($user_bulkimport_path)) {
                if (!mkdir($user_bulkimport_path, 0777, true)) {
                    die('Failed to create Role folder');
                }
                chmod($user_bulkimport_path, 0777);
            }

            // Check directory is writable
            if (is_writable($user_bulkimport_path)) {
                $xlsfile->move($user_bulkimport_path, $xlsfile->getClientOriginalName() . '.' . $xlsfile->getClientOriginalExtension());
            } else {
                die('Role directory is not writable');
            }

            if ($errorFlag) {
                Session::put('userxlsreport', $result);
                Session::flash('errorflag', 1);
                return redirect('cp/usergroupmanagement/import-user-to-usergroup');
            } else {
                Input::flush();
                Session::forget('userxlsreport');
                Session::forget('errorflag');

                if ($isEmpty == 0) {
                    return redirect('cp/usergroupmanagement/import-user-to-usergroup')
                        ->with('error', trans('admin/user.bulk_import_empty'));
                } elseif ($updateFlag == 1) {
                    return redirect('cp/usergroupmanagement/import-user-to-usergroup')
                        ->with('success', trans('admin/user.bulk_import_user_usergroup_success'));
                } else {
                }
            }
        }
    }

    /**
     * Method to replace custom field labels with field names
     * @param array $userData_WithCustomField
     * @return array
     */
    public function ReplaceCustomFieldLabel($userData_WithCustomField)
    {
        $field_labels = array_keys($userData_WithCustomField);
        $custom_field_details = $this->custom_field_service->getCustomFieldDetails($field_labels)->toArray();
        $fieldnames = array_column($custom_field_details, 'fieldname');
        $fieldlabels = array_column($custom_field_details, 'fieldlabel');
        $keys = array_flip(array_combine($fieldlabels, $fieldnames));
        $keys = array_keys($keys);
        $values = array_values($userData_WithCustomField);
        $userData_WithCustomField = array_combine($keys, $values);
        return $userData_WithCustomField;
    }

    public function getBulkDeleteUsers()
    {
        if (!has_admin_permission(ModuleEnum::USER, UserPermission::DELETE_USER)) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/user.manage_user') => 'usergroupmanagement',
            trans('admin/user.bulk_delete_users') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/user.bulk_delete_users');
        $this->layout->pageicon = 'fa fa-user';
        $this->layout->pagedescription = trans('admin/user.bulk_delete_users');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'users_groups')
            ->with('submenu', 'user');
        $this->layout->footer = view('admin.theme.common.footer');

        $context_data = $this->roleService->getContextDetails(Contexts::SYSTEM, true);

        $this->layout->content = view('admin.theme.users.bulk_delete_users')
                                 ->with('context_data', $context_data)
                                 ->with('timezones', Timezone::get())
                                 ->with('frequent_tz', Timezone::frequent());
    }

    /*
     * Purpose: Attach the custom fields with Users header fields and
    Force the download to the browser
     */

    public function getBulkDeleteUsersExcel()
    {
        if (!has_admin_permission(ModuleEnum::USER, UserPermission::DELETE_USER)) {
            return parent::getAdminError($this->theme_path);
        }

        $downloadUserXls = [trans('admin/user.username') . '*'];
        
        $excelObj = new PHPExcel();
        $excelObj->setActiveSheetIndex(0);
        $excelObj->getActiveSheet()->setTitle('Excel upload');
        $excelObj->getActiveSheet()->fromArray($downloadUserXls, null, 'A1');
        $filename = 'delete_bulk_users.xls'; //save our workbook as this file name
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); //mime type
        header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name
        header('Cache-Control: max-age=0'); //no cache
        /*save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007'
        (and adjust the filename extension, also the header mime type)
        if you want to save it as .XLSX Excel 2007 format*/
        $objWriter = PHPExcel_IOFactory::createWriter($excelObj, 'Excel2007');
        //force user to download the Excel file without writing it to server's HD
        $objWriter->save('php://output');
        exit;
    }

    /**
     * Method used to upload file and update data
     * @return nothing
     */
    public function postBulkDeleteUsers()
    {
        if (!has_admin_permission(ModuleEnum::USER, UserPermission::DELETE_USER)) {
            return parent::getAdminError($this->theme_path);
        }

        ini_set('max_execution_time', 300);

        $rules = [
            'xlsfile' => 'Required|allowexcel'
        ];
        $niceNames = [
            'xlsfile' => 'import file',
        ];
        Validator::extend('allowexcel', function ($attribute, $value, $parameters) {
            $mime = $value->getMimeType();
            if (in_array($mime, ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.oasis.opendocument.text', 'application/vnd.ms-excel',
                'application/zip', 'application/vnd.ms-office','application/octet-stream'])) {
                return true;
            }
            return false;
        });

        $messages = [];
        $messages += [
            'xlsfile.allowexcel' => 'Please upload only XLS file',
        ];

        $validator = Validator::make(Input::all(), $rules, $messages);
        $validator->setAttributeNames($niceNames);
        if ($validator->fails()) {
            Session::flash('errorflag', 'error');
            return redirect('cp/usergroupmanagement/bulk-delete-users')->withInput()
                ->withErrors($validator);
        } else {
            $xlsfile = Input::file('xlsfile');
            $user_bulkimport_path = Config::get('app.user_bulkimport_path');

            $objPHPExcel = PHPExcel_IOFactory::load($xlsfile);
            $sheet = $objPHPExcel->getActiveSheet();
            $rows = $sheet->getHighestRow();
            $highestColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestDataColumn();
            $errorFlag = 0;
            $isEmpty = 0;
            $success_count = 0;
            $failed_count = 0;
            $records = [];
            $timezone = implode(',', Timezone::get());
            $errorData = null;
            $customFieldDataFromExcel = [];

            /* read the rows of the excel sheet one by one */
            for ($i = 1; $i <= $rows; ++$i) {
                $rowData = $sheet->rangeToArray('A' . $i . ":$highestColumn" . $i, null, true, false);
                if ($i == 1) {
                    $filteredCols = array_get($rowData, 0, []);
                    if (is_array($filteredCols)) {
                        // replace * sign with ''
                        $filteredCols = array_map(function ($val) {
                            return str_replace("*", '', $val);
                        }, $filteredCols);
                        $filteredCols = array_filter($filteredCols);
                    }
                    $filteredCols = array_map('strtolower', $filteredCols);
                    
                    /* Excel sheet cannot be uploaded without mandatory cols*/
                    $mandatoryCols = [trans('admin/user.username')];
                    $mandatoryCols = array_map('strtolower', $mandatoryCols);
                    $additional_column = array_diff($filteredCols, $mandatoryCols);
                    if (!empty($additional_column)) {
                        return Redirect::back()->with("error", trans('admin/user.invalid_template'));
                    }
                }

                $records = array_merge($records, $rowData);
                $excelRowData = [];

                if (!empty($rowData) && $i > 1) {
                    $isEmpty = 1;
                    $rowData = array_get($rowData, 0, []);
                    $rowData = Common::trimArray($rowData, false);

                    if (count($mandatoryCols) == count($rowData)) {
                        $excelRowData = array_combine($filteredCols, $rowData);
                        $records[$i - 1] = $excelRowData;
                        $username = strtolower(array_get($excelRowData, 'username'));
                        $user = $this->user_service->getAllDetailsByUsername($username);

                        if (empty($user)) {
                            $errorFlag = 1;
                            $failed_count = $failed_count + 1;
                            $records[$i - 1]['record_status'] = 'Failed';
                            if (empty($username)) {
                                $records[$i - 1]['errors'] = trans('admin/user.username_require');
                            } else {
                                $records[$i - 1]['errors'] = trans('admin/user.check_username');
                            }
                        } else {
                            $user_details = $user->toArray();
                            if (isset($user_details['super_admin']) && $user_details['super_admin'] == true) {
                                $errorFlag = 1;
                                $failed_count = $failed_count + 1;
                                $records[$i - 1]['record_status'] = 'Failed';
                                $records[$i - 1]['errors'] = trans('admin/user.cant_delete_super_admin');
                            } else {
                                $usersessions = $user->session_ids;
                                if ($usersessions && is_array($usersessions)) {
                                    foreach ($usersessions as $sessionid) {
                                        if (file_exists(config('session.files') . '/' . $sessionid)) {
                                            unlink(config('session.files') . '/' . $sessionid);
                                        }
                                    }
                                }
                                $user->update(['session_ids' => []]);
                                $delete_users = $this->user_service->getDeleteUsers(
                                    array_get($user_details, 'uid'),
                                    $username,
                                    array_get($user_details, 'email')
                                );
                                $this->roleService->unmapUserAndRole(array_get($user_details, 'uid'));

                                $success_count = $success_count + 1;
                                $records[$i - 1]['record_status'] = 'Success';
                                $records[$i - 1]['errors'] = '';
                                $updateFlag = 1;
                            }
                        }
                    } else {
                        return redirect('cp/usergroupmanagement/bulk-delete-users')
                            ->with('error', trans('admin/user.bulk_import_update_column_error'));
                    }
                }
            }
            
            $no_of_records = $i - 2;
            $filename = $xlsfile->getClientOriginalName();
            if ($errorFlag) {
                $status = 'FAILED';
            } else {
                $status = 'SUCCESS';
            }

            $result = $records;
            unset($records[0]);
            
            //adding import history to the db
            UserimportHistory::getInsertHistory($filename, $success_count, $failed_count, $status, $no_of_records);

            // Check directory exits
            if (!is_dir($user_bulkimport_path)) {
                if (!mkdir($user_bulkimport_path, 0777, true)) {
                    die('Failed to create Role folder');
                }
                chmod($user_bulkimport_path, 0777);
            }

            // Check directory is writable
            if (is_writable($user_bulkimport_path)) {
                $xlsfile->move(
                    $user_bulkimport_path,
                    $xlsfile->getClientOriginalName() . '.' . $xlsfile->getClientOriginalExtension()
                );
            } else {
                die('Role directory is not writable');
            }

            if ($errorFlag) {
                Session::put('userxlsreport', $result);
                Session::flash('errorflag', 1);
                return redirect('cp/usergroupmanagement/bulk-delete-users');
            } else {
                Input::flush();
                Session::forget('userxlsreport');
                Session::forget('errorflag');

                if ($isEmpty == 0) {
                    return redirect('cp/usergroupmanagement/bulk-delete-users')
                        ->with('error', trans('admin/user.bulk_import_empty'));
                } elseif ($updateFlag == 1) {
                    return redirect('cp/usergroupmanagement/bulk-delete-users')
                        ->with('success', trans('admin/user.bulk_delete_users_success'));
                } else {
                }
            }
        }
    }
}
