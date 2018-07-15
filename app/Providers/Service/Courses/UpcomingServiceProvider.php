<?php

namespace App\Providers\Service\Courses;

use Illuminate\Support\ServiceProvider;

/**
 * Class UpcomingServiceProvider
 *
 * @package App\Providers\Service\Courses
 */
class UpcomingServiceProvider extends ServiceProvider
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
            \App\Services\Courses\Upcoming\IUpcomingService::class,
            \App\Services\Courses\Upcoming\UpcomingService::class
        );
    }
}
