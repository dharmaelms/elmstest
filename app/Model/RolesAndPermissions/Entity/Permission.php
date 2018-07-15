<?php

namespace App\Model\RolesAndPermissions\Entity;

use App\Enums\RolesAndPermissions\PermissionType;
use App\Model\RolesAndPermissions\Entity\Context;
use App\Model\Module\Entity\Module;
use Moloquent;
use Schema;
use App\Model\Role;
use App\Model\Sequence;

class Permission extends Moloquent
{

    /**
     * Defines collection that is associated with the model
     *
     * @var string
     */
    protected $collection = "permissions";

    /**
     * Defines primary key on the model
     *
     * @var string
     */
    protected $primaryKey = "id";

    /**
     * Attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = ["id"];

    /**
     * The attributes that should not be allowed to auto fill.
     *
     * @var array
     */
    protected $guarded = ["_id", "id"];

    /**
     * Function generate unique auto incremented id for this collection.
     *
     * @param bool $unique force to set unique index (Default: true)
     *
     * @return int
     */
    public static function getNextSequence($unique = true)
    {
        return Sequence::getSequence('permission_id');
    }

    /**
     * Get context where a particular permission is allowed
     *
     * @return \Jenssegers\Mongodb\Relations\BelongsToMany
     */
    public function contexts()
    {
        return $this->belongsToMany(
            Context::class,
            null,
            "permission_ids",
            "context_ids"
        );
    }

    /**
     * Get roles of a particular permission
     *
     * @return \Jenssegers\Mongodb\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            null,
            "permission_ids",
            "role_ids"
        );
    }

    /**
     * Get module the permission belongs to.
     *
     * @return \Jenssegers\Mongodb\Relations\BelongsTo
     */
    public function module()
    {
        return $this->belongsTo(Module::class, "module_id");
    }

    /**
     * @param \Jenssegers\Mongodb\Eloquent\Builder $query
     * @param string $slug
     * @return \Jenssegers\Mongodb\Eloquent\Builder
     */
    public function scopeSlug($query, $slug)
    {
        return $query->where("slug", $slug);
    }

    /**
     * Define scope method to query permissions based on their type
     * @param \Jenssegers\Mongodb\Eloquent\Builder $query
     * @param string $type
     * @return \Jenssegers\Mongodb\Eloquent\Builder
     */
    public function scopeType($query, $type)
    {
        return $query->where("type", $type);
    }

    /**
     * Define scope method to query admin permissions
     * @param \Jenssegers\Mongodb\Eloquent\Builder $query
     * @return \Jenssegers\Mongodb\Eloquent\Builder
     */
    public function scopeAdminPermissions($query)
    {
        return $query->type(PermissionType::ADMIN);
    }

    /**
     * Define scope method to query portal permissions
     * @param \Jenssegers\Mongodb\Eloquent\Builder $query
     * @return \Jenssegers\Mongodb\Eloquent\Builder
     */
    public function scopePortalPermissions($query)
    {
        return $query->type(PermissionType::PORTAL);
    }

    /**
     * @param $data
     * @return boolean
     */
    public static function createPermission($data)
    {
        return self::insert($data);
    }
}
