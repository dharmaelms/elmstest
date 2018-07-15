<?php

namespace App\Providers\Model\CustomFields;

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
            \App\Model\CustomFields\Repository\ICustomRepository::class,
            \App\Model\CustomFields\Repository\CustomRepository::class
        );
    }
}
