<?php

namespace App\Model\AccessRequest;

use App\Model\AccessRequest;

/**
 * class AccessRequestRepository
 * @package App\Model\AccessRequest
 */
class AccessRequestRepository implements IAccessRequestRepository
{
    /**
     * @inheritdoc
     */
    public function getAccessRequestCount(array $program_ids, array $date)
    {
        $query = AccessRequest::whereBetween('created_at', $date)
                    ->where('program_id', '!=', 0);
        if (!empty($program_ids)) {
            $query->whereIn('program_id', $program_ids);
        }
        return $query->count();
    }

    /**
     * @inheritdoc
     */
    public function getAccessRequests(array $program_ids, array $date, $start, $limit)
    {
        $query = AccessRequest::whereBetween('created_at', $date)
                    ->where('program_id', '!=', 0);
        if (!empty($program_ids)) {
            $query->whereIn('program_id', $program_ids);
        }
        return $query->skip((int)$start)
                    ->take((int)$limit)
                    ->orderBy('created_at', 'desc')
                    ->get(['program_title', 'user_name']);
    }

}
