<?php

namespace App\Providers\Model\UserGroup;

use Illuminate\Support\ServiceProvider;

/**
 * class UserGroupServiceProvider
 * @package App\Providers\Model\UserGroup
 */
class UserGroupServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     * @return void
     */
    public function boot()
    {
    }

    /**
     * [register binding interface and implementation class]
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \App\Model\UserGroup\Repository\IUserGroupRepository::class,
            \App\Model\UserGroup\Repository\UserGroupRepository::class
        );
    }
}