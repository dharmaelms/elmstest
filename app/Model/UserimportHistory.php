<?php

namespace App\Model;

use Auth;
use Moloquent;

class UserimportHistory extends Moloquent
{
    protected $table = 'userimport_history';
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at'];

    public static function getInsertHistory($filename, $success_count, $failed_count, $status, $no_of_records)
    {
        $hid = self::uniqueId();
        self::insert([
            'hid' => (int)$hid,
            'filename' => $filename,
            'success_count' => $success_count,
            'failed_count' => $failed_count,
            'status' => $status,
            'no_of_records' => $no_of_records,
            'created_at' => time(),
            'created_by' => Auth::user()->username,
        ]);
    }

    //function to generate unique user id
    public static function uniqueId()
    {
        return Sequence::getSequence('user_history_id');
    }

    //function to get the imported user history
    public static function getImportedHistory()
    {
        return self::orderBy('created_at', 'desc')->get();
    }
}
