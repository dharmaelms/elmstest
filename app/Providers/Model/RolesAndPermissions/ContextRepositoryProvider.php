<?php

namespace App\Providers\Model\RolesAndPermissions;

use App\Model\RolesAndPermissions\Repository\ContextRepository;
use App\Model\RolesAndPermissions\Repository\IContextRepository;
use Illuminate\Support\ServiceProvider;

class ContextRepositoryProvider extends ServiceProvider
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
        $this->app->bind(IContextRepository::class, ContextRepository::class);
    }
}
