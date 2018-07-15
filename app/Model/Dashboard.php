<?php
namespace App\Model;

use App\Exceptions\User\RelationNotFoundException;
use Auth;
use Carbon\Carbon;
use Moloquent;

/**
 * Dashboard Model
 * @author sathishkumar@linkstreet.in
 */
class Dashboard extends Moloquent
{
    /**
     * user relations array
     */
    public static $user_relations = [];

    /**
     * this function is used to check relation is exist or not
     *
     * @return array relations
     * @throws RelationNotFoundException
     */
    public static function relations()
    {
        if (isset(Auth::user()->attributes['relations'])) {
            self::$user_relations = Auth::user()->attributes['relations'];
        } else {
            throw new RelationNotFoundException();
        }
        return self::$user_relations;
    }

    /**
     * function used to get latest channels assigned to user
     */
    public static function getLatestUpdatedChannels($ids, $limit = 10)
    {
        $channels = Program::whereIn('program_id', $ids)
            ->where('status', 'ACTIVE')
            ->where('program_display_enddate', '>=', Carbon::now(Auth::user()->timezone)->getTimestamp())
            ->orderBy('updated_at', 'DESC')
            ->take((int)$limit)
            ->get();
        return $channels;
    }

    /**
     * this function is used to get announcements to particular user
     * @param  int $start description
     * @return object
     */
    public static function getAnnouncements($start, $limit)
    {
        $announcement_ids = self::getAllAnnouncementsAssignedToUser();
        if (!empty($announcement_ids)) {
            return self::getAnnouncementWithIds($announcement_ids, $start, $limit);
        } else {
            return self::getAnnouncementWithoutIds($start, $limit);
        }
    }

    /**
     * this function is used to get asessments based on conditions are public and registerusers and ids
     * @param  array $ids assessment ids
     * @return object      assessment data
     */
    public static function getAnnouncementWithIds($ids, $start, $limit)
    {
        $announcements = Announcement::where('status', '=', 'ACTIVE')
            ->Where(
                function ($query) use ($ids) {
                    $query->orwhere('announcement_for', '=', 'public')
                        ->orwhere('announcement_for', '=', 'registerusers')
                        ->orwhereIn('announcement_id', $ids);
                }
            )
            ->where('schedule', '<', time())
            ->where('expire_date', '>=', time())
            ->orderBy('schedule', 'desc')->skip((int)$start)->take((int)$limit)->get();
        return $announcements;
    }

    /**
     * this function is used to get asessments based on conditiona are public and registerusers
     * @return object assessment data
     */
    public static function getAnnouncementWithoutIds($start, $limit)
    {
        $announcements = Announcement::where('status', '=', 'ACTIVE')
            ->Where(function ($query) {
                $query->orwhere('announcement_for', '=', 'public')
                    ->orwhere('announcement_for', '=', 'registerusers');
            })
            ->where('schedule', '<', time())
            ->where('expire_date', '>=', time())
            ->orderBy('created_at', 'desc')->skip((int)$start)->take((int)$limit)->get();
        return $announcements;
    }

    /**
     * this function is used to get all announcements to particular user
     */
    public static function getAllAnnouncementsAssignedToUser()
    {
        $relations = self::relations();
        $announcement_ids = [];
        foreach ($relations as $key => $relation) {
            if ($key == 'active_usergroup_user_rel') { //user group relations
                $userGroupAnnouncementLists = UserGroup::getAnnouncementList($relation);
                foreach ($userGroupAnnouncementLists as $userGroupAnnouncementList) {
                    if (isset($userGroupAnnouncementList['relations']['usergroup_announcement_rel'])) {
                        foreach ($userGroupAnnouncementList['relations']['usergroup_announcement_rel'] as $id) {
                            $announcement_ids[] = $id;
                        }
                    }
                }
            }
            if ($key == 'user_feed_rel' || $key == 'user_package_feed_rel' || $key == 'user_course_feed_rel') {
                $programAnnouncementLists = Program::getAnnouncementList($relation);
                foreach ($programAnnouncementLists as $programAnnouncementList) {
                    if (isset($programAnnouncementList['relations']['contentfeed_announcement_rel'])) {
                        foreach ($programAnnouncementList['relations']['contentfeed_announcement_rel'] as $id) {
                            $announcement_ids[] = $id;
                        }
                    }
                }
            }
            if ($key == 'user_announcement_rel') {
                if (!empty($relation)) {
                    foreach ($relation as $id) {
                        $announcement_ids[] = $id;
                    }
                }
            }
        }
        return array_unique($announcement_ids);
    }

    public static function getAllEventsAssignedToUser()
    {
        $relations = self::relations();
        // Host privileged events
        $hosting_events = Event::where('status', '=', 'ACTIVE')
            ->where('event_host_id', '=', Auth::user()->uid)
            ->get(['event_id']);

        $events = [];
        if (!$hosting_events->isEmpty()) {
            foreach ($hosting_events as $value) {
                $events[] = (int)$value->event_id;
            }
        }

        // Users
        $user_event = [];
        if (isset($relations['user_event_rel']) && !empty($relations['user_event_rel'])) {
            $user_event = $relations['user_event_rel'];
        }

        // User groups
        $usergroup_event = $assigned_feed = [];
        if (isset($relations['active_usergroup_user_rel']) && !empty($relations['active_usergroup_user_rel'])) {
            $user_groups = UserGroup::where('status', '=', 'ACTIVE')
                ->whereIn('ugid', $relations['active_usergroup_user_rel'])
                ->get()
                ->toArray();
            foreach ($user_groups as $usergroup) {
                // Event assigned to user group
                if (!empty($usergroup['relations']['usergroup_event_rel'])) {
                    $usergroup_event = array_merge($usergroup_event, $usergroup['relations']['usergroup_event_rel']);
                }
                // program assigned to user group
                if (!empty($usergroup['relations']['usergroup_feed_rel'])) {
                    $assigned_feed = array_merge($assigned_feed, $usergroup['relations']['usergroup_feed_rel']);
                }
                // program assigned to user group
                if (!empty($usergroup['relations']['usergroup_feed_rel'])) {
                    $assigned_feed = array_merge($assigned_feed, $usergroup['relations']['usergroup_feed_rel']);
                }
            }
        }

        // Content feeds
        $feed_event_list = $feed_event = [];

        // Program assigned directly to user
        if (isset($relations['user_feed_rel']) && !empty($relations['user_feed_rel'])) {
            $assigned_feed = array_merge($assigned_feed, $relations['user_feed_rel']);
        }

        // Packages assigned directly to user
        if (isset($relations['user_feed_rel']) && !empty($relations['user_feed_rel'])) {
            $assigned_feed = array_merge($assigned_feed, $relations['user_feed_rel']);
        }

        // Program assigned directly to user
        if (isset($relations['user_package_feed_rel']) && !empty($relations['user_package_feed_rel'])) {
            $assigned_feed = array_merge($assigned_feed, $relations['user_package_feed_rel']);
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
            'event_list' => array_merge($hosting_events, $user_event, $usergroup_event, $feed_event),
            'feed_list' => $feed,
            'feed_event_list' => $feed_event_list
        ];
    }
}
