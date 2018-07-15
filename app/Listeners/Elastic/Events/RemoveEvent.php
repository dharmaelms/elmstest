<?php

namespace App\Listeners\Elastic\Events;

use App\Events\Elastic\Events\EventRemoved;
use App\Jobs\Elastic\Events\DeleteEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class RemoveEvent implements ShouldQueue
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
     * @param EventRemoved $event
     * @return void
     */
    public function handle(EventRemoved $event)
    {
        dispatch(new DeleteEvent($event->event_id));
    }
}
