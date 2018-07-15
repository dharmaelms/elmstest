<?php

namespace App\Listeners\DAMs;

use App\Events\DAMs\DocumentAdded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\DAMs\ProcessDocument;

class AddDocumentHandler
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  DocumentAdded  $event
     * @return void
     */
    public function handle(DocumentAdded $event)
    {
        dispatch(new ProcessDocument($event->upload_info));
    }
}
