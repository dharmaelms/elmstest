<?php

namespace App\Providers\Model\Elastic;

use Illuminate\Support\ServiceProvider;

/**
 * Class ElasticRepositoryProvider
 *
 * @package App\Providers\Model\Elastic
 */
class ElasticRepositoryProvider extends ServiceProvider
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
        $this->app->bind(\App\Model\Elastic\Repository\IElasticRepository::class, \App\Model\Elastic\Repository\ElasticRepository::class);
    }
}
