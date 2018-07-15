<?php

namespace App\Providers\Model\AccessRequest;

use Illuminate\Support\ServiceProvider;

/**
 * Class AccessRequestServiceProvider
 *
 * @package App\Providers\Model\AccessRequest
 */
class AccessRequestServiceProvider extends ServiceProvider
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
            \App\Model\AccessRequest\IAccessRequestRepository::class,
            \App\Model\AccessRequest\AccessRequestRepository::class
        );
    }
}
