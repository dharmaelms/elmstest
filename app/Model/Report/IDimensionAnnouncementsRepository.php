<?php

namespace App\Model\Report;

interface IDimensionAnnouncementsRepository
{
    /**
     * getChannelSetResult
     * @param  array $announcement_ids
     * @param  integer $start
     * @param  integer $limit
     * @param  integer $start_date
     * @param  integer $end_date
     * @return array
     */
    public function getAnnouncements($announcement_ids = [], $start = 0, $limit = 7, $start_date = 0, $end_date = 0);
}
