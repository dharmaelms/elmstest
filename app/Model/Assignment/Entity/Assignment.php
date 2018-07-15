<?php

namespace App\Model\Assignment\Entity;

use App\Model\Sequence;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * Class Survey
 * @package App\Model\Assignment\Entity
 */
class Assignment extends Model
{
    /**
     * Defines collection that is associated with the model
     *
     * @var string
     */
    protected $collection = "assignments";

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
    protected $dates = ['created_at', 'updated_at', 'start_time', 'end_time', 'cutoff_time'];

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filter_params
     * @param array $orderBy
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, $filter_params = [], $orderBy = ["name" => "desc"])
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
            array_has($filter_params, "start_time"),
            function ($query) use ($filter_params) {
                return $query->where("start_time", "<=", $filter_params["start_time"]);
            }
        )->when(
            array_has($filter_params, "cutoff_time"),
            function ($query) use ($filter_params) {
                return $query->where("cutoff_time", ">=", $filter_params["cutoff_time"]);
            }
        )->when(
            array_has($filter_params, "created_by"),
            function ($query) use ($filter_params) {
                return $query->where("created_by", "=", $filter_params["created_by"]);
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

        if (isset($this->attributes['cutoff_time'])) {
            $cutoff_time = $this->attributes['cutoff_time'];

            if (!$cutoff_time instanceof \MongoDB\BSON\UTCDateTime && $cutoff_time == 0) {
                $date_mutatuor = array_diff($date_mutatuor, ['cutoff_time']);
            }
        }

        return $date_mutatuor;
    }

    /**
     * @param $aid
     * @param $array_name
     * @param $input_ids
     * @return mixed
     */
    public static function updateAssignmentRelations($aid, $array_name, $input_ids)
    {
        if (!is_array($input_ids)) {
            $input_ids = (int)$input_ids;
        }
        return self::where('id', '=', (int)$aid)
            ->update([
                $array_name => $input_ids,
                'updated_at' => time()
            ]);
    }

    /**
     * @param $aid
     * @param $arrname
     * @return mixed
     */
    public static function unsetAssignmentRelations($aid, $arrname)
    {
        return self::where('id', '=', (int)$aid)
            ->unset($arrname);
    }

    /**
     * @param $aid
     * @param $array_name
     * @return mixed
     */
    public static function unassignPost($aid, $array_name)
    {
        return self::where('id', (int)$aid)->update([ $array_name => null]);
    }

    /**
     * @param $assignment_ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAssignmentByIds($assignment_ids)
    {
        if (is_array($assignment_ids)) {
            return Assignment::whereIn("id", $assignment_ids)->orderBy('start_time', 'desc')->get();
        } else {
            return Assignment::where("id", '=', (int)$assignment_ids)->get();
        }
    }

    /**
     * @param $ids
     * @return array
     */
    public static function getAssignmentNameByID($ids)
    {
        return Assignment::whereIn('id', $ids)->get(['id', 'name',  'created_at', 'created_by']);
    }

    /**
     * @param $assignment_id
     * @param $field_name
     * @param $input_ids
     * @return mixed
     */
    public static function pullAssignmentRelations($assignment_id, $field_name, $input_ids)
    {
        foreach ($field_name as $field) {
            return self::where('id', '=', (int)$assignment_id)->pull($field, (int)$input_ids);
        }
        return self::where('id', (int)$assignment_id)->update(['updated_at' => time()]);
    }
}