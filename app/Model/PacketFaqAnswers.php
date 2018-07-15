<?php

namespace App\Model;

use Auth;
use Carbon;
use Moloquent;
use Timezone;

class PacketFaqAnswers extends Moloquent
{

    protected $table = 'packets_faq_ans';

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
    protected $dates = ['created_at', 'updated_at'];


    public static function getUniqueId()
    {
        return Sequence::getSequence('packet_faq_answers_id');
    }

    public static function getAnswers($packet_id = null)
    {
        return self::where('packet_id', '=', (int)$packet_id)->get();
    }

    public static function getAnswersByAnswerID($answer_id = null)
    {
        return self::where('id', '=', (int)$answer_id)->get();
    }

    public static function getAnswersByQuestionID($question_id = null, $userid = null)
    {
        if ($userid) {
            return self::where('ques_id', '=', (int)$question_id)->where('user_id', '!=', (int)$userid)->get();
        } else {
            return self::where('ques_id', '=', (int)$question_id)->get();
        }
    }

    public static function getOnlyPublicIds($question_id, $userid)
    {
        return self::where('ques_id', '=', (int)$question_id)->where('user_id', '!=', (int)$userid)->lists('ques_id')->all();
    }

    public static function getDisplayDate($utc_time = 0)
    {
        if (Auth::check()) {
            $tz = Auth::user()->timezone;
        } else {
            $tz = config('app.default_timezone');
        }

        $date = Carbon::createFromTimestampUTC(Timezone::getTimeStamp($utc_time))->timezone($tz);
        if ($date->isToday()) {
            return 'Today';
        } elseif ($date->isYesterday()) {
            return 'Yesterday';
        } else {
            return $date->format(config('app.date_format'));
        }
    }

    public static function getAnswerWithTypeAndPagination($id = '', $type = 'all', $start = 0, $limit = 10, $orderby = ['created_at' => 'desc'], $search = null)
    {
        return self::AnswerByPacket($id)
            ->AnswerSearch($search)
            ->AnswerFilter($type)
            ->where('status', '!=', 'DELETED')
            ->GetOrderBy($orderby)
            ->GetByPagination($start, $limit)
            ->GetAsArray();
    }

    public static function getAnswerCount($id = '', $type = 'all', $search = null)
    {
        return self::AnswerByPacket($id)
            ->AnswerSearch($search)
            ->AnswerFilter($type)
            ->where('status', '!=', 'DELETED')
            ->count();
    }

    /* Scopes for querying starts here */

    public static function scopeAnswerByPacket($query, $id = null)
    {
        if ($id) {
            $query->where('packet_id', '=', $id);
        }

        return $query;
    }

    public static function scopeAnswerFilter($query, $filter = 'all')
    {
        if ($filter != 'all') {
            $query->where('status', '=', $filter);
        }

        return $query;
    }

    public static function scopeAnswerSearch($query, $search = null)
    {
        if ($search != null) {
            $query->where('answer', 'like', '%' . $search . '%');
        }

        return $query;
    }

    public static function scopeGetOrderBy($query, $orderby = ['created_at' => 'desc'])
    {
        $key = key($orderby);
        $value = $orderby[$key];

        return $query->orderBy($key, $value);
    }

    public static function scopeGetByPagination($query, $start = 0, $limit = 10)
    {
        return $query->skip((int)$start)->take((int)$limit);
    }

    public static function scopeGetAsArray($query)
    {
        return $query->get()->toArray();
    }

    public static function DeleteRecord($question_id)
    {
        return self::where('id', '=', (int)$question_id)->delete();
    }

    /* Scopes for querying ends here */
}
