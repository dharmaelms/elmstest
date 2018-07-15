<?php

namespace App\Jobs\Elastic\Quizzes;

use App\Jobs\Job;
use App\Services\Elastic\IElasticService;

class DeleteQuiz extends Job
{
    /**
     * @var int $quiz_id
     */
    protected $quiz_id;
     
    /**
     * Create a new job instance.
     * @param $quiz_id
     */
    public function __construct($quiz_id)
    {
        $this->quiz_id = $quiz_id;
    }

    /**
     * Execute the job.
     *
     * @param IElasticService $elastic_service
     */
    public function handle(IElasticService $elastic_service)
    {
        $elastic_service->deleteQuiz($this->quiz_id);
    }
}
