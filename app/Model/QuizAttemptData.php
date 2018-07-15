<?php

namespace App\Model;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Moloquent;
use Schema;

/**
 * QuizAttemptData Model
 *
 * @package Assessment
 */
class QuizAttemptData extends Moloquent
{
    /**
     * The table/collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'quiz_attempt_data';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;


    protected $guarded = ["_id"];

    public function insertQuestionAttemptData($data)
    {
        $this->fill($data);
        $this->save();
        return $this;
    }

    /*for reports*/
    public static function getLastDayQuizzAttemptData()
    {

        return self::get(['attempt_id', 'user_id', 'quiz_id', 'question_id', 'question_text', 'question_mark', 'answers', 'answer_status', 'status'])
            ->toArray();
    }

    public static function getSpecDayQuizzAttemptData($start_day = 0, $end_day = 0, $start = 0, $limit = 100)
    {
        if ($start_day > 0 && $end_day > 0) {
            return self::where('history.status', '=', 'COMPLETED')
                ->WhereBetween('history.time', [$start_day, $end_day])
                ->where(function ($q) {
                    $q->orWhere('type', 'exists', false)
                        ->orWhere('type', '!=', 'QUESTION_GENERATOR');
                })
                ->skip((int)$start)
                ->take((int)$limit)
                ->get()
                ->toArray();
        }
    }

    public static function getSpecDayQuizzAttemptDataCount($start_day = 0, $end_day = 0)
    {
        if ($start_day > 0 && $end_day > 0) {
            return self::where('history.status', '=', 'COMPLETED')
                ->WhereBetween('history.time', [$start_day, $end_day])
                ->where(function ($q) {
                    $q->orWhere('type', 'exists', false)
                        ->orWhere('type', '!=', 'QUESTION_GENERATOR');
                })
                ->count();
        }
    }


    public static function getQuizAttemptDataById($id)
    {
        try {
            return QuizAttemptData::find($id);
        } catch (ModelNotFoundException $e) {
            throw new \Exception();
        }
    }

    public static function getQuestionAttemptData($attemptId, $questionId)
    {
        $quizAttempt = QuizAttemptData::where("attempt_id", (int)$attemptId)
            ->where("question_id", (int)$questionId);

        return $quizAttempt->first();
    }

    /**
     * copyToDimensionQuizAttemptDataTbl
     * @return array
     */
    public static function copyToDimensionQuizAttemptDataTbl()
    {
        $result = QuizAttemptData::raw(function ($table) {
            return $table->aggregate([
                [
                    '$match' => [
                        'history.status' => 'COMPLETED',
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
                    '$out' => 'dim_quiz_attempt_datas'
                ]
            ]);
        });
        return $result;
    }
}
