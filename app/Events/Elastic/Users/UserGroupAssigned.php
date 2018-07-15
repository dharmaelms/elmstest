<?php

namespace App\Events\Elastic\Users;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class UserGroupAssigned extends Event
{
    use SerializesModels;

    /**
     * @var int $user_group_id
     */
    public $user_group_id;

    /**
     * Create a new event instance.
     * @param $user_group_id
     */
    public function __construct($user_group_id)
    {
        $this->user_group_id = $user_group_id;
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
