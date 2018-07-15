<?php

namespace App\Providers\Service\Post;

use Illuminate\Support\ServiceProvider;

/**
 * Class PostServiceProvider
 *
 * @package App\Providers\Service\Post
 */
class PostServiceProvider extends ServiceProvider
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
        $this->app->bind(\App\Services\Post\IPostService::class, \App\Services\Post\PostService::class);
    }
}
