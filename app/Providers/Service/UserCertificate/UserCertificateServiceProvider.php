<?php

namespace App\Providers\Service\UserCertificate;

use Illuminate\Support\ServiceProvider;

/**
 * Class CertificateServiceProvider
 *
 * @package App\Providers\Service\UserCertificate
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
            \App\Services\UserCertificate\IUserCertificateService::class,
            \App\Services\UserCertificate\UserCertificateService::class
        );
    }
}
