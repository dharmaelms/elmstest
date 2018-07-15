<?php

namespace App\Listeners\Elastic\Events;

use App\Events\Elastic\Events\EventAdded;
use App\Jobs\Elastic\Events\IndexEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddEvent implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param EventAdded $event
     * @return void
     */
    public function handle(EventAdded $event)
    {
        dispatch(new IndexEvent($event->event_id));
    }
}
