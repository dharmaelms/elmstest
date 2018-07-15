<?php

namespace App\Events\User;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class EntityUnenrollmentThroughUserGroup extends Event
{
    use SerializesModels;

    /**
     * @var int
     */
    public $user_id;

    /**
     * @var string
     */
    public $entity_type;

    /**
     * @var int
     */
    public $entity_id;

    /**
     * @var
     */
    public $user_group_id;

    /**
     * EntityUnenrollmentThroughUserGroup constructor.
     * @param int $user_id
     * @param string $entity_type
     * @param int $entity_id
     * @param int $user_group_id
     */
    public function __construct($user_id, $entity_type, $entity_id, $user_group_id)
    {
        $this->user_id = $user_id;
        $this->entity_type = $entity_type;
        $this->entity_id = $entity_id;
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
