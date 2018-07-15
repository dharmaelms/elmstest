<?php

namespace App\Model\Report;

use App\Model\DirectQuizPerformanceByIndividualQuestion;

class DirectQuizPerformanceByIndividualQuestionRepository implements IDirectQuizPerformanceByIndividualQuestionRepository
{
    /**
     * @inheritdoc
     */
    public function getSearchCountQuizUser($quiz_id = 0)
    {
        return DirectQuizPerformanceByIndividualQuestion::where('quiz_id', '=', (int)$quiz_id)
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
        $quiz_id = 0
    )
    {
        $key = key($orderby);
        $value = $orderby[$key];
        $query = DirectQuizPerformanceByIndividualQuestion::where('quiz_id', '=', (int)$quiz_id);
        if ($search && !empty($search)) {
            $query->where('user_name', 'like', '%' . $search . '%');
        }
        return $query->orderBy($key, $value)
            ->skip((int)$start)
            ->take((int)$limit)
            ->get();
    }
}
