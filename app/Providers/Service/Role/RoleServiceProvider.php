<?php

namespace App\Providers\Service\Role;

use App\Services\Role\IRoleService;
use App\Services\Role\RoleService;
use Illuminate\Support\ServiceProvider;

class RoleServiceProvider extends ServiceProvider
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
        $this->app->bind(IRoleService::class, RoleService::class);
    }
}
