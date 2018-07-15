<?php

namespace App\Providers\Model\DimensionChannel;

use Illuminate\Support\ServiceProvider;

/**
 * class DimensionChannelServiceProvider
 * @package App\Providers\Model\DimensionChannel
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
            \App\Model\DimensionChannel\Repository\IDimensionChannelRepository::class,
            \App\Model\DimensionChannel\Repository\DimensionChannelRepository::class
        );
    }
}