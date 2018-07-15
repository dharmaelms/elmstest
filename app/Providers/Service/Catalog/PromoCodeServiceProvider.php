<?php

namespace App\Providers\Service\Catalog;

use Illuminate\Support\ServiceProvider;

class PromoCodeServiceProvider extends ServiceProvider
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
            \App\Services\Catalog\Promocode\IPromoCodeService::class,
            \App\Services\Catalog\Promocode\PromoCodeService::class
        );
    }
}
