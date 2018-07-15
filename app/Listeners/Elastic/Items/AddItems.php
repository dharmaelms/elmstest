<?php

namespace App\Listeners\Elastic\Items;

use App\Events\Elastic\Items\ItemsAdded;
use App\Jobs\Elastic\Items\IndexItems;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddItems implements ShouldQueue
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
     * @param  ItemsAdded  $event
     * @return void
     */
    public function handle(ItemsAdded $event)
    {
        dispatch(new IndexItems($event->post_id));
    }
}
