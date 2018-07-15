<?php

namespace App\Providers\Model\RolesAndPermissions;

use App\Model\Module\Repository\IModuleRepository;
use App\Model\Module\Repository\ModuleRepository;
use Illuminate\Support\ServiceProvider;

class ModuleRepositoryProvider extends ServiceProvider
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
        $this->app->bind(IModuleRepository::class, ModuleRepository::class);
    }
}
