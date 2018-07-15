<?php

/**
 * Webex lib class
 *
 * @package Webex
 * @subpackage Client helper class
 * @author Linkstreet Dev Team
 */

abstract class Lib
{

    /* Service layer URL */
    protected $serviceLayerURL = null;

    /* Service layer application key to find the webex microsite configuration */
    protected $appKey = null;

    /* Service layer HTTP username */
    protected $username = null;

    /* Service layer HTTP password */
    protected $password = null;

    /**
     * Recorded session
     * read (get) and delete
     *
     * @access public
     * @param string $action
     * @param array $param
     * @return array|boolean
     */
    public function recording($action, $param)
    {
        switch ($action) {
            case 'read':
                $list = [
                    'sessionKey'
                ];
                return $this->call('webex/recording', $list, $param);
                break;
            
            case 'delete':
                $list = [
                    'recordingID'
                ];
                return $this->call('webex/recording/delete', $list, $param);
                break;

            case 'forceDelete':
                $list = [
                    'recordingID',
                    'deleteAll',
                    'hostUsername',
                    'hostPassword'
                ];
                $param['deleteAll'] = false;
                return $this->call('webex/recording/forceDelete', $list, $param);
                break;    
        }
        return false;
    }

    /**
     * Meeting session
     * create, update, delete and summary
     *
     * @param string $action
     * @param array $param
     * @return array|boolean
     */
    public function meeting($action, $param)
    {
        switch ($action) {
            case 'create':
                $list = [
                    'hostUsername',
                    'hostPassword',
                    'confName',
                    'startDate',
                    'duration'
                ];
                return $this->call('webex/meeting/create', $list, $param);
                break;
                    
            case 'update':
                $list = [
                    'hostUsername',
                    'hostPassword',
                    'confName',
                    'startDate',
                    'duration',
                    'sessionKey'
                ];
                return $this->call('webex/meeting/update', $list, $param);
                break;
                    
            case 'delete':
                $list = [
                    'hostUsername',
                    'hostPassword',
                    'sessionKey'
                ];
                return $this->call('webex/meeting/delete', $list, $param);
                break;
                    
            case 'summary':
                $list = [
                    'hostUsername',
                    'hostPassword'
                ];
                return $this->call('webex/meeting/summary', $list, $param);
                break;

            case 'usage':
                $list = [
                    'hostUsername',
                    'hostPassword',
                    'sessionKey'
                ];
                return $this->call('webex/meeting/usage', $list, $param);
                break;

            case 'attendee':
                $list = [
                    'hostUsername',
                    'hostPassword',
                    'sessionKey'
                ];
                return $this->call('webex/meeting/attendee', $list, $param);
                break;
        }
        return false;
    }
    
    /**
     * Training session
     * create, update, delete and summary
     *
     * @param string $action
     * @param array $param
     * @return array|boolean
     */
    public function training($action, $param)
    {
        switch ($action) {
            case 'create':
                $list = [
                    'hostUsername',
                    'hostPassword',
                    'confName',
                    'startDate',
                    'duration'
                ];
                return $this->call('webex/training/create', $list, $param);
                break;
            
            case 'update':
                $list = [
                    'hostUsername',
                    'hostPassword',
                    'confName',
                    'startDate',
                    'duration',
                    'sessionKey'
                ];
                return $this->call('webex/training/update', $list, $param);
                break;
            
            case 'delete':
                $list = [
                    'hostUsername',
                    'hostPassword',
                    'sessionKey'
                ];
                return $this->call('webex/training/delete', $list, $param);
                break;
            
            case 'summary':
                $list = ['hostUsername',
                    'hostPassword'
                ];
                return $this->call('webex/training/summary', $list, $param);
                break;

            case 'usage':
                $list = [
                    'hostUsername',
                    'hostPassword',
                    'sessionKey'
                ];
                return $this->call('webex/training/usage', $list, $param);
                break;

            case 'attendee':
                $list = [
                    'hostUsername',
                    'hostPassword',
                    'sessionKey'
                ];
                return $this->call('webex/training/attendee', $list, $param);
                break;
        }
        return false;
    }
    
    /**
     * User details
     * create, update, delete, summary, activate and deactivate
     *
     * @access public
     * @param string $action
     * @param array $param
     * @return array|boolean
     */
    public function user($action, $param)
    {
        switch ($action) {
            case 'create':
                $list = [
                    'firstname',
                    'lastname',
                    'email',
                    'webExId',
                    'password',
                    'active',
                    'hostPrivilege',
                    'supportMeetingCenter',
                    'supportTrainingCenter',
                    'supportEventCenter'
                ];
                return $this->call('webex/user/create', $list, $param);
                break;
            
            case 'update':
                $list = [
                    'firstname',
                    'lastname',
                    'email',
                    'webExId',
                    'password',
                    'active',
                    'hostPrivilege',
                    'supportMeetingCenter',
                    'supportTrainingCenter',
                    'supportEventCenter'
                ];
                return $this->call('webex/user/update', $list, $param);
                break;
            
            case 'delete':
                $list = [
                    'webExId'
                ];
                return $this->call('webex/user/delete', $list, $param);
                break;
            
            case 'summary':
                $list = [];
                return $this->call('webex/user/summary', $list, $param);
                break;
            
            case 'activate':
                $list = [
                    'webExId'
                ];
                return $this->call('webex/user/activate', $list, $param);
                break;
            
            case 'deactivate':
                $list = [
                    'webExId'
                ];
                return $this->call('webex/user/deactivate', $list, $param);
                break;
            
            case 'hostjoinurl':
                $list = [
                    'hostUsername',
                    'hostPassword',
                    'sessionKey',
                    'backURL'
                ];
                return $this->call('webex/user/hostjoinurl', $list, $param);
                break;
                
            case 'hostlogouturl':
                $list = [
                    'hostUsername',
                    'backURL'
                ];
                return $this->call('webex/user/hostlogouturl', $list, $param);
                break;
                
            case 'attendeejoinurl':
                $list = [
                    'firstname',
                    'lastname',
                    'email',
                    'sessionKey',
                    'sessionType',
                    'backURL'
                ];
                return $this->call('webex/user/attendeejoinurl', $list, $param);
                break;
        }
        return false;
    }
    
    /**
     * Site details
     * timezone
     *
     * @access public
     * @param string $action
     * @param array $param
     * @return array|boolean
     */
    public function site($action, $param)
    {
        switch ($action) {
            case 'timezone':
                $list = [
                    'timeZoneID',
                    'date'
                ];
                return $this->call('webex/site/timezone', $list, $param);
                break;
        }
        return false;
    }
    
    /**
     * Webex Service send function
     *
     * @param string $url
     * @param array $data
     * @return array
     */
    protected function send_curl($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/json']);
        curl_setopt($ch, CURLOPT_USERPWD, "$this->username:$this->password");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        
        $curl_info = curl_getinfo($ch);
        curl_close($ch);
        
        // Checking for Curl error
        if ($response !== false && !empty($response)) {
            $result = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $result;
            } else {
                throw new Exception('Error while parsing the json response');
            }
        } else {
            throw new Exception('Error in curl ('.($curl_info['http_code']).')');
        }
    }
    
    /**
     * Call function
     *
     * @param string $actionURL
     * @param array $list List of required params
     * @param array $param Passed parameters
     * @return array
     */
    protected function call($actionURL, $list, $param)
    {
        try {
            // Checking the webex application key
            if (!isset($this->appKey) && empty($this->appKey)) {
                throw new Exception("Webex App key value is not set or empty");
            } else {
                // Adding the webex application key to parameter
                $param['appKey'] = $this->appKey;
            }
            
            // Checking the service layer URL
            if (!isset($this->serviceLayerURL) && empty($this->serviceLayerURL)) {
                throw new Exception("Service layer URL value is not set or empty");
            }

            // Checking for valid paraneters and Counting the request parameters
            $rec_list = count(array_intersect_key(array_flip($list), $param));

            if ($rec_list != count($list)) {
                throw new Exception("Parameter is invalid or missing");
            }
            
            // Forming the url
            $url = $this->serviceLayerURL.'/'.$actionURL.'/';
            
            // Sending the JSON data to CURL function
            return $this->send_curl($url, json_encode($param));
        } catch (Exception $e) {
            return ['status'=>false, 'error'=>[$e->getMessage()]];
        }
    }

    /**
     * Create a default array of integers with the associated WebEx time zone list
     *
     * @access public
     * @return array
     */
    public static function webex_time_zone_list_array()
    {
        return  [
            0 => 'GMT-12:00, Dateline (Eniwetok)',
            1 => 'GMT-11:00, Samoa (Samoa)',
            2 => 'GMT-10:00, Hawaii (Honolulu)',
            3 => 'GMT-09:00, Alaska (Anchorage)',
            4 => 'GMT-08:00, Pacific (San Jose)',
            5 => 'GMT-07:00, Mountain (Arizona)',
            6 => 'GMT-07:00, Mountain (Denver)',
            7 => 'GMT-06:00, Central (Chicago)',
            8 => 'GMT-06:00, Mexico (Mexico City, Tegucigalpa)',
            9 => 'GMT-06:00, Central (Regina)',
            10 => 'GMT-05:00, S. America Pacific (Bogota)',
            11 => 'GMT-05:00, Eastern (New York)',
            12 => 'GMT-05:00, Eastern (Indiana)',
            13 => 'GMT-04:00, Atlantic (Halifax)',
            14 => 'GMT-04:00, S. America Western (Caracas)',
            15 => 'GMT-03:30, Newfoundland (Newfoundland)',
            16 => 'GMT-03:00, S. America Eastern (Brasilia)',
            17 => 'GMT-03:00, S. America Eastern (Buenos Aires)',
            18 => 'GMT-02:00, Mid-Atlantic (Mid-Atlantic)',
            19 => 'GMT-01:00, Azores (Azores)',
            20 => 'GMT+00:00, Greenwich (Casablanca)',
            21 => 'GMT+00:00, GMT (London)',
            22 => 'GMT+01:00, Europe (Amsterdam)',
            23 => 'GMT+01:00, Europe (Paris)',
            24 => 'GMT+01:00, Europe (Prague)',
            25 => 'GMT+01:00, Europe (Berlin)',
            26 => 'GMT+02:00, Greece (Athens)',
            27 => 'GMT+02:00, Eastern Europe (Bucharest)',
            28 => 'GMT+02:00, Egypt (Cairo)',
            29 => 'GMT+02:00, South Africa (Pretoria)',
            30 => 'GMT+02:00, Northern Europe (Helsinki)',
            31 => 'GMT+02:00, Israel (Tel Aviv)',
            32 => 'GMT+03:00, Saudi Arabia (Baghdad)',
            33 => 'GMT+03:00, Russian (Moscow)',
            34 => 'GMT+03:00, Nairobi (Nairobi)',
            35 => 'GMT+03:30, Iran (Tehran)',
            36 => 'GMT+04:00, Arabian (Abu Dhabi, Muscat)',
            37 => 'GMT+04:00, Baku (Baku)',
            38 => 'GMT+04:30, Afghanistan (Kabul)',
            39 => 'GMT+05:00, West Asia (Ekaterinburg)',
            40 => 'GMT+05:00, West Asia (Islamabad)',
            41 => 'GMT+05:30, India (Bombay) ',
            42 => 'GMT+06:00, Columbo (Columbo)',
            43 => 'GMT+06:00, Central Asia (Almaty)',
            44 => 'GMT+07:00, Bangkok (Bangkok)',
            45 => 'GMT+08:00, China (Beijing)',
            46 => 'GMT+08:00, Australia Western (Perth)',
            47 => 'GMT+08:00, Singapore (Singapore)',
            48 => 'GMT+08:00, Taipei (Hong Kong)',
            49 => 'GMT+09:00, Tokyo (Tokyo)',
            50 => 'GMT+09:00, Korea (Seoul)',
            51 => 'GMT+09:00, Yakutsk (Yakutsk)',
            52 => 'GMT+09:30, Australia Central (Adelaide)',
            53 => 'GMT+09:30, Australia Central (Darwin) ',
            54 => 'GMT+10:00, Australia Eastern (Brisbane)',
            55 => 'GMT+10:00, Australia Eastern (Sydney)',
            56 => 'GMT+10:00, West Pacific (Guam)',
            57 => 'GMT+10:00, Tasmania (Hobart)',
            58 => 'GMT+10:00, Vladivostok (Vladivostok) ',
            59 => 'GMT+11:00, Central Pacific (Solomon Is)',
            60 => 'GMT+12:00, New Zealand (Wellington)',
            61 => 'GMT+12:00, Fiji (Fiji)'
        ];
    }

    /**
     * [webex_mdl_to_webex_tz_array description]
     * @return array
     */
    public static function webex_mdl_to_webex_tz_array()
    {
        return [
            '-13.0' => [0],
            '-12.0' => [0],
            '-11.0' => [1],
            '-10.0' => [2],
            '-9.0'  => [3],
            '-8.0'  => [4],
            '-7.0'  => [5,6],
            '-6.0'  => [7,8,9],
            '-5.0'  => [10,11,12],
            '-4.0'  => [13,14],
            '-3.0'  => [15,16,17],
            '-2.0'  => [18],
            '-1.0'  => [19],
            '0.0'   => [20,21],
            '1.0'   => [22,23,24,25],
            '2.0'   => [26,27,28,29,30,31],
            '3.0'   => [32,33,34],
            '3.5'   => [35],
            '4.0'   => [36,37],
            '4.5'   => [38],
            '5.0'   => [39,40],
            '5.5'   => [41],
            '6.0'   => [42,43],
            '7.0'   => [44],
            '8.0'   => [45,46,47,48],
            '9.0'   => [49,50,51],
            '9.5'   => [52,53],
            '10.0'  => [54,55,56,57,58],
            '11.0'  => [59],
            '12.0'  => [60,61],
            '13.0'  => [60,61]
        ];
    }
    
    /**
     * Create a default array of integers with the associated WebEx session type options
     *
     * @access public
     * @return array An array of WebEx session type options
     */
    public static function webex_session_type_array()
    {
        return [
            'MC' => 'Meeting Center',
            'TC' => 'Training Center'
        ];
    }
    
    /**
     * Returns WebEx session repeat type
     *
     * @access public
     * @return array
     */
    public static function webex_repeat_type_array()
    {
        return [
            'SINGLE' => 'Single Session',
            'RECURRING_SINGLE' => 'Recurring Session'
        ];
    }

    /**
     * Returns recurring type list
     *
     * @access public
     * @return array
     */
    public static function webex_recurring_array()
    {
        return [
            'DAILY'=>'Daily',
            'WEEKLY'=>'Weekly',
            'MONTHLY'=>'Monthly'
        ];
    }

    /**
     * Returns WebEx day in week list
     *
     * @access public
     * @return array
     */
    public static function webex_dayinweek_array()
    {
        return [
            'SUNDAY' => 'Sunday',
            'MONDAY' => 'Monday',
            'TUESDAY' => 'Tuesday',
            'WEDNESDAY' => 'Wednesday',
            'THURSDAY' => 'Thursday',
            'FRIDAY' => 'Friday',
            'SATURDAY' => 'Saturday'
        ];
    }

    /**
     * Returns WebEx week in month list
     *
     * @access public
     * @return array
     */
    public static function webex_weekinmonth_array()
    {
        return [
            '1' => '1st',
            '2' => '2nd',
            '3' => '3rd',
            '4' => '4th',
            '5' => 'Last'
        ];
    }

    /**
     * Returns WebEx ending type
     *
     * @access public
     * @return array
     */
    public static function webex_endingtype_array()
    {
        return [
            '0' => 'Ending',
            '1' => 'Ending after X sessions'
        ];
    }
}


/* End of file lib.php */

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
