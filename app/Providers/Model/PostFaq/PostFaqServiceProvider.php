<?php

namespace App\Providers\Model\PostFaq;

use Illuminate\Support\ServiceProvider;

/**
 * Class PostFaqServiceProvider
 *
 * @package App\Providers\Model\PostFaq
 */
class PostFaqServiceProvider extends ServiceProvider
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
            \App\Model\PostFaq\IPostFaqRepository::class,
            \App\Model\PostFaq\PostFaqRepository::class
        );
    }
}
