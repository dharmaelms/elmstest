<?php

namespace App\Listeners\DAMs;

use App\Events\DAMs\DocumentDeleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\DAMs\DeleteDocument;

class DeleteDocumentHandler
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
     * @param  DocumentDeleted  $event
     * @return void
     */
    public function handle(DocumentDeleted $event)
    {
        dispatch(new DeleteDocument($event->doc_box_info));
    }
}
