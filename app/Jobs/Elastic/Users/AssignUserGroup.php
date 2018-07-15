<?php
namespace App\Jobs\Elastic\Users;

use App\Jobs\Job;
use App\Services\Elastic\IElasticService;

class AssignUserGroup extends Job
{
    /**
     * @var int $user_group_id
     */
    protected $user_group_id;

    /**
     * Create a new job instance.
     * @param $user_group_id
     */
    public function __construct($user_group_id)
    {
        $this->user_group_id = $user_group_id;
    }

    /**
     * Execute the job.
     *
     * @param IElasticService $elastic_service
     */
    public function handle(IElasticService $elastic_service)
    {
        $elastic_service->assignUserGroup($this->user_group_id);
    }
}
