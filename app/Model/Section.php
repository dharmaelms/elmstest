<?php

namespace App\Model;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Moloquent;

/**
 * Quiz Model.
 */
class Section extends Moloquent
{
    /**
     * The table/collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'sections';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'section_id' => 'integer'
    ];

    /**
     * Function generate unique auto incremented id for this collection.
     *
     * @param bool $unique force to set unique index (Default: true)
     *
     * @return int
     */
    public static function getNextSequence()
    {
        return self::max('section_id') + 1;
    }

    public static function sectionInsert($data = [])
    {
        if (!empty($data)) {
            $data['section_id'] = self::getNextSequence();
            return self::insert($data);
        } else {
            return false;
        }
    }

    public static function getSectionByCustomId($sectionCustomId)
    {
        $sectionCollection = Section::raw(function ($collection) use ($sectionCustomId) {
            return $collection->find([
                "section_id" => (int)$sectionCustomId
            ]);
        });
        return $sectionCollection->first();
    }

    public static function sectionUpdate($data = [], $id = 0)
    {
        if (!empty($data) && $id > 0) {
            return self::where('section_id', '=', (int)$id)
                ->update($data, ['upsert' => true]);
        } else {
            return false;
        }
    }

    public static function getSectionOne($sec_id = 0)
    {
        if ($sec_id > 0) {
            return self::where('section_id', '=', $sec_id)
                ->get();
        } else {
            return false;
        }
    }

    public static function getSectionInQuizCount($quiz_id = 0)
    {
        return self::where('quiz_id', '=', (int)$quiz_id)
            ->where('status', '!=', 'DELETE')
            ->count();
    }

    public static function getSectionInQuizPageCount($quiz_id = 0, $start = 0, $limit = 10)
    {
        return self::where('quiz_id', '=', (int)$quiz_id)
            ->skip((int)$start)
            ->where('status', '!=', 'DELETE')
            ->take((int)$limit)
            ->count();
    }

    public static function getSectionInQuizPagenation($quiz_id = 0, $start = 0, $limit = 10, $orderby = ['created_at' => 'desc'], $search = '')
    {
        $key = key($orderby);
        $value = $orderby[$key];
        if ($search != '') {
            return self::where('status', '!=', 'DELETE')
                ->where('quiz_id', '=', (int)$quiz_id)
                ->orwhere('title', 'like', '%' . $search . '%')
                ->orderBy($key, $value)
                ->skip((int)$start)
                ->take((int)$limit)
                ->get()
                ->toArray();
        } else {
            return self::where('status', '!=', 'DELETE')
                ->where('quiz_id', '=', (int)$quiz_id)
                ->orderBy($key, $value)
                ->skip((int)$start)
                ->take((int)$limit)
                ->get()
                ->toArray();
        }
    }

    public static function getSectionInQuiz($quiz_id = 0)
    {
        if ($quiz_id > 0) {
            return self::where('quiz_id', '=', (int)$quiz_id)
                ->where('status', '=', 'ACTIVE')
                ->orderBy('created_at', 'asc')
                ->get();
        } else {
            return false;
        }
    }

    public static function getIsNameExist($quiz_id = 0, $title = "")
    {
        if ($quiz_id > 0 && $title != "") {
            return self::where('quiz_id', '=', (int)$quiz_id)
                ->where('status', '=', 'ACTIVE')
                ->where('title', '=', $title)
                ->first();
        } else {
            return true;
        }
    }

    public static function getIsNameExistExceptSec($quiz_id = 0, $title = "", $section_id = 0)
    {
        if ($quiz_id > 0 && $title != "" && $section_id > 0) {
            return self::where('quiz_id', '=', (int)$quiz_id)
                ->where('section_id', '!=', (int)$section_id)
                ->where('status', '=', 'ACTIVE')
                ->where('title', '=', $title)
                ->first();
        } else {
            return false;
        }
    }

    public static function getQuestionInQuiz($quiz_id = 0)
    {
        if ($quiz_id > 0) {
            return self::where('quiz_id', '=', (int)$quiz_id)
                ->where('status', '=', 'ACTIVE')
                ->where('questions.0', 'exists', true)
                ->orderBy('created_at', 'asc')
                ->get(['section_id', 'questions', 'description', 'title', 'total_marks', 'cut_off', 'cut_off_mark', 'duration']);
        } else {
            return [];
        }
    }

    public static function getSectionById($id)
    {
        try {
            return Section::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new \Exception();
        }
    }

    /**
     * Get all sections duration by using quiz id
     * @param  int $quiz_id
     * @return  int $duration
     */
    public static function getSectionsTotalDuration($quiz_id)
    {
        return self::where('quiz_id', (int)$quiz_id)
            ->where('status', 'ACTIVE')
            ->sum('duration');
    }

    /**
     * Scope a query to only active sections
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }
}
