<?php

namespace App\Providers\Model\QuizAttempt;

use Illuminate\Support\ServiceProvider;

/**
 * Class QuizAttemptRepositoryProvider
 *
 * @package App\Providers\Model\QuizAttempt
 */
class QuizAttemptRepositoryProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(\App\Model\QuizAttempt\Repository\IQuizAttemptRepository::class, \App\Model\QuizAttempt\Repository\QuizAttemptRepository::class);
    }
}
