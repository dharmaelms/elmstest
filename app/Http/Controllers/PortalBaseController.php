<?php

namespace App\Http\Controllers;

use App\Enums\RolesAndPermissions\PermissionType;
use App\Model\Announcement;
use App\Model\Category;
use App\Model\Common;
use App\Model\MyActivity;
use App\Model\Notification;
use App\Model\Program;
use App\Model\SiteSetting;
use App\Model\StaticPage;
use App\Model\UserGroup;
use Auth;
use Config;
use Request;
use Session;
use View;
use Timezone;

class PortalBaseController extends Controller
{
    /**
     * @var \Illuminate\View\View|\Illuminate\Contracts\View\Factory|string
     */
    public $layout;

    public $theme;

    public $theme_path;

    /**
     * PortalBaseController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function setupLayout()
    {
        if (Request::input("setup_layout") !== "FALSE") {
            if (!is_null($this->layout)) {
                $this->layout = view($this->layout);
            }

            $theme = config('app.portal_theme_name');
            $theme_path = 'portal.theme.' . $theme;
            View::share('theme_path', $theme_path);
            View::share('pagetitle', config('app.site_name'));
            View::share('theme', 'portal/theme/' . $theme);
            $is_loggedin = Auth::check() ? "" : "is_loggedin";
            View::share('is_loggedin', $is_loggedin);
            if (Auth::check()) {
                $moodle_userid = Auth::user()->uid;
            } else {
                $moodle_userid = 0;
            }
            View::share('moodle_userid', $moodle_userid);

            /*To get only the category relations to the Auth user*/
            $categories = [];
            $parentCat = [];
            if (Auth::check() && config('app.show_complete_functionalities')) {
                $user_relations = Auth::user()->relations;
                if (is_array($user_relations) && array_key_exists("user_feed_rel", $user_relations) && !empty($user_relations['user_feed_rel'])) {
                    $program_type = 'content_feed';
                    $program_sub_type = 'single';
                    $userChannel = Program::getProgramCategoryRelation($user_relations['user_feed_rel'], $program_type, $program_sub_type);
                    $program_categories = array_unique(array_flatten($userChannel));
                    $categories = Category::getCategorybyID($program_categories);
                    if (is_array($categories) && !empty($categories)) {
                        foreach ($categories as $catkey => $value) {
                            if (array_key_exists('children', $categories[$catkey])) {
                                unset($categories[$catkey]['children']);
                            }
                            /* below if condition is to take the parent id's of the child cat*/
                            if ($value['parents'] != null) {
                                $parent_id = null;
                                $parent_id = $value['parents'];
                                $foundKey = $this->multiArraySearch($parent_id, 'category_id', $categories);
                                if (!is_null($foundKey)) {
                                    $categories[$foundKey]['children'][] = $value;
                                } else {
                                    $parentCat = Category::getFeedsRelation($parent_id);
                                    foreach ($parentCat as $Pkey => $val) {
                                        if (array_key_exists('children', $parentCat[$Pkey])) {
                                            unset($parentCat[$Pkey]['children']);
                                        }
                                        $parentCat[$Pkey]['children'][] = $value;
                                    }
                                }
                            }
                        }
                    }
                    $categories = array_merge($categories, $parentCat);
                }
            }
            
            View::share('categories', $categories);
            $url_str = Request::url();
            $url_ary = explode('/', $url_str);

            //pages functionality
            if (!in_array('cp', $url_ary)) {
                if (Auth::check() && config('app.show_complete_functionalities')) {
                    $start_notifi = 0;
                    $limit = (int)SiteSetting::module('Notifications and Announcements', 'displayed_in_popup');
                    $announce_titles = [];
                    $announce_id = [];
                    $noti_count = 0;
                    $user_id = Auth::user()->uid;
                    $noti_count += Notification::getNotReadNotificationCount($user_id);
                    $announce_list_id = [];
                    $user_id = Auth::user()->uid;
                    $announce_date_header = [];
                    $private_announcement = [];
                        if (isset(Auth::user()->relations)) {
                            $relations = Auth::user()->relations;
                            foreach ($relations as $key => $value) {
                                if ($key == 'active_usergroup_user_rel' && !empty($value)) {
                                    $agl = UserGroup::getAnnouncementList($value);
                                    foreach ($agl as $value3) {
                                        if (isset($value3['relations']['usergroup_announcement_rel'])) {
                                            $private_announcement =  Announcement::filterPrivateAnnouncements($value3['relations']['usergroup_announcement_rel']);
                                        }

                                        if (isset($private_announcement)) {
                                            foreach ($private_announcement as $value4) {
                                                $announce_list_id[] = $value4;
                                            }
                                        }
                                    }
                                }
                                if ($key == 'user_feed_rel' && !empty($value)) {
                                    $acfl = Program::getAnnouncementList($value);
                                    foreach ($acfl as $value6) {
                                        if (isset($value6['relations']['contentfeed_announcement_rel'])) {
                                            $private_announcement =  Announcement::filterPrivateAnnouncements($value6['relations']['contentfeed_announcement_rel']);
                                        }

                                        if (isset($private_announcement)) {
                                            foreach ($private_announcement as $value7) {
                                                $announce_list_id[] = $value7;
                                            }
                                        }
                                    }
                                }
                                if ($key == 'user_announcement_rel' && !empty($value)) {
                                    foreach ($value as $key5 => $value5) {
                                        $announce_list_id[] = $value5;
                                    }
                                }
                            }
                        }
                        $announce_list_id = array_unique($announce_list_id);
                        $announcements = Announcement::getNotReadAnnouncementForHead($user_id, $announce_list_id, 0, $limit);
                        if (!is_null($announcements) && !empty($announcements)) {
                            $noti_count += Announcement::getNotReadAnnouncementCount($user_id, $announce_list_id);
                            foreach ($announcements as $i => $value1) {
                                $announce_titles[$i] = $value1['announcement_title'];
                                $announce_id[$i] = $value1['announcement_id'];
                                $announce_date_header[$i] = Timezone::getTimeStamp($value1['schedule']);
                            }
                        }
                        View::share('specific_user_announce_titles', $announce_titles);
                        View::share('specific_user_announce_id', $announce_id);
                        View::share('announce_date_header', $announce_date_header);
                    $user_id = Auth::user()->uid;
                    $notifi_list = Notification::getNotificationlatest($user_id, $start_notifi, $limit);
                    View::share('noti_count', $noti_count);
                    $continuewrleft = MyActivity::getContinueWrULeft($user_id);
                    if (isset($continuewrleft) && !empty($continuewrleft) && $continuewrleft > 0) {
                        View::share('continuewrleft', $continuewrleft[0]['url']);
                    }
                }
                View::share('max_read_delay', Config::get('app.notification_delay_read'));
                $static_page_chk = SiteSetting::module('General', 'static_pages');
                if ($static_page_chk == 'on') {
                    $pages = StaticPage::getOnlyActivePage();
                    View::share('pages', $pages);
                }
                View::share('max_read_delay', Config::get('app.notification_delay_read'));
            }
        }
    }

    public function callAction($method, $parameters)
    {
        $this->setupLayout();
        $response = call_user_func_array([$this, $method], $parameters);
        if (is_null($response) && !is_null($this->layout)) {
            $response = $this->layout;
        }

        return $response;
    }

    public function getError($theme, $theme_path, $error = 404, $message = '', $callback = '')
    {
        $this->layout->theme = 'portal/theme/' . $theme;
        $this->layout->header = view($theme_path . '.common.header');
        $this->layout->footer = view($theme_path . '.common.footer');
        if ((int)$error == 401) {
            if (empty($message)) {
                $message = 'The requested page requires user authorization';
            }
            if (empty($callback)) {
                $callback = url('/');
            }
            $this->layout->content = view($theme_path . '.common.error')
                ->with('message', $message)
                ->with('callback', $callback);
        } else {
            $this->layout->content = view($theme_path . '.common.404');
        }
    }

    /**/
    public function multiArraySearch($searchVal, $searchKey, $arr)
    {
        $foundIndex = null;
        if (is_array($arr) && !empty($arr)) {
            foreach ($arr as $k => $v) {
                if ($searchVal === $arr[$k][$searchKey]) {
                    $foundIndex = $k;
                    break;
                }
            }
            return $foundIndex;
        }
    }
}
