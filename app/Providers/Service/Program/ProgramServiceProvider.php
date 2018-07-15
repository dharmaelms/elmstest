<?php

namespace App\Providers\Service\Program;

use Illuminate\Support\ServiceProvider;

/**
 * Class ProgramServiceProvider
 *
 * @package App\Providers\Service\Program
 */
class ProgramServiceProvider extends ServiceProvider
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
            \App\Services\Program\IProgramService::class,
            \App\Services\Program\ProgramService::class
        );
    }
}
