<?php

namespace App\Providers\Service\Module;

use App\Services\Module\IModuleService;
use App\Services\Module\ModuleService;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
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
        $this->app->bind(IModuleService::class, ModuleService::class);
    }
}
