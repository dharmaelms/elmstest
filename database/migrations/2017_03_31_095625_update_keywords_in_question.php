<?php

use App\Model\Question;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateKeywordsInQuestion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $questions = Question::where('status', 'ACTIVE')->get(['question_id', 'keywords']);
        foreach ($questions as $question) {
            if (!empty($question['keywords'])) {
                $keywords = [];
                foreach (array_flatten($question['keywords']) as $keyword) {
                    $keywords[] = explode(' ', $keyword);
                }
                $keywords = array_flatten($keywords);
                Question::where('question_id', (int)$question->question_id)->update(['keywords' => $keywords]);
            }
        };
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $questions = Question::where('status', 'ACTIVE')->get(['question_id', 'keywords']);
        foreach ($questions as $question) {
            if (!empty($question['keywords'])) {
                $keywords = [];
                foreach (array_flatten($question['keywords']) as $keyword) {
                    $keywords[] = explode(' ', $keyword);
                }
                $keywords = array_flatten($keywords);
                Question::where('question_id', (int)$question->question_id)->update(['keywords' => $keywords]);
            }
        };
    }
}
