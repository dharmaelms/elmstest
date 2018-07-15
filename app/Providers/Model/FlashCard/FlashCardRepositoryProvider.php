<?php

namespace App\Providers\Model\FlashCard;

use Illuminate\Support\ServiceProvider;

/**
 * Class FlashCardRepositoryProvider
 *
 * @package App\Providers\Model\FlashCard
 */
class FlashCardRepositoryProvider extends ServiceProvider
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
            \App\Model\FlashCard\Repository\IFlashCardRepository::class,
            \App\Model\FlashCard\Repository\FlashCardRepository::class
        );
    }
}
