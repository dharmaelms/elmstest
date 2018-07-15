<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Model\QuizAttempt;
use App\Model\QuizAttemptData;
 
class AddDetailsFieldToQuizAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $attempts = QuizAttempt::where('status', 'OPENED')->get();
        $attempts->each(function ($attempt) {
            $attemptdata = QuizAttemptData::where('attempt_id', (int)$attempt->attempt_id)->get();
            if ($attemptdata->count() > 0) {
                $viewed = $attemptdata->pluck('question_id');
                if (!empty($attempt->questions)) {
                    $not_viewed = array_diff($attempt->questions, $viewed->all());
                    $answered = $attemptdata->whereIn('answer_status', ['CORRECT', 'INCORRECT'])->pluck('question_id');
                    $reviewed = $attemptdata->where('mark_review', true)->pluck('question_id');
                    $details = [
                        'not_viewed' => $not_viewed,
                        'reviewed' => $reviewed->all(),
                        'answered' => $answered->all(),
                        'viewed' => $viewed->all(),
                    ];
                    QuizAttempt::where('attempt_id', (int)$attempt->attempt_id)->update(['details' => $details]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $attempts = QuizAttempt::where('details', 'exists', true)->get();
        $attempts->each(function ($attempt) {
            QuizAttempt::where('attempt_id', (int)$attempt->attempt_id)->unset('details');
        });
    }
}
