<?php

namespace App\Providers\Service\PostFaqAnswer;

use Illuminate\Support\ServiceProvider;

class PostFaqAnswerServiceProvider extends ServiceProvider
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
            \App\Services\PostFaqAnswer\IPostFaqAnswerService::class,
            \App\Services\PostFaqAnswer\PostFaqAnswerService::class
        );
    }
}
