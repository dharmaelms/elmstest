<?php
namespace App\Model\EventReport;

use Moloquent;

class EventsAttendeeHistory extends Moloquent
{
    /**
     * The table/collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'events_attendee_history';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
