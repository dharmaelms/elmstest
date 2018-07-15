<?php

namespace App\Model;

use Auth;
use Moloquent;
use App\Model\User;
use App\Model\Program;
use stdClass;
use App\Model\Assignment\Entity\Assignment;
use App\Model\Package\Entity\Package;
use App\Model\Survey\Entity\Survey;

class UserGroup extends Moloquent
{

    protected $table = 'usergroups';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    protected $primaryKey = 'ugid';

    //function to get user groups to display in the list according to filter
    public static function getAllUserGroups($status)
    {
        if ($status == 'ALL') {
            $usergroups = self::where('status', '!=', 'DELETED')->where('_id', '!=', 'ugid')->get(['ugid', 'usergroup_name', 'parent_usergroup', 'usergroup_email', 'status', 'created_at', 'description'])->toArray();
        } else {
            $usergroups = self::where('status', '=', $status)->get(['ugid', 'usergroup_name', 'parent_usergroup', 'usergroup_email', 'status', 'created_at', 'description'])->toArray();
        }

        return $usergroups;
    }

    public static function getUserGroupsWithPagination($status = 'ALL', $start = 0, $limit = 10, $orderby = ['created_at' => 'desc'], $search = null, $relinfo = [], $filter_params = [])
    {
        $key = key($orderby);
        $value = $orderby[$key];
        if ($relinfo && key($relinfo) == 'contentfeed') {
            $field = 'relations.usergroup_feed_rel';
            $program = Program::getProgramDetailsByID((int)$relinfo[key($relinfo)]);
            if ($program['program_sub_type'] == 'collection') {
                $field = 'relations.usergroup_parent_feed_rel';
            }
        } elseif ($relinfo && key($relinfo) == 'course') {
            $field = 'relations.usergroup_course_rel';
        } elseif ($relinfo && key($relinfo) == 'user') {
            $field = 'relations.active_user_usergroup_rel';
        } elseif ($relinfo && key($relinfo) == 'quiz') {
            $field = 'relations.usergroup_quiz_rel';
        } elseif ($relinfo && key($relinfo) == 'announcement') {
            $field = 'relations.usergroup_announcement_rel';
        } elseif ($relinfo && key($relinfo) == 'dams') {
            $field = 'relations.usergroup_media_rel';
        } elseif ($relinfo && key($relinfo) == 'event') {
            $field = 'relations.usergroup_event_rel';
        } elseif ($relinfo && key($relinfo) == 'questionbank') {
            $field = 'relations.usergroup_questionbank_rel';
        } elseif ($relinfo && key($relinfo) == 'survey') {
            $field = 'survey';
        } elseif ($relinfo && key($relinfo) == 'assignment') {
            $field = 'assignment';
        }else {
            $field = '';
        }

        $assignable_user_group_ids = array_get($filter_params, 'assignable_user_group_ids', null);

        if ($status == 'ALL') {
            if ($search) {
                return self::where('usergroup_name', 'like', '%' . $search . '%')
                    ->orWhere('usergroup_email', 'like', '%' . $search . '%')
                    ->where('status', '!=', 'DELETED')
                    ->where('_id', '!=', 'ugid')
                    ->orderBy($key, $value)
                    ->skip((int)$start)
                    ->take((int)$limit)
                    ->get();
            } else {
                return self::where('status', '!=', 'DELETED')
                    ->where('_id', '!=', 'ugid')
                    ->orderBy($key, $value)
                    ->skip((int)$start)
                    ->take((int)$limit)
                    ->get();
            }
        }

        if ($status == 'assigned') {
            if ($search) {
                return self::where('usergroup_name', 'like', '%' . $search . '%')
                    ->orWhere('usergroup_email', 'like', '%' . $search . '%')
                    ->where('status', '=', 'ACTIVE')
                    ->where($field, '=', (int)$relinfo[key($relinfo)])
                    ->when(isset($assignable_user_group_ids), function ($query) use ($assignable_user_group_ids) {
                        return $query->whereIn('ugid', $assignable_user_group_ids);
                    })->orderBy($key, $value)
                    ->skip((int)$start)
                    ->take((int)$limit)
                    ->get();
            } else {
                return self::where('status', '=', 'ACTIVE')
                    ->where($field, '=', (int)$relinfo[key($relinfo)])
                    ->when(isset($assignable_user_group_ids), function ($query) use ($assignable_user_group_ids) {
                        return $query->whereIn('ugid', $assignable_user_group_ids);
                    })->orderBy($key, $value)
                    ->skip((int)$start)
                    ->take((int)$limit)
                    ->get();
            }
        }
        if ($status == 'nonassigned') {
            if ($search) {
                return self::where('usergroup_name', 'like', '%' . $search . '%')
                    ->orWhere('usergroup_email', 'like', '%' . $search . '%')
                    ->where('status', '=', 'ACTIVE')
                    ->where($field, '!=', (int)$relinfo[key($relinfo)])
                    ->when(isset($assignable_user_group_ids), function ($query) use ($assignable_user_group_ids) {
                        return $query->whereIn('ugid', $assignable_user_group_ids);
                    })->orderBy($key, $value)
                    ->skip((int)$start)
                    ->take((int)$limit)
                    ->get();
            } else {
                return self::where('status', '=', 'ACTIVE')
                    ->where($field, '!=', (int)$relinfo[key($relinfo)])
                    ->when(isset($assignable_user_group_ids), function ($query) use ($assignable_user_group_ids) {
                        return $query->whereIn('ugid', $assignable_user_group_ids);
                    })->orderBy($key, $value)
                    ->skip((int)$start)
                    ->take((int)$limit)
                    ->get();
            }
        } elseif ($search) {
            return self::where('usergroup_name', 'like', '%' . $search . '%')
                ->orWhere('usergroup_email', 'like', '%' . $search . '%')
                ->where('status', '=', $status)
                ->orderBy($key, $value)
                ->skip((int)$start)
                ->take((int)$limit)
                ->get();
        } else {
            return self::where('status', '=', $status)
                ->orderBy($key, $value)
                ->skip((int)$start)
                ->take((int)$limit)
                ->get();
        }
    }

    public static function getUserGroupsCount($status = 'ALL', $search = null, $relinfo = [], $filter_params = [])
    {
        if ($relinfo && key($relinfo) == 'contentfeed') {
            $field = 'relations.usergroup_feed_rel';
            $program = Program::getProgramDetailsByID((int)$relinfo[key($relinfo)]);
            if ($program['program_sub_type'] == 'collection') {
                $field = 'relations.usergroup_parent_feed_rel';
            }
        } elseif ($relinfo && key($relinfo) == 'course') {
            $field = 'relations.usergroup_course_rel';
        } elseif ($relinfo && key($relinfo) == 'user') {
            $field = 'relations.active_user_usergroup_rel';
        } elseif ($relinfo && key($relinfo) == 'quiz') {
            $field = 'relations.usergroup_quiz_rel';
        } elseif ($relinfo && key($relinfo) == 'announcement') {
            $field = 'relations.usergroup_announcement_rel';
        } elseif ($relinfo && key($relinfo) == 'dams') {
            $field = 'relations.usergroup_media_rel';
        } elseif ($relinfo && key($relinfo) == 'event') {
            $field = 'relations.usergroup_event_rel';
        } elseif ($relinfo && key($relinfo) == 'questionbank') {
            $field = 'relations.usergroup_questionbank_rel';
        } elseif ($relinfo && key($relinfo) == 'survey') {
            $field = 'survey';
        } elseif ($relinfo && key($relinfo) == 'assignment') {
            $field = 'assignment';
        } else {
            $field = '';
        }

        $assignable_user_group_ids = array_get($filter_params, 'assignable_user_group_ids', null);

        if ($status == 'ALL') {
            if ($search) {
                return self::where('usergroup_name', 'like', '%' . $search . '%')
                    ->orWhere('usergroup_email', 'like', '%' . $search . '%')
                    ->where('status', '!=', 'DELETED')
                    ->where('_id', '!=', 'ugid')
                    ->count();
            } else {
                return self::where('status', '!=', 'DELETED')->where('_id', '!=', 'ugid')->count();
            }
        }

        if ($status == 'assigned') {
            if ($search) {
                return self::where('usergroup_name', 'like', '%' . $search . '%')
                    ->orWhere('usergroup_email', 'like', '%' . $search . '%')
                    ->where('status', '=', 'ACTIVE')
                    ->where($field, '=', (int)$relinfo[key($relinfo)])
                    ->when(isset($assignable_user_group_ids), function ($query) use ($assignable_user_group_ids) {
                        return $query->whereIn('ugid', $assignable_user_group_ids);
                    })
                    ->count();
            } else {
                return self::where('status', '=', 'ACTIVE')
                    ->where($field, '=', (int)$relinfo[key($relinfo)])
                    ->when(isset($assignable_user_group_ids), function ($query) use ($assignable_user_group_ids) {
                        return $query->whereIn('ugid', $assignable_user_group_ids);
                    })
                    ->count();
            }
        }

        if ($status == 'nonassigned') {
            if ($search) {
                return self::where('usergroup_name', 'like', '%' . $search . '%')
                    ->orWhere('usergroup_email', 'like', '%' . $search . '%')
                    ->where('status', '=', 'ACTIVE')
                    ->where($field, '!=', (int)$relinfo[key($relinfo)])
                    ->when(isset($assignable_user_group_ids), function ($query) use ($assignable_user_group_ids) {
                        return $query->whereIn('ugid', $assignable_user_group_ids);
                    })
                    ->count();
            } else {
                return self::where('status', '=', 'ACTIVE')
                    ->where($field, '!=', (int)$relinfo[key($relinfo)])
                    ->when(isset($assignable_user_group_ids), function ($query) use ($assignable_user_group_ids) {
                        return $query->whereIn('ugid', $assignable_user_group_ids);
                    })->count();
            }
        } elseif ($search) {
            return self::where('usergroup_name', 'like', '%' . $search . '%')
                ->orWhere('usergroup_email', 'like', '%' . $search . '%')
                ->where('status', '=', $status)
                ->count();
        } else {
            return self::where('status', '=', $status)
                ->count();
        }
    }

    public static function getUserGroupsUsingID($ugid = 'ALL')
    {
        if ($ugid == 'ALL') {
            return self::where('status', '!=', 'DELETED')->where('_id', '!=', 'ugid')->get()->toArray();
        } else {
            return self::where('status', '!=', 'DELETED')->where('ugid', '=', (int)$ugid)->get()->toArray();
        }
    }

    public static function getActiveUserGroupsUsingID($ugid)
    {
        return self::where('status', '=', 'ACTIVE')->where('ugid', '=', (int)$ugid)->get()->toArray();
    }

    //function to get group names to use as parent group in user group add/edit
    public static function getAllUserGroupNames($ugid = null)
    {
        if ($ugid) {
            $usergroups = self::where('ugid', '!=', (int)$ugid)->where('status', '!=', 'DELETED')->get(['ugid', 'usergroup_name'])->toArray();
        } else {
            $usergroups = self::where('status', '!=', 'DELETED')->get(['ugid', 'usergroup_name'])->toArray();
        }

        return $usergroups;
    }

    public static function getInsertUserGroup($input)
    {
        $ugid = self::uniqueId();
        self::insert([
            'ugid' => (int)$ugid,
            'usergroup_name' => trim($input['usergroup_name']),
            'ug_name_lower' => trim($input['ug_name_lower']),
            //'parent_usergroup' => $input['parent_usergroup'],
            'usergroup_email' => $input['usergroup_email'],
            'description' => $input['description'],
            'status' => $input['status'],
            'relations' => new stdClass(),
            'created_at' => time(),
        ]);

        return $ugid;
    }

    //function to generate unique user id
    public static function uniqueId()
    {
        return Sequence::getSequence('ugid');
    }

    public static function getDelete($ugid)
    {
        $data = self::where('ugid', '=', (int)$ugid)->get(['relations', 'survey', 'assignment'])->first()->toArray();
        $relations = (isset($data['relations'])) ? $data['relations'] : [];
        $survey = array_get($data, 'survey', []);
        $assignment = array_get($data, 'assignment', []);
        if (isset($relations['active_user_usergroup_rel']) && !empty($relations['active_user_usergroup_rel'])) {
            return false;
        }

        self::where('ugid', '=', (int)$ugid)->update(['status' => 'DELETED']);

        //usergroup relation
        if (isset($relations['active_user_usergroup_rel'])) {
            foreach ($relations['active_user_usergroup_rel'] as $value) {
                $uids = User::where('uid', '=', (int)$value)->value('relations');
                $uids = array_get($uids, 'active_usergroup_user_rel', 'default');
                if (is_array($uids) && in_array($ugid, $uids)) {
                    User::removeUserRelation($value, ['active_usergroup_user_rel'], $ugid);
                }
            }
        }

        //dams relation
        if (isset($relations['usergroup_media_rel'])) {
            foreach ($relations['usergroup_media_rel'] as $value) {
                $ids = Dam::where('id', '=', (int)$value)->value('relations');
                $ids = array_get($ids, 'active_usergroup_media_rel', 'default');
                if (is_array($ids) && in_array($ugid, $ids)) {
                    Dam::removeMediaRelationId($value, ['active_usergroup_media_rel'], $ugid);
                }
            }
        }

        //feed relation
        if (isset($relations['usergroup_feed_rel'])) {
            foreach ($relations['usergroup_feed_rel'] as $value) {
                $pids = Program::where('program_id', '=', (int)$value)->value('relations');
                $pids = array_get($pids, 'active_usergroup_feed_rel', 'default');
                if (is_array($pids) && in_array($ugid, $pids)) {
                    Program::removeFeedRelation($value, ['active_usergroup_feed_rel'], $ugid);
                    TransactionDetail::updateStatusByLevel('usergroup', (int)$ugid, (int)$value, ['status' => 'inactive']);
                    // Also inactive the user transaction
                }
            }
        }

        //announcemnet relation
        if (isset($relations['usergroup_announcement_rel'])) {
            foreach ($relations['usergroup_announcement_rel'] as $value) {
                $aids = Announcement::where('announcement_id', '=', (int)$value)->value('relations');
                $aids = array_get($aids, 'active_usergroup_announcement_rel', 'default');
                if (is_array($aids) && in_array($ugid, $aids)) {
                    Announcement::removeAnnouncementRelation($value, ['active_usergroup_announcement_rel'], $ugid);
                }
            }
        }

        //quiz relation
        if (isset($relations['usergroup_quiz_rel'])) {
            foreach ($relations['usergroup_quiz_rel'] as $value) {
                $pids = Quiz::where('quiz_id', '=', (int)$value)->value('relations');
                $pids = array_get($pids, 'active_usergroup_quiz_rel', 'default');
                if (is_array($pids) && in_array($ugid, $pids)) {
                    Quiz::removeQuizRelation($value, ['active_usergroup_quiz_rel'], $ugid);
                }
            }
        }

        //event relation
        if (isset($relations['usergroup_event_rel'])) {
            foreach ($relations['usergroup_event_rel'] as $value) {
                $eids = Event::where('event_id', '=', (int)$value)->value('relations');
                $eids = array_get($eids, 'active_usergroup_event_rel', 'default');
                if (is_array($eids) && in_array($ugid, $eids)) {
                    Event::removeEventRelation($value, ['active_usergroup_event_rel'], $ugid);
                }
            }
        }

        //questionbank relation
        if (isset($relations['usergroup_questionbank_rel'])) {
            foreach ($relations['usergroup_questionbank_rel'] as $value) {
                $qbids = QuestionBank::where('question_bank_id', '=', (int)$value)->value('relations');
                $qbids = array_get($qbids, ' active_usergroup_questionbank_rel', 'default');
                if (is_array($qbids) && in_array($ugid, $qbids)) {
                    QuestionBank::removeQuestionBankRelation($value, [' active_usergroup_questionbank_rel'], $ugid);
                }
            }
        }

        //survey relation
        if (isset($survey)) {
            foreach ($survey as $value) {
                $sids = Survey::where('id', '=', (int)$value)->value('usergroups');
                if (is_array($sids) && in_array($ugid, $sids)) {
                    Survey::pullSurveyRelations($value, ['usergroups'], $ugid);
                }
            }
        }

        //Assignment relation
        if (isset($assignment)) {
            foreach ($assignment as $value) {
                $aids = Assignment::where('id', '=', (int)$value)->value('usergroups');
                if (is_array($aids) && in_array($ugid, $aids)) {
                    Assignment::pullAssignmentRelations($value, ['usergroups'], $ugid);
                }
            }
        }

        return true;
    }

    public static function getUpdateUserGroup($ugid, $input)
    {
        self::where('ugid', '=', (int)$ugid)->update([
            'usergroup_name' => $input['usergroup_name'],
            'ug_name_lower' => $input['ug_name_lower'],
            //'parent_usergroup' => $input['parent_usergroup'],
            'usergroup_email' => $input['usergroup_email'],
            'description' => $input['description'],
            'status' => $input['status'],
            'updated_at' => time(),
        ]);

        if ($input['status'] == 'ACTIVE') {
            self::getActivateGroup($ugid);
        } elseif ($input['status'] == 'IN-ACTIVE') {
            self::getInactivateGroup($ugid);
        }
    }

    //function to check the group name uniqueness in edit
    public static function pluckGroupName($ugid, $usergroup_name)
    {
        return self::where('ugid', '!=', (int)$ugid)->where('usergroup_name', '=', $usergroup_name)->value('usergroup_name');
    }

    //function to check the group name uniqueness in edit
    public static function pluckGroupNameLower($ugid, $usergroup_name)
    {
        return self::where('ugid', '!=', (int)$ugid)->where('ug_name_lower', '=', strtolower($usergroup_name))->value('usergroup_name');
    }

    //function to check the group email uniqueness in edit
    public static function pluckGroupEmail($ugid, $usergroup_email)
    {
        return self::where('ugid', '!=', (int)$ugid)->where('usergroup_email', '=', $usergroup_email)->value('usergroup_email');
    }

    public static function updateUserGroupRelation($key, $arrname, $updateArr, $overwrite = false)
    {
        if ($overwrite) {
            self::where('ugid', (int)$key)->unset('relations.' . $arrname);
            self::where('ugid', (int)$key)->update(['relations.' . $arrname => $updateArr]);
        } else {
            self::where('ugid', (int)$key)->push('relations.' . $arrname, $updateArr, true);
        }

        return self::where('ugid', (int)$key)->update(['updated_at' => time()]);
    }

    //function to get the assigned user ids from particular user group
    public static function getAssignedUsers($ugid)
    {
        if (is_array($ugid)) {
            $userids = self::whereIn('ugid', $ugid)->value('relations');
            $userids = array_get($userids, 'active_user_usergroup_rel', 'default');
        } else {
            $userids = self::where('ugid', '=', (int)$ugid)->value('relations');
            $userids = array_get($userids, 'active_user_usergroup_rel', 'default');
        }

        return $userids;
    }

    public static function pluckStatus($ugid)
    {
        return self::where('ugid', '=', (int)$ugid)->value('status');
    }

    //function to get the assigned content feed ids from particular user
    public static function getAssignedContentFeed($ugid)
    {
        $pids = self::where('ugid', '=', (int)$ugid)->value('relations');
        $pids = array_get($pids, 'usergroup_feed_rel', 'default');

        return $pids;
    }

    /* Added by Cerlin*/

    public static function removeUserGroupRelation($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('ugid', $key)->pull('relations.' . $field, (int)$id);
        }

        return self::where('ugid', $key)->update(['updated_at' => time()]);
    }

    public static function addUserGroupRelation($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('ugid', $key)->push('relations.' . $field, (int)$id, true);
        }

        return self::where('ugid', $key)->update(['updated_at' => time()]);
    }

    /* Added by Cerlin*/

    public static function getActivateGroup($ugid)
    {
        self::where('ugid', '=', (int)$ugid)->update(['status' => 'ACTIVE']);
        $relations = self::where('ugid', '=', (int)$ugid)->value('relations');

        //user relation
        if (isset($relations['active_user_usergroup_rel'])) {
            foreach ($relations['active_user_usergroup_rel'] as $value) {
                $uids = User::where('uid', '=', (int)$value)->value('relations');
                $uids = array_get($uids, 'inactive_usergroup_user_rel', 'default');
                if (is_array($uids) && in_array($ugid, $uids)) {
                    User::removeUserRelation($value, ['inactive_usergroup_user_rel'], $ugid);
                    User::addUserRelation($value, ['active_usergroup_user_rel'], $ugid);
                }
            }
        }

        //dams relation
        if (isset($relations['usergroup_media_rel'])) {
            foreach ($relations['usergroup_media_rel'] as $value) {
                $ids = Dam::where('id', '=', (int)$value)->value('relations');
                $ids = array_get($ids, 'inactive_usergroup_media_rel', 'default');
                if (is_array($ids) && in_array($ugid, $ids)) {
                    Dam::removeMediaRelationId($value, ['inactive_usergroup_media_rel'], $ugid);
                    Dam::addMediaRelation($value, ['active_usergroup_media_rel'], $ugid);
                }
            }
        }

        //feed relation
        if (isset($relations['usergroup_feed_rel'])) {
            foreach ($relations['usergroup_feed_rel'] as $value) {
                $pids = Program::where('program_id', '=', (int)$value)->value('relations');
                $pids = array_get($pids, 'inactive_usergroup_feed_rel', 'default');
                if (is_array($pids) && in_array($ugid, $pids)) {
                    Program::removeFeedRelation($value, ['inactive_usergroup_feed_rel'], $ugid);
                    Program::addFeedRelation($value, ['active_usergroup_feed_rel'], $ugid);
                }
            }
        }

        //announcemnet relation
        if (isset($relations['usergroup_announcement_rel'])) {
            foreach ($relations['usergroup_announcement_rel'] as $value) {
                $aids = Announcement::where('announcement_id', '=', (int)$value)->value('relations');
                $aids = array_get($aids, 'inactive_usergroup_announcement_rel', 'default');
                if (is_array($aids) && in_array($ugid, $aids)) {
                    Announcement::removeAnnouncementRelation($value, ['inactive_usergroup_announcement_rel'], $ugid);
                    Announcement::addAnnouncementRelation($value, ['active_usergroup_announcement_rel'], $ugid);
                }
            }
        }

        //quiz relation
        if (isset($relations['usergroup_quiz_rel'])) {
            foreach ($relations['usergroup_quiz_rel'] as $value) {
                $qids = Quiz::where('quiz_id', '=', (int)$value)->value('relations');
                $qids = array_get($qids, 'inactive_usergroup_quiz_rel', 'default');
                if (is_array($qids) && in_array($ugid, $qids)) {
                    Quiz::removeQuizRelation($value, ['inactive_usergroup_quiz_rel'], $ugid);
                    Quiz::addQuizRelation($value, ['active_usergroup_quiz_rel'], $ugid);
                }
            }
        }

        //event relation
        if (isset($relations['usergroup_event_rel'])) {
            foreach ($relations['usergroup_event_rel'] as $value) {
                $eids = Event::where('event_id', '=', (int)$value)->value('relations');
                $eids = array_get($eids, 'inactive_usergroup_event_rel', 'default');
                if (is_array($eids) && in_array($ugid, $eids)) {
                    Event::removeEventRelation($value, ['inactive_usergroup_event_rel'], $ugid);
                    Event::addEventRelation($value, ['active_usergroup_event_rel'], $ugid);
                }
            }
        }

        //questionbank relation
        if (isset($relations['usergroup_questionbank_rel'])) {
            foreach ($relations['usergroup_questionbank_rel'] as $value) {
                $qbids = QuestionBank::where('question_bank_id', '=', (int)$value)->value('relations');
                $qbids = array_get($qbids, 'inactive_usergroup_questionbank_rel', 'default');
                if (is_array($qbids) && in_array($ugid, $qbids)) {
                    QuestionBank::removeQuestionBankRelation($value, ['inactive_usergroup_questionbank_rel'], $ugid);
                    QuestionBank::addQuestionBankRelation($value, [' active_usergroup_questionbank_rel'], $ugid);
                }
            }
        }
    }

    public static function getInactivateGroup($ugid)
    {
        self::where('ugid', '=', (int)$ugid)->update(['status' => 'IN-ACTIVE']);
        $relations = self::where('ugid', '=', (int)$ugid)->value('relations');

        //usergroup relation
        if (isset($relations['active_user_usergroup_rel'])) {
            foreach ($relations['active_user_usergroup_rel'] as $value) {
                $uids = User::where('uid', '=', (int)$value)->value('relations');
                $uids = array_get($uids, 'active_usergroup_user_rel', 'default');
                if (is_array($uids) && in_array($ugid, $uids)) {
                    User::removeUserRelation($value, ['active_usergroup_user_rel'], $ugid);
                    User::addUserRelation($value, ['inactive_usergroup_user_rel'], $ugid);
                }
            }
        }

        //dams relation
        if (isset($relations['usergroup_media_rel'])) {
            foreach ($relations['usergroup_media_rel'] as $value) {
                $ids = Dam::where('id', '=', (int)$value)->value('relations');
                $ids = array_get($ids, 'active_usergroup_media_rel', 'default');
                if (is_array($ids) && in_array($ugid, $ids)) {
                    Dam::removeMediaRelationId($value, ['active_usergroup_media_rel'], $ugid);
                    Dam::addMediaRelation($value, ['inactive_usergroup_media_rel'], $ugid);
                }
            }
        }

        //feed relation
        if (isset($relations['usergroup_feed_rel'])) {
            foreach ($relations['usergroup_feed_rel'] as $value) {
                $pids = Program::where('program_id', '=', (int)$value)->value('relations');
                $pids = array_get($pids, 'active_usergroup_feed_rel', 'default');
                if (is_array($pids) && in_array($ugid, $pids)) {
                    Program::removeFeedRelation($value, ['active_usergroup_feed_rel'], $ugid);
                    Program::addFeedRelation($value, ['inactive_usergroup_feed_rel'], $ugid);
                }
            }
        }

        //announcemnet relation
        if (isset($relations['usergroup_announcement_rel'])) {
            foreach ($relations['usergroup_announcement_rel'] as $value) {
                $aids = Announcement::where('announcement_id', '=', (int)$value)->value('relations');
                $aids = array_get($aids, 'active_usergroup_announcement_rel', 'default');
                if (is_array($aids) && in_array($ugid, $aids)) {
                    Announcement::removeAnnouncementRelation($value, ['active_usergroup_announcement_rel'], $ugid);
                    Announcement::addAnnouncementRelation($value, ['inactive_usergroup_announcement_rel'], $ugid);
                }
            }
        }

        //quiz relation
        if (isset($relations['usergroup_quiz_rel'])) {
            foreach ($relations['usergroup_quiz_rel'] as $value) {
                $qids = Quiz::where('quiz_id', '=', (int)$value)->value('relations');
                $qids = array_get($qids, 'active_usergroup_quiz_rel', 'default');
                if (is_array($qids) && in_array($ugid, $qids)) {
                    Quiz::removeQuizRelation($value, ['active_usergroup_quiz_rel'], $ugid);
                    Quiz::addQuizRelation($value, ['inactive_usergroup_quiz_rel'], $ugid);
                }
            }
        }

        //event relation
        if (isset($relations['usergroup_event_rel'])) {
            foreach ($relations['usergroup_event_rel'] as $value) {
                $eids = Event::where('event_id', '=', (int)$value)->value('relations');
                $eids = array_get($eids, 'active_usergroup_event_rel', 'default');
                if (is_array($eids) && in_array($ugid, $eids)) {
                    Event::removeEventRelation($value, ['active_usergroup_event_rel'], $ugid);
                    Event::addEventRelation($value, ['inactive_usergroup_event_rel'], $ugid);
                }
            }
        }

        //questionbank relation
        if (isset($relations['usergroup_questionbank_rel'])) {
            foreach ($relations['usergroup_questionbank_rel'] as $value) {
                $qbids = QuestionBank::where('question_bank_id', '=', (int)$value)->value('relations');
                $qbids = array_get($qbids, 'active_usergroup_questionbank_rel', 'default');
                if (is_array($qbids) && in_array($ugid, $qbids)) {
                    QuestionBank::removeQuestionBankRelation($value, ['active_usergroup_questionbank_rel'], $ugid);
                    QuestionBank::addQuestionBankRelation($value, ['inactive_usergroup_questionbank_rel'], $ugid);
                }
            }
        }
    }

    /*for statistic */
    public static function getLastThirtydaysCreatedUserGroupCount()
    {
        return self::where('status', '=', 'ACTIVE')->where('created_at', '>', strtotime('-30 day', time()))->get()->count();
    }

    /*for report*/

    public static function getFeedList($gids = [])
    {
        if (!empty($gids)) {
            return self::where('status', '=', 'ACTIVE')
                ->whereIn('ugid', $gids)
                ->get(['relations.usergroup_feed_rel'])
                ->toArray();
        } else {
            return false;
        }
    }

    /*for get announcement list*/
    public static function getAnnouncementList($gids = [])
    {
        if (!empty($gids)) {
            return self::where('status', '=', 'ACTIVE')
                ->whereIn('ugid', $gids)
                ->get(['relations.usergroup_announcement_rel'])
                ->toArray();
        } else {
            return [];
        }
    }

    /* for reports*/
    public static function getUsergroupDetails($ugid = [])
    {
        return self::where('status', '=', 'ACTIVE')
            ->whereIn('ugid', $ugid)
            ->get();
    }

    /* to get user IDs from group IDs */
    public static function getUserIDsUsingGroupIDs($gids = [])
    {
        if (!empty($gids)) {
            $uids = self::whereIn('ugid', $gids)
                ->where('status', '!=', 'DELETED')
                ->orderby('ug_name_lower', 'desc')
                ->get()
                ->toArray();
            $data = [];
            $users_ids = $announcement_ids = $channel_ids = [];

            foreach ($uids as $uid) {
                $ugname = '';
                $ugname .= $uid['usergroup_name'];
                if ($uid['status'] != 'ACTIVE') {
                    $ugname .= '<b> (' . $uid['status'] . ')</b>';
                }
                $data['user_group'][] = $ugname;
                $data['user_group_status'][$uid['usergroup_name']] = $uid['status'];
                if (isset($uid['relations']['active_user_usergroup_rel']) && !empty($uid['relations']['active_user_usergroup_rel'])) {
                    foreach ($uid['relations']['active_user_usergroup_rel'] as $user) {
                        $users_ids[] = $user;
                    }
                }

                if (isset($uid['relations']['inactive_user_usergroup_rel']) && !empty($uid['relations']['inactive_user_usergroup_rel'])) {
                    foreach ($uid['relations']['inactive_user_usergroup_rel'] as $user) {
                        $users_ids[] = $user;
                    }
                }

                if (isset($uid['relations']['usergroup_announcement_rel']) && !empty($uid['relations']['usergroup_announcement_rel'])) {
                    foreach ($uid['relations']['usergroup_announcement_rel'] as $announcement) {
                        $announcement_ids[] = $announcement;
                    }
                }

                if (isset($uid['relations']['usergroup_feed_rel']) && !empty($uid['relations']['usergroup_feed_rel'])) {
                    foreach ($uid['relations']['usergroup_feed_rel'] as $channel) {
                        $channel_ids[] = $channel;
                    }
                }
            }
            $data['users_ids'] = $users_ids;
            $data['announcement_ids'] = $announcement_ids;
            $data['channel_ids'] = $channel_ids;
            return $data;
        } else {
            return [];
        }
    }

    // to check the existance of user group
    public static function getUserGroupCount($userGroupName)
    {
        return self::where('ug_name_lower', $userGroupName)->where('status', '=', 'ACTIVE')->count();
    }

    public static function getUserGroupId($userGroupName)
    {
        return self::where('ug_name_lower', $userGroupName)->value('ugid');
    }

    public static function getUserGroupNames($arr)
    {
        if (is_array($arr)) {
            return self::whereIn('ugid', $arr)->where('status', '=', 'ACTIVE')->lists('usergroup_name')->all();
        } else {
            return [];
        }
    }

    /**
     * Get group belongs to many user 
     *
     * @return \Jenssegers\Mongodb\Relations\BelongsToMany
     */
    public function user()
    {
        return $this->belongsToMany(User::class, 'relations.active_user_usergroup_rel', 'relations. active_usergroup_user_rel');
    }

    /**
     * Programs belongs to many user group 
     *
     * @return \Jenssegers\Mongodb\Relations\BelongsToMany
     */
    public function program()
    {
        return $this->belongsToMany(
            Program::class,
            'relations.usergroup_feed_rel',
            'relations.active_usergroup_feed_rel'
        );
    }

    /**
     * package belongs to many user group 
     *
     * @return \Jenssegers\Mongodb\Relations\BelongsToMany
     */
    public function package()
    {
        return $this->belongsToMany(
            Package::class,
            'package_ids',
            'user_group_ids'
        );
    }

    public function scopeFilter($query, $filter_params = [])
    {
        return $query->when(
            isset($filter_params["ugid"]),
            function ($query) use ($filter_params) {
                if (is_array($filter_params["ugid"])) {
                    return $query->whereIn("ugid", $filter_params["ugid"]);
                } else {
                    return $query->where("ugid", (int) $filter_params["ugid"]);
                }
            }
        )
        ->when(
            isset($filter_params["status"]),
            function ($query) use ($filter_params) {
                return $query->whereIn("status", $filter_params["status"]);
            }
        )
        ->when(
            !empty($filter_params["search_key"]),
            function ($query) use ($filter_params) {
                return $query->orWhere("usergroup_name", "like", "%{$filter_params["search_key"]}%")
                    ->orWhere("usergroup_name", "like", "%{$filter_params["search_key"]}%")
                    ->orWhere("usergroup_name", "like", "%{$filter_params["search_key"]}%");
            }
        )->when(
            !empty($filter_params["order_by"]),
            function ($query) use ($filter_params) {
                return $query->orderBy(
                    $filter_params["order_by"],
                    isset($filter_params["order_by_dir"])? $filter_params["order_by_dir"] : "desc"
                );
            }
        )->when(
            isset($filter_params["start"]),
            function ($query) use ($filter_params) {
                return $query->skip((int)$filter_params["start"]);
            }
        )->when(
            isset($filter_params["limit"]),
            function ($query) use ($filter_params) {
                return $query->take((int)$filter_params["limit"]);
            }
        );
    }

    /**
     * Method used to get user group id by user group name
     * @param $user_group
     * @return array
     */
    public static function getUserGroupIDByUserGroupName($user_group){
        return self::whereIn('ug_name_lower', $user_group)
            ->get(['ugid'])
            ->toArray();
    }

    public static function removeUserGroupSurvey($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('ugid', (int)$key)->pull($field, (int)$id);
        }

        return self::where('ugid', (int)$key)->update(['updated_at' => time()]);
    }

    public static function addUserGroupSurvey($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('ugid', (int)$key)->push($field, (int)$id, true);
        }

        return self::where('ugid', (int)$key)->update(['updated_at' => time()]);
    }

    public static function removeUserGroupAssignment($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('ugid', (int)$key)->pull($field, (int)$id);
        }

        return self::where('ugid', (int)$key)->update(['updated_at' => time()]);
    }

    public static function addUserGroupAssignment($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('ugid', (int)$key)->push($field, (int)$id, true);
        }

        return self::where('ugid', (int)$key)->update(['updated_at' => time()]);
    }
}
