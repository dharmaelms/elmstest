<?php
namespace App\Model\Section\Repository;

use App\Model\Section;

/**
 * class SectionRepository
 * @package App\Model\Section\Repository
 */
class SectionRepository implements ISectionRepository
{
    /**
     * {@inheritdoc}
     */
    public function getQuizSections($quiz_id)
    {
        return Section::where('quiz_id', (int)$quiz_id)->active()->get();
    }
}
