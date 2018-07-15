<?php

namespace App\Providers\Model\ClientSecurity;

use Illuminate\Support\ServiceProvider;

/**
 * Class ClientSecurityRepositoryProvider
 *
 * @package App\Providers\Model\ClientSecurity
 */
class ClientSecurityRepositoryProvider extends ServiceProvider
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
            \App\Model\ClientSecurity\Repository\IClientSecurityRepository::class,
            \App\Model\ClientSecurity\Repository\ClientSecurityRepository::class
        );
    }
}
