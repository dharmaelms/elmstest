<?php

namespace App\Services\Package;

/**
 * Interface IPackageService
 *
 * @package App\Services\Package
 */
interface IPackageService
{
    /**
     * @param array $filter_params
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPackages($filter_params = [], $columns = []);

    /**
     * @param array $filter_params
     * @return int
     */
    public function getPackagesCount($filter_params = []);

    /**
     * @param array $package_data
     * @return \App\Model\Package\Entity\Package
     */
    public function createPackage($package_data = []);

    /**
     * @param int $package_id
     * @param array $package_data
     * @return \App\Model\Package\Entity\Package
     */
    public function updatePackage($package_id, $package_data = []);

    /**
     * @param int $package_id
     * @return \App\Model\Package\Entity\Package
     */
     public function deletePackage($package_id);

    /**
     * To get package details using key value
     * @param string $attribute
     * @param string $value
     * @return \App\Model\Package\Entity\Package
     */
    public function getPackageBySlug($attribute, $value);

    /**
     * @param int $package_id
     * @param array $program_ids
     * @return \App\Model\Package\Entity\Package
     */
    public function assignProgramsToPackage($package_id, $program_ids);

    /**
     * @param int $package_id
     * @param array $program_ids
     * @return \App\Model\Package\Entity\Package
     */
    public function unAssignProgramsFromPackage($package_id, $program_ids);

    /**
     * @param int $package_id
     * @param array $package_columns
     * @param array $program_filter_params
     * @param array $program_columns
     * @return array
     */
    public function getPackageWithAssignedPrograms(
        $package_id,
        $program_filter_params = [],
        $package_columns = ["*"],
        $program_columns = ["*"]
    );

    /**
     * @param int $package_id
     * @param array $package_columns
     * @param array $program_filter_params
     * @param array $program_columns
     * @return array
     */
    public function getPackageWithNoNAssignedPrograms(
        $package_id,
        $program_filter_params = [],
        $package_columns = ["*"],
        $program_columns = ["*"]
    );

    /**
     * @param int $package_id
     * @param array $filter_params
     * @return array
     */
    public function getPackageUsers($package_id, $filter_params = []);

    /**
     * @param int $package_id
     * @param array $filter_params
     * @return array
     */
    public function getPackageUserGroups($package_id, $filter_params = []);

    /**
     * @param int $package_id
     * @param array $user_ids
     * @return \App\Model\package\Entity\package
     */
    public function mapUserAndPackage($package_id, $user_ids);

    /**
     * @param int $package_id
     * @param array $user_ids
     * @return \App\Model\package\Entity\package
     */
    public function unMapUserAndPackage($package_id, $user_ids);

    /**
     * @param int $package_id
     * @param array $user_group_ids
     * @return \App\Model\package\Entity\package
     */
    public function mapUserGroupAndPackage($package_id, $user_group_ids);

    /**
     * @param int $package_id
     * @param array $user_group_ids
     * @return \App\Model\package\Entity\package
     */
    public function unMapUserGroupAndPackage($package_id, $user_group_ids);

    /**
     * @param $package_id
     * @return \App\Model\User\Entity\UserEnrollment
     */
    public function getPackageActiveUserRelations($package_id);

    /**
     * Method to get packages list with pagination
     *
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @param array $categories
     * @param array $others
     * @return Response
     */
    public function getPackageList($page, $limit, $filter, $categories = [], $others = []);

    /**
     * Method to get assigend to user
     */
    public function getUserPackages();

    public function getPackageUsersCount($package_id);

    /**
     * Method to get package detail with categories and channels by its slug
     *
     * @param string $slug
     * @return App|Model|Package
     */
    public function getPackageDetails($slug);
    /**
     * Method to prepare erp package data
     * @param int $id
     * @param array $data
     * @param int $cron
     * @return nothing
     */
    public function getPrepareErpPackageData($id, $data, $cron);

    /**
     * @param array $logdata
     * @param string $status
     * @param string $slug
     * @param string $action
     * @param int $cron
     * @return nothing sends email & insert log
     */
    public function postPackageLog($logdata, $status, $slug, $action, $cron);

     /**
     * Method to prepare erp package log data
     * @param array $data
     * @param string $status
     * @param int $cron
     * @return array
     */
    public function getErpLogdata($data, $status, $cron);

    /**
     * @param string $status
     * @param string $slug
     * @param string $reason
     * @param string $action
     * @return nothing sends email to support email
     */
    public function packageImportEmail($status, $slug, $reason = null, $action);

     /**
     * @param string $name
     * @param string $action
     * @return string $filename
     */
    public function getFileName($name, $action);

     /**
     * Method to get all package fields to insert update package log
     * @param array $logdata
     * @param array $fields
     * @returns all fields with values
     */
    public function getMissingPackageFields($logdata, $fields);

    /**
     * generatePackageSlug prepares package slug
     * @param string $name
     * @param string $shortname
     * @returns package slug
     */
    public function generatePackageSlug($name, $shortname);

    /**
     * Method to update package details
     * @param array $program_data
     * @param string $old_slug
     * @param string $new_slug
     * @param int $cron
     * @returns nothing updates package information
     */
    public function updatePackageDetails($program_data, $old_slug, $new_slug, $cron);

    /**
     * @param string $status
     * @param string $date
     * @return nothing exports programs to csv file
     */
    public function exportPackages($status = 'ALL', $date = null, $action = 'ALL');

    /**
     * @param array $logdata
     * @param string $status
     * @param string $slug
     * @param string $action
     * @param int $cron
     * @return nothing sends email & insert log
     */
    public function postUsergroupPackLog($logdata, $status, $slug, $action, $cron);

    /**
     * @param string $status
     * @param string $slug
     * @param string $reason
     * @param string $action
     * @return nothing sends email to support email
     */
    public function enrolUsergroupToPackageEmail($status, $slug, $reason = null, $action);

    /**
     * @param string $name
     * @param string $shortname
     * @return int package_id
     */
    public function getPackageId($name, $shortname);

    /**
     * @return int programid
     * @param int $groupid
     * @param string $name
     * @param string $shortname
     * @param string $level
     * @param int $cron
     * @return nothing enrols usergroup to program
     */
    public function enrolUsergroupToPackage($packageid, $groupid, $name, $shortname, $level, $cron);

    /**
     * validateErpPackages
     * @param array $csvrowData
     * @return boolean
     */
    public function validateErpPackages($csvrowData);

    /**
     * @param string $enrol_level
     * @param string $status
     * @param string $date
     * @return nothing exports ug-package to csv file
     */
    public function usergroupPackageExport($enrol_level, $status = 'ALL', $date = null);

    /**
     * getPackagesBySearch
     * @param  string  $search
     * @param  boolean $is_ug_rel
     * @param  integer $start
     * @param  integer $limit
     * @return collection
     */
    public function getPackagesBySearch($search = '', $is_ug_rel = false, $start = 0, $limit = 500);
    
    /**
     * @param  string  $attribute
     * @param  string $value
     * @param  string $status
     * @return collection
     */
    public function getPackageByAttribute($attribute, $value, $status);
}
