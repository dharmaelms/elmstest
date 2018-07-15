<?php

namespace App\Model;

use Moloquent;
use Schema;

/**
 * QuizReport Model.
 */
class QuizReport extends Moloquent
{

    /**
     * The table/collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'quiz_reports';

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
        'quiz_report_id' => 'integer',
        'quiz_id' => 'integer',
        'user_id' => 'integer',
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
        return Sequence::getSequence('quiz_report_id');
    }

    /**
     * This function create/update the consolidated result for the quiz
     * based on the user attempts. This will called once the user complete
     * the attempt.
     *
     * @param Quiz $quiz Quiz collection object
     * @param int $user_id unique user id
     * @param int $attempt_id attempt unique identifier
     * @return bool
     */
    public static function calAttemptScore($quiz, $user_id, $attempt_id)
    {
        $report = self::where('quiz_id', '=', (int)$quiz->quiz_id)
            ->where('user_id', '=', (int)$user_id)
            ->get();

        $attempts = QuizAttempt::where('user_id', '=', (int)$user_id)
            ->where('quiz_id', '=', (int)$quiz->quiz_id)
            ->where('status', '=', 'CLOSED')
            ->orderby('completed_on', 'desc')
            ->get();

        $lastAttempt = isset($attempts[0]) ? $attempts[0] : [];
        if (empty($lastAttempt)) {
            return false;
        }
        $last_attempt_data = QuizAttemptData::where('quiz_id', '=', (int)$quiz->quiz_id)
            ->where('attempt_id', '=', (int)$attempt_id)
            ->where('user_id', '=', (int)$user_id)
            ->get();
        $attempt_ques_id = $last_attempt_data->where('answer_status', '')
            ->lists('question_id')
            ->all();
        $attempt_ques_id_temp = $last_attempt_data->where('status', 'NOT_VIEWED')
            ->lists('question_id')
            ->all();
        $for_skip = array_unique(array_merge($attempt_ques_id_temp, $attempt_ques_id));
        $correc_ques_id = $last_attempt_data->where('answer_status', 'CORRECT')->lists('question_id')->all();
        $incorrec_ques_id = $last_attempt_data
            ->where('answer_status', 'INCORRECT')->lists('question_id')->all();
        $for_incorrect = array_diff($incorrec_ques_id, $for_skip);

        $countCorrect = count($correc_ques_id);
        $countIncorrect = count($for_incorrect);
        $attemptQesCount = count($for_skip) +
            $countCorrect +
            $countIncorrect;
        $attemptQesCount = $attemptQesCount - count($attempt_ques_id_temp);
        $accuracy = $countCorrect / (($countCorrect + $countIncorrect) > 1 ?
                ($countCorrect + $countIncorrect) : 1) * 100;

        $speed = $lastAttempt->started_on->diffInSeconds($lastAttempt->completed_on) /
            ($attemptQesCount > 1 ?
                $attemptQesCount : 1);
        $sum_total = $attempts->sum('total_mark');
        $sum_obtained = $attempts->sum('obtained_mark');

        if ($report->isEmpty()) {
            $param = [
                'quiz_report_id' => self::getNextSequence(),
                'quiz_id' => (int)$quiz->quiz_id,
                'quiz_name' => $quiz->quiz_name,
                'quiz_type' => (isset($quiz->practice_quiz) && $quiz->practice_quiz) ?
                    'practice' : 'mock',
                'user_id' => (int)$user_id,
                'attempts' => $attempts->count(),
                'sum_of_total_mark' => $sum_total,
                'sum_of_obtained_mark' => $sum_obtained,
                'quiz_avg_percent' => (($sum_total != 0) ? round(($sum_obtained / $sum_total) * 100, 1) : 0),
                'speed' => $speed,
                'accuracy' => $accuracy,
                'created_at' => time(),
            ];
            self::insert($param);
        } else {
            $param = [
                'quiz_name' => $quiz->quiz_name,
                'quiz_type' => (isset($quiz->practice_quiz) && $quiz->practice_quiz) ?
                    'practice' : 'mock',
                'attempts' => $attempts->count(),
                'sum_of_total_mark' => $sum_total,
                'sum_of_obtained_mark' => $sum_obtained,
                'quiz_avg_percent' => (($sum_total != 0) ? round(($sum_obtained / $sum_total) * 100, 1) : 0),
                'speed' => $speed,
                'accuracy' => $accuracy,
                'updated_at' => time(),
            ];
            self::where('quiz_report_id', '=', $report->first()->quiz_report_id)
                ->update($param);
        }
    }

    public static function getSpecDayQuizReports($start_time = 0, $end_time = 0)
    {
        if ($start_time > 0 && $end_time > 0) {
            return self::where(function ($query) use ($start_time, $end_time) {
                $query->WhereBetween('created_at', [$start_time, $end_time])
                    ->orWhereBetween('updated_at', [$start_time, $end_time]);
            })
                ->get()
                ->toArray();
        }
    }

    /**
     * copyToDimensionQuizReportTbl
     * @return array
     */
    public static function copyToDimensionQuizReportTbl()
    {
        $result = QuizReport::raw(function ($table) {
            return $table->aggregate([
                [
                    '$match' => [
                        'quiz_id' => ['$exists'=>true]
                    ]
                ],
                [
                    '$out' => 'dim_quiz_reports'
                ]
            ]);
        });
        return $result;
    }
}
