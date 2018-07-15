<?php

namespace App\Model\RolesAndPermissions\Repository;

interface IRoleRepository
{
    /**
     * Find role using unique id
     *
     * @var int $id
     *
     * @return \App\Model\Role
     *
     * @throws \App\Exceptions\RolesAndPermissions\RoleNotFoundException
     */
    public function find($id);

    /**
     * @param string $attribute
     * @param int|string|array|boolean $value
     * @return \App\Model\Role
     *
     * @throws \App\Exceptions\RolesAndPermissions\RoleNotFoundException
     */
    public function findByAttribute($attribute, $value);

    /**
     * Get roles which can be inherited.
     *
     * @param bool $include_contexts
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getInheritableRoles($include_contexts = false);

    /**
     * @param int $user_id
     * @param int $context_id
     * @param int $role_id
     * @param int $instance_id
     * @return \App\Model\RolesAndPermissions\Entity\UserRoleAssignment
     */
    public function mapUserAndRole($user_id, $context_id, $role_id, $instance_id = null);

    /**
     * @param int $user_id
     * @param int $context_id
     * @param int $role_id
     * @param int $instance_id
     * @return \App\Model\RolesAndPermissions\Entity\UserRoleAssignment
     */
    public function updateUserAndRoleMapping($user_id, $context_id, $role_id, $instance_id = null);
    
    /**
     * @param int $user_id
     * @param int $context_id
     * @param int $instance_id
     * @return \App\Model\RolesAndPermissions\Entity\UserRoleAssignment
     *
     * @throws \App\Exceptions\RolesAndPermissions\UserRoleMappingNotFoundException
     */
    public function findUserRoleMapping($user_id, $context_id, $instance_id = null);

    /**
     * @param array $filter_params
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserRoleMappings($filter_params = []);

    /**
     * @param int $user_id
     * @param int $context_id
     * @param int $instance_id
     * @return boolean
     */
    public function unmapUserAndRole($user_id, $context_id = null, $instance_id = null);

    /**
     * @param int $role_id
     * @param array $permission_ids
     *
     * @return \App\Model\Role
     * @throws \App\Exceptions\RolesAndPermissions\RoleNotFoundException
     */
    public function mapRoleAndPermissions($role_id, $permission_ids);

    /**
     * @param int $role_id
     * @param array $permission_ids
     *
     * @return \App\Model\Role
     * @throws \App\Exceptions\RolesAndPermissions\RoleNotFoundException
     */
    public function syncRoleAndPermissions($role_id, $permission_ids);

    /**
     * @param string $role_slug
     * @param string $module_slug
     * @param string $permission_type
     * @param string $permission_slug
     * @return \App\Model\RolesAndPermissions\Entity\Permission
     */
    public function findPermissionInRole($role_slug, $module_slug, $permission_type, $permission_slug);

    /**
     * Get available permissions for role
     * @param string $role_slug
     * @param string $module
     * @param string $permission_type
     * @return \Illuminate\Database\Eloquent\Collection
     *
     * @throws \App\Exceptions\RolesAndPermissions\RoleNotFoundException
     */
    public function getRolePermissions($role_slug, $module = null, $permission_type = null);

    /**
     * To check is role assigned to any user, in roles list to disable actions
     * @param int $role_id
     * @return array
     */
    public function isRoleAssignedToUser($role_id);

    /**
     * Unmap context level user role mappings.
     * Note:: This method should not be used to remove user system level role rather to remove user role assignments
     * in low level contexts like program.
     * @param int $user_id
     * @param array $context_ids
     * @return void
     */
    public function unMapContextLevelUserRoleMappings($user_id, $context_ids = []);
}
