<?php

namespace App\Model\Announcement;

use App\Exceptions\User\RelationNotFoundException;
use App\Model\Announcement;
use App\Model\Program;
use App\Model\UserGroup;
use Auth;
use Carbon\Carbon;

/**
 * Class AnnouncementRepository
 *
 * @package App\Model\Announcement
 */
class AnnouncementRepository implements IAnnouncementRepository
{

    /**
     * {@inheritdoc}
     */
    public function getUserAnnouncements($page, $limit)
    {
        $announcement_ids = $this->getAllPrivateAnnouncements();
        $start = ($page * $limit) - $limit;
        $columns = ['announcement_id', 'announcement_title', 'announcement_content'];
        if (!empty($announcement_ids)) {
            return $this->getAnnouncementDataWithIds($announcement_ids, $start, $limit, $columns);
        } else {
            return $this->getAnnouncementDataWithoutIds($start, $limit, $columns);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllPrivateAnnouncements()
    {
        if (!isset(Auth::user()->relations)) {
            throw new RelationNotFoundException();
        }
        $relations = Auth::user()->relations;
        $announcement_ids = [];
        $private_announcements = [];
        foreach ($relations as $key => $relation) {
            //user group relations
            if ($key == 'active_usergroup_user_rel') {
                $userGroupAnnouncementLists = UserGroup::getAnnouncementList($relation);
                foreach ($userGroupAnnouncementLists as $userGroupAnnouncementList) {
                    if (isset($userGroupAnnouncementList['relations']['usergroup_announcement_rel'])) {
                        $private_announcements =  Announcement::filterPrivateAnnouncements($userGroupAnnouncementList['relations']['usergroup_announcement_rel']);
                    }

                    if (isset($private_announcements) && !empty($private_announcements)) {
                        foreach ($private_announcements as $id) {
                            $announcement_ids[] = $id;
                        }
                    }
                }
            }
            if ($key == 'user_feed_rel' || $key == 'user_package_feed_rel' || $key == 'user_course_feed_rel') {
                $programAnnouncementLists = Program::getAnnouncementList($relation);
                foreach ($programAnnouncementLists as $programAnnouncementList) {
                    if (isset($programAnnouncementList['relations']['contentfeed_announcement_rel'])) {
                        $private_announcements =  Announcement::filterPrivateAnnouncements($programAnnouncementList['relations']['contentfeed_announcement_rel']);
                    }

                    if (isset($private_announcements) && !empty($private_announcements)) {
                        foreach ($private_announcements as $id) {
                            $announcement_ids[] = $id;
                        }
                    }
                }
            }
            if ($key == 'user_announcement_rel' && !empty($relation)) {
                foreach ($relation as $id) {
                    $announcement_ids[] = $id;
                }
            }
        }
        return array_unique($announcement_ids);
    }

    /**
     * Method to get all announcements with status as registerusers and ids
     *
     * @param array $ids announcement ids
     * @param $start
     * @param int $limit no of results
     * @param array $columns
     * @return object
     * @internal param int $page page number
     */
    private function getAnnouncementDataWithIds($ids, $start, $limit, $columns = [])
    {
        $announcements = Announcement::where('status', '=', 'ACTIVE')
            ->Where(
                function ($query) use ($ids) {
                    $query->orwhereIn('announcement_id', $ids)
                             ->orwhere('announcement_for', '=', 'registerusers');
                }
            )
            ->where('schedule', '<', Carbon::today()->timestamp)
            ->where('expire_date', '>=', Carbon::today()->timestamp)
            ->orderBy('schedule', 'desc')
            ->skip((int)$start)
            ->take((int)$limit)
            ->get($columns);

        return $announcements;
    }

    /**
     * Method to get all announcements with status as registerusers
     * @param int $start start record number
     * @param int $limit no of results
     * @param $columns
     * @return object
     */
    private function getAnnouncementDataWithoutIds($start, $limit, $columns = [])
    {
        $announcements = Announcement::where('status', '=', 'ACTIVE')
            ->Where(function ($query) {
                    $query->orwhere('announcement_for', '=', 'registerusers');
            })
            ->where('schedule', '<', Carbon::today()->timestamp)
            ->where('expire_date', '>=', Carbon::today()->timestamp)
            ->orderBy('schedule', 'desc')
            ->skip((int)$start)
            ->take((int)$limit)
            ->get($columns);
        return $announcements;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminUserAnnouncements($start, $limit, $columns = [])
    {
        $announcements = Announcement::where('status', '=', 'ACTIVE')
            ->where('schedule', '<', time())
            ->where('expire_date', '>=', time())
            ->orderBy('schedule', 'desc')
            ->skip((int)$start)
            ->take((int)$limit)
            ->get($columns);
        return $announcements;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllPublicAnnouncements($start, $limit, $device)
    {
        if ($device == 'mobile') {
            $temp = 'Web only';
        } elseif ($device == 'web') {
            $temp = 'Mobile only';
        } else {
            $temp = 'both';
        }

        return Announcement::where('status', '=', 'ACTIVE')
            ->where('announcement_device', '!=', $temp)
            ->Where('announcement_for', '=', 'public')
            ->where('schedule', '<', Carbon::today()->timestamp)
            ->where('expire_date', '>=', Carbon::today()->timestamp)
            ->orderBy('schedule', 'desc')->skip((int)$start)->take((int)$limit)->get()->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getUnReadPublicAnnouncementsCount($user_id, $announcement_ids, $device)
    {
        if ($device == 'mobile') {
            $temp = 'Web only';
        } elseif ($device == 'web') {
            $temp = 'Mobile only';
        } else {
            $temp = 'both';
        }
        if (!is_null($user_id) && !is_null($announcement_ids)) {
            return Announcement::where('status', '=', 'ACTIVE')
                ->whereNotIN('readers.user', [$user_id])
                ->where('announcement_device', '!=', $temp)
                ->where(function ($query) use ($announcement_ids) {
                    $query->orWhereIn('announcement_id', $announcement_ids);
                })
                ->where('schedule', '<', time())
                ->where('expire_date', '>=', time())
                ->count();
        } elseif (!is_null($announcement_ids)) {
            return Announcement::where('status', '=', 'ACTIVE')
                ->where('announcement_device', '!=', $temp)
                ->where(function ($query) use ($announcement_ids) {
                    $query->orWhereIn('announcement_id', $announcement_ids);
                })
                ->where('schedule', '<', time())
                ->where('expire_date', '>=', time())
                ->count();
        } else {
            return 0;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllAnnouncements($filter_params = [])
    {
        return $this->get($filter_params);
    }

    /**
     * {@inheritdoc}
     */
    public function getAnnouncementsCreatedByUsers($usernames = [])
    {
        return Announcement::whereIn('created_by', $usernames)
                ->where('status', '!=', 'DELETE')
                ->get();
    }

    /**
     * @inheritDoc
     */
    public function get($filter_params = [])
    {
        return Announcement::filter($filter_params)->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getNewAnnouncementCount(array $announcement_ids, array $date)
    {
        $query = Announcement::where('status', '=', 'ACTIVE');
        if (!empty($announcement_ids)) {
            $query->whereIn('announcement_id', $announcement_ids);
        }
        $query->whereBetWeen('created_at', $date);
        return $query->count();
    }

    /**
     * {@inheritDoc}
     */
    public function getNewAnnouncements(array $announcement_ids, array $date, $start, $limit)
    {
        $query = Announcement::where('status', '=', 'ACTIVE');
        if (!empty($announcement_ids)) {
            $query->whereIn('announcement_id', $announcement_ids);
        }
        $query->whereBetWeen('created_at', $date);
        return $query->skip((int)$start)
                    ->take((int)$limit)
                    ->orderBy('created_at', 'desc')
                    ->get(['announcement_title', 'announcement_for']);
    }

}
