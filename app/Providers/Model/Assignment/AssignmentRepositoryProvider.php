<?php

namespace App\Providers\Model\Assignment;

use Illuminate\Support\ServiceProvider;

/**
 * Class PostRepositoryProvider
 *
 * @package App\Providers\Model\Assignment
 */
class AssignmentRepositoryProvider extends ServiceProvider
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
            \App\Model\Assignment\Repository\IAssignmentRepository::class,
            \App\Model\Assignment\Repository\AssignmentRepository::class
        );
        $this->app->bind(
            \App\Model\Assignment\Repository\IAssignmentAttemptRepository::class,
            \App\Model\Assignment\Repository\AssignmentAttemptRepository::class
        );
    }
}
