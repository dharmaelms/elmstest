<?php

namespace App\Listeners\Elastic\Packages;

use App\Events\Elastic\Packages\PackageEdited;
use App\Jobs\Elastic\Packages\IndexPackage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class EditPackage implements ShouldQueue
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
     * @param  PackageEdited  $event
     * @return void
     */
    public function handle(PackageEdited $event)
    {
        dispatch(new IndexPackage($event->package_id, $event->is_slug_changed));
    }
}
