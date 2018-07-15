<?php
namespace App\Model\DeletedEventsRecordings\Repository;

use App\Model\DeletedEventsRecordings;

/**
 * Class DeletedEventsRecordingsRepository
 * @package App\Model\DeletedEventsRecordings\Repository
 */
class DeletedEventsRecordingsRepository implements IDeletedEventsRecordingsRepository
{
    /**
     * {inheritdoc}
     */
    public function getNextSequence()
    {
        return DeletedEventsRecordings::getNextSequence();
    }

    /**
     * {inheritdoc}
     */
    public function getEventDetails($event_id)
    {
        return DeletedEventsRecordings::where('event_id', (int)$event_id)->first();
    }

    /**
     * {inheritdoc}
     */
    public function updateDeletedRecordings($event_id, $recordings)
    {
        return DeletedEventsRecordings::where('event_id', $event_id)->update(['recordings' => $recordings]);
    }

    /**
     * {inheritdoc}
     */
    public function insertEventsDetails($data)
    {
        return DeletedEventsRecordings::insert($data);
    }
}
