<?php

namespace App\Providers\Service\PostFaq;

use Illuminate\Support\ServiceProvider;

/**
 * Class PostFaqServiceProvider
 *
 * @package App\Providers\Service\PostFaq
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

    /**s
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \App\Services\PostFaq\IPostFaqService::class,
            \App\Services\PostFaq\PostFaqService::class
        );
    }
}
