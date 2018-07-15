<?php

namespace App\Providers\Service\Testimonial;

use Illuminate\Support\ServiceProvider;

class TestimonialServiceProvider extends ServiceProvider
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
            \App\Services\Testimonial\ITestimonialService::class,
            \App\Services\Testimonial\TestimonialService::class
        );
    }
}
