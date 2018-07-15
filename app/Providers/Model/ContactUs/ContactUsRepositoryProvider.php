<?php

namespace App\Providers\Model\ContactUs;

use Illuminate\Support\ServiceProvider;

/**
 * Class ContactUsRepositoryProvider
 *
 * @package App\Providers\Model\ContactUs
 */
class ContactUsRepositoryProvider extends ServiceProvider
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
            \App\Model\ContactUs\IContactUsRepository::class,
            \App\Model\ContactUs\ContactUsRepository::class
        );
    }
}
