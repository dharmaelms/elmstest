<?php

namespace App\Services\Package;

use Auth;
use App\Enums\Cron\CronBulkImport;
use App\Enums\Program\ProgramType;
use App\Enums\RolesAndPermissions\Contexts;
use App\Enums\RolesAndPermissions\SystemRoles;
use App\Exceptions\Package\NoPackageAssignedException;
use App\Exceptions\Package\CannotUnAssignProgramsException;
use App\Model\Package\Repository\IPackageRepository;
use App\Model\Category\Repository\ICategoryRepository;
use App\Exceptions\Package\PackageNotFoundException;
use App\Model\Program\IProgramRepository;
use App\Model\User\Repository\IUserRepository;
use App\Model\UserGroup\Repository\IUserGroupRepository;
use App\Enums\User\UserEntity;
use App\Enums\User\UserStatus;
use App\Enums\UserGroup\UserGroupStatus;
use App\Services\Role\IRoleService;
use App\Model\ImportLog\Entity\PackageLog;
use App\Model\ImportLog\Entity\PackageEnrolLog;
use Log;
use App\Model\Email;
use App\Model\Common;

/**
 * Class PackageService
 *
 * @package App\Services\Package
 */
class PackageService implements IPackageService
{
    /**
     * @var IPackageRepository
     */
    private $package_repository;

    /**
     * @var App\Model\Category\Repository\ICategoryRepository
     */
    private $category_repository;

    /**
     * @var IUserRepository
     */
    private $userRepository;

    /**
     * @var IUserGroupRepository
     */
    private $userGroupRepository;
    /**
     * @var IProgramRepository
     */
    private $programRepository;
    /**
     * @var IRoleService
     */
    private $roleService;

    /**
     * PackageService constructor.
     * @param IPackageRepository $package_repository
     * @param IUserRepository $userRepository
     * @param IUserGroupRepository $userGroupRepository
     * @param IProgramRepository $programRepository
     * @param IRoleService $roleService
     */
    public function __construct(
        IPackageRepository $package_repository,
        ICategoryRepository $category_repository,
        IUserRepository $userRepository,
        IUserGroupRepository $userGroupRepository,
        IProgramRepository $programRepository,
        IRoleService $roleService
    ) {
        $this->package_repository = $package_repository;
        $this->category_repository = $category_repository;
        $this->userRepository = $userRepository;
        $this->userGroupRepository = $userGroupRepository;
        $this->programRepository = $programRepository;
        $this->roleService = $roleService;
    }

    /**
     * {@inheritdoc}
     */
    public function getPackages($filter_params = [], $columns = [])
    {
        return $this->package_repository->get($filter_params, $columns);
    }

    /**
     * @inheritDoc
     */
    public function getPackagesCount($filter_params = [])
    {
        return $this->package_repository->getCount($filter_params);
    }

    /**
     * {@inheritdoc}
     */
    public function createPackage($package_data = [])
    {
        return $this->package_repository->createPackage($package_data);
    }

    /**
     * {@inheritdoc}
     */
    public function updatePackage($package_id, $package_data = [])
    {
        return $this->package_repository->updatePackage($package_id, $package_data);
    }

    /**
     * {@inheritdoc}
     */
    public function deletePackage($package_id)
    {
        return $this->package_repository->deletePackage($package_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageBySlug($attribute, $value)
    {
        try {
            return $this->package_repository->findByAttribute($attribute, $value);
        } catch (PackageNotFoundException $e) {
            throw new PackageNotFoundException();
        }
    }

    /**
     * @inheritDoc
     */
    public function assignProgramsToPackage($package_id, $program_ids)
    {
        $package = $this->package_repository->mapPackageAndPrograms($package_id, $program_ids);

        $active_user_associations = $this->getPackageActiveUserRelations($package_id);

        if (!$active_user_associations->isEmpty()) {
            $context_data = $this->roleService->getContextDetails(Contexts::PROGRAM);
            $role_data = $this->roleService->getRoleDetails(SystemRoles::LEARNER);
            $active_user_associations->each(
                function ($package_user_relation) use ($program_ids, $context_data, $role_data) {
                    foreach ($program_ids as $program_id) {
                        $this->roleService->mapUserAndRole(
                            $package_user_relation->user_id,
                            $context_data["id"],
                            $role_data["id"],
                            $program_id
                        );
                    }
                }
            );
        }

        return $package;
    }

    /**
     * @inheritDoc
     */
    public function unAssignProgramsFromPackage($package_id, $program_ids)
    {
        $active_user_associations = $this->getPackageActiveUserRelations($package_id);

        if ($active_user_associations->isEmpty()) {
            return $this->package_repository->unMapPackageAndPrograms($package_id, $program_ids);
        } else {
            throw new CannotUnAssignProgramsException();
        }
    }

    /**
     * @inheritDoc
     */
    public function getPackageWithAssignedPrograms(
        $package_id,
        $program_filter_params = [],
        $package_columns = ["*"],
        $program_columns = ["*"]
    ) {
        $total_count = $this->package_repository->getPackageProgramsCount(
            $package_id,
            ["type" => ProgramType::CHANNEL]
        );

        $program_filter_params = array_merge($program_filter_params, ["type" => ProgramType::CHANNEL]);
        $filtered_count = $this->package_repository->getPackageProgramsCount(
            $package_id,
            array_except($program_filter_params, ["start", "limit", "order_by", "order_by_dir"])
        );

        $package_with_programs = $this->package_repository->findPackageWithPrograms(
            $package_id,
            $program_filter_params,
            $package_columns,
            $program_columns
        );

        return [
            "package" => $package_with_programs,
            "programs_total_count" => $total_count,
            "programs_filtered_count" => $filtered_count
        ];
    }

    /**
     * @inheritDoc
     */
    public function getPackageWithNoNAssignedPrograms(
        $package_id,
        $program_filter_params = [],
        $package_columns = ["*"],
        $program_columns = ["*"]
    ) {
        $package = $this->package_repository->find($package_id, $package_columns);
        $assigned_program_ids = $package->program_ids;
        $total_count = $this->programRepository->getCount(
            ["type" => ProgramType::CHANNEL, "not_in_ids" => $assigned_program_ids]
        );

        $program_filter_params = array_merge(
            $program_filter_params,
            ["type" => ProgramType::CHANNEL, "not_in_ids" => $assigned_program_ids]
        );

        $filtered_count = $this->programRepository->getCount(
            array_except($program_filter_params, ["start", "limit", "order_by", "order_by_dir"])
        );

        $programs = $this->programRepository->get($program_filter_params, $program_columns);

        return [
            "package" => $package,
            "programs" => $programs,
            "programs_total_count" => $total_count,
            "programs_filtered_count" => $filtered_count
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageUsers($package_id, $filter_params = [])
    {
        $data = [];
        try {
            $totalUsersCount = 0;
            $user_ids = [];
            $package = $this->package_repository->find($package_id);
            $package = $package->toArray();
            $assigned_user_ids = array_get($package, 'user_ids', []);
            switch ($filter_params["enrollment_status"]) {
                case "ASSIGNED":
                    $user_ids = $assigned_user_ids;
                    $totalUsersCount = count($user_ids);
                    break;
                case "UNASSIGNED":
                    $registered_user_ids = $this->userRepository->getRegisteredUsers(
                        ["status" => [UserStatus::ACTIVE]]
                    )->pluck("uid")->toArray();
                    $user_ids = array_diff($registered_user_ids, $assigned_user_ids);
                    $totalUsersCount = count($user_ids);
                    break;
                default:
                    break;
            }

            $filter_params = array_merge($filter_params, ["user_ids" => $user_ids]);

            $filtered_users = $this->userRepository->get($filter_params);

            array_forget($filter_params, ["start", "limit"]);

            $filteredUsersCount = $this->userRepository->getUsersCount($filter_params);

            $filtered_users->each(
                function ($user, $key) use (&$data) {
                    $data[$key]["id"] = $user->uid;
                    $data[$key]["firstname"] = $user->firstname;
                    $data[$key]["lastname"] = $user->lastname;
                    $data[$key]["username"] = $user->username;
                    $data[$key]["email"] = $user->email;
                    $data[$key]["status"] = $user->status;
                    $data[$key]["created_at"] = $user->created_at;
                }
            );
        } catch (PackageNotFoundException $e) {
            Log::error($e->getMessage());
        }

        return [
            "total_users_count" => $totalUsersCount,
            "filtered_users_count" => $filteredUsersCount,
            "data" => $data
        ];
    }

    public function getPackageUserGroups($package_id, $filter_params = [])
    {
        try {
            $totalUserGroupsCount = 0;
            $user_group_ids = [];
            $package = $this->package_repository->find($package_id);
            $package = $package->toArray();
            $assigned_user_group_ids = array_get($package, 'user_group_ids', []);
            switch ($filter_params["enrollment_status"]) {
                case "ASSIGNED":
                    $user_group_ids = $assigned_user_group_ids;
                    $totalUserGroupsCount = count($user_group_ids);
                    break;
                case "UNASSIGNED":
                    $registered_user_group_ids = $this->userGroupRepository->get(
                        ["status" => [UserGroupStatus::ACTIVE]]
                    )->pluck("ugid")->toArray();
                    $user_group_ids = array_diff($registered_user_group_ids, $assigned_user_group_ids);
                    $totalUserGroupsCount = count($user_group_ids);
                    break;
                default:
                    break;
            }

            $filter_params = array_merge($filter_params, ["ugid" => $user_group_ids]);

            $filtered_user_groups = $this->userGroupRepository->get($filter_params);

            array_forget($filter_params, ["start", "limit"]);

            $filteredUserGroupsCount = count($this->userGroupRepository->get($filter_params)->toArray());
            
        } catch (PackageNotFoundException $e) {
            Log::error($e->getMessage());
        }

        return [
            "total_user_groups_count" => $totalUserGroupsCount,
            "filtered_user_groups_count" => $filteredUserGroupsCount,
            "data" => $filtered_user_groups
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function mapUserAndPackage($package_id, $user_ids)
    {
        return $this->package_repository->mapUserandPackage($package_id, $user_ids);
    }
    
    /**
     * {@inheritdoc}
     */
    public function unMapUserAndPackage($package_id, $user_ids)
    {
        return $this->package_repository->unmapUserandPackage($package_id, $user_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function mapUserGroupAndPackage($package_id, $user_group_ids)
    {
        return $this->package_repository->mapUserGroupAndPackage($package_id, $user_group_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function unMapUserGroupAndPackage($package_id, $user_group_ids)
    {
        return $this->package_repository->unMapUserGroupAndPackage($package_id, $user_group_ids);
    }

    /**
     * @inheritDoc
     */
    public function getPackageActiveUserRelations($package_id)
    {
        return $this->userRepository->getActiveUserEntityRelations(
            ["entity_type" => UserEntity::PACKAGE, "entity_id" => $package_id]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageList($page, $limit, $filter, $categories = [], $others = [])
    {
        $package_ids = $this->getUserPackages();
        $results = collect();

        if (!empty($categories)) {
            $categories = explode(',', $categories);
            $categories = $this->category_repository->getPackagesByAttribute('slug', $categories);
            $category_packages = array_unique(
                array_flatten($categories->pluck('package_ids')->all())
            );
            $package_ids = array_intersect($package_ids, $category_packages);
        }

        if (!empty($others)) {
            $other_packages = $this->package_repository->getPackageWithoutCategories();
            $other_package_ids = $other_packages->pluck('package_id')->all();
            if (empty($categories)) {
                $package_ids = $other_package_ids;
            } else {
                $package_ids = array_merge($other_package_ids, $package_ids);
            }
        }

        if (!empty($package_ids)) {
            $filter_params = $this->getFilter($filter);
            $filter_params['in_ids'] = $package_ids;
            $filter_params['start'] = ($page * $limit) - $limit;
            $filter_params['limit'] = $limit;
            $packages = $this->package_repository->get($filter_params);
            $results = $packages->map(function ($package) {
                $data = new \StdClass;
                $data->title = $package->package_title;
                $data->slug = $package->package_slug;
                $data->description = $package->package_description;
                $data->start_date = $package->package_startdate;
                $data->enddate = $package->package_enddate;
                $data->cover_image = $package->package_cover_media;
                if (isset($package->category_ids) && !empty($package->category_ids)) {
                    $data->categories = $this->category_repository->getPackagesByAttribute('category_id', $package->category_ids)->pluck('category_name')->all();
                }
                if (isset($package->program_ids) && !empty($package->program_ids)) {
                    $ids = collect($package->program_ids)->map(function ($id) {
                        return (int)$id;
                    });
                    $data->channels = $this->programRepository
                        ->getProgramsByAttribute('program_id', $ids)
                        ->map(function ($program) {
                            return ['title' => $program->program_title, 'slug' => $program->program_slug];
                        });
                }
                return $data;
            });
        } else {
            throw new NoPackageAssignedException;
        }
        if ($results->isEmpty()) {
            throw new NoPackageAssignedException;
        }
        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserPackages()
    {
        $package_ids = [];
        if (!is_admin_role(Auth::user()->role)) {
            $assigned_packages = $this->userRepository->getUserEntities(Auth::user()->uid, ["entity_type" =>  [UserEntity::PACKAGE] ]);
            if (!$assigned_packages->isEmpty()) {
                $package_ids = $assigned_packages->pluck('entity_id')->all();
            }
        } else {
            $package_ids = $this->package_repository->get()->pluck('package_id')->all();
        }
        return $package_ids;
    }

    /**
     * Method to get column and order by for given filter
     *
     * @param string $filter
     * @return array
     */
    public function getFilter($filter)
    {
        switch ($filter) {
            case 'new ':
                $column = 'updated_at';
                $condition = 'desc';
                break;
            case 'old':
                $column = 'updated_at';
                $condition = 'asc';
                break;
            case 'a-z':
                $column = 'title_lower';
                $condition = 'asc';
                break;
            case 'z-a':
                $column = 'title_lower';
                $condition = 'desc';
                break;
            default:
                $column = 'updated_at';
                $condition = 'desc';
                break;
        }
        return ['order_by' => $column, 'order_by_dir' => $condition];
    }

    /**
     * {inheritdoc}
     */
    public function getPackageUsersCount($package_id)
    {
        $package_user_relations = $this->getPackageActiveUserRelations($package_id);

        return count(array_unique($package_user_relations->pluck("user_id")->toArray()));
    }

    /**
     * {inheritdoc}
     */
    public function getPackageDetails($slug)
    {
        $package = $this->package_repository->findByAttribute('package_slug', $slug);
        if (!empty($package)) {
            if (isset($package->category_ids) && !empty($package->category_ids)) {
                $package->categories = $this->category_repository->getPackagesByAttribute('category_id', $package->category_ids)->pluck('category_name')->all();
            }
            if (isset($package->program_ids) && !empty($package->program_ids)) {
                $ids = collect($package->program_ids)->map(function ($id) {
                    return (int)$id;
                });
                $package->channels = $this->programRepository
                    ->getProgramsByAttribute('program_id', $ids)
                    ->map(function ($program) {
                        return ['title' => $program->program_title, 'slug' => $program->program_slug];
                    });
            }
        }
        return $package;
    }

    /**
     * Method to prepare erp package data
     * @param int $id
     * @param array $data
     * @param int $cron
     * @return nothing
     */
    public function getPrepareErpPackageData($id, $data, $cron)
    {
        $this->package_repository->getPreparePackageData($id, $data, $cron);
    }

    /**
     * @param array $logdata
     * @param string $status
     * @param string $slug
     * @param string $action
     * @param int $cron
     * @return nothing sends email & insert log
     */
    public function postPackageLog($logdata, $status, $slug, $action, $cron)
    {
        $logdata['type'] = 'file';
        $logdata['action'] = $action;
        $record = $this->getErpLogdata($logdata, $status, $cron);
        PackageLog::getInsertErpPackageLog($record);
        $this->packageImportEmail($status, $slug, $logdata['error_msgs'], $action);
    }

    /**
     * Method to prepare erp package log data
     * @param array $data
     * @param string $status
     * @param int $cron
     * @return array
     */
    public function getErpLogdata($data, $status, $cron)
    {
        $data['created_by'] = ($cron == 0) ? Auth::user()->username : CronBulkImport::CRONUSER;
        $data['created_at'] = time();
        $data['status'] = $status;  
        return $data;
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
            $total = PackageLog::getPackageImportCount($type = 'ALL', $search = null, $created_date, $action);
            $success = PackageLog::getPackageImportCount($type = 'SUCCESS', $search = null, $created_date, $action);
            $failure = PackageLog::getPackageImportCount($type = 'FAILURE', $search = null, $created_date, $action);
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
     * Method to get all program fields to insert update program log
     * @param array $logdata
     * @param array $fields
     * @returns all fields with values
     */
    public function getMissingPackageFields($logdata, $fields)
    {
        foreach($logdata as $key => $value)
        {
            $record[$key] = $value;
        }
        $record['sellable'] = '';
        return $record;
    }

    /**
     * generatePackageSlug prepares package slug
     * @param string $name
     * @param string $shortname
     * @returns package slug
     */
    public function generatePackageSlug($name, $shortname)
    {
        return $this->package_repository->generatePackageSlug($name, $shortname);
    }

    /**
     * Method to update program details
     * @param array $program_data
     * @param string $old_slug
     * @param string $new_slug
     * @param int $cron
     * @returns nothing updates program information
     */
    public function updatePackageDetails($program_data, $old_slug, $new_slug, $cron)
    {
        return $this->package_repository->updatePackageDetails($program_data, $old_slug, $new_slug, $cron);
    }

    /**
     * @param string $status
     * @param string $date
     * @return nothing exports packages to csv file
     */
    public function exportPackages($status = 'ALL', $date = null, $action = 'ALL')
    {
        $this->package_repository->exportPackages($status, $date, $action);
    }

    /**
     * @param array $logdata
     * @param string $status
     * @param string $slug
     * @param string $action
     * @param int $cron
     * @return nothing sends email & insert log
     */
    public function postUsergroupPackLog($logdata, $status, $slug, $action, $cron)
    {
        $logdata['type'] = 'file';
        $logdata['enrol_level'] = 'usergroup';
        $logdata['action'] = $action;
        $record = $this->getErpLogdata($logdata, $status, $cron);
        PackageEnrolLog::InsertErpEnrolLog($record);
        $this->enrolUsergroupToPackageEmail($status, $slug, $logdata['error_msgs'], $action);
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
            $total = PackageEnrolLog::getPackageUsergroupImportCount($type = 'ALL', $search = null, $created_date, 'usergroup', $action);
            $success = PackageEnrolLog::getPackageUsergroupImportCount($type = 'SUCCESS', $search = null, $created_date, 'usergroup', $action);
            $failure = PackageEnrolLog::getPackageUsergroupImportCount($type = 'FAILURE', $search = null, $created_date, 'usergroup', $action);
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
     * @param string $name
     * @param string $shortname
     * @return int package_id
     */
    
    public function getPackageId($name, $shortname)
    {
        return $this->package_repository->getPackageId($name, $shortname);
    }

    /**
     * @return int packageid
     * @param int $groupid
     * @param string $name
     * @param string $shortname
     * @param string $level
     * @param int $cron
     * @return nothing enrols usergroup to program
     */
    public function enrolUsergroupToPackage($packageid, $groupid, $name, $shortname, $level, $cron)
    {
        return $this->package_repository->enrolUsergroupToPackage($packageid, $groupid, $name, $shortname, $level, $cron);
    }

    /**
     * @inheritdoc
     */
    public function validateErpPackages($csvrowData)
    {
        return $this->package_repository->validateErpPackages($csvrowData);
    }

    /**
     * @inheritdoc
     */
    public function usergroupPackageExport($enrol_level, $status = 'ALL', $date = null)
    {
        $this->package_repository->usergroupPackageExport($enrol_level, $status = 'ALL', $date = null);
    }

    /**
     * {@inheritdoc}
     */
    public function getPackagesBySearch($search = '', $is_ug_rel = false, $start = 0, $limit = 500)
    {
        return $this->package_repository->getPackagesBySearch($search, $is_ug_rel, $start, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageByAttribute($attribute, $value, $status)
    {
        return $this->package_repository->getPackageByAttribute($attribute, $value, $status);
    }
}
