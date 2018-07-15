<?php

namespace App\Providers\Model\Package;

use App\Model\Package\Repository\IPackageRepository;
use App\Model\Package\Repository\PackageRepository;
use Illuminate\Support\ServiceProvider;

/**
 *  class PackageRepositoryProvider
 *  @package App\Providers\Model\Package
 */
class PackageRepositoryProvider extends ServiceProvider
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
        $this->app->bind(IPackageRepository::class, PackageRepository::class);
    }
}
