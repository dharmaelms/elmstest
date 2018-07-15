<?php

namespace App\Services\Report;

interface IDimensionChannelUserQuizService
{
    /**
     * getQuizzesByChannel
     * @param  array $channel_ids
     * @param  array $start
     * @param  array $limit
     * @return array
     */
    public function getQuizzesByChannel(array $channel_ids, $start, $limit);
}
