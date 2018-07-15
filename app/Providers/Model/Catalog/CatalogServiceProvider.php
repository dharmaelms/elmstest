<?php

namespace App\Providers\Model\Catalog;

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
            \App\Model\Catalog\CatList\Repository\ICatalogListRepository::class,
            \App\Model\Catalog\CatList\Repository\CatalogListRepository::class
        );
    }
}
