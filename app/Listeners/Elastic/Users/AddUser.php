<?php

namespace App\Listeners\Elastic\Users;

use App\Events\Elastic\Users\UsersAssigned;
use App\Jobs\Elastic\Users\AssignUser;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddUser implements ShouldQueue
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
     * @param  UsersAssigned $event
     * @return void
     */
    public function handle(UsersAssigned $event)
    {
        dispatch(new AssignUser($event->program_id));
    }
}
