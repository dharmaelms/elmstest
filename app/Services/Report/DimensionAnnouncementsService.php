<?php

namespace App\Services\Report;

use App\Model\Report\IDimensionAnnouncementsRepository;

class DimensionAnnouncementsService implements IDimensionAnnouncementsService
{
    private $dim_announce_repo;

    public function __construct(
        IDimensionAnnouncementsRepository $dim_announce_repo
    ) {
        $this->dim_announce_repo = $dim_announce_repo;
    }
    
    /**
     * @inheritdoc
     */
    public function getAnnouncements($announcement_ids = [], $start = 0, $limit = 7, $start_date = 0, $end_date = 0)
    {
        return $this->dim_announce_repo->getAnnouncements(
            $announcement_ids,
            $start,
            $limit,
            $start_date,
            $end_date
        );
    }
}
