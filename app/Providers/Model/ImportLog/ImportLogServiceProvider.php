<?php

namespace App\Providers\Model\ImportLog;

use Illuminate\Support\ServiceProvider;

/**
 * Class ImportServiceProvider
 *
 * @package App\Providers\Model\ImportLog
 */
class ImportLogServiceProvider extends ServiceProvider
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
            \App\Model\ImportLog\IImportRepository::class,
            \App\Model\ImportLog\ImportRepository::class
        );
    }
}
