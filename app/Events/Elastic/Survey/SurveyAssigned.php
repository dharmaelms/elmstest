<?php

namespace App\Events\Elastic\Survey;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class SurveyAssigned extends Event
{
    use SerializesModels;

    /**
     * @var int $event_id
     */
    public $survey_id;

    /**
     * Create a new event instance.
     * @param $event_id
     */
    public function __construct($survey_id)
    {
        $this->survey_id = $survey_id;
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
