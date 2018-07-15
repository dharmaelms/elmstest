<?php

namespace App\Services\UserCertificate;

/**
 * Interface ICertificateService
 *
 * @package App\Services\Ceritificate
 */
interface IUserCertificateService
{

    /**
     * Method to process certificate generation
     * @param $channel_id
     * @param $user_id
     */
    public function process($channel_id, $user_id);

    /**
     * Method to get list of records with completion percentage 100%
     *
     * @param integer $channel_id
     * @return array
     */
    public function getChannelBenchmark($channel_id);

    /**
     * Method to get channel analytic data
     * @param integer $channel_id
     * @param integer $user_id
     *
     * @return object $data
     */
    public function getChannelAnalyticsData($channel_id, $user_id);

    /**
     * Method to validate user score against benchmark
     *
     * @param array $program_data
     * @param array $analytic_data
     */
    public function validateBenchmark($program_data, $analytic_data);

    /**
     * Method to generate pdf
     * @param  array $analytic_data
     * @param  array $program_data
     * @return boolean
     */
    public function generateCertificate($analytic_data, $program_data);

    /**
     * Method to get user certificates
     * @param  int $page
     * @param  int $limit
     * @param  string $condition query condition
     * @param  string $title search string
     *
     * @return array
     */
    public function listUserCertificates($page, $limit, $condition, $title);

    /**
     * Method to generate certificate
     * @param  array $analytic_data
     * @param  array $channel_data
     *
     * @return
     */
    public function getCertificateContent($analytic_data, $channel_data);

    /**
     * Method to put entry
     * @param  int $channel_id
     * @param  int $user_id
     *
     * @return  boolean
     */
    public function updateChannelAnalytics($channel_id, $user_id);

    /**
     * Method to get certificate details
     * @param  string $id
     *
     * @return object
     */
    public function getUserCertificateById($id);

    /**
     * getSPecifiedChannelCertifiedUser get certificate status as per channel by array of users
     * @param  int $channel_id unique channel id
     * @param  array $user_ids   users list
     * @return collection             certified user list
     */
    public function getSpecifiedChannelCertifiedUsers($channel_id, $user_ids);

    /**
     * @param  int $program_id
     * @param  array $orderby
     * @return collection
     */
    public function getCertificatesByProgramId($program_id, $column, $orderby = ['created_at' => 'desc'], $start = 0, $limit = 0);

    /**
     * @param  int $user_id
     * @param  int $progarm_id
     * @return collection
     */
    public function getCertificateByUserAndProgramId($user_id, $progarm_id);

    /**
     * @param  int $progarm_id
     * @return int
     */
    public function getCountByProgramId($program_id);

    /**
     * @param  int $progarm_id
     * @param  array $user_ids
     * @param  array $column
     * @return collection
     */
    public function getCertificatesByProgramAndUsers($program_id, $user_ids, $column);

    /**
     * @param  int $progarm_id
     * @param  array $user_ids
     * @param  array $column
     * @param  array $orderby
     * @param  int $start
     * @param  int $limit
     * @return collection
     */
    public function getCertifiedUsersLists($program_id, $user_ids, $column, $orderby, $start, $limit);
}
