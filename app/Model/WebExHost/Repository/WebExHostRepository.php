<?php
    namespace App\Model\WebExHost\Repository;

    use App\Model\WebexHost;

    class WebExHostRepository implements IWebExHostRepository
    {
        public function getWebExHosts()
        {
            return WebexHost::Active()
                            ->get()
                            ->map(function($host){
                                return [
                                    'webex_host_id' => $host->webex_host_id,
                                    'name' => $host->name,
                                    'username' => $host->username,
                                    'storage_limit' => (!is_null($host->storage_limit)) ? $host->storage_limit : 0
                                ];
                            });
        }

        public function getWebHostDetails($webex_host_id)
        {
            return WebexHost::where('webex_host_id', $webex_host_id)->first();
        }

        public function updateStorageLimit($Webex_id, $storage_limit)
        {
            WebexHost::where('webex_host_id', '=', $Webex_id)->update(['storage_limit' => (int)$storage_limit]);
            return true;
        }
    }
