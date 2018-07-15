<?php

namespace App\Model\Package\Repository;

interface IPackageRepository
{
    /**
     * Find package using unique id
     *
     * @var int $id
     *
     * @param array $columns
     * @return \App\Model\Package\Entity\Package
     */
    public function find($id, $columns = ["*"]);

    /**
     * @param string $attribute
     * @param int|string|array|boolean $value
     * @return \App\Model\Package\Entity\Package
     *
     * @throws \App\Exceptions\Package\PackageNotFoundException
     */
    public function findByAttribute($attribute, $value);

    /**
     * @param int $package_id
     * @param array $program_filter_params
     * @return int
     */
    public function getPackageProgramsCount($package_id, $program_filter_params = []);

    /**
     * @param int $package_id
     * @param array $package_columns
     * @param array $program_filter_params
     * @param array $program_columns
     * @return \App\Model\Package\Entity\Package
     */
    public function findPackageWithPrograms(
        $package_id,
        $program_filter_params = [],
        $package_columns = ["*"],
        $program_columns = ["*"]
    );

    /**
     * Get packages using filter params
     * @param array $filter_params
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get($filter_params = [], $columns = []);

    /**
     * @param array $filter_params
     * @return int
     */
    public function getCount($filter_params = []);

    /**
     * insert package method using to store all the package data.
     * @param array $package_data
     * @return \App\Model\Package\Entity\Package
     */
    public function createPackage($package_data = []);

    /**
     * Update package.
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
     * @param int $package_id
     * @param array $program_ids
     * @return \App\Model\Package\Entity\Package
     */
    public function mapPackageAndPrograms($package_id, $program_ids);

    /**
     * @param int $package_id
     * @param array $program_ids
     * @return \App\Model\Package\Entity\Package
     */
    public function unMapPackageAndPrograms($package_id, $program_ids);

    /**
     * map user and package
     * @param int $package_id
     * @param array $user_ids
     * @return \App\Model\Package\Entity\Package
     */
    public function mapUserAndPackage($package_id, $user_ids);

    /**
     * unmap user and package
     * @param int $package_id
     * @param array $user_ids
     * @return \App\Model\Package\Entity\Package
     */
    public function unMapUserAndPackage($package_id, $user_ids);

    /**
     * @param $package_id
     * @param $user_group_ids
     * @return mixed
     */
    public function mapUserGroupAndPackage($package_id, $user_group_ids);

    /**
     * @param $package_id
     * @param $user_group_ids
     * @return mixed
     */
    public function unMapUserGroupAndPackage($package_id, $user_group_ids);

    /**
     * @param $package_id
     *
     * @return \App\Model\Package\Entity\Package
     */
    public function getActivePackages($filter_params = []);

    /**
     * @param int $id
     * @param array $input
     * @param int $cron
     * @return nothing
     */
    public function getPreparePackageData($id, $input, $cron);

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
     * @return nothing exports package to csv file
     */
    public function exportPackages($status = 'ALL', $date = null, $action = 'ALL');

    /**
     * @param string $name
     * @param string $shortname
     * @return int package_id
     */
    public function getPackageId($name, $shortname);

     /**
     * @param int $packageid
     * @param int $groupid
     * @param string $name
     * @param string $shortname
     * @param string $level
     * @param int $cron
     * @return nothing enrols usergroup to program
     */

    /**
     * getProgramsByPackages get Program ids, Related with packages
     * @param  array  $package_ids
     * @return collection of program ids
     */
    public function getProgramsByPackages(array $package_ids);

     /**
     * @param int $groupid
     * @param string $name
     * @param string $shortname
     * @param string $level
     * @param int $cron
     * @return int packageid
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
