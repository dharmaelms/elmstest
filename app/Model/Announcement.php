<?php

namespace App\Model;

use Auth;
use Carbon;
use Moloquent;

class Announcement extends Moloquent
{

    protected $table = 'announcements';

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

    protected $dates = ['schedule', 'expire_date', 'created_at', 'updated_at'];

    public static function getAnounceMaxID()
    {
        return Sequence::getSequence('announcement_id');
    }

    public static function addAnnouncement($addannouncejson = null)
    {
        if (!is_null($addannouncejson)) {
            return self::insert($addannouncejson);
        } else {
            return 0;
        }
    }

    public static function getOneAnnouncement($key = null)
    {
        if ($key) {
            return self::where('status', '=', 'ACTIVE')->where('announcement_id', '=', (int)$key)->get();
        } else {
            return self::take(1)->get();
        }
    }

    public static function getAnnouncement($key = null)
    {
        if ($key) {
            return self::where('announcement_id', '=', (int)$key)->get()->toArray();
        } else {
            return self::take(1)->get();
        }
    }

    public static function getOneAnnouncementforPortal($key = null)
    {
        if ($key) {
            return self::where('status', '=', 'ACTIVE')->where('announcement_id', '=', (int)$key)->get()->toArray();
        } else {
            return self::take(1)->get()->toArray();
        }
    }

    public static function getAnnouncementCount()
    {
        return self::count();
    }

    public static function getAnnouncementSearchCount($search, $status_filter)
    {
        if ($search) {
            return self::where('status', '=', $status_filter)->where('announcement_title', 'like', '%' . $search . '%')->count();
        } else {
            return self::where('status', '=', $status_filter)->count();
        }
    }

    public static function getAnnouncementwithPagenation($start = 0, $limit = 10, $orderby = ['created_at' => 'desc'], $search = null, $status_filter = 'ACTIVE')
    {

        $key = key($orderby);

        $value = $orderby[$key];

        if ($search) {
            return self::where('status', '=', $status_filter)
                ->where(

                    function ($q) use ($search) {

                        $q->orwhere('announcement_title', 'like', '%' . $search . '%')
                            ->orWhere('announcement_content', 'like', '%' . $search . '%');
                    }
                )
                ->orderBy($key, $value)
                ->skip((int)$start)
                ->take((int)$limit)
                ->get();
        } else {
            return self::where('status', '=', $status_filter)
                ->orderBy($key, $value)
                ->skip((int)$start)
                ->take((int)$limit)
                ->get();
        }
    }

    public static function updateAnnouncement($key = null, $datajson = [])
    {
        if ($key) {
            return self::where('announcement_id', '=', (int)$key)->update($datajson, ['upsert' => true]);
        } else {
            return;
        }
    }

    public static function singleDelete($key = null)
    {
        if ($key) {
            return self::where('announcement_id', '=', (int)$key)->delete();
        } else {
            return -1;
        }
    }

    public static function getAnnouncementsRelation($announcement_id)
    {
        return self::where('announcement_id', (int)$announcement_id)->get();
    }

    public static function insertAnnouncementsRelation($key, $insertArr)
    {
        return self::where('announcement_id', '=', (int)$key)->update(['relations' => $insertArr]);
    }

    public static function updateAnnouncementsRelationchk($key, $announce_rel)
    {
        return self::where('announcement_id', '=', (int)$key)->update(['relations' => $announce_rel]);
    }

    public static function updateAnnouncementsRelation($key, $arrname, $updateArr, $overwrite = false)
    {
        if ($overwrite) {
            self::where('announcement_id', (int)$key)->unset('relations.' . $arrname);
            self::where('announcement_id', (int)$key)->update(['relations.' . $arrname => $updateArr]);
        } else {
            self::where('announcement_id', $key)->push('relations.' . $arrname, $updateArr, true);
        }

        return self::where('announcement_id', $key)->update(['updated_at' => time()]);
    }

    public static function getAnnouncementsforAll($limit = null, $device = 'both')
    {
        if ($device == 'mobile') {
            $temp = 'Web only';
        } elseif ($device == 'web') {
            $temp = 'Mobile only';
        } else {
            $temp = 'both';
        }
        if (!is_null($limit)) {
            return self::where('status', '=', 'ACTIVE')
                ->where('announcement_for', '=', 'public')
                ->where('announcement_device', '!=', $temp)
                ->where('schedule', '<', time())
                ->where('expire_date', '>=', time())
                ->orderBy('schedule', 'desc')->take((int)$limit)->get()->toArray();
        } else {
            return self::where('status', '=', 'ACTIVE')
                ->where('announcement_device', '!=', $temp)
                ->where('announcement_for', '=', 'public')
                ->where('schedule', '<', time())
                ->where('expire_date', '>=', time())
                ->orderBy('schedule', 'desc')->get()->toArray();
        }
    }

    public static function getAnnouncementsforscroll($aid = [], $start = 0, $limit = 5, $device = 'both')
    {
        if ($device == 'mobile') {
            $temp = 'Web only';
        } elseif ($device == 'web') {
            $temp = 'Mobile only';
        } else {
            $temp = 'both';
        }
        if (!empty($aid)) {
            return self::where('status', '=', 'ACTIVE')
                ->Where(
                    function ($query) use ($aid) {
                            $query->orwhere('announcement_for', '=', 'registerusers')
                            ->orwhereIn('announcement_id', $aid);
                    }
                )
                ->where('announcement_device', '!=', $temp)
                ->where('schedule', '<', Carbon::today()->timestamp)
                ->where('expire_date', '>=', Carbon::today()->timestamp)
                ->orderBy('schedule', 'desc')->skip((int)$start)->take((int)$limit)->get()->toArray();
        } else {
            return self::where('status', '=', 'ACTIVE')
                ->Where(function ($query) {
                        $query->orwhere('announcement_for', '=', 'registerusers');
                })
                ->where('announcement_device', '!=', $temp)
                ->where('schedule', '<', Carbon::today()->timestamp)
                ->where('expire_date', '>=', Carbon::today()->timestamp)
                ->orderBy('created_at', 'desc')->skip((int)$start)->take((int)$limit)->get()->toArray();
        }
    }

    public static function getAnnouncementsforscrollCount($aid = [])
    {
        if (!empty($aid)) {
            return self::where('status', '=', 'ACTIVE')
                ->Where(
                    function ($query) use ($aid) {
                        $query->orwhere('announcement_for', '=', 'public')
                            ->orwhere('announcement_for', '=', 'registerusers')
                            ->orwhereIn('announcement_id', $aid);
                    }
                )
                ->where('schedule', '<', time())
                ->where('expire_date', '>=', time())
                ->count();
        }

        return 0;
    }

    public static function updateAnnouncementsReaders($key, $updateArr, $overwrite = false)
    {
        if ($overwrite) {
            self::where('announcement_id', (int)$key)->unset('readers.user');
            self::where('announcement_id', (int)$key)->update(['readers.user' => $updateArr]);
        } else {
            self::where('announcement_id', (int)$key)->push('readers.user', $updateArr, true);
        }

        return self::where('announcement_id', (int)$key)->update(['updated_at' => time()]);
    }

    public static function updateAnnouncementsSentMail($key, $updateArr, $overwrite = false)
    {
        if ($overwrite) {
            self::where('announcement_id', (int)$key)->unset('readers.user');
            self::where('announcement_id', (int)$key)->update(['readers.user' => $updateArr]);
        } else {
            self::where('announcement_id', (int)$key)->push('sentmail.users', $updateArr, true);
        }

        return self::where('announcement_id', (int)$key)->update(['updated_at' => time()]);
    }

    /* Added by Sahana*/

    public static function removeAnnouncementRelation($key, $fieldarr = [], $id = 0)
    {
        foreach ($fieldarr as $field) {
            self::where('announcement_id', $key)->pull('relations.' . $field, (int)$id);
        }

        return self::where('announcement_id', $key)->update(['updated_at' => time()]);
    }

    public static function addAnnouncementRelation($key, $fieldarr = [], $id = 0)
    {
        foreach ($fieldarr as $field) {
            self::where('announcement_id', $key)->push('relations.' . $field, (int)$id, true);
        }

        return self::where('announcement_id', $key)->update(['updated_at' => time()]);
    }

    /*for statistic */
    public static function getLastThirtydaysCreatedAnnouncementCount()
    {
        return self::where('status', '=', 'ACTIVE');
    }

    /*for portal side  not read count*/

    public static function getNotReadAnnouncementCount($user_id = null, $aids = null, $device = 'both')
    {
        if ($device == 'mobile') {
            $temp = 'Web only';
        } elseif ($device == 'web') {
            $temp = 'Mobile only';
        } else {
            $temp = 'both';
        }
        if (!is_null($user_id) && !is_null($aids)) {
            return self::where('status', '=', 'ACTIVE')
                ->whereNotIN('readers.user', [$user_id])
                ->where('announcement_device', '!=', $temp)
                ->Where(function ($query) use ($aids) {
                        $query->orwhere('announcement_for', '=', 'registerusers')
                        ->orwhereIn('announcement_id', $aids);
                })
                ->where('schedule', '<', Carbon::today()->timestamp)
                ->where('expire_date', '>=', Carbon::today()->timestamp)
                ->count();
        } else {
            return 0;
        }
    }

    public static function getNotReadAnnouncementForHead($user_id = null, $aids = null, $start = 0, $limit = 5, $device = 'mobile & web')
    {
        if ($device == 'mobile') {
            $temp = 'Web only';
        } elseif ($device == 'web') {
            $temp = 'Mobile only';
        } else {
            $temp = 'both';
        }
        if (!is_null($user_id) && !is_null($aids)) {
            return self::where('status', '=', 'ACTIVE')
                ->where('announcement_device', '!=', $temp)
                ->whereNotIN('readers.user', [$user_id])
                ->Where(function ($query) use ($aids) {
                        $query->orwhere('announcement_for', '=', 'registerusers')
                        ->orwhereIn('announcement_id', $aids);
                })
                ->where('schedule', '<', time())
                ->where('expire_date', '>=', time())
                ->orderBy('schedule', 'desc')->skip((int)$start)->take((int)$limit)->get()->toArray();
        } else {
            return [];
        }
    }

    public static function getAnnouncements()
    {
        return self::where('status', '=', 'ACTIVE')->orderBy('created_at', 'desc');
    }

    public static function scopeGetByTime($query, $starttime = null, $endtime = null)
    {
        if ($starttime && $endtime) {
            return $query->where('schedule', '>', $starttime)->where('schedule', '<', $endtime);
        }

        return $query;
    }

    public static function getNewAnnouncement($start = null, $end = null)
    {
        if (!is_null($start) && !is_null($end)) {
            return self::where('status', '=', 'ACTIVE')
                ->whereBetween('created_at', [$start, $end])
                ->count();
        } else {
            return 0;
        }
    }

    public static function getAnnouncementListForCron()
    {
        return self::where('status', '=', 'ACTIVE')
            ->where('schedule', '<', Carbon::today()->timestamp)
            ->where('expire_date', '>', Carbon::today()->timestamp)
            ->where('cron_flag', '=', 0)
            ->Where('notify_mail', '=', 'on')
            ->get()
            ->toArray();
    }

    public static function getLastDayAnnouncements()
    {

        /*$start_time = strtotime('yesterday midnight');
        $end_time = $start_time + 86400;

        return self::whereBetween('date', [$start_time, $end_time])
                    ->where('status','=', 'ACTIVE')
                    ->get()
                    ->toArray();*/
        return self::where('status', '=', 'ACTIVE')->get()->toArray();
    }

    /*Gets announcemt informatin using Ids*/
    public static function getAnnouncementDetails($aids = [])
    {
        return self::whereIn('announcement_id', $aids)->get()->toArray();
    }

    /*get announcement for reports */

    public static function getInBTWCreateAndUpdateAnnouncement($start_date = 0, $end_date = 0)
    {
        if ($start_date <= 0 && $end_date <= 0) {
            $start_date = Carbon::yesterday()->timestamp;
            $end_date = Carbon::today()->timestamp;
        }
        $time_line = [(int)$start_date, (int)$end_date];

        return self::where('schedule', '<', time())
            ->Where(function ($query) use ($time_line) {
                $query->orwhereBetween('created_at', $time_line)
                    ->orwhereBetween('updated_at', $time_line);
            })
            // ->where('expire_date', '>=', time())
            ->get()
            ->toArray();
    }

    public static function filterPrivateAnnouncements($aid)
    {
        return Announcement::where('status', '=', 'ACTIVE')->whereIn('announcement_id', $aid)->where('announcement_for', '!=', 'public')->lists('announcement_id');
    }

 /**
     * @param $query
     * @param array $filter_params
     */
    public function scopeFilter($query, $filter_params = [])
    {
        return $query->when(
            isset($filter_params["in_ids"]),
            function ($query) use ($filter_params) {
                return $query->whereIn("announcement_id", $filter_params["in_ids"]);
            }
        )->when(
            isset($filter_params["status_filter"]),
            function ($query) use ($filter_params) {
                return $query->where("status", $filter_params["status_filter"]);
            }
        )->when(
            !empty($filter_params["search_key"]),
            function ($query) use ($filter_params) {
                return $query->Where("announcement_title", "like", "%{$filter_params["search_key"]}%");
            }
        )->when(
            !empty($filter_params["order_by"]),
            function ($query) use ($filter_params) {
                return $query->orderBy(
                    key($filter_params["order_by"]),
                    $filter_params["order_by"][key($filter_params["order_by"])]
                );
            }
        )->when(
            isset($filter_params["start"]),
            function ($query) use ($filter_params) {
                return $query->skip((int)$filter_params["start"]);
            }
        )->when(
            isset($filter_params["limit"]),
            function ($query) use ($filter_params) {
                return $query->take((int)$filter_params["limit"]);
            }
        );
    }
}
