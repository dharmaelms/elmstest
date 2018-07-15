<?php

namespace App\Jobs\Elastic\Packages;

use App\Jobs\Job;
use App\Services\Elastic\IElasticService;

class IndexPackage extends Job
{

    /**
     * @var int $package_id
     */
    protected $package_id;

    /**
     * @var boolean $is_new
     */
    protected $is_new;

    /**
     * @var boolean $is_slug_updated
     */
    protected $is_slug_changed;

    /**
     * Create a new job instance.
     * @param $package_id
     * @param bool $is_slug_changed
     * @param bool $is_new
     */
    public function __construct($package_id, $is_slug_changed = false, $is_new = true)
    {
        $this->package_id = $package_id;
        $this->is_new = $is_new;
        $this->is_slug_changed = $is_slug_changed;
    }

    /**
     * Execute the job.
     *
     * @param IElasticService $elastic_service
     */
    public function handle(IElasticService $elastic_service)
    {
        $elastic_service->indexPackage($this->package_id, $this->is_slug_changed, $this->is_new);
    }
}
