<?php

namespace App\Model\ProgramFaq;

use App\Model\ChannelFaq;

/**
 * class ProgramFaqRepository
 * @package App\Model\ProgramFaq
 */
class ProgramFaqRepository implements IProgramFaqRepository
{
    /**
     * @inheritdoc
     */
    public function getUnAnsChannelsQusCount(array $program_ids, array $date)
    {
        $query =  ChannelFaq::where('status', '=', 'UNANSWERED')
                        ->whereBetween('created_at', $date);
        if (!empty($program_ids)) {
            $query->whereIn('program_id', $program_ids);
        }
        return $query->count();
    }
}
