<?php

namespace App\Providers\Service\Event;

use Illuminate\Support\ServiceProvider;

/**
 * Class EventServiceProvider
 *
 * @package App\Providers\Service\Event
 */
class EventServiceProvider extends ServiceProvider
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
        $this->app->bind(\App\Services\Event\IEventService::class, \App\Services\Event\EventService::class);
    }
}
