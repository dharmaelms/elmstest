<?php

namespace App\Model;

use Auth;
use Carbon\Carbon;
use Mail;
use Session;
use URL;
use App;
use Timezone;

/**
 * Class Common
 * @package App\Model
 */
class Common
{
    /**
     * @param array $crumbs
     * @return string
     */
    public static function getBreadCrumbs($crumbs)
    {
        $totalPath = '/';
        $out = '';
        $breadcrumbs = [];
        foreach ($crumbs as $crumb => $path) {
            if ($path == '') {
                $breadcrumbs[] = [
                    'name' => $crumb,
                    'link' => '',
                ];
            } else {
                if ($path == '/') {
                    $totalPath = URL::to('/') . '/';
                } else {
                    $totalPath .= "$path/";
                }
                $breadcrumbs[] = [
                    'name' => $crumb,
                    'link' => $totalPath,
                ];
            }
        }
        foreach ($breadcrumbs as $breadcrumb) {
            if ($breadcrumb['link'] == '') {
                $out .= "<span>$breadcrumb[name]</span> > ";
            } else {
                $out .= "<a href=\"$breadcrumb[link]\">$breadcrumb[name]</a> > ";
            }
        }
        $out = substr($out, 0, -3);

        return $out;
    }

    /**
     * @param array $crumbs
     * @return string
     */
    public static function getEFEBreadCrumbs($crumbs)
    {
        $totalPath = '/';
        $out = "<div class='page-bar'><ul class='page-breadcrumb'>";
        $breadcrumbs = [];
        foreach ($crumbs as $crumb => $path) {
            if ($path == '') {
                $breadcrumbs[] = [
                    'name' => $crumb,
                    'link' => '',
                ];
            } else {
                if ($path == '/') {
                    $totalPath = URL::to('/') . '/';
                } else {
                    $totalPath .= "$path/";
                }
                $breadcrumbs[] = [
                    'name' => $crumb,
                    'link' => $totalPath,
                ];
            }
        }
        foreach ($breadcrumbs as $breadcrumb) {
            $out .= "<li>";
            if ($breadcrumb['link'] == '') {
                $out .= "<span>$breadcrumb[name]</span>";
            } else {
                $out .= "<a href=\"$breadcrumb[link]\">$breadcrumb[name] <i class='fa fa-angle-right'></i> </a>  ";
            }
            $out .= "</li>";
        }
        $out = substr($out, 0, -3);

        return $out . "</ul></div>";
    }

    /**
     * @param string $message
     * @param string $subject
     * @param string $to
     * @param array $from
     * @param string $cc
     * @param string $bcc
     * @param string $attach path to a file
     * @return mixed
     */
    public static function sendMailHtml($message, $subject, $to, $from = [], $cc = null, $bcc = null, $attach = null)
    {
        return Mail::queue('admin.theme.email.echo', ['data' => $message], function ($mail) use ($to, $subject, $from, $cc, $bcc, $attach) {
            if ($from && is_array($from) && !empty($from)) {
                $fromMail = key($from);
                $fromText = $from[$fromMail];
                $mail->from($fromMail, $fromText);
            }
            if ($cc) {
                $mail->cc($cc);
            }
            if ($bcc) {
                $mail->bcc($bcc);
            }
            if ($attach) {
                $mail->attach($attach);
            }
            $mail->subject($subject);
            $mail->to($to);
        });
    }

    /**
     * @param string $view
     * @param array $data
     * @param string $subject
     * @param string $to
     * @param array $from
     * @param string $cc
     * @param string $bcc
     * @param string $attach path to a file
     * @return mixed
     */
    public static function sendMail($view, $data, $subject, $to, $from = [], $cc = null, $bcc = null, $attach = null)
    {
        return Mail::queue($view, $data, function ($mail) use ($to, $subject, $from, $cc, $bcc, $attach) {
            if ($from && is_array($from) && !empty($from)) {
                $fromMail = key($from);
                $fromText = $from[$fromMail];
                $mail->from($fromMail, $fromText);
            }
            if ($cc) {
                $mail->cc($cc);
            }
            if ($bcc) {
                $mail->bcc($bcc);
            }
            if ($attach) {
                $mail->attach($attach);
            }
            $mail->subject($subject);
            $mail->to($to);
        });
    }

    /**
     * @param array $arr
     * @param string $order
     * @param string $comp_val
     * @return array
     */
    public static function bubbleSort(array $arr, $order = 'asc', $comp_val)
    {
        $sorted = false;
        while (false === $sorted) {
            $sorted = true;
            for ($i = 0; $i < count($arr) - 1; ++$i) {
                $current = $arr[$i];
                $next = $arr[$i + 1];
                if ($order == 'asc') {
                    if ($next[$comp_val] < $current[$comp_val]) {
                        $arr[$i] = $next;
                        $arr[$i + 1] = $current;
                        $sorted = false;
                    }
                } else {
                    if ($next[$comp_val] > $current[$comp_val]) {
                        $arr[$i] = $next;
                        $arr[$i + 1] = $current;
                        $sorted = false;
                    }
                }
            }
        }

        return $arr;
    }

    /**
     * @param array $arr
     * @param bool $strtolower
     * @return array
     */
    public static function trimArray(&$arr, $strtolower = false)
    {
        if (is_array($arr)) {
            foreach ($arr as $key => $value) {
                if ($strtolower == true) {
                    $value = strtolower($value);
                }

                $arr[$key] = trim($value);
            }
            return $arr;
        }
    }

    /**
     * @param array $arr
     * @return mixed
     */
    public static function getUserProfilePicture(&$arr)
    {
        foreach ($arr as $key => $value) {
            $uid = $value['user_id'];
            $users = User::where('uid', '=', (int)$uid)->get(['profile_pic'])->toArray();
            if (isset($users[0]) && array_key_exists('profile_pic', $users[0]) && !empty($users[0]['profile_pic'])) {
                $arr[$key]['profile_pic'] = $users[0]['profile_pic'];
            }
        }
        return $arr;
    }

    /**
     * @param string $element_type
     * @return array
     */
    public static function getAuthorElement($element_type = 'media')
    {
        $user = Auth::user()->toArray();
        $media_ids_allowed = [];

        if (array_has($user, 'relations.user_feed_rel') &&
            !empty(array_get($user, 'relations.user_feed_rel'))
        ) {
            $feed_ids = array_get($user, 'relations.user_feed_rel');

            $u_media_ids_allowed = self::getMediaByFeedIDs($feed_ids, $element_type);
            $media_ids_allowed = array_merge($u_media_ids_allowed);
        }

        if (array_has($user, 'relations.active_usergroup_user_rel') &&
            !empty(array_get($user, 'relations.active_usergroup_user_rel'))
        ) {
            $feeds_2d_ids = null;

            $user_groups = UserGroup::whereIn('relations.active_user_usergroup_rel', [Auth::user()->uid])
                ->get(['relations.usergroup_feed_rel']);

            foreach ($user_groups as $key => $each_group) {
                $feeds_2d_ids[] = array_get($each_group->toArray(), 'relations.usergroup_feed_rel');
            }

            $feed_ids = self::makeSingleDimensionArray($feeds_2d_ids);

            $ug_media_ids_allowed = self::getMediaByFeedIDs($feed_ids, $element_type);

            $media_ids_allowed = array_merge($media_ids_allowed, $ug_media_ids_allowed);
        }

        return array_unique($media_ids_allowed);
    }


    /**
     * Get element list for channel admin
     *
     * @param string $element_type
     * @return array
     */
    public static function getChannelAdminElement($element_type = 'media')
    {
        $user = Auth::user()->toArray();
        $media_ids_allowed = [];

        if (array_has($user, 'relations.user_feed_rel') &&
            !empty(array_get($user, 'relations.user_feed_rel'))
        ) {
            $feed_ids = array_get($user, 'relations.user_feed_rel');

            $u_media_ids_allowed = self::getMediaByFeedIDs($feed_ids, $element_type);
            $media_ids_allowed = array_merge($u_media_ids_allowed);
        }

        if (array_has($user, 'relations.active_usergroup_user_rel') &&
            !empty(array_get($user, 'relations.active_usergroup_user_rel'))
        ) {
            $feeds_2d_ids = null;

            $user_groups = UserGroup::whereIn('relations.active_user_usergroup_rel', [Auth::user()->uid])
                ->get(['relations.usergroup_feed_rel']);

            foreach ($user_groups as $key => $each_group) {
                $feeds_2d_ids[] = array_get($each_group->toArray(), 'relations.usergroup_feed_rel');
            }

            $feed_ids = self::makeSingleDimensionArray($feeds_2d_ids);

            $ug_media_ids_allowed = self::getMediaByFeedIDs($feed_ids, $element_type);

            $media_ids_allowed = array_merge($media_ids_allowed, $ug_media_ids_allowed);
        }

        $direct_assigned_media = self::getDirectAssignedMedia($user, $element_type);

        if (!empty($direct_assigned_media) && is_array($direct_assigned_media)) {
            $media_ids_allowed = array_merge($media_ids_allowed, $direct_assigned_media);
        }

        return array_unique($media_ids_allowed);
    }

    /**
     * Convert two dimension to one
     *
     * @param $two_dimensional_array
     * @return array|\RecursiveIteratorIterator
     */
    public static function makeSingleDimensionArray($two_dimensional_array)
    {
        if (!is_array($two_dimensional_array)) {
            return [];
        }

        $it = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator($two_dimensional_array)
        );

        return iterator_to_array($it, false);
    }

    /**
     * Get list of media elements
     *
     * @param $packet_element_list
     * @param string $element_type
     * @return array
     */
    public static function getMediaElementIDs($packet_element_list, $element_type = 'media')
    {
        if (!is_array($packet_element_list)) {
            return [];
        }

        $media_element_ids = function ($value) use ($element_type) {

            $media_ids = [];

            if (!is_array($value)) {
                return $media_ids;
            }

            foreach ($value as $key => $item) {
                if ($item['type'] === $element_type) {
                    $media_ids[] = $item['id'];
                }
            }

            return $media_ids;
        };

        return array_map($media_element_ids, $packet_element_list);
    }

    /**
     * Get list of Feed Slug By ID
     *
     * @param array $feed_ids
     * @return array
     */
    public static function getFeedSlugByFeedID($feed_ids)
    {
        if (!is_array($feed_ids)) {
            return [];
        }

        $program_list = Program::whereIn('program_id', $feed_ids)
            ->get(['program_slug']);

        return array_pluck(
            $program_list->all(),
            'program_slug'
        );
    }

    /**
     * Get list packets by id
     *
     * @param $feed_slug_list
     * @return array
     */
    public static function getPacketElementList($feed_slug_list)
    {

        if (!is_array($feed_slug_list)) {
            return [];
        }

        $packet = Packet::whereIn('feed_slug', $feed_slug_list)
            ->get(['elements']);

        $packet_element_list = array_pluck(
            $packet->all(),
            'elements'
        );

        return $packet_element_list;
    }

    /**
     * Get media ids by feeds
     *
     * @param $feed_ids
     * @param string $element_type
     * @return array
     */
    public static function getMediaByFeedIDs($feed_ids, $element_type = 'media')
    {
        if (!is_array($feed_ids)) {
            return [];
        }

        $feed_slug_list = self::getFeedSlugByFeedID($feed_ids);

        $packet_element_list = self::getPacketElementList($feed_slug_list);

        $ids = self::getMediaElementIDs($packet_element_list, $element_type);

        return self::makeSingleDimensionArray($ids);
    }

    /**
     * @return array|mixed
     */
    public static function getUserFeedList()
    {
        $user = Auth::user()->toArray();

        $feed_ids = [];

        if (array_has($user, 'relations.user_feed_rel') &&
            !empty(array_get($user, 'relations.user_feed_rel'))
        ) {
            $feed_ids = array_get($user, 'relations.user_feed_rel');
        }

        if (array_has($user, 'relations.active_usergroup_user_rel') &&
            !empty(array_get($user, 'relations.active_usergroup_user_rel'))
        ) {
            $feeds_2d_ids = null;

            $user_groups = UserGroup::whereIn('relations.active_user_usergroup_rel', [Auth::user()->uid])
                ->get(['relations.usergroup_feed_rel']);

            foreach ($user_groups as $key => $each_group) {
                $feeds_2d_ids[] = array_get($each_group->toArray(), 'relations.usergroup_feed_rel');
            }

            $feed_id = self::makeSingleDimensionArray($feeds_2d_ids);

            $feed_ids = array_merge($feed_ids, $feed_id);
        }

        return $feed_ids;
    }

    /**
     * @return array
     */
    public static function getUserListByFeed()
    {
        $program_ids = self::getUserFeedList();

        if (is_array($program_ids) && !empty($program_ids)) {
            $programs = Program::whereIn('program_id', $program_ids)->get();

            $user_ids = [];

            foreach ($programs as $key => $value) {
                $program = $value->toArray();

                if (isset($program['relations']['active_user_feed_rel'])) {
                    $user_ids[] = $program['relations']['active_user_feed_rel'];
                }
            }

            $user_id_list = self::makeSingleDimensionArray($user_ids);

            return array_unique($user_id_list);
        }
    }

    /**
     * @return array
     */
    public static function getUserGroupListByFeed()
    {
        $program_ids = self::getUserFeedList();

        if (is_array($program_ids) && !empty($program_ids)) {
            $user_groups = UserGroup::whereIn('relations.usergroup_feed_rel', $program_ids)->get();

            $user_group_ids = [];

            foreach ($user_groups as $key => $value) {
                $user_group_ids[] = $value->ugid;
            }

            $user_id_list = self::makeSingleDimensionArray($user_group_ids);

            return array_unique($user_id_list);
        }
    }

    /**
     * @return array
     */
    public static function getAnnouncementUser()
    {

        $user_announcement = self::getUserAnnouncement();

        $program_announcement = self::getProgramUserAnnouncement();

        $usergroup_announcement = self::getUserGroupAnnouncement();

        $access_announcement = array_merge(
            $user_announcement,
            $program_announcement,
            $usergroup_announcement
        );


        return array_unique($access_announcement);
    }

    /**
     * [getUserAnnouncement list announcement id for user]
     * @method getUserAnnouncement
     * @return array
     */
    public static function getUserAnnouncement()
    {

        $user = Auth::user()->toArray();

        $relations = $user['relations'];

        if (isset($relations) && is_array($relations) &&
            isset($relations['user_announcement_rel'])
        ) {
            return $relations['user_announcement_rel'];
        } else {
            return [];
        }
    }

    /**
     * @return array
     */
    public static function getProgramUserAnnouncement()
    {
        $feed_list = self::getUserFeedList();

        $announcement_list = [];

        $int_val = function ($value) {
            return (int)$value;
        };

        if (isset($feed_list) && is_array($feed_list) && !empty($feed_list)) {
            foreach ($feed_list as $value) {
                $each_program_announcement = self::getProgramAnnouncement($value);

                if (!empty($each_program_announcement) &&
                    is_array($each_program_announcement)
                ) {
                    $each_program_announcement = array_map(
                        $int_val,
                        $each_program_announcement
                    );

                    $announcement_list[] = $each_program_announcement;
                }
            }

            $announcement_list = self::makeSingleDimensionArray($announcement_list);

            return array_unique($announcement_list);
        } else {
            return [];
        }
    }

    /**
     * @param $program_id
     * @return null
     */
    public static function getProgramAnnouncement($program_id)
    {
        $program_details = Program::where('program_id', '=', (int)$program_id)
            ->get();
        foreach ($program_details as $value) {
            $each_program = $value->toArray();

            if (isset($each_program['relations']) &&
                isset($each_program['relations']['contentfeed_announcement_rel'])
            ) {
                if (!empty($each_program['relations']['contentfeed_announcement_rel'])) {
                    return $each_program['relations']['contentfeed_announcement_rel'];
                }
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public static function getUserGroupAnnouncement()
    {

        $user = Auth::user()->toArray();

        if (isset($user['relations']['active_usergroup_user_rel'])) {
            $user_group_id_list = $user['relations']['active_usergroup_user_rel'];

            $announcement_list = [];

            if (!empty($user_group_id_list) && is_array($user_group_id_list)) {
                foreach ($user_group_id_list as $value) {
                    $user_group = UserGroup::where('ugid', '=', (int)$value)
                        ->first();

                    $user_group = $user_group->toArray();

                    if (isset($user_group['relations']['usergroup_announcement_rel'])) {
                        $announcement_ids = $user_group['relations']['usergroup_announcement_rel'];

                        if (!empty($announcement_ids) && is_array($announcement_ids)) {
                            $announcement_list[] = $announcement_ids;
                        }
                    }
                }

                $announcement_list = self::makeSingleDimensionArray($announcement_list);

                return array_unique($announcement_list);
            } else {
                return [];
            }
        } else {
            return [];
        }
    }

    /**
     * @param $user
     * @param $type
     * @return mixed
     */
    public static function getDirectAssignedMedia($user, $type)
    {

        switch ($type) {
            case 'assessment':
                if (array_has($user, 'relations.user_quiz_rel') &&
                    !empty(array_get($user, 'relations.user_quiz_rel'))
                ) {
                    return $user['relations']['user_quiz_rel'];
                }

                break;

            case 'event':
                if (array_has($user, 'relations.user_event_rel') &&
                    !empty(array_get($user, 'relations.user_event_rel'))
                ) {
                    return $user['relations']['user_event_rel'];
                }

                break;

            default:
                # code...
                break;
        }
    }

    /**
     * @param int $utc_time
     * @return string
     */
    public static function getPublishOnDisplay($utc_time = 0)
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

}
