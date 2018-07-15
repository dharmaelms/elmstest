<?php

namespace App\Providers\Model\Testimonial;

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
            \App\Model\Testimonial\Repository\ITestimonialRepository::class,
            \App\Model\Testimonial\Repository\TestimonialRepository::class
        );
    }
}
