<?php

namespace App\Services\Module;


interface IModuleService
{
    /**
     * @param int|string $module
     * @param int|string $permission
     * @param string $permission_type
     * @return array
     *
     * @throws \App\Exceptions\RolesAndPermissions\PermissionNotFoundException or
     * \App\Exceptions\Module\ModuleNotFoundException
     */
    public function getModulePermissionDetails($module, $permission, $permission_type = null);
}