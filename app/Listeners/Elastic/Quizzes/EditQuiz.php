<?php

namespace App\Listeners\Elastic\Quizzes;

use App\Events\Elastic\Quizzes\QuizEdited;
use App\Jobs\Elastic\Quizzes\IndexQuiz;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class EditQuiz implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param QuizAdded $quiz
     * @return void
     */
    public function handle(QuizEdited $quiz)
    {
        dispatch(new IndexQuiz($quiz->quiz_id, false));
    }
}
