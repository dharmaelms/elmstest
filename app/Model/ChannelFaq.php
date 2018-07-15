<?php
namespace App\Model;

use App\Enums\Program\QuestionStatus;
use Auth;
use Moloquent;

class ChannelFaq extends Moloquent
{

    protected $table = 'channels_faq';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Defines primary key on the model
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    public static function getUniqueId()
    {
        return Sequence::getSequence('channel_faq_id');
    }

    /**
     * Create belongs to relation with program
     * @return \Jenssegers\Mongodb\Relations\BelongsTo
     */
    public function program()
    {
        return $this->belongsTo(Program::class, "program_id");
    }

    public static function getInsert($question, $program_id, $program_slug)
    {
        $id = self::getUniqueId();
        self::insert([
            'id' => (int)$id,
            'program_id' => (int)$program_id,
            'program_slug' => $program_slug,
            'user_id' => Auth::user()->uid,
            'username' => Auth::user()->username,
            'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
            'question' => $question,
            'access' => 'public',
            'like_count' => 0,
            'status' => 'UNANSWERED',
            'hidden' => 'no',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    public static function scopeHiddenFilter($query)
    {
        return $query;
    }

    public static function getChannelQuestions($program_id, $filter, $page_no, $records_per_page)
    {
        $skip = $records_per_page * $page_no;

        if ($filter == 'my_questions') {
            return self::HiddenFilter()
                ->where('program_id', '=', (int)$program_id)
                ->where('status', '!=', 'DELETED')
                ->where('username', '=', Auth::user()->username)
                ->orderBy('created_at', 'desc')
                ->skip((int)$skip)
                ->take($records_per_page)
                ->get()
                ->toArray();
        } elseif ($filter == 'other_questions') {
            return self::HiddenFilter()
                ->where('program_id', '=', (int)$program_id)
                ->where('status', '!=', 'DELETED')
                ->where('username', '!=', Auth::user()->username)
                ->orderBy('created_at', 'desc')
                ->skip((int)$skip)
                ->take($records_per_page)
                ->get()
                ->toArray();
        } else {
            return self::HiddenFilter()
                ->where('program_id', '=', (int)$program_id)
                ->where('status', '!=', 'DELETED')
                ->orderBy('created_at', 'desc')
                ->skip((int)$skip)
                ->take($records_per_page)
                ->get()
                ->toArray();
        }
    }

    public static function getChannelQuestionsCount($program_id)
    {
        return self::HiddenFilter()
            ->where('program_id', '=', (int)$program_id)
            ->where('status', '!=', 'DELETED')
            ->count();
    }

    public static function getQuestionsCount($question_id = null)
    {
        return self::where('id', '=', (int)$question_id)->where('status', '=', 'UNANSWERED')->count();
    }

    public static function getUpdate($question_id, $question)
    {
        return self::where('id', '=', (int)$question_id)->update(['question' => $question]);
    }

    public static function getDelete($question_id)
    {
        return self::where('id', '=', (int)$question_id)->update(['status' => 'DELETED']);
    }

    public static function getHideQuestion($question_id, $type)
    {
        if ($type == 'hide') {
            return self::where('id', '=', (int)$question_id)->update(['hidden' => 'yes']);
        } elseif ($type == 'unhide') {
            return self::where('id', '=', (int)$question_id)->update(['hidden' => 'no']);
        } else {
            return false;
        }
    }

    public static function updateQALikedCount($action, $question_id)
    {
        $uid = Auth::user()->uid;
        if ($action == 'like') {
            self::where('id', '=', (int)$question_id)->increment('like_count');
            self::where('id', '=', (int)$question_id)->push('users_liked', (int)$uid, true);
            return true;
        } elseif ($action == 'unlike') {
            self::where('id', '=', (int)$question_id)->decrement('like_count');
            self::where('id', '=', (int)$question_id)->pull('users_liked', (int)$uid, true);
            return true;
        } else {
            return false;
        }
    }

    public static function getLikedCount($question_id)
    {
        return self::where('id', '=', (int)$question_id)->value('like_count');
    }

    public static function getUserId($question_id)
    {
        return self::where('id', '=', (int)$question_id)->value('user_id');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filter_params
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, $filter_params = [])
    {
        return $query->when(
            array_has($filter_params, "in_program_ids"),
            function ($query) use ($filter_params) {
                return $query->whereIn("program_id", $filter_params["in_program_ids"]);
            }
        )->when(
            array_has($filter_params, "search_key") && !empty($filter_params["search_key"]),
            function ($query) use ($filter_params) {
                return $query->where(function ($query) use ($filter_params) {
                    return $query->where("question", "like", "%{$filter_params["search_key"]}%")
                        ->orwhere("created_by_name", "like", "%{$filter_params["search_key"]}%");
                });
            }
        )->when(
            array_has($filter_params, "status"),
            function ($query) use ($filter_params) {
                return $query->where("status", $filter_params["status"]);
            }
        )->when(
            !array_has($filter_params, "status"),
            function ($query) use ($filter_params) {
                return $query->where("status", "!=", QuestionStatus::DELETED);
            }
        )->when(
            array_has($filter_params, "order_by"),
            function ($query) use ($filter_params) {
                return $query->orderBy(
                    $filter_params["order_by"],
                    array_has($filter_params, "order_by_dir") ? $filter_params["order_by_dir"] : "desc"
                );
            }
        )->when(
            array_has($filter_params, "start"),
            function ($query) use ($filter_params) {
                return $query->skip((int)$filter_params["start"]);
            }
        )->when(
            array_has($filter_params, "limit"),
            function ($query) use ($filter_params) {
                return $query->take((int)$filter_params["limit"]);
            }
        );
    }

    public static function getUnansweredQuestions()
    {
        $now = time();

        if (Auth::user()->super_admin) {
            return self::where('status', '=', 'UNANSWERED')
                ->count();
        } else {
            $program_ids = Program::where('created_by', '=', Auth::user()->username)
                ->where('status', '=', 'ACTIVE')
                ->Where('program_display_startdate', '<=', $now)
                ->Where('program_display_enddate', '>=', $now)
                ->lists('program_id')
                ->all();

            return self::whereIn('program_id', $program_ids)
                ->where('status', '=', 'UNANSWERED')
                ->count();
        }
    }

    public static function getQuestionsByQuestionID($question_id = null)
    {
        return self::where('id', '=', (int)$question_id)->where('status', '!=', 'DELETED')->get()->toArray();
    }
}
