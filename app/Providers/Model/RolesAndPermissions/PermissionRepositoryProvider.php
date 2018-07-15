<?php

namespace App\Providers\Model\RolesAndPermissions;

use App\Model\RolesAndPermissions\Repository\IPermissionRepository;
use App\Model\RolesAndPermissions\Repository\PermissionRepository;
use Illuminate\Support\ServiceProvider;

class PermissionRepositoryProvider extends ServiceProvider
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
        $this->app->bind(IPermissionRepository::class, PermissionRepository::class);
    }
}
