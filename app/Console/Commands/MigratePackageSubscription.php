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

class MigratePackageSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:packageSubscription  {from : User_id as Integer}
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
            $system_context_data = $this->roleService->getContextDetails(Contexts::SYSTEM);
            $log_collection = $this->mongodb->createCollection("system_level_role_change_logs");
            $bar = $this->output->createProgressBar(User::whereBetween('uid', [$from, $to])->count());
            User::whereBetween('uid',[$from, $to])->get()->each( 
                function ($user) use (&$bar) {
                    try {
                        $user_array = $user->toArray();
                        if (array_has($user_array, "relations")) {
                            $user_subscriptions = array_get($user_array, "subscription", []);
                            $this->createUserPackageEntityRelation($user, $user_subscriptions);
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

    private function createUserPackageEntityRelation($user, $user_subscriptions)
    {
        $package_migration_log_collection = $this->mongodb->selectCollection("package_migration_log");
        $package_subscriptions = collect($user_subscriptions)->filter(
            function ($subscription_array) {
                return (
                    ($subscription_array["program_type"] === ProgramType::CHANNEL) &&
                    (array_has($subscription_array, "package_id")) &&
                    ($subscription_array["package_id"] === $subscription_array["program_id"])
                );
            }
        );

        if (!$package_subscriptions->isEmpty()) {
            foreach ($package_subscriptions as $package_subscription) {
                $migrated_package_data =
                    $package_migration_log_collection->findOne(["program_id" => $package_subscription["program_id"]]);
                if (!is_null($migrated_package_data)) {
                    $enrollment_source_data["source_type"] = EnrollmentSource::SUBSCRIPTION;
                    $enrollment_source_data["subscription_slug"] = $package_subscription["subscription_slug"];
                    $entity_data["entity_type"] = UserEntity::PACKAGE;
                    $entity_data["entity_id"] = $migrated_package_data["package_id"];
                    $entity_data["valid_from"] = $package_subscription["start_time"];
                    $entity_data["expire_on"] = $package_subscription["end_time"];
                    $this->userService->enrollEntityToUser($user->uid, $entity_data, $enrollment_source_data);

                    $this->mapUserRoleWithPackageChannels(
                        $user->uid,
                        $user->role,
                        $package_subscription["program_id"]
                    );
                } else {
                    Log::notice("Could not find migrated package data for id {$package_subscription["program_id"]}");
                }
            }
        }
    }

    private function mapUserRoleWithPackageChannels($user_id, $role_id, $package_id)
    {
        try {
            $package = Program::findOrFail($package_id, ["child_relations.active_channel_rel"]);
            $package_channel_ids = array_get($package->toArray(), "child_relations.active_channel_rel", []);
            $program_context_data = $this->roleService->getContextDetails(Contexts::PROGRAM);
            foreach ($package_channel_ids as $channel_id) {
                $this->roleService->mapUserAndRole($user_id, $program_context_data["id"], $role_id, $channel_id);
            }
        } catch (ModelNotFoundException $e) {
            Log::notice("Could not find package with id {$package_id}");
            Log::error($e->getTraceAsString());
        }
    }
}
