<?php

namespace App\Listeners\Elastic\Events;

use App\Events\Elastic\Events\EventEdited;
use App\Jobs\Elastic\Events\IndexEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class EditEvent implements ShouldQueue
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
     * @param EventEdited $event
     * @return void
     */
    public function handle(EventEdited $event)
    {
        dispatch(new IndexEvent($event->event_id, false));
    }
}
