<?php

namespace App\Listeners\Elastic\Packages;

use App\Events\Elastic\Packages\PackageAdded;
use App\Jobs\Elastic\Packages\IndexPackage;
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
     * @param  PackageAdded  $event
     * @return void
     */
    public function handle(PackageAdded $event)
    {
        dispatch(new IndexPackage($event->package_id));
    }
}
