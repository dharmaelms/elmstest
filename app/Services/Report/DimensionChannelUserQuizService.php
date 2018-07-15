<?php

namespace App\Services\Report;

use App\Model\Report\IDimensionChannelUserQuizRepository;

class DimensionChannelUserQuizService implements IDimensionChannelUserQuizService
{
    private $dim_channel_quiz;

    public function __construct(
        IDimensionChannelUserQuizRepository $dim_channel_quiz
    ) {
        $this->dim_channel_quiz = $dim_channel_quiz;
    }
    /**
     * @inheritdoc
     */
    public function getQuizzesByChannel(array $channel_ids, $start, $limit)
    {
        return $this->dim_channel_quiz->getQuizzesByChannel($channel_ids, $start, $limit)->keyBy('channel_id');
    }
}
