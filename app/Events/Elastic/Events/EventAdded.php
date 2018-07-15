<?php

namespace App\Events\Elastic\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class EventAdded extends Event
{
    use SerializesModels;

    /**
     * @var int $event_id
     */
    public $event_id;

    /**
     * Create a new event instance.
     * @param $post_id
     */
    public function __construct($event_id)
    {
        $this->event_id = $event_id;
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
