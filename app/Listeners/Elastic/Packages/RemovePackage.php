<?php

namespace App\Listeners\Elastic\Packages;

use App\Events\Elastic\Packages\PackageRemoved;
use App\Jobs\Elastic\Packages\DeletePackage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class RemovePackage implements ShouldQueue
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
     * @param  PackageRemoved  $event
     * @return void
     */
    public function handle(PackageRemoved $event)
    {
        dispatch(new DeletePackage($event->package_id));
    }
}
