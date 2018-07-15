<?php

namespace App\Providers\Model\ChannelCompletionTillDate;

use Illuminate\Support\ServiceProvider;

/**
 * class ChannelCompletionTillDateServiceProvider
 * @package App\Providers\Model\ChannelCompletionTillDate
 */
class ChannelCompletionTillDateServiceProvider extends ServiceProvider
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
            \App\Model\ChannelCompletionTillDate\Repository\IChannelCompletionTillDateRepository::class,
            \App\Model\ChannelCompletionTillDate\Repository\ChannelCompletionTillDateRepository::class
        );
    }
}