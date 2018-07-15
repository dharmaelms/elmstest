<?php

namespace App\Model\Report;

interface ITillQuizPerformanceRepository
{
    /**
     * findIndChannelPerformance
     * @param  int $channel_id
     * @param  int $start
     * @return array
     */
    public function findIndChannelPerformance($channel_id, $start);
}
