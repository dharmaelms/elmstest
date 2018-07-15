<?php

namespace App\Model;

use Moloquent;

class QuizPerformanceByIndividualQuestionSummary extends Moloquent
{
    protected $collection = 'quiz_performance_by_individual_question_summary';
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['create_date', 'update_date'];

    public static function isExist($channel_id = 0, $quiz_id = 0)
    {
        return self::where('channel_id', '=', $channel_id)
            ->where('quiz_id', '=', $quiz_id)
            ->first();
    }

    public static function getupdatedata($data = [], $id = 0)
    {
        return self::where('id', '=', (int)$id)->update($data, ['upsert' => true]);
    }

    public static function getDetailsDateRange($channel_id = 0, $orderby = 'asc')
    {
        return self::where('channel_id', '=', $channel_id)->get()->toArray();
    }

    public static function getAvgQuesScore($quiz_id)
    {
        return self::where('quiz_id', '=', $quiz_id)
            ->orderBy('id', 'desc')
            ->first();
    }

    public static function getAvgChannelQuesScore($quiz_id = 0, $channel_id = 0)
    {
        return self::where('quiz_id', '=', (int)$quiz_id)
            ->where('channel_id', '=', (int)$channel_id)
            ->orderBy('id', 'desc')
            ->first();
    }
}
