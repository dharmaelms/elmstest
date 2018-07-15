<?php

namespace App\Providers\Model\Leadsquared;

use Illuminate\Support\ServiceProvider;

class LeadsquaredServiceProvider extends ServiceProvider
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
            \App\Model\Leadsquared\Repository\ILeadsquaredRepository::class,
            \App\Model\Leadsquared\Repository\LeadsquaredRepository::class
        );
    }
}
