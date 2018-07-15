<?php

namespace App\Model\UserGroup\Repository;

use App\Model\Program;
use App\Model\UserGroup;
use App\Model\Package\Repository\IPackageRepository;
use App\Exceptions\UserGroup\UserGroupNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Enums\UserGroup\UserGroupStatus;

/**
 * class UserGroupRepository
 * @package App\Model\UserGroup\Repository;
 */
class UserGroupRepository implements IUserGroupRepository
{
    private $package_repo;

    function __construct(IPackageRepository $package_repo)
    {
        $this->package_repo = $package_repo;
    }
    /**
     * @inheritDoc
     */
    public function find($id)
    {
        try {
            return UserGroup::findOrFail((int) $id);
        } catch (ModelNotFoundException $e) {
            throw new UserGroupNotFoundException();
        }
    }

    /**
    * {@inheritdoc}
    */
    public function getUsergroupIdName($ug_ids = 'ALL', $search = '', $start = 0, $limit = 500)
    {
        $query = UserGroup::where('status', '!=', 'DELETED')->where('_id', '!=', 'ugid');
        if ($ug_ids != 'ALL') {
            $query->whereIn('ugid', $ug_ids);
        }
        if ($search != '') {
            $query->where('usergroup_name', 'like', "%" . $search . "%");
        }
        
        $query->skip((int)$start)
            ->take((int)$limit)
            ->orderBy('usergroup_name', 'asc')
            ->get(['ugid', 'usergroup_name', 'relations']);

        return $query->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getUsergroupDetailsByDate($date_range)
    {
        if ($date_range == 'all_time') {
            return UserGroup::where('status', '=', 'ACTIVE')
                    ->get();
        } else {
            return UserGroup::where('status', '=', 'ACTIVE')
                    ->where('created_at', '>=', (int)array_get($date_range, 'start_date'))
                    ->where('created_at', '<=', (int)array_get($date_range, 'end_date'))
                    ->get();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUserGroupsUsingID($ugid = 'ALL')
    {

        if ($ugid == 'ALL') {
            return UserGroup::where('status', '!=', 'DELETED')->where('_id', '!=', 'ugid')->get()->toArray();
        } elseif (is_array($ugid) && !empty($ugid)) {
            return UserGroup::where('status', '!=', 'DELETED')->whereIn('ugid', $ugid)->get()->toArray();
        } elseif (is_numeric($ugid) && $ugid > 0) {
            return UserGroup::where('status', '!=', 'DELETED')->where('ugid', '=', (int)$ugid)->get()->toArray();
        } else {
            return [];
        }
    }

    /**
     * @inheritDoc
     */
    public function getTotalUserGroupsCount()
    {
        return UserGroup::filter(["status" => [UserGroupStatus::ACTIVE]])
                    ->count();
    }

    /**
     * {@inheritdoc}
     */
    public function get($filter_params = [], $columns = [])
    {
        return UserGroup::filter($filter_params)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserGroupsByIds($ug_ids)
    {
        return UserGroup::where('status', '!=', 'DELETED')->whereIn('ugid', $ug_ids)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getUsersByUserGroupIds($ug_ids)
    {
        return UserGroup::whereIn('ugid', $ug_ids)
            ->pluck('relations.active_user_usergroup_rel')
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function updateByKey($key, $value, $data)
    {
        return UserGroup::where($key, $value)->update($data);
    }

    /**
     * {@inheritdoc}
    */
    public function getInActiveUserGroupCount($ug_names)
    {   
        return UserGroup::whereIn('ug_name_lower', $ug_names)->where('status', '=', 'IN-ACTIVE')->count();
    }

    /**
     * {@inheritdoc}
    */
    public function getUserUsergroupRelation($ug_names)
    {
       return UserGroup::whereIn('ug_name_lower', $ug_names)->where('status', '=', 'ACTIVE')
       ->where('relations.active_user_usergroup_rel', 'exists', true)
       ->pluck('relations.active_user_usergroup_rel')
       ->toArray(); 
    }

    /**
     * {@inheritdoc}
    */
    public function getUserGroupCount($userGroupName)
    {
        return UserGroup::where('ug_name_lower', $userGroupName)->count();
    }

     /**
     * {@inheritdoc}
     */
    public function getUserGroupIDByUserGroupName($user_group)
    {
       return UserGroup::whereIn('ug_name_lower', $user_group)
            ->get(['ugid'])
            ->toArray(); 
    }

    /**
     * {@inheritdoc}
     */
    public function addUserGroupRelation($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            UserGroup::where('ugid', $key)->push('relations.' . $field, (int)$id, true);
        }

        return UserGroup::where('ugid', $key)->update(['updated_at' => time()]);
    }

    /**
     * {@inheritdoc}
     */
    public function updateUserGroupRelation($key, $arrname, $updateArr, $overwrite = false)
    {
        if ($overwrite) {
            UserGroup::where('ugid', (int)$key)->unset('relations.' . $arrname);
            UserGroup::where('ugid', (int)$key)->update(['relations.' . $arrname => $updateArr]);
        } else {
            UserGroup::where('ugid', (int)$key)->push('relations.' . $arrname, $updateArr, true);
        }

        return UserGroup::where('ugid', (int)$key)->update(['updated_at' => time()]);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserGroupChannels($ug_id)
    {
        $ug_details =  UserGroup::where('ugid', '=', (int)$ug_id)->first(['relations', 'package_ids']);
        $package_ids = array_get($ug_details, 'package_ids', []);
        $package_details = $this->package_repo->get(["in_ids" => $package_ids], ['program_ids']);
        $pack_pro_ids = $package_details->lists('program_ids')->flatten()->unique()->all();
        $ug_programs = array_get($ug_details->relations, 'usergroup_feed_rel', []);
        $program_ids = array_unique(array_merge($pack_pro_ids, $ug_programs));
        return Program::where('status', '!=', 'DELETED')->whereIn('program_id', $program_ids)->get()
                ->pluck('program_id')->all();
    }

    public function removeUserGroupSurvey($user_id, $fieldarr, $sid)
    {
        return UserGroup::removeUserGroupSurvey($user_id, $fieldarr, $sid);
    }

    public function addUserGroupSurvey($ug_id, $fieldarr, $sid)
    {
        return UserGroup::addUserGroupSurvey($ug_id, $fieldarr, $sid);
    }

    public function removeUserGroupAssignment($user_id, $fieldarr, $aid)
    {
        return UserGroup::removeUserGroupAssignment($user_id, $fieldarr, $aid);
    }

    public function addUserGroupAssignment($ug_id, $fieldarr, $aid)
    {
        return UserGroup::addUserGroupAssignment($ug_id, $fieldarr, $aid);
    }
}
