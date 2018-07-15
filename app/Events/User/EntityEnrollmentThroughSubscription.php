<?php

namespace App\Events\User;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class EntityEnrollmentThroughSubscription extends Event
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
     * @var int
     */
    public $valid_from;

    /**
     * @var int
     */
    public $expire_on;

    /**
     * @var string
     */
    public $subscription_slug;

    /**
     * Create a new event instance.
     * @param int $user_id
     * @param string $entity_type
     * @param int $entity_id
     * @param int $valid_from
     * @param int $expire_on
     * @param string $subscription_slug
     */
    public function __construct($user_id, $entity_type, $entity_id, $valid_from, $expire_on, $subscription_slug)
    {
        $this->user_id = $user_id;
        $this->entity_type = $entity_type;
        $this->entity_id = $entity_id;
        $this->valid_from = $valid_from;
        $this->expire_on = $expire_on;
        $this->subscription_slug = $subscription_slug;
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
