<?php


namespace App\Model;

use Moloquent;

class CronLog extends Moloquent
{

    protected $collection = 'cron_logs';
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['create_date', 'update_date'];

    public static function getNextSequence()
    {
        return Sequence::getSequence('cron_log_id');
    }

    public static function getInsertData($module_name = '', $status = '')
    {
        if ($module_name != '' && $status != '') {
            $data = [
                'id' => CronLog::getNextSequence(),
                'module_name' => $module_name,
                'status' => $status,
                'create_date' => time()
            ];
            $res = self::insert($data);
            if ($res) {
                return self::max('id');
            } else {
                return 0;
            }
        }
    }

    public static function getupdatedata($module_name = '', $status = '', $id = 0)
    {
        $data = [
            'module_name' => $module_name,
            'status' => $status,
            'update_date' => time()
        ];
        return self::where('id', '=', (int)$id)->update($data, ['upsert' => true]);
    }

    public static function getDetailsDateRange($channel_id = 0, $orderby = 'asc')
    {
        return self::where('channel_id', '=', $channel_id)->get()->toArray();
    }

    public static function getCronLogswithPagenation($start = 0, $limit = 10, $orderby = ['created_at' => 'desc'], $search = null)
    {
        $key = key($orderby);
        $value = $orderby[$key];
        if ($search) {
            return self::orwhere('user_name', 'like', '%' . $search . '%')
                ->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
        } else {
            return self::orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
        }
    }

    public static function getCronLogsSearchCount()
    {
        return self::count();
    }
}
