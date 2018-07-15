<?php

namespace App\Listeners\DAMs;

use App\Events\DAMs\DocumentUpdated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\DAMs\ProcessDocument;

class UpdateDocumentHandler
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
     * @param  DocumentUpdated  $event
     * @return void
     */
    public function handle(DocumentUpdated $event)
    {
        dispatch(new ProcessDocument($event->upload_info));
    }
}
