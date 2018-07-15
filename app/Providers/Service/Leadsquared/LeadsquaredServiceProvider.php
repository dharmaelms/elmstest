<?php

namespace App\Providers\Service\Leadsquared;

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
            \App\Services\Leadsquared\ILeadsquaredService::class,
            \App\Services\Leadsquared\LeadsquaredService::class
        );
    }
}
