<?php

namespace App\Model\Report;

use App\Model\DimensionChannelUserQuiz;

class DimensionChannelUserQuizRepository implements IDimensionChannelUserQuizRepository
{
    /**
     * @inheritdoc
     */
    public function getQuizzesByChannel(array $channel_ids, $start, $limit)
    {
        $query = DimensionChannelUserQuiz::where('quiz_ids.0', 'exists', true);
        if (!empty($channel_ids)) {
            $query->whereIn('channel_id', $channel_ids);
        }
        $query->skip((int)$start);
        if ($limit >= 1) {
            $query->take((int)$limit);
        }
        return $query->orderBy('channel_id', 'desc')
                ->get();
    }
}
