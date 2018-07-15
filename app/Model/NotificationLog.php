<?php

namespace App\Model;

use Moloquent;

class NotificationLog extends Moloquent
{
    protected $table = 'notifications_log';

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
    protected $dates = ['created_at', 'updated_at', 'time_send'];

    public static function getInsertNotification($user_ids = [], $from_module = '', $message = '')
    {
        if (!empty($user_ids) && $from_module != '' && $message != '') {
            $notificationjson = [];
            $notificationjson['user_ids'] = $user_ids;
            $notificationjson['from_module'] = $from_module;
            $notificationjson['message'] = $message;
            $notificationjson['is_send'] = false;
            $notificationjson['created_at'] = time();
            return self::insert($notificationjson);
        } else {
            return 0;
        }
    }

    public static function updateReadNotification($_id = null)
    {
        if (!is_null($_id)) {
            $notificationjson = [];
            $notificationjson['is_send'] = true;
            $notificationjson['time_send'] = time();
            $notificationjson['updated_at'] = time();
            return self::where('_id', '=', $_id)
                ->update($notificationjson, ['upsert' => true]);
        } else {
            return 0;
        }
    }

    public static function getGetUnProcessNotifications()
    {
        return self::where('is_send', '=', false)
            ->get();
    }

    public static function deleteNotification($_id)
    {
            return self::where('_id', '=', $_id)->delete();

    }
}
