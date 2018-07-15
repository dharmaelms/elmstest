<?php

namespace App\Services\User;

/**
 * interface IUserService
 * @package App\Services\User
 */
interface IUserService
{
    /**
     * @param array $data
     * @param string $error
     * @param string $status
     * @param string $action
     * @param int $cron
     * @return array $data 
     */
    public function getUserFailedLogData($data,$error,$status,$action,$cron);
    /**
     * @param array $data
     * @param string $status
     * @param string $action
     * @param int $cron
     * @return array $data 
     */
    public function getUserSuccessLogData($data,$status,$action,$cron);
    /**
     * @param string $status
     * @param string $slug
     * @param string $reason
     * @param string $action
     * @return nothing sends email to support email
     */
    public function userImportEmail($status,$slug,$reason,$action);
    /**
     * @param string $data
     * @param int $cron
     * @return int $uid
     */
    public function prepareUserData($data,$cron);
    /**
     * @param int $gid
     * @param int $uid
     * @param array $data
     * @param int $cron
     * @return nothing creates user group
     */
    public function createUserGroup($gid,$uid,$data,$cron);
    /**
     * @param string $name
     * @param string $action
     * @return string $filename
     */
    public function getFileName($name,$action);
    /**
     * @param string $error
     * @param string $status
     * @param string $slug
     * @param string $action
     * @param int $cron
     * @return nothing sends email & insert user log
     */
    public function postUserLog($error, $status, $slug, $action, $cron);

     /**
     * getListOfUsersDetails get list of user details by array of users
     * @param  array $user_ids user ids as an array
     * @param  integer $start start point of records
     * @param  integer $limit records need to get max
     * @return collection            Users details as collections
     */
    public function getListOfUsersDetails($user_ids, $start = 0, $limit = 0);

    /**
     * getListOfActiveUsers get list of user details by array of users
     * @param  array $user_ids user ids as an array
     * @param  integer $start start point of records
     * @param  integer $limit records need to get max
     * @return collection            Users details as collections
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
     * @param  string  $search search key sort for firstname and lastname
     * @param  integer $start  start point of records
     * @param  integer $limit  how many records need to get max
     * @return collection          as user'd details of uid, first name and lastname
     */
    public function getAllUsersBySearchKey($search, $start, $limit);

    /**
     * getSpecificUser get Specific user details
     * @param  int $user_id unique user is
     * @return collection          User's details
     */
    public function getSpecificUser($user_id);

    /**
     * Method to download update users csv file 
     * @returns update users csv file with fields as headers
     */
    public function downloadUpdateUserTemplate();
    /**
     * Method to get all user fields to insert update users log
     * @param array $logdata
     * @param array $fields
     * @returns all fields with values
     */
    public function getMissingFields($logdata,$fields);
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
     * @param string $error
     * @param string $status
     * @param string $slug
     * @param string $action
     * @param int $cron
     * @return nothing sends email & insert usergroup log
     */
    public function postUserGroupLog($error,$status,$slug,$action,$cron);
    /**
     * @param array $data
     * @param string $error
     * @param string $status
     * @param string $action
     * @param int $cron
     * @return array $data 
     */
    public function getUserGroupFailedData($data,$error,$status,$action,$cron);
     /**
     * @param string $status
     * @param string $slug
     * @param string $reason
     * @param string $action
     * @return nothing sends email to support email
     */
    public function sendUserGroupEmail($status,$slug,$reason,$action);
    /**
     * @param array $data
     * @param string $status
     * @param string $action
     * @param int $cron
     * @return array $data 
     */
    public function getUserGroupSuccessData($data,$status,$action,$cron);
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
    public function updateUserUserGroupRelations($uid,$gid,$operation);
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
     * To get user information using filter params 
     * @param  array $filter_params
     * @return collection
     */
    public function getUsersDetails($filter_params = [], $start = 0, $limit = 0);

    /**
     * @param int $user_id
     * @param array $entity_data
     * @param array $enrollment_source_info
     * @return \App\Model\User\Entity\UserEnrollment
     */
    public function enrollEntityToUser($user_id, $entity_data, $enrollment_source_info);

    /**
     * @param int $user_id
     * @param string $entity_type
     * @param int $entity_id
     * @param string $source_type
     * @param int $source_id
     * @return boolean
     */
    public function unenrollUserFromEntity($user_id, $entity_type, $entity_id, $source_type, $source_id = null);

    /**
     * @param $entity_type
     * @param $entity_id
     * @return mixed
     */
    public function isEntityEnrolled($entity_type, $entity_id);

    /**
     * getNewUsersCount
     * @param  array  $date
     * @return integer
     */
    public function getNewUsersCount(array $date);

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
     * getUsersByChannelEnrolleddate
     * @param  integer  $channel_id
     * @param  array|string  $date
     * @return collection
     */
    public function getUsersByChannelEnrolleddate($channel_id, $date);

    /**
     * getUserEnrolledChannelsByUser
     * @param  integer  $user_id
     * @param  array|string  $date
     * @return collection
     */
    public function getUserEnrolledChannelsByUser($user_id, $date);

    /**
     * getUserEnrolledByChannelUG
     * @param  integer $channel_id
     * @param  integer $ug_id
     * @return collection
     */
    public function getUserEnrolledByChannelUG($channel_id, $ug_id);

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
