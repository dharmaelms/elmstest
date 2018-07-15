<?php

namespace App\Listeners\Elastic\Assignment;

use App\Events\Elastic\Assignment\AssignmentAssigned;
use App\Jobs\Elastic\Assignment\AssignAssignment;
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
     * @param AssignmentAssigned $assignment
     * @return void
     */
    public function handle(AssignmentAssigned $assignment)
    {
        dispatch(new AssignAssignment($assignment->assignment_id));
    }
}
