<?php

namespace App\Model;

use Auth;
use Carbon;
use Moloquent;

class MyActivity extends Moloquent
{
    protected $table = 'my_activity';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['date'];

    public static function getInsertActivity($array2 = [])
    {
        $now = time();
        $user_id = Auth::user()->uid;
        $array1 = [
            'DAYOW' => date('l', $now),
            'DOM' => (int)date('j', $now),
            'DOW' => (int)date('w', $now),
            'DOY' => (int)date('z', $now),
            'MOY' => (int)date('n', $now),
            'WOY' => (int)date('W', $now),
            'YEAR' => (int)date('Y', $now),
            'user_id' => (int)$user_id,
            'date' => $now,
        ];
        $array3 = array_merge($array1, $array2);
        self::insert($array3);
    }

    public static function getActivitieswithPaginition($user_id, $record_perpage, $page, $tab)
    {
        $skip = (int)$record_perpage * $page;

        if ($tab == 'general') {
            return self::where('user_id', '=', $user_id)->where('module', '=', 'general')->orderBy('date', 'desc')->skip((int)$skip)->take((int)$record_perpage)->get()->toArray();
        } elseif ($tab == 'feed') {
            return self::where('user_id', '=', $user_id)
                ->whereIn('module', ['contentfeed', 'packet', 'element'])
                ->orderBy('date', 'desc')
                ->skip((int)$skip)
                ->take((int)$record_perpage)
                ->get()
                ->toArray();
        } elseif ($tab == 'library') {
            return 0;
        } elseif ($tab == 'assessment') {
            return self::where('user_id', '=', $user_id)->where('module', '=', 'assessment')->orderBy('date', 'desc')->skip((int)$skip)->take((int)$record_perpage)->get()->toArray();
        } elseif ($tab == 'event') {
            return self::where('user_id', '=', $user_id)->where('module', '=', 'event')->orderBy('date', 'desc')->skip((int)$skip)->take((int)$record_perpage)->get()->toArray();
        } elseif ($tab == 'QAs') {
            return self::where('user_id', '=', $user_id)->where('module', '=', 'QAs')->orderBy('date', 'desc')->skip((int)$skip)->take((int)$record_perpage)->get()->toArray();
        }
    }

    public static function getNewCompletedPackets($packets)
    {
        $array_packets['new'] = [];
        $array_packets['completed'] = [];
        foreach ($packets as $packet) {
            $elements_count = count($packet['elements']);
            $activity_count = count(MyActivity::getPacketElementDetails(Auth::user()->uid, $packet['packet_id']));
            if ($activity_count == 0) {
                $array_packets['new'][] = $packet['packet_id'];
            }

            if (($activity_count != 0) && ($elements_count == $activity_count)) {
                $array_packets['completed'][] = $packet['packet_id'];
            }
        }

        return $array_packets;
    }

    public static function pluckElementActivity($packet_id, $element_id, $element_type)
    {
        if ($element_type == 'assessment') {
            return self::where('user_id', '=', Auth::user()->uid)
                ->where('module', '=', 'element')
                ->where('packet_id', '=', (int)$packet_id)
                ->where('element_type', '=', $element_type)
                ->where('action', '=', 'attempt_closed')
                ->where('module_id', '=', (int)$element_id)
                ->value('module_id');
        }
        return self::where('user_id', '=', Auth::user()->uid)
            ->where('module', '=', 'element')
            ->where('packet_id', '=', (int)$packet_id)
            ->where('element_type', '=', $element_type)
            ->where('action', '=', 'view')
            ->where('module_id', '=', (int)$element_id)
            ->value('module_id');
    }

    public static function getContinueWrULeft($user_id = null)
    {
        if ($user_id) {
            return self::where('user_id', '=', $user_id)
                ->where('module', '!=', 'general')
                ->where('is_mobile', '!=', 1)
                ->orderBy('date', 'desc')
                ->limit(1)
                ->get()
                ->toArray();
        } else {
            return false;
        }
    }

    public static function getFeedActivity($distinct = true)
    {
        if ($distinct) {
            return self::where('module', '=', 'contentfeed')->where('action', '=', 'view')->distinct('module_id');
        } else {
            return self::where('module', '=', 'contentfeed')->where('action', '=', 'view');
        }
    }

    public static function scopeGetByTime($query, $starttime = null, $endtime = null)
    {
        if ($starttime && $endtime) {
            return $query->where('date', '>', $starttime)->where('date', '<', $endtime);
        }

        return $query;
    }


    public static function getActiveUsers($start = null, $end = null, $admin_user = [])
    {
        if (!is_null($start) && !is_null($end)) {
            return self::where('module', '=', 'general')
                ->whereBetween('date', [$start, $end])
                ->whereNotIn('user_id', $admin_user)
                ->distinct('user_id')
                ->get()
                ->count();
        } else {
            return 0;
        }
    }

    public static function getLastDayActivity()
    {
        $start_time = strtotime('yesterday midnight');
        $end_time = $start_time + 86400;

        return self::whereBetween('date', [$start_time, $end_time])
            ->where('module', '!=', 'general')
            ->where(function ($query) {
                $query->orWhere(function ($q) {
                    $q->where('element_type', '=', 'assessment')
                        ->where('action', '=', 'attempt_closed');
                })
                ->orWhere(function ($qe) {
                    $qe->where('element_type', '!=', 'assessment')
                        ->where('action', '=', 'view');
                });
            })
            ->get()
            ->toArray();
    }

    public static function getSpecDaysActivity($start_time, $end_time, $start, $limit)
    {
        return self::whereBetween('date', [$start_time, $end_time])
            ->where('module', '=', 'element')
            ->where(function ($query) {
                $query->orWhere(function ($q) {
                    $q->where('element_type', '=', 'assessment')
                        ->where('action', '=', 'attempt_closed');
                })
                ->orWhere(function ($qe) {
                    $qe->where('element_type', '!=', 'assessment')
                        ->where('action', '=', 'view');
                });
            })
            ->skip((int)$start)
            ->take((int)$limit)
            ->get()
            ->toArray();
    }

    public static function getUserGeneralActivity($startDate = 0, $endDate = 0)
    {
        if ($startDate <= 0 || $endDate <= 0) {
            $startDate = Carbon::yesterday()->timestamp;
            $endDate = Carbon::today()->timestamp;
        }
        return self::whereBetween('date', [$startDate, $endDate])
            ->where('module', '=', 'general')
            ->distinct('user_id')
            ->get()->toArray();
    }

    public static function getUserGeneralActivityCount($startDate = 0, $endDate = 0, $user_id = 0)
    {
        if ($startDate <= 0 || $endDate <= 0) {
            $startDate = Carbon::yesterday()->timestamp;
            $endDate = Carbon::today()->timestamp;
        }
        return self::whereBetween('date', [$startDate, $endDate])
            ->where('module', '=', 'general')
            ->where('user_id', '=', $user_id)
            ->where(function ($q) {
                $q->orwhere('action', '=', 'Sign In')
                    ->orwhere('action', '=', 'login');
            })
            ->count();
    }

    public static function getPacketElementDetails($userId, $postId)
    {
        return Self::where('user_id', '=', (int)$userId)
            ->where('packet_id', '=', (int)$postId)
            ->where('module', '=', 'element')
            ->where(function ($query) {
                $query->orWhere(function ($q) {
                    $q->where('element_type', '=', 'assessment')
                        ->where('action', '=', 'attempt_closed');
                })
                ->orWhere(function ($qe) {
                    $qe->where('element_type', '!=', 'assessment')
                        ->where('action', '=', 'view');
                });
            })
            ->groupBy('element_type', 'module_id')
            ->get(['module_id', 'element_type']);
    }

    /**
     * copyToDimensionMyActivityTbl
     * @return array
     */
    public static function copyToDimensionMyActivityTbl()
    {

        $result = MyActivity::raw(function ($table) {
            return $table->aggregate(
                [
                    '$match' => [
                        '$or' => [
                            [
                                '$and' => [
                                    [ 'element_type' => "assessment"],
                                    [ 'action' => "attempt_closed" ]
                                ]
                            ],
                            [
                                '$and' => [
                                    [ 'element_type' => ['$ne' => "assessment"]],
                                    [ 'action' => "view" ]
                                ]
                            ]
                        ],
                        'module' => "element"
                    ]
                ],
                [ '$out' => "dim_my_activity" ]
            );
        });
        return $result;
    }
}
