<?php namespace App\Services\Report;

interface IExportReportService
{
    /**
    * prepareUserActivityByCourse Making matric of user's activity of program as specific course/channel/packages's channel inbetween specified date ranges, Eg. completion status, last score, actual duration and certificate status
    * @param  int $channel_id unique identity of channel
    * @param  string $date_range timestamp date values
    * @param  int $package_id unique identity of package
    * @param  string $filter_by
    * @param  string $date_string
    * @return boolean
    */
    public function prepareUserActivityByCourse($channel_id, $date_range, $package_id, $filter_by, $date_string);

    /**
     * prepareCourseActivityByUser Making matric of specific user's, specified dates entrolled course/channel activities. Eg. completion status, last score, actual duration and enrollment date
     * @param  int $user_id    unique identity of user
     * @param  int array $date_range timestamp date values
     * @param  string $date_string
     * @return boolean
     */
    public function prepareCourseActivityByUser($user_id, $date_range, $date_string);
    
    /**
     * prepareCourseActivityByGroup Making matric of specific channel's and specified usergroup's each user's activities Eg. completion status, last score, actual duration and enrollment date
     * @param  int $group_id   unique identity of usergroup
     * @param  int $channel_id unique identity of channel
     * @param  int $package_id unique identity of package
     * @param  string $filter_by  program type
     * @return boolean
     */
    public function prepareCourseActivityByGroup($group_id, $channel_id, $package_id, $filter_by);
    
    /**
     * prepareGroupSummary, Making matric of, In specified date creatd usergroup's summary reports Eg.total users, active and inactive users and count of assigned programs
     * @param  int array $create_date_range timestamp date values
     * @param  string $date_string
     * @return boolean
     */
    public function prepareGroupSummary($create_date_range, $date_string);
    
    /**
     * prepareDetailedByGroup Making matric of, Specified usergroup's users details Eg. user name and status
     * @param  int $group_id          unique identity of usergroup
     * @return boolean
     */
    public function prepareDetailedByGroup($group_id);
    
    /**
     * preparePostlevelCompletion Making matric of, Post level completion of selected channel's, In between selected dates activities of users Eg. fullname, over all completion percentage, and each post completion percentage.
     * @param  int $channel_id unique identity of channel
     * @param  array $date_range timestamp date values
     * @param  int $package_id unique identity of package or course
     * @param  string $filter_by program type
     * @param  string $date_string
     * @return boolean
     */
    public function preparePostLevelCompletion($channel_id, $date_range, $package_id, $filter_by, $date_string);

    /**
     * @param  array $date_timestamp
     * @param  string $date_range
     * @param  boolean $is_completed
     * @return boolean
     */
    public function prepareProgramsCompletion($date_timestamp, $date_range, $is_completed);

    /**
     * writeReportsTitle
     * @param  array $title_details
     * @return file object $file_pointer
     */
    public function writeReportsTitle($title_details);

}
