<?php

namespace App\Providers\Model\Playlyfe;

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
            \App\Model\Playlyfe\Repository\IPlaylyfeRepository::class,
            \App\Model\Playlyfe\Repository\PlaylyfeRepository::class
        );
    }
}
