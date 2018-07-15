<?php

namespace App\Model;

use Moloquent;

class DirectQuizPerformanceByIndividualQuestion extends Moloquent
{
    
    protected $collection = 'direct_quiz_performance_by_individual_question';
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['create_date', 'update_date'];

    public static function getNextSequence()
    {
        return Sequence::getSequence('dir_q_perf_que_id');
    }

    public static function getInsertData($data = [])
    {
        $user_id = $data['user_id'];
        $quiz_id = $data['quiz_id'];
        $res = self::isExist($user_id, $quiz_id);
        if (isset($res) && !is_null($res->id)) {
            $data['update_date'] = time();

            return self::getupdatedata($data, $res->id);
        } else {
            $data['id'] = self::getNextSequence();
            $data['create_date'] = time();

            return self::insert($data);
        }
    }

    public static function getupdatedata($data = [], $id = 0)
    {
        return self::where('id', '=', (int)$id)->update($data, ['upsert' => true]);
    }

    public static function isExist($user_id = 0, $quiz_id = 0)
    {
        return self::where('user_id', '=', $user_id)
            ->where('quiz_id', '=', (int)$quiz_id)
            ->first();
    }

    public static function getLaestQuizDetail($quiz_id = 0)
    {
        return self::where('quiz_id', '=', (int)$quiz_id)
            ->orderby('id', 'desc')
            ->first();
    }

    public static function getSearchCount($quiz_id = 0)
    {
        return self::where('quiz_id', '=', (int)$quiz_id)
            ->count();
    }

    public static function getSearchCountQuizUser($quiz_id = 0)
    {
        return self::where('quiz_id', '=', (int)$quiz_id)
            ->count();
    }

    public static function getQuizperformanceWithPagenation($start = 0, $limit = 10, $orderby = ['created_at' => 'desc'], $search = null, $quiz_id = 0)
    {
        $key = key($orderby);
        $value = $orderby[$key];
        if ($search) {
            return self::where('quiz_id', '=', (int)$quiz_id)
                ->where('user_name', 'like', '%' . $search . '%')
                ->orderBy($key, $value)
                ->skip((int)$start)
                ->take((int)$limit)
                ->get()
                ->toArray();
        } else {
            return self::where('quiz_id', '=', (int)$quiz_id)
                ->orderBy($key, $value)
                ->skip((int)$start)
                ->take((int)$limit)
                ->get()
                ->toArray();
        }
    }

    public static function getChannelQuizperformanceWithPagenation($start = 0, $limit = 10, $orderby = ['created_at' => 'desc'], $search = null, $quiz_id = 0)
    {
        $key = key($orderby);
        $value = $orderby[$key];
        if ($search) {
            return self::where('quiz_id', '=', (int)$quiz_id)
                ->where('user_name', 'like', '%' . $search . '%')
                ->orderBy($key, $value)
                ->skip((int)$start)
                ->take((int)$limit)
                ->get()
                ->toArray();
        } else {
            return self::where('quiz_id', '=', (int)$quiz_id)
                ->orderBy($key, $value)
                ->skip((int)$start)
                ->take((int)$limit)
                ->get()
                ->toArray();
        }
    }

    public static function getUsersQuizDetails($quiz_id = 0)
    {
        return self::where('quiz_id', '=', (int)$quiz_id)
            ->orderby('id', 'desc')
            ->get()
            ->toArray();
    }

    public static function getCSVChannelQuizperformanceWithPagenation($quiz_id = 0)
    {
        return self::where('quiz_id', '=', (int)$quiz_id)
            ->get()
            ->toArray();
    }

    public static function getDetailsByQuizIds($quiz_ids)
    {
        return self::whereIn('quiz_id', $quiz_ids)->get();
    }
}
