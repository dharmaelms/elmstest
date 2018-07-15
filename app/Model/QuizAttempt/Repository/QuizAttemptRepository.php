<?php
namespace App\Model\QuizAttempt\Repository;

use App\Enums\QuizAttempt\QuizAttemptStatus;
use App\Helpers\Quiz\AttemptHelper;
use App\Model\QuizAttempt;

/**
 * class QuizAttemptRepository
 * @package App\Model\QuizAttempt\Repository
 */
class QuizAttemptRepository implements IQuizAttemptRepository
{
    /**
     * {@inheritdoc}
     */
    public function find($attempt_id)
    {
        return QuizAttempt::where('attempt_id', (int)$attempt_id)->get();
    }
    /**
     * {@inheritdoc}
     */
    public function findAllAttempts($quiz_id, $user_id)
    {
        return QuizAttempt::where('quiz_id', (int)$quiz_id)
                    ->where('user_id', (int)$user_id)
                    ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function newAttempt($attempt)
    {
        $attempt->attempt_id = (int)QuizAttempt::getNextSequence();
        QuizAttempt::insert(collect($attempt)->toArray());
        return $attempt;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDetails($attempt, $question_id, $answer, $reviewed)
    {
        $details = $attempt->details;
        if ($answer != '') {
            $details['answered'] = array_unique(array_merge($details['answered'], [$question_id]));
        } else {
            $details['answered'] = array_unique(array_flatten(array_diff($details['answered'], [$question_id])));
        }
        if ($reviewed == 'true') {
            $details['reviewed'] = array_unique(array_merge($details['reviewed'], [$question_id]));
        } else {
            $details['reviewed'] = array_flatten(array_diff($details['reviewed'], [$question_id]));
        }
        $details['viewed'] = array_unique(array_merge($details['viewed'], [$question_id], $details['answered'], $details['reviewed']));
        $details['not_viewed'] = array_flatten(array_diff($details['not_viewed'], $details['viewed'], $details['answered'], $details['reviewed']));
        $attempt->details = $details;
        $status = $attempt->save();
        return ['status' => $status, 'class' => AttemptHelper::getQuestionStatus($details, $question_id)];
    }

    /**
     * {@inheritdoc}
     */
    public function getAttemptDetailsByTime($start_date, $end_date)
    {
         return QuizAttempt::where('status', '=', QuizAttemptStatus::CLOSED)
                ->whereBetween('completed_on', [(int)$start_date, (int)$end_date])
                ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxAttempts($start_date, $end_date)
    {

        $resultset = QuizAttempt::raw(function ($c) use ($start_date, $end_date) {
            return $c->aggregate([
                [
                    '$match' => [
                        'create_date' => [
                            '$gte' => $start_date,
                            '$lte' => $end_date
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => ['user_id' => '$user_id', 'quiz_id' => '$quiz_id'],
                        'attempt_id' => ['$max' => '$attempt_id'],
                        'count' => ['$sum' => 1],

                    ]
                ]
            ]);
        });
        return $resultset;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttemptDetailsByIds($attempt_ids)
    {
        return QuizAttempt::whereIn('attempt_id', $attempt_ids)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getClosedQuizzes($quiz_id, $uid)
    {
        return QuizAttempt::where('quiz_id', '=', (int)$quiz_id)
                ->where('user_id', '=', $uid)
                ->where('status', '=', 'CLOSED')
                ->orderBy('started_on')
                ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttemptes($quiz_id)
    {
        return (QuizAttempt::where('quiz_id', '=', (int)$quiz_id)->count()) > 0;
    }
}
