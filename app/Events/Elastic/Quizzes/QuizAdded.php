<?php

namespace App\Events\Elastic\Quizzes;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class QuizAdded extends Event
{
    use SerializesModels;

    /**
     * @var int $quiz_id
     */
    public $quiz_id;

    /**
     * Create a new event instance.
     * @param $quiz_id
     */
    public function __construct($quiz_id)
    {
        $this->quiz_id = $quiz_id;
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
