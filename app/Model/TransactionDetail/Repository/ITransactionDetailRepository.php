<?php

namespace App\Model\TransactionDetail\Repository;

/**
 * interface ITransactionDetailRepository
 * @package App\Model\TransactionDetail\Repository
 */
interface ITransactionDetailRepository
{
    /**
     * getUseridByProgramId get list of users by sepcified program, and entrolled on specified dates
     * @param  int $program_id           unique program id
     * @param  array $date_range_timestamp start and end dates as timestamp
     * @return collection                       list of userid and created_at
     */
    public function getUseridByProgramId($program_id, $date_range_timestamp, $trans_level);

    /**
     * getProgramsByUserWithInTime get list of programs by userid and entrolled on specified dates
     * @param  int $user_id              unique user id
     * @param  array $date_range_timestamp start and end dates as timestamp
     * @return collection                       details of transation_details tables as filtered
     */
    public function getProgramsByUserWithInTime($user_id, $date_range_timestamp);

    /**
     * getTransDetailsByProgramForUsers get Transactuion details by specific program for all time
     * @param  int $program_id unique program id
     * @param array $total_users array of user ids
     * @return collection             transactuion details
     */
    public function getTransDetailsByProgramForUsers($program_id, $total_users);

     /**
     * getProgramsByUserWithInTime get list of programs by userid and entrolled on specified dates
     * @param  array $ug_ids
     * @param  array $date_range_timestamp start and end dates as timestamp
     * @return collection                       details of transation_details tables as filtered
     */
    public function getProgramsByUserGroupWithInTime($ug_ids, $date_range_timestamp);

    /**
     * @param  int $programid
     * @param  int $id
     * @return collection
     */
    public function getDetailsByProgramDetails($programid, $id);
}