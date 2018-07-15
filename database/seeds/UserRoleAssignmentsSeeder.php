<?php

use App\Enums\RolesAndPermissions\Contexts;
use App\Enums\RolesAndPermissions\SystemRoles;
use App\Model\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class UserRoleAssignmentsSeeder extends Seeder
{
    private $collection = "user_role_assignments";

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user_role_assignment_array = [
            [
                "user_email" => "ultron@linkstreet.in",
                "context_slug" => Contexts::SYSTEM,
                "instance_id" => null,
                "role_slug" => SystemRoles::SUPER_ADMIN
            ],
            [
                "user_email" => "admin@ultron.com",
                "context_slug" => Contexts::SYSTEM,
                "instance_id" => null,
                "role_slug" => SystemRoles::SITE_ADMIN
            ],
        ];
        // Removing existing collection
        Schema::drop($this->collection);

        $roleService = App::make(\App\Services\Role\IRoleService::class);
        foreach ($user_role_assignment_array as $user_role_assignment) {
            $user_id = User::getIdByEmail($user_role_assignment["user_email"]);
            $context_data = $roleService->getContextDetails($user_role_assignment["context_slug"]);
            $role_data = $roleService->getRoleDetails($user_role_assignment["role_slug"]);

            $roleService->mapUserAndRole($user_id, $context_data["id"], $role_data["id"]);
        }
    }
}
