<?php

namespace App\Providers\Service\DAMS;

use App\Services\DAMS\DAMsService;
use App\Services\DAMS\IDAMsService;
use Illuminate\Support\ServiceProvider;

class DAMsServiceProvider extends ServiceProvider
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
        $this->app->bind(IDAMsService::class, DAMsService::class);
    }
}
