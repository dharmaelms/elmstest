<?php

namespace App\Providers\Service\Survey;

use Illuminate\Support\ServiceProvider;

/**
 * Class PostServiceProvider
 *
 * @package App\Providers\Service\Survey
 */
class SurveyServiceProvider extends ServiceProvider
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
            \App\Services\Survey\ISurveyService::class,
            \App\Services\Survey\SurveyService::class
        );
        $this->app->bind(
            \App\Services\Survey\ISurveyQuestionService::class,
            \App\Services\Survey\SurveyQuestionService::class
        );

        $this->app->bind(
            \App\Services\Survey\ISurveyAttemptService::class,
            \App\Services\Survey\SurveyAttemptService::class
        );

        $this->app->bind(
            \App\Services\Survey\ISurveyAttemptDataService::class,
            \App\Services\Survey\SurveyAttemptDataService::class
        );
    }
}
