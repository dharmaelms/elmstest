<?php

namespace App\Providers\Model\Program;

use Illuminate\Support\ServiceProvider;

/**
 * Class ProgramServiceProvider
 *
 * @package App\Providers\Model\Program
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
            \App\Model\Program\IProgramRepository::class,
            \App\Model\Program\ProgramRepository::class
        );
    }
}
