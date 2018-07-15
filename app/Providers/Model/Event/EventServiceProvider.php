<?php

namespace App\Providers\Model\Event;

use Illuminate\Support\ServiceProvider;

/**
 * Class EventServiceProvider
 *
 * @package App\Providers\Model\Event
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
        $this->app->bind(\App\Model\Event\IEventRepository::class, \App\Model\Event\EventRepository::class);
    }
}
