<?php

namespace App\Model;

use App\Libraries\moodle\MoodleAPI;
use Auth;
use DB;
use Hash;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Str;
use App\Model\Assignment\Entity\Assignment;
use App\Model\RolesAndPermissions\Entity\UserRoleAssignment;
use App\Model\Package\Entity\Package;
use App\Model\Survey\Entity\Survey;
use App\Exceptions\RolesAndPermissions\RoleNotFoundException;
use Moloquent;
use Session;
use Timezone;
use App\Events\Auth\Registered;
use Illuminate\Support\Facades\Schema;
use App\Model\Role;
use App\Model\UserGroup;
use App\Model\ErpUserLog;
use App\Model\ErpUserGroupLog;
use App\Enums\User\NDAStatus as NDA;
use URL;

class User extends Moloquent implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    protected $primaryKey = 'uid';
    
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'last_login_time'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'uid' => 'int'
    ];

    /**
     * Function generate unique auto incremented id for this collection.
     *
     * @return int
     */
    public static function getNextSequence()
    {
        return Sequence::getSequence('uid');
    }

    /**
     * this function is used to concatenate firstname and lastname
     */
    public function getFullNameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    //function to get users to display in the list according to filter
    public static function getAllUsers($status)
    {
        $query = self::where('_id', '!=', 'uid')->where('super_admin', '!=', true);
        if ($status == 'ALL') {
            $query->where('status', '!=', 'DELETED');
        } else {
            $query->where('status', '=', $status);
        }
        return $query->get([
            'uid',
            'firstname',
            'lastname',
            'role',
            'username',
            'email',
            'mobile',
            'created_at',
            'status',
            'app_registration'
        ])->toArray();
    }

    /*
    purpose: to get all the users with customefields(if availble)
    */
    public static function getAllUsersWithCustomFields($status)
    {
        $query = self::where('_id', '!=', 'uid')->where('super_admin', '!=', true);
        if ($status == 'ALL') {
            $query->where('status', '!=', 'DELETED');
        } else {
            $query->where('status', '=', $status);
        }
        return $query->get()->toArray();
    }


    public static function getUserNames($email)
    {
        return self::where('email', '=', $email)->get(['firstname', 'lastname']);
    }

    public static function getUsersWithPagination(
        $status = 'ALL',
        $start = 0,
        $limit = 10,
        $orderby = ['created_at' => 'desc'],
        $search = null,
        $relinfo = [],
        $users_to_be_excluded = [],
        $filter_params = []
    ) {
        $key = key($orderby);
        $value = $orderby[$key];
        if ($relinfo && key($relinfo) == 'contentfeed') {
            $field = 'relations.user_feed_rel';
            $program = Program::getProgramDetailsByID($relinfo['contentfeed']);
            if ($program['program_sub_type'] == 'collection') {
                $field = 'relations.user_parent_feed_rel';
            }
        } elseif ($relinfo && key($relinfo) == 'usergroup') {
            $field = 'relations.active_usergroup_user_rel';
        } elseif ($relinfo && key($relinfo) == 'quiz') {
            $field = 'relations.user_quiz_rel';
        } elseif ($relinfo && key($relinfo) == 'announcement') {
            $field = 'relations.user_announcement_rel';
        } elseif ($relinfo && key($relinfo) == 'dams') {
            $field = 'relations.user_media_rel';
        } elseif ($relinfo && key($relinfo) == 'event') {
            $field = 'relations.user_event_rel';
        } elseif ($relinfo && key($relinfo) == 'questionbank') {
            $field = 'relations.user_questionbank_rel';
        } elseif ($relinfo && key($relinfo) == 'survey') {
            $field = 'survey';
        } elseif ($relinfo && key($relinfo) == 'assignment') {
            $field = 'assignment';
        } else {
            $field = '';
        }

        $assignable_user_ids = array_get($filter_params, 'assignable_user_ids', null);
        $queries = self::where('_id', '!=', 'uid')
            ->whereNotIn('uid', $users_to_be_excluded)
            ->where('super_admin', '!=', true);
        if (($status == 'ACTIVE') || ($status == 'IN-ACTIVE')) {
            $queries->where('status', '=', $status);
        } else {
            $queries->where('status', '!=', 'DELETED');
        }
        if ($search) {
            $queries->orWhere('username', 'like', '%' . $search . '%')
                ->orWhere('firstname', 'like', '%' . $search . '%')
                ->orWhere('lastname', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%');
        }
        if ($status == 'assigned') {
            $queries->where($field, '=', (int)$relinfo[key($relinfo)])
                ->when(isset($assignable_user_ids), function ($query) use ($assignable_user_ids) {
                    return $query->whereIn('uid', $assignable_user_ids);
                });
        }
        if ($status == 'nonassigned') {
            $queries->where('status', '=', 'ACTIVE')
                ->where($field, '!=', (int)$relinfo[key($relinfo)])
                ->when(isset($assignable_user_ids), function ($query) use ($assignable_user_ids) {
                    return $query->whereIn('uid', $assignable_user_ids);
                });
        }
        return $queries->orderBy($key, $value)
            ->skip((int)$start)
            ->take((int)$limit)
            ->get();
    }

    public static function getUsersCount(
        $status = 'ALL',
        $search = null,
        $relinfo = [],
        $users_to_be_excluded = [],
        $filter_params = []
    ) {
        if ($relinfo && key($relinfo) == 'contentfeed') {
            $field = 'relations.user_feed_rel';
            $program = Program::getProgramDetailsByID($relinfo['contentfeed']);
            if ($program['program_sub_type'] == 'collection') {
                $field = 'relations.user_parent_feed_rel';
            }
        } elseif ($relinfo && key($relinfo) == 'usergroup') {
            $field = 'relations.active_usergroup_user_rel';
        } elseif ($relinfo && key($relinfo) == 'quiz') {
            $field = 'relations.user_quiz_rel';
        } elseif ($relinfo && key($relinfo) == 'announcement') {
            $field = 'relations.user_announcement_rel';
        } elseif ($relinfo && key($relinfo) == 'dams') {
            $field = 'relations.user_media_rel';
        } elseif ($relinfo && key($relinfo) == 'event') {
            $field = 'relations.user_event_rel';
        } elseif ($relinfo && key($relinfo) == 'questionbank') {
            $field = 'relations.user_questionbank_rel';
        } elseif ($relinfo && key($relinfo) == 'survey') {
            $field = 'survey';
        } elseif ($relinfo && key($relinfo) == 'assignment') {
            $field = 'assignment';
        } else {
            $field = '';
        }
        $assignable_user_ids = array_get($filter_params, 'assignable_user_ids', null);
        $queries = self::where('_id', '!=', 'uid')
            ->whereNotIn('uid', $users_to_be_excluded)
            ->where('super_admin', '!=', true);
        if (($status == 'ACTIVE') || ($status == 'IN-ACTIVE')) {
            $queries->where('status', '=', $status);
        } else {
            $queries->where('status', '!=', 'DELETED');
        }
        if ($search) {
            $queries->orWhere('username', 'like', '%' . $search . '%')
                ->orWhere('firstname', 'like', '%' . $search . '%')
                ->orWhere('lastname', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%');
        }
        if ($status == 'assigned') {
            $queries->where($field, '=', (int)$relinfo[key($relinfo)])
                ->when(isset($assignable_user_ids), function ($query) use ($assignable_user_ids) {
                    return $query->whereIn('uid', $assignable_user_ids);
                });
        }
        if ($status == 'nonassigned') {
            $queries->where('status', '=', 'ACTIVE')
                ->where($field, '!=', (int)$relinfo[key($relinfo)])
                ->when(isset($assignable_user_ids), function ($query) use ($assignable_user_ids) {
                    return $query->whereIn('uid', $assignable_user_ids);
                });
        }
        return $queries->count();
    }

    public static function getUsersUsingID($uid = 'ALL')
    {
        if ($uid == 'ALL') {
            return self::where('status', '!=', 'DELETED')->get()->toArray();
        } else {
            return self::where('status', '!=', 'DELETED')->where('uid', '=', (int)$uid)->get()->toArray();
        }
    }

    public static function getActiveUserUsingID($uid)
    {
        return self::where('status', '!=', 'DELETED')->where('uid', '=', (int)$uid)->get()->toArray();
    }

    //Function to get the channels assigned through usergroup for a specific user for the Group Channels column in the user list page
    public static function getUserGroupFeeds($uid)
    {
        $output = [];

        $groups = User::where('uid', '=', (int)$uid)->value('relations');

        if (!empty($groups['active_usergroup_user_rel'])) {
            $groups = $groups['active_usergroup_user_rel'];
            $array = Program::where('program_type', '=', 'content_feed')->where('program_sub_type', '!=', 'collection')->whereIn('relations.active_usergroup_feed_rel', $groups)->get()->toArray();
            if (!empty($array)) {
                foreach ($array as $key => $value) {
                    $result = array_intersect($value['relations']['active_usergroup_feed_rel'], $groups);
                    $group_names = UserGroup::whereIn('ugid', $result)->lists('usergroup_name')->all();
                    $name = [
                        'group_names' => implode(",", $group_names)
                    ];
                    $output[] = array_merge($array[$key], $name);
                }
                return $output;
            } else {
                return $output;
            }
        } else {
            return $output;
        }
    }

    //Function to get the channels count assigned through usergroup for a specific user for the Group Channels column in the user list page
    public static function getUserGroupFeedsCount($uid, $groups)
    {
        $count = Program::where('program_type', '=', 'content_feed')->where('program_sub_type', '!=', 'collection')->whereIn('relations.active_usergroup_feed_rel', $groups)->count();
        if (!empty($count)) {
            return $count;
        } else {
            return 0;
        }
    }

    public static function getInsertUsers($input, $userCustomFields = null, $import = null, $role = null, $timezone = null)
    {
        if ($import) {
            $status = 'ACTIVE';
        } else {
            $role = $input['role'];
            $status = $input['status'];
            if ($input['timezone']) {
                $timezone = $input['timezone'];
            } else {
                $timezone = config('app.default_timezone');
            }
        }
        $uid = self::uniqueId();
        $UserArray = [
            'uid' => (int)$uid,
            'firstname' => $input['firstname'],
            'lastname' => $input['lastname'],
            'email' => $input['email'],
            'mobile' => $input['mobile'],
            'username' => $input['username'],
            'password' => Hash::make($input['password']),
            'role' => (int)$role,
            'timezone' => $timezone,
            'status' => $status,
            'profile_pic' => '',
            //'relations' => new stdClass(),
            'created_at' => time(),
            'created_by' => Auth::user()->username,
            'authkey' => self::getAuthKey($input['username'], $input['password']), //authkey
            'nda_status' => NDA::NO_RESPONSE,

        ];
        $final_array = array_merge($UserArray, $userCustomFields);

        self::insert([$final_array]);
        $arrname = 'user_feed_rel';
        self::where('uid', $uid)->push('relations.' . $arrname, 0, true);
        self::where('uid', $uid)->pull('relations.' . $arrname, 0);

        if (!empty(SiteSetting::module('Lmsprogram', 'wstoken'))) {
            //lms create user in moodle
            $moodleapi = MoodleAPI::get_instance();
            $paramlist['username'] = $input['username'];
            $paramlist['password'] = $input['password'];
            $paramlist['firstname'] = $input['firstname'];
            $paramlist['lastname'] = $input['lastname'];
            $paramlist['email'] = $input['email'];
            $lmsuser = $moodleapi->moodle_user_create($paramlist);
            //if user having manager role assign moodle manager role
            if ((int)$role == 2) {
                $paramlist['userid'] = $lmsuser[0]['id'];
                $paramlist['roleid'] = 1;
                $paramlist['contextid'] = 1;
                $moodleapi->moodle_user_role_assign($paramlist);
            }
            //update ultron user collection with moodle userid
            self::where('uid', '=', (int)$uid)->update(['userid' => $lmsuser[0]['id']]);
        }


        // sending email to user
        $site_name = config('app.site_name');
        $to = $input['email'];
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
        $headers .= 'From:' . $site_name . "\r\n";
        $name = 'admin-register-user-template';
        $base_url = config('app.url');
        $email_details = Email::getEmail($name);
        $subject = $email_details[0]['subject'];
        $body = $email_details[0]['body'];
        $token = Str::random(64);

        /*creating password_reset log table and inserting a token  */
        $pw_reset = \DB::table(config('auth.passwords.users.table'))->insert(
            ['email' => $input['email'], 'token' => $token, 'created_at' => new \MongoDB\BSON\UTCDateTime()]
        );
        $reset_password = '<a href="' . $base_url . '/password/reset/'.$token.'">'.trans('admin/user.click_to_reset_password').'</a>';
        $support_email = config('mail.support');
        $name = ucwords($input['firstname']) . ' ' . ucwords($input['lastname']);

        $subject_find = ['<SITE NAME>'];
        $subject_replace = [$site_name];
        $subject = str_replace($subject_find, $subject_replace, $subject);

        $find = ['<NAME>', '<USERNAME>', '<SITE NAME>', '<EMAIL>', '<RESET PASSWORD>', '<SUPPORT EMAIL>'];
        $replace = [$name, $input['username'], $site_name, $input['email'], $reset_password, $support_email['address']];
        $body = str_replace($find, $replace, $body);

        Common::sendMailHtml($body, $subject, $to);

        //sending mail to admin
        if ($import == '') {
            $to = config('app.site_admin_email');
            $login_url = '<a href="' . $base_url . '/auth/login">Click here to login</a>';
            $site_admin_name = config('app.site_admin_name');
            $date_time = Timezone::convertFromUTC('@' . time(), config('app.default_timezone'), config('app.date_format'));
            $name = 'admin-register-admin-template';
            $role_name = Role::where('rid', '=', (int)$role)->value('name');
            $find = ['<SITE ADMIN NAME>', '<CREATED BY>', '<USERNAME>', '<EMAIL>', '<ROLE>', '<STATUS>', '<SITE NAME>', '<DATETIME>', '<LOGIN URL>'];
            $replace = [$site_admin_name, Auth::user()->username, $input['username'], $input['email'], $role_name, $status, $site_name, $date_time, $login_url];
            $email_details = Email::getEmail($name);
            $subject = $email_details[0]['subject'];
            $body = $email_details[0]['body'];
            $subject = str_replace($subject_find, $subject_replace, $subject);
            $body = str_replace($find, $replace, $body);
            Common::sendMailHtml($body, $subject, $to);
        }

        return $uid;
    }

    //function to generate unique user id
    public static function uniqueId()
    {
        $cursor = self::where('uid', '>', 0)->value('uid');
        if (count($cursor) == 0) {
            return 1;
        } else {
            $cursor = self::where('uid', '>', 0)->orderBy('uid', 'desc')->limit(1)->get(['uid'])->toArray();
            $uid = $cursor[0]['uid'] + 1;

            return $uid;
        }
    }

    // to check is role assigned to any user, in roles list to disable actions.
    public static function getIsAssigned($role_id)
    {
        $data = self::where('role', '=', (int)$role_id)->where('status', '!=', 'DELETED')->value('uid');

        return $data;
    }

    public static function getDelete($uid, $username, $email)
    {
        self::where('uid', '=', (int)$uid)->update(['status' => 'DELETED', 'username' => $username . '_' . time(), 'email' => $email . '_' . time()]);
        //delete user on lms
        $lmsuser = self::where('uid', '=', (int)$uid)->get()->toArray();
        if (!empty(SiteSetting::module('Lmsprogram', 'wstoken')) && isset($lmsuser[0]['userid'])) {
            $moodleapi = MoodleAPI::get_instance();
            $paramlist['id'] = $lmsuser[0]['userid'];
            $lmsuser = $moodleapi->moodle_user_delete($paramlist);
        }
        $data = self::where('uid', '=', (int)$uid)->get(['relations', 'survey', 'assignment'])->first()->toArray();
        $relations = (isset($data['relations'])) ? $data['relations'] : [];
        $survey = array_get($data, 'survey', []);
        $assignment = array_get($data, 'assignment', []);
        //usergroup relation
        if (isset($relations['active_usergroup_user_rel'])) {
            foreach ($relations['active_usergroup_user_rel'] as $value) {
                $ugids = UserGroup::where('ugid', '=', (int)$value)->value('relations');
                $ugids = array_get($ugids, 'active_user_usergroup_rel', 'default');
                if (is_array($ugids) && in_array($uid, $ugids)) {
                    UserGroup::removeUserGroupRelation($value, ['active_user_usergroup_rel'], $uid);
                }
            }
        }

        //dams relation
        if (isset($relations['user_media_rel'])) {
            foreach ($relations['user_media_rel'] as $value) {
                $ids = Dam::where('id', '=', (int)$value)->value('relations');
                $ids = array_get($ids, 'active_user_media_rel', 'default');
                if (is_array($ids) && in_array($uid, $ids)) {
                    Dam::removeMediaRelationId($value, ['active_user_media_rel'], $uid);
                }
            }
        }

        //feed relation
        if (isset($relations['user_feed_rel'])) {
            foreach ($relations['user_feed_rel'] as $value) {
                $pids = Program::where('program_id', '=', (int)$value)->value('relations');
                $pids = array_get($pids, 'active_user_feed_rel', 'default');
                if (is_array($pids) && in_array($uid, $pids)) {
                    Program::removeFeedRelation($value, ['active_user_feed_rel'], $uid);
                    TransactionDetail::updateStatusByLevel('user', (int)$uid, (int)$value, ['status' => 'inactive']);
                    // Also inactive the user transaction
                }
            }
        }

        //package relation
        if (isset($relations['user_parent_feed_rel'])) {
            foreach ($relations['user_parent_feed_rel'] as $value) {
                $pids = Program::where('program_id', '=', (int)$value)->value('relations');
                $pids = array_get($pids, 'active_user_feed_rel', 'default');
                if (is_array($pids) && in_array($uid, $pids)) {
                    Program::removeFeedRelation($value, ['active_user_feed_rel'], $uid);
                    TransactionDetail::updateStatusByLevel('user', (int)$uid, (int)$value, ['status' => 'inactive']);
                    // Also inactive the user transaction
                }
            }
        }

        //batch relation
        if (isset($relations['user_course_rel'])) {
            foreach ($relations['user_course_rel'] as $value) {
                $pids = Program::where('program_id', '=', (int)$value)->value('relations');
                $pids = array_get($pids, 'active_user_feed_rel', 'default');
                if (is_array($pids) && in_array($uid, $pids)) {
                    Program::removeFeedRelation($value, ['active_user_feed_rel'], $uid);
                    TransactionDetail::updateStatusByLevel('user', (int)$uid, (int)$value, ['status' => 'inactive']);
                    // Also inactive the user transaction
                }
            }
        }

        //announcemnet relation
        if (isset($relations['user_announcement_rel'])) {
            foreach ($relations['user_announcement_rel'] as $value) {
                $aids = Announcement::where('announcement_id', '=', (int)$value)->value('relations');
                $aids = array_get($aids, 'active_user_announcement_rel', 'default');
                if (is_array($aids) && in_array($uid, $aids)) {
                    Announcement::removeAnnouncementRelation($value, ['active_user_announcement_rel'], $uid);
                }
            }
        }

        //quiz relation
        if (isset($relations['user_quiz_rel'])) {
            foreach ($relations['user_quiz_rel'] as $value) {
                $pids = Quiz::where('quiz_id', '=', (int)$value)->value('relations');
                $pids = array_get($pids, 'active_user_quiz_rel', 'default');
                if (is_array($pids) && in_array($uid, $pids)) {
                    Quiz::removeQuizRelation($value, ['active_user_quiz_rel'], $uid);
                }
            }
        }

        //event relation
        if (isset($relations['user_event_rel'])) {
            foreach ($relations['user_event_rel'] as $value) {
                $eids = Event::where('event_id', '=', (int)$value)->value('relations');
                $eids = array_get($eids, 'active_user_event_rel', 'default');
                if (is_array($eids) && in_array($uid, $eids)) {
                    Event::removeEventRelation($value, ['active_user_event_rel'], $uid);
                }
            }
        }

        //questionbank relation
        if (isset($relations['user_questionbank_rel'])) {
            foreach ($relations['user_questionbank_rel'] as $value) {
                $qbids = QuestionBank::where('question_bank_id', '=', (int)$value)->value('relations');
                $qbids = array_get($qbids, 'active_user_questionbank_rel', 'default');
                if (is_array($qbids) && in_array($uid, $qbids)) {
                    QuestionBank::removeQuestionBankRelation($value, ['active_user_questionbank_rel'], $uid);
                }
            }
        }

        //survey relation
        if (isset($survey)) {
            foreach ($survey as $value) {
                $sids = Survey::where('id', '=', (int)$value)->value('users');
                if (is_array($sids) && in_array($uid, $sids)) {
                    Survey::pullSurveyRelations($value, ['users'], $uid);
                }
            }
        }

        //assignment relation
        if (isset($assignment)) {
            foreach ($assignment as $value) {
                $aids = Assignment::where('id', '=', (int)$value)->value('users');
                if (is_array($aids) && in_array($uid, $aids)) {
                    Assignment::pullAssignmentRelations($value, ['users'], $uid);
                }
            }
        }
    }

    public static function getActivate($uid)
    {
        self::where('uid', '=', (int)$uid)->update(['status' => 'ACTIVE']);
        $relations = self::where('uid', '=', (int)$uid)->value('relations');

        //usergroup relation
        if (isset($relations['active_usergroup_user_rel'])) {
            foreach ($relations['active_usergroup_user_rel'] as $value) {
                $ugids = UserGroup::where('ugid', '=', (int)$value)->value('relations');
                $ugids = array_get($ugids, 'inactive_user_usergroup_rel', 'default');
                if (is_array($ugids) && in_array($uid, $ugids)) {
                    UserGroup::removeUserGroupRelation($value, ['inactive_user_usergroup_rel'], $uid);
                    UserGroup::addUserGroupRelation($value, ['active_user_usergroup_rel'], $uid);
                }
            }
        }

        //dams relation
        if (isset($relations['user_media_rel'])) {
            foreach ($relations['user_media_rel'] as $value) {
                $ids = Dam::where('id', '=', (int)$value)->value('relations');
                $ids = array_get($ids, 'inactive_user_media_rel', 'default');
                if (is_array($ids) && in_array($uid, $ids)) {
                    Dam::removeMediaRelationId($value, ['inactive_user_media_rel'], $uid);
                    Dam::addMediaRelation($value, ['active_user_media_rel'], $uid);
                }
            }
        }

        //feed relation
        if (isset($relations['user_feed_rel'])) {
            foreach ($relations['user_feed_rel'] as $value) {
                $pids = Program::where('program_id', '=', (int)$value)->value('relations');
                $pids = array_get($pids, 'inactive_user_feed_rel', 'default');
                if (is_array($pids) && in_array($uid, $pids)) {
                    Program::removeFeedRelation($value, ['inactive_user_feed_rel'], $uid);
                    Program::addFeedRelation($value, ['active_user_feed_rel'], $uid);
                }
            }
        }

        //package relation
        if (isset($relations['user_parent_feed_rel'])) {
            foreach ($relations['user_parent_feed_rel'] as $value) {
                $pids = Program::where('program_id', '=', (int)$value)->value('relations');
                $pids = array_get($pids, 'inactive_user_feed_rel', 'default');
                if (is_array($pids) && in_array($uid, $pids)) {
                    Program::removeFeedRelation($value, ['inactive_user_feed_rel'], $uid);
                    Program::addFeedRelation($value, ['active_user_feed_rel'], $uid);
                }
            }
        }


        //batch relation
        if (isset($relations['user_course_rel'])) {
            foreach ($relations['user_course_rel'] as $value) {
                $pids = Program::where('program_id', '=', (int)$value)->value('relations');
                $pids = array_get($pids, 'inactive_user_feed_rel', 'default');
                if (is_array($pids) && in_array($uid, $pids)) {
                    Program::removeFeedRelation($value, ['inactive_user_feed_rel'], $uid);
                    Program::addFeedRelation($value, ['active_user_feed_rel'], $uid);
                }
            }
        }

        //announcemnet relation
        if (isset($relations['user_announcement_rel'])) {
            foreach ($relations['user_announcement_rel'] as $value) {
                $aids = Announcement::where('announcement_id', '=', (int)$value)->value('relations');
                $aids = array_get($aids, 'inactive_user_announcement_rel', 'default');
                if (is_array($aids) && in_array($uid, $aids)) {
                    Announcement::removeAnnouncementRelation($value, ['inactive_user_announcement_rel'], $uid);
                    Announcement::addAnnouncementRelation($value, ['active_user_announcement_rel'], $uid);
                }
            }
        }

        //quiz relation
        if (isset($relations['user_quiz_rel'])) {
            foreach ($relations['user_quiz_rel'] as $value) {
                $qids = Quiz::where('quiz_id', '=', (int)$value)->value('relations');
                $qids = array_get($qids, 'inactive_user_quiz_rel', 'default');
                if (is_array($qids) && in_array($uid, $qids)) {
                    Quiz::removeQuizRelation($value, ['inactive_user_quiz_rel'], $uid);
                    Quiz::addQuizRelation($value, ['active_user_quiz_rel'], $uid);
                }
            }
        }

        //event relation
        if (isset($relations['user_event_rel'])) {
            foreach ($relations['user_event_rel'] as $value) {
                $eids = Event::where('event_id', '=', (int)$value)->value('relations');
                $eids = array_get($eids, 'inactive_user_event_rel', 'default');
                if (is_array($eids) && in_array($uid, $eids)) {
                    Event::removeEventRelation($value, ['inactive_user_event_rel'], $uid);
                    Event::addEventRelation($value, ['active_user_event_rel'], $uid);
                }
            }
        }

        //questionbank relation
        if (isset($relations['user_questionbank_rel'])) {
            foreach ($relations['user_questionbank_rel'] as $value) {
                $qbids = QuestionBank::where('question_bank_id', '=', (int)$value)->value('relations');
                $qbids = array_get($qbids, 'inactive_user_questionbank_rel', 'default');
                if (is_array($qbids) && in_array($uid, $qbids)) {
                    QuestionBank::removeQuestionBankRelation($value, ['inactive_user_questionbank_rel'], $uid);
                    QuestionBank::addQuestionBankRelation($value, ['active_user_questionbank_rel'], $uid);
                }
            }
        }
    }

    public static function getInactivate($uid)
    {
        self::where('uid', '=', (int)$uid)->update(['status' => 'IN-ACTIVE']);
        $relations = self::where('uid', '=', (int)$uid)->value('relations');

        //usergroup relation
        if (isset($relations['active_usergroup_user_rel'])) {
            foreach ($relations['active_usergroup_user_rel'] as $value) {
                $ugids = UserGroup::where('ugid', '=', (int)$value)->value('relations');
                $ugids = array_get($ugids, 'active_user_usergroup_rel', 'default');
                if (is_array($ugids) && in_array($uid, $ugids)) {
                    UserGroup::removeUserGroupRelation($value, ['active_user_usergroup_rel'], $uid);
                    UserGroup::addUserGroupRelation($value, ['inactive_user_usergroup_rel'], $uid);
                }
            }
        }

        //dams relation
        if (isset($relations['user_media_rel'])) {
            foreach ($relations['user_media_rel'] as $value) {
                $ids = Dam::where('id', '=', (int)$value)->value('relations');
                $ids = array_get($ids, 'active_user_media_rel', 'default');
                if (is_array($ids) && in_array($uid, $ids)) {
                    Dam::removeMediaRelationId($value, ['active_user_media_rel'], $uid);
                    Dam::addMediaRelation($value, ['inactive_user_media_rel'], $uid);
                }
            }
        }

        //feed relation
        if (isset($relations['user_feed_rel'])) {
            foreach ($relations['user_feed_rel'] as $value) {
                $pids = Program::where('program_id', '=', (int)$value)->value('relations');
                $pids = array_get($pids, 'active_user_feed_rel', 'default');
                if (is_array($pids) && in_array($uid, $pids)) {
                    Program::removeFeedRelation($value, ['active_user_feed_rel'], $uid);
                    Program::addFeedRelation($value, ['inactive_user_feed_rel'], $uid);
                }
            }
        }

        //package relation
        if (isset($relations['user_parent_feed_rel'])) {
            foreach ($relations['user_parent_feed_rel'] as $value) {
                $pids = Program::where('program_id', '=', (int)$value)->value('relations');
                $pids = array_get($pids, 'active_user_feed_rel', 'default');
                if (is_array($pids) && in_array($uid, $pids)) {
                    Program::removeFeedRelation($value, ['active_user_feed_rel'], $uid);
                    Program::addFeedRelation($value, ['inactive_user_feed_rel'], $uid);
                }
            }
        }

        //batch relation
        if (isset($relations['user_course_rel'])) {
            foreach ($relations['user_course_rel'] as $value) {
                $pids = Program::where('program_id', '=', (int)$value)->value('relations');
                $pids = array_get($pids, 'active_user_feed_rel', 'default');
                if (is_array($pids) && in_array($uid, $pids)) {
                    Program::removeFeedRelation($value, ['active_user_feed_rel'], $uid);
                    Program::addFeedRelation($value, ['inactive_user_feed_rel'], $uid);
                }
            }
        }

        //announcemnet relation
        if (isset($relations['user_announcement_rel'])) {
            foreach ($relations['user_announcement_rel'] as $value) {
                $aids = Announcement::where('announcement_id', '=', (int)$value)->value('relations');
                $aids = array_get($aids, 'active_user_announcement_rel', 'default');
                if (is_array($aids) && in_array($uid, $aids)) {
                    Announcement::removeAnnouncementRelation($value, ['active_user_announcement_rel'], $uid);
                    Announcement::addAnnouncementRelation($value, ['inactive_user_announcement_rel'], $uid);
                }
            }
        }

        //quiz relation
        if (isset($relations['user_quiz_rel'])) {
            foreach ($relations['user_quiz_rel'] as $value) {
                $qids = Quiz::where('quiz_id', '=', (int)$value)->value('relations');
                $qids = array_get($qids, 'active_user_quiz_rel', 'default');
                if (is_array($qids) && in_array($uid, $qids)) {
                    Quiz::removeQuizRelation($value, ['active_user_quiz_rel'], $uid);
                    Quiz::addQuizRelation($value, ['inactive_user_quiz_rel'], $uid);
                }
            }
        }

        //event relation
        if (isset($relations['user_event_rel'])) {
            foreach ($relations['user_event_rel'] as $value) {
                $eids = Event::where('event_id', '=', (int)$value)->value('relations');
                $eids = array_get($eids, 'active_user_event_rel', 'default');
                if (is_array($eids) && in_array($uid, $eids)) {
                    Event::removeEventRelation($value, ['active_user_event_rel'], $uid);
                    Event::addEventRelation($value, ['inactive_user_event_rel'], $uid);
                }
            }
        }

        //questionbank relation
        if (isset($relations['user_questionbank_rel'])) {
            foreach ($relations['user_questionbank_rel'] as $value) {
                $qbids = QuestionBank::where('question_bank_id', '=', (int)$value)->value('relations');
                $qbids = array_get($qbids, 'active_user_questionbank_rel', 'default');
                if (is_array($qbids) && in_array($uid, $qbids)) {
                    QuestionBank::removeQuestionBankRelation($value, ['active_user_questionbank_rel'], $uid);
                    QuestionBank::addQuestionBankRelation($value, ['inactive_user_questionbank_rel'], $uid);
                }
            }
        }
    }

    //function to check the user name uniqueness in edit
    public static function pluckUserName($uid, $username)
    {
        return self::where('uid', '!=', (int)$uid)->where('username', '=', $username)->value('username');
    }

    //function to check the user email uniqueness in edit
    public static function pluckUserEmail($uid, $email)
    {
        return self::where('uid', '!=', (int)$uid)->where('email', '=', $email)->value('email');
    }

    public static function getUpdateUser($uid, $input, $customFieldList = null)
    {
        $relation = self::where('uid', '=', (int)$uid)->value('relations');

        if ($input['timezone']) {
            $timezone = $input['timezone'];
        } else {
            $timezone = config('app.default_timezone');
        }

        if (!empty($input['password'])) {
            $array = [
                'firstname' => $input['firstname'],
                'lastname' => $input['lastname'],
                'email' => $input['email'],
                'mobile' => $input['mobile'],
                //'username' => $input['username'],
                'password' => Hash::make($input['password']),
                'role' => (int)$input['role'],
                'status' => $input['status'],
                'profile_pic' => '',
                'timezone' => $timezone,
                'updated_at' => time(),
                'updated_by' => Auth::user()->username,
                'authkey' => self::getAuthKey($input['username'], $input['password']), //authkey
                'nda_status' => NDA::NO_RESPONSE,
            ];

            $final_array = array_merge($array, $customFieldList);
            self::where('uid', '=', (int)$uid)->update($final_array);
        } else {
            $array = [
                'firstname' => $input['firstname'],
                'lastname' => $input['lastname'],
                'email' => $input['email'],
                'mobile' => $input['mobile'],
                //'username' => $input['username'],
                'role' => (int)$input['role'],
                'status' => $input['status'],
                'timezone' => $timezone,
                'updated_at' => time(),
                'updated_by' => Auth::user()->username,
                'nda_status' => NDA::NO_RESPONSE,
            ];
            $final_array = array_merge($array, $customFieldList);
            self::where('uid', '=', (int)$uid)->update($final_array);
        }
        //start of update empty relations to object
        if (empty($relation)) {
            self::where('uid', '=', (int)$uid)->unset('relations');
            $arrname = 'user_feed_rel';
            self::where('uid', '=', (int)$uid)->push('relations.' . $arrname, 0, true);
            self::where('uid', '=', (int)$uid)->pull('relations.' . $arrname, 0);
        }
        //end of update empty relations to object
        //update user on lms
        $lmsuser = self::where('uid', '=', (int)$uid)->get()->toArray();
        if (!empty(SiteSetting::module('Lmsprogram', 'wstoken')) && isset($lmsuser[0]['userid'])) {
            $moodleapi = MoodleAPI::get_instance();
            if (!empty($input['password'])) {
                $paramlist['password'] = $input['password'];
            }
            $paramlist['id'] = $lmsuser[0]['userid'];
            $paramlist['username'] = $input['username'];
            $paramlist['firstname'] = $input['firstname'];
            $paramlist['lastname'] = $input['lastname'];
            $paramlist['email'] = $input['email'];
            $lmsuser = $moodleapi->moodle_user_update($paramlist);
            //if user having manager role assign moodle manager role
            if ((int)$input['role'] == 2) {
                $paramlist['userid'] = $paramlist['id'];
                $paramlist['roleid'] = 1;
                $paramlist['contextid'] = 1;
                $moodleapi->moodle_user_role_assign($paramlist);
            }
            //if user role changed from manager unassign moodle manager role
            if ((int)$input['role'] != 2) {
                $paramlist['userid'] = $paramlist['id'];
                $paramlist['roleid'] = 1;
                $paramlist['contextid'] = 1;
                $moodleapi->moodle_user_role_unassign($paramlist);
            }
        }

        if ($input['status'] == 'ACTIVE') {
            self::getActivate($uid);
        } elseif ($input['status'] == 'IN-ACTIVE') {
            self::getInactivate($uid);
        }
    }

    public static function updateInsertUsers($uid, $input, $customFieldList = [])
    {
        $final_input_arr = [
            'firstname' => $input['firstname'],
            'lastname' => $input['lastname'],
            'email' => $input['email'],
            'mobile' => $input['mobile'],
            'username' => $input['username']
        ];

        $final_array = array_merge($final_input_arr, $customFieldList);
        return self::where('username', '=', $input['username'])->update($final_array);
    }

    public static function updateUserRelation($key, $arrname, $updateArr, $overwrite = false)
    {
        if ($overwrite) {
            self::where('uid', (int)$key)->unset('relations.' . $arrname);
            self::where('uid', (int)$key)->update(['relations.' . $arrname => $updateArr]);
        } else {
            self::where('uid', (int)$key)->push('relations.' . $arrname, $updateArr, true);
        }

        return self::where('uid', (int)$key)->update(['updated_at' => time()]);
    }

    //function to get the assigned user group ids from particular user
    public static function getAssignedUsergroups($uid)
    {
        $ugids = self::where('uid', '=', (int)$uid)->value('relations');
        $ugids = array_get($ugids, 'active_usergroup_user_rel', 'default');
        return $ugids;
    }

    //function to get the user relations from particular user
    public static function fetchingRelationsArray($uid)
    {
        $relations = self::where('exist', "relations")->where('uid', '=', (int)$uid)->value('relations');
    }

    public static function testRelations($uid, $arrname, $relations)
    {
        self::where('uid', '=', (int)$uid)->unset('relations');
        self::where('uid', '=', (int)$uid)->push('relations.' . $arrname, 0, true);
        self::where('uid', '=', (int)$uid)->pull('relations.' . $arrname, 0);
        //self::where('uid', $uid)->push(['relations.'.$arrname => []] );
    }

    public static function getAssignedPackUsergroups($program_slugs)
    {
        $list = [];
        foreach ($program_slugs as $program_slug) {
            $program = Program::getProgram($program_slug);
            if (isset($program[0]['child_relations']['active_channel_rel']) && !empty($program[0]['child_relations']['active_channel_rel'])) {
                foreach ($program[0]['child_relations']['active_channel_rel'] as $child_id) {
                    $lists = Program::getProgramDetailsByID($child_id);
                    $list [] = $lists['program_slug'];
                }
            }
        }
        return $list;
    }

    //function to get the assigned content feed ids from particular user
    public static function getAssignedContentFeed($uid)
    {
        $pids = self::where('uid', '=', (int)$uid)->value('relations');
        $pids = array_get($pids, 'user_feed_rel', 'default');
        return $pids;
    }

    public static function getAssignedCourse($uid)
    {
        $pids = self::where('uid', '=', (int)$uid)->value('relations');
        $pids = array_get($pids, 'user_course_rel', 'default');
        return $pids;
    }

    public static function getAssignedLmsCourse($uid)
    {
        $pids = self::where('uid', '=', (int)$uid)->value('relations');
        $pids = array_get($pids, 'lms_course_rel', 'default');

        return $pids;
    }

    public static function getAssignedPackContentFeed($uid)
    {
        $user = self::getAllUserDetailsByID($uid);
        $pids = self::where('uid', '=', (int)$uid)->value('relations');
        if (isset($user[0]['relations']['user_package_feed_rel'])) {
            $pids = array_get($pids, 'user_package_feed_rel', 'default');
            return $pids;
        }
    }

    /* Added by Cerlin*/

    public static function removeUserRelation($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('uid', $key)->pull('relations.' . $field, (int)$id);
        }

        return self::where('uid', $key)->update(['updated_at' => time()]);
    }

    public static function addUserRelation($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('uid', $key)->push('relations.' . $field, (int)$id, true);
        }

        return self::where('uid', $key)->update(['updated_at' => time()]);
    }

    /* ^ Added by Cerlin*/

    //Portal functions
    public static function getRegisterUser($input, $app_registration = 0)
    {
        if (isset($input['timezone'])) {
            $timezone = $input['timezone'];
        } else {
            $timezone = config('app.default_timezone');
        }

        $auth_key = self::getAuthKey($input['username'], $input['password']);
        if (config('app.email_verification') == 'ACTIVE') {
            $user_status = 'IN-ACTIVE';
        } else {
            $user_status = 'ACTIVE';
        }

        $uid = self::uniqueId();
        if (config('app.email_verification') == 'ACTIVE') {
            $insert_array = [
                'uid' => (int)$uid,
                'firstname' => $input['firstname'],
                'lastname' => $input['lastname'],
                'email' => $input['email'],
                'mobile' => $input['mobile'],
                'username' => $input['username'],
                'password' => Hash::make($input['password']),
                'role' => (int)$input["role_id"],
                'timezone' => $timezone,
                'status' => $user_status,
                //'relations' => new stdClass(),
                'created_at' => time(),
                'created_by' => $input['username'],
                'authkey' => $auth_key, //authkey
                'nda_status' => NDA::NO_RESPONSE,
                'app_registration' => (int)$app_registration,
                'email_verification' => false
            ];
        } else {
            $insert_array = [
                'uid' => (int)$uid,
                'firstname' => $input['firstname'],
                'lastname' => $input['lastname'],
                'email' => $input['email'],
                'mobile' => $input['mobile'],
                'username' => $input['username'],
                'password' => Hash::make($input['password']),
                'role' => (int)config('app.learner_role_id'),
                'timezone' => $timezone,
                'status' => $user_status,
                //'relations' => new stdClass(),
                'created_at' => time(),
                'created_by' => $input['username'],
                'authkey' => $auth_key, //authkey
                'nda_status' => NDA::NO_RESPONSE,
                'app_registration' => (int)$app_registration
            ];
        }


        self::insert($insert_array);

        $arrname = 'user_feed_rel';
        self::where('uid', $uid)->push('relations.' . $arrname, 0, true);
        self::where('uid', $uid)->pull('relations.' . $arrname, 0);

        //lms register
        self::lmsRegister($input, $uid);

        //email verification
        if (config('app.email_verification') != 'ACTIVE') {
            self::sendRegistrationUserMail(
                $input['firstname'],
                $input['lastname'],
                $input['username'],
                $input['email']
            );

            self::sendRegistrationAdminMail($input['username']);
        } else {
            self::sendUserVerificationMail(
                $input['firstname'],
                $input['lastname'],
                $input['username'],
                $input['email'],
                $auth_key,
                $input['current_url'],
                $input['catalog_url'],
                $input['posts_url']
            );
        }

        return $uid;
    }

    public static function lmsRegister($input, $uid)
    {
        //register user in lms
        if (!empty(SiteSetting::module('Lmsprogram', 'wstoken'))) {
            //create user in moodle
            $moodleapi = MoodleAPI::get_instance();
            $paramlist['username'] = $input['username'];
            $paramlist['password'] = $input['password'];
            $paramlist['firstname'] = $input['firstname'];
            $paramlist['lastname'] = $input['lastname'];
            $paramlist['email'] = $input['email'];
            $lmsuser = $moodleapi->moodle_user_create($paramlist);
            //update ultron user collection with moodle userid
            self::where('uid', '=', (int)$uid)->update(['userid' => $lmsuser[0]['id']]);
        }
    }

    public static function sendRegistrationUserMail($firstname, $lastname, $username, $email)
    {
        // sending email to user
        $site_name = config('app.site_name');
        $to = $email;
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
        $headers .= 'From:' . $site_name . "\r\n";
        $name = 'user-registration';

        $base_url = config('app.url');

        $email_details = Email::getEmail($name);
        $subject = $email_details[0]['subject'];
        $body = $email_details[0]['body'];

        $login_url = '<a href="' . $base_url . '/auth/login">Click here to login</a>';
        $support_email = config('mail.from');
        $name = ucwords($firstname) . ' ' . ucwords($lastname);

        $subject_find = ['<SITE NAME>'];
        $subject_replace = [$site_name];
        $subject = str_replace($subject_find, $subject_replace, $subject);

        $find = ['<NAME>', '<USERNAME>', '<SITE NAME>', '<EMAIL>', '<LOGIN URL>', '<SUPPORT EMAIL>', '<SITE URL>'];
        $replace = [$name, $username, $site_name, $email, $login_url, $support_email['address'], $base_url];
        $body = str_replace($find, $replace, $body);

        Common::sendMailHtml($body, $subject, $to);
    }


    public static function sendRegistrationAdminMail($username)
    {
        //sending mail to admin
        $to = config('app.site_admin_email');
        $name = 'user-register-admin-template';
        $email_details = Email::getEmail($name);
        $subject = $email_details[0]['subject'];
        $body = $email_details[0]['body'];

        $base_url = config('app.url');

        $site_name = config('app.site_name');
        $subject_find = ['<SITE NAME>'];
        $subject_replace = [$site_name];
        $subject = str_replace($subject_find, $subject_replace, $subject);
        $login_url = '<a href="' . $base_url . '/auth/login">Click here to login</a>';
        $site_admin_name = config('app.site_admin_name');
        $date_time = Timezone::convertFromUTC('@' . time(), config('app.default_timezone'), config('app.date_format'));


        $find = ['<SITE ADMIN NAME>', '<USERNAME>', '<SITE NAME>', '<DATETIME>', '<LOGIN URL>'];
        $replace = [$site_admin_name, $username, $site_name, $date_time, $login_url];
        $body = str_replace($find, $replace, $body);

        Common::sendMailHtml($body, $subject, $to);
    }

    public static function sendUserVerificationMail($firstname, $lastname, $username, $email, $authkey, $current_url, $catalog_url, $posts_url)
    {
        // sending email to user
        $site_name = config('app.site_name');
        $to = $email;
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
        $headers .= 'From:' . $site_name . "\r\n";
        $name = 'user-email-verification';

        $base_url = config('app.url');

        $email_details = Email::getEmail($name);
        $subject = $email_details[0]['subject'];
        $body = $email_details[0]['body'];
        
        $url = self::redirectUrlAfterVerification($authkey, $base_url, $current_url, $catalog_url, $posts_url);
      
        $login_url = '<a href="' . $url . '">Click here to verify email.</a>';
        $support_email = config('mail.from');
        $name = ucwords($firstname) . ' ' . ucwords($lastname);

        $subject_find = ['<SITE NAME>'];
        $subject_replace = [$site_name];
        $subject = str_replace($subject_find, $subject_replace, $subject);

        $find = ['<NAME>', '<SITE NAME>', '<VERIFY URL>', '<SUPPORT EMAIL>', '<SITE URL>'];
        $replace = [$name, $site_name, $login_url, $support_email['address'], $base_url];
        $body = str_replace($find, $replace, $body);

        Common::sendMailHtml($body, $subject, $to);
    }
    
    public static function redirectUrlAfterVerification($authkey, $base_url, $current_url, $catalog_url, $posts_url)
    {
        $url = '';
        $verification_url = config('app.verify_url');
        if ($verification_url != null) {
            $url = $base_url . $verification_url . '/verify-me/' . $authkey;
        } elseif (!empty($catalog_url)) {
            $url = $base_url . '/auth/verify-me/' . $authkey.'?catalog_url='.urlencode($catalog_url);
        } elseif (!empty($posts_url)) {
            $url = $base_url . '/auth/verify-me/' . $authkey.'?posts_url='.urlencode($posts_url);
        } elseif(!empty($current_url))  {
            $url = $base_url . '/auth/verify-me/' . $authkey.'?current_url='.urlencode($current_url);
        }
        return $url;
    }
    
    public static function getVerify($firstname, $lastname, $username, $email)
    {
        self::sendRegistrationUserMail($firstname, $lastname, $username, $email);

        self::sendRegistrationAdminMail($username);
    }

    public static function getUpdateProfile($uid, $input, $customFieldList = null)
    {
        $relation = self::where('uid', '=', (int)$uid)->value('relations');
        if ($input['timezone']) {
            $timezone = $input['timezone'];
        } else {
            $timezone = config('app.default_timezone');
        }

        if (!empty($input['password'])) {
            $array = [
                'firstname' => $input['firstname'],
                'lastname' => $input['lastname'],
                'email' => $input['email'],
                'mobile' => $input['mobile'],
                'password' => Hash::make($input['password']),
                'profile_pic' => '',
                'timezone' => $timezone,
                'gender' => $input['gender'],
                'dob' => $input['dob'],
                'updated_at' => time(),
                'updated_by' => Auth::user()->username,
                'authkey' => self::getAuthKey($input['username'], $input['password']), //authkey
            ];
            $final_array = array_merge($array, $customFieldList);
            self::where('uid', '=', (int)$uid)->update($final_array);

            // sending email
            $site_name = config('app.site_name');
            $to = $input['email'];
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
            $headers .= 'From:' . $site_name . "\r\n";
            $name = 'change-password';

            $base_url = config('app.url');

            $email_details = Email::getEmail($name);
            $subject = $email_details[0]['subject'];
            $body = $email_details[0]['body'];

            $login_url = '<a href="' . $base_url . '/auth/login">Click here to login</a>';
            $support_email = config('mail.from');
            $name = ucwords($input['firstname']) . ' ' . ucwords($input['lastname']);
            $subject_find = ['<Website Name>'];
            $subject_replace = [$site_name];
            $subject = str_replace($subject_find, $subject_replace, $subject);
            $find = ['<NAME>', '<SITE NAME>', '<LOGIN URL>', '<SUPPORT EMAIL>'];
            $replace = [$name, $site_name, $login_url, $support_email['address']];
            $body = str_replace($find, $replace, $body);

            Common::sendMailHtml($body, $subject, $to);
        } else {
            $array = [
                'firstname' => $input['firstname'],
                'lastname' => $input['lastname'],
                'email' => $input['email'],
                'mobile' => $input['mobile'],
                'timezone' => $timezone,
                'gender' => $input['gender'],
                'dob' => $input['dob'],
                'updated_at' => time(),
                'updated_by' => Auth::user()->username,
            ];
            $final_array = array_merge($array, $customFieldList);
            self::where('uid', '=', (int)$uid)->update($final_array);
        }
        //start of update empty relations to object
        if (empty($relation)) {
            self::where('uid', '=', (int)$uid)->unset('relations');
            $arrname = 'user_feed_rel';
            self::where('uid', '=', (int)$uid)->push('relations.' . $arrname, 0, true);
            self::where('uid', '=', (int)$uid)->pull('relations.' . $arrname, 0);
        }
        //end of update empty relations to object
        //update user on lms
        $lmsuser = self::where('uid', '=', (int)$uid)->get()->toArray();
        if (!empty(SiteSetting::module('Lmsprogram', 'wstoken')) && isset($lmsuser[0]['userid'])) {
            $moodleapi = MoodleAPI::get_instance();
            if (!empty($input['password'])) {
                $paramlist['password'] = $input['password'];
            }
            $paramlist['id'] = $lmsuser[0]['userid'];
            $paramlist['username'] = $input['username'];
            $paramlist['firstname'] = $input['firstname'];
            $paramlist['lastname'] = $input['lastname'];
            $paramlist['email'] = $input['email'];
            $lmsuser = $moodleapi->moodle_user_update($paramlist);
        }
    }

    public static function getUserAddress($uid)
    {
        return self::where('uid', '=', (int)$uid)->value('myaddress');
    }

    public static function updateMyAddress($uid, $input, $address_id)
    {
        if (!empty($address_id)) {
            self::where('uid', '=', (int)$uid)
                ->pull('myaddress', [
                    'address_id' => $address_id
                ]);
        } else {
            $address_id = md5(uniqid(rand(), true));
        }

        $addresses = User::getUserAddress($uid);
        if (count($addresses) == 0 || isset($input['default_address'])) {
            self::where('uid', '=', (int)$uid)->update([
                'default_address_id' => $address_id
            ]);
        } elseif ((Auth::user()->default_address_id == $address_id) && (!isset($input['default_address']))) {
            if (count($addresses) >= 1) {
                self::where('uid', '=', (int)$uid)->update([
                    'default_address_id' => $addresses[0]['address_id']
                ]);
            }
        }

        $array = [
            'address_id' => $address_id,
            'fullname' => $input['fullname'],
            'street' => $input['street'],
            'landmark' => $input['landmark'],
            'city' => $input['city'],
            'country' => $input['country'],
            'state' => $input['state'],
            'pincode' => $input['pincode'],
            'phone' => $input['phone']
        ];
        return self::where('uid', '=', (int)$uid)
            ->push('myaddress', $array);
    }

    public static function deleteAddress($uid, $address_id)
    {
        self::where('uid', '=', (int)$uid)
            ->pull('myaddress', [
                'address_id' => $address_id
            ]);

        $addresses = User::getUserAddress($uid);
        if (count($addresses) == 0) {
            self::where('uid', '=', (int)$uid)->where('default_address_id', '=', $address_id)->unset('default_address_id');
        } elseif ((Auth::user()->default_address_id == $address_id)) {
            self::where('uid', '=', (int)$uid)->update([
                'default_address_id' => $addresses[0]['address_id']
            ]);
        }

        return true;
    }

    /*public static function getUpdateEmail($uid, $email)
    {
        User::where('uid', '=', (int)$uid)->update(array(
                   'email' => $email,
                   'updated_at' => time()
            ));
    }*/

    public static function getUpdateLastLogin($uid)
    {
        self::where('uid', '=', (int)$uid)->update([
            'last_login_time' => time(),
        ]);
    }

    // Added By Cerlin

    public static function updateSessionID($uid)
    {
        self::where('uid', '=', (int)$uid)->push('session_ids', Session::getId(), true);
    }

    // Added By Cerlin

    public static function removeSessionID($uid)
    {
        self::where('uid', '=', (int)$uid)->pull('session_ids', Session::getId(), true);
    }

    public static function getUserDetailsByID($id)
    {
        return self::where('uid', '=', $id)->first();
    }

    public static function getAllUserDetailsByID($id)
    {
        return self::where('uid', '=', $id)->get()->toArray();
    }

    /*for statistic */
    public static function getLastThirtydaysUserJoinCount($start = null, $end = null)
    {
        if (!is_null($start) && !is_null($end)) {
            return self::where('status', '=', 'ACTIVE')
                ->whereBetween('created_at', [$start, $end])
                ->count();
        } else {
            return 0;
        }
    }

    public static function getUsersforChart($status = 'ACTIVE')
    {
        $result_record = [];
        $last_month = [];
        $last_month_key = [];
        $last_month_asociate = [];
        for ($i = 30; $i > 0; --$i) {
            $j = $i - 1;
            $st_t = strtotime("-$i day", time());
            $ed_t = strtotime("-$j day", time());
            $val_count = self::where('status', '=', $status)->whereBetween('created_at', [$st_t, $ed_t])->count();
            array_push($last_month, $val_count);
            $key = Timezone::convertFromUTC('@' . $ed_t, Auth::user()->timezone, 'd M');
            array_push($last_month_key, $key);
            $last_month_asociate[$key] = $val_count;
        }

        $last24hours = [];
        $last24hours_key = [];
        $last24hours_asoc = [];
        $flag = true;
        $buf_time = 0;
        for ($i = 24; $i > 0; --$i) {
            $j = $i - 1;
            if ($flag) {
                $flag = false;
                $buf_time = Timezone::convertFromUTC('@' . time(), Auth::user()->timezone, 'h');
                $buf_time = $buf_time * 60 * 60;
            }
            $st_t = (strtotime("-$i hour", time())) - $buf_time;
            if ($j == 0) {
                $ed_t = time() - $buf_time;
            } else {
                $ed_t = strtotime("-$j hour", time()) - $buf_time;
            }
            $val_count = self::where('status', '=', $status)->whereBetween('created_at', [$st_t, $ed_t])->count();
            array_push($last24hours, $val_count);
            $key = Timezone::convertFromUTC('@' . $st_t, Auth::user()->timezone, 'H');
            array_push($last24hours_key, $key);
            $last24hours_asoc[$key] = $val_count;
        }
        $last7days = [];
        $last7days_key = [];
        $last7days_asoc = [];
        for ($i = 7; $i > 0; --$i) {
            $j = $i - 1;
            $st_t = strtotime("-$i day", time());
            $ed_t = strtotime("-$j day", time());
            $val_count = self::where('status', '=', $status)->whereBetween('created_at', [$st_t, $ed_t])->count();
            array_push($last7days, $val_count);
            $key = Timezone::convertFromUTC('@' . $ed_t, Auth::user()->timezone, 'D');
            array_push($last7days_key, $key);
            $last7days_asoc[$key] = $val_count;
        }
        $last12months = [];
        $last12months_key = [];
        $last12months_asoc = [];
        $flag = true;
        $buffer_time = 0;
        for ($i = -1; $i < 11; ++$i) {
            $j = $i + 1;
            if ($flag) {
                $flag = false;
                $get_curent_month = date('m', time());
                $get_curent_month_y = date('Y', time());
                $buffer_time = '1-' . $get_curent_month . '-' . $get_curent_month_y;
                $ed_t = strtotime($buffer_time, time());
                $st_t = time();
                $buffer_time = $st_t - $ed_t;
            } else {
                $st_t = strtotime("-$i Month", time()) - $buffer_time;
                $ed_t = strtotime("-$j Month", time()) - $buffer_time;
            }
            $val_count = self::where('status', '=', $status)->whereBetween('created_at', [$ed_t, $st_t])->count();
            array_push($last12months, $val_count);
            $key = Timezone::convertFromUTC('@' . ($st_t - 86400), Auth::user()->timezone, 'M Y');
            array_push($last12months_key, $key);
            $last12months_asoc[$key] = $val_count;
        }
        $result_record['last12months_asoc'] = $last12months_asoc;
        $result_record['last7days_asoc'] = $last7days_asoc;
        $result_record['last24hours_asoc'] = $last24hours_asoc;
        $result_record['last_month_asociate'] = $last_month_asociate;
        $result_record['last_month'] = $last_month;
        $result_record['last_month_key'] = $last_month_key;
        $result_record['last24hours'] = $last24hours;
        $result_record['last24hours_key'] = $last24hours_key;
        $result_record['last7days'] = $last7days;
        $result_record['last7days_key'] = $last7days_key;
        $result_record['last12months'] = $last12months;
        $result_record['last12months_key'] = $last12months_key;

        return $result_record;
    }

    /*For Reports*/

    public static function getListofContentFeedsforUser($uid = null)
    {
        if (!is_null($uid)) {
            return self::where('status', '=', 'ACTIVE')
                ->where('uid', '=', $uid)->get(['relations.user_feed_rel'])
                ->toArray();
        }
    }

    public static function getUserSubscribedFeedIds()
    {
        $user_id = Auth::user()->uid;
        $subscribed_feeds = '';
        $subscribed_feeds = Program::getSubscribedFeeds($user_id);
        $subscribed_feeds_through_groups = Program::getSubscribedFeedsThroughGroups($user_id);
        $sub_feed_ids = [];
        foreach ($subscribed_feeds as $each) {
            $sub_feed_ids[] = $each['program_id'];
        }
        $sub_feed_ids = array_unique(array_merge($subscribed_feeds_through_groups, $sub_feed_ids));

        Session::put('sub_feed_ids', $sub_feed_ids);

        return $sub_feed_ids;
    }

    public static function getUserSearchableContentIds()
    {
        $uid = Auth::user()->uid;
        //$relations_info=Auth::user()->relations;
        // echo "<pre>"; print_r($relations_info); die;
        $sub_feed_ids = Session::get('sub_feed_ids');
        $relation = $announce_list_id = [];
        $fields = [];
        $accessible_ids = '';
        if (isset($sub_feed_ids)) {
            $accessible_feed_ids = '';
            foreach ($sub_feed_ids as $each) {
                $fields['program_id'][] = $each;
            }
            $feed_info = Program::getSubscribedFeedInfo($sub_feed_ids);
            $feed_slugs = [];
            foreach ($feed_info as $each) {
                $feed_slugs[] = $each['program_slug'];
            }
            $packet_info = Packet::getPacketsUsingSlugs($feed_slugs);
            /* Asset assigned through Channel */
            // echo "<pre>"; print_r($packet_info); die;
            if (isset($packet_info)) {
                foreach ($packet_info as $each_pack) {
                    foreach ($each_pack['elements'] as $each_asset) {
                        switch ($each_asset['type']) {
                            case 'assessment':
                                $fields['quiz_id'][] = $each_asset['id'];
                                break;
                            case 'media':
                                $fields['media_id'][] = $each_asset['id'];
                                break;
                            case 'event':
                                $fields['event_id'][] = $each_asset['id'];
                                break;
                        }
                    }
                }
            }

            foreach ($packet_info as $each) {
                $fields['packet_id'][] = $each['packet_id'];
            }
        }

        $relations_info = self::where('uid', '=', (int)$uid)->value('relations');

        if (isset($relations_info['active_usergroup_user_rel'])) {
            $user_group_relations = UserGroup::whereIn('ugid', $relations_info['active_usergroup_user_rel'])->get(['relations'])->toArray();
        }

        $accessible_media_ids = '';
        $accessible_quiz_ids = '';
        $accessible_user_questionbank_ids = '';
        $accessible_event_ids = '';
        $accessible_announcement_ids = '';

        /* User-Search filter, from content assigned to usergroup to which user belongs */
        if (isset($user_group_relations)) {
            foreach ($user_group_relations as $each_relation) {
                if (isset($each_relation['relations']['usergroup_media_rel'])) {
                    foreach ($each_relation['relations']['usergroup_media_rel'] as $each) {
                        $fields['media_id'][] = $each;
                    }
                }

                if (isset($each_relation['relations']['usergroup_quiz_rel'])) {
                    foreach ($each_relation['relations']['usergroup_quiz_rel'] as $each) {
                        $fields['quiz_id'][] = $each;
                    }
                }

                if (isset($each_relation['relations']['usergroup_event_rel'])) {
                    foreach ($each_relation['relations']['usergroup_event_rel'] as $each) {
                        $fields['event_id'][] = $each;
                    }
                }
            }
        }

        /* User-Search filter, from content assigned to user */

        if (isset($relations_info)) {
            if (isset($relations_info['user_media_rel'])) {
                foreach ($relations_info['user_media_rel'] as $each) {
                    $fields['media_id'][] = $each;
                }
            }
            if (isset($relations_info['user_quiz_rel'])) {
                foreach ($relations_info['user_quiz_rel'] as $each) {
                    $fields['quiz_id'][] = $each;
                }
            }

            if (isset($relations_info['user_event_rel'])) {
                foreach ($relations_info['user_event_rel'] as $each) {
                    $fields['event_id'][] = $each;
                }
            }
            if (isset($relations_info)) {
                foreach ($relations_info as $key => $value) {
                    if ($key == 'active_usergroup_user_rel') {
                        $agl = UserGroup::getAnnouncementList($value);
                        foreach ($agl as $key3 => $value3) {
                            if (isset($value3['relations']['usergroup_announcement_rel'])) {
                                foreach ($value3['relations']['usergroup_announcement_rel'] as $key4 => $value4) {
                                    $fields['announcement_id'][] = $value4;
                                }
                            }
                        }
                    }
                    if ($key == 'user_feed_rel') {
                        $acfl = Program::getAnnouncementList($value);
                        foreach ($acfl as $key6 => $value6) {
                            if (isset($value6['relations']['contentfeed_announcement_rel'])) {
                                foreach ($value6['relations']['contentfeed_announcement_rel'] as $key7 => $value7) {
                                    $fields['announcement_id'][] = $value7;
                                }
                            }
                        }
                    }
                    if ($key == 'user_announcement_rel') {
                        if (!empty($value)) {
                            foreach ($value as $key5 => $value5) {
                                $fields['announcement_id'][] = $value5;
                            }
                        }
                    }
                }
            }
        }
        $query_string = '';
        $i = 0;
        $length = count($fields);
        foreach ($fields as $key => $value) {
            $ids = implode(' ', array_unique(array_map('intval', $value), SORT_REGULAR));
            $query_string .= $key . ':(' . $ids . ')';
            if ($i < $length - 1) {
                $query_string .= ' OR ';
            }
            $i++;
        }
        return $query_string;
        // $relation['media_ids']=$accessible_media_ids;
        // $relation['quiz_ids']=$accessible_quiz_ids;
        // $relation['event_ids']=$accessible_event_ids;
        // $relation['questionbank_ids']=$accessible_user_questionbank_ids;
        // $relation['announcement_ids']=$accessible_announcement_ids;

        // return $relation;
    }

    public static function getSuperAdmin()
    {
        return self::where('status', '!=', 'DELETED')
            ->where('status', '=', 'ACTIVE')
            ->where('super_admin', '=', true)
            ->get(['uid'])
            ->toArray();
    }

    public static function getAllUsersDetails($status)
    {
        if ($status == 'ALL') {
            $users = self::where('super_admin', '!=', true)->get()->toArray();
        } else {
            $users = self::where('status', '=', $status)->where('super_admin', '!=', true)->get()->toArray();
        }

        return $users;
    }

    /* to get user information by uids*/
    public static function getUserDetailsUsingUserIDs($uids = [])
    {

        return self::whereIn('uid', $uids)->where('status', '!=', 'DELETED')->orderby('firstname', 'asc')->get()->toArray();
    }

    /********
     * Added by Muniraju N.
     * Purpose : To get last login time of a particular user.
     ********/

    public static function getLastLoginTime($uid)
    {
        $user = self::where("uid", $uid)
            ->get(["last_login_time"])
            ->first();
        if (isset($user->last_login_time)) {
            return Timezone::getTimeStamp($user->last_login_time);
        }
        return null;
    }

    public static function getActiveUsersDetails($is_count, $start = 0, $limit = 100)
    {
        if ($is_count) {
            return self::where('status', "ACTIVE")->count();
        }
        return self::where('status', "ACTIVE")->skip((int)$start)->take((int)$limit)->get([
            'uid',
            'status',
            'username',
            'relations',
            'subscription'
        ])->toArray();
    }

    public static function registerSocialite($userdata)
    {
        $email = $userdata['email'];
        $data = self::where('email', '=', $email)->get();
        if (!$data->isEmpty()) {
            $user = $data->first();
            if ($user->status === "ACTIVE") {
                return "no";
            } else {
                return "abort";
            }
        }
        $timezone = config('app.default_timezone');
        $uid = self::uniqueId();
        self::insert([
            'uid' => (int)$uid,
            'firstname' => $userdata['firstname'],
            'lastname' => $userdata['lastname'],
            'email' => $userdata['email'],
            'mobile' => '',
            'username' => $userdata['email'],
            'password' => '',
            'role' => (int)$userdata["role_id"],
            'timezone' => $timezone,
            'status' => 'ACTIVE',
            //'relations' => new stdClass(),
            'created_at' => time(),
            'created_by' => $userdata['provider'],
        ]);
        $arrname = 'user_feed_rel';
        self::where('uid', $uid)->push('relations.' . $arrname, 0, true);
        self::where('uid', $uid)->pull('relations.' . $arrname, 0);

        // sending email to user
        $site_name = config('app.site_name');
        $to = $userdata['email'];
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
        $headers .= 'From:' . $site_name . "\r\n";
        $name = 'user-registration';

        $base_url = config('app.url');

        $email_details = Email::getEmail($name);
        $subject = $email_details[0]['subject'];
        $body = $email_details[0]['body'];

        $login_url = '<a href="' . $base_url . '/auth/login">Click here to login</a>';
        $support_email = config('mail.from');
        $name = ucwords($userdata['firstname']) . ' ' . ucwords($userdata['lastname']);

        $subject_find = ['<SITE NAME>'];
        $subject_replace = [$site_name];
        $subject = str_replace($subject_find, $subject_replace, $subject);

        $find = ['<NAME>', '<USERNAME>', '<SITE NAME>', '<EMAIL>', '<LOGIN URL>', '<SUPPORT EMAIL>'];
        $replace = [$name, $userdata['email'], $site_name, $userdata['email'], $login_url, $support_email['address']];
        $body = str_replace($find, $replace, $body);

        Common::sendMailHtml($body, $subject, $to);

        //sending mail to admin
        $to = config('app.site_admin_email');
        $name = 'user-register-admin-template';
        $email_details = Email::getEmail($name);
        $subject = $email_details[0]['subject'];
        $body = $email_details[0]['body'];

        $subject = str_replace($subject_find, $subject_replace, $subject);
        $login_url = '<a href="' . $base_url . '/">Click here to login</a>';
        $site_admin_name = config('app.site_admin_name');
        $date_time = Timezone::convertFromUTC('@' . time(), config('app.default_timezone'), config('app.date_format'));


        $find = ['<SITE ADMIN NAME>', '<USERNAME>', '<SITE NAME>', '<DATETIME>', '<LOGIN URL>'];
        $replace = [$site_admin_name, $userdata['email'], $site_name, $date_time, $login_url];
        $body = str_replace($find, $replace, $body);

        Common::sendMailHtml($body, $subject, $to);
        return $uid;
    }

    public static function getLmsUsersCount($status = 'nonassigned', $search = null, $relinfo = null)
    {

        if ($relinfo && key($relinfo) == 'lmscourse') {
            $field = 'relations.lms_course_rel';
        } else {
            $field = '';
        }

        if ($status == 'assigned') {
            if ($search) {
                return self::where('username', 'like', '%' . $search . '%')
                    ->orWhere('firstname', 'like', '%' . $search . '%')
                    ->orWhere('lastname', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->where('status', '=', 'ACTIVE')
                    ->where('super_admin', '!=', true)
                    ->where($field, '=', (int)$relinfo[key($relinfo)])
                    ->count();
            } else {
                return self::where('status', '=', 'ACTIVE')->where('super_admin', '!=', true)->
                where($field, '=', (int)$relinfo[key($relinfo)])->count();
            }
        }
        if ($status == 'nonassigned') {
            if ($search) {
                return self::where('username', 'like', '%' . $search . '%')->orWhere('firstname', 'like', '%' . $search . '%')
                    ->orWhere('lastname', 'like', '%' . $search . '%')->orWhere('email', 'like', '%' . $search . '%')
                    ->where('status', '=', 'ACTIVE')->where('super_admin', '!=', true)
                    ->where($field, '!=', (int)$relinfo[key($relinfo)])->count();
            } else {
                return self::where('status', '=', 'ACTIVE')->where('super_admin', '!=', true)
                    ->where($field, '!=', (int)$relinfo[key($relinfo)])->count();
            }
        } elseif ($search) {
            return self::where('username', 'like', '%' . $search . '%')->orWhere('firstname', 'like', '%' . $search . '%')
                ->orWhere('lastname', 'like', '%' . $search . '%')->orWhere('email', 'like', '%' . $search . '%')
                ->where('status', '=', $status)->where('super_admin', '!=', true)->count();
        } else {
            return self::where('status', '=', $status)->where('super_admin', '!=', true)->count();
        }
    }

    public static function getCourseUsersCount($status = 'nonassigned', $search = null, $relinfo = null)
    {

        if ($relinfo && key($relinfo) == 'course') {
            $field = 'relations.user_course_rel';
        } else {
            $field = '';
        }

        if ($status == 'assigned') {
            if ($search) {
                return self::where('username', 'like', '%' . $search . '%')
                    ->orWhere('firstname', 'like', '%' . $search . '%')
                    ->orWhere('lastname', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->where('status', '=', 'ACTIVE')
                    //->where('super_admin', '!=', true)
                    ->where($field, '=', (int)$relinfo[key($relinfo)])
                    ->count();
            } else {
                return self::where('status', '=', 'ACTIVE')
                    //->where('super_admin', '!=', true)
                    ->where($field, '=', (int)$relinfo[key($relinfo)])->count();
            }
        }
        if ($status == 'nonassigned') {
            if ($search) {
                return self::where('username', 'like', '%' . $search . '%')->orWhere('firstname', 'like', '%' . $search . '%')
                    ->orWhere('lastname', 'like', '%' . $search . '%')->orWhere('email', 'like', '%' . $search . '%')
                    ->where('status', '=', 'ACTIVE')
                    //->where('super_admin', '!=', true)
                    ->where($field, '!=', (int)$relinfo[key($relinfo)])->count();
            } else {
                return self::where('status', '=', 'ACTIVE')
                    //->where('super_admin', '!=', true)
                    ->where($field, '!=', (int)$relinfo[key($relinfo)])->count();
            }
        } elseif ($search) {
            return self::where('username', 'like', '%' . $search . '%')->orWhere('firstname', 'like', '%' . $search . '%')
                ->orWhere('lastname', 'like', '%' . $search . '%')->orWhere('email', 'like', '%' . $search . '%')
                ->where('status', '=', $status)
                //->where('super_admin', '!=', true)
                ->count();
        } else {
            return self::where('status', '=', $status)
                //->where('super_admin', '!=', true)
                ->count();
        }
    }

    public static function getCourseUsersWithPagination($status = 'nonassigned', $start = 0, $limit = 10, $orderby = ['created_at' => 'desc'], $search = null, $relinfo = null)
    {
        $key = key($orderby);
        $value = $orderby[$key];
        if ($relinfo && key($relinfo) == 'course') {
            $field = 'relations.user_course_rel';
        } else {
            $field = '';
        }

        if ($status == 'assigned') {
            if ($search) {
                return self::where('username', 'like', '%' . $search . '%')->orWhere('firstname', 'like', '%' . $search . '%')
                    ->orWhere('lastname', 'like', '%' . $search . '%')->orWhere('email', 'like', '%' . $search . '%')->
                    where('status', '=', 'ACTIVE')
                    //->where('super_admin', '!=', true)
                    ->where($field, '=', (int)$relinfo[key($relinfo)])->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            } else {
                return self::where('status', '=', 'ACTIVE')
                    //->where('super_admin', '!=', true)
                    ->where($field, '=', (int)$relinfo[key($relinfo)])->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            }
        }
        if ($status == 'nonassigned') {
            if ($search) {
                return self::where('username', 'like', '%' . $search . '%')->orWhere('firstname', 'like', '%' . $search . '%')
                    ->orWhere('lastname', 'like', '%' . $search . '%')->
                    orWhere('email', 'like', '%' . $search . '%')->where('status', '=', 'ACTIVE')
                    //->where('super_admin', '!=', true)
                    //->where('role','=',3)
                    ->where($field, '!=', (int)$relinfo[key($relinfo)])->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            } else {
                return self::where('status', '=', 'ACTIVE')
                    //->where('super_admin', '!=', true)
                    ->where($field, '!=', (int)$relinfo[key($relinfo)])->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            }
        } elseif ($search) {
            return self::where('username', 'like', '%' . $search . '%')->orWhere('firstname', 'like', '%' . $search . '%')
                ->orWhere('lastname', 'like', '%' . $search . '%')->
                orWhere('email', 'like', '%' . $search . '%')->where('status', '=', $status)
                //->where('super_admin', '!=', true)
                ->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
        } else {
            return self::where('status', '=', $status)
                //->where('super_admin', '!=', true)
                ->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
        }
    }

    public static function getLmsUsersWithPagination($status = 'nonassigned', $start = 0, $limit = 10, $orderby = ['created_at' => 'desc'], $search = null, $relinfo = null)
    {
        $key = key($orderby);
        $value = $orderby[$key];
        if ($relinfo && key($relinfo) == 'lmscourse') {
            $field = 'relations.lms_course_rel';
        } else {
            $field = '';
        }

        if ($status == 'assigned') {
            if ($search) {
                return self::where('username', 'like', '%' . $search . '%')->orWhere('firstname', 'like', '%' . $search . '%')->orWhere('lastname', 'like', '%' . $search . '%')->orWhere('email', 'like', '%' . $search . '%')->
                where('status', '=', 'ACTIVE')->where('super_admin', '!=', true)->where($field, '=', (int)$relinfo[key($relinfo)])->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            } else {
                return self::where('status', '=', 'ACTIVE')->where('super_admin', '!=', true)->where($field, '=', (int)$relinfo[key($relinfo)])->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            }
        }
        if ($status == 'nonassigned') {
            if ($search) {
                return self::where('username', 'like', '%' . $search . '%')->orWhere('firstname', 'like', '%' . $search . '%')->orWhere('lastname', 'like', '%' . $search . '%')->
                orWhere('email', 'like', '%' . $search . '%')->where('status', '=', 'ACTIVE')->where('super_admin', '!=', true)->where($field, '!=', (int)$relinfo[key($relinfo)])->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            } else {
                return self::where('status', '=', 'ACTIVE')->where('super_admin', '!=', true)->where($field, '!=', (int)$relinfo[key($relinfo)])->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            }
        } elseif ($search) {
            return self::where('username', 'like', '%' . $search . '%')->orWhere('firstname', 'like', '%' . $search . '%')->orWhere('lastname', 'like', '%' . $search . '%')->
            orWhere('email', 'like', '%' . $search . '%')->where('status', '=', $status)->where('super_admin', '!=', true)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
        } else {
            return self::where('status', '=', $status)->where('super_admin', '!=', true)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
        }
    }

    public static function removeLmsUserRelation($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('uid', $key)->pull('relations.' . $field, (int)$id);
        }

        return self::where('uid', $key)->update(['updated_at' => time()]);
    }

    public static function addLmsUserRelation($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('uid', $key)->push('relations.' . $field, (int)$id, true);
        }

        return self::where('uid', $key)->update(['updated_at' => time()]);
    }

    public static function addLmsUserId($uid, $userid)
    {
        // self::where('uid', '=', (int) $uid)->push('userid'=>(int) $userid);
        self::where('uid', '=', (int)$uid)->update(['userid' => (int)$userid]);
    }

    public static function updateLmsPassword($email, $password)
    {
        $user = self::where('email', '=', $email)->get()->toArray();
        if (!empty(SiteSetting::module('Lmsprogram', 'wstoken')) && isset($user[0]['userid'])) {
            $moodleapi = MoodleAPI::get_instance();
            $paramlist['id'] = $user[0]['userid'];
            $paramlist['password'] = $password;
            $result = $moodleapi->moodle_user_forgot_password($paramlist);
        }
    }

    public static function getAuthKey($user, $pwd)
    {
        $pass = base64_encode($pwd);
        $authkey = base64_encode($user . $pass);
        return $authkey;
    }

    public static function getUserbyEmail($email)
    {
        $username = '';
        $user = self::where('email', '=', $email)->get()->toArray();
        if (isset($user[0])) {
            $username = $user[0]['username'];
        }
        return $username;
    }

    public static function saveQuizAttemptStatus($data)
    {
        $user = self::find(Auth::user()["_id"]);
        $user->quiz_attempt_status = $data;
        return $user->save();
    }

    public static function getById($id)
    {
        return self::find($id);
    }

    /**
     * this function used to get deviceId
     * @param integer $uid user primary key
     * @author sathishkumar@linkstreet.in
     */
    public static function getDeviceId($uid)
    {
        return self::where('uid', '=', $uid)
            ->get(['user_device_id']);
    }

    // On bulk upload of user, check if user's email is alreday existing

    public static function checkIfUserExists($email)
    {
        return self::where('email', '=', $email)->where('status', '=', 'ACTIVE')->get()->count();
    }

    public static function checkIfUserNameExists($username)
    {
        return self::where('username', '=', $username)->where('status', '!=', 'DELETED')->get()->count();
    }

    public static function getIdByEmail($email)
    {
        $uid = self::where('email', '=', $email)->where('status', '=', 'ACTIVE')->value('uid');

        return $uid;
    }

    public static function getIdBy($data, $askedVar)
    {
        $uid = self::where("$askedVar", '=', $data["$askedVar"])->where('status', '=', 'ACTIVE')->value('uid');
        return $uid;
    }

    public static function emptyUserRelation($key, $arrname)
    {
        return self::where('uid', (int)$key)->update(['relations.' . $arrname => []]);
    }


    public static function getAllUsersforUpdateWithCustomFields($status)
    {
        if ($status == 'ALL') {
            $users = self::where('status', '!=', 'DELETED')->where('super_admin', '!=', true)->whereNotIn('role', [1, 2])->get()->toArray();
        } else {
            $users = self::where('status', '=', $status)->where('super_admin', '!=', true)->whereNotIn('role', [1, 2])->get()->toArray();
        }

        return $users;
    }

    public static function getProfilePicture($uid)
    {
        $flag = true;
        $users = self::where('uid', '=', (int)$uid)->get()->toArray();
        if (isset($users[0]) && array_key_exists('profile_pic', $users[0]) && !empty($users[0]['profile_pic'])) {
            return $users[0]['profile_pic'];
        } else {
            return;
        }
    }

    /**
     * [verfiyAuthKey verify db with auth key]
     * @method verfiyAuthKey
     * @param  [string]        $authkey [description]
     * @return [true/false]                 [description]
     */

    public static function verfiyAuthKey($authkey)
    {
        $result = self::where('authkey', '=', $authkey)
            ->where('email_verification', '=', false)
            ->first();

        if (is_null($result)) {
            return false;
        }
        return true;
    }

    /**
     * [updateEmailVerification - Update verification db]
     * @method updateEmailVerification
     * @param  [string]                $authkey
     * @return []                      [user details]
     */

    public static function updateEmailVerification($authkey)
    {

        $result = self::where('authkey', '=', $authkey)
            ->where('email_verification', '=', false)
            ->first();

        self::where('authkey', '=', $authkey)
            ->update([
                'email_verification' => true,
                'status' => 'ACTIVE'
            ]);

        self::sendRegistrationUserMail(
            $result['firstname'],
            $result['lastname'],
            $result['username'],
            $result['email']
        );

        self::sendRegistrationAdminMail($result['username']);

        return $result;
    }

    public static function getPasswordUpdate($uid, $input)
    {
        $user = self::where('uid', '=', (int)$uid)->get()->toArray();
        $user = $user[0];
        if (!empty($input['password'])) {
            $array = [
                'password' => Hash::make($input['password']),
            ];
            self::where('uid', '=', (int)$uid)->update($array);

            // sending email
            $site_name = config('app.site_name');
            $to = $user['email'];
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
            $headers .= 'From:' . $site_name . "\r\n";
            $name = 'change-password';

            $base_url = config('app.url');

            $email_details = Email::getEmail($name);
            $subject = $email_details[0]['subject'];
            $body = $email_details[0]['body'];

            $login_url = '<a href="' . $base_url . '/auth/login">Click here to login</a>';
            $support_email = config('mail.from');
            $name = ucwords($user['firstname']) . ' ' . ucwords($user['lastname']);
            $subject_find = ['<Website Name>'];
            $subject_replace = [$site_name];
            $subject = str_replace($subject_find, $subject_replace, $subject);
            $find = ['<NAME>', '<SITE NAME>', '<LOGIN URL>', '<SUPPORT EMAIL>'];
            $replace = [$name, $site_name, $login_url, $support_email['address']];
            $body = str_replace($find, $replace, $body);

            Common::sendMailHtml($body, $subject, $to);
        }
    }

    public static function getUserRelations($uid)
    {
        if(is_array($uid) && !empty($uid)) {
            $relation = self::whereIn('uid', $uid)->get(['relations'])->toArray();
        } else {
            $relation = self::where('uid', '=', (int)$uid)->value('relations');
        }
       return $relation;
    }


    public static function samlCheckUser($input)
    {
        return $is_user_check = self::where('username', '=', $input['username'])
            ->orWhere('email', '=', $input['email'])
            ->count();
    }

    public static function samlRegister($input)
    {

        if (isset($input['timezone'])) {
            $timezone = $input['timezone'];
        } else {
            $timezone = config('app.default_timezone');
        }

        $uid = self::uniqueId();
        $auth_key = self::getAuthKey($input['email'], $input['password']);
        $default_array_vals = [
            'uid' => (int)$uid,
            'timezone' => $timezone,
            'password' => Hash::make($input['password']),
            'status' => 'ACTIVE',
            'role' => (int)config('app.learner_role_id'),
            //'relations' => new stdClass(),
            'created_at' => time(),
            'created_by' => $input['email'],
            'authkey' => $auth_key, //authkey
            'app_registration' => 0,
            'email_verification' => false,
        ];


        self::insert(array_merge($input, $default_array_vals));

        $arrname = 'user_feed_rel';
        self::where('uid', $uid)->push('relations.' . $arrname, 0, true);
        self::where('uid', $uid)->pull('relations.' . $arrname, 0);
        self::updateSessionID($uid);

        return $uid;
    }

    /**
     * Create many to many relation b/w user and program
     * @return mixed
     */
    public function programs()
    {
        return $this->belongsToMany(
            \App\Model\Program::class,
            "relations.user_feed_rel",
            "relations.active_user_feed_rel"
        );
    }

    /**
     * @param $query
     * @param array $filter_params
     */
    public function scopeFilter($query, $filter_params)
    {
        return $query->when(
            isset($filter_params["user_ids"]),
            function ($query) use ($filter_params) {
                return $query->whereIn("uid", $filter_params["user_ids"]);
            }
        )->when(
            isset($filter_params["not_in_user_ids"]),
            function ($query) use ($filter_params) {
                return $query->whereNotIn("uid", $filter_params["not_in_user_ids"]);
            }
        )->when(
            !empty($filter_params["status"]),
            function ($query) use ($filter_params) {
                if (is_array($filter_params["status"])) {
                    return $query->whereIn("status", $filter_params["status"]);
                } else {
                    return $query->where("status", $filter_params["status"]);
                }
            }
        )->when(
            !empty($filter_params["search_key"]),
            function ($query) use ($filter_params) {
                return $query->orWhere("firstname", "like", "%{$filter_params["search_key"]}%")
                    ->orWhere("username", "like", "%{$filter_params["search_key"]}%")
                    ->orWhere("email", "like", "%{$filter_params["search_key"]}%");
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
     * The user that belong to many usergroup.
     */
    public function userGroup()
    {
        return $this->belongsToMany(
            UserGroup::class,
            'relations.active_usergroup_user_rel',
            'relations.active_user_usergroup_rel'
        );
    }

    /**
     * package belongs to many user  
     *
     * @return \Jenssegers\Mongodb\Relations\BelongsToMany
     */
    public function package()
    {
        return $this->belongsToMany(
            Package::class,
            'package_ids',
            'user_ids'
        );
    }

    //TODO: This method not recommended to use since it returns only user ids
    public static function getAdminUsers()
    {
        try {
            $admin_user_role_ids = Role::where("is_admin_role", true)->pluck('rid')->toArray();
            $admin_user_ids = UserRoleAssignment::whereIn("role_id", $admin_user_role_ids)
                ->pluck("user_id")->toArray();
        } catch (RoleNotFoundException $e) {
            $admin_user_ids = [];
        }

        return $admin_user_ids;
    }

    /**
     * [getUsersid get list of users ids only]
     * @return [collection] [it collection]
     */
    public static function getUserids()
    {
        return self::where('status', '!=', 'DELETED')->pluck('uid');
    }

    /**
     * Method used to get count for active user
     * @param $user_name
     * @return int
     */
    public static function getActiveUserCount($user_name)
    {
        return self::where('status', '=', 'ACTIVE')
            ->where('username', '=', $user_name)
            ->count();
    }

    /**
     * Method used to get active usergroup relation for In-Active user
     * @param $user_name
     * @return collection
     */
    public static function getActiveUsergroupUserRel($user_name)
    { 
        return self::where('status', '=', 'IN-ACTIVE')
           ->where('username', '=', $user_name)
           ->first();
    }

    /**
     * @param $query
     * @return collection
     */
    public static function scopeActive($query)
    {
        return $query->where('status', '=', 'ACTIVE');
    }

    public static function removeUserSurvey($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('uid', (int)$key)->pull($field, (int)$id);
        }
        return self::where('uid', (int)$key)->update(['updated_at' => time()]);
    }

    public static function addUserSurvey($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('uid', (int)$key)->push($field, (int)$id, true);
        }
        return self::where('uid', (int)$key)->update(['updated_at' => time()]);
    }

    public static function removeUserAssignment($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('uid', (int)$key)->pull($field, (int)$id);
        }
        return self::where('uid', (int)$key)->update(['updated_at' => time()]);
    }

    public static function addUserAssignment($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('uid', (int)$key)->push($field, (int)$id, true);
        }
        return self::where('uid', (int)$key)->update(['updated_at' => time()]);
    }

    public static function getUserColumnsbyId($user_ids, $columns)
    {
        if (is_array($user_ids)) {
            $query = self::whereIn('uid', $user_ids);
        } else {
            $query = self::where('uid', '=', $user_ids);
        }
        return $query->where('status', '!=', 'DELETED')->get($columns);
    }
}
