<?php

namespace App\Model\UserCertificates\Repository;

use App\Model\UserCertificates\UserCertificates;
use Auth;

/**
 * Class UserCertificateRepository
 *
 * @package App\Model\UserCertificates\Repository
 */
class UserCertificatesRepository implements IUserCertificatesRepository
{
    /**
     * {@inheritdoc}
     */
    public function getUserCertificates($start, $limit, $condition, $title)
    {
        $certificates_list = UserCertificates::where('user_id', (int)Auth::user()->uid)->FilterBy($condition)->searchTitle($title)->where('status', 'ACTIVE')->skip((int)$start)->take((int)$limit)->get();
        return $certificates_list;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataByColumn($data, $column)
    {
        return UserCertificates::where('certificate_id', '=', (int)1)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getSpecifiedChannelCertifiedUsers($channel_id, $user_ids)
    {
        return UserCertificates::where('status', '=', 'ACTIVE')
                    ->where('program_id', '=', (int)$channel_id)
                    ->whereIn('user_id', $user_ids)
                    ->pluck('user_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getCertificatesByProgramId($program_id, $column, $orderby = ['created_at' => 'desc'], $start = 0, $limit = 0)
    {   
        list($key, $value) = each($orderby);
        $query = UserCertificates::where('status', '=', 'ACTIVE')
            ->whereNotIn('user_id', [Auth::user()->uid])
            ->where('program_id', '=', (int)$program_id);
        if ($limit > 0) {
            $query->skip((int)$start)
            ->take((int)$limit);
        }
        return $query->orderBy($key, $value)->get($column);
    }

    /**
     * {@inheritdoc}
     */
    public function getCountByProgramId($program_id) 
    {
        return UserCertificates::where('status', '=', 'ACTIVE')
                    ->whereNotIn('user_id', [Auth::user()->uid])
                    ->where('program_id', '=', (int)$program_id)->get(['user_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function getCertificatesByProgramAndUsers($program_id, $user_ids, $column)
    {
        return UserCertificates::where('status', '=', 'ACTIVE')
                    ->whereIn('user_id', $user_ids)
                    ->where('program_id', '=', (int)$program_id)
                    ->get($column);
    }

    /**
     * {@inheritdoc}
     */
    public function getCertificateByUserAndProgramId($user_id, $progarm_id)
    {
        return UserCertificates::where('user_id', (int)$user_id)->where('program_id', (int)$progarm_id)->first(); 
    }

    /**
     * {@inheritdoc}
     */
    public function getCertifiedUsersLists($program_id, $user_ids, $column, $orderby = ['created_at' => 'desc'], $start = 0, $limit = 0)
    {   
        list($key, $value) = each($orderby);
        $query = UserCertificates::where('status', '=', 'ACTIVE')
            ->whereNotIn('user_id', [Auth::user()->uid])
            ->whereIn('user_id', $user_ids)
            ->where('program_id', '=', (int)$program_id);
        if ($limit > 0) {
            $query->skip((int)$start)
            ->take((int)$limit);
        }
        return $query->orderBy($key, $value)->get($column);
    }
}
