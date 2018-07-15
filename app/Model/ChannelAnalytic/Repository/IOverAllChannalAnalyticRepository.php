<?php

namespace App\Model\ChannelAnalytic\Repository;

/**
 * interface IChannelCompletionTillDateRepository
 * @package App\Model\ChannelAnalytic\Repository
 */
interface IOverAllChannalAnalyticRepository
{
    /**
     * getSpecificChannelsUserCompletion channel completion details as specific channel and for array of users
     * @param  int $channel_id unique channel id
     * @param  array $user_ids   array of users
     * @return collection             list of channel completion details
     */
    public function getSpecificChannelUserCompletion($channel_id, $user_ids);
    /**
     * getUserChannelsCompletionDetails Specific user's and specified array of channel details
     * @param  array $channel_id array channel id
     * @param  int $user_id    uique userid
     * @return collection            list of channel completions details
     */
    public function getUserChannelCompletionDetails($channel_id, $user_id);

    /**
     * findChannelPerformance
     * @param  array $channel_ids
     * @param  int $start
     * @param  boolean $is_completion
     * @return array
     */
    public function findChannelPerformanceOrComp(array $channel_ids, $start, $is_completion);

    /**
     * @param  int $channel_id
     * @param  array  $post_ids
     * @param  int $start
     * @param  int $limit
     * @return array
     */
    public function findIndChannelCompletion($channel_id, $post_ids, $start, $limit);

    /**
     * findUsersPerformanceAndCompletion
     * @param  array  $user_ids
     * @param  array  $channel_ids
     * @param  array  $order_by
     * @param  integer $start
     * @param  integer $limit
     * @return array
     */
    public function findUsersPerformanceAndCompletion(array $user_ids, array $channel_ids, array $order_by, $start, $limit);

    /**
     * usersPerformanceCount
     * @param  array  $user_ids
     * @param  array  $channel_ids
     * @return int
     */

    public function usersPerformanceCount(array $user_ids, array $channel_ids);

    /**
     * findUserChannelPerformanceOrComp
     * @param  array   $channel_ids
     * @param  integer $user_id
     * @param  boolean $is_completion
     * @return array
     */
    public function findUserChannelPerformanceOrComp(array $channel_ids, $user_id, $is_completion);

    /**
     * findCompletedChannels
     * @param  array|string $date_timestamp
     * @param boolean $is_completed
     * @param boolean $get_count
     * @param integer $start
     * @param integer $limit
     * @return collection
     */
    public function findCompletedChannels($date_timestamp, $is_completed, $get_count, $start, $limit);

    /**
     * @param int $channelID
     * @param int $userID
     * @return collection
     */
    public function isExists($channelID, $userID);

    /**
     * @param array $data
     * @return boolean
     */
    public function insertData($data);

    /**
     * @param array $data
     * @param int $channel_id
     * @param int $user_id
     * @return boolean
     */
    public function updateData($data, $channel_id, $user_id);
}
