<?php

namespace App\Listeners\Elastic\Quizzes;

use App\Events\Elastic\Quizzes\QuizAssigned;
use App\Jobs\Elastic\Quizzes\AssignQuiz;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AssignUser implements ShouldQueue
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
    public function handle(QuizAssigned $quiz)
    {
        dispatch(new AssignQuiz($quiz->quiz_id));
    }
}
