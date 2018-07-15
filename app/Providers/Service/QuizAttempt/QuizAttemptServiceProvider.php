<?php

namespace App\Providers\Service\QuizAttempt;

use Illuminate\Support\ServiceProvider;

/**
 * Class QuizAttemptServiceProvider
 *
 * @package App\Providers\Service\QuizAttempt
 */
class QuizAttemptServiceProvider extends ServiceProvider
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
        $this->app->bind(\App\Services\QuizAttempt\IQuizAttemptService::class, \App\Services\QuizAttempt\QuizAttemptService::class);
    }
}
