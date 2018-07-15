<?php

namespace App\Model\Report;

use App\Model\FactChannelUserQuiz;

class FactChannelUserQuizRepository implements IFactChannelUserQuizRepository
{

    public function getQuizPerformanceInChannels(
        $channel_ids,
        $start_date,
        $end_date,
        $user_id = 0
    )
    {
        $query = FactChannelUserQuiz::whereBetween('created_at', [(int)$start_date, (int)$end_date])
            ->orWhereBetween('updated_at', [(int)$start_date, (int)$end_date]);
        if (!empty($channel_ids)) {
            $query->whereIn('channel_id', array_map('intval', $channel_ids));
        }
        if ($user_id > 1) {
            $query->where('user_id', '=', (int)$user_id);
        }
        return $query->get();
    }
}