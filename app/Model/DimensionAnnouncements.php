<?php

namespace App\Model;

use Carbon;
use Moloquent;

class DimensionAnnouncements extends Moloquent
{   
    protected $collection = 'dim_announcements';
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'create_date', 'update_date'];

    public static function getNextSequence()
    {
        return Sequence::getSequence('dim_announcement_id');
    }

    public static function getInsertData($data = [])
    {
        return self::insert($data);
    }

    public static function getupdatedata($data = [], $id = 0)
    {
        return self::where('announcement_id', '=', (int)$id)->update($data, ['upsert' => true]);
    }

    public static function getCheckAnnouncementExist($announcement_id = 0)
    {
        return self::where('announcement_id', '=', (int)$announcement_id)->first();
    }

    public static function getChannelSetResult($start = 0, $limit = 7, $start_date = 0, $end_date = 0)
    {
        if ($start_date <= 0 && $end_date <= 0) {
            $start_date = Carbon::yesterday()->timestamp;
            $end_date = Carbon::today()->timestamp;
        }
        $time_line = [(int)$start_date, (int)$end_date];

        return self::where(function ($query) use ($time_line) {
            $query->orWhereBetween('create_date', $time_line)
                ->orWhereBetween('update_date', $time_line);
        })
            ->skip((int)$start)->take((int)$limit)->get()->toArray();
    }

    public static function getChannelSetResultCSV($start_date = 0, $end_date = 0)
    {
        if ($start_date <= 0 && $end_date <= 0) {
            $start_date = Carbon::yesterday()->timestamp;
            $end_date = Carbon::today()->timestamp;
        }
        $time_line = [(int)$start_date, (int)$end_date];
        return self::where(function ($query) use ($time_line) {
            $query->orWhereBetween('create_date', $time_line)
                ->orWhereBetween('update_date', $time_line);
        })
            ->get()
            ->toArray();
    }
}
