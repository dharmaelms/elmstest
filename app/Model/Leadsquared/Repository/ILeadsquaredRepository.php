<?php
namespace App\Model\Leadsquared\Repository;

/**
 * Interface ILeadsquaredRepository
 * @package App\Model\Leadsquared\Repository
 */
interface ILeadsquaredRepository
{
    /**
     * @param $data
     * @return mixed
     */
    public function createLead($data);

    /**
     * @param $email
     * @return mixed
     */
    public function getLeadByEmail($email);

    /**
     * @param $data
     * @param $lead_id
     * @return mixed
     */
    public function convertLeadToVisitor($data, $lead_id);

    /**
     * @param $lead_id
     * @return mixed
     */
    public function getLeadByLeadID($lead_id);
}
