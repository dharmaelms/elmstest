<?php

namespace App\Listeners;

use App\Enums\RolesAndPermissions\PermissionType;
use App\Services\Role\IRoleService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Session;

class SaveUserSessionData
{
    /**
     * @var IRoleService
     */
    private $roleService;

    /**
     * Create the event listener.
     *
     * @param IRoleService $roleService
     */
    public function __construct(IRoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $this->roleService->createUserPermissionsListCacheFile($event->user->uid);
        $accessible_admin_modules = $this->roleService->getUserAccessibleModules(
            $event->user->uid,
            PermissionType::ADMIN
        );

        $admin_permissions = $this->roleService->getUserPermissions($event->user->uid, PermissionType::ADMIN);

        Session::put("accessible_admin_modules", $accessible_admin_modules);
        Session::put("admin_permissions", $admin_permissions);
    }
}
