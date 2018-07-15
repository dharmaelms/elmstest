<?php

namespace App\Libraries\PushNotifications;

use App\Model\PushNotifications;

/**
 * this class is used to send push notifications to users who installed the app;
 * @author sathishkumar@linkstreet.in
 */
class PushNotificationsApi
{
    private $app_key; //appery app key
    private $headers = []; //api headers
    private $url; //appery api url
    private $status_code; //carries curl status code
    private $response;
    // private $actions = array("register" => "reg", "update" => "reg", "unregister" => "reg", "sendnotification" => "msg");
    // private $method = array("register" => "POST", "update" => "PUT", "unregister" => "DELETE", "sendnotification" => "POST"); //http method for requests like POST, PUT, DELETE etc.
    /*
	 * constructor to initialize headers
	 */
    public function __construct()
    {
        $this->app_key = config('app.appery_app_key');
    }
    /**
     * this function used to register device ids
     */
    public function pushToAll($data, $devices = false)
    {
        $this->url = "https://api.appery.io/rest/push/msg";
        $this->headers  = [
            "X-Appery-Push-Master-Key: {$this->app_key}",
            "Content-Type: application/json",
        ];
        $method = $this->method['sendnotification'];
        if ($devices && is_array($devices)) {
            foreach ($devices as $key => $device) {
                $device_id = $device['device_id'];
                $user_id = $device['uid'];
                $this->generateData($method, $data, $this->url, $device_id);
                if ($this->status_code == '200') {
                    $this->updateNotificationStatus($data['id'], $this->status_code, $this->response, $data['id'], $user_id, $device_id);
                } else {
                    $this->updateNotificationStatus($data['id'], $this->status_code, $this->response, $data['id'], $user_id, $device_id);
                }
            }
        } else {
            $this->generateData($method, $data, $this->url);
            $this->updateNotificationStatus($data['id'], $this->status_code, $this->response, $data['id']);
        }
    }
    /**
     * this function used to create data with device or without device id
     */
    public function generateData($method, $data, $url, $deviceId = false)
    {

        if ($deviceId) {//for deviceid
            $data = [
                    'filter'=>[ 'deviceID' => $deviceId ],
                    'payload' => [ 'message' => $data['message'], 'badge'=>'3','id'=>$data['id'] ],
                    'status' => 'sent',
                ];
        } else {//for no device id
            $data = [
                    'payload' => [ 'message' => $data['message'], 'badge'=>'3','id'=>$data['id'] ],
                    'status' => 'sent',
                ];
        }
        $this->request($method, $url, $data);//final call
    }
    /**
     * this functio used to get token of the device-id
     * @param string $deviceId unique device id
     */
    public function getDeviceToken($deviceId)
    {
        $url = 'https://api.appery.io/rest/push/reg/'.urlencode($deviceId);
        $this->headers  = [
            "X-Appery-App-ID: 2a8fb533-545d-4674-b962-7e041c53f1e5",
            "Content-Type: application/json",
        ];
        $method = 'GET';
        $this->request($method, $url);
        return json_decode($this->response, true);
    }
    /**
     * this function used to register deviceid with respective token and type either A(Android) or I (IOS)
     * @param string $deviceId unique id of the device
     * @param string $token appery device token
     * @param string $type device type (A or I)
     */
    public function registerDevice($deviceId, $token, $type = 'A')
    {
        $this->url = "https://api.appery.io/rest/push/reg";
        $this->headers  = [
            "X-Appery-App-ID: 2a8fb533-545d-4674-b962-7e041c53f1e5",
            "Content-Type: application/json",
        ];
        $method = 'POST';
        $data = [
                'deviceID' => $deviceId,
                'token' => $token,
                'type' => $type
            ];
        return $this->request($method, $this->url, $data);
    }

    /*
	 * this function used to comprises request and send data to appery push api
	 */
    public function request($method, $url, $data = false)
    {
        $message = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
        }
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        $this->response = curl_exec($ch);
        $this->status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return true;
    }
    public function databaseSample()
    {
        $this->url = "https://api.appery.io/rest/1/db/collections/Devices";
        $this->headers  = [
            "X-Appery-Database-Id: 5656e6d9e4b0eec00e7f4f2d",
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        $this->response = curl_exec($ch);
        $this->status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }
    /**
     * this function used to update notification status
     */
    public function updateNotificationStatus($id, $status, $response, $elementId, $uid = 'public', $deviceId = 'public')
    {
        PushNotifications::insert(
            [
                    'announcement_id' => $id,
                    'user_id' => $uid,
                    'device_id' => $deviceId,
                    'status' => $status,
                    'response' => $response,
                    'element_type' => $elementId,
                    'element_type' => 'announcement',
                ]
        );
    }
}
