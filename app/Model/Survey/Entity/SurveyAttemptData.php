<?php

namespace App\Model\Survey\Entity;

use Carbon\Carbon;
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SurveyAttemptData extends Model
{
    /**
     * Defines collection that is associated with the model
     *
     * @var string
     */
    protected $collection = "survey_attempt_data";

    /**
     * Attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        "survey_id" => "integer",
        'user_id' => 'integer',
        'question_id' => 'integer',
        'attempt_id' => 'integer'
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
            array_has($filter_params, "question_id"),
            function ($query) use ($filter_params) {
                return $query->where("id", (int) $filter_params["id"]);
            }
        )->when(
            array_has($filter_params, "survey_id"),
            function ($query) use ($filter_params) {
                return $query->where("survey_id", (int) $filter_params["survey_id"]);
            }
        )->when(
            array_has($filter_params, "user_id"),
            function ($query) use ($filter_params) {
                return $query->where("user_id", (int) $filter_params["user_id"]);
            }
        )->when(
            array_has($filter_params, "attempt_id"),
            function ($query) use ($filter_params) {
                return $query->where("attempt_id", (int) $filter_params["attempt_id"]);
            }
        );
    }
}
