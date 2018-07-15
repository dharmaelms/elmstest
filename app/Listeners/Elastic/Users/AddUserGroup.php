<?php

namespace App\Listeners\Elastic\Users;

use App\Events\Elastic\Users\UserGroupAssigned;
use App\Jobs\Elastic\Users\AssignUserGroup;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddUserGroup implements ShouldQueue
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
    public function handle(UserGroupAssigned $event)
    {
        dispatch(new AssignUserGroup($event->user_group_id));
    }
}
