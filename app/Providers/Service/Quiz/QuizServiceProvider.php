<?php

namespace App\Providers\Service\Quiz;

use Illuminate\Support\ServiceProvider;

/**
 * Class QuizServiceProvider
 *
 * @package App\Providers\Service\Quiz
 */
class QuizServiceProvider extends ServiceProvider
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
        $this->app->bind(\App\Services\Quiz\IQuizService::class, \App\Services\Quiz\QuizService::class);
    }
}
