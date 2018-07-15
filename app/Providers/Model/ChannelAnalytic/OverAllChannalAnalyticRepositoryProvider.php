<?php

namespace App\Providers\Model\ChannelAnalytic;

use Illuminate\Support\ServiceProvider;

/**
 * class OverAllChannalAnalyticRepositoryProvider
 * @package App\Providers\Model\ChannelAnalytic
 */
class OverAllChannalAnalyticRepositoryProvider extends ServiceProvider
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
            \App\Model\ChannelAnalytic\Repository\IOverAllChannalAnalyticRepository::class,
            \App\Model\ChannelAnalytic\Repository\OverAllChannalAnalyticRepository::class
        );
    }
}
