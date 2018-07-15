<?php

namespace App\Model\RolesAndPermissions\Repository;

use App\Exceptions\RolesAndPermissions\PermissionNotFoundException;
use App\Model\RolesAndPermissions\Entity\Permission;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use DB;

class PermissionRepository implements IPermissionRepository
{
    /**
     * @inheritDoc
     */
    public function find($id)
    {
        // TODO: Implement find() method.
    }

    /**
     * @inheritDoc
     */
    public function findByAttribute($attribute, $value)
    {
        try {
            return Permission::where($attribute, $value)
                        ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new PermissionNotFoundException();
        }
    }
}
