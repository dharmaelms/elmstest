<?php

namespace App\Providers\Service\DeletedEventsRecordings;

use Illuminate\Support\ServiceProvider;

class DeletedEventsRecordingsServiceProvider extends ServiceProvider
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
            \App\Services\DeletedEventsRecordings\IDeletedEventsRecordingsService::class,
            \App\Services\DeletedEventsRecordings\DeletedEventsRecordingsService::class
        );
    }
}
