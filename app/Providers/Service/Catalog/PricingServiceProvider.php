<?php

namespace App\Providers\Service\Catalog;

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
            \App\Services\Catalog\Pricing\IPricingService::class,
            \App\Services\Catalog\Pricing\PricingService::class
        );
    }
}
