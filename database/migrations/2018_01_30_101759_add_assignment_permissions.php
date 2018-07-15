<?php


use App\Enums\Module\Module as ModuleEnum;
use App\Enums\RolesAndPermissions\PermissionType;
use App\Enums\Assignment\AssignmentPermission;
use App\Model\Module\Entity\Module;
use App\Model\Role;
use App\Model\RolesAndPermissions\Entity\Permission;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Migrations\Migration;

class AddAssignmentPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $collection_module = "modules";
            $module = Module::where("slug", ModuleEnum::ASSIGNMENT)->get();
            if ($module->isEmpty()) {
                DB::collection($collection_module)->insert(
                    ["id" => Module::getNextSequence(), "name" => "Assignment", "slug" => ModuleEnum::ASSIGNMENT]
                );
                $module_permissions_mapping = [
                    ModuleEnum::ASSIGNMENT => [
                        PermissionType::ADMIN => [
                            [
                                "name"=>"List Assignment",
                                "slug"=> AssignmentPermission::LIST_ASSIGNMENT,
                                "description"=>"User with list assignment permission will be able to view list of assignment that are created by him or related programs he is assigned to.",
                                "is_default" => true,
                            ],
                            [
                                "name"=>"Add Assignment",
                                "slug"=> AssignmentPermission::ADD_ASSIGNMENT,
                                "description"=>"User with add assignment permission will have the ability to create new assignment",
                                "is_default" => false,
                            ],
                            [
                                "name"=>"Edit Assignment",
                                "slug"=> AssignmentPermission::EDIT_ASSIGNMENT,
                                "description"=>"User with edit assignment permissons will have the ability to edit all the assignment created by him or related programs he is assigned to",
                                "is_default" => false,
                            ],
                            [
                                "name"=>"Delete Assignment",
                                "slug"=> AssignmentPermission::DELETE_ASSIGNMENT,
                                "description"=>"User with delete assignment permissons will have the ability to delete all the assignment created by him or related programs he is assigned to",
                                "is_default" => false,
                            ],
                            [
                                "name"=>"Export Assignment",
                                "slug"=> AssignmentPermission::EXPORT_ASSIGNMENT,
                                "description"=>"Export assignment permissions can Export all the assignments",
                                "is_default"=> true
                            ],
                            [
                                "name"=>"Report Assignment",
                                "slug"=> AssignmentPermission::REPORT_ASSIGNMENT,
                                "description"=>"Report assignment permissions can Export all the assignment",
                                "is_default"=> true
                            ],
                            [
                                "name"=>"Assign Assignment To User",
                                "slug"=> AssignmentPermission::ASSIGNMENT_ASSIGN_USER,
                                "description"=>"User with assign assignment to user permissons will have the ability to assign assignment to user that assignment are created by him or related programs he is assigned to",
                                "is_default" => false,
                            ],
                            [
                                "name"=>"Assign Assignment User Group",
                                "slug"=> AssignmentPermission::ASSIGNMENT_ASSIGN_USER_GROUP,
                                "description"=>"User with assign assignment to usergroup permissons will have the ability to assign assignment to usergroup that assignment are created by him or related programs he is
                                        assigned to",
                                "is_default" => false,
                            ],
                        ],
                    ],
                ];
                collect($module_permissions_mapping)->each(
                    function ($permissions_and_type_mapping, $module_slug) {
                        try {
                            $module = Module::where("slug", $module_slug)->firstOrFail();
                            collect($permissions_and_type_mapping)->each(
                                function ($permissions, $type) use ($module) {
                                    foreach ($permissions as $permission) {
                                        $permission["id"] = Permission::getNextSequence();
                                        $permission["type"] = $type;
                                        $permission["module_id"] = $module->id;
                                        Log::info($permission);
                                        Permission::createPermission($permission);
                                    }
                                }
                            );
                        } catch (ModelNotFoundException $e) {
                            Log::error("Couldn't find module with the slug \"{$module_slug}\"");
                        } catch (Exception $e) {
                            Log::error("Error While create permissions". $e->getMessage());
                        }
                    }
                );
                $this->mapPermissionsForSystemDefinedRoles();
            } else {
                Log::info(" Assignment permission are exists");
            }
        } catch (Exception $e){
            Log::info("Error while read data from module :: ".$e->getMessage());
        }
    }

    private function mapPermissionsForSystemDefinedRoles()
    {
        $role_permissions_mappings = include_once database_path("includes/roles_and_permissions_mapping_data.php");
        Role::where("system_role", true)->get()->each(
            function ($role) use ($role_permissions_mappings) {
                if (isset($role_permissions_mappings[$role->slug])) {
                    //Map role with it's permissions
                    $module_wise_permission_mappings = $role_permissions_mappings[$role->slug];
                    $admin_permissions = [];
                    if (isset($module_wise_permission_mappings["admin_permissions"])) {
                        $admin_permissions = $module_wise_permission_mappings["admin_permissions"];
                        $this->mapRoleAndPermission($role, PermissionType::ADMIN, $admin_permissions);
                    }
                }
            }
        );
    }

    /**
     * @param \App\Model\Role $role
     * @param string $permission_type
     * @param array $permissions
     */
    private function mapRoleAndPermission($role, $permission_type, $permissions)
    {
        foreach ($permissions as $module_permission_set) {
            $module_slug = $module_permission_set["slug"];
            foreach ($module_permission_set["action"] as $permission) {
                try {
                    $permission = Module::where("slug", $module_slug)->firstOrFail()->permissions()
                        ->where("type", $permission_type)->where("slug", $permission["slug"])->firstOrFail();
                    $role->permissions()->save($permission);
                } catch (ModelNotFoundException $e) {
                    //  If the given permission is not found log and continue mapping for other permissions
                    Log::error(
                        str_replace(
                            [":permission", ":module"],
                            [$permission["slug"], $module_slug],
                            Lang::get("admin/role.permission_not_found")
                        )
                    );
                }
            }
        }
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
