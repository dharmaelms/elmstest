<?php
namespace App\Services\DeletedEventsRecordings;

use App\Model\DeletedEventsRecordings\Repository\IDeletedEventsRecordingsRepository;

class DeletedEventsRecordingsService implements IDeletedEventsRecordingsService
{
    /**
     * @var deletedEventsRecordingsRepository
     */
    private $deletedEventsRecordingsRepository;

    /**
     * DeletedEventsRecordingsService constructor.
     * @param IDeletedEventsRecordingsRepository $deletedEventsRecordingsRepository
     */
    public function __construct(IDeletedEventsRecordingsRepository $deletedEventsRecordingsRepository)
    {
        $this->deletedEventsRecordingsRepository = $deletedEventsRecordingsRepository;
    }

    /**
     * {inheritdoc}
     */
    public function getNextSequence()
    {
        return $this->deletedEventsRecordingsRepository->getNextSequence();
    }
    
    /**
     * @inheritDoc
     */
    public function getEventDetails($event_id)
    {
        return $this->deletedEventsRecordingsRepository->getEventDetails($event_id);
    }

    /**
     * {inheritdoc}
     */
    public function updateDeletedRecordings($event_id, $recordings)
    {
        return $this->deletedEventsRecordingsRepository->updateDeletedRecordings($event_id, $recordings);
    }

    /**
     * {inheritdoc}
     */
    public function insertEventsDetails($data)
    {
        return $this->deletedEventsRecordingsRepository->insertEventsDetails($data);
    }
}
