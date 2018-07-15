<?php
namespace App\Model\EventReport;

use Moloquent;

class EventsHistory extends Moloquent
{
    /**
     * The table/collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'events_history';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * this function is used to convert the confid to string
     */
    public function getStrConfIdAttribute()
    {
        return strval($this->confID);
    }
}
