<?php

namespace App\Model\Survey\Entity;

use App\Model\Sequence;
use Auth;
use Carbon\Carbon;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * Class Survey
 * @package App\Model\Survey\Entity
 */
class Survey extends Model
{
    /**
     * Defines collection that is associated with the model
     *
     * @var string
     */
    protected $collection = "surveys";

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
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'start_time', 'end_time'];

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filter_params
     * @param array $orderBy
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, $filter_params = [], $orderBy = ["survey_title" => "desc"])
    {
        return $query->when(
            array_has($filter_params, "id"),
            function ($query) use ($filter_params) {
                if (is_array($filter_params)) {
                    return $query->whereIn("id", $filter_params["id"]);
                } else {
                    return $query->where("id", (int) $filter_params["id"]);
                }
            }
        )->when(
            array_has($filter_params, "status"),
            function ($query) use ($filter_params) {
                return $query->where("status", $filter_params["status"]);
            }
        )->when(
            !empty($filter_params["search"]),
            function ($query) use ($filter_params) {
                return $query->Where("survey_title", "like", "%{$filter_params["search"]}%");
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
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where(
            function ($query) {
                return $query->whereNull("end_time")
                    ->orWhere("end_time", ">=", Carbon::create()->timestamp);
            }
        );
    }

    /**
     * @return int
     */
    public static function getNextSequence()
    {
        return Sequence::getSequence('survey_id');
    }

    /**
     * Overrides dates mutator when field has 0 as value
     * @return array
     */
    public function getDates()
    {
        $date_mutatuor = $this->dates;

        if (isset($this->attributes['end_time'])) {
            $end_time = $this->attributes['end_time'];

            if (!$end_time instanceof \MongoDB\BSON\UTCDateTime && $end_time == 0) {
                $date_mutatuor = array_diff($date_mutatuor, ['end_time']);
            }
        }

        return $date_mutatuor;
    }

    /**
     * @param $sid
     * @param $array_name
     * @param $input_ids
     * @return mixed
     */
    public static function UpdateSurveyRelations($sid, $array_name, $input_ids)
    {
        if (!is_array($input_ids)) {
            $input_ids = (int)$input_ids;
        }
        return self::where('id', '=', (int)$sid)
            ->update([
                $array_name => $input_ids,
                'updated_at' => time()
            ]);
    }

    /**
     * @param $sid
     * @param $arrname
     * @return mixed
     */
    public static function UnsetSurveyRelations($sid, $arrname)
    {
        return self::where('id', '=', (int)$sid)
            ->unset($arrname);
    }

    /**
     * @param $ids
     * @return mixed
     */
    public static function getSurveyNameByID($ids)
    {
        return self::whereIn('id', $ids)->get(['id', 'survey_title',  'created_at', 'created_by']);
    }

    /**
     * @param $survey_ids
     * @return mixed
     */
    public static function getSurveyByIds($survey_ids)
    {
        if (is_array($survey_ids)) {
            return self::whereIn("id", $survey_ids)->orderBy('start_time', 'desc')->get();
        } else {
            return self::where("id", '=', (int)$survey_ids)->get();
        }
    }

    /**
     * @param $sid
     * @return mixed
     */
    public static function DeleteSurvey($sid)
    {
        return self::where('id', '=', (int)$sid)
                    ->update(
                        [
                            'status' => 'DELETED',
                            'updated_at' => time()
                        ]
                    );
    }

    /**
     * @param $survey_id
     * @param $field_name
     * @return mixed
     */
    public static function getSurveyFieldById($survey_id, $field_name)
    {
        if (is_array($survey_id)) {
            return self::whereIn("id", $survey_id)->get($field_name);
        } else {
            return self::where("id", '=', (int)$survey_id)->get($field_name);
        }
    }

    /**
     * @param $survey_id
     * @param $field_name
     * @param array $input_ids
     * @return mixed
     */
    public static function pushSurveyRelations($survey_id, $field_name, $input_ids = [])
    {
        foreach ($field_name as $field) {
            return self::where('id', '=', (int)$survey_id)->push($field, (int)$input_ids);
        }
        return self::where('id', (int)$survey_id)->update(['updated_at' => time()]);
    }

    /**
     * @param $survey_id
     * @param $field_name
     * @param $input_ids
     * @return mixed
     */
    public static function pullSurveyRelations($survey_id, $field_name, $input_ids)
    {
        foreach ($field_name as $field) {
            return self::where('id', '=', (int)$survey_id)->pull($field, (int)$input_ids);
        }
        return self::where('id', (int)$survey_id)->update(['updated_at' => time()]);
    }

    /**
     * @param $input_data
     */
    public static function insertSurvey($input_data)
    {
        self::insert([
            "id" => Survey::getNextSequence(),
            'survey_title' => $input_data['survey_title'],
            'description' => $input_data['description'],
            'start_time' => (int) $input_data['start_time'],
            'end_time' => (int) $input_data['end_time'],
            'display_report' => ($input_data['display_report'] == true) ? true : false,
            'status' => 'ACTIVE',
            'created_by' => Auth::user()->username,
            'created_at' => time(),
        ]);
    }

    /**
     * @param $sid
     * @param $data
     */
    public static function updateSurvey($sid, $data)
    {
        self::where('id', '=', (int)$sid)
                ->update($data);
    }

    /**
     * @param $sid
     * @param $array_name
     * @return mixed
     */
    public static function unassignPost($sid, $array_name)
    {
        return self::where('id', (int)$sid)->update([ $array_name => null]);
    }
}
