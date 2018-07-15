<?php

namespace App\Providers\Model\ProgramFaq;

use Illuminate\Support\ServiceProvider;

/**
 * Class ProgramFaqServiceProvider
 *
 * @package App\Providers\Model\ProgramFaq
 */
class ProgramFaqServiceProvider extends ServiceProvider
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
            \App\Model\ProgramFaq\IProgramFaqRepository::class,
            \App\Model\ProgramFaq\ProgramFaqRepository::class
        );
    }
}
