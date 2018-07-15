<?php

namespace App\Providers\Service\Courses;

use Illuminate\Support\ServiceProvider;

/**
 * Class PopularServiceProvider
 *
 * @package App\Providers\Service\Courses
 */
class PopularServiceProvider extends ServiceProvider
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
            \App\Services\Courses\Popular\IPopularService::class,
            \App\Services\Courses\Popular\PopularService::class
        );
    }
}
