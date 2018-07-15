<?php namespace App\Services\Leadsquared;

/**
 * Interface ILeadsquaredService
 * @package App\Services\Leadsquared
 */
interface ILeadsquaredService
{
    /**
     * @return mixed
     */
    public function isLeadsquaredEnabled();

    // public function createCrmLead($data);

    /**
     * @param $email
     * @return mixed
     */
    public function getCrmLeadByEmail($email);

    /**
     * @param $data
     * @return mixed
     */
    public function createCrmLead($data);

    /**
     * @param $data
     * @return mixed
     */
    public function convertCrmLead($data);

    /**
     * @param $leadID
     * @return mixed
     */
    public function getCrmLeadByLeadID($leadID);

    /**
     * @param $domain
     * @return mixed
     */
    public function isDomainAvailable($domain);
}
