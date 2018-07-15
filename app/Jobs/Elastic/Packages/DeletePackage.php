<?php

namespace App\Jobs\Elastic\Packages;

use App\Jobs\Job;
use App\Services\Elastic\IElasticService;

class DeletePackage extends Job
{

    /**
     * @var int $package_id
     */
    protected $package_id;

    /**
     * Create a new job instance.
     * @param $package_id
     */
    public function __construct($package_id)
    {
        $this->package_id = $package_id;
    }

    /**
     * Execute the job.
     *
     * @param IElasticService $elastic_service
     */
    public function handle(IElasticService $elastic_service)
    {
        $elastic_service->deletePackage($this->package_id);
    }
}
