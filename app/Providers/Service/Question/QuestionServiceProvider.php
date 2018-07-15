<?php

namespace App\Providers\Service\Question;

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
            \App\Services\Question\IQuestionService::class,
            \App\Services\Question\QuestionService::class
        );
    }
}
