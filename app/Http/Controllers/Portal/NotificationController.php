<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\PortalBaseController;
use App\Model\Common;
use App\Model\Notification;
use App\Model\SiteSetting;
use Auth;
use Config;
use Input;
use Timezone;
use URL;

class NotificationController extends PortalBaseController
{
    public function __construct()
    {
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
    }

    public function getIndex()
    {
        return parent::getError($this->theme, $this->theme_path, 401);
        /*$this->layout->theme = 'portal/theme/'.$this->theme;
        // $this->layout->header = view($this->theme_path.'.common.header');
        // $this->layout->leftsidebar = view($this->theme_path.'.common.leftsidebar');
        // $this->layout->footer = view($this->theme_path.'.common.footer');
        $notifi_delay=Config::get('app.notification_delay_read');
        $this->layout->content = view($this->theme_path.'.common.notification')->with('notifi_delay',$notifi_delay);
*/
    }

    public function getViewNotification($notification_id = null)
    {
        $user_id = Auth::user()->uid;
        if (!is_null($notification_id)) {
            Notification::updateReadNotification((int)$notification_id);

            return redirect('/notification');
        }
    }

    public function getNotificationListAjax()
    {
        if (!Auth::check()) {
            return;
        }
        $start = 0;
        $limit = 10;
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '0') {
                $orderByArray = ['from_module' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '2') {
                $orderByArray = ['is_read' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '3') {
                $orderByArray = ['created_at' => $order_by[0]['dir']];
            }
        }
        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }

        $filter = Input::get('filter');
        if (!in_array($filter, [true, false])) {
            $filter = false;
        }
        $user_id = Auth::user()->uid;
        // return $user_id;

        $filteredRecords = Notification::getUserNotification($user_id);
        $totalRecords = Notification::getUserNotification($user_id);
        $filtereddata = Notification::getNotificationwithPagenation($user_id, $start, $limit, $orderByArray);
        $notifi_list = Notification::getNotificationlatest($user_id, $start, $limit);
        if (!empty($notifi_list)) {
            foreach ($notifi_list as $i => $nid) {
                $notification_ids[$i] = $nid['notification_id'];
            }
            if (!empty($notification_ids)) {
                $notification_lists = implode(',', $notification_ids);
            } else {
                $notification_lists = '';
            }
        } else {
            $notification_lists = '';
        }

        $dataArr = [];
        $make_one_list = 1;
        foreach ($filtereddata as $key => $value) {
            //  $checkbox='<input type="checkbox" value="'.$value['notification_id'].'">';
            if ($make_one_list == 1 && !empty($notification_lists)) {
                $notifi_list = '<input class="notification_ids" type="hidden" name="notification" value="' . $notification_lists . '">';
            } else {
                $notifi_list = '';
            }
            ++$make_one_list;
            $temparr = [
                // $checkbox,
                $value['from_module'] . $notifi_list,
                $value['message'],
                $value['is_read'],
                Timezone::convertFromUTC('@' . $value['created_at'], Auth::user()->timezone, config('app.date_format')),
                // '<a class="btn btn-circle show-tooltip " title="View Details" href="'.URL::to('/notification/view-notification/'.$value['notification_id']).'" ><i class="fa fa-eye"></i></a>'.,

            ];
            $dataArr[] = $temparr;
        }
        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr,
        ];

        return response()->json($finaldata);
    }

    public function getMarkRead($id = null)
    {
        if (is_null($id)) {
            if (!empty(Input::get('notification_ids')) && Auth::check()) {
                $notification_ids = explode(',', Input::get('notification_ids'));
                foreach ($notification_ids as $key => $value) {
                    Notification::updateReadNotification((int)$value);
                }
                $list_noti_count = Notification::getNotReadNotificationCount(Auth::user()->uid);
                $finaldata = [
                    'list_noti_count' => $list_noti_count,
                ];

                return response()->json($finaldata);
            } else {
                return 'failed ';
            }
        } else {
            Notification::updateReadNotification((int)$id);

            return 'sucessfull update';
        }
    }

    public function getNotificationList()
    {
        $user_id = Auth::user()->uid;
        $notify_date = [];
        $limit = (int)SiteSetting::module('Notifications and Announcements', 'displayed_in_popup');
        $notifi_list = Notification::getNotificationlatest($user_id, 0, $limit);
        if (!is_null($notifi_list) && !empty($notifi_list)) {
            foreach ($notifi_list as $i => $aid) {
                // $date = new DateTime($aid['created_at']);
                $temp = (int)Timezone::convertToUTC($aid['created_at'], Auth::user()->timezone, 'U');
                $notification_ids[$i] = $aid['notification_id'];
                $messages[$i] = $aid['message'];
                $notify_date[$i] = Common::getPublishOnDisplay($temp);
            }
            $finaldata = [
                'notification_ids' => $notification_ids,
                'messages' => $messages,
                'notify_date' => $notify_date,
                'flag' => 'success',
            ];
        } else {
            $finaldata = [
                'flag' => 'failed',
            ];
        }

        return response()->json($finaldata);
    }

    public function getNotificationRecords()
    {
        if (!is_null(Input::get('page_no'))) {
            $pageno = Input::get('page_no');
            $limit = 10;
            $start_id = ($pageno * $limit) - $limit;
            $user_id = 0;
            $notificationlist = 'No More Notifications';
            $notifi_notread_ids = [];
            $notifi_notread_list = '';
            $list_noti_count = 0;
            $map_img_url = [
                'assessment' => Config::get('app.notifications.images.assessment'),
                'packetfaq' => Config::get('app.notifications.images.assessment'),
                'event' => Config::get('app.notifications.images.event'),
                strtolower(trans('program.program')) => Config::get('app.notifications.images.program'),
                strtolower(trans('program.programs')) => Config::get('app.notifications.images.program'),
                strtolower(trans('program.packet')) => Config::get('app.notifications.images.packet'),
                'more ' . strtolower(trans('program.programs')) => Config::get('app.notifications.images.program'),
                'more channels' => Config::get('app.notifications.images.program'),
                'more feeds' => Config::get('app.notifications.images.program'),
                'dams' => Config::get('app.notifications.images.dams'),
                'packet' => Config::get('app.notifications.images.packet'),
                'contentfeed' => Config::get('app.notifications.images.program'),
            ];
            if (Auth::check()) {
                $user_id = Auth::user()->uid;

                $notifications = Notification::getNotificationwithPagenation($user_id, $start_id, $limit);
                $list_noti_count = Notification::getNotReadNotificationCount($user_id);
                $new_notifi = '';
                $i = 0;

                if (empty($notifications)) {
                    $finaldata = [
                        'notificationlist' => $notificationlist,
                        'notifi_notread_list' => $notifi_notread_list,
                        'list_noti_count' => $list_noti_count,
                    ];

                    return response()->json($finaldata);
                }
                $notificationlist = '';
                foreach ($notifications as $key => $value) {
                    $temp = (int)Timezone::convertToUTC($value['created_at'], Auth::user()->timezone, 'U');
                    // $path =URL::to('notification/mark-read?notification_ids='.$value["notification_id"].);
                    $new_notifi = '';
                    $for_need = '';
                    if (!$value['is_read']) {
                        $new_notifi = '<span class="make_click_read pull-right" data-key="' . $value['notification_id'] . '" id="newspan' . $value['notification_id'] . '" style="color:green">New</span>';
                        $notifi_notread_ids[$i] = $value['notification_id'];
                        ++$i;
                    }
                    $var_img = array_key_exists(strtolower($value['from_module']), $map_img_url) ? $map_img_url[strtolower($value['from_module'])] : Config::get('app.notifications.images.default');
                    $notificationlist .= '<li>
                                        <div class="img-div"><img src="' . URL::to($var_img) . '" alt="Notifications"></div>
                                        <div class="data-div">
                                        <span class="txt">' . $value['message'] . '</span>
                                    <br><span class="d-gray font-10">' . Common::getPublishOnDisplay($temp) . '</span>' . $new_notifi . '</div>
                                    </li>';
                }
                $notifi_notread_list = implode(',', $notifi_notread_ids);
            }
            $finaldata = [
                'notificationlist' => $notificationlist,
                'notifi_notread_list' => $notifi_notread_list,
                'list_noti_count' => $list_noti_count,
            ];

            return response()->json($finaldata);
            // return $notificationlist;
        } else {
            return '<p>someware Worng</p>';
        }
    }

    public function getListNotifications()
    {
        /*$crumbs = array(
                    'Home'=>'/',
                    'List Announcement and notification' => ''
                );*/
        if (Auth::check()) {
            $user_id = Auth::user()->uid;
            // $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
            $this->layout->footer = view($this->theme_path . '.common.footer');
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->header = view($this->theme_path . '.common.header');
            $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
            $this->layout->content = view($this->theme_path . '.announcement.listannouncement')->with('user_id', $user_id)
                ->with('annonceoranotifi', 'notification')
                ->with('path', 'dashboard_or_notification');
            $this->layout->footer = view($this->theme_path . '.common.footer');
        }
    }
}
