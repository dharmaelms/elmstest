<?php
namespace App\Services\FlashCard;

use App\Model\FlashCard\Repository\IFlashCardRepository;

class FlashCardService implements IFlashCardService
{
    /**
     * @var IFlashCardRepository
     */
    private $flashcardRepository;

    /**
     * FlashCardService constructor.
     * @param IFlashCardRepository $FlashCardRepository
     */
    public function __construct(IFlashCardRepository $flashcardRepository)
    {
        $this->flashcardRepository = $flashcardRepository;
    }

    /**
     * @inheritDoc
     */
    public function getFlashCardsCreatedByUsers($usernames)
    {
        return $this->flashcardRepository->get(["created_by" => $usernames]);
    }

    /**
     * {inheritdoc}
     */
    public function getFlashcardsDataUsingIDS($ids)
    {
        return $this->flashcardRepository->getFlashcardsDataUsingIDS($ids);
    }
}
