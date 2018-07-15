<?php

namespace App\Providers\Model\TransactionDetail;

use Illuminate\Support\ServiceProvider;

/**
 * class TransactionDetailServiceProvider
 * @package App\Providers\Model\TransactionDetail
 */

class TransactionDetailServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        $this->app->bind(
            \App\Model\TransactionDetail\Repository\ITransactionDetailRepository::class,
            \App\Model\TransactionDetail\Repository\TransactionDetailRepository::class
        );
    }
}