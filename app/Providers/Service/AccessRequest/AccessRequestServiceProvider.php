<?php

namespace App\Providers\Service\AccessRequest;

use Illuminate\Support\ServiceProvider;

/**
 * Class AccessRequestServiceProvider
 *
 * @package App\Providers\Service\AccessRequest
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
            \App\Services\AccessRequest\IAccessRequestService::class,
            \App\Services\AccessRequest\AccessRequestService::class
        );
    }
}
