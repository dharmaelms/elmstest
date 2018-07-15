<?php

namespace App\Providers\Model\RolesAndPermissions;

use App\Model\RolesAndPermissions\Repository\IRoleRepository;
use App\Model\RolesAndPermissions\Repository\RoleRepository;
use Illuminate\Support\ServiceProvider;

class RoleRepositoryProvider extends ServiceProvider
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
        $this->app->bind(IRoleRepository::class, RoleRepository::class);
    }
}
