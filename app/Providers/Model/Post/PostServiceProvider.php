<?php

namespace App\Providers\Model\Post;

use Illuminate\Support\ServiceProvider;

/**
 * Class PostServiceProvider
 *
 * @package App\Providers\Model\Post
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
        $this->app->bind(\App\Model\Post\IPostRepository::class, \App\Model\Post\PostRepository::class);
    }
}
