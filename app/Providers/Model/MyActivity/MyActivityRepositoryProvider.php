<?php

namespace App\Providers\Model\MyActivity;

use Illuminate\Support\ServiceProvider;

/**
 * Class EventServiceProvider
 *
 * @package App\Providers\Model\MyActivity
 */
class MyActivityRepositoryProvider extends ServiceProvider
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
        $this->app->bind(\App\Model\MyActivity\Repository\IMyActivityRepository::class, \App\Model\MyActivity\Repository\MyActivityRepository::class);
    }
}
