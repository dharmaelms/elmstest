<?php

namespace App\Http\Controllers\Admin;

use App;
use App\Enums\Program\ProgramStatus;
use App\Enums\RolesAndPermissions\Contexts;
use App\Enums\RolesAndPermissions\PermissionType;
use App\Enums\RolesAndPermissions\SystemRoles;
use App\Enums\User\UserEntity;
use App\Events\Elastic\Items\ItemsAdded;
use App\Events\Elastic\Posts\PostAdded;
use App\Events\Elastic\Posts\PostEdited;
use App\Events\Elastic\Posts\PostRemoved;
use App\Events\Elastic\Programs\ProgramAdded;
use App\Events\Elastic\Programs\ProgramAssigned;
use App\Events\Elastic\Programs\ProgramRemoved;
use App\Events\Elastic\Programs\ProgramUpdated;
use App\Events\Elastic\Users\UsersAssigned;
use App\Events\User\EntityEnrollmentThroughUserGroup;
use App\Events\User\EntityUnenrollmentByAdminUser;
use App\Events\User\EntityUnenrollmentThroughUserGroup;
use App\Exceptions\ApplicationException;
use App\Exceptions\Post\QuestionNotFoundException;
use App\Exceptions\Program\ProgramNotFoundException;
use App\Helpers\DAMS\ScormHelper;
use App\Http\Controllers\AdminBaseController;
use App\Http\Validators\DAMS\ScormValidator;
use App\Model\Assignment\Entity\Assignment;
use App\Model\AccessRequest;
use App\Model\Category;
use App\Model\ChannelFaq;
use App\Model\ChannelFaqAnswers;
use App\Model\Common;
use App\Model\CustomFields\Entity\CustomFields;
use App\Model\Dam;
use App\Model\Email;
use App\Model\Event;
use App\Model\FlashCard;
use App\Model\NotificationLog;
use App\Model\Packet;
use App\Model\PacketFaq;
use App\Model\PacketFaqAnswers;
use App\Model\Package\Repository\IPackageRepository;
use App\Model\Program;
use App\Model\Quiz;
use App\Model\SiteSetting;
use App\Model\Transaction;
use App\Model\TransactionDetail;
use App\Model\User;
use App\Model\UserGroup;
use App\Model\Survey\Entity\Survey;
use App\Services\Catalog\Order\IOrderService;
use App\Services\PostFaqAnswer\IPostFaqAnswerService;
use App\Services\PostFaq\IPostFaqService;
use App\Services\Catalog\Pricing\IPricingService;
use App\Services\Country\ICountryService;
use App\Services\CustomFields\ICustomService;
use App\Services\Post\IPostService;
use App\Services\Program\IProgramService;
use App\Services\Tabs\ITabService;
use App\Services\TransactionDetail\ITransactionDetailService;
use App\Services\UserGroup\IUserGroupService;
use App\Services\UserCertificate\IUserCertificateService;
use App\Services\User\IUserService;
use App\Exceptions\User\UserGroupNotFoundException;
use App\Exceptions\RolesAndPermissions\UserRoleMappingNotFoundException;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\Package\PackagePermission;
use App\Enums\Program\ChannelPermission;
use App\Enums\Category\CategoryPermission;
use App\Enums\Program\ElementType;
use App\Enums\Announcement\AnnouncementPermission;
use App\Events\User\EntityEnrollmentByAdminUser;
use App\Enums\Course\CoursePermission;
use Carbon\Carbon;
use Auth;
use Config;
use Dompdf\Dompdf;
use Exception;
use Imagick;
use Input;
use PHPExcel;
use PHPExcel_IOFactory;
use Redirect;
use Request;
use Session;
use Timezone;
use URL;
use Validator;
use ZipArchive;
use Log;

class ContentFeedManagementController extends AdminBaseController
{
    protected $layout = 'admin.theme.layout.master_layout';
    private $userID = 0;
    private $userGroups = [];
    private $priceService = null;
    private $tabServ = null;
    private $countryService = null;
    private $customSer;
    private $post_service;
    private $post_faq_answer;
    private $post_faq_service;
    private $programservice;
    private $orderService;
    private $packageRepository;
    private $transaction_details_service;
    private $user_certificate_service;
    private $user_service;

    /**
     * @var IUserGroupService
     */
    private $userGroupService;

    public function __construct(
        Request $request,
        IPricingService $priceService,
        ITabService $tabs,
        ICountryService $countryService,
        ICustomService $customService,
        IUserGroupService $userGroupService,
        IPostService $post_service,
        IPostFaqAnswerService $post_faq_ans_service,
        IPostFaqService $post_faq_service,
        IProgramService $programservice,
        IOrderService $orderService,
        IPackageRepository $packageRepository,
        ITransactionDetailService $transaction_details_service,
        IUserCertificateService $user_certificate_service,
        IUserService $user_service
    ) {
        parent::__construct();

        // Stripping all html tags from the request body
        $input = $request::input();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request::merge($input);
        $this->userID = Auth::user()->uid;
        $relations = Auth::user()->relations;
        if (isset($relations['active_usergroup_user_rel']) && !empty($relations['active_usergroup_user_rel'])) {
            $this->userGroups = $relations['active_usergroup_user_rel'];
        }
        $this->theme_path = 'admin.theme';

        //Pricing Service
        $this->priceService = $priceService;
        //Tab Service
        $this->tabServ = $tabs;
        $this->countryService = $countryService;
        $this->customSer = $customService;
        $this->post_faq_ans = $post_faq_ans_service;
        $this->post_faq = $post_faq_service;
        $this->post_service = $post_service;
        $this->userGroupService = $userGroupService;
        $this->programservice = $programservice;
        $this->orderService = $orderService;
        $this->packageRepository = $packageRepository;
        $this->transaction_details_service = $transaction_details_service;
        $this->user_certificate_service = $user_certificate_service;
        $this->user_service = $user_service;
    }

    public function getIndex()
    {
        $this->getListFeeds();
    }

    public function getListFeeds()
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/program.manage_channel') => 'contentfeedmanagement',
            trans('admin/program.list_program') => '',
        ];
        $viewmode = Input::get('view', 'desktop');
        $relfilter = Input::get('relfilter', 'all');
        $from = Input::get('from', 'none');
        $relid = Input::get('relid', 'none');
        $subtype = Input::get('subtype', 'all');
        $filters = Input::get('filters', 'all');
        if ($from == 'category') {
            $field = 'program_categories';
        } elseif ($from == 'usergroup') {
            $field = 'relations.active_usergroup_feed_rel';
        } elseif ($from == 'contentfeed') {
            $field = 'parent_relations.active_parent_rel';
        } elseif($from == "announcement") {
            $field = 'relations.contentfeed_announcement_rel';
        } else {
            $field = 'relations.active_user_feed_rel';
        }
        if ($viewmode == 'iframe') {
            $this->layout->breadcrumbs = '';
            $this->layout->pagetitle = '';
            $this->layout->pageicon = '';
            $this->layout->pagedescription = '';
            $this->layout->header = '';
            $this->layout->sidebar = '';
            $this->layout->content = view('admin.theme.programs.listcontentfeediframe')
                ->with('relfilter', $relfilter)
                ->with('from', $from)
                ->with('subtype', $subtype)
                ->with('field', $field)
                ->with('filters', $filters)
                ->with('relid', $relid);
            $this->layout->footer = '';
        } else {
            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = trans('admin/program.manage_channel');
            $this->layout->pageicon = 'fa fa-rss';
            $this->layout->pagedescription = trans('admin/program.manage_channel');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'program')
                ->with('submenu', 'contentfeed');
            $this->layout->content = view('admin.theme.programs.listcontentfeed');
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }

    public function getFeedListAjax()
    {
        $user_id = $this->request->user()->uid;
        $list_channel_permission_info_with_flag = [];
        $has_list_permission = false;
        $viewmode = Input::get('view', 'desktop');
        switch ($viewmode) {
            case "desktop":
                $list_channel_permission_info_with_flag = $this->roleService->hasPermission(
                    $user_id,
                    ModuleEnum::CHANNEL,
                    PermissionType::ADMIN,
                    ChannelPermission::LIST_CHANNEL,
                    Contexts::PROGRAM,
                    null,
                    true
                );
                $has_list_permission = get_permission_flag($list_channel_permission_info_with_flag);
                break;
            case "iframe":
                switch (Input::get('from', 'none')) {
                    case "user":
                        $list_channel_permission_info_with_flag = $this->roleService->hasPermission(
                            $user_id,
                            ModuleEnum::CHANNEL,
                            PermissionType::ADMIN,
                            ChannelPermission::CHANNEL_ASSIGN_USER,
                            Contexts::PROGRAM,
                            null,
                            true
                        );
                        $has_list_permission = get_permission_flag($list_channel_permission_info_with_flag);
                        break;
                    case 'usergroup':
                        $list_channel_permission_info_with_flag = $this->roleService->hasPermission(
                            $user_id,
                            ModuleEnum::CHANNEL,
                            PermissionType::ADMIN,
                            ChannelPermission::CHANNEL_ASSIGN_USER_GROUP,
                            Contexts::PROGRAM,
                            null,
                            true
                        );
                        $has_list_permission = get_permission_flag($list_channel_permission_info_with_flag);
                        break;
                    case 'category':
                        $list_channel_permission_info_with_flag = $this->roleService->hasPermission(
                            $user_id,
                            ModuleEnum::CATEGORY,
                            PermissionType::ADMIN,
                            CategoryPermission::ASSIGN_CHANNEL,
                            Contexts::PROGRAM,
                            null,
                            true
                        );
                        $has_list_permission = get_permission_flag($list_channel_permission_info_with_flag);
                        break;
                    case "announcement":
                        $list_channel_permission_info_with_flag = $this->roleService->hasPermission(
                            $user_id,
                            ModuleEnum::ANNOUNCEMENT,
                            PermissionType::ADMIN,
                            AnnouncementPermission::ASSIGN_CHANNEL,
                            Contexts::PROGRAM,
                            null,
                            true
                        );
                        $has_list_permission = get_permission_flag($list_channel_permission_info_with_flag);
                }
                break;
        }

        if (!$has_list_permission) {
            $finaldata = [
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ];

            return response()->json($finaldata);
        }

        $filter_params = [];
        $list_permission_data = get_permission_data($list_channel_permission_info_with_flag);
        $list_channel_filter_params = has_system_level_access($list_permission_data)?
            [] : ["in_ids" => get_instance_ids($list_permission_data, Contexts::PROGRAM)];
        $filter_params = array_merge($filter_params, $list_channel_filter_params);

        $start = 0;
        $limit = 10;

        $search = Input::get('search');
        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['program_startdate' => 'desc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '1') {
                $orderByArray = ['program_title' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '2') {
                $orderByArray = ['program_startdate' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '3') {
                $orderByArray = ['program_enddate' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '4' || $order_by[0]['column'] == '8') {
                $orderByArray = ['status' => $order_by[0]['dir']];
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
        $filter = strtolower($filter);
        $filters = Input::get('filters');
        $filters = strtolower($filters);
        if (!in_array($filter, ['active', 'in-active'])) {
            $filter = 'all';
        } else {
            $filter = strtoupper($filter);
        }
        if (!in_array($filters, ['single', 'collection'])) {
            $filters = 'all';
        }

        $relfilter = Input::get('relfilter', 'all');
        $from = Input::get('from', 'none');
        $relid = Input::get('relid', 'none');
        $subtype = Input::get('subtype', 'all');
        $field = Input::get('field', 'relations.active_user_feed_rel');
        $userID = $this->userID;
        $userGroups = $this->userGroups;

        if ($viewmode == "iframe") {
            $relinfo = [$from => $relid];

            $filteredRecords = Program::getContentFeedCount(
                $relfilter,
                $searchKey,
                $relinfo,
                null,
                null,
                $subtype,
                'all',
                $field,
                'all',
                'all',
                null,
                null,
                null,
                null,
                null,
                null,
                [],
                [],
                'all',
                null,
                null,
                '=',
                '=',
                $filter_params
            );

            $filtereddata = Program::getContentFeedWithTypeAndPagination(
                $relfilter,
                $start,
                $limit,
                $orderByArray,
                $searchKey,
                $relinfo,
                null,
                null,
                $subtype,
                'all',
                $field,
                'all',
                'all',
                null,
                null,
                null,
                null,
                null,
                null,
                [],
                [],
                'all',
                null,
                null,
                '=',
                '=',
                $filter_params
            );
        } else {
            $visibility = Input::get('visibility', 'all');
            $sellability = Input::get('sellability', 'all');
            $access = Input::get('access', 'all');
            $access = ($sellability == 'no' || $sellability == 'all') ? 'all' : $access;
            $feed_title = Input::get('feed_title');
            $shortname = Input::get('shortname');
            $created_date = Input::get('created_date');
            $updated_date = Input::get('updated_date');
            $description = Input::get('descriptions');
            $feed_tags = Input::get('feed_tags');
            $category = Input::get('category');
            $channel_name = Input::get('channel_name', '');
            $get_created_date = Input::get('get_created_date', '=');
            $get_updated_date = Input::get('get_updated_date', '=');
            $custom_field_name = [];
            $custom_field_value = [];
            $pgmCustomField = CustomFields::getUserActiveCustomField(
                $program_type = 'content_feed',
                $filters,
                $status = 'ACTIVE'
            );

            if (!empty($pgmCustomField)) {
                foreach ($pgmCustomField as $key => $pgm_field) {
                    $custom_field_name[] = $pgm_field["fieldname"];
                    $custom_field_value[] = Input::get($pgm_field["fieldname"]);
                }
            }

            if ($searchKey != '') {
                $filter = 'all';
                $visibility = 'all';
                $sellability = 'all';
                $access = 'all';
                $feed_title = '';
                $shortname = '';
                $created_date = '';
                $updated_date = '';
                $description = '';
                $feed_tags = '';
                $category = '';
                $channel_name = '';
                $custom_field_name = [];
                $custom_field_value = [];
            }

            $filteredRecords = Program::getContentFeedCount(
                $filter,
                $searchKey,
                null,
                $userID,
                $userGroups,
                $subtype,
                $filters,
                $field,
                $visibility,
                $sellability,
                $feed_title,
                $shortname,
                $created_date,
                $updated_date,
                $description,
                $feed_tags,
                $custom_field_name,
                $custom_field_value,
                $access,
                $category,
                $channel_name,
                $get_created_date,
                $get_updated_date,
                $filter_params
            );

            $filtereddata = Program::getContentFeedWithTypeAndPagination(
                $filter,
                $start,
                $limit,
                $orderByArray,
                $searchKey,
                null,
                $userID,
                $userGroups,
                $subtype,
                $filters,
                $field,
                $visibility,
                $sellability,
                $feed_title,
                $shortname,
                $created_date,
                $updated_date,
                $description,
                $feed_tags,
                $custom_field_name,
                $custom_field_value,
                $access,
                $category,
                $channel_name,
                $get_created_date,
                $get_updated_date,
                $filter_params
            );
        }
        $totalRecords = $filteredRecords;

        $dataArr = [];
        foreach ($filtereddata as $key => $value) {
            $program_title = $value['program_title'];
            if (isset($value['program_shortname']) && !empty($value['program_shortname'])) {
                $program_shortname = $value['program_shortname'];
            } else {
                $program_shortname = 'NA';
            }
            $packets = Program::getPacketsCount($value['program_slug']);
            $actions = '';
            if ($this->roleService->hasPermission(
                $user_id,
                ModuleEnum::CHANNEL,
                PermissionType::ADMIN,
                ChannelPermission::VIEW_CHANNEL,
                Contexts::PROGRAM,
                $value["program_id"]
            )) {
                $actions .= '<a class="btn btn-circle show-tooltip viewfeed" title="' . trans('admin/manageweb.action_view') . '"
                href="' . URL::to('/cp/contentfeedmanagement/feed-details/' . $value['program_slug']) . '" >
                    <i class="fa fa-eye"></i>
                </a>';
            } else {
                $actions .= '<a class="badge badge-info show-tooltip" title="'.trans('admin/program.no_permission_to_view_details').'"
                href="javascript:void;" >NA</a>';
            }

            if ($this->roleService->hasPermission(
                $user_id,
                ModuleEnum::CHANNEL,
                PermissionType::ADMIN,
                ChannelPermission::EDIT_CHANNEL,
                Contexts::PROGRAM,
                $value["program_id"]
            )) {
                $actions .= '<a class="btn btn-circle show-tooltip" title="' . trans('admin/manageweb.action_edit') . '"
                 href="' . URL::to('/cp/contentfeedmanagement/edit-feed/' . $value['program_slug']) .
                    '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey .
                    '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" >
                    <i class="fa fa-edit"></i>
                 </a>';
            } else {
                $actions .= '<a class="badge badge-info show-tooltip" title="'.trans('admin/program.no_permission_to_edit').'"
                href="javascript:void;" >NA</a>';
            }

            if ($this->roleService->hasPermission(
                $user_id,
                ModuleEnum::CHANNEL,
                PermissionType::ADMIN,
                ChannelPermission::DELETE_CHANNEL,
                Contexts::PROGRAM,
                $value["program_id"]
            )) {
                $actions .= '<a class="btn btn-circle show-tooltip deletefeed" title="' . trans('admin/manageweb.action_delete') . '" href="' . URL::to('/cp/contentfeedmanagement/delete-feed/' . $value['program_slug']) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-trash-o"></i></a>';
            } else {
                $actions .= '<a class="badge badge-info show-tooltip" title="'.trans('admin/program.no_permission_to_delete').'"
                href="javascript:void;" >NA</a>';
            }

            $temparr = [
                '<input type="checkbox" value="' . $value['program_id'] . '">',
                $program_title,
                $program_shortname,
                Timezone::convertFromUTC('@' . $value['program_startdate'], Auth::user()->timezone, config('app.date_format')),
                Timezone::convertFromUTC('@' . $value['program_enddate'], Auth::user()->timezone, config('app.date_format')),
                ucfirst(strtolower($value['status'])),
            ];
            if ($actions) {
                $temparr[] = $actions;
            }
            if ($viewmode != 'iframe') {
                $has_assign_category_permission = $this->roleService->hasPermission(
                    $user_id,
                    ModuleEnum::CHANNEL,
                    PermissionType::ADMIN,
                    ChannelPermission::CHANNEL_ASSIGN_CATEGORY,
                    Contexts::PROGRAM,
                    $value["program_id"]
                );

                if ($has_assign_category_permission) {
                    $category = '<a href="' . URL::to('/cp/categorymanagement/categories?view=iframe&filter=ACTIVE') . '"
                     title="' . trans('admin/program.assign_cat') . '" class="show-tooltip feedrel badge badge-grey"
                      data-key="' . $value['program_slug'] . '" data-info="category"
                       data-text="Assign Category to <b>' . htmlentities('"' . $value['program_title'] . '"', ENT_QUOTES) . '</b>"
                        data-json="">' . 0 . '</a>';
                } else {
                    $category = '<a class="badge badge-info show-tooltip" title="'.trans('admin/program.no_permission_to_assign_category').'"
                    href="javascript:void;" >NA</a>';
                }

                if (isset($value['program_categories']) && count($value['program_categories'])) {
                    if ($has_assign_category_permission) {
                        $category = '<a href="' . URL::to('/cp/categorymanagement/categories?view=iframe&filter=ACTIVE') . '" title="' . trans('admin/program.assign_cat') . '" class="show-tooltip feedrel badge badge-grey badge-success" data-key="' . $value['program_slug'] . '" data-info="category" data-text="Assign Category to <b>' . htmlentities('"' . $value['program_title'] . '"', ENT_QUOTES) . '</b>" data-json="' . json_encode($value['program_categories']) . '">' . count($value['program_categories']) . '</a>';
                    } else {
                        $category = '<a class="badge badge-info show-tooltip" title="'.trans('admin/program.no_permission_to_assign_category').'"
                        href="javascript:void;" >NA</a>';
                    }
                }

                $has_assign_user_permission = $this->roleService->hasPermission(
                    $user_id,
                    ModuleEnum::CHANNEL,
                    PermissionType::ADMIN,
                    ChannelPermission::CHANNEL_ASSIGN_USER,
                    Contexts::PROGRAM,
                    $value["program_id"]
                );

                if ($has_assign_user_permission) {
                    if ($value['program_sub_type'] == 'single' || ($value['program_sub_type'] != 'single' && isset($value['child_relations']['active_channel_rel']) && !empty($value['child_relations']['active_channel_rel']))) {
                        $userCount = '<a href="' . URL::to('/cp/contentfeedmanagement/edit-feed/' . $value['program_slug']) .
                            '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey .
                            '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '#channel-users-tab"
                            title="' . trans('admin/program.assign_user') . '"
                            class="show-tooltip badge badge-grey" data-key="' . $value['program_slug'] . '">' . 0 . '</a>';
                    } else {
                        $userCount = '<a href="" onclick="return false;" title="' . trans('admin/program.no_channel_to_assign') . '" class="show-tooltip badge badge-grey" data-key="' . $value['program_slug'] . '" data-info="user" data-text="Assign User(s) to <b>' . htmlentities('"' . $value['program_title'] . '"', ENT_QUOTES) . '</b>" data-json="">' . 0 . '</a>';
                    }
                } else {
                    $userCount = '<a class="badge badge-info show-tooltip" title="'.trans('admin/program.no_permi_to_assign_user').'"
                        href="javascript:void;" >NA</a>';
                }

                $has_assign_user_group_permission = $this->roleService->hasPermission(
                    $user_id,
                    ModuleEnum::CHANNEL,
                    PermissionType::ADMIN,
                    ChannelPermission::CHANNEL_ASSIGN_USER_GROUP,
                    Contexts::PROGRAM,
                    $value["program_id"]
                );

                if ($has_assign_user_group_permission) {
                    if ($value['program_sub_type'] == 'single' || ($value['program_sub_type'] != 'single' && isset($value['child_relations']['active_channel_rel']) && !empty($value['child_relations']['active_channel_rel']))) {
                        $userGroupCount = '<a href="' . URL::to('/cp/usergroupmanagement/user-groups?filter=ACTIVE&view=iframe&from=contentfeed&relid=' . $value['program_id']) . '" title="' . trans('admin/program.assign_usergroup') . '" class="show-tooltip feedrel badge badge-grey" data-key="' . $value['program_slug'] . '" data-info="usergroup" data-text="Assign User Group(s) to <b>' . htmlentities('"' . $value['program_title'] . '"', ENT_QUOTES) . '</b>" data-json="">' . 0 . '</a>';
                    } else {
                        $userGroupCount = '<a href="" onclick="return false;" title="' . trans('admin/program.no_channel_to_assign') . '" class="show-tooltip badge badge-grey" data-key="' . $value['program_slug'] . '" data-info="usergroup" data-text="Assign User Group(s) to <b>' . htmlentities('"' . $value['program_title'] . '"', ENT_QUOTES) . '</b>" data-json="">' . 0 . '</a>';
                    }
                } else {
                    $userGroupCount = '<a class="badge badge-info show-tooltip" title="'.trans('admin/program.no_permission_to_assign_user_group').'"
                        href="javascript:void;" >NA</a>';
                }
                if (isset($value['relations']) && !empty($value['relations'])) {
                    if (isset($value['relations']['active_user_feed_rel']) && !empty($value['relations']['active_user_feed_rel'])) {
                        if ($has_assign_user_permission) {
                            if ($value['program_sub_type'] == 'single' || ($value['program_sub_type'] != 'single' && isset($value['child_relations']['active_channel_rel']) && !empty($value['child_relations']['active_channel_rel']))) {
                                $userCount = '<a href="' . URL::to('/cp/contentfeedmanagement/edit-feed/' . $value['program_slug']) .
                                    '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey .
                                    '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '#channel-users-tab"
                                            title="' . trans('admin/program.assign_user') . '" class="show-tooltip badge badge-success">'
                                    . count($value['relations']['active_user_feed_rel']) .
                                    '</a>';
                            } else {
                                $userCount = '<a href="" onclick="return false;" title="' . trans('admin/program.no_channel_to_assign') . '" class="show-tooltip badge badge-success" data-key="' . $value['program_slug'] . '" data-info="user" data-text="Assign User(s) to <b>' . htmlentities('"' . $value['program_title'] . '"', ENT_QUOTES) . '</b>" >' . count($value['relations']['active_user_feed_rel']) . '</a>';
                            }
                        } else {
                            $userCount = '<a class="badge badge-info show-tooltip" title="'.trans('admin/program.no_permi_to_assign_user').'"
                        href="javascript:void;" >NA</a>';
                        }
                    }
                    if (isset($value['relations']['active_usergroup_feed_rel']) && !empty($value['relations']['active_usergroup_feed_rel'])) {
                        if ($has_assign_user_group_permission) {
                            if ($value['program_sub_type'] == 'single' || ($value['program_sub_type'] != 'single' && isset($value['child_relations']['active_channel_rel']) && !empty($value['child_relations']['active_channel_rel']))) {
                                $userGroupCount = "<a href='" . URL::to('/cp/usergroupmanagement/user-groups?filter=ACTIVE&view=iframe&from=contentfeed&relid=' . $value['program_id']) . "' title='" . trans('admin/program.assign_usergroup') . "' class='show-tooltip feedrel badge badge-success' data-key='" . $value['program_slug'] . "' data-info='usergroup' data-text='Assign User Group(s) to <b>" . htmlentities('"' . $value['program_title'] . '"', ENT_QUOTES) . "</b>' data-json='" . json_encode($value['relations']['active_usergroup_feed_rel']) . "'>" . count($value['relations']['active_usergroup_feed_rel']) . '</a>';
                            } else {
                                $userGroupCount = "<a href='' onclick='return false' title='" . trans('admin/program.no_channel_to_assign') . "' class='show-tooltip badge badge-success' data-key='" . $value['program_slug'] . "' data-info='usergroup' data-text='Assign User Group(s) : <b>" . htmlentities('"' . $value['program_title'] . '"', ENT_QUOTES) . "</b>'>" . count($value['relations']['active_usergroup_feed_rel']) . '</a>';
                            }
                        } else {
                            $userGroupCount = '<a class="badge badge-info show-tooltip" title="'.trans('admin/program.no_permission_to_assign_user_group').'"
                            href="javascript:void;" >NA</a>';
                        }
                    }
                }

                $has_permission_to_manage_posts = $this->roleService->hasPermission(
                    $user_id,
                    ModuleEnum::CHANNEL,
                    PermissionType::ADMIN,
                    ChannelPermission::MANAGE_CHANNEL_POST,
                    Contexts::PROGRAM,
                    $value["program_id"]
                );

                if ($has_permission_to_manage_posts) {
                    $packetsHTML = "<a href='" . URL::to('/cp/contentfeedmanagement/packets/'.$value['program_type'].'/'. $value['program_slug']) . "' title='" . trans('admin/program.manage_posts') . "' class='show-tooltip badge badge-grey'>" . $packets . '</a>';
                } else {
                    $packetsHTML = '<a class="badge badge-info show-tooltip" title="'.trans('admin/program.no_permission_to_manage_posts').'"
                            href="javascript:void;" >NA</a>';
                }

                if ($packets) {
                    if ($has_permission_to_manage_posts) {
                        $packetsHTML = "<a href='" . URL::to('/cp/contentfeedmanagement/packets/'.$value['program_type'].'/'. $value['program_slug']) . "' title='" . trans('admin/program.manage_posts') . "' class='show-tooltip badge badge-grey badge-info '>" . $packets . '</a>';
                    } else {
                        $packetsHTML = '<a class="badge badge-info show-tooltip" title="'.trans('admin/program.no_permission_to_manage_posts').'"
                            href="javascript:void;" >NA</a>';
                    }
                }

                array_splice($temparr, 5, 0, [$category, $packetsHTML, $userCount, $userGroupCount]);
            }
            if ($viewmode == 'iframe') {
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

    public function postAssignFeed($action = null, $slug = null)
    {
        $msg = null;
        $program = Program::getAllProgramByIDOrSlug('content_feed', $slug);
        $ids = Input::get('ids');
        $user_role_mapping = Input::get("user_role_mapping", []);
        $empty = Input::get('empty');

        if ($ids) {
            $ids = array_map("intval", explode(',', $ids));
        } else {
            $ids = [];
        }
        if (!$empty || !in_array($action, ['user', 'usergroup', 'category', 'collection'])) {
            if (empty($program) || !$slug || !is_array($ids) || empty($ids)) {
                $msg = trans('admin/program.missing_program');

                return response()->json(['flag' => 'error', 'message' => $msg]);
            }
        }

        $program = $program->toArray();
        $program = $program[0];

        $has_assign_feed_permission = false;
        switch ($action) {
            case "user":
                $has_assign_feed_permission = $this->roleService->hasPermission(
                    $this->request->user()->uid,
                    ModuleEnum::CHANNEL,
                    PermissionType::ADMIN,
                    ChannelPermission::CHANNEL_ASSIGN_USER,
                    Contexts::PROGRAM,
                    $program["program_id"]
                );
                $msg = $has_assign_feed_permission? null : trans("admin/program.no_permission_to_assign_user");
                break;
            case "usergroup":
                $has_assign_feed_permission = $this->roleService->hasPermission(
                    $this->request->user()->uid,
                    ModuleEnum::CHANNEL,
                    PermissionType::ADMIN,
                    ChannelPermission::CHANNEL_ASSIGN_USER_GROUP,
                    Contexts::PROGRAM,
                    $program["program_id"]
                );
                $msg = $has_assign_feed_permission? null : trans("admin/program.no_permission_to_assign_user_group");
                break;
            case "category":
                $has_assign_feed_permission = $this->roleService->hasPermission(
                    $this->request->user()->uid,
                    ModuleEnum::CHANNEL,
                    PermissionType::ADMIN,
                    ChannelPermission::CHANNEL_ASSIGN_CATEGORY,
                    Contexts::PROGRAM,
                    $program["program_id"]
                );
                $msg = $has_assign_feed_permission? null : trans("admin/program.no_permission_to_assign_category");
        }

        if (!$has_assign_feed_permission) {
            return response()->json(['flag' => 'error', 'message' => $msg]);
        }

        //To get learner role_id and context_id
        $role_info = $this->roleService->getRoleDetails(SystemRoles::LEARNER, ['context']);
        $context_info = $this->roleService->getContextDetails(Contexts::PROGRAM, false);
        $role_id = array_get($role_info, 'id', '');
        $context_id = array_get($context_info, 'id', '');

        $program_context = $this->roleService->getContextDetails(Contexts::PROGRAM);
        $instance_id = array_get($program, 'program_id', '');

        if ($action == 'user') {
            $arrname = 'active_user_feed_rel';
            $msg = trans('admin/user.user_assigned');
        }
        if ($action == 'usergroup') {
            $arrname = 'active_usergroup_feed_rel';
            $msg = trans('admin/user.usergroup_assigned');
        }

        //start of channel to pkg
        if ($action == 'collection') {
            if (isset($program['relations']['active_user_feed_rel']) && !empty($program['relations']['active_user_feed_rel'])) {
                $msg = trans('admin/program.active_user_exist');
                return response()->json(['flag' => 'error', 'message' => $msg]);
            }
            if (isset($program['relations']['active_usergroup_feed_rel']) && !empty($program['relations']['active_usergroup_feed_rel'])) {
                $msg = trans('admin/program.active_usergroup_exist');
                return response()->json(['flag' => 'error', 'message' => $msg]);
            }
            if (isset($program['child_relations']['active_channel_rel'])) {
                $deleted = array_diff($program['child_relations']['active_channel_rel'], $ids);
                $ids = array_diff($ids, $program['child_relations']['active_channel_rel']);
                if (!empty($deleted)) {
                    foreach ($deleted as $value) {
                        Program::removeParentRelation($value, ['active_parent_rel'], (int)$program['program_id']);
                        Program::where('program_id', (int)$program['program_id'])->pull('child_relations.active_channel_rel', $value, true);
                    }
                }
            }
            if (!empty($ids)) {
                $ids = array_values($ids);
            }
            if (!empty($deleted)) {
                $deleted = array_values($deleted);
            }
            if (!empty($ids)) {
                foreach ($ids as $value) {
                    $value = (int)$value;
                    $now = time();
                    Program::addParentRelation($value, ['active_parent_rel'], (int)$program['program_id']);
                    Program::updateChildRelation((int)$program['program_id'], ['active_channel_rel'], (int)$value);
                }
            }
            $msg = trans('admin/program.channel_assigned_success');
            return response()->json(['flag' => 'success', 'message' => $msg]);
        }

        $key = (int)$program['program_id'];
        $deleted = [];
        if (isset($program['relations'])) {
            if ($action == 'user' && isset($program['relations']['active_user_feed_rel']) && $program['program_sub_type'] != 'collection') {
                // Code to remove relations from user collection
                $enrollment_action = Input::get("enrollment_action", null);
                if (!is_null($enrollment_action)) {
                    if ($enrollment_action === "ASSIGN") {
                        $deleted = [];
                    } elseif ($enrollment_action === "UNASSIGN") {
                        $deleted = $ids;
                        $ids = [];
                        $msg = trans('admin/user.user_unassigned');
                    }
                } else {
                    $deleted = array_diff($program['relations']['active_user_feed_rel'], $ids);
                    $ids = array_diff($ids, $program['relations']['active_user_feed_rel']);
                }
                foreach ($deleted as $value1) {
                    User::removeUserRelation($value1, ['user_feed_rel'], (int)$program['program_id']);

                    event(new EntityUnenrollmentByAdminUser($value1, UserEntity::PROGRAM, $program["program_id"]));

                    $this->roleService->unmapUserAndRole($value1, $program_context["id"], $program["program_id"]);

                    TransactionDetail::updateStatusByLevel(
                        'user',
                        $value1,
                        (int)$program['program_id'],
                        ['status' => 'IN-ACTIVE']
                    );
                    // Also inactive the user transaction

                    // Remove the access granted rel (if its thr)
                    Program::removeFeedRelation($key, ['access_request_granted'], $value1);
                }
            } elseif ($action == 'user' && isset($program['relations']['active_user_feed_rel']) && $program['program_sub_type'] == 'collection') {
                $deleted = array_diff($program['relations']['active_user_feed_rel'], $ids);
                $ids = array_diff($ids, $program['relations']['active_user_feed_rel']);
                foreach ($deleted as $value1) {
                    //transaction & user
                    foreach ($program['child_relations']['active_channel_rel'] as $channel_id) {
                        User::removeUserRelation($value1, ['user_package_feed_rel'], (int)$channel_id);
                        TransactionDetail::updateStatusByLevel('user', $value1, (int)$channel_id, ['status' => 'IN-ACTIVE'], $type = 'collection', (int)$program['program_id']);
                    }
                    //end
                    User::removeUserRelation($value1, ['user_parent_feed_rel'], (int)$program['program_id']);
                    TransactionDetail::updateStatusByLevel('user', $value1, (int)$program['program_id'], ['status' => 'IN-ACTIVE'], $type = 'collection', (int)$program['program_id']);
                    Program::removeFeedRelation($key, ['access_request_granted'], $value1);
                }
            } elseif ($action == 'usergroup' && isset($program['relations']['active_usergroup_feed_rel'])) {
                //collection code

                // Code to remove relations from usergroup collection
                $deleted = array_diff($program['relations']['active_usergroup_feed_rel'], $ids);
                $ids = array_diff($ids, $program['relations']['active_usergroup_feed_rel']);
                foreach ($deleted as $value2) {
                    // User role assignments
                    try {
                        $usergroup_info = $this->userGroupService->getUserGroupDetails($value2);
                        $usergroup_rel = $usergroup_info->relations;
                        $user_usergroup_rel_ids = array_get($usergroup_rel, 'active_user_usergroup_rel', '');
                    } catch (UserGroupNotFoundException $e) {
                        Log::info(trans('admin/user.usergroup_not_found', ['id' => $value2]));
                    }

                    if (!empty($user_usergroup_rel_ids)) {
                        foreach ($user_usergroup_rel_ids as $user_id) {
                            event(new EntityUnenrollmentThroughUserGroup(
                                $user_id,
                                UserEntity::PROGRAM,
                                $instance_id,
                                $value2
                            ));

                            $this->roleService->unmapUserAndRole((int)$user_id, $context_id, $instance_id);
                        }
                    }

                    UserGroup::removeUserGroupRelation($value2, ['usergroup_feed_rel'], (int)$program['program_id']);
                    $type = 'null';

                    TransactionDetail::updateStatusByLevel('usergroup', $value2, (int)$program['program_id'], ['status' => 'IN-ACTIVE'], $type, (int)$program['program_id']);
                    // Also inactive the user/usergroup transaction
                }
            }
        }
        if ($action == 'category' && isset($program['program_categories'])) {
            // Code to remove relations from category collection
            $deleted = array_diff($program['program_categories'], $ids);
            $ids = array_diff($ids, $program['program_categories']);
            foreach ($deleted as $value3) {
                Category::removeCategoryRelation($value3, ['assigned_feeds'], (int)$program['program_id']);
            }
        }
        $notify_user_ids = $ids = array_values($ids); //  This is to reset the index. [Problem: when using array_diff, the keys gets altered which converts normal array to associative array.]
        $deleted = array_values($deleted); //  This is to reset the index. [Problem: when using array_diff, the keys gets altered which converts normal array to associative array.]
        $notify_log_flg = true;
        $notify_user_ids_ary = [];
        foreach ($ids as &$value) {
            $value = (int)$value;
            $now = time();

            if ($action == 'user') {
                User::addUserRelation($value, ['user_feed_rel'], $program['program_id']);

                event(new EntityEnrollmentByAdminUser($value, UserEntity::PROGRAM, $program["program_id"]));

                $this->roleService->mapUserAndRole(
                    $value,
                    $program_context["id"],
                    array_has($user_role_mapping, $value) ? $user_role_mapping[$value] : $role_id,
                    $program["program_id"]
                );
                $trans_id = Transaction::uniqueTransactionId();
                $userdetails = User::getUserDetailsByID($value)->toArray();
                $email = '';
                if (isset($userdetails['email'])) {
                    $email = $userdetails['email'];
                }
                $transaction = [
                    'DAYOW' => Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'l'),
                    'DOM' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'j'),
                    'DOW' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'w'),
                    'DOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'z'),
                    'MOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'n'),
                    'WOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'W'),
                    'YEAR' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'Y'),
                    'trans_level' => 'user',
                    'id' => $value,
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
                    'id' => $value,
                    'trans_id' => (int)$trans_id,
                    'program_id' => $program['program_id'],
                    'program_slug' => $program['program_slug'],
                    'type' => 'content_feed',
                    'program_title' => $program['program_title'],
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
                if (Config::get('app.notifications.contentfeed.feedadd') && $notify_log_flg) {
                    $notify_log_flg = false;
                    $notif_msg = trans('admin/notifications.feedadd', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'feed' => $program['program_title']]);
                    // Notification::getInsertNotification($value, trans('admin/program.program'), $notif_msg);
                    NotificationLog::getInsertNotification($notify_user_ids, trans('admin/program.program'), $notif_msg);
                }
                // Send Mail Notifications to the user
                if (Config::get('email.notifications.contentfeed.feedadd')) {
                    $email = Email::getEmail('feed_assignment')->first()->toArray();
                    if (isset($email['body']) && isset($email['subject'])) {
                        $body = str_replace('[:adminname:]', Auth::user()->firstname . ' ' . Auth::user()->lastname, $email['body']);
                        $body = str_replace('[:feed:]', $program['program_title'], $body);
                        Common::sendMailHtml($body, $email['subject'], $email);
                    }
                }
            } elseif ($action == 'usergroup') {
                // User role assignments
                try {
                    $usergroup_info = $this->userGroupService->getUserGroupDetails($value);
                    $usergroup_rel = $usergroup_info->relations;
                    $user_usergroup_rel_ids = array_get($usergroup_rel, 'active_user_usergroup_rel', '');
                } catch (UserGroupNotFoundException $e) {
                    Log::info(trans('admin/user.usergroup_not_found', ['id' =>  $value]));
                }

                if (!empty($user_usergroup_rel_ids)) {
                    foreach ($user_usergroup_rel_ids as $user_id) {
                        event(new EntityEnrollmentThroughUserGroup(
                            $user_id,
                            UserEntity::PROGRAM,
                            $instance_id,
                            $value
                        ));

                        $this->roleService->mapUserAndRole((int)$user_id, $context_id, $role_id, $instance_id);
                    }
                }

                UserGroup::addUserGroupRelation($value, ['usergroup_feed_rel'], $program['program_id']);

                $trans_id = Transaction::uniqueTransactionId();
                $transaction = [
                    'DAYOW' => Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'l'),
                    'DOM' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'j'),
                    'DOW' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'w'),
                    'DOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'z'),
                    'MOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'n'),
                    'WOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'W'),
                    'YEAR' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'Y'),
                    'trans_level' => 'usergroup',
                    'id' => $value,
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
                    'id' => $value,
                    'trans_id' => (int)$trans_id,
                    'program_id' => $program['program_id'],
                    'program_slug' => $program['program_slug'],
                    'type' => 'content_feed',
                    'program_title' => $program['program_title'],
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
                if (Config::get('app.notifications.contentfeed.feedadd')) {
                    $usergroup_data = UserGroup::getUserGroupsUsingID((int)$value);
                    foreach ($usergroup_data as $usergroup) {
                        if (isset($usergroup['relations']['active_user_usergroup_rel'])) {
                            $notify_user_ids_ary = array_merge($notify_user_ids_ary, $usergroup['relations']['active_user_usergroup_rel']);
                        }
                    }
                }

                // Send Mail Notifications to the user
                if (Config::get('email.notifications.contentfeed.feedadd')) {
                    $usergroup_data = UserGroup::getUserGroupsUsingID((int)$value);
                    $emailtemplate = Email::getEmail('feed_assignment')->first()->toArray();
                    foreach ($usergroup_data as $usergroup) {
                        if (isset($usergroup['relations']['active_user_usergroup_rel'])) {
                            foreach ($usergroup['relations']['active_user_usergroup_rel'] as $user) {
                                $userdetails = User::getUserDetailsByID($user)->toArray();
                                if (isset($emailtemplate['body']) && isset($emailtemplate['subject'])) {
                                    $body = str_replace('[:adminname:]', Auth::user()->firstname . ' ' . Auth::user()->lastname, $emailtemplate['body']);
                                    $body = str_replace('[:feed:]', $program['program_title'], $body);
                                    Common::sendMailHtml($body, $emailtemplate['subject'], $userdetails['email']);
                                }
                            }
                        }
                    }
                }
            } elseif ($action == 'category') {
                Category::updateCategoryRelation($value, 'assigned_feeds', $program['program_id']);
            }
        }
        if (!empty($notify_user_ids_ary)) {
            $notif_msg = trans('admin/notifications.feedadd', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'feed' => $program['program_title']]);
            NotificationLog::getInsertNotification($notify_user_ids_ary, trans('admin/program.program'), $notif_msg);
            $notify_user_ids_ary = [];
        }
        if ($action == 'category') {
            if (!empty($ids)) {
                Program::updateFeedCategories($key, $ids);
            }
            if (!empty($deleted)) {
                Program::removeFeedCategories($key, $deleted);
            }
            $msg = trans('admin/category.category');

            if ((count($ids) > 1) || (count($deleted) > 1)) {
                $msg = trans('admin/category.categories');
            }
            $msg = $msg . trans('admin/category.assigned_success');
        } else {
            if (!empty($ids)) {
                Program::updateFeedRelation($key, $arrname, $ids);
            }
            $notify_user_ids = $deleted;
            $temp_flag = true;
            $notify_user_ids_ary = [];
            foreach ($deleted as $value) {
                $value = (int)$value;
                if ($action == 'user') {
                    if (Config::get('app.notifications.contentfeed.feedrevoke') && $temp_flag) {
                        $temp_flag = false;
                        $notif_msg = trans('admin/notifications.feedrevoke', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'feed' => $program['program_title'], 'channel' => config('app.cahnnel_name')]);
                        NotificationLog::getInsertNotification($notify_user_ids, trans('admin/program.program'), $notif_msg);
                    }
                } elseif ($action == 'usergroup') {
                    if (Config::get('app.notifications.contentfeed.feedrevoke')) {
                        $usergroup_data = UserGroup::getUserGroupsUsingID((int)$value);
                        foreach ($usergroup_data as $usergroup) {
                            if (isset($usergroup['relations']['active_user_usergroup_rel'])) {
                                $notify_user_ids_ary = array_merge($notify_user_ids_ary, $usergroup['relations']['active_user_usergroup_rel']);
                            }
                        }
                    }
                }
            }
            if (!empty($notify_user_ids_ary)) {
                $notif_msg = trans('admin/notifications.feedrevoke', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'feed' => $program['program_title'], 'channel' => config('app.cahnnel_name')]);
                NotificationLog::getInsertNotification($notify_user_ids_ary, trans('admin/program.program'), $notif_msg);
                $notify_user_ids_ary = [];
            }
            Program::where('program_id', $key)->pull('relations.' . $arrname, $deleted, true);
        }
        if ($action == 'user' || $action == 'usergroup') {
            if (config('elastic.service')) {
                event(new UsersAssigned($program['program_id']));
            }
        }
        return response()->json(['flag' => 'success', 'message' => $msg]);
    }

    /**
     * Using user id and program id updating user role assignment
     */
    public function postUpdateRole($action = null, $slug = null)
    {
        if (!empty($slug)) {
            $program = Program::getAllProgramByIDOrSlug('content_feed', $slug);
            //To get learner role_id and context_id
            $context_info = $this->roleService->getContextDetails(Contexts::PROGRAM, false);
            $context_id = array_get($context_info, 'id', '');
            $ids = Input::get('ids');
            $user_role_mapping = Input::get("user_role_mapping", []);

            if ($ids) {
                $ids = array_map("intval", explode(',', $ids));
            } else {
                $ids = [];
            }

            if (empty($program) || !is_array($ids) || empty($ids) || empty($user_role_mapping)) {
                $msg = trans('admin/program.missing_program');
                return response()->json(['flag' => 'error', 'message' => $msg]);
            }

            $program = $program->toArray();
            $program = head($program);
            $instance_id = array_get($program, 'program_id', '');

            foreach ($ids as $user_id) {
                try {
                    $this->roleService->updateMapUserAndRole(
                        (int)$user_id,
                        (int)$context_id,
                        (int)$user_role_mapping[$user_id],
                        (int)$instance_id
                    );
                } catch (UserRoleMappingNotFoundException $e) {
                    Log::info(trans('admin/role.user_role_assignment_not_found', ['program_id' =>  $instance_id, 'user_id' => $user_id]));
                }
            }

            $msg = trans('admin/role.role_update');
            return response()->json(['flag' => 'success', 'message' => $msg]);
        } else {
            $msg = trans('admin/program.missing_program');
            return response()->json(['flag' => 'error', 'message' => $msg]);
        }
    }

    public function getAddFeeds()
    {
        if (!has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::ADD_CHANNEL)) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/program.manage_channel') => 'contentfeedmanagement',
            trans('admin/program.title_add_channel') => '',
        ];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/program.add_channel');
        $this->layout->pageicon = 'fa fa-rss';
        $this->layout->pagedescription = trans('admin/program.add_channel');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'program')
            ->with('submenu', 'contentfeed');

        $this->layout->content = view('admin.theme.programs.addcontentfeed');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function postAddFeeds()
    {
        if (!has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::ADD_CHANNEL)) {
            return parent::getAdminError($this->theme_path);
        }

        $programShortnameSlug = Input::get('feed_shortname_slug', '');
        Validator::extend('checkslug', function ($attribute, $value, $parameters) use ($programShortnameSlug) {
            $slug = 'content-feed-' . $value;
            if (!empty($programShortnameSlug)) {
                $slug .= '-' . $programShortnameSlug;
            }
            $returnval = Program::where('program_slug', '=', $slug)
                ->where('program_type', '=', 'content_feed')
                ->where('status', '!=', 'DELETED')
                ->get(['program_slug'])->toArray();
            if (empty($returnval)) {
                return true;
            }

            return false;
        });
        Validator::extend('checkslugregex', function ($attribute, $value, $parameters) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $value)) {
                return true;
            }

            return false;
        });
        Validator::extend('datecheck', function ($attribute, $value, $parameters) {
            $feed_start_date = Input::get('feed_start_date');
            $feed_end_date = Input::get('feed_end_date');
            // if((strtotime($pub_date) + 432000) ) // 432000 is for 5 days. Skipped this calculation for now
            if ((strtotime($feed_start_date) < strtotime($feed_end_date))) {
                return true;
            }

            return false;
        });
        Validator::extend('displaydatecheck', function ($attribute, $value, $parameters) {
            $feed_display_start_date = Input::get('feed_display_start_date');
            $feed_display_end_date = Input::get('feed_display_end_date');
            if ((strtotime($feed_display_start_date) < strtotime($feed_display_end_date))) {
                return true;
            }

            return false;
        });
        Validator::extend('displaystartdatecheck', function ($attribute, $value, $parameters) {
            $feed_start_date = Input::get('feed_start_date');
            $feed_display_start_date = Input::get('feed_display_start_date');
            if ((strtotime($feed_display_start_date) >= strtotime($feed_start_date))) {
                return true;
            }

            return false;
        });
        Validator::extend('displayenddatecheck', function ($attribute, $value, $parameters) {
            $feed_end_date = Input::get('feed_end_date');
            $feed_display_end_date = Input::get('feed_display_end_date');
            if ((strtotime($feed_display_end_date) <= strtotime($feed_end_date))) {
                return true;
            }

            return false;
        });

        $messages = [
            'displaystartdatecheck' => trans('admin/program.disp_start_date_great_than_start_date'),
            'displayenddatecheck' => trans('admin/program.disp_end_date_less_than_end_date'),
            'displaydatecheck' => trans('admin/program.disp_end_date_greater_than_disp_start_date'),
            'datecheck' => trans('admin/program.date_check'),
            'checkslug' => trans('admin/program.channel_check_slug'),
            'checkslugregex' => trans('admin/program.channel_check_slug_regex'),
            'feed_title.required' => trans('admin/program.channel_field_required'),
            'min' => trans('admin/program.shortname'),
        ];

        $rules = [
            'feed_title' => 'Required',
            'feed_slug' => 'Required|checkslugregex|checkslug',
            'program_shortname' => 'min:3',
            'feed_start_date' => 'Required',
            'feed_end_date' => 'Required|datecheck',
            'feed_display_start_date' => 'Required|displaystartdatecheck',
            'feed_display_end_date' => 'Required|displaydatecheck|displayenddatecheck',
            'visibility' => 'Required|in:yes,no',
            'status' => 'Required|in:active,inactive',
        ];

        if (config('app.ecommerce')) {
            $rules += ['sellability' => 'Required|in:yes,no'];
        } else {
            $rules += ['program_access' => 'Required|in:restricted_access,general_access'];
        }

        $productid = Program::uniqueProductId();
        $validation = Validator::make(Input::all(), $rules, $messages);
        if ($validation->fails()) {
            return redirect('cp/contentfeedmanagement/add-feeds/')->withInput()
                ->withErrors($validation);
        } elseif ($validation->passes()) {
            $status = 'IN-ACTIVE';
            $feed_media_rel = 'contentfeed_media_rel';
            if (Input::get('status') == 'active') {
                $status = 'ACTIVE';
            }
            $mediaid = Input::get('banner', '');
            $program_keywords = explode(',', Input::get('feed_tags'));
            if (!$program_keywords) {
                $program_keywords = [];
            }
            $speedTemp = Input::get('speed', '0:0');
            $temp = explode(':', trim($speedTemp));
            if (!empty($temp) && count($temp) > 1) {
                $speedTime = (($temp[0] * 60) + $temp[1]);
            } else {
                $speedTime = 0;
            }
            $score = (int)Input::get('score', 0);
            $accuracy = (int)Input::get('accuracy', 0);

            if (!empty(Input::get('feed_shortname_slug', ''))) {
                $program_slug = 'content-feed-' . Input::get('feed_slug') . '-' . Input::get('feed_shortname_slug', '');
            } else {
                $program_slug = 'content-feed-' . Input::get('feed_slug');
            }

            $feedData = [
                'program_id' => $productid,
                'program_title' => trim(Input::get('feed_title')),
                'title_lower' => trim(strtolower(Input::get('feed_title'))),
                'program_shortname' => Input::get('program_shortname'),
                'program_slug' => $program_slug,
                'program_description' => Input::get('feed_description'),
                'program_startdate' => (int)Timezone::convertToUTC(Input::get('feed_start_date'), Auth::user()->timezone, 'U'),
                'program_enddate' => (int)Timezone::convertToUTC(Carbon::createFromFormat('d-m-Y', Input::get('feed_end_date'))->endOfDay(), Auth::user()->timezone, 'U'),
                'program_display_startdate' => (int)Timezone::convertToUTC(Input::get('feed_display_start_date'), Auth::user()->timezone, 'U'),
                'program_display_enddate' => (int)Timezone::convertToUTC(Carbon::createFromFormat('d-m-Y', Input::get('feed_display_end_date'))->endOfDay(), Auth::user()->timezone, 'U'),
                'program_duration' => '',
                'program_review' => 'no',
                'program_rating' => 'no',
                'program_visibility' => Input::get('visibility'),
                'program_keywords' => $program_keywords,
                'program_cover_media' => $mediaid,
                'program_type' => 'content_feed',
                'program_sub_type' => 'single',
                'duration' => [ // Duration may have more than one object like different subscription plans
                    [
                        'label' => 'Forever',
                        'days' => 'forever',
                    ],
                ],
                'benchmarks' => [
                    'speed' => $speedTime,
                    'score' => $score,
                    'accuracy' => $accuracy,
                ],
                'program_categories' => [],
                'last_activity' => time(),
                'status' => $status,
                'created_by' => Auth::user()->username,
                'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                'created_at' => time(),
                'updated_at' => time(),
            ];

            if (config('app.ecommerce')) {
                $feedData += [
                    'program_sellability' => Input::get('sellability'),
                    'program_access' => 'restricted_access'
                ];
            } else {
                $feedData += [
                    'program_access' => Input::get('program_access'),
                    'program_sellability' => 'yes'
                ];
            }

            Program::insert($feedData);
            if (config('elastic.service')) {
                event(new ProgramAdded($productid));
            }
            //Insert custom fields
            $this->customSer->insertNewProgramCustomFields($productid, "channelfields");

            Dam::removeMediaRelation($mediaid, ['contentfeed_media_rel'], (int)$productid);
            if ($mediaid) {
                Dam::updateDAMSRelation($mediaid, $feed_media_rel, (int)$productid);
            }
            $msg = trans('admin/program.content_feed_add_success');

            if (Input::get('sellability') === 'yes' && config('app.ecommerce') === true) {
                return redirect(URL::to('cp/pricing/add-price/' . $program_slug))->with('success', $msg);
            }
            return redirect("cp/contentfeedmanagement/add-feed-success/content_feed/{$program_slug}")
                ->with('success', $msg);
        }
    }

    public function getEditFeed($slug)
    {
        $programs = Program::getAllProgramByIDOrSlug('content_feed', $slug);
        if ($slug == null || $programs->isEmpty()) {
            $msg = trans('admin/program.slug_missing_error');

            return redirect('/cp/contentfeedmanagement/')
                ->with('error', $msg);
        }

        $program = $programs->toArray();

        if (!$this->roleService->hasPermission(
            $this->request->user()->uid,
            ModuleEnum::CHANNEL,
            PermissionType::ADMIN,
            ChannelPermission::EDIT_CHANNEL,
            Contexts::PROGRAM,
            $programs->first()->program_id
        )) {
            return parent::getAdminError($this->theme_path);
        }

        $url = '';
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/program.manage_channel') => 'contentfeedmanagement/',
            trans('admin/program.edit_channel') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/program.edit_channel');
        $this->layout->pageicon = 'fa fa-rss';
        $this->layout->pagedescription = trans('admin/program.edit_channel');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'program')
            ->with('submenu', 'contentfeed');

        $pri_ser_data = $this->setPricingService($programs);
        $tabs = $this->setTabService($programs);
        $feedCF = $this->customSer->getFormCustomFields('channelfields');
        $packageCF = $this->customSer->getFormCustomFields('packagefields');
        $subscription_array = $this->priceService->getSubscriptionArray($program[0]['program_id']);
        $this->layout->content = view('admin.theme.programs.editcontentfeed')
            ->with('pri_ser_info', $pri_ser_data)
            ->with('programs', $programs)
            ->with('prgm', $program)
            ->with('tabs', $tabs)
            ->with('url', $url)
            ->with('feedCF', $feedCF)
            ->with('packageCF', $packageCF)
            ->with('subscription_array', $subscription_array);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    private function setTabService($programs)
    {
        $program_id = null;
        foreach ($programs as $value) {
            $program_id = $value->program_id;
        }

        $r_data = $this->tabServ->getTabs($program_id);
        if (!empty($r_data)) {
            foreach ($r_data as $key => $value) {
                if (!empty($value['tabs'])) {
                    $tabs = $value['tabs'];
                } else {
                    $tabs = null;
                }
                return [
                    'p_id' => $program_id,
                    'tabs' => $tabs,
                ];
            }
        } else {
            return null;
        }
    }

    private function setPricingService($programs)
    {
        $returnData = [];
        if (!empty($programs) && $programs->first()) {
            //multicurrency support
            $returnData['currency_support_list'] = $this->countryService->supportedCurrencies();

            $value = $programs->first();

            $returnData['pri_service'] = 'enabled';
            $returnData['program_slug'] = $value->program_slug;
            $returnData['program_sellability'] = $value->program_sellability;

            $inputdata = [
                'sellable_id' => $value->program_id,
                'sellable_type' => $value->program_type,
            ];

            $returnData = array_merge($returnData, $inputdata);

            if ($value->program_type == "content_feed") {
                $returnData['pri_ser_data'] = $this->priceService->priceFirst($inputdata);
            } elseif ($value->program_type === 'course') {
                $returnData['pri_ser_data'] = $this->priceService->listVertical($inputdata);
            } else {
                $returnData['pri_service'] = 'disabled';
            }

            return $returnData;
        } else {
            $returnData['pri_service'] = 'disabled';
        }

        return $returnData;
    }

    public function postEditFeed($slug)
    {
        $programs = Program::getAllProgramByIDOrSlug('content_feed', $slug);
        if ($slug == null || $programs->isEmpty()) {
            $msg = trans('admin/program.slug_missing_error');

            return redirect('/cp/contentfeedmanagement/')
                ->with('error', $msg);
        }

        $program = $programs->toArray();

        if (!$this->roleService->hasPermission(
            $this->request->user()->uid,
            ModuleEnum::CHANNEL,
            PermissionType::ADMIN,
            ChannelPermission::EDIT_CHANNEL,
            Contexts::PROGRAM,
            $programs->first()->program_id
        )) {
            return parent::getAdminError($this->theme_path);
        }

        Input::flash();
        $program_shortname = strtolower(Input::get('program_shortname'));
        $old_slug = $program[0]['program_slug'];
        $old_shortname = isset($program[0]['program_shortname']) ? $program[0]['program_shortname'] : []; //Input::get('old_shortname');
        $feed_title = trim(strtolower(Input::get('feed_title')));

        $programShortnameSlug = Input::get('feed_shortname_slug', '');
        Validator::extend('checkslug', function ($attribute, $value, $parameters) use ($programShortnameSlug, $old_slug, $program_shortname, $old_shortname) {
            if ($old_slug == $value && $old_shortname == $program_shortname) {
                return true;
            }
            $slug = 'content-feed-' . $value;
            if (!empty($programShortnameSlug)) {
                $slug .= '-' . $programShortnameSlug;
            }
            $returnval = Program::where('program_slug', '=', $slug)
                ->where('program_type', '=', 'content_feed')
                ->where('status', '!=', 'DELETED')
                ->whereNotIn('_id', $parameters)->get()->toArray();
            if (empty($returnval)) {
                return true;
            }
            return false;
        });

        Validator::extend('checkstatus', function ($attribute, $value, $parameters) {
            $parameters = array_filter($parameters);
            if ($value == 'inactive') {
                if (is_array($parameters) && !empty($parameters)) {
                    return false;
                }

                return true;
            }

            return true;
        });
        Validator::extend('checkslugregex', function ($attribute, $value, $parameters) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $value)) {
                return true;
            }

            return false;
        });
        Validator::extend('datecheck', function ($attribute, $value, $parameters) {
            $feed_start_date = Input::get('feed_start_date');
            $feed_end_date = Input::get('feed_end_date');
            if ((strtotime($feed_start_date) < strtotime($feed_end_date))) {
                return true;
            }

            return false;
        });
        Validator::extend('displaydatecheck', function ($attribute, $value, $parameters) {
            $feed_display_start_date = Input::get('feed_display_start_date');
            $feed_display_end_date = Input::get('feed_display_end_date');
            if ((strtotime($feed_display_start_date) < strtotime($feed_display_end_date))) {
                return true;
            }

            return false;
        });
        Validator::extend('displaystartdatecheck', function ($attribute, $value, $parameters) {
            $feed_start_date = Input::get('feed_start_date');
            $feed_display_start_date = Input::get('feed_display_start_date');
            if ((strtotime($feed_display_start_date) >= strtotime($feed_start_date))) {
                return true;
            }

            return false;
        });
        Validator::extend('displayenddatecheck', function ($attribute, $value, $parameters) {
            $feed_end_date = Input::get('feed_end_date');
            $feed_display_end_date = Input::get('feed_display_end_date');
            if ((strtotime($feed_display_end_date) <= strtotime($feed_end_date))) {
                return true;
            }

            return false;
        });

        $messages = [
            'displaystartdatecheck' => trans('admin/program.disp_start_date_great_than_start_date'),
            'displayenddatecheck' => trans('admin/program.disp_end_date_less_than_end_date'),
            'displaydatecheck' => trans('admin/program.disp_end_date_greater_than_disp_start_date'),
            'datecheck' => trans('admin/program.date_check'),
            'checkslug' => trans('admin/program.channel_check_slug'),
            'checkslugregex' => trans('admin/program.channel_check_slug_regex'),
            'feed_title.required' => trans('admin/program.channel_field_required'),
            'min' => trans('admin/program.shortname'),
        ];

        $relations = '';

        if (isset($programs[0]->relations['active_user_feed_rel']) && !empty($programs[0]->relations['active_user_feed_rel'])) {
            $rel = $programs[0]->relations['active_user_feed_rel'];
            $relations = implode(',', $rel);
            $messages['status.checkstatus'] = trans('admin/program.cannot_deactivate_program');
        }

        if (isset($programs[0]->relations['active_usergroup_feed_rel']) && !empty($programs[0]->relations['active_usergroup_feed_rel'])) {
            $rel = $programs[0]->relations['active_usergroup_feed_rel'];
            $relations = implode(',', $rel);
            $messages['status.checkstatus'] = trans('admin/program.usergroup_deactivate_program');
        }

        $rules = [
            'feed_title' => 'Required',
            'program_shortname' => 'min:3',
            'feed_slug' => 'Required|checkslugregex|checkslug:' . $programs[0]->_id,
            'feed_start_date' => 'Required',
            'feed_end_date' => 'Required|datecheck',
            'feed_display_start_date' => 'Required|displaystartdatecheck',
            'feed_display_end_date' => 'Required|displaydatecheck|displayenddatecheck',
            'visibility' => 'Required|in:yes,no',
            'status' => 'Required|in:active,inactive|checkstatus:' . $relations,
        ];

        if (config('app.ecommerce')) {
            $rules += ['sellability' => 'Required|in:yes,no'];
        } else {
            $rules += ['program_access' => 'Required|in:restricted_access,general_access'];
        }

        $programdata = $programs->first()->toArray();
        $validation = Validator::make(Input::all(), $rules, $messages);
        if ($validation->fails()) {
            return redirect('cp/contentfeedmanagement/edit-feed/' . $slug)->withInput()
                ->withErrors($validation);
        } elseif ($validation->passes()) {
            // Send notification to the users that there is a change in the meta data
            if (Config::get('app.notifications.contentfeed.metadatachange')) {
                if (isset($programdata['relations']['active_user_feed_rel'])) {
                    $notify_user_ids_ary = [];
                    $notify_user_ids_ary = $programdata['relations']['active_user_feed_rel'];
                    // foreach ($programdata['relations']['active_user_feed_rel'] as $user) {
                    $notif_msg = trans('admin/notifications.feedmetachange', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'feed' => $programdata['program_title']]);
                    NotificationLog::getInsertNotification($notify_user_ids_ary, trans('admin/program.program'), $notif_msg);
                    // }
                }
                if (isset($programdata['relations']['active_usergroup_feed_rel'])) {
                    $notify_user_ids_ary = [];
                    foreach ($programdata['relations']['active_usergroup_feed_rel'] as $usergroupid) {
                        $usergroup_data = UserGroup::getUserGroupsUsingID((int)$usergroupid);
                        foreach ($usergroup_data as $usergroup) {
                            if (isset($usergroup['relations']['active_user_usergroup_rel'])) {
                                $notify_user_ids_ary = array_merge($notify_user_ids_ary, $usergroup['relations']['active_user_usergroup_rel']);
                            }
                        }
                    }
                    if (!empty($notify_user_ids_ary)) {
                        $notif_msg = trans('admin/notifications.feedmetachange', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'feed' => $programdata['program_title']]);
                        NotificationLog::getInsertNotification($notify_user_ids_ary, trans('admin/program.program'), $notif_msg);
                    }
                }
            }

            $status = 'IN-ACTIVE';
            $feed_media_rel = 'contentfeed_media_rel';
            if (Input::get('status') == 'active') {
                $status = 'ACTIVE';
            }
            $mediaid = Input::get('banner', '');
            $program_keywords = explode(',', Input::get('feed_tags'));
            if (!$program_keywords) {
                $program_keywords = [];
            }

            $feed_slug = Input::get('feed_slug');
            if ($old_slug != $feed_slug || $old_shortname != $program_shortname) {
                $new_slug = 'content-feed-' . $feed_slug;
                if (!empty($programShortnameSlug)) {
                    $new_slug .= '-' . $programShortnameSlug;
                }
            } else {
                $new_slug = $old_slug;
            }
            $speedTemp = Input::get('speed', '0:0');
            $temp = explode(':', trim($speedTemp));
            if (!empty($temp) && count($temp) > 1) {
                $speedTime = (($temp[0] * 60) + $temp[1]) * 60;
            } else {
                $speedTime = 0;
            }
            $score = (int)Input::get('score', 0);
            $accuracy = (int)Input::get('accuracy', 0);

            $feedData = [
                'program_title' => trim(Input::get('feed_title')),
                'title_lower' => trim(strtolower(Input::get('feed_title'))),
                'program_shortname' => Input::get('program_shortname'),
                'program_slug' => $new_slug,
                'program_description' => Input::get('feed_description'),
                'program_startdate' => (int)Timezone::convertToUTC(Input::get('feed_start_date'), Auth::user()->timezone, 'U'),
                'program_enddate' => (int)Timezone::convertToUTC(Carbon::createFromFormat('d-m-Y', Input::get('feed_end_date'))->endOfDay(), Auth::user()->timezone, 'U'),
                'program_display_startdate' => (int)Timezone::convertToUTC(Input::get('feed_display_start_date'), Auth::user()->timezone, 'U'),
                'program_display_enddate' => (int)Timezone::convertToUTC(Carbon::createFromFormat('d-m-Y', Input::get('feed_display_end_date'))->endOfDay(), Auth::user()->timezone, 'U'),
                'program_duration' => '',
                'program_visibility' => Input::get('visibility'),
                'program_keywords' => $program_keywords,
                'program_cover_media' => $mediaid,
                'program_type' => 'content_feed',
                'program_sub_type' => 'single',
                'benchmarks' => [
                    'speed' => $speedTime,
                    'score' => $score,
                    'accuracy' => $accuracy,
                ],
                'last_activity' => time(),
                'status' => $status,
                'updated_at' => time(),
                'updated_by' => Auth::user()->username,
                'updated_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
            ];


            if (config('app.ecommerce')) {
                $feedData += [
                    'program_sellability' => Input::get('sellability'),
                    'program_access' => array_has($programdata, "program_access") ?
                        $programdata['program_access'] : "restricted_access"
                ];
            } else {
                $feedData += [
                    'program_access' => Input::get('program_access'),
                    'program_sellability' => array_has($programdata, "program_sellability") ?
                        $programdata['program_sellability'] : "yes"
                ];
            }

            Dam::removeMediaRelation($programs[0]->program_cover_media, [$feed_media_rel], (int)$programs[0]->program_id);
            if ($mediaid) {
                Dam::updateDAMSRelation($mediaid, $feed_media_rel, (int)$programs[0]->program_id);
            }

            $user_count = $user_group_count = $child_count = $parent_count = 0;
            $post_count = Packet::where('feed_slug', '=', $slug)
                ->where('status', '!=', 'DELETED')
                ->count();

            if (isset($programs->first()->relations)) {
                if (isset($programs->first()->relations['active_user_feed_rel'])) {
                    $user_count = count($programs->first()->relations['active_user_feed_rel']);
                }
                if (isset($programs->first()->relations['active_usergroup_feed_rel'])) {
                    $user_group_count = count($programs->first()->relations['active_usergroup_feed_rel']);
                }
            }
            if (isset($programs->first()->child_relations) && isset($programs->first()->child_relations['active_channel_rel'])) {
                $child_count = count($programs->first()->child_relations['active_channel_rel']);
            }
            if (isset($programs->first()->parent_relations) && isset($programs->first()->parent_relations['active_parent_rel'])) {
                $parent_count = count($programs->first()->parent_relations['active_parent_rel']);
            }

            if ($user_count > 0 || $user_group_count > 0 || $child_count > 0 || $parent_count > 0 || $post_count > 0) {
                $program_sub_type = array_get($programdata, 'program_sub_type');
            } else {
                $program_sub_type = Input::get('program_sub_type');
                $feedData += ['program_sub_type' => $program_sub_type];
            }

            $updated_program = Program::where('program_slug', '=', $slug)->where('program_type', '=', 'content_feed')->where('status', '!=', 'DELETED')->update($feedData);

            Packet::where('feed_slug', '=', $slug)->update(['feed_slug' => $new_slug]);
            //to remove parent-child relations
            Program::updateParentChild($slug, 'single');
            TransactionDetail::where('program_slug', '=', $slug)->where('type', '=', 'content_feed')->update(['program_slug' => $new_slug, 'program_sub_type' => 'single']);
            $this->orderService->updateSlug($slug, $new_slug);
            $slug_changed = $old_slug != $new_slug;
            if (config('elastic.service')) {
                event(new ProgramUpdated($program[0]['program_id'], $slug_changed));
            }
            $msg = trans('admin/program.content_feed_edit_success');
            return redirect('cp/contentfeedmanagement/')
                ->with('success', $msg);
        }
    }

    public function getDeleteFeed($slug = '')
    {
        $programs = Program::getAllProgramByIDOrSlug('content_feed', $slug);

        $start = Input::get('start', 0);
        $limit = Input::get('limit', 10);
        $filter = Input::get('filter', 'all');
        $search = Input::get('search', '');
        $order_by = Input::get('order_by', '3 desc');

        if ($slug == null || empty($programs)) {
            $msg = trans('admin/program.slug_missing_error');

            return redirect('/cp/contentfeedmanagement?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $search . '&order_by=' . $order_by)
                ->with('error', $msg);
        }

        $program = $programs->first();
        if (!$this->roleService->hasPermission(
            $this->request->user()->uid,
            ModuleEnum::CHANNEL,
            PermissionType::ADMIN,
            ChannelPermission::DELETE_CHANNEL,
            Contexts::PROGRAM,
            $program->program_id
        )) {
            return parent::getAdminError($this->theme_path);
        }

        $url = 'list-feeds';

        if ($program->packages()->filter([])->count() > 0) {
            $msg = trans('admin/program.content_feed_delete_rel_package_error');

            return redirect('cp/contentfeedmanagement/' . $url)
                ->with('error', $msg);
        }

        $program = $program->toArray();
        // Check if any user or user group is linked with this content feed
        // If true dont delete else delete the content feed
        if (isset($program['relations']) && is_array($program['relations']) && !empty($program['relations'])) {
            foreach ($program['relations'] as $k => $v) {
                if (is_array($v) && count($v)) {
                    if ($k == 'active_user_feed_rel' && count($v) == 1 && is_array($v) && in_array(Auth::user()->uid, $v)) {
                        ;
                    } else {
                        $msg = trans('admin/program.content_feed_delete_rel_error');

                        return redirect('cp/contentfeedmanagement/' . $url)
                            ->with('error', $msg);
                    }
                }
            }
        }

        // Unlink all the related categories
        if (isset($program['program_categories']) && is_array($program['program_categories'])) {
            foreach ($program['program_categories'] as $category) {
                Category::removeCategoryRelation($category, ['assigned_feeds'], (int)$program['program_id']);
            }
        }

        // Get all packets and remove the dams relations
        $packets = Packet::getAllPackets($program['program_slug']);
        foreach ($packets as $val) {
            if (isset($val['packet_cover_media']) && $val['packet_cover_media']) {
                Dam::removeMediaRelation($val['packet_cover_media'], ['packet_banner_media_rel'], (int)$val['packet_id']);
            }
            if (isset($val['elements']) && is_array($val['elements'])) {
                foreach ($val['elements'] as $element) {
                    if ($element['type'] == 'media') {
                        Dam::removeMediaRelationUsingID($element['id'], ['dams_packet_rel'], (int)$val['packet_id']);
                    } elseif ($element['type'] == 'assessment') {
                        Quiz::removeQuizRelationForFeed($element['id'], (string)$program['program_id'], (int)$val['packet_id']);
                    } elseif ($element['type'] == 'event') {
                        Event::where("event_id", $element['id'])->pull("relations.feed_event_rel.{$program["program_id"]}", (int)$val['packet_id']);
                    }
                }
            }

            $current_date = Carbon::create()->timestamp;
            Packet::updatePacket(
                $val['packet_slug'],
                [
                    'packet_slug' => "{$val["packet_slug"]}_deleted_{$current_date}",
                    'status' => 'DELETED',
                    'elements' => []
                ]
            );
        }

        // Unlink DAMS relation
        if (isset($program['program_cover_media']) && $program['program_cover_media']) {
            Dam::removeMediaRelation($program['program_cover_media'], ['contentfeed_media_rel'], (int)$program['program_id']);
        }
        Program::where('program_slug', '=', $slug)->where('program_type', '=', 'content_feed')->where('status', '!=', 'DELETED')->update(['status' => 'DELETED']);
        if (config('elastic.service')) {
            event(new ProgramRemoved((int)$program['program_id']));
        }
        $totalRecords = Program::getContentFeedCount($filter);
        if ($totalRecords <= $start) {
            $start -= $limit;
            if ($start < 0) {
                $start = 0;
            }
        }

        $msg = trans('admin/program.content_feed_delete_success');
        $url = "";
        return redirect('cp/contentfeedmanagement/' . $url . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $search . '&order_by=' . $order_by)
            ->with('success', $msg);
    }

    public function getFeedDetails($slug = '')
    {
        $programs = Program::getAllProgramByIDOrSlug('content_feed', $slug);
        if ($slug == null || empty($programs)) {
            $msg = trans('admin/program.slug_missing_error');

            return redirect('/cp/contentfeedmanagement/')
                ->with('error', $msg);
        }
        $programs = $programs->toArray();
        $programs = $programs[0];

        if (!$this->roleService->hasPermission(
            $this->request->user()->uid,
            ModuleEnum::CHANNEL,
            PermissionType::ADMIN,
            ChannelPermission::VIEW_CHANNEL,
            Contexts::PROGRAM,
            $programs["program_id"]
        )) {
            return parent::getAdminError($this->theme_path);
        }

        $programs['program_startdate'] = Timezone::convertFromUTC('@' . $programs['program_startdate'], Auth::user()->timezone, config('app.date_format'));
        $programs['program_enddate'] = Timezone::convertFromUTC('@' . $programs['program_enddate'], Auth::user()->timezone, config('app.date_format'));
        $programs['program_display_startdate'] = Timezone::convertFromUTC('@' . $programs['program_display_startdate'], Auth::user()->timezone, config('app.date_format'));
        $programs['program_display_enddate'] = Timezone::convertFromUTC('@' . $programs['program_display_enddate'], Auth::user()->timezone, config('app.date_format'));
        $programs['package_names'] = $this->packageRepository->get(['in_ids' => array_get($programs, 'package_ids', [])])->toArray();

        $media = '';
        if (isset($programs['program_cover_media'])) {
            $media = Dam::getDAMSAssetsUsingID($programs['program_cover_media']);
            if (!empty($media)) {
                $media = $media[0];
            }
        }
        $uniconf_id = Config::get('app.uniconf_id');
        $kaltura_url = Config::get('app.kaltura_url');
        $partnerId = Config::get('app.partnerId');
        $kaltura = $kaltura_url . 'index.php/kwidget/cache_st/1389590657/wid/_' . $partnerId . '/uiconf_id/' . $uniconf_id . '/entry_id/';

        return view('admin.theme.programs.feeddetails')->with('feed', $programs)->with('media', $media)->with('kaltura', $kaltura);
    }

    public function getAddFeedSuccess($type = null, $slug = null)
    {
        try {
            $program = $this->programservice->getProgramBySlug($type, $slug);

            if (!$this->roleService->hasPermission(
                $this->request->user()->uid,
                ModuleEnum::CHANNEL,
                PermissionType::ADMIN,
                ChannelPermission::MANAGE_CHANNEL_POST,
                Contexts::PROGRAM,
                $program->program_id
            )) {
                return parent::getAdminError();
            }

            $crumbs = [
                trans('admin/dashboard.dashboard') => 'cp',
                trans('admin/program.manage_channel') => 'contentfeedmanagement',
                trans('admin/program.title_add_channel') => '',
            ];
            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = trans('admin/program.title_add_channel');
            $this->layout->pageicon = 'fa fa-rss';
            $this->layout->pagedescription = trans('admin/program.title_add_channel');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'program')
                ->with('submenu', 'contentfeed');
            $this->layout->content = view('admin.theme.programs.addfeedsuccess')
                ->with('program', $program->toArray());
            $this->layout->footer = view('admin.theme.common.footer');
        } catch (ProgramNotFoundException $e) {
            $msg = trans('admin/program.slug_missing_error');

            return redirect('/cp/contentfeedmanagement/')
                ->with('error', $msg);
        }
    }

    public function getPackets($type = null, $slug = null)
    {
        $from = Input::get('from');
        try {
            $program = $this->programservice->getProgramBySlug($type, $slug);

            switch ($type) {
                case 'course':
                    if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                        return parent::getAdminError();
                    }
                    break;
                default:
                    if (!$this->roleService->hasPermission(
                        $this->request->user()->uid,
                        ModuleEnum::CHANNEL,
                        PermissionType::ADMIN,
                        ChannelPermission::MANAGE_CHANNEL_POST,
                        Contexts::PROGRAM,
                        $program->program_id
                    )) {
                        return parent::getAdminError();
                    }
                    break;
            }

            $viewmode = Input::get('view', 'desktop');
            if ($viewmode == 'iframe') {
                $this->layout->breadcrumbs = '';
                $this->layout->pagetitle = '';
                $this->layout->pageicon = '';
                $this->layout->pagedescription = '';
                $this->layout->header = '';
                $this->layout->sidebar = '';
                $this->layout->content = view('admin.theme.programs.listpacketsiframe')
                    ->with("program", $program)
                    ->with("from", $from)
                    ->with('input_type', Input::get('input_type', 'checkbox'));
                $this->layout->footer = '';
            } else {
                if ($type == 'course') {
                    $crumbs = [
                    trans('admin/dashboard.dashboard') => 'cp',
                    trans('admin/program.course_list') => 'contentfeedmanagement/list-courses',
                    trans('admin/program.manage_packets') =>
                        "../packets/{$program->program_type}/{$program->program_slug}",
                    trans('admin/program.add_packets') => '',
                ];
                } else {
                    $crumbs = [
                    trans('admin/dashboard.dashboard') => 'cp',
                    trans('admin/program.manage_channel') => 'contentfeedmanagement',
                    trans('admin/program.manage_packets') =>
                        "packets/{$program->program_type}/{$program->program_slug}",
                    trans('admin/program.add_packets') => '',
                ];
                }
                $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
                $this->layout->pagetitle = trans('admin/program.manage_packets');
                $this->layout->pageicon = 'fa fa-rss';
                $this->layout->pagedescription = trans('admin/program.manage_packets');
                $this->layout->header = view('admin.theme.common.header');

                if ($type == 'course') {
                    $this->layout->sidebar = view('admin.theme.common.sidebar')
                        ->with('mainmenu', 'course');
                } else {
                    $this->layout->sidebar = view('admin.theme.common.sidebar')
                        ->with('mainmenu', 'program')
                        ->with('submenu', 'contentfeed');
                }

                $this->layout->content = view('admin.theme.programs.listpackets')
                    ->with('type', $program->program_type)
                    ->with('slug', $program->program_slug)
                    ->with('input_type', Input::get('input_type', 'checkbox'));
                $this->layout->footer = view('admin.theme.common.footer');
            }
        } catch (ProgramNotFoundException $e) {
            $msg = trans('admin/program.slug_missing_error');

            return redirect('/cp/contentfeedmanagement/')
                ->with('error', $msg);
        }
    }

    public function getPacketListAjax($type = null, $slug = null, $input_type = "checkbox")
    {
        $from_event = Input::get('from_event', 'null');
        $program_data = [];
        $has_post_list_permission = false;
        $viewmode = Input::get('view', 'desktop');
        $from_module = Input::get('from', 'survey'); 
        try {
            $program_data = $this->programservice->getProgramBySlug($type, $slug)->toArray();
        } catch (ProgramNotFoundException $e) {
            Log::error($e->getMessage());
            return response()->json([
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        switch ($viewmode) {
            case "desktop":
            case "iframe":
                switch ($type) {
                    case 'course':
                        $has_post_list_permission = has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST);
                        break;
                    default:
                        $has_post_list_permission = $this->roleService->hasPermission(
                            $this->request->user()->uid,
                            ModuleEnum::CHANNEL,
                            PermissionType::ADMIN,
                            ChannelPermission::MANAGE_CHANNEL_POST,
                            Contexts::PROGRAM,
                            $program_data["program_id"]
                        );
                        break;
                }
                break;
        }

        if (!$has_post_list_permission) {
            return response()->json([
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = '';
        $order_by = Input::get('order');
        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '1') {
                $orderByArray = ['packet_title' => $order_by[0]['dir']];
            }

            if ($order_by[0]['column'] == '2') {
                $orderByArray = ['packet_publish_date' => $order_by[0]['dir']];
            }

            if ($order_by[0]['column'] == '3') {
                $orderByArray = ['created_at' => $order_by[0]['dir']];
            }

            if ($order_by[0]['column'] == '4') {
                $orderByArray = ['updated_at' => $order_by[0]['dir']];
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
        $filter = strtolower($filter);
        if (!in_array($filter, ['active', 'in-active'])) {
            $filter = 'all';
        } else {
            $filter = strtoupper($filter);
        }
        $totalRecords = Packet::getPacketsCount($slug);
        $filteredRecords = Packet::getPacketsCount($slug, $filter, $searchKey);
        $filtereddata = Packet::getPacketsWithTypeAndPagination($slug, $filter, $start, $limit, $orderByArray, $searchKey);
        $dataArr = [];
        foreach ($filtereddata as $key => $value) {
            $elements = '<a href="' . URL::to('/cp/contentfeedmanagement/edit-packet/'.$program_data['program_type'].
                    '/'.$program_data['program_slug'].'/'. $value['packet_slug'].'#items-tab') .
                '" class="show-tooltip badge badge-grey" title="' . trans('admin/program.manage_elements') . '"> 0 </a>';

            if (isset($value['elements']) && count($value['elements'])) {
                $elements = '<a href="' . URL::to('/cp/contentfeedmanagement/edit-packet/'.
                        $program_data['program_type'].'/'.$program_data['program_slug'].'/'.
                        $value['packet_slug'].'#items-tab') . '" class="show-tooltip badge badge-info" title="' .
                    trans('admin/program.manage_elements') . '"> ' . count($value['elements']) . ' </a>';
            }

            $qanda = '<a href="' . URL::to("/cp/contentfeedmanagement/post-questions-list-template/{$type}/{$slug}/{$value["packet_slug"]}") . '" class="show-tooltip badge badge-grey" title="' . trans('admin/program.manage_qanda') . '"> 0 </a>';
            $qanda_count = 0;
            if (isset($value['total_ques_public'])) {
                $qanda_count = (int)$value['total_ques_public'];
            }

            if (isset($value['total_ques_private'])) {
                $qanda_count = $qanda_count + (int)$value['total_ques_private'];
            }

            if ($qanda_count) {
                $qanda = '<a href="' . URL::to("/cp/contentfeedmanagement/post-questions-list-template/{$type}/{$slug}/{$value["packet_slug"]}") . '" class="show-tooltip badge badge-info" title="' . trans('admin/program.manage_qanda') . '"> ' . $qanda_count . ' </a>';
            }

            $qanda_unanswered = '<a href="'.URL::to("/cp/contentfeedmanagement/post-questions-list-template/{$type}/{$slug}/{$value["packet_slug"]}?filter=unanswered").'" class="show-tooltip badge badge-danger" title="' . trans('admin/program.manage_unansw_Q_&_A') . '"> 0 </a>';
            if (isset($value['total_ques_unanswered']) && $value['total_ques_unanswered']) {
                $qanda_unanswered = '<a href="' .URL::to("/cp/contentfeedmanagement/post-questions-list-template/{$type}/{$slug}/{$value["packet_slug"]}?filter=unanswered"). '" class="show-tooltip badge badge-important" title="' . trans('admin/program.manage_unansw_Q_&_A') . '"> ' . $value['total_ques_unanswered'] . ' </a>';
            }

            $delete = ' <a class="btn btn-circle show-tooltip deletepacket" href="' . URL::to('cp/contentfeedmanagement/delete-packet/'.$program_data['program_type'].'/'.$program_data['program_slug'].'/'. $value['packet_slug']).'?start=' . $start . '&limit=' . $limit . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" title="' . trans('admin/manageweb.action_delete') . '" href="" ><i class="fa fa-trash-o"></i></a>';
            $error = false;
            if (isset($program_data['relations']) && is_array($program_data['relations']) && !empty($program_data['relations'])) {
                foreach ($program_data['relations'] as $k => $v) {
                    if (is_array($v) && count($v)) {
                        $error = true;
                    }
                }

                if ($error == 1) {
                    $delete = '<a class="btn btn-circle show-tooltip postrelations" href="' . URL::to('cp/contentfeedmanagement/packet-relations/'.$program_data['program_type'].'/'.$program_data['program_slug'].'/'.$value['packet_slug']).'?start=' . $start . '&limit=' . $limit . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" title="' . trans('admin/manageweb.action_delete') . '"><i class="fa fa-trash-o"></i></a>';
                }
            }
            $packet_publish_date = "";
            if ($from_event != "event") {
                $packet_publish_date = Timezone::convertFromUTC('@' . $value['packet_publish_date'], Auth::user()->timezone, config('app.date_format'));
            }
            $temparr = [
                '<input type="'.$input_type.'" name="input_posts" value="' . $value['packet_id'] . '">',
                "<div>" . $value['packet_title'] . "</div>",
                $packet_publish_date,
                Timezone::convertFromUTC('@' . $value['created_at'], Auth::user()->timezone, config('app.date_format')),
                Timezone::convertFromUTC('@' . $value['updated_at'], Auth::user()->timezone, config('app.date_format')),
                ucfirst(strtolower($value['status'])),
                $elements,
                $qanda . '&nbsp;' . $qanda_unanswered,
                '<a class="btn btn-circle show-tooltip viewpacket" title="' . trans('admin/manageweb.action_view') . '" href="' . URL::to('cp/contentfeedmanagement/packet-details/'.$program_data['program_type'].'/'.$program_data['program_slug'].'/'.$value['packet_slug']) . '" ><i class="fa fa-eye"></i></a>
                <a class="btn btn-circle show-tooltip" title="' . trans('admin/manageweb.action_edit') . '" href="' . URL::to('cp/contentfeedmanagement/edit-packet/'.$program_data['program_type'].'/'.$program_data['program_slug'].'/'.$value['packet_slug']) . '?start=' . $start . '&limit=' . $limit . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-edit"></i></a>
                <a class="btn btn-circle show-tooltip" title="' . trans('admin/program.manage_elements') . '" href="' . URL::to("cp/contentfeedmanagement/edit-packet/{$program_data['program_type']}/{$program_data['program_slug']}/{$value['packet_slug']}#items-tab") . '" ><i class="fa fa-bars"></i></a> ' . $delete,

            ];

            if ($from_event == "event" || $from_module == 'from_module') {
                unset($temparr[2]);
            }
            $temparr = array_values($temparr);

            if ($viewmode == 'iframe') {
                array_pop($temparr);
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

    public function getAddPackets($type = null, $slug = null)
    {
        try {
            $program = $this->programservice->getProgramBySlug($type, $slug);

            switch ($type) {
                case 'course':
                    if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                        return parent::getAdminError();
                    }
                    break;
                default:
                    if (!$this->roleService->hasPermission(
                        $this->request->user()->uid,
                        ModuleEnum::CHANNEL,
                        PermissionType::ADMIN,
                        ChannelPermission::MANAGE_CHANNEL_POST,
                        Contexts::PROGRAM,
                        $program->program_id
                    )) {
                        return parent::getAdminError();
                    }
                    break;
            }

            if ($type == 'course') {
                    $crumbs = [
                    trans('admin/dashboard.dashboard') => 'cp',
                    trans('admin/program.manage_packets') =>
                        "contentfeedmanagement/packets/{$program->program_type}/{$program->program_slug}",
                    trans('admin/program.add_packets') => '',
                ];
                } else {
                    $crumbs = [
                    trans('admin/dashboard.dashboard') => 'cp',
                    trans('admin/program.manage_channel') => 'contentfeedmanagement',
                    trans('admin/program.manage_packets') =>
                        "packets/{$program->program_type}/{$program->program_slug}",
                    trans('admin/program.add_packets') => '',
                ];
                }

            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'program')
                ->with('submenu', 'contentfeed');

            $this->layout->content = view('admin.theme.programs.addpacket')
                ->with("program", $program->toArray());

            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = trans('admin/program.add_packets');
            $this->layout->pageicon = 'fa fa-rss';
            $this->layout->pagedescription = trans('admin/program.add_packets');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->footer = view('admin.theme.common.footer');
        } catch (ProgramNotFoundException $e) {
            $msg = trans('admin/program.slug_missing_error');

            return redirect('/cp/contentfeedmanagement/')
                ->with('error', $msg);
        }
    }

    public function postAddPackets($type, $slug = null)
    {
        Input::flash();

        try {
            $program = $this->programservice->getProgramBySlug($type, $slug);

            switch ($type) {
                case 'course':
                    if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                        return parent::getAdminError();
                    }
                    break;
                default:
                    if (!$this->roleService->hasPermission(
                        $this->request->user()->uid,
                        ModuleEnum::CHANNEL,
                        PermissionType::ADMIN,
                        ChannelPermission::MANAGE_CHANNEL_POST,
                        Contexts::PROGRAM,
                        $program->program_id
                    )) {
                        return parent::getAdminError();
                    }
                    break;
            }

            Validator::extend('checkslug', function ($attribute, $value, $parameters) {
                $returnval = Packet::where('packet_slug', '=', $parameters[0])
                    ->get(['packet_slug'])
                    ->toArray();

                if (empty($returnval)) {
                    return true;
                }

                return false;
            });

            Validator::extend('checkslugregex', function ($attribute, $value, $parameters) {
                if (preg_match('/^[a-zA-Z0-9-_]+$/', $value)) {
                    return true;
                }

                return false;
            });

            $messages = [
                'checkslug' => trans('admin/program.check_slug'),
                'checkslugregex' => trans('admin/program.check_slug_regex'),
                'packet_name.required' => trans('admin/program.post_field_required'),
            ];

            $rules = [
                'packet_name' => 'Required',
                'packet_slug' => 'Required|checkslugregex|checkslug:' . $slug . '-' . Input::get('packet_slug'),
                'packet_publish_date' => 'Required',
                'status' => 'Required',
                'qanda' => 'Required',
                'access' => 'Required',
            ];

            $validation = Validator::make(Input::all(), $rules, $messages);

            if ($validation->fails()) {
                return redirect("cp/contentfeedmanagement/add-packets/{$program->program_type}/{$slug}")->withInput()
                    ->withErrors($validation);
            } elseif ($validation->passes()) {
                $status = 'IN-ACTIVE';
                $feed_media_rel = 'packet_banner_media_rel';
                if (Input::get('status') == 'active') {
                    $status = 'ACTIVE';
                }
                $packetid = Packet::uniquePacketId();
                $mediaid = Input::get('banner', '');
                $packetData = [
                    'packet_id' => $packetid,
                    'packet_title' => trim(Input::get('packet_name')),
                    'title_lower' => trim(strtolower(Input::get('packet_name'))),
                    'packet_slug' => $slug . '-' . Input::get('packet_slug'),
                    'feed_slug' => $slug,
                    'packet_description' => Input::get('packet_description'),
                    'packet_publish_date' => (int)Timezone::convertToUTC(
                        Input::get('packet_publish_date'),
                        Auth::user()->timezone,
                        'U'
                    ),
                    'packet_cover_media' => $mediaid,
                    'sequential_access' => Input::get('access', 'no'),
                    'quiz_result' => Input::get('quiz_result', 'no'),
                    'qanda' => Input::get('qanda', 'yes'),
                    'elements' => [],
                    'total_ques_public' => 0,
                    'total_ques_private' => 0,
                    'total_ques_unanswered' => 0,
                    'status' => $status,
                    'created_by' => Auth::user()->username,
                    'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                    'created_at' => time(),
                    'updated_at' => time(),
                    'updated_by' => Auth::user()->username,
                ];
                Packet::Insert($packetData);
                if (config('elastic.service')) {
                    event(new PostAdded($packetid));
                }
                Program::where('program_slug', '=', $slug)
                    ->where('program_type', '=', 'content_feed')
                    ->where('status', '!=', 'DELETED')
                    ->update(['updated_at' => time(), 'updated_by' => Auth::user()->username]);

                // Send notification to the users that there is a change in the meta data
                $programdata = $program->toArray();
                if (Config::get('app.notifications.contentfeed.packetadd')) {
                    if (isset($programdata['relations']['active_user_feed_rel'])) {
                        $notify_user_ids_ary = [];
                        $notify_user_ids_ary = array_merge(
                            $notify_user_ids_ary,
                            $programdata['relations']['active_user_feed_rel']
                        );

                        if (!empty($notify_user_ids_ary)) {
                            $notif_msg = trans(
                                'admin/notifications.packetadd',
                                [
                                    'adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                                    'packet' => Input::get('packet_name'),
                                    'feed' => $programdata['program_title']
                                ]
                            );

                            NotificationLog::getInsertNotification(
                                $notify_user_ids_ary,
                                trans('admin/program.packet'),
                                $notif_msg
                            );
                        }
                    }
                    if (isset($programdata['relations']['active_usergroup_feed_rel'])) {
                        $notify_user_ids_ary = [];
                        foreach ($programdata['relations']['active_usergroup_feed_rel'] as $usergroupid) {
                            $usergroup_data = UserGroup::getUserGroupsUsingID((int)$usergroupid);
                            foreach ($usergroup_data as $usergroup) {
                                if (isset($usergroup['relations']['active_user_usergroup_rel'])) {
                                    $notify_user_ids_ary = array_merge(
                                        $notify_user_ids_ary,
                                        $usergroup['relations']['active_user_usergroup_rel']
                                    );
                                }
                            }
                        }
                        if (!empty($notify_user_ids_ary)) {
                            $notif_msg = trans(
                                'admin/notifications.packetadd',
                                [
                                    'adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                                    'packet' => Input::get('packet_name'),
                                    'feed' => $programdata['program_title']
                                ]
                            );

                            NotificationLog::getInsertNotification(
                                $notify_user_ids_ary,
                                trans('admin/program.packet'),
                                $notif_msg
                            );
                        }
                    }
                }

                Dam::removeMediaRelation($mediaid, ['packet_banner_media_rel'], (int)$packetid);
                Dam::updateDAMSRelation($mediaid, $feed_media_rel, (int)$packetid);
                $msg = trans('admin/program.packet_success');

                $packet_slug = $slug."-".Input::get("packet_slug");
                return redirect(
                    "/cp/contentfeedmanagement/edit-packet/{$program->program_type}/{$program->program_slug}/{$packet_slug}#items-tab"
                )->with('success', $msg);
            }
        } catch (ProgramNotFoundException $e) {
            $msg = trans('admin/program.slug_missing_error');

            return redirect('/cp/contentfeedmanagement/')
                ->with('error', $msg);
        }
    }

    public function getCourseChange()
    {
        $slug = Input::get('slug');
        $parentid = Program::where('program_slug', '=', $slug)->value('program_id');
        $parentid = (int)$parentid;
        $array = Program::where('program_type', '=', 'course')->where('parent_id', '=', $parentid)->orderBy('created_at', 'desc')->get()->toArray();
        return response()->json([
            'array' => $array,
            'parentid' => $parentid,
        ]);
    }

    public function getAddPacketSuccess($program_type = null, $program_slug = null, $packet_slug = null)
    {
        try {
            $program = $this->programservice->getProgramBySlug($program_type, $program_slug);
            $packet = $this->programservice->getProgramPostBySlug($program_type, $program_slug, $packet_slug);

            switch ($program_type) {
                case 'course':
                    if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                        return parent::getAdminError();
                    }
                    break;
                default:
                    if (!$this->roleService->hasPermission(
                        $this->request->user()->uid,
                        ModuleEnum::CHANNEL,
                        PermissionType::ADMIN,
                        ChannelPermission::MANAGE_CHANNEL_POST,
                        Contexts::PROGRAM,
                        $program->program_id
                    )) {
                        return parent::getAdminError();
                    }
                    break;
            }

            $crumbs = [
                trans('admin/dashboard.dashboard') => 'cp',
                trans('admin/program.manage_packets') =>
                    "contentfeedmanagement/packets/{$program_type}/{$program_slug}",
                trans('admin/program.edit_packet') => '',
            ];

            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = trans('admin/program.add_packets');
            $this->layout->pageicon = 'fa fa-rss';
            $this->layout->pagedescription = trans('admin/program.add_packets');
            $this->layout->header = view('admin.theme.common.header');

            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'program')
                ->with('submenu', 'contentfeed');

            $this->layout->content = view('admin.theme.programs.addpacketsuccess')
                ->with('program', $program->toArray())
                ->with('packet', $packet->toArray());
            $this->layout->footer = view('admin.theme.common.footer');
        } catch (ApplicationException $e) {
            $msg = trans('admin/program.packet_slug_missing_error');

            return redirect('/cp/contentfeedmanagement/')
                ->with('error', $msg);
        }
    }

    public function getEditPacket($program_type = null, $program_slug = null, $packet_slug = null)
    {
        try {
            $program = $this->programservice->getProgramBySlug($program_type, $program_slug);
            $packet = $this->programservice->getProgramPostBySlug($program_type, $program_slug, $packet_slug);

            switch ($program_type) {
                case 'course':
                    if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                        return parent::getAdminError();
                    }
                    break;
                default:
                    if (!$this->roleService->hasPermission(
                        $this->request->user()->uid,
                        ModuleEnum::CHANNEL,
                        PermissionType::ADMIN,
                        ChannelPermission::MANAGE_CHANNEL_POST,
                        Contexts::PROGRAM,
                        $program->program_id
                    )) {
                        return parent::getAdminError();
                    }
                    break;
            }

            if ($program_type == 'course') {
                $crumbs = [
                trans('admin/dashboard.dashboard') => 'cp',
                trans('admin/program.course_list') => 'contentfeedmanagement/list-courses',
                trans('admin/program.manage_packets') =>
                    "../packets/{$program->program_type}/{$program->program_slug}",
                trans('admin/program.add_packets') => '',
            ];
            } else {
                $crumbs = [
                trans('admin/dashboard.dashboard') => 'cp',
                trans('admin/program.manage_channel') => 'contentfeedmanagement',
                trans('admin/program.manage_packets') =>
                    "packets/{$program->program_type}/{$program->program_slug}",
                trans('admin/program.add_packets') => '',
            ];
            }

            $packet = $packet->toArray();

            //Items tab content
            $mediaElements = [];
            $assessmentElements = [];
            $eventElements = [];
            $flashcardElements = [];
            $dam_ele = [];
            $assessment_ele = [];
            $event_ele = [];
            $flashcard_ele = [];
            $surveyElements = [];
            $assignmentElements = [];
            $data = [];
            if (isset($packet['elements']) && is_array($packet['elements']) && count($packet['elements'])) {
                foreach ($packet['elements'] as $key => &$value) {
                    if ($value['type'] == 'media') {
                        $media_record = Dam::getDAMSMediaUsingID($value['id']);
                        if (is_array($media_record) && !empty($media_record)) {
                            $mediaElements['ids'][] = $value['id'];
                            $mediaElements['names'][$value['id']] = isset($value['name']) ? $value['name'] : '';
                            $mediaElements['display_name'][$value['id']] =
                                isset($value['display_name']) ? $value['display_name'] : '';
                            $value['media_type'] = ucfirst(Dam::getAssetType((int)$value['id']));
                            $dam_ele[] = $value['id'];
                        }
                    } elseif ($value['type'] == 'assessment') {
                        $assessment_record = Quiz::getQuizAssetsUsingAutoID($value['id']);
                        if (is_array($assessment_record) && !empty($assessment_record)) {
                            $assessmentElements['ids'][] = $value['id'];
                            $assessmentElements['names'][$value['id']] = isset($value['name']) ? $value['name'] : '';
                            $assessmentElements['display_name'][$value['id']] =
                                isset($value['display_name']) ? $value['display_name'] : '';
                            $assessment_ele[] = $value['id'];
                        }
                    } elseif ($value['type'] == 'event') {
                        $event_record = Event::getEventsAssetsUsingAutoID($value['id']);
                        if (is_array($event_record) && !empty($event_record)) {
                            $eventElements['ids'][] = $value['id'];
                            $eventElements['names'][$value['id']] = isset($value['name']) ? $value['name'] : '';
                            $eventElements['display_name'][$value['id']] =
                                isset($value['display_name']) ? $value['display_name'] : '';
                            $event_ele[] = $value['id'];
                        }
                    } elseif ($value['type'] == 'flashcard') {
                        $flashcard_record = FlashCard::getFlashcardsAssetsUsingAutoID($value['id']);
                        if (is_array($flashcard_record) && !empty($flashcard_record)) {
                            $flashcardElements['ids'][] = $value['id'];
                            $flashcardElements['names'][$value['id']] = isset($value['name']) ? $value['name'] : '';
                            $flashcardElements['display_name'][$value['id']] =
                                isset($value['display_name']) ? $value['display_name'] : '';
                            $flashcard_ele[] = $value['id'];
                        }
                    } elseif ($value['type'] == 'survey') {
                        $survey_record = Survey::getSurveyByIds($value['id'])->toArray();
                        if (is_array($survey_record) && !empty($survey_record)) {
                            $surveyElements['ids'][] = $value['id'];
                            $surveyElements['names'][$value['id']] = isset($value['name']) ? $value['name'] : '';
                            $surveyElements['display_name'][$value['id']] =
                                isset($value['display_name']) ? $value['display_name'] : '';
                            $survey_ele[] = $value['id'];
                        }
                    } elseif ($value['type'] == 'assignment') {
                        $assignment_record = Assignment::getAssignmentByIds(array_get($value, 'id'))->toArray();
                        if (is_array($assignment_record) && !empty($assignment_record)) {
                            $assignmentElements['ids'][] = array_get($value, 'id');
                            $assignmentElements['names'][array_get($value, 'id')] = isset($value['name']) ? array_get($value, 'name') : '';
                            $assignmentElements['display_name'][array_get($value, 'id')] =
                                isset($value['display_name']) ? array_get($value, 'display_name') : '';
                            $assignment_ele[] = array_get($value, 'id');
                        }
                    }
                }
                if (Config::get('app.get_ele_live')) {
                    if (!empty($dam_ele)) {
                        $res = Dam::getMediaNameByID($dam_ele);

                        foreach ($res as $media) {
                            $mediaElements['names'][$media->id] = $media->name;
                            $mediaElements['types'][$media->id] = $media->type;
                            $mediaElements['created_by'][$media->id] = $media->created_by_username;
                            $mediaElements['created_at'][$media->id] =
                                $media->created_at->timezone(Auth::user()->timezone)->format(config('app.date_format'));
                        }
                    }
                    if (!empty($assessment_ele)) {
                        $res = Quiz::getQuizNameByID($assessment_ele);

                        foreach ($res as $quiz) {
                            $assessmentElements['names'][$quiz->quiz_id] = $quiz->quiz_name;
                            $assessmentElements['created_by'][$quiz->quiz_id] = $quiz->created_by;
                            $assessmentElements['created_at'][$quiz->quiz_id] =
                                $quiz->created_at->timezone(Auth::user()->timezone)->format(config('app.date_format'));
                        }
                    }
                    if (!empty($event_ele)) {
                        $res = Event::getEventNameByID($event_ele);
                        foreach ($res as $event) {
                            $eventElements['names'][$event->event_id] = $event->event_name;
                            $eventElements['created_by'][$event->event_id] = $event->created_by;
                            $eventElements['created_at'][$event->event_id] =
                                Timezone::convertFromUTC(
                                    '@' . $event->created_at,
                                    Auth::user()->timezone,
                                    config('app.date_format')
                                );
                        }
                    }
                    if (!empty($flashcard_ele)) {
                        $res = FlashCard::getFCNameByID($flashcard_ele);
                        foreach ($res as $fc) {
                            $flashcardElements['names'][$fc->card_id] = $fc->title;
                            $flashcardElements['created_by'][$fc->card_id] = $fc->created_by;
                            $flashcardElements['created_at'][$fc->card_id] =
                                $fc->created_at->timezone(Auth::user()->timezone)->format(config('app.date_format'));
                        }
                    }

                    if (!empty($survey_ele)) {
                        $res = Survey::getSurveyNameByID($survey_ele);
                        foreach ($res as $survey) {
                            $surveyElements['names'][$survey->id] = $survey->survey_title;
                            $surveyElements['created_by'][$survey->id] = $survey->created_by;
                            $surveyElements['created_at'][$survey->id] =
                                $survey->created_at->timezone(Auth::user()->timezone)->format(config('app.date_format'));
                        }
                    }

                    if (!empty($assignment_ele)) {
                        $res = Assignment::getAssignmentNameByID($assignment_ele);
                        foreach ($res as $assignment) {
                            $assignmentElements['names'][$assignment->id] = $assignment->name;
                            $assignmentElements['created_by'][$assignment->id] = $assignment->created_by;
                            $assignmentElements['created_at'][$assignment->id] =
                                $assignment->created_at->timezone(Auth::user()->timezone)->format(config('app.date_format'));
                        }
                    }
                }
            }
    
            $data = [
                'mediaElements' => $mediaElements,
                'assessmentElements' => $assessmentElements,
                'eventElements' => $eventElements,
                'flashcardElements' => $flashcardElements,
                'surveyElements' => $surveyElements,
                'assignmentElements' => $assignmentElements
            ];

            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle =
                trans('admin/program.edit_packet').' - '.$packet['packet_title'].' - '.$program->program_title;
            $this->layout->pageicon = 'fa fa-rss';
            $this->layout->pagedescription = trans('admin/program.edit_packet');
            $this->layout->header = view('admin.theme.common.header');

            if ($program_type == 'course') {
                $this->layout->sidebar = view('admin.theme.common.sidebar')
                    ->with('mainmenu', 'course');
            } else {
                $this->layout->sidebar = view('admin.theme.common.sidebar')
                    ->with('mainmenu', 'program')
                    ->with('submenu', 'contentfeed');
            }

            $this->layout->content = view(
                'admin.theme.programs.addpostsuccess',
                [
                    'program' => $program->toArray(),
                    'packet' => $packet,
                    'data' => $data
                ]
            );

            $this->layout->footer = view('admin.theme.common.footer');
        } catch (ApplicationException $e) {
            $msg = trans('admin/program.packet_slug_missing_error');

            return redirect('/cp/contentfeedmanagement/')
                ->with('error', $msg);
        }
    }

    public function postUploadMedia($program_type = null, $program_slug = null, $packet_slug = null)
    {
        try {
            $program = $this->programservice->getProgramBySlug($program_type, $program_slug);
            $packet = $this->programservice->getProgramPostBySlug($program_type, $program_slug, $packet_slug);

            switch ($program_type) {
                case 'course':
                    if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                        return parent::getAdminError();
                    }
                    break;
                default:
                    if (!$this->roleService->hasPermission(
                        $this->request->user()->uid,
                        ModuleEnum::CHANNEL,
                        PermissionType::ADMIN,
                        ChannelPermission::MANAGE_CHANNEL_POST,
                        Contexts::PROGRAM,
                        $program->program_id
                    )) {
                        return parent::getAdminError();
                    }
                    break;
            }

            $packet = $packet->toArray();

            $success_array = [];
            $error_array = [];
            foreach (Input::all()['file'] as $key => $value) {
                if ($value) {
                    $extension = $value->getClientOriginalExtension();
                    $mime = $value->getMimeType();
                    if (in_array($extension, config('app.dams_image_extensions'))) {
                        $media_type = 'image';
                        $keyword = '';
                        $random_filename = strtolower(str_random(32));
                        while (true) {
                            $result = Dam::getDAMSAsset($random_filename);
                            if ($result->isEmpty()) {
                                break;
                            } else {
                                $random_filename = strtolower(str_random(32));
                            }
                        }
                        $image_sizes = Config::get('app.thumb_resolutions');
                        $private_dams_images_path = Config::get('app.private_dams_images_path');
                        $public_dams_images_path = Config::get('app.public_dams_images_path');
                        $visibility = 'public';
                        $title = basename($value->getClientOriginalName(), "." . $extension);
                        $id = Dam::uniqueDAMSId();
                        $insertarr = [
                            'id' => $id,
                            'name' => $title,
                            'name_lower' => strtolower($title),
                            'description' => '',
                            'type' => 'image',
                            'asset_type' => 'file',
                            'unique_name' => $random_filename,
                            'unique_name_with_extension' => $random_filename . '.' . $extension,
                            'visibility' => $visibility,
                            'file_client_name' => $value->getClientOriginalName(),
                            'id3_info' => '',
                            'file_size' => $value->getSize(),
                            'file_extension' => $extension,
                            'mimetype' => $mime,
                            'tags' => explode(',', $keyword),
                            'status' => 'ACTIVE',
                            'created_at' => time(),
                            'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                            'created_by_username' => Auth::user()->username,
                        ];
                        //
                        $insertarr['public_file_location'] = $public_dams_images_path . $random_filename . '.' . $value->getClientOriginalExtension();
                        $value->move($public_dams_images_path, $insertarr['public_file_location']);
                        // $insertarr['id3_info'] = $getID3->analyze($insertarr['public_file_location']);
                        foreach ($image_sizes as $val) {
                            $res = explode('x', $val);
                            if (is_array($res)) {
                                $loc = null;
                                $image_obj = new Imagick($insertarr['public_file_location']);
                                $loc = $public_dams_images_path . $random_filename . '_' . $val . '.' . $value->getClientOriginalExtension();
                                if (isset($res[0]) && isset($res[1])) {
                                    // $image_obj->resizeImage($res[0], $res[1], Imagick::FILTER_LANCZOS, 1, true);
                                    // skips resizing (and copy the original file) if the given res is less than the original image.
                                    if ($res[0] < $image_obj->getImageWidth() && $res[1] < $image_obj->getImageHeight()) {
                                        $image_obj->resizeImage($res[0], $res[1], Imagick::FILTER_LANCZOS, 1, true);
                                    }
                                    $image_obj->writeImage($loc);
                                    $insertarr['thumb_img'][$val] = $loc;
                                }
                            }
                        }
                        Dam::insert($insertarr);
                        Dam::addMediaRelation($id, ['dams_packet_rel'], $packet['packet_id']);
                        if (isset($packet['elements']) && is_array($packet['elements']) && count($packet['elements'])) {
                            $order = count($packet['elements']) + 1;
                        } else {
                            $order = 1;
                        }
                        $elements = [
                            'type' => 'media',
                            'order' => $order,
                            'id' => $id,
                            'name' => $title,
                        ];
                        Packet::where('packet_slug', '=', $packet_slug)->push('elements', $elements, true);
                        $success_array[] = $key;
                    } elseif (in_array($extension, config('app.dams_video_extensions'))) {
                        $media_type = 'video';
                        if ($value->getSize() / 1024 > config('app.dams_max_upload_size') * 1024) {
                            $error_array[$key] = trans('admin/dams.size_exceeded_error', ['size' => config('app.dams_max_upload_size')]);
                        } else {
                            $keyword = '';
                            $random_filename = strtolower(str_random(32));
                            while (true) {
                                $result = Dam::getDAMSAsset($random_filename);
                                if ($result->isEmpty()) {
                                    break;
                                } else {
                                    $random_filename = strtolower(str_random(32));
                                }
                            }
                            $dams_temp_video_path = Config::get('app.dams_temp_video_path');
                            $transcoding = 'yes';
                            $visibility = 'public';
                            $title = basename($value->getClientOriginalName(), "." . $extension);
                            $id = Dam::uniqueDAMSId();
                            $insertarr = [
                                'id' => $id,
                                'name' => $title,
                                'name_lower' => strtolower($title),
                                'description' => '',
                                'type' => 'video',
                                'asset_type' => 'file',
                                'unique_name' => $random_filename,
                                'unique_name_with_extension' => $random_filename . '.' . $value->getClientOriginalExtension(),
                                'visibility' => $visibility,
                                'file_client_name' => $value->getClientOriginalName(),
                                'id3_info' => '',
                                'file_size' => $value->getSize(),
                                'file_extension' => $value->getClientOriginalExtension(),
                                'mimetype' => $value->getMimeType(),
                                'tags' => explode(',', $keyword),
                                'transcoding' => $transcoding,
                                'status' => 'ACTIVE',
                                'video_status' => 'INTEMP',
                                'created_at' => time(),
                                'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                                'created_by_username' => Auth::user()->username,
                            ];
                            $value->move($dams_temp_video_path, $random_filename . '.' . $value->getClientOriginalExtension());
                            //
                            $insertarr['temp_location'] = $dams_temp_video_path . $random_filename . '.' . $value->getClientOriginalExtension();
                            Dam::insert($insertarr);
                            Dam::addMediaRelation($id, ['dams_packet_rel'], $packet['packet_id']);
                            if (isset($packet['elements']) && is_array($packet['elements']) && count($packet['elements'])) {
                                $order = count($packet['elements']) + 1;
                            } else {
                                $order = 1;
                            }
                            $elements = [
                                'type' => 'media',
                                'order' => $order,
                                'id' => $id,
                                'name' => $title,
                            ];
                            Packet::where('packet_slug', '=', $packet_slug)->push('elements', $elements, true);
                            $success_array[] = $key;
                        }
                    } elseif (in_array($extension, config('app.dams_document_extensions'))) {
                        $media_type = 'document';
                        if (!in_array($mime, config('app.dams_document_mime_types'))
                        ) {
                            $error_array[$key] = trans('admin/dams.mimetype_error');
                        } else {
                            $keyword = '';
                            $random_filename = strtolower(str_random(32));
                            while (true) {
                                $result = Dam::getDAMSAsset($random_filename);
                                if ($result->isEmpty()) {
                                    break;
                                } else {
                                    $random_filename = strtolower(str_random(32));
                                }
                            }
                            $public_dams_documents_path = Config::get('app.public_dams_documents_path');
                            $visibility = 'public';
                            $title = basename($value->getClientOriginalName(), "." . $extension);
                            $id = Dam::uniqueDAMSId();
                            $insertarr = [
                                'id' => $id,
                                'name' => $title,
                                'name_lower' => strtolower($title),
                                'description' => '',
                                'type' => 'document',
                                'asset_type' => 'file',
                                'unique_name' => $random_filename,
                                'unique_name_with_extension' => $random_filename . '.' . $value->getClientOriginalExtension(),
                                'visibility' => $visibility,
                                'file_client_name' => $value->getClientOriginalName(),
                                'id3_info' => '',
                                'file_size' => $value->getSize(),
                                'file_extension' => $value->getClientOriginalExtension(),
                                'mimetype' => $value->getMimeType(),
                                'tags' => explode(',', $keyword),
                                'status' => 'ACTIVE',
                                'created_at' => time(),
                                'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                                'created_by_username' => Auth::user()->username,
                            ];
                            //
                            $insertarr['public_file_location'] = $public_dams_documents_path . $random_filename . '.' . $value->getClientOriginalExtension();
                            $value->move($public_dams_documents_path, $insertarr['public_file_location']);
                            Dam::insert($insertarr);
                            Dam::addMediaRelation($id, ['dams_packet_rel'], $packet['packet_id']);
                            if (isset($packet['elements']) && is_array($packet['elements']) && count($packet['elements'])) {
                                $order = count($packet['elements']) + 1;
                            } else {
                                $order = 1;
                            }
                            $elements = [
                                'type' => 'media',
                                'order' => $order,
                                'id' => $id,
                                'name' => $title,
                            ];
                            Packet::where('packet_slug', '=', $packet_slug)->push('elements', $elements, true);
                            $success_array[] = $key;
                        }
                    } elseif (in_array($extension, config('app.dams_audio_extensions'))) {
                        $media_type = 'audio';
                        if (!in_array($mime, config('app.dams_audio_mime_types'))) {
                            $error_array[$key] = trans('admin/dams.check_audio_extension');
                        } else {
                            $keyword = '';
                            $random_filename = strtolower(str_random(32));
                            while (true) {
                                $result = Dam::getDAMSAsset($random_filename);
                                if ($result->isEmpty()) {
                                    break;
                                } else {
                                    $random_filename = strtolower(str_random(32));
                                }
                            }
                            $public_dams_audio_path = Config::get('app.public_dams_audio_path');
                            // $dams_documents_path = Config::get('app.dams_documents_path');
                            $visibility = 'public';
                            $title = basename($value->getClientOriginalName(), "." . $extension);
                            $id = Dam::uniqueDAMSId();
                            $insertarr = [
                                'id' => $id,
                                'name' => $title,
                                'name_lower' => strtolower($title),
                                'description' => '',
                                'type' => 'audio',
                                'asset_type' => 'file',
                                'unique_name' => $random_filename,
                                'unique_name_with_extension' => $random_filename . '.' . $value->getClientOriginalExtension(),
                                'visibility' => $visibility,
                                'file_client_name' => $value->getClientOriginalName(),
                                'id3_info' => '',
                                'file_size' => $value->getSize(),
                                'file_extension' => $value->getClientOriginalExtension(),
                                'mimetype' => $value->getMimeType(),
                                'tags' => explode(',', $keyword),
                                'status' => 'ACTIVE',
                                'created_at' => time(),
                                'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                                'created_by_username' => Auth::user()->username,
                            ];
                            //
                            $insertarr['public_file_location'] = $public_dams_audio_path . $random_filename . '.' . $value->getClientOriginalExtension();
                            $value->move($public_dams_audio_path, $insertarr['public_file_location']);
                            Dam::insert($insertarr);
                            Dam::addMediaRelation($id, ['dams_packet_rel'], $packet['packet_id']);
                            if (isset($packet['elements']) && is_array($packet['elements']) && count($packet['elements'])) {
                                $order = count($packet['elements']) + 1;
                            } else {
                                $order = 1;
                            }
                            $elements = [
                                'type' => 'media',
                                'order' => $order,
                                'id' => $id,
                                'name' => $title,
                            ];
                            Packet::where('packet_slug', '=', $packet_slug)->push('elements', $elements, true);
                            $success_array[] = $key;
                        }
                    } elseif (in_array($extension, ['zip'])) {
                        ScormValidator::extendValidatorToValidateScorm();

                        $rules = [
                            "file" => 'Required|mimes:zip|max:1048576|unsupported_files_not_exist|imsmanifest_exists',
                        ];

                        $validator = Validator::make(["file" => $value], $rules);

                        if ($validator->passes()) {
                            $keyword = '';
                            $random_filename = strtolower(str_random(32));
                            while (true) {
                                $result = Dam::getDAMSAsset($random_filename);
                                if ($result->isEmpty()) {
                                    break;
                                } else {
                                    $random_filename = strtolower(str_random(32));
                                }
                            }
                            $public_dams_scorm_path = Config::get('app.public_dams_scorm_path');
                            $visibility = 'public';
                            $file_client_name = basename($value->getClientOriginalName(), ".zip");
                            $id = Dam::uniqueDAMSId();
                            $title = basename($value->getClientOriginalName(), "." . $extension);
                            $insertarr = [
                                'id' => $id,
                                'name' => $file_client_name,
                                'name_lower' => strtolower($file_client_name),
                                'description' => '',
                                'type' => 'scorm',
                                'asset_type' => 'file',
                                'unique_name' => $random_filename,
                                'visibility' => $visibility,
                                'file_client_name' => $file_client_name,
                                'id3_info' => '',
                                'file_size' => $value->getSize(),
                                'file_extension' => $value->getClientOriginalExtension(),
                                'mimetype' => $value->getMimeType(),
                                'tags' => explode(',', $keyword),
                                'status' => 'ACTIVE',
                                'created_at' => time(),
                                'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                                'created_by_username' => Auth::user()->username,
                            ];
                            $insertarr['public_file_location'] = $public_dams_scorm_path . $random_filename;
                            $zip = new ZipArchive;
                            if ($zip->open($value) === true) {
                                $zip->extractTo($public_dams_scorm_path . $random_filename);
                                $zip->close();
                            }

                            $scorm_config_data =
                            ScormHelper::getScormConfigData($insertarr['public_file_location']);

                            if (array_has($scorm_config_data, "scorm_version")
                            && array_has($scorm_config_data, "scorm_launch_file")) {
                                $insertarr["version"] = $scorm_config_data["scorm_version"];
                                $insertarr["launch_file"] = $scorm_config_data["scorm_launch_file"];
                                if (array_has($scorm_config_data, "scorm_mastery_score")) {
                                    $insertarr["mastery_score"] = $scorm_config_data["scorm_mastery_score"];
                                }
                            }

                            Dam::insert($insertarr);
                            Dam::addMediaRelation($id, ['dams_packet_rel'], $packet['packet_id']);
                            if (isset($packet['elements']) && is_array($packet['elements']) && count($packet['elements'])) {
                                $order = count($packet['elements']) + 1;
                            } else {
                                $order = 1;
                            }

                            $elements = [
                                'type' => 'media',
                                'order' => $order,
                                'id' => $id,
                                'name' => $title,
                            ];
                            Packet::where('packet_slug', '=', $packet_slug)->push('elements', $elements, true);
                            $success_array[] = $key;
                        } else {
                            $error_array[$key] = $validator->getMessageBag()->get("file");
                        }
                    } else {
                        $error_array[$key] = trans('admin/dams.check_doc_extension');
                    }
                } else {
                    $error_array[$key] = trans('admin/dams.file_not_select');
                }
            }

            if (count($error_array) == 0 || count($error_array) == '') {
                $msg = count($success_array) . trans('admin/program.upload_success');
                Session::put('success', $msg);
            }

            $success_count = count($success_array);

            if ($success_count > 0 || $success_count != '') {
                //updating the channel and post record
                Packet::where('packet_slug', '=', $packet_slug)
                    ->where('status', '!=', 'DELETED')
                    ->update(['updated_at' => time(), 'updated_by' => Auth::user()->username]);

                $feed_slug = Packet::where('packet_slug', '=', $packet_slug)
                    ->where('status', '!=', 'DELETED')
                    ->value('feed_slug');

                Program::where('program_slug', '=', $feed_slug)
                    ->where('program_type', '=', $program_type)
                    ->where('status', '!=', 'DELETED')
                    ->update(['updated_at' => time(), 'updated_by' => Auth::user()->username]);
            }

            return response()->json([
                'success_array' => $success_array,
                'error_array' => $error_array,
                'error_count' => count($error_array),
                'success_count' => $success_count,
            ]);
        } catch (ApplicationException $e) {
            $msg = trans('admin/program.packet_slug_missing_error');

            return response()->json(['flag' => 'error', 'message' => $msg]);
        }
    }

    public function postEditPacket($program_type = null, $program_slug = null, $packet_slug = null)
    {
        try {
            $program = $this->programservice->getProgramBySlug($program_type, $program_slug);
            $packet = $this->programservice->getProgramPostBySlug($program_type, $program_slug, $packet_slug)
                        ->toArray();

            switch ($program_type) {
                case 'course':
                    if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                        return parent::getAdminError();
                    }
                    break;
                default:
                    if (!$this->roleService->hasPermission(
                        $this->request->user()->uid,
                        ModuleEnum::CHANNEL,
                        PermissionType::ADMIN,
                        ChannelPermission::MANAGE_CHANNEL_POST,
                        Contexts::PROGRAM,
                        $program->program_id
                    )) {
                        return parent::getAdminError();
                    }
                    break;
            }

            Validator::extend('checkslug', function ($attribute, $value, $parameters) {
                $id[] = $parameters[1];
                $returnval = Packet::where('packet_slug', '=', $parameters[0])
                    ->whereNotIn('_id', $id)
                    ->get()
                    ->toArray();
                if (empty($returnval)) {
                    return true;
                }

                return false;
            });
            Validator::extend('checkslugregex', function ($attribute, $value, $parameters) {
                if (preg_match('/^[a-zA-Z0-9-_]+$/', $value)) {
                    return true;
                }

                return false;
            });

            $messages = [
                'checkslug' => trans('admin/program.check_slug'),
                'checkslugregex' => trans('admin/program.check_slug_regex'),
                'packet_name.required' => trans('admin/program.post_field_required'),
            ];
            $tmpData = Packet::getUniquePacketSlug(Input::get('packet_name'), $packet['feed_slug']);
            $tmpString = "{$tmpData},{$packet["_id"]}";
            $rules = [
                'packet_name' => 'required',
                'packet_slug' => "Required|checkslugregex|checkslug:{$tmpString}",
                'packet_publish_date' => 'Required',
                'status' => 'Required',
                'qanda' => 'Required',
                'access' => 'Required',
            ];
            $validation = Validator::make(Input::all(), $rules, $messages);

            if ($validation->fails()) {
                return redirect("cp/contentfeedmanagement/edit-packet/{$program_type}/{$program_slug}/{$packet_slug}")
                    ->withInput()
                    ->withErrors($validation);
            } elseif ($validation->passes()) {
                // Send notification to the users that there is a change in the meta data
                $programdata = Program::getAllProgramByIDOrSlug($program_type, $packet['feed_slug'])
                    ->first()
                    ->toArray();

                if (Config::get('app.notifications.contentfeed.packetmetadatachange')) {
                    if (isset($programdata['relations']['active_user_feed_rel'])) {
                        $notify_user_ids_ary = [];
                        $notify_user_ids_ary = $programdata['relations']['active_user_feed_rel'];

                        if (!empty($notify_user_ids_ary)) {
                            $notif_msg = trans(
                                'admin/notifications.packetmetachange',
                                [
                                    'adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                                    'packet' => $packet['packet_title']
                                ]
                            );

                            NotificationLog::getInsertNotification(
                                $notify_user_ids_ary,
                                trans('admin/program.packet'),
                                $notif_msg
                            );
                        }
                    }
                    if (isset($programdata['relations']['active_usergroup_feed_rel'])) {
                        $notify_user_ids_ary = [];
                        foreach ($programdata['relations']['active_usergroup_feed_rel'] as $usergroupid) {
                            $usergroup_data = UserGroup::getUserGroupsUsingID((int)$usergroupid);
                            foreach ($usergroup_data as $usergroup) {
                                if (isset($usergroup['relations']['active_user_usergroup_rel'])) {
                                    $notify_user_ids_ary = array_merge(
                                        $notify_user_ids_ary,
                                        $usergroup['relations']['active_user_usergroup_rel']
                                    );
                                }
                            }
                        }

                        if (!empty($notify_user_ids_ary)) {
                            $notif_msg = trans(
                                'admin/notifications.packetmetachange',
                                [
                                    'adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                                    'packet' => $packet['packet_title']
                                ]
                            );

                            NotificationLog::getInsertNotification(
                                $notify_user_ids_ary,
                                trans('admin/program.packet'),
                                $notif_msg
                            );
                        }
                    }
                }

                $status = 'IN-ACTIVE';
                $feed_media_rel = 'packet_banner_media_rel';
                if (Input::get('status') == 'active') {
                    $status = 'ACTIVE';
                }
                $mediaid = Input::get('banner', '');
                $packetData = [
                    'packet_title' => trim(Input::get('packet_name')),
                    'title_lower' => trim(strtolower(Input::get('packet_name'))),
                    'packet_slug' => $tmpData,
                    'packet_description' => Input::get('packet_description'),
                    'packet_publish_date' => (int)Timezone::convertToUTC(
                        Input::get('packet_publish_date'),
                        Auth::user()->timezone,
                        'U'
                    ),
                    'packet_cover_media' => $mediaid,
                    'sequential_access' => Input::get('access', 'no'),
                    'quiz_result' => Input::get('quiz_result', 'no'),
                    'qanda' => Input::get('qanda', 'yes'),
                    'status' => $status,
                    'updated_at' => time(),
                    'updated_by' => Auth::user()->username,
                ];

                Dam::removeMediaRelation($packet['packet_cover_media'], [$feed_media_rel], (int)$packet['packet_id']);
                Dam::updateDAMSRelation($mediaid, $feed_media_rel, (int)$packet['packet_id']);
                Packet::where('packet_slug', '=', $packet_slug)->where('status', '!=', 'DELETED')->update($packetData);
                $slug_changed = $packet['packet_slug'] != $tmpData;
                if (config('elastic.service')) {
                    event(new PostEdited((int)$packet['packet_id'], $slug_changed));
                }
                $feed_slug = Packet::where('packet_slug', '=', $packet_slug)
                    ->where('status', '!=', 'DELETED')
                    ->value('feed_slug');

                Program::where('program_slug', '=', $feed_slug)
                    ->where('program_type', '=', $program_type)
                    ->where('status', '!=', 'DELETED')
                    ->update(['updated_at' => time(), 'updated_by' => Auth::user()->username]);

                $msg = trans('admin/program.packet_edit_success');

                return redirect("/cp/contentfeedmanagement/packets/{$program_type}/{$program_slug}")
                    ->with('success', $msg);
            }
        } catch (ApplicationException $e) {
            Log::error($e->getTraceAsString());
            $msg = trans('admin/program.packet_slug_missing_error');

            return redirect('/cp/contentfeedmanagement/')
                ->with('error', $msg);
        }
    }

    public function postAddElementsLibrary($program_type = null, $program_slug = null, $packet_slug = null)
    {
        try {
            $program = $this->programservice->getProgramBySlug($program_type, $program_slug);
            $packet = $this->programservice->getProgramPostBySlug($program_type, $program_slug, $packet_slug);

            switch ($program_type) {
                case 'course':
                    if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                        return parent::getAdminError();
                    }
                    break;
                default:
                    if (!$this->roleService->hasPermission(
                        $this->request->user()->uid,
                        ModuleEnum::CHANNEL,
                        PermissionType::ADMIN,
                        ChannelPermission::MANAGE_CHANNEL_POST,
                        Contexts::PROGRAM,
                        $program->program_id
                    )) {
                        return parent::getAdminError();
                    }
                    break;
            }

            $packet = $packet->toArray();
            $oldElements = array_get($packet, 'elements', '');
            $serveys = $assignment = $final_array = [];
            $contentfeed = Program::getAllProgramByIDOrSlug($program_type, $packet['feed_slug'])->first()->toArray();
            if (isset($packet['elements']) && is_array($packet['elements']) && count($packet['elements'])) {
                // Delete old relations
                foreach ($packet['elements'] as $element) {
                    if ($element['type'] == 'media') {
                        Dam::removeMediaRelationUsingID($element['id'], ['dams_packet_rel'], $packet['packet_id']);
                    } elseif ($element['type'] == 'assessment') {
                        Quiz::removeQuizRelationForFeed($element['id'], (string)$contentfeed['program_id'], $packet['packet_id']);
                    } elseif ($element['type'] == 'event') {
                        Event::where("event_id", $element['id'])->pull("relations.feed_event_rel.{$contentfeed["program_id"]}", $packet['packet_id']);
                    } elseif ($element['type'] == 'flashcard') {
                        FlashCard::removeFlashcardRelation($element['id'], ['flashcard_packet_rel'], $packet['packet_id']);
                    } elseif ($element['type'] == 'survey') {
                        $serveys[] = $element;
                    }  elseif ($element['type'] == 'assignment') {
                        $assignment[] = $element;
                    }
                }
            }

            

            if (Input::get('media')) {
                $array1 = [];
                $madiaval = explode(',', Input::get('media'));
                foreach ($madiaval as $value) {
                    $array1[] = 'media.' . $value;
                }
                $final_array = array_merge($final_array, $array1);
            }
            if (Input::get('assessment')) {
                $array2 = [];
                $assessmentval = explode(',', Input::get('assessment'));
                foreach ($assessmentval as $value) {
                    $array2[] = 'assessment.' . $value;
                }
                $final_array = array_merge($final_array, $array2);
            }

            if (Input::get('event')) {
                $array3 = [];
                $eventval = explode(',', Input::get('event'));
                foreach ($eventval as $value) {
                    $array3[] = 'event.' . $value;
                }
                $final_array = array_merge($final_array, $array3);
            }

            if (Input::get('flashcard')) {
                $array4 = [];
                $flashcardval = explode(',', Input::get('flashcard'));
                foreach ($flashcardval as $value) {
                    $array4[] = 'flashcard.' . $value;
                }
                $final_array = array_merge($final_array, $array4);
            }

            $insertarr = [];
            foreach ($final_array as $key => $value) {
                $explodedval = explode('.', $value);
                if (!isset($explodedval[0]) || !isset($explodedval[1]) || !in_array($explodedval[0], ['media', 'assessment', 'event', 'flashcard'])) {
                    continue;
                }
                $tempname = '';
                if ($explodedval[0] == 'media') {
                    $media = Dam::getDAMSAssetsUsingAutoID((int)$explodedval[1]);
                    $media = (isset($media[0])) ? $media[0] : [];
                    $tempname = (isset($media['name'])) ? $media['name'] : '';
                    Dam::updateDAMSRelationUsingID((int)$explodedval[1], 'dams_packet_rel', $packet['packet_id']);
                } elseif ($explodedval[0] == 'assessment') {
                    $quiz = Quiz::getQuizAssetsUsingAutoID((int)$explodedval[1]);
                    $quiz = (isset($quiz[0])) ? $quiz[0] : [];
                    $tempname = (isset($quiz['quiz_name'])) ? $quiz['quiz_name'] : '';
                    Quiz::addQuizRelationForFeed((int)$explodedval[1], (string)$contentfeed['program_id'], $packet['packet_id']);
                } elseif ($explodedval[0] == 'event') {
                    $event = Event::getEventsAssetsUsingAutoID((int)$explodedval[1]);
                    $event = (isset($event[0])) ? $event[0] : [];
                    $tempname = (isset($event['event_name'])) ? $event['event_name'] : '';
                    Event::where("event_id", (int)$explodedval[1])->push("relations.feed_event_rel.{$program["program_id"]}", [$packet["packet_id"]]);
                } elseif ($explodedval[0] == 'flashcard') {
                    $flashcard = FlashCard::getFlashcardsAssetsUsingAutoID((int)$explodedval[1]);
                    $flashcard = (isset($flashcard[0])) ? $flashcard[0] : [];
                    $tempname = (isset($flashcard['title'])) ? $flashcard['title'] : '';
                    FlashCard::addFlashcardRelation((int)$explodedval[1], ['flashcard_packet_rel'], $packet['packet_id']);
                }

                $displaynameKey = array_search((int)$explodedval[1], array_column($oldElements, 'id'));
                $displayname = $displaynameKey !== false ? array_get($oldElements[$displaynameKey], 'display_name', '') : '';
                $temparr = ['type' => $explodedval[0], 'order' => ($key + 1), 'id' => (int)$explodedval[1], 'name' => $tempname, 'display_name' => $displayname];
                $insertarr[] = $temparr;
            }
            if (!empty($serveys)) {
                $insertarr = array_merge($insertarr, $serveys);
            }
            if (!empty($assignment)) {
                $insertarr = array_merge($insertarr, $assignment);
            }
            Packet::updateElements($packet_slug, $insertarr);
            if (!empty($insertarr)) {
                if (config('elastic.service')) {
                    event(new ItemsAdded($packet['packet_id']));
                }
            }

            //updating the channel and post record
            Packet::where('packet_slug', '=', $packet_slug)
                ->where('status', '!=', 'DELETED')
                ->update(['updated_at' => time(), 'updated_by' => Auth::user()->username]);

            $feed_slug = Packet::where('packet_slug', '=', $packet_slug)
                ->where('status', '!=', 'DELETED')
                ->value('feed_slug');

            Program::where('program_slug', '=', $feed_slug)
                ->where('program_type', '=', $program_type)
                ->where('status', '!=', 'DELETED')
                ->update(['updated_at' => time(), 'updated_by' => Auth::user()->username]);

            $success = trans('admin/program.post_items_success');
            return redirect("/cp/contentfeedmanagement/edit-packet/{$program_type}/{$program_slug}/{$packet_slug}#items-tab")
                ->with('success', $success);
        } catch (ApplicationException $e) {
            $msg = trans('admin/program.packet_slug_missing_error');

            return redirect('/cp/contentfeedmanagement/')
                ->with('error', $msg);
        }
    }

    public function getRemoveItems(
        $program_type = null,
        $program_slug = null,
        $packet_slug = null,
        $element_type = null,
        $element_id = null
    ) {
        try {
            $program = $this->programservice->getProgramBySlug($program_type, $program_slug);
            $packet = $this->programservice->getProgramPostBySlug($program_type, $program_slug, $packet_slug)
                        ->toArray();

            switch ($program_type) {
                case 'course':
                    if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                        return parent::getAdminError();
                    }
                    break;
                default:
                    if (!$this->roleService->hasPermission(
                        $this->request->user()->uid,
                        ModuleEnum::CHANNEL,
                        PermissionType::ADMIN,
                        ChannelPermission::MANAGE_CHANNEL_POST,
                        Contexts::PROGRAM,
                        $program->program_id
                    )) {
                        return parent::getAdminError();
                    }
                    break;
            }

            $element_id = (int)$element_id;
            $feed_slug = Packet::where('packet_slug', '=', $packet_slug)
                ->where('status', '!=', 'DELETED')
                ->value('feed_slug');

            $program_id = Program::where('program_slug', '=', $feed_slug)
                ->where('program_type', '=', $program_type)
                ->where('status', '!=', 'DELETED')
                ->value('program_id');

            //Remove packet id from libraries
            if ($element_type == 'media') {
                Dam::removeMediaRelationUsingID($element_id, ['dams_packet_rel'], $packet['packet_id']);
            } elseif ($element_type == 'assessment') {
                Quiz::removeQuizRelationForFeed($element_id, (string)$program_id, $packet['packet_id']);
            } elseif ($element_type == 'event') {
                Event::where("event_id", $element_id)->where("relations.feed_event_rel.{$program["program_id"]}", "exists", true)->pull("relations.feed_event_rel.{$program["program_id"]}", [$packet["packet_id"]]);
            } elseif ($element_type == 'flashcard') {
                FlashCard::removeFlashcardRelation($element_id, ['flashcard_packet_rel'], $packet['packet_id']);
            }
    
            //Remove item from packet elements array
            Packet::where('packet_slug', '=', $packet_slug)->pull('elements', ['id' => (int)$element_id, 'type' => $element_type]);
            if (config('elastic.service')) {
                event(new ItemsAdded($packet['packet_id']));
            }
            //updating the channel and post record
            Packet::where('packet_slug', '=', $packet_slug)
                ->where('status', '!=', 'DELETED')
                ->update(['updated_at' => time(), 'updated_by' => Auth::user()->username]);

            Program::where('program_slug', '=', $feed_slug)
                ->where('program_type', '=', $program_type)
                ->where('status', '!=', 'DELETED')
                ->update(['updated_at' => time(), 'updated_by' => Auth::user()->username]);

            $success = trans('admin/program.delete_items_success');
            return redirect("/cp/contentfeedmanagement/edit-packet/{$program_type}/{$program_slug}/{$packet_slug}#items-tab")
                ->with('success', $success);
        } catch (ApplicationException $e) {
            $msg = trans('admin/program.packet_slug_missing_error');
            return redirect('/cp/contentfeedmanagement/')
                ->with('error', $msg);
        }
    }

    public function postEditItems($packet_slug, $element_type, $element_id, $type)
    {
        $this->layout->pagetitle = '';

        $data = [
            'packet_slug' => $packet_slug,
            'element_type' => $element_type,
            'element_id' => $element_id,
            'type' => $type,
            'item_name' => Input::get('item_name'),
            'display_name' => Input::get('display_name'),
        ];
        return $data;
    }

    public function postEditNames(
        $program_type = null,
        $program_slug = null,
        $packet_slug = null,
        $element_type = null,
        $element_id = null
    ) {
        $json = [];
        try {
            $program = $this->programservice->getProgramBySlug($program_type, $program_slug);
            $packet = $this->programservice->getProgramPostBySlug($program_type, $program_slug, $packet_slug)
                ->toArray();

            switch ($program_type) {
                case 'course':
                    if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                        return response()->json(array_merge($json, ['status' => 'error']));
                    }
                    break;
                default:
                    if (!$this->roleService->hasPermission(
                        $this->request->user()->uid,
                        ModuleEnum::CHANNEL,
                        PermissionType::ADMIN,
                        ChannelPermission::MANAGE_CHANNEL_POST,
                        Contexts::PROGRAM,
                        $program->program_id
                    )) {
                        return response()->json(array_merge($json, ['status' => 'error']));
                    }
                    break;
            }

            $data = Input::all();
            $rules = [
                'item_name' => 'Required',
            ];

            $validation = Validator::make(Input::all(), $rules);
            foreach ($data as $key => $value) {
                $json[$key] = '';
            }

            foreach ($validation->getMessageBag()->toArray() as $key => $eachError) {
                $json = array_merge($json, [$key => ucfirst($eachError[0])]);
            }

            if ($validation->fails()) {
                $json = array_merge($json, ['status' => 'error']);
                return response()->json($json);
            } elseif ($validation->passes()) {
                $element_id = (int)$element_id;

                foreach ($packet['elements'] as $element) {
                    if ($element['id'] == (int)$element_id && $element['type'] == $element_type) {
                        $push_array = [
                            'type' => $element['type'],
                            'order' => $element['order'],
                            'id' => (int)$element['id'],
                            'name' => Input::get('item_name'),
                            'display_name' => Input::get('display_name'),
                        ];
                        Packet::where('packet_slug', '=', $packet_slug)->pull('elements', ['id' => (int)$element['id']]);
                        Packet::where('packet_slug', '=', $packet_slug)->push('elements', $push_array);

                        if ($element_type == 'media') {
                            Dam::where('id', '=', (int)$element_id)->update(['name' => Input::get('item_name'), 'name_lower' => strtolower(Input::get('item_name'))]);
                        } elseif ($element_type == 'assessment') {
                            Quiz::where('quiz_id', '=', (int)$element_id)->update(['quiz_name' => Input::get('item_name')]);
                        } elseif ($element_type == 'event') {
                            Event::where('event_id', '=', (int)$element_id)->update(['event_name' => Input::get('item_name')]);
                        } elseif ($element_type == 'flashcard') {
                            FlashCard::where('card_id', '=', (int)$element_id)->update(['title' => Input::get('item_name')]);
                        }
                    }
                }
                if (config('elastic.service')) {
                    event(new ItemsAdded($packet['packet_id']));
                }
                $msg = trans('admin/program.item_edit_success');
                Session::put('success', $msg);
                $json = [
                    'status' => 'success',
                ];
                return response()->json($json);
            }
        } catch (ApplicationException $e) {
            Log::error($e->getTraceAsString());
            return response()->json(array_merge($json, ['status' => 'error']));
        }
    }

    public function getElements($slug = null, $type = 'content_feed')
    {
        switch ($type) {
            case 'course':
                if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                    return parent::getAdminError($this->theme_path);
                }
                break;
            default:
                if (!has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_POST)) {
                    return parent::getAdminError($this->theme_path);
                }
                break;
        }

        $packet = Packet::getPacket($slug);
        if ($slug == null || empty($packet)) {
            $msg = trans('admin/program.packet_slug_missing_error');

            return redirect('/cp/contentfeedmanagement/')
                ->with('error', $msg);
        } else {
            $packet = $packet[0];
            $crumbs = [
                trans('admin/dashboard.dashboard') => 'cp',
                trans('admin/program.manage_packets') => 'contentfeedmanagement/packets/' . $packet['feed_slug'] . '/' . $type,
                trans('admin/program.manage_elements') => '',
            ];
            $mediaElements = [];
            $assessmentElements = [];
            $eventElements = [];
            $flashcardElements = [];
            $dam_ele = [];
            $assessment_ele = [];
            $event_ele = [];
            $flashcard_ele = [];
            if (isset($packet['elements']) && is_array($packet['elements']) && count($packet['elements'])) {
                foreach ($packet['elements'] as $key => &$value) {
                    if ($value['type'] == 'media') {
                        $mediaElements['ids'][] = $value['id'];
                        $mediaElements['names'][$value['id']] = isset($value['name']) ? $value['name'] : '';
                        $value['media_type'] = ucfirst(Dam::getAssetType((int)$value['id']));
                        $dam_ele[] = $value['id'];
                    } elseif ($value['type'] == 'assessment') {
                        $assessmentElements['ids'][] = $value['id'];
                        $assessmentElements['names'][$value['id']] = isset($value['name']) ? $value['name'] : '';
                        $assessment_ele[] = $value['id'];
                    } elseif ($value['type'] == 'event') {
                        $eventElements['ids'][] = $value['id'];
                        $eventElements['names'][$value['id']] = isset($value['name']) ? $value['name'] : '';
                        $event_ele[] = $value['id'];
                    } elseif ($value['type'] == 'flashcard') {
                        $flashcardElements['ids'][] = $value['id'];
                        $flashcardElements['names'][$value['id']] = isset($value['name']) ? $value['name'] : '';
                        $flashcard_ele[] = $value['id'];
                    }
                }
                if (Config::get('app.get_ele_live')) {
                    if (!empty($dam_ele)) {
                        $res = Dam::getMediaNameByID($dam_ele);
                        foreach ($res as $media) {
                            $mediaElements['names'][$media->id] = $media->name;
                        }
                    }
                    if (!empty($assessment_ele)) {
                        $res = Quiz::getQuizNameByID($assessment_ele);
                        foreach ($res as $quiz) {
                            $assessmentElements['names'][$quiz->quiz_id] = $quiz->quiz_name;
                        }
                    }
                    if (!empty($event_ele)) {
                        $res = Event::getEventNameByID($event_ele);
                        foreach ($res as $event) {
                            $eventElements['names'][$event->event_id] = $event->event_name;
                        }
                    }
                    if (!empty($flashcard_ele)) {
                        $res = FlashCard::getFCNameByID($flashcard_ele);
                        foreach ($res as $fc) {
                            $flashcard_ele['names'][$fc->card_id] = $fc->title;
                        }
                    }
                }
            }
            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = trans('admin/program.elements');
            $this->layout->pageicon = 'fa fa-rss';
            $this->layout->pagedescription = trans('admin/program.elements');
            $this->layout->header = view('admin.theme.common.header');
            if ($type == 'course') {
                $this->layout->sidebar = view('admin.theme.common.sidebar')
                    ->with('mainmenu', 'program')
                    ->with('submenu', 'course');
            } else {
                $this->layout->sidebar = view('admin.theme.common.sidebar')
                    ->with('mainmenu', 'program')
                    ->with('submenu', 'contentfeed');
            }
            $this->layout->content = view('admin.theme.programs.elements')
                ->with('slug', $slug)
                ->with('mediaelements', $mediaElements)
                ->with('assessmentelements', $assessmentElements)
                ->with('eventelements', $eventElements)
                ->with('flashcardelements', $flashcardElements)
                ->with('packet', $packet)
                ->with('type', $type);
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }

    public function postSortItems($program_type = null, $program_slug = null, $packet_slug = null)
    {
        try {
            $program = $this->programservice->getProgramBySlug($program_type, $program_slug);
            $this->programservice->getProgramPostBySlug($program_type, $program_slug, $packet_slug);

            switch ($program_type) {
                case 'course':
                    if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                        return response()->json(["flag" => "error"]);
                    }
                    break;
                default:
                    if (!$this->roleService->hasPermission(
                        $this->request->user()->uid,
                        ModuleEnum::CHANNEL,
                        PermissionType::ADMIN,
                        ChannelPermission::MANAGE_CHANNEL_POST,
                        Contexts::PROGRAM,
                        $program->program_id
                    )) {
                        return response()->json(["flag" => "error"]);
                    }
                    break;
            }

            $dataids = Input::get('data', []);
            $insertarr = [];
            foreach ($dataids as $key => $value) {
                $temparr = [
                    'type' => array_get($value, 'type', ''),
                    'order' => ($key + 1),
                    'id' => (int)array_get($value, 'id', 0),
                    'name' => array_get($value, 'name', ''),
                    'display_name' => array_get($value, 'display_name', ''),
                ];
                $insertarr[] = $temparr;
            }
            Packet::updateElements($packet_slug, $insertarr);

            //updating the channel and post record
            Packet::where('packet_slug', '=', $packet_slug)
                ->where('status', '!=', 'DELETED')
                ->update(['updated_at' => time(), 'updated_by' => Auth::user()->username]);

            Program::where('program_slug', '=', $program_slug)
                ->where('program_type', '=', $program_type)
                ->where('status', '!=', 'DELETED')
                ->update(['updated_at' => time(), 'updated_by' => Auth::user()->username]);

            return response()->json(['flag' => 'success']);
        } catch (ApplicationException $e) {
            return response()->json(["flag" => "error"]);
        }
    }

    public function postAssignElements($slug = null, $type = 'content_feed')
    {
        switch ($slug) {
            case 'course':
                if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                    return parent::getAdminError($this->theme_path);
                }
                break;
            default:
                if (!has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_POST)) {
                    return parent::getAdminError($this->theme_path);
                }
                break;
        }

        $packet = Packet::getPacket($slug);
        if ($slug == null || empty($packet)) {
            $msg = trans('admin/program.packet_slug_missing_error');

            return response()->json(['flag' => 'error', 'message' => $msg]);
        }
        $packet = $packet[0];
        $contentfeed = Program::getAllProgramByIDOrSlug($type, $packet['feed_slug'])->first()->toArray();

        if (isset($packet['elements']) && is_array($packet['elements']) && count($packet['elements'])) {
            // Delete old relations
            foreach ($packet['elements'] as $element) {
                if ($element['type'] == 'media') {
                    Dam::removeMediaRelationUsingID($element['id'], ['dams_packet_rel'], $packet['packet_id']);
                } elseif ($element['type'] == 'assessment') {
                    Quiz::removeQuizRelationForFeed($element['id'], (string)$contentfeed['program_id'], $packet['packet_id']);
                } elseif ($element['type'] == 'event') {
                    Event::where("event_id", $element['id'])->pull("relations.feed_event_rel.{$contentfeed["program_id"]}", [$packet["packet_id"]]);
                } elseif ($element['type'] == 'flashcard') {
                    FlashCard::removeFlashcardRelation($element['id'], ['flashcard_packet_rel'], $packet['packet_id']);
                }

                // Event is pending
            }
        }
        $dataids = Input::get('data', []);
        $insertarr = [];
        foreach ($dataids as $key => $value) {
            $explodedval = explode('.', $value);
            if (!isset($explodedval[0]) || !isset($explodedval[1]) || !in_array($explodedval[0], ['media', 'assessment', 'event', 'flashcard'])) {
                continue;
            }
            $tempname = '';
            if ($explodedval[0] == 'media') {
                $media = Dam::getDAMSAssetsUsingAutoID((int)$explodedval[1]);
                $media = (isset($media[0])) ? $media[0] : [];
                $tempname = (isset($media['name'])) ? $media['name'] : '';
                Dam::updateDAMSRelationUsingID((int)$explodedval[1], 'dams_packet_rel', $packet['packet_id']);
            } elseif ($explodedval[0] == 'assessment') {
                $quiz = Quiz::getQuizAssetsUsingAutoID((int)$explodedval[1]);
                $quiz = (isset($quiz[0])) ? $quiz[0] : [];
                $tempname = (isset($quiz['quiz_name'])) ? $quiz['quiz_name'] : '';
                Quiz::addQuizRelationForFeed((int)$explodedval[1], (string)$contentfeed['program_id'], $packet['packet_id']);
            } elseif ($explodedval[0] == 'event') {
                $event = Event::getEventsAssetsUsingAutoID((int)$explodedval[1]);
                $event = (isset($event[0])) ? $event[0] : [];
                $tempname = (isset($event['event_name'])) ? $event['event_name'] : '';
                Event::where("event_id", (int)$explodedval[1])->push("relations.feed_event_rel.{$contentfeed["program_id"]}", [$packet["packet_id"]]);
            } elseif ($explodedval[0] == 'flashcard') {
                $flashcard = FlashCard::getFlashcardsAssetsUsingAutoID((int)$explodedval[1]);
                $flashcard = (isset($flashcard[0])) ? $flashcard[0] : [];
                $tempname = (isset($flashcard['title'])) ? $flashcard['title'] : '';
                FlashCard::addFlashcardRelation((int)$explodedval[1], ['flashcard_packet_rel'], $packet['packet_id']);
            }

            $temparr = ['type' => $explodedval[0], 'order' => ($key + 1), 'id' => (int)$explodedval[1], 'name' => $tempname];
            $insertarr[] = $temparr;
        }
        Packet::updateElements($slug, $insertarr);

        //updating the channel and post record
        Packet::where('packet_slug', '=', $slug)->where('status', '!=', 'DELETED')->update(['updated_at' => time(), 'updated_by' => Auth::user()->username]);
        $feed_slug = Packet::where('packet_slug', '=', $slug)->where('status', '!=', 'DELETED')->value('feed_slug');
        Program::where('program_slug', '=', $feed_slug)->where('program_type', '=', $type)->where('status', '!=', 'DELETED')->update(['updated_at' => time(), 'updated_by' => Auth::user()->username]);

        return response()->json(['flag' => 'success']);
    }

    public function getDeletePacket($program_type = null, $program_slug = null, $packet_slug = null)
    {
        try {
            $program = $this->programservice->getProgramBySlug($program_type, $program_slug);
            $packet = $this->programservice->getProgramPostBySlug($program_type, $program_slug, $packet_slug)
                        ->toArray();

            switch ($program_type) {
                case 'course':
                    if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                        return parent::getAdminError();
                    }
                    break;
                default:
                    if (!$this->roleService->hasPermission(
                        $this->request->user()->uid,
                        ModuleEnum::CHANNEL,
                        PermissionType::ADMIN,
                        ChannelPermission::MANAGE_CHANNEL_POST,
                        Contexts::PROGRAM,
                        $program->program_id
                    )) {
                        return parent::getAdminError();
                    }
                    break;
            }

            $start = Input::get('start', 0);
            $limit = Input::get('limit', 10);
            $search = Input::get('search', '');
            $order_by = Input::get('order_by', '3 desc');

            $program = $program->toArray();
            $error = false;

            if (!$error) {
                // Delete logic comes here
                if (isset($packet['packet_cover_media']) && $packet['packet_cover_media']) {
                    Dam::removeMediaRelation(
                        $packet['packet_cover_media'],
                        ['packet_banner_media_rel'],
                        (int)$packet['packet_id']
                    );
                }

                if (isset($packet['elements']) && is_array($packet['elements']) && !empty($packet['elements'])) {
                    foreach ($packet['elements'] as $element) {
                        if ($element['type'] == 'media') {
                            Dam::removeMediaRelationUsingID(
                                $element['id'],
                                ['dams_packet_rel'],
                                (int)$packet['packet_id']
                            );
                        } elseif ($element['type'] == 'assessment') {
                            Quiz::removeQuizRelationForFeed(
                                $element['id'],
                                (string)$program['program_id'],
                                (int)$packet['packet_id']
                            );
                        } elseif ($element['type'] == 'event') {
                            Event::where("event_id", $element['id'])->pull("relations.feed_event_rel.{$program["program_id"]}", [(int) $packet["packet_id"]]);
                        } elseif ($element['type'] == 'survey') {
                            Survey::where("id", $element['id'])->update(["post_id" => null]);
                        } elseif ($element['type'] == 'assignment') {
                            Assignment::where("id", $element['id'])->update(["post_id" => null]);
                        }
                    }
                } else {
                    Packet::where('packet_slug', '=', $packet_slug)
                        ->where('status', '!=', 'DELETED')
                        ->where('feed_slug', '=', $packet['feed_slug'])
                        ->delete();
                    $msg = trans('admin/program.packet_delete_rel_success');

                    return redirect("cp/contentfeedmanagement/packets/{$program_type}/{$program_slug}?start={$start}&limit={$limit}&search={$search}&order_by={$order_by}")
                        ->with('success', $msg);
                }

                $current_date = Carbon::create()->timestamp;
                Packet::updatePacket(
                    $packet['packet_slug'],
                    [
                        'packet_slug' => "{$packet["packet_slug"]}_deleted_{$current_date}",
                        'status' => 'DELETED',
                        'elements' => [],
                        'assignment_ids' => [],
                        'survey_ids' => [],
                        'updated_by' => Auth::user()->username,
                        'updated_at' => $current_date
                    ]
                );

                PacketFaq::getDeletePostSpecificQuestions($packet['packet_id']);
                if (config('elastic.service')) {
                    event(new PostRemoved($packet['packet_id']));
                }
                $totalRecords = Packet::getPacketsCount($packet_slug);
                if ($totalRecords <= $start) {
                    $start -= $limit;
                    if ($start < 0) {
                        $start = 0;
                    }
                }
                $msg = trans('admin/program.packet_delete_rel_success');

                return redirect("cp/contentfeedmanagement/packets/{$program_type}/{$program_slug}?start={$start}&limit={$limit}&search={$search}&order_by={$order_by}")
                    ->with('success', $msg);
            } else {
                $msg = trans('admin/program.packet_delete_rel_error');

                return redirect("cp/contentfeedmanagement/packets/{$program_type}/{$program_slug}?start={$start}&limit={$limit}&search={$search}&order_by={$order_by}")
                    ->with('error', $msg);
            }
        } catch (ApplicationException $e) {
            $msg = trans('admin/program.packet_slug_missing_error');

            return redirect('/cp/contentfeedmanagement/')
                ->with('error', $msg);
        }
    }

    public function getPacketDetails($program_type = null, $program_slug = null, $packet_slug = null)
    {
        try {
            $program = $this->programservice->getProgramBySlug($program_type, $program_slug);
            $packet = $this->programservice->getProgramPostBySlug($program_type, $program_slug, $packet_slug)
                ->toArray();

            switch ($program_type) {
                case 'course':
                    if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                        return response()->json([]);
                    }
                    break;
                default:
                    if (!$this->roleService->hasPermission(
                        $this->request->user()->uid,
                        ModuleEnum::CHANNEL,
                        PermissionType::ADMIN,
                        ChannelPermission::MANAGE_CHANNEL_POST,
                        Contexts::PROGRAM,
                        $program->program_id
                    )) {
                        return response()->json([]);
                    }
                    break;
            }

            $from = '';

            $packet['packet_publish_date'] = Timezone::convertFromUTC(
                '@' . $packet['packet_publish_date'],
                Auth::user()->timezone,
                config('app.date_format')
            );

            $packet['created_at'] = Timezone::convertFromUTC(
                '@' . $packet['created_at'],
                Auth::user()->timezone,
                config('app.date_format')
            );

            $packet['updated_at'] = Timezone::convertFromUTC(
                '@' . $packet['updated_at'],
                Auth::user()->timezone,
                config('app.date_format')
            );

            $media = '';
            if (isset($packet['packet_cover_media'])) {
                $media = Dam::getDAMSAssetsUsingID($packet['packet_cover_media']);
                if (!empty($media)) {
                    $media = $media[0];
                }
            }
            $uniconf_id = Config::get('app.uniconf_id');
            $kaltura_url = Config::get('app.kaltura_url');
            $partnerId = Config::get('app.partnerId');
            $kaltura =
                "{$kaltura_url}index.php/kwidget/cache_st/1389590657/wid/_{$partnerId}/uiconf_id/{$uniconf_id}/entry_id/";

            return view('admin.theme.programs.packetdetails')
                ->with('packet', $packet)
                ->with('media', $media)
                ->with('kaltura', $kaltura)
                ->with('from', $from);
        } catch (ApplicationException $e) {
            Log::error($e->getTraceAsString());
        }
    }

    public function getPostQuestionsListTemplate($program_type = null, $program_slug = null, $post_slug = null)
    {
        switch ($program_type) {
            case 'course':
                if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                     return $this->getAdminError();
                }
                break;
            default:
                if (!has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_POST)) {
                    return $this->getAdminError();
                }
                break;
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => "cp",
            trans('admin/program.manage_post_question') =>
                "contentfeedmanagement/post-questions-list-template/{$program_type}/{$program_slug}/{$post_slug}",
            trans('admin/program.list_question') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/program.manage_post_question');
        $this->layout->pageicon = 'fa fa-question';
        $this->layout->pagedescription = trans('admin/program.manage_post_question');
        $this->layout->header = view('admin.theme.common.header');
        if ($program_type == 'course') {
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'course');
        } else {
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'program')
                ->with('submenu', 'contentfeed');
        }
        $this->layout->content = view('admin.theme.programs.post-questions-list-template')
            ->with("program_type", $program_type)
            ->with("program_slug", $program_slug)
            ->with("packet_slug", $post_slug);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getPostQuestionsListData($program_type = null, $program_slug = null, $post_slug = null)
    {
        $totalRecords = 0;
        $filteredRecords = 0;
        $dataArr = [];

        try {
            $program = $this->programservice->getProgramBySlug($program_type, $program_slug);

            switch ($program_type) {
                case 'course':
                    $has_permission = has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST);
                    break;
                default:
                    $has_permission = $this->roleService->hasPermission(
                        $this->request->user()->uid,
                        ModuleEnum::CHANNEL,
                        PermissionType::ADMIN,
                        ChannelPermission::MANAGE_CHANNEL_POST,
                        Contexts::PROGRAM,
                        $program->program_id
                    );
                    break;
            }

            if ($has_permission) {
                $packet = $this->programservice->getProgramPostBySlug($program_type, $program_slug, $post_slug);

                $filter_params = [];
                $totalRecords = $this->programservice->getPostQuestionsCount($packet->packet_id, $filter_params);

                $filter_params["search_key"] = $this->request->input("search.value");
                $status = $this->request->input("filter", null);
                if ($status !== "ALL") {
                    $filter_params["status"] = $status;
                }

                $filteredRecords = $this->programservice->getPostQuestionsCount($packet->packet_id, $filter_params);

                $order_by_columns = ["created_at", "question", null, null, "created_by_name"];
                $order_by_column_index = $this->request->input("order.0.column", 0);

                if (array_has($order_by_columns, $order_by_column_index)) {
                    $filter_params["order_by"] = $order_by_columns[$order_by_column_index];
                    $filter_params["order_by_dir"] = $this->request->input("order.0.dir", "desc");
                }

                $filter_params["start"] = $this->request->input("start", 0);
                $filter_params["limit"] = $this->request->input("length", 10);

                $filtereddata = $this->programservice->getPostQuestions($packet->packet_id, $filter_params);
                $dataArr = [];
                foreach ($filtereddata as $key => $value) {
                    $temparr = [
                        Timezone::convertFromUTC('@' . $value['created_at'], Auth::user()->timezone, config('app.date_format')),
                        "<div>" . $value['question'] . "</div>",
                        "<div>" . $packet->packet_title . "</div>",
                        "<div>" . $program->program_title . "</div>",
                        $value['created_by_name'],
                        ucfirst(strtolower($value['status'])),
                    ];
                    if ($value['access'] == 'private') {
                        $temparr[] = '<a class="btn btn-circle show-tooltip" target="_blank"
                                title="'. trans('admin/manageweb.action_portal_view').'"
                                href="'. URL::to('program/packet/' . $packet->packet_slug . '?from=allquestions') .'">
                                    <i class="fa fa-image"></i>
                            </a>
                            <a class="btn btn-circle show-tooltip" title="'. trans('admin/manageweb.action_view').'"
                                href="'.URL::to("cp/contentfeedmanagement/question/{$packet->packet_id}/{$value['id']}").'">
                                    <i class="fa fa-eye"></i>
                            </a>
                            <a class="btn btn-circle show-tooltip faq" data-action="addfaq"
                                data-text="Are you sure you want to mark this question as an FAQ?"
                                title="'.trans('admin/program.mark_as_faq').'"
                                href="'.URL::to("cp/contentfeedmanagement/question-action/{$packet["packet_id"]}/{$value["id"]}/addtofaq").'">
                                    <i class="fa fa-square-o"></i>
                            </a>';
                    } else {
                        $temparr[] = '<a class="btn btn-circle show-tooltip" target="_blank"
                                title="'. trans('admin/manageweb.action_portal_view').'"
                                href="'. URL::to('program/packet/' . $packet->packet_slug . '?from=allquestions') .'">
                                    <i class="fa fa-image"></i>
                            </a>
                            <a class="btn btn-circle show-tooltip" title="'. trans('admin/manageweb.action_view').'"
                                href="'.URL::to("cp/contentfeedmanagement/question/{$packet->packet_id}/{$value['id']}").'">
                                    <i class="fa fa-eye"></i>
                            </a>
                            <a class="btn btn-circle show-tooltip faq" data-action="removefaq"
                                data-text="Are you sure you want to remove this question from FAQ?"
                                title="'.trans('admin/program.remove_from_faq').'"
                                href="'.URL::to("cp/contentfeedmanagement/question-action/{$packet["packet_id"]}/{$value["id"]}/removefromfaq").'">
                                    <i class="fa fa-check-square-o"></i>
                            </a>';
                    }
                    $dataArr[] = $temparr;
                }
            }
        } catch (ApplicationException $e) {
            Log::error($e->getTraceAsString());
        }

        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr,
        ];

        return response()->json($finaldata);
    }

    public function getQuestion($post_id = null, $question_id = null)
    {
        try {
            $question = $this->programservice->getPostQuestion($post_id, $question_id);
            $packet = $question->packet;
            $program = $packet->program;

            switch ($program->program_type) {
                case 'course':
                    if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                        return parent::getAdminError();
                    }
                    break;
                default:
                    if (!$this->roleService->hasPermission(
                        $this->request->user()->uid,
                        ModuleEnum::CHANNEL,
                        PermissionType::ADMIN,
                        ChannelPermission::MANAGE_CHANNEL_POST,
                        Contexts::PROGRAM,
                        $program->program_id
                    )) {
                        return parent::getAdminError();
                    }
                    break;
            }

            $answers = PacketFaqAnswers::getAnswersByQuestionID($question['id']);
            $answers = $answers->toArray();
            $crumbs = [
                trans('admin/dashboard.dashboard') => "cp",
                trans('admin/program.manage_question') =>
                    "contentfeedmanagement/post-questions-list-template/{$program->program_type}/{$packet->feed_slug}/{$packet->packet_slug}",
                trans('admin/program.view_question') => "",
            ];

            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = trans('admin/program.view_question');
            $this->layout->pageicon = 'fa fa-question';
            $this->layout->pagedescription = trans('admin/program.view_question');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'program')
                ->with('submenu', 'postquestions');
            $this->layout->content = view('admin.theme.programs.questionandanswers')
                ->with("post", $packet)
                ->with('question', $question)
                ->with('answers', $answers);
            $this->layout->footer = view('admin.theme.common.footer');
        } catch (QuestionNotFoundException $e) {
            Log::error($e->getTraceAsString());
            return parent::getAdmin404Error();
        }
    }

    public function postAnswer($post_id = null, $question_id = null)
    {
        try {
            $question = $this->programservice->getPostQuestion($post_id, $question_id);
            $packet = $question->packet;
            $program = $packet->program;

            switch ($program->program_type) {
                case 'course':
                    if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                        return parent::getAdminError();
                    }
                    break;
                default:
                    if (!$this->roleService->hasPermission(
                        $this->request->user()->uid,
                        ModuleEnum::CHANNEL,
                        PermissionType::ADMIN,
                        ChannelPermission::MANAGE_CHANNEL_POST,
                        Contexts::PROGRAM,
                        $program->program_id
                    )) {
                        return parent::getAdminError();
                    }
                    break;
            }

            $rules = [
                'answer' => 'Required',
            ];
            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                return redirect("cp/contentfeedmanagement/question/{$post_id}/{$question_id}")->withInput()
                    ->withErrors($validation);
            } elseif ($validation->passes()) {
                // Update the total_unanswered questions for packets
                $oldanswers = PacketFaqAnswers::getAnswersByQuestionID($question['id'], $question['user_id']);
                if ($oldanswers->isEmpty()) {
                    // Decrease one count from total unanswered questions count of packet
                    Packet::where('packet_id', '=', (int)$question['packet_id'])->where('total_ques_unanswered', '>', 0)
                        ->decrement('total_ques_unanswered');
                }

                PacketFaq::where('id', '=', $question['id'])->update(['status' => 'ANSWERED']);
                $insertarr = [
                    'id' => PacketFaqAnswers::getUniqueId(),
                    'ques_id' => (int)$question_id,
                    'user_id' => Auth::user()->uid,
                    'username' => Auth::user()->username,
                    'answer' => Input::get('answer'),
                    'status' => 'ACTIVE',
                    'created_at' => time(),
                    'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                ];
                // Send a notification to the user.
                if (Config::get('app.notifications.packetsfaq.answered')) {
                    $notif_msg = trans(
                        'admin/notifications.answered',
                        ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname]
                    );
                    NotificationLog::getInsertNotification([$question['user_id']], 'packetfaq', $notif_msg);
                }

                PacketFaqAnswers::insert($insertarr);
                $msg = trans('admin/qanda.answer_success');
                return redirect("/cp/contentfeedmanagement/question/{$post_id}/{$question_id}")
                    ->with('success', $msg);
            }
        } catch (QuestionNotFoundException $e) {
            Log::error($e->getTraceAsString());
            return parent::getAdmin404Error();
        }
    }

    public function getQuestionAction($post_id = null, $question_id = null, $action = null)
    {
        try {
            $question = $this->programservice->getPostQuestion($post_id, $question_id);
            $packet = $question->packet;
            $program = $packet->program;

            switch ($program->program_type) {
                case 'course':
                    if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                        return parent::getAdminError();
                    }
                    break;
                default:
                    if (!$this->roleService->hasPermission(
                        $this->request->user()->uid,
                        ModuleEnum::CHANNEL,
                        PermissionType::ADMIN,
                        ChannelPermission::MANAGE_CHANNEL_POST,
                        Contexts::PROGRAM,
                        $program->program_id
                    )) {
                        return parent::getAdminError();
                    }
                    break;
            }

            switch ($action) {
                case "addtofaq":
                    PacketFaq::where('id', '=', (int)$question_id)->update(['access' => 'public']);

                    // Decrease one count from total private questions count of packet
                    Packet::where('packet_id', '=', (int)$question['packet_id'])->where('total_ques_private', '>', 0)
                        ->decrement('total_ques_private');

                    // Increase one count from total public questions count of packet
                    Packet::where('packet_id', '=', (int)$question['packet_id'])->increment('total_ques_public');

                    // Send a notification to the user.
                    if (Config::get('app.notifications.packetsfaq.addtofaq')) {
                        $notif_msg = trans(
                            'admin/notifications.addtofaq',
                            [
                                'adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                                'question' => $question['question']
                            ]
                        );

                        NotificationLog::getInsertNotification([$question['user_id']], 'packetfaq', $notif_msg);
                    }

                    $msg = trans('admin/qanda.faq_success');
                    return redirect("/cp/contentfeedmanagement/post-questions-list-template/{$program->program_type}/{$program->program_slug}/{$packet->packet_slug}")
                        ->with('success', $msg);

                    break;
                case 'removefromfaq':
                    PacketFaq::where('id', '=', (int)$question_id)->update(['access' => 'private']);

                    // Decrease one count from total public questions count of packet
                    Packet::where('packet_id', '=', (int)$question['packet_id'])->where('total_ques_public', '>', 0)
                        ->decrement('total_ques_public');

                    // Increase one count from total private questions count of packet
                    Packet::where('packet_id', '=', (int)$question['packet_id'])->increment('total_ques_private');

                    $msg = trans('admin/qanda.faq_remove_success');
                    return redirect("/cp/contentfeedmanagement/post-questions-list-template/{$program->program_type}/{$program->program_slug}/{$packet->packet_slug}")
                        ->with('success', $msg);
                    break;
            }
        } catch (QuestionNotFoundException $e) {
            Log::error($e->getTraceAsString());
            return parent::getAdmin404Error();
        }
    }

    public function getDeleteAnswer($post_id = null, $question_id = null, $answer_id = null)
    {
        try {
            $question = $this->programservice->getPostQuestion($post_id, $question_id);
            $packet = $question->packet;
            $program = $packet->program;

            switch ($program->program_type) {
                case 'course':
                    if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                        return parent::getAdminError();
                    }
                    break;
                default:
                    if (!$this->roleService->hasPermission(
                        $this->request->user()->uid,
                        ModuleEnum::CHANNEL,
                        PermissionType::ADMIN,
                        ChannelPermission::MANAGE_CHANNEL_POST,
                        Contexts::PROGRAM,
                        $program->program_id
                    )) {
                        return parent::getAdminError();
                    }
                    break;
            }

            $answer = PacketFaqAnswers::getAnswersByAnswerID((int)$answer_id);
            if ($answer->isEmpty()) {
                return parent::getAdmin404Error();
            }

            $answer = $answer->first()->toArray();
            PacketFaqAnswers::where('id', '=', (int)$answer_id)->delete();
            $oldanswers = PacketFaqAnswers::getAnswersByQuestionID($question['id'], $question['user_id']);
            if ($oldanswers->isEmpty()) {
                Packet::where('packet_id', '=', (int)$question['packet_id'])->increment('total_ques_unanswered');
                PacketFaq::where('id', '=', (int)$answer['ques_id'])->update(['status' => 'UNANSWERED']);
            }
            $msg = trans('admin/qanda.answer_delete');
            return redirect("/cp/contentfeedmanagement/question/{$post_id}/{$question['id']}")
                ->with('success', $msg);
        } catch (QuestionNotFoundException $e) {
            Log::error($e->getTraceAsString());
            return parent::getAdmin404Error();
        }
    }

    public function getAddElementSuccess($slug = null, $type = 'content_feed')
    {
        switch ($slug) {
            case 'course':
                if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                    return parent::getAdminError($this->theme_path);
                }
                break;
            default:
                if (!has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_POST)) {
                    return parent::getAdminError($this->theme_path);
                }
                break;
        }

        $packet = Packet::getPacket($slug);
        if ($slug == null || empty($packet)) {
            $msg = trans('admin/program.packet_slug_missing_error');
            return redirect('/cp/contentfeedmanagement/')
                ->with('error', $msg);
        }
        $packet = $packet[0];
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/program.manage_packets') => 'contentfeedmanagement/packets/' . $packet['feed_slug'] . '/' . $type,
            trans('admin/program.manage_elements') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/program.manage_elements');
        $this->layout->pageicon = 'fa fa-file-text';
        $this->layout->pagedescription = trans('admin/program.manage_elements');
        $this->layout->header = view('admin.theme.common.header');
        if ($type == 'course') {
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'program')
                ->with('submenu', 'course');
        } else {
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'program')
                ->with('submenu', 'contentfeed');
        }
        $msg = trans('admin/program.add_element_success');
        Session::flash('success', $msg);
        $this->layout->content = view('admin.theme.programs.addelementsuccess')
            ->with('packet', $packet)
            ->with('slug', $slug)
            ->with('type', $type);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getRequestedFeeds()
    {
        if (!has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_ACCESS_REQUEST)) {
            return $this->getAdminError();
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/program.manage_channel') => 'contentfeedmanagement',
            trans('admin/program.program') . ' Requests' => '',
        ];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/program.manage_request_program');
        $this->layout->pageicon = 'fa fa-rss';
        $this->layout->pagedescription = trans('admin/program.manage_request_program');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'program')
            ->with('submenu', 'contentfeed');
        $this->layout->content = view('admin.theme.programs.listfeedrequests');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getRequestedFeedsAjax()
    {
        if (!has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_ACCESS_REQUEST)) {
            return [
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ];
        }

        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '1') {
                $orderByArray = ['program_title' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '2') {
                $orderByArray = ['user_name' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '3') {
                $orderByArray = ['user_email' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '4') {
                $orderByArray = ['requested_at' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '5') {
                $orderByArray = ['status' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '6') {
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
        // $filter = strtolower($filter);
        if (!in_array($filter, ['GRANTED', 'DENIED', 'PENDING'])) {
            $filter = 'all';
        }

        $total_num_feed_requests = AccessRequest::accessRequestCount();
        $num_feed_requests_with_filter = AccessRequest::accessRequestCount($filter, $searchKey);
        $access_requests = AccessRequest::accessRequestInfo($filter, $start, $limit, $orderByArray, $searchKey);

        $dataArr = [];

        foreach ($access_requests as $request) {
            if ($request['status'] == 'PENDING') {
                $grant = '<a class="btn btn-circle show-tooltip grantrequest " href="' . URL::to('/cp/contentfeedmanagement/access/grant/' . $request['request_id']) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" title="" data-original-title="Grant Access"><i class="fa fa-check"></i></a>';
                $deny = '<a class="btn btn-circle show-tooltip denyrequest " href="' . URL::to('/cp/contentfeedmanagement/access/deny/' . $request['request_id']) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" title="" data-original-title="Deny access"><i class="fa fa-times"></i></a>';
            } elseif ($request['status'] == 'DENIED') {
                $grant = '';
                $deny = '<a class="btn btn-circle show-tooltip btn-danger" disabled="disabled" title="' . trans('admin/program.access_denied') . '"><i class="fa fa-times"></i></a>';
            } else {
                $grant = '<a class="btn btn-circle show-tooltip btn-success " disabled="disabled" title="' . trans('admin/program.access_granted') . '" ><i class="fa fa-check"></i></a>';
                $deny = '';
            }
            $updated_at = 'NA';
            if (isset($request['updated_at'])) {
                $updated_at = Timezone::convertFromUTC('@' . $request['updated_at'], Auth::user()->timezone, config('app.date_format'));
            }
            $temparr = [
                $request['program_title'],
                $request['user_name'],
                $request['user_email'],
                Timezone::convertFromUTC('@' . $request['created_at'], Auth::user()->timezone, config('app.date_format')),
                $updated_at,
                $request['status'],
                $grant . ' ' . $deny,
            ];
            $dataArr[] = $temparr;
        }
        $finaldata = [
            'recordsTotal' => $total_num_feed_requests,
            'recordsFiltered' => $num_feed_requests_with_filter,
            'data' => $dataArr,
        ];
        return response()->json($finaldata);
    }

    public function getAccess($action, $req_id)
    {
        if (!has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_ACCESS_REQUEST)) {
            return $this->getAdminError();
        }

        $start = Input::get('start', 0);
        $limit = Input::get('limit', 10);
        $filter = Input::get('filter', 'all');
        if ($action == 'grant') {
            $request_data = AccessRequest::grantAccess($req_id, "GRANTED");
            $program_context_data = $this->roleService->getContextDetails(Contexts::PROGRAM);
            $learner_role_data = $this->roleService->getRoleDetails(SystemRoles::LEARNER);

            $this->roleService->mapUserAndRole(
                $request_data["user_id"],
                $program_context_data["id"],
                $learner_role_data["id"],
                $request_data["program_id"]
            );

            event(
                new EntityEnrollmentByAdminUser(
                    $request_data["user_id"],
                    UserEntity::PROGRAM,
                    $request_data["program_id"]
                )
            );

            $totalRecords = AccessRequest::accessRequestCount($filter);
            if ($totalRecords <= $start) {
                $start -= $limit;
                if ($start < 0) {
                    $start = 0;
                }
            }
            $msg = trans('admin/program.content_feed_granted');
            return redirect('cp/contentfeedmanagement/requested-feeds?start=' . $start . '&limit=' . $limit . '&filter=' . $filter)
                ->with('success', $msg);
        }
        if ($action == 'deny') {
            AccessRequest::denyAccess($req_id);
            $totalRecords = AccessRequest::accessRequestCount($filter);
            if ($totalRecords <= $start) {
                $start -= $limit;
                if ($start < 0) {
                    $start = 0;
                }
            }
            $msg = trans('admin/program.content_feed_denied');
            return redirect('cp/contentfeedmanagement/requested-feeds?start=' . $start . '&limit=' . $limit . '&filter=' . $filter)
                ->with('success', $msg);
        }
    }

    public function getPacketRelations($program_type = null, $program_slug = null, $packet_slug = null)
    {
        $data = [];
        $programs = Program::getAllProgramByIDOrSlug($program_type, $program_slug)->first()->toArray();
        $packet_detail = Packet::getPacket($packet_slug);

        $start = Input::get('start', 0);
        $limit = Input::get('limit', 10);
        $search = Input::get('search', '');
        $order_by = Input::get('order_by', '3 desc');

        $rel_detail = '';
        $channel_user_groups = $channel_users = [];
        $rel_detail .= '</br><b>' . $packet_detail[0]['packet_title'] . '</b> (Channel: ' . $programs['program_title'] . ') is assigned to the following: <br><br>';
        if (isset($programs['relations']) && is_array($programs['relations']) && !empty($programs['relations'])) {
            /*Gets directly assigned user IDs*/
            if (isset($programs['relations']['active_user_feed_rel']) && !empty($programs['relations']['active_user_feed_rel'])) {
                foreach ($programs['relations']['active_user_feed_rel'] as $user) {
                    $channel_users[] = $user;
                }
            }
        }

        if (isset($programs['relations']) && is_array($programs['relations']) && !empty($programs['relations'])) {
            /*Gets directly assigned inactive/deleted user IDs*/
            if (isset($programs['relations']['inactive_user_feed_rel']) && !empty($programs['relations']['inactive_user_feed_rel'])) {
                foreach ($programs['relations']['inactive_user_feed_rel'] as $user) {
                    $channel_users[] = $user;
                }
            }
        }

        /*Gets user IDs assigned through user gropus*/
        if (isset($programs['relations']['active_usergroup_feed_rel']) && !empty($programs['relations']['active_usergroup_feed_rel'])) {
            foreach ($programs['relations']['active_usergroup_feed_rel'] as $usergroup) {
                $channel_user_groups[] = $usergroup;
            }
        }

        /*Gets user IDs assigned through inactive/deleted user gropus*/
        if (isset($programs['relations']['inactive_usergroup_feed_rel']) && !empty($programs['relations']['inactive_usergroup_feed_rel'])) {
            foreach ($programs['relations']['inactive_usergroup_feed_rel'] as $usergroup) {
                $channel_user_groups[] = $usergroup;
            }
        }

        $data['del_url'] = URL::to('/') . '/cp/contentfeedmanagement/delete-packet/'.$program_type.'/'.$program_slug.'/'.$packet_slug . '?start=' . $start . '&limit=' . $limit . '&search=' . $search . '&order_by=' . $order_by;
        /* Gets user IDs from group IDs*/
        $users_of_group = UserGroup::getUserIDsUsingGroupIDs($channel_user_groups);

        if (isset($users_of_group['users_ids']) && !empty($users_of_group['users_ids'])) {
            foreach ($users_of_group['users_ids'] as $each) {
                $channel_users[] = $each;
            }
        }

        $channel_users = array_unique($channel_users);

        $user_details = $this->user_service->getUsersDetailsUsingUserIDs(
            $channel_users,
            'DELETED',
            ['firstname' => 'asc'],
            ['firstname', 'lastname', 'status']
        )->toArray();

        $rel_detail .= "<div class='tabbable'>
                        <ul class='nav nav-tabs active-green'>";
        if (count($channel_users)) {
            $rel_detail .= "<li class='active'><a href='#relted-users' data-toggle='tab'><i class='fa fa-user'></i> <b>Users (" . count($channel_users) . ")</b></a></li>";
        }
        if (isset($users_of_group['user_group'])) {
            $rel_detail .= "<li><a href='#related-usergroups'data-toggle='tab'><i class='fa fa-group'></i> User Groups (" . count($users_of_group['user_group']) . ")</a></li>";
        }
        $rel_detail .= "</ul>
                        <div class='tab-content'>
                            <div class='tab-pane fade in active' id='relted-users'>
                                   <div style='height: 150px; overflow-y: auto;'>
                                   <table  border='1' style='width:100%'>";
        $i = 0;
        foreach ($user_details as $each) {
            if ($i % 3 == 0) {
                $rel_detail .= '<tr>';
            }
            $rel_detail .= '<td>' . $each['firstname'] . ' ';
            if ($each['lastname'] != '') {
                $rel_detail .= $each['lastname'];
                if ($each['status'] != 'ACTIVE') {
                    $rel_detail .= ' <b>(' . $each['status'] . ')</b></td>';
                }
            } else {
                if ($each['status'] != 'ACTIVE') {
                    $rel_detail .= ' <b>(' . $each['status'] . ')</b>';
                }
                $rel_detail .= '</td>';
            }

            $i++;
            if ($i % 3 == 0) {
                $rel_detail .= '</tr>';
            }
        }

        $rel_detail .= "</table></div>
                            </div>
                            <div class='tab-pane fade' id='related-usergroups'>";
        if (isset($users_of_group['user_group']) && !empty($users_of_group['user_group'])) {
            $rel_detail .= '<div style="height: 150px; overflow-y: auto;"><table  border="1" style="width:100%">';
            $i = 0;
            if ($i % 3 == 0) {
                $rel_detail .= '<tr>';
            }
            foreach ($users_of_group['user_group'] as $each) {
                $rel_detail .= '<td>' . $each . '</td>';
                $i++;
                if ($i % 3 == 0) {
                    $rel_detail .= '</tr>';
                }
            }

            $rel_detail .= "</table></div>";
        }
        $rel_detail .= "
                            </div>
                        </div>
                    </div> ";
        $data['rel_detail'] = $rel_detail;
        return $data;
    }

    //Manage Channel Question starts here @Author:Sahana
    public function getChannelQuestions()
    {
        if (!has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_QUESTION)) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/program.manage_channel_questions') => 'contentfeedmanagement/channel-questions',
            trans('admin/program.list_question') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/program.manage_channel_questions');
        $this->layout->pageicon = 'fa fa-question';
        $this->layout->pagedescription = trans('admin/program.manage_channel_questions');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'program')
            ->with('submenu', 'channelquestions');
        $this->layout->content = view('admin.theme.programs.channel_questions');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getChannelQuestionsAjax()
    {
        $permission_data_with_flag = $this->roleService->hasPermission(
            $this->request->user()->uid,
            ModuleEnum::CHANNEL,
            PermissionType::ADMIN,
            ChannelPermission::MANAGE_CHANNEL_QUESTION,
            Contexts::PROGRAM,
            null,
            true
        );

        if (!get_permission_flag($permission_data_with_flag)) {
            return response()->json(
                [
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    'data' => [],
                ]
            );
        }

        $filter_params = [];

        $permission_data = get_permission_data($permission_data_with_flag);
        if (!has_system_level_access($permission_data)) {
            $filter_params["in_program_ids"] = get_instance_ids($permission_data, Contexts::PROGRAM);
        }

        $total_count = $this->programservice->getQuestionsCount($filter_params);

        $filter_params["search_key"] = $this->request->input("search.value");
        $status = $this->request->input("filter", null);
        if ($status !== "ALL") {
            $filter_params["status"] = $status;
        }
        $filtered_count = $this->programservice->getQuestionsCount($filter_params);

        $order_by_columns = ["created_at", "question", null, null, "created_by_name"];
        $order_by_column_index = $this->request->input("order.0.column", 0);

        if (array_has($order_by_columns, $order_by_column_index)) {
            $filter_params["order_by"] = $order_by_columns[$order_by_column_index];
            $filter_params["order_by_dir"] = $this->request->input("order.0.dir", "desc");
        }

        $filter_params["start"] = $this->request->input("start", 0);
        $filter_params["limit"] = $this->request->input("length", 10);
        $filtered_data = $this->programservice->getQuestions($filter_params);
        $dataArr = [];
        foreach ($filtered_data as $key => $value) {
            $program = $value->program;
            if (!empty($program)) {
                $action = '<a class="btn btn-circle show-tooltip" target="_blank"
                        title="'.trans('admin/manageweb.action_portal_view').'"
                        href="'.URL::to('program/packets/'.$program->program_slug.'?from=channelquestions&tab_enabled=qanda').'">
                            <i class="fa fa-image"></i>
                    </a>
                    <a class="btn btn-circle show-tooltip" title="'.trans('admin/manageweb.action_view').'"
                        href="'.URL::to("cp/contentfeedmanagement/channel-question/{$program->program_id}/{$value->id}").'">
                            <i class="fa fa-eye"></i>
                    </a>';

                $delete = '<a class="btn btn-circle show-tooltip deletequestion" title="'.trans('admin/qanda.delete_question').'"
                    href="'.URL::to("cp/contentfeedmanagement/channel-question-delete/{$program->program_id}/{$value->id}").'">
                        <i class="fa fa-trash-o"></i>
                    </a>';

                $temparr = [
                    Timezone::convertFromUTC('@' . $value->created_at, Auth::user()->timezone, config('app.date_format')),
                    "<div>" . $value->question . "</div>",
                    "<div>" . $program->program_title . "</div>",
                    $value->created_by_name,
                    ucfirst(strtolower($value->status)),
                    $action . $delete ,
                ];
                $dataArr[] = $temparr;
            }
        }
        $finaldata = [
            'recordsTotal' => $total_count,
            'recordsFiltered' => $filtered_count,
            'data' => $dataArr,
        ];

        return response()->json($finaldata);
    }

    public function getChannelQuestion($program_id = null, $question_id = null)
    {
        try {
            $question = $this->programservice->getQuestion($program_id, $question_id);

            $has_permission = $this->roleService->hasPermission(
                $this->request->user()->uid,
                ModuleEnum::CHANNEL,
                PermissionType::ADMIN,
                ChannelPermission::MANAGE_CHANNEL_QUESTION,
                Contexts::PROGRAM,
                $program_id
            );

            if (!$has_permission) {
                return parent::getAdminError();
            }

            $answers = ChannelFaqAnswers::getAnswersByQuestionID($question['id'], 'admin');
            $crumbs = [
                trans('admin/dashboard.dashboard') => 'cp',
                trans('admin/program.manage_channel_questions') => 'contentfeedmanagement/channel-questions',
                trans('admin/program.view_question') => '',
            ];

            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = trans('admin/program.view_question');
            $this->layout->pageicon = 'fa fa-question';
            $this->layout->pagedescription = trans('admin/program.view_question');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'program')
                ->with('submenu', 'channelquestions');
            $this->layout->content = view('admin.theme.programs.channel_answers')
                ->with("program_id", $program_id)
                ->with('question', $question)
                ->with('answers', $answers);
            $this->layout->footer = view('admin.theme.common.footer');
        } catch (App\Exceptions\Program\QuestionNotFoundException $e) {
            Log::error($e->getTraceAsString());
            $msg = trans('admin/program.question_missing_error');
            return redirect('/cp/contentfeedmanagement/channel-questions')
                ->with('error', $msg);
        }
    }

    public function getChannelQuestionDelete($program_id = null, $question_id = null)
    {
        try {
            $question = $this->programservice->getQuestion($program_id, $question_id);

            $has_permission = $this->roleService->hasPermission(
                $this->request->user()->uid,
                ModuleEnum::CHANNEL,
                PermissionType::ADMIN,
                ChannelPermission::MANAGE_CHANNEL_QUESTION,
                Contexts::PROGRAM,
                $program_id
            );

            if (!$has_permission) {
                return parent::getAdminError();
            }

            ChannelFaq::getDelete($question->id);

            $msg = trans('admin/qanda.delete');
            return redirect('/cp/contentfeedmanagement/channel-questions/')
                ->with('success', $msg);
        } catch (App\Exceptions\Program\QuestionNotFoundException $e) {
            Log::error($e->getTraceAsString());
            $msg = trans('admin/program.question_missing_error');
            return redirect('/cp/contentfeedmanagement/channel-questions')
                ->with('error', $msg);
        }
    }

    public function postChannelAnswer($program_id = null, $question_id = null)
    {
        try {
            $question = $this->programservice->getQuestion($program_id, $question_id);

            $has_permission = $this->roleService->hasPermission(
                $this->request->user()->uid,
                ModuleEnum::CHANNEL,
                PermissionType::ADMIN,
                ChannelPermission::MANAGE_CHANNEL_QUESTION,
                Contexts::PROGRAM,
                $program_id
            );

            if (!$has_permission) {
                return parent::getAdminError();
            }

            $rules = [
                'answer' => 'Required|min:3',
            ];
            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                return redirect("cp/contentfeedmanagement/channel-question/{$program_id}/{$question_id}")
                    ->withInput()
                    ->withErrors($validation);
            } elseif ($validation->passes()) {
                $parent_id = 0;

                ChannelFaqAnswers::getInsert(
                    $question['id'],
                    Input::get('answer'),
                    $parent_id,
                    $question['user_id']
                );

                // Send a notification to the user.
                if (Config::get('app.notifications.packetsfaq.answered')) {
                    $notif_msg = trans(
                        'admin/notifications.answered',
                        ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname]
                    );
                    NotificationLog::getInsertNotification([$question['user_id']], 'channelfaq', $notif_msg);
                }

                $msg = trans('admin/qanda.answer_success');
                return redirect("/cp/contentfeedmanagement/channel-question/{$program_id}/{$question_id}")
                    ->with('success', $msg);
            }
        } catch (App\Exceptions\Program\QuestionNotFoundException $e) {
            Log::error($e->getTraceAsString());
            return parent::getAdmin404Error();
        }
    }

    public function getHideChannelAnswer($program_id = null, $question_id = null, $answer_id = null)
    {
        try {
            $this->programservice->getQuestion($program_id, $question_id);

            $has_permission = $this->roleService->hasPermission(
                $this->request->user()->uid,
                ModuleEnum::CHANNEL,
                PermissionType::ADMIN,
                ChannelPermission::MANAGE_CHANNEL_QUESTION,
                Contexts::PROGRAM,
                $program_id
            );

            if (!$has_permission) {
                return parent::getAdminError();
            }

            $answers = ChannelFaqAnswers::getAnswersByAnswerID($answer_id);

            if (!preg_match('/^[0-9]+$/', $answer_id) || empty($answers)) {
                $msg = trans('admin/qanda.answer_missing_error');
                return redirect("/cp/contentfeedmanagement/channel-questions")
                    ->with('error', $msg);
            }

            $type = Input::get('type');
            ChannelFaqAnswers::getHideAnswer($answer_id, $type);

            if ($type == 'hide') {
                $msg = trans('admin/qanda.hide_success');
            } else {
                $msg = trans('admin/qanda.un_hide_success');
            }

            return redirect("/cp/contentfeedmanagement/channel-question/{$program_id}/{$question_id}")
                ->with('success', $msg);
        } catch (App\Exceptions\Program\QuestionNotFoundException $e) {
            Log::error($e->getTraceAsString());
            return parent::getAdmin404Error();
        }
    }

    public function getDeleteChannelAnswer($program_id = null, $question_id = null, $answer_id = null)
    {
        try {
            $question = $this->programservice->getQuestion($program_id, $question_id);

            $has_permission = $this->roleService->hasPermission(
                $this->request->user()->uid,
                ModuleEnum::CHANNEL,
                PermissionType::ADMIN,
                ChannelPermission::MANAGE_CHANNEL_QUESTION,
                Contexts::PROGRAM,
                $program_id
            );

            if (!$has_permission) {
                return parent::getAdminError();
            }

            $answers = ChannelFaqAnswers::getAnswersByAnswerID($answer_id);

            if (!preg_match('/^[0-9]+$/', $answer_id) || empty($answers)) {
                $msg = trans('admin/qanda.answer_missing_error');
                return redirect('/cp/contentfeedmanagement/channel-questions')
                    ->with('error', $msg);
            }

            $answer = $answers[0];
            ChannelFaqAnswers::getDelete($answer_id);
            $oldanswers = ChannelFaqAnswers::getAdminAnswers($question['id'], $question['user_id']);

            if (empty($oldanswers)) {
                ChannelFaq::where('id', '=', (int)$answer['ques_id'])->update(['status' => 'UNANSWERED']);
            }

            $msg = trans('admin/qanda.answer_delete');

            return redirect("/cp/contentfeedmanagement/channel-question/{$program_id}/{$question_id}")
                ->with('success', $msg);
        } catch (App\Exceptions\Program\QuestionNotFoundException $e) {
            Log::error($e->getTraceAsString());
            return parent::getAdmin404Error();
        }
    }

    public function getListCourses()
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/program.course_list') => 'contentfeedmanagement/list-courses',
            trans('admin/program.list_courses') => '',
        ];

        if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::LIST_COURSE)) {
            return parent::getAdminError($this->theme_path);
        }
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/program.course_list');
        $this->layout->pageicon = 'fa fa-rss';
        $this->layout->pagedescription = trans('admin/program.course_list');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'course');
        $this->layout->content = view('admin.theme.programs.listcourse');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getBatchEnrolUser()
    {

        $relfilter = Input::get('relfilter', 'nonassigned');
        $from = Input::get('from', 'none');
        $relid = Input::get('relid', 'none');
        $viewmode = Input::get('view', 'iframe');

        if ($viewmode == 'iframe') {
            $this->layout->breadcrumbs = '';
            $this->layout->pagetitle = '';
            $this->layout->pageicon = '';
            $this->layout->pagedescription = '';
            $this->layout->header = '';
            $this->layout->sidebar = '';
            $this->layout->footer = '';
            $this->layout->content = view('admin.theme.programs.listenroluseriframe')
                ->with('relfilter', $relfilter)
                ->with('from', $from)
                ->with('relid', $relid);
        }
    }

    public function getUserListAjax()
    {

        $relfilter = Input::get('relfilter');
        $start = 0;
        $limit = 10;
        $viewmode = Input::get('view', 'desktop');
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

        $relfilter = Input::get('relfilter', 'nonassigned');
        $from = Input::get('from', 'course');
        $relid = Input::get('relid', 'none');
        if ($viewmode == 'iframe' && in_array($relfilter, ['assigned', 'nonassigned'])) {
            $relinfo = [$from => $relid];

            $totalRecords = User::getCourseUsersCount('', '', $relinfo);
            $filteredRecords = User::getCourseUsersCount($relfilter, $searchKey, $relinfo);
            $filtereddata = User::getCourseUsersWithPagination(
                $relfilter,
                $start,
                $limit,
                $orderByArray,
                $searchKey,
                $relinfo
            );
        }
        $totalRecords = $filteredRecords;
        $dataArr = [];
        foreach ($filtereddata as $key => $value) {
            $checkbox = '<input type="checkbox" value="' . $value['uid'] . '">';
            $temparr = [
                $checkbox,
                $value['username'],
                $value['firstname'] . ' ' . $value['lastname'],
                $value['email'],
                Timezone::convertFromUTC('@' . $value['created_at'], Auth::user()->timezone, config('app.date_format')),
                $value['status'],
            ];
            $dataArr[] = $temparr;
        }
        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr,
        ];
        return response()->json($finaldata);
    }

    public function getCourseListAjax()
    {
        $list_course = has_admin_permission(ModuleEnum::COURSE, CoursePermission::LIST_COURSE);
        $start = 0;
        $limit = 10;
        $viewmode = Input::get('view', 'desktop');
        if ($viewmode != 'iframe' && !$list_course) {
            $finaldata = [
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ];

            return response()->json($finaldata);
        }
        $search = Input::get('search');
        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['program_startdate' => 'desc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '1') {
                $orderByArray = ['program_title' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '2') {
                $orderByArray = ['program_startdate' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '3') {
                $orderByArray = ['program_enddate' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '4' || $order_by[0]['column'] == '8') {
                $orderByArray = ['status' => $order_by[0]['dir']];
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

        $filter = strtolower($filter);
        if (!in_array($filter, ['active', 'in-active'])) {
            $filter = 'all';
        } else {
            $filter = strtoupper($filter);
        }
        //start of advanced filters
        $visibility = Input::get('visibility', 'all');
        $sellability = Input::get('sellability', 'all');
        $feed_title = Input::get('feed_title');
        $shortname = Input::get('shortname');
        $created_date = Input::get('created_date');
        $updated_date = Input::get('updated_date');
        $description = Input::get('descriptions');
        $feed_tags = Input::get('feed_tags');
        $category = Input::get('category');
        $batch_name = Input::get('batch_name');
        $get_created_date = Input::get('get_created_date', '=');
        $get_updated_date = Input::get('get_updated_date', '=');
        $custom_field_name = [];
        $custom_field_value = [];
        $pgmCustomField = CustomFields::getUserActiveCustomField($program_type = 'course', $program_sub_type = 'single', $status = 'ACTIVE');
        if (!empty($pgmCustomField)) {
            foreach ($pgmCustomField as $key => $pgm_field) {
                $custom_field_name[] = $pgm_field["fieldname"];
                $custom_field_value[] = Input::get($pgm_field["fieldname"]);
            }
        }
        //end of advanced filters
        if ($searchKey != '') {
            $filter = 'all';
            $visibility = 'all';
            $sellability = 'all';
            $feed_title = '';
            $shortname = '';
            $created_date = '';
            $updated_date = '';
            $description = '';
            $feed_tags = '';
            $category = '';
            $batch_name = '';
            $custom_field_name = [];
            $custom_field_value = [];
        }
        $totalRecords = Program::getCourseCount();
        $filteredRecords = Program::getCourseCount($filter, $searchKey, '', '', '', $visibility, $sellability, $feed_title, $shortname, $created_date, $updated_date, $description, $feed_tags, $custom_field_name, $custom_field_value, $category, $batch_name, $get_created_date, $get_updated_date);
        $filtereddata = Program::getCourseWithTypeAndPagination($filter, $start, $limit, $orderByArray, $searchKey, '', '', '', $visibility, $sellability, $feed_title, $shortname, $created_date, $updated_date, $description, $feed_tags, $custom_field_name, $custom_field_value, $category, $batch_name, $get_created_date, $get_updated_date);

        $totalRecords = $filteredRecords;
        $dataArr = [];
        foreach ($filtereddata as $key => $value) {
            /*batch starts here*/
            $batch_list = [];
            $i = 0;
            $batchfiltereddata = Program::getCourseBatchList($value['program_id']);
            foreach ($batchfiltereddata as $batchkey => $batchvalue) {
                if (has_admin_permission(ModuleEnum::COURSE, CoursePermission::BATCH_ASSIGN_USER)) {
                    if (isset($batchvalue['relations']['active_user_feed_rel']) && !empty($batchvalue['relations']['active_user_feed_rel'])) {
                        $batch_users = '<a href="' . URL::to('/cp/contentfeedmanagement/batch-enrol-user?filter=ACTIVE&view=iframe&from=course&relid=' . $batchvalue['program_id']) . '" title="' . trans('admin/program.assign_user') . '" class="show-tooltip feedrel badge badge-success" data-key="' . $batchvalue['program_slug'] . '" data-info="user" data-text="Assign User(s) to <b>' . htmlentities('"' . $batchvalue['program_title'] . '"', ENT_QUOTES) . '</b>" data-json="' . json_encode($batchvalue['relations']['active_user_feed_rel']) . '">
                                ' . count($batchvalue['relations']['active_user_feed_rel']) . '</a>';
                    } else {
                        $batch_users = '<a href="' . URL::to('/cp/contentfeedmanagement/batch-enrol-user?view=iframe&from=course&relid=' . $batchvalue['program_id']) . '" title="' . trans('admin/program.assign_user') . '" class="show-tooltip feedrel badge badge-grey" data-key="' . $batchvalue['program_slug'] . '" data-info="user" data-text="Assign User(s) to <b>' . htmlentities('"' . $batchvalue['program_title'] . '"', ENT_QUOTES) . '</b>" data-json="">0</a>';
                    }
                } else {
                    $batch_users = '<a href="" title="' . trans('admin/program.no_permi_to_assign_user_batch') . '" class="show-tooltip badge badge-grey badge-info">NA</a>';
                }

                if (has_admin_permission(ModuleEnum::COURSE, CoursePermission::BATCH_ASSIGN_USER_GROUP)) {
                    if (isset($batchvalue['relations']['active_usergroup_feed_rel']) && !empty($batchvalue['relations']['active_usergroup_feed_rel'])) {
                        $batch_user_groups = '<a href="' . URL::to('/cp/usergroupmanagement/user-groups?filter=ACTIVE&view=iframe&from=course&relid=' . $batchvalue['program_id']) . '"
    title="' . trans('admin/program.assign_usergroup') . '" class="show-tooltip feedrel badge badge-success" data-key="' . $batchvalue['program_slug'] . '" data-info="usergroup"
    data-text="Assign User Group(s) to <b>' . htmlentities('"' . $batchvalue['program_title'] . '"', ENT_QUOTES) . '</b>"
    data-json="' . json_encode($batchvalue['relations']['active_usergroup_feed_rel']) . '">
                                ' . count($batchvalue['relations']['active_usergroup_feed_rel']) . '</a>';
                    } else {
                        $batch_user_groups = '<a href="' . URL::to('/cp/usergroupmanagement/user-groups?view=iframe&from=course&relid=' . $batchvalue['program_id']) . '" title="' . trans('admin/program.assign_usergroup') . '"
    class="show-tooltip feedrel badge badge-grey" data-key="' . $batchvalue['program_slug'] . '" data-info="usergroup"
    data-text="Assign User Group(s) to <b>' . htmlentities('"' . $batchvalue['program_title'] . '"', ENT_QUOTES) . '</b>" data-json="">0</a>';
                    }
                } else {
                    $batch_user_groups = "<a href='' onclick='return false;' title='" . trans('admin/program.no_perm_manage') . "' class='show-tooltip badge badge-grey badge-info'>NA</a>";
                }


                $batch_packets = Program::getPacketsCount($batchvalue['program_slug']);

                if (has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_BATCH_POST)) {
                    if ($batch_packets > 0) {
                        $batchpacketsHTML = "<a href='" . URL::to('/cp/contentfeedmanagement/packets/course/' . $batchvalue['program_slug']) . "' title='" . trans('admin/program.manage_posts') . "' class='show-tooltip badge badge-grey'>" . $batch_packets . '</a>';
                    } elseif ($batch_packets < 1) {
                        $batchpacketsHTML = "<a href='' onclick='return false;' title='" . trans('admin/program.copy_content_need_to_be_done') . "' class='show-tooltip badge badge-grey'>" . $batch_packets . '</a>';
                    }
                } else {
                    $batchpacketsHTML = "<a href='' onclick='return false;' title='" . trans('admin/program.mange_post_denied') . "' class='show-tooltip badge badge-grey badge-info'>NA</a>";
                }

                if ($batch_packets > 0) {
                    if (has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_BATCH_POST)) {
                        $batchpacketsHTML = "<a href='" . URL::to('/cp/contentfeedmanagement/packets/course/' . $batchvalue['program_slug']) . "' title='" . trans('admin/program.manage_posts') . "' class='show-tooltip badge badge-grey badge-info '>" . $batch_packets . '</a>';
                    } else {
                        $batchpacketsHTML = "<a href='' onclick='return false;' title='" . trans('admin/program.mange_post_denied') . "' class='show-tooltip badge badge-grey badge-info'>NA</a>";
                    }
                }

                $batch_list[$i]['batch_name'] = $batchvalue['program_title'];
                $batch_list[$i]['batch_startdate'] = Timezone::convertFromUTC('@' . $batchvalue['program_startdate'], Auth::user()->timezone, config('app.date_format'));
                $batch_list[$i]['batch_enddate'] = Timezone::convertFromUTC('@' . $batchvalue['program_enddate'], Auth::user()->timezone, config('app.date_format'));
                $batch_list[$i]['batch_posts'] = $batchpacketsHTML;
                $batch_list[$i]['batch_users'] = $batch_users;
                $batch_list[$i]['batch_usergroups'] = $batch_user_groups;
                $course_copy_icon_title = trans('admin/batch/copy_course_content.course_copy_icon_title');
                if ($batch_packets == 0) {
                    if (has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_BATCH_POST)) {
                        $batch_list[$i]['batch_actions'] = '<a style="cursor:pointer" class="open-course-copy-list btn btn-circle show-tooltip" data-tocopy=' . $batchvalue['program_slug'] . ' data-parentcourse=' . $value['program_slug'] . ' title="' . $course_copy_icon_title . '"><i class="fa fa-files-o" aria-hidden="true"></i></a>';
                    } else {
                        $batch_list[$i]['batch_actions'] = '<a class="btn btn-circle show-tooltip" title="' .trans('admin/program.copy_denied'). '"><i class="fa fa-files-o" aria-hidden="true"></i></a>';
                    }
                } else {
                    $batch_list[$i]['batch_actions'] = 'N/A';
                }

                $batch_info_service = [];
                $batch_info_service['sellable_id'] = $batchvalue['parent_id'];
                $batch_info_service['sellable_type'] = 'course';

                $data_slug = strtolower(substr($batchvalue['program_slug'], 7));

                $data_slug = explode("-c" . $batchvalue['program_id'], $data_slug);

                $batch_info = $this->priceService->getVerticalBySlug($batch_info_service, $data_slug[0]);

                $batch_list[$i]['user_group_enable'] = (isset($batch_info['batch_maximum_enrollment']) && isset($batch_info['batch_maximum_enrollment']) && $batch_info['batch_minimum_enrollment'] == "0" && $batch_info['batch_minimum_enrollment'] == "0") ? true : false;

                $i++;
            }
            /*batch ends here*/
            $shortname = (!empty($value['program_shortname'])) ? $value['program_shortname'] : 'NA';
            $packets = Program::getPacketsCount($value['program_slug']);
            $batch_count = Program::getCourseBatchCount($value['program_id']);
            $actions = '';
            if (has_admin_permission(ModuleEnum::COURSE, CoursePermission::VIEW_COURSE)) {
                $actions .= '<a class="btn btn-circle show-tooltip viewfeed" title="' . trans('admin/manageweb.action_view') . '" href="' . URL::to('/cp/contentfeedmanagement/course-details/' . $value['program_slug']) . '" ><i class="fa fa-eye"></i></a>';
            } else {
                $actions .= '<a class="btn btn-circle show-tooltip" title="' . trans('admin/program.view_restricted') . '"><i class="fa fa-eye"></i></a>';
            }

            if (has_admin_permission(ModuleEnum::COURSE, CoursePermission::EDIT_COURSE)) {
                $actions .= '<a class="btn btn-circle show-tooltip" title="' . trans('admin/manageweb.action_edit') . '" href="' . URL::to('/cp/contentfeedmanagement/edit-course/' . $value['program_slug']) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-edit"></i></a>';
            } else {
                $actions .= '<a class="btn btn-circle show-tooltip" title="' . trans('admin/program.edit_restricted') . '"><i class="fa fa-edit"></i></a>';
            }

            if (has_admin_permission(ModuleEnum::COURSE, CoursePermission::DELETE_COURSE)) {
                if ($batch_count > 0) {
                    $actions .= '<a class="btn btn-circle show-tooltip" title="' . trans('admin/program.course_not_delete_msg') . '"><i class="fa fa-trash-o"></i></a>';
                } else {
                    $actions .= '<a class="btn btn-circle show-tooltip deletefeed" title="' . trans('admin/manageweb.action_delete') . '" href="' . URL::to('/cp/contentfeedmanagement/delete-course/' . $value['program_slug']) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-trash-o"></i></a>';
                }
            } else {
                $actions .= '<a class="btn btn-circle show-tooltip" title="' . trans('admin/program.delete_restricted') . '"><i class="fa fa-trash-o"></i></a>';
            }

            if (has_admin_permission(ModuleEnum::COURSE, CoursePermission::VIEW_BATCH)) {
                if ($batch_count > 0) {
                    $batch = '<a title="' . trans('admin/program.batches') . '" class="badge badge-success details-control">' . $batch_count . '</a>';
                } else {
                    $batch = '<a title="' . trans('admin/program.no_batches') . '" class="badge badge-grey details-control">' . 0 . '</a>';
                }
            } else {
                $batch = '<a title="' . trans('admin/program.batch_access_restricted') . '" class="badge badge-grey badge-info">NA</a>';
            }

            if (has_admin_permission(ModuleEnum::COURSE, CoursePermission::ASSIGN_CATEGORY)) {
                $category = '<a href="' . URL::to('/cp/categorymanagement/categories?view=iframe&filter=ACTIVE') . '"
                    title="' . trans('admin/program.assign_cat') . '" class="show-tooltip feedrel badge badge-grey"
                    data-key="' . $value['program_slug'] . '" data-info="category" data-text="Assign Category to
                    <b>' . htmlentities('"' . $value['program_title'] . '"', ENT_QUOTES) . '</b>" data-json="">' . 0 . '</a>';
            } else {
                $category = '<a href="" onclick="return false;" title="' . trans('admin/program.category_permission_denied') . '" class="show-tooltip badge badge-grey badge-info" data-key="' . $value['program_slug'] . '" data-info="category" data-text="Assign Category to <b>' . htmlentities('"' . $value['program_title'] . '"', ENT_QUOTES) . '</b>" data-json="">NA</a>';
            }

            if (isset($value['program_categories']) && count($value['program_categories'])) {
                if (has_admin_permission(ModuleEnum::COURSE, CoursePermission::ASSIGN_CATEGORY)) {
                    $category = '<a href="' . URL::to('/cp/categorymanagement/categories?view=iframe&filter=ACTIVE') . '"
                        title="' . trans('admin/program.assign_cat') . '" class="show-tooltip feedrel badge badge-grey badge-success"
                        data-key="' . $value['program_slug'] . '" data-info="category" data-text="Assign Category to
                        <b>' . htmlentities('"' . $value['program_title'] . '"', ENT_QUOTES) . '</b>" data-json="' . json_encode($value['program_categories']) . '">' . count($value['program_categories']) . '</a>';
                } else {
                    $category = '<a href="" onclick="return false;" title="' . trans('admin/program.category_permission_denied') . '" class="show-tooltip badge badge-grey badge-info" data-key="' . $value['program_slug'] . '" data-info="category" data-text="Assign Category to <b>' . htmlentities('"' . $value['program_title'] . '"', ENT_QUOTES) . '</b>">NA</a>';
                }
            }

            $course_packets = Program::getPacketsCount($value['program_slug']);
            if (has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                $packetsHTML = "<a href='" . URL::to('/cp/contentfeedmanagement/packets/course/' . $value['program_slug']) . "' title='" . trans('admin/program.manage_posts') . "' class='show-tooltip badge badge-grey'>" . $course_packets . '</a>';
            } else {
                $packetsHTML = '<a href="" title="' . trans('admin/program.mange_post_denied') . '" class="show-tooltip badge badge-grey badge-info">NA</a>';
            }

            if ($course_packets) {
                if (has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST)) {
                    $packetsHTML = "<a href='" . URL::to('/cp/contentfeedmanagement/packets/course/' . $value['program_slug']) . "' title='" . trans('admin/program.manage_posts') . "' class='show-tooltip badge badge-grey badge-info '>" . $course_packets . '</a>';
                } else {
                    $packetsHTML = '<a href="" title="' . trans('admin/program.mange_post_denied') . '" class="show-tooltip badge badge-grey badge-info">NA</a>';
                }
            }

            $temparr = [
                'id' => $value['program_id'],
                'coursename' => $value['program_title'],
                'shortname' => $shortname,
                'startdate' => Timezone::convertFromUTC('@' . $value['program_startdate'], Auth::user()->timezone, config('app.date_format')),
                'enddate' => Timezone::convertFromUTC('@' . $value['program_enddate'], Auth::user()->timezone, config('app.date_format')),
                'category' => $category,
                'posts' => $packetsHTML,
                'batches' => $batch,
                'status' => ucfirst(strtolower($value['status'])),
                'actions' => $actions,
                'count' => $batch_count,
                'batch' => $batch_list,
                'program_sellability' => $value['program_sellability'],
            ];
            array_push($dataArr, $temparr);
        }
        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr,
        ];

        return json_encode($finaldata);
    }

    public function getAddCourse()
    {
        if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::VIEW_COURSE)) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/program.course_list') => 'contentfeedmanagement/list-courses',
            trans('admin/program.add_course') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/program.add_new_course');
        $this->layout->pageicon = 'fa fa-rss';
        $this->layout->pagedescription = trans('admin/program.add_new_course');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'course');
        $this->layout->content = view('admin.theme.programs.addcourse');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function postAddCourse()
    {
        if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::VIEW_COURSE)) {
            return parent::getAdminError($this->theme_path);
        }

        $program_shortname = strtolower(Input::get('program_shortname'));
        $programShortnameSlug = Input::get('feed_shortname_slug', '');
        Validator::extend('checkslug', function ($attribute, $value, $parameters) use ($programShortnameSlug) {
            $slug = 'course-' . $value;
            if (!empty($programShortnameSlug)) {
                $slug .= '-' . $programShortnameSlug;
            }
            $returnval = Program::where('program_slug', '=', $slug)
                ->where('program_type', '=', 'course')
                ->where('status', '!=', 'DELETED')
                ->get(['program_slug'])->toArray();
            if (empty($returnval)) {
                return true;
            }

            return false;
        });
        Validator::extend('checkslugregex', function ($attribute, $value, $parameters) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $value)) {
                return true;
            }
            return false;
        });
        Validator::extend('datecheck', function ($attribute, $value, $parameters) {
            $feed_start_date = Input::get('feed_start_date');
            $feed_end_date = Input::get('feed_end_date');
            if ((strtotime($feed_start_date) < strtotime($feed_end_date))) {
                return true;
            }
            return false;
        });
        Validator::extend('displaydatecheck', function ($attribute, $value, $parameters) {
            $feed_display_start_date = Input::get('feed_display_start_date');
            $feed_display_end_date = Input::get('feed_display_end_date');
            if ((strtotime($feed_display_start_date) < strtotime($feed_display_end_date))) {
                return true;
            }

            return false;
        });
        Validator::extend('displaystartdatecheck', function ($attribute, $value, $parameters) {
            $feed_start_date = Input::get('feed_start_date');
            $feed_display_start_date = Input::get('feed_display_start_date');
            if ((strtotime($feed_display_start_date) >= strtotime($feed_start_date))) {
                return true;
            }

            return false;
        });
        Validator::extend('displayenddatecheck', function ($attribute, $value, $parameters) {
            $feed_end_date = Input::get('feed_end_date');
            $feed_display_end_date = Input::get('feed_display_end_date');
            if ((strtotime($feed_display_end_date) <= strtotime($feed_end_date))) {
                return true;
            }

            return false;
        });

        $messages = [
            'displaystartdatecheck' => trans('admin/program.course_disp_start_date_great_than_start_date'),
            'displayenddatecheck' => trans('admin/program.course_disp_end_date_less_than_end_date'),
            'displaydatecheck' => trans('admin/program.course_disp_end_date_greater_than_disp_start_date'),
            'datecheck' => trans('admin/program.course_date_check'),
            'checkslug' => trans('admin/program.course_check_slug'),
            'checkslugregex' => trans('admin/program.course_check_slug_regex'),
            'feed_title.required' => trans('admin/program.course_field_required'),
            'min' => trans('admin/program.shortname'),
        ];
        $rules = [
            'feed_title' => 'Required',
            'feed_slug' => 'Required|checkslugregex|checkslug',
            'program_shortname' => 'min:3',
            'feed_start_date' => 'Required',
            'feed_end_date' => 'Required|datecheck',
            'feed_display_start_date' => 'Required|displaystartdatecheck',
            'feed_display_end_date' => 'Required|displaydatecheck|displayenddatecheck',
            'sellability' => 'Required|in:yes,no',
            'visibility' => 'Required|in:yes,no',
            'status' => 'Required|in:active,inactive',
        ];
        $productid = Program::uniqueProductId();
        $validation = Validator::make(Input::all(), $rules, $messages);
        if ($validation->fails()) {
            return redirect('cp/contentfeedmanagement/add-course')->withInput()
                ->withErrors($validation);
        } elseif ($validation->passes()) {
            $status = 'IN-ACTIVE';
            $feed_media_rel = 'contentfeed_media_rel';
            if (Input::get('status') == 'active') {
                $status = 'ACTIVE';
            }
            $mediaid = Input::get('banner', '');
            $program_keywords = explode(',', Input::get('feed_tags'));
            if (!$program_keywords) {
                $program_keywords = [];
            }

            if (!empty(Input::get('feed_shortname_slug', ''))) {
                $program_slug = 'course-' . Input::get('feed_slug') . '-' . Input::get('feed_shortname_slug', '');
            } else {
                $program_slug = 'course-' . Input::get('feed_slug');
            }
            $feedData = [
                'program_id' => $productid,
                'program_title' => trim(Input::get('feed_title')),
                'title_lower' => trim(strtolower(Input::get('feed_title'))),
                'program_shortname' => Input::get('program_shortname'),
                'program_slug' => $program_slug,
                'program_description' => Input::get('feed_description'),
                'program_startdate' => (int)Timezone::convertToUTC(Input::get('feed_start_date'), Auth::user()->timezone, 'U'),
                'program_enddate' => (int)Timezone::convertToUTC(Carbon::createFromFormat('d-m-Y', Input::get('feed_end_date'))->endOfDay(), Auth::user()->timezone, 'U'),
                'program_display_startdate' => (int)Timezone::convertToUTC(Input::get('feed_display_start_date'), Auth::user()->timezone, 'U'),
                'program_display_enddate' => (int)Timezone::convertToUTC(Carbon::createFromFormat('d-m-Y', Input::get('feed_display_end_date'))->endOfDay(), Auth::user()->timezone, 'U'),
                'program_duration' => '',
                'program_review' => 'no',
                'program_rating' => 'no',
                'program_visibility' => Input::get('visibility'),
                'program_sellability' => Input::get('sellability'),
                'program_keywords' => $program_keywords,
                'program_cover_media' => $mediaid,
                'program_type' => 'course',
                'program_sub_type' => 'single',
                'parent_id' => 0,
                'duration' => [
                    [
                        'label' => 'Forever',
                        'days' => 'forever',
                    ],
                ],
                'program_categories' => [],
                'last_activity' => time(),
                'status' => $status,
                'created_by' => Auth::user()->username,
                'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                'created_at' => time(),
                'updated_at' => time(),
                'program_access' => 'restricted_access',

            ];

            Program::insert($feedData);
            if (config('elastic.service')) {
                event(new ProgramAdded($productid));
            }
            //Insert custom fields
            $type = 'coursefields';

            $this->customSer->insertNewProgramCustomFields($productid, $type);

            Dam::removeMediaRelation($mediaid, ['contentfeed_media_rel'], (int)$productid);
            if ($mediaid) {
                Dam::updateDAMSRelation($mediaid, $feed_media_rel, (int)$productid);
            }
            $msg = trans('admin/program.course_create_success');
            if (Input::get('sellability') == 'yes') {
                return redirect('cp/contentfeedmanagement/edit-course/' . $program_slug)
                    ->with('pricing_action', 'add')
                    ->with('pricing', 'enabled')
                    ->with('success', $msg);
            } else {
                return redirect('cp/contentfeedmanagement/list-courses')
                    ->with('success', $msg);
            }
        }
    }

    public function postCourseFeed($action = null, $slug = null)
    {

        $assign_user = has_admin_permission(ModuleEnum::COURSE, CoursePermission::BATCH_ASSIGN_USER);
        $assign_usergroup = has_admin_permission(ModuleEnum::COURSE, CoursePermission::BATCH_ASSIGN_USER_GROUP);
        $assign_categories = has_admin_permission(ModuleEnum::COURSE, CoursePermission::ASSIGN_CATEGORY);

        if (!$assign_user && !$assign_usergroup && !$assign_categories) {
            return parent::getAdminError($this->theme_path);
        }

        $program = Program::getAllProgramByIDOrSlug('course', $slug);

        $ids = Input::get('ids');
        $empty = Input::get('empty');

        if ($ids) {
            $ids = explode(',', $ids);
        } else {
            $ids = [];
        }
        if (!$empty || !in_array($action, ['user', 'usergroup', 'category'])) {
            if (empty($program) || !$slug || !is_array($ids) || empty($ids)) {
                $msg = trans('admin/program.missing_program');
                return response()->json(['flag' => 'error', 'message' => $msg]);
            }
        }

        $program = $program->toArray();
        $program = $program[0];
        $batch_info = '';
        /*start of seats avaliability*/
        if ($action != 'category') {
            $data = [];
            $data['sellable_id'] = $program['parent_id'];
            $data['sellable_type'] = 'course';
            $data_slug = substr($program['program_slug'], 7);
            $data_slug = explode("-c" . $program['program_id'], $data_slug);
            $batch_info = $this->priceService->getVerticalBySlug($data, $data_slug[0]);
            $max_enroll = $batch_info['batch_maximum_enrollment'];
        }

        $role_info = $this->roleService->getRoleDetails(SystemRoles::LEARNER, ['context']);
        $context_info = $this->roleService->getContextDetails(Contexts::BATCH, false);
        $role_id = array_get($role_info, 'id', null);
        $context_id = array_get($context_info, 'id', null);
        $instance_id = array_get($program, 'program_id', null);
        $batch_start_date = array_get($batch_info, 'batch_start_date', null);
        $batch_start_date = !empty($batch_start_date) ? (int)Timezone::convertToUTC($batch_start_date, Auth::user()->timezone, 'U') : null;
        $batch_end_date = array_get($batch_info, 'batch_end_date', null);
        $batch_end_date = !empty($batch_end_date) ? Carbon::createFromFormat('d-m-Y', $batch_end_date, Auth::user()->timezone)->endOfDay()->timestamp : null;
        if ($action == 'user' && count($ids) > $max_enroll && $max_enroll > 0) {
            $msg = trans('admin/program.no_seats');

            return response()->json(['flag' => 'error', 'message' => $msg]);
        }

        if ($action == 'usergroup' && !empty($ids)) {
            $total_count = 0;
            foreach ($ids as $id) {
                $course_group = UserGroup::getActiveUserGroupsUsingID($id);
                if (isset($course_group[0]['relations']['active_user_usergroup_rel']) && !empty($course_group[0]['relations']['active_user_usergroup_rel'])) {
                    $group_count = count($course_group[0]['relations']['active_user_usergroup_rel']);
                    $total_count = $total_count + $group_count;
                    if ($max_enroll != 0 && $total_count > $max_enroll) {
                        $msg = trans('admin/program.no_seats');
                        return response()->json(['flag' => 'error', 'message' => $msg]);
                    }
                }
            }
        }

        /*end of seats avaliability*/

        if ($action == 'user') {
            $arrname = 'active_user_feed_rel';
            $msg = trans('admin/user.user_assigned');
        }
        if ($action == 'usergroup') {
            $arrname = 'active_usergroup_feed_rel';
            $msg = trans('admin/user.usergroup_assigned');
        }

        $key = (int)$program['program_id'];
        $deleted = [];
        if (isset($program['relations'])) {
            if ($action == 'user' && isset($program['relations']['active_user_feed_rel'])) {
                // Code to remove relations from user collection
                $deleted = array_diff($program['relations']['active_user_feed_rel'], $ids);
                $ids = array_diff($ids, $program['relations']['active_user_feed_rel']);
                foreach ($deleted as $value1) {
                    event(new EntityUnenrollmentByAdminUser($value1, UserEntity::BATCH, $instance_id));
                    $this->roleService->unmapUserAndRole($value1, $context_id, $instance_id);

                    User::removeUserRelation($value1, ['user_course_rel'], (int)$program['program_id']);
                    TransactionDetail::updateStatusByLevel('user', $value1, (int)$program['program_id'], ['status' => 'IN-ACTIVE']);
                    Program::removeFeedRelation($key, ['access_request_granted'], $value1);
                }
            } elseif ($action == 'usergroup' && isset($program['relations']['active_usergroup_feed_rel'])) {
                $deleted = array_diff($program['relations']['active_usergroup_feed_rel'], $ids);
                $ids = array_diff($ids, $program['relations']['active_usergroup_feed_rel']);
                foreach ($deleted as $value2) {
                    try {
                        $usergroup_info = $this->userGroupService->getUserGroupDetails($value2);
                        $usergroup_rel = $usergroup_info->relations;
                        $user_usergroup_rel_ids = array_get($usergroup_rel, 'active_user_usergroup_rel', '');
                    } catch (UserGroupNotFoundException $e) {
                        Log::info(trans('admin/user.usergroup_not_found', ['id' => $value2]));
                    }

                    if (!empty($user_usergroup_rel_ids)) {
                        foreach ($user_usergroup_rel_ids as $user_id) {
                            event(
                                new EntityUnenrollmentThroughUserGroup(
                                    $user_id,
                                    UserEntity::BATCH,
                                    $instance_id,
                                    $value2
                                )
                            );

                            $this->roleService->unmapUserAndRole((int)$user_id, $context_id, $instance_id);
                        }
                    }

                    UserGroup::removeUserGroupRelation($value2, ['usergroup_course_rel'], (int)$program['program_id']);
                    TransactionDetail::updateStatusByLevel('usergroup', $value2, (int)$program['program_id'], ['status' => 'IN-ACTIVE'], '', (int)$program['program_id']);
                }
            }
        }
        if ($action == 'category' && isset($program['program_categories'])) {
            // Code to remove relations from category collection
            $deleted = array_diff($program['program_categories'], $ids);
            $ids = array_diff($ids, $program['program_categories']);
            foreach ($deleted as $value3) {
                Category::removeCategoryRelation($value3, ['assigned_courses'], (int)$program['program_id']);
            }
        }
        $notify_user_ids = $ids = array_values($ids); //  This is to reset the index. [Problem: when using array_diff, the keys gets altered which converts normal array to associative array.]
        $deleted = array_values($deleted); //  This is to reset the index. [Problem: when using array_diff, the keys gets altered which converts normal array to associative array.]
        $notify_log_flg = true;
        $notify_user_ids_ary = [];
        foreach ($ids as &$value) {
            $value = (int)$value;
            $now = time();

            if ($action == 'user') {
                User::addUserRelation($value, ['user_course_rel'], $program['program_id']);

                event(
                    new EntityEnrollmentByAdminUser(
                        $value,
                        UserEntity::BATCH,
                        $instance_id,
                        $batch_start_date,
                        $batch_end_date
                    )
                );

                $this->roleService->mapUserAndRole(
                    $value,
                    $context_id,
                    $role_id,
                    $instance_id
                );

                $trans_id = Transaction::uniqueTransactionId();
                $userdetails = User::getUserDetailsByID($value)->toArray();
                $email = '';
                if (isset($userdetails['email'])) {
                    $email = $userdetails['email'];
                }
                $transaction = [
                    'DAYOW' => Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'l'),
                    'DOM' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'j'),
                    'DOW' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'w'),
                    'DOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'z'),
                    'MOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'n'),
                    'WOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'W'),
                    'YEAR' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'Y'),
                    'trans_level' => 'user',
                    'id' => $value,
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
                    'id' => $value,
                    'trans_id' => (int)$trans_id,
                    'program_id' => $program['program_id'],
                    'program_slug' => $program['program_slug'],
                    'type' => 'course',
                    'program_title' => $program['program_title'],
                    'duration' => [
                        'label' => 'Forever',
                        'days' => 'forever',
                    ],
                    'start_date' => $program['program_startdate'],
                    'end_date' => $program['program_enddate'],
                    'created_at' => time(),
                    'updated_at' => time(),
                    'status' => 'COMPLETE',
                ];

                Transaction::insert($transaction);
                TransactionDetail::insert($transaction_details);

                if (Config::get('app.notifications.contentfeed.feedadd') && $notify_log_flg) {
                    $notify_log_flg = false;
                    $notif_msg = trans('admin/notifications.feedadd', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'feed' => $program['program_title']]);
                    NotificationLog::getInsertNotification($notify_user_ids, trans('admin/program.program'), $notif_msg);
                }
                // Send Mail Notifications to the user
                if (Config::get('email.notifications.contentfeed.feedadd')) {
                    $email = Email::getEmail('feed_assignment')->first()->toArray();
                    if (isset($email['body']) && isset($email['subject'])) {
                        $body = str_replace('[:adminname:]', Auth::user()->firstname . ' ' . Auth::user()->lastname, $email['body']);
                        $body = str_replace('[:feed:]', $program['program_title'], $body);
                        Common::sendMailHtml($body, $email['subject'], $email);
                    }
                }
            } elseif ($action == 'usergroup') {
                try {
                    $usergroup_info = $this->userGroupService->getUserGroupDetails($value);
                    $usergroup_rel = $usergroup_info->relations;
                    $user_usergroup_rel_ids = array_get($usergroup_rel, 'active_user_usergroup_rel', '');
                } catch (UserGroupNotFoundException $e) {
                    Log::info(trans('admin/user.usergroup_not_found', ['id' =>  $value]));
                }

                if (!empty($user_usergroup_rel_ids)) {
                    foreach ($user_usergroup_rel_ids as $user_id) {
                        event(
                            new EntityEnrollmentThroughUserGroup(
                                $user_id,
                                UserEntity::BATCH,
                                $instance_id,
                                $value,
                                $batch_start_date,
                                $batch_end_date
                            )
                        );

                        $this->roleService->mapUserAndRole((int)$user_id, $context_id, $role_id, $instance_id);
                    }
                }

                UserGroup::addUserGroupRelation($value, ['usergroup_course_rel'], $program['program_id']);
                $trans_id = Transaction::uniqueTransactionId();
                $transaction = [
                    'DAYOW' => Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'l'),
                    'DOM' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'j'),
                    'DOW' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'w'),
                    'DOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'z'),
                    'MOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'n'),
                    'WOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'W'),
                    'YEAR' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'Y'),
                    'trans_level' => 'usergroup',
                    'id' => $value,
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
                    'id' => $value,
                    'trans_id' => (int)$trans_id,
                    'program_id' => $program['program_id'],
                    'program_slug' => $program['program_slug'],
                    'type' => 'course',
                    'program_title' => $program['program_title'],
                    'duration' => [
                        'label' => 'Forever',
                        'days' => 'forever',
                    ],
                    'start_date' => '',
                    'end_date' => '',
                    'created_at' => time(),
                    'updated_at' => time(),
                    'status' => 'COMPLETE',
                ];

                // Add record to user transaction table
                Transaction::insert($transaction);
                TransactionDetail::insert($transaction_details);
                if (Config::get('app.notifications.contentfeed.feedadd')) {
                    $usergroup_data = UserGroup::getUserGroupsUsingID((int)$value);
                    foreach ($usergroup_data as $usergroup) {
                        if (isset($usergroup['relations']['active_user_usergroup_rel'])) {
                            $notify_user_ids_ary = array_merge($notify_user_ids_ary, $usergroup['relations']['active_user_usergroup_rel']);
                        }
                    }
                }

                // Send Mail Notifications to the user
                if (Config::get('email.notifications.contentfeed.feedadd')) {
                    $usergroup_data = UserGroup::getUserGroupsUsingID((int)$value);
                    $emailtemplate = Email::getEmail('feed_assignment')->first()->toArray();
                    foreach ($usergroup_data as $usergroup) {
                        if (isset($usergroup['relations']['active_user_usergroup_rel'])) {
                            foreach ($usergroup['relations']['active_user_usergroup_rel'] as $user) {
                                $userdetails = User::getUserDetailsByID($user)->toArray();
                                if (isset($emailtemplate['body']) && isset($emailtemplate['subject'])) {
                                    $body = str_replace('[:adminname:]', Auth::user()->firstname . ' ' . Auth::user()->lastname, $emailtemplate['body']);
                                    $body = str_replace('[:feed:]', $program['program_title'], $body);
                                    Common::sendMailHtml($body, $emailtemplate['subject'], $userdetails['email']);
                                }
                            }
                        }
                    }
                }
            } elseif ($action == 'category') {
                Category::updateCategoryRelation($value, 'assigned_courses', $program['program_id']);
            }
        }
        if (!empty($notify_user_ids_ary)) {
            $notif_msg = trans('admin/notifications.feedadd', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'feed' => $program['program_title']]);
            NotificationLog::getInsertNotification($notify_user_ids_ary, trans('admin/program.program'), $notif_msg);
            $notify_user_ids_ary = [];
        }
        if ($action == 'category') {
            if (!empty($ids)) {
                Program::updateFeedCategories($key, $ids);
            }
            if (!empty($deleted)) {
                Program::removeFeedCategories($key, $deleted);
            }
            $msg = trans('admin/category.category');

            if ((count($ids) > 1) || (count($deleted) > 1)) {
                $msg = trans('admin/category.categories');
            }
            $msg = $msg . trans('admin/category.assigned_success');
        } else {
            if (!empty($ids)) {
                Program::updateFeedRelation($key, $arrname, $ids);
            }
            $notify_user_ids = $deleted;
            $temp_flag = true;
            $notify_user_ids_ary = [];
            foreach ($deleted as $value) {
                $value = (int)$value;
                if ($action == 'user') {
                    if (Config::get('app.notifications.contentfeed.feedrevoke') && $temp_flag) {
                        $temp_flag = false;
                        $notif_msg = trans('admin/notifications.feedrevoke', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'feed' => $program['program_title'], 'channel' => config('app.cahnnel_name')]);
                        NotificationLog::getInsertNotification($notify_user_ids, trans('admin/program.program'), $notif_msg);
                    }
                } elseif ($action == 'usergroup') {
                    if (Config::get('app.notifications.contentfeed.feedrevoke')) {
                        $usergroup_data = UserGroup::getUserGroupsUsingID((int)$value);
                        foreach ($usergroup_data as $usergroup) {
                            if (isset($usergroup['relations']['active_user_usergroup_rel'])) {
                                $notify_user_ids_ary = array_merge($notify_user_ids_ary, $usergroup['relations']['active_user_usergroup_rel']);
                            }
                        }
                    }
                }
            }
            if (!empty($notify_user_ids_ary)) {
                $notif_msg = trans('admin/notifications.feedrevoke', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'feed' => $program['program_title'], 'channel' => config('app.cahnnel_name')]);
                NotificationLog::getInsertNotification($notify_user_ids_ary, trans('admin/program.program'), $notif_msg);
                $notify_user_ids_ary = [];
            }
            Program::where('program_id', $key)->pull('relations.' . $arrname, $deleted, true);
        }
        //Update Enrolement
        $this->updateEnrollCount($program['program_slug']);
        //end
        if ($action == 'user' || $action == 'usergroup') {
            if (config('elastic.service')) {
                event(new UsersAssigned($program['program_id']));
            }
        }
        return response()->json(['flag' => 'success', 'message' => $msg]);
    }

    public function getCourseDetails($slug = '')
    {
        if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::VIEW_COURSE)) {
            return parent::getAdminError($this->theme_path);
        }
        $programs = Program::getAllProgramByIDOrSlug('course', $slug);
        if ($slug == null || empty($programs)) {
            $msg = trans('admin/program.slug_missing_error');

            return redirect('/cp/contentfeedmanagement/list-courses')
                ->with('error', $msg);
        }
        $programs = $programs->toArray();
        $programs = $programs[0];

        $programs['program_startdate'] = Timezone::convertFromUTC('@' . $programs['program_startdate'], Auth::user()->timezone, config('app.date_format'));
        $programs['program_enddate'] = Timezone::convertFromUTC('@' . $programs['program_enddate'], Auth::user()->timezone, config('app.date_format'));
        $programs['program_display_startdate'] = Timezone::convertFromUTC('@' . $programs['program_display_startdate'], Auth::user()->timezone, config('app.date_format'));
        $programs['program_display_enddate'] = Timezone::convertFromUTC('@' . $programs['program_display_enddate'], Auth::user()->timezone, config('app.date_format'));

        $media = '';
        if (isset($programs['program_cover_media'])) {
            $media = Dam::getDAMSAssetsUsingID($programs['program_cover_media']);
            if (!empty($media)) {
                $media = $media[0];
            }
        }
        $uniconf_id = Config::get('app.uniconf_id');
        $kaltura_url = Config::get('app.kaltura_url');
        $partnerId = Config::get('app.partnerId');
        $kaltura = $kaltura_url . 'index.php/kwidget/cache_st/1389590657/wid/_' . $partnerId . '/uiconf_id/' . $uniconf_id . '/entry_id/';

        return view('admin.theme.programs.feeddetails')->with('feed', $programs)->with('media', $media)->with('kaltura', $kaltura);
    }

    public function getDeleteCourse($slug = '')
    {
        if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::DELETE_COURSE)) {
            return parent::getAdminError($this->theme_path);
        }

        $programs = Program::getAllProgramByIDOrSlug('course', $slug);
        $programs = $programs->toArray();

        $start = Input::get('start', 0);
        $limit = Input::get('limit', 10);
        $filter = Input::get('filter', 'all');
        $search = Input::get('search', '');
        $order_by = Input::get('order_by', '3 desc');

        if ($slug == null || empty($programs)) {
            $msg = trans('admin/program.slug_missing_error');

            return redirect('/cp/contentfeedmanagement/list-courses?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $search . '&order_by=' . $order_by)
                ->with('error', $msg);
        }
        $programs = $programs[0];

        // Unlink all the related categories
        if (isset($programs['program_categories']) && is_array($programs['program_categories'])) {
            foreach ($programs['program_categories'] as $category) {
                Category::removeCategoryRelation($category, ['assigned_courses'], (int)$programs['program_id']);
            }
        }

        // Get all packets and remove the dams relations

        // Unlink DAMS relation
        if (isset($programs['program_cover_media']) && $programs['program_cover_media']) {
            Dam::removeMediaRelation($programs['program_cover_media'], ['contentfeed_media_rel'], (int)$programs['program_id']);
        }
        $program_id = Program::deleteCourse($slug);
        if (config('elastic.service')) {
            event(new ProgramRemoved($program_id));
        }
        $totalRecords = Program::getCourseCount($filter);
        if ($totalRecords <= $start) {
            $start -= $limit;
            if ($start < 0) {
                $start = 0;
            }
        }

        $msg = trans('admin/program.course_delete_success');
        return redirect('cp/contentfeedmanagement/list-courses?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $search . '&order_by=' . $order_by)
            ->with('success', $msg);
    }

    public function postEditCourse($slug)
    {
        if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::EDIT_COURSE)) {
            return parent::getAdminError($this->theme_path);
        }
        $programs = Program::getAllProgramByIDOrSlug('course', $slug);

        if ($slug == null || $programs->isEmpty()) {
            $msg = trans('admin/program.slug_missing_error');
            return redirect('/cp/contentfeedmanagement/list-courses')
                ->with('error', $msg);
        }

        $program = $programs->toArray();
        $batch_count = Program::getCourseBatchCount($program[0]['program_id']);

        Input::flash();
        $program_shortname = strtolower(Input::get('program_shortname'));

        $old_slug = $program[0]['program_slug'];
        $old_shortname = isset($program[0]['program_shortname']) ? $program[0]['program_shortname'] : []; //Input::get('old_shortname');
        $feed_title = trim(strtolower(Input::get('feed_title')));

        $programShortnameSlug = Input::get('feed_shortname_slug', '');
        Validator::extend('checkslug', function ($attribute, $value, $parameters) use ($programShortnameSlug, $old_slug, $program_shortname, $old_shortname) {
            if ($old_slug == $value && $old_shortname == $program_shortname) {
                return true;
            }
            $slug = 'course-' . $value;
            if (!empty($programShortnameSlug)) {
                $slug .= '-' . $programShortnameSlug;
            }
            $returnval = Program::where('program_slug', '=', $slug)
                ->where('program_type', '=', 'course')
                ->where('status', '!=', 'DELETED')
                ->whereNotIn('_id', $parameters)->get()->toArray();
            if (empty($returnval)) {
                return true;
            }
            return false;
        });

        Validator::extend('statuscheck', function ($attribute, $value, $parameters) {
            $status = Input::get('status');
            if ($status == 'active') {
                return true;
            }
            if ($status == 'inactive' && $parameters[0] > 0) {
                return false;
            }
            return true;
        });
        Validator::extend('checkslugregex', function ($attribute, $value, $parameters) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $value)) {
                return true;
            }

            return false;
        });
        Validator::extend('datecheck', function ($attribute, $value, $parameters) {
            $feed_start_date = Input::get('feed_start_date');
            $feed_end_date = Input::get('feed_end_date');
            if ((strtotime($feed_start_date) < strtotime($feed_end_date))) {
                return true;
            }

            return false;
        });
        Validator::extend('displaydatecheck', function ($attribute, $value, $parameters) {
            $feed_display_start_date = Input::get('feed_display_start_date');
            $feed_display_end_date = Input::get('feed_display_end_date');
            if ((strtotime($feed_display_start_date) < strtotime($feed_display_end_date))) {
                return true;
            }

            return false;
        });
        Validator::extend('displaystartdatecheck', function ($attribute, $value, $parameters) {
            $feed_start_date = Input::get('feed_start_date');
            $feed_display_start_date = Input::get('feed_display_start_date');
            if ((strtotime($feed_display_start_date) >= strtotime($feed_start_date))) {
                return true;
            }

            return false;
        });
        Validator::extend('displayenddatecheck', function ($attribute, $value, $parameters) {
            $feed_end_date = Input::get('feed_end_date');
            $feed_display_end_date = Input::get('feed_display_end_date');
            if ((strtotime($feed_display_end_date) <= strtotime($feed_end_date))) {
                return true;
            }

            return false;
        });

        $messages = [
            'displaystartdatecheck' => trans('admin/program.course_disp_start_date_great_than_start_date'),
            'displayenddatecheck' => trans('admin/program.course_disp_end_date_less_than_end_date'),
            'displaydatecheck' => trans('admin/program.course_disp_end_date_greater_than_disp_start_date'),
            'datecheck' => trans('admin/program.course_date_check'),
            'checkslug' => trans('admin/program.course_check_slug'),
            'checkslugregex' => trans('admin/program.course_check_slug_regex'),
            'statuscheck' => trans('admin/program.cannot_deactivate_course'),
            'min' => trans('admin/program.shortname'),
            'feed_title.required' => trans('admin/program.course_field_required'),
        ];

        $relations = '';

        if (isset($programs[0]->relations['active_user_feed_rel']) && !empty($programs[0]->relations['active_user_feed_rel'])) {
            $rel = $programs[0]->relations['active_user_feed_rel'];
            $relations = implode(',', $rel);
            $messages['status.checkstatus'] = trans('admin/program.cannot_deactivate_program');
        }

        $rules = [
            'feed_title' => 'Required',
            'program_shortname' => 'min:3',
            'feed_slug' => 'Required|checkslugregex|checkslug:' . $programs[0]->_id,
            'feed_start_date' => 'Required',
            'feed_end_date' => 'Required|datecheck',
            'feed_display_start_date' => 'Required|displaystartdatecheck',
            'feed_display_end_date' => 'Required|displaydatecheck|displayenddatecheck',
            'visibility' => 'Required|in:yes,no',
            'status' => 'Required|statuscheck:' . $batch_count . '',
        ];
        $validation = Validator::make(Input::all(), $rules, $messages);
        if ($validation->fails()) {
            return redirect('cp/contentfeedmanagement/edit-course/' . $slug)->withInput()
                ->withErrors($validation);
        } elseif ($validation->passes()) {
            $programdata = $programs->first()->toArray();

            $status = 'IN-ACTIVE';
            $feed_media_rel = 'contentfeed_media_rel';
            if (Input::get('status') == 'active') {
                $status = 'ACTIVE';
            }
            $mediaid = Input::get('banner', '');
            $program_keywords = explode(',', Input::get('feed_tags'));
            if (!$program_keywords) {
                $program_keywords = [];
            }
            $feed_slug = Input::get('feed_slug');
            if ($old_slug != $feed_slug || $old_shortname != $program_shortname) {
                $new_slug = 'course-' . $feed_slug;
                if (!empty($programShortnameSlug)) {
                    $new_slug .= '-' . $programShortnameSlug;
                }
            } else {
                $new_slug = $old_slug;
            }
            $feedData = [
                'program_title' => trim(Input::get('feed_title')),
                'title_lower' => trim(strtolower(Input::get('feed_title'))),
                'program_shortname' => Input::get('program_shortname'),
                'program_slug' => $new_slug,
                'program_description' => Input::get('feed_description'),
                'program_startdate' => (int)Timezone::convertToUTC(Input::get('feed_start_date'), Auth::user()->timezone, 'U'),
                'program_enddate' => (int)Timezone::convertToUTC(Carbon::createFromFormat('d-m-Y', Input::get('feed_end_date'))->endOfDay(), Auth::user()->timezone, 'U'),
                'program_display_startdate' => (int)Timezone::convertToUTC(Input::get('feed_display_start_date'), Auth::user()->timezone, 'U'),
                'program_display_enddate' => (int)Timezone::convertToUTC(Carbon::createFromFormat('d-m-Y', Input::get('feed_display_end_date'))->endOfDay(), Auth::user()->timezone, 'U'),
                'program_duration' => '',
                'program_visibility' => Input::get('visibility'),
                'program_keywords' => $program_keywords,
                'program_cover_media' => $mediaid,
                'program_type' => 'course',
                'program_sub_type' => 'single',
                'last_activity' => time(),
                'status' => $status,
                'updated_at' => time(),
                'updated_by' => Auth::user()->username,
            ];

            Dam::removeMediaRelation($programs[0]->program_cover_media, [$feed_media_rel], (int)$programs[0]->program_id);
            if ($mediaid) {
                Dam::updateDAMSRelation($mediaid, $feed_media_rel, (int)$programs[0]->program_id);
            }
            Program::where('program_slug', '=', $slug)->where('program_type', '=', 'course')->where('status', '!=', 'DELETED')->update($feedData);
            Packet::where('feed_slug', '=', $slug)->update(['feed_slug' => $new_slug]);
            TransactionDetail::where('program_slug', '=', $slug)->where('type', '=', 'course')->update(['program_slug' => $new_slug, 'program_sub_type' => 'single']);
            $msg = trans('admin/program.course_edit_success');
            $slug_changed = $old_slug != $new_slug;
            if (config('elastic.service')) {
                event(new ProgramUpdated($program[0]['program_id'], $slug_changed));
            }
            return redirect('cp/contentfeedmanagement/list-courses')
                ->with('success', $msg);
        }
    }

    public function getEditCourse($slug)
    {
        if (!has_admin_permission(ModuleEnum::COURSE, CoursePermission::EDIT_COURSE)) {
            return parent::getAdminError($this->theme_path);
        }

        $programs = Program::getAllProgramByIDOrSlug('course', $slug);
        if ($slug == null || $programs->isEmpty()) {
            $msg = trans('admin/program.slug_missing_error');
            return redirect('/cp/contentfeedmanagement/list-courses')
                ->with('error', $msg);
        }
        $program = $programs->toArray();

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/program.course_list') => 'contentfeedmanagement/list-courses',
            trans('admin/program.edit_course') => '',
        ];
        $pri_ser_data = $this->setPricingService($programs);
        $tabs = $this->setTabService($programs);

        $courseCF = $this->customSer->getFormCustomFields('coursefields');

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/program.edit_course');
        $this->layout->pageicon = 'fa fa-rss';
        $this->layout->pagedescription = trans('admin/program.edit_course');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'course');
        $this->layout->content = view('admin.theme.programs.editcourse')
            ->with('programs', $programs)
            ->with('pri_ser_info', $pri_ser_data)
            ->with('tabs', $tabs)
            ->with('courseCF', $courseCF)
            ->with('slug', $slug);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /*Function for Channel Export - Direct Users mapping */
    public function getChannelUserExport($program_type, $program_sub_type, $status = 'ALL')
    {
        $user_id = $this->request->user()->uid;
        $list_channel_permission_info_with_flag = [];
        $list_channel_permission_info_with_flag = $this->roleService->hasPermission(
            $user_id,
            ModuleEnum::CHANNEL,
            PermissionType::ADMIN,
            ChannelPermission::LIST_CHANNEL,
            Contexts::PROGRAM,
            null,
            true
        );
        $filter_params = [];
        $list_permission_data = get_permission_data($list_channel_permission_info_with_flag);
        $list_channel_filter_params = has_system_level_access($list_permission_data)?
                                         [] : ["in_ids" => get_instance_ids($list_permission_data, Contexts::PROGRAM)];
        $filter_params = array_merge($filter_params, $list_channel_filter_params);
        $channels = Program::getChannel($status, $program_type, $program_sub_type, $filter_params);
        $DirectOrGroup = null;
        $export_user_list = [];
        $userAttributes = config('app.ChannelExportUserFields');
        $user_time = Auth::user()->timezone;

        array_walk_recursive($channels, function (&$item, $key) {
            (is_string($item)) ? $item = htmlspecialchars_decode(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($item))), ENT_QUOTES) : '';
        });
        foreach ($channels as $key => &$val) {
            unset($val['_id']);
            //taking user list
            if (isset($channels[$key]['relations']) && !empty($channels[$key]['relations']['active_user_feed_rel']) && array_key_exists('active_user_feed_rel', $channels[$key]['relations'])) {
                $userList = $channels[$key]['relations']['active_user_feed_rel'];
                $user_details = User::whereIn('uid', $userList)->get($userAttributes)->toArray();
                $usersFullName = null;
                foreach ($user_details as $each_user) {
                    $user['fullname'] = $each_user['firstname'] . ' ' . $each_user['lastname'];
                    $user['username'] = (isset($each_user['username'])) ? $each_user['username'] : 'NA';
                    $user['email'] = (isset($each_user['email'])) ? $each_user['email'] : 'NA';
                    $export_user_list[$key]['users'][] = $user;
                };
                $export_user_list[$key]['program_title'] = $channels[$key]['program_title'];
                $export_user_list[$key]['shortname'] = (isset($channels[$key]['program_shortname'])) ? $channels[$key]['program_shortname'] : " ";
                $export_user_list[$key]['status'] = $channels[$key]['status'];
                $export_user_list[$key]['updated_by'] = (isset($val['updated_by_name'])) ? $val['updated_by_name'] : $val['created_by_name'];
                $export_user_list[$key]['updated_at'] = (isset($val['updated_at'])) ? Timezone::convertFromUTC($val['updated_at'], $user_time, config('app.date_format')) : ' ';
            } else {
                $export_user_list[$key]['program_title'] = $channels[$key]['program_title'];
                $export_user_list[$key]['shortname'] = (isset($channels[$key]['program_shortname'])) ? $channels[$key]['program_shortname'] : " ";
                $export_user_list[$key]['status'] = $channels[$key]['status'];
                $export_user_list[$key]['users'] = " ";
                $export_user_list[$key]['updated_by'] = (isset($val['updated_by_name'])) ? $val['updated_by_name'] : $val['created_by_name'];
                $export_user_list[$key]['updated_at'] = (isset($val['updated_at'])) ? Timezone::convertFromUTC($val['updated_at'], $user_time, config('app.date_format')) : ' ';
            }
            if (array_key_exists("relations", $channels[$key])) {
                array_pull($export_user_list[$key], 'relations');
            }
        }

        /* User list  */
        /* ============================================================================== */
        if ($program_sub_type == 'single' && $program_type == 'content_feed') {
            $filename = "ChannelExportUsers.csv";
            $name = 'Channel Name';
        } elseif ($program_sub_type == 'single' && $program_type == 'course') {
            $filename = "CourseExportUsers.csv";
            $name = 'Course Name';
        } else {
            $filename = "PackageExportUsers.csv";
            $name = 'Package Name';
        }

        $fp = fopen('php://output', 'w');
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename=' . $filename);
        $headers = [$name, 'Short Name', 'Status', 'Updated Date', 'Updated By', 'Assigned_User_FullName', 'Assigned_UserName', 'Assigned_User_Email'];
        fputcsv($fp, $headers);
        $export_user_new_list = null;
        foreach ($export_user_list as $each_channels) {
            $row['channel_name'] = $each_channels['program_title'];
            $row['short_name'] = $each_channels['shortname'];
            $row['status'] = $each_channels['status'];
            $row['updated_at'] = $each_channels['updated_at'];
            $row['updated_by'] = $each_channels['updated_by'];
            if (isset($each_channels['users']) && !empty($each_channels['users'])) {
                if ($each_channels['users'] != " ") {
                    foreach ($each_channels['users'] as $each_user) {
                        $row['Fullname'] = $each_user['fullname'];
                        $row['username'] = $each_user['username'];
                        $row['email'] = $each_user['email'];
                        $export_user_new_list[] = $row;
                    }
                } else {
                    $row = [];
                    $row['channel_name'] = $each_channels['program_title'];
                    $row['short_name'] = $each_channels['shortname'];
                    $row['status'] = $each_channels['status'];
                    $row['updated_at'] = $each_channels['updated_at'];
                    $row['updated_by'] = $each_channels['updated_by'];
                    $export_user_new_list[] = $row;
                }
            } else {
            }
        }
        foreach ($export_user_new_list as $val) {
            fputcsv($fp, $val);
        }
        /* ============================================================================== */
        exit();
    }

    /*Function for Channel Export - UserGroup(indirect Users mapping */
    public function getChannelUsergroupExport($program_type, $program_sub_type, $status = 'ALL')
    {
        $user_id = $this->request->user()->uid;
        $list_channel_permission_info_with_flag = [];
        $list_channel_permission_info_with_flag = $this->roleService->hasPermission(
            $user_id,
            ModuleEnum::CHANNEL,
            PermissionType::ADMIN,
            ChannelPermission::LIST_CHANNEL,
            Contexts::PROGRAM,
            null,
            true
        );
        $filter_params = [];
        $list_permission_data = get_permission_data($list_channel_permission_info_with_flag);
        $list_channel_filter_params = has_system_level_access($list_permission_data)?
                                         [] : ["in_ids" => get_instance_ids($list_permission_data, Contexts::PROGRAM)];
        $filter_params = array_merge($filter_params, $list_channel_filter_params);

        $channels = Program::getChannel($status, $program_type, $program_sub_type, $filter_params);
        $export_user_list = [];
        $user_time = Auth::user()->timezone;

        array_walk_recursive($channels, function (&$item, $key) {
            (is_string($item)) ? $item = htmlspecialchars_decode(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($item))), ENT_QUOTES) : '';
        });

        foreach ($channels as $key => &$val) {
            unset($val['_id']);
            //$export_user_list[$key] = $channels[$key];

            //taking user list
            if (isset($channels[$key]['relations']) && !empty($channels[$key]['relations']['active_usergroup_feed_rel']) && array_key_exists('active_usergroup_feed_rel', $channels[$key]['relations'])) {
                $userGroupList = $channels[$key]['relations']['active_usergroup_feed_rel'];
                //we need to display this usergroup names in the csv file

                $userGroupNames = UserGroup::getUserGroupNames($userGroupList);
                $export_user_list[$key]['program_title'] = $channels[$key]['program_title'];
                $export_user_list[$key]['program_shortname'] = (isset($channels[$key]['program_shortname'])) ? $channels[$key]['program_shortname'] : " ";
                $export_user_list[$key]['status'] = $channels[$key]['status'];
                $export_user_list[$key]['updated_by'] = (isset($channels[$key]['updated_by_name'])) ? $channels[$key]['updated_by_name'] : $channels[$key]['created_by_name'];
                $export_user_list[$key]['updated_at'] = (isset($channels[$key]['updated_at'])) ?
                    Timezone::convertFromUTC($channels[$key]['updated_at'], $user_time, config('app.date_format')) :
                    Timezone::convertFromUTC($channels[$key]['created_at'], $user_time, config('app.date_format'));
                $usergroup = implode(",", $userGroupNames);
                $export_user_list[$key]['usersgroup'] = $usergroup;
            } else {
                $export_user_list[$key]['program_title'] = $channels[$key]['program_title'];
                $export_user_list[$key]['program_shortname'] = (isset($channels[$key]['program_shortname'])) ? $channels[$key]['program_shortname'] : " ";
                $export_user_list[$key]['status'] = $channels[$key]['status'];
                $export_user_list[$key]['updated_by'] = (isset($channels[$key]['updated_by_name'])) ? $channels[$key]['updated_by_name'] : $channels[$key]['created_by_name'];
                $export_user_list[$key]['updated_at'] = (isset($channels[$key]['updated_at'])) ?
                    Timezone::convertFromUTC($channels[$key]['updated_at'], $user_time, config('app.date_format')) :
                    Timezone::convertFromUTC($channels[$key]['created_at'], $user_time, config('app.date_format'));
            }

            if (array_key_exists("relations", $channels[$key])) {
                array_pull($export_user_list[$key], 'relations');
            }
        }
        /* Usergroup list  */
        /* ============================================================================== */
        if ($program_sub_type == 'single' && $program_type == 'content_feed') {
            $filename = "ChannelExportUserGroup.csv";
            $name = 'Channel Name';
        } elseif ($program_sub_type == 'single' && $program_type == 'course') {
            $filename = "CourseExportUserGroup.csv";
            $name = 'Course Name';
        } else {
            $filename = "PackageExportUserGroup.csv";
            $name = 'Package Name';
        }
        //$filename = "ChannelExportUserGroup.csv";
        $fp = fopen('php://output', 'w');
        header('Content-Encoding: UTF-8');
        header("Content-type: application/csv; charset=UTF-8");
        header('Content-Disposition: attachment; filename=' . $filename);
        $headers = [$name, 'Short Name', 'Status', 'Updated By', 'Updated Date', 'Assigned_UserGroups'];
        fputcsv($fp, $headers);

        foreach ($export_user_list as $line) {
            fputcsv($fp, $line);
        }
        unset($export_user_list);
        /* ============================================================================== */

        exit();
    }

    public function postCopyCourseContent($from, $to)
    {
        $packets = Packet::getAllPackets($from);
        if (!empty($packets)) {
            foreach ($packets as $key => $each_packet) {
                $packet_id = Packet::uniquePacketId();
                $packet_slug = str_replace($from, $to, $each_packet['packet_slug']);
                $new_packet = array_merge(array_except($each_packet, ['_id', 'to', 'remove']), [
                    'packet_id' => $packet_id,
                    'packet_slug' => $packet_slug,
                    'created_at' => Timezone::getTimeStamp(array_get($each_packet, 'created_at')),
                    'updated_at' => Timezone::getTimeStamp(array_get($each_packet, 'updated_at')),
                    'feed_slug' => $to,
                ]);
                if (isset($each_packet['packet_publish_date']) && is_string($each_packet['packet_publish_date'])) {
                    $new_packet['packet_publish_date'] = strtotime($each_packet['packet_publish_date']);
                }
                Packet::Insert($new_packet);
                $this->post_service->updatePacketElementRelations($new_packet);
                if (config('elastic.service')) {
                    event(new PostAdded($packet_id));
                }
            }
        } else {
            return "Empty Packet";
        }
        return "Copied successfully";
    }

    public function postCourseListForCopyContent()
    {
        $course_slug = Input::get('slug');
        $parent_course_slug = Input::get('parentcourse');
        $program_list = Program::getCourseListForCopyContent($parent_course_slug, $course_slug);
        return view('admin/theme/programs/__course_copy_list', ['program_list' => $program_list]);
    }

    //Custom field post save

    public function postSaveCustomfield($slug)
    {
        Input::flash();
        $filter = Input::get('filter');
        $feedCF = $this->customSer->getFormCustomFields($filter);
        $niceNames = [];
        $rules = [];

        foreach ($feedCF as $feedfield) {
            if ($feedfield['mark_as_mandatory'] == 'yes') {
                $rules[$feedfield['fieldname']] = 'required|max:256';
            } else {
                $rules[$feedfield['fieldname']] = 'max:256';
            }
            $niceNames[$feedfield['fieldname']] = $feedfield['fieldlabel'];
        }

        $validation = Validator::make(Input::all(), $rules, [], $niceNames);

        if ($filter == 'channelfields') {
            if ($validation->fails()) {
                return Redirect::back()->withInput()
                    ->withErrors($validation)->with('feedcustomfield', 'feedcustomfield');
            } elseif ($validation->passes()) {
                $input = Input::except('filter');
                $result = $this->customSer->insertModuleCustomField($input, $slug);
                Input::flush();
                if ($result) {
                    $success = trans('admin/customfields.success_msg');
                    return redirect('/cp/contentfeedmanagement')->with('success', $success);
                } else {
                    return redirect('/cp/contentfeedmanagement');
                }
            }
        } elseif ($filter == 'packagefields') {
            if ($validation->fails()) {
                return Redirect::back()->withInput()
                    ->withErrors($validation)->with('packagecustomfield', 'packagecustomfield');
            } elseif ($validation->passes()) {
                $input = Input::except('filter');
                $result = $this->customSer->insertModuleCustomField($input, $slug);
                Input::flush();
                if ($result) {
                    return redirect('/cp/contentfeedmanagement/list-packs')
                        ->with('success', trans('admin/customfields.success_msg'));
                } else {
                    return redirect('/cp/contentfeedmanagement/list-packs');
                }
            }
        } elseif ($filter == 'productfields') {
            if ($validation->fails()) {
                return Redirect::back()->withInput()
                    ->withErrors($validation)->with('productcustomfield', 'productcustomfield');
            } elseif ($validation->passes()) {
                $input = Input::except('filter');
                $result = $this->customSer->insertModuleCustomField($input, $slug);

                Input::flush();
                if ($result) {
                    $success = trans('admin/customfields.success_msg');
                    return redirect('/cp/contentfeedmanagement/list-products')->with('success', $success);
                } else {
                    return redirect('/cp/contentfeedmanagement/list-products');
                }
            }
        } elseif ($filter == 'coursefields') {
            if ($validation->fails()) {
                return Redirect::back()->withInput()
                    ->withErrors($validation)->with('coursecustomfield', 'coursecustomfield');
            } elseif ($validation->passes()) {
                $input = Input::except('filter');
                $result = $this->customSer->insertModuleCustomField($input, $slug);

                Input::flush();
                if ($result) {
                    $success = trans('admin/customfields.success_msg');
                    return redirect('/cp/contentfeedmanagement/list-courses')->with('success', $success);
                } else {
                    return redirect('/cp/contentfeedmanagement/list-courses');
                }
            }
        } else {
            $msg = trans('admin/program.slug_missing_error');

            return redirect('/cp/contentfeedmanagement/')
                ->with('error', $msg);
        }
    }

    public function updateEnrollCount($program_slug = "")
    {
        $data = Program::where('program_slug', $program_slug)
            ->where('status', 'ACTIVE')
            ->first();
        if (isset($data->relations['active_user_feed_rel'])) {
            $enrolled_count = count($data->relations['active_user_feed_rel']);
            $price_data = ['sellable_id' => $data->parent_id, 'sellable_type' => $data->program_type];
            $v_data = $this->priceService->getVerticalBySlug($price_data, $data->program_title);
            if ($v_data['batch_enrolled'] <= $v_data['batch_maximum_enrollment']) {
                //limited enrollment
                $v_data = array_merge($v_data, ['batch_enrolled' => $enrolled_count]);
                $pv_data = $this->priceService->priceFirst($price_data);
                $this->priceService->updateVertical($pv_data, array_merge($v_data, ['ctitle' => $v_data['batch_name']]), $v_data);
            } elseif ($v_data['batch_maximum_enrollment'] === 0) {
                //unlimited enrollment
                $v_data = array_merge($v_data, ['batch_enrolled' => $enrolled_count]);
                $pv_data = $this->priceService->priceFirst($price_data);
                $this->priceService->updateVertical($pv_data, array_merge($v_data, ['ctitle' => $v_data['batch_name']]), $v_data);
            }
        }
    }

    public function getCourseData()
    {
        return $this->prepareData(Program::getCourseData(Input::get('feed_title')));
    }

    public function getProductData()
    {
        return $this->prepareData(Program::getProductData(Input::get('feed_title')));
    }

    public function getBatchData()
    {
        return $this->prepareData(Program::getBatchData(Input::get('feed_title')));
    }

    public function getChannelData()
    {
        $search = Input::get('feed_title');
        $program_sub_type = Input::get('program_sub_type');
        return $this->prepareData(Program::getChannelData($search, $program_sub_type));
    }

    /**
     * @param string $program_type
     * @param string $program_sub_type
     * @param string $status
     * @param string $visibility
     * @param string $sellability
     */
    public function getChannelExport($program_type, $program_sub_type, $status, $visibility = 'all', $sellability = 'all')
    {
        $filter = [
            'status' => $status,
            'visibility' => $visibility,
            'sellability' => $sellability,
            'access' => Input::get('access', 'all'),
            'category' => Input::get('category'),
            'feed_title' => Input::get('feed_title'),
            'short_name' => Input::get('shortname'),
            'descriptions' => Input::get('descriptions'),
            'created_date' => Input::get('created_date'),
            'updated_date' => Input::get('updated_date'),
            'get_created_date' => Input::get('get_created_date'),
            'get_updated_date' => Input::get('get_updated_date'),
            'feed_tags' => Input::get('feed_tags'),
            'channel_name' => Input::get('channel_name'),
            'batch_name' => Input::get('batch_name')
        ];
        $custom_field_name = [];
        $custom_field_value = [];
        $pgmCustomField = CustomFields::getUserActiveCustomField($program_type, $program_sub_type, 'ACTIVE');
        if (!empty($pgmCustomField)) {
            foreach ($pgmCustomField as $pgm_field) {
                $custom_field_name[] = $pgm_field["fieldname"];
                $custom_field_value[] = Input::get($pgm_field["fieldname"]);
            }
        }
        $this->programservice->exportChannels(
            $program_type,
            $program_sub_type,
            $filter,
            $custom_field_name,
            $custom_field_value
        );
    }

    /**
     * @param $s_data
     * @return \Illuminate\Http\JsonResponse
     */
    private function prepareData($s_data)
    {
        return response()->json([
            'status' => !empty($s_data),
            'data' => array_map(function ($element) {
                return html_entity_decode($element);
            }, $s_data),
        ]);
    }

    public function getImportUserToChannel()
    {
        if (!has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_ACCESS_REQUEST)) {
            return $this->getAdminError();
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/program.manage_channel') => 'contentfeedmanagement',
            trans('admin/program.bulk_import_user_channel') => '',
        ];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/program.manage_channel');
        $this->layout->pageicon = 'fa fa-rss';
        $this->layout->pagedescription = trans('admin/program.manage_channel');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'program')
            ->with('submenu', 'contentfeed');
        $this->layout->content = view('admin.theme.programs.bulk_import_user_channel_mapping');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /*
    @name    : getUserToChannelTemplate
    @purpose : Download xls file with heaers as username, name and shortname
    */
    public function getUserToChannelTemplate()
    {

        $downloadUserXls = [strtolower(trans('admin/user.username')) . '*', strtolower(trans('admin/program.name')). '*', trans('admin/program.channel_shortname')];
        $excelObj = new PHPExcel();
        $excelObj->setActiveSheetIndex(0);
        $excelObj->getActiveSheet()->setTitle('Excel upload');
        $excelObj->getActiveSheet()->fromArray($downloadUserXls, null, 'A1');
        $filename = trans('admin/program.user_channel_filename');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($excelObj, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

     /**
     * Method used to upload file and update data
     * @return nothing
     */
    public function postImportUserToChannel()
    {
        if (!has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_ACCESS_REQUEST)) {
            return $this->getAdminError();
        }
        return $this->programservice->getImportUserToChannelMapping();
    }


    /**
     * Method to download user-channel mapping error file
     * @return download xls file
     */
    public function getBulkImportUserChannelErrorReport()
    {
        $userchannelxlsreport = Session::get('userchannelxlsreport');
        if ($userchannelxlsreport) {
            $excelObj = new PHPExcel();
            $excelObj->setActiveSheetIndex(0);
            $excelObj->getActiveSheet()->setTitle('Excel upload report');
            $excelObj->getActiveSheet()->fromArray($userchannelxlsreport, null, 'A1');
            $filename = trans('admin/program.user_channel_error_file');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($excelObj, 'Excel2007');
            $objWriter->save('php://output');
        }
        exit;
    }

    // Added new options post question

    public function getAllQuestions()
    {
        if (!has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_POST)) {
          return $this->getAdminError();
        }
        $crumbs = [
        trans('admin/dashboard.dashboard') => 'cp',
        trans('admin/program.manage_post_question') => 'contentfeedmanagement/all-questions',
        trans('admin/program.list_question') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/program.manage_post_question');
        $this->layout->pageicon = 'fa fa-question';
        $this->layout->pagedescription = trans('admin/program.manage_post_question');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
        ->with('mainmenu', 'program')
        ->with('submenu', 'postquestions');
        $this->layout->content = view('admin.theme.programs.allquestions');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getAllQuestionsAjax()
    {
        $has_permission_to_manage_posts = $this->roleService->hasPermission(
            $this->request->user()->uid,
            ModuleEnum::CHANNEL,
            PermissionType::ADMIN,
            ChannelPermission::MANAGE_CHANNEL_POST,
            Contexts::PROGRAM,
            null,
            true
        );

        if (!$has_permission_to_manage_posts) {
             return $this->getAdminError();
        }

        $list_permission_data = get_permission_data($has_permission_to_manage_posts);
        $filter_params = has_system_level_access($list_permission_data) ?
            [ 'type' => 'content_feed', 'sub_type' => 'single' ] : ['in_ids' => get_instance_ids($list_permission_data, Contexts::PROGRAM)];
        $program_details = $this->programservice->getFilterPrograms($filter_params, ['program_title', 'program_slug']);
        $program_slugs = $program_details->lists('program_slug');
        try {
            $post_details = $this->post_service->getPostsBySlugLimitedColumn($program_slugs, ['packet_id', 'packet_title', 'packet_slug', 'feed_slug']);
        } catch (Exception $e) {
            Log::info('No Posts');
            $post_details = collect();
        }

        $post_ids = $post_details->lists('packet_id');

        $start = 0;
        $limit = 10;
        $viewmode = Input::get('view', 'desktop');
        $search = Input::get('search');
        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc']; // packet_publish_date

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '0') {
                $orderByArray = ['created_at' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '1') {
                $orderByArray = ['question' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '4') {
                $orderByArray = ['created_by_name' => $order_by[0]['dir']];
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
        $filter = strtolower($filter);
        if (!in_array($filter, ['answered', 'unanswered'])) {
            $filter = 'all';
        } else {
            $filter = strtoupper($filter);
        }

        $total_Records_to_display = $allRecords = [];
        try {
            $total_Records_to_display = $this->post_faq->getPostQuestions($post_ids, $filter, $start, $limit, $orderByArray, $searchKey);
            $allRecords = $this->post_faq->getPostQuestions($post_ids, $filter);
        }
        catch(Exception $e){
            Log::info($e->getMessage());
        }
        $filtereddata = $total_Records_to_display;

        $dataArr = [];
        foreach ($filtereddata as $key => $value) {
            $packet_info = $post_details->where('packet_id', $value['packet_id'])->first();
            $packet_title = $packet_info->packet_title;
            $packet_slug = $packet_info->packet_slug;
            $feed_slug = $packet_info->feed_slug;
            $program_title = $program_details->where('program_slug', $feed_slug)->first()->program_title;

            $temparr = [
                Timezone::convertFromUTC('@' . $value['created_at'], Auth::user()->timezone, config('app.date_format')),
                "<div>" . $value['question'] . "</div>",
                "<div>" . $packet_title . "</div>",
                "<div>" . $program_title . "</div>",
                $value['created_by_name'],
                ucfirst(strtolower($value['status'])),
            ];
            if ($value['access'] == 'private') {
                $temparr[] = '<a class="btn btn-circle show-tooltip" target="_blank" title="' . trans('admin/manageweb.action_portal_view') . '" href="' . URL::to('program/packet/' . $packet_slug . '?from=allquestions') . '" ><i class="fa fa-image"></i></a><a class="btn btn-circle show-tooltip" title="' . trans('admin/manageweb.action_view') . '" href="' . URL::to('cp/contentfeedmanagement/view-packet-question/' . $value['id'] . '?from=allquestions') . '" ><i class="fa fa-eye"></i></a><a class="btn btn-circle show-tooltip faq" data-action="addfaq" data-text="Are you sure you want to mark this question as an FAQ?" title="' . trans('admin/program.mark_as_faq') . '" href="' . URL::to('cp/contentfeedmanagement/packet-question-action/addtofaq/' . $value['id'] . '/' . $packet_slug . '?from=allquestions') . '" ><i class="fa fa-square-o"></i></a>';
            } else {
                $temparr[] = '<a class="btn btn-circle show-tooltip" target="_blank" title="' . trans('admin/manageweb.action_portal_view') . '" href="' . URL::to('program/packet/' . $packet_slug . '?from=allquestions') . '" ><i class="fa fa-image"></i></a><a class="btn btn-circle show-tooltip" title="' . trans('admin/manageweb.action_view') . '" href="' . URL::to('cp/contentfeedmanagement/view-packet-question/' . $value['id'] . '?from=allquestions') . '" ><i class="fa fa-eye"></i></a><a class="btn btn-circle show-tooltip faq" data-action="removefaq" data-text="Are you sure you want to remove this question from FAQ?" title="' . trans('admin/program.remove_from_faq') . '" href="' . URL::to('cp/contentfeedmanagement/packet-question-action/removefromfaq/' . $value['id'] . '/' . $packet_slug . '?from=allquestions') . '" ><i class="fa fa-check-square-o"></i></a>';
            }
            if ($viewmode == 'iframe') {
                array_pop($temparr);
                array_pop($temparr);
            }
            $dataArr[] = $temparr;
        }
        $finaldata = [
            'recordsTotal' => $total_Records_to_display->count(),
            'recordsFiltered' =>  $allRecords->count(),
            'data' => $dataArr,
        ];
        return response()->json($finaldata);
    }

    public function getViewPacketQuestion($id = null)
    {
        $has_permission_to_manage_posts = $this->roleService->hasPermission(
            $this->request->user()->uid,
            ModuleEnum::CHANNEL,
            PermissionType::ADMIN,
            ChannelPermission::MANAGE_CHANNEL_POST,
            Contexts::PROGRAM,
            null,
            true
        );

        if (!$has_permission_to_manage_posts) {
             return $this->getAdminError();
        }

        $question = $this->post_faq->getQuestionsByQuestionID((int)$id);
        if (!preg_match('/^[0-9]+$/', $id) || $question->isEmpty()) {
            $msg = trans('admin/program.question_missing_error');
            return redirect('/cp/contentfeedmanagement/')
                ->with('error', $msg);
        }
        $question = $question->first()->toArray();
        $packet = $this->post_service->getPacketByID($question['packet_id']);
        $answers = $this->post_faq_ans->getAnswersByQuestionID($question['id'])->toArray();
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/program.manage_question') => 'contentfeedmanagement/all-questions',
            trans('admin/program.view_question') => '',
        ];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/program.view_question');
        $this->layout->pageicon = 'fa fa-question';
        $this->layout->pagedescription = trans('admin/program.view_question');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'program')
            ->with('submenu', 'postquestions');
        $this->layout->content = view('admin.theme.programs.postqanda')
            ->with('packet', $packet)
            ->with('id', $id)
            ->with('question', $question)
            ->with('answers', $answers);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getDeletePacketAnswer($id = null)
    {
        $has_permission_to_manage_posts = $this->roleService->hasPermission(
            $this->request->user()->uid,
            ModuleEnum::CHANNEL,
            PermissionType::ADMIN,
            ChannelPermission::MANAGE_CHANNEL_POST,
            Contexts::PROGRAM,
            null,
            true
        );

        if (!$has_permission_to_manage_posts) {
             return $this->getAdminError();
        }

        $answer = $this->post_faq_ans->getAnswersByAnswerID((int)$id);
        if (!preg_match('/^[0-9]+$/', $id) || $answer->isEmpty()) {
            $msg = trans('admin/qanda.answer_missing_error');
            return redirect('/cp/contentfeedmanagement/')
                ->with('error', $msg);
        }
        $answer = $answer->first()->toArray();
        $question = $this->post_faq->getQuestionsByQuestionID((int)$answer['ques_id'])->first()->toArray();
        $this->post_faq_ans->DeleteRecord($id);
        $oldanswers = $this->post_faq_ans->getAnswersByQuestionID($question['id'], $question['user_id']);
        if ($oldanswers->isEmpty()) {
            $this->post_service->IncrementField($question['packet_id'], 'total_ques_unanswered');
            $this->post_faq->getUpdateFieldByQuestionId($answer['ques_id'], 'status', 'UNANSWERED');
        }
        $msg = trans('admin/qanda.answer_delete');
        return redirect('/cp/contentfeedmanagement/view-packet-question/' . $question['id'])
            ->with('success', $msg);
    }

    public function postWritePacketAnswer($id = null)
    {
        $has_permission_to_manage_posts = $this->roleService->hasPermission(
            $this->request->user()->uid,
            ModuleEnum::CHANNEL,
            PermissionType::ADMIN,
            ChannelPermission::MANAGE_CHANNEL_POST,
            Contexts::PROGRAM,
            null,
            true
        );

        if (!$has_permission_to_manage_posts) {
             return $this->getAdminError();
        }

        $question = $this->post_faq->getQuestionsByQuestionID((int)$id);
        if (!preg_match('/^[0-9]+$/', $id) || $question->isEmpty()) {
            $msg = trans('admin/program.question_missing_error');
            return redirect('/cp/contentfeedmanagement/')
                ->with('error', $msg);
        }
        $rules = [
            'answer' => 'Required',
        ];
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return redirect('cp/contentfeedmanagement/view-packet-question/' . $id)->withInput()
                ->withErrors($validation);
        } elseif ($validation->passes()) {
            $question = $question->first()->toArray();
            // Update the total_unanswered questions for packets
            $oldanswers = $this->post_faq_ans->getAnswersByQuestionID($question['id'], $question['user_id']);
            if ($oldanswers->isEmpty()) {
                // Decrease one count from total unanswered questions count of packet
                $this->post_service->DecrementField($question['packet_id'], 'total_ques_unanswered');
            }

            $this->post_faq->getUpdateFieldByQuestionId($question['id'], 'status', 'ANSWERED');
            $insertarr = [
                'id' => PacketFaqAnswers::getUniqueId(),
                'ques_id' => (int)$id,
                'user_id' => Auth::user()->uid,
                'username' => Auth::user()->username,
                'answer' => Input::get('answer'),
                'status' => 'ACTIVE',
                'created_at' => time(),
                'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
            ];
            // Send a notification to the user.
            if (Config::get('app.notifications.packetsfaq.answered')) {
                $notif_msg = trans('admin/notifications.answered', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname]);
                NotificationLog::getInsertNotification([$question['user_id']], 'packetfaq', $notif_msg);
            }

            $this->post_faq_ans->InsertRecord($insertarr);
            $msg = trans('admin/qanda.answer_success');
            return redirect('/cp/contentfeedmanagement/view-packet-question/' . $id)
                ->with('success', $msg);
        }
    }

    public function getPacketQuestionAction($action = null, $questionid = null, $packet_slug = null)
    {
        $has_permission_to_manage_posts = $this->roleService->hasPermission(
            $this->request->user()->uid,
            ModuleEnum::CHANNEL,
            PermissionType::ADMIN,
            ChannelPermission::MANAGE_CHANNEL_POST,
            Contexts::PROGRAM,
            null,
            true
        );

        if (!$has_permission_to_manage_posts) {
             return $this->getAdminError();
        }

        $question = $this->post_faq->getQuestionsByQuestionID((int)$questionid);
        $actions = ['addtofaq', 'removefromfaq'];
        if (!preg_match('/^[0-9]+$/', $questionid) || $question->isEmpty() || !in_array($action, $actions) || !$packet_slug) {
            $msg = trans('admin/program.question_missing_error');
            return redirect('/cp/contentfeedmanagement/')
                ->with('error', $msg);
        }
        $question = $question->first()->toArray();
        switch ($action) {
            case 'addtofaq':
                // Send a notification to the user.
                if (Config::get('app.notifications.packetsfaq.addtofaq')) {
                    $notif_msg = trans('admin/notifications.addtofaq', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'question' => $question['question']]);
                    NotificationLog::getInsertNotification([$question['user_id']], 'packetfaq', $notif_msg);
                }

                $this->post_faq->getUpdateFieldByQuestionId($questionid, 'access', 'public');
                // Decrease one count from total private questions count of packet
                $this->post_service->DecrementField($question['packet_id'], 'total_ques_private');
                // Increase one count from total public questions count of packet
                $this->post_service->IncrementField($question['packet_id'], 'total_ques_public');
                $msg = trans('admin/qanda.faq_success');
                return redirect('/cp/contentfeedmanagement/all-questions')
                        ->with('success', $msg);

                // Update the total faq count in packets
                break;
            case 'removefromfaq':

                $this->post_faq->getUpdateFieldByQuestionId($questionid, 'access', 'private');
                // Decrease one count from total public questions count of packet
                $this->post_service->DecrementField($question['packet_id'], 'total_ques_public');
                // Increase one count from total private questions count of packet
                $this->post_service->IncrementField($question['packet_id'], 'total_ques_private');
                $msg = trans('admin/qanda.faq_remove_success');
                return redirect('/cp/contentfeedmanagement/all-questions')
                        ->with('success', $msg);
                // Update the total faq count in packets
                break;
        }
        $msg = trans('admin/qanda.error');
        return redirect('/cp/contentfeedmanagement/')
            ->with('error', $msg);
    }

    public function getListCertificateUsers()
    {
        /*Since we don't have explicit permission to this certificate so currently using package permission*/
        if ((!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::ADD_PACKAGE)) ||
            (!is_certificate_enable('certificates')) || !config('app.list_certificate'))
        {
            return $this->getAdminError();
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/user.list_users') => '',
        ];
        $programs = $this->programservice->getAllUndeletedPrograms();

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/program.issued_certificates');
        $this->layout->pageicon = 'fa fa-rss';
        $this->layout->pagedescription = trans('admin/program.issued_certificates');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
                                ->with('mainmenu', 'program')
                                ->with('submenu', 'certificates');
        $this->layout->content = view('admin.theme.programs.user_certificates')
                                ->with('programs', $programs);
        $this->layout->footer = view('admin.theme.common.footer');

    }

    public function getCertificateUsersList()
    {
        /*Since we don't have explicit permission to this certificate so currently using package permission*/
        if ((!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::ADD_PACKAGE)) ||
            (!is_certificate_enable('certificates')) || !config('app.list_certificate'))
        {
            return $this->getAdminError();
        }
        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = "";
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];
        $filter = Input::get('filter');

        $filteredRecords = [];
        $filteredRecords = [];
        $dataArr = [];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            switch ($order_by[0]['column']) {
                case '3':
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

        $program_details = $this->programservice->getProgramById($filter);
        $program_id = $program_details->program_id;

        if ((empty($searchKey)) && (key($orderByArray) == 'created_at')) {
            $total_certicates = $this->user_certificate_service->getCountByProgramId($program_id)->keyBy('user_id');
            $certified_user_ids = $total_certicates->keys()->all();
            $certified_user_ids =  array_unique(array_filter($certified_user_ids));
            $total_cer_count = $this->user_service->getCertifiedUsersCount($certified_user_ids, [Auth::user()->uid]);
            $userCertificates = $this->user_certificate_service->getCertificatesByProgramId(
                $program_id,
                ['user_id', 'created_at'],
                $orderByArray,
                $start,
                $limit
            )->keyBy('user_id');
            $user_ids = $userCertificates->pluck('user_id')->all();
            $filteredData = $this->user_service->getUserDetailsByUserIds(
                0,
                $limit,
                ['uid', 'username', 'firstname', 'lastname', 'email'],
                $searchKey,
                $user_ids,
                [Auth::user()->uid],
                $orderByArray
            )->keyBy('uid');

        } else {
            $filteredData = $this->user_service->getFilteredUserDetails(
                0,
                config('app.user_report_limit'),
                ['uid', 'username', 'firstname', 'lastname', 'email'],
                $searchKey,
                [Auth::user()->uid],
                ['created_at' => 'desc']
            )->keyBy('uid');
            $user_lists_ids = $filteredData->keys()->all();

            $userCertificates = $this->user_certificate_service->getCertifiedUsersLists(
                $program_id,
                $user_lists_ids,
                ['user_id', 'created_at'],
                $orderByArray,
                $start,
                $limit
            )->keyBy('user_id');
            $total_cer_count = $userCertificates->count();
        }
        foreach ($userCertificates as $user_id => $user_certificate) {
            $user_detail = $filteredData->get($user_id);
            if (is_null($user_detail) && empty($user_detail)) {
                continue;
            }
            $dataArr[] = [
                $user_detail->username,
                $user_detail->firstname.' '.$user_detail->lastname,
                $user_detail->email,
                 Timezone::convertFromUTC('@' . $user_certificate->created_at, Auth::user()->timezone, config('app.date_format')),
                '<a class="btn btn-circle show-tooltip ajax view-certificate" title="' . trans('admin/manageweb.action_view') . '"
                href="' . URL::to('/cp/contentfeedmanagement/pdf/' . $user_id. '/'. $program_id) . '" target="_blank" >
                <i class="fa fa-eye"></i></a>'
            ];
        }
        $finaldata = [
            'recordsTotal' => $total_cer_count,
            'recordsFiltered' => $total_cer_count,
            'data' => $dataArr
        ];
        return response()->json($finaldata);
    }

    public function getExportCertificateUsers($channel)
    {
        /*Since we don't have explicit permission to this certificate so currently using package permission*/
        if ((!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::ADD_PACKAGE)) ||
            (!is_certificate_enable('certificates')) || !config('app.list_certificate'))
        {
            return $this->getAdminError();
        }
        try {
            $program_details = $this->programservice->getProgramById($channel);
            $program_id = $program_details->program_id;
            $program_title = $program_details->program_title;

            $filename = "CertificateUsers.csv";
            $file_pointer = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . $filename);
            fputcsv(
                $file_pointer,
                [
                    trans('admin/program.content_feed').' Name:',
                    $program_title
                ]
            );
            fputcsv(
                $file_pointer,
                [
                    trans('admin/user.username'),
                    trans('admin/user.full_name'),
                    trans('admin/user.email_id'),
                    trans('admin/program.issued_date')
                ]
            );
            $batch_limit = config('app.bulk_insert_limit');
            $record_set = 0;

            $total_cer_count = $this->user_certificate_service->getCountByProgramId($program_id)->count();
            $total_batchs = intval($total_cer_count / $batch_limit);
            do {
                $start = $record_set * $batch_limit;
                $userCertificates = $this->user_certificate_service->getCertificatesByProgramId(
                    $program_id,
                    ['user_id', 'created_at'],
                    ['created_at' => 'desc'],
                    $start,
                    $batch_limit
                )->keyBy('user_id');
                $user_ids = $userCertificates->pluck('user_id')->all();
                $filteredData = $this->user_service->getUserDetailsByUserIds(
                    0,
                    0,
                    ['uid', 'username', 'firstname', 'lastname', 'email'], '',
                    $user_ids,
                    [Auth::user()->uid],
                    ['created_at' => 'desc']
                )->keyBy('uid');
                if ($userCertificates->count() > 0) {
                    foreach ($userCertificates as $user_id => $user_certificate) {
                        $user_detail = $filteredData->get($user_id);
                        if (is_null($user_detail) && empty($user_detail)) {
                            continue;
                        }

                        fputcsv(
                            $file_pointer,
                            [
                                $user_detail->username,
                                $user_detail->firstname.' '.$user_detail->lastname,
                                $user_detail->email,
                                Timezone::convertFromUTC('@' . $user_certificate->created_at, Auth::user()->timezone, config('app.date_format'))
                            ]
                        );
                    }
                } else {
                    fputcsv($file_pointer,[]);
                    fputcsv($file_pointer,[trans('admin/program.no_records_found')]);
                }
                $record_set++;
            } while ($record_set <= $total_batchs);
            fclose($file_pointer);
            exit;
        } catch (Exception $e) {
            Log::error('CertificateUsersLists::getExportCertificateUsers()  ' . $e->getMessage());
            exit;
        }

    }

    /**
     * Method to view/download pdf
     * @param  int $progarm_id
     * @param  int $uid
     */
    public function getPdf($uid, $progarm_id)
    {
        /*Since we don't have explicit permission to this certificate so currently using package permission*/
        if ((!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::ADD_PACKAGE)) ||
            !is_certificate_enable('certificates') || !config('app.list_certificate'))
        {
            return $this->getAdminError();
        }
        $certificate = $this->user_certificate_service->getCertificateByUserAndProgramId($uid, $progarm_id);
        if (empty($certificate)) {
            abort(404);
        }

        // instantiate and use the dompdf class
        $dompdf = new Dompdf([
            'enable_remote' => true,
            'enable_html5parser' => true,
            'enable_fontsubsetting' => true,
            'unicode_enabled' => true,
        ]);

        $dompdf->loadHtml($certificate->content);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A3', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream($certificate->program_title . '_certificate', ['Attachment' => 0]);
        exit(); //fix for preview
    }
}
