<?php

namespace App\Providers\Model\Quiz;

use Illuminate\Support\ServiceProvider;

/**
 * Class QuizServiceProvider
 *
 * @package App\Providers\Model\Quiz
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
        $this->app->bind(\App\Model\Quiz\IQuizRepository::class, \App\Model\Quiz\QuizRepository::class);
    }
}
