<?php
namespace App\Libraries\webex;

use App\Enums\WebEx\NBRServices;
use Log;
use Riverline\MultiPartParser\Part;

class WebExNBR
{
    /**
     * @var string
     */
    protected $nbr_url;

    /**
     * @var integer
     * WebEx site id
     */
    protected $site_id;

    /**
     * @var string
     * Admin user username to generate ticket
     */
    protected $admin_username;

    /**
     * @var string
     * Admin user password to generate ticket
     */
    protected $admin_password;

    /**
     * @var string
     * NBR API service type e.g NBRFileOpenService, NBRStorageService, nbrXmlService
     */
    protected $service_type;

    /**
     * @var container for SOAP requests XML body
     */
    protected $soap_body;

    /**
     * @var SOAP Request XML
     */
    protected $xml;

    /**
     * @var container for ticket id
     */
    protected $ticket_id;

    /**
     * WebExNBR constructor.
     *
     * @param $siteId
     * @param $adminUsername
     * @param $adminPassword
     */
    public function __construct($siteId, $adminUsername, $adminPassword)
    {
        $this->site_id = $siteId;
        $this->admin_username = $adminUsername;
        $this->admin_password = $adminPassword;
    }

    /**
     * @param $api_url NBR Service URL
     *
     */
    public function setRequestUrl($api_url)
    {
        $this->nbr_url = $api_url;
    }

    /**
     * Method to generate the valid ticket for NBR API requests
     *
     * @return mixed
     */
    public function generateTicket()
    {
        $this->service_type = NBRServices::NBR_STORAGE_SERVICE;
        $this->constructBody('getStorageAccessTicket',
            [
                'siteId' => $this->site_id,
                'username' => $this->admin_username,
                'password' => $this->admin_password,
            ]
        );
        $part = new Part($this->sendRequest());
        $doc = new \DOMDocument();
        $doc->loadXML($part->getBody());
        $this->ticket_id = $doc->getElementsByTagName('getStorageAccessTicketReturn')->item(0)->nodeValue;
    }

    /**
     * Method to download the recording from WebEx server
     *
     * @param $record_id
     * @return array
     * @throws \Exception
     * @throws \LogicException
     */

    public function getRecordingData($record_id)
    {
        $this->service_type = NBRServices::NBR_STORAGE_SERVICE;
        $this->constructBody('downloadNBRStorageFile',
                [
                    'siteId' => $this->site_id,
                    'recordId' => $record_id,
                    'ticket' => $this->ticket_id
                ]
            );
        $document = new Part($this->sendRequest());
        if ($document->isMultiPart()) {
            $parts = $document->getParts();
            $name = str_replace(' ', '-', strtok($parts[1]->getBody(), "\n"));
            return ['name' => $name, 'body' => $parts[2]->getBody()];
        } else {
            throw new \Exception();
        }
    }

    public function getRecordingsList()
    {
        $this->service_type = NBRServices::NBR_XML_SERVICE;
        $this->constructBody('getNBRRecordIdList',
            [
                'siteId' => $this->site_id,
                'username' => $this->admin_username,
                'password' => $this->admin_password,
            ]
        );
        $part = new Part($this->sendRequest());
        return $part->getBody();
    }

    /**
     * @param $method
     * @param $params
     *
     * @return void
     */
    protected function constructBody($method, $params)
    {
        $body = '<soapenv:Body>';
        $body .= '<ns1:'.$method.' xmlns:ns1="' . $this->service_type. '">';
        foreach ($params as $key => $value) {
            $body .= '<' . $key . '>' . $value . '</' . $key . '>';
        }
        $body .= '</ns1:'.$method.'>';
        $body .= '</soapenv:Body>';
        $this->soap_body = $body;
    }

    /**
     * Method to generate the SOAP request body
     *
     * @return void
     */
    protected function constructXML()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .=' <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">';
        $xml .= $this->soap_body;
        $xml .= '</soapenv:Envelope>';
        $this->xml = $xml;
    }

    /**
     * Method to send the curl request to WebEx server
     *
     * @return mixed
     */
    public function sendRequest()
    {
        $this->constructXML();
        $ch = curl_init($this->nbr_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'SOAPAction: ""',
            'Content-Type: text/xml',
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "$this->xml");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
