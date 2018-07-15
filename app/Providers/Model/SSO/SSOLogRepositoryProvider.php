<?php

namespace App\Providers\Model\SSO;

use Illuminate\Support\ServiceProvider;

/**
 * Class SSOLogRepositoryRepositoryProvider
 *
 * @package App\Providers\Model\SSO
 */
class SSOLogRepositoryProvider extends ServiceProvider
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
        $this->app->bind(
            \App\Model\SSO\Repository\ISSOLogRepository::class,
            \App\Model\SSO\Repository\SSOLogRepository::class
        );
    }
}
