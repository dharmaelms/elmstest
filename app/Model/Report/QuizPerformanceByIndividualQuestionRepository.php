<?php

namespace App\Model\Report;

use App\Model\QuizPerformanceByIndividualQuestion;

class QuizPerformanceByIndividualQuestionRepository implements IQuizPerformanceByIndividualQuestionRepository
{

    /**
     * @inheritdoc
     */
    public function getSearchCountChannel($quiz_id, $channel_id)
    {
        return QuizPerformanceByIndividualQuestion::where('quiz_id', '=', (int)$quiz_id)
            ->where('channel_id', '=', (int)$channel_id)
            ->count();
    }

    /**
     * @inheritdoc
     */
    public function getChannelQuizperformanceWithPagenation(
        $start = 0,
        $limit = 10,
        $orderby = ['created_at' => 'desc'],
        $search = null,
        $quiz_id = 0,
        $channel_id = 0,
        $user_id = 0
    )
    {

        $key = key($orderby);
        $value = $orderby[$key];
        $query = QuizPerformanceByIndividualQuestion::where('quiz_id', '=', (int)$quiz_id)
            ->where('channel_id', '=', (int)$channel_id);
        if ($user_id > 0) {
            $query->where('user_id', '=', (int)$user_id);
        }
        if ($search) {
            $query->where('user_name', 'like', '%' . $search . '%');
        }
        $query->orderBy($key, $value);
        if ($limit > 0) {
            $query->skip((int)$start)
                ->take((int)$limit);
        }
        return $query->get();
    }
}
