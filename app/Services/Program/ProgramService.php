<?php

namespace App\Services\Program;

use Log;
use App\Enums\Cron\CronBulkImport;
use Auth;
use Carbon;
use Config;
use Input;
use PHPExcel;
use PHPExcel_IOFactory;
use Redirect;
use Request;
use Session;
use Validator;
use App\Model\Email;
use App\Model\Common;
use App\Model\User;
use App\Enums\Program\ProgramStatus;
use App\Enums\User\UserEntity;
use App\Enums\User\UserStatus;
use App\Services\Package\IPackageService;
use App\Services\Post\IPostService;
use App\Services\Role\IRoleService;
use App\Enums\User\EnrollmentSource;
use App\Model\User\Entity\UserEnrollment;
use App\Model\ImportLog\Entity\EnrolLog;
use App\Exceptions\ApplicationException;
use App\Model\Post\IPostRepository;
use App\Model\Program\IProgramRepository;
use App\Model\ImportLog\Entity\ProgramLog;
use App\Enums\RolesAndPermissions\Contexts;
use App\Enums\RolesAndPermissions\SystemRoles;
use App\Model\User\Repository\IUserRepository;
use App\Exceptions\User\RelationNotFoundException;
use App\Model\Package\Repository\IPackageRepository;
use App\Exceptions\Program\ProgramNotFoundException;
use App\Model\Category\Repository\ICategoryRepository;
use App\Exceptions\Category\CategoryNotFoundException;
use App\Exceptions\Program\NoProgramAssignedException;
use App\Model\UserGroup\Repository\IUserGroupRepository;
use App\Model\RolesAndPermissions\Repository\IRoleRepository;
use App\Model\RolesAndPermissions\Repository\IContextRepository;
use App\Model\TransactionDetail\Repository\ITransactionDetailRepository;
use App\Events\User\EntityEnrollmentByAdminUser;

/**
 * Class ProgramService
 *
 * @package App\Services\Program
 */
class ProgramService implements IProgramService
{
    /**
     * @var \App\Model\Program\IProgramRepository
     */
    private $program_repository;

    /**
     * @var \App\Model\Post\IPostRepository
     */
    private $post_repository;

    /**
     * @var \App\Model\Category\Repository\ICategoryRepository
     */
    private $category_repository;

    /**
     * @var |App\Services\Post\IPostService
     */
    private $post_service;

    /**
     * @var /App\Model\Package\Repository\IPackageRepository
     */
    private $packageRepository;

    /**
     * @var IUserRepository
     */
    private $userRepository;
    /**
     * @var IRoleRepository
     */
    private $roleRepository;
    /**
     * @var IContextRepository
     */
    private $contextRepository;
    /**
     * @var IUserGroupRepository
     */
    private $userGroupRepository;

    /**
     * @var App\Services\Package\IPackageService
     */
    private $packageService;

    /**
     * @var ITransactionDetailRepository
     */
    private $transactionDetailsRepository;

     /**
     * @var IRoleService
     */
    private $roleService;

    /**
     * ProgramService constructor.
     * @param IProgramRepository $program_repository
     * @param IPostRepository $post_repository
     * @param ICategoryRepository $category_repository
     * @param IPostService $post_service
     * @param IPackageRepository $packageRepository
     * @param IUserRepository $userRepository
     * @param IRoleRepository $roleRepository
     * @param IContextRepository $contextRepository
     * @param IUserGroupRepository $userGroupRepository
     * @param IPackageService $packageService
     * @param ITransactionDetailRepository $transactionDetailsRepository
     * @param IRoleService $roleService
     */
    public function __construct(
        IProgramRepository $program_repository,
        IPostRepository $post_repository,
        ICategoryRepository $category_repository,
        IPostService $post_service,
        IPackageRepository $packageRepository,
        IUserRepository $userRepository,
        IRoleRepository $roleRepository,
        IContextRepository $contextRepository,
        IUserGroupRepository $userGroupRepository,
        IPackageService $packageService,
        ITransactionDetailRepository $transactionDetailsRepository,
        IRoleService $roleService
    ) {
        $this->program_repository = $program_repository;
        $this->post_repository = $post_repository;
        $this->category_repository = $category_repository;
        $this->post_service = $post_service;
        $this->packageRepository = $packageRepository;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->contextRepository = $contextRepository;
        $this->userGroupRepository = $userGroupRepository;
        $this->packageService = $packageService;
        $this->transactionDetailsRepository = $transactionDetailsRepository;
        $this->roleService = $roleService;
    }

    public function getProgram($id)
    {
        return $this->program_repository->find($id);
    }

    /**
     * @inheritDoc
     */
    public function getProgramBySlug($type, $slug)
    {
        return $this->program_repository->findByAttribute($type, "program_slug", $slug);
    }

    public function getProgramIdBySlug($program_slug)
    {
        return $this->program_repository->getProgramIdBySlug($program_slug);
    }

    /**
     * @inheritDoc
     */
    public function getProgramPostBySlug($program_type, $program_slug, $post_slug)
    {
        return $this->program_repository->findProgramPostByAttribute(
            $program_slug,
            "packet_slug",
            $post_slug
        );
    }

    /**
     * @inheritDoc
     */
    public function getPostQuestionsCount($post_id, $filter_params = [])
    {
        return $this->program_repository->getPostQuestionsCount($post_id, $filter_params);
    }

    /**
     * @inheritDoc
     */
    public function getPostQuestions($post_id, $filter_params = [])
    {
        return $this->program_repository->getPostQuestions(
            $post_id,
            $filter_params
        );
    }

    /**
     * {@inheritdoc}
     * @throws ProgramNotFoundException
     */
    public function getUserPrograms($page, $limit, $posts = false, $order = false)
    {
        $start = ($page * $limit) - $limit;
        $channel_ids = [];
        $programs = collect();
        if (!$posts) {
            $columns = ['program_id', 'program_title', 'program_slug', 'program_display_startdate', 'program_display_enddate', 'program_cover_media', 'program_categories'];
        } else {
            $columns = ['program_id', 'program_title', 'program_slug', 'program_categories'];
        }
        if (!is_admin_role(Auth::user()->role)) {
            $entities = $this->getAllProgramsAssignedToUser(Auth::user()->uid);
            $channel_ids = $entities['channel_ids'];
            if (!$order) {
                $programs = $this->program_repository->getActiveProgramsData($channel_ids, $start, $limit, $columns);
            } else {
                $programs = $this->program_repository->getProgramsOrderByName($channel_ids, $start, $limit, $columns);
            }
        } else {
            if (!$order) {
                $programs = $this->program_repository->getActiveProgramsData($channel_ids, $start, $limit, $columns, true);
            } else {
                $programs = $this->program_repository->getProgramsOrderByName($channel_ids, $start, $limit, $columns, true);
            }
        }
        $data = [];
        if (config('app.dashboard_cat_display')) {
            $data['category_details'] = $this->category_repository->getCategoryDetails(array_filter(array_flatten($programs->pluck('program_categories')->all())));
        }
        if ($programs->count() > 0) {
            if (!$posts) {
                $results = $programs->map(function ($program) {
                    $row = $program->toArray();
                    $row['program_startdate'] = $program->program_display_startdate->timestamp;
                    $row['program_enddate'] = $program->program_display_enddate->timestamp;
                    return collect($row);
                });
            } else {
                $post_repository = $this->post_repository;
                $results = $programs->map(function ($program) use ($post_repository) {
                    $row = $program->toArray();
                    $row['posts'] = $post_repository->getActivePostsCount($program->program_slug);
                    return collect($row);
                });
            }

            $data['results'] = $results;
            $analytics =  $this->program_repository->getProgramsAnalytics($programs->pluck('program_id')->all());
            if ($analytics->count() > 0) {
                $data['analytics'] = $analytics;
            }
            $faq = $this->program_repository->getProgramsFaq($programs->pluck('program_id')->all());
            if (!empty($faq)) {
                $data['faqs'] = $faq;
            }
        }
        if (empty($data)) {
            throw new ProgramNotFoundException();
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     * @throws ProgramNotFoundException
     */
    public function getActiveProgramsTotalCount()
    {
        $data = [];
        $slugs = $this->program_repository->getActiveProgramsSlug();
        if (!$slugs->isEmpty()) {
            $data['program_count'] = $slugs->count();
            $data['quiz_count'] = $this->post_service->getAssessmentsCountInPostsBySlugs($slugs->lists('program_slug')->all());
            return $data;
        } else {
            throw new ProgramNotFoundException();
        }
    }

    /**
     * {@inheritdoc}
     * @throws ProgramNotFoundException
     * @throws NoProgramAssignedException
     */
    public function getActiveProgramsCount($page, $limit)
    {
        $data = [];
        $programs_id = $this->program_repository->getAllProgramsAssignedToUser(Auth::user()->uid);
        $programs = $this->program_repository->getActiveProgramsData($programs_id, ($page * $limit) - $limit, $limit, ['program_slug', 'program_title']);
        if (!$programs->isEmpty()) {
            $data['programs'] = $programs;
            foreach ($programs as $program) {
                $posts = $this->post_service->getNewPostsBySlug([$program->program_slug]);
                $assessments = $this->post_service->getAssessmentsCountInPostsBySlugs([$program->program_slug]);
                $data['count'][$program->program_slug] = ['assessments' => $assessments, 'posts' => $posts];
            }
            return $data;
        } else {
            throw new ProgramNotFoundException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryWiseChannels()
    {
        if (is_admin_role(Auth::user()->role)) {
            $channels_list = $this->getAllProgramsAssignedToSiteAdmin();
            $data = $this->getAllCategoryWithChannel($channels_list);
        } else {
            $channels_list = $this->getAllProgramsAssignedToUser(Auth::user()->uid);
            $data = $this->getAllCategoryWithChannel($channels_list);
        }
       
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllCategoryWithChannel($channels_list, $page_no = null, $package_name_flag = false)
    {
        $data = $assigned_channels = $package_name = [];
        $all_channel_list = collect([]);
        if (empty($channels_list)) {
            throw new NoProgramAssignedException();
        }
        $categories = $this->category_repository->getCategories($channels_list);
        $channel_details = $this->program_repository->get(['in_ids' => $channels_list['channel_ids']]);
        $package_details = $this->packageRepository->get(['in_ids' => array_get($channels_list, 'package_ids', [])]);
        
        if ($package_name_flag == true) {
            collect($channels_list['channel_ids'])->map(function ($item, $key) use ($package_details, &$package_name, $channel_details) {
                $package_details->map(function ($package, $key) use ($item, &$package_name, $channel_details) {
                    if (in_array($item, $package->program_ids)) {
                        $package_name[$item]['package_title'] = $package->package_title;
                        return $package_name;
                    } else {
                        return false;
                    }
                });
            });
        }
        
        if ($categories->isEmpty()) {
            $count = count($channels_list['channel_ids']);
            $data[$count]['title'] = trans('program.other_programs');
            $data[$count]['slug'] = 'other-categories';
            $data[$count]['data'] = $channel_details;
            $data = ['data' => $data];
        } else {
            foreach ($categories as $row => $category) {
                if ((isset($category->relations['assigned_feeds']) && !empty($category->relations['assigned_feeds']) ) || (isset($category->package_ids) && !empty($category->package_ids))) {
                    $category_assigned_feeds = array_get($category->relations, 'assigned_feeds', []);
                    $category_assigned_packages = array_get($category, 'package_ids', []);

                    $category_package_details = $package_details->whereIn(
                        'package_id',
                        $category_assigned_packages
                    );
                    $package_channel = $category_package_details->pluck('program_ids')->collapse()->toArray();
                    $category_channel_details = $channel_details->whereIn(
                        'program_id',
                        array_merge($category_assigned_feeds, $package_channel)
                    );
                    if (!$category_channel_details->isEmpty()) {
                        $all_channel_list  = $all_channel_list->merge($category_channel_details->pluck('program_id'))->unique();
                        $data[$row]['title'] = html_entity_decode($category->category_name);
                        $data[$row]['slug'] = $category->slug;
                        $data[$row]['id'] = $category->category_id;
                        $data[$row]['data'] = $category_channel_details;
                    }
                }
            }
            if (!$category_channel_details->isEmpty()) {
                $other_channels = array_diff($channels_list['channel_ids'], $all_channel_list->toArray());
                $other_channels = $channel_details->whereIn(
                    'program_id',
                    $other_channels
                );
                if (!$other_channels->isEmpty()) {
                    $count = count($data);
                    $data[$count]['title'] = trans('program.other_programs');
                    $data[$count]['slug'] = 'other-categories';
                    $data[$count]['data'] = $other_channels;
                }
            }
            if (!is_null($page_no)) {
                $perPage = 5;
                $start = (int)$page_no * $perPage;
                $data = array_slice($data, $start, $perPage, true);
            }
            if ($package_name_flag == true) {
                $data = ['data' => $data,
                        'package_name' => $package_name
                ];
            } else {
                $data = ['data' => $data];
            }
        }
        return $data;
    }

    /**
     * @param $channels
     * @return array
     */
    private function getProgramData($channels)
    {
        $result = [];
        foreach ($channels as $key => $channel) {
            $program = $this->program_repository->getProgramDataByAttribute(
                'program_id',
                $channel,
                ['program_id','program_title', 'program_slug', 'program_cover_media']
            );
            if (!$program->isEmpty()) {
                $result[$key] = $program->first();
            }
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getProgramUsers($program_id, $filter_params)
    {
        $data = [];
        $totalUsersCount = 0;
        $filteredUsersCount = 0;

        $program_context_id = $this->contextRepository->findByAttribute("slug", Contexts::PROGRAM)->id;

        try {
            $program = $this->program_repository->find($program_id);
            $assigned_user_ids = $this->userRepository->getUserEntityRelationsUIds(
                [
                    "entity_type" => UserEntity::PROGRAM,
                    "entity_id" => $program->program_id,
                    "source_type" => EnrollmentSource::DIRECT_ENROLLMENT,
                ]
            );
            $user_ids = [];
            switch ($filter_params["enrollment_status"]) {
                case "ASSIGNED":
                    $user_ids = $assigned_user_ids;
                    $totalUsersCount = $this->userRepository->getUsersCount(["user_ids" => $user_ids]);
                    break;
                case "UNASSIGNED":
                    $registered_user_ids = $this->userRepository->getRegisteredUsersId(
                        ["status" => [UserStatus::ACTIVE]]
                    );
                    $user_ids = array_diff($registered_user_ids, $assigned_user_ids);
                    $totalUsersCount = $this->userRepository->getUsersCount(
                        ["user_ids" => $user_ids]
                    );
                    break;
                default:
                    break;
            }

            $filter_params = array_merge($filter_params, ["user_ids" => $user_ids]);

            $filtered_users = $this->userRepository->get($filter_params);

            array_forget($filter_params, ["start", "limit"]);
            $filteredUsersCount = $this->userRepository->getUsersCount($filter_params);
            $filtered_users->each(
                function ($user, $key) use (&$data, $filter_params, $program_context_id, $program_id) {
                    $data[$key]["id"] = $user->uid;
                    $data[$key]["firstname"] = $user->firstname;
                    $data[$key]["lastname"] = $user->lastname;
                    $data[$key]["username"] = $user->username;
                    $data[$key]["email"] = $user->email;
                    $data[$key]["role"] = $user->role;

                    if ($filter_params["enrollment_status"] === "ASSIGNED") {
                        try {
                            $user_role_mapping = $this->roleRepository->findUserRoleMapping(
                                $user->uid,
                                $program_context_id,
                                $program_id
                            );

                            $role = $this->roleRepository->find($user_role_mapping->role_id);
                            $data[$key]["role_id"] = $role->rid;
                            $data[$key]["role_name"] = $role->name;
                            $data[$key]["role_slug"] = $role->slug;
                        } catch (ApplicationException $e) {
                            Log::info($e->getMessage());
                        }
                    }

                    $data[$key]["status"] = $user->status;
                    $data[$key]["created_at"] = $user->created_at;
                }
            );

        } catch (ProgramNotFoundException $e) {
            Log::error($e->getMessage());
        }

        return [
            "total_users_count" => $totalUsersCount,
            "filtered_users_count" => $filteredUsersCount,
            "data" => $data
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAllPrograms($filter_params = [])
    {
        $data = $this->program_repository->get($filter_params);
        return $data->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getQuestion($program_id, $question_id)
    {
        return $this->program_repository->findQuestion($program_id, $question_id);
    }

    /**
     * @inheritDoc
     */
    public function getQuestionsCount($filter_params = [])
    {
        return $this->program_repository->getQuestionCount($filter_params);
    }

    /**
     * @inheritDoc
     */
    public function getQuestions($filter_params = [])
    {
        return $this->program_repository->getQuestions($filter_params);
    }

    /**
     * {@inheritdoc}
     */
    public function getPackets($program_slugs = [])
    {
        $data = $this->program_repository->getPackets($program_slugs);
        return $data->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getPostQuestion($post_id, $question_id)
    {
        return $this->program_repository->findPostQuestion($post_id, $question_id);
    }

    /**
     * Method to prepare erp program log data
     * @param array $data
     * @param string $type
     * @param string $status
     * @param int $cron
     * @return array
     */
    public function getErpLogdata($data, $type, $status, $cron)
    {
        $data['program_type'] = 'content_feed';
        $data['program_sub_type'] = $type;
        $data['created_by'] = ($cron == 0) ? Auth::user()->username : CronBulkImport::CRONUSER;
        $data['created_at'] = time();
        $data['status'] = $status;
        return $data;
    }

    /**
     * Method to prepare erp program data
     * @param int $id
     * @param array $data
     * @param string $type
     * @param int $cron
     * @return nothing
     */
    public function getPrepareErpProgramData($id, $data, $type, $cron)
    {
        $this->program_repository->getPrepareProgramData($id, $data, $type, $cron);
    }

    /**
     * @param string $name
     * @param string $shortname
     * @param string $type
     * @param string $subtype
     * @return int program_id
     */
    public function getProgramId($name, $shortname, $type, $subtype)
    {
        return $this->program_repository->getProgramId($name, $shortname, $type, $subtype);
    }

    /**
     * @return int programid
     * @param int $userid
     * @param string $name
     * @param string $shortname
     * @param string $type
     * @param string $subtype
     * @param string $level
     * @param int $cron
     * @return nothing enrols user to program
     */
    public function enrolUserToProgram($programid, $userid, $name, $shortname, $type, $subtype, $level, $cron)
    {
        return $this->program_repository->enrolUserToProgram($programid, $userid, $name, $shortname, $type, $subtype, $level, $cron);
    }

    /**
     * @return int programid
     * @param int $groupid
     * @param string $name
     * @param string $shortname
     * @param string $type
     * @param string $subtype
     * @param string $level
     * @param int $cron
     * @return nothing enrols usergroup to program
     */
    public function enrolUsergroupToProgram($programid, $groupid, $name, $shortname, $type, $subtype, $level, $cron)
    {
        return $this->program_repository->enrolUsergroupToProgram($programid, $groupid, $name, $shortname, $type, $subtype, $level, $cron);
    }

    /**
     * @param string $status
     * @param string $slug
     * @param string $reason
     * @param string $action
     * @return nothing sends email to support email
     */
    public function channelImportEmail($status, $slug, $reason = null, $action)
    {
        $created_date = date('d-m-Y');
        $site_name = config('app.site_name');
        $to = config('app.import_email');
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
        $headers .= 'From:' . $site_name . "\r\n";
        if ($status == 'SUCCESS') {
            $find = ['<EMAIL>', '<TOTAL>', '<SUCCESS>', '<FAILURE>', '<SITE NAME>'];
            $total = ProgramLog::getPackageImportCount($type = 'ALL', $search = null, $created_date, 'single', $action);
            $success = ProgramLog::getPackageImportCount($type = 'SUCCESS', $search = null, $created_date, 'single', $action);
            $failure = ProgramLog::getPackageImportCount($type = 'FAILURE', $search = null, $created_date, 'single', $action);
            $replace = [$to, $total, $success, $failure, $site_name];
        } else {
            $find = ['<EMAIL>', '<REASON>', '<SITE NAME>'];
            $replace = [$to, $reason, $site_name];
        }
        $email_details = Email::getEmail($slug);
        $subject = $email_details[0]['subject'];
        $body = $email_details[0]['body'];
        $body = str_replace($find, $replace, $body);
        Common::sendMailHtml($body, $subject, $to);
    }

    /**
     * @param string $status
     * @param string $slug
     * @param string $reason
     * @param string $action
     * @return nothing sends email to support email
     */
    public function packageImportEmail($status, $slug, $reason = null, $action)
    {
        $created_date = date('d-m-Y');
        $site_name = config('app.site_name');
        $to = config('app.import_email');
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
        $headers .= 'From:' . $site_name . "\r\n";
        if ($status == 'SUCCESS') {
            $find = ['<EMAIL>', '<TOTAL>', '<SUCCESS>', '<FAILURE>', '<SITE NAME>'];
            $total = ProgramLog::getPackageImportCount($type = 'ALL', $search = null, $created_date, 'collection', $action);
            $success = ProgramLog::getPackageImportCount($type = 'SUCCESS', $search = null, $created_date, 'collection', $action);
            $failure = ProgramLog::getPackageImportCount($type = 'FAILURE', $search = null, $created_date, 'collection', $action);
            $replace = [$to, $total, $success, $failure, $site_name];
        } else {
            $find = ['<EMAIL>', '<REASON>', '<SITE NAME>'];
            $replace = [$to, $reason, $site_name];
        }
        $email_details = Email::getEmail($slug);
        $subject = $email_details[0]['subject'];
        $body = $email_details[0]['body'];
        $body = str_replace($find, $replace, $body);
        Common::sendMailHtml($body, $subject, $to);
    }

    /**
     * @param string $status
     * @param string $slug
     * @param string $reason
     * @param string $action
     * @return nothing sends email to support email
     */
    public function enrolUsergroupToPackageEmail($status, $slug, $reason = null, $action)
    {
        $created_date = date('d-m-Y');
        $site_name = config('app.site_name');
        $to = config('app.import_email');
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
        $headers .= 'From:' . $site_name . "\r\n";
        if ($status == 'SUCCESS') {
            $find = ['<EMAIL>', '<TOTAL>', '<SUCCESS>', '<FAILURE>', '<SITE NAME>'];
            $total = EnrolLog::getPackageUsergroupImportCount($type = 'ALL', $search = null, $created_date, 'collection', 'usergroup', $action);
            $success = EnrolLog::getPackageUsergroupImportCount($type = 'SUCCESS', $search = null, $created_date, 'collection', 'usergroup', $action);
            $failure = EnrolLog::getPackageUsergroupImportCount($type = 'FAILURE', $search = null, $created_date, 'collection', 'usergroup', $action);
            $replace = [$to, $total, $success, $failure, $site_name];
        } else {
            $find = ['<EMAIL>', '<REASON>', '<SITE NAME>'];
            $replace = [$to, $reason, $site_name];
        }
        $email_details = Email::getEmail($slug);
        $subject = $email_details[0]['subject'];
        $body = $email_details[0]['body'];
        $body = str_replace($find, $replace, $body);
        Common::sendMailHtml($body, $subject, $to);
    }

    /**
     * @param string $status
     * @param string $slug
     * @param string $reason
     * @param string $action
     * @return nothing sends email to support email
     */
    public function enrolUserToChannelEmail($status, $slug, $reason = null, $action)
    {
        $created_date = date('d-m-Y');
        $site_name = config('app.site_name');
        $to = config('app.import_email');
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
        $headers .= 'From:' . $site_name . "\r\n";
        if ($status == 'SUCCESS') {
            $find = ['<EMAIL>', '<TOTAL>', '<SUCCESS>', '<FAILURE>', '<SITE NAME>'];
            $total = EnrolLog::getPackageUsergroupImportCount($type = 'ALL', $search = null, $created_date, 'single', 'user', $action);
            $success = EnrolLog::getPackageUsergroupImportCount($type = 'SUCCESS', $search = null, $created_date, 'single', 'user', $action);
            $failure = EnrolLog::getPackageUsergroupImportCount($type = 'FAILURE', $search = null, $created_date, 'single', 'user', $action);
            $replace = [$to, $total, $success, $failure, $site_name];
        } else {
            $find = ['<EMAIL>', '<REASON>', '<SITE NAME>'];
            $replace = [$to, $reason, $site_name];
        }
        $email_details = Email::getEmail($slug);
        $subject = $email_details[0]['subject'];
        $body = $email_details[0]['body'];
        $body = str_replace($find, $replace, $body);
        Common::sendMailHtml($body, $subject, $to);
    }

    /**
     * @param string $program_type
     * @param string $program_sub_type
     * @param string $status
     * @param string $date
     * @return nothing exports programs to csv file
     */
    public function exportPrograms($program_type, $program_sub_type, $status = 'ALL', $date = null, $action = 'ALL')
    {
        $this->program_repository->exportPrograms($program_type, $program_sub_type, $status, $date, $action);
    }

    /**
     * @param string $enrol_level
     * @param string $program_sub_type
     * @param string $status
     * @param string $date
     * @return nothing exports user-channel to csv file
     */
    public function userChannelExport($enrol_level, $program_sub_type, $status = 'ALL', $date = null)
    {
        $this->program_repository->userChannelExport($enrol_level, $program_sub_type, $status = 'ALL', $date = null);
    }

    /**
     * @param string $name
     * @param string $action
     * @return string $filename
     */
    public function getFileName($name, $action)
    {
        $now = time();
        $date = date('m-d-Y');
        $action = strtolower($action);
        $filename = $action . '-' . $date . '-' . $now . '-' . $name;
        return $filename;
    }

    /**
     * @param array $logdata
     * @param string $type
     * @param string $status
     * @param string $slug
     * @param string $action
     * @param int $cron
     * @return nothing sends email & insert log
     */
    public function postProgramLog($logdata, $type, $status, $slug, $action, $cron)
    {
        $logdata['type'] = 'file';
        $logdata['action'] = $action;
        $record = $this->getErpLogdata($logdata, $type, $status, $cron);
        ProgramLog::getInsertErpProgramLog($record);
        if($type == 'collection') {
        $this->packageImportEmail($status, $slug, $logdata['error_msgs'], $action);   
        }
        else {
        $this->channelImportEmail($status, $slug, $logdata['error_msgs'], $action);   
        }
    }

    /**
     * @param array $logdata
     * @param string $type
     * @param string $status
     * @param string $slug
     * @param string $action
     * @param int $cron
     * @return nothing sends email & insert log
     */
    public function postChannelLog($logdata, $type, $status, $slug, $action, $cron)
    {
        $logdata['type'] = 'file';
        $logdata['action'] = $action;
        $record = $this->getErpLogdata($logdata, $type, $status, $cron);
        ProgramLog::getInsertErpProgramLog($record);
        $this->channelImportEmail($status, $slug, $logdata['error_msgs'], $action);
    }

    /**
     * @param array $logdata
     * @param string $type
     * @param string $status
     * @param string $slug
     * @param string $action
     * @param int $cron
     * @return nothing sends email & insert log
     */
    public function postUsergroupPackLog($logdata, $type, $status, $slug, $action, $cron)
    {
        $logdata['type'] = 'file';
        $logdata['enrol_level'] = 'usergroup';
        $logdata['action'] = $action;
        $record = $this->getErpLogdata($logdata, $type, $status, $cron);
        EnrolLog::InsertErpEnrolLog($record);
        $this->enrolUsergroupToPackageEmail($status, $slug, $logdata['error_msgs'], $action);
    }

    /**
     * {@inheritdoc}
     */
    public function postCronUserChannelLog($logdata, $type, $status, $slug, $action)
    {
        $logdata['type'] = 'file';
        $logdata['enrol_level'] = 'user';
        $logdata['action'] = $action;
        $record = $this->getCronErpLogdata($logdata, $type, $status);
        EnrolLog::InsertErpEnrolLog($record);
        $this->enrolUserToChannelEmail($status, $slug, $logdata['error_msgs'], $action);
    }

    /**
     * @param array $logdata
     * @param string $type
     * @param string $status
     * @param string $slug
     * @param string $action
     * @param int $cron
     * @return nothing sends email & insert log
     */
    public function postUserChannelLog($logdata, $type, $status, $slug, $action, $cron)
    {
        $logdata['type'] = 'file';
        $logdata['enrol_level'] = 'user';
        $logdata['action'] = $action;
        $record = $this->getErpLogdata($logdata, $type, $status, $cron);
        EnrolLog::InsertErpEnrolLog($record);
        $this->enrolUserToChannelEmail($status, $slug, $logdata['error_msgs'], $action);
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramsBySearch($search, $is_ug_rel, $start, $limit)
    {
        return $this->program_repository->getProgramsBySearch($search, $is_ug_rel, $start, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function getPackagesBySearch($search, $is_ug_rel, $start, $limit)
    {
        return $this->program_repository->getPackagesBySearch($search, $is_ug_rel, $start, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageProgramsBySearch($search = '', $package_id = 0, $start = 0, $limit = 500)
    {
        return $this->program_repository->getPackageProgramsBySearch($search, $package_id, $start, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function getCFDetailsById($feed_id)
    {
        return $this->program_repository->getCFDetailsById($feed_id);
    }

    public function getCoursesBySearchKey($search_key, $course_id = 0, $start = 0, $limit = 500)
    {
        return $this->program_repository->getCoursesBySearchKey($search_key, $course_id, $start, $limit);
    }

    public function getProgramById($program_id = 0)
    {
        if ($program_id > 0) {
            return $this->program_repository->getProgramById($program_id);
        } else {
            return null;
        }
    }
    public function getVisibleProgramids($program_ids)
    {
       return $this->program_repository->getVisibleProgramids($program_ids)->pluck('program_id')->toArray();
    }

    /**
     * using this method system will return all the programs that are assigned to this specific user
     */
    public function getAllProgramsAssignedToUser($user_id)
    {
        $entity = new \StdClass;
        $entity->channel_ids = $entity->package_ids = $entity->package_channel_ids = [];
        $enrollments = $this->userRepository->getUserEntities((int)$user_id, ["entity_type" =>  [UserEntity::PROGRAM, UserEntity::PACKAGE, UserEntity::BATCH] ]);
        $entity->enrollments = $enrollments;
        if (!$enrollments->isEmpty()) {
            $entity->channel_ids = $enrollments->whereIn('entity_type', [UserEntity::PROGRAM, UserEntity::BATCH])->pluck('entity_id')->all();
            $entity->channel_ids = $this->getVisibleProgramids($entity->channel_ids);
            $package_ids = $enrollments->where('entity_type', UserEntity::PACKAGE)->pluck('entity_id')->all();
            if (!empty($package_ids)) {
                $entity->package_ids = $package_ids;
                $packages = $this->packageRepository->get(['in_ids' => $package_ids]);
                if (!$packages->isEmpty()) {
                    $entity->package_channel_ids = array_unique(array_collapse($packages->pluck('program_ids')->all()));
                    $entity->package_channel_ids = $this->getVisibleProgramids($entity->package_channel_ids);
                    $entity->channel_ids = array_merge($entity->channel_ids, $entity->package_channel_ids);
                }
            }
        } else {
            throw new NoProgramAssignedException();
        }
        return collect($entity);
    }

    /**
     * generateProgramSlug prepares program slug
     * @param string $name
     * @param string $shortname
     * @returns program slug
     */
    public function generateProgramSlug($name, $shortname)
    {
        return $this->program_repository->generateProgramSlug($name, $shortname);
    }
    /**
     * Method to download update channel/package csv file 
     * @returns update channel/package csv file with fields as headers
     */
    public function downloadProgramTemplate($type,$subtype,$action)
    {
        $this->program_repository->downloadProgramTemplate($type,$subtype,$action);
    }
    /**
     * Method to get all program fields to insert update program log
     * @param array $logdata
     * @param array $fields
     * @returns all fields with values
     */
    public function getMissingProgramFields($logdata,$fields)
    {   
        foreach($logdata as $key => $value)
        {
            $record[$key] = $value;
        }
        $record['sellable'] = '';
        return $record;
    }
    /**
     * Method to update program details
     * @param array $program_data
     * @param string $old_slug
     * @param string $new_slug
     * @param int $cron
     * @returns nothing updates program information
     */
    public function updateProgramDetails($program_data,$old_slug,$new_slug,$cron)
    {
        return $this->program_repository->updateProgramDetails($program_data,$old_slug,$new_slug,$cron);
    }
    /**
     * {@inheritdoc}
     */
    public function exportChannels(
        $program_type,
        $program_sub_type,
        $filter,
        $custom_field_name,
        $custom_field_value
    )
    {
        $this->program_repository->exportChannels(
            $program_type,
            $program_sub_type,
            $filter,
            $custom_field_name,
            $custom_field_value
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getUserProgramSlugs()
    {
        $channel_slugs = [];
        if (!is_admin_role(Auth::user()->role)) {
            $channel_slugs = $this->getProgramSlugs();
        } else {
            $channel_slugs = $this->program_repository->get()->pluck('program_slug')->all();
        }
        return $channel_slugs;
    }

    /**
     * {inheritdoc}
     */
    public function getProgramSlugs()
    {
        $channel_slugs = [];
        $entities = $this->getAllProgramsAssignedToUser(Auth::user()->uid);
        $channel_ids = $entities['channel_ids'];
        $channel_slugs = $this->program_repository->getProgramsByAttribute('program_id', $channel_ids, ['program_slug'])->pluck('program_slug')->all();
        return $channel_slugs;
    }
    
    /**
     * {inheritdoc}
     */
    public function getNewProgramsCount(array $program_ids, array $date)
    {
        return $this->program_repository->getNewProgramsCount($program_ids, $date);
    }
    
    /**
     * {inheritdoc}
     */
    public function countActivePrograms(array $program_ids, array $date)
    {
        return $this->program_repository->getActiveProgramsCount($program_ids, $date);
    }

    /**
     * {inheritdoc}
     */
    public function getUsersPermittedCFByIds(array $program_ids)
    {
        return $this->program_repository->getUsersPermittedCFByIds($program_ids);
    }

    /**
     * inheritdoc
     */
    public function getAboutExpirePrograms($date)
    {
        return $this->program_repository->getAboutExpirePrograms([array_get($date, 'start', 0), array_get($date, 'end', 0)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramPacketsQuizzs($program_ids)
    {
        return $this->program_repository->getProgramPacketsQuizzs($program_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramsDetailsById($program_ids)
    {
        return $this->program_repository->getProgramsDetailsById($program_ids)->keyBy('program_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getFtpConnectionDetails()
    {
        return $this->program_repository->getFtpConnectionDetails();
    }

    /**
     * {@inheritdoc}
     */
    public function getFtpDirUsergroupToPackageDetails($ftp_conn_id)
    {
        return $this->program_repository->getFtpDirUsergroupToPackageDetails($ftp_conn_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getUsergroupToPackageFileColumns($ftp_connection_details, $ftp_dir_details)
    {
        return $this->program_repository->getUsergroupToPackageFileColumns($ftp_connection_details, $ftp_dir_details);
    }

    /**
     * {@inheritdoc}
     */
    public function validateErpEnrolRules($csvrowData)
    {
        return $this->program_repository->validateErpEnrolRules($csvrowData);
    }

    /**
     * {@inheritdoc}
     */
    public function getFtpDirUserToChannelDetails($ftp_conn_id)
    {
        return $this->program_repository->getFtpDirUserToChannelDetails($ftp_conn_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserToChannelFileColumns($ftp_connection_details, $ftp_dir_details)
    {
        return $this->program_repository->getUserToChannelFileColumns($ftp_connection_details, $ftp_dir_details);
    }

    /**
     * {@inheritdoc}
     */
    public function getFtpDirPackageDetails($ftp_conn_id)
    {
        return $this->program_repository->getFtpDirPackageDetails($ftp_conn_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageFileColumns($ftp_connection_details, $ftp_dir_details)
    {
        return $this->program_repository->getPackageFileColumns($ftp_connection_details, $ftp_dir_details);
    }

    /**
     * {@inheritdoc}
     */
    public function validateErpPackageRules($csvrowData, $type)
    {
        return $this->program_repository->validateErpPackageRules($csvrowData, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function getFtpDirPackageUpdateDetails($ftp_conn_id)
    {
        return $this->program_repository->getFtpDirPackageUpdateDetails($ftp_conn_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatePackageFileColumns($ftp_connection_details, $ftp_dir_details)
    {
        return $this->program_repository->getUpdatePackageFileColumns($ftp_connection_details, $ftp_dir_details);
    }

    /**
     * {@inheritdoc}
     */
    public function getFtpDirChannelDetails($ftp_conn_id)
    {
        return $this->program_repository->getFtpDirChannelDetails($ftp_conn_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelFileColumns($ftp_connection_details, $ftp_dir_details)
    {
        return $this->program_repository->getChannelFileColumns($ftp_connection_details, $ftp_dir_details);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdateChannelFileColumns($ftp_connection_details, $ftp_dir_details)
    {
        return $this->program_repository->getUpdateChannelFileColumns($ftp_connection_details, $ftp_dir_details);
    }

    /**
     * {@inheritdoc}
     */
    public function getFtpDirChannelUpdateDetails($ftp_conn_id)
    {
        return $this->program_repository->getFtpDirChannelUpdateDetails($ftp_conn_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getUsergroupUserRelationBySlug($program_slug)
    {
        return $this->program_repository->getUsergroupUserRelationBySlug($program_slug);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllProgramsAssignedToSiteAdmin()
    {
        $channel_ids = $this->program_repository->get()->lists('program_id')->toArray();
        $package_id = $this->packageRepository->get()->lists('package_id')->toArray();
        $package_channel_ids = array_unique(array_collapse($this->packageRepository->get()->pluck('program_ids')->all()));
        $channels_list['channel_ids'] = $channel_ids;
        $channels_list['package_id'] = $package_id;
        $channels_list['package_channel_ids'] = $package_channel_ids;
        return $channels_list;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getUserEnrollmentsAndCategories($uid, $page_no)
    {
        $data = $channels_list = [];
        $package_name_flag = true;
        if (is_admin_role(Auth::user()->role)) {
            $channels_list = $this->getAllProgramsAssignedToSiteAdmin();
            $catergory_with_program = $this->getAllCategoryWithChannel($channels_list, $page_no, $package_name_flag);

            $data['enrollment_ids'] = $channels_list;
            $data['catergory_with_program'] = $catergory_with_program;
        } else {
            $channels_list = $this->getAllProgramsAssignedToUser(Auth::user()->uid);
            $catergory_with_program = $this->getAllCategoryWithChannel($channels_list, $page_no, $package_name_flag);
            $data['enrollment_ids'] = $channels_list;
            $data['catergory_with_program'] = $catergory_with_program;
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramDetailsBySlug($feed_slugs)
    {
        $programs = $this->program_repository->pluckFeedDetails($feed_slugs);
        $program_data = $package_details = [];
        //package details
        $package_ids = collect($programs)->pluck('package_ids')->all();
        $package_ids = array_filter(array_flatten($package_ids), 'strlen');
        $filter_params = ['in_ids' => $package_ids];
        $packages = $this->packageService->getPackages($filter_params, 
            ['package_title', 'package_slug', 'package_id']);
        //program details
        foreach ($programs as $value) {
           $program_slug = array_get($value, 'program_slug');
           $program_data[$program_slug]['program_title'] = array_get($value, 'program_title');
           $program_data[$program_slug]['program_type'] = array_get($value, 'program_type');
           $program_data[$program_slug]['parent_id'] = array_get($value, 'parent_id');
           $program_package_ids = array_get($value, 'package_ids', []);
           $package_details[$program_slug]['package_titles'] = collect($packages)
            ->whereIn('package_id', $program_package_ids)
            ->pluck('package_title')->all();
        }
        $data = ['program_data' => $program_data, 'package_data' => $package_details];
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramByChannelSlug($attribute, $value, $status)
    {
        return $this->program_repository->getProgramByChannelSlug($attribute, $value, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function getImportUserToChannelMapping()
    {
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
            return redirect('cp/contentfeedmanagement/import-user-to-channel')->withInput()
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
            $errorData = null;

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
                    $mandatoryCols = [strtolower(trans('admin/user.username')) , strtolower(trans('admin/program.name')), trans('admin/program.channel_shortname')];
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
                        $errorData = $this->validateUserToChannelRules($excelRowData);
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
                                $programid = $this->getProgramId(array_get($excelRowData, 'name'), array_get($excelRowData, 'shortname'), 'content_feed', 'single');
                                $userid = User::where('username', '=', strtolower(array_get($excelRowData, 'username')))->value('uid');
                                /*---Insert data in program,users,transactions,transaction_details collection---*/
                                $this->enrolUserToProgram($programid, $userid, array_get($excelRowData, 'name'), array_get($excelRowData, 'shortname'), 'content_feed', 'single', 'user', 0);
                                event(
                                    new EntityEnrollmentByAdminUser(
                                        $userid,
                                        UserEntity::PROGRAM,
                                        $programid
                                    )
                                );

                                $program_context_data = $this->roleService->getContextDetails(Contexts::PROGRAM);
                                $learner_role = $this->roleService->getRoleDetails(SystemRoles::LEARNER);

                                $this->roleRepository->mapUserAndRole(
                                    $userid,
                                    $program_context_data["id"],
                                    $learner_role["id"],
                                    $programid
                                );

                                $success_count = $success_count + 1;
                                $emailTemp[$i - 1]['record_status'] = 'Success';
                                $emailTemp[$i - 1]['errors'] = '';
                                $updateFlag = 1;
                            }

                    }else{ 
                        return redirect('cp/contentfeedmanagement/import-user-to-channel') 
                            ->with('error', trans('admin/user.bulk_import_update_column_error')); 
                    } 
                }
            }

            $result = $emailTemp;
            unset($emailTemp[0]);

            if ($errorFlag) {
                Session::put('userchannelxlsreport', $result);
                Session::flash('errorflag', 1);
                return redirect('cp/contentfeedmanagement/import-user-to-channel');
            } else {
                Input::flush();
                Session::forget('userchannelxlsreport');
                Session::forget('errorflag');

                if ($isEmpty == 0) {
                    return redirect('cp/contentfeedmanagement/import-user-to-channel')
                        ->with('error', trans('admin/user.bulk_import_empty'));
                } elseif ($updateFlag == 1) {
                    return redirect('cp/contentfeedmanagement/import-user-to-channel')
                        ->with('success', trans('admin/program.bulk_import_user_channel_success'));
                } else {
                    
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateUserToChannelRules($csvrowData)
    {   
        
        $rules = [
            'username' => 'Required|checkusername:' . $csvrowData['username'] . '|userexist:'
            . $csvrowData['name'] . ',' . $csvrowData['shortname'] . ',' . $csvrowData['username']
            . '|checkactiveuser:' . $csvrowData['username'] . '|checkmanyusers:'. '',
            'name' => 'Required|checkprogram:' . $csvrowData['name'] . ',' . $csvrowData['shortname'] . '|checkshortname:'. $csvrowData['name'] . ',' . $csvrowData['shortname'] . '',
            'shortname' => 'min:3',

        ];

        Validator::extend('userexist', function ($attribute, $value, $parameters) {
            if (!(strpos($value, ',') !== false)) {
                $parameters[0] = preg_replace('/[^\w ]+/', '', array_get($parameters, 0));
                $parameters[0] = strtolower(preg_replace('/ +/', '-', $parameters[0]));
                if (!empty(array_get($parameters, 1))) {
                    $parameters[1] = preg_replace('/[^\w ]+/', '', array_get($parameters, 1));
                    $parameters[1] = strtolower(preg_replace('/ +/', '-', $parameters[1]));
                    $slug = "content-feed" . '-' . $parameters[0] . '-' . $parameters[1];
                } else {
                    $slug = "content-feed" . '-' . $parameters[0];
                }
                $program = $this->getProgramByChannelSlug('program_slug', $slug, ProgramStatus::DELETED)->toArray();
                $user = $this->userRepository->getUserDetailsByUserName(strtolower(array_get($parameters, 2)), ProgramStatus::DELETED)->toArray();
                if (!empty($program)) {
                    $program_details = array_get($program, 0, []);
                    $programid = array_get($program_details, 'program_id');
                } else {
                    $programid = ' ';
                }
                if (!empty($user)) {
                    $user_details = array_get($user, 0, []);
                    $groupid = array_get($user_details, 'uid');
                } else {
                    $groupid = ' ';
                }
                $transactions = $this->transactionDetailsRepository->getDetailsByProgramDetails($programid, $groupid)->toArray();
                if (empty($transactions)) {
                    return true;
                }
                return false;
            }
            return true;
        });

        Validator::extend('checkprogram', function ($attribute, $value, $parameters) {
            $parameters[0] = preg_replace('/[^\w ]+/', '', array_get($parameters, 0));
            $parameters[0] = strtolower(preg_replace('/ +/', '-', $parameters[0]));
            if (!empty(array_get($parameters, 1))) {
                $parameters[1] = preg_replace('/[^\w ]+/', '', array_get($parameters, 1));
                $parameters[1] = strtolower(preg_replace('/ +/', '-', $parameters[1]));
                $slug = "content-feed" . '-' . $parameters[0] . '-' . $parameters[1];
            } else {
                $slug = "content-feed" . '-' . $parameters[0];
            }
            $returnval = $this->getProgramByChannelSlug('program_slug', $slug, ProgramStatus::DELETED)->toArray();
            if (!empty($returnval)) {
                return true;
            }
            return false;
        });

        Validator::extend('checkusername', function ($attribute, $value, $parameters) {
            $username = strtolower(array_get($parameters, 0));
            $returnval = $this->userRepository->getUserDetailsByUserName($username, ProgramStatus::DELETED)->toArray();
            if (!empty($returnval)) {
                return true;
            }
            return false;
        });

        Validator::extend('checkactiveuser', function ($attribute, $value, $parameters) {
            $username = strtolower(array_get($parameters, 0));
            if (!empty($this->userRepository->getUserDetailsByUserName($username, ProgramStatus::DELETED)->toArray())) {
                $returnval = $this->userRepository->getActiveUserCount($username);
                if ($returnval == 0) {
                    return false;
                }
                return true;
            } 
            return true;
        });

        Validator::extend('checkshortname', function ($attribute, $value, $parameters) {
            if (empty(array_get($parameters, 1))) {
                $parameters[0] = preg_replace('/[^\w ]+/', '', array_get($parameters, 0)); 
                $parameters[0] = strtolower(preg_replace('/ +/', '-', $parameters[0])); 
                $slug = "content-feed" . '-' . $parameters[0]; 
                $program_details = $this->getProgramByChannelSlug('title_lower', strtolower($value), ProgramStatus::DELETED)->toArray();
                if (!empty($program_details)) {
                    $program = $this->getProgramByChannelSlug('program_slug', $slug, ProgramStatus::DELETED)->toArray();
                    if (empty($program)) { 
                        return false; 
                    } 
                    return true; 
                } 
                return true; 
            }
            return true; 
        });

        Validator::extend('checkmanyusers', function ($attribute, $value, $parameters) {
            if (strpos($value, ',') !== false) {
                return false;
            }
            return true;
        });
        $messages = [
            'checkusername' => trans('admin/program.check_username'),
            'checkprogram' => trans('admin/program.check_program'),
            'min' => trans('admin/program.shortname'),
            'userexist' => trans('admin/program.check_user'),
            'checkactiveuser' => trans('admin/program.check_active_user'),
            'checkshortname' => trans('admin/program.check_channel_shortname'),
            'checkmanyusers' => trans('admin/program.check_many_users'),
        ];

        return $this->customErpPackageValidate($csvrowData, $rules, $messages);
    }

    /**
     * Method to validate program
     * @param array $input
     * @param array $rules
     * @param array $messages
     * @return array with validation messages
     */
    public function customErpPackageValidate($input, $rules, $messages = [])
    {
        $validation = Validator::make($input, $rules, $messages);
        if ($validation->fails()) {
            return $validation->messages();
        } else {
            return false;
        }
    }

    public function getAllDetailsOfProgram($programs, $order_by, $sub_program_slugs)
    {
        $subscriptionCollection = collect(Auth::user()->subscription);
        $subsCripedProgramCollection = $subscriptionCollection->groupBy('program_id');
        $subsProgId = $subscriptionCollection->pluck('program_id')->toArray();
        $programs = collect($programs);
        
        $packets = $this->post_service->getPacketsUsingSlug($sub_program_slugs, $order_by);
        $category = $this->category_repository->filter($programs->pluck('program_id')->toArray());
        $parent_programs = $this->checkprogramTypeIsCourse($programs);
        $programs = $programs->map(function ($program, $key) use ($subsCripedProgramCollection, $subsProgId, $order_by, $packets, $category, $parent_programs) {
           if($program['status'] == 'ACTIVE') {
                 if (in_array($program['program_slug'], array_keys($packets))) {
                $program_packets = $packets[$program['program_slug']];
                $packet_count = collect($packets[$program['program_slug']])->count();
            }
            
            $s_date = $program['program_startdate'];
            $e_date = $program['program_enddate'];
            $assigned_cat_details = $category->whereIn('category_id', $program['program_categories'])->toArray();
            if (in_array($program['program_id'], $subsProgId)) {
                $spcificProg = $subsCripedProgramCollection->get($program['program_id']);
                $start_date = array_get($spcificProg, '0.start_time');
                $end_date = array_get($spcificProg, '0.end_time');
                
                if (!is_null($spcificProg) && isset($start_date) && isset($end_date)) {
                       $s_date = $start_date;
                       $e_date = $end_date;
                }
            }
                
            if ($program['program_type'] == 'course' && (isset($program['parent_id']) && $program['parent_id'] > 0 )) {
                $course_batch_name = $program['program_title'];
                if(in_array($program['parent_id'], $parent_programs['parents_ids'])) {
                    $parent_name = $parent_programs['parent_details'][$program['parent_id']][0]['program_title'];
                    $course_batch_name = $parent_name." - " .$program['program_title'];
                }
                
            }
            $program['assigned_cat_details'] = $assigned_cat_details;
            $program['packets'] = isset($program_packets) ? $program_packets : [];
            $program['packet_count'] = !empty($packet_count) ? $packet_count : 0;
            $program['course_batch_name'] = isset($course_batch_name) ? $course_batch_name : '';
            $program['s_date'] = $s_date;
            $program['e_date'] = $e_date;
            return $program;
           }
        });
        return $programs;
    }

    public function checkprogramTypeIsCourse($programs)
    {
        $parent_ids = $programs->pluck('parent_id')->filter()->toArray();
         $parent_details = $this->program_repository->getProgram($parent_ids)->groupBy('program_id')->toArray();
         $parent_programs['parents_ids'] = array_keys($parent_details);
         $parent_programs['parent_details'] = $parent_details;
         return $parent_programs;
    }

    /**
     * {inheritdoc}
     */
    public function getNewPrograms(array $program_ids, array $date, $start, $limit)
    {
        return $this->program_repository->getNewPrograms($program_ids, $date, $start, $limit);
    }

    /**
     * {inheritdoc}
     */
    public function getActiveProgramsDetails(array $program_ids, $start, $limit)
    {
        $programs = $this->program_repository->getActiveProgramsDetails($program_ids, $start, $limit)->keyBy('program_id');
        $course_ids = array_filter($programs->lists('parent_id')->toArray());
        $courses = $this->program_repository->getActiveProgramsDetails($course_ids, $start, $limit)->keyBy('program_id');
        $data = [];
        foreach ($programs as $program) {
            $course_name = null;
            if (is_null($program->parent_id) || $program->parent_id <= 0) {
                $course_name = $program->program_title;
            } else {
                $course_name =  $program->program_title. ' - '. $courses->get($program->parent_id)->program_title;
            }
            $data[] = [
                $course_name,
                Carbon::parse($program->created_at)->format('d/m/Y')
            ];
        }
        return $data;
    }

    /**
     * {inheritdoc}
     */
    public function getProgramsBySlugs(array $program_slugs, array $columns = [])
    {
        return $this->program_repository->getProgramsBySlugs($program_slugs, $columns);
    }

    /**
     * @param  array $program_ids
     * @inheritdoc
     */
    public function countActiveChannels($program_ids)
    {
        return $this->program_repository->countActiveChannels($program_ids);
    }

    /**
     * @param  array $program_ids
     * @inheritdoc
     */
    public function countInActiveChannels($program_ids)
    {
        return $this->program_repository->countInActiveChannels($program_ids);
    }

    public function getFilterPrograms($filter_params, $columns)
    {
        return $this->program_repository->get($filter_params, $columns);
    }

    /**
     * @param  array $channel_ids
     * @inheritdoc
     */
    public function getOnlyProgramIds($channel_ids)
    {
        return $this->program_repository->getOnlyProgramIds($channel_ids);
    }

    /**
     * @inheritdoc
     */
    public function getAllUndeletedPrograms()
    {
        return $this->program_repository->getAllUndeletedPrograms();
    }

    /**
     * @inheritdoc
     */
    public function getAllActiveChannels()
    {
        return $this->program_repository->getAllActiveChannels();
    }

    /**
     * @inheritdoc
     */
    public function getChannelByProgramId($program_id)
    {
        return $this->program_repository->getChannelByProgramId($program_id);
    }

    /**
     * @inheritdoc
     */
    public function getAllProgramByIDOrSlug($type = 'all', $slug = '', $filter_params = [])
    {
        return $this->program_repository->getAllProgramByIDOrSlug($type, $slug, $filter_params);
    }
}
