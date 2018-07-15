<?php

namespace App\Model\User\Entity;

use App\Enums\User\EnrollmentStatus;
use Carbon\Carbon;
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UserEnrollment extends Model
{
    /**
     * Defines collection that is associated with the model
     *
     * @var string
     */
    protected $collection = "user_enrollments";

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
    protected $casts = [
        // "id" => "integer",
        "user_id" => "integer",
        "entity_id" => "integer",
    ];

    /**
     * The attributes that should not be allowed to auto fill.
     *
     * @var array
     */
    protected $guarded = ["_id"];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filter_params
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, $filter_params = [])
    {
        return $query->when(
            array_has($filter_params, "user_id"),
            function ($query) use ($filter_params) {
                return $query->where("user_id", (int) $filter_params["user_id"]);
            }
        )->when(
            array_has($filter_params, "entity_type"),
            function ($query) use ($filter_params) {
                if (is_array($filter_params["entity_type"])) {
                    return $query->whereIn("entity_type", $filter_params["entity_type"]);
                } else {
                    return $query->where("entity_type", $filter_params["entity_type"]);
                }
            }
        )->when(
            array_has($filter_params, "entity_id"),
            function ($query) use ($filter_params) {
                return $query->where("entity_id", (int) $filter_params["entity_id"]);
            }
        )->when(
            array_has($filter_params, "source_type"),
            function ($query) use ($filter_params) {
                return $query->where("source_type", $filter_params["source_type"])
                    ->when(
                        array_has($filter_params, "source_id") && !is_null($filter_params["source_id"]),
                        function ($query) use ($filter_params) {
                            return $query->where("source_id", (int) $filter_params["source_id"]);
                        }
                    );
            }
        )->when(
            array_has($filter_params, "enrolled_on"),
            function ($query) use ($filter_params) {
                return $query->whereBetween("enrolled_on", $filter_params["enrolled_on"]);
            }
        )->where("status", EnrollmentStatus::ENROLLED);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where(
            function ($query) {
                return $query->whereNull("expire_on")
                    ->orWhere("expire_on", ">=", Carbon::create()->timestamp);
            }
        );
    }
}
