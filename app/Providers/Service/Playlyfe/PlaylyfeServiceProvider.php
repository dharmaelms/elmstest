<?php

namespace App\Providers\Service\Playlyfe;

use Illuminate\Support\ServiceProvider;

class PlaylyfeServiceProvider extends ServiceProvider
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
            \App\Services\Playlyfe\IPlaylyfeService::class,
            \App\Services\Playlyfe\PlaylyfeService::class
        );
    }
}
