<?php

namespace App\Services\DAMS;


interface IDAMsService
{
    /**
     * @param array $usernames
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMediasCreatedByUsers($usernames);

    /**
     * @param string $type
     * @return array
     */
    public function getTypeScormRecords($assigned_items, $type, $start, $limit);

    /**
     * @param array $ids
     * @return array
     */
    public function getDAMSDataUsingIDS($ids);

    /**
     * @param $item_ids
     * @param array $date
     * @return integer
     */
    public function getItemsCount($item_ids, $date);

    /**
     * @param array $date
     * @param array $item_ids
     * @param integer $start
     * @param integer $limit
     * @return collection
     */
    public function getNewItems($item_ids, $date, $start, $limit);

    /**
     * @param  array $items_ids
     * @param  array $items_type
     * @return integer
     */
    public function countActiveItems($items_ids, $item_type);

    /**
     * Method to get media by its `id` or `_id`
     * @param $key - String|Integer - mongoID or mediaID
     * @param $id_type - String - id or _id
     * @return $media - App\Model\Dam
     * @throws \App\Exceptions\Dams\MediaNotFoundException
     */
    public function getMedia($key, $id_type);
}
