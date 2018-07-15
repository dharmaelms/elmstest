<?php

namespace App\Events\Elastic\Programs;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class ProgramAssigned extends Event
{
    use SerializesModels;

    /**
     * @var int program_id
     */
    public $program_id;

    /**
     * Create a new event instance.
     * @param $program_id
     */
    public function __construct($program_id)
    {
        $this->program_id = $program_id;
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
