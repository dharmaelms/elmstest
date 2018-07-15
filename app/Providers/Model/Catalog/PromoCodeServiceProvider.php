<?php

namespace App\Providers\Model\Catalog;

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
            \App\Model\Catalog\Promocode\Repository\IPromoCodeRepository::class,
            \App\Model\Catalog\Promocode\Repository\PromoCodeRepository::class
        );
    }
}
