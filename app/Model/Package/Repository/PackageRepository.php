<?php

namespace App\Model\Package\Repository;

use App\Enums\Package\PackageStatus;
use App\Model\Package\Entity\Package;
use App\Exceptions\Package\PackageNotFoundException;
use App\Enums\Cron\CronBulkImport;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Model\ImportLog\Entity\PackageLog;
use App\Model\ImportLog\Entity\PackageEnrolLog;
use App\Model\CustomFields\Entity\CustomFields;
use App\Model\Transaction;
use App\Model\TransactionDetail;
use App\Model\UserGroup;
use Auth;
use Session;
use Timezone;
use Validator;

class PackageRepository implements IPackageRepository
{
    /**
     * @inheritDoc
     */
    public function createPackage($package_data = [])
    {
        $package = new Package();
        $package->fill($package_data);
        $package->package_id = $package->getNextSequence();
        $package->created_at = time();
        $package->updated_at = time();
        
        $package->save();

        return $package;
    }

    /**
     * @inheritDoc
     */
    public function updatePackage($package_id, $package_data = [])
    {
        $package = $this->find($package_id);
        $package->fill($package_data);
        $package->updated_at = time();
        $package->save();
        return $package;
    }

    /**
     * @inheritDoc
     */
    public function deletePackage($package_id)
    {
        $package = $this->find($package_id);
        $package->updated_at = time();
        $package->status = 'DELETED';
        $package->save();

        return $package;
    }

    /**
     * @inheritDoc
     */
    public function mapPackageAndPrograms($package_id, $program_ids)
    {
        $package = $this->find($package_id);
        $package->programs()->attach($program_ids);

        return $package;
    }

    /**
     * @inheritDoc
     */
    public function unMapPackageAndPrograms($package_id, $program_ids)
    {
        $package = $this->find($package_id);
        $package->programs()->detach($program_ids);

        return $package;
    }

    /**
     * @inheritDoc
     */
    public function find($id, $columns = ["*"])
    {
        try {
            return Package::where("package_id", (int) $id)
                ->where("status", "!=", PackageStatus::DELETED)
                ->firstOrFail($columns);
        } catch (ModelNotFoundException $e) {
            throw new PackageNotFoundException();
        }
    }

    /**
     * @inheritDoc
     */
    public function getPackageProgramsCount($package_id, $program_filter_params = [])
    {
        return $this->find($package_id)->programs()->filter($program_filter_params)->count();
    }

    /**
     * @inheritDoc
     */
    public function findPackageWithPrograms(
        $package_id,
        $program_filter_params = [],
        $package_columns = ["*"],
        $program_columns = ["*"]
    ) {
        try {
            return Package::where("package_id", (int) $package_id)
                ->where("status", "!=", PackageStatus::DELETED)
                ->with(
                    [
                        "programs" => function ($query) use ($program_filter_params, $program_columns) {
                            $query->filter($program_filter_params)
                                ->select(
                                    (array_search("package_ids", $program_columns) !== false)?
                                    $program_columns : array_merge($program_columns, ["package_ids"])
                                );
                        }
                    ]
                )->firstOrFail($package_columns);
        } catch (ModelNotFoundException $e) {
            throw new PackageNotFoundException();
        }
    }

    /**
     * @inheritDoc
     */
    public function findByAttribute($attribute, $value)
    {
        try {
            return Package::where($attribute, $value)
                ->where("status", "!=", PackageStatus::DELETED)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new PackageNotFoundException();
        }
    }

    /**
     * @inheritDoc
     */
    public function get($filter_params = [], $columns = [])
    {
        return Package::filter($filter_params)->get($columns);
    }

    /**
     * @inheritDoc
     */
    public function getCount($filter_params = [])
    {
        return Package::filter($filter_params)->count();
    }

    /**
     * @inheritDoc
     */
    public function mapUserAndPackage($package_id, $user_ids)
    {
        $package = $this->find($package_id);
        $package->user()->attach($user_ids);
        return $package;
    }

    public function unMapUserAndPackage($package_id, $user_ids)
    {
        $package = $this->find($package_id);
        $package->user()->detach($user_ids);
        return $package;
    }

    /**
     * {@inheritdoc}
     */
    public function mapUserGroupAndPackage($package_id, $user_group_ids)
    {
        $package = $this->find($package_id);
        $package->userGroup()->attach($user_group_ids);
        return $package;
    }

    /**
     * {@inheritdoc}
     */
    public function unMapUserGroupAndPackage($package_id, $user_group_ids)
    {
        $package = $this->find($package_id);
        $package->userGroup()->detach($user_group_ids);
        return $package;
    }

    /**
     * {@inheritdoc}
     */
    public function getActivePackages($filter_params = [])
    {
        return Package::filter($filter_params)->displayActive()->get();
    }

    public function getPackageWithoutCategories()
    {
        return Package::where(function ($query) {
            $query->where('category_ids', 'size', 0)
                ->orWhere('category_ids', 'exists', false);
        })->active()->get();
    }

    /**
     * @param int $id
     * @param string $type
     * @return nothing inserts records
     */
    public function getPreparePackageData($id, $input, $cron)
    {   
        $default_timezone = ($cron == 0) ? Auth::user()->timezone : config('app.default_timezone');
        $username = ($cron == 0) ? Auth::user()->username : CronBulkImport::CRONUSER;
        $slug = $this->getSlugName($input['name'], $input['shortname']);
        $feeddata['package_id'] = $id;
        $feeddata['package_title'] = trim($input['name']);
        $feeddata['title_lower'] = trim(strtolower($input['name']));
        $feeddata['package_shortname'] = $input['shortname'];
        $feeddata['package_slug'] = $slug;
        $feeddata['package_description'] = '';
        $feeddata['package_startdate'] = (int)Timezone::convertToUTC($input['startdate'], $default_timezone, 'U');
        $feeddata['package_enddate'] = (int)Timezone::convertToUTC($input['enddate'], $default_timezone, 'U');
        $feeddata['package_display_startdate'] = (int)Timezone::convertToUTC($input['displaystartdate'], $default_timezone, 'U');
        $feeddata['package_display_enddate'] = (int)Timezone::convertToUTC($input['displayenddate'], $default_timezone, 'U');
        $feeddata['package_duration'] = '';
        $feeddata['package_review'] = 'no';
        $feeddata['package_rating'] = 'no';
        $feeddata['package_visibility'] = 'yes';
        if (config('app.ecommerce')) {
            $feeddata['package_sellability'] = $input['sellable'];
        } else {
            $feeddata['package_sellability'] = 'yes';
        }
        $feeddata['package_keywords'] = array($input['keywords']);
        $feeddata['package_cover_media'] = '';
        $feeddata['duration'] = [array('label' => 'Forever', 'days' => 'forever')];
        $feeddata['benchmarks'] = array('speed' => 0, 'score' => 0, 'accuracy' => 0);
        $feeddata['category_ids'] = array();
        $feeddata['last_activity'] = time();
        $feeddata['status'] = 'ACTIVE';
        $feeddata['created_by'] = $username;
        $feeddata['created_by_name'] = $username;
        $feeddata['created_at'] = time();
        $feeddata['updated_at'] = time();
        if (array_get($feeddata, 'package_sellability') == "yes") { 
            $feeddata['package_access'] = "restricted_access"; 
        } else {
            $feeddata['package_access'] = "general_access";
        }
        
        unset($input['name'], $input['shortname'], $input['description'], $input['startdate'],
            $input['enddate'], $input['displaystartdate'], $input['displayenddate'],
            $input['sellable'], $input['keywords']);

        $record = array_merge($feeddata, $input);
        Package::insert($record);
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
            $slug = "package" . '-' . $name . '-' . $shortname;
        } else {
            $slug = "package" . '-' . $name;
        }
        return $slug;
    }

    /**
     * generatePackageSlug prepares package slug
     * @param string $name
     * @param string $shortname
     * @returns package slug
     */
    public function generatePackageSlug($name, $shortname)
    {
        $slug = $this->getSlugName($name, $shortname);
        return $slug;
    }

     /**
     * Method to update package details
     * @param array $program_data
     * @param string $old_slug
     * @param string $new_slug
     * @param int $cron
     * @returns nothing updates package information
     */
    public function updatePackageDetails($program_data, $old_slug, $new_slug, $cron)
    {   
        $default_timezone = ($cron == 0) ? Auth::user()->timezone : config('app.default_timezone');
        $package_id = Package::where('package_slug', '=', $old_slug)->value('package_id');
        if (array_key_exists('newname', $program_data)) {
            $program_data['package_title'] = trim($program_data['newname']);
            $program_data['package_shortname'] = $program_data['newshortname'];
            $program_data['title_lower'] = trim(strtolower($program_data['newname']));
            $program_data['package_slug'] = $new_slug;
            unset($program_data['newname']);
            unset($program_data['newshortname']);
        }

        if (array_key_exists('startdate', $program_data)) {
            $program_data['package_startdate'] = (int)Timezone::convertToUTC($program_data['startdate'], $default_timezone, 'U');
            unset($program_data['startdate']);
        }

        if (array_key_exists('enddate', $program_data)) {
            $program_data['package_enddate'] = (int)Timezone::convertToUTC($program_data['enddate'], $default_timezone, 'U');
            unset($program_data['enddate']);
        }

        if (array_key_exists('displaystartdate', $program_data)) {
            $program_data['package_display_startdate'] = (int)Timezone::convertToUTC($program_data['displaystartdate'], $default_timezone, 'U');
            unset($program_data['displaystartdate']);
        }

        if (array_key_exists('displayenddate', $program_data)) {
            $program_data['package_display_enddate'] = (int)Timezone::convertToUTC($program_data['displayenddate'], $default_timezone, 'U');
            unset($program_data['displayenddate']);
        }

        if (array_key_exists('description', $program_data)) {
            $program_data['package_description'] = $program_data['description'];
            unset($program_data['description']);
        }

        if (array_key_exists('keywords', $program_data)) {
            $program_data['package_keywords'] = array($program_data['keywords']);
            unset($program_data['keywords']);
        }

        unset($program_data['name']);
        unset($program_data['shortname']);
        Package::where('package_id', '=', (int)$package_id)->update($program_data);
        return $package_id;
    }

     /**
     * @param string $program_type
     * @param string $program_sub_type
     * @param string $status
     * @param string $date
     * @return nothing exports programs to csv file
     */
    public function exportPackages($status = 'ALL', $date = null, $action = 'ALL')
    {

        $reports = PackageLog::getPackageExportRecords($status, $date, $action);
        $custom_fields = CustomFields::getUserCustomFieldArr('content_feed', 'collection', ['fieldname', 'fieldname']);
        if (!empty($reports)) {
            $data = [];
            $data[] = [trans('admin/program.package') . 'Report'];
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
                    $program = Package::where('package_id', '=', (int)$report['program_id'])->get()->first();
                    $tempRow[] = $program['package_title'];
                    $tempRow[] = $program['package_shortname'];
                    $tempRow[] = $program['package_description'];
                    $tempRow[] = Timezone::convertFromUTC('@' . $program['package_startdate'], Auth::user()->timezone, config('app.date_format'));
                    $tempRow[] = Timezone::convertFromUTC('@' . $program['package_enddate'], Auth::user()->timezone, config('app.date_format'));
                    $tempRow[] = Timezone::convertFromUTC('@' . $program['package_display_startdate'], Auth::user()->timezone, config('app.date_format'));
                    $tempRow[] = Timezone::convertFromUTC('@' . $program['package_display_enddate'], Auth::user()->timezone, config('app.date_format'));
                    $tempRow[] = $program['package_sellability'];
                    $error = '';
                    if (!empty(array_get($program, 'package_keywords', []))) {
                        $tempRow[] = implode(',', $program['package_keywords']);
                    }
                    /*custom fields starts here*/
                    if (!empty($custom_fields)) {
                        foreach ($custom_fields as $custom) {
                            if (isset($program[$custom['fieldname']]))
                                $tempRow[] = $program[$custom['fieldname']];
                            else
                                $tempRow[] = '';
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
                            if (isset($report[$custom['fieldname']]))
                                $tempRow[] = $report[$custom['fieldname']];
                            else
                                $tempRow[] = '';
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
                $filename = trans('admin/program.package') . "Report.csv";
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
     * @param string $name
     * @param string $shortname
     * @return int package_id
     */
    public function getPackageId($name, $shortname)
    {
        $slug = $this->getSlugName($name, $shortname);
        return Package::where('package_slug', $slug)->value('package_id');

    }

    /**
     * @return int packageid
     * @param int $groupid
     * @param string $name
     * @param string $shortname
     * @param string $level
     * @param int $cron
     * @return nothing enrols usergroup to program
     */
    public function enrolUsergroupToPackage($packageid, $groupid, $name, $shortname, $level, $cron)
    {
        $slug = $this->getSlugName($name, $shortname);
        $transaction = $this->getTransactionData($level, $groupid, $cron);
        $transaction_details = $this->getTransactionDetailsData($level, $groupid, $transaction['trans_id'], $packageid, $slug, 'package', $name, 'collection', $packageid);
        Transaction::insert($transaction);
        TransactionDetail::insert($transaction_details);
        UserGroup::addUserGroupRelation($groupid, ['usergroup_parent_feed_rel'], $packageid);
        return $this->mapUserGroupAndPackage($packageid, [$groupid]);
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
     * @inheritdoc
     */
    public function validateErpPackages($csvrowData)
    {
        $rules = [
            'name' => 'Required|checkslugregex:' . $csvrowData['name'] . '|checkslug:' . 
            $csvrowData['name'] . ',' . $csvrowData['shortname'] . '',
            'shortname' => 'min:3|checkshortnameregex:' . $csvrowData['shortname'] . '',
            'startdate' => 'Required|date_format:d-m-Y',
            'enddate' => 'Required|date_format:d-m-Y|datecheck:' . $csvrowData['startdate'] 
            . ',' . $csvrowData['enddate'] . '',
            'displaystartdate' => 'Required|date_format:d-m-Y|displaystartdatecheck:' 
            . $csvrowData['startdate'] . ',' . $csvrowData['displaystartdate'] . '',
            'displayenddate' => 'Required|date_format:d-m-Y|displaydatecheck:' 
            . $csvrowData['displaystartdate'] . ',' . $csvrowData['displayenddate'] 
            . '|displayenddatecheck:' . $csvrowData['enddate'] . ',' . $csvrowData['displayenddate'] . '',
        ];
        
        if (config('app.ecommerce')) {
            $rules += ['sellable' => 'Required|in:yes,no'];
        }            

        $custom_fields = CustomFields::getUserCustomFieldArr('content_feed', 'collection', ['fieldname', 'fieldname', 'mark_as_mandatory']);

        if (!empty($custom_fields)) {
            foreach ($custom_fields as $key => $values) {
                if ($values['mark_as_mandatory'] == 'yes') {
                    $rules[$values['fieldname']] = 'Required';
                }
            }
        }
        
        Validator::extend('checkslug', function ($attribute, $value, $parameters) {
            $parameters[0] = preg_replace('/[^\w ]+/', '', $parameters[0]);
            $parameters[0] = strtolower(preg_replace('/ +/', '-', $parameters[0]));
            if (!empty($parameters[1])) {
                $parameters[1] = preg_replace('/[^\w ]+/', '', $parameters[1]);
                $parameters[1] = strtolower(preg_replace('/ +/', '-', $parameters[1]));
                $slug = "package" . '-' . $parameters[0] . '-' . $parameters[1];
            } else {
                $slug = "package" . '-' . $parameters[0];
            }

            $returnval = Package::where('package_slug', '=', $slug)
            ->where('status', '!=', 'DELETED')->get(['package_slug'])->toArray();
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
     * @param string $enrol_level
     * @param string $status
     * @param string $date
     * @return nothing exports ug-package to csv file
     */
    public function usergroupPackageExport($enrol_level, $status = 'ALL', $date = null)
    {
        $reports = PackageEnrolLog:: getPackageUsergroupExportRecords($status, $date, $enrol_level);
        if (!empty($reports)) {
            $data = [];
            $data[] = [trans('admin/program.package_ug') . 'Report'];
            $header[] = 'usergroup';
            $header[] = 'packagename';
            $header[] = 'packageshortname';
            $header[] = 'createdate';
            $header[] = 'status';
            $header[] = 'errormessage';
            $header[] = trans('admin/program.action');
            $data[] = $header;
            foreach ($reports as $report) {
                $tempRow = [];
                if ($report['status'] == 'SUCCESS') {
                    $program = Package::where('package_id', '=', (int)$report['program_id'])->get()->first();
                    $group = UserGroup::where('ugid', '=', (int)$report['uid'])->first();
                    $tempRow[] = $group['usergroup_name'];
                    $tempRow[] = $program['package_title'];
                    $tempRow[] = $program['package_shortname'];
                    $error = '';
                } else {
                    $tempRow[] = $report['usergroup'];
                    $tempRow[] = $report['packagename'];
                    $tempRow[] = $report['packageshortname'];
                    $error = $report['error_msgs'];
                }

                $tempRow[] = Timezone::convertFromUTC($report['created_at'], Auth::user()->timezone, config('app.date_format'));
                $tempRow[] = $report['status'];
                $tempRow[] = $error;
                $tempRow[] = $report['action'];
                $data[] = $tempRow;
            }
            if (!empty($data)) {
                $filename = trans('admin/program.package_ug') . "Report.csv";
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
    public function getProgramsByPackages(array $package_ids)
    {
        return Package::whereIn('package_id', $package_ids)
            ->where('program_ids.0', 'exists', true)
            ->pluck('program_ids');
    }

    /**
     * {@inheritdoc}
     */
    public function getPackagesBySearch($search = '', $is_ug_rel = false, $start = 0, $limit = 500)
    {
        $query = Package::where('status', '=', 'ACTIVE');
        if ($is_ug_rel) {
            $query->where('user_group_ids', 'exists', true);
        }
        if ($search != '') {
             $query->where('package_title', 'like', "%" . $search . "%");
        }
        return $query->orderBy('package_title', 'asc')
            ->orderBy('package_shortname', 'asc')
            ->skip((int)$start)
            ->take((int)$limit)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageByAttribute($attribute, $value, $status)
    {
        return Package::where($attribute, $value)
            ->where('status', '!=', $status)
            ->get();
    }
}
