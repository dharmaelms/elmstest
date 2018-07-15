<?php

namespace App\Providers\Service\DimensionChannel;

use Illuminate\Support\ServiceProvider;

/**
 * class DimensionChannelServiceProvider
 * @package App\Providers\Service\DimensionChannel
 */
class DimensionChannelServiceProvider extends ServiceProvider
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
            \App\Services\DimensionChannel\IDimensionChannelService::class,
            \App\Services\DimensionChannel\DimensionChannelService::class
        );
    }
}