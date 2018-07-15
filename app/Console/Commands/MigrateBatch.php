<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Enums\Program\ProgramType;
use App\Enums\User\UserEntity;
use App\Model\Program;
use App\Model\User;
use App\Model\Role;
use App\Model\UserGroup;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\App;
use App\Services\Role\IRoleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\RolesAndPermissions\SystemRoles;
use App\Enums\RolesAndPermissions\Contexts;
use Illuminate\Support\Facades\Schema;
use App\Enums\User\EnrollmentSource;
use App\Services\User\IUserService;

class MigrateBatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:batch  {from : User_id as Integer}
                                {to : User_id as Integer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrating user roles';

    /**
     * Create a new command instance.
     *
     * @return void
     */

     /**
     * @var \App\Services\Role\IRoleService
     */
    private $roleService;

    /**
     * @var \App\Services\User\IUserService
     */
    private $userService;


    /**
     * @var MongoDB
     */
    private $mongodb;

    public function __construct()
    {
        $this->mongodb = DB::getMongoDB();
        $this->roleService = App::make(IRoleService::class);
        $this->userService = App::make(IUserService::class);
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
       $from = (int) $this->argument('from');
       $to = (int) $this->argument('to');
       try {
            $bar = $this->output->createProgressBar(User::whereBetween('uid', [$from, $to])->count());
            User::whereBetween('uid', [$from, $to])->get()->each( 
                function ($user) use (&$bar) {
                    try {
                        $user_array = $user->toArray();
                        if (array_has($user_array, "relations")) {
                            $user_batch_ids = array_get($user_array, "relations.user_course_rel", []);
                            $this->createUserBatchEntityRelation($user, $user_batch_ids);
                        }
                        $bar->advance();
                    } catch (ModelNotFoundException $e) {
                        Log::notice("Could not find role with id {$user->role}");
                        Log::error($e->getTraceAsString());
                    }
                }
            );
            $bar->finish();
        } catch (\Exception $e) {
            Log::error($e->getTraceAsString());
        }
    }

    private function createUserBatchEntityRelation($user, $user_batch_ids)
    {
        foreach ($user_batch_ids as $batch_id) {
            try {
                $package = Program::findOrFail($batch_id, ["program_startdate", "program_enddate"]);
                $enrollment_source_data["source_type"] = EnrollmentSource::DIRECT_ENROLLMENT;
                $entity_data["entity_type"] =  UserEntity::BATCH;
                $entity_data["entity_id"] = $batch_id;
                $entity_data["valid_from"] = null;
                $entity_data["expire_on"] = null;
                $this->userService->enrollEntityToUser($user->uid, $entity_data, $enrollment_source_data);
                $batch_context_data = $this->roleService->getContextDetails(Contexts::BATCH);
                $this->roleService->mapUserAndRole($user->uid, $batch_context_data["id"], $user->role, $batch_id);
            } catch (ModelNotFoundException $e) {
                Log::notice("Could not find batch with id {$batch_id}");
                Log::error($e->getTraceAsString());
            }
        }
    }
}
