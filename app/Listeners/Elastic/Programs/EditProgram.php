<?php

namespace App\Listeners\Elastic\Programs;

use App\Events\Elastic\Programs\ProgramUpdated;
use App\Jobs\Elastic\Programs\IndexProgram;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class EditProgram implements ShouldQueue
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
     * @param  ProgramUpdated $program
     * @return void
     */
    public function handle(ProgramUpdated $program)
    {
        dispatch(new IndexProgram($program->program_id, $program->is_slug_changed, false));
    }
}
