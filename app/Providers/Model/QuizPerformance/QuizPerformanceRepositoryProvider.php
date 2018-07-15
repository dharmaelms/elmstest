<?php

namespace App\Providers\Model\QuizPerformance;

use Illuminate\Support\ServiceProvider;

/**
 * class QuizPerformanceRepositoryProvider
 * @package App\Providers\Model\QuizPerformance
 */
class QuizPerformanceRepositoryProvider extends ServiceProvider
{
    /**
     * [boot description]
     * @return [void]
     */
    public function boot()
    {
    }

    /**
     * [register description]
     * @return [void]
     */
    public function register()
    {
        $this->app->bind(
            \App\Model\QuizPerformance\Repository\IOverAllQuizPerformanceRepository::class,
            \App\Model\QuizPerformance\Repository\OverAllQuizPerformanceRepository::class
        );
    }
}
