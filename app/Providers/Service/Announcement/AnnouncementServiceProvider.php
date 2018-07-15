<?php

namespace App\Providers\Service\Announcement;

use Illuminate\Support\ServiceProvider;

/**
 * Class AnnouncementServiceProvider
 *
 * @package App\Providers\Service\Announcement
 */
class AnnouncementServiceProvider extends ServiceProvider
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
            \App\Services\Announcement\IAnnouncementService::class,
            \App\Services\Announcement\AnnouncementService::class
        );
    }
}
