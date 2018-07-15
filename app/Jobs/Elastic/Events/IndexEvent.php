<?php

namespace App\Jobs\Elastic\Events;

use App\Jobs\Job;
use App\Services\Elastic\IElasticService;

class IndexEvent extends Job
{
    /**
     * @var int $event_id
     */
    protected $event_id;

    /**
     * @var boolean $is_new
     */
    protected $is_new;
  
    /**
     * Create a new job instance.
     * @param $event_id
     * @param bool $is_new
     */
    public function __construct($event_id, $is_new = true)
    {
        $this->event_id = $event_id;
        $this->is_new = $is_new;
    }

    /**
     * Execute the job.
     *
     * @param IElasticService $elastic_service
     */
    public function handle(IElasticService $elastic_service)
    {
        $elastic_service->indexEvent($this->event_id, $this->is_new);
    }
}
