<?php

namespace App\Providers\Service\ProgramFaq;

use Illuminate\Support\ServiceProvider;

/**
 * Class ProgramFaqServiceProvider
 *
 * @package App\Providers\Service\ProgramFaq
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
            \App\Services\ProgramFaq\IProgramFaqService::class,
            \App\Services\ProgramFaq\ProgramFaqService::class
        );
    }
}
