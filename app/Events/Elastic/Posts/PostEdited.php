<?php

namespace App\Events\Elastic\Posts;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class PostEdited extends Event
{
    use SerializesModels;

    /**
     * @var int $post_id
     */
    public $post_id;

    /**
     * @var string $is_slug_changed
     */
    public $is_slug_changed;

    /**
     * Create a new event instance.
     * @param $post_id
     * @param $is_slug_changed
     */
    public function __construct($post_id, $is_slug_changed)
    {
        $this->post_id = $post_id;
        $this->is_slug_changed = $is_slug_changed;
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
