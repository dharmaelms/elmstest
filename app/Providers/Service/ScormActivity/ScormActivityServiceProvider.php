<?php

namespace App\Providers\Service\ScormActivity;

use Illuminate\Support\ServiceProvider;

class ScormActivityServiceProvider extends ServiceProvider
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
            \App\Services\ScormActivity\IScormActivityService::class,
            \App\Services\ScormActivity\ScormActivityService::class
        );
    }
}
