<?php
namespace App\Services\ClientSecurity;

/**
 * Interface IClientSecurityService
 * @package App\Services\ClientSecurity
 */
interface IClientSecurityService
{
    /**
     * Method to generate security_id
     * @return int
     */
    public function getSequence();

    /**
     * Method to get client security details
     * @return collection
     */
    public function getClientSecurityDetails();

    /**
     * Method to update client security details
     * @param int $security_id
     * @param string $access_token
     * @param int $expired_at
     * @param int $updated_at
     * @return boolean
     */
    public function updateTokenDetails($security_id, $access_token, $expired_at, $updated_at);
    
    /**
     * Method to get client security details by security id
     * @param int $security_id
     * @return collection
     */
    public function getClientSecurityDetailsBySecurityId($security_id);

    /**
     * Method to update client security details
     * @param int $security_id
     * @param int $expired_at
     * @param int $updated_at
     * @return boolean
     */
    public function updateTokenExpirtDate($security_id, $expired_at, $updated_at);

    /**
     * Method to insert security details
     * @param array $client_security_details
     * @return boolean
     */
    public function insertData($client_security_details);
}
