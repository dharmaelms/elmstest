<?php

namespace App\Listeners\Elastic\Users;

use App\Events\Elastic\Users\PackageAssigned;
use App\Jobs\Elastic\Users\AssignPackage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddPackage implements ShouldQueue
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
     * @param  PackageAssigned $event
     * @return void
     */
    public function handle(PackageAssigned $event)
    {
        dispatch(new AssignPackage($event->package_id));
    }
}
