<?php

namespace App\Providers\Service\Catalog;

use Illuminate\Support\ServiceProvider;

class AccessControlServiceProvider extends ServiceProvider
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
            \App\Services\Catalog\AccessControl\IAccessControlService::class,
            \App\Services\Catalog\AccessControl\AccessControlService::class
        );
    }
}
