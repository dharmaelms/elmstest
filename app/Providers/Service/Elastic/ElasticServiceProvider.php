<?php

namespace App\Providers\Service\Elastic;

use Illuminate\Support\ServiceProvider;

/**
 * Class ElasticServiceProvider
 *
 * @package App\Providers\Service\Elastic
 */
class ElasticServiceProvider extends ServiceProvider
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
        $this->app->bind(\App\Services\Elastic\IElasticService::class, \App\Services\Elastic\ElasticService::class);
    }
}
