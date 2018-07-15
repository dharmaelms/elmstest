<?php

namespace App\Services\Announcement;

/**
 * Interface IAnnouncementService
 * @package App\Services\Announcement
 */
interface IAnnouncementService
{
    /**
     * Method to get announcements with page and limit
     *
     * @param int $start
     * @param int $limit
     * @return array
     */
    public function getAnnouncements($start, $limit);

    /**
     * @param $start_id
     * @param $limit
     * @param $device
     * @return mixed
     */
    public function getAllPublicAnnouncements($start_id, $limit, $device);

    /**
     * @return mixed
     */
    public function getAllPrivateAnnouncements();

    /**
     * @param $user_id
     * @param $announcement_ids
     * @param string $device
     * @return mixed
     */
    public function getUnReadPublicAnnouncementsCount($user_id, $announcement_ids, $device = 'web');

    /**
     * @param $filter_params
     * @return mixed
     */
    public function getAllAnnouncements($filter_params = []);

   /**
     * Method to get all announcements created by username
     *
     * @param array $usernames
     * @return array
     */
    public function getAnnouncementsCreatedByUsers($usernames = []);

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
