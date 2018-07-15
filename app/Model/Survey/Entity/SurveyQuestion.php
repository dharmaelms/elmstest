<?php

namespace App\Model\Survey\Entity;

use Carbon\Carbon;
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Model\Sequence;

class SurveyQuestion extends Model
{
    /**
     * Defines collection that is associated with the model
     *
     * @var string
     */
    protected $collection = "survey_questions";

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
        "survey_id" => "integer"
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
    public function scopeFilter($query, $filter_params = [], $orderBy = ["title" => "desc"])
    {
        return $query->when(
            array_has($filter_params, "id"),
            function ($query) use ($filter_params) {
                return $query->where("id", (int) $filter_params["id"]);
            }
        )->when(
            array_has($filter_params, "status"),
            function ($query) use ($filter_params) {
                return $query->where("status", $filter_params["status"]);
            }
        )->when(
            !empty($filter_params["search"]),
            function ($query) use ($filter_params) {
                return $query->Where("title", "like", "%{$filter_params["search"]}%");
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
        )->when(
            array_has($filter_params, "survey_id"),
            function ($query) use ($filter_params) {
                return $query->where("survey_id", (int)$filter_params["survey_id"]);
            }
        );
    }

    public static function getNextSequence()
    {
        return Sequence::getSequence('ques_id');
    }

    public static function getSurveyQuestionsById($question_id)
    {
        if (is_array($question_id)) {
            return SurveyQuestion::whereIn("id", $question_id)
                    ->where('status', '=', 'ACTIVE')
                    ->get();
        } else {
            return SurveyQuestion::where("id", '=', (int)$question_id)
                    ->where('status', '=', 'ACTIVE')
                    ->get();
        }
    }

    public static function updateSurveyQuestions($qid, $sqdata)
    {
        SurveyQuestion::where('id', '=', (int)$qid)
                ->update($sqdata);
    }

    public static function DeleteSurveyQuestion($qid)
    {
        return self::where('id', '=', (int)$qid)
                    ->update(
                        [
                            'status' => 'DELETED',
                            'updated_at' => time()
                        ]
                    );
    }

    public static function insertSurveyQuestions($data)
    {
        return SurveyQuestion::insert($data);
    }
}
