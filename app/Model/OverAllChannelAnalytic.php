<?php

namespace App\Model;

use Carbon;
use Moloquent;

class OverAllChannelAnalytic extends Moloquent
{
    protected $collection = 'over_all_channel_analytics';
    public $timestamps = false;

    protected $casts = [
        'user_id' => 'int',
        'channel_id' => 'int'
    ];

    public static function insertData($data = [])
    {
        if (!empty($data)) {
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

    public static function updateData($upData = [], $channelID = 0, $userID = 0)
    {

        if (!empty($upData) && $channelID > 0 && $userID > 0) {
            $res = self::where('channel_id', '=', $channelID)
                ->where('user_id', '=', $userID)
                ->update($upData, ['upsert' => true]);

            if ($res) {
                return true;
            } else {
                return false;
            }
        }
    }

    public static function isExists($channelID, $userID)
    {
        return self::where('channel_id', '=', $channelID)
            ->where('user_id', '=', $userID)
            ->first();
    }

    public static function getChannelAnalytics($channelIds, $user_id)
    {
        return self::where('user_id', '=', $user_id)
            ->whereIn('channel_id', $channelIds)
            ->get();
    }

    public static function getLastDayChannelCompletion()
    {
        $fromDate = Carbon::yesterday()->timestamp;

        return self::where(function ($q) use ($fromDate) {
            $q->orwhere('created_at', '>=', $fromDate)
                ->orwhere('updated_at', '>=', $fromDate);
        })
            ->get()->toArray();
    }

    public static function getPastDaysChannelCompletion($startDate = 0, $endDate = 0)
    {
        if ($startDate == 0 || $endDate == 0) {
            return [];
        }

        return self::where(function ($qe) use ($startDate, $endDate) {
            $qe->orWhere(function ($q) use ($startDate, $endDate) {
                $q->Where('created_at', '>=', (int)$startDate)
                    ->Where('created_at', '<=', (int)$endDate);
            })
                ->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->Where('updated_at', '>=', (int)$startDate)
                        ->Where('updated_at', '<=', (int)$endDate);
                });
        })
            ->get()->toArray();
    }

    public static function getIncompleteCertifiedChannelList()
    {
        return self::where('is_certificate_generated', '=', 1)
                    ->where('completion', '!=', 100)
                    ->distinct('channel_id')->get();
    }

    public static function getCompleteCertifiedChannelItems($channel_ids)
    {
        return OverAllChannelAnalytic::raw(function ($table) use ($channel_ids) {
            return $table->aggregate([
                [
                    '$match' => [
                        'channel_id' => ['$in' => $channel_ids],
                        'is_certificate_generated' => 1,
                        'completion' => 100
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$channel_id',
                        'items' => ['$first' => '$item_details'],
                        'post_completion' => ['$first' => '$post_completion'],
                        'completion' => ['$first' => '$completion'],
                    ]
                ],
                [
                    '$project' => [
                        'channel_id' => '$_id',
                        'items' => '$items',
                        'post_completion' => '$post_completion',
                        'completion' => '$completion'
                    ]
                ]
            ]);
        });
    }

    public static function getUserChannelActivityDuration($startDate, $endDate)
    {
        if ($startDate > 0 && $endDate > 0) {
            return self::whereBetween('created_at', [(int)$startDate, (int)$endDate])
                ->orWhereBetween('updated_at', [(int)$startDate, (int)$endDate])
                ->get(['user_id', 'channel_id', 'created_at', 'updated_at']);
        }
        return self::get(['user_id', 'channel_id', 'created_at', 'updated_at']);
    }

    public static function getUserAnalytics($user_id)
    {
        return self::where('user_id', '=', $user_id)
            ->get();
    }
}
