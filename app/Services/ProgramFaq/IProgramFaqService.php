<?php

namespace App\Services\ProgramFaq;

/**
 * Interface IProgramFaqService
 * @package App\Services\ProgramFaq
 */
interface IProgramFaqService
{
    /**
     * getUnAnsChannelsQusCount
     * @param  array  $program_ids
     * @param  array  $date
     * @return integer
     */
    public function getUnAnsChannelsQusCount(array $program_ids, array $date);
}
