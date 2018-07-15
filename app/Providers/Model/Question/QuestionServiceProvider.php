<?php

namespace App\Providers\Model\Question;

use Illuminate\Support\ServiceProvider;

class QuestionServiceProvider extends ServiceProvider
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
        $this->app->bind(
            \App\Model\Question\Repository\IQuestionRepository::class,
            \App\Model\Question\Repository\QuestionRepository::class
        );

        $this->app->bind(
            \App\Model\Question\Repository\IQuestionBankRepository::class,
            \App\Model\Question\Repository\QuestionBankRepository::class
        );
    }
}
