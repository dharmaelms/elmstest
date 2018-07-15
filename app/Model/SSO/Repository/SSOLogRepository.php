<?php
namespace App\Model\SSO\Repository;

use App\Model\SSO\Entity\SSOLog;

/**
 * class SSOLogRepository
 * @package App\Model\SSO\Repository
 */
class SSOLogRepository implements ISSOLogRepository
{
    /**
     * {@inheritdoc}
     */
    public function addLog($data)
    {
        return SSOLog::insert($data);
    }
}