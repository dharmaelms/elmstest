<?php

namespace App\Services\DimensionChannel;

/**
 * interface IDimensionChannelService
 * @package App\Services\DimensionChannel
 */
interface IDimensionChannelService
{
    /**
     * getChannelsDetails Get specified channels details
     * @param  array  $channel_ids description
     * @return collection  diamension channel details
     */
    public function getChannelsDetails($channel_ids = []);

     /**
     * isExist
     * @param  integer $channel_id
     * @return boolean
     */
    public function isExist($channel_id = 0);

    /**
     * getChannelSlugsNameAndIds
     * @return array
     */
    public function getChannelSlugsNameAndIds();

    /**
     * getChannelsFullName
     * @param  string  $search_key
     * @param  array   $channel_ids
     * @param  integer $limit
     * @param  integer $start
     * @param  String  $sort_by
     * @return collection array
     */
    public function getChannelsFullName($search_key, $channel_ids, $limit, $start = 0, $sort_by = 'channel_name');
}
