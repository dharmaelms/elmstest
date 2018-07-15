<?php
namespace App\Model\Leadsquared\Repository;

use Config;
use Leadsquared_Api;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class LeadsquaredRepository
 * @package App\Model\Leadsquared\Repository
 */
class LeadsquaredRepository implements ILeadsquaredRepository
{
    /**
     * @var mixed
     */
    public $leadsquaredEnabledFlag;

    /**
     * @var mixed
     */
    public $leadsquaredApiSite;

    /**
     * @var Leadsquared_Api
     */
    public $leadsquared;


    /**
     * LeadsquaredRepository constructor.
     */
    public function __construct()
    {
        $this->leadsquaredEnabledFlag = Config::get("app.leadsquared.enabled");
        $this->leadsquaredApiSite = Config::get("app.leadsquared.apisite");

        if ($this->leadsquaredEnabledFlag) {
            define('LSQ_NAME', Config::get("app.leadsquared.log"));
            define('LSQ_ACCESSKEY', Config::get("app.leadsquared.LSQ_ACCESSKEY"));
            define('LSQ_SECRETKEY', Config::get("app.leadsquared.LSQ_SECRETKEY"));
            $this->leadsquared = new Leadsquared_Api();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createLead($data)
    {

        $this->leadsquared->FirstName = $data["FirstName"];
        $this->leadsquared->EmailAddress = $data["EmailAddress"];
        $this->leadsquared->Phone = $data["Phone"];
        $return_data = $this->leadsquared->create_lead(json_encode($data));
        $view_log = new Logger('Leadsquared Logs');
        $d = $view_log->pushHandler(new StreamHandler(storage_path() . '/logs/Leadsquared.log', Logger::INFO));
        $view_log->addInfo(json_encode($data));
        $view_log->addInfo($return_data);
        return $this->leadsquared;
    }

    /**
     * {@inheritdoc}
     */
    public function convertLeadToVisitor($data, $lead_id)
    {

        $this->leadsquared->FirstName = $data["FirstName"];
        $this->leadsquared->EmailAddress = $data["EmailAddress"];
        $this->leadsquared->Phone = $data["Phone"];
        $return_data = $this->leadsquared->convert_visitor(json_encode($data), $lead_id);
        $view_log = new Logger('Leadsquared Converted Logs');
        $d = $view_log->pushHandler(new StreamHandler(storage_path() . '/logs/Leadsquared_ConvertVisitor.log', Logger::INFO));
        $view_log->addInfo(json_encode($data));
        $view_log->addInfo(json_encode($lead_id));
        $view_log->addInfo($return_data);
        return $this->leadsquared;
    }

    /**
     * {@inheritdoc}
     */
    public function getLeadByEmail($email)
    {
        $exist_lead = $this->leadsquared->get_lead_by_email($email);
        return $exist_lead;
    }

    /**
     * {@inheritdoc}
     */
    public function getLeadByLeadID($lead_id)
    {
        $exist_lead = $this->leadsquared->get_lead_by_id($lead_id);
        return $exist_lead;
    }
}
