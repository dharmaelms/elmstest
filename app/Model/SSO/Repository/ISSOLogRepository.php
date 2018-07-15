<?php
namespace App\Model\SSO\Repository;

/**
 * interface ISSOLogRepository
 * @package App\Model\SSO\Repository
 */
interface ISSOLogRepository
{
    /**
     * Method to add log for incoming and outgoing
     * request through SSO API
     *
     * @param mixed $data
     * @return App|Model|SSO|Entity
     */
    public function addLog($data);
}
