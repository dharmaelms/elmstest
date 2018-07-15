<?php

namespace App\Http\Controllers\Admin;

use App;
use App\Http\Controllers\AdminBaseController;
use App\Model\Common;
use App\Model\Country;
use App\Model\CustomFields\Entity\CustomFields;
use App\Model\Email;
use App\Model\ImportLog\Entity\EnrolLog;
use App\Model\ImportLog\Entity\PackageEnrolLog;
use App\Model\ImportLog\Entity\PackageLog;
use App\Model\ImportLog\Entity\ProgramLog;
use App\Model\ImportLog\Entity\UsergroupLog;
use App\Model\ImportLog\Entity\UserLog;
use App\Model\Package\Entity\Package;
use App\Model\NotificationLog;
use App\Model\Packet;
use App\Model\Program;
use App\Model\Role;
use App\Model\SiteSetting;
use App\Model\States;
use App\Model\Transaction;
use App\Model\TransactionDetail;
use App\Model\User;
use App\Model\UserGroup;
use App\Model\UserimportHistory;
use App\Services\Package\IPackageService;
use App\Services\Program\IProgramService;
use App\Services\User\IUserService;
use Carbon;
use Config;
use Exception;
use Illuminate\Support\MessageBag;
use Input;
use Log;
use PHPExcel;
use PHPExcel_IOFactory;
use Redirect;
use Request;
use Session;
use Timezone;
use URL;
use Validator;

class CronBulkImportController extends AdminBaseController
{
    private $programservice;
    private $user_service;

    /**
     * @var IPackageService
     */
    private $packageService;

    public function __construct(
        Request $request,
        IProgramService $programservice,
        IUserService $user_service,
        IPackageService $packageService
    )
    {
        // Stripping all html tags from the request body
        $input = $request::input();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request::merge($input);

        $this->programservice = $programservice;
        $this->user_service = $user_service;
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
                                $this->packageService->postPackageLog($logdata, 'FAILURE', 'erp-package-import-failure-template', 'ADD', 1);
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
                                            $record = $this->packageService->getErpLogdata($logdata, 'FAILURE', 1);
                                            PackageLog::getInsertErpPackageLog($record);
                                        } else {
                                            /*---Insert data in program collection---*/
                                            $productid = Package::uniqueProductId();
                                            $this->packageService->getPrepareErpPackageData($productid, array_combine($csv_file_head, $data), $cron = 1);
                                            /*---Insert data in erp program log collection with success---*/
                                            $input['program_id'] = $productid;
                                            $input['action'] = 'ADD';
                                            $record = $this->packageService->getErpLogdata($input, 'SUCCESS', 1);
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
                            $this->packageService->postPackageLog($logdata, 'FAILURE', 'erp-package-import-failure-template', 'ADD', 1);
                            $message = trans('admin/program.invalid_file');
                        }
                    } else {
                        ftp_close($ftp_connection_id); //ftp connection close
                        $logdata['error_msgs'] = trans('admin/program.invalid_add_dir');
                        $this->packageService->postPackageLog($logdata, 'FAILURE', 'erp-package-import-failure-template', 'ADD', 1);
                        $message = trans('admin/program.invalid_add_dir');
                    }
                } else {
                    $logdata['error_msgs'] = trans('admin/program.invalid_ftp');
                    $this->packageService->postPackageLog($logdata, 'FAILURE', 'erp-package-import-failure-template', 'ADD', 1);
                    $message = trans('admin/program.invalid_ftp');
                }
                Log::info($message);
            } else {
                Log::info('getErpImportPackages: ' . trans('admin/program.cron_ftp_disabled'));
            }
        } catch (Exception $e) {
            Log::error($e->getFile(). trans('admin/program.error_line', ['error_line_no'=> $e->getLine()]).trans('admin/program.message', ['error_message' => $e->getMessage()]));
        }
    }
    /**
     * Method to import channels
     * @return nothing
     */
    public function getErpImportChannels()
    {   
        try {
            if(config('app.ftp_enabled')) {
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
                                $this->programservice->postChannelLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-channel-import-failure-template', $action = 'ADD', $cron = 1);
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
                                            $record = $this->programservice->getErpLogData($logdata, $type = 'single', $status = 'FAILURE', $cron = 1);
                                            ProgramLog::getInsertErpProgramLog($record);
                                        } else {

                                            /*---Insert data in program collection---*/
                                            $productid = Program::uniqueProductId();
                                            $this->programservice->getPrepareErpProgramData($productid, array_combine($csv_file_data['head'], $data), $type = 'single', $cron = 1);
                                            $input['program_id'] = $productid;
                                            $input['action'] = 'ADD';
                                            /*---Insert record in erp program log collection with success---*/
                                            $record = $this->programservice->getErpLogData($input, $type = 'single', $status = 'SUCCESS', $cron = 1);
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
                            $this->programservice->postChannelLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-channel-import-failure-template', $action = 'ADD', $cron = 1);
                        }
                    } else {
                        ftp_close($ftp_connection_details['conn_id']); //ftp connection close
                        $message = trans('admin/program.invalid_add_dir');
                        $logdata['error_msgs'] = trans('admin/program.invalid_add_dir');
                        $this->programservice->postChannelLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-channel-import-failure-template', $action = 'ADD', $cron = 1);
                    }
                } else {
                    $message = trans('admin/program.invalid_ftp');
                    $logdata['error_msgs'] = trans('admin/program.invalid_ftp');
                    $this->programservice->postChannelLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-channel-import-failure-template', $action = 'ADD', $cron = 1);
                }
                Log::info($message);
            } else {
                Log::info('getErpImportChannels: ' . trans('admin/program.cron_ftp_disabled'));
            }
        } catch (Exception $e) {
            Log::error($e->getFile(). trans('admin/program.error_line', ['error_line_no'=> $e->getLine()]).trans('admin/program.message', ['error_message' => $e->getMessage()]));
        }
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
                                $this->programservice->postUserChannelLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-user-to-channel-failure-template', $action = 'ADD', $cron = 1);
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
                                            $record = $this->programservice->getErpLogData($logdata, $type = 'single', $status = 'FAILURE', $cron = 1);
                                            EnrolLog::InsertErpEnrolLog($record);
                                        } else {
                                            $rowdata = array_combine($csv_file_data['head'], $data);
                                            $programid = $this->programservice->getProgramId($rowdata['name'], $rowdata['shortname'], $type = 'content_feed', $subtype = 'single');
                                            $userid = User::where('username', '=', strtolower($rowdata['username']))->value('uid');
                                            /*---Insert data in program,users,transactions,transaction_details collection---*/
                                            $this->programservice->enrolUserToProgram($programid, $userid, $rowdata['name'], $rowdata['shortname'], $type = 'content_feed', $subtype = 'single', $level = 'user', $cron = 1);
                                            /*---Insert data in erp enrol log collection with success---*/
                                            $input['uid'] = $userid;
                                            $input['program_id'] = $programid;
                                            $input['enrol_level'] = 'user';
                                            $input['action'] = 'ADD';
                                            $record = $this->programservice->getErpLogData($input, $type = 'single', $status = 'SUCCESS', $cron = 1);
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
                            $this->programservice->postUserChannelLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-user-to-channel-failure-template', $action = 'ADD', $cron = 1);
                        }
                    } else {
                        $message = trans('admin/program.invalid_add_dir');
                        ftp_close($ftp_connection_details['conn_id']); //ftp connection close
                        $logdata['error_msgs'] = trans('admin/program.invalid_add_dir');
                        $this->programservice->postUserChannelLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-user-to-channel-failure-template', $action = 'ADD', $cron = 1);
                    }
                } else {
                    $message = trans('admin/program.invalid_ftp');
                    $logdata['error_msgs'] = trans('admin/program.invalid_ftp');
                    $this->programservice->postUserChannelLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-user-to-channel-failure-template', $action = 'ADD', $cron = 1);
                }
                Log::info($message);
            } else {
                Log::info('getEnrolUserToChannel' . trans('admin/program.cron_ftp_disabled'));
            }
        } catch (Exception $e) {
            Log::error($e->getFile(). trans('admin/program.error_line', ['error_line_no'=> $e->getLine()]).trans('admin/program.message', ['error_message' => $e->getMessage()]));
        }
    }
    /**
     * Method to import usergroup to packages
     * @return nothing
     */
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
                                $this->packageService->postUsergroupPackLog($logdata, 'FAILURE', 'erp-usergroup-to-package-failure-template', 'ADD', 1);
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
                                            $record = $this->packageService->getErpLogdata($logdata, 'FAILURE', 1);
                                            PackageEnrolLog::InsertErpEnrolLog($record);
                                        } else {
                                            $rowdata = array_combine($csv_file_head, $data);
                                            $packageid = $this->packageService->getPackageId(array_get($rowdata, 'packagename', ""), array_get($rowdata, 'packageshortname', ""));
                                            $usergroupid = UserGroup::where('ug_name_lower', '=', strtolower(array_get($rowdata, 'usergroup', "")))->value('ugid');
                                            /*---Insert data in program,users,transactions,transaction_details collection---*/
                                            $this->packageService->enrolUsergroupToPackage($packageid, $usergroupid, array_get($rowdata, 'packagename', ""), array_get($rowdata, 'packageshortname', ""), 'usergroup', 1);

                                            /*---Insert data in erp enrol log collection with success---*/
                                            $input['uid'] = $usergroupid;
                                            $input['program_id'] = $packageid;
                                            $input['enrol_level'] = 'usergroup';
                                            $input['action'] = 'ADD';
                                            $record = $this->packageService->getErpLogdata($input, 'SUCCESS', 1);
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
                            $this->packageService->postUsergroupPackLog($logdata, 'FAILURE', 'erp-usergroup-to-package-failure-template', 'ADD', 1);
                        }
                    } else {
                        ftp_close($ftp_connection_id); //ftp connection close
                        $message = trans('admin/program.invalid_add_dir');
                        $logdata['error_msgs'] = trans('admin/program.invalid_add_dir');
                        $this->packageService->postUsergroupPackLog($logdata, 'FAILURE', 'erp-usergroup-to-package-failure-template', 'ADD', 1);
                    }
                } else {
                    $message = trans('admin/program.invalid_ftp');
                    $logdata['error_msgs'] = trans('admin/program.invalid_ftp');
                    $this->packageService->postUsergroupPackLog($logdata, 'FAILURE', 'erp-usergroup-to-package-failure-template', 'ADD', 1);
                }
                Log::info($message);
            } else {
                Log::info('getEnrolUsergroupToPackage' . trans('admin/program.cron_ftp_disabled'));
            }
        } catch (Exception $e) {
            Log::error($e->getFile(). trans('admin/program.error_line', ['error_line_no'=> $e->getLine()]).trans('admin/program.message', ['error_message' => $e->getMessage()]));
        }
    }
    /**
     * Method to validate update program metadata
     * @param array $csvrowData
     * @param string $type
     * @return bool|array messages
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
            $start_date = (array_key_exists('startdate', $csvrowData)) ? $csvrowData['startdate'] : Timezone::convertFromUTC('@' . $program['program_startdate'], config('app.default_timezone'), config('app.date_format'));
            $end_date = (array_key_exists('enddate', $csvrowData)) ? $csvrowData['enddate'] : Timezone::convertFromUTC('@' . $program['program_enddate'], config('app.default_timezone'), config('app.date_format'));
            $display_start_date = (array_key_exists('displaystartdate', $csvrowData)) ? $csvrowData['displaystartdate'] : Timezone::convertFromUTC('@' . $program['program_display_startdate'], config('app.default_timezone'), config('app.date_format'));
            $display_end_date = (array_key_exists('displayenddate', $csvrowData)) ? $csvrowData['displayenddate'] : Timezone::convertFromUTC('@' . $program['program_display_enddate'], config('app.default_timezone'), config('app.date_format'));

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
                                $this->packageService->postPackageLog($logdata, 'FAILURE', 'erp-package-update-failure-template', 'UPDATE', 1);
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
                                            $record = $this->packageService->getErpLogdata($logdata, 'FAILURE', 1);
                                            PackageLog::getInsertErpPackageLog($record);
                                        } else {
                                            $program_data = array_combine($csv_file_head, $data);
                                            $old_slug = $this->packageService->generatePackageSlug(array_get($program_data, 'name', ""), array_get($program_data, 'shortname', ""));
                                            $new_slug = '';
                                            if (array_key_exists('newname', $program_data) && array_key_exists('newshortname', $program_data)) {
                                                $new_slug = $this->packageService->generatePackageSlug(array_get($program_data, 'newname', ""), array_get($program_data, 'newshortname', ""));
                                            }
                                            //update program
                                            $program_id = $this->packageService->updatePackageDetails($program_data, $old_slug, $new_slug, 1);
                                            //insert program success log
                                            $input['program_id'] = $program_id;
                                            $input['action'] = 'UPDATE';
                                            $record = $this->packageService->getErpLogdata($input, 'SUCCESS', 1);
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
                            $this->packageService->postPackageLog($logdata, 'FAILURE', 'erp-package-update-failure-template', 'UPDATE', 1);
                            $message = trans('admin/program.invalid_file');
                        }
                    } else {
                        ftp_close($ftp_connection_id);
                        $logdata['error_msgs'] = trans('admin/program.invalid_update_dir');
                        $this->packageService->postPackageLog($logdata, 'FAILURE', 'erp-package-update-failure-template', 'UPDATE', 1);
                        $message = trans('admin/program.invalid_update_dir');
                    }
                } else {
                    $logdata['error_msgs'] = trans('admin/program.invalid_ftp');
                    $this->packageService->postPackageLog($logdata, 'FAILURE', 'erp-package-update-failure-template', 'UPDATE', 1);
                    $message = trans('admin/program.invalid_ftp');
                }
                Log::info($message);
            } else {
                Log::info('getErpUpdatePackages: ' . trans('admin/program.cron_ftp_disabled'));
            }
        } catch (Exception $e) {
            Log::error($e->getFile(). trans('admin/program.error_line', ['error_line_no'=> $e->getLine()]).trans('admin/program.message', ['error_message' => $e->getMessage()]));
        }
    }
    /**
     * Method to update channels
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
                                $this->programservice->postProgramLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-channel-update-failure-template', $action = 'UPDATE', $cron = 1);
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
                                            $record = $this->programservice->getErpLogData($logdata, $type = 'single', $status = 'FAILURE', $cron = 1);
                                            ProgramLog::getInsertErpProgramLog($record);
                                        } else {
                                            $program_data = array_combine($csv_file_data['head'], $data);
                                            $old_slug = $this->programservice->generateProgramSlug($program_data['name'], $program_data['shortname']);
                                            $new_slug = '';
                                            if (array_key_exists('newname', $program_data) && array_key_exists('newshortname', $program_data)) {
                                                $new_slug = $this->programservice->generateProgramSlug($program_data['newname'], $program_data['newshortname']);
                                            }
                                            //update program
                                            $program_id = $this->programservice->updateProgramDetails($program_data, $old_slug, $new_slug, $cron = 1);
                                            //insert program success log
                                            $input['program_id'] = $program_id;
                                            $input['action'] = 'UPDATE';
                                            $record = $this->programservice->getErpLogData($input, $type = 'single', $status = 'SUCCESS', $cron = 1);
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
                            $this->programservice->postProgramLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-channel-update-failure-template', $action = 'UPDATE', $cron = 1);
                            $message = trans('admin/program.invalid_file');
                        }
                    } else {
                        ftp_close($ftp_connection_details['conn_id']);
                        $logdata['error_msgs'] = trans('admin/program.invalid_update_dir');
                        $this->programservice->postProgramLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-channel-update-failure-template', $action = 'UPDATE', $cron = 1);
                        $message = trans('admin/program.invalid_update_dir');
                    }
                } else {
                    $logdata['error_msgs'] = trans('admin/program.invalid_ftp');
                    $this->programservice->postProgramLog($logdata, $type = 'single', $status = 'FAILURE', $slug = 'erp-channel-update-failure-template', $action = 'UPDATE', $cron = 1);
                    $message = trans('admin/program.invalid_ftp');
                }
                Log::info($message);
            } else {
                Log::info('getErpUpdateChannels: ' . trans('admin/program.cron_ftp_disabled'));
            }
        } catch (Exception $e) {
            Log::error($e->getFile(). trans('admin/program.error_line', ['error_line_no'=> $e->getLine()]).trans('admin/program.message', ['error_message' => $e->getMessage()]));
        }
    }
    /**
     * Method to import users
     */
    public function getErpImportUsers()
    {   
        try {
            if (config('app.ftp_enabled')) {
                $connect = UserLog::getValidateFtp();
                if ($connect) {
                    $ftp_connection_details = $this->user_service->getFtpConnectionDetails();
                    if (in_array('add', $ftp_connection_details['dir_list'])) {
                        $ftp_dir_details = $this->user_service->getFtpDirDetails($ftp_connection_details);
                        if (in_array($ftp_dir_details['local_file'], $ftp_dir_details['file_list'])) {
                            $csv_file_data = $this->user_service->getUserImportFileColumns($ftp_connection_details, $ftp_dir_details);
                            /*---Validate headers ends here---*/
                            if ($csv_file_data['count'] > 0) {
                                $message = trans('admin/program.invalid_headers');
                                $error = trans('admin/program.invalid_headers');
                                $this->user_service->postUserLog($error, $status = 'FAILURE', $slug = 'erp-user-import-failure-template', $action = 'ADD', $cron = 1);
                            } else {
                                $i = 0;
                                foreach ($csv_file_data['csvFile'] as $line) {
                                    $data = str_getcsv($line);
                                    $i++;
                                    if ($i > 1) {
                                        $valid_data = $this->user_service->validateRulesErp(array_combine($csv_file_data['head'], $data));
                                        if ($valid_data) {
                                            $error_msg = '';
                                            foreach ($valid_data->all() as $k) {
                                                $error_msg .= $k;
                                            }
                                            /*---Insert data in user log collection with failure---*/
                                            $logdata = array_combine($csv_file_data['head'], $data);
                                            $logdata = $this->user_service->getUserFailedLogData($logdata, $error_msg, $status = 'FAILURE', $action = 'ADD', $cron = 1);
                                            UserLog::insertErpUserLog($logdata);
                                        } else {
                                            /*---Insert data in user & log collection with success---*/
                                            $result_data = array_combine($csv_file_data['head'], $data);
                                            //dd($result_data);
                                            $uid = $this->user_service->prepareUserData($result_data, $cron = 1);
                                            $input['userid'] = $uid;
                                            $logdata = $this->user_service->getUserSuccessLogData($input, $status = 'SUCCESS', $action = 'ADD', $cron = 1);
                                            UserLog::insertErpUserLog($logdata);
                                            $groupid = Usergroup::getUserGroupId(strtolower($result_data['usergroup']));
                                            $this->user_service->createUserGroup($groupid, $uid, $result_data, $cron = 1);
                                        }
                                    }
                                }
                                $this->user_service->userImportEmail($status = 'SUCCESS', 'erp-user-import-success-template', '', 'ADD');
                                $message = trans('admin/program.cron_success');
                            }
                            //process records ends here
                            $new_file = $this->user_service->getFileName($ftp_dir_details['local_file'], $action = 'add');
                            rename($ftp_connection_details['path'] . $ftp_dir_details['local_file'], $ftp_connection_details['path'] . $new_file);
                        } else {
                            ftp_close($ftp_connection_details['conn_id']); //ftp connection close
                            $message = trans('admin/program.invalid_file');
                            $error = trans('admin/program.invalid_file');
                            $this->user_service->postUserLog($error, $status = 'FAILURE', $slug = 'erp-user-import-failure-template', $action = 'ADD', $cron = 1);
                        }
                    } else {
                        ftp_close($ftp_connection_details['conn_id']); //ftp connection close
                        $message = trans('admin/program.invalid_add_dir');
                        $error = trans('admin/program.invalid_add_dir');
                        $this->user_service->postUserLog($error, $status = 'FAILURE', $slug = 'erp-user-import-failure-template', $action = 'ADD', $cron = 1);
                    }
                } else {
                    $message = trans('admin/program.invalid_ftp');
                    $error = trans('admin/program.invalid_ftp');
                    $this->user_service->postUserLog($error, $status = 'FAILURE', $slug = 'erp-user-import-failure-template', $action = 'ADD', $cron = 1);
                }
                Log::info($message);
            } else {
                Log::info('getErpImportUsers: ' . trans('admin/program.cron_ftp_disabled'));
            }
        } catch (Exception $e) {
            Log::error($e->getFile(). trans('admin/program.error_line', ['error_line_no'=> $e->getLine()]).trans('admin/program.message', ['error_message' => $e->getMessage()]));
        }
    }

    /**
     * Method to validate program
     * @param array $input
     * @param array $rules
     * @param array $messages
     * @return bool | MessageBag with validation messages
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
     * Method to update bulk users
     */
    public function getErpUpdateUsers()
    {   
        try {
            if (config('app.ftp_enabled')) {
                $connect = UserLog::getValidateFtp();
                if ($connect) {
                    $ftp_connection_details = $this->user_service->getFtpConnectionDetails();
                    if (in_array('update', $ftp_connection_details['dir_list'])) {
                        $ftp_dir_details = $this->user_service->getFtpDirUpdateDetails($ftp_connection_details);
                        if (in_array($ftp_dir_details['local_file'], $ftp_dir_details['file_list'])) {
                            $csv_file_data = $this->user_service->getUserImportUpdateFileColumns($ftp_connection_details, $ftp_dir_details);
                            /*---Validate headers ends here---*/
                            if ($csv_file_data['count'] > 0) {
                                $error = trans('admin/program.invalid_headers');
                                $message = trans('admin/program.invalid_headers');
                                $this->user_service->postUserLog($error, $status = 'FAILURE', $slug = 'erp-user-update-failure-template', $action = 'UPDATE', $cron = 1);
                            } else {
                                $i = 0;
                                foreach ($csv_file_data['csvFile'] as $line) {
                                    $data = str_getcsv($line);
                                    $i++;
                                    if ($i > 1) {
                                        $valid_data = $this->user_service->validateUpdateRules(array_combine($csv_file_data['head'], $data));
                                        if ($valid_data) {
                                            $error_msg = '';
                                            foreach ($valid_data->all() as $k) {
                                                $error_msg .= $k;
                                            }
                                            $logdata = array_combine($csv_file_data['head'], $data);
                                            $logdata = $this->user_service->getMissingFields($logdata, $csv_file_data['fields']);
                                            $logdata = $this->user_service->getUserFailedLogData($logdata, $error_msg, $status = 'FAILURE', $action = 'UPDATE', $cron = 1);
                                            UserLog::insertErpUserLog($logdata);
                                        } else {
                                            $user_data = array_combine($csv_file_data['head'], $data);
                                            $uid = $this->user_service->getUserIdByUserName($user_data['username']);
                                            //update user data
                                            $this->user_service->updateUserDetails($uid, $user_data);
                                            //insert success log in user log table
                                            $input['userid'] = $uid;
                                            $logdata = $this->user_service->getUserSuccessLogData($input, $status = 'SUCCESS', $action = 'UPDATE', $cron = 1);
                                            UserLog::insertErpUserLog($logdata);

                                        }
                                    }
                                }
                                $this->user_service->userImportEmail($status = 'SUCCESS', 'erp-user-update-success-template', '', 'UPDATE');
                                $message = trans('admin/program.cron_success');
                            }
                            //process records ends here
                            $new_file = $this->user_service->getFileName($ftp_dir_details['local_file'], $action = 'UPDATE');
                            rename($ftp_connection_details['path'] . $ftp_dir_details['local_file'], $ftp_connection_details['path'] . $new_file);
                        } else {
                            ftp_close($ftp_connection_details['conn_id']);
                            $message = trans('admin/program.invalid_file');
                            $error = trans('admin/program.invalid_file');
                            $this->user_service->postUserLog($error, $status = 'FAILURE', $slug = 'erp-user-update-failure-template', $action = 'UPDATE', $cron = 1);
                        }
                    } else {
                        ftp_close($ftp_connection_details['conn_id']); //ftp connection close
                        $message = trans('admin/program.invalid_update_dir');
                        $error = trans('admin/program.invalid_update_dir');
                        $this->user_service->postUserLog($error, $status = 'FAILURE', $slug = 'erp-user-update-failure-template', $action = 'UPDATE', $cron = 1);
                    }
                } else {
                    $message = trans('admin/program.invalid_ftp');
                    $error = trans('admin/program.invalid_ftp');
                    $this->user_service->postUserLog($error, $status = 'FAILURE', $slug = 'erp-user-update-failure-template', $action = 'UPDATE', $cron = 1);
                }
                Log::info($message);
            } else {
                Log::info('getErpUpdateUsers: ' . trans('admin/program.cron_ftp_disabled'));
            }
        } catch (Exception $e) {
            Log::error($e->getFile(). trans('admin/program.error_line', ['error_line_no'=> $e->getLine()]).trans('admin/program.message', ['error_message' => $e->getMessage()]));
        }
    }

    /**
     * Method to assign & unassign user to user group
     */
    public function getAssignUserToUsergroup()
    {   
        try {
            if (config('app.ftp_enabled')) {
                $connect = UserLog::getValidateFtp();
                if ($connect) {
                    $ftp_connection_details = $this->user_service->getFtpConnectionDetails();
                    if (in_array('update', $ftp_connection_details['dir_list'])) {
                        $ftp_dir_details = $this->user_service->getUserUsergroupUpdateFtpDetails($ftp_connection_details);
                        if (in_array($ftp_dir_details['local_file'], $ftp_dir_details['file_list'])) {
                            $csv_file_data = $this->user_service->getUserUsergroupUpdateFileColumns($ftp_connection_details, $ftp_dir_details);
                            /*---Validate headers ends here---*/
                            if ($csv_file_data['count'] > 0) {
                                $error = trans('admin/program.invalid_headers');
                                $message = trans('admin/program.invalid_headers');
                                $this->user_service->postUserGroupLog($error, $status = 'FAILURE', $slug = 'erp-assign-user-usergroup-failure-template', $action = 'UPDATE', $cron = 1);
                            } else {
                                $i = 0;
                                foreach ($csv_file_data['csvFile'] as $line) {
                                    $data = str_getcsv($line);
                                    $i++;
                                    if ($i > 1) {
                                        $valid_data = $this->user_service->validateUserUsergroupRules(array_combine($csv_file_data['head'], $data));
                                        if ($valid_data) {
                                            $error_msg = '';
                                            foreach ($valid_data->all() as $k) {
                                                $error_msg .= $k;
                                            }
                                            $logdata = array_combine($csv_file_data['head'], $data);
                                            $logdata = $this->user_service->getUserGroupFailedData($logdata, $error_msg, $status = 'FAILURE', $action = 'UPDATE', $cron = 1);
                                            UsergroupLog::insertErpUserGroupLog($logdata);
                                        } else {
                                            $user_data = array_combine($csv_file_data['head'], $data);
                                            $uid = $this->user_service->getUserIdByUserName($user_data['username']);
                                            $gid = $this->user_service->getUserGroupIdByGroupName($user_data['usergroup']);
                                            $this->user_service->updateUserUserGroupRelations($uid, $gid, $user_data['operation']); //update user & ug table
                                            $input['groupid'] = $gid;
                                            $input['userid'] = $uid;
                                            $input['operation'] = $user_data['operation'];
                                            $logdata = $this->user_service->getUserGroupSuccessData($input, $status = 'SUCCESS', $action = 'UPDATE', $cron = 1);
                                            UsergroupLog::insertErpUserGroupLog($logdata); //insert success log in user log table
                                        }
                                    }
                                }
                                $this->user_service->sendUserGroupEmail($status = 'SUCCESS', 'erp-assign-user-usergroup-success-template', '', 'UPDATE');
                                $message = trans('admin/program.cron_success');
                            }
                            //process records ends here
                            $new_file = $this->user_service->getFileName($ftp_dir_details['local_file'], $action = 'UPDATE');
                            rename($ftp_connection_details['path'] . $ftp_dir_details['local_file'], $ftp_connection_details['path'] . $new_file);
                        } else {
                            ftp_close($ftp_connection_details['conn_id']);
                            $message = trans('admin/program.invalid_file');
                            $error = trans('admin/program.invalid_file');
                            $this->user_service->postUserGroupLog($error, $status = 'FAILURE', $slug = 'erp-assign-user-usergroup-failure-template', $action = 'UPDATE', $cron = 1);
                        }
                    } else {
                        ftp_close($ftp_connection_details['conn_id']); //ftp connection close
                        $message = trans('admin/program.invalid_update_dir');
                        $error = trans('admin/program.invalid_update_dir');
                        $this->user_service->postUserGroupLog($error, $status = 'FAILURE', $slug = 'erp-assign-user-usergroup-failure-template', $action = 'UPDATE', $cron = 1);
                    }
                } else {
                    $message = trans('admin/program.invalid_ftp');
                    $error = trans('admin/program.invalid_ftp');
                    $this->user_service->postUserGroupLog($error, $status = 'FAILURE', $slug = 'erp-assign-user-usergroup-failure-template', $action = 'UPDATE', $cron = 1);
                }
                Log::info($message);
            } else {
                Log::info('getErpUpdateUsers: ' . trans('admin/program.cron_ftp_disabled'));
            }
        } catch (Exception $e) {
            Log::error($e->getFile(). trans('admin/program.error_line', ['error_line_no'=> $e->getLine()]).trans('admin/program.message', ['error_message' => $e->getMessage()]));
        }
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
            . $csvrowData['packagename'] . ',' . $csvrowData['packageshortname'] .   '',

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

        $messages = [
            'checkusergroup' => trans('admin/program.check_usergroup_exists'),
            'checkpackageexists' => trans('admin/program.check_package_exists'),
            'checkprogram' => trans('admin/program.check_program'),
            'min' => trans('admin/program.shortname'),
            'checkchannel' => trans('admin/program.check_channel'),
            'usergroupexist' => trans('admin/program.check_usergroup_package_mapping'),
            'activeusergroup' => trans('admin/program.check_active_usergroup'),
        ];

        return $this->customErpPackageValidate($csvrowData, $rules, $messages);
    }
       
}
