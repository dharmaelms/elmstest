<?php

namespace App\Providers\Model\Category;

use Illuminate\Support\ServiceProvider;

/**
 * Class CategoryRepositoryProvider
 *
 * @package App\Providers\Model\Event
 */
class CategoryRepositoryProvider extends ServiceProvider
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
            \App\Model\Category\Repository\ICategoryRepository::class,
            \App\Model\Category\Repository\CategoryRepository::class
        );
    }
}
