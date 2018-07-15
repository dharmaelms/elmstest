<?php

namespace App\Events\Elastic\Programs;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class ProgramUpdated extends Event
{
    use SerializesModels;

    /**
     * @var int program_id
     */
    public $program_id;

    /**
     * @var boolean $is_slug_changed
     */
    public $is_slug_changed;

    /**
     * Create a new event instance.
     * @param $program_id
     * @param $is_slug_changed
     */
    public function __construct($program_id, $is_slug_changed)
    {
        $this->program_id = $program_id;
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
