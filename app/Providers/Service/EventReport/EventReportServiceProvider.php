<?php
namespace App\Providers\Service\EventReport;

use Illuminate\Support\ServiceProvider;

/**
 * Class EventServiceProvider
 *
 * @package App\Providers\Service\EventReport
 */
class EventReportServiceProvider extends ServiceProvider
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
        $this->app->bind(\App\Services\EventReport\IEventReportService::class, \App\Services\EventReport\EventReportService::class);
    }
}
