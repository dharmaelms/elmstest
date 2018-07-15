<?php

namespace App\Services\Box;

/**
 * interface IBoxService
 * @package App\Services\Box
 */
/**
 * Interface IBoxService
 * @package App\Services\Box
 */
interface IBoxService
{
    /**
     * Get embed url for viewing
     * Expiry time for URL is 60 seconds and expiry time for session is 60 mins
     * @param $document_id int
     * @return string
     */
    public function getEmbedUrl($document_id);

    /**
     * Uploads document to BOX.
     * @param $document
     * @return \stdClass
     */
    public function uploadToBox($document);

    /**
     * Deletes document
     * @param stdClass
     * @return void
     */
    public function removeDocument($document);

    /**
     * Deletes document from box.
     * @param  stdClass
     * @return boolean
     */
    public function destroyDocument($document);

    /**
     * Method to get the folder id using folder name
     * @param $name string
     * @return integer
     */
    public function getFolderID($name);
}
