<?php

namespace App\Model\Module\Repository;

interface IModuleRepository
{
    /**
     * @param int $id
     * @return \App\Model\Module\Entity\Module
     *
     * @throws \App\Exceptions\Module\ModuleNotFoundException
     */
    public function find($id);

    /**
     * @param string $attribute
     * @param int|string|boolean $value
     * @return \App\Model\Module\Entity\Module
     *
     * @throws \App\Exceptions\Module\ModuleNotFoundException
     */
    public function findByAttribute($attribute, $value);

    /**
     * @param int|string|\App\Model\Module\Entity\Module $module unique numeric id or slug or module object
     * @param int|string $permission unique id or slug
     * @param string $permission_type When permission slug is passed it is mandatory to pass permission type
     * @return \App\Model\RolesAndPermissions\Entity\Permission
     *
     * @throws \App\Exceptions\Module\ModuleNotFoundException or
     * App\Exceptions\RolesAndPermissions\PermissionNotFoundException
     */
    public function findPermissionInModule($module, $permission, $permission_type = null);
}
