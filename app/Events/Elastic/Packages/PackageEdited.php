<?php

namespace App\Events\Elastic\Packages;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class PackageEdited extends Event
{
    use SerializesModels;

    /**
     * @var int package_id
     */
    public $package_id;

    /**
     * @var int is_slug_changed
     */
    public $is_slug_changed;

    /**
     * Create a new event instance.
     * @param $package_id
     * @param $is_slug_changed
     */
    public function __construct($package_id, $is_slug_changed)
    {
        $this->package_id = $package_id;
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
