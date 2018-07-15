<?php

namespace App\Model\Assignment\Entity;

use App\Model\Sequence;
use Jenssegers\Mongodb\Eloquent\Model;

class AssignmentAttempt extends Model
{
    /**
     * Defines collection that is associated with the model
     *
     * @var string
     */
    protected $collection = "assignment_attempts";

    /**
     * Defines primary key on the model
     *
     * @var integer
     */
    protected $primaryKey = "id";

    /**
     * Attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        "id" => "integer",
        "assignment_id" => "integer",
        'user_id' => 'integer'
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
    public function scopeFilter($query, $filter_params = [], $orderBy = [])
    {
        return $query->when(
            array_has($filter_params, "id"),
            function ($query) use ($filter_params) {
                return $query->where("id", (int) $filter_params["id"]);
            }
        )->when(
            array_has($filter_params, "assignment_id"),
            function ($query) use ($filter_params) {
                if (is_array($filter_params["assignment_id"])) {
                    return $query->whereIn("assignment_id", $filter_params["assignment_id"]);
                } else {
                    return $query->where("assignment_id", (int) $filter_params["assignment_id"]);
                }
            }
        )->when(
            array_has($filter_params, "user_id"),
            function ($query) use ($filter_params) {
                return $query->where("user_id", (int) $filter_params["user_id"]);
            }
        )->when(
            array_has($filter_params, "excluded_user_id"),
            function ($query) use ($filter_params) {
                return $query->whereNotIn("user_id",  $filter_params["excluded_user_id"]);
            }
        )->when(
            array_has($filter_params, "submission_type"),
            function ($query) use ($filter_params) {
                return $query->where("submission_status", $filter_params["submission_type"]);
            }
        )->when(
            !empty($filter_params["search"]),
            function ($query) use ($filter_params) {
                return $query->Where("name", "like", "%{$filter_params["search"]}%");
            }
        )->when(
            !empty($orderBy),
            function ($query) use ($orderBy) {
                return $query->orderBy(
                    key($orderBy),
                    $orderBy[key($orderBy)]
                );
            }
        )->when(
            isset($filter_params["start"]),
            function ($query) use ($filter_params) {
                return $query->skip((int)$filter_params["start"]);
            }
        )->when(
            isset($filter_params["limit"]),
            function ($query) use ($filter_params) {
                return $query->take((int)$filter_params["limit"]);
            }
        );
    }

    /**
     * @return int
     */
    public static function getNextSequence()
    {
        return Sequence::getSequence('assignment_id');
    }
}
