<?php

namespace App\Services\Box;

use Box\View\Client;
use App\Model\Dams\Repository\IDamsRepository;
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
    /**
     * BoxViewer API key.
     * @var String
     */
    private $box_key;
    /**
     * BoxViewer client
     * @var \Box\View\Client
     */
    private $box_view_client;

    /**
     * @var \App\Model\Dams\Repository\IDamsRepository
     */
    private $dams_repository;

    public function __construct(IDamsRepository $dams_repository)
    {
        $this->box_key = config('app.viewer_api_key');
        $this->box_view_client = new Client($this->box_key);
        $this->dams_repository = $dams_repository;
    }
    /**
     * {@inheritdoc}
     */
    public function getDocument($box_document_id)
    {
        try {
            return $this->box_view_client->getDocument($box_document_id);
        } catch (Exception $e) {
            throw new BoxDocumentNotFoundException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function uploadToBox($document, $new_file)
    {
        $data = new stdClass();
        try {
            $data->document_id = null;
            $data->status = BoxDocumentStatus::UPLOADING;
            $data->uploaded_at = null;
            
            $this->dams_repository->updateBoxDetailsStatus($document, $data);

            $file_handle = fopen($new_file->file_path, 'r');
            $box_document = $this->box_view_client->uploadFile($file_handle);

            $data->document_id = $box_document->id();
            $data->status = BoxDocumentStatus::UPLOADED;
            $data->uploaded_at = time();
            
            $this->dams_repository->updateBoxDetailsStatus($document, $data);
        } catch (Exception $e) {
            Log::error('BOX: '.$e->getMessage());
            $data->status = BoxDocumentStatus::PENDING;
            $this->dams_repository->updateBoxDetailsStatus($document, $data);
            throw new BoxDocumentUploadFailedException();
        }

        return $box_document;
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
        try {
            $box_document = $this->getDocument(array_get($document->box_details, 'document_id'));
            return $box_document->delete();
        } catch (Exception $e) {
            /**
             * All the documents might have not uploaded to BOX, it will throw exception if  the document is
             * not present in BOX. As per the application flow, its not necessary to break the flow.
             * That is why its caught, logged and returning false, so that flow will continue.
             */
            Log::error('BOX: '.$e->getMessage());
            return false;
        }
    }
}
