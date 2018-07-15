<?php

namespace App\Providers\Model\Country;

use Illuminate\Support\ServiceProvider;

class CountryServiceProvider extends ServiceProvider
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
            \App\Model\Country\Repository\ICountryRepository::class,
            \App\Model\Country\Repository\CountryRepository::class
        );
    }
}
