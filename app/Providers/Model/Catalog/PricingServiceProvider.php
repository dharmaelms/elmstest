<?php

namespace App\Providers\Model\Catalog;

use Illuminate\Support\ServiceProvider;

class PricingServiceProvider extends ServiceProvider
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
            \App\Model\Catalog\Pricing\Repository\IPricingRepository::class,
            \App\Model\Catalog\Pricing\Repository\PricingRepository::class
        );
    }
}
