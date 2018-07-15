<?php

namespace App\Model;

use App\Model\RolesAndPermissions\Entity\Context;
use App\Model\RolesAndPermissions\Entity\Permission;
use Moloquent;

class Role extends Moloquent
{

    protected $table = 'roles';

    /**
     * Defines primary key on the model
     *
     * @var string
     */
    protected $primaryKey = "rid";

    public $timestamps = false;

    protected $casts = [
        'rid' => 'int'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    // get all roles for admin roles to get parents drop down in role add and edit.
    public static function getAllRoles()
    {
        return self::where('status', '=', 'ACTIVE')
            ->whereNotIn('slug', ['visitor', 'super_admin'])
            ->orderby('updated_at', 'desc')
            ->get(['rid', 'name', 'slug', 'parent', 'created_at', 'updated_at', 'status', 'system_role', 'portal_capabilities', 'admin_capabilities'])
            ->toArray();
    }

    public static function pluckRoleName($rid)
    {
        return self::where('rid', '=', (int)$rid)->value('name');
    }

    // get all roles for admin roles list page.
    public static function getAllUserRoles($filter)
    {
        if ($filter == '' || $filter == 'ALL') {
            $user_roles = self::where('_id', '!=', 'rid')->where('status', '!=', 'DELETED')->orderby('updated_at', 'desc')->get(['rid', 'name', 'slug', 'parent', 'created_at', 'updated_at', 'status', 'system_role'])->toArray();
        } else {
            $user_roles = self::where('_id', '!=', 'rid')->where('status', '=', $filter)->orderby('updated_at', 'desc')->get(['rid', 'name', 'slug', 'parent', 'created_at', 'updated_at', 'status', 'system_role'])->toArray();
        }

        return $user_roles;
    }

    public static function getNextRoleId()
    {
        return Sequence::getSequence('rid');
    }

    public static function getRoleSlug($rolename)
    {
        $slug = strtolower(stripslashes(trim($rolename)));   // Convert all the text to lower case
        $slug = str_replace(' - ', '-', $slug);   // Replace any ' - ' sign with spaces on both sides to '-'
        $slug = str_replace(' & ', '-and-', $slug);   // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace('& ', '-and-', $slug);    // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace("'", '', $slug);  // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace('\\', '', $slug); // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace('/', '-', $slug); // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace(', ', '-', $slug);    // Replace any comma and a space to -
        $slug = str_replace('.com', 'dotcom', $slug); // Remove any dot and a space
        $slug = str_replace('.', '', $slug);  // Remove any dot and a space
        $slug = str_replace('   ', '-', $slug);   // replace space to -
        $slug = str_replace('  ', '-', $slug);    // replace space to -
        $slug = str_replace(' ', '-', $slug); // replace space to -
        $slug = str_replace('!', '', $slug);  // remove !
        $slug = str_replace('#', '', $slug);  // remove #
        $slug = str_replace('$', '', $slug);  // remove $
        $slug = str_replace(':', '', $slug);  // remove :
        $slug = str_replace(';', '', $slug);  // remove ;
        $slug = str_replace('[', '', $slug);  // remove [
        $slug = str_replace(']', '', $slug);  // remove ]
        $slug = str_replace('(', '', $slug);  // remove (
        $slug = str_replace(')', '', $slug);  // remove )
        $slug = str_replace('\n', '', $slug); // remove \n
        $slug = str_replace('\r', '', $slug); // remove \r
        $slug = str_replace('?', '', $slug);  // remove ?
        $slug = str_replace('`', '', $slug);  // remove `
        $slug = str_replace('%', '', $slug);  // remove %
        $slug = str_replace('&#39;', '', $slug);  // remove &#39; = '
        $slug = str_replace('&39;', '', $slug);   // remove &39; = '
        $slug = str_replace('&39', '', $slug);    // remove &39; = '
        $slug = str_replace('&quot;', '-', $slug);
        $slug = str_replace('\"', '-', $slug);
        $slug = str_replace('"', '-', $slug);
        $slug = str_replace('&lt;', '-', $slug);
        $slug = str_replace('&gt;', '-', $slug);
        $slug = str_replace('<', '', $slug);
        $slug = str_replace('>', '', $slug);

        return $slug;
    }

    //function to get portal role details to edit roles in admin
    public static function getRole($id)
    {
        return self::where('rid', '=', (int)$id)->get()->toArray();
    }

    public static function UpdateRole($info, $id)
    {
        $slug = self::getRoleSlug($info['role_name']);
        $role_info = self::getRole($id);

        if (strstr($role_info[0]['slug'], $slug)) {
            $role_cache_file = storage_path() . '/framework/cache/roles/' . $role_info[0]['slug'] . '.json';
            if (file_exists($role_cache_file)) {
                unlink($role_cache_file);
            }
        }
        $id = (int)$id;
        if (!isset($info['status'])) {
            self::where('rid', '=', $id)
                ->update(
                    [
                        'name' => trim($info['role_name']),
                        'slug' => $slug,
                        'description' => $info['description'],
                        'is_admin_role' => $info['is_admin_role'],
                        'parent' => $info['parent_role'],
                        'updated_at' => time(),
                    ]
                );
        } else {
            self::where('rid', '=', $id)
                ->update(
                    [
                        'name' => trim($info['role_name']),
                        'slug' => $slug,
                        'description' => $info['description'],
                        'status' => $info['status'],
                        'is_admin_role' => $info['is_admin_role'],
                        'parent' => $info['parent_role'],
                        'updated_at' => time(),
                    ]
                );
        }

        return 1;
    }
   
    /**
     * Add roles method used to store the roles details
     * @return \App\Model\Role
     */
    public static function AddRole($data)
    {
        $role = new Role();
        $role->name = trim($data['role_name']);
        $role->slug = self::getRoleSlug($data['role_name']);
        $role->rid = $data["id"];
        $role->is_admin_role = $data["is_admin_role"];
        $role->parent = $data["parent_role_slug"];
        $role->description =  $data['description'];
        $role->system_role = false;
        $role->status = $data['status'];
        $role->created_at = time();
        $role->updated_at= time();
        $role->contexts()->attach($data["context_ids"]);
        $role->save();

        return $role;
    }

    public static function RemovePermissions($id)
    {
        self::where('rid', '=', (int)$id)->unset(['admin_capabilities', 'portal_capabilities']);

        return 1;
    }

    //function to check uniqueness of role name except current role
    public static function getRoleArray($id = null)
    {
        if ($id) {
            $data = self::where("_id", "!=", "rid")
                        ->where('rid', '!=', (int)$id)
                        ->get(['name'])->toArray();
        } else {
            $data = self::where("_id", "!=", "rid")
                        ->get(['name'])->toArray();
        }

        $count = count($data);
        $role_array = [];
        for ($i = 0; $i < $count; ++$i) {
            $role_array[] = $data[$i]['name'];
        }

        return $role_array;
    }

    public static function getDeleteRole($id)
    {
        self::where('rid', '=', (int)$id)->update(['status' => 'DELETED']);
    }

    public static function getRolesCount($status = 'all', $search = null)
    {
        if ($status == 'all') {
            if ($search) {
                return self::where('_id', '!=', 'rid')->where('name', 'like', '%' . $search . '%')->orWhere('description', 'like', '%' . $search . '%')->where('status', '!=', 'DELETED')->whereNotIn('slug', ['super_admin'])->count();
            } else {
                return self::where('_id', '!=', 'rid')->where('status', '!=', 'DELETED')->whereNotIn('slug', ['super_admin'])->count();
            }
        } else {
            if ($search) {
                return self::where('_id', '!=', 'rid')->where('name', 'like', '%' . $search . '%')->orWhere('description', 'like', '%' . $search . '%')->where('status', '!=', 'DELETED')->where('status', '=', $status)->whereNotIn('slug', ['super_admin'])->count();
            } else {
                return self::where('_id', '!=', 'rid')->where('status', '!=', 'DELETED')->where('status', '=', $status)->whereNotIn('slug', ['super_admin'])->count();
            }
        }
    }

    public static function getFilteredRolesWithPagination($status = 'all', $start = 0, $limit = 10, $orderby = ['created_at' => 'desc'], $search = null)
    {
        $key = key($orderby);
        $value = $orderby[$key];
        if ($status == 'all') {
            if ($search) {
                return self::where('_id', '!=', 'rid')->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->where('status', '!=', 'DELETED')->whereNotIn('slug', ['super_admin'])
                    ->orderBy($key, $value)->skip((int)$start)->take((int)$limit)
                    ->with("contexts")->get()->toArray();
            } else {
                return self::where('_id', '!=', 'rid')->where('status', '!=', 'DELETED')->whereNotIn('slug', ['super_admin'])
                    ->orderBy($key, $value)->skip((int)$start)->take((int)$limit)
                    ->with("contexts")->get()->toArray();
            }
        } else {
            if ($search) {
                return self::where('_id', '!=', 'rid')->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->where('status', '=', $status)->where('status', '!=', 'DELETED')
                    ->whereNotIn('slug', ['super_admin'])->orderBy($key, $value)
                    ->skip((int)$start)->take((int)$limit)
                    ->with("contexts")->get()->toArray();
            } else {
                return self::where('_id', '!=', 'rid')->where('status', '!=', 'DELETED')
                    ->where('status', '=', $status)
                    ->whereNotIn('slug', ['super_admin'])
                    ->orderBy($key, $value)->skip((int)$start)->take((int)$limit)
                    ->with("contexts")->get()->toArray();
            }
        }
    }

    public static function getRoleinfo($slug)
    {
        return self::where('slug', '=', $slug)
            ->get(['rid', 'name', 'slug', 'parent', 'description', 'status', 'system_role'])
            ->toArray();
    }

    /**
     * Get permissions of a particular role
     *
     * @return \Jenssegers\Mongodb\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, null, "role_ids", "permission_ids");
    }

    /**
     * Create many to many relationship b/w context and role model to identify which context the role belongs to
     *
     * @return \Jenssegers\Mongodb\Relations\BelongsToMany
     */
    public function contexts()
    {
        return $this->belongsToMany(Context::class, null, "role_ids", "context_ids");
    }

    /**
     * @return \Jenssegers\Mongodb\Eloquent\Builder
     */
    public function adminPermissions()
    {
        return $this->permissions()->adminPermissions();
    }

    /**
     * @return \Jenssegers\Mongodb\Eloquent\Builder
     */
    public function portalPermissions()
    {
        return $this->permissions()->portalPermissions();
    }

    public static function getErpRole($user_role = 'learner') {
        $roles = self::where('rid', '>', 0)->get(['rid', 'name'])->toArray();
        foreach($roles as $role) {
           $role_name = strtolower(str_replace(" ",'',$role['name']));
           $role_id = $role['rid'];
           if($user_role == $role_name) {
              return $role_id;
           }
        }
    }
}
