<?php

namespace App\Providers\Model\Report;

use Illuminate\Support\ServiceProvider;

/**
 * Class ReportRepositoryProvider
 *
 * @package App\Providers\Service\Post
 */
class ReportRepositoryProvider extends ServiceProvider
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
            \App\Model\Report\IDimensionAnnouncementsRepository::class,
            \App\Model\Report\DimensionAnnouncementsRepository::class
        );
        $this->app->bind(
            \App\Model\Report\IDimensionChannelRepository::class,
            \App\Model\Report\DimensionChannelRepository::class
        );
        $this->app->bind(
            \App\Model\Report\IDimensionChannelUserQuizRepository::class,
            \App\Model\Report\DimensionChannelUserQuizRepository::class
        );
        $this->app->bind(
            \App\Model\Report\IDimensionUserRepository::class,
            \App\Model\Report\DimensionUserRepository::class
        );
        $this->app->bind(
            \App\Model\Report\IDirectQuizPerformanceByIndividualQuestionRepository::class,
            \App\Model\Report\DirectQuizPerformanceByIndividualQuestionRepository::class
        );
        $this->app->bind(
            \App\Model\Report\IDirectQuizPerformanceByIndividualQuestionSummaryRepository::class,
            \App\Model\Report\DirectQuizPerformanceByIndividualQuestionSummaryRepository::class
        );
        $this->app->bind(
            \App\Model\Report\IFactChannelUserQuizRepository::class,
            \App\Model\Report\FactChannelUserQuizRepository::class
        );
        $this->app->bind(
            \App\Model\Report\IQuizPerformanceByIndividualQuestionRepository::class,
            \App\Model\Report\QuizPerformanceByIndividualQuestionRepository::class
        );
        $this->app->bind(
            \App\Model\Report\IQuizPerformanceByIndividualQuestionSummaryRepository::class,
            \App\Model\Report\QuizPerformanceByIndividualQuestionSummaryRepository::class
        );
        $this->app->bind(
            \App\Model\Report\ITillQuizPerformanceRepository::class,
            \App\Model\Report\TillQuizPerformanceRepository::class
        );
    }
}
