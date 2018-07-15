<?php

namespace App\Providers\Service\SSO;

use Illuminate\Support\ServiceProvider;

/**
 * Class SSOServiceProvider
 *
 * @package App\Providers\Service\SSO
 */
class SSOServiceProvider extends ServiceProvider
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
        $this->app->bind(\App\Services\SSO\ISSOService::class, \App\Services\SSO\SSOService::class);
    }
}
