<?php

namespace App\Model\Program;

use App\Enums\Post\PostStatus;
use App\Enums\Program\ProgramStatus;
use App\Enums\Program\QuestionStatus;
use App\Exceptions\Post\PostNotFoundException;
use App\Exceptions\Post\QuestionNotFoundException;
use App\Enums\Cron\CronBulkImport;
use App\Exceptions\Program\NoProgramAssignedException;
use App\Exceptions\Program\ProgramNotFoundException;
use App\Exceptions\User\RelationNotFoundException;
use App\Model\ChannelFaq;
use App\Model\OverAllChannelAnalytic;
use App\Model\PacketFaq;
use App\Model\Program;
use App\Model\User;
use App\Model\UserGroup;
use App\Model\Packet;
use App\Model\Transaction;
use App\Model\TransactionDetail;
use App\Model\CustomFields\Entity\CustomFields;
use App\Model\ImportLog\Entity\ProgramLog;
use App\Model\ImportLog\Entity\EnrolLog;
use App\Model\ImportLog\Entity\UserLog;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Session;
use Timezone;
use Validator;

/**
 * Class ProgramRepository
 *
 * @package App\Model\Program
 */
class ProgramRepository implements IProgramRepository
{
    /**
     * @inheritDoc
     */
    public function find($id)
    {
        try {
            return Program::where("program_id", (int) $id)
                ->where("status", "!=", ProgramStatus::DELETED)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new ProgramNotFoundException();
        }
    }

    /**
     * @inheritDoc
     */
    public function findByAttribute($type, $attribute, $value)
    {
        try {
            return Program::where("program_type", $type)
                ->where($attribute, $value)
                ->where("status", "!=", ProgramStatus::DELETED)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new ProgramNotFoundException();
        }
    }

    /**
     * @inheritDoc
     */
    public function findQuestion($program_id, $question_id)
    {
        try {
            return ChannelFaq::where("program_id", (int) $program_id)
                ->where("id", (int) $question_id)
                ->where("status", "!=", QuestionStatus::DELETED)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new \App\Exceptions\Program\QuestionNotFoundException();
        }
    }

    /**
     * @inheritDoc
     */
    public function getQuestionCount($filter_params = [])
    {
        return ChannelFaq::filter($filter_params)->count();
    }

    /**
     * @inheritDoc
     */
    public function getQuestions($filter_params = [])
    {
        return ChannelFaq::filter($filter_params)->get();
    }

    /**
     * @inheritDoc
     */
    public function findProgramPost($program_slug, $post_id)
    {
        try {
            return Packet::where("feed_slug", $program_slug)
                ->where("packet_id", $post_id)
                ->where("status", "!=", PostStatus::DELETED)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new PostNotFoundException();
        }
    }

    /**
     * @inheritDoc
     */
    public function findProgramPostByAttribute($program_slug, $attribute, $value)
    {
        try {
            return Packet::where("feed_slug", $program_slug)
                ->where($attribute, $value)
                ->where("status", "!=", PostStatus::DELETED)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new PostNotFoundException();
        }
    }

    /**
     * @inheritDoc
     */
    public function getPostQuestionsCount($post_id, $filter_params = [])
    {
        return PacketFaq::filter(array_merge($filter_params, ["packet_id" => $post_id]))->count();
    }

    /**
     * @inheritDoc
     */
    public function getPostQuestions($post_id, $filter_params = [])
    {
        return PacketFaq::filter(array_merge($filter_params, ["packet_id" => $post_id]))->get();
    }

    /**
     * @inheritDoc
     */
    public function findPostQuestion($post_id, $question_id)
    {
        try {
            return PacketFaq::where("packet_id", (int) $post_id)
                ->where("id", (int) $question_id)
                ->where("status", "!=", QuestionStatus::DELETED)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new QuestionNotFoundException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllProgramsAssignedToUser()
    {
        $channels = [];
        $uids = [];
        if (isset(Auth::user()->relations)) {
            $channels = Auth::user()->relations;
        } else {
            throw new RelationNotFoundException();
        }

        if (isset($channels['active_usergroup_user_rel']) &&
            is_array($channels['active_usergroup_user_rel'])
        ) {
            foreach ($channels['active_usergroup_user_rel'] as $ugids) {
                $ugids_details = UserGroup::getActiveUserGroupsUsingID($ugids);
                if (isset($ugids_details[0]['relations']['usergroup_feed_rel'])) {
                    foreach ($ugids_details[0]['relations']['usergroup_feed_rel'] as $key => $value) {
                        $uids[] = $value;
                    }
                }
                if (isset($ugids_details[0]['relations']['usergroup_package_feed_rel']) && !empty($ugids_details[0]['relations']['usergroup_package_feed_rel'])) {
                    foreach ($ugids_details[0]['relations']['usergroup_package_feed_rel'] as $key => $value) {
                        $uids[] = $value;
                    }
                }
                if (isset($ugids_details[0]['relations']['usergroup_course_rel']) && !empty($ugids_details[0]['relations']['usergroup_course_rel'])) {
                    foreach ($ugids_details[0]['relations']['usergroup_course_rel'] as $key => $value) {
                        $uids[] = $value;
                    }
                }
            }
        }

        if (isset($channels['active_usergroup_user_rel']) &&
            is_array($channels['active_usergroup_user_rel']) &&
            isset($channels['user_feed_rel']) && is_array($channels['user_feed_rel'])
        ) {
            $channels_id = array_unique(array_merge($uids, $channels['user_feed_rel']));
            if (isset($channels['user_package_feed_rel'])) {
                $channels_id = array_unique(array_merge($channels_id, $channels['user_package_feed_rel']));
            }
        } elseif (isset($channels['active_usergroup_user_rel']) && is_array($channels['active_usergroup_user_rel'])) {
            $channels_id = $uids;
        } elseif (isset($channels['user_feed_rel']) && is_array($channels['user_feed_rel'])) {
            $channels_id = $channels['user_feed_rel'];
            if (isset($channels['user_package_feed_rel'])) {
                $channels_id = array_unique(array_merge($channels_id, $channels['user_package_feed_rel']));
            }
        }
        if (isset($channels['user_course_rel']) && !empty($channels['user_course_rel'])) {
            $channels_id = array_merge($channels_id, $channels['user_course_rel']);
        }
        if (isset($channels['user_package_feed_rel']) && !empty($channels['user_package_feed_rel'])) {
            $channels_id = array_merge($channels_id, $channels['user_package_feed_rel']);
        }
        if (empty($channels_id)) {
            throw new NoProgramAssignedException();
        }
        return array_unique($channels_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveProgramsData($program_ids, $start, $limit, $columns = [], $is_admin = false)
    {
        if ($is_admin) {
            $programs = Program::where('status', 'ACTIVE')
                ->whereIn('program_type', ["content_feed", "course"])
                ->where('program_sub_type', 'single')
                ->where(function ($query) {
                    $query->orWhere('parent_id', 'exists', false)
                        ->orWhere('parent_id', '>', 0);
                })
                ->where('program_display_enddate', '>=', Carbon::now()->timestamp)
                ->orderBy('program_display_enddate', 'ASC')
                ->skip((int)$start)
                ->take((int)$limit)
                ->get($columns);
        } else {
            $programs = Program::whereIn('program_id', $program_ids)
                    ->where('status', 'ACTIVE')
                    ->where('program_display_enddate', '>=', Carbon::now()->timestamp)
                    ->orderBy('program_display_enddate', 'ASC')
                    ->skip((int)$start)
                    ->take((int)$limit)
                    ->get($columns);
        }
        return $programs;
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramsData($column, $data, $columns = [])
    {
        $programs = Program::whereIn($column, $data)
            ->where('status', 'ACTIVE')
            ->where('program_display_enddate', '>=', Carbon::today()->timestamp)
            ->orderBy('program_display_enddate', 'ASC')
            ->get($columns);

        return $programs;
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramsAnalytics($program_ids)
    {
        $analytics = OverAllChannelAnalytic::getChannelAnalytics(
            array_filter($program_ids),
            (int)Auth::user()->uid
        );
        return $analytics;
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramsAnalyticById($program_id)
    {
        $analytics = OverAllChannelAnalytic::where('channel_id', (int)$program_id)
            ->where('user_id', (int)Auth::user()->uid)->get();
        return $analytics;
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramsFaq($program_ids)
    {
        $faqs = [];
        foreach ($program_ids as $id) {
            $faqs[$id] = ChannelFaq::where('program_id', (int)$id)
                ->where('access', 'public')
                ->where('status', '!=', 'DELETED')
                ->count();
        }
        return $faqs;
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramsByAttribute($column, $data, $columns = [])
    {
        $programs = Program::whereIn($column, $data)
            ->get($columns);
        return $programs;
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramDataByAttribute($column, $data, $columns = [])
    {
        $programs = Program::where($column, $data)
            ->get($columns);
        return $programs;
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramsOrderByName($program_ids, $start, $limit, $columns = [], $is_admin = false)
    {
        if ($is_admin) {
            $programs = Program::whereIn('program_type', ["content_feed", "course"])
                    ->where('status', 'ACTIVE')
                    ->where('program_sub_type', 'single')
                    ->where(function ($query) {
                        $query->orWhere('parent_id', 'exists', false)
                            ->orWhere('parent_id', '>', 0);
                    })
                    ->where('program_display_enddate', '>=', Carbon::now()->timestamp)
                    ->orderBy('title_lower', 'ASC')
                    ->skip((int)$start)
                    ->take((int)$limit)
                    ->get($columns);
        } else {
            $programs = Program::whereIn('program_id', $program_ids)
                    ->where('status', 'ACTIVE')
                    ->where('program_display_enddate', '>=', Carbon::now()->timestamp)
                    ->orderBy('title_lower', 'ASC')
                    ->skip((int)$start)
                    ->take((int)$limit)
                    ->get($columns);
        }
        return $programs;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveProgramsSlug()
    {
        $channel_ids = [];
        $channel_ids = $this->getAllProgramsAssignedToUser();
        if (!empty($channel_ids)) {
            $programs = Program::whereIn('program_id', $channel_ids)
                ->where('status', 'ACTIVE')
                ->where('program_display_enddate', '>=', Carbon::today()->timestamp)
                ->get(['program_slug']);
            return $programs;
        } else {
            throw new NoProgramAssignedException;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignedPrograms($start, $limit, $columns = [])
    {
        $channel_ids = [];
        $channel_ids = $this->getAllProgramsAssignedToUser();
        if (!empty($channel_ids)) {
            $programs = Program::whereIn('program_id', $channel_ids)
                ->where('status', 'ACTIVE')
                ->get($columns);
        } else {
            throw new NoProgramAssignedException;
        }
    }

    /**
     * [getCourseByBatch list parent course]
     * @method getCourseByBatch
     * @param  [type]           $programs [list of programs]
     * @return [type]                     [list parent course]
     */
    private function getCourseByBatch($programs)
    {
        $parent_ids = [];
        $parent_programs = [];

        array_where($programs, function ($key, $value) use (&$parent_ids) {
            if ($value['program_type'] == 'course' &&
                isset($value['parent_id']) &&
                !empty($value['parent_id'])
            ) {
                $parent_ids[] = $value['parent_id'];
            }
        });

        if (!empty($parent_ids)) {
            $parent_programs = Program::whereIn('program_id', $parent_ids)
                ->get()
                ->toArray();
        }

        return $parent_programs;
    }

    /**
     * {@inheritdoc}
     */
    public function getCourseTitle($program_id, $program_title)
    {

        $parent_course = Session::get('parent_course');
        $course_title = '';
        array_where($parent_course, function ($key, $value) use ($program_id, &$course_title) {
            if ($program_id == $value['program_id']) {
                $course_title = $value['program_title'];
            }
        });
        if (!empty($course_title)) {
            return $course_title . " - " . $program_title;
        }

        return $program_title;
    }

    /**
     * @param string $name
     * @param string $shortname
     * @return slug
     */
    private function getSlugName($name, $shortname)
    {
        $name = preg_replace('/[^\w ]+/', '', $name);
        $name = strtolower(preg_replace('/ +/', '-', $name));
        if (!empty($shortname)) {
            $shortname = preg_replace('/[^\w ]+/', '', $shortname);
            $shortname = strtolower(preg_replace('/ +/', '-', $shortname));
            $slug = "content-feed" . '-' . $name . '-' . $shortname;
        } else {
            $slug = "content-feed" . '-' . $name;
        }
        return $slug;
    }

    /**
     * @param int $id
     * @param array $input
     * @param string $type
     * @param int $cron
     * @return nothing inserts records
     */
    public function getPrepareProgramData($id, $input, $type, $cron)
    {
        $default_timezone = ($cron == 0) ? Auth::user()->timezone : config('app.default_timezone');
        $username = ($cron == 0) ? Auth::user()->username : CronBulkImport::CRONUSER;
        $slug = $this->getSlugName($input['name'], $input['shortname']);
        $feeddata['program_id'] = $id;
        $feeddata['program_title'] = trim($input['name']);
        $feeddata['title_lower'] = trim(strtolower($input['name']));
        $feeddata['program_shortname'] = $input['shortname'];
        $feeddata['program_slug'] = $slug;
        $feeddata['program_description'] = '';
        $feeddata['program_startdate'] = (int)Timezone::convertToUTC($input['startdate'], $default_timezone, 'U');
        $feeddata['program_enddate'] = (int)Timezone::convertToUTC($input['enddate'], $default_timezone, 'U');
        $feeddata['program_display_startdate'] = (int)Timezone::convertToUTC($input['displaystartdate'], $default_timezone, 'U');
        $feeddata['program_display_enddate'] = (int)Timezone::convertToUTC($input['displayenddate'], $default_timezone, 'U');
        $feeddata['program_duration'] = '';
        $feeddata['program_review'] = 'no';
        $feeddata['program_rating'] = 'no';
        $feeddata['program_visibility'] = 'yes';
        if (config('app.ecommerce')) {
            $feeddata['program_sellability'] = $input['sellable'];
        } else {
            $feeddata['program_sellability'] = 'yes';
        }
        $feeddata['program_keywords'] = array($input['keywords']);
        $feeddata['program_cover_media'] = '';
        $feeddata['program_type'] = 'content_feed';
        $feeddata['program_sub_type'] = $type;
        $feeddata['duration'] = [array('label' => 'Forever', 'days' => 'forever')];
        $feeddata['benchmarks'] = array('speed' => 0, 'score' => 0, 'accuracy' => 0);
        $feeddata['program_categories'] = array();
        $feeddata['last_activity'] = time();
        $feeddata['status'] = 'ACTIVE';
        $feeddata['created_by'] = $username;
        $feeddata['created_by_name'] = $username;
        $feeddata['created_at'] = time();
        $feeddata['updated_at'] = time();
        if (array_get($feeddata, 'program_sellability') == "yes") {
            $feeddata['program_access'] = "restricted_access";
        } else {
            $feeddata['program_access'] = "general_access";
        }
        //Unsetting core fields from array
        unset(
            $input['name'],
            $input['shortname'],
            $input['description'],
            $input['startdate'],
            $input['enddate'],
            $input['displaystartdate'],
            $input['displayenddate'],
            $input['sellable'],
            $input['keywords']
        );
        $packageslug = '';
        if ($type == 'single' && !(empty($input['packagename']))) {
            $packageslug = $this->getSlugName($input['packagename'], $input['packageshortname']);
        }

        if ($type == 'single') {
            unset($input['packagename'], $input['packageshortname']);
        }

        $record = array_merge($feeddata, $input);
        //Inserting record in profram collection
        Program::insert($record);
        if ($type == 'single' && !(empty($packageslug))) {
            $package_id = Program::where('program_slug', '=', $packageslug)->where('status', '!=', 'DELETED')->value('program_id');
            //Update relation in program collection to maintain packageid
            Program::where('program_id', (int)$id)->push('parent_relations.active_parent_rel', (int)$package_id, true);
            //Update relation in program collection to maintain channel id
            Program::where('program_id', (int)$package_id)->push('child_relations.active_channel_rel', (int)$id, true);
        }
    }

    /**
     * @param string $name
     * @param string $shortname
     * @param string $type
     * @param string $subtype
     * @return int program_id
     */
    public function getProgramId($name, $shortname, $type, $subtype)
    {
        $slug = $this->getSlugName($name, $shortname);
        return Program::where('program_slug', $slug)->where('program_type', '=', $type)->where('program_sub_type', '=', $subtype)->value('program_id');
    }

    /**
     * @return int programid
     * @param int $userid
     * @param string $name
     * @param string $shortname
     * @param string $type
     * @param string $subtype
     * @param string $level
     * @param int $cron
     * @return nothing enrols user to program
     */
    public function enrolUserToProgram($programid, $userid, $name, $shortname, $type, $subtype, $level, $cron)
    {
        $slug = $this->getSlugName($name, $shortname);
        $transaction = $this->getTransactionData($level, $userid, $cron);
        $transaction_details = $this->getTransactionDetailsData($level, $userid, $transaction['trans_id'], $programid, $slug, $type, $name, $subtype, $packid = 0);
        Transaction::insert($transaction);
        TransactionDetail::insert($transaction_details);
        User::addUserRelation($userid, ['user_feed_rel'], $programid);
        Program::addFeedRelation($programid, ['active_user_feed_rel'], $userid);
    }

    /**
     * @param string $level
     * @param int $uid
     * @return transaction data
     */
    private function getCronTransactionData($level, $uid)
    {
        $now = time();
        $trans_id = Transaction::uniqueTransactionId();
        $record = [
            'DAYOW' => Timezone::convertToUTC('@' . $now, config('app.default_timezone'), 'l'),
            'DOM' => (int)Timezone::convertToUTC('@' . $now, config('app.default_timezone'), 'j'),
            'DOW' => (int)Timezone::convertToUTC('@' . $now, config('app.default_timezone'), 'w'),
            'DOY' => (int)Timezone::convertToUTC('@' . $now, config('app.default_timezone'), 'z'),
            'MOY' => (int)Timezone::convertToUTC('@' . $now, config('app.default_timezone'), 'n'),
            'WOY' => (int)Timezone::convertToUTC('@' . $now, config('app.default_timezone'), 'W'),
            'YEAR' => (int)Timezone::convertToUTC('@' . $now, config('app.default_timezone'), 'Y'),
            'trans_level' => $level,
            'id' => $uid,
            'created_date' => time(),
            'trans_id' => (int)$trans_id,
            'full_trans_id' => 'ORD' . sprintf('%07d', (string)$trans_id),
            'access_mode' => 'assigned_by_admin',
            'added_by' => CronBulkImport::CRONUSER,
            'added_by_name' => CronBulkImport::CRONUSER,
            'created_at' => time(),
            'updated_at' => time(),
            'type' => 'subscription',
            'status' => 'COMPLETE',
        ];
        if ($level == 'user') {
            $user = User::getUserDetailsByID($uid);
            $data = ['email' => $user['email']];
            $record = array_merge($record, $data);
        }
        return $record;
    }

    /**
     * @param string $level
     * @param int $uid
     * @param int $cron
     * @return transaction data
     */
    private function getTransactionData($level, $uid, $cron)
    {
        $default_timezone = ($cron == 0) ? Auth::user()->timezone : config('app.default_timezone');
        $username = ($cron == 0) ? Auth::user()->username : CronBulkImport::CRONUSER;
        $now = time();
        $trans_id = Transaction::uniqueTransactionId();
        $record = [
            'DAYOW' => Timezone::convertToUTC('@' . $now, $default_timezone, 'l'),
            'DOM' => (int)Timezone::convertToUTC('@' . $now, $default_timezone, 'j'),
            'DOW' => (int)Timezone::convertToUTC('@' . $now, $default_timezone, 'w'),
            'DOY' => (int)Timezone::convertToUTC('@' . $now, $default_timezone, 'z'),
            'MOY' => (int)Timezone::convertToUTC('@' . $now, $default_timezone, 'n'),
            'WOY' => (int)Timezone::convertToUTC('@' . $now, $default_timezone, 'W'),
            'YEAR' => (int)Timezone::convertToUTC('@' . $now, $default_timezone, 'Y'),
            'trans_level' => $level,
            'id' => $uid,
            'created_date' => time(),
            'trans_id' => (int)$trans_id,
            'full_trans_id' => 'ORD' . sprintf('%07d', (string)$trans_id),
            'access_mode' => 'assigned_by_admin',
            'added_by' => $username,
            'added_by_name' => $username,
            'created_at' => time(),
            'updated_at' => time(),
            'type' => 'subscription',
            'status' => 'COMPLETE',
        ];
        if ($level == 'user') {
            $user = User::getUserDetailsByID($uid);
            $data = ['email' => $user['email']];
            $record = array_merge($record, $data);
        }
        return $record;
    }

    /**
     * @param string $level
     * @param int $uid
     * @param int $trans_id
     * @param int $programid
     * @param string $slug
     * @param string $type
     * @param string $title
     * @return transaction_details data
     */
    private function getTransactionDetailsData($level, $uid, $trans_id, $programid, $slug, $type, $title, $subtype, $packid)
    {
        $record = [
            'trans_level' => $level,
            'id' => $uid,
            'trans_id' => (int)$trans_id,
            'program_id' => $programid,
            'program_slug' => $slug,
            'type' => $type,
            'program_title' => $title,
            'duration' => [
                'label' => 'Forever',
                'days' => 'forever',
            ],
            'start_date' => '',
            'end_date' => '',
            'created_at' => time(),
            'updated_at' => time(),
            'status' => 'COMPLETE',
        ];
        if ($subtype == 'collection') {
            $data = ['package_id' => $packid, 'program_sub_type' => $subtype];
            $record = array_merge($record, $data);
        }

        return $record;
    }

    /**
     * @return int programid
     * @param int $groupid
     * @param string $name
     * @param string $shortname
     * @param string $type
     * @param string $subtype
     * @param string $level
     * @param int $cron
     * @return nothing enrols usergroup to program
     */
    public function enrolUsergroupToProgram($programid, $groupid, $name, $shortname, $type, $subtype, $level, $cron)
    {
        $slug = $this->getSlugName($name, $shortname);
        $transaction = $this->getTransactionData($level, $groupid, $cron);
        $transaction_details = $this->getTransactionDetailsData($level, $groupid, $transaction['trans_id'], $programid, $slug, $type, $name, $subtype, $programid);
        Transaction::insert($transaction);
        TransactionDetail::insert($transaction_details);
        UserGroup::addUserGroupRelation($groupid, ['usergroup_parent_feed_rel'], $programid);
        Program::addFeedRelation($programid, ['active_usergroup_feed_rel'], $groupid);
        $program = Program::getProgram($slug);
        if (isset($program[0]['child_relations']['active_channel_rel']) && !empty($program[0]['child_relations']['active_channel_rel'])) {
            foreach ($program[0]['child_relations']['active_channel_rel'] as $each) {
                UserGroup::addUserGroupRelation($groupid, ['usergroup_child_feed_rel'], $each);
                $sub_transaction = $this->getTransactionData($level, $groupid, $cron);
                $sub_program = Program::getProgramDetailsByID($each);
                $sub_transaction_details = $this->getTransactionDetailsData(
                    $level,
                    $groupid,
                    $sub_transaction['trans_id'],
                    $each,
                    $sub_program['program_slug'],
                    $type,
                    $sub_program['program_title'],
                    $subtype,
                    $programid
                );

                Transaction::insert($sub_transaction);
                TransactionDetail::insert($sub_transaction_details);
            }
        }
    }

    /**
     * @param string $program_type
     * @param string $program_sub_type
     * @param string $status
     * @param string $date
     * @return nothing exports programs to csv file
     */
    public function exportPrograms($program_type, $program_sub_type, $status = 'ALL', $date = null, $action = 'ALL')
    {

        $reports = ProgramLog::getPackageExportRecords($status, $date, $program_sub_type, $action);
        $custom_fields = CustomFields::getUserCustomFieldArr('content_feed', $program_sub_type, ['fieldname', 'fieldname']);
        if (!empty($reports)) {
            $data = [];
            $data[] = ($program_sub_type == 'single') ? [trans('admin/program.channel') . 'Report'] : [trans('admin/program.package') . 'Report'];
            $header[] = 'name';
            $header[] = 'shortname';
            $header[] = 'description';
            $header[] = 'startdate';
            $header[] = 'enddate';
            $header[] = 'displaystartdate';
            $header[] = 'displayenddate';
            $header[] = 'sellable';
            $header[] = 'keywords';
            /*custom fields starts here*/
            if (!empty($custom_fields)) {
                foreach ($custom_fields as $custom) {
                    $header[] = $custom['fieldname'];
                }
            }
            /*custom fields ends here*/
            $header[] = 'createdate';
            $header[] = 'status';
            $header[] = 'errormessage';
            $header[] = trans('admin/program.action');
            $data[] = $header;
            foreach ($reports as $report) {
                $tempRow = [];
                if ($report['status'] == 'SUCCESS') {
                    $program = Program::where('program_id', '=', (int)$report['program_id'])->get()->first();
                    $tempRow[] = $program['program_title'];
                    $tempRow[] = $program['program_shortname'];
                    $tempRow[] = $program['program_description'];
                    $tempRow[] = Timezone::convertFromUTC('@' . $program['program_startdate'], Auth::user()->timezone, config('app.date_format'));
                    $tempRow[] = Timezone::convertFromUTC('@' . $program['program_enddate'], Auth::user()->timezone, config('app.date_format'));
                    $tempRow[] = Timezone::convertFromUTC('@' . $program['program_display_startdate'], Auth::user()->timezone, config('app.date_format'));
                    $tempRow[] = Timezone::convertFromUTC('@' . $program['program_display_enddate'], Auth::user()->timezone, config('app.date_format'));
                    $tempRow[] = $program['program_sellability'];
                    $error = '';
                    $tempRow[] = implode(',', $program['program_keywords']);
                    /*custom fields starts here*/
                    if (!empty($custom_fields)) {
                        foreach ($custom_fields as $custom) {
                            if (isset($program[$custom['fieldname']])) {
                                $tempRow[] = $program[$custom['fieldname']];
                            } else {
                                $tempRow[] = '';
                            }
                        }
                    }
                    /*custom fields ends here*/
                } else {
                    $tempRow[] = $report['name'];
                    $tempRow[] = $report['shortname'];
                    $tempRow[] = $report['description'];
                    $tempRow[] = $report['startdate'];
                    $tempRow[] = $report['enddate'];
                    $tempRow[] = $report['displaystartdate'];
                    $tempRow[] = $report['displayenddate'];
                    $tempRow[] = $report['sellable'];
                    $error = $report['error_msgs'];
                    $tempRow[] = $report['keywords'];
                    /*custom fields starts here*/
                    if (!empty($custom_fields)) {
                        foreach ($custom_fields as $custom) {
                            if (isset($report[$custom['fieldname']])) {
                                $tempRow[] = $report[$custom['fieldname']];
                            } else {
                                $tempRow[] = '';
                            }
                        }
                    }
                    /*custom fields ends here*/
                }

                $tempRow[] = Timezone::convertFromUTC($report['created_at'], Auth::user()->timezone, config('app.date_format'));
                $tempRow[] = $report['status'];
                $tempRow[] = $error;
                $tempRow[] = $report['action'];
                $data[] = $tempRow;
            }
            if (!empty($data)) {
                $filename = ($program_sub_type == 'single') ? trans('admin/program.channel') . "Report.csv" : trans('admin/program.package') . "Report.csv";
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
     * @param string $enrol_level
     * @param string $program_sub_type
     * @param string $status
     * @param string $date
     * @return nothing exports user-channel to csv file
     */
    public function userChannelExport($enrol_level, $program_sub_type, $status = 'ALL', $date = null)
    {
        $reports = EnrolLog:: getPackageUsergroupExportRecords($status, $date, $program_sub_type, $enrol_level);

        if (!empty($reports)) {
            $data = [];
            $data[] = [trans('admin/program.channel_user') . 'Report'];
            $header[] = 'username';
            $header[] = 'name';
            $header[] = 'shortname';
            $header[] = 'createdate';
            $header[] = 'status';
            $header[] = 'errormessage';
            $header[] = trans('admin/program.action');
            $data[] = $header;
            foreach ($reports as $report) {
                $tempRow = [];
                if ($report['status'] == 'SUCCESS') {
                    $program = Program::where('program_id', '=', (int)$report['program_id'])->get()->first();
                    $user = User::where('uid', '=', (int)$report['uid'])->first();
                    $tempRow[] = $user['username'];
                    $tempRow[] = $program['program_title'];
                    $tempRow[] = $program['program_shortname'];
                    $error = '';
                } else {
                    $tempRow[] = $report['username'];
                    $tempRow[] = $report['name'];
                    $tempRow[] = $report['shortname'];
                    $error = $report['error_msgs'];
                }

                $tempRow[] = Timezone::convertFromUTC($report['created_at'], Auth::user()->timezone, config('app.date_format'));
                $tempRow[] = $report['status'];
                $tempRow[] = $error;
                $tempRow[] = $report['action'];
                $data[] = $tempRow;
            }
            if (!empty($data)) {
                $filename = trans('admin/program.channel_user') . "Report.csv";
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
     * @inheritDoc
     */
    public function getCount($filter_params = [])
    {
        return Program::filter($filter_params)->count();
    }

    /**
     * {@inheritdoc}
     */
    public function get($filter_params = [], $columns = ["*"])
    {
        return Program::filter($filter_params)->select($columns)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getPackets($program_slugs = [])
    {
        return Packet::filter(["program_slugs" => $program_slugs])->get();
    }

    /**
     * {@inheritdoc}
     */

    public function getCoursesBySearchKey($search_key, $course_id = 0, $start = 0, $limit = 500)
    {
        $query = Program::where('status', '!=', 'DELETED')
            ->where('program_type', '=', 'course')
            ->where('parent_id', '=', $course_id);
        if ($search_key != '') {
            $query->where('program_title', 'like', "%" . $search_key . "%");
        }
        return $query->skip((int)$start)->take((int)$limit)
            ->orderBy('program_title', 'asc')
            ->orderBy('program_shortname', 'asc')
            ->get();
    }


    /**
     * {@inheritdoc}
     */
    public function getProgramsBySearch($search = '', $is_ug_rel = false, $start = 0, $limit = 500)
    {
        $query = Program::where('status', '!=', 'DELETED')
            ->where('program_type', '=', 'content_feed')
            ->where('program_sub_type', '=', 'single');
        if ($is_ug_rel) {
            $query->where('relations.active_usergroup_feed_rel.0', 'exists', true);
        }
        if ($search != '') {
             $query->where('program_title', 'like', "%" . $search . "%");
        }
        return $query->orderBy('program_title', 'asc')
            ->orderBy('program_shortname', 'asc')
            ->skip((int)$start)
            ->take((int)$limit)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getPackagesBySearch($search = '', $is_ug_rel = false, $start = 0, $limit = 500)
    {
        $query = Program::where('status', '!=', 'DELETED')
            ->where('program_type', '=', 'content_feed')
            ->where('program_sub_type', '=', 'collection');
        if ($is_ug_rel) {
            $query->where('relations.active_usergroup_feed_rel.0', 'exists', true);
        }
        if ($search != '') {
             $query->where('program_title', 'like', "%" . $search . "%");
        }
        return $query->orderBy('program_title', 'asc')
            ->orderBy('program_shortname', 'asc')
            ->skip((int)$start)
            ->take((int)$limit)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageProgramsBySearch($search = '', $package_id = 0, $start = 0, $limit = 500)
    {
        $query = Program::where('status', '!=', 'DELETED')
            ->Where(function ($single) {
                $single->where('program_type', '=', 'content_feed')
                    ->where('program_sub_type', '=', 'single');
            })
            ->where(function ($q) use ($package_id) {
                $q->Where('parent_relations.active_parent_rel', '=', (int)$package_id)
                ->orWhere('package_ids', '=', (int)$package_id);
            });
            
        if ($search != '') {
             $query->where('program_title', 'like', "%" . $search . "%");
        }
        return $query->orderBy('program_title', 'asc')
            ->orderBy('program_shortname', 'asc')
            ->skip((int)$start)
            ->take((int)$limit)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getCFDetailsById($feed_id)
    {
        return Program::where('status', '!=', 'DELETED')
            ->whereIn('program_id', $feed_id)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramById($program_id)
    {
        return Program::where('program_id', '=', (int)$program_id)->first();
    }

    /**
     * generateProgramSlug prepares program slug
     * @param string $name
     * @param string $shortname
     * @returns program slug
     */
    public function generateProgramSlug($name, $shortname)
    {
        $slug = $this->getSlugName($name, $shortname);
        return $slug;
    }

    /**
     * Method to download update channel/package csv file
     * @returns update channel/package csv file with fields as headers
     */
    public function downloadProgramTemplate($type, $subtype, $action)
    {
        $data = [];
        $data[] = 'name*';
        $data[] = 'shortname';
        $data[] = 'newname*';
        $data[] = 'newshortname';
        $data[] = 'description';
        $data[] = 'startdate*';
        $data[] = 'enddate*';
        $data[] = 'displaystartdate*';
        $data[] = 'displayenddate*';
       //$data[] = 'sellable*';
        $data[] = 'keywords';
        if ($type == 'content_feed' && $subtype == 'single') {
         //$data[] = 'packagename';
         //$data[] = 'packageshortname';
        }
        $file = ($subtype == 'single') ? config('app.channel_import_file'): config('app.package_import_file');
        $custom_fields = CustomFields::getUserCustomFieldArr($type, $subtype, ['fieldname', 'fieldname','mark_as_mandatory']);
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom) {
                if ($custom['mark_as_mandatory']=='yes') {
                    $data[] = $custom['fieldname'].'*';
                } else {
                    $data[] = $custom['fieldname'];
                }
            }
        }
        header('Content-type: application/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file);
        $fp = fopen('php://output', 'w');
        fputcsv($fp, $data);
        die;
    }
    /**
     * Method to update program details
     * @param array $program_data
     * @param string $old_slug
     * @param string $new_slug
     * @param int $cron
     * @returns nothing updates program information
     */
    public function updateProgramDetails($program_data, $old_slug, $new_slug, $cron)
    {
          $default_timezone = ($cron == 0) ? Auth::user()->timezone : config('app.default_timezone');
          $program_id = Program::where('program_slug', '=', $old_slug)->value('program_id');
        if (array_key_exists('newname', $program_data)) {
            $program_data['program_title'] = trim($program_data['newname']);
            $program_data['program_shortname'] = $program_data['newshortname'];
            $program_data['title_lower'] = trim(strtolower($program_data['newname']));
            $program_data['program_slug'] = $new_slug;
            unset($program_data['newname']);
            unset($program_data['newshortname']);
        }
        if (array_key_exists('startdate', $program_data)) {
            $program_data['program_startdate'] = (int)Timezone::convertToUTC($program_data['startdate'], $default_timezone, 'U');
            unset($program_data['startdate']);
        }
        if (array_key_exists('enddate', $program_data)) {
            $program_data['program_enddate'] = (int)Timezone::convertToUTC($program_data['enddate'], $default_timezone, 'U');
            unset($program_data['enddate']);
        }
        if (array_key_exists('displaystartdate', $program_data)) {
            $program_data['program_display_startdate'] = (int)Timezone::convertToUTC($program_data['displaystartdate'], $default_timezone, 'U');
            unset($program_data['displaystartdate']);
        }
        if (array_key_exists('displayenddate', $program_data)) {
            $program_data['program_display_enddate'] = (int)Timezone::convertToUTC($program_data['displayenddate'], $default_timezone, 'U');
            unset($program_data['displayenddate']);
        }
        if (array_key_exists('description', $program_data)) {
            $program_data['program_description'] = $program_data['description'];
            unset($program_data['description']);
        }
        if (array_key_exists('keywords', $program_data)) {
            $program_data['program_keywords'] = array($program_data['keywords']);
            unset($program_data['keywords']);
        }
          unset($program_data['name']);
          unset($program_data['shortname']);
          Program::where('program_id', '=', (int)$program_id)->update($program_data);
          return $program_id;
    }

    public function displayActivePrograms($filter_params = [], $skip = 0, $limit = 0)
    {
        return Program::filter($filter_params)->DisplayActive()->Active()->skip((int)$skip)->limit((int)$limit)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function exportChannels($program_type, $program_sub_type, $filter, $custom_field_name, $custom_field_value)
    {
        $records = Program::where('program_type', '=', $program_type)
                    ->where('program_sub_type', '=', $program_sub_type)
                    ->FeedStatus(array_get($filter, 'status', 'ALL'))
                    ->VisibilityFilter(array_get($filter, 'visibility', 'all'))
                    ->SellabilityFilter(array_get($filter, 'sellability', 'all'))
                    ->AccessFilter(array_get($filter, 'access'))
                    ->CategoryFilter(array_get($filter, 'category'))
                    ->TitleFilter(array_get($filter, 'feed_title'))
                    ->ShortnameFilter(array_get($filter, 'short_name'))
                    ->DescriptionFilter(array_get($filter, 'descriptions'))
                    ->CreateddateFilter(array_get($filter, 'created_date'), array_get($filter, 'get_created_date'))
                    ->UpdateddateFilter(array_get($filter, 'updated_date'), array_get($filter, 'get_updated_date'))
                    ->FeedtagsFilter(array_get($filter, 'feed_tags'))
                    ->CustomfieldFilter($custom_field_name, $custom_field_value)
                    ->ChannelFilter(array_get($filter, 'channel_name'))
                    ->BatchFilter(array_get($filter, 'batch_name'))
                    ->CourseRecords($program_type)
                    ->GetAsArray();

                $custom_fields = CustomFields::getUserCustomFieldArr(
                    $program_type == 'content_feed' ? $program_type : 'course',
                    $program_sub_type,
                    ['fieldname', 'fieldname', 'mark_as_mandatory']
                );


        if (!empty($records)) {
            $data = [];
            if ($program_type == "content_feed") {
                if ($program_sub_type == 'single') {
                    $data[] = ['Report Name:' .trans('admin/program.channel') . 'Export'];
                } else {
                    $data[] = ['Report Name:' .trans('admin/program.package') . 'Export'];
                }
            } else {
                $data[] = ['Report Name:' .trans('admin/program.course') . 'Export'];
            }
            $data[] = [];
            
            if ($program_type == "content_feed") {
                if ($program_sub_type == 'single') {
                    $header[] = 'Channel Title*';
                } else {
                    $header[] = 'Package Title*';
                }
            } else {
                $header[] = 'Course Title*';
            }

            $header[] = 'Short Name';
            $header[] = 'Start Date*';
            $header[] = 'End Date*';
            $header[] = 'Display Start Date*';
            $header[] = 'Display End Date*';
            $header[] = 'Sellability*';
            if ($program_type != "course") {
                $header[] = 'Access';
            }
            $header[] = 'Visibility*';
            $header[] = 'Status';
            $header[] = 'Description';
            $header[] = 'Keywords';
           
            if (!empty($custom_fields)) {
                foreach ($custom_fields as $custom) {
                    if ($custom['mark_as_mandatory'] == 'yes') {
                        $header[] = $custom['fieldname'] . '*';
                    } else {
                        $header[] = $custom['fieldname'];
                    }
                }
            }

            if ($program_type == "content_feed") {
                if ($program_sub_type == 'single') {
                    $header[] = '';
                } else {
                    $header[] = 'No. of Channels';
                }
            } else {
                $header[] = 'No. of Batches';
            }
            
            $data[] = $header;
            foreach ($records as $report) {
                $tempRow = [];
                $program = Program::where('program_id', '=', (int)$report['program_id'])->get()->first();
                $tempRow[] = html_entity_decode($program['program_title']);
                $tempRow[] = html_entity_decode($program['program_shortname']);
                $tempRow[] = Timezone::convertFromUTC('@' . $program['program_startdate'], Auth::user()->timezone, config('app.date_format'));
                $tempRow[] = Timezone::convertFromUTC('@' . $program['program_enddate'], Auth::user()->timezone, config('app.date_format'));
                $tempRow[] = Timezone::convertFromUTC('@' . $program['program_display_startdate'], Auth::user()->timezone, config('app.date_format'));
                $tempRow[] = Timezone::convertFromUTC('@' . $program['program_display_enddate'], Auth::user()->timezone, config('app.date_format'));
                $tempRow[] = $program['program_sellability'];
                if ($program_type != "course") {
                     $tempRow[] = $program['program_access'];
                }
                $tempRow[] = $program['program_visibility'];
                $tempRow[] = $program['status'];
                $tempRow[] = html_entity_decode($program['program_description']);
                $tempRow[] = (!empty($program['program_keywords'])) ? implode(',', $program['program_keywords']) : '';

                if (!empty($custom_fields)) {
                    foreach ($custom_fields as $custom) {
                        if (isset($program[$custom['fieldname']])) {
                            $tempRow[] = html_entity_decode($program[$custom['fieldname']]);
                        } else {
                            $tempRow[] = '';
                        }
                    }
                }
                
                if ($program_type == "content_feed") {
                    if ($program_sub_type == 'single') {
                        $tempRow[] = '';
                    } else {
                        $active_channel_rel = $program['child_relations']['active_channel_rel'];
                        $tempRow[] = count($active_channel_rel);
                    }
                } else {
                    $program_id = $program['program_id'];
                    $batches_query = Program::where('parent_id', '=', (int)$program_id)
                                    ->where('status', '!=', 'DELETED')
                                    ->get();
                    $tempRow[] = count($batches_query);
                }
                
                $data[] = $tempRow;
            }
                
            if (!empty($data)) {
                if ($program_type == "content_feed") {
                    if ($program_sub_type == 'single') {
                        $filename = trans('admin/program.channel') . "Report.csv";
                    } else {
                        $filename = trans('admin/program.package') . "Report.csv";
                    }
                } else {
                    $filename = trans('admin/program.course') . "Report.csv";
                }
                
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
     * {@inheritdoc}
     */
    public function getActiveProgramsCount(array $program_ids, array $date)
    {
        $query = Program::where('status', '=', 'ACTIVE')
                    ->where('program_type', 'content_feed')
                    ->where('program_sub_type', 'single');
        if (!empty($program_ids)) {
            $query->whereIn('program_id', $program_ids);
        }
        return $query->whereBetween('created_at', $date)->count();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getNewProgramsCount(array $program_ids, array $date)
    {
        $query = Program::whereIn('status', ['ACTIVE','IN-ACTIVE'])
                    ->where('program_type', 'content_feed')
                    ->where('program_sub_type', 'single');
        if (!empty($program_ids)) {
            $query->whereIn('program_id', $program_ids);
        }
        return $query->whereBetween('created_at', $date)->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getSlugsByIds(array $program_ids)
    {
        $query = Program::where('status', '=', 'ACTIVE');
        if (!empty($program_ids)) {
            $query->whereIn('program_id', $program_ids);
        }
        return $query->pluck('program_slug')->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getUsersPermittedCFByIds(array $program_ids)
    {
        $query = Program::where('status', '=', 'ACTIVE');
        if (!empty($program_ids)) {
            $query->whereIn('program_id', $program_ids);
        }
        return $query->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getAboutExpirePrograms($date)
    {
        return Program::where('relations.active_user_feed_rel.0', 'exists', true)
                    ->orWhere('relations.active_usergroup_feed_rel.0', 'exists', true)
                    ->where('status', '=', 'ACTIVE')
                    ->whereBetween('program_display_enddate', $date)
                    ->get(['program_id', 'relations.active_user_feed_rel', 'relations.active_usergroup_feed_rel']);
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramPacketsQuizzs($program_ids)
    {
        return Program::raw(function ($c) use ($program_ids) {
            return $c->aggregate([
                [
                    '$match' =>  [
                        'program_id' => ['$in' => array_values($program_ids)]
                    ]
                ],
                [
                    '$lookup' => [
                        'from' =>  'packets',
                        'localField' =>  'program_slug',
                        'foreignField' =>  'feed_slug',
                        'as' =>  'packet_details'
                    ]
                ],
                [
                    '$project' => [
                        "program_id" => 1,
                        "program_slug" => 1,
                        'relations' => 1,
                        "packet_details" =>  ["packet_id" => 1, "feed_slug" => 1, 'elements' => 1]
                    ]
                ],
                [
                    '$match' => [
                        'packet_details.elements.type' => 'assessment'
                    ]
                ],
                [
                    '$group' => [
                        '_id' => [
                            'program_id' => '$program_id',
                        ],
                        'elements' => ['$addToSet' => '$packet_details.elements'],
                        'user_relations' => ['$addToSet' => '$relations.active_user_feed_rel'],
                        'ug_relations' => ['$addToSet' => '$relations.active_usergroup_feed_rel']
                    ]
                ],
                [
                    '$unwind' => '$elements'
                ],
                [
                    '$unwind' => '$elements'
                ],
                [
                    '$unwind' => '$elements'
                ],
                [
                    '$match' => [
                        'elements.type' => 'assessment'
                    ]
                ],
                [
                    '$group' => [
                        '_id' => [
                            'program_id' => '$_id.program_id',
                        ],
                        'quiz_ids' => ['$addToSet' => '$elements.id'],
                        'user_relations' => ['$addToSet' => '$user_relations'],
                        'ug_relations' => ['$addToSet' => '$ug_relations']
                    ]

                ],
                [
                    '$unwind' => '$user_relations'
                ],
                [
                    '$unwind' => '$ug_relations'
                ],
                [
                    '$project' => [
                        'program_id' => '$_id.program_id',
                        'quiz_ids' => 1,
                        'user_relations' => 1,
                        'ug_relations' => 1,
                        '_id' => 0
                    ]
                ]
            ]);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramsDetailsById($program_ids)
    {
        return Program::Where('status', '=', 'ACTIVE')
            ->whereIn('program_id', array_values($program_ids))
            ->get(['program_id', 'program_title', 'program_slug']);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getFtpDirUsergroupToPackageDetails($ftp_conn_id)
    {
         $ftp_chdir = ftp_chdir($ftp_conn_id['conn_id'], "add");
         $file_list = ftp_nlist($ftp_conn_id['conn_id'], '.');
         $local_file = config('app.package_usergroup_import_file');
         $ftp_dir_details = [
          'file_list' => $file_list,
          'local_file' => $local_file,
          'ftp_chdir' => $ftp_chdir,
         ];
         return $ftp_dir_details;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsergroupToPackageFileColumns($ftp_connection_details, $ftp_dir_details)
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
        $count = 0;
        $fields = array('usergroup', 'packagename', 'packageshortname');
        /*---Validating headers starts here---*/
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
     * {@inheritdoc}
     */
    public function validateErpEnrolRules($csvrowData)
    {
        
        $rules = [
            'username' => 'Required|checkusername:' . $csvrowData['username'] . '|userexist:'
            . $csvrowData['name'] . ',' . $csvrowData['shortname'] . ',' . $csvrowData['username']
            . '|checkactiveuser:' . $csvrowData['username'] . '',
            'name' => 'Required|checkprogram:' . $csvrowData['name'] . ',' . $csvrowData['shortname'] . '',
            'shortname' => 'min:3',

        ];

        Validator::extend('userexist', function ($attribute, $value, $parameters) {
            $parameters[0] = preg_replace('/[^\w ]+/', '', $parameters[0]);
            $parameters[0] = strtolower(preg_replace('/ +/', '-', $parameters[0]));
            if (!empty($parameters[1])) {
                $parameters[1] = preg_replace('/[^\w ]+/', '', $parameters[1]);
                $parameters[1] = strtolower(preg_replace('/ +/', '-', $parameters[1]));
                $slug = "content-feed" . '-' . $parameters[0] . '-' . $parameters[1];
            } else {
                $slug = "content-feed" . '-' . $parameters[0];
            }
            $program = Program::getProgram($slug);
            $user = User::where('username', '=', strtolower($parameters[2]))
                    ->where('status', '!=', 'DELETED')->get(['uid'])->toArray();
            $programid = (isset($program[0]['program_id'])) ? $program[0]['program_id'] : ' ';
            $groupid = (isset($user[0]['uid'])) ? $user[0]['uid'] : ' ';
            $transactions = TransactionDetail::where('program_id', '=', (int)$programid)->where('id', '=', $groupid)
                ->where('trans_level', '=', 'user')->where('status', '=', 'COMPLETE')->get()->toArray();
            if (empty($transactions)) {
                return true;
            }
            return false;
        });

        Validator::extend('checkprogram', function ($attribute, $value, $parameters) {
            $parameters[0] = preg_replace('/[^\w ]+/', '', $parameters[0]);
            $parameters[0] = strtolower(preg_replace('/ +/', '-', $parameters[0]));
            if (!empty($parameters[1])) {
                $parameters[1] = preg_replace('/[^\w ]+/', '', $parameters[1]);
                $parameters[1] = strtolower(preg_replace('/ +/', '-', $parameters[1]));
                $slug = "content-feed" . '-' . $parameters[0] . '-' . $parameters[1];
            } else {
                $slug = "content-feed" . '-' . $parameters[0];
            }

            $returnval = Program::where('program_slug', '=', $slug)
                        ->where('program_type', '=', 'content_feed')
                        ->where('status', '!=', 'DELETED')->get(['program_slug'])->toArray();
            if (!empty($returnval)) {
                return true;
            }
            return false;
        });

        Validator::extend('checkusername', function ($attribute, $value, $parameters) {
            $username = strtolower($parameters[0]);
            $returnval = User::where('username', 'like', $username)
                        ->where('status', '!=', 'DELETED')->get(['uid'])->toArray();
            if (!empty($returnval)) {
                return true;
            }
            return false;
        });

        Validator::extend('checkactiveuser', function ($attribute, $value, $parameters) {
            $username = strtolower($parameters[0]);
            $returnval = User::where('username', 'like', $username)
                    ->where('status', '!=', 'DELETED')->where('status', '!=', 'IN-ACTIVE')
                    ->get(['uid'])->toArray();
            if (!empty($returnval)) {
                return true;
            }
            return false;
        });
        
        $messages = [
            'checkusername' => trans('admin/program.check_username'),
            'checkprogram' => trans('admin/program.check_program'),
            'min' => trans('admin/program.shortname'),
            'userexist' => trans('admin/program.check_user'),
            'checkactiveuser' => trans('admin/program.check_active_user'),
        ];

        return $this->customErpPackageValidate($csvrowData, $rules, $messages);
    }

    /**
     * Method to validate program
     * @param array $input
     * @param array $rules
     * @param array $messages
     * @return array with validation messages
     */
    public function customErpPackageValidate($input, $rules, $messages = [])
    {
        $validation = Validator::make($input, $rules, $messages);
        if ($validation->fails()) {
            return $validation->messages();
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFtpDirUserToChannelDetails($ftp_conn_id)
    {
         $ftp_chdir = ftp_chdir($ftp_conn_id['conn_id'], "add");
         $file_list = ftp_nlist($ftp_conn_id['conn_id'], '.');
         $local_file = config('app.channel_user_import_file');
         $ftp_dir_details = [
          'file_list' => $file_list,
          'local_file' => $local_file,
          'ftp_chdir' => $ftp_chdir,
         ];
         return $ftp_dir_details;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserToChannelFileColumns($ftp_connection_details, $ftp_dir_details)
    {
        $fd = fopen($ftp_connection_details['path'] . $ftp_dir_details['local_file'], "w");
        fclose($fd);
        ftp_get($ftp_connection_details['conn_id'], $ftp_connection_details['path'] . $ftp_dir_details['local_file'], $ftp_dir_details['local_file'], FTP_BINARY); //downoading file from ftp to remote
        ftp_delete($ftp_connection_details['conn_id'], $ftp_dir_details['local_file']);
        ftp_close($ftp_connection_details['conn_id']); //ftp connection close
        //process records starts here

        $csvFile = file($ftp_connection_details['path'] . $ftp_dir_details['local_file']);
        //$csvFile = file('/var/www/html/ultron-core/channel_user.csv');
        $csv = array_map("str_getcsv", $csvFile);
        $headers = array_shift($csv);
        $count = 0;
        $fields = array('username', 'name', 'shortname');
        /*---Validating headers starts here---*/
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
     * {@inheritdoc}
     */
    public function getFtpDirPackageDetails($ftp_conn_id)
    {
         $ftp_chdir = ftp_chdir($ftp_conn_id['conn_id'], "add");
         $file_list = ftp_nlist($ftp_conn_id['conn_id'], '.');
         $local_file = config('app.package_import_file');
         $ftp_dir_details = [
          'file_list' => $file_list,
          'local_file' => $local_file,
          'ftp_chdir' => $ftp_chdir,
         ];
         return $ftp_dir_details;
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageFileColumns($ftp_connection_details, $ftp_dir_details)
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
        $core_fields = array('name', 'shortname', 'description', 'startdate', 'enddate', 'displaystartdate',
            'displayenddate', 'sellable', 'keywords');
        $custom_fields = CustomFields::getUserCustomFieldArr('content_feed', 'collection', ['fieldname', 'fieldname']);

        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom) {
                $customfield[] = $custom['fieldname'];
            }
        }
        $fields = array_merge($core_fields, $customfield);
        /*---Validating headers starts here---*/
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
     * {@inheritdoc}
     */
    public function validateErpPackageRules($csvrowData, $type = 'collection')
    {
        if ($type != 'collection') {
            $rules = [
                'name' => 'Required|checkslugregex:' . $csvrowData['name'] . '|checkslug:' . $csvrowData['name'] . ',' . $csvrowData['shortname'] . '',
                'shortname' => 'min:3|checkshortnameregex:' . $csvrowData['shortname'] . '',
                'startdate' => 'Required|date_format:d-m-Y',
                'enddate' => 'Required|date_format:d-m-Y|datecheck:' . $csvrowData['startdate'] . ',' . $csvrowData['enddate'] . '',
                'displaystartdate' => 'Required|date_format:d-m-Y|displaystartdatecheck:' . $csvrowData['startdate'] . ',' . $csvrowData['displaystartdate'] . '',
                'displayenddate' => 'Required|date_format:d-m-Y|displaydatecheck:' . $csvrowData['displaystartdate'] . ',' . $csvrowData['displayenddate'] . '|displayenddatecheck:' . $csvrowData['enddate'] . ',' . $csvrowData['displayenddate'] . '',
            ];
            if (config('app.ecommerce')) {
                $rules += ['sellable' => 'Required|in:yes,no'];
            }
        } else {
            $rules = [
                'name' => 'Required|checkslugregex:' . $csvrowData['name'] . '|checkslug:' . $csvrowData['name'] . ',' . $csvrowData['shortname'] . '',
                'packagename' => 'checkpackage:' . $csvrowData['packagename'] . ',' . $csvrowData['packageshortname'] . '|checkenrolments:' . $csvrowData['packagename'] . ',' . $csvrowData['packageshortname'] . '',
                'shortname' => 'min:3|checkshortnameregex:' . $csvrowData['shortname'] . '',
                'startdate' => 'Required|date_format:d-m-Y',
                'enddate' => 'Required|date_format:d-m-Y|datecheck:' . $csvrowData['startdate'] . ',' . $csvrowData['enddate'] . '',
                'displaystartdate' => 'Required|date_format:d-m-Y|displaystartdatecheck:' . $csvrowData['startdate'] . ',' . $csvrowData['displaystartdate'] . '',
                'displayenddate' => 'Required|date_format:d-m-Y|displaydatecheck:' . $csvrowData['displaystartdate'] . ',' . $csvrowData['displayenddate'] . '|displayenddatecheck:' . $csvrowData['enddate'] . ',' . $csvrowData['displayenddate'] . '',
                'sellable' => 'Required|in:yes,no',
            ];
        }

        $custom_fields = CustomFields::getUserCustomFieldArr('content_feed', $type, ['fieldname', 'fieldname', 'mark_as_mandatory']);

        if (!empty($custom_fields)) {
            foreach ($custom_fields as $key => $values) {
                if ($values['mark_as_mandatory'] == 'yes') {
                    $rules[$values['fieldname']] = 'Required';
                }
            }
        }
        Validator::extend('checkpackage', function ($attribute, $value, $parameters) {
            if (empty($parameters[0])) {
                return true;
            } else {
                $parameters[0] = preg_replace('/[^\w ]+/', '', $parameters[0]);
                $parameters[0] = strtolower(preg_replace('/ +/', '-', $parameters[0]));
                if (!empty($parameters[1])) {
                    $parameters[1] = preg_replace('/[^\w ]+/', '', $parameters[1]);
                    $parameters[1] = strtolower(preg_replace('/ +/', '-', $parameters[1]));
                    $packageslug = "content-feed" . '-' . $parameters[0] . '-' . $parameters[1];
                } else {
                    $packageslug = "content-feed" . '-' . $parameters[0];
                }
                $returnval = Program::where('program_slug', '=', $packageslug)->where('program_type', '=', 'content_feed')->where('status', '!=', 'DELETED')->get(['program_slug'])->toArray();
                if (!empty($returnval)) {
                    return true;
                }

                return false;
            }
        });
        Validator::extend('checkenrolments', function ($attribute, $value, $parameters) {
            if (empty($parameters[0])) {
                return true;
            } else {
                $parameters[0] = preg_replace('/[^\w ]+/', '', $parameters[0]);
                $parameters[0] = strtolower(preg_replace('/ +/', '-', $parameters[0]));
                if (!empty($parameters[1])) {
                    $parameters[1] = preg_replace('/[^\w ]+/', '', $parameters[1]);
                    $parameters[1] = strtolower(preg_replace('/ +/', '-', $parameters[1]));
                    $packageslug = "content-feed" . '-' . $parameters[0] . '-' . $parameters[1];
                } else {
                    $packageslug = "content-feed" . '-' . $parameters[0];
                }
                $program = Program::where('program_slug', '=', $packageslug)->where('program_type', '=', 'content_feed')->where('status', '!=', 'DELETED')->get()->toArray();
                if (isset($program[0]['relations']['active_user_feed_rel']) && !empty($program[0]['relations']['active_user_feed_rel'])) {
                    return false;
                } elseif (isset($program[0]['relations']['active_usergroup_feed_rel']) && !empty($program[0]['relations']['active_usergroup_feed_rel'])) {
                    return false;
                } else {
                    return true;
                }
            }
        });
        Validator::extend('checkslug', function ($attribute, $value, $parameters) {
            $parameters[0] = preg_replace('/[^\w ]+/', '', $parameters[0]);
            $parameters[0] = strtolower(preg_replace('/ +/', '-', $parameters[0]));
            if (!empty($parameters[1])) {
                $parameters[1] = preg_replace('/[^\w ]+/', '', $parameters[1]);
                $parameters[1] = strtolower(preg_replace('/ +/', '-', $parameters[1]));
                $slug = "content-feed" . '-' . $parameters[0] . '-' . $parameters[1];
            } else {
                $slug = "content-feed" . '-' . $parameters[0];
            }

            $returnval = Program::where('program_slug', '=', $slug)->where('program_type', '=', 'content_feed')->where('status', '!=', 'DELETED')->get(['program_slug'])->toArray();
            if (empty($returnval)) {
                return true;
            }

            return false;
        });
        Validator::extend('checkslugregex', function ($attribute, $value, $parameters) {
            if (preg_match('/^[a-zA-Z0-9 _-]+$/', $parameters[0])) {
                return true;
            }

            return false;
        });
        Validator::extend('checkshortnameregex', function ($attribute, $value, $parameters) {
            if (preg_match('/^[a-zA-Z0-9 _-]+$/', $parameters[0])) {
                return true;
            }

            return false;
        });
        Validator::extend('datecheck', function ($attribute, $value, $parameters) {
            $feed_start_date = $parameters[0];
            $feed_end_date = $parameters[1];
            if ((strtotime($feed_start_date) < strtotime($feed_end_date))) {
                return true;
            }

            return false;
        });
        Validator::extend('displaydatecheck', function ($attribute, $value, $parameters) {
            $feed_display_start_date = $parameters[0];
            $feed_display_end_date = $parameters[1];
            if ((strtotime($feed_display_start_date) < strtotime($feed_display_end_date))) {
                return true;
            }

            return false;
        });
        Validator::extend('displaystartdatecheck', function ($attribute, $value, $parameters) {
            $feed_start_date = $parameters[0];
            $feed_display_start_date = $parameters[1];
            if ((strtotime($feed_display_start_date) >= strtotime($feed_start_date))) {
                return true;
            }

            return false;
        });
        Validator::extend('displayenddatecheck', function ($attribute, $value, $parameters) {
            $feed_end_date = $parameters[0];
            $feed_display_end_date = $parameters[1];
            if ((strtotime($feed_display_end_date) <= strtotime($feed_end_date))) {
                return true;
            }

            return false;
        });

        $messages = [
            'displaystartdatecheck' => trans('admin/program.pack_disp_start_date_great_than_start_date'),
            'displayenddatecheck' => trans('admin/program.pack_disp_end_date_less_than_end_date'),
            'displaydatecheck' => trans('admin/program.pack_disp_end_date_greater_than_disp_start_date'),
            'datecheck' => trans('admin/program.pack_date_check'),
            'checkslug' => trans('admin/program.channel_check_slug'),
            'checkslugregex' => trans('admin/program.channel_check_slug_regex'),
            'checkshortnameregex' => trans('admin/program.channel_check_shortname_regex'),
            'name.required' => trans('admin/program.channel_field_required'),
            'min' => trans('admin/program.shortname'),
            'checkpackage' => trans('admin/program.channel_invalid'),
            'checkenrolments' => trans('admin/program.check_enrol'),
        ];

        return $this->customErpPackageValidate($csvrowData, $rules, $messages);
    }

    /**
     * {@inheritdoc}
     */
    public function getFtpDirPackageUpdateDetails($ftp_conn_id)
    {
         $ftp_chdir = ftp_chdir($ftp_conn_id['conn_id'], "update");
         $file_list = ftp_nlist($ftp_conn_id['conn_id'], '.');
         $local_file = config('app.package_import_file');
         $ftp_dir_details = [
          'file_list' => $file_list,
          'local_file' => $local_file,
          'ftp_chdir' => $ftp_chdir,
         ];
         return $ftp_dir_details;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatePackageFileColumns($ftp_connection_details, $ftp_dir_details)
    {
        $fd = fopen($ftp_connection_details['path'] . $ftp_dir_details['local_file'], "w");
        fclose($fd);
        ftp_get($ftp_connection_details['conn_id'], $ftp_connection_details['path'] . $ftp_dir_details['local_file'], $ftp_dir_details['local_file'], FTP_BINARY);
        ftp_delete($ftp_connection_details['conn_id'], $ftp_dir_details['local_file']);
        ftp_close($ftp_connection_details['conn_id']);
        $csvFile = file($ftp_connection_details['path'] . $ftp_dir_details['local_file']);
        $csv = array_map("str_getcsv", $csvFile);
        $headers = array_shift($csv);
        $custom_fields = array();
        $count = 0;
        $core_fields =[ 'name', 'shortname', 'newname', 'newshortname', 'description', 'startdate', 'enddate', 'displaystartdate', 'displayenddate', 'keywords'];
        $custom_fields = CustomFields::getUserCustomFieldArr('content_feed', 'collection', ['fieldname', 'fieldname']);
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom) {
                $custom_fields[] = $custom['fieldname'];
            }
        }
        $fields = array_merge($core_fields, $custom_fields);
        /*---Validating headers starts here---*/
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
     * {@inheritdoc}
     */
    public function getFtpDirChannelDetails($ftp_conn_id)
    {
         $ftp_chdir = ftp_chdir($ftp_conn_id['conn_id'], "add");
         $file_list = ftp_nlist($ftp_conn_id['conn_id'], '.');
         $local_file = config('app.channel_import_file');
         $ftp_dir_details = [
          'file_list' => $file_list,
          'local_file' => $local_file,
          'ftp_chdir' => $ftp_chdir,
         ];
         return $ftp_dir_details;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelFileColumns($ftp_connection_details, $ftp_dir_details)
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
        $core_fields = array('name', 'shortname', 'description', 'startdate', 'enddate', 'displaystartdate',
            'displayenddate', 'sellable', 'keywords', 'packagename', 'packageshortname');
        $custom_fields = CustomFields::getUserCustomFieldArr('content_feed', 'single', ['fieldname', 'fieldname']);

        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom) {
                $customfield[] = $custom['fieldname'];
            }
        }
        $fields = array_merge($core_fields, $customfield);
        /*---Validating headers starts here---*/
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
     * {@inheritdoc}
     */
    public function getFtpDirChannelUpdateDetails($ftp_conn_id)
    {
         $ftp_chdir = ftp_chdir($ftp_conn_id['conn_id'], "update");
         $file_list = ftp_nlist($ftp_conn_id['conn_id'], '.');
         $local_file = config('app.channel_import_file');

         $ftp_dir_details = [
          'file_list' => $file_list,
          'local_file' => $local_file,
          'ftp_chdir' => $ftp_chdir,
         ];
         return $ftp_dir_details;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdateChannelFileColumns($ftp_connection_details, $ftp_dir_details)
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
        $core_fields = array('name', 'shortname', 'newname', 'newshortname', 'description', 'startdate', 'enddate', 'displaystartdate', 'displayenddate', 'keywords');
        $custom_fields = CustomFields::getUserCustomFieldArr('content_feed', 'single', ['fieldname', 'fieldname']);
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom) {
                $customfield[] = $custom['fieldname'];
            }
        }
        $fields = array_merge($core_fields, $customfield);
        /*---Validating headers starts here---*/
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
     * {@inheritdoc}
     */
    public function getUsergroupUserRelationBySlug($program_slug)
    {
        $user_feed_rel = $usergroup_feed_rel = [];
        $relations = Program::where('relations.active_user_feed_rel.0', 'exists', true)
            ->orWhere('relations.active_usergroup_feed_rel.0', 'exists', true)
            ->whereIn('program_slug', $program_slug)
            ->where('status', '!=', 'DELETED')
            ->get();
        $user_feed_rel =  $relations->map(function ($item) {
            return array_get($item->relations, 'active_user_feed_rel', []);
        });
        $usergroup_feed_rel =  $relations->map(function ($item) {
            return array_get($item->relations, 'active_usergroup_feed_rel', []);
        });
        return [
            "active_user_feed_rel" => $user_feed_rel->toArray(),
            "active_usergroup_feed_rel" => $usergroup_feed_rel->toArray()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function pluckFeedDetails($program_slugs)
    {
        return Program::whereIn('program_slug', $program_slugs)
            ->where('status', '!=', 'DELETED')
            ->get(['program_title', 'program_id', 'program_type', 'parent_id',
                 'program_slug', 'package_ids'])
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramsByIds($ids, $columns = [])
    {
        return Program::whereIn('program_id', $ids)
                        ->where('status', 'ACTIVE')
                        ->get($columns);
    }
    /**
     * {@inheritdoc}
     */
    public function getProgramIdBySlug($program_slug)
    {
        return Program::where('program_slug', $program_slug)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramByChannelSlug($attribute, $value, $status)
    {
        return Program::where($attribute, $value)
            ->where('status', '!=', $status)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getProgram($program_id)
    {
        if (is_array($program_id)) {
            return Program::whereIn('program_id', $program_id)
                    ->where('status', 'ACTIVE')
                    ->get(['program_title', 'program_id']);
        } else {
            return Program::where('program_id', $program_id)
                    ->where('status', 'ACTIVE')
                    ->get(['program_title', 'program_id']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getNewPrograms(array $program_ids, array $date, $start, $limit)
    {
        $query = Program::whereIn('status', ['ACTIVE','IN-ACTIVE'])
                    ->where('program_type', 'content_feed')
                    ->where('program_sub_type', 'single');
        if (!empty($program_ids)) {
            $query->whereIn('program_id', $program_ids);
        }
        return $query->whereBetween('created_at', $date)
            ->skip((int)$start)->take((int)$limit)->orderBy('created_at', 'desc')->get(['program_title', 'created_at']);
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveProgramsDetails(array $program_ids, $start, $limit)
    {
        if (!empty($program_ids)) {
            $query = Program::whereIn('program_id', $program_ids);
            return $query->skip((int)$start)
                        ->take((int)$limit)
                        ->orderBy('created_at', 'desc')
                        ->get(['program_id','program_title', 'created_at', 'parent_id']);
        } else {
            return Program::skip((int)$start)
                        ->take((int)$limit)
                        ->orderBy('created_at', 'desc')
                        ->get(['program_id', 'program_title', 'created_at', 'parent_id']);
        }
    }

    /**
     * {inheritdoc}
     */
    public function getProgramsBySlugs(array $program_slugs, array $columns = [])
    {
        return Program::whereIn("program_slug", $program_slugs)->get($columns);
    }

    /**
     * @inheritdoc
     */
    public function countActiveChannels($program_ids)
    {
        if (!empty($program_ids)) {
            return Program::whereIn('program_id', $program_ids)->where('program_type', 'content_feed')->where('program_sub_type', 'single')->where('status', 'ACTIVE')->count();
        } else {
            return Program::where('program_type', 'content_feed')->where('program_sub_type', 'single')->where('status', 'ACTIVE')->count();
        }
    }

    /**
     * @inheritdoc
     */
    public function countInActiveChannels($program_ids)
    {
        if (!empty($program_ids)) {
            return Program::whereIn('program_id', $program_ids)->where('program_type', 'content_feed')->where('program_sub_type', 'single')->where('status', 'IN-ACTIVE')->count();
        } else {
            return Program::where('program_type', 'content_feed')->where('program_sub_type', 'single')->where('status', 'IN-ACTIVE')->count();
        }
    }

     /**
     * @inheritdoc
     */
    public function getOnlyProgramIds($channel_ids)
    {
        return Program::where('status', '!=', 'DELETED')
                ->where('program_type', 'content_feed')
                ->whereIn('program_id', $channel_ids)
                ->pluck('program_id');
    }

    /**
     * @inheritdoc
     */
    public function getAllUndeletedPrograms()
    {
        return Program::where('status', '!=', 'DELETED')
                ->where('program_type', 'content_feed')
                ->where('program_sub_type', 'single')
                ->orderBy('program_startdate', 'desc')
                ->get();
    }

    /**
     * @inheritdoc
     */
    public function getAllActiveChannels()
    {
        return Program::where('status', '=', 'ACTIVE')
                ->where('program_type', 'content_feed')
                ->where('program_sub_type', 'single')
                ->get(['program_id', 'program_title', 'program_slug']);
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelByProgramId($program_id)
    {
        return Program::where('program_id', (int)$program_id)
            ->where('program_type', 'content_feed')
            ->where('program_sub_type', 'single')
            ->where('status', '=', 'ACTIVE')
            ->get(['program_id', 'program_title', 'program_slug'])->first();
    }

    /**
     * @inheritdoc
     */
    public function getAllProgramByIDOrSlug($type, $slug, $filter_params)
    {
        return Program::getAllProgramByIDOrSlug($type, $slug, $filter_params);
    }

    /**
     * @inheritdoc
     */
    public function getVisibleProgramids($program_ids)
    {
        return Program::whereIn('program_id', $program_ids)
            ->where('program_visibility', '=', 'yes')
            ->where('status', '=', 'ACTIVE')
            ->get(['program_id']);

    }
}
