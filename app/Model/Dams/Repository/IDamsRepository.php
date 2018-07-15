<?php
namespace App\Model\Dams\Repository;

use App\Model\Dam;

/**
 * Interface IDamsRepository
 * @package App\Model\Dams\Repository
 */
interface IDamsRepository
{
    /**
     * @param array $filter_params
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get($filter_params = []);

    /**
     * Method to get embed code for video, audio and image
     * @param  \App\Model\Dam $media Dam instance for which we need to prepare embed code
     * @return String|Null
     */
    public function getMediaEmbedCode(Dam $media);

    /**
     * Method to get embed code for audio
     * @param  \App\Model\Dam $media Dam instance for which we need to prepare embed code
     * @return String
     */
    public function getAudioEmbedCode(Dam $audio);

    /**
     * Method to get embed code for image
     * @param  \App\Model\Dam $media Dam instance for which we need to prepare embed code
     * @return String
     */
    public function getImageEmbedCode(Dam $image);

    /**
     * Method to get media by its `id` or `_id`
     * @param $key - String|Integer - mongoID or mediaID
     * @param $id_type - String - id or _id
     * @return $media - App\Model\Dam
     * @throws \App\Exceptions\Dams\MediaNotFoundException
     */
    public function getMedia($key, $id_type = "_id");

    /**
     * updateRelation update secific relation
     * @param  int $program_id sepcific program id
     * @param string $tab_slug
     * @param  array $ids mongo _id array
     * @return void
     */
    public function updateTabDamsRelation($program_id, $tab_slug, $ids);

    /**
     * update package tab relation
     * @param  int $package_id sepcific program id
     * @param string $tab_slug
     * @param  array $ids mongo _id array
     * @return void
     */
    public function updatePackageTabDamsRelation($package_id, $tab_slug, $ids);

    /**
     * getTabDamRelation get all program's tab related dam elements
     * @param  int $program_id
     * @param  string $tab_slug
     * @return void
     */
    public function removeTabDamsRelation($program_id, $tab_slug);

    /**
     * removing package dam relation
     * @param  int $package_id
     * @param  string $tab_slug
     * @return void
     */
    public function removePackageTabDamsRelation($package_id, $tab_slug);

    /**
     * Method to update BoxDetails Status
     * @param  \App\Model\Dam  $document
     * @param  \stdClass $data
     * @return boolean
     */
    public function updateBoxDetailsStatus($document, $data);

    /**
     * @param array $type
     * @return array
     */
    public function getTypeScormRecords($assigned_items, $type, $start, $limit);

    /**
     * @param array $ids
     * @return array
     */
    public function getDAMSDataUsingIDS($ids);

    /**
     * @param array $item_ids
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
}
