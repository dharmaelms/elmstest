<?php

namespace App\Events\Elastic\Items;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class ItemsAdded extends Event
{
    use SerializesModels;

    /**
     * @var array $post_id
     */
    public $post_id;

    /**
     * Create a new event instance.
     * @param $post_id
     */
    public function __construct($post_id)
    {
        $this->post_id = $post_id;
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
