<?php

namespace App\Model\UserGroup\Repository;

/**
 * interface IUserGroupRepository
 * @package App\Model\UserGroup\Repository
 */
interface IUserGroupRepository
{
    /**
     * Find UserGroup using unique id
     *
     * @var int $id
     *
     * @return \App\Model\UserGroup
     *
     * @throws \App\Exceptions\UserGroup\UserGroupNotFoundException
     */
    public function find($id);

    /**
     * getUsergroupIdName get list of usergroup by all/searchkey
     * @param  string/int array  $ug_ids array of usergroup ids or all
     * @param  string  $search search key for search in usergroup name
     * @param  integer $start  Records strating point
     * @param  integer $limit  Max number of records
     * @return collection          list of usergroup detils of ugid, usergroup name and relations
     */
    public function getUsergroupIdName($ug_ids = 'ALL', $search = '', $start = 0, $limit = 500);

    /**
     * getUsergroupDetailsByDate get usergroup all feilds inbetween specified dates/all
     * @param  array $date_range array of start_date and end_date as timestamp
     * @return collection          list of usergroup detils
     */
    public function getUsergroupDetailsByDate($date_range);

    /**
     * getUserGroupsUsingID get usergroup details by ugid
     * @param  string/array $ugid array of ugid or all
     * @return array       ug details as an array
     */
    public function getUserGroupsUsingID($ugid = 'ALL');

    /**
     * Using this method you can get all the usergroup details using scope filters
     * @param array $filter_params
     * @param array  $columns
     * @return collections
     */
    public function get($filter_params = [], $columns = []);

    /**
     * @return int
     */
    public function getTotalUserGroupsCount();

    /**
     * getUserGroupsByIds
     * @param  array $ug_ids
     * @return array
     */
    public function getUserGroupsByIds($ug_ids);

    /**
     * getUsersByUserGroupIds
     * @param  array $ug_ids
     * @return array
     */
    public function getUsersByUserGroupIds($ug_ids);

    /**
     * @param  array $ug_names
     * @return int
    */
    public function getInActiveUserGroupCount($ug_names);

    /**
     * @param  array $ug_names
     * @return array
     */
    public function getUserUsergroupRelation($ug_names);

    /**
     * @param  string $userGroupName
     * @return int
     */
    public function getUserGroupCount($userGroupName);

    /**
     * @param array $user_group
     * @return array
     */
    public function getUserGroupIDByUserGroupName($user_group);

    /**
     * @param int $key
     * @param array $fieldarr
     * @param int $id
     * @return array
     */
    public function addUserGroupRelation($key, $fieldarr = [], $id);

    /**
     * @param int $key
     * @param string $arrname
     * @param array $updateArr
     * @param boolean $overwrite
     * @return boolean
     */
    public function updateUserGroupRelation($key, $arrname, $updateArr, $overwrite = false);

    /**
     * @param  int $ug_id
     * @return array
     */
    public function getUserGroupChannels($ug_id);

    public function removeUserGroupSurvey($ug_id, $fieldarr, $sid);

    public function addUserGroupSurvey($ug_id, $fieldarr, $sid);

    public function removeUserGroupAssignment($ug_id, $fieldarr, $aid);

    public function addUserGroupAssignment($ug_id, $fieldarr, $aid);
        
}
