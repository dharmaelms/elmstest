<?php

namespace App\Listeners\Elastic\Events;

use App\Events\Elastic\Events\EventAssigned;
use App\Jobs\Elastic\Events\AssignEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AssignUser implements ShouldQueue
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
     * @param EventAssigned $event
     * @return void
     */
    public function handle(EventAssigned $event)
    {
        dispatch(new AssignEvent($event->event_id));
    }
}
