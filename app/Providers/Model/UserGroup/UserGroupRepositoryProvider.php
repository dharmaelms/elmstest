<?php

namespace App\Providers\Model\UserGroup;

use App\Model\UserGroup\Repository\IUserGroupRepository;
use App\Model\UserGroup\Repository\UserGroupRepository;
use Illuminate\Support\ServiceProvider;

class UserGroupRepositoryProvider extends ServiceProvider
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
        $this->app->bind(IUserGroupRepository::class, UserGroupRepository::class);
    }
}
