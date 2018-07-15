<?php

namespace App\Model\TransactionDetail\Repository;

use App\Model\TransactionDetail;

/**
 * class TransactionDetailRepository
 * @package App\Model\TransactionDetail\Repository
 */
class TransactionDetailRepository implements ITransactionDetailRepository
{
    /**
     * {@inheritdoc}
     */
    public function getUseridByProgramId($program_id, $date_range_timestamp, $trans_level)
    {
        $query = TransactionDetail::where('program_id', '=', (int)$program_id);
        if (is_array($date_range_timestamp)) {
            $query->where('created_at', '>=', (int)array_get($date_range_timestamp, 'start_date'))
                ->where('created_at', '<=', (int)array_get($date_range_timestamp, 'end_date'));
        }
        return $query->where('trans_level', '=', $trans_level)
                ->where('status', '=', 'COMPLETE')
                ->get(['id', 'created_at']);
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramsByUserWithInTime($user_id, $date_range_timestamp)
    {
        $query = TransactionDetail::where('id', '=', (int)$user_id)
                    ->where('trans_level', '=', 'user')
                    ->where('status', '=', 'COMPLETE');
        if (is_array($date_range_timestamp)) {
            $query->where('created_at', '>=', (int)array_get($date_range_timestamp, 'start_date'))
                ->where('created_at', '<=', (int)array_get($date_range_timestamp, 'end_date'));
        }
        return $query->get(['program_id', 'created_at', 'trans_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransDetailsByProgramForUsers($program_id, $total_users)
    {
        return TransactionDetail::where('program_id', '=', (int)$program_id)
                                    ->whereIn('id', $total_users)
                                    ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramsByUserGroupWithInTime($ug_ids, $date_range_timestamp)
    {
        $query = TransactionDetail::whereIn('id', $ug_ids)
                    ->where('trans_level', '=', 'usergroup')
                    ->where('status', '=', 'COMPLETE');
        if (is_array($date_range_timestamp)) {
            $query->where('created_at', '>=', (int)array_get($date_range_timestamp, 'start_date'))
                ->where('created_at', '<=', (int)array_get($date_range_timestamp, 'end_date'));
        }
        return $query->get(['program_id', 'created_at', 'trans_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function getDetailsByProgramDetails($programid, $id)
    {
        return TransactionDetail::where('program_id', '=', (int)$programid)->where('id', '=', $id)
                ->where('trans_level', '=', 'user')
                ->where('status', '=', 'COMPLETE')
                ->get();
    }
}