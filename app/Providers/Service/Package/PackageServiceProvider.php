<?php

namespace App\Providers\Service\Package;

use App\Services\Package\IPackageService;
use App\Services\Package\PackageService;
use Illuminate\Support\ServiceProvider;

/**
 *  class PackageServiceProvider
 *  @package App\Providers\Service\Package
 */
class PackageServiceProvider extends ServiceProvider
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
        $this->app->bind(IPackageService::class, PackageService::class);
    }
}
