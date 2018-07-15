<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Enums\User\UserEntity;
use App\Model\Program;
use App\Model\User;
use App\Model\Role;
use App\Model\User\Entity\UserEnrollment;
use Illuminate\Support\Facades\App;
use App\Services\Role\IRoleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\User\EntityEnrollmentByAdminUser;
use App\Enums\RolesAndPermissions\SystemRoles;
use App\Enums\RolesAndPermissions\Contexts;
use Illuminate\Support\Facades\Schema;

class InsertMissingChannelUserRelationInUserEnrollments extends Migration
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
            $program = Program::where('program_type', 'content_feed')
                    ->where('program_sub_type', 'single')
                    ->where('relations.active_user_feed_rel', 'exists', true)
                    ->get(['relations.active_user_feed_rel', 'program_id'])
                    ->toArray();
            $role_info = $this->roleService->getRoleDetails(SystemRoles::LEARNER, ['context']);
            $role_learner_id = array_get($role_info, 'id');
            $program_context = $this->roleService->getContextDetails(Contexts::PROGRAM);
            foreach ($program as $key => $value) {
                $active_user_feed_rel = array_get($value, 'relations.active_user_feed_rel', []);
                $program_id = $value['program_id'];
                if (!empty($active_user_feed_rel)) {
                    foreach ($active_user_feed_rel as $key => $value) {
                        $user_id = $value;

                        $role_site_admin_info = $this->roleService->getRoleDetails(SystemRoles::SITE_ADMIN, ['context']);
                        $role_site_admin_id = array_get($role_site_admin_info, 'id');

                        $user_details = User::where('uid', $user_id)->get(['role'])->toArray();
                        $user_details = array_get($user_details, 0);
                        $user_details_role_id = array_get($user_details, 'role');
                        if ($role_site_admin_id = $user_details_role_id) {
                            $role_id = $role_site_admin_id;
                        } else {
                            $role_id = $role_learner_id;
                        }

                        $userEnrollment = UserEnrollment::where('user_id', $user_id)
                                            ->where('entity_type', 'PROGRAM')
                                            ->where('entity_id', $program_id)
                                            ->where('status', 'ENROLLED')
                                            ->where('source_type', 'DIRECT_ENROLLMENT')
                                            ->orderBy('enrolled_on', 'desc')
                                            ->limit(1)->get()->toArray();
                        if (!empty($userEnrollment)) {
                            $userEnrollment = $userEnrollment[0];                   
                            $status = array_get($userEnrollment, 'status');
                        } else {
                            event(new EntityEnrollmentByAdminUser($user_id, UserEntity::PROGRAM, $program_id));
                            $this->roleService->mapUserAndRole(
                                    $user_id,
                                    $program_context["id"],
                                    $role_id,
                                    $program_id
                            );
                            
                        }                   
                    }
                }
            }
            echo 'Inserted missing channel user relation in user_enrollments collection successfuly.';
        }
        catch (\Exception $e) {
            Log::error($e->getTraceAsString());
        } 
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
