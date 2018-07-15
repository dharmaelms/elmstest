<?php

namespace App\Providers\Service\FlashCard;

use Illuminate\Support\ServiceProvider;

/**
 * Class FlashCardServiceProvider
 *
 * @package App\Providers\Service\FlashCard
 */
class FlashCardServiceProvider extends ServiceProvider
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
            \App\Services\FlashCard\IFlashCardService::class,
            \App\Services\FlashCard\FlashCardService::class
        );
    }
}
