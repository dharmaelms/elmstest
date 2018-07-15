<?php

namespace App\Services\Program;

/**
 * Interface IProgramService
 *
 * @package App\Services\Program
 */
interface IProgramService
{
    /**
     * @param int $id
     * @return \App\Model\Program
     */
    public function getProgram($id);

    /**
     * @param string $type
     * @param string $slug
     * @return \App\Model\Program
     */
    public function getProgramBySlug($type, $slug);

    /**
     * @param string $slug
     * @return \App\Model\Program
     */
    public function getProgramIdBySlug($program_slug);

    /**
     * @param string $program_type
     * @param string $program_slug
     * @param string $post_slug
     * @return \App\Model\Packet
     */
    public function getProgramPostBySlug($program_type, $program_slug, $post_slug);

    /**
     * @param int $post_id
     * @param array $filter_params
     * @return int
     */
    public function getPostQuestionsCount($post_id, $filter_params = []);

    /**
     * @param int $post_id
     * @param array $filter_params
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPostQuestions($post_id, $filter_params = []);

    /**
     * Method to get programs with page and limit
     *
     * @param int $page
     * @param int $limit
     * @param boolean $posts (optional)
     * @param boolean $order (optional)
     * @return array
     */
    public function getUserPrograms($page, $limit, $posts = false, $order = false);

    /**
     * Method to get total active programs and assigned assessments count
     *
     * @return array
     */
    public function getActiveProgramsTotalCount();

    /**
     * Method to get programs new posts and assigned assessments count
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getActiveProgramsCount($page, $limit);

    /**
     * Method to get list of channels assigned to user based on category
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getCategoryWiseChannels();
    /**
     * Method to prepare erp program log data
     * @param array $data
     * @param string $type
     * @param string $status
     * @param int $cron
     * @return array
     */
    public function getErpLogdata($data,$type,$status,$cron);

    /**
     * @param string $name
     * @param string $shortname
     * @param string $type
     * @param string $subtype
     * @return int program_id
     */
    public function getProgramId($name, $shortname, $type, $subtype);
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
    public function enrolUserToProgram($programid,$userid,$name,$shortname,$type,$subtype,$level,$cron);

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
    public function enrolUsergroupToProgram($programid,$groupid,$name,$shortname,$type,$subtype,$level,$cron);

    /**
     * @param string $status
     * @param string $slug
     * @param string $reason
     * @param string $action
     * @return nothing sends email to support email
     */
    public function channelImportEmail($status, $slug, $reason = null, $action);
    /**
     * @param string $status
     * @param string $slug
     * @param string $reason
     * @param string $action
     * @return nothing sends email to support email
     */
    public function packageImportEmail($status, $slug, $reason = null, $action);
    /**
     * @param string $status
     * @param string $slug
     * @param string $reason
     * @param string $action
     * @return nothing sends email to support email
     */
    public function enrolUsergroupToPackageEmail($status, $slug, $reason = null, $action);
    /**
     * @param string $status
     * @param string $slug
     * @param string $reason
     * @param string $action
     * @return nothing sends email to support email
     */
    public function enrolUserToChannelEmail($status, $slug, $reason = null, $action);
    /**
     * @param string $program_type
     * @param string $program_sub_type
     * @param string $status
     * @param string $date
     * @return nothing exports programs to csv file
     */
    public function exportPrograms($program_type, $program_sub_type, $status = 'ALL', $date = null, $action = 'ALL');
    /**
     * @param string $enrol_level
     * @param string $program_sub_type
     * @param string $status
     * @param string $date
     * @return nothing exports user-channel to csv file
     */
    public function userChannelExport($enrol_level, $program_sub_type, $status = 'ALL', $date = null);
    /**
     * @param string $name
     * @param string $action
     * @return string $filename
     */
    public function getFileName($name, $action);
    /**
     * @param array $logdata
     * @param string $type
     * @param string $status
     * @param string $slug
     * @param string $action
     * @param int $cron
     * @return nothing sends email & insert log
     */
    public function postProgramLog($logdata,$type,$status,$slug,$action,$cron);

    /**
     * @param array $logdata
     * @param string $type
     * @param string $status
     * @param string $slug
     * @param string $action
     * @param int $cron
     * @return nothing sends email & insert log
     */
    public function postChannelLog($logdata,$type,$status,$slug,$action,$cron);

    /**
     * @param array $logdata
     * @param string $type
     * @param string $status
     * @param string $slug
     * @param string $action
     * @param int $cron
     * @return nothing sends email & insert log
     */
    public function postUsergroupPackLog($logdata,$type,$status,$slug,$action,$cron);

    /**
     * @param array $logdata
     * @param string $type
     * @param string $status
     * @param string $slug
     * @param string $action
     * @return nothing sends email & insert log
     */
    public function postCronUserChannelLog($logdata,$type,$status,$slug,$action);

    /**
     * @param int $program_id
     * @param array $filter_params
     * @return \App\Model\User
     */
    public function getProgramUsers($program_id, $filter_params);

    /**
     * @param array $filter_params
     * @return array
     */
    public function getAllPrograms($filter_params = []);

    /**
     * @param int $program_id
     * @param int $question_id
     * @return \App\Model\ChannelFaq
     */
    public function getQuestion($program_id, $question_id);

    /**
     * @param array $filter_params
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getQuestionsCount($filter_params = []);

    /**
     * @param array $filter_params
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getQuestions($filter_params = []);

    /**
     * @param array $program_slugs
     * @return array
     */
    public function getPackets($program_slugs = []);

    /**
     * @param int $post_id
     * @param int $question_id
     * @return \App\Model\PacketFaq
     */
    public function getPostQuestion($post_id, $question_id);

    /**
     * @param array $logdata
     * @param string $type
     * @param string $status
     * @param string $slug
     * @param string $action
     * @param int $cron
     * @return nothing sends email & insert log
     */
    public function postUserChannelLog($logdata,$type,$status,$slug,$action,$cron);

    /**
     * getProgramsBySearch get list of programs as per searched keys
     * @param  string  $search program title search using this key
     * @param  boolean  $is_ug_rel for get usergroup related programs
     * @param  integer $start  Begining of the record
     * @param  integer $limit  Max number of record need to get
     * @return Collection         list of program as program_title and program_id
     */
    public function getProgramsBySearch($search, $is_ug_rel, $start, $limit);

    /**
     * getPackagesBySearch get list of packages as per searched keys
     * @param  string  $search program title search using this key
     * @param  boolean  $is_ug_rel for get usergroup related programs
     * @param  integer $start  Begining of the record
     * @param  integer $limit  Max number of record need to get
     * @return Collection         list of program as program_title and program_id
     */
    public function getPackagesBySearch($search, $is_ug_rel, $start, $limit);

    /**
     * getPackageProgramsBySearch get list pf channel blongs to specified package's
     * @param  string  $search program title search using this key
     * @param  integer $package_id unique channel/package id
     * @param  integer $start  Begining of the record
     * @param  integer $limit  Max number of record need to get
     * @return Collection          list of program as program_title and program_id
     */
    public function getPackageProgramsBySearch($search = '', $package_id = 0, $start = 0, $limit = 500);

    /**
     * getCFDetailsById get channel details as specific channel
     * @param  int $feed_id unique channel_id
     * @return colection          Channel's details
     */
    public function getCFDetailsById($feed_id);

    /**
     * getCoursesBySearchkey get the course name and id as per search key with 0 to limited set of records
     * @param  string  $search_key search key
     * @param  int sepecific of course
     * @param  integer $start      records start from
     * @param  integer $limit      records limitting
     * @return collection              course id and name as records collection
     */
    public function getCoursesBySearchKey($search_key, $course_id = 0, $start = 0, $limit = 500);

    /**
     * getProgramById get Specific program details
     * @param  integer $program_id unique program id
     * @return collection              collection of object
     */
    public function getProgramById($program_id = 0);
    
    /**
     * Method to get all assigned programs to user
     *
     * @param int $user_id
     * @return array
     */
    public function getAllProgramsAssignedToUser($user_id);

    /**
     * generateProgramSlug prepares program slug
     * @param string $name
     * @param string $shortname
     * @returns program slug
     */
    public function generateProgramSlug($name, $shortname);
    /**
     * Method to download update channel/package csv file
     * @returns update channel/package csv file with fields as headers
     */
    public function downloadProgramTemplate($type, $subtype, $action);
    /**
     * Method to get all program fields to insert update program log
     * @param array $logdata
     * @param array $fields
     * @returns all fields with values
     */
    public function getMissingProgramFields($logdata, $fields);

    /**
     * Method to update program details
     * @param array $program_data
     * @param string $old_slug
     * @param string $new_slug
     * @param int $cron
     * @returns nothing updates program information
     */
    public function updateProgramDetails($program_data,$old_slug,$new_slug,$cron);

    /**
     * @param string $program_type
     * @param string $program_sub_type
     * @param array $filter
     * @param array $custom_field_name
     * @param array $custom_field_value
     */
    public function exportChannels(
        $program_type,
        $program_sub_type,
        $filter,
        $custom_field_name,
        $custom_field_value
    );

    /**
     * Method to return slugs of assigned programs based on user role
     *
     * @return array
     */
    public function getUserProgramSlugs();

    /**
     * Method to return program slugs for assigend users
     *
     * @return array
     */
    public function getProgramSlugs();

    /**
     * getNewProgramsCount
     * @param  array  $program_ids
     * @param  array  $date
     * @return integer
     */
    public function getNewProgramsCount(array $program_ids, array $date);

    /**
     * countOfActivePrograms
     * @param  array  $program_ids
     * @return integer
     */
    public function countActivePrograms(array $program_ids, array $date);

    /**
     * getUsersPermittedCFByIds
     * @param  array  $program_ids
     * @return collection
     */
    public function getUsersPermittedCFByIds(array $program_ids);

    /**
     * getAboutExpirePrograms
     * @param  array $date
     * @return array
     */
    public function getAboutExpirePrograms($date);

    /**
     * getProgramPacketsQuizzs
     * @param  array $program_ids
     * @return array
     */
    public function getProgramPacketsQuizzs($program_ids);

    /**
     * getProgramsDetailsById
     * @param  array $program_ids
     * @return array
     */
    public function getProgramsDetailsById($program_ids);

    /**
     * getFtpConnectionDetails
     * @return array
     */
    public function getFtpConnectionDetails();

    /**
     * getFtpDirUsergroupToPackageDetails
     * @param array $ftp_conn_id
     * @return array
     */
    public function getFtpDirUsergroupToPackageDetails($ftp_conn_id);

    /**
     * getUsergroupToPackageFileColumns
     * @param array $ftp_connection_details
     * @param array $ftp_dir_details
     * @return array
     */
    public function getUsergroupToPackageFileColumns($ftp_connection_details, $ftp_dir_details);

    /**
     * validateErpEnrolRules
     * @param array $csvrowData
     * @return boolean
     */
    public function validateErpEnrolRules($csvrowData);

    /**
     * getFtpDirUserToChannelDetails
     * @param array $ftp_conn_id
     * @return array
     */
    public function getFtpDirUserToChannelDetails($ftp_conn_id);

    /**
     * getUserToChannelFileColumns
     * @param array $ftp_connection_details
     * @param array $ftp_dir_details
     * @return array
     */
    public function getUserToChannelFileColumns($ftp_connection_details, $ftp_dir_details);

    /**
     * getFtpDirPackageDetails
     * @param array $ftp_conn_id
     * @return array
     */
    public function getFtpDirPackageDetails($ftp_conn_id);

    /**
     * getPackageFileColumns
     * @param array $ftp_connection_details
     * @param array $ftp_dir_details
     * @return array
     */
    public function getPackageFileColumns($ftp_connection_details, $ftp_dir_details);

    /**
     * validateErpPackageRules
     * @param array $csvrowData
     * @return boolean
     */
    public function validateErpPackageRules($csvrowData, $type);

    /**
     * getFtpDirPackageUpdateDetails
     * @param array $ftp_conn_id
     * @return array
     */
    public function getFtpDirPackageUpdateDetails($ftp_conn_id);

    /**
     * getUpdatePackageFileColumns
     * @param array $ftp_connection_details
     * @param array $ftp_dir_details
     * @return array
     */
    public function getUpdatePackageFileColumns($ftp_connection_details, $ftp_dir_details);

    /**
     * getFtpDirChannelDetails
     * @param array $ftp_conn_id
     * @return array
     */
    public function getFtpDirChannelDetails($ftp_conn_id);

    /**
     * getChannelFileColumns
     * @param array $ftp_connection_details
     * @param array $ftp_dir_details
     * @return array
     */
    public function getChannelFileColumns($ftp_connection_details, $ftp_dir_details);

    /**
     * getUpdateChannelFileColumns
     * @param array $ftp_connection_details
     * @param array $ftp_dir_details
     * @return array
     */
    public function getUpdateChannelFileColumns($ftp_connection_details, $ftp_dir_details);

    /**
     * getFtpDirChannelUpdateDetails
     * @param array $ftp_connection_details
     * @param array $ftp_dir_details
     * @return array
     */
    public function getFtpDirChannelUpdateDetails($ftp_conn_id);

    /**
     * Method to prepare erp program data
     * @param int $id
     * @param array $data
     * @param string $type
     * @param int $cron
     * @return nothing
     */
    public function getPrepareErpProgramData($id, $data, $type, $cron);

    /**
     * getUsergroupUserRelationBySlug
     * @param array $program_slug
     * @return array
     */
    public function getUsergroupUserRelationBySlug($program_slug);

    /**
     * getUserEnrollmentsAndCategories
     * @param string user_id
     * @return array
     */
    public function getUserEnrollmentsAndCategories($uid, $page_no);

    /**
     * getAllCategoryWithChannel
     * @param array channels_list
     * @return array
     */
    public function getAllCategoryWithChannel($channels_list, $page_no = null);

    /**
     * getAllProgramsAssignedToSiteAdmin
     * @return array channels_list
     */
    public function getAllProgramsAssignedToSiteAdmin();

    /**
     * @param array $packets
     * @return array
     */
    public function getProgramDetailsBySlug($packets);

    /**
     * @param string $attribute
     * @param string $value
     * @param string $status
     * @return collection
     */
    public function getProgramByChannelSlug($attribute, $value, $status);

    /**
     * @return update data
     */
    public function getImportUserToChannelMapping();

    /**
     * @param array $programs
     * @param string $value
     * @param array $sub_program_slugs
     * @return collection
     */
    public function getAllDetailsOfProgram($programs, $order_by, $sub_program_slugs);

    /**
     * getNewPrograms
     * @param  array  $program_ids
     * @param  array  $date
     * @param  integer $start
     * @param  integer  $limit
     * @return collection
     */
    public function getNewPrograms(array $program_ids, array $date, $start, $limit);

    /**
     * getActivePrograms
     * @param  array  $program_ids
     * @param  integer $start
     * @param  integer  $limit
     * @return collection
     */
    public function getActiveProgramsDetails(array $program_ids, $start, $limit);

    /**
     * @param  array  $program_slugs
     * @param  array  $columns
     * @return collection
     */
    public function getProgramsBySlugs(array $program_slugs, array $columns = []);

    /**
     * @param  array $program_ids
     * @return integer
     */
    public function countActiveChannels($program_ids);

    /**
     * @param  array $program_ids
     * @return integer
     */
    public function countInActiveChannels($program_ids);

    /**
     * @param  array $channel_ids
     * @return collection
     */
    public function getOnlyProgramIds($channel_ids);

    /**
     * @return collection
     */
    public function getAllUndeletedPrograms();
    
    /**
     * @return collection
     */
    public function getAllActiveChannels();

    /**
     * @param  int $program_id
     * @return collection
     */
    public function getChannelByProgramId($program_id);

    /**
     * @param  int $program_id
     * @return collection
     */
    public function getAllProgramByIDOrSlug($type = 'all', $slug = '', $filter_params = []);

    /**
     * @param  array $program_ids
     * @return array
     */
    public function getVisibleProgramids($program_ids);
}
