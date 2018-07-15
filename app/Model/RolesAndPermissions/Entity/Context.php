<?php

namespace App\Model\RolesAndPermissions\Entity;

use App\Model\Role;
use App\Model\Sequence;
use App\Model\RolesAndPermissions\Entity\Permission;
use Moloquent;
use Schema;

class Context extends Moloquent
{
    /**
     * The table/collection associated with the model.
     *
     * @var string
     */
    protected $collection = "contexts";

    /**
     * Defines primary key on the model
     *
     * @var string
     */
    protected $primaryKey = "id";

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];

    /**
     * The attributes that should not be allowed to auto fill
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
        return Sequence::getSequence('context_id');
    }

    /**
     * Get permissions that allowed in particular context
     *
     * @return \Jenssegers\Mongodb\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            null,
            "context_ids",
            "permission_ids"
        );
    }

    /**
     * Define Many to Many relation b/w context and role model
     *
     * @return \Jenssegers\Mongodb\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, null, "context_ids", "role_ids");
    }
}
