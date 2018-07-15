<?php
namespace App\Model;

use Auth;
use Moloquent;
use Schema;

/**
 * DeletedEventsRecordings model
 *
 * @package DeletedEventsRecordings
 */
class DeletedEventsRecordings extends Moloquent
{
    /**
     * The table/collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'deleted_events_recordings';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Function generate unique auto incremented id for this collection
     *
     * @return integer
     */
    public static function getNextSequence()
    {
        return Sequence::getSequence('event_delete_recording_id');
    }
}
