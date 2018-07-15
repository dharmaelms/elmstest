<?php

namespace App\Model\Report;

interface IFactChannelUserQuizRepository
{
    /**
     * getQuizPerformanceInChannels
     * @param  array $channel_ids
     * @param  integer $start_date
     * @param  integer $end_date
     * @param  integer $user_id
     * @return collection
     */
    public function getQuizPerformanceInChannels(
        $channel_ids,
        $start_date,
        $end_date,
        $user_id = 0
    );
}
