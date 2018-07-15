<?php

namespace App\Providers\Model\Catalog;

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
            \App\Model\Catalog\AccessControl\Repository\IAccessControlRepository::class,
            \App\Model\Catalog\AccessControl\Repository\AccessControlRepository::class
        );
    }
}
