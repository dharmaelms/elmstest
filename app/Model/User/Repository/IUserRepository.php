<?php
namespace App\Model\User\Repository;

/**
 * interface UserRepository
 * @package App\Model\User\Repository
 */
interface IUserRepository
{
    /**
     * Find User using unique id
     *
     * @var int $uid
     *
     * @return \App\Model\User
     */
    public function find($uid);

    /**
     * @param string $status
     * @param string $slug
     * @param string $reason
     * @param string $action
     * @return nothing sends email to support
     */

    public function sendUserEmail($status, $slug, $reason, $action);

    /**
     * @param array $data
     * @param int $cron
     * @return int $uid
     */
    public function getUserData($data,$cron);

    /**
     * getListOfUsersDetails get list of user details by array of users
     * @param  array $user_ids user ids as an array
     * @param  integer $start start point of records
     * @param  integer $limit records need to get max
     * @return collection  Users details as collections
     */
    public function getListOfUsersDetails($user_ids, $start = 0, $limit = 0);

    /**
     * getListOfActiveUsers get list of user details by array of users
     * @param  array $user_ids user ids as an array
     * @param  integer $start start point of records
     * @param  integer $limit records need to get max
     * @return collection  Users details as collections
     */
    public function getListOfActiveUsers($user_ids, $start = 0, $limit = 0, $search = '');

    /**
     * getListOfUsersCount get list of user details by array of users
     * @param  array $user_ids user ids as an array
     * @return count of user ids
     */
    public function getListOfUsersCount($user_ids);

    /**
     * getALlUsersBySearchKey get the list of users as fullname and uid
     * @param  string $search search key sort for firstname and lastname
     * @param  integer $start start point of records
     * @param  integer $limit how many records need to get max
     * @return collection          as user'd details of uid, first name and lastname
     */
    public function getAllUsersBySearchKey($search = '', $start = 0, $limit = 500);

    /**
     * getSpecificUser get Specific user details
     * @param  int $user_id unique user is
     * @return collection          User's details
     */
    public function getSpecificUser($user_id = 0);

    /**
     * Method to download update users csv file 
     * @returns update users csv file with fields as headers
     */
    public function downloadUpdateUserTemplate();
    /**
     * Method to get userid
     * @param string $username
     * @returns int $userid
     */
    public function getUserIdByUserName($username);
    /**
     * Method to update user details
     * @param int $uid
     * @param array $data
     * @returns nothing updates user information
     */
    public function updateUserDetails($uid,$data);
     /**
     * @param string $status
     * @param string $slug
     * @param string $reason
     * @param string $action
     * @return nothing sends email to support
     */
   public function sendUserGroupEmail($status,$slug,$reason,$action);
   /**
     * Method to get user group id
     * @param string $ugname
     * @returns int $ugid
     */
    public function getUserGroupIdByGroupName($ugname);
    /**
     * Method to update user & usergroup relations
     * @param int $uid
     * @param int $gid
     * @returns nothing updates user & usergroup relations 
     */
    public function updateUserUserGroupRelations($uid,$gid,$operation,$ug_arrayname = 'active_user_usergroup_rel',$user_arrayname = 'active_usergroup_user_rel');
    /**
     * Method to download user-usergroup mapping csv file 
     * @returns csv file with fields as headers
     */
    public function downloadUserUserGroupTemplate();
    /**
     * Method to export user-usergroup log records
     * @return csv file with user-usergroup log records
     */
    public function getUserUgExport($status,$operation,$created_date);

    /**
     * getExpireSubscribedChannels
     * @param  array $date int array has start and end time
     * @return array
     */
    public function getExpireSubscribedChannels($date);

    /**
     * getUsersByUidUGid
     * @param  array $u_ids
     * @param  array $ug_ids
     * @return array
     */
    public function getUsersByUidUGid($u_ids, $ug_ids);

    /**
     * getUsersByUGid
     * @param  array $ug_ids
     * @return array
     */
    public function getUsersByUGids($ug_ids);

    /**
     * @param array $filter_params
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRegisteredUsers($filter_params = []);

    /**
     * @param array $filter_params
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRegisteredUsersId($filter_params = []);

    /**
     * @return int
     */
    public function getTotalUsersCount();

    /**
     * @param array $filter_params
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get($filter_params = [], $start = 0, $limit = 0);

    /**
     * @param array $filter_params
     * @return int
     */
    public function getUsersCount($filter_params = []);

    /**
     * Find user using unique user_id
     *
     * @var int $user_id
     *
     * @return \App\Model\user
     */
    public function findActiveUser($user_id);

    /**
     * @param int $user_id
     * @param array $entity_data
     * @param array $enrollment_source_data
     * @return \App\Model\User\Entity\UserEnrollment
     */
    public function createUserEntityRelation($user_id, $entity_data, $enrollment_source_data);

    /**
     * @param int $user_id
     * @param string $entity_type
     * @param int $entity_id
     * @param $enrollment_source_type
     * @param null $enrollment_source_id
     * @return \App\Model\User\Entity\UserEnrollment
     */
    public function findUserEntityRelation(
        $user_id,
        $entity_type,
        $entity_id,
        $enrollment_source_type,
        $enrollment_source_id = null
    );

    /**
     * @param int|\App\Model\User $user
     * @param array $filter_params
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserEntities($user, $filter_params = []);

    /**
     * @param int $user_id
     * @param string $entity_type
     * @param int $entity_id
     * @return \App\Model\User\Entity\UserEnrollment
     */
    public function findActiveUserEntityRelation($user_id, $entity_type, $entity_id);

    /**
     * @param array $filter_params
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserEntityRelations($filter_params = []);

    /**
     * @param array $filter_params
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEnrolledUsersByDate($filter_params = []);
    
    /**
     * @param array $filter_params
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserEntityRelationsUIds($filter_params = []);

    /**
     * @param array $filter_params
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveUserEntityRelations($filter_params = []);

    /**
     * getNewUsersCount
     * @param  array  $date
     * @return integer
     */
    public function getNewUsersCount(array $date);
    /**
     * getFtpConnectionDetails
     * @return array
     */
    public function getFtpConnectionDetails();

    /**
     * getFtpDirDetails
     * @param array $ftp_conn_id
     * @return array
     */
    public function getFtpDirDetails($ftp_conn_id);

    /**
     * getUserImportFileColumns
     * @param array $ftp_connection_details
     * @param array $ftp_dir_details
     * @return array
     */
    public function getUserImportFileColumns($ftp_connection_details, $ftp_dir_details);

    /**
     * validateRulesErp
     * @param array $csvrowData
     * @return boolean
     */
    public function validateRulesErp($csvrowData);

    /**
     * getFtpDirUpdateDetails
     * @param array $ftp_conn_id
     * @return array
     */
    public function getFtpDirUpdateDetails($ftp_conn_id);

    /**
     * getUserImportUpdateFileColumns
     * @param array $ftp_connection_details
     * @param array $ftp_dir_details
     * @return array
     */
    public function getUserImportUpdateFileColumns($ftp_connection_details, $ftp_dir_details);

    /**
     * validateUpdateRules
     * @param array $csvrowData
     * @return boolean
     */
    public function validateUpdateRules($csvrowData);

    /**
     * getUserUsergroupUpdateFtpDetails
     * @param array $ftp_conn_id
     * @return array
     */
    public function getUserUsergroupUpdateFtpDetails($ftp_conn_id);

    /**
     * getUserUsergroupUpdateFileColumns
     * @param array $ftp_connection_details
     * @param array $ftp_dir_details
     * @return array
     */
    public function getUserUsergroupUpdateFileColumns($ftp_connection_details, $ftp_dir_details);

    /**
     * validateUserUsergroupRules
     * @param array $csvrowData
     * @return boolean
     */
    public function validateUserUsergroupRules($csvrowData);

    /**
     * @param string $user_name
     * @return collection
     */
    public function getActiveUsergroupUserRel($user_name);
    
    /**
     * @param string $user_name
     * @return int
     */
    public function getActiveUserCount($user_name);

    /**
     * @param array $data
     * @param string $askedVar
     * @return int
     */
    public function getIdBy($data, $askedVar);

    /**
     * @param int $uid
     * @return array
     */
    public function getAssignedUsergroups($uid);

   /**
     * @param int $key
     * @param string $arrname
     * @param int $updateArr
     * @param boolean $overwrite
     * @return boolean
     */
    public function updateUserRelation($key, $arrname, $updateArr, $overwrite = false); 

    /**
     * @return array
     */
    public function getAdminUsers();

    /**
     * @param array $users_to_be_excluded
     * @return array
     */
    public function getActiveUsers($users_to_be_excluded);

    /**
     * @param string $username
     * @param string $status
     * @return int
     */
    public function getUserDetailsByUserName($username, $status);

    /**
     * @param string $username
     * @return collection
     */
    public function getAllDetailsByUsername($username);

    /**
     * @param int $uid
     * @param string $username
     * @param string $email
     */
    public function getDeleteUsers($uid, $username, $email);

    /**
     * getNewUsers
     * @param  array  $date Which contains start and end date
     * @param  int $start
     * @param  int $limit
     * @return collection
     */
    public function getNewUsers(array $date, $start, $limit);

    /**
     * @return integer
     */
    public function countActiveUsers();

    /**
     * @return integer
     */
    public function countInActiveUsers();

    /**
     * @param  array $columns
     * @param  $start
     * @param  $limit
     * @return collection
     */
    public function getAllUsersData($columns, $start, $limit);

    /**
     * @param  int $start
     * @param  int $limit
     * @param  string $searchKey
     * @param  array $user_ids
     * @param  array $users_to_be_excluded
     * @param  array $orderby
     * @return collection
     */
    public function getUserDetailsByUserIds($start, $limit, $column, $searchKey, $user_ids, $users_to_be_excluded, $orderby);
    
    /**
     * @param  int $start
     * @param  int $limit
     * @param  array $user_ids
     * @param  array $orderby
     * @return collection
     */
    public function getUsersByUserIds($start, $limit, $column, $user_ids, $orderby, $search);

    /**
     * @param  int $start
     * @param  int $limit
     * @param  array $column
     * @param  string $searchKey
     * @param  array $users_to_be_excluded
     * @param  array $orderby
     * @return collection
     */
    public function getFilteredUserDetails($start, $limit, $column, $searchKey, $users_to_be_excluded, $orderby);

    /**
     * @param  array $user_id
     * @param  array $users_to_be_excluded
     * @return int
     */
    public function getCertifiedUsersCount($user_id, $users_to_be_excluded);

    /**
     * @param  array $user_ids
     * @param  string $status
     * @param  array $orderby
     * @param  array $columns
     * @return int
     */
    public function getUsersDetailsUsingUserIDs($user_ids, $status, $orderby, $columns);

    /**
     * @param  int $user_id
     * @param  array $fieldarr
     * @param  int $package_id
     * @return boolean
     */
    public function addUserRelation($user_id, $fieldarr, $package_id);

    /**
     * @param  int $user_id
     * @param  array $fieldarr
     * @param  int $package_id
     * @return boolean
     */
    public function removeUserRelation($user_id, $fieldarr, $package_id);

    /**
     * @param string $email
     * @return int
     */
    public function getIdByEmail($email);

    public function removeUserSurvey($user_id, $fieldarr, $sid);

    public function addUserSurvey($user_id, $fieldarr, $sid);

    public function removeUserAssignment($user_id, $fieldarr, $aid);

    public function addUserAssignment($user_id, $fieldarr, $aid);

    public function getUserColumnsbyId($user_ids, $columns);

    /**
     * @return array
     */
    public function getSuperAdminIds();
}
