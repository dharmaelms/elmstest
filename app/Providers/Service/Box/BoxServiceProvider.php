<?php

namespace App\Providers\Service\Box;

use Illuminate\Support\ServiceProvider;
use App\Services\Box\IBoxService;
use App\Services\Box\V2\BoxService;

class BoxServiceProvider extends ServiceProvider
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
            IBoxService::class,
            BoxService::class
        );
    }
}
