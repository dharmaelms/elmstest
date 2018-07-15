<?php
namespace App\Model\Section\Repository;

/**
 * Interface ISectionRepository
 * @package  App\Model\Section\Repository
 */
interface ISectionRepository
{
    /**
     * Method to get sections by quiz id
     *
     * @param int $quiz_id
     * @return App\Model\Section
     */
    public function getQuizSections($quiz_id);
}
