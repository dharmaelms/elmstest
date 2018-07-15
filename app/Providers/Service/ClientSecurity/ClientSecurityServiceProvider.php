<?php

namespace App\Providers\Service\ClientSecurity;

use Illuminate\Support\ServiceProvider;

/**
 * Class ClientSecurityServiceProvider
 *
 * @package App\Providers\Service\ClientSecurity
 */
class ClientSecurityServiceProvider extends ServiceProvider
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
            \App\Services\ClientSecurity\IClientSecurityService::class,
            \App\Services\ClientSecurity\ClientSecurityService::class
        );
    }
}
