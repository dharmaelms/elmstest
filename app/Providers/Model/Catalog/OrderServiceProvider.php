<?php

namespace App\Providers\Model\Catalog;

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
            \App\Model\Catalog\Order\Repository\IOrderRepository::class,
            \App\Model\Catalog\Order\Repository\OrderRepository::class
        );
    }
}
