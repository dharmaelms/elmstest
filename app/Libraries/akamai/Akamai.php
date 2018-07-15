<?php

/*
	Base class which decides which driver to be called.
	@Options: "NetStorageUpload" and "FTPupload"
*/

class Akamai
{

    var $serviceProviders;
    var $type;
    var $client;

    function __construct()
    {
        $this->type = config('app.akamai.default_driver');
        $this->serviceProviders = [
            "netstorage" => "NetStorageUpload",
            "ftp" => "FTPupload",
        ];
        $this->client = new $this->serviceProviders[$this->type];
    }

    function dir($url)
    {
        return $this->client->dir($url);
    }

    function delete($url)
    {
        $this->client->delete($url);
    }

    function download($url)
    {
        return $this->client->download($url);
    }

    function rmdir($url)
    {
        return $this->client->rmdir($url);
    }

    function Push($filename, $raw_file_loc)
    {
        $response = $this->client->Upload($filename, $this->readFileData($raw_file_loc));
        return $response;
    }
    function statusCode()
    {
        return $this->client->getLastStatusCode();
    }
    private function readFileData($filename)
    {
        if (file_exists($filename)) {
            $handle = fopen($filename, "r");
            $contents = fread($handle, filesize($filename));
            fclose($handle);
            return $contents;
        } else {
            die('File Not Found in location "'.$filename.'"');
        }
    }
}

/*
	More Interface methods to be exposed
*/

interface AkamaiInterface
{

    function upload($url, $body);

    function dir($url);

    function download($url);

    function delete($url);
    
    function rmdir($url);
}

/*
	Original Source of this class can be found in https://github.com/raben/Akamai
*/

class NetStorageUpload implements AkamaiInterface
{

    public $host;
    public $auth;
    public $version = 1;

    private $_http;
    private $_last_status_code = null;

    public function __construct()
    {
        $this->config = config('app.akamai');
        $this->key = $this->config['key'];
        $this->key_name = $this->config['key_name'];
        // $this->version = $this->config->version;
        $this->host = $this->config['host'];
        $this->auth = new Akamai_Netstorage_Authorize($this->key, $this->key_name, $this->version);
    }

    /*
		Implemented Method from Interface
	*/

    public function Upload($url, $body)
    {
        $response_data = $this->_updateAction('upload', $url, ['body' => $body]);
        return ['data' => $response_data,'code' => $this->_last_status_code];
    }

    public function download($url)
    {
        return $this->_readOnlyAction('download', $url);
    }

    public function du($url)
    {
        return $this->_readOnlyAction('du', $url);
    }

    public function dir($url)
    {
        return $this->_readOnlyAction('dir', $url);
    }

    public function stat($url)
    {
        return $this->_readOnlyAction('stat', $url);
    }

    private function _readOnlyAction($action, $url)
    {
        if (!$this->auth) {
            throw new Exception('it is not authorized yet.');
        }

        $action_string = 'version='.$this->version;
        $action_string .= '&action='.$action;

        if ($action != 'download') {
            $action_string .= "&format=xml";
        }

        $auth_data  = $this->auth->getAuthData();
        $auth_sign  = $this->auth->getAuthSign($url, $action_string);
        
        $headers    = [
            "Accept:",
            "Accept-Encoding: identity",
            "X-Akamai-ACS-Auth-Data: {$auth_data}",
            "X-Akamai-ACS-Auth-Sign: {$auth_sign}",
            "X-Akamai-ACS-Action: {$action_string}"
        ];

        return $this->request('GET', $url, null, $headers);
    }

    public function mtime($url, $time)
    {
        $this->_updateAction('mtime', $url, ['mtime' => $time]);
    }

    public function rename($url, $destination)
    {
        $this->_updateAction('rename', $url, ['destination' => $destination]);
    }

    public function symlink($url, $target)
    {
        $this->_updateAction('symlink', $url, ['target' => $target]);
    }

    public function mkdir($url)
    {
        $this->_updateAction('mkdir', $url);
    }

    public function rmdir($url)
    {
        $this->_updateAction('rmdir', $url);
    }

    public function delete($url)
    {
        $this->_updateAction('delete', $url);
    }

    /**
     * quick_delete
     *
         * Used to perform a “quick-delete” of a selected directory (including all of its contents).
     * NOTE: The “quick-delete” action is disabled by default for security reasons, as it allows recursive
     *       removal of non-empty directory structures in a matter of seconds. If you wish to enable this feature,
         *       please contact your Akamai Representative with the NetStorage CPCode(s) for which you wish to
         *       use this feature.
     */
    public function quick_delete($url, $qd_confirm)
    {
        $this->_updateAction('quick-delete', $url, ['qd_confirm' => $qd_confirm]);
    }

    private function _updateAction($action, $url, $options = [])
    {
        if (!$this->auth) {
            throw new Exception('it is not authorized yet.');
        }

        $action_string = 'version='.$this->version;
        $action_string .= '&action='.$action;
        if ($action != 'download') {
            $action_string .= "&format=xml";
        }

        foreach ($options as $key => $value) {
            if (in_array($key, ['index_zip', 'mtime', 'size', 'md5', 'sha1', 'md5', 'destination', 'target', 'qd_confirm'])) {
                if ($key == 'target' || $key == 'destination') {
                    $value = urlencode($value);
                }
                if ($key == 'qd_confirm') {
                    $key = 'quick-delete';
                }
                $action_string .= "&{$key}={$value}";
            }
        }

        $auth_data  = $this->auth->getAuthData();
        $auth_sign  = $this->auth->getAuthSign($url, $action_string);
        
        $headers    = [
            "Accept:",
            "Accept-Encoding: identity",
            "X-Akamai-ACS-Auth-Data: {$auth_data}",
            "X-Akamai-ACS-Auth-Sign: {$auth_sign}",
            "X-Akamai-ACS-Action: {$action_string}"
        ];

        $body = (isset($options["body"])) ? $options["body"] : "";
        $method = 'PUT';
        return $this->request($method, $url, $body, $headers);
    }

    public function request($method, $url, $body, $headers)
    {
        $curl = curl_init('https://'.$this->host.$url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        $tmpfile = ""; // Fix added from https://github.com/raben/Akamai/pull/4/files
        if ($method == 'PUT') {
            $length = strlen($body);
            if ($length != 0) {
                $tmpfile = tmpfile();
                fwrite($tmpfile, $body);
                fflush($tmpfile); // Fix added from https://github.com/raben/Akamai/pull/4/files
                fseek($tmpfile, 0);
                curl_setopt($curl, CURLOPT_INFILE, $tmpfile);
            }
            curl_setopt($curl, CURLOPT_UPLOAD, 1);
            curl_setopt($curl, CURLOPT_INFILESIZE, strlen($body));
        }

        $data = curl_exec($curl);
        $this->_last_status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($tmpfile) { // Fix added from https://github.com/raben/Akamai/pull/4/files
            fclose($tmpfile); // Fix added from https://github.com/raben/Akamai/pull/4/files
        }        return $data;
    }

    public function getLastStatusCode()
    {
        return $this->_last_status_code;
    }
}

class FTPupload implements AkamaiInterface
{

    function Upload($url, $body)
    {
        die("Not Yet Implemented");
    }


    function dir($url)
    {
        die('Not Yet Implemented');
    }

    function download($url)
    {
        die('Not Yet Implemented');
    }

    function delete($url)
    {
        die('Not Yet Implemented');
    }

    function rmdir($url)
    {
        die('Not Yet Implemented');
    }
}

class Akamai_Netstorage_Authorize
{

    public $key;
    public $key_name;
    public $client;
    public $server;
    public $time;
    public $unique_id;
    public $version = 5;

    private $_data;

    public function __construct($key, $key_name, $version = 5)
    {
        $this->key = $key;
        $this->key_name = $key_name;
        $this->client   = '0.0.0.0';
        $this->server   = '0.0.0.0';
        $this->time     = time();
        $this->unique_id = mt_rand(1000000000, 9999999999);
        // $this->version	= $version;
    }

    public function getAuthData()
    {
        if (!$this->_data) {
            $this->_data = implode(', ', [
                $this->version,
                $this->server,
                $this->client,
                $this->time,
                $this->unique_id,
                $this->key_name
            ]);
        }
        return $this->_data;
    }

    public function getAuthSign($uri, $action)
    {
        $lf         = "\x0a";
#		$lf		= "\n";
        $label      = 'x-akamai-acs-action:';
        $authd      = $this->getAuthData();
        $sign_string    = $authd.$uri.$lf.$label.$action.$lf;

        $algorithm  = ($this->version == 3) ? "md5" :
                  ($this->version == 4) ? "sha1" :
                  ($this->version == 5) ? "sha256" :
                  null;
        if ($algorithm === null) {
            throw new Exception('it is not supported version ['.$this->version.']');
        }
        return base64_encode(hash_hmac($algorithm, $sign_string, $this->key, true));
    }
}
