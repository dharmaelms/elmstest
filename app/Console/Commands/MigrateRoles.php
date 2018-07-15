<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Enums\RolesAndPermissions\PermissionType;
use App\Enums\RolesAndPermissions\RoleStatus;
use App\Enums\RolesAndPermissions\SystemRoles;
use App\Model\Module\Entity\Module;
use App\Model\Role;
use App\Model\RolesAndPermissions\Entity\Context;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MigrateRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrating roles';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    /**
     * @var MongoDB
     */
    private $mongodb;

    public function __construct()
    {
        $this->mongodb = DB::getMongoDB();
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {

            $this->runModuleSeeder();

            $this->runPermissionSeeder();
            $this->runContextSeeder();
            $this->renameExistingRolesTable();
            $this->createNewRolesTable();
            $this->updateChannelAdminAndContentAuthorRoles();
            $this->insertSystemRolesMetaData();
            $this->insertInheritedRolesMetaData();
            $this->updateDeletedRoleSlugs();
            $this->updateSystemDefinedRolesDuplicateSlugs();
            $this->updateSiteAdminRoles();
            $this->insertRegisteredUserRole();
            $this->mapRoleWithContexts();
            $this->mapPermissionsForSystemDefinedRoles();
            $this->mapPermissionsForInheritedRoles();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }


    private function runContextSeeder()
    {
        return Artisan::call("db:seed", ["--class"=>"ContextTypeSeeder", "--force" => true]);
    }

    private function runModuleSeeder()
    {
        return Artisan::call("db:seed", ["--class"=>"ModulesSeeder", "--force" => true]);
    }

    private function runPermissionSeeder()
    {
        return Artisan::call("db:seed", ["--class"=>"PermissionSeeder", "--force" => true]);
    }

    private function renameExistingRolesTable()
    {
        $this->mongodb->execute("db.roles.renameCollection(\"roles_backup\", true)");
    }

    private function createNewRolesTable()
    {
        Schema::create(
            "roles",
            function ($collection) {
                $collection->unique('rid');
                $collection->index("slug");
            }
        );
    }

    private function updateChannelAdminAndContentAuthorRoles()
    {
        $this->mongodb->selectCollection("roles_backup")->update(
            ["rid" => ["\$in" => [5, 6]]],
            ["\$set" => ["system_role" => true]],
            ["multiple" => true]
        );
    }

    private function insertSystemRolesMetaData()
    {
        $system_defined_roles = $this->getSystemDefinedRoles($this->getExistingSystemDefinedRoleSlugs());
        foreach ($system_defined_roles as $system_defined_role) {
            $role_data = array_only($system_defined_role, $this->getRoleMetaDataFields());

            array_set($role_data, "parent", null);

            try {
                $this->mongodb->selectCollection("roles")->insert($role_data);
            } catch (MongoException $e) {
                throw new \Exception($this->getRoleInsertionErrorMessage($role_data));
            }
        }
    }

    private function insertInheritedRolesMetaData()
    {
        $inherited_roles = $this->getInheritedRoles();
        foreach ($inherited_roles as $inherited_role) {
            $role_data = array_only($inherited_role, $this->getRoleMetaDataFields());

            try {
                $this->mongodb->selectCollection("roles")->insert($role_data);
            } catch (MongoException $e) {
                throw new \Exception($this->getRoleInsertionErrorMessage($role_data));
            }
        }
    }

    private function updateDeletedRoleSlugs()
    {
        $roles_collection = $this->mongodb->selectCollection("roles");
        $deleted_roles = $roles_collection->find(["status" => RoleStatus::DELETED]);

        foreach ($deleted_roles as $deleted_role) {
            try {
                $roles_collection->findOneAndUpdate(
                    ["rid" => $deleted_role["rid"]],
                    ["\$set" =>
                        [
                            "slug" =>
                                $deleted_role["slug"]."_deleted_".Carbon::create()->timestamp.$deleted_role["rid"]
                        ]
                    ]
                );
            } catch (MongoResultException $e) {
                Log::error($e->getMessage());
            }
        }
    }

    private function updateSystemDefinedRolesDuplicateSlugs()
    {
        $inherited_roles = $this->mongodb->selectCollection("roles")->find(
            ["system_role" => ["\$in" => [false, ""]], "status" => ["\$ne" => RoleStatus::DELETED]]
        )->sort(["rid" => 1]);

        $existing_system_defined_roles = $this->getExistingSystemDefinedRoleSlugs();
        foreach ($inherited_roles as $inherited_role) {
            $new_slug = "{$inherited_role["slug"]}_{$inherited_role["rid"]}";
            $this->mongodb->selectCollection("roles")->findOneAndUpdate(
                ["rid" => $inherited_role["rid"]],
                ["\$set" => ["slug" => $new_slug]]
            );

            $inherited_role_children = $this->mongodb->selectCollection("roles")->find(
                [
                    "\$and" => [
                        ["parent" => ["\$nin" => $existing_system_defined_roles]],
                        ["parent" => $inherited_role["slug"]]
                    ],
                    "status" => ["\$ne" => RoleStatus::DELETED]
                ]
            )->sort(["updated_at", 1]);

            foreach ($inherited_role_children as $inherited_role_child) {
                $this->mongodb->selectCollection("roles")->findOneAndUpdate(
                    ["rid" => $inherited_role_child["rid"]],
                    ["\$set" => ["parent" => $new_slug]]
                );
            }
        }
    }

    private function updateSiteAdminRoles()
    {
        $roles_collection = $this->mongodb->selectCollection("roles");
        $roles = $roles_collection->find();
        foreach ($roles as $role) {
            $parent_most_role = $this->findParentMostRole($role);
            $slug = $parent_most_role["slug"];

            $is_admin_role = in_array($slug, $this->getSiteAdminRoles());
            $roles_collection->findOneAndUpdate(
                ["rid" => $role["rid"]],
                ["\$set" => ["is_admin_role" => $is_admin_role]]
            );
        }
    }

    private function findParentMostRole($role)
    {
        if (is_string($role["parent"])) {
            $tmp_role = $role;
            $role = $this->mongodb->selectCollection("roles")->findOne(["slug" => $role["parent"]]);
            if (!is_null($role)) {
                $role = $this->findParentMostRole($role);
            } else {
                throw new \Exception("Could not find role for slug:{$tmp_role["slug"]}");
            }
        }

        return $role;
    }

    private function insertRegisteredUserRole()
    {
        $registered_role_data = [
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
        ];

        try {
            $this->mongodb->selectCollection("roles")->insert($registered_role_data);
        } catch (MongoException $e) {
            throw new \Exception($this->getRoleInsertionErrorMessage($registered_role_data));
        }
    }

    private function mapRoleWithContexts()
    {
        //Get "system" context type id
        $system_context = Context::where("slug", "system")->first();

        //Get "program" context type id
        $program_context = Context::where("slug", "program")->first();

        //Get "program" context type id
        $batch_context = Context::where("slug", "batch")->first();

        //  Map role and contexts the role belongs to
        $system_defined_roles_context_mappings = [
            SystemRoles::SUPER_ADMIN => [$system_context],
            SystemRoles::SITE_ADMIN => [$system_context],
            SystemRoles::CONTENT_AUTHOR => [$program_context],
            SystemRoles::PROGRAM_ADMIN => [$program_context],
            SystemRoles::LEARNER => [$program_context, $batch_context],
            SystemRoles::REGISTERED_USER => [$system_context],
        ];

        Role::get()->each(
            function ($role) use ($system_defined_roles_context_mappings) {
                $parent_role_data = $this->findParentMostRole($role->toArray());

                //Map role with context types
                $role->contexts()->saveMany($system_defined_roles_context_mappings[$parent_role_data["slug"]]);
            }
        );
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

    private function mapPermissionsForInheritedRoles()
    {
        $old_permission_mapping_with_new_permissions =
            include_once database_path("includes/old_permissions_mapping_data.php");

        Role::whereIn("system_role", [false, ""])
            ->orderBy("rid", "asc")->get()
            ->each(
                function ($role) use ($old_permission_mapping_with_new_permissions) {
                    $old_role_data = $this->mongodb->selectCollection("roles_backup")
                        ->findOne(["rid" => $role->rid]);

                    $parent_role = Role::where("slug", $role->parent)->firstOrFail();
                    if (array_has($old_role_data, "admin_capabilities")) {
                        $old_admin_permissions_by_module = $old_role_data["admin_capabilities"];
                        foreach ($old_admin_permissions_by_module as $old_module) {
                            if (array_has($old_permission_mapping_with_new_permissions, $old_module["slug"])) {
                                if (!empty($old_module["action"])) {
                                    foreach ($old_module["action"] as $old_permission) {
                                        $new_permission_data = array_get(
                                            $old_permission_mapping_with_new_permissions,
                                            "{$old_module["slug"]}.{$old_permission["slug"]}",
                                            null
                                        );

                                        if (!is_null($new_permission_data)) {
                                            if (is_array($new_permission_data)) {
                                                $module_slug = $new_permission_data["module"];
                                                $permission_slug = $new_permission_data["permission"];
                                            } else {
                                                $module_slug = $old_module["slug"];
                                                $permission_slug = $new_permission_data;
                                            }

                                            $new_permission = Module::where("slug", $module_slug)
                                                ->firstOrFail()->permissions()->where("type", PermissionType::ADMIN)
                                                ->where("slug", $permission_slug)
                                                ->firstOrFail();

                                            if (in_array($new_permission->id, $parent_role->permission_ids)) {
                                                $role->permissions()->save($new_permission);
                                            }
                                        } else {
                                            Log::notice("Could not find old module {$old_module["slug"]} permission 
                                            {$old_permission["slug"]} in new module and permissions mapping");
                                        }
                                    }
                                }
                            } else {
                                Log::notice("Could not find old module {$old_module["slug"]} in new module list");
                            }
                        }
                    }
                }
            );
    }

    private function getExistingSystemDefinedRoleSlugs()
    {
        return [
            SystemRoles::SUPER_ADMIN,
            SystemRoles::SITE_ADMIN,
            SystemRoles::CONTENT_AUTHOR,
            SystemRoles::PROGRAM_ADMIN,
            SystemRoles::LEARNER
        ];
    }

    private function getSiteAdminRoles()
    {
        return [SystemRoles::SUPER_ADMIN, SystemRoles::SITE_ADMIN];
    }

    private function getRoleMetaDataFields()
    {
        return [
            "rid",
            "name",
            "slug",
            "system_role",
            "parent",
            "description",
            "status",
            "created_by",
            "created_at",
            "updated_by",
            "updated_at"
        ];
    }

    private function getRoleInsertionErrorMessage($role_data)
    {
        return "Could not insert data for role \"{$role_data["name"]}\" with slug - {$role_data["slug"]}";
    }

    private function getInheritedRoles()
    {
        return DB::collection("roles_backup")->raw(
            function ($collection) {
                return $collection->find(["system_role" => ["\$in" => [false, ""]]]);
            }
        );
    }

    private function getSystemDefinedRoles($system_defined_role_slugs)
    {
        return DB::collection("roles_backup")->raw(
            function ($collection) use ($system_defined_role_slugs) {
                return $collection->find(
                    [
                        "\$and" => [
                            ["slug" => ["\$in" => $system_defined_role_slugs]],
                            ["system_role" => true],
                            ["status" => ["\$ne" => RoleStatus::DELETED]]
                        ]
                    ]
                );
            }
        );
    }
}
