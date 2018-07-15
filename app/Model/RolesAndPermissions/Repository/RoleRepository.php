<?php

namespace App\Model\RolesAndPermissions\Repository;

use App\Exceptions\RolesAndPermissions\PermissionNotFoundException;
use App\Exceptions\RolesAndPermissions\RoleNotFoundException;
use App\Exceptions\RolesAndPermissions\UserRoleMappingNotFoundException;
use App\Model\Role;
use App\Enums\RolesAndPermissions\RoleStatus;
use App\Enums\RolesAndPermissions\SystemRoles;
use App\Model\RolesAndPermissions\Entity\UserRoleAssignment;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RoleRepository implements IRoleRepository
{
    /**
     * @inheritDoc
     */
    public function find($id)
    {
        try {
            return Role::where("_id", "!=", "rid")
                    ->where("rid", (int) $id)
                    ->where("status", "!=", RoleStatus::DELETED)
                    ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new RoleNotFoundException();
        }
    }

    /**
     * @inheritDoc
     */
    public function findByAttribute($attribute, $value)
    {
        try {
            return Role::where($attribute, $value)
                        ->where("status", "!=", RoleStatus::DELETED)
                        ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new RoleNotFoundException();
        }
    }

    /**
     * @inheritDoc
     */
    public function isRoleAssignedToUser($role_id)
    {
        return UserRoleAssignment::filter(['role_id' => (int)$role_id])->count();
    }

    /**
     * @inheritDoc
     */
    public function getInheritableRoles($include_contexts = false)
    {
        //Roles defined in $roles_to_be_excluded cannot be inherited by custom roles.
        $roles_to_be_excluded = [SystemRoles::SUPER_ADMIN, SystemRoles::LEARNER, SystemRoles::REGISTERED_USER];

        //If $include_contexts is true retrieve contexts the roles are associated with else just retrieve roles metadata
        return Role::whereNotIn("slug", $roles_to_be_excluded)
            ->where("status", RoleStatus::ACTIVE)
            ->when(
                $include_contexts,
                function ($query) {
                    return $query->with("contexts");
                }
            )->get();
    }

    /**
     * @inheritDoc
     */
    public function mapUserAndRole($user_id, $context_id, $role_id, $instance_id = null)
    {
        $user_role_assignment = new UserRoleAssignment();
        // $user_role_assignment->id = UserRoleAssignment::getNextSequence();
        $user_role_assignment->user_id = (int) $user_id;
        $user_role_assignment->context_id = (int) $context_id;
        $user_role_assignment->instance_id = is_numeric($instance_id)? (int) $instance_id : $instance_id;
        $user_role_assignment->role_id = (int) $role_id;

        $user_role_assignment->save();

        return $user_role_assignment;
    }

    public function updateUserAndRoleMapping($user_id, $context_id, $role_id, $instance_id = null)
    {
        try {
            $user_role_assignment =
                UserRoleAssignment::filter(
                    [
                     "user_id" => $user_id, "context_id" => $context_id,
                     "instance_id" => $instance_id
                    ]
                )->firstOrFail();

            $user_role_assignment->role_id = (int) $role_id;
            $user_role_assignment->save();
            
            return $user_role_assignment;
        } catch (ModelNotFoundException $e) {
            throw new UserRoleMappingNotFoundException();
        }
    }

    /**
     * @inheritDoc
     */
    public function findUserRoleMapping($user_id, $context_id, $instance_id = null)
    {
        try {
            return UserRoleAssignment::filter(
                [
                    "user_id" => $user_id, "context_id" => $context_id, "instance_id" => $instance_id,
                ]
            )->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new UserRoleMappingNotFoundException();
        }
    }

    /**
     * @inheritDoc
     */
    public function getUserRoleMappings($filter_params = [])
    {
        return UserRoleAssignment::filter($filter_params)->get();
    }

    /**
     * @inheritDoc
     */
    public function unmapUserAndRole($user_id, $context_id = null, $instance_id = null)
    {
        $filter_params = [
            "user_id" => $user_id, "context_id" => $context_id, "instance_id" => $instance_id,
        ];

        try {
            if (($context_id === null) && ($instance_id === null)) {
                array_forget($filter_params, "instance_id");
            }

            return UserRoleAssignment::filter($filter_params)->delete();
        } catch (ModelNotFoundException $e) {
            return true;
        }
    }

    /**
     * @inheritDoc
     */
    public function mapRoleAndPermissions($role_id, $permission_ids)
    {
        $role = $this->find($role_id);
        $role->permissions()->attach($permission_ids);
        return $role;
    }
    
    /**
     * @inheritDoc
     */
    public function syncRoleAndPermissions($role_id, $permission_ids)
    {
        $role = $this->find($role_id);
        $role->permissions()->sync($permission_ids);
        return $role;
    }

    /**
     * @inheritDoc
     */
    public function findPermissionInRole($role_slug, $module_slug, $permission_type, $permission_slug)
    {
        //If both the validations are success return permission object
        //else throw PermissionNotFoundException

        $permissions = $this->findByAttribute("slug", $role_slug)
            ->permissions()->type($permission_type)->slug($permission_slug)->get();

        //Validate whether permission is available in the given module
        // if so validate whether given role has that permission

        $filtered_permission_collection = $this->filterRolePermissionsByModule($module_slug, $permissions);

        if (!$filtered_permission_collection->isEmpty()) {
            $filtered_permission_collection->first();
        } else {
            throw new PermissionNotFoundException();
        }
    }

    /**
     * @inheritDoc
     */
    public function getRolePermissions($role_slug, $module = null, $permission_type = null)
    {
        $permissions = $this->findByAttribute("slug", $role_slug)
            ->permissions()->when(
                !is_null($permission_type),
                function ($query) use ($permission_type) {
                    return $query->type($permission_type);
                }
            )->get();

        if (!is_null($module)) {
            $permissions = $this->filterRolePermissionsByModule($module, $permissions);
        }

        return $permissions;
    }

    /**
     * @param string $module_slug
     * @param \Illuminate\Database\Eloquent\Collection $permissions
     * @return \Illuminate\Database\Eloquent\Collection or \Illuminate\Support\Collection
     */
    private function filterRolePermissionsByModule($module_slug, $permissions)
    {
        return $permissions->filter(
            function ($permission) use ($module_slug) {
                return $permission->module->slug === $module_slug;
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function unMapContextLevelUserRoleMappings($user_id, $context_ids = [])
    {
        UserRoleAssignment::filter(["user_id" => $user_id, "context_id" => $context_ids])
                            ->delete();
    }
}
