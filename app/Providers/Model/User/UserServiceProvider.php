<?php

namespace App\Providers\Model\User;

use Illuminate\Support\ServiceProvider;

/**
 * class UserServiceProvider
 * @package App\Providers\Model\User
 */
class UserServiceProvider extends ServiceProvider
{
    /**
     * [boot description]
     * @return [type] [description]
     */
    public function boot()
    {
    }

    /**
     * [register description]
     * @return [type] [description]
     */
    public function register()
    {
        $this->app->bind(
            \App\Model\User\Repository\IUserRepository::class,
            \App\Model\User\Repository\UserRepository::class
        );
    }
}

