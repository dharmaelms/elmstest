<?php
namespace App\Model\WebExHost\Repository;

interface IWebExHostRepository
{
    /**
     * Method to return WebExHost names with username
     *
     * @return mixed
     */
    public function getWebExHosts();

    /**
     * @param int $webex_host_id
     * @return WebexHost|null
     */
    public function getWebHostDetails($webex_host_id);

    /**
     * @param array $webex_host_details
     * @return boolean|true
     */
    public function updateStorageLimit($Webex_id, $storage_limit);
}
