<?php

namespace App\Providers\Model\Dams;

use Illuminate\Support\ServiceProvider;

class DamsServiceProvider extends ServiceProvider
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
            \App\Model\Dams\Repository\IDamsRepository::class,
            \App\Model\Dams\Repository\DamsRepository::class
        );
    }
}
