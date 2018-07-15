<?php

namespace App\Services\Report;

interface IDimensionUserService
{
    /**
     * getSpecificUserDetail
     * @param  integer $user_id
     * @return array
     */
    public function getSpecificUserDetail($user_id = 0);

    /**
     * getUserNameListByids
     * @param  array  $user_ids
     * @param  integer $start
     * @param  integer $limit
     * @return array
     */
    public function getUserNameListByids($user_ids, $start, $limit);

    /**
     * getChannelsUsers
     * @param  array  $channel_ids
     * @return array
     */
    public function getChannelsUsers($channel_ids);

    /**
     * getChannelsUserIds
     * @param  array $channel_ids
     * @return array
     */
    public function getChannelsUserIds($channel_ids);
}
