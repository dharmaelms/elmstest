<?php

namespace App\Providers\Service\QuizAttemptData;

use Illuminate\Support\ServiceProvider;

/**
 * Class QuizAttemptDataServiceProvider
 *
 * @package App\Providers\Service\QuizAttemptData
 */
class QuizAttemptDataServiceProvider extends ServiceProvider
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
        $this->app->bind(\App\Services\QuizAttemptData\IQuizAttemptDataService::class, \App\Services\QuizAttemptData\QuizAttemptService::class);
    }
}
