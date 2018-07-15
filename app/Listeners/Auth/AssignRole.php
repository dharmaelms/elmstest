<?php

namespace App\Listeners\Auth;

use App\Enums\RolesAndPermissions\Contexts;
use App\Enums\RolesAndPermissions\SystemRoles;
use App\Events\Auth\Registered;
use App\Services\Role\IRoleService;

class AssignRole
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
    public function handle(Registered $event)
    {
        $system_context = $this->roleService->getContextDetails(Contexts::SYSTEM);
        $role_id = $event->role_id;
        if (is_null($role_id)) {
            $registered_user_role = $this->roleService->getRoleDetails(SystemRoles::REGISTERED_USER);
            $role_id = $registered_user_role["id"];
        }

        $this->roleService->mapUserAndRole($event->user_id, $system_context["id"], $role_id);
    }
}
