<?php

namespace App\Providers\Model\Section;

use Illuminate\Support\ServiceProvider;

/**
 * Class SectionRepositoryProvider
 *
 * @package App\Providers\Model\Section
 */
class SectionRepositoryProvider extends ServiceProvider
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
        $this->app->bind(\App\Model\Section\Repository\ISectionRepository::class, \App\Model\Section\Repository\SectionRepository::class);
    }
}
