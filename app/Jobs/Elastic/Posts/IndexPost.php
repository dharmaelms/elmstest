<?php

namespace App\Jobs\Elastic\Posts;

use App\Jobs\Job;
use App\Services\Elastic\IElasticService;

class IndexPost extends Job
{
    /**
     * @var int $post_id
     */
    protected $post_id;

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
     * @param $post_id
     * @param bool $is_slug_changed
     * @param bool $is_new
     */
    public function __construct($post_id, $is_slug_changed = false, $is_new = true)
    {
        $this->post_id = $post_id;
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
        $elastic_service->indexPost($this->post_id, $this->is_slug_changed, $this->is_new);
    }
}
