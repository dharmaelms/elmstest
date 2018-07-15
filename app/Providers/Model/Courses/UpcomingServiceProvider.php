<?php

namespace App\Providers\Model\Courses;

use Illuminate\Support\ServiceProvider;

/**
 * Class UpcomingServiceProvider
 *
 * @package App\Providers\Model\Courses
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
            \App\Model\Courses\Upcoming\Repository\IUpcomingRepository::class,
            \App\Model\Courses\Upcoming\Repository\UpcomingRepository::class
        );
    }
}
