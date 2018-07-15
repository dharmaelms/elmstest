<?php
namespace App\Jobs\Elastic\Events;

use App\Jobs\Job;
use App\Services\Elastic\IElasticService;

class AssignEvent extends Job
{
    /**
     * @var int $event_id
     */
    protected $event_id;

    /**
     * Create a new job instance.
     * @param $event_id
     */
    public function __construct($event_id)
    {
        $this->event_id = $event_id;
    }

    /**
     * Execute the job.
     *
     * @param IElasticService $elastic_service
     */
    public function handle(IElasticService $elastic_service)
    {
        $elastic_service->assignEvent($this->event_id);
    }
}
