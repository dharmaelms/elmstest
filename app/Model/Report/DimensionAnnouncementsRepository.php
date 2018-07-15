<?php

namespace App\Model\Report;

use App\Model\DimensionAnnouncements;

class DimensionAnnouncementsRepository implements IDimensionAnnouncementsRepository
{
    /**
     * @inheritdoc
     */
    public function getAnnouncements($announcement_ids = [], $start = 0, $limit = 7, $start_date = 0, $end_date = 0)
    {
        $time_line = [(int)$start_date, (int)$end_date];
        $query = DimensionAnnouncements::WhereBetween('create_date', $time_line)
            ->orWhereBetween('update_date', $time_line);
        if (!empty($announcement_ids)) {
            $query->whereIn('announcement_id', $announcement_ids);
        }
        if ($limit > 0) {
            $query->skip((int)$start)
                ->take((int)$limit);
        }
        return $query->get();
    }
}
