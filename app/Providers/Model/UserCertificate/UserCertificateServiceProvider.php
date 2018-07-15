<?php

namespace App\Providers\Model\UserCertificate;

use Illuminate\Support\ServiceProvider;

/**
 * Class AnnouncementServiceProvider
 *
 * @package App\Providers\Model\Announcement
 */
class UserCertificateServiceProvider extends ServiceProvider
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
            \App\Model\UserCertificates\Repository\IUserCertificatesRepository::class,
            \App\Model\UserCertificates\Repository\UserCertificatesRepository::class
        );
    }
}
