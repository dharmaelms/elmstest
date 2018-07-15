<?php

namespace App\Events\Elastic\Posts;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class PostAdded extends Event
{
    use SerializesModels;

    /**
     * @var int $post_id
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
