<?php

namespace App\Services\DAMS;

use App\Model\Dams\Repository\IDamsRepository;

class DAMsService implements IDAMsService
{
    /**
     * @var IDamsRepository
     */
    private $damsRepository;

    /**
     * DAMsService constructor.
     * @param IDamsRepository $damsRepository
     */
    public function __construct(IDamsRepository $damsRepository)
    {
        $this->damsRepository = $damsRepository;
    }

    /**
     * @inheritDoc
     */
    public function getMediasCreatedByUsers($usernames)
    {
        return $this->damsRepository->get(["created_by" => $usernames]);
    }

    public function getTypeScormRecords($assigned_items, $type, $start, $limit)
    {
        return $this->damsRepository->getTypeScormRecords($assigned_items, $type, $start, $limit);
    }

    /**
     * @inheritdoc
     */
    public function getDAMSDataUsingIDS($ids)
    {
        return $this->damsRepository->getDAMSDataUsingIDS($ids);
    }

    /**
     * @inheritdoc
     */
    public function getItemsCount($item_ids, $date)
    {
        return $this->damsRepository->getItemsCount($item_ids, $date);
    }

    /**
     * @inheritdoc
     */
    public function getNewItems($item_ids, $date, $start, $limit)
    {
        return $this->damsRepository->getNewItems($item_ids, $date, $start, $limit);
    }

    /**
     * @param  array $items_ids
     * @param  array $items_type
     * @inheritdoc
     */
    public function countActiveItems($items_ids, $item_type)
    {
        return $this->damsRepository->countActiveItems($items_ids, $item_type);
    }

    /**
     * @inheritdoc
     */
    public function getMedia($key, $id_type)
    {
        return $this->damsRepository->getMedia($key, $id_type);
    }
}
