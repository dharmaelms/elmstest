<?php
namespace App\Providers\Model\EventReport;

use Illuminate\Support\ServiceProvider;

/**
 * Class EventHistoryRepositoryProvider
 *
 * @package App\Providers\Model\EventReport
 */
class EventsHistoryRepositoryProvider extends ServiceProvider
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
        $this->app->bind(\App\Model\EventReport\Repository\IEventsHistoryRepository::class, \App\Model\EventReport\Repository\EventsHistoryRepository::class);
    }
}
