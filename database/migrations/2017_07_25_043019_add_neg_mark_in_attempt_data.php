<?php

use App\Helpers\Quiz\QuizHelper;
use App\Model\Question;
use App\Model\QuizAttempt;
use App\Model\QuizAttemptData;
use App\Model\QuizAttemptData\Repository\IQuizAttemptDataRepository;
use Illuminate\Database\Migrations\Migration;

class AddNegMarkInAttemptData extends Migration
{

    public $attempt_data_repo;

    public function __construct()
    {
        $this->attempt_data_repo = App::make(IQuizAttemptDataRepository::class);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Log::debug('Attempt data neg starts - up');
        $attempts = QuizAttempt::Where('status', 'OPENED')
            ->where('type', 'exists', false)
            ->get();
        $attempts->each(function ($attempt) {
            Log::debug('Attempt id ' . $attempt->attempt_id);
           $answers = QuizAttemptData::where('attempt_id', (int)$attempt->attempt_id)->get();
           $answers->each(function ($answer) use ($attempt) {
               if ($answer->user_response == '') {
                   $answer->default_negative_mark_percentage = $attempt->un_attempt_neg_mark;
                   $answer->obtained_negative_mark = QuizHelper::roundOfNumber(($attempt->un_attempt_neg_mark/100) * $answer->question_mark);
               }
               if ($answer->user_response != $answer->correct_answer) {
                   $answer->default_negative_mark_percentage = $attempt->attempt_neg_mark;
                   $answer->obtained_negative_mark = QuizHelper::roundOfNumber(($attempt->attempt_neg_mark/100) * $answer->question_mark);
               }
               if ($answer->user_response == $answer->correct_answer) {
                   $answer->obtained_mark = (float)$answer->question_mark;
               }
               $answer->save();
           });
            QuizAttemptData::where('attempt_id', (int)$attempt->attempt_id)->whereNotIn('question_id', array_get($attempt, 'questions', [null]))->delete();
        });
        Log::debug('Attempt data neg finished - up');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Log::debug('Attempt data neg starts - down');
        $attempts = QuizAttempt::Where('status', 'OPENED')
            ->where('type', 'exists', false)
            ->get();
        $attempts->each(function ($attempt) {
            Log::debug('Attempt id ' . $attempt->attempt_id);
            QuizAttemptData::where('attempt_id', (int)$attempt->attempt_id)->unset(['default_negative_mark_percentage', 'obtained_negative_mark', 'obtained_mark']);
        });
        Log::debug('Attempt data neg finished - down');
    }
}
