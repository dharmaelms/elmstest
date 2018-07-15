<?php

namespace App\Model;

use Moloquent;

class DimensionChannelUserQuiz extends Moloquent
{
    protected $collection = 'dim_channels_user_quiz';
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['create_date', 'update_date', 'created_at', 'updated_at'];

    public static function isExist($channel_id = 0)
    {
        return self::where('channel_id', '=', $channel_id)->first();
    }

    public static function getQuizzesByChannel()
    {
        return self::get()->toArray();
    }

    public static function getUserByChannelId($channel_id = 0)
    {
        if ($channel_id > 0) {
            return self::where('channel_id', '=', $channel_id)
                ->get()->toArray();
        } else {
            return [];
        }
    }

    public static function getRecordsWithQuizids()
    {
        return self::where('quiz_ids', 'exists', true)
            ->where('quiz_ids', '!=', [])
            ->get()->toArray();
    }

    public static function getChannelDetailsByQuizUser($user_ids, $quiz_ids)
    {
        return self::whereIn('quiz_ids', $quiz_ids)
                    ->orwhereIn('user_ids', $user_ids)->orderBy('attempt_id', 'asc')->get();
    }
}
