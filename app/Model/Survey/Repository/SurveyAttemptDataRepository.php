<?php

namespace App\Model\Survey\Repository;

use App\Model\Sequence;
use App\Model\Survey\Entity\SurveyAttemptData;

/**
 * Class SurveyAttemptDataRepository
 *
 * @package App\Model\Survey\Repository
 */
class SurveyAttemptDataRepository implements ISurveyAttemptDataRepository
{
    /**
     * {@inheritdoc}
     */
    public function getSurveyAttempt($attempt_id)
    {
        //TODO
    }

    /**
     * {@inheritdoc}
     */
    public function getSurveyAttemptData($survey_id, $user_ids)
    {
        return SurveyAttemptData::where('survey_id', (int)$survey_id)
        ->whereIn('user_id', $user_ids)
        ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getNextSequence()
    {
        return Sequence::getSequence('attempt_id');
    }

    /**
     * {@inheritdoc}
     */
    public function insertData($data = [])
    {
        return SurveyAttemptData::insert($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getSurveyAttemptDataByUserIdAndSurveyId($survey_id, $user_id)
    {
        return SurveyAttemptData::where('survey_id', (int)$survey_id)->where('user_id', (int)$user_id)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getSurveyAttemptByUserIdAndSurveyIds($user_id, $survey_ids)
    {
        return SurveyAttemptData::where('user_id', '=', $user_id)
            ->whereIn('survey_id', $survey_ids)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getSurveyAnswers($survey_id, $question_id)
    {
        return SurveyAttemptData::where('survey_id', '=', (int)$survey_id)
            ->where('question_id', '=', (int)$question_id)
            ->limit(10)
            ->get(['other_text']);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserResponse($survey_id, $user_ids)
    {
        return SurveyAttemptData::raw(function ($c) use ($survey_id, $user_ids) {
            return $c->aggregate([
                [
                    '$match' => [
                        'survey_id'=> (int)$survey_id,
                        'user_id' => ['$in' => $user_ids]
                    ]
                ],
                [
                    '$unwind' => '$user_answer'
                ],
                [
                    '$group' => [
                        '_id' => [
                                    'question_id' => '$question_id',
                                    'user_answer' => '$user_answer'
                                ],
                        'count' => ['$sum' => 1]
                    ]
                ],
                [
                    '$sort' => ['_id.question_id' => 1],
                ],
                [
                    '$project' => [
                        'question_id' => '$_id.question_id',
                        'user_answer' => '$_id.user_answer',
                        'count' => 1,
                        '_id' => 0
                    ]
                ]
            ]);
        });
    }

    /**
     * {@inheritdoc}
     */
     public function getDescAnswers($survey_id, $user_ids)
     {
        return SurveyAttemptData::raw(function ($c) use ($survey_id, $user_ids) {
            return $c->aggregate([
                [
                    '$match' => [
                        'survey_id' => (int)$survey_id,
                        'user_id' => ['$in' => $user_ids]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$question_id',
                        'count' => [
                            '$sum'  => [
                                '$cond'  => [ 'if' => ['$and' =>[ ['$ne' => ['$other_text', '']], ['$ne' => ['$other_text', null]]]], 'then' => 1, 'else' => 0]
                            ]
                        ],
                        'Other_text' => ['$push' => '$other_text']
                    ]
                ]
            ]);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getUserTextByQuestion($survey_id, $question_id, $user_ids, $start, $limit)
    {
        return SurveyAttemptData::where('survey_id', '=', (int)$survey_id)
            ->where('question_id', '=', (int)$question_id)
            ->whereIn('user_id', $user_ids)
            ->where('other_text', '$exists', true)
            ->where('other_text', '!=', '')
            ->skip((int)$start)
            ->take((int)$limit)
            ->get(['other_text', 'user_id']);
    }

    /**
     * {@inheritdoc}
     */
    public  function  getAttemptedSurveysBySurveyIds($survey_ids)
    {
        return SurveyAttemptData::whereIn('survey_id', $survey_ids)
            ->get(['survey_id']);
    }

    public function getRespondedUsers($survey_id, $question_id, $choice_index, $user_ids, $start, $limit)
    {
        return SurveyAttemptData::where('survey_id', (int)$survey_id)
            ->whereIn('user_id', $user_ids)
            ->where('question_id', (int)$question_id)
            ->where('user_answer', (int)$choice_index)
            ->skip((int)$start)
            ->take((int)$limit)
            ->get(['user_id']);
    }
}
