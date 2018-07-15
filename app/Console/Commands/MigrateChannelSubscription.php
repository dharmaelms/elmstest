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

class MigrateChannelSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:channelSubscription  {from : User_id as Integer}
                                {to : User_id as Integer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'createUserChannelEntityRelation';

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
                    try {
                        $user_array = $user->toArray();
                        if (array_has($user_array, "relations")) {
                            $user_subscriptions = array_get($user_array, "subscription", []);
                            $this->createUserChannelEntityRelation($user, $user_subscriptions);
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

    private function createUserChannelEntityRelation($user, $user_subscriptions)
    {
        $program_context_data = $this->roleService->getContextDetails(Contexts::PROGRAM);

        $channel_subscriptions = collect($user_subscriptions)->filter(
            function ($subscription_array) {
                return (($subscription_array["program_type"] === ProgramType::CHANNEL) &&
                    (!array_has($subscription_array, "package_id")));
            }
        );

        if (!$channel_subscriptions->isEmpty()) {
            foreach ($channel_subscriptions as $channel_subscription) {
                $enrollment_source_data["source_type"] = EnrollmentSource::SUBSCRIPTION;
                $enrollment_source_data["subscription_slug"] = $channel_subscription["subscription_slug"];
                $entity_data["entity_type"] = UserEntity::PROGRAM;
                $entity_data["entity_id"] = $channel_subscription["program_id"];
                $entity_data["valid_from"] = $channel_subscription["start_time"];
                $entity_data["expire_on"] = $channel_subscription["end_time"];
                $this->userService->enrollEntityToUser($user->uid, $entity_data, $enrollment_source_data);
                $this->roleService->mapUserAndRole(
                    $user->uid,
                    $program_context_data["id"],
                    $user->role,
                    $channel_subscription["program_id"]
                );
            }
        }
    }
}
