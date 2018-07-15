<?php

namespace App\Providers\Service\ChannelCompletionTillDate;

use Illuminate\Support\ServiceProvider;

/**
 * class ChannelCompletionTillDateServiceProvider
 * @package App\Providers\Service\ChannelCompletionTillDate
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
            \App\Services\ChannelCompletionTillDate\IChannelCompletionTillDateService::class,
            \App\Services\ChannelCompletionTillDate\ChannelCompletionTillDateService::class
        );
    }
}