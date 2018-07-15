<?php

namespace App\Services\TransactionDetail;

use App\Model\TransactionDetail\Repository\ITransactionDetailRepository;
use App\Model\UserGroup\Repository\IUserGroupRepository;

/**
 * class TransactionDetailService
 * @package App\Services\TransactionDetail
 */
class TransactionDetailService implements ITransactionDetailService
{
    private $trans_detail_repo;

    private $ug_repository;

    public function __construct(
        ITransactionDetailRepository $trans_detail_repo,
        IUserGroupRepository $ug_repository
    ) {
        $this->trans_detail_repo = $trans_detail_repo;
        $this->ug_repository = $ug_repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getUseridByProgramId($program_id, $date_range_timestamp)
    {
        $user_trans_details = $this->trans_detail_repo->getUseridByProgramId($program_id, $date_range_timestamp, 'user');
        $ug_trans_details = $this->trans_detail_repo->getUseridByProgramId($program_id, $date_range_timestamp, 'usergroup');
        if (!empty($ug_trans_details)) {
            $ug_ids = $ug_trans_details->keyBy('id')->keys()->toArray();
            $ug_details = $this->ug_repository->getUserGroupsUsingID($ug_ids);
            foreach ($ug_details as $ug_detail) {
                $ug_id = array_get($ug_detail, 'ugid', 0);
                if (in_array($ug_id, $ug_ids)) {
                    $created_at = $ug_trans_details->where('id', $ug_id)->first()->created_at;
                    $user_rel_u_ids = array_get($ug_detail, 'relations.active_user_usergroup_rel', []);
                    foreach ($user_rel_u_ids as $uid) {
                        $temp_collection = collect();
                        $temp_collection->put('id', $uid);
                        $temp_collection->put('created_at', $created_at);
                        $user_trans_details->push($temp_collection);
                    }
                } else {
                    continue;
                }
            }
        }
        return $user_trans_details;
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramsByUserWithInTime($user_id, $date_range_timestamp, $ug_ids)
    {
        $ug_ids = array_map('intval', $ug_ids);
        $user_trans = $this->trans_detail_repo->getProgramsByUserWithInTime(
            $user_id,
            $date_range_timestamp
        )->keyBy('program_id');
        $ug_trans = [];
        if (!empty($ug_ids)) {
            $ug_trans = $this->trans_detail_repo->getProgramsByUserGroupWithInTime(
                $ug_ids,
                $date_range_timestamp
            )->keyBy('program_id');
        }
        return $user_trans->merge($ug_trans)
            ->sortBy('created_at')
            ->groupBy('program_id')
            ->map(function ($trans) {
                return $trans->first();
            });
    }

    /**
     * {@inheritdoc}
     */
    public function getTransDetailsByProgramForUsers($program_id, $total_users)
    {
        if ($program_id > 0 && !empty($total_users)) {
            return $this->trans_detail_repo->getTransDetailsByProgramForUsers($program_id, $total_users);
        } else {
            return collect([]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDetailsByProgramDetails($programid, $id)
    {
        return $this->trans_detail_repo->getDetailsByProgramDetails($programid, $id);
    }
}