<?php
namespace App\Model\DeletedEventsRecordings\Repository;

/**
 * Interface IDeletedEventsRecordingsRepository
 *
 * @package App\Model\DeletedEventsRecordings\Repository
 */
interface IDeletedEventsRecordingsRepository
{
	/**
     * @return int
     */
    public function getNextSequence();

    /**
     * @param int $event_id
     * @return model
     */
    public function getEventDetails($event_id);

    /**
     * @param int $event_id
     * @param array $recordings
     * @return boolean
     */
    public function updateDeletedRecordings($event_id, $recordings);

    /**
     * @param array $data
     * @return boolean
     */
    public function insertEventsDetails($data);
}
