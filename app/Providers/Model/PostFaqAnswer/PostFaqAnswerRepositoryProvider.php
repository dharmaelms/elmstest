<?php

namespace App\Providers\Model\PostFaqAnswer;

use Illuminate\Support\ServiceProvider;

class PostFaqAnswerRepositoryProvider extends ServiceProvider
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
             \App\Model\PostFaqAnswer\IPostFaqAnswerRepository::class,
             \App\Model\PostFaqAnswer\PostFaqAnswerRepository::class
         );
    }
}
