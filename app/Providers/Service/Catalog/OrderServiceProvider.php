<?php

namespace App\Providers\Service\Catalog;

use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
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
            \App\Services\Catalog\Order\IOrderService::class,
            \App\Services\Catalog\Order\OrderService::class
        );
    }
}
