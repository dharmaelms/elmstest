<?php
namespace App\Model\ClientSecurity\Repository;

use App\Model\ClientSecurity\Entity\ClientSecurity;

/**
 * class ClientSecurityRepository
 * @package App\Model\ClientSecurity\Repository
 */
class ClientSecurityRepository implements IClientSecurityRepository
{
    /**
     * @inheritdoc
     */
    public function getSequence()
    {
        return ClientSecurity::getSequence();
    }

    /**
     * @inheritdoc
     */
    public function getClientSecurityDetails()
    {
        return ClientSecurity::orderBy('security_id', 'desc')->first();
    }

    /**
     * @inheritdoc
     */
    public function updateTokenDetails($security_id, $access_token, $expired_at, $updated_at)
    {
        return ClientSecurity::where('security_id', '=', (int)$security_id)
        	->update(['token' => $access_token, 'expired_at' => $expired_at, 'updated_at' => $updated_at]);
    }

    /**
     * @inheritdoc
     */
    public function getClientSecurityDetailsBySecurityId($security_id)
    {
        return ClientSecurity::where('security_id', '=', (int)$security_id)->first();
    }

    /**
     * @inheritdoc
     */
    public function updateTokenExpirtDate($security_id, $expired_at, $updated_at)
    {
        return ClientSecurity::where('security_id', '=', (int)$security_id)
        	->update(['expired_at' => $expired_at, 'updated_at' => $updated_at]);
    }

    /**
     * @inheritdoc
     */
    public function insertData($client_security_details = [])
    {
        return ClientSecurity::insert($client_security_details);
    }
}