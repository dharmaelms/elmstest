<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Model\Quiz;

class InsertIsScoreDisplayFieldInQuiz extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Quiz::where('type', '!=', 'QUESTION_GENERATOR')
            ->where('is_score_display', 'exists', false)
            ->update(['is_score_display' => true]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Quiz::where('type', '!=', 'QUESTION_GENERATOR')
            ->where('is_score_display', 'exists', true)
            ->unset('is_score_display');
    }
}
