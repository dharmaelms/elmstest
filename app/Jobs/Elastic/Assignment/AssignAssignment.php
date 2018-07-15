<?php
namespace App\Jobs\Elastic\Events;

use App\Jobs\Job;
use App\Services\Elastic\IElasticService;

class AssignAssignment extends Job
{
    /**
     * @var int $assignment_id
     */
    protected $assignment_id;

    /**
     * Create a new job instance.
     * @param $event_id
     */
    public function __construct($assignment_id)
    {
        $this->assignment_id = $assignment_id;
    }

    /**
     * Execute the job.
     *
     * @param IElasticService $elastic_service
     */
    public function handle(IElasticService $elastic_service)
    {
        $elastic_service->AssignAssignment($this->assignment_id);
    }
}
