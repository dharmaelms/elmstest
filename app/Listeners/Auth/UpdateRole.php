<?php

namespace App\Listeners\Auth;

use App\Enums\RolesAndPermissions\Contexts;
use App\Enums\RolesAndPermissions\SystemRoles;
use App\Events\Auth\UpdateUserRole;
use App\Services\Role\IRoleService;

class UpdateRole
{
    /**
     * @var IRoleService
     */
    private $roleService;

    /**
     * Create the event listener.
     * @param IRoleService $roleService
     */
    public function __construct(IRoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Handle the event.
     *
     * @param  Registered  $event
     * @return void
     */
    public function handle(UpdateUserRole $event)
    {
        $role_id = $event->role_id;

        if (is_null($role_id)) {
            $registered_user_role = $this->roleService->getRoleDetails(SystemRoles::REGISTERED_USER);
            $role_id = $registered_user_role["id"];
        }
        
        $this->roleService->updateUserSystemContextRole($event->user_id, $role_id);
    }
}
