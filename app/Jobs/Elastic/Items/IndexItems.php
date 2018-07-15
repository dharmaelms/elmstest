<?php

namespace App\Jobs\Elastic\Items;

use App\Jobs\Job;
use App\Services\Elastic\IElasticService;

class IndexItems extends Job
{
    /**
     * @var int $post_id
     */
    public $post_id;

    /**
     * Create a new job instance.
     * @param $post_id
     */
    public function __construct($post_id)
    {
        $this->post_id = $post_id;
    }

    /**
     * Execute the job.
     *
     * @param IElasticService $elastic_service
     */
    public function handle(IElasticService $elastic_service)
    {
        $elastic_service->indexItems($this->post_id);
    }
}
