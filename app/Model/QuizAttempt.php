<?php

namespace App\Model;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\GetNextSequence;
use Moloquent;
use Schema;
use DB;



/**
 * QuizAttempt Model
 *
 * @package Assessment
 */
class QuizAttempt extends Moloquent
{
    use GetNextSequence;
    /**
     * The table/collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'quiz_attempts';

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
        'attempt_id' => 'integer',
        'quiz_id' => 'integer',
        'user_id' => 'integer'
    ];

    /**
     * The attributes that should be mutated to dates
     *
     * @var array
     */
    protected $dates = ['started_on', 'completed_on'];

    /**
     * @var array
     */
    protected $guarded = ["_id"];

    /**
     * Function generate unique auto incremented id for this collection
     *
     * @return integer
     */
    public static function getNextSequence()
    {
        return Sequence::getSequence('attempt_id');
    }

    /*for reports*/
    /**
     * @return array
     */
    public static function getLastDayQuizzAttempt()
    {
        return self::where('status', '=', 'CLOSED')
            ->get(['attempt_id', 'user_id', 'quiz_id', 'questions', 'total_mark', 'obtained_mark', 'started_on', 'completed_on'])
            ->toArray();
    }

    /**
     * @param int $start_day
     * @param int $end_day
     * @return array
     */
    public static function getSpecDayQuizzAttempt($start_day = 0, $end_day = 0)
    {
        if ((int)$start_day > 0 && (int)$end_day > 0) {
            return self::where('status', '=', 'CLOSED')
                ->WhereBetween('completed_on', [(int)$start_day, (int)$end_day])
                ->where(function ($q) {
                    $q->orWhere('type', 'exists', true)
                        ->orWhere('type', '!=', 'QUESTION_GENERATOR');
                })
                ->get()
                ->toArray();
        }
    }

    /**
     * @param $data
     * @return $this
     */
    public function insertQuizAttempt($data)
    {
        self::insert($data);
        return self::where('attempt_id', (int)$data['attempt_id'])->first();
    }

    /**
     * @param $customId
     * @return \Jenssegers\Mongodb\Collection
     */
    public static function getAttemptByCustomId($customId)
    {
        $attempts = QuizAttempt::raw(function ($collection) use ($customId) {
            return $collection->find([
                "quiz_id" => (int)$customId
            ]);
        });

        return $attempts->first();
    }

    /**
     * @param $id
     * @return \Jenssegers\Mongodb\Collection
     * @throws \Exception
     */
    public static function getAttemptById($id)
    {
        try {
            return QuizAttempt::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new \Exception();
        }
    }

    /**
     * @param $quizId
     * @param $userId
     * @return \Jenssegers\Mongodb\Collection
     */
    public static function getAttemptByUser($quizId, $userId)
    {
        return self::where("quiz_id", (int)$quizId)
            ->where("user_id", (int)$userId)
            ->get();
    }

    /**
     * @param $id
     * @param $data
     * @return \Jenssegers\Mongodb\Collection
     */
    public static function updateAttempt($id, $data)
    {
        $attempt = self::find($id);
        $attempt->fill($data);
        $attempt->save();
        return $attempt;
    }

    /**
     * Overrides dates mutator when field has '' as value
     * @return array
     */
    public function getDates()
    {
        $date_mutatuor = $this->dates;
        
        if (!array_get($this->attributes, 'completed_on') || $this->attributes['completed_on'] == null) {
            $date_mutatuor = array_diff($date_mutatuor, ['completed_on']);
        }

        return $date_mutatuor;
    }

    /**
     * copyToDimensionQuizAttemptTbl
     * @return array
     */
    public static function copyToDimensionQuizAttemptTbl()
    {
        $result = QuizAttempt::raw(function ($table) {
            return $table->aggregate([
                [
                    '$match' => [
                        'status' => 'CLOSED',
                        '$or' => [
                            [
                                'type' => [
                                    '$exists' => false
                                ]
                            ],
                            [
                                'type' => [
                                    '$ne' => 'QUESTION_GENERATOR'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    '$out' => 'dim_quiz_attempts'
                ]
            ]);
        });
        return $result;
    }

    public static function getAttemptedQuizzIdsByUID($uid)
    {
        return QuizAttempt::where('status', '=', 'CLOSED')
                    ->where('user_id', '=', (int)$uid)
                    ->get(['quiz_id']);
    }
}
