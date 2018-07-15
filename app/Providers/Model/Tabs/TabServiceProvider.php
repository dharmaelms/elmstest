<?php

namespace App\Providers\Model\Tabs;

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
            \App\Model\Tabs\Repository\ITabRepository::class,
            \App\Model\Tabs\Repository\TabRepository::class
        );
    }
}
