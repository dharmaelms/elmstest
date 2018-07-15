<?php

namespace App\Model\Program;

/**
 * Interface IProgramRepository
 * @package App\Model\Program
 */
interface IProgramRepository
{
    /**
     * @param int $id
     * @return \App\Model\Program
     *
     * @throws \App\Exceptions\Program\ProgramNotFoundException
     */
    public function find($id);

    /**
     * @param string $type
     * @param string $attribute
     * @param int|string|boolean $value
     * @return \App\Model\Program
     */
    public function findByAttribute($type, $attribute, $value);

    /**
     * @param int $program_id
     * @param int $question_id
     * @return \App\Model\ChannelFaq
     */
    public function findQuestion($program_id, $question_id);

    /**
     * @param array $filter_params
     * @return int
     */
    public function getQuestionCount($filter_params = []);

    /**
     * @param array $filter_params
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getQuestions($filter_params = []);

    /**
     * @param string $program_slug
     * @param string $post_id
     * @return \App\Model\Packet
     *
     * @throw \App\Exceptions\Program\ProgramNotFoundException or
     *        \App\Exceptions\Post\PostNotFoundException
     */
    public function findProgramPost($program_slug, $post_id);

    /**
     * @param string $program_slug
     * @param string $attribute
     * @param int|string $value
     * @return \App\Model\Packet
     *
     * @throw \App\Exceptions\Program\ProgramNotFoundException or
     *        \App\Exceptions\Post\PostNotFoundException
     */
    public function findProgramPostByAttribute($program_slug, $attribute, $value);

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
     * @param int $post_id
     * @param int $question_id
     * @return \App\Model\PacketFaq
     */
    public function findPostQuestion($post_id, $question_id);

    /**
     * Method to get all programs assigned to user
     * @return array
     */
    public function getAllProgramsAssignedToUser();

    /**
     * Method to get programs data with pagination
     *
     * @param  array $program_ids description
     * @param  int $start
     * @param  int $limit
     * @param array $columns optional
     * @return object
     */
    public function getActiveProgramsData($program_ids, $start, $limit, $columns = [], $is_admin = false);

    /**
     * Method to get all programs
     *
     * @param  string $column
     * @param  array $data
     * @param array $columns optional
     * @return object
     */
    public function getProgramsData($column, $data, $columns = []);

    /**
     * Method to get program analytics
     *
     * @param array $program_ids
     * @return  object
     */
    public function getProgramsAnalytics($program_ids);

    /**
     * Method to get program analytics
     *
     * @param int $program_id
     * @return  object
     */
    public function getProgramsAnalyticById($program_id);

    /**
     * Method to get programs faq
     *
     * @param array $program_ids
     * @return  object
     */
    public function getProgramsFaq($program_ids);

    /**
     * Method to get programs faq
     *
     * @param  string $column
     * @param  array $data
     * @param array $columns optional
     * @return object
     */
    public function getProgramsByAttribute($column, $data, $columns = []);

    /**
     * Method to get programs faq
     *
     * @param  string $column
     * @param array $columns optional
     * @return object
     */
    public function getProgramDataByAttribute($column, $data, $columns = []);

    /**
     * Method to query assigned peograms order by program name
     * 
     * @param  array $program_ids description
     * @param  int $start
     * @param  int $limit
     * @param  array $columns optional
     * @return object
     */
    public function getProgramsOrderByName($program_ids, $start, $limit, $columns = [], $is_admin = false);
    /**
     * Method to get active programs slug
     *
     * @return array
     */
    public function getActiveProgramsSlug();

    /**
     * [getCourseTitle Course Name + Batch name]
     * @method getCourseTitle
     * @param  [type]         $program_id    [parent program id]
     * @param  [type]         $program_title [batch program title]
     * @return [type]                        [description]
     */
    public function getCourseTitle($program_id, $program_title);

    /**
     * @param array $filter_params
     * @return int
     */
    public function getCount($filter_params = []);

    /**
     * @param array $filter_params
     * @param array $columns
     * @return mixed
     */
    public function get($filter_params = [], $columns = ["*"]);

    /**
     * @param array $program_slugs
     * @return mixed
     */
    public function getPackets($program_slugs = []);

    /**
     * @param int $id
     * @param array $input
     * @param string $type
     * @param int $cron
     * @return nothing
     */
    public function getPrepareProgramData($id, $input, $type, $cron);

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
    public function enrolUserToProgram($programid, $userid, $name, $shortname, $type, $subtype, $level, $cron);

    /**
     * @param int $programid
     * @param int $groupid
     * @param string $name
     * @param string $shortname
     * @param string $type
     * @param string $subtype
     * @param string $level
     * @param int $cron
     * @return nothing enrols usergroup to program
     */
    public function enrolUsergroupToProgram($programid, $groupid, $name, $shortname, $type, $subtype, $level, $cron);

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
     * getCoursesBySearchkey get the course name and id as per search key with 0 to limited set of records
     * @param  string $search_key search key
     * @param  int sepecific of course
     * @param  integer $start records start from
     * @param  integer $limit records limitting
     * @return collection              course id and name as records collection
     */
    public function getCoursesBySearchKey($search_key, $course_id = 0, $start = 0, $limit = 500);


    /**
     * getProgramsBySearch get list of programs as per searched keys
     * @param  string $search program title search using this key
     * @param  boolean $is_ug_rel for get usergroup related programs
     * @param  integer $start Beginning of the record
     * @param  integer $limit Max number of record need to get
     * @return Collection          list of program as program_title and program_id
     */
    public function getProgramsBySearch($search = '', $is_ug_rel = false, $start = 0, $limit = 500);

    /**
     * getPackagesBySearch get list of packages as per searched keys
     * @param  string $search program title search using this key
     * @param  boolean $is_ug_rel for get usergroup related programs
     * @param  integer $start Beginning of the record
     * @param  integer $limit Max number of record need to get
     * @return Collection    list of program as program_title and program_id
     */
    public function getPackagesBySearch($search = '', $is_ug_rel = false, $start = 0, $limit = 500);

    /**
     * getPackageProgramsBySearch get list pf channel blongs to specified package's
     * @param  string $search program title search using this key
     * @param  integer $package_id unique channel/package id
     * @param  integer $start Beginning of the record
     * @param  integer $limit Max number of record need to get
     * @return Collection          list of program as program_title and program_id
     */
    public function getPackageProgramsBySearch($search = '', $package_id = 0, $start = 0, $limit = 500);

    /**
     * getCFDetailsById get channel details as specific channel
     * @param  int $feed_id unique channel_id
     * @return Collection          Channel's details
     */
    public function getCFDetailsById($feed_id);

    /**
     * getProgramById get channel details as specific channel as null or object
     * @param int $program_id unique channel_id
     * @return Collection           Channel's details as collection object or null
     */
    public function getProgramById($program_id);

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
     * Method to update program details
     * @param array $program_data
     * @param string $old_slug
     * @param string $new_slug
     * @param int $cron
     * @returns nothing updates program information
     */
    public function updateProgramDetails($program_data,$old_slug,$new_slug,$cron);

    /**
     * Method to get display active programs
     *
     * @param array $filter_params
     * @return \App\Model\Package\Entity\Package
     */
    public function displayActivePrograms($filter_params = [], $skip = 0, $limit = 0);

    /**
     * @param string $program_type
     * @param string $program_sub_type
     * @param array $filter
     * @param array $custom_field_name
     * @param array $custom_field_value
     */
    public function exportChannels($program_type, $program_sub_type, $filter, $custom_field_name, $custom_field_value);

    /**
     * getActiveProgramsCount
     * @param  array  $program_ids
     * @return integer
     */
    public function getActiveProgramsCount(array $program_ids, array $date);

    /**
     * getNewProgramsCount
     * @param  array  $program_ids
     * @param  array  $date
     * @return integer
     */
    public function getNewProgramsCount(array $program_ids, array $date);

    /**
     * getSlugsByIds
     * @param  array  $program_ids
     * @return array
     */
    public function getSlugsByIds(array $program_ids);

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
     * @param string $type
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
     * getFtpDirChannelUpdateDetails
     * @param array $ftp_conn_id
     * @return array
     */
    public function getFtpDirChannelUpdateDetails($ftp_conn_id);

    /**
     * getUpdateChannelFileColumns
     * @param array $ftp_connection_details
     * @param array $ftp_dir_details
     * @return array
     */
    public function getUpdateChannelFileColumns($ftp_connection_details, $ftp_dir_details);

    /**
     * getUsergroupUserRelationBySlug
     * @param array $program_slug
     * @return array
     */
    public function getUsergroupUserRelationBySlug($program_slug);

    /**
     * @param array $program_slugs
     * @return array
     */
    public function pluckFeedDetails($program_slugs);

    /**
     * Method to search multiple programs using program_id
     *
     * @param array $ids
     * @param array $columns - optional
     * @return mixed
     */
    public function getProgramsByIds($ids, $columns = []);
    /**
     * getProgramIdBySlug
     * @param  String $program_slug
     * @return collection
     */
    public function getProgramIdBySlug($program_slug);

    /**
     * @param string $attribute
     * @param string $value
     * @param string $status
     * @return collection
     */
    public function getProgramByChannelSlug($attribute, $value, $status);

    /**
     * @param string $program_id
     * @return array
     */
    public function getProgram($program_id);

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
    public function getAllProgramByIDOrSlug($type, $slug, $filter_params);

    /**
     * @param  array $program_ids
     * @return array
     */
    public function getVisibleProgramids($program_ids);
}
