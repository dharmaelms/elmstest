<?php

use App\Enums\Module\Module as ModuleEnum;
use App\Enums\RolesAndPermissions\PermissionType;
use App\Enums\Survey\SurveyPermission;
use App\Model\Module\Entity\Module;
use App\Model\Role;
use App\Model\RolesAndPermissions\Entity\Permission;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Migrations\Migration;

class AddSurveyPermission extends Migration
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
            $module = Module::where("slug", ModuleEnum::SURVEY)->get();
            if ($module->isEmpty()) {
                DB::collection($collection_module)->insert(
                    ["id" => Module::getNextSequence(), "name" => "Survey", "slug" => ModuleEnum::SURVEY]
                );
                $module_permissions_mapping = [
                    ModuleEnum::SURVEY => [
                        PermissionType::ADMIN => [
                            [
                                "name"=>"List Survey",
                                "slug"=> SurveyPermission::LIST_SURVEY,
                                "description"=>"User with list survey permission will be able to view list of survey that are created by him or related programs he is assigned to.",
                                "is_default" => true,
                            ],
                            [
                                "name"=>"Add Survey",
                                "slug"=> SurveyPermission::ADD_SURVEY,
                                "description"=>"User with add survey permission will have the ability to create new survey",
                                "is_default" => false,
                            ],
                            [
                                "name"=>"Edit Survey",
                                "slug"=> SurveyPermission::EDIT_SURVEY,
                                "description"=>"User with edit survey permissons will have the ability to edit all the survey created by him or related programs he is assigned to",
                                "is_default" => false,
                            ],
                            [
                                "name"=>"Delete Survey",
                                "slug"=> SurveyPermission::DELETE_SURVEY,
                                "description"=>"User with delete survey permissons will have the ability to delete all the survey created by him or related programs he is assigned to",
                                "is_default" => false,
                            ],
                            [
                                "name"=>"Export Survey",
                                "slug"=> SurveyPermission::EXPORT_SURVEY,
                                "description"=>"Export survey permissions can Export all the survey",
                                "is_default"=> true
                            ],
                            [
                                "name"=>"Report Survey",
                                "slug"=> SurveyPermission::REPORT_SURVEY,
                                "description"=>"Report survey permissions can Export all the survey",
                                "is_default"=> true
                            ],
                            [
                                "name"=>"Assign Survey To User",
                                "slug"=> SurveyPermission::SURVEY_ASSIGN_USER,
                                "description"=>"User with assign survey to user permissons will have the ability to assign survey to user that survey are created by him or related programs he is assigned to",
                                "is_default" => false,
                            ],
                            [
                                "name"=>"Assign Survey To User Group",
                                "slug"=> SurveyPermission::SURVEY_ASSIGN_USER_GROUP,
                                "description"=>"User with assign survey to usergroup permissons will have the ability to assign survey to usergroup that survey are created by him or related programs he is
                                                    assigned to",
                                "is_default" => false,
                            ],
                            [
                                "name"=>"List Survey Question",
                                "slug"=> SurveyPermission::LIST_SURVEY_QUESTION,
                                "description"=>"List Survey Question permissions can list all the survey questions",
                                "is_default" => true,
                            ],
                            [
                                "name"=>"Add Survey Question",
                                "slug"=> SurveyPermission::ADD_SURVEY_QUESTION,
                                "description"=>"Add Survey Question permissions can Add the new survey question",
                                "is_default" => false,
                            ],
                            [
                                "name"=>"Edit Survey Question",
                                "slug"=> SurveyPermission::EDIT_SURVEY_QUESTION,
                                "description"=>"Edit Survey Question permissions can edit the survey question",
                                "is_default" => false,
                            ],
                            [
                                "name"=>"Delete Survey Question",
                                "slug"=> SurveyPermission::DELETE_SURVEY_QUESTION,
                                "description"=>"Delete Survey Question permissions can delete the survey question",
                                "is_default" => false,
                            ],
                        ],
                    ]
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
                Log::info(" Survey permission are exists");
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
