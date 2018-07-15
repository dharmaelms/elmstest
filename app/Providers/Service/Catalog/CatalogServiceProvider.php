<?php

namespace App\Providers\Service\Catalog;

use Illuminate\Support\ServiceProvider;

class CatalogServiceProvider extends ServiceProvider
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
            \App\Services\Catalog\CatList\ICatalogService::class,
            \App\Services\Catalog\CatList\CatalogService::class
        );
    }
}
