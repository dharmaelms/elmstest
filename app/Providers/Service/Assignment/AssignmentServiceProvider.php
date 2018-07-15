<?php

namespace App\Providers\Service\Assignment;

use Illuminate\Support\ServiceProvider;

/**
 * Class AssignmentServiceProvider
 *
 * @package App\Providers\Service\Assignment
 */
class AssignmentServiceProvider extends ServiceProvider
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
            \App\Services\Assignment\IAssignmentAttemptService::class,
            \App\Services\Assignment\AssignmentAttemptService::class
        );
        $this->app->bind(
            \App\Services\Assignment\IAssignmentService::class,
            \App\Services\Assignment\AssignmentService::class
        );
    }
}
