<?php

namespace App\Model;

use App\Enums\Program\QuestionStatus;
use Auth;
use Moloquent;

class PacketFaq extends Moloquent
{
    protected $table = 'packets_faq';

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

    /**
     * Create belongs to relation with packet
     * @return \Jenssegers\Mongodb\Relations\BelongsTo
     */
    public function packet()
    {
        return $this->belongsTo(Packet::class, "packet_id");
    }

    public static function getInsert($question, $packet_id, $slug, $feed_slug)
    {
        $id = self::uniqueId();
        $question = self::insert([
            'id' => (int)$id,
            'packet_id' => (int)$packet_id,
            'user_id' => Auth::user()->uid,
            'username' => Auth::user()->username,
            'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
            'question' => $question,
            'access' => 'private',
            'like_count' => 0,
            'status' => 'UNANSWERED',
            'created_at' => time(),
        ]);

        Packet::getUpdatePacketFaq($packet_id);

        $feed = Program::pluckFeedName($feed_slug);
        $feed = $feed[0];

        $array = [
            'module' => 'QAs',
            'action' => 'posted',
            'module_name' => 'question',
            'module_id' => (int)$id,
            'feed_id' => (int)$feed['program_id'],
            'feed_name' => $feed['program_title'],
            'packet_id' => (int)$packet_id,
            'packet_name' => Packet::pluckPacketName($slug),
            'url' => 'program/packet/' . $slug,
        ];
        MyActivity::getInsertActivity($array);

        return $id;
    }

    public static function updateQALikedCount($liked, $qid, $packet_id, $slug, $packet_name, $feed_slug)
    {
        $feed = Program::pluckFeedName($feed_slug);
        $feed = $feed[0];
        $uid = Auth::user()->uid;
        if ($liked == 'true') {
            self::where('id', '=', (int)$qid)->increment('like_count');
            self::where('id', '=', (int)$qid)->push('users_liked', (int)$uid, true);
            $array = [
                'module' => 'QAs',
                'action' => 'liked',
                'module_name' => 'question',
                'module_id' => (int)$qid,
                'feed_id' => (int)$feed['program_id'],
                'feed_name' => $feed['program_title'],
                'packet_id' => (int)$packet_id,
                'packet_name' => $packet_name,
                'url' => 'program/packet/' . $slug,
            ];
        } else {
            self::where('id', '=', (int)$qid)->decrement('like_count');
            self::where('id', '=', (int)$qid)->pull('users_liked', (int)$uid, true);
            $array = [
                'module' => 'QAs',
                'action' => 'unliked',
                'module_name' => 'question',
                'module_id' => (int)$qid,
                'feed_id' => (int)$feed['program_id'],
                'feed_name' => $feed['program_title'],
                'packet_id' => (int)$packet_id,
                'packet_name' => $packet_name,
                'url' => 'program/packet/' . $slug,
            ];
        }

        MyActivity::getInsertActivity($array);
    }

    //function to generate unique user id
    public static function uniqueId()
    {
        return Sequence::getSequence('packet_faq_id');
    }

    public static function getDelete($qid, $userid)
    {
        $array = PacketFaqAnswers::getOnlyPublicIds($qid, $userid);
        $array = array_map('intval', $array);
        if (in_array((int)$qid, $array)) {
            return false;
        } else {
            PacketFaqAnswers::where('ques_id', '=', (int)$qid)->where('user_id', '=', (int)$userid)->update(['status' => 'DELETED']);
            return self::where('id', '=', (int)$qid)->update(['status' => 'DELETED']);
        }
    }

    public static function getUpdate($qid, $faq, $userid)
    {
        $array = PacketFaqAnswers::getOnlyPublicIds($qid, $userid);
        $array = array_map('intval', $array);
        if (in_array((int)$qid, $array)) {
            return false;
        } else {
            return self::where('id', '=', (int)$qid)->update(['question' => $faq]);
        }
    }

    public static function getPublicQuestions($packet_id, $records_per_page, $page_no)
    {
        $skip = $records_per_page * $page_no;

        return self::where('packet_id', '=', (int)$packet_id)
            ->where('status', '=', 'ANSWERED')
            ->where('access', '=', 'public')
            ->orderBy('created_at', 'desc')
            ->skip((int)$skip)
            ->take((int)$records_per_page)
            ->get()
            ->toArray();
    }

    public static function getUserQuestions($packet_id, $records_per_page = 9, $page_no = 0)
    {
        $skip = $records_per_page * $page_no;

        return self::where('packet_id', '=', (int)$packet_id)
            ->where('status', '!=', 'DELETED')
            ->orderBy('created_at', 'desc')
            ->skip((int)$skip)
            ->take((int)$records_per_page)
            ->get()
            ->toArray();
    }

    public static function getQuestionsByQuestionID($question_id = null)
    {
        return self::where('id', '=', (int)$question_id)->where('status', '!=', 'DELETED')->get();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filter_params
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, $filter_params = [])
    {
        return $query->when(
            array_has($filter_params, "packet_id"),
            function ($query) use ($filter_params) {
                if (is_array($filter_params["packet_id"])) {
                    return $query->whereIn("packet_id", $filter_params["packet_id"]);
                } else {
                    return $query->where("packet_id", $filter_params["packet_id"]);
                }
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
                    array_has($filter_params, "order_by_dir")? $filter_params["order_by_dir"] : "desc"
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

    /*for statistic */
    public static function getLastThirtydaysCreatedPacketFaqUnansCount()
    {
        return self::where('status', '=', 'UNANSWERED')->where('created_at', '>', strtotime('-30 day', time()))->get()->count();
    }

    /*for statistic */
    public static function getLastThirtydaysCreatedPacketFaqCount()
    {
        return self::where('status', '!=', 'DELETED')->where('created_at', '>', strtotime('-30 day', time()))->get()->count();
    }

    public static function getLastThirtydaysCreatedPacketFaqUnansslug()
    {
        return self::where('status', '=', 'UNANSWERED')
            ->get()->toArray();
    }

    /*Sandeep - get all questions*/
    public static function getAllQuestions($packet_id = '')
    {
        if ($packet_id == '') {
            return self::where('status', '!=', 'DELETED')
                ->get()->toArray();
        } else {
            return self::where('packet_id', '=', (int)$packet_id)
                ->where('status', '!=', 'DELETED')
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
        }
    }

    /* Delete related questions, if post gets deletd */
    public static function getDeletePostSpecificQuestions($post_id = null)
    {
        return self::where('packet_id', '=', (int)$post_id)->update(['status' => 'DELETED']);
    }

    /*Get all packet ids using channel slug belongs to user*/
    public static function scopePacketIDsOfUserChannels()
    {
        $user_id = Auth::user()->uid;
        $packet_ids = [];
        $channel_ids = User::getAssignedContentFeed($user_id);
        if (isset($channel_ids) && !empty($channel_ids) && is_array($channel_ids)) {
            $program_slugs = Program::getCFSlugFromFeedID($channel_ids);
            $program_slug_array = [];
            foreach ($program_slugs as $each) {
                $program_slug_array[] = $each['program_slug'];
            }
            return $packet_ids = Packet::getPacketIdsUsingSlugs($program_slug_array);
        } else {
            return $packet_ids;
        }
    }

    public static function scopeQuestionSearch($query, $search = null)
    {
        if ($search != null) {
            $query->where('question', 'like', '%' . $search . '%')->orwhere('created_by_name', 'like', '%' . $search . '%');
        }
        return $query;
    }

    public static function scopeQuestionFilter($query, $filter = 'all')
    {
        if ($filter != 'all') {
            $query->where('status', '=', $filter);
        }
        return $query;
    }

    public static function scopeGetOrderBy($query, $orderby = ['created_at' => 'desc'])
    {
        $key = key($orderby);
        $value = $orderby[$key];
        return $query->orderBy($key, $value);
    }

    public static function scopeGetByPagination($query, $start = false, $limit = false)
    {
        return $query->skip((int)$start)->take((int)$limit);
    }

    public static function getQuestionWithTypeAndPagination($post_ids, $type = 'all', $start = false, $limit = false, $orderby = ['created_at' => 'desc'], $search = null)
    {
        return self::whereIn('packet_id', $post_ids)
                ->QuestionSearch($search)
                ->QuestionFilter($type)
                ->where('status', '!=', 'DELETED')
                ->GetOrderBy($orderby)
                ->GetByPagination((int)$start, (int)$limit)
                ->get();
    }

    public static function getUpdateFieldByQuestionId($questionid, $fieldname, $fieldvalue)
    {
        return self::where('id', '=', (int)$questionid)->update([$fieldname => $fieldvalue]);
    }
}
