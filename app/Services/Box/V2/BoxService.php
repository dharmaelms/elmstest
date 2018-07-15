<?php

namespace App\Services\Box\V2;

use App\Enums\DAMs\MediaVisibility;
use App\Model\Dams\Repository\IDamsRepository;
use App\Services\Box\IBoxService;
use Linkstreet\Box\Box;
use Webmozart\Assert\Assert;
use App\Enums\DAMs\BoxDocumentStatus;
use App\Exceptions\Box\BoxDocumentNotFoundException;
use App\Exceptions\Box\BoxDocumentUploadFailedException;

use stdClass;
use Exception;
use Log;

/**
 * @package App\Services\Box
 */
class BoxService implements IBoxService
{

    private $box_config = [];
    private $box_sdk = null;
    private $app_auth = null;

    /**
     * @var \App\Model\Dams\Repository\IDamsRepository
     */
    private $dams_repository;

    public function __construct(IDamsRepository $dams_repository)
    {
        $this->dams_repository = $dams_repository;
    }
    /**
     * {@inheritdoc}
     */
    public function getEmbedUrl($box_document_id)
    {
        Log::info('BOX: Generating embed url for ' . $box_document_id);
        return $this->getAppAuth()->getFileService()->getEmbedUrl($box_document_id);
    }

    /**
     * {@inheritdoc}
     */
    public function uploadToBox($document)
    {
        $file_service = $this->getAppAuth()->getFileService();

        $media_visibility = MediaVisibility::PUBLIC_MEDIA;
        if ($document->visibility == MediaVisibility::PRIVATE_MEDIA) {
            $media_visibility = MediaVisibility::PRIVATE_MEDIA;
        }

        $path = getcwd() .
            DIRECTORY_SEPARATOR .
            config("app." . strtolower($media_visibility) . "_dams_documents_path") .
            $document->unique_name_with_extension
            ;

        $response = null;

        $folder_id = $this->getFolderID($this->box_config['others']['folder_name']);

        try {

            $data = new stdClass();
            $data->document_id = null;
            $data->status = BoxDocumentStatus::UPLOADING;
            $data->uploaded_at = null;
            $data->folder_name = array_get($this->box_config, 'others.folder_name');

            $this->dams_repository->updateBoxDetailsStatus($document, $data);

            LOG::info("BOX: Uploading file " . $path . " to " . $this->box_config['others']['folder_name'] . "(" . $folder_id . ")");
            $file = json_decode($file_service->upload($path, $folder_id)->getBody()->getContents());
            Log::info('BOX: '.json_encode($file));

            $data->document_id = (int)head($file->entries)->id;
            $data->status = BoxDocumentStatus::UPLOADED;
            $data->uploaded_at = time();
            $data->folder_name = array_get($this->box_config, 'others.folder_name');

            $this->dams_repository->updateBoxDetailsStatus($document, $data);
            $response = $data;
        } catch (Exception $e) {
            Log::error('BOX: '.$e->getMessage());
            $data->status = BoxDocumentStatus::PENDING;
            $data->folder_name = array_get($this->box_config, 'others.folder_name');
            $this->dams_repository->updateBoxDetailsStatus($document, $data);
            throw new BoxDocumentUploadFailedException($e->getMessage());
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function removeDocument($document)
    {
        if (array_get($document->box_details, 'document_id')) {
            try {
                $this->destroyDocument($document);

                $data = new stdClass();

                $data->document_id = null;
                $data->status = BoxDocumentStatus::PENDING;
                $data->uploaded_at = null;

                $this->dams_repository->updateBoxDetailsStatus($document, $data);
            } catch (Exception $e) {
                Log::error('BOX: '.$e->getMessage());
                throw $e;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function destroyDocument($document)
    {
        $file_service = $this->getAppAuth()->getFileService();
        try {
            $document_id = (int)array_get($document->box_details, 'document_id');

            Log::info('BOX: Deleting document with id ' . $document_id);
            // Returns null on success, throws exception on failure
            $file_service->delete($document_id);
            $file_service->destroyTrashedFile($document_id);
        } catch (Exception $e) {
            /**
             * All the documents might have not uploaded to BOX, it will throw exception if  the document is
             * not present in BOX. As per the application flow, its not necessary to break the flow.
             * That is why its caught, logged and returning false.
             */
            Log::error('BOX: '.$e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFolderID($name)
    {
        Assert::stringNotEmpty($name, 'The folder name must be string and not empty. Got: %s');
        $folder_service = $this->getAppAuth()->getFolderService();
        $folder_id = null;

        // Get root folder and search for $name since there is no api to get folder by name.
        $folders = json_decode($folder_service->getFolderItems()->getBody()->getContents());

        // Search for folders with same name.
        foreach ($folders->entries as $item) {
            // TODO: Move `folder` to enums
            if ($item->type == "folder" && trim($item->name) === trim($name)) {
                $folder_id = (int)$item->id;
            }
        }

        // If no folder is present, create one in the root and return the ID
        if (is_null($folder_id)) {

            Log::info("BOX: Folder name ( " . $name . " ) not found. System will try to create one");
            $folder_id = json_decode($folder_service->create($name)->getBody()->getContents())->id;
            Log::info("BOX: Folder name ( " . $name . " ) created with ID - " . $folder_id);
        }

        return $folder_id;

    }

    /**
     * @return \Linkstreet\Box\Auth\AppAuth
     */
    private function getAppAuth()
    {
        // Moved all init logic to this method since
        // BoxService was initialized even when box is switched off (cos of dependency injection).
        if (empty($this->box_config)){
            $box_config = config('app.box');

            Assert::stringNotEmpty(array_get($box_config, 'others.folder_name'), 'The folder name must be string and not empty. Got: %s');
            $this->box_config = config('app.box');
        }

        if (is_null($this->box_sdk)) {
            // Throws exception on invalid credentials
            $this->box_sdk = new Box(array_get($this->box_config, 'sdk_info'));
        }

        if (is_null($this->app_auth)) {
            $this->app_auth = $this->box_sdk->getAppAuthClient(array_get($this->box_config, 'app_auth_info'));
        }

        return $this->app_auth;

    }

}
