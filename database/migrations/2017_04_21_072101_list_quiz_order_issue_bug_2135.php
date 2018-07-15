<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Model\Quiz;
use App\Libraries\Timezone;

class ListQuizOrderIssueBug2135 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $quizzes = Quiz::all();
        $quizzes->each(function ($quiz) {
            $data['created_at'] = Timezone::getTimeStamp($quiz->created_at);
            $data['updated_at'] = Timezone::getTimeStamp($quiz->created_at);
            Quiz::where('quiz_id', (int)$quiz->quiz_id)->update($data);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $quizzes = Quiz::all();
        $quizzes->each(function ($quiz) {
            $data['created_at'] = Timezone::getTimeStamp($quiz->created_at);
            $data['updated_at'] = Timezone::getTimeStamp($quiz->created_at);
            Quiz::where('quiz_id', (int)$quiz->quiz_id)->update($data);
        });
    }
}
