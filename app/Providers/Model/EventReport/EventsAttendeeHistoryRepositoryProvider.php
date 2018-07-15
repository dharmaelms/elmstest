<?php
namespace App\Providers\Model\EventReport;

use Illuminate\Support\ServiceProvider;

/**
 * Class EventAttendeeHistoryRepositoryProvider
 *
 * @package App\Providers\Model\EventReport
 */
class EventsAttendeeHistoryRepositoryProvider extends ServiceProvider
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
        $this->app->bind(\App\Model\EventReport\Repository\IEventsAttendeeHistoryRepository::class, \App\Model\EventReport\Repository\EventsAttendeeHistoryRepository::class);
    }
}
