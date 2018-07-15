<?php

namespace App\Model;

use App\Enums\Post\PostStatus;
use Auth;
use Moloquent;

class Packet extends Moloquent
{
    protected $collection = 'packets';

    /**
     * Defines primary key on the model
     * @var string
     */
    protected $primaryKey = 'packet_id';

    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'packet_publish_date'];

    /**
     * @return \Jenssegers\Mongodb\Relations\BelongsTo
     */
    public function program()
    {
        return $this->belongsTo(Program::class, "program_slug", "packet_slug");
    }

    /**
     * Create has many relation with packet questions
     * @return \Jenssegers\Mongodb\Relations\HasMany
     */
    public function questions()
    {
        return $this->hasMany(PacketFaq::class, "packet_id");
    }

    protected $casts = [
        'packet_id' => 'int',
    ];

    public static function uniquePacketId()
    {
        return Sequence::getSequence('packet_id');
    }

    public static function getAllPackets($slug = '')
    {
        $returndata = [];
        if ($slug != '') {
            $returndata = self::where('feed_slug', '=', $slug)
                ->where('status', '!=', 'DELETED')
                ->get()
                ->toArray();
        } else {
            $returndata = self::get()->toArray();
        }

        return $returndata;
    }

    public static function getPackets($slug = '')
    {
        return self::where('feed_slug', '=', $slug)->where('status', '!=', 'DELETED')->get();
    }

    /**
     * @param string $slug
     * @return $this
     */
    public static function queryPackets($slug = '')
    {
        return self::where('feed_slug', '=', $slug)->where('status', '!=', 'DELETED');
    }

    public static function getPacket($slug = '')
    {
        // if($slug)
        return self::where('packet_slug', '=', $slug)->where('status', '!=', 'DELETED')->get()->toArray();
        // else
        // return Packet::where('status','!=','DELETED')->get()->toArray();
    }

    public static function getPacketByID($id = '')
    {
        // if($slug)
        return self::where('packet_id', '=', $id)->where('status', '!=', 'DELETED')->get()->toArray();
        // else
        // return Packet::where('status','!=','DELETED')->get()->toArray();
    }

    public static function getPacketsWithTypeAndPagination($feed_slug = '', $type = 'all', $start = 0, $limit = 10, $orderby = ['created_at' => 'desc'], $search = null)
    {
        $key = key($orderby);
        $value = $orderby[$key];
        if ($type == 'all') {
            if ($search) {
                return self::where('packet_title', 'like', '%' . $search . '%')->orWhere('packet_description', 'like', '%' . $search . '%')->where('feed_slug', '=', $feed_slug)->where('status', '!=', 'DELETED')->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            } else {
                return self::where('feed_slug', '=', $feed_slug)->where('status', '!=', 'DELETED')->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            }
        } elseif ($search) {
            return self::where('packet_title', 'like', '%' . $search . '%')->orWhere('packet_description', 'like', '%' . $search . '%')->where('status', '=', $type)->where('feed_slug', '=', $feed_slug)->where('status', '!=', 'DELETED')->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
        } else {
            return self::where('status', '=', $type)->where('feed_slug', '=', $feed_slug)->where('status', '!=', 'DELETED')->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
        }
    }

    public static function getPacketsCount($feed_slug = '', $type = 'all', $search = null)
    {
        if ($type == 'all') {
            if ($search) {
                return self::where('packet_title', 'like', '%' . $search . '%')->orWhere('packet_description', 'like', '%' . $search . '%')->where('feed_slug', '=', $feed_slug)->where('status', '!=', 'DELETED')->count();
            } else {
                return self::where('feed_slug', '=', $feed_slug)->where('status', '!=', 'DELETED')->count();
            }
        } elseif ($search) {
            return self::where('packet_title', 'like', '%' . $search . '%')->orWhere('packet_description', 'like', '%' . $search . '%')->where('status', '=', $type)->where('feed_slug', '=', $feed_slug)->where('status', '!=', 'DELETED')->count();
        } else {
            return self::where('status', '=', $type)->where('feed_slug', '=', $feed_slug)->where('status', '!=', 'DELETED')->count();
        }
    }

    public static function updateElements($slug, $elementarr)
    {
        return self::where('packet_slug', '=', $slug)->where('status', '!=', 'DELETED')->update(['elements' => $elementarr]);
    }

    public static function updatePacket($slug, $dataarr = [])
    {
        return self::where('packet_slug', '=', $slug)->where('status', '!=', 'DELETED')->update($dataarr);
    }

    //Added by Sahana
    //To display in more feed page
    public static function getPacketsUsingSlugs($program_slugs)
    {
        return self::whereIn('feed_slug', $program_slugs)
            ->where('status', '=', 'ACTIVE')
            ->where('packet_publish_date', '<=', time())
            ->orderby('updated_at', 'desc')
            ->get()
            ->toArray();
    }

    //To display in what to watch page
    public static function getPacketsUsingIds($packet_ids)
    {
        return self::whereIn('packet_id', $packet_ids)
            ->where('status', '=', 'ACTIVE')
            ->where('packet_publish_date', '<=', time())
            ->orderby('updated_at', 'desc')
            ->get()
            ->toArray();
    }

    public static function getPacketsUsingIdsSortBy($packet_ids, $sort_by, $records_per_page = 9, $page_no = 0, $orderby = 'updated_at')
    {
        $skip = $records_per_page * $page_no;
        if ($sort_by == 'old_to_new') {
            $sortby = 'asc';
        } elseif ($sort_by == 'a_z') {
            $orderby = 'title_lower';
            $sortby = 'asc';
        } elseif ($sort_by == 'z_a') {
            $orderby = 'title_lower';
            $sortby = 'desc';
        } else {
            $sortby = 'desc';
        }

        return self::whereIn('packet_id', $packet_ids)
            ->where('status', '=', 'ACTIVE')
            ->where('packet_publish_date', '<=', time())
            //->where('elements', '!=', [])
            ->orderby($orderby, $sortby)
            ->skip((int)$skip)
            ->take((int)$records_per_page)
            ->get()
            ->toArray();
    }

    public static function getPacketsCountUsingIds($packet_ids)
    {
        return self::whereIn('packet_id', $packet_ids)
            ->where('status', '=', 'ACTIVE')
            ->where('packet_publish_date', '<=', time())
            ->count();
    }

    //To display in feed detail view page
    public static function getPacketsUsingSlug($program_slug, $sort_by = 'updated_at')
    {
        if ($sort_by == 'created_at') {
            $sort_order = 'asc';
        } else {
            $sort_order = 'desc';
        }
        if (is_array($program_slug)) {
            $packet_details = self::whereIn('feed_slug', $program_slug)
                ->where('status', '=', 'ACTIVE')
                ->where('packet_publish_date', '<=', time())
                ->orderby($sort_by, $sort_order)
                ->get()
                ->groupBy('feed_slug')
                ->toArray();
        } else {
            $packet_details = self::where('feed_slug', '=', $program_slug)
                ->where('status', '=', 'ACTIVE')
                ->where('packet_publish_date', '<=', time())
                ->orderby($sort_by, $sort_order)
                ->get()
                ->toArray();
        }
        return $packet_details;
    }

    //To check element activities in what to watch method
    public static function getPacketElementsUsingSlug($program_slugs)
    {
        return self::whereIn('feed_slug', $program_slugs)//->where('elements', '!=', [])
        ->get(['packet_id', 'elements'])->toArray();
    }

    public static function getPacketIdsUsingSlugs($program_slugs)
    {
        return self::whereIn('feed_slug', $program_slugs)
            ->lists('packet_id')
            ->all();
    }

    public static function getPacketsCountUsingSlug($program_slug)
    {
        return self::where('feed_slug', '=', $program_slug)
            ->where('status', '=', 'ACTIVE')
            //->where('elements', '!=', [])
            ->where('packet_publish_date', '<=', time())
            ->count();
    }

    public static function getLikedPackets($program_slug)
    {
        return self::where('feed_slug', '=', $program_slug)
            ->where('favourited_count', '>', 0)
            ->where('status', '=', 'ACTIVE')
            ->where('packet_publish_date', '<=', time())
            ->count();
    }

    public static function getUpdatePacketFaq($packet_id)
    {
        $total_ques_private = self::where('packet_id', '=', (int)$packet_id)->max('total_ques_private') + 1;
        $total_ques_unanswered = self::where('packet_id', '=', (int)$packet_id)->max('total_ques_unanswered') + 1;

        self::where('packet_id', '=', (int)$packet_id)->update([
            'total_ques_private' => (int)$total_ques_private,
            'total_ques_unanswered' => (int)$total_ques_unanswered,
        ]);
    }

    public static function pluckPacketName($slug)
    {
        return self::where('packet_slug', '=', $slug)->where('status', '!=', 'DELETED')->value('packet_title');
    }

    public static function getPacketInfo($packet_id)
    {
        return self::where('packet_id', '=', (int)$packet_id)->get(['packet_slug', 'feed_slug', 'packet_title'])->toArray();
    }

    public static function updateFavouriteCount($packet_id, $count, $slug, $feed_slug)
    {
        $feed = Program::pluckFeedName($feed_slug);
        //echo "<pre>";print_r($feed);
        //echo $count;die;
        $feed = $feed[0];
        $uid = Auth::user()->uid;
        if ($count == 'true') {
            self::where('packet_id', '=', (int)$packet_id)->increment('favourited_count');
            User::where('uid', '=', (int)$uid)->push('favourited_packets', (int)$packet_id, true);
            $array = [
                'module' => 'packet',
                'action' => 'favourited',
                'module_name' => self::pluckPacketName($slug),
                'module_id' => (int)$packet_id,
                'feed_id' => (int)$feed['program_id'],
                'feed_name' => $feed['program_title'],
                'url' => 'program/packet/' . $slug,
            ];
        } else {
            self::where('packet_id', '=', (int)$packet_id)->decrement('favourited_count');
            User::where('uid', '=', (int)$uid)->pull('favourited_packets', (int)$packet_id);
            $array = [
                'module' => 'packet',
                'action' => 'unfavourited',
                'module_name' => self::pluckPacketName($slug),
                'module_id' => (int)$packet_id,
                'feed_id' => (int)$feed['program_id'],
                'feed_name' => $feed['program_title'],
                'url' => 'program/packet/' . $slug,
            ];
        }

        MyActivity::getInsertActivity($array);
    }

    public static function updateElementLikedCount($count, $element_id, $element_type, $packet_id, $slug, $feed_slug)
    {
        $feed = Program::pluckFeedName($feed_slug);
        $feed = $feed[0];
        $uid = Auth::user()->uid;
        $element_name = '';
        if ($count == 'true') {
            switch ($element_type) {
                case 'media':
                    $element_name = Dam::where('id', '=', (int)$element_id)->value('name');
                    Dam::where('id', '=', (int)$element_id)->increment('liked_count');
                    Dam::where('id', '=', (int)$element_id)->push('users_liked', (int)$uid, true);
                    break;
                case 'assessment':
                    $element_name = Quiz::where('quiz_id', '=', (int)$element_id)->value('quiz_name');
                    Quiz::where('quiz_id', '=', (int)$element_id)->increment('liked_count');
                    Quiz::where('quiz_id', '=', (int)$element_id)->push('users_liked', (int)$uid, true);
                    break;
                case 'event':
                    $element_name = Event::where('event_id', '=', (int)$element_id)->value('event_name');
                    Event::where('event_id', '=', (int)$element_id)->increment('liked_count');
                    Event::where('event_id', '=', (int)$element_id)->push('users_liked', (int)$uid, true);
                    break;
            }

            $array = [
                'module' => 'element',
                'action' => 'liked',
                'module_name' => $element_name,
                'module_id' => (int)$element_id,
                'element_type' => $element_type,
                'packet_id' => (int)$packet_id,
                'packet_name' => self::pluckPacketName($slug),
                'feed_id' => (int)$feed['program_id'],
                'feed_name' => $feed['program_title'],
                'url' => 'program/packet/' . $slug,
            ];
        } else {
            switch ($element_type) {
                case 'media':
                    $element_name = Dam::where('id', '=', (int)$element_id)->value('name');
                    Dam::where('id', '=', (int)$element_id)->decrement('liked_count');
                    Dam::where('id', '=', (int)$element_id)->pull('users_liked', (int)$uid, true);
                    break;
                case 'assessment':
                    $element_name = Quiz::where('quiz_id', '=', (int)$element_id)->value('quiz_name');
                    Quiz::where('quiz_id', '=', (int)$element_id)->decrement('liked_count');
                    Quiz::where('quiz_id', '=', (int)$element_id)->pull('users_liked', (int)$uid, true);
                    break;
                case 'event':
                    $element_name = Event::where('event_id', '=', (int)$element_id)->value('event_name');
                    Event::where('event_id', '=', (int)$element_id)->decrement('liked_count');
                    Event::where('event_id', '=', (int)$element_id)->pull('users_liked', (int)$uid, true);
                    break;
            }

            $array = [
                'module' => 'element',
                'action' => 'unliked',
                'module_name' => $element_name,
                'module_id' => (int)$element_id,
                'element_type' => $element_type,
                'packet_id' => (int)$packet_id,
                'packet_name' => self::pluckPacketName($slug),
                'feed_id' => (int)$feed['program_id'],
                'feed_name' => $feed['program_title'],
                'url' => 'program/packet/' . $slug,
            ];
        }

        MyActivity::getInsertActivity($array);
    }

    /*for statistic */
    public static function getLastThirtydaysCreatedPacketCount()
    {
        return self::where('status', '=', 'ACTIVE');
    }

    public static function getLastThirtydaysCreatedPacketqusCount()
    {
        $quscount = 0;
        $qus = self::where('status', '=', 'ACTIVE')->where('created_at', '>', strtotime('-30 day', time()))->orWhere('total_ques_public', '>', 0)->orWhere('total_ques_private', '>', 0)->get()->toArray();
        if (!empty($qus)) {
            foreach ($qus as $key => $value) {
                if (isset($value['total_ques_public']) && isset($value['total_ques_private'])) {
                    $quscount += $value['total_ques_public'] + $value['total_ques_private'];
                }
            }
        } else {
            return $quscount;
        }

        return $quscount;
    }

    public static function getLastThirtydaysCreatedPacketqusunansCount()
    {
        $unanscount = 0;
        $qus = self::where('status', '=', 'ACTIVE')->where('created_at', '>', strtotime('-30 day', time()))->orWhere('total_ques_unanswered', '>', 0)->get()->toArray();
        if (!empty($qus)) {
            foreach ($qus as $key => $value) {
                if (isset($value['total_ques_unanswered'])) {
                    $unanscount += $value['total_ques_unanswered'];
                }
            }
        } else {
            return $unanscount;
        }

        return $unanscount;
    }

    /*for UAR */
    public static function getUnansqusinPacket()
    {
        $post_ids = PacketFaq::PacketIDsOfUserChannels();
        if (!is_array($post_ids)) {
            $post_ids = [];
        }
        return self::where('status', '=', 'ACTIVE')
            ->whereIn('packet_id', $post_ids)
            ->Where('total_ques_unanswered', '>', 0)
            ->get(['total_ques_unanswered', 'feed_slug', 'packet_title'])
            ->toArray();
    }

    public static function getUnansqusinPacketCount()
    {
        return self::where('status', '=', 'ACTIVE')
            ->Where('total_ques_unanswered', '>', 0)
            ->count();
    }

    /*for reports*/
    public static function getPacketListFromFeed($feed_slugs = [])
    {
        if (!empty($feed_slugs)) {
            return self::where('status', '=', 'ACTIVE')
                ->whereIn('feed_slug', $feed_slugs)
                ->get(['packet_id'])
                ->toArray();
        }
    }

    /*
        Scope methods for querying
    */
    public static function scopeGetByTime($query, $starttime, $endtime)
    {
        if ($starttime && $endtime) {
            return $query->where('created_at', '>', $starttime)->where('created_at', '<', $endtime);
        }

        return $query;
    }

    /*for reports*/
    public static function getLastDayPosts()
    {

        /*$start_time = strtotime('yesterday midnight');
        $end_time = $start_time + 86400;

        return self::whereBetween('date', [$start_time, $end_time])
                    ->get(['packet_id', 'packet_slug', 'feed_slug', 'favourited_count'])
                    ->toArray();*/
        return self::get(['packet_id', 'packet_slug', 'feed_slug', 'favourited_count'])
            ->toArray();
    }

    /*Get post infromation for display of media relations*/
    public static function getPostDetailsUsingIds($pids = [])
    {
        return self::whereIn('packet_id', $pids)
            ->get()
            ->toArray();
    }

    /*Sandeep - delete post cover media*/
    public static function removePacketCoverMedia($pids = [])
    {
        self::whereIn('packet_id', $pids)->update(['packet_cover_media' => '']);
    }

    /*Sandeep - Remove media item from the post*/
    public static function removePacketItemMedia($pids = [], $item)
    {
        self::whereIn('packet_id', $pids)->pull('elements', ['id' => (int)$item]);
    }

    public static function getPostNameUsingIds($pids = [])
    {
        if (!empty($pids)) {
            return self::whereIn('packet_id', $pids)
                ->get(['packet_id', 'packet_title'])
                ->toArray();
        } else {
            return [];
        }
    }

    /**
     * used to get incomplete packet ids
     * @param [array] $[packets] [Array of packets]
     * @return array
     */
    public static function getIncompletePackets($packets)
    {
        $packet_ids = [];
        foreach ($packets as $packet) {
            $elements_count = count($packet['elements']);
            $activity_count = count(MyActivity::getPacketElementDetails(Auth::user()->uid, $packet['packet_id']));
            if (($activity_count != 0) && ($elements_count > $activity_count)) {
                $packet_ids[] = (int)$packet['packet_id'];
            }
        }
        return $packet_ids;
    }

    //For portal side assessment reports show packet name
    public static function getPacketsNameForQuizz($feed_slug = '', $quiz_id = 0)
    {
        if (!empty($feed_slug)) {
            return self::where('status', '=', 'ACTIVE')
                ->where('feed_slug', '=', $feed_slug)
                ->where('elements.type', '=', 'assessment')
                ->where('elements.id', '=', (int)$quiz_id)
                // ->get(['packet_title']);
                ->lists('packet_title')
                ->all();
        } else {
            return false;
        }
    }

    public static function getUniquePacketSlug($packet_name, $feed_slug)
    {
        $slug = strtolower(stripslashes(trim($packet_name)));   // Convert all the text to lower case
        $slug = str_replace('&amp;', '', $slug);
        $slug = str_replace('&amp', '', $slug);
        $slug = str_replace(' - ', '-', $slug);   // Replace any ' - ' sign with spaces on both sides to '-'
        $slug = str_replace(' & ', '-and-', $slug);   // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace('& ', '-and-', $slug);    // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace("'", '', $slug);  // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace('\\', '', $slug); // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace('/', '-', $slug); // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace(', ', '-', $slug);    // Replace any comma and a space to -
        $slug = str_replace('.com', 'dotcom', $slug); // Remove any dot and a space
        $slug = str_replace('.', '', $slug);  // Remove any dot and a space
        $slug = str_replace('   ', '-', $slug);   // replace space to -
        $slug = str_replace('  ', '-', $slug);    // replace space to -
        $slug = str_replace(' ', '-', $slug); // replace space to -
        $slug = str_replace('!', '', $slug);  // remove !
        $slug = str_replace('#', '', $slug);  // remove #
        $slug = str_replace('$', '', $slug);  // remove $
        $slug = str_replace(':', '', $slug);  // remove :
        $slug = str_replace(';', '', $slug);  // remove ;
        $slug = str_replace('[', '', $slug);  // remove [
        $slug = str_replace(']', '', $slug);  // remove ]
        $slug = str_replace('(', '', $slug);  // remove (
        $slug = str_replace(')', '', $slug);  // remove )
        $slug = str_replace('\n', '', $slug); // remove \n
        $slug = str_replace('\r', '', $slug); // remove \r
        $slug = str_replace('?', '', $slug);  // remove ?
        $slug = str_replace('`', '', $slug);  // remove `
        $slug = str_replace('%', '', $slug);  // remove %
        $slug = str_replace('&#39;', '', $slug);  // remove &#39; = '
        $slug = str_replace('&39;', '', $slug);   // remove &39; = '
        $slug = str_replace('&39', '', $slug);    // remove &39; = '
        $slug = str_replace('&quot;', '-', $slug);
        $slug = str_replace('\"', '-', $slug);
        $slug = str_replace('"', '-', $slug);
        $slug = str_replace('&lt;', '-', $slug);
        $slug = str_replace('&gt;', '-', $slug);
        $slug = str_replace('<', '', $slug);
        $slug = str_replace('>', '', $slug);


        return $feed_slug . '-' . $slug;
    }

    public static function getPacketList($packet_id)
    {
        return self::whereIn('packet_id', $packet_id)->get(['feed_slug', 'packet_title'])->toArray();
    }

    /**
     * Overrides dates mutator when field has '' as value
     * @return array
     */
    public function getDates()
    {
        $date_mutatuor = $this->dates;

        if (isset($this->attributes['updated_at']) && $this->attributes['updated_at'] == '') {
            $date_mutatuor = array_diff($date_mutatuor, ['updated_at']);
        }
        return $date_mutatuor;
    }

    /**
     * @param $query
     * @param array $filter_params
     */
    public function scopeFilter($query, $filter_params)
    {
        return $query->where("status", "!=", PostStatus::DELETED)->when(
            !empty($filter_params["program_slugs"]),
            function ($query) use ($filter_params) {
                return $query->whereIn("feed_slug", $filter_params["program_slugs"]);
            }
        );
    }

    /**
     * Scope a query to only include active quizzes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        $query->where('status', 'ACTIVE');
    }

    public static function IncrementField($post_id, $field_name)
    {
        Packet::where('packet_id', '=', (int) $post_id)->increment($field_name);
    }

    public static function DecrementField($post_id, $field_name)
    {
        Packet::where('packet_id', '=', (int)$post_id)->where($field_name, '>', 0)->decrement($field_name);
    }

    public static function getPostByID($id, $field_name)
    {
        return Packet::where($field_name, (int)$id)->where('status', '!=', 'DELETED');
    }

    public static function pushRelations($post_id, $field_name, $input_ids)
    {
        Packet::where('packet_id', '=', $post_id)->push($field_name, $input_ids);
    }

    public static function pullRelations($post_id, $field_name, $input_ids)
    {
        Packet::where('packet_id', '=', $post_id)->pull($field_name, (int)$input_ids);
    }

    public static function updateRelationsByID($post_id, $field_name, $data)
    {
        Packet::where('packet_id', '=', $post_id)
                    ->update([ $field_name => [] ]);
        Packet::where('packet_id', '=', $post_id)
                    ->update([ 
                            $field_name => $data, 
                            'updated_at' => time()
                        ]);
    }
}
