<?php

namespace App\Providers\Model\QuizAttemptData;

use Illuminate\Support\ServiceProvider;

/**
 * Class QuizAttemptDataRepositoryProvider
 *
 * @package App\Providers\Model\QuizAttemptData
 */
class QuizAttemptDataRepositoryProvider extends ServiceProvider
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
        $this->app->bind(\App\Model\QuizAttemptData\Repository\IQuizAttemptDataRepository::class, \App\Model\QuizAttemptData\Repository\QuizAttemptDataRepository::class);
    }
}
