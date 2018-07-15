<?php

namespace App\Model\RolesAndPermissions\Repository;

interface IPermissionRepository
{

    /**
     * Find permission using unique id
     *
     * @var int $id
     *
     * @return \App\Model\RolesAndPermissions\Entity\Permission
     *
     * @throws \App\Exceptions\RolesAndPermissions\RoleNotFoundException
     */
    public function find($id);

     /**
     * @param string $attribute
     * @param int|string|array|boolean $value
     * @return \App\Model\RolesAndPermissions\Entity\Permission
     *
     * @throws \App\Exceptions\RolesAndPermissions\PermissionNotFoundException
     */
    public function findByAttribute($attribute, $value);
}
