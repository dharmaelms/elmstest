<?php

namespace App\Jobs\Elastic\Quizzes;

use App\Jobs\Job;
use App\Services\Elastic\IElasticService;

class IndexQuiz extends Job
{
    /**
     * @var int $quiz_id
     */
    protected $quiz_id;

    /**
     * @var boolean $is_new
     */
    protected $is_new;
  
    /**
     * Create a new job instance.
     * @param $quiz_id
     * @param bool $is_new
     */
    public function __construct($quiz_id, $is_new = true)
    {
        $this->quiz_id = $quiz_id;
        $this->is_new = $is_new;
    }

    /**
     * Execute the job.
     *
     * @param IElasticService $elastic_service
     */
    public function handle(IElasticService $elastic_service)
    {
        $elastic_service->indexQuiz($this->quiz_id, $this->is_new);
    }
}
