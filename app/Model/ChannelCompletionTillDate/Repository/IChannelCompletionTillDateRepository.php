<?php

namespace App\Model\ChannelCompletionTillDate\Repository;

/**
 * interface IChannelCompletionTillDateRepository
 * @package App\Model\ChannelCompletionTillDate\Repository
 */
interface IChannelCompletionTillDateRepository
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

}
