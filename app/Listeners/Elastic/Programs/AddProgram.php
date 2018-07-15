<?php

namespace App\Listeners\Elastic\Programs;

use App\Events\Elastic\Programs\ProgramAdded;
use App\Jobs\Elastic\Programs\IndexProgram;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddProgram implements ShouldQueue
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
     * @param  ProgramAdded  $event
     * @return void
     */
    public function handle(ProgramAdded $event)
    {
        dispatch(new IndexProgram($event->program_id));
    }
}
