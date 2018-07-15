<?php
use App\Enums\Program\ProgramType;
use App\Enums\User\UserEntity;
use App\Model\Program;
use App\Model\User;
use App\Model\Role;
use App\Model\UserGroup;
use App\Model\User\Entity\UserEnrollment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

class InsertMissingRecordsInUserEnrollments extends Migration
{   
    /**
     * @var \App\Services\Role\IRoleService
     */
    private $roleService;

    /**
     * @var MongoDB
     */
    private $mongodb;

    public function __construct()
    {
        $this->mongodb = DB::getMongoDB();
        $this->roleService = App::make(IRoleService::class);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {

            $system_context_data = $this->roleService->getContextDetails(Contexts::SYSTEM);
            $log_collection = DB::collection('system_level_role_change_logs');
            User::where('relations.user_feed_rel', 'exists', true)->get()->each(
                function ($user) use ($system_context_data, $log_collection) {
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

                $user_array = $user->toArray();
                $uid = array_get($user_array, "uid");
                $user_channel_ids = array_get($user_array, "relations.user_feed_rel", []);
                $user_mapping_channel_ids = $user_channel_ids;
                $user_subscriptions = array_get($user_array, "subscription", []);
                if (!empty($user_mapping_channel_ids)){
                    foreach ($user_mapping_channel_ids as $key => $channel_id) {
                       $userEnrollment =  UserEnrollment::where("user_id", $uid)->where("entity_id", $channel_id)->get()->toArray();
                       if (empty($userEnrollment)) {
                        
                            $this->createUserChannelEntityRelation($user, $user_channel_ids, $user_subscriptions);

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
                        }
                    }
                }
       
            });

        } catch (\Exception $e) {
            Log::error($e->getTraceAsString());
        }    
    }




    private function createUserChannelEntityRelation($user, $user_channel_ids, $user_subscriptions)
    {
        $user_channel_ids = array_diff($user_channel_ids, [0]);
        $program_context_data = $this->roleService->getContextDetails(Contexts::PROGRAM);
        foreach ($user_channel_ids as $channel_id) {
            event(
                new EntityEnrollmentByAdminUser(
                    $user->uid,
                    UserEntity::PROGRAM,
                    $channel_id
                )
            );

            $this->roleService->mapUserAndRole($user->uid, $program_context_data["id"], $user->role, $channel_id);
        }

        $channel_subscriptions = collect($user_subscriptions)->filter(
            function ($subscription_array) {
                return (($subscription_array["program_type"] === ProgramType::CHANNEL) &&
                    (!array_has($subscription_array, "package_id")));
            }
        );

        if (!$channel_subscriptions->isEmpty()) {
            foreach ($channel_subscriptions as $channel_subscription) {
                event(
                    new EntityEnrollmentThroughSubscription(
                        $user->uid,
                        UserEntity::PROGRAM,
                        $channel_subscription["program_id"],
                        $channel_subscription["start_time"],
                        $channel_subscription["end_time"],
                        $channel_subscription["subscription_slug"]
                    )
                );

                $this->roleService->mapUserAndRole(
                    $user->uid,
                    $program_context_data["id"],
                    $user->role,
                    $channel_subscription["program_id"]
                );
            }
        }
    }

    private function getParentMostRole($role)
    {
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






    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
