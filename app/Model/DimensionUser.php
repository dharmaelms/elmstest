<?php

namespace App\Model;

use Carbon;
use Moloquent;

class DimensionUser extends Moloquent
{
    protected $collection = 'dim_users';

    public $timestamps = false;

    protected $casts = [
        'user_id' => 'int'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['create_date', 'update_date'];

    public static function scopeGetOrderBy($query, $orderby = ['user_name' => 'asc'])
    {
        $key = key($orderby);
        $value = $orderby[$key];
        return $query->orderBy($key, $value);
    }

    
    public static function getDetailsDateRange($orderby = 'asc')
    {
        return self::where('status', '=', 'ACTIVE')
            ->orderby('user_id', $orderby)
            ->get()
            ->toArray();
    }

    public static function getUserDetails($orderby = 'asc')
    {
        return self::where('status', '=', 'ACTIVE')
            ->orderby('user_id', $orderby)
            ->get(['user_id', 'channel_ids'])
            ->toArray();
    }

    public static function isExist($user_id = 0)
    {
        return self::where('user_id', '=', $user_id)->first();
    }

    public static function getUserList($start = 0, $limit = 500)
    {
        return self::skip((int)$start)->take((int)$limit)->get(['user_name','user_id']);
    }

    public static function getSpecificUserDetail($user_id = 0)
    {
        if ($user_id > 0) {
            return self::where('user_id', '=', $user_id)
                ->get()
                ->toArray();
        } else {
            return [];
        }
    }

    public static function getUserListids()
    {
        return self::get(['user_id'])->toArray();
    }

    public static function getUserNameListByids($user_ids = [])
    {
        return self::whereIn('user_id', $user_ids)->get(['user_name'])->toArray();
    }

    public static function getUserIdByChannel($channel_id = 0)
    {
        if ($channel_id > 0) {
            return self::where('status', '=', 'ACTIVE')
                ->where('channel_ids', '=', (int)$channel_id)
                ->get(['user_id'])
                ->toArray();
        }
    }

    public static function getUsersQuizIDs()
    {
        $timeline = Carbon::yesterday()->timestamp;
        return self::where('quiz_ids.0', 'exists', true)
            ->where(function ($q) use ($timeline) {
                $q->where('create_date', '>=', $timeline)
                    ->where('update_date', '>=', $timeline);
            })
            ->get()
            ->toArray();
    }

    public static function getUserChannelDetails($orderby = 'asc')
    {
        return self::where('channel_ids.0', 'exists', true)
            ->orderby('user_id', $orderby)
            ->get(['user_id', 'channel_ids', 'user_name'])
            ->toArray();
    }

    public static function getUserIdsByChannel($channel_id = 0)
    {
        if ($channel_id > 0) {
            return self::where('channel_ids', '=', (int)$channel_id)
                ->get(['user_id']);
        } else {
            return [];
        }
    }

    public static function getUserDetailsByUIds($user_ids = [])
    {
        return self::whereIn('user_id', $user_ids)
            ->orderby('user_id', 'asc')
            ->get(['user_id', 'channel_ids'])
            ->toArray();
    }

    public static function getDetailsByUids($user_ids = [])
    {
        return self::whereIn('user_id', $user_ids)
            ->orderby('user_id', 'asc')
            ->get(['user_id', 'user_name']);
    }

    /**
     * [getUserCount get users count]
     * @return [int] [total users count]
     */
    public static function getUserCount()
    {
        return self::count();
    }
}
