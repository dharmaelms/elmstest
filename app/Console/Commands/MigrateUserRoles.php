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
use App\Events\User\EntityEnrollmentByAdminUser;
use App\Events\User\EntityEnrollmentThroughUserGroup;
use App\Events\User\EntityEnrollmentThroughSubscription;

use App\Enums\RolesAndPermissions\SystemRoles;
use App\Enums\RolesAndPermissions\Contexts;
use Illuminate\Support\Facades\Schema;
use App\Enums\User\EnrollmentSource;
use App\Services\User\IUserService;

class MigrateUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:user  {from : User_id as Integer}
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
                function ($user) use ($system_context_data, $log_collection, &$bar) {
                    Log::info('Updating user ' . $user->uid); 
                    try {

                        if ((int)$user->role === 4) {
                            $user->role = 3;
                            $user->save();
                        }

                        try {
                            $role = Role::findOrFail($user->role);
                        } catch (ModelNotFoundException $e) {
                            $user->role = 3;
                            $user->save();

                            $role = Role::findOrFail(3);
                        }

                        if ($role->slug !== SystemRoles::LEARNER) {
                            $parent_most_role = $this->getParentMostRole($role);
                            if ($parent_most_role->slug === SystemRoles::LEARNER) {
                                $user->role = $parent_most_role->rid;
                                $user->save();
                            }
                        }

                        if (!$role->is_admin_role) {
                            $registered_user_role_data =
                                $this->roleService->getRoleDetails(SystemRoles::REGISTERED_USER);

                            $this->roleService->mapUserAndRole(
                                $user->uid,
                                $system_context_data["id"],
                                $registered_user_role_data["id"]
                            );

                            $log_collection->insert(
                                [
                                    "user_id" => $user->uid,
                                    "old_role_id" => $user->role,
                                    "new_role_id" => $registered_user_role_data["id"]
                                ]
                            );
                        } else {
                            $this->roleService->mapUserAndRole(
                                $user->uid,
                                $system_context_data["id"],
                                $user->role
                            );
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

    private function getParentMostRole($role) {
        try {
            if (!($role->system_role && is_null($role->parent))) {
                $parent_role = Role::where("slug", $role->parent)->firstOrFail();
                $role = $this->getParentMostRole($parent_role);
            }
        } catch (ModelNotFoundException $e) {
            Log::notice("Could not find role with id {$role->rid}");
            Log::error($e->getTraceAsString());
        }

        return $role;
    }

}
