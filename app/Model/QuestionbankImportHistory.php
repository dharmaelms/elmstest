<?php

namespace App\Model;

use Auth;
use Moloquent;

class QuestionbankImportHistory extends Moloquent
{
    protected $table = 'questionbankimport_history';
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at'];

    public static function getInsertHistory($filename, $success_count, $failed_count, $status, $no_of_records)
    {
        self::insert([
            'id' => (int)self::getUniqueId(),
            'filename' => $filename,
            'success_count' => $success_count,
            'failed_count' => $failed_count,
            'status' => $status,
            'no_of_records' => $no_of_records,
            'created_at' => time(),
            'created_by' => Auth::user()->username,
        ]);
    }

    //function to generate unique id
    public static function getUniqueId()
    {
        return Sequence::getSequence('ques_bank_imp_his_id');
    }

    //function to get the imported questionbank history
    public static function getImportedHistory()
    {
        return self::orderBy('created_at', 'desc')->get();
    }
}
