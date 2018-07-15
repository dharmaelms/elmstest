<?php
namespace App\Model\User\Repository;

use App\Enums\Cron\CronBulkImport;
use App\Enums\RolesAndPermissions\SystemRoles;
use App\Enums\User\EnrollmentSource;
use App\Enums\User\EnrollmentStatus;
use App\Enums\User\NDAStatus;
use App\Enums\User\SSOTokenStatus;
use App\Enums\User\UserStatus;
use App\Exceptions\RolesAndPermissions\RoleNotFoundException;
use App\Exceptions\User\UserEntityRelationNotFoundException;
use App\Exceptions\User\UserNotFoundException;
use App\Model\Common;
use App\Model\Country;
use App\Model\CustomFields\Entity\CustomFields;
use App\Model\Email;
use App\Model\ImportLog\Entity\UsergroupLog;
use App\Model\ImportLog\Entity\UserLog;
use App\Model\Role;
use App\Model\RolesAndPermissions\Entity\UserRoleAssignment;
use App\Model\States;
use App\Model\User;
use App\Model\User\Entity\UserEnrollment;
use App\Model\UserGroup;
use Auth;
use Carbon\Carbon;
use Hash;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Log;
use Timezone;
use Validator;

/**
 * Class UserRepository
 * @package App\Model\User\Repository
 */
class UserRepository implements IUserRepository
{
    /**
     * @inheritDoc
     */
    public function find($uid)
    {
        return User::findOrFail((int) $uid);
    }
   /**
     * @param string $status
     * @param string $slug
     * @param string $reason
     * @param string $action
     * @return nothing sends email to support
     */
    public function sendUserEmail($status, $slug, $reason, $action)
    {
        $created_date = date('d-m-Y');
        $site_name = config('app.site_name');
        $to = config('app.import_email');
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
        $headers .= 'From:' . $site_name . "\r\n";
        if ($status == 'SUCCESS') {
            $find = ['<EMAIL>','<TOTAL>', '<SUCCESS>', '<FAILURE>','<SITE NAME>'];
            $total = UserLog::getUserImportCount($type = 'ALL', $search = null, $created_date, $action);
            $success = UserLog::getUserImportCount($type = 'SUCCESS', $search = null, $created_date, $action);
            $failure = UserLog::getUserImportCount($type = 'FAILURE', $search = null, $created_date, $action);
            $replace = [$to,$total, $success, $failure, $site_name];
        } else {
            $find = ['<EMAIL>','<REASON>','<SITE NAME>'];
            $replace = [$to, $reason, $site_name];
        }
        $email_details = Email::getEmail($slug);
        $subject = $email_details[0]['subject'];
        $body = $email_details[0]['body'];
        $body = str_replace($find, $replace, $body);
        Common::sendMailHtml($body, $subject, $to);
    }

   /**
     * @param array $data
     * @param int $cron
     * @return int $uid
     */
    public function getUserData($data, $cron)
    {
        $uid = User::uniqueId();
        $time_zone = config('app.default_timezone');
        $auth_key = User::getAuthKey($data['email'], $data['password']);
        $data['role'] = Role :: getErpRole($data['role']);
        $data['username'] = strtolower($data['username']);
        $data['timezone'] = $time_zone;
        $data['authkey'] = $auth_key;
        $data['app_registration'] = 0;
        $data['email_verification'] = 0;
        $data['status'] = 'ACTIVE';
        $data['created_at'] = time();
        $data['created_by'] = ($cron == 0) ? Auth::user()->username : CronBulkImport::CRONUSER;
        $data['password'] = Hash::make($data['password']);
        $userid['uid'] = $uid;
        $country = str_replace(' ', '', strtolower($data['country']));
        $countries = Country::getCountries();
        foreach ($countries as $values) {
            $db_countries =  str_replace(' ', '', strtolower($values['country_name']));
            if ($db_countries == $country) {
                $country_code = $values['country_code'];
                $data['country'] = $country_code;
                $country_code_check = States::where('country_code', $country_code)->first();
                if (count($country_code_check) > 0) {
                    $state = str_replace(' ', '', strtolower($data['state']));
                    $state_records = States::all();
                    $states = $state_records[0]['states'];
                    foreach ($states as $values) {
                        $db_states = str_replace(' ', '', strtolower($values));
                        if ($db_states == $state) {
                            $data['state'] = $values;
                        }
                    }
                }
            }
        }

        $data['username'] = strtolower($data['username']);
        $data['email'] = strtolower($data['email']);
        if (!empty($data['address'])) {
            $data['default_address_id'] = md5(uniqid(rand(), true));
            $myaddress = [];
            $myaddress[0] = [
            'address_id' => $data['default_address_id'],
            'fullname' => trim($data['firstname'].' '.$data['lastname']),
            'street' => $data['address'],
            'landmark' => $data['landmark'],
            'city' => $data['city'],
            'country' => $data['country'],
            'state' => $data['state'],
            'pincode' => $data['pincode'],
            'phone' => $data['phone']
            ];
            $data['myaddress'] = $myaddress;
        }
      
        unset($data['address'], $data['landmark'], $data['country'], $data['state'], $data['city'],$data['pincode'], $data['phone']);
        $record = array_merge($userid, $data);
        unset($record['usergroup']);
        /*Replace custom field label with field name*/
        $userCustomField = CustomFields::getUserActiveCustomField('user', '');
        $customField = [];
        foreach ($userCustomField as $user) {
            $customField[array_get($user, 'fieldlabel')] = null;
        }
        $userlist = array_intersect_key($record, $customField);
        $userlist_with_custom_fields = $record;
        $custom_field_keys = array_keys($userlist);
        foreach ($custom_field_keys as $value) {
            if (array_key_exists($value, $record)) {
                unset($record[$value]);
            }
        }
        $userlist_without_custom_fields = $record;
        $custom = CustomFields::getUserCustomField('user', '');
        $fieldlabel = array_column($custom, 'fieldlabel');
        $custom_field_names = array_keys(array_intersect_key($userlist_with_custom_fields, array_flip($fieldlabel)));
        $values = array_intersect_key($userlist_with_custom_fields, array_flip($fieldlabel));
        $custom_field_details = $this->getCustomFieldDetails($custom_field_names)->toArray();
        $fieldnames = array_column($custom_field_details, 'fieldname');
        $fieldlabels = array_column($custom_field_details, 'fieldlabel');
        $keys = array_flip(array_combine($fieldlabels, $fieldnames));
        $keys = array_keys($keys);
        $custom_fields_data = array_combine($keys, $values);
        $record = array_merge($userlist_without_custom_fields, $custom_fields_data);
        /*Code end here for replace custom field label with field name*/
        User::insert($record);
        $arrname = 'user_feed_rel';
        User::where('uid', $uid)->push('relations.' . $arrname, 0, true);
        User::where('uid', $uid)->pull('relations.' . $arrname, 0);
        return $uid;
    }
    /**
     * @inheritDoc
     */
    public function getRegisteredUsers($filter_params = [])
    {
        try {
            $registered_user_role_id = Role::where("slug", SystemRoles::REGISTERED_USER)->first()->rid;
            $registered_user_ids = UserRoleAssignment::where("role_id", $registered_user_role_id)
                                                        ->pluck("user_id")->toArray();
        } catch (RoleNotFoundException $e) {
            $registered_user_ids = [];
        }

        $filter_params = array_merge($filter_params, ["user_ids" => $registered_user_ids]);
        return User::filter($filter_params)->get();
    }
    
    /**
     * @inheritDoc
     */
    public function getRegisteredUsersId($filter_params = [])
    {
        try {
            $registered_user_role_id = Role::where("slug", SystemRoles::REGISTERED_USER)->first()->rid;
            $registered_user_ids = UserRoleAssignment::where("role_id", $registered_user_role_id)
                                                        ->pluck("user_id")->all();
        } catch (RoleNotFoundException $e) {
            $registered_user_ids = [];
        }

        $filter_params = array_merge($filter_params, ["user_ids" => $registered_user_ids]);
        return User::filter($filter_params)->pluck('uid')->all();
    }
    
    /**
     * @inheritDoc
     */
    public function getTotalUsersCount()
    {
        return User::filter(["status" => UserStatus::ACTIVE])
                    ->count();
    }

    /**
     * @inheritDoc
     */
    public function get($filter_params = [], $start = 0, $limit = 0)
    {
        if ($start == 0 && $limit == 0) {
            return User::filter($filter_params)
                    ->get();
        } else {
            return User::filter($filter_params)
                    ->skip((int)$start)
                    ->take((int)$limit)
                    ->get();
        }
    }

    /**
     * @inheritDoc
     */
    public function getUsersCount($filter_params = [])
    {
        return User::filter($filter_params)
            ->count();
    }

    /**
     * @inheritDoc
     */
    public function findActiveUser($id)
    {
        try {
            return User::where("uid", $id)
                ->where("status", UserStatus::ACTIVE)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new UserNotFoundException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getListOfUsersDetails($user_ids, $start = 0, $limit = 0)
    {
        $query =  User::where('status', '!=', 'DELETED');
        if (isset($start, $limit) && $limit > 0) {
            $query->skip((int)$start)->limit((int)$limit);
        }
        return $query->whereIn('uid', $user_ids)
            ->orderBy('firstname', 'asc')
            ->get(['uid', 'firstname', 'lastname', 'username', 'fullname', 'email', 'mobile', 'subscription']);
    }

    /**
     * {@inheritdoc}
     */
    public function getListOfActiveUsers($user_ids, $start = 0, $limit = 0, $search = '')
    {
        $query =  User::where('status', '=', 'ACTIVE')
                    ->whereIn('uid', $user_ids);
        if (isset($search) && !empty($search)) {
            $query = $query->orWhere('username', 'like', '%' . $search . '%')
                  ->orWhere('firstname', 'like', '%' . $search . '%')
                  ->orWhere('lastname', 'like', '%' . $search. '%')
                  ->orWhere('email', 'like', '%' . $search. '%');
        }
        if (isset($start, $limit) && $limit > 0) {
            $query->skip((int)$start)->limit((int)$limit);
        }
        return $query->orderBy('firstname', 'asc')
                  ->get(['uid', 'firstname', 'lastname', 'username', 'fullname', 'email']);
    }

    /**
     * {@inheritdoc}
     */
    public function getListOfUsersCount($user_ids)
    {
        return User::where('status', '!=', 'DELETED')
                    ->whereIn('uid', $user_ids)
                    ->orderBy('firstname', 'asc')
                    ->count();
    }
    /**
     * {@inheritdoc}
     */
    public function getAllUsersBySearchKey($search = '', $start = 0, $limit = 500)
    {
        $query = User::where('status', '!=', 'DELETED');
        if ($search != '') {
            $query->where('username', 'like', "%" .$search. "%");
        }
        return $query->skip((int)$start)
                ->take((int)$limit)
                ->orderBy('username', 'asc')
                ->get(['uid', 'username', 'firstname', 'lastname', 'fullname']);
    }

    /**
     * {@inheritdoc}
     */
    public function getSpecificUser($user_id = 0)
    {
        return  User::where('uid', '=', (int)$user_id)->first();
    }

    /**
     * Method to download update users csv file
     * @returns update users csv file with fields as headers
     */
    public function downloadUpdateUserTemplate()
    {
        $data = [];
        $data[] = 'firstname*';
        $data[] = 'lastname';
        $data[] = 'email*';
        $data[] = 'mobile';
        $data[] = 'username*';
        $data[] = 'newusername*';
        $data[] = 'role*';
        $custom_fields = CustomFields::getUserCustomFieldArr('user', '', ['fieldlabel', 'fieldlabel','mark_as_mandatory']);
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom) {
                if ($custom['mark_as_mandatory']=='yes') {
                    $data[] = array_get($custom, 'fieldlabel').'*';
                } else {
                    $data[] = array_get($custom, 'fieldlabel');
                }
            }
        }
            
        $file = config('app.user_import_file');
        header('Content-type: application/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file);
        $fp = fopen('php://output', 'w');
        fputcsv($fp, $data);
        die;
    }
    /**
     * Method to get userid
     * @param string $username
     * @returns int $userid
     */
    public function getUserIdByUserName($username)
    {
        $username=strtolower($username);
        $userid = User::where('username', '=', $username)->value('uid');
        return $userid;
    }
    /**
     * Method to update user details
     * @param int $uid
     * @param array $data
     * @returns nothing updates user information
     */
    public function updateUserDetails($uid, $data)
    {
        if (array_key_exists('newusername', $data)) {
             $data['username'] = strtolower($data['newusername']);
             unset($data['newusername']);
        }
        if (array_key_exists('role', $data)) {
             $data['role'] = Role :: getErpRole($data['role']);
        }

      /*Replace custom field label with field name*/
        $userCustomField = CustomFields::getUserActiveCustomField('user', '');
        $customField = [];
        foreach ($userCustomField as $user) {
            $customField[array_get($user, 'fieldlabel')] = null;
        }
        $userlist = array_intersect_key($data, $customField);
        $userlist_with_custom_fields = $data;
        $custom_field_keys = array_keys($userlist);
        foreach ($custom_field_keys as $value) {
            if (array_key_exists($value, $data)) {
                unset($data[$value]);
            }
        }
        $userlist_without_custom_fields = $data;
        $custom = CustomFields::getUserCustomField('user', '');
        $fieldlabel = array_column($custom, 'fieldlabel');
        $custom_field_names = array_keys(array_intersect_key($userlist_with_custom_fields, array_flip($fieldlabel)));
        $values = array_intersect_key($userlist_with_custom_fields, array_flip($fieldlabel));
        $custom_field_details = $this->getCustomFieldDetails($custom_field_names)->toArray();
        $fieldnames = array_column($custom_field_details, 'fieldname');
        $fieldlabels = array_column($custom_field_details, 'fieldlabel');
        $keys = array_flip(array_combine($fieldlabels, $fieldnames));
        $keys = array_keys($keys);
        $custom_fields_data = array_combine($keys, $values);
        $data = array_merge($userlist_without_custom_fields, $custom_fields_data);
      /*Code end here for replace custom field label with field name*/
        User::where('uid', '=', (int)$uid)->update($data);
    }
    /**
     * @param string $status
     * @param string $slug
     * @param string $reason
     * @param string $action
     * @return nothing sends email to support
     */
    public function sendUserGroupEmail($status, $slug, $reason, $action)
    {
        $created_date = date('d-m-Y');
        $site_name = config('app.site_name');
        $to = config('app.import_email');
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
        $headers .= 'From:' . $site_name . "\r\n";
        if ($status == 'SUCCESS') {
            $find = ['<EMAIL>','<TOTAL>', '<SUCCESS>', '<FAILURE>','<SITE NAME>'];
            $total = UsergroupLog::getUserGroupUpdateCount($type = 'ALL', $search = null, $created_date, $action);
            $success = UsergroupLog::getUserGroupUpdateCount($type = 'SUCCESS', $search = null, $created_date, $action);
            $failure = UsergroupLog::getUserGroupUpdateCount($type = 'FAILURE', $search = null, $created_date, $action);
            $replace = [$to,$total, $success, $failure, $site_name];
        } else {
            $find = ['<EMAIL>','<REASON>','<SITE NAME>'];
            $replace = [$to, $reason, $site_name];
        }
        $email_details = Email::getEmail($slug);
        $subject = $email_details[0]['subject'];
        $body = $email_details[0]['body'];
        $body = str_replace($find, $replace, $body);
        Common::sendMailHtml($body, $subject, $to);
    }

    /**
     * Method to get user group id
     * @param string $ugname
     * @returns int $ugid
     */
    public function getUserGroupIdByGroupName($ugname)
    {
        $ugname = strtolower($ugname);
        $ugid = UserGroup::where('ug_name_lower', '=', $ugname)->value('ugid');
        return $ugid;
    }
    /**
     * Method to update user & usergroup relations
     * @param int $uid
     * @param int $gid
     * @returns nothing updates user & usergroup relations
     */
    public function updateUserUserGroupRelations($uid, $gid, $operation, $ug_arrayname = 'active_user_usergroup_rel', $user_arrayname = 'active_usergroup_user_rel')
    {
        if ($operation == 'assign') {
            UserGroup::where('ugid', (int)$gid)->push('relations.'.$ug_arrayname, (int)$uid, true);
            User::where('uid', (int)$uid)->push('relations.'.$user_arrayname, (int)$gid, true);
        }
        if ($operation == 'unassign') {
            UserGroup::where('ugid', (int)$gid)->pull('relations.'.$ug_arrayname, (int)$uid);
            User::where('uid', (int)$uid)->pull('relations.'.$user_arrayname, (int)$gid);
        }
    }
    /**
     * Method to download user-usergroup mapping csv file
     * @returns csv file with fields as headers
     */
    public function downloadUserUserGroupTemplate()
    {
        $data = [];
        $data[] = 'username*';
        $data[] = 'usergroup*';
        $data[] = 'operation*';
        $file = config('app.user_usergroup_import_file');
        header('Content-type: application/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file);
        $fp = fopen('php://output', 'w');
        fputcsv($fp, $data);
        die;
    }
    /**
     * Method to export users & usergroups
     * @return csv file with users & usergroups
     */
    public function getUserUgExport($status, $operation, $created_date)
    {
        $reports = UsergroupLog::getUserUgExportRecords($status, $created_date, $operation);

        if (!empty($reports)) {
            $data = [];
            $data[] = ['UserUserGroupmappingReport'];
            $header[] = 'username';
            $header[] = 'usergroup';
            $header[] = 'operation';
            $header[] = 'createdate';
            $header[] = 'status';
            $header[] = 'errormessage';
            $data[] = $header;
            foreach ($reports as $report) {
                $tempRow = [];
                if ($report['status'] == 'SUCCESS') {
                    $user = User::getUserDetailsByID($report['userid']);
                    $username = $user['username'];
                    $usergroup = UserGroup::where('ugid', '=', (int)$report['groupid'])->value('usergroup_name');
                    $error = '';
                } else {
                    $username = $report['username'];
                    $usergroup = $report['usergroup'];
                    $error = $report['error_msgs'];
                }
                $tempRow[] = $username;
                $tempRow[] = $usergroup;
                $tempRow[] = $report['operation'];
                $tempRow[] = Timezone::convertFromUTC($report['created_at'], Auth::user()->timezone, config('app.date_format'));
                $tempRow[] = $report['status'];
                $tempRow[] = $error;

                $data[] = $tempRow;
            }
            if (!empty($data)) {
                $filename = "UserUserGroupReport.csv";
                $fp = fopen('php://output', 'w');
                header('Content-type: application/csv');
                header('Content-Disposition: attachment; filename=' . $filename);
                foreach ($data as $row) {
                    fputcsv($fp, $row);
                }
                exit;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getExpireSubscribedChannels($date)
    {
       
        return User::raw(function ($c) use ($date) {
            return $c->aggregate([
                [
                    '$match' => [
                        'status' => 'ACTIVE',
                        'subscription' => [
                            '$elemMatch' => [
                                'end_time' => [
                                    '$gte' => array_get($date, 'start', 0),
                                    '$lte' => array_get($date, 'end', time())
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => ['uid' => '$uid'],
                        'subscription' => ['$addToSet' => '$subscription']
                    ]
                ],
                [
                    '$unwind' => '$subscription'
                ],
                [
                    '$unwind' => '$subscription'
                ],
                [
                    '$match' => [
                        'subscription.end_time' => [
                            '$gte' => array_get($date, 'start', 0),
                            '$lte' => array_get($date, 'end', time())
                        ]
                    ]
                ],
                [
                    '$group' => [
                       '_id' => ['uid' => '$_id.uid'],
                       'subscription' => ['$addToSet' => '$subscription'],
                       'program_id' => ['$push' => '$subscription.program_id']
                    ]
                ],
                [
                    '$project' => [
                        'uid' => '$_id.uid',
                        'program_id' => 1,
                        '_id' => 0
                    ]
                ]
            ]);
        });
    }

    /**
     * @inheritdoc
     */
    public function getUsersByUidUGid($u_ids, $ug_ids)
    {
        return User::whereIn('uid', $u_ids)
            ->orWhereIn('relations.active_usergroup_user_rel', $ug_ids)
            ->get(['uid', 'relations', 'email', 'firstname', 'lastname', 'timezone']);
    }

    /**
     * @inheritdoc
     */
    public function getUsersByUGids($ug_ids)
    {
        return User::WhereIn('relations.active_usergroup_user_rel', $ug_ids)
                    ->where('status', '!=', 'DELETED')
                    ->get(['uid']);
    }

    /**
     * @inheritDoc
     */
    public function createUserEntityRelation($user_id, $entity_data, $enrollment_source_data)
    {
        $user_entity_relation = new UserEnrollment();
        $user_entity_relation->user_id = $user_id;
        $user_entity_relation->entity_type = array_get($entity_data, "entity_type");
        $user_entity_relation->entity_id = array_get($entity_data, "entity_id");
        $user_entity_relation->valid_from = array_has($entity_data, "valid_from")?
            (int) $entity_data["valid_from"] : null;
        $user_entity_relation->expire_on = array_has($entity_data, "expire_on")? $entity_data["expire_on"] : null;
        $user_entity_relation->status = EnrollmentStatus::ENROLLED;
        $user_entity_relation->source_type = array_get($enrollment_source_data, "source_type");

        if ($user_entity_relation->source_type === EnrollmentSource::USER_GROUP) {
            $user_entity_relation->source_id = array_get($enrollment_source_data, "source_id");
        } elseif ($user_entity_relation->source_type === EnrollmentSource::SUBSCRIPTION) {
            $user_entity_relation->source_id = $enrollment_source_data["subscription_slug"];
        }

        $user_entity_relation->enrolled_on = Carbon::create()->timestamp;
        $user_entity_relation->enrolled_by = array_get($enrollment_source_data, "enrolled_by");

        $user_entity_relation->unenrolled_on = null;
        $user_entity_relation->unenrolled_by = null;

        $user_entity_relation->save();

        return $user_entity_relation;
    }

    /**
     * @inheritDoc
     */
    public function findUserEntityRelation(
        $user_id,
        $entity_type,
        $entity_id,
        $enrollment_source_type,
        $enrollment_source_id = null
    ) {
        try {
            return UserEnrollment::filter(
                [
                    "user_id" => $user_id,
                    "entity_type" => $entity_type,
                    "entity_id" => $entity_id,
                    "source_type" => $enrollment_source_type,
                    "source_id" => $enrollment_source_id
                ]
            )->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new UserEntityRelationNotFoundException();
        }
    }

    /**
     * @inheritDoc
     */
    public function getUserEntities($user, $filter_params = [])
    {
        $entities = new Collection();
        try {
            if (!$user instanceof User) {
                $user = $this->findActiveUser($user);
            }
            $entities = $this->getActiveUserEntityRelations(
                array_merge($filter_params, ["user_id" => $user->uid])
            );
        } catch (ApplicationException $e) {
            Log::error($e->getTraceAsString());
        }
        return $entities;
    }

    /**
     * @inheritDoc
     */
    public function findActiveUserEntityRelation($user_id, $entity_type, $entity_id)
    {
        try {
            return UserEnrollment::filter(
                [
                    "user_id" => $user_id,
                    "entity_type" => $entity_type,
                    "entity_id" => $entity_id,
                ]
            )->active()
            ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new UserEntityRelationNotFoundException();
        }
    }

    /**
     * @inheritDoc
     */
    public function getUserEntityRelations($filter_params = [])
    {
        return UserEnrollment::filter($filter_params)->get();
    }

    /**
     * @inheritDoc
     */
    public function getEnrolledUsersByDate($filter_params = [])
    {
        return UserEnrollment::filter($filter_params)->get(['enrolled_on', 'user_id']);
    }

    /**
     * @inheritDoc
     */
    public function getUserEntityRelationsUIds($filter_params = [])
    {
        return UserEnrollment::filter($filter_params)->pluck('user_id')->all();
    }

    /**
     * @inheritDoc
     */
    public function getActiveUserEntityRelations($filter_params = [])
    {
        return UserEnrollment::filter($filter_params)
            ->active()
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getNewUsersCount(array $date)
    {
        return User::whereIn('status', ['ACTIVE', 'IN-ACTIVE'])
                ->whereBetween('created_at', $date)
                ->count();
    }


    /**
     * @inheritdoc
     */
    public function newSSOUser($request)
    {
        $user = new \StdClass;
        $user->uid = User::uniqueId();
        $user->email = $request->email_id;
        $user->username = array_get($request, 'username', $request->email_id);
        $user->firstname = $request->first_name;
        $user->lastname = array_get($request, 'last_name', '');
        $user->mobile = array_get($request, 'phone_number', '');
        $user->role = (int)$request->role_id;
        $user->timezone = array_get($request, 'timezone', config('app.default_timezone'));
        $user->status = UserStatus::ACTIVE;
        $user->nda_status = NDAStatus::NO_RESPONSE;
        $user->email_verification = false;
        $user->created_at = time();
        $user->created_by = $user->username;
        $user->remember_token = str_random(60);
        $access_token = str_random(60);
        $user->sso_token = ['token' => $access_token, 'expired_at' => time() + 300, 'status' => SSOTokenStatus::NOT_USED];
        $user->relations['active_usergroup_user_rel'] = [(int)config('app.sso.usergroup_id')];
        User::insert(collect($user)->toArray());
        Log::info('SSO: Account created for ' . $request->email_id);
        return ['access_token' => $access_token, 'uid' => $user->uid];
    }

    /**
     * {@inheritdoc}
     */
    public function findByEmail($email_id)
    {
        return User::where('email', $email_id)->get();
    }

    /**
     * @inheritdoc
     */
    public function getFtpConnectionDetails()
    {
        $email = config('app.import_email');
        $path = config('app.file_path');
        $conn_id = UserLog::getFtpDetails();
        $dir_list = ftp_nlist($conn_id, '.');
        $ftp_connection_details = [
          'email' => $email,
          'path' => $path,
          'conn_id' => $conn_id,
          'dir_list' => $dir_list,
        ];
        return $ftp_connection_details;
    }

    /**
     * @inheritdoc
     */
    public function getFtpDirDetails($ftp_conn_id)
    {
         $ftp_chdir = ftp_chdir($ftp_conn_id['conn_id'], "add");
         $file_list = ftp_nlist($ftp_conn_id['conn_id'], '.');
         $local_file = config('app.user_import_file');
         $ftp_dir_details = [
          'file_list' => $file_list,
          'local_file' => $local_file,
          'ftp_chdir' => $ftp_chdir,
         ];
         return $ftp_dir_details;
    }


    /**
     * @inheritdoc
     */
    public function getUserImportFileColumns($ftp_connection_details, $ftp_dir_details)
    {
        $fd = fopen($ftp_connection_details['path'] . $ftp_dir_details['local_file'], "w");
        fclose($fd);
        ftp_get($ftp_connection_details['conn_id'], $ftp_connection_details['path'] . $ftp_dir_details['local_file'], $ftp_dir_details['local_file'], FTP_BINARY); //downoading file from ftp to remote
        ftp_delete($ftp_connection_details['conn_id'], $ftp_dir_details['local_file']);
        ftp_close($ftp_connection_details['conn_id']); //ftp connection close
      //process records starts here
        $csvFile = file($ftp_connection_details['path'] . $ftp_dir_details['local_file']);
        $csv = array_map("str_getcsv", $csvFile);
        $headers = array_shift($csv);
        $customfield = array();
        $count = 0;
        $core_fields = array('firstname', 'lastname', 'email', 'mobile', 'username', 'password', 'usergroup', 'role', 'address', 'landmark', 'country', 'state', 'city', 'pincode', 'phone');
        $custom_fields = CustomFields::getUserCustomFieldArr('user', '', ['fieldlabel', 'fieldlabel']);
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom) {
                $customfield[] = $custom['fieldlabel'];
            }
        }
        $fields = array_merge($core_fields, $customfield);
      /*---Validate headers starts here---*/
        foreach ($headers as $header) {
            $head[] = str_replace("*", '', $header);
            if (!in_array(str_replace("*", '', $header), $fields)) {
                $count++;
            }
        }
        $csv_file_data = [
        'count' => $count,
        'csvFile' => $csvFile,
        'head' => $head,
        ];
        return $csv_file_data;
    }

    /**
     * @inheritdoc
     */
    public function validateRulesErp($csvrowData)
    {
        $rules = [
            'firstname' => 'Required|Min:3|Max:30|Regex:/^[[:alpha:]]+(?:[-_ ]?[[:alpha:]]+)*$/',
            'lastname' => 'Max:15|Regex:/^([A-Za-z\'. ])+$/',
            'mobile' => 'numeric|Regex:/^([0-9]{10})/',
            'password' => 'Required|Regex:/^[0-9A-Za-z!@#$%_.-]{6,24}$/',
            'email' => 'Required|email|checkemail:' . $csvrowData['email']  . '',
            'username' => 'Required|Min:3|Max:93|checkUserNameRegex|checkusername:' . $csvrowData['username'] . '',
            'usergroup' => 'Min:3|Max:60|Regex:/^([a-zA-Z0-9 :_()+-])+$/|checkusergroupexists|checkactiveusergroupexists',
            'role' => 'Required|In:learner,contentauthor,channeladmin',
            'address' => 'required_with:landmark,country,state,city,pincode,phone|Min:3|Max:255|regex:/[a-zA-Z#.,0-9- ]+$/',
            'landmark' => 'Min:3|Max:255|Regex:/^([a-zA-Z0-9:.,\-@#&()\/+\n\' ])+$/',
            'country' => 'required_with:address|Min:3|Max:30|regex:/[a-zA-Z ]+$/|checkcountry',
            'state' => 'required_with:address|Min:3|Max:75|regex:/[a-zA-Z ]+$/|checkstate:'.$csvrowData['country'].'',
            'city' => 'required_with:address|Min:3|Max:75|regex:/^[a-zA-Z ]+$/',
            'pincode' => 'required_with:address|regex:/^[0-9]{6}$/',
            'phone' => 'required_with:address|regex:/[0-9+-]{10,15}$/',
         ];

       
        $custom_fields = CustomFields::getUserCustomFieldArr('user', '', ['fieldlabel', 'fieldlabel', 'mark_as_mandatory']);

        if (!empty($custom_fields)) {
            foreach ($custom_fields as $key => $values) {
                if ($values['mark_as_mandatory'] == 'yes') {
                    $rules[array_get($values, 'fieldlabel')] = 'Required';
                }
            }
        }

        Validator::extend('checkUserNameRegex', function ($attribute, $value, $parameters) {
            if (isset($value) && !empty($value)) {
                (strpos($value, '@') !== false) ? $pattern = "/^(?!.*?[._]{2})[a-zA-Z0-9]+[a-zA-Z0-9(\._\)?-]+[a-zA-Z0-9]+@[a-zA-Z0-9]+([\.]?[a-z]{2,6}+)*$/" : $pattern = "/^[a-zA-Z0-9._]*$/";
                if (preg_match($pattern, $value)) {
                    return true;
                } else {
                    return false;
                }
            }
        });



         Validator::extend('checkemail', function ($attribute, $value, $parameters) {
            $email = strtolower($parameters[0]);
            $returnval = User::where('email', '=', $email)->get(['uid'])->toArray();
            if (empty($returnval)) {
                return true;
            }

            return false;
         });


            Validator::extend('checkusername', function ($attribute, $value, $parameters) {
                  $username = strtolower($parameters[0]);
                  $returnval = User::where('username', 'like', $username)->get(['uid'])->toArray();
                if (empty($returnval)) {
                    return true;
                }

                  return false;
            });

            Validator::extend('checkcountry', function ($attribute, $value, $parameters) {
                $country = str_replace(' ', '', strtolower($value));
                $countries = Country::getCountries();
                foreach ($countries as $values) {
                    $db_countries =  str_replace(' ', '', strtolower($values['country_name']));
                    if ($db_countries == $country) {
                        return true;
                    }
                }
                   return false;
            });

            Validator::extend('checkstate', function ($attribute, $value, $parameters) {
                $country = strtolower($parameters[0]);
                $countries = Country::getCountries();
                foreach ($countries as $values) {
                    $db_countries =  str_replace(' ', '', strtolower($values['country_name']));
                    if ($db_countries == $country) {
                        $country_code = $values['country_code'];
                        $country_code_check = States::where('country_code', $country_code)->first();
                        if (count($country_code_check) > 0) {
                             $state = str_replace(' ', '', strtolower($value));
                             $state_records = States::all();
                             $states = array_map('strtolower', $state_records[0]['states']);
                            foreach ($states as $values) {
                                $spaces_removed_states = str_replace(' ', '', $values);
                                if ($spaces_removed_states == $state) {
                                    return true;
                                }
                            }
                               return false;
                        } else {
                            return true;
                        }
                    }
                }
            });
              
            Validator::extend('checkusergroupexists', function ($attribute, $value, $parameters) {
                $usergroup = strtolower($value);
                $returnval = UserGroup::where('ug_name_lower', '=', $usergroup)->where('status', '!=', 'DELETED')->get(['ugid'])->toArray();
                if (empty($returnval)) {
                    return false;
                }
                return true;
            });

            Validator::extend('checkactiveusergroupexists', function ($attribute, $value, $parameters) {
                $usergroup = strtolower($value);
                $returnval = UserGroup::where('ug_name_lower', '=', $usergroup)->where('status', '!=', 'DELETED')->get(['ugid'])->toArray();
                if (!empty($returnval)) {
                    $active_usergroup = UserGroup::where('ug_name_lower', '=', $usergroup)->where('status', '=', 'ACTIVE')->get(['ugid'])->toArray();
                    if (empty($active_usergroup)) {
                        return false;
                    }
                    return true;
                }
                return true;
            });

            $message = [
            'checkUserNameRegex' => 'Symbolic characters not allowed',
            'checkemail' => trans('admin/user.check_email'),
            'checkusername' => trans('admin/user.user_error'),
            'checkstate' => trans('admin/user.state_error'),
            'checkcountry' => trans('admin/user.country_errors'),
            'checkusergroupexists' => trans('admin/user.check_usergroup_exists'),
            'checkactiveusergroupexists' => trans('admin/user.check_active_usergroup_exists')
            ];
            return $this->customValidate($csvrowData, $rules, $message);
    }

    /**
     * customValidate
     * @param array $input
     * @param array $rules
     * @param array $messages
     * @return boolean
     */
    public function customValidate($input, $rules, $messages = [])
    {
        $validation = Validator::make($input, $rules, $messages);
        if ($validation->fails()) {
            return $validation->messages();
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function getFtpDirUpdateDetails($ftp_conn_id)
    {
         $ftp_chdir = ftp_chdir($ftp_conn_id['conn_id'], "update");
         $file_list = ftp_nlist($ftp_conn_id['conn_id'], '.');
         $local_file = config('app.user_import_file');
         $ftp_dir_details = [
          'file_list' => $file_list,
          'local_file' => $local_file,
          'ftp_chdir' => $ftp_chdir,
         ];
         return $ftp_dir_details;
    }

    /**
     * @inheritdoc
     */
    public function getUserImportUpdateFileColumns($ftp_connection_details, $ftp_dir_details)
    {
        $fd = fopen($ftp_connection_details['path'] . $ftp_dir_details['local_file'], "w");
        fclose($fd);
        ftp_get($ftp_connection_details['conn_id'], $ftp_connection_details['path'] . $ftp_dir_details['local_file'], $ftp_dir_details['local_file'], FTP_BINARY);
        ftp_delete($ftp_connection_details['conn_id'], $ftp_dir_details['local_file']);
        ftp_close($ftp_connection_details['conn_id']);
        $csvFile = file($ftp_connection_details['path'] . $ftp_dir_details['local_file']);
        $csv = array_map("str_getcsv", $csvFile);
        $headers = array_shift($csv);
        $customfield = array();
        $count = 0;
        $core_fields = array('firstname', 'lastname', 'email', 'mobile', 'username', 'role', 'newusername');
        $custom_fields = CustomFields::getUserCustomFieldArr('user', '', ['fieldlabel', 'fieldlabel']);
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom) {
                $customfield[] = array_get($custom, 'fieldlabel');
            }
        }
        $fields = array_merge($core_fields, $customfield);
      /*---Validate headers starts here---*/
        foreach ($headers as $header) {
            $head[] = str_replace("*", '', $header);
            if (!in_array(str_replace("*", '', $header), $fields)) {
                $count++;
            }
        }
        $csv_file_data = [
        'count' => $count,
        'csvFile' => $csvFile,
        'head' => $head,
        'fields' => $fields,
        ];
        return $csv_file_data;
    }

    /**
     * @inheritdoc
     */
    public function validateUpdateRules($csvrowData)
    {
        $rules['username'] = 'Required|checkusername:' . $csvrowData['username'] . '';
        $message['checkusername'] = trans('admin/user.check_username');
        if (array_key_exists('firstname', $csvrowData)) {
            $rules['firstname'] = 'Required|Min:3|Max:30|Regex:/^[[:alpha:]]+(?:[-_ ]?[[:alpha:]]+)*$/';
        }
        if (array_key_exists('lastname', $csvrowData)) {
            $rules['lastname'] = 'Max:15|Regex:/^([A-Za-z\'. ])+$/';
        }
        if (array_key_exists('mobile', $csvrowData)) {
            $rules['mobile'] = 'numeric|Regex:/^([0-9]{10})/';
        }
        if (array_key_exists('email', $csvrowData)) {
            $rules['email'] = 'Required|email|checkemail:' . $csvrowData['email'] . ',' . $csvrowData['username'] . '';
            $message['checkemail'] = trans('admin/user.check_email');
        }
        if (array_key_exists('newusername', $csvrowData)) {
            $rules['newusername'] = 'Required|Min:3|Max:93|unique:users|checknewusernameregex:' . $csvrowData['newusername'] . '|checknewusername:' . $csvrowData['newusername'] . '';
            $message['checknewusernameregex'] = trans('admin/user.check_new_username_regex');
            $message['checknewusername'] = trans('admin/user.check_new_username');
        }
        if (array_key_exists('role', $csvrowData)) {
            $rules['role'] = 'Required|In:learner,contentauthor,channeladmin';
        }
        $custom_fields = CustomFields::getUserCustomFieldArr('user', '', ['fieldlabel', 'fieldlabel', 'mark_as_mandatory']);

        if (!empty($custom_fields)) {
            foreach ($custom_fields as $key => $values) {
                if ($values['mark_as_mandatory'] == 'yes' && array_key_exists($values['fieldlabel'], $csvrowData)) {
                    $rules[array_get($values, 'fieldlabel')] = 'Required';
                }
            }
        }
        Validator::extend('checkusername', function ($attribute, $value, $parameters) {
            $username = strtolower($parameters[0]);
            $returnval = User::where('username', 'like', $username)->get(['uid'])->toArray();
            if (empty($returnval)) {
                return false;
            }

            return true;
        });
        Validator::extend('checknewusername', function ($attribute, $value, $parameters) {
            $username = strtolower($parameters[0]);
            $returnval = User::where('username', '=', $username)->get(['uid'])->toArray();
            if (empty($returnval)) {
                return true;
            }

            return false;
        });
        Validator::extend('checkemail', function ($attribute, $value, $parameters) {
            $email = $parameters[0];
            $username = strtolower($parameters[1]);
            $returnval = User::where('username', '!=', $username)->where('email', '=', $email)->get(['uid'])->toArray();
            if (empty($returnval)) {
                return true;
            }

            return false;
        });
        Validator::extend('checknewusernameregex', function ($attribute, $value, $parameters) {
            if (isset($value) && !empty($value)) {
                (strpos($value, '@') !== false) ? $pattern = "/^(?!.*?[._]{2})[a-zA-Z0-9]+[a-zA-Z0-9(\._\)?-]+[a-zA-Z0-9]+@[a-zA-Z0-9]+([\.]?[a-z]{2,6}+)*$/" : $pattern = "/^[a-zA-Z0-9._]*$/";
                if (preg_match($pattern, $value)) {
                    return true;
                } else {
                    return false;
                }
            }
        });
        return $this->customValidate($csvrowData, $rules, $message);
    }

    /**
     * @inheritdoc
     */
    public function getUserUsergroupUpdateFtpDetails($ftp_conn_id)
    {
        $ftp_chdir = ftp_chdir($ftp_conn_id['conn_id'], "update");
        $file_list = ftp_nlist($ftp_conn_id['conn_id'], '.');
        $local_file = config('app.user_usergroup_import_file');
        $ftp_dir_details = [
          'file_list' => $file_list,
          'local_file' => $local_file,
          'ftp_chdir' => $ftp_chdir,
        ];
        return $ftp_dir_details;
    }

     /**
     * @inheritdoc
     */
    public function getUserUsergroupUpdateFileColumns($ftp_connection_details, $ftp_dir_details)
    {
        $fd = fopen($ftp_connection_details['path'] . $ftp_dir_details['local_file'], "w");
        fclose($fd);
        ftp_get($ftp_connection_details['conn_id'], $ftp_connection_details['path'] . $ftp_dir_details['local_file'], $ftp_dir_details['local_file'], FTP_BINARY);
        ftp_delete($ftp_connection_details['conn_id'], $ftp_dir_details['local_file']);
        ftp_close($ftp_connection_details['conn_id']);
        $csvFile = file($ftp_connection_details['path'] . $ftp_dir_details['local_file']);
        $csv = array_map("str_getcsv", $csvFile);
        $headers = array_shift($csv);
        $customfield = array();
        $count = 0;
        $fields = array('username', 'usergroup', 'operation');
        /*---Validate headers starts here---*/
        foreach ($headers as $header) {
            $head[] = str_replace("*", '', $header);
            if (!in_array(str_replace("*", '', $header), $fields)) {
                $count++;
            }
        }
        $csv_file_data = [
          'count' => $count,
          'csvFile' => $csvFile,
          'head' => $head,
        ];
        return $csv_file_data;
    }

    /**
     * @inheritdoc
     */
    public function validateUserUsergroupRules($csvrowData)
    {
        $rules = [
            'usergroup' => 'Required|checkusergroup:' . $csvrowData['usergroup'] . '',
            'operation' => 'Required|In:assign,unassign',
        ];
        $message = [
            'checkusername' => trans('admin/user.invalid_user'),
            'checkusergroup' => trans('admin/user.invalid_usergroup'),
            'checkactionassign' => trans('admin/user.user_exist'),
            'checkactiounassign' => trans('admin/user.user_not_exist'),
        ];
        if ($csvrowData['operation'] == 'assign') {
            $rules['username'] = 'Required|checkusername:' . $csvrowData['username'] . '|checkactionassign:' . $csvrowData['username'] . ',' . $csvrowData['usergroup'] . ',' . $csvrowData['operation'] . '';
        } elseif ($csvrowData['operation'] == 'unassign') {
            $rules['username'] = 'Required|checkusername:' . $csvrowData['username'] . '|checkactiounassign:' . $csvrowData['username'] . ',' . $csvrowData['usergroup'] . ',' . $csvrowData['operation'] . '';
        } else {
            $rules['username'] = 'Required|checkusername:' . $csvrowData['username'] . '';
        }
        Validator::extend('checkusername', function ($attribute, $value, $parameters) {
            $username = strtolower($parameters[0]);
            $returnval = User::where('username', 'like', $username)->where('status', '=', 'ACTIVE')->get(['uid'])->toArray();
            if (empty($returnval)) {
                return false;
            }
            return true;
        });
        Validator::extend('checkusergroup', function ($attribute, $value, $parameters) {
            $usergroup = strtolower($parameters[0]);
            $returnval = UserGroup::where('ug_name_lower', '=', $usergroup)->where('status', '=', 'ACTIVE')->get(['ugid'])->toArray();
            if (empty($returnval)) {
                return false;
            }
            return true;
        });
        Validator::extend('checkactionassign', function ($attribute, $value, $parameters) {
            $username = strtolower($parameters[0]);
            $usergroup = strtolower($parameters[1]);
            $action = strtolower($parameters[2]);
            $uid = User::where('username', '=', $username)->where('status', '=', 'ACTIVE')->value('uid');
            $gid = UserGroup::where('ug_name_lower', '=', $usergroup)->where('status', '=', 'ACTIVE')->value('ugid');
            $uids = array();
            $uids = UserGroup::where('ug_name_lower', '=', $usergroup)->where('status', '=', 'ACTIVE')->value('relations');
            if (!empty($uids)) {
                $uids = array_get($uids, 'active_user_usergroup_rel');
            }
            if (!empty($uid) && !empty($gid) && !empty($uids)) {
                if (in_array($uid, $uids)) {
                    return false;
                }
            }
            return true;
        });
        Validator::extend('checkactiounassign', function ($attribute, $value, $parameters) {
            $username = strtolower($parameters[0]);
            $usergroup = strtolower($parameters[1]);
            $action = strtolower($parameters[2]);
            $uid = User::where('username', '=', $username)->where('status', '=', 'ACTIVE')->value('uid');
            $gid = UserGroup::where('ug_name_lower', '=', $usergroup)->where('status', '=', 'ACTIVE')->value('ugid');
            $uids = array();
            $uids = UserGroup::where('ug_name_lower', '=', $usergroup)->where('status', '=', 'ACTIVE')->value('relations');
            if (!empty($uids)) {
                $uids = array_get($uids, 'active_user_usergroup_rel');
            }
            if (!empty($uid) && !empty($gid)&& !empty($uids)) {
                if (!in_array($uid, $uids)) {
                    return false;
                }
            }
            return true;
        });
        return $this->customValidate($csvrowData, $rules, $message);
    }

    /**
     * {@inheritdoc}
    */
    public function getActiveUsergroupUserRel($user_name)
    {
        return User::where('status', '=', 'IN-ACTIVE')
           ->where('username', '=', $user_name)
           ->first();
    }

    /**
     * @inheritdoc
     */
    public function getActiveUserCount($user_name)
    {
        return User::where('username', '=', $user_name)
            ->active()
            ->count();
    }

    /**
     * @inheritdoc
     */
    public function getIdBy($data, $askedVar)
    {
        return User::where("$askedVar", '=', $data["$askedVar"])
                ->active()
                ->value('uid');
    }

    /**
     * @inheritdoc
     */
    public function getAssignedUsergroups($uid)
    {
        $ugids = User::where('uid', '=', (int)$uid)->value('relations');
        $ugids = array_get($ugids, 'active_usergroup_user_rel', 'default');
        return $ugids;
    }

    /**
     * @inheritdoc
     */
    public function updateUserRelation($key, $arrname, $updateArr, $overwrite = false)
    {
        if ($overwrite) {
            User::where('uid', (int)$key)->unset('relations.' . $arrname);
            User::where('uid', (int)$key)->update(['relations.' . $arrname => $updateArr]);
        } else {
            User::where('uid', (int)$key)->push('relations.' . $arrname, $updateArr, true);
        }

        return User::where('uid', (int)$key)->update(['updated_at' => time()]);
    }

    /**
     * @inheritdoc
     */
    public function getAdminUsers()
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
     * @inheritdoc
     */
    public function getActiveUsers($users_to_be_excluded)
    {
        return User::where('super_admin', '!=', true)
                ->whereNotIn('uid', $users_to_be_excluded)
                ->active()->get()->toArray();
    }
    
    /**
     * @param array $field_labels
     * @return collection
     */
    public function getCustomFieldDetails($field_labels = [])
    {
        return CustomFields::where('program_type', '=', 'user')
            ->where('status', '=', 'ACTIVE')
            ->whereIn('fieldlabel', $field_labels)
            ->get();
    }

    /**
     * @inheritdoc
     */
    public function getUserDetailsByUserName($username, $status)
    {
        return User::where('username', 'like', $username)
                    ->where('status', '!=', 'DELETED')
                    ->get(['uid']);
    }

    /**
     * @inheritdoc
     */
    public function getAllDetailsByUsername($username)
    {
        return User::where('username', '=', $username)->first();
    }

    /**
     * @inheritdoc
     */
    public function getDeleteUsers($uid, $username, $email)
    {
        return User::getDelete($uid, $username, $email);
    }

    /**
     * @inheritDoc
     */
    public function getNewUsers(array $date, $start, $limit)
    {
        return User::whereIn('status', ['ACTIVE', 'IN-ACTIVE'])
                ->whereBetween('created_at', $date)
                ->skip((int)$start)
                ->take((int)$limit)
                ->orderBy('created_at', 'desc')
                ->get(['uid', 'username', 'firstname', 'lastname', 'email']);
    }

    /**
     * @inheritdoc
     */
    public function countActiveUsers()
    {
        return User::where('status', 'ACTIVE')->count();
    }

    /**
     * @inheritdoc
     */
    public function countInActiveUsers()
    {
        return User::where('status', 'IN-ACTIVE')->count();
    }

    /**
     * @inheritdoc
     */
    public function getAllUsersData($columns, $start, $limit)
    {
        return User::where('status', '!=', 'DELETED')
                    ->where('super_admin', '!=', true)
                    ->skip((int)$start)
                    ->take((int)$limit)
                    ->get($columns);
    }

    /**
     * @inheritdoc
     */
    public function getUserDetailsByUserIds(
        $start = 0, 
        $limit = 0,
        $column = [],
        $searchKey = null, 
        $user_ids = [], 
        $users_to_be_excluded = [],
        $orderby = ['created_at' => 'desc']
    ) {
        $key = key($orderby);
        $value = $orderby[$key];
        $query = User::where('status', '!=', 'DELETED')
                    ->where('super_admin', '!=', true)
                    ->whereIn('uid', $user_ids)
                    ->whereNotIn('uid', $users_to_be_excluded);
        if (!empty($searchKey)) {
            $query = $query->orWhere('username', 'like', '%' . $searchKey . '%')
                ->orWhere('firstname', 'like', '%' . $searchKey . '%')
                ->orWhere('lastname', 'like', '%' . $searchKey . '%')
                ->orWhere('email', 'like', '%' . $searchKey . '%');
        }

        if ($limit > 0) {
            $query->skip((int)$start)
            ->take((int)$limit);
        }
        return $query->orderBy($key, $value)->get($column); 

    }

    /**
     * @inheritdoc
     */
    public function getUsersByUserIds(
        $start = 0, 
        $limit = 0,
        $column = [],
        $user_ids = [], 
        $orderby = ['created_at' => 'desc'],
        $serach = ""
    ) {
        $key = key($orderby);
        $value = $orderby[$key];

        $query = User::where('status', '!=', 'DELETED')
                    ->whereIn('uid', $user_ids);
        if(!empty($serach)){
            $query = $query->orWhere('username', 'like', '%' . $serach . '%')
                ->orWhere('firstname', 'like', '%' . $serach . '%')
                ->orWhere('lastname', 'like', '%' . $serach . '%')
                ->orWhere('email', 'like', '%' . $serach . '%');
        }

        if ($limit > 0) {
            $query->skip((int)$start)
            ->take((int)$limit);
        }
        return $query->orderBy($key, $value)->get($column); 
    }

    /**
     * @inheritdoc
     */
    public function getFilteredUserDetails(
        $start = 0,
        $limit = 0,
        $column = [],
        $searchKey = null, 
        $users_to_be_excluded = [],
        $orderby = ['created_at' => 'desc']
    ) {
        $key = key($orderby);
        $value = $orderby[$key];
        $query = User::where('status', '!=', 'DELETED')
                    ->where('super_admin', '!=', true)
                    ->whereNotIn('uid', $users_to_be_excluded);
        if (!empty($searchKey)) {
            $query = $query->orWhere('username', 'like', '%' . $searchKey . '%')
                ->orWhere('firstname', 'like', '%' . $searchKey . '%')
                ->orWhere('lastname', 'like', '%' . $searchKey . '%')
                ->orWhere('email', 'like', '%' . $searchKey . '%');
        }
        return $query->skip((int)$start)->take((int)$limit)->orderBy($key, $value)->get($column); 
    }

    /**
     * @inheritdoc
     */
    public function getCertifiedUsersCount($user_id, $users_to_be_excluded)
    {
        return User::where('status', '!=', 'DELETED')
                    ->where('super_admin', '!=', true)
                    ->whereIn('uid', $user_id)
                    ->whereNotIn('uid', $users_to_be_excluded)
                    ->count();
    }

    /**
     * @inheritdoc
     */
    public function getUsersDetailsUsingUserIDs($user_ids = [], $status, $orderby = ['created_at' => 'desc'], $columns = [])
    {
        $key = key($orderby);
        $value = $orderby[$key];
        return User::whereIn('uid', $user_ids)->where('status', '!=', $status)->orderBy($key, $value)->get($columns);

    }

    /**
     * @inheritdoc
     */
    public function addUserRelation($user_id, $fieldarr = [], $package_id)
    {
        return User::addUserRelation($user_id, $fieldarr, $package_id);
    }

    /**
     * @inheritdoc
     */
    public function removeUserRelation($user_id, $fieldarr = [], $package_id)
    {
        return User::removeUserRelation($user_id, $fieldarr, $package_id);
    }

    /**
     * @inheritdoc
     */
    public function getIdByEmail($email)
    {
        return User::getIdByEmail($email);
    }

    public function removeUserSurvey($user_id, $fieldarr, $sid)
    {
        return User::removeUserSurvey($user_id, $fieldarr, $sid);
    }

    public function addUserSurvey($user_id, $fieldarr, $sid)
    {
        return User::addUserSurvey($user_id, $fieldarr, $sid);
    }

    public function removeUserAssignment($user_id, $fieldarr, $aid)
    {
        return User::removeUserAssignment($user_id, $fieldarr, $aid);
    }

    public function addUserAssignment($user_id, $fieldarr, $aid)
    {
        return User::addUserAssignment($user_id, $fieldarr, $aid);
    }

    public function getUserColumnsbyId($user_ids, $columns)
    {
        return User::getUserColumnsbyId($user_ids, $columns);

    }

    /**
     * @inheritdoc
     */
    public function getSuperAdminIds()
    {
        return User::where('status', '!=', 'DELETED')
            ->where('status', '=', 'ACTIVE')
            ->where(function ($q) {
                $q->where('super_admin', '=', 1)
                    ->orWhere('super_admin', '=', true);
            })
            ->get(['uid']);
    }
}
