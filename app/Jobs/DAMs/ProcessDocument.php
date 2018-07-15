<?php

namespace App\Jobs\DAMs;

use App\Jobs\Job;
use App\Services\Box\IBoxService;
use App\Model\Dams\Repository\IDamsRepository;
use App\Enums\DAMs\BoxDocumentStatus;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;
use Exception;

/**
 * Class ProcessDocument
 * @package App\Jobs\DAMs
 */
class ProcessDocument extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var Object
     */
    protected $upload_info;

    /**
     * ProcessDocument constructor.
     * @param $input \stdClass
     */
    public function __construct($input)
    {
        $this->upload_info = $input;
    }

    /**
     * Execute the job.
     * @param IBoxService $box_service
     * @param IDamsRepository $dams_repository
     */
    public function handle(
        IBoxService $box_service,
        IDamsRepository $dams_repository
    ) {
        /**
         * In case of edit, the existing file is replaced with new file.
         */
        try {
            if ($this->upload_info->new_file) {
                $document = $dams_repository->getMedia($this->upload_info->id);
                if (array_get($document->box_details, 'document_id')) {
                    // Check if the file is already uploaded.
                    if (array_get($document->box_details, 'status') === BoxDocumentStatus::UPLOADED) {
                        // In case of edit, remove the previous file from BOX.
                        $box_service->removeDocument($document);
                    }
                }

                // Upload the new document to BOX.
                $box_service->uploadToBox($document);
                Log::info('BOX: Document '.$document->file_client_name.' uploaded to box.');
            }
        } catch (Exception $e) {
            Log::error('BOX: While document upload : Msg :'.$e->getMessage());
        }
    }
}
