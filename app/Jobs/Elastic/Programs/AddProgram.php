<?php

namespace App\Jobs\Elastic\Programs;

use App\Jobs\Job;
use App\Services\Elastic\IElasticService;

class AddProgram extends Job
{

    /**
     * @var int $program_id
     */
    protected $program_id;

    /**
     * Create a new job instance.
     * @param $program_id
     */
    public function __construct($program_id)
    {
        $this->program_id = $program_id;
    }

    /**
     * Execute the job.
     *
     * @param IElasticService $elastic_service
     */
    public function handle(IElasticService $elastic_service)
    {
        $elastic_service->assignProgram($this->program_id);
    }
}
