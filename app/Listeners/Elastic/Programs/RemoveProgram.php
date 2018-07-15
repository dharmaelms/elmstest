<?php

namespace App\Listeners\Elastic\Programs;

use App\Events\Elastic\Programs\ProgramRemoved;
use App\Jobs\Elastic\Programs\DeleteProgram;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class RemoveProgram implements ShouldQueue
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
     * @param  ProgramRemoved $program
     * @return void
     */
    public function handle(ProgramRemoved $program)
    {
        dispatch(new DeleteProgram($program->program_id));
    }
}
