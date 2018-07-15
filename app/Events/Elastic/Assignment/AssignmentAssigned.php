<?php

namespace App\Events\Elastic\assignment;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class AssignmentAssigned extends Event
{
    use SerializesModels;

    /**
     * @var int $assignment_id
     */
    public $assignment_id;

    /**
     * Create a new event instance.
     * @param $assignment_id
     */
    public function __construct($assignment_id)
    {
        $this->assignment_id = $assignment_id;
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
