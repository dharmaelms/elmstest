<?php

namespace App\Model\ProgramFaq;

/**
 * Interface IProgramFaqRepository
 * @package App\Model\ProgramFaq
 */
interface IProgramFaqRepository
{
    /**
     * getUnAnsChannelsQusCount
     * @param  array  $program_ids
     * @param  array  $date
     * @return integer
     */
    public function getUnAnsChannelsQusCount(array $program_ids, array $date);
}
