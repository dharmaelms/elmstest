<?php

namespace App\Events\DAMs;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

/**
 * Class DocumentAdded
 * @package App\Events\DAMs
 */
class DocumentAdded extends Event
{
    use SerializesModels;

    /**
     * @var Object
     */
    public $upload_info;

    /**
     * DocumentAdded constructor.
     * @param $data array
     */
    public function __construct($data)
    {
        $this->upload_info = $data;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
