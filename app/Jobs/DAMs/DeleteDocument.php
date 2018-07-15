<?php

namespace App\Jobs\DAMs;

use App\Jobs\Job;
use App\Services\Box\IBoxService;

use stdClass;
use Log;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteDocument extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

     /**
     * @var Object
     */
    protected $doc_box_info;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($input)
    {
        $this->doc_box_info = $input;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(IBoxService $box_service)
    {
        $data = new stdClass();

        $data->box_details = array_get($this->doc_box_info, 'box_details');
        
        if ($box_service->destroyDocument($data)) {
            Log::info('BOX: Document '.array_get($this->doc_box_info, 'file_client_name').' deleted from box.');
        }
    }
}
