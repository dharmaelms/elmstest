<?php

namespace App\Model;

use Moloquent;

class DirectQuizPerformanceByIndividualQuestionSummary extends Moloquent
{
    protected $collection = 'direct_quiz_performance_by_individual_question_summary';
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['create_date', 'update_date'];

    public static function isExist($quiz_id = 0)
    {
        return self::where('quiz_id', '=', (int)$quiz_id)
            ->first();
    }

    public static function getupdatedata($data = [], $id = 0)
    {
        return self::where('id', '=', (int)$id)->update($data, ['upsert' => true]);
    }

    public static function getAvgQuesScore($quiz_id)
    {
        return self::where('quiz_id', '=', (int)$quiz_id)
            ->orderBy('id', 'desc')
            ->first();
    }

    public static function getAvgChannelQuesScore($quiz_id = 0)
    {
        return self::where('quiz_id', '=', (int)$quiz_id)
            ->orderBy('id', 'desc')
            ->first();
    }
}
