<?php

namespace App\Services\Role;

interface IRoleService
{
    /**
     * @param string $role_slug
     * @param array $relations
     * @return array
     */
    public function getRoleDetails($role_slug, $relations = []);

    /**
     * Get all the contexts available in the system
     *
     * @param bool $include_roles
     *
     * @return array
     */
    public function getContexts($include_roles = false);


    /**
     * Get roles that can be inherited by custom roles.
     *
     * @param bool $include_contexts
     * @return array
     */
    public function getInheritableRoles($include_contexts = false);

    /**
     * Get roles that belong to specific context
     *
     * @param String $context_slug
     * @param bool $include_roles
     * @return array
     */
    public function getContextDetails($context_slug, $include_roles = false);

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
     * @param int $instance_id
     * @return boolean
     */
    public function unmapUserAndRole($user_id, $context_id = null, $instance_id = null);

    /**
     * @param int $user_id
     * @param int $context_id
     * @param int $role_id
     * @param int $instance_id
     * @return \App\Model\RolesAndPermissions\Entity\UserRoleAssignment
     */
    public function updateMapUserAndRole($user_id, $context_id, $role_id, $instance_id);

    /**
     * @param int $role_id
     * @param array $permission_ids
     * @return \App\Model\Role
     * 
     * @throws \App\Exceptions\RolesAndPermissions\RoleNotFoundException
     */
    public function mapRoleAndPermissions($role_id, $permission_ids);

    /**
     * @param int $role_id
     * @param array $permission_ids
     * @return \App\Model\Role
     * 
     * @throws \App\Exceptions\RolesAndPermissions\RoleNotFoundException
     */
    public function syncRoleAndPermissions($role_id, $permission_ids);

    /**
     * Generate consolidated list of permissions for user and store it in cache
     * @param int $user_id
     */
    public function createUserPermissionsListCacheFile($user_id);

    /**
     * Delete consolidated permission list of user from cache
     * @param int $user_id
     * @return boolean
     */
    public function deleteUserPermissionsListCacheFile($user_id);

    /**
     * @param int $user_id
     * @param string $module_slug
     * @param string $permission_type
     * @param string $permission_slug
     * @param string|array $context_slug
     * @param int $instance_id
     * @param bool $include_permission_data when $include permission data is true method returns array containing flag to check whether user has permission and permission data which will have
     *system level access flag and context overrides array
     * @return array|bool
     */
    public function hasPermission(
        $user_id,
        $module_slug,
        $permission_type,
        $permission_slug,
        $context_slug = null,
        $instance_id = null,
        $include_permission_data = false
    );

    /**
     * Get available permissions for user. Filter parameters can be passed to filter permissions.
     * Supported filter params: user_id, context_type, instance_id, module, permission_type
     *
     * @param int $user_id
     * @param string $permission_type
     * @param string $module
     * @return array
     */
    public function getUserPermissions($user_id, $permission_type, $module = null);

    /**
     * Get list of admin and portal modules which can be accessed by user based on the roles assigned.
     * @param int $user_id
     * @param string $module_type
     * @return array
     */
    public function getUserAccessibleModules($user_id, $module_type = null);

    /**
     * To check is role assigned to any user, in roles list to disable actions
     * @param int $role_id
     * @return bool 
     */

    public function isRoleAssignedToUser($role_id);


    /**
     * Get user role that is assigned in system context
     * @param int $user_id
     * @return \App\Model\Role
     *
     * @throws \App\Exceptions\RolesAndPermissions\UserRoleMappingNotFoundException or
     *          \App\Exceptions\RolesAndPermissions\ContextNotFoundException
     *          \App\Exceptions\RolesAndPermissions\RoleNotFoundException
     */
    public function getUserSystemContextRole($user_id);

    /**
     * This method should be used when user is assigned new role in system context
     * @param int $user_id
     * @param int|\App\Model\Role $role new system context role that needs to be assigned to user
     * @return \App\Model\RolesAndPermissions\Entity\UserRoleAssignment
     */
    public function updateUserSystemContextRole($user_id, $role);

    /**
     * Get the user role assigmnet using filter params
     * @param array $filter_params
     * @return \App\Model\RolesAndPermissions\Entity\UserRoleAssignment
     */
    public function getContextRoleEnrolement($filter_params = []);
}
