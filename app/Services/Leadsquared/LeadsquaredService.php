<?php

namespace App\Services\Leadsquared;

use App\Model\Leadsquared\Repository\ILeadsquaredRepository;
use Config;
use URL;

/**
 * Class LeadsquaredService
 * @package App\Services\Leadsquared
 */
class LeadsquaredService implements ILeadsquaredService
{
    /**
     * @var ILeadsquaredRepository
     */
    private $leadsquared_repository;

    /**
     * @var
     */
    private $enabled;

    /**
     * @var
     */
    private $apiSite;

    /**
     * @var
     */
    private $leadsquared;

    /**
     * LeadsquaredService constructor.
     * @param ILeadsquaredRepository $leadsquared_repository
     * @param bool $enabled
     */
    public function __construct(ILeadsquaredRepository $leadsquared_repository, $enabled = false)
    {
        $this->leadsquared_repository = $leadsquared_repository;

        $this->enabled = $leadsquared_repository->leadsquaredEnabledFlag;

        $this->apiSite = $leadsquared_repository->leadsquaredApiSite;

        if ($this->enabled) {
            $this->leadsquared = $leadsquared_repository->leadsquared;
        }
    }

    /**
     * @return mixed
     */
    public function isLeadsquaredEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param $leads
     */
    public function processLeads($leads)
    {

        // print_r($this->enabled); exit;
        if ($this->enabled) {
            // echo"hai";
            switch ($leads["type"]) {
                case "create-lead":
                    $this->createCrmLead($leads["data"]);
                    break;

                case "convert-lead":
                    $this->convertCrmLead($leads["data"]);
                    break;
            }
        }
    }

    /**
     * @param $data
     * @return mixed
     */
    public function createCrmLead($data)
    {
        if ($this->isDomainAvailable($this->apiSite)) {
            if (empty(json_decode($this->getCrmLeadByEmail($data["email"])))) {
                $response = $this->leadsquared_repository->createLead([
                    "FirstName" => $this->testInput($data["name"]),
                    "EmailAddress" => $this->testInput($data["email"]),
                    "Phone" => $this->testInput($data["mobile"])
                ]);

                // $leadmsg = json_decode($createlead_response);
                // $leadId = $leadmsg->Message->Id;


                return $response;
            }
        }
    }


    /**
     * @param $data
     * @return mixed
     */
    public function convertCrmLead($data)
    {
        if ($this->isDomainAvailable($this->apiSite)) {
            $p_id = Config::get("app.leadsquared.cookiename");
            $data["leadId"] = $_COOKIE[$p_id];
            $course_name = isset($data["course"]) ? $data["course"] : "";
            if (empty(json_decode($this->getCrmLeadByLeadID($data["leadId"])))) {
                $response = $this->leadsquared_repository->convertLeadToVisitor(
                    [
                        "FirstName" => $this->testInput($data["name"]),
                        "EmailAddress" => $this->testInput($data["email"]),
                        "Phone" => $this->testInput($data["mobile"]),
                        "mx_Course" => $this->testInput($course_name),
                        "mx_URL" => URL::current(),
                    ],
                    $this->testInput($data["leadId"])
                );

                return $response;
            }
        }
    }


    /**
     * @param $EmailAddress
     * @return mixed
     */
    public function getCrmLeadByEmail($EmailAddress)
    {
        return $this->leadsquared_repository->getLeadByEmail($EmailAddress);
    }

    /**
     * @param $leadId
     * @return mixed
     */
    public function getCrmLeadByLeadID($leadId)
    {

        $existlead = $this->leadsquared_repository->getLeadByLeadID($leadId);

        return $existlead;
    }

    /**
     * @param $domain
     * @return bool
     */
    public function isDomainAvailable($domain)
    {
        //check, if a valid url is provided
        if (!filter_var($domain, FILTER_VALIDATE_URL)) {
            return false;
        }

        //initialize curl
        $curlInit = curl_init($domain);
        curl_setopt($curlInit, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curlInit, CURLOPT_HEADER, true);
        curl_setopt($curlInit, CURLOPT_NOBODY, true);
        curl_setopt($curlInit, CURLOPT_RETURNTRANSFER, true);

        //get answer
        $response = curl_exec($curlInit);

        curl_close($curlInit);

        if ($response) {
            return true;
        }

        return false;
    }


    /**
     * @param $data
     * @return string
     */
    public function testInput($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}
