<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Model\Quiz;

class LowercaseToUppercaseOfQuizCutOffFormat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $quizzes = Quiz::where('cut_off_format', 'exists', true)->get(['quiz_id', 'cut_off_format']);
        $quizzes->each(function ($quiz) {
            $cut_off_format = strtoupper($quiz->cut_off_format);
            Quiz::where('quiz_id', (int)$quiz->quiz_id)->update(['cut_off_format' => $cut_off_format]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $quizzes = Quiz::where('cut_off_format', 'exists', true)->get(['quiz_id', 'cut_off_format']);
        $quizzes->each(function ($quiz) {
            $cut_off_format = strtolower($quiz->cut_off_format);
            Quiz::where('quiz_id', (int)$quiz->quiz_id)->update(['cut_off_format' => $cut_off_format]);
        });
    }
}
