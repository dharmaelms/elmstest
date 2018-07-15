<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Model\Quiz;
use App\Model\QuizAttempt;
use App\Model\QuizAttemptData;
use App\Enums\Quiz\QuizType;
use Carbon\Carbon;

class QuestionGeneratorAttemptCountAndDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $quizzes = Quiz::where('type', QuizType::QUESTION_GENERATOR)->get();
        $quizzes->each(function ($quiz) {
            $attempts = QuizAttempt::where('quiz_id', (int)$quiz->quiz_id)->where('status', 'CLOSED')->get();
            $attempts->each(function ($attempt) {
                $attemptdata = QuizAttemptData::where('attempt_id', (int)$attempt->attempt_id)->get();
                $data = [];
                $data['correct_answer_count'] = $attemptdata->where('answer_status', 'CORRECT')->count();
                $data['in_correct_answer_count'] = $attemptdata->where('answer_status', 'INCORRECT')->count();
                $data['un_attempted_question_count'] = 0;
                $data['started_on'] = Carbon::createFromTimestamp($attempt->started_on->timestamp)->timestamp;
                $data['completed_on'] = Carbon::createFromTimestamp($attempt->completed_on->timestamp)->timestamp;
                QuizAttempt::where('attempt_id', (int)$attempt->attempt_id)->update($data);
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
          
    }
}
