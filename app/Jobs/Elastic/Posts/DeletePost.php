<?php

namespace App\Jobs\Elastic\Posts;

use App\Jobs\Job;
use App\Services\Elastic\IElasticService;

class DeletePost extends Job
{
    /**
     * @var int $post_id
     */
    protected $post_id;

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
        $elastic_service->deletePost($this->post_id);
    }
}
