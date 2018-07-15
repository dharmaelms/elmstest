<?php

namespace App\Providers\Model\Courses;

use Illuminate\Support\ServiceProvider;

/**
 * Class PopularServiceProvider
 *
 * @package App\Providers\Model\Courses
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
            \App\Model\Courses\Popular\Repository\IPopularRepository::class,
            \App\Model\Courses\Popular\Repository\PopularRepository::class
        );
    }
}
