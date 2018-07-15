<?php

namespace App\Events\Auth;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class Registered extends Event
{
    use SerializesModels;

    /**
     * @var int
     */
    public $user_id;

    /**
     * @var int
     */
    public $role_id;

    /**
     * Create a new event instance.
     * @param int $user_id
     * @param int $role_id
     */
    public function __construct($user_id, $role_id = null)
    {
        $this->user_id = $user_id;
        $this->role_id = $role_id;
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
