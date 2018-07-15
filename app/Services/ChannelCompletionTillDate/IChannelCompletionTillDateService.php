<?php

namespace App\Services\ChannelCompletionTillDate;

/**
 * interface ChannelCompletionTillDate
 * @package App\Services\ChannelCompletionTillDate
 */
interface IChannelCompletionTillDateService
{
    /**
     * getSpecificChannelsUserCompletion channel completion details as specific channel and for array of users
     * @param  int $channel_id unique channel id
     * @param  array $user_ids   array of users
     * @return collection           list of channel completion details
     */
    public function getSpecificChannelUserCompletion($channel_id, $user_ids);
    /**
     * getUserChannelsCompletionDetails Specific user's and specified array of channel details
     * @param  array $channel_id array channel id
     * @param  int $user_id    uique userid
     * @return collection             list of channel completions details
     */
    public function getUserChannelCompletionDetails($channel_id, $user_id);
}