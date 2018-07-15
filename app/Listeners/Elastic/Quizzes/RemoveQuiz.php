<?php

namespace App\Listeners\Elastic\Quizzes;

use App\Events\Elastic\Quizzes\QuizRemoved;
use App\Jobs\Elastic\Quizzes\DeleteQuiz;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class RemoveQuiz implements ShouldQueue
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
     * @param QuizRemoved $quiz
     * @return void
     */
    public function handle(QuizRemoved $quiz)
    {
        dispatch(new DeleteQuiz($quiz->quiz_id));
    }
}
