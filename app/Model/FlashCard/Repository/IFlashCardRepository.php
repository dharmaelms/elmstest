<?php
namespace App\Model\FlashCard\Repository;

/**
 * Interface IFlashCardRepository
 *
 * @package App\Model\FlashCard\Repository
 */
interface IFlashCardRepository
{
	/**
     * @param array $filter_params
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get($filter_params = []);

    /**
     * @param array $ids
     * @return array
     */
    public function getFlashcardsDataUsingIDS($ids);
}
