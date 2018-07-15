<?php
namespace App\Model;

use Auth;
use Moloquent;

class ChannelFaqAnswers extends Moloquent
{

    protected $table = 'channels_faq_ans';

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
        return Sequence::getSequence('channel_faq_answers_id');
    }

    public static function getInsert($question_id, $answer, $parent_id, $userid)
    {
        $id = self::getUniqueId();
        self::insert([
            'id' => (int)$id,
            'ques_id' => (int)$question_id,
            'user_id' => Auth::user()->uid,
            'username' => Auth::user()->username,
            'answer' => $answer,
            'status' => 'ACTIVE',
            'hidden' => 'no',
            'parent_id' => $parent_id,
            'created_at' => time(),
            'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
        ]);

        if (Auth::user()->uid != $userid) {
            ChannelFaq::where('id', '=', (int)$question_id)->update(['status' => 'ANSWERED']);
        }
    }

    public static function scopeHiddenFilter($query, $admin = null)
    {
        if ($admin == 'admin') {
            return $query;
        } else {
            return $query->where('hidden', '=', 'no');
        }
    }

    public static function getAnswersByQuestionID($question_id, $admin = null)
    {
        return self::HiddenFilter($admin)->where('ques_id', '=', (int)$question_id)->where('status', '!=', 'DELETED')->orderBy('created_at', 'asc')->get()->toArray();
    }

    public static function getAnswersByAnswerID($answer_id = null)
    {
        return self::where('id', '=', (int)$answer_id)->where('status', '!=', 'DELETED')->get()->toArray();
    }

    public static function getAdminAnswers($question_id = null, $userid = null)
    {
        return self::where('ques_id', '=', (int)$question_id)->where('user_id', '!=', (int)$userid)->where('status', '!=', 'DELETED')->get()->toArray();
    }

    public static function getDelete($answer_id)
    {
        return self::where('id', '=', (int)$answer_id)->update(['status' => 'DELETED']);
    }

    public static function getHideAnswer($answer_id, $type)
    {
        if ($type == 'hide') {
            return self::where('id', '=', (int)$answer_id)->update(['hidden' => 'yes']);
        } elseif ($type == 'unhide') {
            return self::where('id', '=', (int)$answer_id)->update(['hidden' => 'no']);
        } else {
            return false;
        }
    }
}
