<?php

use App\Model\Quiz;
use App\Model\QuizAttempt;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNegMarkInAttempt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Log::debug('Attempt neg started - up');
        $attempts = QuizAttempt::Where('status', 'OPENED')
            ->where('type', 'exists', false)
            ->get();
        $attempts->each(function ($attempt) {
            Log::debug('Attempt id ' . $attempt->attempt_id);
            $quiz = Quiz::where('quiz_id', (int)$attempt->quiz_id)->get();
            $attempt->attempt_neg_mark = array_get($quiz, 'attempt_neg_mark', 0);
            $attempt->un_attempt_neg_mark = array_get($quiz, 'un_attempt_neg_mark', 0);
            $attempt->save();
        });
        Log::debug('Attempt neg finished - up');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Log::debug('Attempt neg started - down');
        QuizAttempt::Where('status', 'OPENED')
           ->where('type', 'exists', false)
           ->unset(['attempt_neg_mark', 'un_attempt_neg_mark']);
        Log::debug('Attempt neg finished - up');
    }
}
