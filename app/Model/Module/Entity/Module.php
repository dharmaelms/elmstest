<?php

namespace App\Model\Module\Entity;

use App\Model\RolesAndPermissions\Entity\Permission;
use Moloquent;
use Schema;
use App\Model\Sequence;

class Module extends Moloquent
{

    /**
     * Defines collection that is associated with the model
     *
     * @var string
     */
    protected $collection = "modules";

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
        return Sequence::getSequence('module_id');
    }

    /**
     * Get permissions for a particular module
     *
     * @return \Jenssegers\Mongodb\Relations\HasMany
     */
    public function permissions()
    {
        return $this->hasMany(Permission::class, "module_id");
    }

    /**
     * @param \Jenssegers\Mongodb\Eloquent\Builder $query
     * @param string $module_slug
     * @return \Jenssegers\Mongodb\Eloquent\Builder
     */
    public function scopeSlug($query, $module_slug)
    {
        return $query->where("slug", $module_slug);
    }
}
