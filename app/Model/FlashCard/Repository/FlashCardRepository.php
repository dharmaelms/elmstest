<?php
namespace App\Model\FlashCard\Repository;

use App\Model\FlashCard;

/**
 * Class FlashCardRepository
 * @package App\Model\FlashCard\Repository
 */
class FlashCardRepository implements IFlashCardRepository
{
    /**
     * {inheritdoc}
     */
    public function get($filter_params = [])
    {
        return FlashCard::filter($filter_params);
    }

    /**
     * {inheritdoc}
     */
    public function getFlashcardsDataUsingIDS($ids)
    {
        return FlashCard::whereIn('card_id', $ids)->get()->toArray();
    }
}
