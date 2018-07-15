<?php

namespace App\Services\User;

use App\Enums\RolesAndPermissions\Contexts;
use App\Enums\User\EnrollmentStatus;
use App\Enums\User\UserEntity;
use App\Exceptions\ApplicationException;
use App\Exceptions\User\UserEntityRelationNotFoundException;
use App\Model\Program\IProgramRepository;
use App\Model\RolesAndPermissions\Repository\IContextRepository;
use App\Model\RolesAndPermissions\Repository\IRoleRepository;
use App\Enums\Cron\CronBulkImport;
use App\Model\User\Repository\IUserRepository;
use App\Model\ImportLog\Entity\UsergroupLog;
use App\Model\ImportLog\Entity\UserLog;
use App\Model\User;
use App\Model\UserGroup;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * class UserService
 * @package App\Services\User
 */
class UserService implements IUserService
{

    /**
     * @var \App\Model\User\Repository\IUserRepository
     */
    private $user_repository;
    /**
     * @var IRoleRepository
     */
    private $roleRepository;
    /**
     * @var IProgramRepository
     */
    private $programRepository;
    /**
     * @var IContextRepository
     */
    private $contextRepository;

    /**
     * UserService constructor.
     * @param IUserRepository $user_repository
     * @param IRoleRepository $roleRepository
     * @param IProgramRepository $programRepository
     * @param IContextRepository $contextRepository
     */
    public function __construct(
        IUserRepository $user_repository,
        IRoleRepository $roleRepository,
        IProgramRepository $programRepository,
        IContextRepository $contextRepository
    ) {
        $this->user_repository = $user_repository;
        $this->roleRepository = $roleRepository;
        $this->programRepository = $programRepository;
        $this->contextRepository = $contextRepository;
    }
    /**
     * @param array $data
     * @param string $error
     * @param string $status
     * @param string $action
     * @param int $cron
     * @return array $data 
     */
    public function getUserFailedLogData($data,$error,$status,$action,$cron)
    {
        
        $data['error_msgs'] = $error;
        $data['created_by'] = ($cron == 0) ? Auth::user()->username : CronBulkImport::CRONUSER;
        $data['created_at'] = time();
        $data['status'] = $status;
        $data['action'] = $action;
        return $data;
    }
    /**
     * @param array $data
     * @param string $status
     * @param string $action
     * @param int $cron
     * @return array $data 
     */
    public function getUserSuccessLogData($data,$status,$action,$cron)
    {
        
        $data['created_by'] = ($cron == 0) ? Auth::user()->username : CronBulkImport::CRONUSER;
        $data['created_at'] = time();
        $data['status'] = $status;
        $data['action'] = $action;
        return $data;
    }
    
    /**
     * @param string $status
     * @param string $slug
     * @param string $reason
     * @param string $action
     * @return nothing sends email to support email
     */
    public function userImportEmail($status,$slug,$reason,$action)
    {
        $this->user_repository->sendUserEmail($status,$slug,$reason,$action);
    }
    /**
     * @param string $data
     * @param int $cron
     * @return int $uid
     */
    public function prepareUserData($data,$cron)
    {
      $uid = $this->user_repository->getUserData($data,$cron);
      return $uid;
    }
    /**
     * @param int $gid
     * @param int $uid
     * @param array $data
     * @param int $cron
     * @return nothing creates user group
     */
    public function createUserGroup($gid,$uid,$data,$cron)
     {
        if($gid > 0) {
            Usergroup::where('ugid', $gid)->push('relations.' . 'active_user_usergroup_rel', (int)$uid, true);
            User::where('uid', $uid)->push('relations.' . 'active_usergroup_user_rel', (int)$gid, true);
        }
        else {
               $input = UsergroupLog::prepareUgLogData($data);
               $length = strlen(str_replace(' ', '',$input['ug_name_lower']));
               if($length > 0) {
               $groupid = Usergroup::getInsertUserGroup($input);
               $grouplog = UsergroupLog::getUgLogData($groupid,$cron);
               UsergroupLog::insertErpUserGroupLog($grouplog);
               Usergroup::where('ugid', $groupid)->push('relations.' . 'active_user_usergroup_rel', (int)$uid, true);
               User::where('uid', $uid)->push('relations.' . 'active_usergroup_user_rel', (int)$groupid, true);
            }
        }
     }
     /**
     * @param string $name
     * @return string $filename
     */
    public function getFileName($name,$action)
    {
        $now = time();
        $date = date('m-d-Y');
        $filename = $action.'-'.$date.'-'.$now.'-'.$name;
        return $filename;
    }
    /**
     * @param string $error
     * @param string $status
     * @param string $slug
     * @param string $action
     * @param int $cron
     * @return nothing sends email & insert user log
     */
    public function postUserLog($error, $status, $slug, $action, $cron)
    {
        $logdata['type'] = 'file';
        $logdata = $this->getUserFailedLogData($logdata, $error, $status, $action, $cron);
        UserLog::insertErpUserLog($logdata);
        $this->userImportEmail($status, $slug, $logdata['error_msgs'], $action);
    }
    /**
     * {@inheritdoc}
     */
    public function getListOfUsersDetails($user_ids, $start = 0, $limit = 0)
    {
        return $this->user_repository->getListOfUsersDetails($user_ids, $start, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function getListOfActiveUsers($user_ids, $start = 0, $limit = 0, $search = '')
    {
        return $this->user_repository->getListOfActiveUsers($user_ids, $start, $limit, $search);
    }

    /**
     * {@inheritdoc}
     */
    public function getListOfUsersCount($user_ids)
    {
        return $this->user_repository->getListOfUsersCount($user_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllUsersBySearchKey($search, $start, $limit)
    {
        return $this->user_repository->getAllUsersBySearchKey($search, $start, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function getSpecificUser($user_id)
    {
        if ($user_id > 0) {
            return $this->user_repository->getSpecificUser($user_id);
        } else {
            return null;
        }
    }

    /**
     * Method to download update users csv file 
     * @returns update users csv file with fields as headers
     */
    public function downloadUpdateUserTemplate()
    {
        $this->user_repository->downloadUpdateUserTemplate();
    }
    /**
     * Method to get all user fields to insert update users log
     * @param array $logdata
     * @param array $fields
     * @returns all fields with values
     */
    public function getMissingFields($logdata,$fields)
    {
        foreach($fields as $keys => $values)
        {
            $record[$values] = '';
        }
        foreach($logdata as $key => $value)
        {
            $record[$key] = $value;
        }
        return $record;
    }
    /**
     * Method to get userid
     * @param string $username
     * @returns int $userid
     */
    public function getUserIdByUserName($username)
    {
        return $this->user_repository->getUserIdByUserName($username);
    }
    /**
     * Method to update user details
     * @param int $uid
     * @param array $data
     * @returns nothing updates user information
     */
    public function updateUserDetails($uid,$data)
    {
        $this->user_repository->updateUserDetails($uid,$data);
    }
    /**
     * @param string $error
     * @param string $status
     * @param string $slug
     * @param string $action
     * @param int $cron
     * @return nothing sends email & insert usergroup log
     */
    public function postUserGroupLog($error,$status,$slug,$action,$cron)
    {
        $logdata['type'] = 'file';
        $logdata = $this->getUserGroupFailedData($logdata,$error,$status,$action,$cron); 
        UsergroupLog::insertErpUserGroupLog($logdata);
        $this->sendUserGroupEmail($status,$slug,$logdata['error_msgs'],$action);
    }
    /**
     * @param array $data
     * @param string $error
     * @param string $status
     * @param string $action
     * @param int $cron
     * @return array $data 
     */
    public function getUserGroupFailedData($data,$error,$status,$action,$cron)
    {
        $data['error_msgs'] = $error;
        $data['created_by'] = ($cron == 0) ? Auth::user()->username : CronBulkImport::CRONUSER;
        $data['created_at'] = time();
        $data['status'] = $status;
        $data['action'] = $action;
        return $data;
    }
    /**
     * @param string $status
     * @param string $slug
     * @param string $reason
     * @param string $action
     * @return nothing sends email to support email
     */
    public function sendUserGroupEmail($status,$slug,$reason,$action)
    {
        $this->user_repository->sendUserGroupEmail($status,$slug,$reason,$action);
    }
    /**
     * @param array $data
     * @param string $status
     * @param string $action
     * @param int $cron
     * @return array $data 
     */
    public function getUserGroupSuccessData($data,$status,$action,$cron)
    {
        $data['created_by'] = ($cron == 0) ? Auth::user()->username : CronBulkImport::CRONUSER;
        $data['created_at'] = time();
        $data['status'] = $status;
        $data['action'] = $action;
        return $data;
    }
    /**
     * Method to get user group id
     * @param string $ugname
     * @returns int $ugid
     */
    public function getUserGroupIdByGroupName($ugname)
    {
        return $this->user_repository->getUserGroupIdByGroupName($ugname);
    }
    /**
     * Method to update user & usergroup relations
     * @param int $uid
     * @param int $gid
     * @returns nothing updates user & usergroup relations 
     */
    public function updateUserUserGroupRelations($uid,$gid,$operation)
    {
        $this->user_repository->updateUserUserGroupRelations($uid,$gid,$operation);
    }
    /**
     * Method to download user-usergroup mapping csv file 
     * @returns csv file with fields as headers
     */
    public function downloadUserUserGroupTemplate()
    {
        $this->user_repository->downloadUserUserGroupTemplate();
    }

    /**
     * Method to export user-usergroup log records
     * @return csv file with user-usergroup log records
     */
    public function getUserUgExport($status,$operation,$created_date)
    {
        $this->user_repository->getUserUgExport($status,$operation,$created_date);
    }

    /**
     * @inheritdoc
     */
    public function getExpireSubscribedChannels($date)
    {
        return $this->user_repository->getExpireSubscribedChannels($date);
    }

    /**
     * @inheritdoc
     */
    public function getUsersByUidUGid($u_ids, $ug_ids)
    {
        return $this->user_repository->getUsersByUidUGid($u_ids, $ug_ids)->keyBy('uid');
    }

    /**
     * @inheritdoc
     */
    public function getUsersByUGids($ug_ids)
    {
        return $this->user_repository->getUsersByUGids($ug_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getUsersDetails($filter_params = [], $start = 0, $limit = 0)
    {
        return $this->user_repository->get($filter_params, $start, $limit);
    }

    /**
     * @inheritDoc
     */
    public function enrollEntityToUser($user_id, $entity_data, $enrollment_source_info)
    {
        $enrollment_source_info["enrolled_by"] = !is_null(Auth::user())? Auth::user()->uid : null;
        return $this->user_repository->createUserEntityRelation($user_id, $entity_data, $enrollment_source_info);
    }

    /**
     * @inheritDoc
     */
    public function unenrollUserFromEntity($user_id, $entity_type, $entity_id, $source_type, $source_id = null)
    {
        try {
            $user_entity_relation = $this->user_repository->findUserEntityRelation(
                $user_id,
                $entity_type,
                $entity_id,
                $source_type,
                $source_id
            );

            $user_entity_relation->status = EnrollmentStatus::UNENROLLED;
            $user_entity_relation->unenrolled_by = !is_null(Auth::user())? Auth::user()->uid : null;
            $user_entity_relation->unenrolled_on = Carbon::create()->timestamp;

            $user_entity_relation->save();

            return true;
        } catch (UserEntityRelationNotFoundException $e) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function isEntityEnrolled($entity_type, $entity_id)
    {
         $data = $this->user_repository->getUserEntityRelations(
            ['entity_type' => $entity_type, 'entity_id' => $entity_id]
         );

         return !$data->isEmpty();
    }

    /**
     * @inheritDoc
     */
    public function getNewUsersCount(array $date)
    {
        return $this->user_repository->getNewUsersCount($date);
    }

    /**
     * @inheritdoc
     */
    public function getFtpConnectionDetails()
    {
        return $this->user_repository->getFtpConnectionDetails();
    }

    /**
     * @inheritdoc
     */
    public function getFtpDirDetails($ftp_conn_id)
    {
        return $this->user_repository->getFtpDirDetails($ftp_conn_id);
    }

    /**
     * @inheritdoc
     */
    public function getUserImportFileColumns($ftp_connection_details, $ftp_dir_details)
    {
        return $this->user_repository->getUserImportFileColumns($ftp_connection_details, $ftp_dir_details);
    }

    /**
     * @inheritdoc
     */
    public function validateRulesErp($csvrowData)
    {
        return $this->user_repository->validateRulesErp($csvrowData);
    }

    /**
     * @inheritdoc
     */
    public function getFtpDirUpdateDetails($ftp_conn_id)
    {
        return $this->user_repository->getFtpDirUpdateDetails($ftp_conn_id);
    }

    /**
     * @inheritdoc
     */
    public function getUserImportUpdateFileColumns($ftp_connection_details, $ftp_dir_details)
    {
        return $this->user_repository->getUserImportUpdateFileColumns($ftp_connection_details, $ftp_dir_details);
    }

    /**
     * @inheritdoc
     */
    public function validateUpdateRules($csvrowData)
    {
        return $this->user_repository->validateUpdateRules($csvrowData);
    }

    /**
     * @inheritdoc
     */
    public function getUserUsergroupUpdateFtpDetails($ftp_conn_id)
    {
        return $this->user_repository->getUserUsergroupUpdateFtpDetails($ftp_conn_id);
    }

    /**
     * @inheritdoc
     */
    public function getUserUsergroupUpdateFileColumns($ftp_connection_details, $ftp_dir_details)
    {
        return $this->user_repository->getUserUsergroupUpdateFileColumns($ftp_connection_details, $ftp_dir_details);
    }

    /**
     * @inheritdoc
     */
    public function validateUserUsergroupRules($csvrowData)
    {
        return $this->user_repository->validateUserUsergroupRules($csvrowData);
    }

    /**
     * @inheritdoc
     */
    public function getActiveUsergroupUserRel($user_name)
    {
        return $this->user_repository->getActiveUsergroupUserRel($user_name);
    }

    /**
     * @inheritdoc
     */
    public function getActiveUserCount($user_name)
    {
        return $this->user_repository->getActiveUserCount($user_name);
    }

    /**
     * @inheritdoc
     */
    public function getIdBy($data, $askedVar)
    {
        return $this->user_repository->getIdBy($data, $askedVar);
    }

    /**
     * @inheritdoc
     */
    public function getAssignedUsergroups($uid)
    {
        return $this->user_repository->getAssignedUsergroups($uid);
    }

    /**
     * @inheritdoc
     */
    public function updateUserRelation($key, $arrname, $updateArr, $overwrite = false)
    {
        return $this->user_repository->updateUserRelation($key, $arrname, $updateArr, $overwrite);
    }

    /**
     * @inheritdoc
     */
    public function getAdminUsers()
    {
        return $this->user_repository->getAdminUsers();
    }

    /**
     * @inheritdoc
     */
    public function getActiveUsers($users_to_be_excluded)
    {
        return $this->user_repository->getActiveUsers($users_to_be_excluded);
    }

    /**
     * @inheritdoc
     */
    public function getUserDetailsByUserName($username, $status)
    {
        return $this->user_repository->getUserDetailsByUserName($username, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function getUsersByChannelEnrolleddate($channel_id, $date)
    {
        $filter_params['entity_type'] = "PROGRAM";
        $filter_params['entity_id'] = $channel_id;
        if (is_array($date)) {
            $filter_params['enrolled_on'] = [$date['start_date'], $date['end_date']];
        }
        return $this->user_repository->getEnrolledUsersByDate($filter_params);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserEnrolledChannelsByUser($user_id, $date)
    {
        $filter_params['user_id'] = (int) $user_id;
        $filter_params['entity_type'] = "PROGRAM";
        if (is_array($date)) {
            $filter_params['enrolled_on'] = [$date['start_date'], $date['end_date']];
        }
        return $this->user_repository->getUserEntityRelations($filter_params);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserEnrolledByChannelUG($channel_id, $ug_id)
    {
        $filter_params['source_type'] = "USER_GROUP";
        $filter_params['source_id'] = (int) $ug_id;
        $filter_params['entity_type'] = "PROGRAM";
        $filter_params['entity_id'] = (int) $channel_id;
        return $this->user_repository->getUserEntityRelations($filter_params);
    }

    /**
     * @inheritdoc
     */
    public function getAllDetailsByUsername($username)
    {
        return $this->user_repository->getAllDetailsByUsername($username);
    }

    /**
     * @inheritdoc
     */
    public function getDeleteUsers($uid, $username, $email)
    {
        return $this->user_repository->getDeleteUsers($uid, $username, $email);
    }

    /**
     * @inheritdoc
     */
    public function getNewUsers(array $date, $start, $limit)
    {
        return $this->user_repository->getNewUsers($date, $start, $limit);
    }

    /**
     * @inheritdoc
     */
    public function countActiveUsers()
    {
        return $this->user_repository->countActiveUsers();
    }

    /**
     * @inheritdoc
     */
    public function countInActiveUsers()
    {
        return $this->user_repository->countInActiveUsers();
    }

    /**
     * @inheritdoc
     */
    public function getAllUsersData($columns, $start, $limit)
    {
        return $this->user_repository->getAllUsersData($columns, $start, $limit);
    }

    /**
     * @inheritdoc
     */
    public function getUserDetailsByUserIds($start, $limit, $column, $searchKey, $user_ids, $users_to_be_excluded, $orderby)
    {
        return $this->user_repository->getUserDetailsByUserIds($start, $limit, $column, $searchKey, $user_ids, $users_to_be_excluded, $orderby);
    }

    /**
     * @inheritdoc
     */
    public function getUsersByUserIds($start, $limit, $column, $user_ids, $orderby, $search)
    {
        return $this->user_repository->getUsersByUserIds($start, $limit, $column, $user_ids, $orderby, $search);
    }

    /**
     * @inheritdoc
     */
    public function getFilteredUserDetails($start, $limit, $column, $searchKey, $users_to_be_excluded, $orderby)
    {
        return $this->user_repository->getFilteredUserDetails($start, $limit, $column, $searchKey, $users_to_be_excluded, $orderby);
    }

    /**
     * @inheritdoc
     */
    public function getCertifiedUsersCount($user_id, $users_to_be_excluded)
    {
        return $this->user_repository->getCertifiedUsersCount($user_id, $users_to_be_excluded);
    }

    /**
     * @inheritdoc
     */
    public function getUsersDetailsUsingUserIDs($user_ids, $status, $orderby, $columns)
    {
        return $this->user_repository->getUsersDetailsUsingUserIDs($user_ids, $status, $orderby, $columns);
    }

    /**
     * @inheritdoc
     */
    public function addUserRelation($user_id, $fieldarr, $package_id)
    {
        return $this->user_repository->addUserRelation($user_id, $fieldarr, $package_id);
    }

    /**
     * @inheritdoc
     */
    public function removeUserRelation($user_id, $fieldarr, $package_id)
    {
        return $this->user_repository->removeUserRelation($user_id, $fieldarr, $package_id);
    }

    /**
     * @inheritdoc
     */
    public function getIdByEmail($email)
    {
        return $this->user_repository->getIdByEmail($email);
    }

    public function removeUserSurvey($user_id, $fieldarr, $sid)
    {
        return $this->user_repository->removeUserSurvey($user_id, $fieldarr, $sid);
    }

    public function addUserSurvey($user_id, $fieldarr, $sid)
    {
        return $this->user_repository->addUserSurvey($user_id, $fieldarr, $sid);
    }

    public function removeUserAssignment($user_id, $fieldarr, $aid)
    {
        return $this->user_repository->removeUserAssignment($user_id, $fieldarr, $aid);
    }

    public function addUserAssignment($user_id, $fieldarr, $aid)
    {
        return $this->user_repository->addUserAssignment($user_id, $fieldarr, $aid);
    }

    public function getUserColumnsbyId($user_ids, $columns)
    {
        return $this->user_repository->getUserColumnsbyId($user_ids, $columns);
    }

    /**
     * @inheritdoc
     */
    public function getSuperAdminIds()
    {
       return $this->user_repository->getSuperAdminIds()->pluck('uid')->all();
    }
}
