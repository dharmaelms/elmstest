<?php

namespace App\Services\UserGroup;

use App\Exceptions\UserGroup\UserGroupNotFoundException;
use App\Model\UserGroup\Repository\IUserGroupRepository;

/**
 * class UserGroupService
 * @package App\Services\UserGroup
 */
class UserGroupService implements IUserGroupService
{
    /**
     * @var IUserGroupRepository
     */
    private $ug_repository;

    /**
     * UserGroupService constructor.
     *
     * @param IUserGroupRepository $userGroupRepository
     */
    public function __construct(
        IUserGroupRepository $userGroupRepository
    ) {
        $this->ug_repository = $userGroupRepository;
    }

    /**
     * @inheritDoc
     */
    public function getUserGroupDetails($id)
    {
        try {
            return $this->ug_repository->find($id);
        } catch (UserGroupNotFoundException $e) {
            throw new UserGroupNotFoundException;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUsergroupIdName($ug_ids, $search = '', $start = 0, $limit = 500)
    {
        return  $this->ug_repository->getUsergroupIdName($ug_ids, $search, $start, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function getUsergroupDetailsByDate($date_range)
    {
        return  $this->ug_repository->getUsergroupDetailsByDate($date_range);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserGroupsUsingID($ugid = 'ALL')
    {
        return $this->ug_repository->getUserGroupsUsingID($ugid);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserGroups($filter_params = [])
    {
        return $this->ug_repository->get($filter_params);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserGroupsByIds($ug_ids)
    {
        return $this->ug_repository->getUserGroupsByIds($ug_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getUsersByUserGroupIds($ug_ids)
    {
        return $this->ug_repository->getUsersByUserGroupIds($ug_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getInActiveUserGroupCount($ug_names)
    {
        return $this->ug_repository->getInActiveUserGroupCount($ug_names);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserUsergroupRelation($ug_names)
    {
        return $this->ug_repository->getUserUsergroupRelation($ug_names);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserGroupCount($userGroupName)
    {
        return $this->ug_repository->getUserGroupCount($userGroupName);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserGroupIDByUserGroupName($user_group)
    {
        return $this->ug_repository->getUserGroupIDByUserGroupName($user_group);
    }

    /**
     * {@inheritdoc}
     */
    public function addUserGroupRelation($key, $fieldarr = [], $id)
    {
        return $this->ug_repository->addUserGroupRelation($key, $fieldarr, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function updateUserGroupRelation($key, $arrname, $updateArr, $overwrite = false)
    {
        return $this->ug_repository->updateUserGroupRelation($key, $arrname, $updateArr, $overwrite);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getUserGroupChannels($ug_id)
    {
        return $this->ug_repository->getUserGroupChannels($ug_id);
    }

    public function removeUserGroupSurvey($ug_id, $fieldarr, $sid)
    {
        return $this->ug_repository->removeUserGroupSurvey($ug_id, $fieldarr, $sid);
    }

    public function addUserGroupSurvey($ug_id, $fieldarr, $sid)
    {
        return $this->ug_repository->addUserGroupSurvey($ug_id, $fieldarr, $sid);
    }

    public function removeUserGroupAssignment($ug_id, $fieldarr, $aid)
    {
        return $this->ug_repository->removeUserGroupAssignment($ug_id, $fieldarr, $aid);
    }

    public function addUserGroupAssignment($ug_id, $fieldarr, $aid)
    {
        return $this->ug_repository->addUserGroupAssignment($ug_id, $fieldarr, $aid);
    }
}
