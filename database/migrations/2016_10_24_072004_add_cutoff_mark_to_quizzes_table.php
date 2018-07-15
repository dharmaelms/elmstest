<?php

use App\Model\Quiz;
use Illuminate\Database\Migrations\Migration;

class AddCutoffMarkToQuizzesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $quizzes = Quiz::where('cut_off_format', 'exists', false)
            ->where('cut_off', 'exists', true)
            ->get();
        if (!empty($quizzes)) {
            $quizzes->each(function ($quiz) {
                $data = [];
                $data['cut_off_format'] = 'mark';
                $data['cut_off_mark'] = $quiz->cut_off;
                Quiz::where('quiz_id', (int)$quiz->quiz_id)->update($data);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $quizzes = Quiz::where('cut_off_format', 'exists', true)
            ->where('cut_off', 'exists', true)
            ->get();
        if (!empty($quizzes)) {
            $quizzes->each(function ($quiz) {
                Quiz::where('quiz_id', (int)$quiz->quiz_id)->unset(['cut_off_format', 'cut_off_mark']);
            });
        }
    }
}
