<?php

namespace App\Providers\Model\MyActivity;

use Illuminate\Support\ServiceProvider;

/**
 * Class MyActivityServiceProvider
 *
 * @package App\Providers\Model\MyActivity
 */
class MyActivityServiceProvider extends ServiceProvider
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
            \App\Model\MyActivity\IMyActivityRepository::class,
            \App\Model\MyActivity\MyActivityRepository::class
        );
    }
}
