<?php

namespace App\Model;

use Moloquent;

class OverAllQuizPerformance extends Moloquent
{
    protected $collection = 'over_all_quizPerformance';
    public $timestamps = false;

    public static function insertData($data = [])
    {
        if (!empty($data)) {
            if (self::isExists($data['user_id'], $data['quiz_id'])) {
                $data['updated_at'] = time();
                $res = self::updateData($data, $data['user_id'], $data['quiz_id']);
                if ($res) {
                    return true;
                } else {
                    return false;
                }
            }
            $data['created_at'] = time();
            $res = self::insert($data);
            if ($res) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function updateData(
        $upData = [],
        $userID = 0,
        $quizID = 0
    )
    {

        if (!empty($upData) && $userID > 0 && $quizID > 0) {
            $res = self::where('quiz_id', '=', (int)$quizID)
                ->where('user_id', '=', $userID)
                ->update($upData, ['upsert' => true]);

            if ($res) {
                return true;
            } else {
                return false;
            }
        }
    }

    public static function isExists(
        $userID = 0,
        $quizID = 0
    )
    {

        if ($userID > 0 && $quizID > 0) {
            return self::where('quiz_id', '=', (int)$quizID)
                ->where('user_id', '=', $userID)
                ->first();
        } else {
            return [];
        }
    }

    public static function getAgregationValues($quizIds = [], $userId = 0)
    {
        if (!empty($quizIds) && $userId > 0) {
            $resultset = OverAllQuizPerformance::raw(function ($c) use ($quizIds, $userId) {
                return $c->aggregate([
                    [
                        '$match' => [
                            'quiz_id' => ['$in' => array_values($quizIds)],
                            'user_id' => $userId,
                            'type' => ['$ne' => "QUESTION_GENERATOR"],
                        ],
                    ],
                    [
                        '$group' => [
                            '_id' => [
                                'user_id' => '$user_id',
                            ],
                            'score' => ['$avg' => '$score'],
                            'accuracy' => ['$avg' => '$accuracy'],
                            'speed_h' => ['$avg' => '$speed_h'],
                            'speed_m' => ['$avg' => '$speed_m'],
                            'speed_s' => ['$avg' => '$speed_s'],
                            'count' => ['$sum' => 1],
                        ],
                    ]
                ]);
            });
            return $resultset;
        }
    }

    public static function getQuizAnalytics($quizIds = [], $user_id = 0)
    {
        if (!empty($quizIds)) {
            return self::where('user_id', '=', $user_id)
                ->whereIn('quiz_id', $quizIds)
                ->get();
        } else {
            return [];
        }
    }

    public static function getSpecQuizReoprtByQuizId($quiz_id = 0, $start_time = 0, $end_time = 0)
    {
        if ($quiz_id > 0) {
            if ($start_time > 0 && $end_time > 0) {
                return self::where('quiz_id', '=', (int)$quiz_id)
                    ->where(function ($query) use ($start_time, $end_time) {
                        $query->orWhereBetween('created_at', [(int)$start_time, (int)$end_time])
                            ->orWhereBetween('updated_at', [(int)$start_time, (int)$end_time]);
                    })
                    ->get()
                    ->toArray();
            } else {
                return self::where('quiz_id', '=', (int)$quiz_id)
                    ->get()
                    ->toArray();
            }
        }
    }

    public static function getUsersQuizReport($user_id = [], $quiz_id = 0, $start_date, $end_date)
    {
        return self::where(function ($q) use ($start_date, $end_date) {
            $q->orwhereBetween('updated_at', [(int)$start_date, (int)$end_date])
                ->orwhereBetween('created_at', [(int)$start_date, (int)$end_date]);
        })
            ->whereIn('user_id', $user_id)
            ->where('quiz_id', '=', (int)$quiz_id)
            ->get()->toArray();
    }
}
