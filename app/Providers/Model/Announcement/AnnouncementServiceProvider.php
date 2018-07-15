<?php

namespace App\Providers\Model\Announcement;

use Illuminate\Support\ServiceProvider;

/**
 * Class AnnouncementServiceProvider
 *
 * @package App\Providers\Model\Announcement
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
            \App\Model\Announcement\IAnnouncementRepository::class,
            \App\Model\Announcement\AnnouncementRepository::class
        );
    }
}
