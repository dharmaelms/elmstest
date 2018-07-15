<?php

namespace App\Providers\Service\CustomFields;

use Illuminate\Support\ServiceProvider;

/**
 * Class CustomServiceProvider
 *
 * @package App\Providers\Service\CustomFields
 */
class CustomServiceProvider extends ServiceProvider
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
            \App\Services\CustomFields\ICustomService::class,
            \App\Services\CustomFields\CustomService::class
        );
    }
}
