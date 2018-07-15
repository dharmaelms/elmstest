<?php

namespace App\Listeners\Elastic\Programs;

use App\Events\Elastic\Programs\ProgramAssigned;
use App\Jobs\Elastic\Programs\AddProgram;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AssignProgram implements ShouldQueue
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
     * @param  ProgramAssigned $event
     * @return void
     */
    public function handle(ProgramAssigned $event)
    {
        dispatch(new AddProgram($event->program_id));
    }
}
