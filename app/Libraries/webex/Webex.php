<?php

/**
 * Webex client side helper class
 *
 * It's a client side helper library to make the request to Webex
 * server using the Linkstreet service layer.
 *
 * @package Webex
 * @subpackage Client helper class
 * @author Linkstreet Dev Team
 */

require_once('Lib.php');

use App\Libraries\Timezone;
use App\Model\WebexHost;

class Webex extends Lib
{

    /**
     * Default constructor to setup the webex instance
     *
     * @access public
     * @param string $serviceLayerURL
     * @param string $appKey Application access key
     * @param string $username HTTP Basic username
     * @param string $password HTTP Basic password
     * @return Webex_Lib Object of the class
     */
    public function __construct($serviceLayerURL, $appKey, $username, $password)
    {
        $this->serviceLayerURL = $serviceLayerURL;
        $this->appKey = $appKey;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Call webex service to create the session
     *
     * @param array $param
     * @return array|boolean
     */
    public function create_session($data, $param = [])
    {
        switch (strtoupper($data['session_type'])) {
            case 'MC':
                $this->meeting_param($data, $param);
                return $this->meeting('create', $param);
                break;
                
            case 'TC':
                $this->training_param($data, $param);
                return $this->training('create', $param);
                break;
        }
        return false;
    }

    /**
     * Call webex service to update the session
     *
     * @param array $param
     * @return array|boolean
     */
    public function update_session($data, $param = [])
    {
        switch (strtoupper($data['session_type'])) {
            case 'MC':
                $this->meeting_param($data, $param);
                return $this->meeting('update', $param);
                break;
                
            case 'TC':
                $this->training_param($data, $param);
                return $this->training('update', $param);
                break;
        }
        return false;
    }

    /**
     * Call webex service to delete the session
     *
     * @param array $param
     * @return array|boolean
     */
    public function delete_session($session_type, $param = [])
    {
        switch (strtoupper($session_type)) {
            case 'MC':
                $this->meeting('delete', $param);
                return ['status' => true];
                break;
                
            case 'TC':
                $this->training('delete', $param);
                return ['status' => true];
                break;
        }
        return false;
    }

    /**
     * Call webex service to get meeting participants
     */
    public function attendee_summary($session_type, $param = [])
    {
        switch (strtoupper($session_type)) {
            case 'MC':
                return $this->meeting('usage', $param);
                break;

            case 'TC':
                return $this->training('usage', $param);
                break;
        }
        return false;
    }

    /**
     * Call webex service to get meeting participants
     */
    public function attendee_details($session_type, $param = [])
    {
        switch (strtoupper($session_type)) {
            case 'MC':
                return $response = $this->meeting('attendee', $param);
                break;

            case 'TC':
                return $this->training('attendee', $param);
                break;
        }
        return false;
    }

    /**
     * Find the php timezone equivalent to webex timezone
     *
     * @param string $session_time
     * @param int $webex_tz
     * @return string $timezone
     */
    public function timezone($session_time, $webex_tz)
    {
        $tz_param['timeZoneID'] = (int) $webex_tz;
        $tz_param['date'] = Timezone::convertToUTC($session_time, 'UTC', 'm/d/Y G:i:s');
        $tz = $this->site('timezone', $tz_param);
        $data['gmt_offest'] = (int) $tz['data']['gmtOffset'];
        $data['in_dst'] = ($tz['data']['fallInDST'] == 'true') ? true : false;
        $data['timezone'] = timezone_name_from_abbr('', $data['gmt_offest'] * 60, $data['in_dst']);
        if ($data['timezone'] === false && $data['in_dst'] != false) {
            $data['timezone'] = timezone_name_from_abbr('', $data['gmt_offest'] * 60, false);
        }
        if ($data['timezone'] === false) {
            $abbrarray = timezone_abbreviations_list();
            foreach ($abbrarray as $abbr) {
                foreach ($abbr as $city) {
                    if ($city['dst'] == $data['in_dst'] && $city['offset'] == ($data['gmt_offest'] * 60)) {
                        $data['timezone'] = $city['timezone_id'];
                    }
                }
            }
        }
        return $data;
    }

    /**
     * meeting_param function
     *
     * @param array $data
     * @param array &$param
     * @return void
     */
    private function meeting_param($data, &$param)
    {
        switch (strtoupper($data['event_cycle'])) {
            case 'SINGLE':
                $param['repeatType'] = 'NO_REPEAT';
                break;
                
            case 'RECURRING':
                break;
        }
    }

    /**
     * meeting_param function
     *
     * @param array $data
     * @param array &$param
     * @return void
     */
    private function training_param($data, &$param)
    {
        switch (strtoupper($data['event_cycle'])) {
            case 'SINGLE':
                $param['repeatType'] = 'SINGLE';
                break;
                
            case 'RECURRING':
                break;
        }
    }

    /**
     * [Get recorded url]
     * @method recording
     * @param  [string]    $action [description]
     * @param  [array]    $param  [webinar details]
     * @return [array]            [response]
     */
}
