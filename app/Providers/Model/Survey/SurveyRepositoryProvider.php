<?php

namespace App\Providers\Model\Survey;

use Illuminate\Support\ServiceProvider;

/**
 * Class PostRepositoryProvider
 *
 * @package App\Providers\Model\Survey
 */
class SurveyRepositoryProvider extends ServiceProvider
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
            \App\Model\Survey\Repository\ISurveyRepository::class,
            \App\Model\Survey\Repository\SurveyRepository::class
        );
        $this->app->bind(
            \App\Model\Survey\Repository\ISurveyQuestionRepository::class,
            \App\Model\Survey\Repository\SurveyQuestionRepository::class
        );

        $this->app->bind(
            \App\Model\Survey\Repository\ISurveyAttemptRepository::class,
            \App\Model\Survey\Repository\SurveyAttemptRepository::class
        );

        $this->app->bind(
            \App\Model\Survey\Repository\ISurveyAttemptDataRepository::class,
            \App\Model\Survey\Repository\SurveyAttemptDataRepository::class
        );
    }
}
