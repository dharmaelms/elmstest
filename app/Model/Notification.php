<?php


namespace App\Model;

use Auth;
use Moloquent;
use Timezone;

class Notification extends Moloquent
{   
    protected $table = 'notifications';

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
    protected $dates = ['created_at'];

    public static function getMaxID()
    {
        return Sequence::getSequence('notification_id');
    }

    public static function getAddNotification($notificationjson = null)
    {
        if (!is_null($notificationjson)) {
            $notificationjson['notification_id'] = self::getMaxID();
            $notificationjson['is_read'] = false;
            $notificationjson['created_at'] = time();

            return self::insert($notificationjson);
        } else {
            return 0;
        }
    }

    public static function getInsertNotification($user_id = null, $from_module = null, $message = null)
    {
        if (!is_null($user_id) && !is_null($from_module) && !is_null($message)) {
            $notificationjson = [];
            $notificationjson['notification_id'] = self::getMaxID();
            $notificationjson['user_id'] = $user_id;
            $notificationjson['from_module'] = $from_module;
            $notificationjson['message'] = $message;
            $notificationjson['is_read'] = false;
            $notificationjson['created_at'] = time();

            return self::insert($notificationjson);
        } else {
            return 0;
        }
    }

    public static function updateReadNotification($notification_id = null)
    {
        if (!is_null($notification_id)) {
            $notificationjson = [];
            $notificationjson['is_read'] = true;
            $notificationjson['time_read'] = Timezone::convertToUTC(date('d-m-Y', time()), Auth::user()->timezone, 'U');

            return self::where('notification_id', '=', (int)$notification_id)->update($notificationjson, ['upsert' => true]);
        } else {
            return 0;
        }
    }

    public static function getUserNotification($user_id = null)
    {
        if (!is_null($user_id)) {
            return self::where('user_id', '=', $user_id)->count();
        } else {
            return self::count();
        }
    }

    public static function getNotification($user_id = null)
    {
        if (!is_null($user_id)) {
            return self::where('user_id', '=', $user_id)->where('is_read', '=', false)->get()->toArray();
        }

        return;
    }

    public static function getOneNotification($notification_id = null)
    {
        if (!is_null($notification_id)) {
            return self::where('notification_id', '=', $notification_id)->get()->toArray();
        }

        return;
    }

    public static function getNotificationwithPagenation($user_id = null, $start = 0, $limit = 10, $orderby = ['created_at' => 'desc'])
    {
        $key = key($orderby);
        $value = $orderby[$key];
        if (!is_null($user_id)) {
            return self::where('user_id', '=', $user_id)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
        } else {
            return self::where('is_read', '=', false)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
        }
    }

    public static function getDeleteNotification($user_id = null, $notification_id = null)
    {
        return false;
    }

    public static function getNotificationlatest($user_id = null, $start = 0, $limit = 5, $orderby = ['created_at' => 'desc'])
    {
        $key = key($orderby);
        $value = $orderby[$key];
        if (!is_null($user_id)) {
            return self::where('user_id', '=', (int)$user_id)->where('is_read', '=', false)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
        } else {
            return;
        }
    }

    public static function getNotificationforscroll($user_id, $start = 0, $limit = 5)
    {
        if (!is_null($user_id)) {
            return self::where('is_read', '=', false)->where('user_id', $user_id)->orderBy('created_at', 'desc')->skip((int)$start)->take((int)$limit)->get()->toArray();
        }/*else{
            return Announcement::where('status','=','ACTIVE')->where('announcement_for','=','All')->orderBy('created_at','desc')->skip((int)$start)->take((int)$limit)->get()->toArray();

        } */
        return 0;
    }

    /*for portal side  not read count*/

    public static function getNotReadNotificationCount($user_id = null)
    {
        if (!is_null($user_id)) {
            return self::where('is_read', '=', false)->where('user_id', $user_id)->count();
        } else {
            return 0;
        }
    }

    public static function getReadAndPeriodNotification($time_line)
    {
        return self::where('created_at', '<', $time_line)
            ->where('is_read', '=', true)
            ->get();
    }

    public static function getDeleteFlushedNotification($time_line)
    {
        return self::where('created_at', '<', $time_line)
            ->delete();
    }
}
