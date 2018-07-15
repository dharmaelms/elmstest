<?php
namespace App\Services\FlashCard;

interface IFlashCardService
{
    /**
     * @param array $usernames
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFlashCardsCreatedByUsers($usernames);

    /**
     * @param array $ids
     * @return array
     */
    public function getFlashcardsDataUsingIDS($ids);
}