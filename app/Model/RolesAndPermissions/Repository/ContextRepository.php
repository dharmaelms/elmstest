<?php

namespace App\Model\RolesAndPermissions\Repository;

use App\Exceptions\RolesAndPermissions\ContextNotFoundException;
use App\Model\RolesAndPermissions\Entity\Context;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ContextRepository implements IContextRepository
{
    /**
     * @inheritDoc
     */
    public function find($id)
    {
        try {
            return Context::findOrFail((int) $id);
        } catch (ModelNotFoundException $e) {
            throw new ContextNotFoundException();
        }
    }

    /**
     * @inheritDoc
     */
    public function findByAttribute($attribute, $value, $include_roles = false)
    {
        try {
            return Context::where($attribute, $value)
                            ->when(
                                $include_roles,
                                function ($query) {
                                    return $query->with("roles");
                                }
                            )->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new ContextNotFoundException();
        }
    }

    /**
     * @inheritDoc
     */
    public function get($include_roles = false)
    {
        //If $include_roles is true retrieve contexts along with roles else just retrieve the roles.
        return Context::where("_id", "!=", "id")
                        ->when(
                            $include_roles,
                            function ($query) {
                                return $query->with("roles");
                            }
                        )->get();
    }
}
