<?php

namespace App\Services\ProgramFaq;

use App\Model\ProgramFaq\IProgramFaqRepository;

/**
 * class ProgramFaqService
 * @package App\Services\ProgramFaq
 */
class ProgramFaqService implements IProgramFaqService
{
    private $program_faq_repo;
    public function __construct(IProgramFaqRepository $program_faq_repo)
    {
        $this->program_faq_repo = $program_faq_repo;
    }

    /**
     * @inheritdoc
     */
    public function getUnAnsChannelsQusCount(array $program_ids, array $date)
    {
        return $this->program_faq_repo->getUnAnsChannelsQusCount($program_ids, $date);
    }

}
