<?php

namespace App\Model\Announcement;

use App\Exceptions\User\RelationNotFoundException;

/**
 * Interface IAnnouncementRepository
 *
 * @package App\Model\Announcement
 */
interface IAnnouncementRepository
{
    /**
     * Method to get user announcements
     * @param int $page page number
     * @param int $limit no of results
     */
    public function getUserAnnouncements($page, $limit);

    /**
     * Method to get all announcements assigned to user
     * @return array
     * @throws RelationNotFoundException
     */
    public function getAllPrivateAnnouncements();

    /**
     * @param $start
     * @param $limit
     * @param $device
     * @return mixed
     */
    public function getAllPublicAnnouncements($start, $limit, $device);

    /**
     * @param $user_id
     * @param $announcement_ids
     * @param $device
     * @return mixed
     */
    public function getUnReadPublicAnnouncementsCount($user_id, $announcement_ids, $device);

     /**
     * @param $filter_params
     * @return mixed
     */
    public function getAllAnnouncements($filter_params = []);

    /**
     * Method to get all announcements  created by username
     *
     * @param array $usernames
     * @return Object
     */
    public function getAnnouncementsCreatedByUsers($usernames = []);

    /**
     * @param array $filter_params
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get($filter_params = []);

    /**
     * Method to get all announcements with pagination
     * @param int $start start record number
     * @param int $limit no of results
     * @param $columns
     * @return object
     */
    public function getAdminUserAnnouncements($start, $limit, $columns = []);

    /**
     * getNewAnnouncementCount
     * @param  array  $announcement_ids
     * @param  array  $date
     * @return integer
     */
    public function getNewAnnouncementCount(array $announcement_ids, array $date);

    /**
     * getNewAnnouncements
     * @param  array  $announcement_ids
     * @param  array  $date
     * @param  integer $start
     * @param  integer  $limit
     * @return integer
     */
    public function getNewAnnouncements(array $announcement_ids, array $date, $start, $limit);
}
