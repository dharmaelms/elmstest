<?php

use App\Enums\RolesAndPermissions\PermissionType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\Schema as Schema;
use App\Model\Role;
use App\Model\RolesAndPermissions\Entity\Context;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Model\Module\Entity\Module;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Lang;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        // Collection name
        $collection = 'roles';

        // Removing existing collection
        Schema::drop($collection);

        // Creating a collection schema with required index fields
        Schema::create(
            $collection,
            function ($collection) {
                $collection->unique('rid');
            }
        );

        //Get "system" context type id
        $system_context = Context::where("slug", "system")->first();

        //Get "program" context type id
        $program_context = Context::where("slug", "program")->first();

        //Get "program" context type id
        $course_context = Context::where("slug", "course")->first();
 
        //Get "program" context type id
        $batch_context = Context::where("slug", "batch")->first();

        // Inserting super admin role
        DB::collection($collection)->insert(
            [
                'rid' => Role::getNextRoleId(),
                'name' => 'Super Administrator',
                'slug' => 'super_admin',
                'is_admin_role' => true,
                'parent' => null,
                'description' => '',
                'system_role' => true,
                'status' => 'ACTIVE',
                'created_by' => '',
                'created_at' => time(),
                'updated_by' => '',
                'updated_at' => time()
            ]
        );

        // Inserting site admin role
        $predefined_roles["site_admin"] = DB::collection($collection)->insert(
            [
                'rid' => Role::getNextRoleId(),
                'name' => 'Site Administrator',
                'slug' => 'site_admin',
                'is_admin_role' => true,
                'parent' => null,
                'description' => '',
                'system_role' => true,
                'status' => 'ACTIVE',
                'created_by' => '',
                'created_at' => time(),
                'updated_by' => '',
                'updated_at' => time()
            ]
        );

        // Inserting Content Author role
        $predefined_roles["content-author"] = DB::collection($collection)->insert(
            [
                'rid' => Role::getNextRoleId(),
                'name' => 'Content Author',
                'slug' => 'content-author',
                'is_admin_role' => false,
                'parent' => null,
                'description' => 'Author can create new content and post to a channel. Have permissions to edit.',
                'system_role' => true,
                'status' => 'ACTIVE',
                'created_by' => '',
                'created_at' => time(),
                'updated_by' => '',
                'updated_at' => time()
            ]
        );

        // Inserting Channel Admin role
        $predefined_roles["channel-admin"] = DB::collection($collection)->insert(
            [
                'rid' => Role::getNextRoleId(),
                'name' => 'Program Admin',
                'slug' => 'channel-admin',
                'is_admin_role' => false,
                'parent' => null,
                'description' => 'Program admin can manage the channels that are assigned to him',
                'system_role' => true,
                'status' => 'ACTIVE',
                'created_by' => '',
                'created_at' => time(),
                'updated_by' => '',
                'updated_at' => time()
            ]
        );

        // Inserting learner role
        $predefined_roles["learner"] = DB::collection($collection)->insert(
            [
                'rid' => Role::getNextRoleId(),
                'name' => 'Learner',
                'slug' => 'learner',
                'is_admin_role' => false,
                'parent' => null,
                'description' => '',
                'system_role' => true,
                'status' => 'ACTIVE',
                'created_by' => '',
                'created_at' => time(),
                'updated_by' => '',
                'updated_at' => time()
            ]
        );

        $predefined_roles["registered-user"] = DB::collection($collection)->insert(
            [
                'rid' => Role::getNextRoleId(),
                'name' => 'Registered user',
                'slug' => 'registered-user',
                'is_admin_role' => false,
                'parent' => null,
                'description' => '',
                'system_role' => true,
                'status' => 'ACTIVE',
                'created_by' => '',
                'created_at' => time(),
                'updated_by' => '',
                'updated_at' => time()
            ]
        );

        //  Define role slugs
        $predefined_role_slugs = [
            "super_admin", "site_admin", "registered-user", "learner", "content-author", "channel-admin", "visitor",
        ];

        //  Map role and contexts the role belongs to
        $role_context_mappings = [
            "super_admin" => [$system_context],
            "site_admin" => [$system_context],
            "content-author" => [$program_context],
            "channel-admin" => [$program_context],
            "learner" => [$program_context, $batch_context],
            "registered-user" => [$system_context],
        ];

        //  Map role and module wise permissions
        $role_permissions_mappings = include_once database_path("includes/roles_and_permissions_mapping_data.php");

        //  Map each role with context types and its permissions
        collect($predefined_role_slugs)
            ->each(
                function ($role_slug) use ($role_context_mappings, $role_permissions_mappings) {
                    try {
                        $role = Role::where("slug", $role_slug)->firstOrFail();

                        //Map role with context types
                        $role->contexts()->saveMany($role_context_mappings[$role_slug]);

                        if (isset($role_permissions_mappings[$role_slug])) {
                            //Map role with it's permissions
                            $module_permission_mappings = $role_permissions_mappings[$role_slug];
                            $admin_permissions = [];
                            if (isset($module_permission_mappings["admin_permissions"])) {
                                $admin_permissions = $module_permission_mappings["admin_permissions"];
                                $this->mapRoleAndPermission($role, PermissionType::ADMIN, $admin_permissions);
                            }
                        }
                    } catch (ModelNotFoundException $e) {
                        //  If no role found for the given slug log and continue mapping for other roles.
                        Log::error($e->getMessage());
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
}
