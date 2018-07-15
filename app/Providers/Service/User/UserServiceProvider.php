<?php

namespace App\Providers\Service\User;

use Illuminate\Support\ServiceProvider;

/**
 * class UserServiceProvider
 * @package App\Providers\Service\User
 */
class UserServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     * @return void
     * [boot description]
     * @return [void]
     */
    public function boot()
    {
    }

    /**
     * [register description]
     * @return [void]
     */
    public function register()
    {
        $this->app->bind(
            \App\Services\User\IUserService::class,
            \App\Services\User\UserService::class
        );
    }

}

