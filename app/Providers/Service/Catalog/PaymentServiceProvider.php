<?php

namespace App\Providers\Service\Catalog;

use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
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
            \App\Services\Catalog\Payment\IPaymentService::class,
            \App\Services\Catalog\Payment\PaymentService::class
        );
    }
}
