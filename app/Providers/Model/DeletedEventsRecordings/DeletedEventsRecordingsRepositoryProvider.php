<?php

namespace App\Providers\Model\DeletedEventsRecordings;

use Illuminate\Support\ServiceProvider;

class DeletedEventsRecordingsRepositoryProvider extends ServiceProvider
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
             \App\Model\DeletedEventsRecordings\Repository\IDeletedEventsRecordingsRepository::class,
             \App\Model\DeletedEventsRecordings\Repository\DeletedEventsRecordingsRepository::class
         );
    }
}
