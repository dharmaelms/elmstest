<?php

namespace App\Model;

use Auth;
use Moloquent;
use Schema;

/**
 * Event model
 *
 * @package Event
 */
class Event extends Moloquent
{
    /**
     * The table/collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'events';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'event_id' => 'integer',
    ];

    protected $dates = ['start_time', 'end_time', 'created_at', 'updated_at'];

    /**
     * Function generate unique auto incremented id for this collection
     *
     * @return integer
     */
    public static function getNextSequence()
    {
        return Sequence::getSequence('event_id');
    }

    /**
     * Extending the query for search functionality using the scope feature
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $searchKey key to search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function scopeSearch($query, $searchKey = null)
    {
        if (!empty($searchKey)) {
            $query->where('event_name', 'like', '%' . $searchKey . '%')
                ->orWhere('event_description', 'like', '%' . $searchKey . '%')
                ->orWhere('event_short_description', 'like', '%' . $searchKey . '%');
        }
        return $query;
    }

    /**
     * Extending the query to filter the event type using the scope feature
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $searchKey key to search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function scopeType($query, $type = 'all')
    {
        if ($type == 'live') {
            $query->where('event_type', '=', 'live');
        } elseif ($type == 'general') {
            $query->where('event_type', '=', 'general');
        }
        return $query;
    }

    /**
     * Scope a query to include only active events.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }
    
    /**
     * Generate the user list based on the realtions with users like
     * user_event, user_group_event, user_feeds_event & user_group_feed_event
     *
     * @return array
     */
    public static function userEventRel()
    {
        $user = Auth::user()->toArray();

        // Host privileged events
        $hevent = Event::where('status', '=', 'ACTIVE')
            ->where('event_host_id', '=', $user['uid'])
            ->get(['event_id']);

        $host_event = [];
        if (!$hevent->isEmpty()) {
            foreach ($hevent as $value) {
                $host_event[] = (int)$value->event_id;
            }
        }

        // Users
        $user_event = [];
        if (isset($user['relations']['user_event_rel']) && !empty($user['relations']['user_event_rel'])) {
            $user_event = $user['relations']['user_event_rel'];
        }

        // User groups
        $usergroup_event = $assigned_feed = [];
        if (isset($user['relations']['active_usergroup_user_rel']) && !empty($user['relations']['active_usergroup_user_rel'])) {
            $usergroup = UserGroup::where('status', '=', 'ACTIVE')
                ->whereIn('ugid', $user['relations']['active_usergroup_user_rel'])
                ->get()
                ->toArray();
            foreach ($usergroup as $value) {
                // Event assigned to user group
                if (!empty($value['relations']['usergroup_event_rel'])) {
                    $usergroup_event = array_merge($usergroup_event, $value['relations']['usergroup_event_rel']);
                }
                // CF assigned to user group
                if (!empty($value['relations']['usergroup_feed_rel'])) {
                    $assigned_feed = array_merge($assigned_feed, $value['relations']['usergroup_feed_rel']);
                }
            }
        }

        // Content feeds
        $feed_event_list = $feed_event = [];

        // CF assigned directly to user
        if (isset($user['relations']['user_feed_rel']) && !empty($user['relations']['user_feed_rel'])) {
            $assigned_feed = array_merge($assigned_feed, $user['relations']['user_feed_rel']);
        }

        $time = time();
        $feed = Program::where('status', '=', 'ACTIVE')
            ->whereIn('program_id', $assigned_feed)
            ->where('program_startdate', '<=', $time)
            ->where('program_enddate', '>=', $time)
            ->orderby('program_title')
            ->get()
            ->toArray();

        $feed_list = [];
        foreach ($feed as $value) {
            $feed_list[] = $value['program_slug'];
        }

        if (!empty($feed_list)) {
            $packet = Packet::where('status', '=', 'ACTIVE')
                ->whereIn('feed_slug', $feed_list)
                ->get();
            foreach ($packet as $p) {
                $feed_event_list[$p->feed_slug] = [];
                foreach ($p->elements as $value) {
                    if ($value['type'] == 'event') {
                        $feed_event[] = $value['id'];
                        $feed_event_list[$p->feed_slug][] = $value['id'];
                    }
                }
            }
        }

        // Get event list for this user
        return [
            'event_list' => array_merge($host_event, $user_event, $usergroup_event, $feed_event),
            'feed_list' => $feed,
            'feed_event_list' => $feed_event_list
        ];
    }

    // Added by Cerlin
    public static function removeEventRelation($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('event_id', $key)->pull('relations.' . $field, (int)$id);
        }
        return self::where('event_id', $key)->update(['updated_at' => time()]);
    }

    public static function addEventRelation($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('event_id', $key)->push('relations.' . $field, (int)$id, true);
        }
        return self::where('event_id', $key)->update(['updated_at' => time()]);
    }

    /*for statistic */
    public static function getLastThirtydaysCreatedEventCount()
    {
        return self::where('status', '=', 'ACTIVE')
            ->where('created_at', '>', strtotime('-30 day', time()))
            ->get()
            ->count();
    }

    /*for UAR*/
    public static function getUnassignEventwithUsersorCF($start = 0, $limit = 3)
    {
        return self::where('status', '=', 'ACTIVE')
            ->where('start_time', '<', strtotime('7 day', time()))
            ->where(function ($qry) {
                $qry->orwhere(function ($qery) {
                    $qery->where('relations.active_user_event_rel', 'exists', false)
                        ->where('relations.active_usergroup_event_rel', 'exists', false);
                })
                    ->orwhere(function ($q) {
                        $q->where('relations.active_user_event_rel.0', 'exists', false)
                            ->where('relations.active_usergroup_event_rel.0', 'exists', false);
                    });
            })
            ->orderby('start_time', 'asc')
            ->skip((int)$start)
            ->take((int)$limit)
            ->get(['event_id', 'start_time', 'event_name'])
            ->toArray();
    }

    public static function getUnassignEventwithUsersorCFCount($start = 0, $limit = 3)
    {
        return self::where('status', '=', 'ACTIVE')
            ->where('start_time', '<', strtotime('7 day', time()))
            ->where(function ($qry) {
                $qry->orwhere(function ($qery) {
                    $qery->where('relations.active_user_event_rel', 'exists', false)
                        ->where('relations.active_usergroup_event_rel', 'exists', false);
                })
                    ->orwhere(function ($q) {
                        $q->where('relations.active_user_event_rel.0', 'exists', false)
                            ->where('relations.active_usergroup_event_rel.0', 'exists', false);
                    });
            })
            ->count();
    }

    //Added by Sahana
    // Do not delete this function coz i am using it - Cerlin
    public static function getEventsAssetsUsingAutoID($id = 'all')
    {
        if ($id == 'all') {
            return self::get()->toArray();
        } else {
            return self::where('event_id', '=', (int)$id)->get()->toArray();
        }
    }

    //for statistic
    public static function getNewEventList($start = null, $end = null)
    {
        if (!is_null($start) && !is_null($end)) {
            return self::where('status', '=', 'ACTIVE')
                ->whereBetween('created_at', [$start, $end])
                ->count();
        } else {
            return 0;
        }
    }

    public static function getEventNameByID($ids)
    {
        return self::whereIn('event_id', $ids)->get(['event_id', 'event_name', 'created_by', 'created_at']);
    }
}
