<?php

namespace App\Services\Role;

use App\Enums\RolesAndPermissions\Contexts;
use App\Enums\RolesAndPermissions\PermissionType;
use App\Enums\RolesAndPermissions\RoleStatus;
use App\Enums\RolesAndPermissions\SystemRoles;
use App\Enums\User\EnrollmentSource;
use App\Enums\User\UserEntity;
use App\Exceptions\ApplicationException;
use App\Exceptions\RolesAndPermissions\ContextNotFoundException;
use App\Exceptions\RolesAndPermissions\RoleNotFoundException;
use App\Exceptions\User\UserEntityRelationNotFoundException;
use App\Exceptions\User\UserNotFoundException;
use App\Model\Role;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\RolesAndPermissions\UserRoleMappingNotFoundException;
use App\Model\RolesAndPermissions\Repository\IContextRepository;
use App\Model\RolesAndPermissions\Repository\IRoleRepository;
use App\Model\RolesAndPermissions\Repository\IPermissionRepository;
use App\Model\User\Repository\IUserRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RoleService implements IRoleService
{
    /**
     * @var \App\Model\RolesAndPermissions\Repository\IContextRepository $contextRepository
     */
    private $contextRepository;
    /**
     * @var IRoleRepository
     */
    private $roleRepository;

    /**
     *  @var IPermissionRepository
     */
    private $permissionRepository;

    /**
     * @var IUserRepository
     */
    private $userRepository;

    /**
     * RoleService constructor.
     *
     * @param IContextRepository $contextRepository
     * @param IRoleRepository $roleRepository
     * @param IPermissionRepository $permissionRepository
     * @param IUserRepository $userRepository
     */
    public function __construct(
        IContextRepository $contextRepository,
        IRoleRepository $roleRepository,
        IPermissionRepository $permissionRepository,
        IUserRepository $userRepository
    ) {
        $this->contextRepository = $contextRepository;
        $this->roleRepository = $roleRepository;
        $this->permissionRepository = $permissionRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @inheritDoc
     */
    public function getRoleDetails($role, $relations = [])
    {
        try {
            if (is_int($role)) {
                $attribute = 'rid';
            } else {
                $attribute = 'slug';
            }
            $role = $this->roleRepository->findByAttribute($attribute, $role);
            $data["id"] = $role->rid;
            $data["name"] = $role->name;
            $data["slug"] = $role->slug;
            $data["status"] = $role->stauts;
            $data["is_admin_role"] = $role->is_admin_role;
            //Check if permissions exist in relations if so include permissions.
            if (in_array('permissions', $relations)) {
                $admin_permissions = $role->adminPermissions()->get();
                $portal_permissions = $role->portalPermissions()->get();
                $data["admin_permissions"]= $this->formPermissionList("admin_permissions", $admin_permissions);
                $data["portal_permissions"] = $this->formPermissionList("portal_permissions", $portal_permissions);
            }

            //Check if contexts exist in relations if so include context information.
            if (in_array('context', $relations)) {
                $data['contexts'] = [];
                foreach ($role->contexts as $each) {
                    $context = array('id'=> $each->id, 'name'=>$each->name,'slug'=>$each->slug );
                    $data['contexts'][$each['slug']] = $context;
                }
            }

        } catch (RoleNotFoundException $e) {
            $data = [];
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getContexts($include_roles = false)
    {
        $data = [];
        $context_collection = $this->contextRepository->get($include_roles);

        $context_collection->each(
            function ($context) use (&$data, $include_roles) {
                $data[$context->slug] = [
                    "id" => $context->id,
                    "name" => $context->name,
                    "slug" => $context->slug,
                ];

                //If $include_roles flag is set to true include roles meta data along with context data.
                if ($include_roles) {
                    $context->roles()->each(
                        function ($role) use (&$data, $context) {
                            $data[$context->slug]["roles"][$role->slug] = [
                                "id" => $role->rid,
                                "name" => $role->name,
                                "slug" => $role->slug,
                            ];
                        }
                    );
                }
            }
        );

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getInheritableRoles($include_contexts = false)
    {
        $roles_collection = $this->roleRepository->getInheritableRoles($include_contexts);

        return $this->transformRoleData($roles_collection, $include_contexts);
    }

    /**
     * @inheritDoc
     */
    public function getContextDetails($context_slug, $include_roles = false)
    {
        try {
            $context = $this->contextRepository->findByAttribute("slug", $context_slug, $include_roles);
            $data["id"] = $context->id;
            $data["name"] = $context->name;
            $data["slug"] = $context->slug;
            if ($include_roles) {
                $context->roles()
                    ->where("status", RoleStatus::ACTIVE)
                    ->whereNotIn("slug", [SystemRoles::SUPER_ADMIN])
                    ->each(
                        function ($role) use (&$data) {
                            $data["roles"][$role->slug] = [
                                "id" => $role->rid,
                                "slug" => $role->slug,
                                "name" => $role->name,
                            ];
                        }
                    );
            }
        } catch (ContextNotFoundException $e) {
            $data = [];
        }

        return $data;
    }


    /**
     * Get formatted role data
     * @param Collection $roles_collection
     * @param bool $include_contexts
     * @return array
     */
    private function transformRoleData($roles_collection, $include_contexts = false)
    {
        $data = [];

        $roles_collection->each(
            function ($role) use (&$data, $include_contexts) {
                $data[$role->slug] = [
                    "id" => $role->rid,
                    "name" => $role->name,
                    "slug" => $role->slug,
                ];

                //If $include_contexts is true then retrieve role with it's allowed contexts else just retrieve
                //role meta data.
                $role->contexts()->each(
                    function ($context) use (&$data, $role) {
                        $data[$role->slug]["contexts"][$context->slug] = [
                            "id" => $context->id,
                            "name" => $context->name,
                            "slug" => $context->slug,
                        ];
                    }
                );
            }
        );

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function mapUserAndRole($user_id, $context_id, $role_id, $instance_id = null)
    {
        try {
            $user_role_mapping = $this->roleRepository->findUserRoleMapping($user_id, $context_id, $instance_id);
            $new_role = $this->roleRepository->find($role_id);
            if ($new_role->slug !== SystemRoles::LEARNER) {
                $user_role_mapping->role_id = (int) $role_id;
                $user_role_mapping->save();
            }
        } catch (UserRoleMappingNotFoundException $e) {
            return $this->roleRepository->mapUserAndRole($user_id, $context_id, $role_id, $instance_id);
        }
    }

    /**
     * @inheritDoc
     */
    public function updateMapUserAndRole($user_id, $context_id, $role_id, $instance_id)
    {
        return $this->roleRepository->updateUserAndRoleMapping($user_id, $context_id, $role_id, $instance_id);
    }

    /**
     * @inheritDoc
     */
    public function unmapUserAndRole($user_id, $context_id = null, $instance_id = null)
    {
        try {
            if (!is_null($context_id)) {
                $context = $this->contextRepository->find($context_id);
                try {
                    switch ($context->slug) {
                        case Contexts::PROGRAM:
                            $this->userRepository->findActiveUserEntityRelation(
                                $user_id,
                                UserEntity::PROGRAM,
                                $instance_id
                            );

                            $direct_active_enrollments = $this->userRepository->getActiveUserEntityRelations(
                                [
                                    "user_id" => $user_id, "entity_type" => UserEntity::PROGRAM,
                                    "entity_id" => $instance_id, "source_type" => EnrollmentSource::DIRECT_ENROLLMENT
                                ]
                            );

                            if ($direct_active_enrollments->isEmpty()) {
                                $learner_role = $this->roleRepository->findByAttribute("slug", SystemRoles::LEARNER);
                                $this->roleRepository->updateUserAndRoleMapping(
                                    $user_id,
                                    $context_id,
                                    $learner_role->rid,
                                    $instance_id
                                );
                            }
                            break;
                    }
                } catch (UserEntityRelationNotFoundException $e) {
                    return $this->roleRepository->unmapUserAndRole($user_id, $context_id, $instance_id);
                }
            } else {
                return $this->roleRepository->unmapUserAndRole($user_id);
            }
        } catch (\Exception $e) {
            if ($e instanceof ModelNotFoundException) {
                throw new UserNotFoundException();
            } else {
                Log::error($e->getMessage());
            }
        }
    }

     /**
     * Forming permission list using get role details data
     * @param string $permission_slug
     * @param collection $response
     * @return array
     */

    private function formPermissionList($permission_slug, $response)
    {
        $data[$permission_slug] = [];
        foreach ($response as $key => $value) {
            $slug =  $value->module->slug;
            if (array_key_exists($slug, $data[$permission_slug])) {
                $data[$permission_slug][$slug]['module'] = $value->module->name;
                $data[$permission_slug][$slug]['slug'] = $value->module->slug;
                $action = ['name'=>$value->name,'slug'=>$value->slug,'is_default'=>$value->is_default];
                array_push($data[$permission_slug][$slug]['action'], $action);
            } else {
                $data[$permission_slug][$slug] = [];
                $data[$permission_slug][$slug]['module'] = $value->module->name;
                $data[$permission_slug][$slug]['slug'] = $value->module->slug;
                $action = ['name'=>$value->name,'slug'=>$value->slug,'is_default'=>$value->is_default];
                $data[$permission_slug][$slug]['action'][] = $action;
            }
        }
        return $data[$permission_slug];
    }

    /**
     * @inheritDoc
     */
    public function mapRoleAndPermissions($role_id, $permission_ids)
    {
        return $this->roleRepository->mapRoleAndPermissions($role_id, $permission_ids);
    }

    /**
     * @inheritDoc
     */
    public function syncRoleAndPermissions($role_id, $permission_ids)
    {
        return $this->roleRepository->syncRoleAndPermissions($role_id, $permission_ids);
    }

    /**
     * @inheritDoc
     */
    public function createUserPermissionsListCacheFile($user_id)
    {
        $user_role_mappings = $this->roleRepository->getUserRoleMappings(["user_id" => $user_id]);
        $modules = [
            "admin" => [],
            "portal" => []
        ];

        $permissions = [];

        $user_role_mappings->each(
            function ($user_role_mapping) use (&$modules, &$permissions) {
                try {
                    $role = $this->roleRepository->find($user_role_mapping->role_id);
                    $permission_collection = $role->permissions()->get();

                    //When user role mapping instance id is set to null or empty, then the role is assigned in system
                    //context. There can be only one role assigned in system context.
                    //We will define all the system level permissions(user will have that permission across
                    //different contexts) and we will define overrides if the permission is overridden in any other
                    //contexts.

                    //Permission key($permission_identifier) is prepended with module slug the permission belongs to and
                    // it's type to make sure that it is not duplicated
                    if (is_null($user_role_mapping->instance_id)) {
                        try {
                            $system_context = $this->contextRepository->find($user_role_mapping->context_id);
                            if ($system_context->slug === Contexts::SYSTEM) {
                                $permission_collection->each(
                                    function ($permission) use (&$modules, &$permissions) {
                                        $this->addModuleToConsolidatedUserPermissionList($permission, $modules);
                                        $permission_key = $this->generatePermissionKey(
                                            $permission->module->slug,
                                            $permission->type,
                                            $permission->slug
                                        );
                                        $permissions[$permission_key]["system_level_access"] = true;
                                    }
                                );
                            }
                        } catch (ContextNotFoundException $e) {
                            Log::error($e->getMessage());
                        }
                    } else {
                        $permission_collection->each(
                            function ($permission) use ($user_role_mapping, &$modules, &$permissions) {
                                try {
                                    $permission_key = $this->generatePermissionKey(
                                        $permission->module->slug,
                                        $permission->type,
                                        $permission->slug
                                    );

                                    $context = $this->contextRepository->find($user_role_mapping->context_id);
                                    $this->addModuleToConsolidatedUserPermissionList($permission, $modules);

                                    //Check the given permission is already exists in permissions list when user is
                                    //mapped to multiple roles in different contexts.
                                    if (array_key_exists($permission_key, $permissions)) {

                                        //Check if user has the given permission in system context and
                                        //it's is overridden in any other context if not override the permission in the
                                        //given context.
                                        if (array_key_exists(
                                            "context_overrides",
                                            $permissions[$permission_key]
                                        )) {
                                            if (array_key_exists(
                                                $context->slug,
                                                $permissions[$permission_key]["context_overrides"]
                                            )) {
                                                array_push(
                                                    $permissions[$permission_key]["context_overrides"]
                                                    [$context->slug],
                                                    $user_role_mapping->instance_id
                                                );
                                            } else {
                                                $permissions[$permission_key]["context_overrides"][$context->slug] =
                                                    [$user_role_mapping->instance_id];
                                            }
                                        } else {
                                            $permissions[$permission_key]["context_overrides"][$context->slug] =
                                                [$user_role_mapping->instance_id];
                                        }
                                    } else {
                                        $permissions[$permission_key] = [
                                            "system_level_access" => false,
                                            "context_overrides" => [
                                                $context->slug => [$user_role_mapping->instance_id]
                                            ]
                                        ];
                                    }
                                } catch (ContextNotFoundException $e) {
                                    Log::error($e->getMessage());
                                }
                            }
                        );
                    }
                } catch (RoleNotFoundException $e) {
                    Log::error($e->getMessage());
                }
            }
        );

        Cache::forever(
            $user_id,
            json_encode(
                [
                    "user_id" => $user_id,
                    "modules" => $modules,
                    "permissions" => $permissions
                ]
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function deleteUserPermissionsListCacheFile($user_id)
    {
        return Cache::forget($user_id);
    }

    /**
     *  Generate consolidated list of modules that user has access to
     * @param \App\Model\RolesAndPermissions\Entity\Permission $permission
     * @param array $modules
     */
    private function addModuleToConsolidatedUserPermissionList($permission, &$modules)
    {
        if ($permission->type === PermissionType::ADMIN) {
            if (!in_array($permission->module->slug, $modules["admin"])) {
                array_push($modules["admin"], $permission->module->slug);
            }
        } elseif ($permission->type === PermissionType::PORTAL) {
            if (!in_array($permission->module->slug, $modules["portal"])) {
                array_push($modules["portal"], $permission->module->slug);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function hasPermission(
        $user_id,
        $module_slug,
        $permission_type,
        $permission_slug,
        $context_slug = null,
        $instance_id = null,
        $include_permission_data = false
    ) {
        //For the given user id check if given permission is available in consolidated permission list if so return true
        //else return false

        //If $include_permission_data is set to true return permission data along with $has_permission flag else just
        //return $has_permission flag

        $has_permission = false;
        $data = [];
        $permissions_data = $this->getUserPermissionDataFromCache($user_id);
        if (!empty($permissions_data)) {
            $permission_key = $this->generatePermissionKey($module_slug, $permission_type, $permission_slug);
            if (!empty($permissions_data["permissions"][$permission_key])) {
                $permission_data = $permissions_data["permissions"][$permission_key];
                $has_permission = array_get(
                    $permission_data,
                    "system_level_access"
                );

                if (!$has_permission && array_has($permission_data, "context_overrides")) {
                    $context_overrides = $permission_data["context_overrides"];

                    if (!is_null($instance_id) && is_string($context_slug)) {
                        $has_permission = array_has($context_overrides, $context_slug) &&
                        in_array($instance_id, $context_overrides[$context_slug])?
                            !$has_permission : $has_permission;
                    } else {
                        if (is_array($context_slug)) {
                            $has_permission = !empty(
                                array_collapse(array_only($context_overrides, $context_slug))
                            );
                        } else {
                            $has_permission = !empty(array_collapse($context_overrides));
                        }
                    }
                }

                if ($include_permission_data && $has_permission) {
                    $data["has_permission"] = $has_permission;
                    $data["permission"] = $permission_data;
                }
            }
        }

        return $include_permission_data? $data : $has_permission;
    }

    /**
     * Generate permission key to store or retrieve permission data in cache
     * @param $module_slug
     * @param $permission_type
     * @param $permission_slug
     *
     * @return string
     */
    private function generatePermissionKey($module_slug, $permission_type, $permission_slug)
    {
        return "{$module_slug}_{$permission_type}_{$permission_slug}";
    }

    /**
     * Get decoded user permissions from cache
     * @param int $user_id
     *
     * @return array
     */
    private function getUserPermissionDataFromCache($user_id)
    {
        $permission_data = [];
        $json_encoded_permission_data = Cache::get($user_id);
        if (!is_null($json_encoded_permission_data)) {
            $permission_data = json_decode($json_encoded_permission_data, true);
        }

        return $permission_data;
    }

    /**
     * @inheritDoc
     */
    public function getUserPermissions($user_id, $permission_type, $module = null)
    {
        $permissions = [];
        $decoded_permission_data = $this->getUserPermissionDataFromCache($user_id);
        if (!empty($decoded_permission_data)) {
            $accessible_modules = $decoded_permission_data["modules"][$permission_type];
            if (is_null($module) || is_array($module)) {
                $accessible_modules = is_null($module)? $accessible_modules :
                    array_intersect($accessible_modules, $module);
                if (!empty($accessible_modules)) {
                    foreach ($accessible_modules as $filtered_module) {
                        $permissions[$filtered_module] = $this->getUserPermissionsByModule(
                            $decoded_permission_data["permissions"],
                            $filtered_module,
                            $permission_type
                        );
                    }
                }
            } elseif (is_string($module) && in_array($module, $accessible_modules)) {
                $permissions = $this->getUserPermissionsByModule(
                    $decoded_permission_data["permissions"],
                    $module,
                    $permission_type
                );
            }
        }

        return $permissions;
    }

    /**
     * Get user accessible permission slugs
     * @param array $permissions decoded json data from cache
     * @param string $module module slug
     * @param string $permission_type
     *
     * @return array
     */
    private function getUserPermissionsByModule($permissions, $module, $permission_type)
    {
        $key_substring = "{$module}_{$permission_type}_";
        $module_permissions = collect($permissions)->filter(
            function ($module_permission, $key) use ($key_substring) {
                $has_permission = false;
                if (starts_with($key, $key_substring)) {
                    $has_permission = $module_permission["system_level_access"];
                    if (!$has_permission) {
                        if (!empty($module_permission["context_overrides"])) {
                            $has_permission = !empty(array_flatten($module_permission["context_overrides"]));
                        }
                    }
                }

                return $has_permission;
            }
        );

        $permission_slugs = $module_permissions->keys()->map(
            function ($permission_key) use ($key_substring) {
                return substr($permission_key, strlen($key_substring));
            }
        );

        return $permission_slugs->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getUserAccessibleModules($user_id, $module_type = null)
    {
        $permission_data = $this->getUserPermissionDataFromCache($user_id);
        return !empty($permission_data)? (!is_null($module_type)?
            $permission_data["modules"][$module_type] : $permission_data["modules"]) : [];
    }

    /**
     * @inheritDoc
     */
    public function isRoleAssignedToUser($role_id)
    {
        $data = $this->roleRepository->isRoleAssignedToUser($role_id);
        if ($data <= 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @inheritDoc
     */
    public function updateUserSystemContextRole($user_id, $role)
    {
        try {
            $system_context = $this->contextRepository->findByAttribute("slug", Contexts::SYSTEM);

            if (!$role instanceof Role) {
                $role = $this->roleRepository->find($role);
            }

            $user_system_role_mapping = $this->roleRepository->updateUserAndRoleMapping(
                $user_id,
                $system_context->id,
                $role->rid
            );

            return $user_system_role_mapping;

        } catch (ApplicationException $e) {
            Log::error($e->getTraceAsString());
        }
    }

    /**
     * @inheritDoc
     */
    public function getUserSystemContextRole($user_id)
    {
        $system_context = $this->contextRepository->findByAttribute("slug", Contexts::SYSTEM);
        $user_role_mapping = $this->roleRepository->findUserRoleMapping($user_id, $system_context->id);

        return $this->roleRepository->find($user_role_mapping->role_id);
    }

    /**
     * @inheritDoc
     */
    public function getContextRoleEnrolement($filter_params = [])
    {
        return $this->roleRepository->getUserRoleMappings($filter_params);
    }
}
