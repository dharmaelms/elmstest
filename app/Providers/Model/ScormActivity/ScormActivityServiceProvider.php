<?php

namespace App\Providers\Model\ScormActivity;

use Illuminate\Support\ServiceProvider;

/**
 * Class AccessRequestServiceProvider
 *
 * @package App\Providers\Model\AccessRequest
 */
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
            \App\Model\ScormActivity\IScormActivityRepository::class,
            \App\Model\ScormActivity\ScormActivityRepository::class
        );
    }
}
