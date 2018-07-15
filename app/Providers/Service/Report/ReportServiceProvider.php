<?php

namespace App\Providers\Service\Report;

use Illuminate\Support\ServiceProvider;

/**
 * Class PostServiceProvider
 *
 * @package App\Providers\Service\Post
 */
class ReportServiceProvider extends ServiceProvider
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
            \App\Services\Report\IReportService::class,
            \App\Services\Report\ReportService::class
        );
        $this->app->bind(
            \App\Services\Report\IMongoBulkInUpService::class,
            \App\Services\Report\MongoBulkInUpService::class
        );

        $this->app->bind(
            \App\Services\Report\IDimensionTblService::class,
            \App\Services\Report\DimensionTblService::class
        );

        $this->app->bind(
            \App\Services\Report\IFactTblService::class,
            \App\Services\Report\FactTblService::class
        );

        $this->app->bind(
            \App\Services\Report\IExportReportService::class,
            \App\Services\Report\ExportReportService::class
        );
        $this->app->bind(
            \App\Services\Report\IDimensionAnnouncementsService::class,
            \App\Services\Report\DimensionAnnouncementsService::class
        );
        $this->app->bind(
            \App\Services\Report\IDimensionChannelService::class,
            \App\Services\Report\DimensionChannelService::class
        );
        $this->app->bind(
            \App\Services\Report\IDimensionChannelUserQuizService::class,
            \App\Services\Report\DimensionChannelUserQuizService::class
        );
        $this->app->bind(
            \App\Services\Report\IDimensionUserService::class,
            \App\Services\Report\DimensionUserService::class
        );
        $this->app->bind(
            \App\Services\Report\IDirectQuizPerformanceByIndividualQuestionService::class,
            \App\Services\Report\DirectQuizPerformanceByIndividualQuestionService::class
        );
        $this->app->bind(
            \App\Services\Report\IDirectQuizPerformanceByIndividualQuestionSummaryService::class,
            \App\Services\Report\DirectQuizPerformanceByIndividualQuestionSummaryService::class
        );
        $this->app->bind(
            \App\Services\Report\IQuizPerformanceByIndividualQuestionService::class,
            \App\Services\Report\QuizPerformanceByIndividualQuestionService::class
        );
        $this->app->bind(
            \App\Services\Report\IQuizPerformanceByIndividualQuestionSummaryService::class,
            \App\Services\Report\QuizPerformanceByIndividualQuestionSummaryService::class
        );
        $this->app->bind(
            \App\Services\Report\ITillContentReportService::class,
            \App\Services\Report\TillContentReportService::class
        );
    }
}
