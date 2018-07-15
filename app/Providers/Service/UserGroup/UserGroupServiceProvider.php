<?php

namespace App\Providers\Service\UserGroup;

use App\Services\UserGroup\IUserGroupService;
use App\Services\UserGroup\UserGroupService;
use Illuminate\Support\ServiceProvider;

/**
 *  class UserGroupServiceProvider
 *  @package App\Providers\Service\UserGroup
 */
class UserGroupServiceProvider extends ServiceProvider
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
        $this->app->bind(IUserGroupService::class, UserGroupService::class);
    }
}
