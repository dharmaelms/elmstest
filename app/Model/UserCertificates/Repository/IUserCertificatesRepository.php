<?php

namespace App\Model\UserCertificates\Repository;

/**
 * Interface ICertificateRepository
 *
 * @package App\Model\Certificates\Repository
 */
interface IUserCertificatesRepository
{
    /**
     * Method to get users certificate
     *
     * @param int $start
     * @param int $limit
     * @param string $condition
     * @param string $title
     *
     * @return array
     */
    public function getUserCertificates($start, $limit, $condition, $title);

    /**
     * Method to get user certificate data
     * @param  $data
     * @param  string $column
     *
     * @return  array
     */
    public function getDataByColumn($data, $column);

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
