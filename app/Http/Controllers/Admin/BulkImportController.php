<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Package\PackageStatus;
use App\Enums\RolesAndPermissions\Contexts;
use App\Enums\RolesAndPermissions\SystemRoles;
use App\Enums\User\UserEntity;
use App\Events\User\EntityEnrollmentByAdminUser;
use App\Events\User\EntityEnrollmentThroughUserGroup;
use App\Http\Controllers\AdminBaseController;
use App\Model\Common;
use App\Model\CustomFields\Entity\CustomFields;
use App\Model\ImportLog\Entity\EnrolLog;
use App\Model\ImportLog\Entity\PackageEnrolLog;
use App\Model\ImportLog\Entity\PackageLog;
use App\Model\ImportLog\Entity\ProgramLog;
use App\Model\ImportLog\Entity\UserLog;
use App\Model\Package\Entity\Package;
use App\Model\Packet;
use App\Model\Program;
use App\Model\TransactionDetail;
use App\Model\User;
use App\Model\UserGroup;
use App\Services\Package\IPackageService;
use App\Services\Program\IProgramService;
use Auth;
use Carbon;
use Config;
use Exception;
use Input;
use Log;
use Redirect;
use Request;
use Timezone;
use Validator;

class BulkImportController extends AdminBaseController
{
    protected $layout = 'admin.theme.layout.master_layout';
    private $isAdmin = false;
    private $userID = 0;
   
    private $programservice;

    /**
     * @var IPackageService
     */
    private $packageService;

    public function __construct(
        Request $request,
        IProgramService $programservice,
        IPackageService $packageService
    ) {
        parent::__construct();
        // Stripping all html tags from the request body
        $input = $request::input();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request::merge($input);

        if ((config('app.super_admin') && (int)Auth::user()->role == config('app.super_admin')) || (config('app.site_admin') && (int)Auth::user()->role == config('app.site_admin'))) {
            // 1 for Super Admin and 2 for Site Admin
            $this->isAdmin = true;
        }
        $this->userID = Auth::user()->uid;
        $this->theme_path = 'admin.theme';
        $this->programservice = $programservice;
        $this->packageService = $packageService;
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
     * Method to import packages
     * @return nothing
     */
    public function getErpImportPackages()
    {   
        try {
            if (config('app.ftp_enabled')) {
                $connect = UserLog::getValidateFtp();
                if ($connect) {
                    $ftp_connection_details = $this->programservice->getFtpConnectionDetails();
                    $ftp_file_path = array_get($ftp_connection_details, 'path', "");
                    $ftp_connection_id = array_get($ftp_connection_details, 'conn_id', "");
                    if (in_array('add', array_get($ftp_connection_details, 'dir_list', ""))) {
                        $ftp_dir_details = $this->programservice->getFtpDirPackageDetails($ftp_connection_details);
                        $ftp_local_file = array_get($ftp_dir_details, 'local_file', "");
                        if (in_array($ftp_local_file, array_get($ftp_dir_details, 'file_list', ""))) {
                            $csv_file_data = $this->programservice->getPackageFileColumns($ftp_connection_details, $ftp_dir_details);
                            /*---Validating headers ends here---*/
                            if (array_get($csv_file_data, 'count', "") > 0) {
                                $logdata['error_msgs'] = trans('admin/program.invalid_headers');
                                $this->packageService->postPackageLog($logdata, 'FAILURE', 'erp-package-import-failure-template', 'ADD', 0);
                                $message = trans('admin/program.invalid_headers');
                            } else {
                                $i = 0;
                                foreach (array_get($csv_file_data, 'csvFile', []) as $line) {
                                    $data = str_getcsv($line);
                                    $i++;
                                    if ($i > 1) {
                                        $csv_file_head = array_get($csv_file_data, 'head', []);
                                        $valid_data = $this->packageService->validateErpPackages(array_combine($csv_file_head, $data));
                                        if ($valid_data) {
                                            $error_msg = '';
                                            foreach ($valid_data->all() as $k) {
                                                $error_msg .= $k;

                                            }
                                            /*---Insert data in erp program log collection with failure---*/
                                            $logdata = array_combine($csv_file_head, $data);
                                            $logdata['error_msgs'] = $error_msg;
                                            $logdata['action'] = 'ADD';
                                            $record = $this->packageService->getErpLogdata($logdata, 'FAILURE', 0);
                                            PackageLog::getInsertErpPackageLog($record);
                                        } else {
                                            /*---Insert data in program collection---*/
                                            $productid = Package::uniqueProductId();
                                            $this->packageService->getPrepareErpPackageData($productid, array_combine($csv_file_head, $data), $cron = 0);
                                            /*---Insert data in erp program log collection with success---*/
                                            $input['program_id'] = $productid;
                                            $input['action'] = 'ADD';
                                            $record = $this->packageService->getErpLogdata($input, 'SUCCESS', 0);
                                            PackageLog::getInsertErpPackageLog($record);
                                        }
                                    }
                                }
                                $this->packageService->packageImportEmail('SUCCESS', 'erp-package-import-success-template', '', 'ADD');
                                $message = trans('admin/program.cron_success');
                            }
                            $new_file = $this->packageService->getFileName($ftp_local_file, 'add');
                            rename($ftp_file_path . $ftp_local_file, $ftp_file_path . $new_file);
                        } else {
                            ftp_close($ftp_connection_id); //ftp connection close
                            $logdata['error_msgs'] = trans('admin/program.invalid_file');
                            $this->packageService->postPackageLog($logdata, 'FAILURE', 'erp-package-import-failure-template', 'ADD', 0);
                            $message = trans('admin/program.invalid_file');
                        }
                    } else {
                        ftp_close($ftp_connection_id); //ftp connection close
                        $logdata['error_msgs'] = trans('admin/program.invalid_add_dir');
                        $this->packageService->postPackageLog($logdata, 'FAILURE', 'erp-package-import-failure-template', 'ADD', 0);
                        $message = trans('admin/program.invalid_add_dir');
                    }
                } else {
                    $logdata['error_msgs'] = trans('admin/program.invalid_ftp');
                    $this->packageService->postPackageLog($logdata, 'FAILURE', 'erp-package-import-failure-template', 'ADD', 0);
                    $message = trans('admin/program.invalid_ftp');
                }
            } else {
                $message = trans('admin/program.cron_ftp_disabled');
            }
            $this->layout->pagetitle = trans('admin/program.import_package_cron');
            $this->layout->pageicon = 'fa fa-cloud-download';
            $this->layout->pagedescription = trans('admin/program.import_package_cron');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->content = view('admin.theme.programs.import_cron')->with('message', $message);
            $this->layout->footer = view('admin.theme.common.footer');
        } catch (Exception $e) {
            Log::error($e->getFile(). trans('admin/program.error_line', ['error_line_no'=> $e->getLine()]).trans('admin/program.message', ['error_message' => $e->getMessage()]));
            $this->layout->pagetitle = trans('admin/program.import_package_cron');
            $this->layout->pageicon = 'fa fa-cloud-download';
            $this->layout->pagedescription = trans('admin/program.import_package_cron');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->content = view('admin.theme.programs.import_cron')->with('message', $e->getMessage());
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }
    /**
     * Method to import channels
     * @return nothing
     */
    public function getErpImportChannels()
    {   
        try {
            if (config('app.ftp_enabled')) {
                $connect = UserLog::getValidateFtp();
                if ($connect) {
                    $ftp_connection_details = $this->programservice->getFtpConnectionDetails();
                    if (in_array('add', $ftp_connection_details['dir_list'])) {
                        $ftp_dir_details = $this->programservice->getFtpDirChannelDetails($ftp_connection_details);
                        if (in_array($ftp_dir_details['local_file'], $ftp_dir_details['file_list'])) {
                            $csv_file_data = $this->programservice->getChannelFileColumns($ftp_connection_details, $ftp_dir_details);
                            /*---Validating headers ends here---*/
                            if ($csv_file_data['count'] > 0) {
                                $message = trans('admin/program.invalid_headers');
                                $logdata['error_msgs'] = trans('admin/program.invalid_headers');
                                $this->programservice->postChannelLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-channel-import-failure-template', $action = 'ADD', $cron = 0);
                            } else {
                                $i = 0;
                                foreach ($csv_file_data['csvFile'] as $line) {
                                    $data = str_getcsv($line);
                                    $i++;
                                    if ($i > 1) {
                                        $valid_data = $this->programservice->validateErpPackageRules(array_combine($csv_file_data['head'], $data), 'single');
                                        if ($valid_data) {
                                            $error_msg = '';
                                            foreach ($valid_data->all() as $k) {
                                                $error_msg .= $k;

                                            }
                                            /*---Insert record in erp program log collection with failure---*/
                                            $logdata = array_combine($csv_file_data['head'], $data);
                                            $logdata['error_msgs'] = $error_msg;
                                            $logdata['action'] = 'ADD';
                                            $record = $this->programservice->getErpLogdata($logdata, $type = 'single', $status = 'FAILURE', $cron = 0);
                                            ProgramLog::getInsertErpProgramLog($record);
                                        } else {

                                            /*---Insert data in program collection---*/
                                            $productid = Program::uniqueProductId();
                                            $this->programservice->getPrepareErpProgramData($productid, array_combine($csv_file_data['head'], $data), $type = 'single', $cron = 0);
                                            $input['program_id'] = $productid;
                                            $input['action'] = 'ADD';
                                            /*---Insert record in erp program log collection with success---*/
                                            $record = $this->programservice->getErpLogdata($input, $type = 'single', $status = 'SUCCESS', $cron = 0);
                                            ProgramLog::getInsertErpProgramLog($record);
                                        }
                                    }
                                }
                                $this->programservice->channelImportEmail($status = 'SUCCESS', $slug = 'erp-channel-import-success-template', '', $action = 'ADD');
                                $message = trans('admin/program.cron_success');
                            }
                            //process records ends here
                            $new_file = $this->programservice->getFileName($ftp_dir_details['local_file'], $action = 'add');
                            rename($ftp_connection_details['path'] . $ftp_dir_details['local_file'], $ftp_connection_details['path'] . $new_file);
                        } else {
                            ftp_close($ftp_connection_details['conn_id']); //ftp connection close
                            $message = trans('admin/program.invalid_file');
                            $logdata['error_msgs'] = trans('admin/program.invalid_file');
                            $this->programservice->postChannelLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-channel-import-failure-template', $action = 'ADD', $cron = 0);
                        }
                    } else {
                        ftp_close($ftp_connection_details['conn_id']); //ftp connection close
                        $message = trans('admin/program.invalid_add_dir');
                        $logdata['error_msgs'] = trans('admin/program.invalid_add_dir');
                        $this->programservice->postChannelLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-channel-import-failure-template', $action = 'ADD', $cron = 0);
                    }
                } else {
                    $message = trans('admin/program.invalid_ftp');
                    $logdata['error_msgs'] = trans('admin/program.invalid_ftp');
                    $this->programservice->postChannelLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-channel-import-failure-template', $action = 'ADD', $cron = 0);
                }
            } else {
                $message = trans('admin/program.cron_ftp_disabled');     
            }
            $this->layout->pagetitle = trans('admin/program.import_channel_cron');
            $this->layout->pageicon = 'fa fa-cloud-download';
            $this->layout->pagedescription = trans('admin/program.import_channel_cron');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->content = view('admin.theme.programs.import_cron')->with('message', $message);
            $this->layout->footer = view('admin.theme.common.footer');
        } catch (Exception $e) {
            Log::error($e->getFile(). trans('admin/program.error_line', ['error_line_no'=> $e->getLine()]).trans('admin/program.message', ['error_message' => $e->getMessage()]));
            $this->layout->pagetitle = trans('admin/program.import_channel_cron');
            $this->layout->pageicon = 'fa fa-cloud-download';
            $this->layout->pagedescription = trans('admin/program.import_channel_cron');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->content = view('admin.theme.programs.import_cron')->with('message', $e->getMessage());
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }


    /**
     * Method to validate enrolments
     * @param array $csvrowData
     * @return validation messages
     */
    public function validateErpPackageEnrolRules($csvrowData)
    {
        $rules = [
            'usergroup' => 'Required|Min:3|Max:60|checkusergroup:' . $csvrowData['usergroup'] 
            . '|usergroupexist:' . $csvrowData['packagename'] . ',' . $csvrowData['packageshortname'] 
            . ',' . $csvrowData['usergroup'] . '|activeusergroup:' . $csvrowData['usergroup'] .'',
            'packageshortname' => 'min:3',
            'packagename' => 'Required|checkpackageexists:'. $csvrowData['packagename'] 
            . ',' . $csvrowData['packageshortname']. '|checkchannel:' 
            . $csvrowData['packagename'] . ',' . $csvrowData['packageshortname']. '|checkprogram:' 
            . $csvrowData['packagename'] . ',' . $csvrowData['packageshortname'] . '|checkpackageshortname:'. $csvrowData['packagename'] . ',' . $csvrowData['packageshortname'] . '',

        ];

        Validator::extend('checkchannel', function ($attribute, $value, $parameters) {
            $parameters[0] = preg_replace('/[^\w ]+/', '', $parameters[0]);
            $parameters[0] = strtolower(preg_replace('/ +/', '-', $parameters[0]));
            if (!empty($parameters[1])) {
                $parameters[1] = preg_replace('/[^\w ]+/', '', $parameters[1]);
                $parameters[1] = strtolower(preg_replace('/ +/', '-', $parameters[1]));
                $slug = "package" . '-' . $parameters[0] . '-' . $parameters[1];
            } else {
                $slug = "package" . '-' . $parameters[0];
            }
            $program = Package::getPackage($slug);
            if (!empty($program)) {
                if (isset($program[0]['program_ids']) && !empty($program[0]['program_ids'])) {
                    return true;
                } else {
                    return false;
                }
            }
            return true;
        });

        Validator::extend('checkpackageexists', function($attribute, $value, $parameters){
            $parameters[0] = preg_replace('/[^\w ]+/', '', $parameters[0]);
            $parameters[0] = strtolower(preg_replace('/ +/', '-', $parameters[0]));
            if (!empty($parameters[1])) {
                $parameters[1] = preg_replace('/[^\w ]+/', '', $parameters[1]);
                $parameters[1] = strtolower(preg_replace('/ +/', '-', $parameters[1]));
                $slug = "package" . '-' . $parameters[0] . '-' . $parameters[1];
            } else {
                $slug = "package" . '-' . $parameters[0];
            }
            $program = Package::getPackage($slug);
            if(!empty($program)){
                return true;
            }
            return false;
        });

        Validator::extend('usergroupexist', function ($attribute, $value, $parameters) {
            $parameters[0] = preg_replace('/[^\w ]+/', '', $parameters[0]);
            $parameters[0] = strtolower(preg_replace('/ +/', '-', $parameters[0]));
            if (!empty($parameters[1])) {
                $parameters[1] = preg_replace('/[^\w ]+/', '', $parameters[1]);
                $parameters[1] = strtolower(preg_replace('/ +/', '-', $parameters[1]));
                $slug = "package" . '-' . $parameters[0] . '-' . $parameters[1];
            } else {
                $slug = "package" . '-' . $parameters[0];
            }
            $program = Package::getPackage($slug);
            $usergroup = UserGroup::where('ug_name_lower', '=', strtolower($parameters[2]))
            ->where('status', '!=', 'DELETED')->get(['ugid'])->toArray();

            $packid = (isset($program[0]['package_id'])) ? $program[0]['package_id'] : ' ';
            $groupid = (isset($usergroup[0]['ugid'])) ? $usergroup[0]['ugid'] : ' ';

            $transactions = TransactionDetail::where('package_id', '=', (int)$packid)->where('id', '=', $groupid)
                            ->where('trans_level', '=', 'usergroup')
                            ->where('status', '=', 'COMPLETE')->get()->toArray();
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
                $slug = "package" . '-' . $parameters[0] . '-' . $parameters[1];
            } else {
                $slug = "package" . '-' . $parameters[0];
            }
            $program = Package::getPackage($slug);
            if (!empty($program)) {
                $returnval = Package::where('package_slug', '=', $slug)
                            ->where('status', '!=', 'DELETED')->get(['package_slug'])->toArray();
                if (!empty($returnval)) {
                    return true;
                }
                return false;
            }
            return true;    
        });

        Validator::extend('checkusergroup', function ($attribute, $value, $parameters) {
            $usergroup = strtolower($parameters[0]);
            $returnval = UserGroup::where('ug_name_lower', '=', $usergroup)
            ->where('status', '!=', 'DELETED')->get(['usergroup_name'])->toArray();
            if (!empty($returnval)) {
                return true;
            }
            return false;
        });

        Validator::extend('activeusergroup', function ($attribute, $value, $parameters) {
            $usergroup = strtolower($parameters[0]);
            $returnval = UserGroup::where('ug_name_lower', '=', $usergroup)
                        ->where('status', '=', 'IN-ACTIVE')
                        ->get(['usergroup_name'])->toArray();
            if (empty($returnval)) {
                return true;
            }
            return false;
        });

        Validator::extend('checkpackageshortname', function ($attribute, $value, $parameters) {
            if (empty(array_get($parameters, 1))) {
                $parameters[0] = preg_replace('/[^\w ]+/', '', array_get($parameters, 0)); 
                $parameters[0] = strtolower(preg_replace('/ +/', '-', $parameters[0])); 
                $slug = "package" . '-' . $parameters[0]; 
                $program_details = $this->packageService->getPackageByAttribute('title_lower', strtolower($value), PackageStatus::DELETED)->toArray();
                if (!empty($program_details)) {
                    $program = $this->packageService->getPackageByAttribute('package_slug', $slug, PackageStatus::DELETED)->toArray();
                    if (empty($program)) { 
                        return false; 
                    }
                    return true; 
                } 
                return true; 
            }
            return true; 
        });

        $messages = [
            'checkusergroup' => trans('admin/program.check_usergroup_exists'),
            'checkpackageexists' => trans('admin/program.check_package_exists'),
            'checkprogram' => trans('admin/program.check_program'),
            'min' => trans('admin/program.shortname'),
            'checkchannel' => trans('admin/program.check_channel'),
            'usergroupexist' => trans('admin/program.check_usergroup_package_mapping'),
            'activeusergroup' => trans('admin/program.check_active_usergroup'),
            'checkpackageshortname' => trans('admin/program.check_package_shortname'),
        ];

        return $this->customErpPackageValidate($csvrowData, $rules, $messages);
    }

    /**
     * Method to assign users to channels
     * @return nothing
     */
    public function getEnrolUserToChannel()
    {   
        try {
            if (config('app.ftp_enabled')) {
                $connect = UserLog::getValidateFtp();
                if ($connect) {
                    $ftp_connection_details = $this->programservice->getFtpConnectionDetails();
                    if (in_array('add', $ftp_connection_details['dir_list'])) {
                        $ftp_dir_details = $this->programservice->getFtpDirUserToChannelDetails($ftp_connection_details);
                        if (in_array($ftp_dir_details['local_file'], $ftp_dir_details['file_list'])) {
                            $csv_file_data = $this->programservice->getUserToChannelFileColumns($ftp_connection_details, $ftp_dir_details);
                            /*---Validating headers ends here---*/
                            if ($csv_file_data['count'] > 0) {
                                /*start of log*/
                                $message = trans('admin/program.invalid_headers');
                                $logdata['error_msgs'] = trans('admin/program.invalid_headers');
                                $this->programservice->postUserChannelLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-user-to-channel-failure-template', $action = 'ADD', $cron = 0);
                                /*end of log*/
                            } else {
                                $i = 0;
                                foreach ($csv_file_data['csvFile'] as $line) {
                                    $data = str_getcsv($line);
                                    $i++;
                                    if ($i > 1) {
                                        $valid_data = $this->programservice->validateErpEnrolRules(array_combine(array_get($csv_file_data, 'head', []), $data));
                                        if ($valid_data) {
                                            $error_msg = '';
                                            foreach ($valid_data->all() as $k) {
                                                $error_msg .= $k;

                                            }
                                            /*---Insert data in erp enrol log collection with failure---*/
                                            $logdata = array_combine($csv_file_data['head'], $data);
                                            $logdata['error_msgs'] = $error_msg;
                                            $logdata['enrol_level'] = 'user';
                                            $logdata['action'] = 'ADD';
                                            $record = $this->programservice->getErpLogdata($logdata, $type = 'single', $status = 'FAILURE', $cron = 0);
                                            EnrolLog::InsertErpEnrolLog($record);
                                        } else {
                                            $rowdata = array_combine($csv_file_data['head'], $data);
                                            $programid = $this->programservice->getProgramId($rowdata['name'], $rowdata['shortname'], $type = 'content_feed', $subtype = 'single');
                                            $userid = User::where('username', '=', strtolower($rowdata['username']))->value('uid');
                                            /*---Insert data in program,users,transactions,transaction_details collection---*/
                                            $this->programservice->enrolUserToProgram($programid, $userid, $rowdata['name'], $rowdata['shortname'], $type = 'content_feed', $subtype = 'single', $level = 'user', $cron = 0);
                                            /*---Insert data in erp enrol log collection with success---*/

                                            event(
                                                new EntityEnrollmentByAdminUser(
                                                    $userid,
                                                    UserEntity::PROGRAM,
                                                    $programid
                                                )
                                            );

                                            $program_context_data = $this->roleService->getContextDetails(Contexts::PROGRAM);
                                            $learner_role = $this->roleService->getRoleDetails(SystemRoles::LEARNER);

                                            $this->roleService->mapUserAndRole(
                                                $userid,
                                                $program_context_data["id"],
                                                $learner_role["id"],
                                                $programid
                                            );

                                            $input['uid'] = $userid;
                                            $input['program_id'] = $programid;
                                            $input['enrol_level'] = 'user';
                                            $input['action'] = 'ADD';
                                            $record = $this->programservice->getErpLogdata($input, $type = 'single', $status = 'SUCCESS', $cron = 0);
                                            EnrolLog::InsertErpEnrolLog($record);
                                        }
                                    }
                                }
                                $this->programservice->enrolUserToChannelEmail($status = 'SUCCESS', 'erp-user-to-channel-success-template', '', 'ADD');
                                $message = trans('admin/program.cron_success');
                            }
                            //process records ends here
                            $new_file = $this->programservice->getFileName($ftp_dir_details['local_file'], $action = 'add');
                            rename($ftp_connection_details['path'] . $ftp_dir_details['local_file'], $ftp_connection_details['path'] . $new_file);
                        } else {
                            $message = trans('admin/program.invalid_file');
                            ftp_close($ftp_connection_details['conn_id']); //ftp connection close
                            $logdata['error_msgs'] = trans('admin/program.invalid_file');
                            $this->programservice->postUserChannelLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-user-to-channel-failure-template', $action = 'ADD', $cron = 0);
                        }
                    } else {
                        $message = trans('admin/program.invalid_add_dir');
                        ftp_close($ftp_connection_details['conn_id']); //ftp connection close
                        $logdata['error_msgs'] = trans('admin/program.invalid_add_dir');
                        $this->programservice->postUserChannelLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-user-to-channel-failure-template', $action = 'ADD', $cron = 0);
                    }
                } else {
                    $message = trans('admin/program.invalid_ftp');
                    $logdata['error_msgs'] = trans('admin/program.invalid_ftp');
                    $this->programservice->postUserChannelLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-user-to-channel-failure-template', $action = 'ADD', $cron = 0);
                }
            } else {
                $message = trans('admin/program.cron_ftp_disabled');    
            }
            $this->layout->pagetitle = trans('admin/program.import_user_channel_cron');
            $this->layout->pageicon = 'fa fa-cloud-download';
            $this->layout->pagedescription = trans('admin/program.import_user_channel_cron');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->content = view('admin.theme.programs.import_cron')->with('message', $message);
            $this->layout->footer = view('admin.theme.common.footer');
        } catch (Exception $e) {
            Log::error($e->getFile(). trans('admin/program.error_line', ['error_line_no'=> $e->getLine()]).trans('admin/program.message', ['error_message' => $e->getMessage()]));
            $this->layout->pagetitle = trans('admin/program.import_user_channel_cron');
            $this->layout->pageicon = 'fa fa-cloud-download';
            $this->layout->pagedescription = trans('admin/program.import_user_channel_cron');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->content = view('admin.theme.programs.import_cron')->with('message', $e->getMessage());
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }

    public function getEnrolUsergroupToPackage()
    {   
        try {
            if (config('app.ftp_enabled')) {
                $connect = UserLog::getValidateFtp();
                if ($connect) {
                    $ftp_connection_details = $this->programservice->getFtpConnectionDetails();
                    $ftp_file_path = array_get($ftp_connection_details, 'path', "");
                    $ftp_connection_id = array_get($ftp_connection_details, 'conn_id', "");
                    if (in_array('add', array_get($ftp_connection_details, 'dir_list', ""))) {
                        $ftp_dir_details = $this->programservice->getFtpDirUsergroupToPackageDetails($ftp_connection_details);
                        $ftp_local_file = array_get($ftp_dir_details, 'local_file', "");
                        if (in_array($ftp_local_file, array_get($ftp_dir_details, 'file_list', ""))) {
                            $csv_file_data = $this->programservice->getUsergroupToPackageFileColumns($ftp_connection_details, $ftp_dir_details);
                            /*---Validating headers ends here---*/
                            if (array_get($csv_file_data, 'count', "") > 0) {
                                $message = trans('admin/program.invalid_headers');
                                $logdata['error_msgs'] = trans('admin/program.invalid_headers');
                                $this->packageService->postUsergroupPackLog($logdata, 'FAILURE', 'erp-usergroup-to-package-failure-template', 'ADD', 0);
                            } else {
                                $i = 0;
                                foreach (array_get($csv_file_data, 'csvFile', []) as $line) {
                                    $data = str_getcsv($line);
                                    $i++;
                                    if ($i > 1) {
                                        $csv_file_head = array_get($csv_file_data, 'head', []);
                                        $valid_data = $this->validateErpPackageEnrolRules(array_combine($csv_file_head, $data));
                                        if ($valid_data) {
                                            $error_msg = '';
                                            foreach ($valid_data->all() as $k) {
                                                $error_msg .= $k;

                                            }
                                            /*---Insert data in erp enrol log collection with failure---*/
                                            $logdata = array_combine($csv_file_head, $data);
                                            $logdata['error_msgs'] = $error_msg;
                                            $logdata['enrol_level'] = 'usergroup';
                                            $logdata['action'] = 'ADD';
                                            $record = $this->packageService->getErpLogdata($logdata, 'FAILURE', 0);
                                            PackageEnrolLog::InsertErpEnrolLog($record);
                                        } else {
                                            $rowdata = array_combine($csv_file_head, $data);

                                            $packageid = $this->packageService->getPackageId(array_get($rowdata, 'packagename', ""), array_get($rowdata, 'packageshortname', ""));

                                            $usergroupid = UserGroup::where('ug_name_lower', '=', strtolower(array_get($rowdata, 'usergroup', "")))->value('ugid');
                                            /*---Insert data in program,users,transactions,transaction_details collection---*/
                                            $this->packageService->enrolUsergroupToPackage($packageid, $usergroupid, array_get($rowdata, 'packagename', ""), array_get($rowdata, 'packageshortname', ""), 'usergroup', 0);

                                            $packageData = Package::getPackageDetailsByID($packageid);
                                            $userGroupDetails = UserGroup::where('ugid', $usergroupid)->first();
                                            $program_ids = array_get($userGroupDetails, 'program_ids', []);

                                            $user_usergroup_rel_ids = array_get($userGroupDetails, 'relations.active_user_usergroup_rel', []);

                                            $role_info = $this->roleService->getRoleDetails(SystemRoles::LEARNER, ['context']);
                                            $context_info = $this->roleService->getContextDetails(Contexts::PROGRAM, false);
                                            $role_id = array_get($role_info, 'id', '');
                                            $context_id = array_get($context_info, 'id', '');

                                            if (!empty($user_usergroup_rel_ids)) {
                                                foreach ($user_usergroup_rel_ids as $user_id) {
                                                    event(
                                                        new EntityEnrollmentThroughUserGroup(
                                                            $user_id,
                                                            UserEntity::PACKAGE,
                                                            $packageid,
                                                            $usergroupid
                                                        )
                                                    );

                                                    if (!empty($program_ids)) {
                                                        foreach ($program_ids as $channel_id) {
                                                            $this->roleService->mapUserAndRole((int)$user_id, $context_id, $role_id, $channel_id);
                                                        }
                                                    }
                                                }
                                            }

                                            /*---Insert data in erp enrol log collection with success---*/
                                            $input['uid'] = $usergroupid;
                                            $input['program_id'] = $packageid;
                                            $input['enrol_level'] = 'usergroup';
                                            $input['action'] = 'ADD';
                                            $record = $this->packageService->getErpLogdata($input, 'SUCCESS', 0);
                                            PackageEnrolLog::InsertErpEnrolLog($record);
                                        }
                                    }
                                }
                                $this->packageService->enrolUsergroupToPackageEmail('SUCCESS', 'erp-usergroup-to-package-success-template', '', 'ADD');
                                $message = trans('admin/program.cron_success');
                            }
                            //process records ends here
                            $new_file = $this->packageService->getFileName($ftp_local_file, 'add');
                            rename($ftp_file_path . $ftp_local_file, $ftp_file_path . $new_file);
                        } else {
                            ftp_close($ftp_connection_id); //ftp connection close
                            $message = trans('admin/program.invalid_file');
                            $logdata['error_msgs'] = trans('admin/program.invalid_file');
                            $this->packageService->postUsergroupPackLog($logdata, 'FAILURE', 'erp-usergroup-to-package-failure-template', 'ADD', 0);
                        }
                    } else {
                        ftp_close($ftp_connection_id); //ftp connection close
                        $message = trans('admin/program.invalid_add_dir');
                        $logdata['error_msgs'] = trans('admin/program.invalid_add_dir');
                        $this->packageService->postUsergroupPackLog($logdata, 'FAILURE', 'erp-usergroup-to-package-failure-template', 'ADD', 0);
                    }
                } else {
                    $message = trans('admin/program.invalid_ftp');
                    $logdata['error_msgs'] = trans('admin/program.invalid_ftp');
                    $this->packageService->postUsergroupPackLog($logdata, 'FAILURE', 'erp-usergroup-to-package-failure-template', 'ADD', 0);
                }
                $this->layout->pagetitle = trans('admin/program.import_ug_package_cron');
                $this->layout->pageicon = 'fa fa-cloud-download';
                $this->layout->pagedescription = trans('admin/program.import_ug_package_cron');
                $this->layout->header = view('admin.theme.common.header');
                $this->layout->content = view('admin.theme.programs.import_cron')->with('message', $message);
                $this->layout->footer = view('admin.theme.common.footer');
            } else {
                $message = trans('admin/program.cron_ftp_disabled');
            }
                $this->layout->pagetitle = trans('admin/program.import_ug_package_cron');
                $this->layout->pageicon = 'fa fa-cloud-download';
                $this->layout->pagedescription = trans('admin/program.import_ug_package_cron');
                $this->layout->header = view('admin.theme.common.header');
                $this->layout->content = view('admin.theme.programs.import_cron')->with('message', $message);
                $this->layout->footer = view('admin.theme.common.footer');
        } catch (Exception $e) {
            Log::error($e->getFile(). trans('admin/program.error_line', ['error_line_no'=> $e->getLine()]).trans('admin/program.message', ['error_message' => $e->getMessage()]));
            $this->layout->pagetitle = trans('admin/program.import_ug_package_cron');
            $this->layout->pageicon = 'fa fa-cloud-download';
            $this->layout->pagedescription = trans('admin/program.import_ug_package_cron');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->content = view('admin.theme.programs.import_cron')->with('message', $e->getMessage());
            $this->layout->footer = view('admin.theme.common.footer');
        }    
    }

    /**
     * Method to validate update program metadata
     * @param array $csvrowData
     * @param string $type
     * @return validation messages
     */
    public function validateUpdateProgramRules($csvrowData, $type = 'collection')
    {
        if ($type == 'collection') {
            $message['checkslugregex'] = trans('admin/program.pack_check_slug_regex');
            $message['checkslug'] = trans('admin/program.channel_invalid');
            $message['checkshortnameregex'] = trans('admin/program.pack_check_shortname_regex');
            $message['checknewslugregex'] = trans('admin/program.pack_check_new_slug_regex');
            $message['checknewslug'] = trans('admin/program.channel_check_new_slug');
            $message['checknewshortnameregex'] = trans('admin/program.pack_check_new_shortname_regex');
            $message['datecheck'] = trans('admin/program.pack_date_check');
            $message['displaystartdatecheck'] = trans('admin/program.pack_disp_start_date_great_than_start_date');
            $message['displaydatecheck'] = trans('admin/program.pack_disp_end_date_greater_than_disp_start_date');
            $message['displayenddatecheck'] = trans('admin/program.pack_disp_end_date_less_than_end_date');
        } else {
            $message['checkslugregex'] = trans('admin/program.channel_check_slug_regex');
            $message['checkslug'] = trans('admin/program.check_program');
            $message['checkshortnameregex'] = trans('admin/program.channel_check_shortname_regex');
            $message['checknewslugregex'] = trans('admin/program.channel_check_new_slug_regex');
            $message['checknewslug'] = trans('admin/program.channel_check_new_slug');
            $message['checknewshortnameregex'] = trans('admin/program.channel_check_new_shortname_regex');
            $message['datecheck'] = trans('admin/program.date_check');
            $message['displaystartdatecheck'] = trans('admin/program.disp_start_date_great_than_start_date');
            $message['displaydatecheck'] = trans('admin/program.disp_end_date_greater_than_disp_start_date');
            $message['displayenddatecheck'] = trans('admin/program.disp_end_date_less_than_end_date');
        }

        $slug = $this->programservice->generateProgramSlug($csvrowData['name'], $csvrowData['shortname']);
        $program = Program::where('program_slug', '=', $slug)->first();
        if (empty($program)) {
            $rules['name'] = 'Required|checkslugregex:' . $csvrowData['name'] . '|checkslug:' . $csvrowData['name'] . ',' . $csvrowData['shortname'] . '';
            if (array_key_exists('shortname', $csvrowData)) {
                $rules['shortname'] = 'min:3|checkshortnameregex:' . $csvrowData['shortname'] . '';
            }
        } else {
            $start_date = (array_key_exists('startdate', $csvrowData)) ? $csvrowData['startdate'] : Timezone::convertFromUTC('@' . $program['program_startdate'], Auth::user()->timezone, config('app.date_format'));
            $end_date = (array_key_exists('enddate', $csvrowData)) ? $csvrowData['enddate'] : Timezone::convertFromUTC('@' . $program['program_enddate'], Auth::user()->timezone, config('app.date_format'));
            $display_start_date = (array_key_exists('displaystartdate', $csvrowData)) ? $csvrowData['displaystartdate'] : Timezone::convertFromUTC('@' . $program['program_display_startdate'], Auth::user()->timezone, config('app.date_format'));
            $display_end_date = (array_key_exists('displayenddate', $csvrowData)) ? $csvrowData['displayenddate'] : Timezone::convertFromUTC('@' . $program['program_display_enddate'], Auth::user()->timezone, config('app.date_format'));

            $rules['name'] = 'Required|checkslugregex:' . $csvrowData['name'] . '|checkslug:' . $csvrowData['name'] . ',' . $csvrowData['shortname'] . '';

            if (array_key_exists('shortname', $csvrowData)) {
                $rules['shortname'] = 'min:3|checkshortnameregex:' . $csvrowData['shortname'] . '';
            }
            if (array_key_exists('newname', $csvrowData)) {
                $rules['newname'] = 'Required|checknewslugregex:' . $csvrowData['newname'] . '|checknewslug:' . $csvrowData['newname'] . ',' . $csvrowData['newshortname'] . '';
            }
            if (array_key_exists('newshortname', $csvrowData)) {
                $rules['newshortname'] = 'min:3|checknewshortnameregex:' . $csvrowData['newshortname'] . '';
            }
            if (array_key_exists('startdate', $csvrowData)) {
                $rules['startdate'] = 'Required|date_format:d-m-Y|datecheck:' . $start_date . ',' . $end_date . '|displaystartdatecheck:' . $start_date . ',' . $display_start_date . '|displaydatecheck:' . $display_start_date . ',' . $display_end_date . '|displayenddatecheck:' . $end_date . ',' . $display_end_date . '';
            }
            if (array_key_exists('enddate', $csvrowData)) {
                $rules['enddate'] = 'Required|date_format:d-m-Y|datecheck:' . $start_date . ',' . $end_date . '|displaystartdatecheck:' . $start_date . ',' . $display_start_date . '|displaydatecheck:' . $display_start_date . ',' . $display_end_date . '|displayenddatecheck:' . $end_date . ',' . $display_end_date . '';
            }
            if (array_key_exists('displaystartdate', $csvrowData)) {
                $rules['displaystartdate'] = 'Required|date_format:d-m-Y|datecheck:' . $start_date . ',' . $end_date . '|displaystartdatecheck:' . $start_date . ',' . $display_start_date . '|displayenddatecheck:' . $end_date . ',' . $display_end_date . '|displaydatecheck:' . $display_start_date . ',' . $display_end_date . '';
            }
            if (array_key_exists('displayenddate', $csvrowData)) {
                $rules['displayenddate'] = 'Required|date_format:d-m-Y|datecheck:' . $start_date . ',' . $end_date . '|displaydatecheck:' . $display_start_date . ',' . $display_end_date . '|displayenddatecheck:' . $end_date . ',' . $display_end_date . '|displaystartdatecheck:' . $start_date . ',' . $display_start_date . '';
            }

        }

        $custom_fields = CustomFields::getUserCustomFieldArr('content_feed', $type, ['fieldname', 'fieldname', 'mark_as_mandatory']);
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $key => $values) {
                if ($values['mark_as_mandatory'] == 'yes' && array_key_exists($values['fieldname'], $csvrowData)) {
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
                $slug = "content-feed" . '-' . $parameters[0] . '-' . $parameters[1];
            } else {
                $slug = "content-feed" . '-' . $parameters[0];
            }

            $returnval = Program::where('program_slug', '=', $slug)->where('program_type', '=', 'content_feed')->where('status', '!=', 'DELETED')->get(['program_slug'])->toArray();
            if (empty($returnval)) {
                return false;
            }
            return true;
        });
        Validator::extend('checkslugregex', function ($attribute, $value, $parameters) {
            if (preg_match('/^[a-zA-Z0-9 _-]+$/', $parameters[0])) {
                return true;
            }
            return false;
        });
        Validator::extend('checknewslug', function ($attribute, $value, $parameters) {
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
        Validator::extend('checknewslugregex', function ($attribute, $value, $parameters) {
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
        Validator::extend('checknewshortnameregex', function ($attribute, $value, $parameters) {
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
        return $this->customErpPackageValidate($csvrowData, $rules, $message);
    }

    /**
     * Method to validate update program metadata
     * @param array $csvrowData
     * @param string $type
     * @return validation messages
     */
    public function validateUpdatePackageRules($csvrowData)
    {
        $message['checkslugregex'] = trans('admin/program.pack_check_slug_regex');
        $message['checkslug'] = trans('admin/program.channel_invalid');
        $message['checkshortnameregex'] = trans('admin/program.pack_check_shortname_regex');
        $message['checknewslugregex'] = trans('admin/program.pack_check_new_slug_regex');
        $message['checknewslug'] = trans('admin/program.channel_check_new_slug');
        $message['checknewshortnameregex'] = trans('admin/program.pack_check_new_shortname_regex');
        $message['datecheck'] = trans('admin/program.pack_date_check');
        $message['displaystartdatecheck'] = trans('admin/program.pack_disp_start_date_great_than_start_date');
        $message['displaydatecheck'] = trans('admin/program.pack_disp_end_date_greater_than_disp_start_date');
        $message['displayenddatecheck'] = trans('admin/program.pack_disp_end_date_less_than_end_date');

        $slug = $this->packageService->generatePackageSlug($csvrowData['name'], $csvrowData['shortname']);

        $program = Package::where('package_slug', '=', $slug)->first();
        if (empty($program)) {
            $rules['name'] = 'Required|checkslugregex:' . $csvrowData['name'] 
            . '|checkslug:' . $csvrowData['name'] . ',' . $csvrowData['shortname'] . '';
            if (array_key_exists('shortname', $csvrowData)) {
                $rules['shortname'] = 'min:3|checkshortnameregex:' . $csvrowData['shortname'] . '';
            }
        } else {
            $start_date = (array_key_exists('startdate', $csvrowData)) ? $csvrowData['startdate'] : 
            Timezone::convertFromUTC('@' . $program['package_startdate'], 
                Auth::user()->timezone, config('app.date_format'));
            $end_date = (array_key_exists('enddate', $csvrowData)) ? $csvrowData['enddate'] : 
            Timezone::convertFromUTC('@' . $program['package_enddate'], 
                Auth::user()->timezone, config('app.date_format'));
            $display_start_date = (array_key_exists('displaystartdate', $csvrowData)) ? 
            $csvrowData['displaystartdate'] : Timezone::convertFromUTC('@' . $program['package_display_startdate'], 
                Auth::user()->timezone, config('app.date_format'));
            $display_end_date = (array_key_exists('displayenddate', $csvrowData)) ? 
            $csvrowData['displayenddate'] : 
            Timezone::convertFromUTC('@' . $program['package_display_enddate'], 
                Auth::user()->timezone, config('app.date_format'));

            $rules['name'] = 'Required|checkslugregex:' . $csvrowData['name'] 
            . '|checkslug:' . $csvrowData['name'] . ',' . $csvrowData['shortname'] . '';

            if (array_key_exists('shortname', $csvrowData)) {
                $rules['shortname'] = 'min:3|checkshortnameregex:' . $csvrowData['shortname'] . '';
            }
            if (array_key_exists('newname', $csvrowData)) {
                $rules['newname'] = 'Required|checknewslugregex:' . $csvrowData['newname'] 
                . '|checknewslug:' . $csvrowData['newname'] . ',' . $csvrowData['newshortname'] . '';
            }
            if (array_key_exists('newshortname', $csvrowData)) {
                $rules['newshortname'] = 'min:3|checknewshortnameregex:' . $csvrowData['newshortname'] . '';
            }
            if (array_key_exists('startdate', $csvrowData)) {
                $rules['startdate'] = 'Required|date_format:d-m-Y|datecheck:' 
                . $start_date . ',' . $end_date . '|displaystartdatecheck:' 
                . $start_date . ',' . $display_start_date . '|displaydatecheck:' 
                . $display_start_date . ',' . $display_end_date . '|displayenddatecheck:' 
                . $end_date . ',' . $display_end_date . '';
            }
            if (array_key_exists('enddate', $csvrowData)) {
                $rules['enddate'] = 'Required|date_format:d-m-Y|datecheck:' 
                . $start_date . ',' . $end_date . '|displaystartdatecheck:' 
                . $start_date . ',' . $display_start_date . '|displaydatecheck:' 
                . $display_start_date . ',' . $display_end_date . '|displayenddatecheck:' 
                . $end_date . ',' . $display_end_date . '';
            }
            if (array_key_exists('displaystartdate', $csvrowData)) {
                $rules['displaystartdate'] = 'Required|date_format:d-m-Y|datecheck:' 
                . $start_date . ',' . $end_date . '|displaystartdatecheck:' . $start_date 
                . ',' . $display_start_date . '|displayenddatecheck:' . $end_date . ',' . $display_end_date 
                . '|displaydatecheck:' . $display_start_date . ',' . $display_end_date . '';
            }
            if (array_key_exists('displayenddate', $csvrowData)) {
                $rules['displayenddate'] = 'Required|date_format:d-m-Y|datecheck:' 
                . $start_date . ',' . $end_date . '|displaydatecheck:' 
                . $display_start_date . ',' . $display_end_date . '|displayenddatecheck:' 
                . $end_date . ',' . $display_end_date . '|displaystartdatecheck:' 
                . $start_date . ',' . $display_start_date . '';
            }

        }

        $custom_fields = CustomFields::getUserCustomFieldArr('content_feed', 'collection', ['fieldname', 'fieldname', 'mark_as_mandatory']);
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $key => $values) {
                if ($values['mark_as_mandatory'] == 'yes' && array_key_exists($values['fieldname'], $csvrowData)) {
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
                return false;
            }
            return true;
        });
        Validator::extend('checkslugregex', function ($attribute, $value, $parameters) {
            if (preg_match('/^[a-zA-Z0-9 _-]+$/', $parameters[0])) {
                return true;
            }
            return false;
        });
        Validator::extend('checknewslug', function ($attribute, $value, $parameters) {
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
        Validator::extend('checknewslugregex', function ($attribute, $value, $parameters) {
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
        Validator::extend('checknewshortnameregex', function ($attribute, $value, $parameters) {
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
        return $this->customErpPackageValidate($csvrowData, $rules, $message);
    }

    /**
     * Method to update packages
     * @return void
     */
    public function getErpUpdatePackages()
    {
        try {
            if (config('app.ftp_enabled')) {
                $connect = UserLog::getValidateFtp();
                if ($connect) {
                    $ftp_connection_details = $this->programservice->getFtpConnectionDetails();
                    $ftp_file_path = array_get($ftp_connection_details, 'path', "");
                    $ftp_connection_id = array_get($ftp_connection_details, 'conn_id', "");
                    if (in_array('update', array_get($ftp_connection_details, 'dir_list', ""))) {
                        $ftp_dir_details = $this->programservice->getFtpDirPackageUpdateDetails($ftp_connection_details);
                        $ftp_local_file = array_get($ftp_dir_details, 'local_file', "");
                        if (in_array($ftp_local_file, array_get($ftp_dir_details, 'file_list', ""))) {
                            $csv_file_data = $this->programservice->getUpdatePackageFileColumns($ftp_connection_details, $ftp_dir_details);
                            $csv_file_head = array_get($csv_file_data, 'head', []);
                            /*---Validating headers ends here---*/
                            if (array_get($csv_file_data, 'count', "") > 0 || !in_array('name', $csv_file_head) || !in_array('shortname', $csv_file_head) || (in_array('newname', $csv_file_head) && !in_array('newshortname', $csv_file_head))
                                || (in_array('newshortname', $csv_file_head) && !in_array('newname', $csv_file_head))
                            ) {
                                $message = trans('admin/program.invalid_headers');
                                $logdata['error_msgs'] = trans('admin/program.invalid_headers');
                                $this->packageService->postPackageLog($logdata, 'FAILURE', 'erp-package-update-failure-template', 'UPDATE', 0);
                            } else {
                                $i = 0;
                                foreach (array_get($csv_file_data, 'csvFile', []) as $line) {
                                    $data = str_getcsv($line);
                                    $i++;
                                    if ($i > 1) {
                                        $valid_data = $this->validateUpdatePackageRules(array_combine($csv_file_head, $data));
                                        if ($valid_data) {
                                            $error_msg = '';
                                            foreach ($valid_data->all() as $k) {
                                                $error_msg .= $k;
                                            }
                                            $logdata = array_combine($csv_file_head, $data);
                                            $logdata = $this->packageService->getMissingPackageFields($logdata, array_get($csv_file_data, 'fields', []));
                                            $logdata['error_msgs'] = $error_msg;
                                            $logdata['action'] = 'UPDATE';
                                            $record = $this->packageService->getErpLogdata($logdata, 'FAILURE', 0);
                                            PackageLog::getInsertErpPackageLog($record);
                                        } else {
                                            $program_data = array_combine($csv_file_head, $data);
                                            $old_slug = $this->packageService->generatePackageSlug(array_get($program_data, 'name', ""), array_get($program_data, 'shortname', ""));
                                            $new_slug = '';
                                            if (array_key_exists('newname', $program_data) && array_key_exists('newshortname', $program_data)) {
                                                $new_slug = $this->packageService->generatePackageSlug(array_get($program_data, 'newname', ""), array_get($program_data, 'newshortname', ""));
                                            }
                                            //update program
                                            $program_id = $this->packageService->updatePackageDetails($program_data, $old_slug, $new_slug, 0);
                                            //insert program success log
                                            $input['program_id'] = $program_id;
                                            $input['action'] = 'UPDATE';
                                            $record = $this->packageService->getErpLogdata($input, 'SUCCESS', 0);
                                            PackageLog::getInsertErpPackageLog($record);
                                            if (!empty($new_slug)) {
                                                //update transaction details slug
                                                TransactionDetail::where('program_slug', '=', $old_slug)->where('type', '=', 'content_feed')->update(['program_slug' => $new_slug]);
                                            }
                                        }
                                    }
                                }
                                $this->packageService->packageImportEmail('SUCCESS', 'erp-package-update-success-template', '', 'UPDATE');
                                $message = trans('admin/program.cron_success');
                            }
                            $new_file = $this->packageService->getFileName($ftp_local_file, 'update');
                            rename($ftp_file_path . $ftp_local_file, $ftp_file_path . $new_file);
                        } else {
                            ftp_close($ftp_connection_id);
                            $logdata['error_msgs'] = trans('admin/program.invalid_file');
                            $this->packageService->postPackageLog($logdata, 'FAILURE', 'erp-package-update-failure-template', 'UPDATE', 0);
                            $message = trans('admin/program.invalid_file');
                        }
                    } else {
                        ftp_close($ftp_connection_id);
                        $logdata['error_msgs'] = trans('admin/program.invalid_update_dir');
                        $this->packageService->postPackageLog($logdata, 'FAILURE', 'erp-package-update-failure-template', 'UPDATE', 0);
                        $message = trans('admin/program.invalid_update_dir');
                    }
                } else {
                    $logdata['error_msgs'] = trans('admin/program.invalid_ftp');
                    $this->packageService->postPackageLog($logdata, 'FAILURE', 'erp-package-update-failure-template', 'UPDATE', 0);
                    $message = trans('admin/program.invalid_ftp');
                }
            } else {
                $message = trans('admin/program.cron_ftp_disabled'); 
            }
            $this->layout->pagetitle = trans('admin/program.package_update_cron');
            $this->layout->pageicon = 'fa fa-cloud-download';
            $this->layout->pagedescription = trans('admin/program.package_update_cron');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->content = view('admin.theme.programs.import_cron')->with('message', $message);
            $this->layout->footer = view('admin.theme.common.footer');
        } catch (Exception $e) {
            Log::error($e->getFile(). trans('admin/program.error_line', ['error_line_no'=> $e->getLine()]).trans('admin/program.message', ['error_message' => $e->getMessage()]));
            $this->layout->pagetitle = trans('admin/program.package_update_cron');
            $this->layout->pageicon = 'fa fa-cloud-download';
            $this->layout->pagedescription = trans('admin/program.package_update_cron');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->content = view('admin.theme.programs.import_cron')->with('message', $e->getMessage());
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }

    /**
     * Method to update channels
     * @returns nothing updates channel metadata
     */
    public function getErpUpdateChannels()
    {   
        try {
            if (config('app.ftp_enabled')) {
                $connect = UserLog::getValidateFtp();
                if ($connect) {
                    $ftp_connection_details = $this->programservice->getFtpConnectionDetails();
                    if (in_array('update', $ftp_connection_details['dir_list'])) {
                        $ftp_dir_details = $this->programservice->getFtpDirChannelUpdateDetails($ftp_connection_details);
                        if (in_array($ftp_dir_details['local_file'], $ftp_dir_details['file_list'])) {
                            $csv_file_data = $this->programservice->getUpdateChannelFileColumns($ftp_connection_details, $ftp_dir_details);
                            /*---Validating headers ends here---*/
                            if ($csv_file_data['count'] > 0 || !in_array('name', $csv_file_data['head']) || !in_array('shortname', $csv_file_data['head']) || (in_array('newname', $csv_file_data['head']) && !in_array('newshortname', $csv_file_data['head']))
                                || (in_array('newshortname', $csv_file_data['head']) && !in_array('newname', $csv_file_data['head']))
                            ) {
                                $message = trans('admin/program.invalid_headers');
                                $logdata['error_msgs'] = trans('admin/program.invalid_headers');
                                $this->programservice->postProgramLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-channel-update-failure-template', $action = 'UPDATE', $cron = 0);
                            } else {
                                $i = 0;
                                foreach ($csv_file_data['csvFile'] as $line) {
                                    $data = str_getcsv($line);
                                    $i++;
                                    if ($i > 1) {
                                        $valid_data = $this->validateUpdateProgramRules(array_combine($csv_file_data['head'], $data), 'single');
                                        if ($valid_data) {
                                            $error_msg = '';
                                            foreach ($valid_data->all() as $k) {
                                                $error_msg .= $k;
                                            }
                                            $logdata = array_combine($csv_file_data['head'], $data);
                                            $logdata = $this->programservice->getMissingProgramFields($logdata, $csv_file_data['fields']);
                                            $logdata['error_msgs'] = $error_msg;
                                            $logdata['action'] = 'UPDATE';
                                            $record = $this->programservice->getErpLogdata($logdata, $type = 'single', $status = 'FAILURE', $cron = 0);
                                            ProgramLog::getInsertErpProgramLog($record);
                                        } else {
                                            $program_data = array_combine($csv_file_data['head'], $data);
                                            $old_slug = $this->programservice->generateProgramSlug($program_data['name'], $program_data['shortname']);
                                            $new_slug = '';
                                            if (array_key_exists('newname', $program_data) && array_key_exists('newshortname', $program_data)) {
                                                $new_slug = $this->programservice->generateProgramSlug($program_data['newname'], $program_data['newshortname']);
                                            }
                                            //update program
                                            $program_id = $this->programservice->updateProgramDetails($program_data, $old_slug, $new_slug, $cron = 0);
                                            //insert program success log
                                            $input['program_id'] = $program_id;
                                            $input['action'] = 'UPDATE';
                                            $record = $this->programservice->getErpLogdata($input, $type = 'single', $status = 'SUCCESS', $cron = 0);
                                            ProgramLog::getInsertErpProgramLog($record);
                                            if (!empty($new_slug)) {
                                                //update packet slug
                                                Packet::where('feed_slug', '=', $old_slug)->update(['feed_slug' => $new_slug]);
                                                //update transaction details slug
                                                TransactionDetail::where('program_slug', '=', $old_slug)->where('type', '=', 'content_feed')->update(['program_slug' => $new_slug]);
                                            }
                                        }
                                    }
                                }
                                $this->programservice->channelImportEmail($status = 'SUCCESS', $slug = 'erp-channel-update-success-template', '', $action = 'UPDATE');
                                $message = trans('admin/program.cron_success');
                            }
                            $new_file = $this->programservice->getFileName($ftp_dir_details['local_file'], $action = 'update');
                            rename($ftp_connection_details['path'] . $ftp_dir_details['local_file'], $ftp_connection_details['path'] . $new_file);
                        } else {
                            ftp_close($ftp_connection_details['conn_id']);
                            $logdata['error_msgs'] = trans('admin/program.invalid_file');
                            $this->programservice->postProgramLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-channel-update-failure-template', $action = 'UPDATE', $cron = 0);
                            $message = trans('admin/program.invalid_file');
                        }
                    } else {
                        ftp_close($ftp_connection_details['conn_id']);
                        $logdata['error_msgs'] = trans('admin/program.invalid_update_dir');
                        $this->programservice->postProgramLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-channel-update-failure-template', $action = 'UPDATE', $cron = 0);
                        $message = trans('admin/program.invalid_update_dir');
                    }
                } else {
                    $logdata['error_msgs'] = trans('admin/program.invalid_ftp');
                    $this->programservice->postProgramLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-channel-update-failure-template', $action = 'UPDATE', $cron = 0);
                    $message = trans('admin/program.invalid_ftp');
                }
            } else {
                $message = trans('admin/program.cron_ftp_disabled');
            }
            $this->layout->pagetitle = trans('admin/program.channel_update_cron');
            $this->layout->pageicon = 'fa fa-cloud-download';
            $this->layout->pagedescription = trans('admin/program.channel_update_cron');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->content = view('admin.theme.programs.import_cron')->with('message', $message);
            $this->layout->footer = view('admin.theme.common.footer');
        } catch (Exception $e) {
            Log::error($e->getFile(). trans('admin/program.error_line', ['error_line_no'=> $e->getLine()]).trans('admin/program.message', ['error_message' => $e->getMessage()]));
            $this->layout->pagetitle = trans('admin/program.channel_update_cron');
            $this->layout->pageicon = 'fa fa-cloud-download';
            $this->layout->pagedescription = trans('admin/program.channel_update_cron');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->content = view('admin.theme.programs.import_cron')->with('message', $e->getMessage());
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }
    /**
     * Method to load package import report
     * @return view page of report
     */
    public function getPackageImportReport()
    {
        $crumbs = [
            'Dashboard' => 'cp',
            trans('admin/user.import_users_in_bulk') . ' Reports' => '',
            trans('admin/program.package') . ' ' . trans('admin/program.import_report') => '',
        ];
        // $view_report = Common::checkPermission('admin', 'bulkimportreports', 'view-import-reports');
        // $permissions = Common::getPermissions('admin', 'bulkimportreports');
        // if (!$view_report) {
        //     return parent::getAdminError($this->theme_path);
        // }
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/user.import_users_in_bulk') . ' Reports';
        $this->layout->pageicon = 'fa fa-bar-chart-o';
        $this->layout->pagedescription = trans('admin/user.import_users_in_bulk') . ' Reports';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'import-reports')
            ->with('submenu', 'package-import-report');
        $this->layout->content = view('admin.theme.programs.packagereport');
        // ->with('permissions', $permissions);
        $this->layout->footer = view('admin.theme.common.footer');
    }
    /**
     * Method to list logs of import packages
     * @return log records of packages
     */
    public function getPackageImportList()
    {
        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];

         if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
             if ($order_by[0]['column'] == '1') {
                 $orderByArray = ['program_title' => $order_by[0]['dir']];
             }

            if ($order_by[0]['column'] == '2') {
                $orderByArray = ['created_at' => 'desc'];
            }

           if ($order_by[0]['column'] == '3') {
                 $orderByArray = ['program_enddate' => $order_by[0]['dir']];
             }

           if ($order_by[0]['column'] == '4' || $order_by[0]['column'] == '8') {
                 $orderByArray = ['status' => $order_by[0]['dir']];
              }
         }

       
        if (isset($search['value'])) {
            $searchKey = $search['value'];
        }

        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }

        $filter = Input::get('filter');
        $filters = Input::get('filters');

        if (!in_array($filter, ['SUCCESS', 'FAILURE'])) {
            $filter = 'ALL';
        } else {
            $filter = strtoupper($filter);
        }
        if (!in_array($filters, ['ADD', 'UPDATE'])) {
            $filters = 'ALL';
        } else {
            $filters = strtoupper($filters);
        }
        $created_date = Input::get('created_date');

        if ($searchKey != '') {
            $filter = 'ALL';
            $created_date = '';
        }
        $totalRecords = PackageLog::getPackageImportCount('ALL', null, null, 'ALL');
        $filteredRecords = PackageLog::getPackageImportCount($filter, $searchKey, $created_date, $filters);
        $filtereddata = PackageLog::getPackageImportRecords($filter, $start, $limit, $orderByArray, $searchKey, $created_date, $filters);

        $totalRecords = $filteredRecords;
        $dataArr = [];
        foreach ($filtereddata as $key => $value) {
            if ($value['status'] == 'SUCCESS') {
                $program = Package::getPackageDetailsByID($value['program_id']);
                $title = $program['package_title'];
                $shortname = $program['package_shortname'];
                $startdate = Timezone::convertFromUTC('@' . $program['package_startdate'], Auth::user()->timezone, config('app.date_format'));
                $enddate = Timezone::convertFromUTC('@' . $program['package_enddate'], Auth::user()->timezone, config('app.date_format'));
                $displaystartdate = Timezone::convertFromUTC('@' . $program['package_display_startdate'], Auth::user()->timezone, config('app.date_format'));
                $displayenddate = Timezone::convertFromUTC('@' . $program['package_display_enddate'], Auth::user()->timezone, config('app.date_format'));
                $error = 'N/A';
            } else {
                $title = $value['name'];
                $shortname = $value['shortname'];
                $startdate = $value['startdate'];
                $enddate = $value['enddate'];
                $displaystartdate = $value['displaystartdate'];
                $displayenddate = $value['displayenddate'];
                $error = $value['error_msgs'];
            }
            $date = Timezone::convertFromUTC($value['created_at'], Auth::user()->timezone, config('app.date_format'));
            $temparr = [$title, $shortname, $startdate, $enddate, $displaystartdate, $displayenddate, $error, $date, $value['status'], $value['action']];
            $dataArr[] = $temparr;
        }
        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr,
        ];

        return response()->json($finaldata);
    }
    /**
     * Method to download update channel/package csv file
     * @returns update channel/package csv file with fields as headers
     */
    public function getImportUpdateProgramTemplate($type, $subtype, $action)
    {
        $this->programservice->downloadProgramTemplate($type, $subtype, $action);
    }
    /**
     * Method to download program template
     * @param string $program_type
     * @param string $program_sub_type
     * @param string $action
     * @return downloads sample csv file
     */
    public function getImportAddProgramTemplate($program_type, $program_sub_type, $action)
    {
        $data = [];
        $data[] = 'name*';
        $data[] = 'shortname';
        $data[] = 'description';
        $data[] = 'startdate*';
        $data[] = 'enddate*';
        $data[] = 'displaystartdate*';
        $data[] = 'displayenddate*';
        if (config('app.ecommerce')) {
            $data[] = 'sellable*';
        }
        $data[] = 'keywords';
        if ($program_type == 'content_feed' && $program_sub_type == 'single') {
            $data[] = 'packagename';
            $data[] = 'packageshortname';
        }
        $file = ($program_sub_type == 'single') ? config('app.channel_import_file') : config('app.package_import_file');
        $custom_fields = CustomFields::getUserCustomFieldArr($program_type, $program_sub_type, ['fieldname', 'fieldname', 'mark_as_mandatory']);
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom) {
                if ($custom['mark_as_mandatory'] == 'yes') {
                    $data[] = $custom['fieldname'] . '*';
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
     * Method to export packages
     * @param string $program_type
     * @param string $program_sub_type
     * @param string $status
     * @param string $date
     * @return csv file of packages
     */
    public function getPackExport($program_type, $program_sub_type, $status = 'ALL', $action = 'ALL', $date = null)
    {
        $this->programservice->exportPrograms($program_type, $program_sub_type, $status, $date, $action);
    }

    /**
     * Method to export packages
     * @param string $program_type
     * @param string $program_sub_type
     * @param string $status
     * @param string $date
     * @return csv file of packages
     */
    public function getPackageExport($status = 'ALL', $action = 'ALL', $date = null)
    {
        $this->packageService->exportPackages($status, $date, $action);
    }

    /**
     * Method to load channel import report
     * @return view page of report
     */
    public function getChannelImportReport()
    {
        $crumbs = [
            'Dashboard' => 'cp',
            trans('admin/user.import_users_in_bulk') . ' Reports' => '',
            trans('admin/program.channel') . ' ' . trans('admin/program.import_report') => '',
        ];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/user.import_users_in_bulk') . ' Reports';
        $this->layout->pageicon = 'fa fa-bar-chart-o';
        $this->layout->pagedescription = trans('admin/user.import_users_in_bulk') . ' Reports';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'import-reports')
            ->with('submenu', 'channel-import-report');
        $this->layout->content = view('admin.theme.programs.channelreport');
        $this->layout->footer = view('admin.theme.common.footer');
    }
     /**
     * Method to return import log of channels
     * @returns log records of channel
     */
    public function getChannelImportList()
    {
        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
             if ($order_by[0]['column'] == '1') {
                 $orderByArray = ['program_title' => $order_by[0]['dir']];
             }

            if ($order_by[0]['column'] == '2') {
                $orderByArray = ['created_at' => 'desc'];
            }

           if ($order_by[0]['column'] == '3') {
                 $orderByArray = ['program_enddate' => $order_by[0]['dir']];
             }

           if ($order_by[0]['column'] == '4' || $order_by[0]['column'] == '8') {
                 $orderByArray = ['status' => $order_by[0]['dir']];
             }
       }

        
        if (isset($search['value'])) {
            $searchKey = $search['value'];
        }

        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }

        $filter = Input::get('filter');
        $filters = Input::get('filters');

        if (!in_array($filter, ['SUCCESS', 'FAILURE'])) {
            $filter = 'ALL';
        } else {
            $filter = strtoupper($filter);
        }
        if (!in_array($filters, ['ADD', 'UPDATE'])) {
            $filters = 'ALL';
        } else {
            $filters = strtoupper($filters);
        }
        $created_date = Input::get('created_date');

        if ($searchKey != '') {
            $filter = 'ALL';
            $created_date = '';
        }
        $totalRecords = ProgramLog::getPackageImportCount('ALL', null, null, 'single', 'ALL');
        $filteredRecords = ProgramLog::getPackageImportCount($filter, $searchKey, $created_date, 'single', $filters);
        $filtereddata = ProgramLog::getPackageImportRecords($filter, $start, $limit, $orderByArray, $searchKey, $created_date, 'single', $filters);

        $totalRecords = $filteredRecords;
        $dataArr = [];
        foreach ($filtereddata as $key => $value) {
            if ($value['status'] == 'SUCCESS') {
                $program = Program::getProgramDetailsByID($value['program_id']);
                $title = $program['program_title'];
                $shortname = $program['program_shortname'];
                $startdate = Timezone::convertFromUTC('@' . $program['program_startdate'], Auth::user()->timezone, config('app.date_format'));
                $enddate = Timezone::convertFromUTC('@' . $program['program_enddate'], Auth::user()->timezone, config('app.date_format'));
                $displaystartdate = Timezone::convertFromUTC('@' . $program['program_display_startdate'], Auth::user()->timezone, config('app.date_format'));
                $displayenddate = Timezone::convertFromUTC('@' . $program['program_display_enddate'], Auth::user()->timezone, config('app.date_format'));
                $error = 'N/A';
            } else {
                $title = $value['name'];
                $shortname = $value['shortname'];
                $startdate = $value['startdate'];
                $enddate = $value['enddate'];
                $displaystartdate = $value['displaystartdate'];
                $displayenddate = $value['displayenddate'];
                $error = $value['error_msgs'];
            }
            $date = Timezone::convertFromUTC($value['created_at'], Auth::user()->timezone, config('app.date_format'));
            $temparr = [$title, $shortname, $startdate, $enddate, $displaystartdate, $displayenddate, $error, $date, $value['status'], $value['action']];
            $dataArr[] = $temparr;
        }
        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr,
        ];

        return response()->json($finaldata);
    }
    /**
     * Method to load ug to package import report
     * @return view page of report
     */
    public function getUsergroupPackageReport()
    {
        $crumbs = [
            'Dashboard' => 'cp',
            trans('admin/user.import_users_in_bulk') . ' Reports' => '',
            trans('admin/program.ug_to_package') => '',
        ];
        // $view_report = Common::checkPermission('admin', 'bulkimportreports', 'view-import-reports');
        // $permissions = Common::getPermissions('admin', 'bulkimportreports');
        // if (!$view_report) {
        //     return parent::getAdminError($this->theme_path);
        // }
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/user.import_users_in_bulk') . ' Reports';
        $this->layout->pageicon = 'fa fa-bar-chart-o';
        $this->layout->pagedescription = trans('admin/user.import_users_in_bulk') . ' Reports';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'import-reports')
            ->with('submenu', 'usergroup-package-report');
        $this->layout->content = view('admin.theme.programs.usergroup_package_report');
        // ->with('permissions', $permissions);
        $this->layout->footer = view('admin.theme.common.footer');

    }
    /**
     * Method to list logs of package to usergroup
     * @return log records of package-usergroup
     */
    public function getPackageUsergroupList()
    {
        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];

        if (isset($search['value'])) {
            $searchKey = $search['value'];
        }

        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }

        $filter = Input::get('filter');

        if (!in_array($filter, ['SUCCESS', 'FAILURE'])) {
            $filter = 'ALL';
        } else {
            $filter = strtoupper($filter);
        }
        $created_date = Input::get('created_date');

        if ($searchKey != '') {
            $filter = 'ALL';
            $created_date = '';
        }
        $totalRecords = PackageEnrolLog::getPackageUsergroupImportCount('ALL', null, null, 'usergroup');
        $filteredRecords = PackageEnrolLog::getPackageUsergroupImportCount($filter, $searchKey, $created_date, 'usergroup');
        $filtereddata = PackageEnrolLog::getPackageUsergroupImportRecords($filter, $start, $limit, $orderByArray, $searchKey, $created_date, 'usergroup');

        $totalRecords = $filteredRecords;
        $dataArr = [];
        foreach ($filtereddata as $key => $value) {
            if ($value['status'] == 'SUCCESS') {
                $program = Package::getPackageDetailsByID($value['program_id']);
                $group = UserGroup::where('ugid', '=', (int)$value['uid'])->first();
                $usergroup = $group['usergroup_name'];
                $title = $program['package_title'];
                $shortname = $program['package_shortname'];
                $error = 'N/A';
            } else {
                $usergroup = $value['usergroup'];
                $title = $value['packagename'];
                $shortname = $value['packageshortname'];
                $error = $value['error_msgs'];
            }
            $date = Timezone::convertFromUTC($value['created_at'], Auth::user()->timezone, config('app.date_format'));
            $temparr = [$usergroup, $title, $shortname, $error, $date, $value['status'], $value['action']];
            $dataArr[] = $temparr;
        }
        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr,
        ];

        return response()->json($finaldata);
    }
    /**
     * Method to export package to usergroup
     * @param string $enrol_level
     * @param string $program_sub_type
     * @param string $status
     * @param string $date
     * @return csv file
     */
    public function getUsergroupPackExport($enrol_level, $program_sub_type, $status = 'ALL', $date = null)
    {
        $this->packageService->usergroupPackageExport($enrol_level, $program_sub_type, $status = 'ALL', $date = null);
    }
    /**
     * Method to download enrol user/ug to channel/package template
     * @param string $program_sub_type
     * @param string $enrol_level
     * @return downloads sample import csv file
     */
    public function getImportProgramEnrolTemplate($enrol_level, $program_sub_type)
    {
        $data = [];
        if ($program_sub_type == 'single' && $enrol_level == 'user') {
            $data[] = 'username*';
            $data[] = 'name*';
            $data[] = 'shortname';
            $file = config('app.channel_user_import_file');
        } else {
            $data[] = 'usergroup*';
            $data[] = 'packagename*';
            $data[] = 'packageshortname';
            $file = config('app.package_usergroup_import_file');
        }
        header('Content-type: application/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file);
        $fp = fopen('php://output', 'w');
        fputcsv($fp, $data);
        die;
    }

    /**
     * Method to download enrol user/ug to channel/package template
     * @param string $program_sub_type
     * @param string $enrol_level
     * @return downloads sample import csv file
     */
    public function getImportPackageEnrolTemplate()
    {
        $data[] = 'usergroup*';
        $data[] = 'packagename*';
        $data[] = 'packageshortname';
        $file = config('app.package_usergroup_import_file');
        header('Content-type: application/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file);
        $fp = fopen('php://output', 'w');
        fputcsv($fp, $data);
        die;
    }

    /**
     * Method to load user channel page
     * @return view page of user-channel
     */
    public function getUserChannelReport()
    {
        $crumbs = [
            'Dashboard' => 'cp',
            trans('admin/user.import_users_in_bulk') . ' Reports' => '',
            trans('admin/program.user_to_channel') => '',
        ];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/user.import_users_in_bulk') . ' Reports';
        $this->layout->pageicon = 'fa fa-bar-chart-o';
        $this->layout->pagedescription = trans('admin/user.import_users_in_bulk') . ' Reports';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'import-reports')
            ->with('submenu', 'user-channel-report');
        $this->layout->content = view('admin.theme.programs.user_channel_report');
        $this->layout->footer = view('admin.theme.common.footer');
    }
    /**
     * Method to export user to channel mapping
     * @param string $enrol_level
     * @param string $program_sub_type
     * @param string $status
     * @param string $date
     * @return csv file
     */
    public function getUserChannelExport($enrol_level, $program_sub_type, $status = 'ALL', $date = null)
    {
        $this->programservice->userChannelExport($enrol_level, $program_sub_type, $status = 'ALL', $date = null);
    }
    /**
     * Method to list logs of channel to user
     * @return log records of channel-user
     */
    public function getChannelUserList()
    {
        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];

        if (isset($search['value'])) {
            $searchKey = $search['value'];
        }

        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }

        $filter = Input::get('filter');

        if (!in_array($filter, ['SUCCESS', 'FAILURE'])) {
            $filter = 'ALL';
        } else {
            $filter = strtoupper($filter);
        }
        $created_date = Input::get('created_date');

        if ($searchKey != '') {
            $filter = 'ALL';
            $created_date = '';
        }
        $totalRecords = EnrolLog::getPackageUsergroupImportCount('ALL', null, null, 'single', 'user');
        $filteredRecords = EnrolLog::getPackageUsergroupImportCount($filter, $searchKey, $created_date, 'single', 'user');
        $filtereddata = EnrolLog::getPackageUsergroupImportRecords($filter, $start, $limit, $orderByArray, $searchKey, $created_date, 'single', 'user');

        $totalRecords = $filteredRecords;
        $dataArr = [];
        foreach ($filtereddata as $key => $value) {
            if ($value['status'] == 'SUCCESS') {
                $program = Program::getProgramDetailsByID($value['program_id']);
                $group = User::where('uid', '=', (int)$value['uid'])->first();
                $usergroup = $group['username'];
                $title = $program['program_title'];
                $shortname = $program['program_shortname'];
                $error = 'N/A';
            } else {
                $usergroup = $value['username'];
                $title = $value['name'];
                $shortname = $value['shortname'];
                $error = $value['error_msgs'];
            }
            $date = Timezone::convertFromUTC($value['created_at'], Auth::user()->timezone, config('app.date_format'));
            $temparr = [$usergroup, $title, $shortname, $error, $date, $value['status'], $value['action']];
            $dataArr[] = $temparr;
        }
        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr,
        ];

        return response()->json($finaldata);
    }
}
