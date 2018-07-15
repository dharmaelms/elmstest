<?php

namespace App\Providers\Service\TransactionDetail;

use Illuminate\Support\ServiceProvider;

/**
 * class TransactionDetailServiceProvider
 * @package App\Providers\Service\TransactionDetail
 */

class TransactionDetailServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        $this->app->bind(
            \App\Services\TransactionDetail\ITransactionDetailService::class,
            \App\Services\TransactionDetail\TransactionDetailService::class
        );
    }
}