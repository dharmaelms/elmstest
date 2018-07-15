<?php

namespace App\Model\RolesAndPermissions\Entity;

use Moloquent;
use Schema;
use Illuminate\Support\Facades\DB;


class UserRoleAssignment extends Moloquent
{
    /**
     * Defines collection that is associated with the model
     *
     * @var string
     */
    protected $collection = "user_role_assignments";

    /**
     * Defines primary key on the model
     *
     * @var string
     */
    // protected $primaryKey = "id";

    /**
     * Attributes that should be casted to native types.
     *
     * @var array
     */
    // protected $casts = ["id"];

    /**
     * The attributes that should not be allowed to auto fill.
     *
     * @var array
     */
    protected $guarded = ["_id"];

    public function scopeFilter($query, $filter_params = [])
    {
        return $query->when(
            isset($filter_params["user_id"]),
            function ($query) use ($filter_params) {
                return $query->where("user_id", (int) $filter_params["user_id"]);
            }
        )->when(
            isset($filter_params["status"]),
            function ($query) use ($filter_params) {
                return $query->where("status", (int) $filter_params["status"]);
            }
        )->when(
            isset($filter_params["context_id"]),
            function ($query) use ($filter_params) {
                if (is_array($filter_params["context_id"])) {
                    return $query->whereIn("context_id", $filter_params["context_id"]);
                } else {
                    return $query->where("context_id", (int) $filter_params["context_id"]);
                }
            }
        )->when(
            array_has($filter_params, "instance_id"),
            function ($query) use ($filter_params) {
                if (is_null($filter_params["instance_id"])) {
                    //When instance id is null query returns role assigned to user in system level.
                    return $query->whereNull("instance_id");
                } else {
                    if (is_array($filter_params["instance_id"])) {
                        return $query->whereIn("instance_id", $filter_params["instance_id"]);
                    } else {
                        return $query->where("instance_id", (int) $filter_params["instance_id"]);
                    }
                }
            }
        )->when(
            isset($filter_params["role_id"]),
            function ($query) use ($filter_params) {
                if (is_array($filter_params["role_id"])) {
                    return $query->whereIn("role_id", $filter_params["role_id"]);
                } else {
                    return $query->where("role_id", (int) $filter_params["role_id"]);
                }
            }
        );
    }
}
