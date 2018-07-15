<?php

namespace App\Model;

use App\Enums\Program\ProgramStatus;
use Auth;
use Carbon\Carbon;
use App\Model\UserGroup;
use App\Model\Package\Entity\Package;
use App\Model\PromoCode;
use DB;
use Moloquent;
use Timezone;

class Program extends Moloquent
{
    protected $collection = 'program';

    /**
     * Defines primary key on the model
     * @var string
     */
    protected $primaryKey = 'program_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $casts = [
        'program_id' => 'int',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'program_startdate', 'program_enddate', 'program_display_startdate', 'program_display_enddate', 'last_activity'];

    public static function uniqueProductId()
    {
        return Sequence::getSequence('program_id');
    }

    /**
     * @return \Jenssegers\Mongodb\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany(Packet::class, "feed_slug", "program_slug");
    }

    /**
     * Create has many relation with program questions
     * @return \Jenssegers\Mongodb\Relations\HasMany
     */
    public function questions()
    {
        return $this->hasMany(ChannelFaq::class, "program_id");
    }

    public function getTitleAttribute()
    {
        return html_entity_decode($this->program_title);
    }

    public function getShortNameAttribute()
    {
        return html_entity_decode($this->program_shortname);
    }

    public static function getAllContentFeeds()
    {
        return self::getAllProgramByIDOrSlug('content_feed');
    }

    public static function getAllProgramByIDOrSlug($type = 'all', $slug = '', $filter_params = [])
    {
        $return_data = [];
        if ($type == 'all') {
            if ($slug != '') {
                $return_data = self::where('program_slug', '=', $slug)
                    ->filter($filter_params)
                    ->get();
            } else {
                $return_data = self::filter($filter_params)
                    ->get();
            }
        } else {
            if ($slug != '') {
                $return_data = self::where('program_type', '=', $type)
                    ->where('program_slug', '=', $slug)
                    ->filter($filter_params)
                    ->get();
            } else {
                $return_data = self::where('program_type', '=', $type)
                    ->filter($filter_params)
                    ->get();
            }
        }
        return $return_data;
    }

    public static function getContentFeedWithTypeAndPagination(
        $type = 'all',
        $start = 0,
        $limit = 10,
        $orderby = ['created_at' => 'desc'],
        $search = null,
        $relinfo = null,
        $userid = null,
        $usergrp = [],
        $subtype = 'all',
        $filter = 'all',
        $field = 'relations.active_user_feed_rel',
        $visibility = 'all',
        $sellability = 'all',
        $feed_title = null,
        $shortname = null,
        $created_date = null,
        $updated_date = null,
        $description = null,
        $feed_tags = null,
        $custom_field_name = [],
        $custom_field_value = [],
        $access = 'all',
        $category = null,
        $channel_name = null,
        $get_created_date = '=',
        $get_updated_date = '=',
        $filter_params = []
    ) {

        if ($subtype == 'all') {
            if ($filter == 'all') {
                return self::where('program_type', '=', 'content_feed')
                    ->when(!empty($filter_params), function ($query) use ($filter_params) {
                        if (array_has($filter_params, "not_in_ids")) {
                            return $query->whereNotIn("program_id", $filter_params["not_in_ids"]);
                        } elseif (array_has($filter_params, "in_ids")) {
                            return $query->whereIn("program_id", $filter_params["in_ids"]);
                        }
                    })->FeedSearch($search)
                    //->FeedFilter($type, $relinfo)
                    ->VisibilityFilter($visibility)
                    ->SellabilityFilter($sellability)
                    ->AccessFilter($access)
                    ->TitleFilter($feed_title)
                    ->ShortnameFilter($shortname)
                    ->CreateddateFilter($created_date, $get_created_date)
                    ->UpdateddateFilter($updated_date, $get_updated_date)
                    ->DescriptionFilter($description)
                    ->FeedtagsFilter($feed_tags)
                    ->CategoryFilter($category)
                    ->ChannelFilter($channel_name)
                    ->CustomfieldFilter($custom_field_name, $custom_field_value)
                    ->where('status', '!=', 'DELETED')
                    //->UserRelation($userid, $usergrp)
                    ->GetOrderBy($orderby)
                    ->GetByPagination($start, $limit)
                    ->GetAsArray();
            } else {
                return self::where('program_type', '=', 'content_feed')
                    ->where('program_sub_type', '=', $filter)
                    ->when(!empty($filter_params), function ($query) use ($filter_params) {
                        if (array_has($filter_params, "not_in_ids")) {
                            return $query->whereNotIn("program_id", $filter_params["not_in_ids"]);
                        } elseif (array_has($filter_params, "in_ids")) {
                            return $query->whereIn("program_id", $filter_params["in_ids"]);
                        }
                    })->FeedSearch($search)
                    //->FeedFilter($type, $relinfo)
                    ->VisibilityFilter($visibility)
                    ->SellabilityFilter($sellability)
                    ->AccessFilter($access)
                    ->TitleFilter($feed_title)
                    ->ShortnameFilter($shortname)
                    ->CreateddateFilter($created_date, $get_created_date)
                    ->UpdateddateFilter($updated_date, $get_updated_date)
                    ->DescriptionFilter($description)
                    ->FeedtagsFilter($feed_tags)
                    ->CategoryFilter($category)
                    ->ChannelFilter($channel_name)
                    ->CustomfieldFilter($custom_field_name, $custom_field_value)
                    ->where('status', '!=', 'DELETED')
                    //->UserRelation($userid, $usergrp)
                    ->GetOrderBy($orderby)
                    ->GetByPagination($start, $limit)
                    ->GetAsArray();
            }
        } else {
            if ($type == 'all') {
                return self::where('program_type', '=', 'content_feed')
                    ->where('program_sub_type', '!=', 'collection')
                    ->when(!empty($filter_params), function ($query) use ($filter_params) {
                        if (array_has($filter_params, "not_in_ids")) {
                            return $query->whereNotIn("program_id", $filter_params["not_in_ids"]);
                        } elseif (array_has($filter_params, "in_ids")) {
                            return $query->whereIn("program_id", $filter_params["in_ids"]);
                        }
                    })->FeedSearch($search)
                    ->where('status', '!=', 'DELETED')
                    ->GetOrderBy($orderby)
                    ->GetByPagination($start, $limit)
                    ->GetAsArray();
            } else {
                if ($type == 'assigned') {
                    $program_ids =[];
                    if( key($relinfo) == "usergroup") {
                        $program_ids = Package::where('user_group_ids', (int)$relinfo[key($relinfo)])
                                    ->get()
                                    ->pluck('program_ids')->flatten()->all();
                    }             
                   return self::where('program_type', '=', 'content_feed')
                        ->where('program_sub_type', '!=', 'collection')
                        ->FeedSearch($search)
                        ->where('status', '!=', 'DELETED')
                        ->where(function($q) use($program_ids, $relinfo, $field){
                            $q->whereIn('program_id', $program_ids)
                            ->orWhere($field, '=', (int)$relinfo[key($relinfo)]);
                        })
                        ->GetOrderBy($orderby)
                        ->GetByPagination($start, $limit)
                        ->GetAsArray();
                }
                if ($type == 'nonassigned') {
                    return self::where('program_type', '=', 'content_feed')
                        ->where('program_sub_type', '!=', 'collection')
                        ->when(!empty($filter_params), function ($query) use ($filter_params) {
                            if (array_has($filter_params, "not_in_ids")) {
                                return $query->whereNotIn("program_id", $filter_params["not_in_ids"]);
                            } elseif (array_has($filter_params, "in_ids")) {
                                return $query->whereIn("program_id", $filter_params["in_ids"]);
                            }
                        })->FeedSearch($search)
                        ->where('status', '!=', 'DELETED')
                        ->where($field, '!=', (int)$relinfo[key($relinfo)])
                        ->GetOrderBy($orderby)
                        ->GetByPagination($start, $limit)
                        ->GetAsArray();
                }
            }
        }
    }

    public static function getContentFeedCount(
        $type = 'all',
        $search = null,
        $relinfo = null,
        $userid = null,
        $usergrp = [],
        $subtype = 'all',
        $filter = 'all',
        $field = 'relations.active_user_feed_rel',
        $visibility = 'all',
        $sellability = 'all',
        $feed_title = null,
        $shortname = null,
        $created_date = null,
        $updated_date = null,
        $description = null,
        $feed_tags = null,
        $custom_field_name = [],
        $custom_field_value = [],
        $access = 'all',
        $category = null,
        $channel_name = null,
        $get_created_date = '=',
        $get_updated_date = '=',
        $filter_params = []
    ) {
        if ($subtype == 'all') {
            if ($filter == 'all') {
                return self::where('program_type', '=', 'content_feed')
                    ->when(!empty($filter_params), function ($query) use ($filter_params) {
                        if (array_has($filter_params, "not_in_ids")) {
                            return $query->whereNotIn("program_id", $filter_params["not_in_ids"]);
                        } elseif (array_has($filter_params, "in_ids")) {
                            return $query->whereIn("program_id", $filter_params["in_ids"]);
                        }
                    })->FeedSearch($search)
                    //->FeedFilter($type, $relinfo)
                    ->VisibilityFilter($visibility)
                    ->SellabilityFilter($sellability)
                    ->AccessFilter($access)
                    ->TitleFilter($feed_title)
                    ->ShortnameFilter($shortname)
                    ->CreateddateFilter($created_date, $get_created_date)
                    ->UpdateddateFilter($updated_date, $get_updated_date)
                    ->DescriptionFilter($description)
                    ->FeedtagsFilter($feed_tags)
                    ->CategoryFilter($category)
                    ->ChannelFilter($channel_name)
                    ->CustomfieldFilter($custom_field_name, $custom_field_value)
                    ->where('status', '!=', 'DELETED')
                    //->UserRelation($userid, $usergrp)
                    ->count();
            } else {
                return self::where('program_type', '=', 'content_feed')
                    ->when(!empty($filter_params), function ($query) use ($filter_params) {
                        if (array_has($filter_params, "not_in_ids")) {
                            return $query->whereNotIn("program_id", $filter_params["not_in_ids"]);
                        } elseif (array_has($filter_params, "in_ids")) {
                            return $query->whereIn("program_id", $filter_params["in_ids"]);
                        }
                    })->where('program_sub_type', '=', $filter)
                    ->FeedSearch($search)
                    //->FeedFilter($type, $relinfo)
                    ->VisibilityFilter($visibility)
                    ->SellabilityFilter($sellability)
                    ->AccessFilter($access)
                    ->TitleFilter($feed_title)
                    ->ShortnameFilter($shortname)
                    ->CreateddateFilter($created_date, $get_created_date)
                    ->UpdateddateFilter($updated_date, $get_updated_date)
                    ->DescriptionFilter($description)
                    ->FeedtagsFilter($feed_tags)
                    ->CategoryFilter($category)
                    ->ChannelFilter($channel_name)
                    ->CustomfieldFilter($custom_field_name, $custom_field_value)
                    ->where('status', '!=', 'DELETED')
                    //->UserRelation($userid, $usergrp)
                    ->count();
            }
        } else {
            if ($type == 'all') {
                return self::where('program_type', '=', 'content_feed')
                    ->where('program_sub_type', '!=', 'collection')
                    ->when(!empty($filter_params), function ($query) use ($filter_params) {
                        if (array_has($filter_params, "not_in_ids")) {
                            return $query->whereNotIn("program_id", $filter_params["not_in_ids"]);
                        } elseif (array_has($filter_params, "in_ids")) {
                            return $query->whereIn("program_id", $filter_params["in_ids"]);
                        }
                    })->FeedSearch($search)
                    ->where('status', '!=', 'DELETED')
                    ->count();
            } else {
                if ($type == 'assigned') {
                    $program_ids = [];
                    if( key($relinfo) == "usergroup") {
                        $program_ids = Package::where('user_group_ids', (int)$relinfo[key($relinfo)])
                                    ->get()
                                    ->pluck('program_ids')->flatten()->all();
                    }  
                    return self::where('program_type', '=', 'content_feed')
                        ->where('program_sub_type', '!=', 'collection')
                        ->FeedSearch($search)
                        ->where('status', '!=', 'DELETED')
                        ->where(function($q) use($program_ids, $relinfo, $field){
                            $q->whereIn('program_id', $program_ids)
                            ->orWhere($field, '=', (int)$relinfo[key($relinfo)]);
                        })
                        ->count();
                }
                if ($type == 'nonassigned') {
                    return self::where('program_type', '=', 'content_feed')
                        ->where('program_sub_type', '!=', 'collection')
                        ->when(!empty($filter_params), function ($query) use ($filter_params) {
                            if (array_has($filter_params, "not_in_ids")) {
                                return $query->whereNotIn("program_id", $filter_params["not_in_ids"]);
                            } elseif (array_has($filter_params, "in_ids")) {
                                return $query->whereIn("program_id", $filter_params["in_ids"]);
                            }
                        })->FeedSearch($search)
                        ->where('status', '!=', 'DELETED')
                        ->where($field, '!=', (int)$relinfo[key($relinfo)])
                        ->count();
                }
            }
        }
    }

    /* Scopes for querying starts here */

    public static function scopeFeedFilter($query, $filter = 'all', $relinfo = [])
    {
        $field = 'relations.active_user_feed_rel';
        if ($relinfo && key($relinfo) == 'usergroup') {
            $field = 'relations.active_usergroup_feed_rel';
        }

        if ($filter == 'assigned') {
            $query->where('status', '=', 'ACTIVE')->where($field, '=', (int)$relinfo[key($relinfo)]);
        } elseif ($filter == 'nonassigned') {
            $query->where('status', '=', 'ACTIVE')->where($field, '!=', (int)$relinfo[key($relinfo)]);
        } elseif ($filter != 'all') {
            $query->where('status', '=', $filter);
        }

        return $query;
    }

    public static function scopeCustomfieldFilter($query, $custom_field_name = [], $custom_field_value = [])
    {
        if (!empty($custom_field_name)) {
            foreach ($custom_field_name as $key => $value) {
                if ($custom_field_value[$key] != null) {
                    $query = $query->where($value, 'like', "%" . $custom_field_value[$key] . "%");
                }
            }
        }
        return $query;
    }

    public static function scopeCategoryFilter($query, $search = null)
    {
        if ($search != null) {
            $lists = Category::where('category_name', 'like', "%" . $search . "%")->get(['category_id'])->toArray();
            $result = [];
            if (!empty($lists)) {
                foreach ($lists as $list) {
                    $result[] = $list['category_id'];
                }
            }
            
            $query = $query->whereIn('program_categories', array_values($result));
        }
        return $query;
    }

    public static function scopeBatchFilter($query, $search = null)
    {
        if ($search != null) {
            $lists = self::where('program_title', 'like', "%" . $search . "%")->
            where('program_type', '=', 'course')->
            where('parent_id', '>', 0)->
            get(['parent_id'])->toArray();
            $result = [];
            if (!empty($lists)) {
                foreach ($lists as $list) {
                    $result[] = $list['parent_id'];
                }
            }

            $query = $query->whereIn('program_id', array_values($result));
        }
        return $query;
    }

    public static function scopeChannelFilter($query, $search = null)
    {
        if ($search != null) {
            $lists = self::where('program_title', 'like', "%" . $search . "%")->
            where('program_type', '=', 'content_feed')->
            where('program_sub_type', '=', 'single')->
            where('status', '=', 'ACTIVE')->get(['program_id'])->toArray();
            $result = [];
            if (!empty($lists)) {
                foreach ($lists as $list) {
                    $result[] = $list['program_id'];
                }
            }

            $query = $query->whereIn('child_relations.active_channel_rel', array_values($result));
        }
        return $query;
    }


    public static function scopeFeedSearch($query, $search = null)
    {
        if ($search != null) {
            $query->where(function ($q) use ($search) {
                $q->where('program_title', 'like', "%" . preg_replace('/\W/', '$0', $search) . "%")
                    ->orWhere('program_shortname', 'like', "%" . preg_replace('/\W/', '$0', $search) . "%")
                    ->orWhere('program_description', 'like', "%" . preg_replace('/\W/', '$0', $search) . "%");
            });
        }

        return $query;
    }

    public static function scopeSellabilityFilter($query, $search = 'all')
    {
        if ($search != 'all') {
            $query->where(function ($q) use ($search) {
                $q->where('program_sellability', '=', $search);
            });
        }

        return $query;
    }

    public static function scopeAccessFilter($query, $search = 'all')
    {
        if ($search != 'all') {
            $query->where(function ($q) use ($search) {
                $q->where('program_access', '=', $search);
            });
        }

        return $query;
    }

    public static function scopeVisibilityFilter($query, $search = 'all')
    {
        if ($search != 'all') {
            $query->where(function ($q) use ($search) {
                $q->where('program_visibility', '=', $search);
            });
        }

        return $query;
    }

    public static function scopeTitleFilter($query, $search = null)
    {
        if ($search != null) {
            $query->where(function ($q) use ($search) {
                $q->where('program_title', 'like', "%" . $search . "%");
            });
        }

        return $query;
    }

    public static function scopeDescriptionFilter($query, $search = null)
    {
        if ($search != null) {
            $query->where(function ($q) use ($search) {
                $q->where('program_description', 'like', "%" . $search . "%");
            });
        }

        return $query;
    }

    public static function scopeFeedtagsFilter($query, $search = null)
    {
        if ($search != null) {
            $query->where(function ($q) use ($search) {
                $search_words = explode(',', $search);
                $q->whereIn('program_keywords', $search_words);
            });
        }

        return $query;
    }

    public static function scopeShortnameFilter($query, $search = null)
    {
        if ($search != null) {
            $query->where(function ($q) use ($search) {
                $q->where('program_shortname', 'like', "%" . $search . "%");
            });
        }

        return $query;
    }

    public static function scopeCreateddateFilter($query, $search = null, $get_created_date = '=')
    {

        if ($search != null) {
            $start = strtotime($search);
            $end = strtotime($search . ' +1 days');
            $stop = ($end - 1);
            if ($get_created_date == '=') {
                $query->where('created_at', '>', $start)->where('created_at', '<', $stop);
            } else {
                $query->where('created_at', htmlspecialchars_decode($get_created_date), $start);
            }
        }

        return $query;
    }

    public static function scopeUpdateddateFilter($query, $search = null, $get_updated_date = '=')
    {
        if ($search != null) {
            $start = strtotime($search);
            $end = strtotime($search . ' +1 days');
            $stop = ($end - 1);
            if ($get_updated_date == '=') {
                $query->where('updated_at', '>', $start)->where('updated_at', '<', $stop);
            } else {
                $query->where('updated_at', htmlspecialchars_decode($get_updated_date), $start);
            }
        }

        return $query;
    }


    public static function scopeUserRelation($query, $userid = null, $usergrpids = [])
    {
        if ($userid) {
            $query->where(function ($q) use ($userid, $usergrpids) {
                $q->whereIn('relations.active_usergroup_feed_rel', $usergrpids)
                    ->orWhere('relations.active_user_feed_rel', '=', $userid);
            });
        }

        return $query;
    }

    public static function scopeGetOrderBy($query, $orderby = ['created_at' => 'desc'])
    {
        $key = key($orderby);
        $value = $orderby[$key];

        return $query->orderBy($key, $value);
    }

    //function to call for coursetype from upcoming courses repository
    public static function scopeCourseType($query, $type = [])
    {
        if (!empty($type)) {
            $program_type = [];
            $program_sub_type = [];

            if (in_array("channels", $type)) {
                $program_type[] = 'content_feed';
                $program_sub_type[] = 'single';
            }

            if (in_array("packages", $type)) {
                $program_type[] = 'content_feed';
                $program_sub_type[] = 'collection';
            }

            if (in_array("products", $type)) {
                $program_type[] = 'product';
                $program_sub_type[] = '';
            }

            if (in_array("course", $type)) {
                $program_type[] = 'course';
                $program_sub_type[] = 'single';
            }

            if (!empty($program_type) && !empty($program_sub_type)) {
                $query = $query->whereIn('program_type', $program_type);
                return $query->whereIn('program_sub_type', $program_sub_type);
            } elseif (!empty($program_type)) {
                return $query->whereIn('program_type', $program_type);
            } else {
                return $query;
            }
        } else {
            return $query->whereNotIn('program_type', ['content_feed', 'product', 'course']);
        }
    }

    public static function scopeGetType($query, $type = 'channel')
    {
        if ($type == 'channel') {
            return $query->where('program_type', '=', 'content_feed')
                ->where('program_sub_type', '=', 'single');
        } elseif ($type == 'package') {
            return $query->where('program_type', '=', 'content_feed')
                ->where('program_sub_type', '=', 'collection');
        } elseif ($type == 'product') {
            return $query->where('program_type', '=', 'product');
        } elseif ($type == 'course') {
            return $query->where('program_type', '=', 'course')
                ->where('program_sub_type', '=', 'single')->where('parent_id', '=', 0);
        } else {
            return $query->where('parent_id', 'exists', false)
                ->orWhere('parent_id', '=', 0);
        }
    }

    public static function scopeFilterCustomType($query, $filter)
    {
        if ($filter == 'channelfields') {
            return $query->where('program_type', '=', 'content_feed')
                ->where('program_sub_type', '=', 'single');
        } elseif ($filter == 'packagefields') {
            return $query->where('program_type', '=', 'content_feed')
                ->where('program_sub_type', '=', 'collection');
        } elseif ($filter == 'coursefields') {
            return $query->where('program_type', '=', 'course')
                ->where('program_sub_type', '=', 'single')->where('parent_id', '=', 0);
        } elseif ($filter == 'productfields') {
            return $query->where('program_type', '=', 'product');
        } else {
            return $query;
        }
    }

    public static function scopeExceptProgramids($query, $program_ids)
    {
        if (is_array($program_ids) && !empty($program_ids)) {
            $program_ids = array_map('intval', $program_ids);
            return $query->whereNotIn('program_id', $program_ids);
        } else {
            return $query;
        }
    }

    public static function getSelectedPrograms($program_ids)
    {
        return Program::whereIn('program_id', $program_ids)->get()->toArray();
    }

    public static function scopeGetByPagination($query, $start = 0, $limit = 10)
    {
        return $query->skip((int)$start)->take((int)$limit);
    }

    public static function scopeGetCount($query)
    {
        return $query->count();
    }

    public static function scopeGetAsArray($query)
    {
        return $query->get()->toArray();
    }

    /* Scopes for querying ends here */

    public static function getPacketsCount($condition = 'all')
    {
        if ($condition == 'all') {
            return DB::collection('packets')->where('status', '!=', 'DELETED')->count();
        } else {
            return DB::collection('packets')->where('feed_slug', '=', $condition)->where('status', '!=', 'DELETED')->count();
        }
    }

    public static function updateFeedCategories($key, $data, $overwrite = false)
    {
        if ($overwrite) {
            self::where('program_id', $key)->update(['program_categories' => $data]);
        } else {
            self::where('program_id', $key)->push('program_categories', $data, true);
        }

        return self::where('program_id', $key)->update(['updated_at' => time()]);
    }

    public static function removeFeedCategories($key, $data)
    {
        self::where('program_id', $key)->pull('program_categories', $data);
    }

    public static function updateFeedRelation($key, $arrname, $updateArr, $overwrite = false)
    {
        if ($overwrite) {
            self::where('program_id', $key)->unset('relations.' . $arrname);
            self::where('program_id', $key)->update(['relations.' . $arrname => $updateArr]);
        } else {
            self::where('program_id', $key)->push('relations.' . $arrname, $updateArr, true);
        }

        //return self::where('program_id', $key)->update(['updated_at' => time()]);
        return true;
    }

    /* Added by Sahana */

    public static function removeFeedRelation($key, $fieldarr = [], $id = null)
    {
        if ($id) {
            foreach ($fieldarr as $field) {
                self::where('program_id', $key)->pull('relations.' . $field, (int)$id);
            }
        }

        //return self::where('program_id', $key)->update(['updated_at' => time()]);
        return true;
    }

    public static function addFeedRelation($key, $fieldarr = [], $id = null)
    {
        if ($id) {
            foreach ($fieldarr as $field) {
                self::where('program_id', (int)$key)->push('relations.' . $field, (int)$id, true);
            }
        }

        //return self::where('program_id', $key)->update(['updated_at' => time()]);
        return true;
    }

    public static function getProgramDetailsByID($id)
    {
        return self::where('program_id', '=', (int)$id)->first();
    }

    public static function getPrograms($program_slugs)
    {
        return self::whereIn('program_slug', $program_slugs)
            ->where('status', '=', 'ACTIVE')
            ->orderby('created_at', 'desc')
            ->get()->toArray();
    }

    public static function isExpired($feed_slug)
    {
        $now = time();
        $feed = self::where('program_slug', '=', $feed_slug)->where('status', '!=', 'DELETED')->get(['program_startdate', 'program_enddate'])->toArray();
        if (!empty($feed[0])) {
            if ((Timezone::getTimeStamp($feed[0]['program_startdate']) > $now) || (Timezone::getTimeStamp($feed[0]['program_enddate']) < $now)) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public static function getProgramsSortBy($categories, $selected_feeds, $sub_program_slugs = [], $sort_by = 'old_to_new', $records_per_page = 9, $page_no = 0)
    {
        $skip = $records_per_page * $page_no;
        if ($sort_by == 'old_to_new') {
            $orderby = 'updated_at';
            $sortby = 'asc';
        } elseif ($sort_by == 'a_z') {
            $orderby = 'title_lower';
            $sortby = 'asc';
        } elseif ($sort_by == 'z_a') {
            $orderby = 'title_lower';
            $sortby = 'desc';
        } else {
            $orderby = 'updated_at';
            $sortby = 'desc';
        }

        if (!empty($categories) && empty($selected_feeds)) {
            return self::where('program_type', '=', 'content_feed')
                ->where('program_sub_type', '=', 'single')
                ->where('status', '=', 'ACTIVE')
                ->whereIn('program_slug', $sub_program_slugs)
                ->whereIn('program_categories', $categories)
                ->orderby($orderby, $sortby)
                ->skip((int)$skip)
                ->take((int)$records_per_page)
                ->with(['packages' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }])->get()->toArray();
        } elseif (empty($categories) && !empty($selected_feeds)) {
            return self::where('program_type', '=', 'content_feed')
                ->where('program_sub_type', '=', 'single')
                ->where('status', '=', 'ACTIVE')
                ->whereIn('program_slug', $sub_program_slugs)
                ->whereIn('program_id', $selected_feeds)
                ->orderby($orderby, $sortby)
                ->skip((int)$skip)
                ->take((int)$records_per_page)
                ->with(['packages' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }])->get()->toArray();
        } elseif (!empty($categories) && !empty($selected_feeds)) {
            return self::where('program_type', '=', 'content_feed')
                ->where('program_sub_type', '=', 'single')
                ->where('status', '=', 'ACTIVE')
                ->whereIn('program_slug', $sub_program_slugs)
                ->where(function ($q) use ($categories, $selected_feeds) {
                    $q->orWhereIn('program_categories', $categories)
                    ->orWhereIn('program_id', $selected_feeds);
                })
                ->orderby($orderby, $sortby)
                ->skip((int)$skip)
                ->take((int)$records_per_page)
                ->with(['packages' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }])->get()->toArray();
        } else {
            return self::whereIn('program_slug', $sub_program_slugs)
                ->where('program_type', '=', 'content_feed')
                ->where('program_sub_type', '=', 'single')
                ->where('status', '=', 'ACTIVE')
                ->orderby($orderby, $sortby)
                ->skip((int)$skip)
                ->take((int)$records_per_page)
                ->with(['packages' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }])->get()->toArray();
        }
    }

    public static function getProgramsCountUsingSlugs($categories, $selected_feeds, $sub_program_slugs = [])
    {
        if (!empty($categories) && empty($selected_feeds)) {
            return self::where('program_type', '=', 'content_feed')
                ->where('program_sub_type', '=', 'single')
                ->where('status', '=', 'ACTIVE')
                ->whereIn('program_slug', $sub_program_slugs)
                ->whereIn('program_categories', $categories)
                ->count();
        } elseif (empty($categories) && !empty($selected_feeds)) {
            return self::where('program_type', '=', 'content_feed')
                ->where('program_sub_type', '=', 'single')   
                ->where('status', '=', 'ACTIVE')
                ->whereIn('program_slug', $sub_program_slugs)
                ->whereIn('program_id', $selected_feeds)
                ->count();
        } elseif (!empty($categories) && !empty($selected_feeds)) {
            return self::where('program_type', '=', 'content_feed')
                ->where('program_sub_type', '=', 'single')
                ->where('status', '=', 'ACTIVE')
                ->whereIn('program_slug', $sub_program_slugs)
                ->whereIn('program_categories', $categories)
                ->whereIn('program_id', $selected_feeds)
                ->count();
        } else {
            return self::whereIn('program_slug', $sub_program_slugs)
        ->where('program_sub_type', '=', 'single')
                ->where('status', '=', 'ACTIVE')
                ->count();
        }
    }

    public static function getExpiredFeedSlugs($program_slug)
    {
        return self::whereIn('program_slug', $program_slug)
            ->Where('program_enddate', '<', time())
            ->lists('program_slug')
            ->all();
    }

    public static function getCategoryRelatedProgramSlugs($categories, $selected_feeds, $sub_program_slugs = [])
    {
        if (!empty($categories) && empty($selected_feeds)) {
            return self::where('program_type', '=', 'content_feed')
                ->where('status', '=', 'ACTIVE')
                ->whereIn('program_slug', $sub_program_slugs)
                ->whereIn('program_categories', $categories)
                ->lists('program_slug')
                ->all();
        } elseif (empty($categories) && !empty($selected_feeds)) {
            return self::where('program_type', '=', 'content_feed')
                ->orwhere('program_type', '=', 'course')
                ->where('status', '=', 'ACTIVE')
                ->whereIn('program_slug', $sub_program_slugs)
                ->whereIn('program_id', $selected_feeds)
                ->lists('program_slug')
                ->all();
        } elseif (!empty($categories) && !empty($selected_feeds)) {
            return self::where('program_type', '=', 'content_feed')
                ->where('status', '=', 'ACTIVE')
                ->whereIn('program_slug', $sub_program_slugs)
                ->whereIn('program_categories', $categories)
                ->whereIn('program_id', $selected_feeds)
                ->lists('program_slug')
                ->all();
        } else {
            return self::where('program_type', '=', 'content_feed')
                ->orwhere('program_type', '=', 'course')
                ->where('status', '=', 'ACTIVE')
                ->whereIn('program_slug', $sub_program_slugs)
                ->lists('program_slug')
                ->all();
        }
    }

    public static function getCategoryRelatedFeedAssets($category, $sub_program_slugs)
    {
        if (empty($category)) {
            return self::whereIn('program_slug', $sub_program_slugs)->get()->toArray();
        } else {
            $array = [];
            $feed_category = Category::whereIn('category_id', $category)->get(['relations.assigned_feeds'])->toArray();

            $array = array_pluck($feed_category, 'relations.assigned_feeds');
            $feed_ids = array_flatten($array);

            return self::whereIn('program_slug', $sub_program_slugs)
                ->where('program_type', '=', 'content_feed')
                ->where('program_sub_type', '=', 'single')
                ->whereIn('program_id', $feed_ids)
                ->get()->toArray();
        }
    }

    /* Added by Sandeep - to get feeds to dispaly in portal section */

    public static function getAllPrograms($type = 'all', $slug = '', $categories = '', $subscribed_feeds = '', $records_per_page = '', $page_number = '', $selected_feeds = [], $sort_by = 'desc', $program_access = '')
    {
        if ($sort_by == 'old_to_new') {
            $orderby = 'updated_at';
            $sortby = 'asc';
        } elseif ($sort_by == 'a_z') {
            $orderby = 'title_lower';
            $sortby = 'asc';
        } elseif ($sort_by == 'z_a') {
            $orderby = 'title_lower';
            $sortby = 'desc';
        } else {
            $orderby = 'updated_at';
            $sortby = 'desc';
        }
        $skip = (int)$records_per_page * (int)$page_number;
        $now = time();
        $returndata = [];
        $queries = self::where('status', '=', 'ACTIVE')
                        ->where('program_startdate', '<=', $now)
                        ->where('program_startdate', '<=', $now)
                        ->Where('program_display_startdate', '<=', $now)
                        ->Where('program_display_enddate', '>=', $now);

        switch ($type) {
            case 'all':
                if ($slug != '') {
                    $returndata = $queries->where('program_slug', '=', $slug)
                        ->get()->toArray();
                } else {
                    $returndata = $queries->whereNotIn('program_id', $subscribed_feeds)
                        ->get()->toArray();
                }
                break;
            case 'content_feed':
                if ($slug != '') {
                    $returndata = $queries->where('program_type', '=', $type)
                        ->where('program_slug', '=', $slug)
                        ->when(!empty($program_access),
                            function ($query) use ($program_access) {
                                return $query->where("program_access", $program_access);
                            }
                        )
                        ->orderby($orderby, $sortby)
                        ->get()->toArray();
                } elseif (empty($categories) && empty($selected_feeds)) {
                    $returndata = $queries->where('program_type', '=', $type)
                        ->whereNotIn('program_id', $subscribed_feeds)
                        ->when(!empty($program_access),
                            function ($query) use ($program_access) {
                                return $query->where("program_access", $program_access);
                            }
                        )
                        ->skip((int)$skip)
                        ->orderby($orderby, $sortby)
                        ->take((int)$records_per_page)
                        ->get()->toArray();
                } elseif (!empty($categories) && empty($selected_feeds)) {
                    $returndata = $queries->where('program_type', '=', $type)
                        ->whereNotIn('program_id', $subscribed_feeds)
                        ->when(!empty($program_access),
                            function ($query) use ($program_access) {
                                return $query->where("program_access", $program_access);
                            }
                        )
                        ->orderby($orderby, $sortby)
                        ->whereIn('program_categories', $categories)
                        ->take((int)$records_per_page)
                        ->skip((int)$skip)
                        ->get()->toArray();
                } elseif (empty($categories) && !empty($selected_feeds)) {
                    $returndata = $queries->where('program_type', '=', $type)
                        ->when(!empty($program_access),
                            function ($query) use ($program_access) {
                                return $query->where("program_access", $program_access);
                            }
                        )
                        ->orderby($orderby, $sortby)
                        ->whereNotIn('program_id', $subscribed_feeds)
                        ->whereIn('program_id', $selected_feeds)
                        ->take((int)$records_per_page)
                        ->skip((int)$skip)
                        ->get()->toArray();
                } else {
                    $returndata = $queries->where('program_type', '=', $type)
                        ->when(!empty($program_access),
                            function ($query) use ($program_access) {
                                return $query->where("program_access", $program_access);
                            }
                        )
                        ->orderby($orderby, $sortby)
                        ->whereNotIn('program_id', $subscribed_feeds)
                        ->whereIn('program_id', $selected_feeds)
                        ->whereIn('program_categories', $categories)
                        ->take((int)$records_per_page)
                        ->skip((int)$skip)
                        ->get()->toArray();
                }
                break;
        }
        return $returndata;
    }

    public static function getAllProgramsCount($type = 'all', $categories = [], $subscribed_feeds = [], $selected_feeds = [], $sort_by = 'desc')
    {
        if ($sort_by == 'old_to_new') {
            $orderby = 'updated_at';
            $sortby = 'asc';
        } elseif ($sort_by == 'a_z') {
            $orderby = 'title_lower';
            $sortby = 'asc';
        } elseif ($sort_by == 'z_a') {
            $orderby = 'title_lower';
            $sortby = 'desc';
        } else {
            $orderby = 'updated_at';
            $sortby = 'desc';
        }
        $returndata = ' ';
        $now = time();
        switch ($type) {
            case 'all':
                $returndata = self::where('status', '=', 'ACTIVE')
                    ->count();
                break;
            case 'content_feed':
                if (empty($categories) && empty($selected_feeds)) {
                    $returndata = self::where('program_type', '=', $type)
                        ->where('status', '=', 'ACTIVE')
                        ->where('program_startdate', '<=', $now)
                        ->Where('program_enddate', '>=', $now)
                        ->Where('program_display_startdate', '<=', $now)
                        ->Where('program_display_enddate', '>=', $now)
                        ->orderby($orderby, $sortby)
                        ->whereNotIn('program_id', $subscribed_feeds)
                        ->count();
                } elseif (!empty($categories) && empty($selected_feeds)) {
                    $returndata = self::where('program_type', '=', $type)
                        ->where('status', '=', 'ACTIVE')
                        ->where('program_startdate', '<=', $now)
                        ->Where('program_enddate', '>=', $now)
                        ->Where('program_display_startdate', '<=', $now)
                        ->Where('program_display_enddate', '>=', $now)
                        ->orderby($orderby, $sortby)
                        ->whereNotIn('program_id', $subscribed_feeds)
                        ->whereIn('program_categories', $categories)
                        ->count();
                } elseif (empty($categories) && !empty($selected_feeds)) {
                    $returndata = self::where('program_type', '=', $type)
                        ->where('status', '=', 'ACTIVE')
                        ->where('program_startdate', '<=', $now)
                        ->Where('program_enddate', '>=', $now)
                        ->Where('program_display_startdate', '<=', $now)
                        ->Where('program_display_enddate', '>=', $now)
                        ->orderby($orderby, $sortby)
                        ->whereNotIn('program_id', $subscribed_feeds)
                        ->whereIn('program_id', $selected_feeds)
                        ->count();
                } else {
                    $returndata = self::where('program_type', '=', $type)
                        ->where('status', '=', 'ACTIVE')
                        ->where('program_startdate', '<=', $now)
                        ->Where('program_enddate', '>=', $now)
                        ->Where('program_display_startdate', '<=', $now)
                        ->Where('program_display_enddate', '>=', $now)
                        ->orderby($orderby, $sortby)
                        ->whereNotIn('program_id', $subscribed_feeds)
                        ->whereIn('program_id', $selected_feeds)
                        ->whereIn('program_categories', $categories)
                        ->count();
                }
                break;
        }

        return $returndata;
    }

    public static function getCate($type = 'all', $slug = '', $categories = [])
    {
    }

    /* Added by Sandeep - to get feeds list for portal side bar*/

    public static function getCategoryRelatedFeeds($category, $sub_feed_ids)
    {
        $now = time();
        $feed_ids_array = [];
        if (empty($category)) {
            $content_feeds = self::getAllContentFeeds();
            foreach ($content_feeds as $feed) {
                $feed_ids_array[] = (int)$feed['program_id'];
            }
            $feed_ids_array = array_diff($feed_ids_array, $sub_feed_ids);

            return $feed_info = self::whereIn('program_id', $feed_ids_array)
                ->where('status', '=', 'ACTIVE')
                ->where('program_startdate', '<=', $now)
                ->Where('program_enddate', '>=', $now)->get()->toArray();
        } else {
            $feed_category = Category::whereIn('category_id', $category)->get(['relations'])->toArray();

            // echo '<pre>'; print_r($feed_category); die;

            foreach ($feed_category as $feed) {
                if (isset($feed['relations']['assigned_feeds'])) {
                    foreach ($feed['relations']['assigned_feeds'] as $feedids) {
                        $feed_ids_array[] = (int)$feedids;
                    }
                }
            }
            $feed_ids_array = array_unique($feed_ids_array);
            $feed_ids_array = array_diff($feed_ids_array, $sub_feed_ids);

            return $feed_info = self::whereIn('program_id', $feed_ids_array)
                ->where('status', '=', 'ACTIVE')
                ->where('program_startdate', '<=', $now)
                ->Where('program_enddate', '>=', $now)->get()->toArray();
        }

        // echo '<pre>'; print_r($feed_info); die;
    }

    public static function getSubscribedFeeds($id)
    {
        return TransactionDetail::where('id', '=', (int)$id)->where('status', '=', 'COMPLETE')->get()->toArray();
    }

    public static function getSubscribedFeedsThroughGroups($user_id)
    {
        $group_ids = [];
        $assigned_group_ids = User::getAssignedUsergroups($user_id);

        if ($assigned_group_ids != 'default') {
            foreach ($assigned_group_ids as $group) {
                $group_ids[] = $group;
            }
        }

        $assigned_feed_ids = UserGroup::where('status', '=', 'ACTIVE')->whereIn('ugid', $group_ids)->get(['relations'])->toArray();
        $feed_ids = [];
        if (isset($assigned_feed_ids)) {
            foreach ($assigned_feed_ids as $each_relation) {
                if (isset($each_relation['relations']['usergroup_feed_rel'])) {
                    foreach ($each_relation['relations']['usergroup_feed_rel'] as $each) {
                        $feed_ids[] = $each;
                    }
                }
                //collection starts
                if (isset($each_relation['relations']['usergroup_parent_feed_rel'])) {
                    foreach ($each_relation['relations']['usergroup_parent_feed_rel'] as $pack) {
                        $progarms = Program::getProgramDetailsByID($pack);
                        if (isset($progarms['child_relations']['active_channel_rel']) && !empty($progarms['child_relations']['active_channel_rel'])) {
                            foreach ($progarms['child_relations']['active_channel_rel'] as $each) {
                                $feed_ids[] = $each;
                            }
                        }
                    }
                }
                //collection ends
            }
        }


        return $feed_ids;
    }

    public static function pluckFeedName($program_slug)
    {
        return self::where('program_slug', '=', $program_slug)->where('status', '!=', 'DELETED')->get(['program_title', 'program_id', 'program_type', 'parent_id', 'program_display_startdate', 'program_display_enddate', 'program_startdate', 'program_enddate']);
    }

    public static function getPacketName($slug)
    {
        return self::where('program_slug', '=', $slug)->where('status', '!=', 'DELETED')->value('program_title');
    }

    public static function getFeedArray($slug)
    {
        return self::where('program_slug', '=', $slug)->where('status', '!=', 'DELETED')->get(['program_id', 'program_description', 'program_title', 'program_startdate', 'program_enddate', 'program_cover_media', 'status', 'program_slug', 'program_sub_type', 'benchmarks', 'program_type', 'parent_id'])->toArray();
    }

    /*for statistic */
    public static function getLastThirtydaysCreatedProgramCount()
    {
        return self::where('status', '=', 'ACTIVE')->where('created_at', '>', strtotime('-30 day', time()))->get()->count();
    }

    /*For Do intimate Urget Action Reqired*/
    public static function getUserUsergrouprelation($start = 0, $limit = 3)
    {
        $programs = self::where('status', '=', 'ACTIVE')->Where(function ($qry) {
            $qry->orwhere(function ($qery) {
                $qery->where('relations.active_user_feed_rel', 'exists', false)
                    ->where('relations.active_usergroup_feed_rel', 'exists', false);
            })->orwhere(function ($q) {
                $q->where('relations.active_user_feed_rel.0', 'exists', false)
                    ->where('relations.active_usergroup_feed_rel.0', 'exists', false);
            });
        })
            ->where('program_startdate', '<', strtotime('7 day', time()))
            ->where('program_startdate', '>=', strtotime('0 day', time()))
            ->orderby('program_startdate', 'asc')
            ->skip((int)$start)
            ->take((int)$limit)
            ->get(['program_id', 'program_slug', 'program_title', 'program_startdate'])->toArray();

        return $programs;
    }

    public static function getUnassignPacketProgram($start = 0, $limit = 3)
    {
        $program_slug_ary = [];

        $packs = Packet::where('status', '=', 'ACTIVE')
            ->distinct()
            ->get(['feed_slug'])
            ->toArray();

        foreach ($packs as $key => $value) {
            array_push($program_slug_ary, $value[0]);
        }

        $program_slug = self::where('status', '=', 'ACTIVE')
            ->whereNotIn('program_slug', $program_slug_ary)
            ->where('program_startdate', '<', strtotime('7 day', time()))
            ->where('program_startdate', '>=', strtotime('0 day', time()))
            ->orderby('program_startdate', 'asc')
            ->skip((int)$start)
            ->take((int)$limit)
            ->get(['program_title', 'program_startdate', 'program_slug'])
            ->toArray();

        return $program_slug;
    }

    public static function getUnassignElementsProgram($start = 0, $limit = 3)
    {
        $program_slug_ary = [];

        $packs = Packet::where('status', '=', 'ACTIVE')
            ->where('elements.0', 'exists', false)
            ->distinct()
            ->get(['feed_slug'])
            ->toArray();

        foreach ($packs as $key => $value) {
            array_push($program_slug_ary, $value[0]);
        }

        $program_slug = self::where('status', '=', 'ACTIVE')
            ->whereIn('program_slug', $program_slug_ary)
            ->where('program_startdate', '<', strtotime('7 day', time()))
            ->where('program_startdate', '>=', strtotime('0 day', time()))
            ->orderby('program_startdate', 'asc')
            ->skip((int)$start)
            ->take((int)$limit)
            ->get(['program_title', 'program_startdate', 'program_slug'])
            ->toArray();

        return $program_slug;
    }

    public static function getUnassignPacketProgramCount()
    {
        $program_slug_ary = [];

        $packs = Packet::where('status', '=', 'ACTIVE')
            ->distinct()
            ->get(['feed_slug'])
            ->toArray();

        foreach ($packs as $key => $value) {
            array_push($program_slug_ary, $value[0]);
        }

        $program_slug = self::where('status', '=', 'ACTIVE')
            ->whereNotIn('program_slug', $program_slug_ary)
            ->where('program_startdate', '<', strtotime('7 day', time()))
            ->where('program_startdate', '>=', strtotime('0 day', time()))
            ->orderby('program_startdate', 'asc')
            ->count();

        return $program_slug;
    }

    public static function getUnassignElementsProgramCount()
    {
        $program_slug_ary = [];

        $packs = Packet::where('status', '=', 'ACTIVE')
            ->where('elements.0', 'exists', false)
            ->distinct()
            ->get(['feed_slug'])
            ->toArray();

        foreach ($packs as $key => $value) {
            array_push($program_slug_ary, $value[0]);
        }

        $program_slug = self::where('status', '=', 'ACTIVE')
            ->whereIn('program_slug', $program_slug_ary)
            ->where('program_startdate', '<', strtotime('7 day', time()))
            ->where('program_startdate', '>=', strtotime('0 day', time()))
            ->orderby('program_startdate', 'asc')
            ->count();

        return $program_slug;
    }

    public static function getUserUsergrouprelationCount()
    {
        $programs = self::where('status', '=', 'ACTIVE')->Where(function ($qry) {
            $qry->orwhere(function ($qery) {
                $qery->where('relations.active_user_feed_rel', 'exists', false)
                    ->where('relations.active_usergroup_feed_rel', 'exists', false);
            })->orwhere(function ($q) {
                $q->where('relations.active_user_feed_rel.0', 'exists', false)
                    ->where('relations.active_usergroup_feed_rel.0', 'exists', false);
            });
        })
            ->where('program_startdate', '<', strtotime('7 day', time()))
            ->where('program_startdate', '>=', strtotime('0 day', time()))
            ->count();

        return $programs;
    }

    public static function getContentFeedforChart($status = 'ACTIVE', $sort_date = 'created_at')
    {
        $result_record = [];
        $last_month = [];
        $last_month_key = [];
        $last_month_asociate = [];
        for ($i = 30; $i > 0; --$i) {
            $j = $i - 1;
            $st_t = strtotime("-$i day", time());
            $ed_t = strtotime("-$j day", time());
            $val_count = self::where('status', '=', $status)->whereBetween($sort_date, [$st_t, $ed_t])->count();
            array_push($last_month, $val_count);
            $key = Timezone::convertFromUTC('@' . $ed_t, Auth::user()->timezone, 'd M');
            array_push($last_month_key, $key);
            $last_month_asociate[$key] = $val_count;
        }
        $last24hours = [];
        $last24hours_key = [];
        $last24hours_asoc = [];
        $flag = true;
        $buf_time = 0;
        for ($i = 24; $i > 0; --$i) {
            $j = $i - 1;
            if ($flag) {
                $flag = false;
                $buf_time = Timezone::convertFromUTC('@' . time(), Auth::user()->timezone, 'h');
                $buf_time = $buf_time * 60 * 60;
            }
            $st_t = (strtotime("-$i hour", time())) - $buf_time;
            if ($j == 0) {
                $ed_t = time() - $buf_time;
            } else {
                $ed_t = strtotime("-$j hour", time()) - $buf_time;
            }
            $val_count = self::where('status', '=', $status)->whereBetween($sort_date, [$st_t, $ed_t])->count();
            array_push($last24hours, $val_count);
            $key = Timezone::convertFromUTC('@' . $st_t, Auth::user()->timezone, 'H');
            array_push($last24hours_key, $key);
            $last24hours_asoc[$key] = $val_count;
        }
        $last7days = [];
        $last7days_key = [];
        $last7days_asoc = [];
        for ($i = 7; $i > 0; --$i) {
            $j = $i - 1;
            $st_t = strtotime("-$i day", time());
            $ed_t = strtotime("-$j day", time());
            $val_count = self::where('status', '=', $status)->whereBetween($sort_date, [$st_t, $ed_t])->count();
            array_push($last7days, $val_count);
            $key = Timezone::convertFromUTC('@' . $ed_t, Auth::user()->timezone, 'D');
            array_push($last7days_key, $key);
            $last7days_asoc[$key] = $val_count;
        }
        $last12months = [];
        $last12months_key = [];
        $last12months_asoc = [];
        $flag = true;
        $buffer_time = 0;
        for ($i = -1; $i < 11; ++$i) {
            $j = $i + 1;
            if ($flag) {
                $flag = false;
                $get_curent_month = date('m', time());
                $get_curent_month_y = date('Y', time());
                $buffer_time = '1-' . $get_curent_month . '-' . $get_curent_month_y;
                $ed_t = strtotime($buffer_time, time());
                $st_t = time();
                $buffer_time = $st_t - $ed_t;
                $ed_t = $ed_t + 86400;
            } else {
                $st_t = strtotime("-$i Month", time()) - $buffer_time;
                $ed_t = strtotime("-$j Month", time()) - $buffer_time + 86400;
            }
            $val_count = self::where('status', '=', $status)->whereBetween($sort_date, [$ed_t, $st_t])->count();
            array_push($last12months, $val_count);
            // $key=Timezone::convertFromUTC('@'.$st_t, Auth::user()->timezone, 'M');
            $key2 = Timezone::convertFromUTC('@' . ($st_t - 86400), Auth::user()->timezone, 'M');

            array_push($last12months_key, $key2);
            $last12months_asoc[$key2] = $val_count;
        }
        /* $result_record['last12months_asoc']=$last12months_asoc;
         $result_record['last7days_asoc']=$last7days_asoc;
         $result_record['last24hours_asoc']=$last24hours_asoc;
         $result_record['last_month_asociate']=$last_month_asociate;*/
        $result_record['last_month'] = $last_month;
        $result_record['last_month_key'] = $last_month_key;
        $result_record['last24hours'] = $last24hours;
        $result_record['last24hours_key'] = $last24hours_key;
        $result_record['last7days'] = $last7days;
        $result_record['last7days_key'] = $last7days_key;
        $result_record['last12months'] = $last12months;
        $result_record['last12months_key'] = $last12months_key;
        /*
        print_r($result_record);
        die;*/
        return $result_record;
    }

    public static function getCFSlugFromFeedID($feed_id = [])
    {
        if (!empty($feed_id) && is_array($feed_id)) {
            return self::where('status', '=', 'ACTIVE')
                ->whereIn('program_id', $feed_id)
                ->get(['program_slug'])
                ->toArray();
        } else {
            return [];
        }
    }

    public static function getSubscribedFeedInfo($sub_feed_ids)
    {
        return $feed_info = self::whereIn('program_id', $sub_feed_ids)->get()->toArray();
    }

    /*for get announcement list*/
    public static function getAnnouncementList($cfids = [])
    {
        if (!empty($cfids)) {
            return self::where('status', '=', 'ACTIVE')
                ->whereIn('program_id', $cfids)
                ->get(['relations'])
                ->toArray();
        } else {
            return [];
        }
    }

    /*get content feed title*/
    public static function getCFTitle($slug = null)
    {
        if (!is_null($slug)) {
            return self::where('status', '=', 'ACTIVE')
                ->where('program_slug', '=', $slug)
                ->value('program_title');
        }
    }

    public static function getCFTitleID($id = null)
    {
        if (!is_null($id)) {
            return self::where('status', '=', 'ACTIVE')
                ->where('program_id', '=', (int)$id)
                ->value('program_title');
        }
    }

    public static function getActiveFeedsList($start = null, $end = null)
    {
        if (!is_null($start) && !is_null($end)) {
            return self::where('status', '=', 'ACTIVE')
                ->where('program_enddate', '>=', $end)
                ->count();
        } else {
            return 0;
        }
    }

    public static function getNewFeedsList($start = null, $end = null)
    {
        if (!is_null($start) && !is_null($end)) {
            return self::where('status', '=', 'ACTIVE')
                ->whereBetween('created_at', [$start, $end])
                ->count();
        } else {
            return 0;
        }
    }

    /*Sandeep - Remove Media from Channel (program_cover_media)*/
    public static function removeChannelCoverMedia($cids = [])
    {
        self::whereIn('program_id', $cids)->update(['program_cover_media' => '']);
    }

    /**
     * [getSlugById get program slug by id]
     * @param  [type] $programid [programid as i/p]
     * @return [type]       [program_slug array as o/p]
     * @Author : Nayan
     */

    public static function getSlugById($id)
    {
        $program_slugs = [];
        if (is_array($id)) {
            $slugs = self::whereIn('program_id', $id)->get(['program_slug'])->toArray();
            foreach ($slugs as $key => $value) {
                $program_slugs[] = $value['program_slug'];
            }
        } else {
            $slugs = self::where('program_id', $id)->get(['program_slug'])->toArray();
            foreach ($slugs as $key => $value) {
                $program_slugs[] = $value['program_slug'];
            }
        }
        return $program_slugs;
    }

    public static function getActiveProgramsById($program_id)
    {
        $program_details = [];
        if (is_array($program_id)) {
            $now = time();
            $program_categories = self::whereIn('program_id', $program_id)
                ->where('status', '=', 'ACTIVE')
                ->where('program_startdate', '<=', $now)
                ->Where('program_enddate', '>=', $now)
                ->Where('program_display_startdate', '<=', $now)
                ->Where('program_display_enddate', '>=', $now)
                ->where('program_visibility', '=', 'yes')
                ->where('program_sellability', '=', 'yes')
                ->get(['program_categories'])->toArray();
            foreach ($program_categories as $key => $value) {
                $program_details[] = $value;
            }
        }

        return $program_details;
    }

    public static function getCatalogProducts()
    {
        $now = time();
        $products = [];
        $product_list = self::where('program_id', '>', 0)
            ->where('program_type', '=', 'product')
            ->where('status', '=', 'ACTIVE')
            ->where('program_startdate', '<=', $now)
            ->Where('program_enddate', '>=', $now)
            ->Where('program_display_startdate', '<=', $now)
            ->Where('program_display_enddate', '>=', $now)
            ->where('program_visibility', '=', 'yes')
            ->where('program_sellability', '=', 'yes')
            ->get(['program_id', 'program_categories'])->toArray();
        if (!empty($product_list)) {
            foreach ($product_list as $product) {
                if (empty($product['program_categories'])) {
                    $products[] = $product['program_id'];
                }
            }
        }
        return $products;
    }

    public static function getCatalogCourses()
    {
        $now = time();
        $courses = [];
        $course_list = self::where('program_id', '>', 0)
            ->where('parent_id', '=', 0)
            ->where('program_type', '=', 'course')
            ->where('status', '=', 'ACTIVE')
            ->where('program_startdate', '<=', $now)
            ->Where('program_enddate', '>=', $now)
            ->Where('program_display_startdate', '<=', $now)
            ->Where('program_display_enddate', '>=', $now)
            ->where('program_visibility', '=', 'yes')
            ->where('program_sellability', '=', 'yes')
            ->get(['program_id', 'program_categories'])->toArray();
        if (!empty($course_list)) {
            foreach ($course_list as $course) {
                if (empty($course['program_categories'])) {
                    $courses[] = $course['program_id'];
                }
            }
        }
        return $courses;
    }

    public static function getCatalogChannels()
    {
        $now = time();
        $channels = [];
        $channel_list = self::where('program_id', '>', 0)
            ->where('program_type', '=', 'content_feed')
            ->where('status', '=', 'ACTIVE')
            ->where('program_startdate', '<=', $now)
            ->Where('program_enddate', '>=', $now)
            ->Where('program_display_startdate', '<=', $now)
            ->Where('program_display_enddate', '>=', $now)
            ->where('program_visibility', '=', 'yes')
            ->where('program_sellability', '=', 'yes')
            ->get(['program_id', 'program_categories'])->toArray();
        if (!empty($channel_list)) {
            foreach ($channel_list as $channel) {
                if (empty($channel['program_categories'])) {
                    $channels[] = $channel['program_id'];
                }
            }
        }
        return $channels;
    }


    public static function removeParentRelation($key, $fieldarr = [], $id)
    {
        //foreach ($fieldarr as $field) {
        self::where('program_id', '=', (int)$key)->pull('parent_relations.active_parent_rel', (int)$id);
        //}

        return self::where('program_id', $key)->update(['updated_at' => time()]);
    }

    public static function addParentRelation($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('program_id', $key)->push('parent_relations.' . $field, (int)$id, true);
        }

        return self::where('program_id', $key)->update(['updated_at' => time()]);
    }

    public static function updateChildRelation($key, $arrname, $updateArr, $overwrite = false)
    {

        self::where('program_id', $key)->push('child_relations.active_channel_rel', (int)$updateArr, true);
        return self::where('program_id', $key)->update(['updated_at' => time()]);
    }

    public static function bulkAddTransactions($key, $level, $programid, $channelarr = [], $slug, $title)
    {
        $now = time();
        foreach ($channelarr as $pid) {
            $child_channel = self::getProgramDetailsByID($pid);
            $trans_id = Transaction::uniqueTransactionId();
            $transaction = [
                'DAYOW' => Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'l'),
                'DOM' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'j'),
                'DOW' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'w'),
                'DOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'z'),
                'MOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'n'),
                'WOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'W'),
                'YEAR' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'Y'),
                'trans_level' => $level,
                'id' => $key,
                'created_date' => time(),
                'trans_id' => (int)$trans_id,
                'full_trans_id' => 'ORD' . sprintf('%07d', (string)$trans_id),
                'access_mode' => 'assigned_by_admin',
                'added_by' => Auth::user()->username,
                'added_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                'created_at' => time(),
                'updated_at' => time(),
                'type' => 'subscription',
                'status' => 'COMPLETE', // This is transaction status
            ];

            $transaction_details = [
                'trans_level' => $level,
                'id' => $key,
                'trans_id' => (int)$trans_id,
                'program_id' => $pid,
                'package_id' => (int)$programid,
                //'program_slug' => $slug,
                'program_slug' => $child_channel['program_slug'],
                'type' => 'content_feed',
                'program_sub_type' => 'collection',
                //'program_title' => $title,
                'program_title' => $child_channel['program_title'],
                'duration' => [ // Using the same structure from duration master
                    'label' => 'Forever',
                    'days' => 'forever',
                ],
                'start_date' => '', // Empty since the duration is forever
                'end_date' => '', // Empty since the duration is forever
                'created_at' => time(),
                'updated_at' => time(),
                'status' => 'COMPLETE',
            ];
            // Add record to user transaction table
            Transaction::insert($transaction);
            TransactionDetail::insert($transaction_details);
        }
    }

    public static function updateParentChild($slug, $subtype = 'single')
    {
        if ($subtype == 'collection') {
            self::where('program_slug', '=', $slug)->where('status', '!=', 'DELETED')->unset('parent_relations');
        }
        if ($subtype == 'single') {
            self::where('program_slug', '=', $slug)->where('status', '!=', 'DELETED')->unset('child_relations');
        }
    }

    public static function getchildrencount($slug)
    {
        $count = 0;
        $program = self::where('program_slug', '=', $slug)->where('status', '!=', 'DELETED')->get()->toArray();
        if (isset($program[0]['child_relations']['active_channel_rel'])) {
            $count = count($program[0]['child_relations']['active_channel_rel']);
        }
        return $count;
    }

    public static function getProgram($program_slug)
    {
        return self::where('program_slug', $program_slug)
            ->where('status', '=', 'ACTIVE')
            ->get()->toArray();
    }

    public static function getOrderPackage($slug)
    {
        $package = null;
        $program = self::getProgram($slug);
        if (isset($program[0]['program_sub_type']) && !empty($program[0]['program_sub_type'] == 'collection')) {
            if (isset($program[0]['child_relations']['active_channel_rel']) && !empty($program[0]['child_relations']['active_channel_rel'])) {
                foreach ($program[0]['child_relations']['active_channel_rel'] as $child_id) {
                    $package[] = (int)$child_id;
                }
            }
        }

        return $package;
    }

    public static function getDashboardChannelPack($program_id)
    {
        $title = '';
        $flag = 0;
        $usergroups = [];
        $user_id = (int)Auth::user()->uid;
        $user = User::where('uid', '=', (int)$user_id)->value('relations');
        if (isset($user['active_usergroup_user_rel'])) {
            $usergroups = $user['active_usergroup_user_rel'];
        }

        $transactions = TransactionDetail::where('program_id', '=', (int)$program_id)->
        where('id', '=', (int)$user_id)->
        where('trans_level', '=', 'user')->
        where('status', '=', 'COMPLETE')->
        get()->toArray();

        if (!empty($transactions)) {
            foreach ($transactions as $transaction) {
                if (isset($transaction['package_id'])) {
                    $flag = 1;
                    $program = self::getProgramDetailsByID($transaction['package_id']);
                    $title = $program['program_title'];
                    return $title;
                }
            }
        }
        if ($flag == 0) {
            $transactions = TransactionDetail::where('program_id', '=', (int)$program_id)->
            where('trans_level', '=', 'usergroup')->
            where('status', '=', 'COMPLETE')->
            get()->toArray();
            if (!empty($transactions)) {
                foreach ($transactions as $transaction) {
                    if (isset($transaction['package_id'])) {
                        if (in_array($transaction['id'], $usergroups)) {
                            $program = self::getProgramDetailsByID($transaction['package_id']);
                            $title = $program['program_title'];
                            return $title;
                        }
                    }
                }
            }
        }

        return $title;
    }

    public static function getFilteredPackWithPagination($pack_id, $start = 0, $limit = 10, $orderby = ['created_at' => 'desc'])
    {
        $program = self::getProgramDetailsByID($pack_id);

        $key = key($orderby);
        $value = $orderby[$key];
        if (isset($program['child_relations']['active_channel_rel']) && !empty($program['child_relations']['active_channel_rel'])) {
            return self::whereIn('program_id', $program['child_relations']['active_channel_rel'])->where('program_id', '>', 0)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
        } else {
            $result = [];
            return $result;
        }
    }

    public static function getDashboardChannelPackCount($program_id)
    {
        $count = 0;
        $program = self::getProgramDetailsByID($program_id);
        if (isset($program['parent_relations']['active_parent_rel']) && !empty($program['parent_relations']['active_parent_rel'])) {
            $count = count($program['parent_relations']['active_parent_rel']);
        }
        return $count;
    }

    public static function getCourseBatchCount($course_id)
    {
        return self::where('parent_id', '=', (int)$course_id)
            ->where('status', '!=', 'DELETED')
            ->count();
    }

    public static function getCourseBatchList($course_id)
    {
        return self::where('parent_id', '=', (int)$course_id)
            ->where('status', '!=', 'DELETED')
            ->get()->toArray();
    }

    public static function deleteCourse($slug = '')
    {
        $programs = self::getAllProgramByIDOrSlug('course', $slug);
        $programs = $programs->toArray();
        if ($slug == null || empty($programs)) {
            $msg = trans('program.slug_missing_error');
            return redirect('/cp/contentfeedmanagement/list-courses')
                ->with('error', $msg);
        }
        $programs = $programs[0];
        // Get all packets and remove the dams relations
        $packets = Packet::getAllPackets($programs['program_slug']);
        foreach ($packets as $val) {
            if (isset($val['packet_cover_media']) && $val['packet_cover_media']) {
                Dam::removeMediaRelation($val['packet_cover_media'], ['packet_banner_media_rel'], (int)$val['packet_id']);
            }
            if (isset($val['elements']) && is_array($val['elements'])) {
                foreach ($val['elements'] as $element) {
                    if ($element['type'] == 'media') {
                        Dam::removeMediaRelationUsingID($element['id'], ['dams_packet_rel'], (int)$val['packet_id']);
                    } elseif ($element['type'] == 'assessment') {
                        Quiz::removeQuizRelationForFeed($element['id'], (string)$programs['program_id'], (int)$val['packet_id']);
                    } elseif ($element['type'] == 'event') {
                        Event::removeEventRelation($element['id'], ['event_packet_rel'], (int)$val['packet_id']);
                        Event::where("event_id", $element['id'])->pull("relations.feed_event_rel.{$program["program_id"]}", (int)$val['packet_id']);
                    }
                }
            }
            Packet::updatePacket($val['packet_slug'], ['status' => 'DELETED', 'elements' => []]);
        }

        Program::where('program_slug', '=', $slug)->where('program_type', '=', 'course')->where('status', '!=', 'DELETED')->update(['status' => 'DELETED']);
        return $programs['program_id'];
    }

    public static function getCourseCount(
        $type = 'all',
        $search = null,
        $relinfo = null,
        $userid = null,
        $usergrp = [],
        $visibility = 'all',
        $sellability = 'all',
        $feed_title = null,
        $shortname = null,
        $created_date = null,
        $updated_date = null,
        $description = null,
        $feed_tags = null,
        $custom_field_name = [],
        $custom_field_value = [],
        $category = null,
        $batch_name = null,
        $get_created_date = '=',
        $get_updated_date = '='
    )
    {

        return self::where('program_type', '=', 'course')
            ->where('parent_id', '=', 0)
            ->FeedSearch($search)
            ->FeedFilter($type, $relinfo)
            ->VisibilityFilter($visibility)
            ->SellabilityFilter($sellability)
            ->TitleFilter($feed_title)
            ->ShortnameFilter($shortname)
            ->CreateddateFilter($created_date, $get_created_date)
            ->UpdateddateFilter($updated_date, $get_updated_date)
            ->DescriptionFilter($description)
            ->FeedtagsFilter($feed_tags)
            ->CategoryFilter($category)
            ->BatchFilter($batch_name)
            ->CustomfieldFilter($custom_field_name, $custom_field_value)
            ->where('status', '!=', 'DELETED')
            ->count();
    }

    public static function getCourseWithTypeAndPagination(
        $type = 'all',
        $start = 0,
        $limit = 10,
        $orderby = ['created_at' => 'desc'],
        $search = null,
        $relinfo = null,
        $userid = null,
        $usergrp = [],
        $visibility = 'all',
        $sellability = 'all',
        $feed_title = null,
        $shortname = null,
        $created_date = null,
        $updated_date = null,
        $description = null,
        $feed_tags = null,
        $custom_field_name = [],
        $custom_field_value = [],
        $category = null,
        $batch_name = null,
        $get_created_date = '=',
        $get_updated_date = '='
    )
    {

        return self::where('program_type', '=', 'course')
            ->where('parent_id', '=', 0)
            ->FeedSearch($search)
            ->FeedFilter($type, $relinfo)
            ->VisibilityFilter($visibility)
            ->SellabilityFilter($sellability)
            ->TitleFilter($feed_title)
            ->ShortnameFilter($shortname)
            ->CreateddateFilter($created_date, $get_created_date)
            ->UpdateddateFilter($updated_date, $get_updated_date)
            ->DescriptionFilter($description)
            ->FeedtagsFilter($feed_tags)
            ->CategoryFilter($category)
            ->BatchFilter($batch_name)
            ->CustomfieldFilter($custom_field_name, $custom_field_value)
            ->where('status', '!=', 'DELETED')
            ->GetOrderBy($orderby)
            ->GetByPagination($start, $limit)
            ->GetAsArray();
    }

    public static function getChannel($status, $program_type, $program_sub_type, $filter_params = [])
    {
        $channelAttributes = config('app.ChannelExportChannelFields');
        if ('ALL' === $status) {
            
            return self::where('status', '!=', 'DELETED')
                ->where('program_type', '=', $program_type)
                ->where('program_sub_type', '=', $program_sub_type)
                ->when(
                    array_has($filter_params, "in_ids"),
                    function ($query) use ($filter_params) {
                        return $query->whereIn("program_id", $filter_params["in_ids"]);
                    }
                )
                ->get($channelAttributes)
                ->toArray();

        } else {
            
            return self::where('status', '=', $status)
                ->where('program_type', '=', $program_type)
                ->where('program_sub_type', '=', $program_sub_type)
                ->when(
                    array_has($filter_params, "in_ids"),
                    function ($query) use ($filter_params) {
                        return $query->whereIn("program_id", $filter_params["in_ids"]);
                    }
                )
                ->get($channelAttributes)
                ->toArray();
        }
    }

    public static function getCourseListForCopyContent($parent_slug, $slug)
    {
        $program = Program::where('program_slug', '=', $parent_slug)->get()->toArray();
        $programs = Program::where('program_slug', '!=', $slug)->where('parent_id', '=', (int)$program[0]['program_id'])->where('status', '!=', 'DELETED')->get()->toArray();
        return array_merge($program, $programs);
    }

    public static function scopeProgramType($query, $p_type, $search = null)
    {
        if ($p_type === "collection") {
            if (!empty($search)) {
                return $query->where('program_type', 'content_feed')
                    ->where('program_sub_type', 'collection')
                    ->where(function ($query) use ($search) {
                        $query->orWhere('program_title', 'like', "%$search%")
                            ->orWhere('program_description', 'like', "%$search%")
                            ->orWhere('program_keywords', 'like', "%$search%")
                            ->orWhere('program_slug', 'like', "%$search%");
                    });
            }
            return $query->where('program_type', 'content_feed')->where('program_sub_type', 'collection');
        } elseif ($p_type === "product") {
            if (!empty($search)) {
                return $query->where('program_type', $p_type)
                    ->where(function ($query) use ($search) {
                        $query->orWhere('program_title', 'like', "%$search%")
                            ->orWhere('program_description', 'like', "%$search%")
                            ->orWhere('program_keywords', 'like', "%$search%")
                            ->orWhere('program_slug', 'like', "%$search%");
                    });
            }
            return $query->where('program_type', $p_type);
        } else {
            if (!empty($search)) {
                return $query->where('program_type', $p_type)
                    ->where('program_sub_type', 'single')
                    ->where(function ($query) use ($search) {
                        $query->orWhere('program_title', 'like', "%$search%")
                            ->orWhere('program_description', 'like', "%$search%")
                            ->orWhere('program_keywords', 'like', "%$search%")
                            ->orWhere('program_slug', 'like', "%$search%");
                    });
            }

            return $query->where('program_type', $p_type)->where('program_sub_type', 'single');
        }
    }

    public static function getChannelData($search, $sub_type)
    {
        return self::where('program_title', 'like', '%' . $search . '%')
            ->where('program_type', '=', 'content_feed')
            ->where('program_sub_type', '=', $sub_type)
            ->where('status', '!=', 'DELETED')
            ->lists('program_title')
            ->all();
    }

    public static function getCourseData($search)
    {
        return self::where('program_title', 'like', '%' . $search . '%')
            ->where('program_type', '=', 'course')
            ->where('parent_id', '=', 0)
            ->where('status', '!=', 'DELETED')
            ->lists('program_title')
            ->all();
    }

    public static function getProductData($search)
    {
        return self::where('program_title', 'like', '%' . $search . '%')
            ->where('program_type', '=', 'product')
            ->where('status', '!=', 'DELETED')
            ->lists('program_title')
            ->all();
    }

    public static function getBatchData($search)
    {
        return self::where('program_title', 'like', '%' . $search . '%')
            ->where('program_type', '=', 'course')
            ->where('parent_id', '>', 0)
            ->where('status', '!=', 'DELETED')
            ->lists('program_title')
            ->all();
    }

    public static function getFeedListForPromocodeCount($relfilter, $promocode_id, $program_type, $search, $orderby)
    {
        $list = PromoCode::where('id', '=', (int)$promocode_id)->first();

        $promocode_feed_rel = isset($list->feed_rel) ? $list->feed_rel : [];

        if ($relfilter == 'assigned') {
            return self::where('status', '!=', 'DELETED')
                ->FeedSearch($search)
                ->whereIn('program_id', $promocode_feed_rel)
                ->ProgramTypePromocode($program_type)
                ->GetOrderBy($orderby)
                ->count();
        } elseif ($relfilter == 'nonassigned') {
            return self::where('status', '!=', 'DELETED')
                ->FeedSearch($search)
                ->whereNotIn('program_id', $promocode_feed_rel)
                ->ProgramTypePromocode($program_type)
                ->GetOrderBy($orderby)
                ->count();
        } else {
            return self::where('status', '!=', 'DELETED')
                ->FeedSearch($search)
                ->ProgramTypePromocode($program_type)
                ->GetOrderBy($orderby)
                ->count();
        }
    }

    public static function getFeedListForPromocode($start = 0, $limit = 10, $relfilter, $promocode_id, $program_type, $search, $orderby)
    {
        $list = PromoCode::where('id', '=', (int)$promocode_id)->first();

        $promocode_feed_rel = isset($list->feed_rel) ? $list->feed_rel : [];

        if ($relfilter == 'assigned') {
            return self::where('status', '!=', 'DELETED')
                ->whereIn('program_id', $promocode_feed_rel)
                ->FeedSearch($search)
                ->ProgramTypePromocode($program_type)
                ->GetOrderBy($orderby)
                ->GetByPagination($start, $limit)
                ->GetAsArray();
        } elseif ($relfilter == 'nonassigned') {
            return self::where('status', '!=', 'DELETED')
                ->whereNotIn('program_id', $promocode_feed_rel)
                ->FeedSearch($search)
                ->ProgramTypePromocode($program_type)
                ->GetOrderBy($orderby)
                ->GetByPagination($start, $limit)
                ->GetAsArray();
        } else {
            return self::where('status', '!=', 'DELETED')
                ->ProgramTypePromocode($program_type)
                ->FeedSearch($search)
                ->GetOrderBy($orderby)
                ->GetByPagination($start, $limit)
                ->GetAsArray();
        }
    }

    public static function scopeProgramTypePromocode($query, $program_type)
    {
        switch ($program_type) {
            case 'content_feed':
                $query = $query->where('program_type', 'content_feed')->where('program_sub_type', 'single');
                break;

            case 'product':
                $query = $query->where('program_type', 'product');
                break;

            case 'course':
                $query = $query->where('program_type', 'course');
                break;

            case 'package':
                $query = $query->where('program_type', 'content_feed')->where('program_sub_type', '=', 'collection');
                break;

            default:
                $query = $query->where('program_type', 'content_feed')->where('program_sub_type', 'single');
                break;
        }
        return $query->where('program_sellability', 'yes');
    }

    public static function getProgramCategoryRelation($program_id, $program_type, $program_sub_type)
    {
        if (is_array($program_id)) {
            return Program::where('program_type', '=', $program_type)->where('program_sub_type', '=', $program_sub_type)->where('status', '=', 'ACTIVE')->whereIn('program_id', $program_id)->lists('program_categories')->all();
        }
    }

    public static function getIDbySlug($slug = null)
    {
        if (!is_null($slug)) {
            return self::where('status', '=', 'ACTIVE')
                ->where('program_slug', '=', $slug)
                ->value('program_id');
        }
    }

    public static function getProgramsForReports()
    {
        return self::where('status', '=', 'ACTIVE')
            ->where('status', '!=', 'DELETED')
            ->orWhere(function ($single) {
                $single->where('program_type', '=', 'content_feed')
                    ->where('program_sub_type', '=', 'single');
            })
            ->orWhere(function ($course) {
                $course->where('program_type', '=', 'course')
                    ->where('parent_id', '!=', 0);
            })
            ->orWhere('program_type', '=', 'product')
            ->orderBy('program_title', 'asc')
            ->get()
            ->toArray();
    }

    public static function getCFSlugForReports($feed_id = [])
    {
        if (!empty($feed_id) && is_array($feed_id)) {
            return self::where('status', '=', 'ACTIVE')
                ->whereIn('program_id', $feed_id)
                ->where('status', '!=', 'DELETED')
                ->orWhere(function ($single) {
                    $single->where('program_type', '=', 'content_feed')
                        ->where('program_sub_type', '=', 'single');
                })
                ->orWhere(function ($course) {
                    $course->where('program_type', '=', 'course')
                        ->where('parent_id', '!=', 0);
                })
                ->orWhere('program_type', '=', 'product')
                ->get(['program_slug'])
                ->toArray();
        } else {
            return [];
        }
    }

    public static function getProgramTypeAndSubType($slug)
    {
        return self::where('program_slug', '=', $slug)
            ->get(['program_id', 'program_type', 'program_sub_type', 'relations'])->toArray();
    }

    public static function pluckFeedNameByID($id)
    {
        return self::where('program_id', '=', (int)$id)->where('status', '!=', 'DELETED')->value('program_title');
    }

    public static function getTypebyID($id)
    {
        return self::whereIn('program_id', $id)->where('program_type', '=', 'content_feed')->where('program_sub_type', '=', 'single')->get(['program_id'])->toArray();
    }

    public static function getPackageName($program_id)
    {
        $packId = [];
        $program = self::getProgramDetailsByID($program_id);
        if (isset($program['parent_relations']['active_parent_rel']) && !empty($program['parent_relations']['active_parent_rel'])) {
            $packId = $program['parent_relations']['active_parent_rel'];
        }
        return self::whereIn('program_id', $packId)->lists('program_title')->all();
    }

    /**
     * Create many to many relation b/w program and user
     * @return mixed
     */
    public function users()
    {
        return $this->belongsToMany(
            \App\Model\User::class,
            "relations.active_user_feed_rel",
            "relations.user_feed_rel"
        );
    }

    /**
     * Create many to many relation b/w program and userGroup
     * @return mixed
     */
    public function userGroup()
    {
        return $this->belongsToMany(
            UserGroup::class,
            "relations.active_usergroup_feed_rel",
            "relations.usergroup_feed_rel"
        );
    }

    /**
     * Create many to many relation b/w program and package
     *
     * @return \Jenssegers\Mongodb\Relations\BelongsToMany
     */
    public function packages()
    {
        return $this->belongsToMany(
            Package::class,
            'package_ids',
            'program_ids'
        );
    }

    /**
     * @param $query
     * @param array $filter_params
     */
    public function scopeFilter($query, $filter_params)
    {
        return $query->when(
                !empty($filter_params["in_ids"]),
                function ($query) use ($filter_params) {
                    return $query->whereIn("program_id", $filter_params["in_ids"]);
                }
            )->when(
                !empty($filter_params["not_in_ids"]),
                function ($query) use ($filter_params) {
                    return $query->whereNotIn("program_id", $filter_params["not_in_ids"]);
                }
            )->when(
                array_has($filter_params, "type"),
                function ($query) use ($filter_params) {
                    return $query->where("program_type", $filter_params["type"]);
                }
            )->when(
                array_has($filter_params, "search_key") && !empty($filter_params["search_key"]),
                function ($query) use ($filter_params) {
                    return $query->where(function ($query) use ($filter_params) {
                        $search_key = preg_replace('/\W/', '$0', $filter_params["search_key"]);

                        return $query->where("program_title", "like", "%{$search_key}%")
                            ->orWhere("program_shortname", "like", "%{$search_key}%")
                            ->orWhere("program_description", "like", "%{$search_key}%");
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
                    return $query->where("status", "!=", ProgramStatus::DELETED);
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
            )->when( 
                array_has($filter_params, "sub_type"), 
                function ($query) use ($filter_params) { 
                    return $query->where("program_sub_type", $filter_params["sub_type"]); 
                } 
            );
    }

    /**
     * Scope a query to only include display active packages
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $timestamp
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDisplayActive($query, $timestamp)
    {
        return $query->where('program_display_enddate', '>=', $timestamp);
    }

    /**
     * Scope a query to only include status active packages
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', '>=', 'ACTIVE');
    }
     public static function scopeFeedStatus($query, $status = 'ALL')
    {
         if ($status != 'ALL') {
            $query->where(function ($q) use ($status) {
                $q->where('status', '=',  $status);
                   
            });
        }else{
            $query->where(function ($q) use ($status){
                $q->where('status', '!=', 'DELETED');
            });
        }

        return $query;
    }

    public static function scopeCourseRecords($query, $program_type)
    {
        if($program_type == 'course'){
            $query->where(function($q) use ($program_type){
                $q->where('parent_id', '=', 0);
            });

        }
        return $query;
    }

}
