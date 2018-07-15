<?php

namespace App\Providers\Service\Tabs;

use Illuminate\Support\ServiceProvider;

class TabServiceProvider extends ServiceProvider
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
            \App\Services\Tabs\ITabService::class,
            \App\Services\Tabs\TabService::class
        );
    }
}
