<?php

namespace App\Providers\Service\MyActivity;

use Illuminate\Support\ServiceProvider;

/**
 * Class MyActivityServiceProvider
 *
 * @package App\Providers\Service\MyActivity
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
            \App\Services\MyActivity\IMyActivityService::class,
            \App\Services\MyActivity\MyActivityService::class
        );
    }
}
