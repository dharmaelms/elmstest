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

class MigrateUserGroupParentRel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:userGroupParentRel  {from : User_id as Integer}
                                {to : User_id as Integer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'createUserEntityRelationsThroughUserGroups';

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
            User::whereBetween('uid',[$from, $to])->get()->each( 
                function ($user) use (&$bar) {
                    Log::info('Updating user usergroup parent rel' . $user->uid); 
                    try {
                        $user_array = $user->toArray();
                        if (array_has($user_array, "relations")) {
                            $this->createUserEntityRelationsThroughUserGroups($user);
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

    private function createUserEntityRelationsThroughUserGroups($user)
    { 
        $user_array = $user->toArray();
        $assigned_user_group_ids = array_get($user_array, "relations.active_usergroup_user_rel", []);
        $program_context_data = $this->roleService->getContextDetails(Contexts::PROGRAM);
        foreach ($assigned_user_group_ids as $user_group_id) {
            try {
                $user_group = UserGroup::findOrFail($user_group_id);
                $user_group_array = $user_group->toArray();
                $user_group_package_ids = array_get($user_group_array, "relations.usergroup_parent_feed_rel", []);

                $package_migration_log_collection = $this->mongodb->selectCollection("package_migration_log");
                foreach ($user_group_package_ids as $package_id) {
                    $migrated_package_data =
                        $package_migration_log_collection->findOne(["program_id" => $package_id]);
                    if (!is_null($migrated_package_data)) {
                        $enrollment_source_data["source_type"] = EnrollmentSource::USER_GROUP;
                        $enrollment_source_data["source_id"] = $user_group_id;
                        $entity_data["entity_type"] = UserEntity::PACKAGE;
                        $entity_data["entity_id"] = $migrated_package_data["package_id"];
                        $entity_data["valid_from"] = null;
                        $entity_data["expire_on"] = null;
                        $this->userService->enrollEntityToUser($user->uid, $entity_data, $enrollment_source_data);
                        try {
                            $package = Program::findOrFail($package_id, ["child_relations.active_channel_rel"]);
                            $package_channel_ids = array_get($package->toArray(), "child_relations.active_channel_rel", []);
                            foreach ($package_channel_ids as $channel_id) {
                                $this->roleService->mapUserAndRole(
                                    $user->uid,
                                    $program_context_data["id"],
                                    $user->role,
                                    $channel_id
                                );
                            }
                        } catch (ModelNotFoundException $e) {
                            Log::notice("Could not find package with id {$package_id}");
                            Log::error($e->getTraceAsString());
                        }
                    } else {
                        Log::notice("Could not find migrated package data for id {$package_id}");
                    }
                }
            } catch (ModelNotFoundException $e) {
                Log::notice("Could not find user group with id {$user_group_id}");
                Log::error($e->getTraceAsString());
            }
        }
    }
}
