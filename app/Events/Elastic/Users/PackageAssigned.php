<?php

namespace App\Events\Elastic\Users;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class PackageAssigned extends Event
{
    use SerializesModels;

    /**
     * @var int $package_id
     */
    public $package_id;

    /**
     * Create a new event instance.
     * @param $package_id
     */
    public function __construct($package_id)
    {
        $this->package_id = $package_id;
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
