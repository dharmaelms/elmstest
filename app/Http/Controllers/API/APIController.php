<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Libraries\PushNotifications\PushNotificationsApi;
use App\Model\Announcement;
use App\Model\API;
use App\Model\Banners;
use App\Model\Category;
use App\Model\Event;
use App\Model\FlashCard;
use App\Model\MyActivity;
use App\Model\Notification;
use App\Model\Packet;
use App\Model\PacketFaq;
use App\Model\PacketFaqAnswers;
use App\Model\Program;
use App\Model\Quiz;
use App\Model\QuizAttempt;
use App\Model\QuizAttemptData;
use App\Model\Role;
use App\Model\SiteSetting;
use App\Model\TransactionDetail;
use App\Model\User;
use App\Model\UserGroup;
use App\Services\Catalog\AccessControl\IAccessControlService;
use App\Services\Catalog\CatList\ICatalogService;
use App\Services\Catalog\Order\IOrderService;
use App\Services\Catalog\Pricing\IPricingService;
use App\Services\Catalog\Promocode\IPromoCodeService;
use Auth;
use Carbon\Carbon;
use Common;
use Config;
use Crypt;
use Input;
use Request;
use stdClass;
use Timezone;
use URL;

/**
 * Class APIController
 * @package App\Http\Controllers\API
 */
class APIController extends Controller
{
    private $access_token;
    private $user_data = null;
    private $user_role = null;
    protected $catSer;
    protected $promoServ;
    protected $acServ;
    protected $pricingSer;
    protected $pay_currency = "INR";

    protected $theme;

    protected $layout;

    protected $theme_path;

    /**
     * APIController constructor.
     * @param Request $request
     * @param ICatalogService $catService
     * @param IPricingService $priceService
     * @param IPromoCodeService $promoService
     * @param IOrderService $orderService
     * @param IAccessControlService $accessControlService
     */
    public function __construct(
        Request $request,
        ICatalogService $catService,
        IPricingService $priceService,
        IPromoCodeService $promoService,
        IOrderService $orderService,
        IAccessControlService $accessControlService
    )
    {
        $this->catSer = $catService;
        $this->pricingSer = $priceService;
        $this->promoServ = $promoService;
        $this->ordSer = $orderService;
        $this->acServ = $accessControlService;

        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;

        $input = $request::input();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request::merge($input);

        header('Access-Control-Allow-Origin:*');
        header('Content-Type:application/json');
        $arr_exception_uris = ['api/userlogin', 'api/userlogout', 'api/forgot-password', 'api/domain-validation', 'api/home', 'api/catalog', 'api/sample-post', 'api/site-announcements', 'api/sample-post-timeline', 'api/user-registration', 'api/social-login', 'api/contact-us'];

        if (in_array(Request::path(), $arr_exception_uris) == false) {
            $access_token = trim(Input::get('access_token'));

            if ($access_token) {
                $result = API::fetchUserInfo($access_token);
                if (empty($result)) {
                    $this->decodeHttpResponseCode(400);
                    echo json_encode(
                        [
                            'flag' => trans('mobile.expired_token'),
                            'message' => trans('mobile.session_expired'),
                        ]
                    );
                    exit;
                } else {
                    foreach ($result as $token) {
                        //print_r($token); die;
                        //$this->access_token = $token['access_token'];
                        $this->user_data = $result;
                        $this->user_role = $token['role'];
                    }
                }
            } else {
                $this->decodeHttpResponseCode(400);
                echo json_encode(
                    [
                        'flag' => trans('mobile.failure'),
                        'message' => trans('mobile.missing_credentials'),
                    ]
                );
                exit;
            }
        }
    }

    public function getDomainValidation()
    {
        echo json_encode(
            [
                'flag' => trans('mobile.success'),
                'message' => trans('mobile.valid_domain'),
            ]
        );
        exit;
    }

    public function postUserlogin()
    {
        $user_name = strtolower(trim(Input::get('username')));
        $password = trim(Input::get('password'));
        $userdeviceid = trim(Input::get('userdeviceid'));
        if ($user_name != '' && $password != '') {
            if (strpos($user_name, '@') !== false) {/*If username for the login is email */
                $user = [
                    'email' => $user_name,
                    'password' => $password,
                    'status' => 'ACTIVE',
                ];
            } else {                                    /* If username for the login is name */
                $user = [
                    'username' => $user_name,
                    'password' => $password,
                    'status' => 'ACTIVE',
                ];
            }

            if (Auth::attempt($user)) {
                $uid = Auth::user()->uid;
                $array = [
                    'module' => 'general',
                    'action' => 'login',
                    'module_name' => '',
                    'module_id' => '',
                    'user_device_id' => (int)$userdeviceid,
                    'url' => Request::path(),
                ];
                API::getLogActivity($array, $uid);  /* Logs user login activity to myactivity table */
                User::getUpdateLastLogin($uid);        /* Updates user last login in user table */
                $old_device_id = User::getDeviceId($uid);
                $push = new PushNotificationsApi;
                if (isset($old_device_id[0]['user_device_id']) && !empty($old_device_id[0]['user_device_id']) && $old_device_id[0]['user_device_id'] != $userdeviceid) {
                    $deviceinfo = $push->getDeviceToken($userdeviceid);
                    if (isset($deviceinfo['token'])) {
                        $push->registerDevice($userdeviceid, $deviceinfo['token'], $deviceinfo['type']);
                    }
                }

                $tokenarr = API::generateTokens();        /* Generates Access Token and Request Token for Authentication*/
                $token_issued_time = time();
                $insertdata = [
                    'user_id' => $uid,
                    'user_device_id' => $userdeviceid,
                    'refresh_token' => $tokenarr['refreshtoken'],
                    'access_token' => $tokenarr['accesstoken'],
                    'created_at' => $token_issued_time,
                ];
                $res = API::updateUser($insertdata);  /* Updates user table once user logins from App*/

                // $permission=API::CheckPermission(Auth::user()->role);
                $address = '';
                $default_address_id = Auth::user()->default_address_id;
                $address_list = Auth::user()->myaddress;
                if (isset($address_list) && !empty($address_list)) {
                    foreach ($address_list as $each) {
                        if ($each['address_id'] == $default_address_id) {
                            $address = [];
                            $temp['address_id'] = $each['address_id'];
                            $temp['fullname'] = $each['fullname'];
                            $temp['street'] = $each['street'];
                            $temp['landmark'] = $each['landmark'];
                            $temp['city'] = $each['city'];
                            $temp['country'] = 'India';
                            $temp['state'] = $each['state'];
                            $temp['pincode'] = $each['pincode'];
                            $temp['phone'] = $each['phone'];
                            $address = $temp;
                        }
                    }
                }
                // Taking logo updated by admin.
                $mobile_logo = SiteSetting::module('Contact Us', 'mobile_logo');
                $logo = '';
                if ($mobile_logo) {
                    $logo = URL::to('/') . '/' . config('app.site_logo_path') . $mobile_logo;
                }

                /* API response */
                if ($res) {
                    echo json_encode(
                        [
                            'flag' => trans('mobile.success'),
                            'message' => trans('mobile.login_success'),
                            'refresh_token' => $tokenarr['refreshtoken'],
                            'access_token' => $tokenarr['accesstoken'],
                            'issued_time' => $token_issued_time,
                            'user_id' => Auth::user()->uid,
                            'user_role' => Auth::user()->role,
                            'user_name' => Auth::user()->username,
                            'user_fullname' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                            'user_email' => Auth::user()->email,
                            'username_first_letter' => ucwords(Auth::user()->firstname[0]),
                            'user_default_address' => $address,
                            'logo' => $logo
                        ]
                    );
                    exit;
                }
            } else {
                Input::flush();
                $this->decodeHttpResponseCode(400);
                echo json_encode(
                    [
                        'flag' => trans('mobile.failure'),
                        'message' => trans('mobile.wrong_credentials'),
                    ]
                );
                exit;
            }
        } else {
            /**
             * Socialite Begins
             */
            $inputdata = Input::all();
            $this->registerAndLoginSocialite($inputdata);
            /**
             * Socialite Ends
             */

            $this->decodeHttpResponseCode(400);
            echo json_encode(
                [
                    'flag' => trans('mobile.failure'),
                    'message' => trans('mobile.missing_credentials'),
                ]
            );
            exit;
        }
    }

    public function postSociliateLogin()
    {
        /**
         * Socialite Begins
         */
        $inputdata = Input::all();
        $this->registerAndLoginSocialite($inputdata);
        /**
         * Socialite Ends
         */
    }

    /**
     * [doUserLoginResponse Logging and Generating Response to Mobile]
     * @param  [type] $inputdata [description]
     * @return [type]            [description]
     */
    private function doUserLoginResponse($inputdata)
    {
        if (isset($inputdata['userdeviceid'])) {
            $userdeviceid = $inputdata['userdeviceid'];
        } else {
            $userdeviceid = 0;
        }
        $uid = Auth::user()->uid;
        $array = [
            'module' => 'general',
            'action' => 'login',
            'module_name' => '',
            'module_id' => '',
            'user_device_id' => (int)$userdeviceid,
            'url' => Request::path(),
        ];

        API::getLogActivity($array, $uid);  /* Logs user login activity to myactivity table */
        User::getUpdateLastLogin($uid);        /* Updates user last login in user table */

        $tokenarr = API::generateTokens();        /* Generates Access Token and Request Token for Authentication*/
        $token_issued_time = time();
        $insertdata = [
            'user_id' => $uid,
            'user_device_id' => $userdeviceid,
            'refresh_token' => $tokenarr['refreshtoken'],
            'access_token' => $tokenarr['accesstoken'],
            'created_at' => $token_issued_time,
        ];
        $res = API::updateUser($insertdata);  /* Updates user table once user logins from App*/
        $address = '';
        $default_address_id = Auth::user()->default_address_id;
        $address_list = Auth::user()->myaddress;
        if (isset($address_list) && !empty($address_list)) {
            foreach ($address_list as $each) {
                if ($each['address_id'] == $default_address_id) {
                    $address = [];
                    $temp['address_id'] = $each['address_id'];
                    $temp['fullname'] = $each['fullname'];
                    $temp['street'] = $each['street'];
                    $temp['landmark'] = $each['landmark'];
                    $temp['city'] = $each['city'];
                    $temp['country'] = 'India';
                    $temp['state'] = $each['state'];
                    $temp['pincode'] = $each['pincode'];
                    $temp['phone'] = $each['phone'];
                    $address = $temp;
                }
            }
        }
        if ($res) {
            echo json_encode(
                [
                    'flag' => trans('mobile.success'),
                    'message' => trans('mobile.login_success'),
                    'refresh_token' => $tokenarr['refreshtoken'],
                    'access_token' => $tokenarr['accesstoken'],
                    'issued_time' => $token_issued_time,
                    'user_id' => Auth::user()->uid,
                    'user_role' => Auth::user()->role,
                    'user_name' => Auth::user()->username,
                    'user_fullname' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                    'user_email' => Auth::user()->email,
                    'username_first_letter' => ucwords(Auth::user()->firstname[0]),
                    'user_default_address' => $address,
                ]
            );
            exit;
        }
    }

    /**
     * [registerAndLoginSocialite Socilaite Registration and Login]
     * @param  [type] $inputdata [description]
     * @return [type]            [description]
     */
    private function registerAndLoginSocialite($inputdata)
    {
        $mobile_app = SiteSetting::module('Socialite', 'mobile_app');
        if ($mobile_app === 'on') {
            if (isset($inputdata) && !empty($inputdata)) {
                if (isset($inputdata['socialite']) && !empty($inputdata['socialite'])) {
                    if (!empty($inputdata['email']) &&
                        !empty($inputdata['firstname']) &&
                        !empty($inputdata['lastname'])
                    ) {
                        $reg_data = [
                            'email' => $inputdata['email'],
                            'firstname' => $inputdata['firstname'],
                            'lastname' => $inputdata['lastname'],
                            'provider' => $inputdata['socialite'],
                            'app_registration' => 1
                        ];
                        User::registerSocialite($reg_data);
                        $authentication = [
                            'email' => $inputdata['email']
                        ];
                        Auth::login(User::firstOrCreate($authentication));
                        $this->doUserLoginResponse($inputdata);
                    }
                    return;
                } else {
                    return;
                }
            }
            return;
        } else {
            echo json_encode(
                [
                    'flag' => trans('mobile.failure'),
                    'message' => trans('mobile.sociliate_active')
                ]
            );
        }
    }

    public function postDashboard()
    {
        $announce_list_id = [];
        $gids = [];
        $response['user'] = [];
        $noti_count = 0;
        $favorites = [];
        // print_r($this->user_data); die;
        /* Getting User data */
        foreach ($this->user_data as $udata) {
            $user_id = $udata['uid'];
            $relations = $udata['relations'];
            if (isset($udata['favourited_packets'])) {
                $favorites = $udata['favourited_packets'];
            }
            $user = '';
            $user = $udata['firstname'];
            if ($udata['lastname'] != '') {
                $user .= $user . ' ' . $udata['lastname'];
            }
            $res = new stdClass();
            $res->user_id = $udata['uid'];
            $res->email = $udata['email'];
            $res->mobile = $udata['mobile'];
            $res->user_name = $udata['username'];
            $time_zone = $udata['timezone'];
            $res->user = $user;
            $res->role = $udata['role'];
            $response['user'][] = $res;
        }

        if (isset($relations)) {
            foreach ($relations as $key => $value) {
                if ($key == 'active_usergroup_user_rel') {
                    $agl = UserGroup::getAnnouncementList($value);
                    foreach ($agl as $key3 => $value3) {
                        if (isset($value3['relations']['usergroup_announcement_rel'])) {
                            foreach ($value3['relations']['usergroup_announcement_rel'] as $key4 => $value4) {
                                $announce_list_id[] = $value4;
                            }
                        }
                    }
                }
                if ($key == 'user_feed_rel') {
                    $acfl = Program::getAnnouncementList($value);
                    foreach ($acfl as $key6 => $value6) {
                        if (isset($value6['relations']['contentfeed_announcement_rel'])) {
                            foreach ($value6['relations']['contentfeed_announcement_rel'] as $key7 => $value7) {
                                $announce_list_id[] = $value7;
                            }
                        }
                    }
                }
                if ($key == 'user_announcement_rel') {
                    if (!empty($value)) {
                        foreach ($value as $key5 => $value5) {
                            $announce_list_id[] = $value5;
                        }
                    }
                }
            }
        }
        $noti_count += Notification::getNotReadNotificationCount($user_id);
        $announce_list_id = array_unique($announce_list_id);
        $announcement_dashboard_number = 2;
        $announcement_dashboard_number = Config::get('app.mobile.announcement_dashboard_number');
        $announcements = Announcement::getNotReadAnnouncementForHead($user_id, $announce_list_id, 0, $announcement_dashboard_number);
        if (!is_null($announcements) && !empty($announcements)) {
            $noti_count += Announcement::getNotReadAnnouncementCount($user_id, $announce_list_id);
        }
        $response['announcements'] = [];

        if (count($announcements) < $announcement_dashboard_number && count($announce_list_id)) {
            $buff_announcement = API::getReadAnnouncement($user_id, $announce_list_id, 0, $announcement_dashboard_number - count($announcements));
        }

        if (!empty($announcements)) {
            foreach ($announcements as $each) {
                $for_media = [];
                $for_media['forret'] = '';
                $for_media['type'] = '';
                $for_media['file'] = '';
                $for_media['video_cover_image'] = '';
                if (isset($each['relations']['active_media_announcement_rel']) && !empty($each['relations']['active_media_announcement_rel'])) {
                    $for_media = API::getMediaDetails($each['relations']['active_media_announcement_rel'][0]);
                }
                $created_by_name = '';
                if (isset($each['created_by_name'])) {
                    $created_by_name = $each['created_by_name'];
                }

                $announcement_content = preg_replace_callback("/(<img[^>]*src *= *[\"']?)([^\"']*)/i", function ($matches) {
                    global $rootURL;

                    return $matches[1] . $rootURL . URL::to('/') . $matches['2'];
                }, $each['announcement_content']);

                $find = ['href='];
                $replace = ['link='];

                $announcement_content = str_replace($find, $replace, $announcement_content);

                $res = new stdClass();
                $res->announcement_id = $each['announcement_id'];
                $res->announcement_title = html_entity_decode($each['announcement_title']);
                $res->announcement_type = $each['announcement_type'];
                $res->announcement_media = $for_media['forret'];
                $res->media_type = $for_media['type'];
                $res->file = $for_media['file'];
                $res->video_cover_image = $for_media['video_cover_image'];
                $res->announcement_publish_date = $each['schedule'];
                $res->announcement_content = html_entity_decode($announcement_content);
                $res->announcement_device = 'both';
                $res->created_by = $each['created_by'];
                $res->new_label = 1;
                $res->created_by_name = $created_by_name;
                $response['announcements'][] = $res;
            }
            if (!empty($buff_announcement)) {
                foreach ($buff_announcement as $each) {
                    $for_media = [];
                    $for_media['forret'] = '';
                    $for_media['type'] = '';
                    $for_media['file'] = '';
                    $for_media['video_cover_image'] = '';
                    if (isset($each['relations']['active_media_announcement_rel']) && !empty($each['relations']['active_media_announcement_rel'])) {
                        $for_media = API::getMediaDetails($each['relations']['active_media_announcement_rel'][0]);
                    }
                    $created_by_name = '';
                    if (isset($each['created_by_name'])) {
                        $created_by_name = $each['created_by_name'];
                    }

                    $announcement_content = preg_replace_callback("/(<img[^>]*src *= *[\"']?)([^\"']*)/i", function ($matches) {
                        global $rootURL;

                        return $matches[1] . $rootURL . URL::to('/') . $matches['2'];
                    }, $each['announcement_content']);

                    $find = ['href='];
                    $replace = ['link='];

                    $announcement_content = str_replace($find, $replace, $announcement_content);
                    // if(isset($for_media['forret']) && $for_media['forret']!='')
                    // {
                    //  $for_media=$for_media['forret'];
                    // }

                    $res = new stdClass();
                    $res->announcement_id = $each['announcement_id'];
                    $res->announcement_title = html_entity_decode($each['announcement_title']);
                    $res->announcement_type = $each['announcement_type'];
                    $res->announcement_media = $for_media['forret'];
                    $res->video_cover_image = $for_media['video_cover_image'];
                    $res->media_type = $for_media['type'];
                    $res->file = $for_media['file'];
                    $res->announcement_publish_date = $each['schedule'];
                    $res->announcement_content = html_entity_decode($announcement_content);
                    $res->announcement_device = 'both';
                    $res->created_by = $each['created_by'];
                    $res->new_label = 0;
                    $res->created_by_name = $created_by_name;
                    $response['announcements'][] = $res;
                }
            }
        } elseif (!empty($buff_announcement)) {
            foreach ($buff_announcement as $each) {
                $for_media = [];
                $for_media['forret'] = '';
                $for_media['type'] = '';
                $for_media['file'] = '';
                $for_media['video_cover_image'] = '';
                if (isset($each['relations']['active_media_announcement_rel']) && !empty($each['relations']['active_media_announcement_rel'])) {
                    $for_media = API::getMediaDetails($each['relations']['active_media_announcement_rel'][0]);
                }

                $created_by_name = '';
                if (isset($each['created_by_name'])) {
                    $created_by_name = $each['created_by_name'];
                }
                $announcement_content = preg_replace_callback("/(<img[^>]*src *= *[\"']?)([^\"']*)/i", function ($matches) {
                    global $rootURL;

                    return $matches[1] . $rootURL . URL::to('/') . $matches['2'];
                }, $each['announcement_content']);

                $find = ['href='];
                $replace = ['link='];

                $announcement_content = str_replace($find, $replace, $announcement_content);
                // if(isset($for_media['forret']) && $for_media['forret']!='')
                // {
                //  $for_media=$for_media['forret'];
                // }

                $res = new stdClass();
                $res->announcement_id = $each['announcement_id'];
                $res->announcement_title = html_entity_decode($each['announcement_title']);
                $res->announcement_type = $each['announcement_type'];
                $res->announcement_media = $for_media['forret'];
                $res->video_cover_image = $for_media['video_cover_image'];
                $res->media_type = $for_media['type'];
                $res->file = $for_media['file'];
                $res->announcement_publish_date = $each['schedule'];
                $res->announcement_content = html_entity_decode($announcement_content);
                $res->announcement_device = 'both';
                $res->created_by = $each['created_by'];
                $res->new_label = 0;
                $res->created_by_name = $created_by_name;
                $response['announcements'][] = $res;
            }
        }

        $program_slugs = TransactionDetail::getProgramIds($user_id, null, 'all');

        $posts_dashboard_number = 10;
        $posts_dashboard_number = Config::get('app.mobile.posts_dashboard_number');

        // $packets=API::getPacketsUsingSlugs($program_slugs,$posts_dashboard_number=10,$skip=0);
        $packets = Packet::getPacketElementsUsingSlug($program_slugs);
        $response['packets'] = [];

        if (isset($packets)) {
            if (empty($favorites)) {
                $favorites = [];
            }

            $packets = Packet::getPacketsUsingIdsSortBy(
                array_column($packets,'packet_id'),
                $sort_by = 'new_to_old',
                $posts_dashboard_number,
                $page_no = 0
            );

            foreach ($packets as $each) {
                $favorite = '';
                $elements_count = '';
                if (isset($each['elements'])) {
                    $elements_count = count($each['elements']);
                }

                if (in_array($each['packet_id'], $favorites)) {
                    $favorite = 1;
                }
                $packet_cover_media = '';
                if ($each['packet_cover_media'] != '') {
                    $packet_cover_media = URL::to('media_image/' . $each['packet_cover_media'] . '?thumb=180x180');
                }
                /* get Channel name */
                $channel = Program::pluckFeedName($each['feed_slug']);
                $channel = $channel[0];
                $res = new stdClass();
                $res->packet_id = $each['packet_id'];
                $res->packet_title = html_entity_decode($each['packet_title']);
                $res->packet_slug = $each['packet_slug'];
                $res->feed_slug = $each['feed_slug'];
                $res->packet_description = html_entity_decode($each['packet_description']);
                $res->created_by_name = $each['created_by_name'];
                $res->packet_cover_media = $packet_cover_media;
                $res->packet_publish_date = (($each['packet_publish_date'] instanceof Carbon) ? $each['packet_publish_date']->timestamp : strtotime($each['packet_publish_date']));
                $res->channel = $channel['program_title'];
                $res->elements_count = $elements_count;
                $res->new_label = '';
                $res->favorite = $favorite;
                $response['packets'][] = $res;
            }
        }
        $response['noti_anno_count'] = $noti_count;
        $response['flag'] = trans('mobile.success');
        $response['message'] = 'Dashboard Information';

        // Taking logo updated by admin.
        $mobile_logo = SiteSetting::module('Contact Us', 'mobile_logo');
        $logo = '';
        if ($mobile_logo) {
            $logo = URL::to('/') . '/' . config('app.site_logo_path') . $mobile_logo;
        }

        $response['logo'] = $logo;
        // print_r($response); die;
        echo json_encode($response);
        exit;
    }

    public function postUpdateNotificationCount()
    {
        $notify_ids = $notification_ids = array_map('intval', explode(',', Input::get('notification_ids')));
        foreach ($this->user_data as $udata) {
            $user_id = $udata['uid'];
            $time_zone = $udata['timezone'];
        }
        $time_read = Timezone::convertToUTC(date('d-m-Y', time()), $time_zone, 'U');
        $notifications = Notification::whereIn('notification_id', $notify_ids)->update([
            'is_read' => true,
            'time_read' => $time_read,
        ]);
        $current_count = 0;
        $current_count = Input::get('current_count');
        $updated_count = $current_count - count($notify_ids);
        $response['updated_count'] = $updated_count;
        $response['flag'] = trans('mobile.success');
        $response['message'] = 'Notification Count Updated.';

        echo json_encode($response);
        exit;
    }

    public function postAnnouncements($per_page = 10, $page = 0)
    {

        /* Getting User data */
        foreach ($this->user_data as $udata) {
            $user_id = $udata['uid'];
            $relations = $udata['relations'];
        }
        $per_page = Config::get('app.mobile.announcements_per_page');

        $skip = $per_page * $page;
        $announce_list_id = [];
        if (isset($relations)) {
            foreach ($relations as $key => $value) {
                if ($key == 'active_usergroup_user_rel') {
                    $agl = UserGroup::getAnnouncementList($value);
                    foreach ($agl as $key3 => $value3) {
                        if (isset($value3['relations']['usergroup_announcement_rel'])) {
                            foreach ($value3['relations']['usergroup_announcement_rel'] as $key4 => $value4) {
                                $announce_list_id[] = $value4;
                            }
                        }
                    }
                }
                if ($key == 'user_feed_rel') {
                    $acfl = Program::getAnnouncementList($value);
                    foreach ($acfl as $key6 => $value6) {
                        if (isset($value6['relations']['contentfeed_announcement_rel'])) {
                            foreach ($value6['relations']['contentfeed_announcement_rel'] as $key7 => $value7) {
                                $announce_list_id[] = $value7;
                            }
                        }
                    }
                }
                if ($key == 'user_announcement_rel') {
                    if (!empty($value)) {
                        foreach ($value as $key5 => $value5) {
                            $announce_list_id[] = $value5;
                        }
                    }
                }
            }
        }
        $announce_list_id = array_unique($announce_list_id);

        $announcements = Announcement::getAnnouncementsforscroll($announce_list_id, $skip, $per_page);

        $response['announcements'] = [];
        if (isset($announcements)) {
            foreach ($announcements as $each) {
                $for_media = [];
                $for_media['forret'] = '';
                $for_media['type'] = '';
                $for_media['file'] = '';
                $for_media['video_cover_image'] = '';
                $new = 1;
                if (isset($each['relations']['active_media_announcement_rel']) && !empty($each['relations']['active_media_announcement_rel'])) {
                    $for_media = API::getMediaDetails($each['relations']['active_media_announcement_rel'][0]);
                }
                // print_r($for_media);
                if (isset($each['readers']) && in_array($user_id, $each['readers']['user'])) {
                    $new = 0;
                }
                $created_by_name = '';
                if (isset($each['created_by_name'])) {
                    $created_by_name = $each['created_by_name'];
                }
                $announcement_content = preg_replace_callback("/(<img[^>]*src *= *[\"']?)([^\"']*)/i", function ($matches) {
                    global $rootURL;

                    return $matches[1] . $rootURL . URL::to('/') . $matches['2'];
                }, $each['announcement_content']);

                $find = ['href='];
                $replace = ['link='];

                $announcement_content = str_replace($find, $replace, $announcement_content);

                $res = new stdClass();
                $res->announcement_id = $each['announcement_id'];
                $res->announcement_title = html_entity_decode($each['announcement_title']);
                $res->announcement_type = $each['announcement_type'];
                $res->announcement_media = $for_media['forret'];
                $res->media_type = $for_media['type'];
                $res->file = $for_media['file'];
                $res->video_cover_image = $for_media['video_cover_image'];
                $res->new = $new;
                $res->announcement_publish_date = $each['schedule'];
                $res->announcement_content = html_entity_decode($announcement_content);
                $res->announcement_device = 'both';
                $res->created_by = $each['created_by'];
                $res->created_by_name = $created_by_name;
                $response['announcements'][] = $res;
            }
        }
        $response['flag'] = trans('mobile.success');
        $response['message'] = 'Announcements Information';
        //  print_r($response); die;
        echo json_encode($response);
        exit;
    }


    public function postAnnouncementRead($announcement_id)
    {
        foreach ($this->user_data as $udata) {
            $uid = $udata['uid'];
        }

        $res = Announcement::updateAnnouncementsReaders($announcement_id, $uid);
        if ($res) {
            $updated_count = Input::get('current_count');
            $updated_count = $updated_count - 1;
            echo json_encode(['success' => true, 'updated_count' => $updated_count]);
            exit;
        } else {
            echo json_encode(['success' => false]);
            exit;
        }
    }

    public function postNotifications($per_page = 10, $page = 0)
    {
        foreach ($this->user_data as $udata) {
            $uid = $udata['uid'];
        }

        $per_page = Config::get('app.mobile.notifications_per_page');

        $skip = $per_page * $page;
        $notifications = Notification::getNotificationwithPagenation($uid, $skip, $per_page);

        $response['notifications'] = [];
        $response['notification_ids'] = [];
        $response['update_after'] = 5000;
        $notification_ids = '';

        if (isset($notifications)) {
            foreach ($notifications as $each) {
                $res = new stdClass();
                $res->notification_id = $each['notification_id'];
                $res->message = html_entity_decode($each['message']);
                $res->created_at = strtotime($each['created_at']);
                $response['notifications'][] = $res;
                if ($each['is_read'] == '') {
                    $notification_ids .= $each['notification_id'] . ',';
                }
            }
        }

        $response['notification_ids'] = trim($notification_ids, ',');
        $response['update_after'] = Config::get('app.notification_delay_read');
        $response['flag'] = trans('mobile.success');
        $response['message'] = 'Notifications Information';
        echo json_encode($response);
        exit;
    }

    public function postWatchnow($per_page = 10, $page = 0)
    {
        // API::CheckPermission();
        $per_page = Config::get('app.mobile.posts_per_page');

        $skip = $per_page * $page;
        foreach ($this->user_data as $udata) {
            $uid = $udata['uid'];
            if (isset($udata['favourited_packets'])) {
                $favorites = $udata['favourited_packets'];
            }
        }

        $program_slugs = TransactionDetail::getProgramIds($uid, null, 'all');
        // $packets=API::getPacketsUsingSlugs($program_slugs,$per_page,$skip);
        $packets = Packet::getPacketElementsUsingSlug($program_slugs);
        // print_r($packets); die;
        $response['packets'] = [];

        if (!empty($packets)) {
            $new_packets = $packet_ids = [];
            foreach ($packets as $each) {
                $elements_count = '';
                if (isset($each['elements'])) {
                    $elements_count = count($each['elements']);
                }

                $activity_count = 0;
                //Its hide since its take mongo load
                /*foreach ($each['elements'] as $value) {
                    $my_activity = API::pluckElementActivity($uid, $each['packet_id'], $value['id'], $value['type']);
                    if (!empty($my_activity)) {
                        $activity_count = $activity_count + 1;
                    }
                }*/
                if (($activity_count == 0) || ($elements_count != $activity_count)) {
                    $packet_ids[] = (int)$each['packet_id'];
                }
                if ($activity_count == 0) {
                    $new_packets[] = $each['packet_id'];
                }
            }

            if (empty($favorites)) {
                $favorites = [];
            }

            $packets = Packet::getPacketsUsingIdsSortBy($packet_ids, $sort_by = 'new_to_old', $per_page, $page_no = 0);

            $response['packets_count'] = count($packets);
            foreach ($packets as $packet) {
                $new = $favorite = '';
                $elements_count = '';
                if (isset($packet['elements'])) {
                    $elements_count = count($packet['elements']);
                }

                // if(isset($each['favourited_count']) && $each['favourited_count']>0){$fav_count=1;}

                if (in_array($packet['packet_id'], $new_packets)) {
                    $new = 1;
                }
                if (in_array($packet['packet_id'], $favorites)) {
                    $favorite = 1;
                }

                //$fav_count=$num='';
                //if(isset($packet['elements'])){$num=count($packet['elements']);}
                //if(isset($packet['favourited_count']) && $packet['favourited_count']>0){$fav_count=1;}

                $packet_cover_media = '';
                if ($packet['packet_cover_media'] != '') {
                    $packet_cover_media = URL::to('media_image/' . $packet['packet_cover_media'] . '?thumb=180x180');
                }
                $channel = Program::pluckFeedName($packet['feed_slug']);
                $channel = $channel[0];
                $res = new stdClass();
                $res->packet_id = $packet['packet_id'];
                $res->packet_title = html_entity_decode($packet['packet_title']);
                $res->packet_slug = $packet['packet_slug'];
                $res->feed_slug = $packet['feed_slug'];
                $res->packet_description = html_entity_decode($packet['packet_description']);
                $res->packet_cover_media = $packet_cover_media;
                $res->packet_publish_date = (($packet['packet_publish_date'] instanceof Carbon) ? $packet['packet_publish_date']->timestamp : strtotime($packet['packet_publish_date']));
                $res->created_by_name = $packet['created_by_name'];
                $res->elements_count = $elements_count;
                $res->new_label = $new;
                $res->program_type = $channel['program_type'];
                $res->channel = $channel['program_title'];
                $res->favorite = $favorite;
                $response['packets'][] = $res;
                $response['flag'] = trans('mobile.success');
                $response['message'] = 'All Packets User can Access to.';
            }
        } else {
            $response['flag'] = trans('mobile.success');
            $response['message'] = trans('mobile.no_packets');
        }
        echo json_encode($response);
        exit;
    }

    public function postPacket($slug, $element_id = null, $element_type = null, $page = 0)
    {
        foreach ($this->user_data as $udata) {
            $uid = $udata['uid'];
        }

        $jwplayer_key = Config::get('app.jwplayer.key');

        $packet = Packet::getPacket($slug);
        // echo "<pre>"; print_r($packet);   die;
        $first_element = 0;
        if (!empty($packet[0]['elements'])) {
            $elements = $packet[0]['elements'];
            $element_count = count($elements);
            $elements = array_values(array_sort($elements, function ($value) {
                return $value['order'];
            }));
            // echo $page;
            $feed = Program::pluckFeedName($packet[0]['feed_slug']);
            if ($element_id == null && $element_type == null && $page == 0) {
                $array = [
                    'module' => 'packet',
                    'action' => 'view',
                    'module_name' => html_entity_decode($packet[0]['packet_title']),
                    'module_id' => (int)$packet[0]['packet_id'],
                    'feed_id' => (int)$feed[0]['program_id'],
                    'feed_name' => html_entity_decode($feed[0]['program_title']),
                    'url' => Request::path(),
                ];
                // print_r($array); die;
                API::getLogActivity($array, $uid);
            }

            if ($page == 0) {
                $first_element = 1;
                $previous_element_id = $previous_element_type = $next_element_id = $next_element_type = '';
                $element_id = $elements[$page]['id'];
                $element_type = $elements[$page]['type'];
                if ($element_count > 1) {
                    $next_element_id = $elements[$page + 1]['id'];
                    $next_element_type = $elements[$page + 1]['type'];
                }
            } elseif ($page == ($element_count - 1)) {
                $previous_element_id = $elements[$page - 1]['id'];
                $previous_element_type = $elements[$page - 1]['type'];
                $element_id = $elements[$page]['id'];
                $element_type = $elements[$page]['type'];
                $next_element_id = $next_element_type = '';
            } else {
                $previous_element_id = $elements[$page - 1]['id'];
                $previous_element_type = $elements[$page - 1]['type'];
                $element_id = $elements[$page]['id'];
                $element_type = $elements[$page]['type'];
                $next_element_id = $elements[$page + 1]['id'];
                $next_element_type = $elements[$page + 1]['type'];
            }
            $element_detail = API::elementDetails($element_id, $element_type);
        }
        $response['packet'] = [];
        if (!empty($packet)) {
            foreach ($packet as $each) {
                $sequential = $qanda = 0;
                if ($each['sequential_access'] == 'yes') {
                    $sequential = 1;
                }
                if ($each['qanda'] == 'yes') {
                    $qanda = 1;
                }

                $res = new stdClass();
                $res->packet_id = $each['packet_id'];
                $res->packet_title = html_entity_decode($each['packet_title']);
                $res->packet_slug = $each['packet_slug'];
                $res->feed_slug = $each['feed_slug'];
                $res->feed_name = html_entity_decode($feed[0]['program_title']);
                $res->packet_description = html_entity_decode($each['packet_description']);
                $res->packet_publish_date = (($each['packet_publish_date'] instanceof Carbon) ? $each['packet_publish_date']->timestamp : strtotime($each['packet_publish_date']));
                $res->sequential_access = $sequential;
                $res->qanda = $qanda;
                $res->total_ques_public = $each['total_ques_public'];
                $res->total_ques_private = $each['total_ques_private'];
                $response['packet'][] = $res;
                $response['flag'] = trans('mobile.success');
                $response['message'] = 'Packet Information';
            }
        } else {
            $response['flag'] = trans('mobile.success');
            $response['message'] = 'Packet Not found';
        }

        $response['current_element'] = [];
        $total = $obtained_mark = $last_attempt_percentage = 0;
        $event_name = $event_type = $event_cycle = $event_description = $event_type = $speakers = $event_host_name = $location = '';
        $event_id = $event_cycle = $event_host_id = $start_date_label = $start_time_label = $start_time = $end_time = $created_at = $end_date_label = $end_time_label = 0;
        if (isset($element_detail)) {
            // print_r($element_detail); die;
            $res = new stdClass();
            $res->element_id = $element_detail['asset']['id'];
            $res->element_title = html_entity_decode($element_detail['asset']['name']);
            $res->element_description = html_entity_decode($element_detail['asset']['description']);
            //$res->element_short_description = $element_detail['asset']['short_description'];
            $res->element_type = $element_detail['asset']['type'];
            $res->element_asset_type = $element_detail['asset']['asset_type'];
            $res->first_element = $first_element;
            $res->jwplayer_key = $jwplayer_key;
            $liked = $quiz_id = $quiz_attempts = $quiz_end_time = $quiz_start_time = $practice_quiz = $quiz_duration = $total_mark = 0;
            $video_cover_image = $mime_type = $file_size = '';

            if (isset($element_detail['asset']['users_liked']) && in_array($uid, $element_detail['asset']['users_liked'])) {
                $liked = 1;
            }
            switch ($element_detail['asset']['type']) {
                case 'video':
                    $asset = API::getDAMSAssetsUsingAutoID((int)$element_detail['asset']['id']);
                    $asset = $asset[0];

                    $type = [
                        'element_type' => $element_type,
                    ];
                    $asset_path = array_merge($asset, $type);

                    if ($asset['asset_type'] == 'file') {
                        if (isset($asset['akamai_details'])) {
                            if (isset($asset['akamai_details']['delivery_html5_url'])) {
                                $asset_path = $asset['akamai_details']['delivery_html5_url'];
                                $video_cover_image = URL::to('/media_image/' . $asset['_id'] . '?compress=1');
                                $mime_type = $asset['mimetype'];
                                $file_size = $asset['file_size'];
                            } elseif (isset($asset['akamai_details']['stream_success_html5'])) {
                                $asset_path = $asset['akamai_details']['stream_success_html5'];
                                $video_cover_image = URL::to('/media_image/' . $asset['_id'] . '?compress=1');
                                $mime_type = $asset['mimetype'];
                                $file_size = $asset['file_size'];
                            } elseif (!isset($asset['akamai_details']['code']) || $asset['akamai_details']['code'] != 200) {
                                $asset_path = 'Error in syncing the file. Please contact';
                            } else {
                                $asset_path = 'File is being proccessed please wait.';
                            }
                            //elseif(isset($asset['akamai_details']['code']) && $asset['akamai_details']['code'] == 200 && isset($asset['akamai_details']['stream_success_flash'])){}
                        } else {
                            $asset_path = 'File is not synced with Video Server';
                        }
                    } else {
                        $asset_path = $asset['url'];
                    }
                    break;
                case 'image':
                    $asset = API::getDAMSAssetsUsingAutoID((int)$element_detail['asset']['id']);
                    $asset = $asset[0];
                    if ($element_detail['asset']['asset_type'] == 'file') {
                        $asset_path = URL::to('media_image/' . $element_detail['asset']['_id']);
                    } else {
                        $asset_path = $asset['url'];
                    }
                    break;
                case 'document':
                    $asset = API::getDAMSAssetsUsingAutoID((int)$element_detail['asset']['id']);
                    $asset = $asset[0];
                    if ($element_detail['asset']['asset_type'] == 'file') {
                        $asset_path = URL::to('media_image/' . $element_detail['asset']['_id']);
                    } else {
                        $asset_path = $element_detail['asset']['url'];
                    }
                    break;
                case 'audio':
                    $asset = API::getDAMSAssetsUsingAutoID((int)$element_detail['asset']['id']);
                    $asset = $asset[0];
                    if ($element_detail['asset']['asset_type'] == 'file') {
                        $asset_path = URL::to('media_image/' . $element_detail['asset']['_id']);
                    } else {
                        $asset_path = $element_detail['asset']['url'];
                    }
                    break;
                case 'assessment':
                    $asset = $element_detail['asset'];
                    // print_r($asset['id']); die;
                    $quiz = QuizAttempt::where('quiz_id', '=', (int)$asset['id'])
                        ->where('user_id', '=', (int)$uid)
                        ->orderBy('started_on', 'desc')
                        ->get();
                    $attempts = $quiz->where('status', 'CLOSED');
                    // $attempts = QuizAttempt::where('quiz_id', '=', (int) $element_detail['asset']['id'])
                    // ->where('user_id', '=', (int) $uid)
                    // ->where('status', 'CLOSED')
                    // ->orderBy('started_on', 'desc')
                    // ->get();
                    //echo "<pre>"; print_r($attempts); die;
                    if (!empty($attempts->first())) {
                        $total = $attempts->first()->total_mark;
                        $obtained_mark = $attempts->first()->obtained_mark;
                        $last_attempt_percentage = round(($obtained_mark / $total) * 100) . '%';
                    }
                    $attempted_count = 0;
                    $attempts_left = '';
                    if (!empty($attempts)) {
                        $attempted_count = count($quiz);
                    }
                    if ($asset['attempts'] > 0) {
                        $attempts_left = $asset['attempts'] - $attempted_count;
                    }
                    // $opened_attempt = QuizAttempt::where('quiz_id', '=', (int) $element_detail['asset']['id'])
                    // ->where('user_id', '=',(int)$uid)
                    // ->where('status','OPENED')
                    // ->orderBy('started_on','desc')
                    // ->get();
                    $opened_attempt = $quiz->where('status', 'OPENED');

                    $continue_last_attempt = 0;
                    foreach ($opened_attempt as $attempt) {
                        if ($attempt->status == 'OPENED') {
                            $continue_last_attempt = 1;
                        }
                    }
                    $section_practice_quiz = 0;
                    $message = '';
                    if ((isset($element_detail['asset']['sectioned_quiz']) && $element_detail['asset']['sectioned_quiz'] == true) || $element_detail['asset']['quiz_type'] == 'QUESTION_GENERATOR') {
                        $section_practice_quiz = 1;
                        $message = trans('mobile.section_quiz_not_support');
                    }
                    $res->continue_last_attempt = $continue_last_attempt;
                    $asset_path = URL::to('api/quiz/attempt-url');
                    $quiz_id = $element_detail['asset']['id'];
                    $quiz_attempts = $element_detail['asset']['attempts'];
                    $quiz_start_time = strtotime($element_detail['asset']['start_time']);
                    $quiz_end_time = strtotime($element_detail['asset']['end_time']);
                    $quiz_duration = $element_detail['asset']['duration'];
                    $practice_quiz = $element_detail['asset']['practice_quiz'];
                    $total_mark = $element_detail['asset']['total_mark'];
                    $res->the_attempt = $element_detail['asset']['the_attempt'];
                    $res->whether_correct = $element_detail['asset']['whether_correct'];
                    $res->marks = $element_detail['asset']['marks'];
                    $res->sectioned_quiz = $section_practice_quiz;
                    $res->message = $message;
                    $res->rationale = $element_detail['asset']['rationale'];
                    $res->correct_answer = $element_detail['asset']['correct_answer'];
                    $res->attempts_left = $attempts_left;
                    $last_attempt_mark = $obtained_mark;
                    $last_attempt_mark_percentage = $last_attempt_percentage;
                    break;
                case 'event':
                    $event_description = preg_replace_callback("/(<img[^>]*src *= *[\"']?)([^\"']*)/i", function ($matches) {
                        global $rootURL;

                        return $matches[1] . $rootURL . URL::to('/') . $matches['2'];
                    }, $element_detail['asset']['description']);

                    $find = ['href='];
                    $replace = ['link='];

                    $event_description = str_replace($find, $replace, $event_description);

                    $asset = $element_detail['asset'];
                    // print_r($asset); die;
                    $event_id = $element_detail['asset']['id'];
                    $event_name = html_entity_decode($element_detail['asset']['name']);
                    $event_type = $element_detail['asset']['event_type'];
                    $event_cycle = $element_detail['asset']['event_cycle'];
                    $event_description = html_entity_decode($event_description);
                    $event_type = $element_detail['asset']['event_type'];
                    $event_cycle = $element_detail['asset']['event_cycle'];
                    $speakers = $element_detail['asset']['speakers'];
                    $event_host_id = $element_detail['asset']['event_host_id'];
                    $event_host_name = $element_detail['asset']['event_host_name'];
                    $start_date_label = $element_detail['asset']['start_date_label'];
                    $start_time_label = $element_detail['asset']['start_time_label'];
                    $start_time = strtotime($element_detail['asset']['start_time']);
                    $end_date_label = $element_detail['asset']['end_date_label'];
                    $end_time = strtotime($element_detail['asset']['end_time']);
                    $end_time_label = $element_detail['asset']['end_time_label'];
                    $location = $element_detail['asset']['location'];
                    $created_at = $element_detail['asset']['created_at'];
                    $asset_path = '';
                    break;

                case 'flashcard':
                    $res->next_element_id = $next_element_id;
                    $res->next_element_type = $next_element_type;
                    $res->page = $page;
                    $response['current_element'][] = $res;
                    $response['success'] = 'success';
                    $response['message'] = trans('mobile.flas_card_not_support');
                    echo json_encode($response);
                    exit;
                case 'scorm':
                    $res->next_element_id = $next_element_id;
                    $res->next_element_type = $next_element_type;
                    $res->page = $page;
                    $response['current_element'][] = $res;
                    $response['success'] = 'success';
                    $response['message'] = trans('mobile.scorm_not_support');
                    echo json_encode($response);
                    exit;
                default:
                    $asset_path = '';
                    break;
            }
            $array = [
                'module' => 'element',
                'action' => 'view',
                'module_name' => html_entity_decode($asset['name']),
                'module_id' => (int)$asset['id'],
                'element_type' => $element_type,
                'packet_id' => (int)$packet[0]['packet_id'],
                'packet_name' => html_entity_decode($packet[0]['packet_title']),
                'feed_id' => (int)$feed[0]['program_id'],
                'feed_name' => html_entity_decode($feed[0]['program_title']),
                'url' => 'program/packet/' . $slug,
            ];
            // print_r($array); die;
            API::getLogActivity($array, $uid);

            $res->asset_type = $element_type;
            $res->asset = $asset_path;
            $res->video_cover_image = $video_cover_image;
            $res->mime_type = $mime_type;
            $res->file_size = $file_size;
            $res->next_element_id = $next_element_id;
            $res->next_element_type = $next_element_type;
            $res->page = $page;
            $res->quiz_id = $quiz_id;
            $res->quiz_attempts = $quiz_attempts;
            $res->quiz_start_time = $quiz_start_time;
            $res->quiz_end_time = $quiz_end_time;
            $res->quiz_duration = $quiz_duration;
            $res->practice_quiz = $practice_quiz;
            $res->total_mark = $total_mark;
            $res->last_attempt_mark = $obtained_mark;
            $res->last_attempt_mark_percentage = $last_attempt_percentage;
            $res->liked = $liked;
            $res->previous_element_id = $previous_element_id;
            $res->previous_element_type = $previous_element_type;
            $res->event_id = $event_id;
            $res->event_name = $event_name;
            $res->event_type = $event_type;
            $res->event_cycle = $event_cycle;
            $res->event_description = $event_description;
            $res->event_type = $event_type;
            $res->event_cycle = $event_cycle;
            $res->speakers = $speakers;
            $res->event_host_id = $event_host_id;
            $res->event_host_name = $event_host_name;
            $res->start_date_label = $start_date_label;
            $res->start_time_label = $start_time_label;
            $res->start_time = $start_time;
            $res->end_date_label = $end_date_label;
            $res->end_time_label = $end_time_label;
            $res->end_time = $end_time;
            $res->location = $location;
            $res->created_at = $created_at;

            $response['current_element'][] = $res;
        }
        // print_r($response); die;
        echo json_encode($response);
        exit;
    }

    public function postPacketTimeline($slug = null)
    {
        $packet = Packet::getPacket($slug);
        foreach ($this->user_data as $udata) {
            $uid = $udata['uid'];
        }
        $i = 0;
        if (!empty($packet[0]['elements'])) {
            $response = [];

            $sequential = $page = $element_count = 0;
            if ($packet[0]['sequential_access'] == 'yes') {
                $sequential = 1;
            }
            $response['sequential'] = $sequential;
            $elements = $packet[0]['elements'];
            $element_count = count($elements);
            $response['elements_count'] = $element_count;
            $elements = array_values(array_sort($elements, function ($value) {
                return $value['order'];
            }));

            $element_asset = '';
            foreach ($elements as $element) {
                if ($page == 0) {
                    $previous_element_id = $previous_element_type = $next_element_id = $next_element_type = '';
                    //$element_id=$elements[$page]['id'];
                    //$element_type=$elements[$page]['type'];
                    if ($element_count > 1) {
                        $next_element_id = $elements[$page + 1]['id'];
                        $next_element_type = $elements[$page + 1]['type'];
                    }
                } elseif ($page == ($element_count - 1)) {
                    $previous_element_id = $elements[$page - 1]['id'];
                    $previous_element_type = $elements[$page - 1]['type'];
                    //$element_id=$elements[$page]['id'];
                    //$element_type=$elements[$page]['type'];
                    $next_element_id = $next_element_type = '';
                } else {
                    $previous_element_id = $elements[$page - 1]['id'];
                    $previous_element_type = $elements[$page - 1]['type'];
                    //$element_id=$elements[$page]['id'];
                    //$element_type=$elements[$page]['type'];
                    $next_element_id = $elements[$page + 1]['id'];
                    $next_element_type = $elements[$page + 1]['type'];
                }
                $name = '';
                switch ($element['type']) {
                    case 'media':
                        $element_asset = API::getDAMSAssetsUsingAutoID((int)$element['id']);
                        $element_asset = $element_asset[0];
                        $name = $element_asset['name'];
                        $elem_type = $element_asset['type'];
                        // $size='Type:'.$element_asset['type'];
                        $type = [
                            'element_type' => $element['type'],
                        ];
                        $element_asset = array_merge($element_asset, $type);
                        break;

                    case 'assessment':
                        $element_asset = Quiz::getQuizAssetsUsingAutoID($element['id']);
                        $element_asset = $element_asset[0];
                        $name = $element_asset['quiz_name'];
                        $elem_type = $element['type'];
                        // $size='Dur:'.$element_asset['duration'];
                        $class = 'fa-edit';
                        $type = [
                            'element_type' => $element['type'],
                            'id' => $element_asset['quiz_id'],
                        ];
                        $element_asset = array_merge($element_asset, $type);
                        break;

                    case 'event':
                        $element_asset = Event::getEventsAssetsUsingAutoID($element['id']);
                        // print_r($element_asset); die;
                        $element_asset = $element_asset[0];
                        $name = $element_asset['event_name'];
                        $elem_type = 'event';
                        // $size='Type:'.$element_asset['event_type'];
                        $class = 'fa-calendar';
                        break;
                    case 'flashcard':
                        $element_asset = FlashCard::getFlashcardsAssetsUsingAutoID((int)$element['id']);
                        $element_asset = $element_asset[0];
                        $name = $element_asset['title'];
                        $elem_type = 'flashcard';
                        // $size='Type:'.$element_asset['event_type'];
                        $class = '';
                        break;
                }
                // print_r($element_asset); die;
                $activity = [];
                $element_status = '';
                //Its hide since its take mongo load
                //$activity = API::pluckElementActivity($uid, $packet[0]['packet_id'], $element['id'], $element['type']);
                $element_visible = 'FALSE';
                if (!empty($activity)) {
                    $element_status = 'WATCHED';
                } else {
                    if ($i == 0) {
                        $element_visible = 'TRUE';
                    }
                    $element_status = 'NOTWATCHED';
                    ++$i;
                }

                $res = new stdClass();
                $res->element_id = $element['id'];
                $res->element_type = $element['type'];
                $res->element_title = html_entity_decode($name);
                $res->element_status = $element_status;
                $res->element_order = $element['order'];
                $res->element_visible = $element_visible;
                $res->type = $elem_type;
                $res->next_element_id = $next_element_id;
                $res->next_element_type = $next_element_type;
                $res->page = $page;
                $res->previous_element_id = $previous_element_id;
                $res->previous_element_type = $previous_element_type;
                $response['elements'][] = $res;

                $page = $page + 1;
            }
            $response['flag'] = 'success';
            $response['message'] = 'Packet Timeline Information.';
        } else {
            $response['flag'] = 'success';
            $response['message'] = 'No Elements found.';
        }
        //print_r($response); die;
        echo json_encode($response);
        exit;
    }

    public function postAskquestion($feed_slug, $packet_id, $packet_slug)
    {
        $question = Input::get('question');
        $response = [];
        if ($question != '') {
            API::postQuestion($this->user_data, $feed_slug, $packet_id, $packet_slug, $question);
            $response['flag'] = trans('mobile.success');
            $response['message'] = trans('mobile.post_question_success');
        } else {
            $response['flag'] = 'failure';
            $response['message'] = 'Missing Parameter.';
        }
        echo json_encode($response);
        exit;
    }

    public function postQuestionComment($packet_slug, $id)
    {
        foreach ($this->user_data as $udata) {
            $user_id = $udata['uid'];
            $user = '';
            $user = $udata['firstname'];
            if ($udata['lastname'] != '') {
                $user .= ' ' . $udata['lastname'];
            }

            $user_name = $udata['username'];
        }
        $response = [];
        $insertarr = [
            'id' => PacketFaqAnswers::getUniqueId(),
            'ques_id' => (int)$id,
            'user_id' => $user_id,
            'username' => $user_name,
            'answer' => htmlentities(Input::get('comment')),
            'status' => 'ACTIVE',
            'created_at' => time(),
            'created_by_name' => $user,
        ];

        PacketFaqAnswers::insert($insertarr);

        $response['flag'] = trans('mobile.success');
        $response['message'] = trans('mobile.question_comment');

        echo json_encode($response);
        exit;
    }

    public function postFaqs($packet_id, $per_page = 5, $page = 0)
    {
        $response = [];
        $public_ques = PacketFaq::getPublicQuestions($packet_id, $per_page, $page);

        foreach ($this->user_data as $udata) {
            $uid = $udata['uid'];
        }

        if (!empty($public_ques)) {
            foreach ($public_ques as $question) {
                $res = new stdClass();
                $res->question_id = $question['id'];
                $res->question = html_entity_decode($question['question']);
                $res->question_asked_by = $question['created_by_name'];
                $res->question_like_count = $question['like_count'];
                $res->question_created_at = $question['created_at'];
                $res->liked = 0;
                if (isset($question['users_liked']) && in_array($uid, $question['users_liked'])) {
                    $res->liked = 1;
                }

                $que_answers = PacketFaqAnswers::getAnswersByQuestionID($question['id']);

                $answer = [];
                foreach ($que_answers as $ans) {
                    $answer['answer_id'] = $ans->id;
                    $answer['answer'] = html_entity_decode($ans->answer);
                    $answer['answered_by'] = $ans->created_by_name;
                    $answer['answer_like_count'] = $ans->like_count;
                    $answer['answered_at'] = $ans->created_at;
                    $res->answers[] = $answer;
                }
                $response['faqs'][] = $res;
            }
            $response['flag'] = 'success';
            $response['message'] = 'FAQs Information.';
        } else {
            $response['flag'] = 'success';
            $response['message'] = trans('mobile.no_faqs');
        }
        // print_r($response); die;
        echo json_encode($response);
        exit;
    }

    public function postMyquestions($packet_id, $per_page = 5, $page_no = 0)
    {
        foreach ($this->user_data as $udata) {
            $uname = $udata['username'];
            $uid = $udata['uid'];
        }
        $skip = $per_page * $page_no;
        $user_ques = API::getUserQuestions($uname, $packet_id, $skip, $per_page);

        if (!empty($user_ques)) {
            foreach ($user_ques as $question) {
                $faq = 0;
                if ($question['access'] == 'public') {
                    $faq = 1;
                }
                $admin_ans = 0;
                $public_answers = PacketFaqAnswers::getAnswersByQuestionID($question['id'], $uid);
                if (count($public_answers) > 0) {
                    $admin_ans = 1;
                }
                $res = new stdClass();
                $res->question_id = $question['id'];
                $res->question = html_entity_decode($question['question']);
                $res->question_asked_by = $question['created_by_name'];
                $res->question_like_count = $question['like_count'];
                $res->question_created_at = $question['created_at'];
                $res->admin_ans = $admin_ans;
                $res->faq = $faq;

                $que_answers = PacketFaqAnswers::getAnswersByQuestionID($question['id']);

                $answer = [];
                foreach ($que_answers as $ans) {
                    $answer['answer_id'] = $ans->id;
                    $answer['answer'] = html_entity_decode($ans->answer);
                    $answer['answered_by'] = $ans->created_by_name;
                    $answer['answer_like_count'] = $ans->like_count;
                    $answer['answer_on'] = $ans->created_at;
                    $res->answers[] = $answer;
                }
                $response['myquestions'][] = $res;
            }

            $response['flag'] = 'success';
            $response['message'] = 'User Questions Information.';
        } else {
            $response['flag'] = 'success';
            $response['message'] = trans('mobile.no_questions');
        }
        echo json_encode($response);
        exit;
    }

    public function postDeleteMyquestion($qid = 0)
    {
        $user_id = 0;
        foreach ($this->user_data as $udata) {
            $user_id = $udata['uid'];
        }
        if (count(PacketFaq::getQuestionsByQuestionID($qid)) == 0 || $user_id == 0) {
            echo json_encode(
                [
                    'flag' => trans('mobile.failure'),
                    'message' => trans('mobile.no_questions'),
                ]
            );
            exit;
        }
        $result = PacketFaq::getDelete($qid, $user_id);

        /* API response */
        if ($result == true) {
            echo json_encode(
                [
                    'flag' => trans('mobile.success'),
                    'message' => trans('mobile.question_delete'),
                ]
            );
            exit;
        } else {
            echo json_encode(
                [
                    'flag' => trans('mobile.failure'),
                    'message' => trans('mobile.no_questions'),
                ]
            );
            exit;
        }
    }

    public function postEditQuestion($qid = 0)
    {
        if (count(PacketFaq::getQuestionsByQuestionID($qid)) == 0) {
            echo json_encode(
                [
                    'flag' => trans('mobile.failure'),
                    'message' => trans('mobile.no_questions'),
                ]
            );
            exit;
        }

        Input::flash();
        $qus_edit = Input::get('question', '');
        $user_id = 0;
        foreach ($this->user_data as $udata) {
            $user_id = $udata['uid'];
        }

        if ($qid != 0 && $qus_edit != '') {
            $result = PacketFaq::getUpdate($qid, $qus_edit, $user_id);
            if ($result == true) {
                echo json_encode(
                    [
                        'flag' => trans('mobile.success'),
                        'message' => trans('mobile.question_update_success'),
                    ]
                );
                exit;
            } else {
                echo json_encode(
                    [
                        'flag' => trans('mobile.failure'),
                        'message' => trans('mobile.question_update_fail'),
                    ]
                );
                exit;
            }
        } elseif ($qid != 0) {
            $question = PacketFaq::getQuestionsByQuestionID($qid);
            if (isset($question[0]->question)) {
                echo json_encode(
                    [
                        'flag' => trans('mobile.success'),
                        'question' => $question[0]->question,
                    ]
                );
                exit;
            } else {
                echo json_encode(
                    [
                        'flag' => trans('mobile.success'),
                        'message' => trans('mobile.no_questions'),
                    ]
                );
                exit;
            }
        }
    }

    public function postFavouritePacket($feed_slug, $packet_slug, $packet_id, $action)
    {
        foreach ($this->user_data as $udata) {
            $uid = $udata['uid'];
        }

        $favourite = API::updateFavouriteCount($uid, $feed_slug, $packet_slug, $packet_id, $action);
        /* API response */
        if (isset($favourite)) {
            echo json_encode(
                [
                    'flag' => 'success',
                    'message' => 'Packet Favourited/Unavourited successfully.',
                ]
            );
            exit;
        }
    }

    public function postLikeElement($packet_slug, $packet_id, $element_id, $element_name, $element_type)
    {
        $feed_slug = Input::get('feed_slug');
        $action = Input::get('action');
        foreach ($this->user_data as $udata) {
            $uid = $udata['uid'];
        }
        $liked = API::updateElementLikedCount($uid, $feed_slug, $packet_slug, $packet_id, $element_id, $element_name, $element_type, $action);
        /* API response */
        if (isset($liked)) {
            echo json_encode(
                [
                    'flag' => 'success',
                    'message' => 'Element Liked/Unliked successfully.',
                ]
            );
            exit;
        }
    }

    public function postPrograms($type = null, $per_page = 10, $page = 0)
    {
        foreach ($this->user_data as $udata) {
            $uid = $udata['uid'];
        }
        $per_page = Config::get('app.mobile.programs_per_page');
        switch ($type) {
            case 'myfeed':
                $sub_program_slugs = TransactionDetail::getProgramIds($uid, 'true', 'all');

                $category = [];
                $feed_ids = [];

                $programs = Program::getProgramsSortBy($category, $feed_ids, $sub_program_slugs, $sort_by = 'new_to_old', $per_page, $page);

                //$programs=Program::getPrograms($program_slugs);

                $response['myfeeds'] = [];

                foreach ($programs as $each) {
                    $categories = Category::getFeedRelatedCategory($each['program_id']);
                    // print_r($categories); die;
                    $feed_cover_media = '';
                    $cat_info = [];
                    if ($each['program_cover_media'] != '') {
                        $feed_cover_media = URL::to('media_image/' . $each['program_cover_media'] . '?thumb=180x180');
                    }
                    foreach ($categories as $category) {
                        $cat_info[] = ucfirst(strtolower($category['category_name']));
                    }
                    $num_packets = 0;
                    $num_packets = Packet::getPacketsCountUsingSlug($each['program_slug']);
                    $res = new stdClass();
                    $res->feed_id = $each['program_id'];
                    $res->feed_title = html_entity_decode($each['program_title']);
                    $res->category = $cat_info;
                    $res->feed_slug = $each['program_slug'];
                    $res->program_type = $each['program_type'];
                    $res->feed_description = html_entity_decode($each['program_description']);
                    $res->feed_display_startdate = (($each['program_display_startdate'] instanceof Carbon) ? $each['program_display_startdate']->timestamp : strtotime($each['program_display_startdate']));
                    $res->feed_display_enddate = (($each['program_display_enddate'] instanceof Carbon) ? $each['program_display_enddate']->timestamp : strtotime($each['program_display_enddate']));
                    $res->feed_startdate = (($each['program_startdate'] instanceof Carbon) ? $each['program_startdate']->timestamp : strtotime($each['program_startdate']));
                    $res->feed_enddate = (($each['program_enddate'] instanceof Carbon) ? $each['program_enddate']->timestamp : strtotime($each['program_enddate']));
                    $res->feed_visibility = $each['program_visibility'];
                    $res->feed_cover_media = $feed_cover_media;
                    $res->packet_count = $num_packets;
                    $response['myfeeds'][] = $res;
                }
                $response['flag'] = 'success';
                $response['message'] = 'My Feed Information';
                break;

            default:
                $response['flag'] = 'success';
                $response['message'] = 'Invalid Program Type.';

                break;
        }

        echo json_encode($response);
        exit;
    }

    public function postProgram($type = null, $slug)
    {
        $favorites = [];
        foreach ($this->user_data as $udata) {
            $uid = $udata['uid'];
            if (isset($udata['favourited_packets'])) {
                $favorites = $udata['favourited_packets'];
            }
        }

        switch ($type) {
            case 'myfeed':
                // $program_slugs=array($slug);

                $programs = Program::getAllPrograms($type = 'all', $slug, $categories = '', $subscribed_feeds = '', $records_per_page = '', $page_number = '', $selected_feeds = '', $sort_by = '');
                //print_r($programs); die;
                //$categories=Category::getFeedRelatedCategory($program['program_id']);
                if (empty($categories)) {
                    $categories = [];
                }
                $packets_count = Packet::getPacketsCountUsingSlug($slug);
                $liked_packets = Packet::getLikedPackets($slug);
                $packets = Packet::getPacketsUsingSlug($slug, 'title_lower');

                //    if($from == null)
                // {
                //  $array=array(
                //      "module" => "contentfeed",
                //      "action" => "view",
                //      "module_name" => $program['program_title'],
                //      "module_id" => (int)$program['program_id'],
                //      "url" => Request::path()
                //        );
                //  MyActivity::getInsertActivity($array);
                // }
                $response['feed'] = [];

                foreach ($programs as $each) {
                    $categories = Category::getFeedRelatedCategory($each['program_id']);
                    // print_r($categories); die;
                    $cat_info = $feed_cover_media = '';
                    if ($each['program_cover_media'] != '') {
                        $feed_cover_media = URL::to('media_image/' . $each['program_cover_media'] . '?thumb=180x180');
                    }
                    // echo $feed_cover_media; die;
                    foreach ($categories as $category) {
                        $cat_info .= ucfirst(strtolower($category['category_name'])) . ',';
                    }

                    $num_packets = 0;
                    $num_packets = count($packets);
                    $res = new stdClass();
                    $res->feed_id = $each['program_id'];
                    $res->feed_title = html_entity_decode($each['program_title']);
                    $res->category = rtrim($cat_info, ',');
                    $res->feed_slug = $each['program_slug'];
                    $res->program_type = $each['program_type'];
                    $res->feed_description = html_entity_decode($each['program_description']);
                    $res->feed_display_startdate = $each['program_display_startdate'];
                    $res->feed_display_enddate = $each['program_display_enddate'];
                    $res->feed_startdate = $each['program_startdate'];
                    $res->feed_enddate = $each['program_enddate'];
                    $res->feed_visibility = $each['program_visibility'];
                    $res->feed_cover_media = $feed_cover_media;
                    $res->packet_count = $num_packets;
                    $response['feed'][] = $res;
                }
                // print_r($response); die;
                $response['packets'] = [];
                if (!empty($packets)) {
                    $new_packets = $packet_ids = [];
                    foreach ($packets as $each) {
                        $elements_count = 0;

                        if (isset($each['elements'])) {
                            $elements_count = count($each['elements']);
                        }

                        $activity_count = 0;
                        //Its hide since its take mongo load
                        /*foreach ($each['elements'] as $value) {
                            $my_activity = API::pluckElementActivity($uid, $each['packet_id'], $value['id'], $value['type']);
                            if (!empty($my_activity)) {
                                $activity_count = $activity_count + 1;
                            }
                        }*/
                        if (($activity_count == 0) || ($elements_count != $activity_count)) {
                            $packet_ids[] = (int)$each['packet_id'];
                        }
                        if ($activity_count == 0) {
                            $new_packets[] = $each['packet_id'];
                        }
                    }
                    foreach ($packets as $each) {
                        $new = $favorite = '';

                        $elements_count = 0;

                        if (isset($each['elements'])) {
                            $elements_count = count($each['elements']);
                        }

                        if (in_array($each['packet_id'], $new_packets)) {
                            $new = 1;
                        }
                        if (in_array($each['packet_id'], $favorites)) {
                            $favorite = 1;
                        }
                        $packet_cover_media = '';
                        if ($each['packet_cover_media'] != '') {
                            $packet_cover_media = URL::to('media_image/' . $each['packet_cover_media'] . '?thumb=180x180');
                        }
                        $res = new stdClass();
                        $res->packet_id = $each['packet_id'];
                        $res->packet_title = html_entity_decode($each['packet_title']);
                        $res->packet_slug = $each['packet_slug'];
                        $res->feed_slug = $each['feed_slug'];
                        $res->packet_description = html_entity_decode($each['packet_description']);
                        $res->created_by_name = $each['created_by_name'];
                        $res->packet_cover_media = $packet_cover_media;
                        $res->packet_publish_date = $each['packet_publish_date'];
                        $res->elements_count = $elements_count;
                        $res->new_label = $new;
                        $res->favorite = $favorite;
                        $response['packets'][] = $res;
                    }
                    $response['flag'] = 'success';
                    $response['message'] = 'Feed Information';
                } else {
                    $response['flag'] = 'success';
                    $response['message'] = trans('mobile.no_packets');
                }

                break;

            default:
                $response['flag'] = 'success';
                $response['message'] = 'Invalid Program Type.';

                break;
        }
        echo json_encode($response);
        exit;
    }

    public function postAssessments($filter, $per_page = 10, $page = 0)
    {
        foreach ($this->user_data as $udata) {
            $user = $udata;
            $uid = $udata['uid'];
        }

        $per_page = Config::get('app.mobile.assessments_per_page');
        //$per_page=3;
        $skip = $per_page * $page;
        $response = [];
        $user_quiz_rel = API::userQuizRel($user);
        $quiz_list = $user_quiz_rel['quiz_list'];
        // Content feed filter
        // $cf_selected = [];
        // if(Session::has('assessment_filter.cf')) {
        //   $cf_selected = Session::get('assessment_filter.cf');
        //   if(!empty($cf_selected)) {
        //       $quiz_list = [];
        //       foreach (Session::get('assessment_filter.cf') as $value) {
        //           if(isset($user_quiz_rel['feed_quiz_list'][$value]))
        //               $quiz_list = array_merge($quiz_list, $user_quiz_rel['feed_quiz_list'][$value]);
        //       }
        //   }
        // }
        $quiz_list = array_map('intval', array_unique($quiz_list));

        $attempted = QuizAttempt::where('user_id', '=', (int)$uid)
            ->whereIn('quiz_id', $quiz_list)
            ->get();

        $attempt['list'] = $attempt['detail'] = [];
        foreach ($attempted as $value) {
            $attempt['list'][] = (int)$value->quiz_id;
            $attempt['detail'][$value->quiz_id][] = $value;
        }
        $attempt['list'] = array_unique($attempt['list']);
        $response['attempted_quize_count'] = $count['attempted'] = count($attempt['list']);
        $response['unattempted_quize_count'] = $count['unattempted'] = count(array_diff($quiz_list, $attempt['list']));

        // $start = Input::get('start', 0);
        // $limit = 9;
        switch ($filter) {
            case 'unattempted':
                $quizzes = Quiz::whereIn('quiz_id', array_diff($quiz_list, $attempt['list']))
                    ->where('status', '=', 'ACTIVE')
                    ->where('is_sections_enabled', '!=', true)
                    ->where('type', '!=', 'QUESTION_GENERATOR')
                    ->orderBy('created_at', 'desc')
                    ->skip($skip)
                    ->take($per_page)
                    ->get();
                break;

            case 'attempted':
                $quizzes = Quiz::whereIn('quiz_id', $attempt['list'])
                    ->where('status', '=', 'ACTIVE')
                    ->where('is_sections_enabled', '!=', true)
                    ->where('type', '!=', 'QUESTION_GENERATOR')
                    ->orderBy('created_at', 'desc')
                    ->skip($skip)
                    ->take($per_page)
                    ->get();
                break;

            default:
                $response['flag'] = 'success';
                $response['message'] = 'Invalid Assessment filter.';
                echo json_encode($response);
                exit;
        }
        // print_r($quizzes); die;
        $response['quiz_list'] = [];
        if (count($quizzes) > 0) {
            foreach ($quizzes as $quiz) {
                $attempted_count = 0;
                $attempted_details = API::userAttempts($uid, $quiz->quiz_id);

                $attempted_count = count($attempted_details);
                $practice_quiz = 0;
                if ($quiz->practice_quiz == true) {
                    $practice_quiz = 1;
                }
                $res = new stdClass();
                $res->quiz_id = $quiz->quiz_id;
                $res->quiz_name = html_entity_decode($quiz->quiz_name);
                $res->quiz_start_date = strtotime($quiz->start_time);
                $res->quiz_end_date = strtotime($quiz->end_time);
                $res->quiz_duration = $quiz->duration;
                $res->quiz_attempts = $quiz->attempts;
                $res->attempted_count = $attempted_count;
                $res->practice_quiz = $practice_quiz;
                $response['quiz_list'][] = $res;
            }
        } else {
            $response['flag'] = 'success';
            $response['message'] = 'No assessments.';
        }
        // print_r($response); die;
        echo json_encode($response);
        exit;
    }

    public function postAssessment($quiz_id)
    {
        $quiz = Quiz::where('quiz_id', '=', (int)$quiz_id)
            ->where('status', '=', 'ACTIVE')
            ->first();

        foreach ($this->user_data as $udata) {
            $user = $udata;
            $uid = $udata['uid'];
        }
        $liked = $total = $obtained_mark = $last_attempt_percentage = 0;
        if (isset($quiz->users_liked) && in_array($uid, $quiz->users_liked)) {
            $liked = 1;
        }
        if (!empty($quiz)) {
            $user_quiz_rel = API::userQuizRel($user);

            $program = collect();
            if (!empty($user_quiz_rel['feed_quiz_list'])) {
                foreach ($user_quiz_rel['feed_quiz_list'] as $key => $value) {
                    if (in_array($quiz_id, $value)) {
                        $slugs[] = $key;
                    }
                }
                if (!empty($slugs)) {
                    $program = Program::whereIn('program_slug', $slugs)
                        ->get(['program_id', 'program_title']);
                }
            }

            // User access permission
            if (!in_array($quiz_id, $user_quiz_rel['quiz_list'])) {
                return parent::getError($this->theme, $this->theme_path, 'You are not assigned to this quiz', url('assessment'));
            }

            $attempts = QuizAttempt::where('quiz_id', '=', (int)$quiz_id)
                ->where('user_id', '=', (int)$uid)
                ->orderBy('started_on', 'desc')
                ->get();
            $closed = $attempts->where('status', 'CLOSED');
            // print_r($closed); die;
            $i = $attempts->count();
            if (!empty($closed->first())) {
                $total = $closed->first()->total_mark;
                $obtained_mark = $closed->first()->obtained_mark;
                $last_attempt_percentage = round(($obtained_mark / $total) * 100) . '%';
            }
            $attempted_count = 0;
            $attempts_left = '';
            if (!empty($attempts)) {
                $attempted_count = count($attempts);
            }
            if ($quiz->attempts > 0) {
                $attempts_left = $quiz->attempts - $attempted_count;
            }
            $response = [];

            $res = new stdClass();
            $res->quiz_id = $quiz->quiz_id;
            $res->quiz_name = html_entity_decode($quiz->quiz_name);
            $res->quiz_start_date = strtotime($quiz->start_time);
            $res->quiz_end_date = strtotime($quiz->end_time);
            $res->quiz_duration = $quiz->duration;
            $res->quiz_attempts = $quiz->attempts;
            $res->the_attempt = $quiz->review_options['the_attempt'];
            $res->whether_correct = $quiz->review_options['whether_correct'];
            $res->marks = $quiz->review_options['marks'];
            $res->rationale = $quiz->review_options['rationale'];
            $res->correct_answer = $quiz->review_options['correct_answer'];
            $res->attempted_count = $attempted_count;
            $res->attempts_left = $attempts_left;
            $res->total_mark = $total;
            $res->last_attempt_mark = $obtained_mark;
            $res->last_attempt_mark_percentage = $last_attempt_percentage;
            $res->liked = $liked;

            $continue_last_attempt = 0;
            foreach ($attempts as $attempt) {
                if ($attempt->status == 'OPENED') {
                    $continue_last_attempt = 1;
                }
            }
            $res->continue_last_attempt = $continue_last_attempt;
            $response['quiz_details'][] = $res;

            $response['attempts_summary'] = [];
            foreach ($attempts as $attempt) {
                // if($attempt->status=='OPENED')
                // {
                //  $continue_last_attempt=1;
                // }
                $last_attempt_percentage = 0;
                $last_attempt_percentage = round(($attempt->obtained_mark / $attempt->total_mark) * 100) . '%';
                $res = new stdClass();
                $res->serial_num = $i--;
                $res->attempt_id = $attempt->attempt_id;
                $res->total_mark = $attempt->total_mark;
                $res->obtained_mark = $attempt->obtained_mark;
                $res->last_attempt_mark_percentage = $last_attempt_percentage;
                $res->session_type = $attempt->session_type;
                $res->status = $attempt->status;
                $res->started_on = strtotime($attempt->started_on);
                $res->completed_on = strtotime($attempt->completed_on);
                $response['attempts_summary'][] = $res;
            }
            $response['flag'] = 'success';
            $response['message'] = 'Quiz Information.';
        } else {
            $response['flag'] = 'success';
            $response['message'] = 'No Quiz found.';
        }

        echo json_encode($response);
        exit;
    }

    public function postStarQuiz($action, $quiz_id)
    {
        foreach ($this->user_data as $udata) {
            $user = $udata;
            $uid = $udata['uid'];
        }
        $quiz = Quiz::where('status', '=', 'ACTIVE')
            ->where('quiz_id', '=', (int)$quiz_id)
            ->first();

        switch ($action) {
            case 'star':
                Quiz::where('quiz_id', '=', (int)$quiz_id)
                    ->push('users_liked', (int)$uid, true);
                break;

            case 'unstar':
                Quiz::where('quiz_id', '=', (int)$quiz_id)
                    ->pull('users_liked', (int)$uid);
                break;

            default:
                return response()->json([
                    'status' => false,
                    'quiz_id' => (int)$quiz_id,
                ]);
                break;
        }
        /* API response */
        echo json_encode(
            [
                'flag' => 'success',
                'message' => 'Quiz starred/Unstarred successfully.',
            ]
        );
        exit;
    }

    public function postLogout()
    {
        $access_token = trim(Input::get('access_token'));
        $logout = API::removeAccessToken($access_token);
        /* API response */
        if (isset($logout)) {
            echo json_encode(
                [
                    'flag' => 'success',
                    'message' => 'Successfully Logged Out',
                ]
            );
            exit;
        }
    }

    public function postReviewQuizAttempt($attempt_id)
    {
        foreach ($this->user_data as $udata) {
            $user = $udata;
            $uid = $udata['uid'];
        }

        $attempt = QuizAttempt::where('attempt_id', '=', (int)$attempt_id)
            ->where('user_id', '=', $uid)
            ->where('status', '=', 'CLOSED')
            ->first();

        if (!empty($attempt)) {
            $quiz = Quiz::where('quiz_id', '=', (int)$attempt->quiz_id)->first();

            $attempt_data = QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
                ->get()
                ->keyBy('question_id')
                ->toArray();

            $data['list'] = $data['questions'] = [];
            $count = 1;
            foreach ($attempt->questions as $question) {
                $q = (isset($attempt_data[(int)$question])) ? $attempt_data[(int)$question] : false;
                if (!empty($q)) {
                    $temp = [];
                    $temp['question_no'] = $count;
                    $temp['ans_status'] = $q['answer_status'];
                    $data['list'][] = $temp;

                    $question = preg_replace_callback("/(<img[^>]*src *= *[\"']?)([^\"']*)/i", function ($matches) {
                        global $rootURL;

                        return $matches[1] . $rootURL . URL::to('/') . $matches['2'];
                    }, $q['question_text']);

                    $find = ['href='];
                    $replace = ['link='];

                    $question = str_replace($find, $replace, $question);

                    $temp = [];
                    $temp['question_no'] = $count;
                    $temp['question_text'] = html_entity_decode($question);
                    $temp['question_mark'] = $q['question_mark'];
                    foreach ($q['answer_order'] as $answer) {
                        $option = preg_replace_callback("/(<img[^>]*src *= *[\"']?)([^\"']*)/i", function ($matches) {
                            global $rootURL;

                            return $matches[1] . $rootURL . URL::to('/') . $matches['2'];
                        }, $q['answers'][$answer]['answer']);

                        $q['answers'][$answer]['answer'] = $option;
                        $temp['answers'][] = $q['answers'][$answer];
                    }
                    $correct_response = preg_replace_callback("/(<img[^>]*src *= *[\"']?)([^\"']*)/i", function ($matches) {
                        global $rootURL;

                        return $matches[1] . $rootURL . URL::to('/') . $matches['2'];
                    }, $q['correct_answer']);
                    $temp['correct_answer'] = html_entity_decode($correct_response);
                    $user_respo = preg_replace_callback("/(<img[^>]*src *= *[\"']?)([^\"']*)/i", function ($matches) {
                        global $rootURL;

                        return $matches[1] . $rootURL . URL::to('/') . $matches['2'];
                    }, $q['user_response']);
                    $temp['user_response'] = html_entity_decode($user_respo);
                    $temp['obtained_mark'] = $q['obtained_mark'];
                    $rationale = preg_replace_callback("/(<img[^>]*src *= *[\"']?)([^\"']*)/i", function ($matches) {
                        global $rootURL;

                        return $matches[1] . $rootURL . URL::to('/') . $matches['2'];
                    }, $q['rationale']);
                    $temp['rationale'] = $rationale;
                    $temp['answer_status'] = $q['answer_status'];
                    $data['questions'][] = $temp;
                    ++$count;
                }
            }
        } else {
            $data['flag'] = 'success';
            $data['message'] = 'Attempt not yet completed.';
        }

        echo json_encode($data);
        exit();
    }

    public function postQuestionLiked($action, $qid, $packet_id)
    {
        foreach ($this->user_data as $udata) {
            $uid = $udata['uid'];
        }
        $likes = 0;
        $packet_info = Packet::getPacketInfo($packet_id);
        switch ($action) {
            case 'like':
                $likes = API::updateQALikedCount($uid, 'TRUE', $qid, $packet_id, $packet_info[0]['packet_slug'], $packet_info[0]['packet_title'], $packet_info[0]['feed_slug']);
                break;

            case 'unlike':
                $likes = API::updateQALikedCount($uid, 'FALSE', $qid, $packet_id, $packet_info[0]['packet_slug'], $packet_info[0]['packet_title'], $packet_info[0]['feed_slug']);
                break;

            default:
                return response()->json([
                    'status' => false,
                    'qid' => (int)$qid,
                    'likes' => $likes,
                ]);
                break;
        }

        return response()->json([
            'status' => true,
            'qid' => (int)$qid,
            'likes' => $likes,
        ]);
    }

    /* Pre login APIs starts here, for E-commerce*/

    public function postHome($status = 'ACTIVE')
    {
        $access_token = Input::get('access_token');

        $banners = Banners::getBanners($status);
        $response = [];
        if ($banners) {
            $banner_path = config('app.site_banners_path');
            foreach ($banners as $each) {
                $banner_file_name = $each['mobile_portrait'];
                $banner_url = '';
                if ($banner_file_name != '') {
                    $banner_url = URL::to($banner_path . $banner_file_name);
                    $res = new stdClass();
                    $res->banner_id = $each['id'];
                    $res->banner_name = $each['name'];
                    $res->banner_file = $banner_url;
                    $res->banner_description = $each['description'];
                    $res->banner_order = $each['sort_order'];
                    $response['banners'][] = $res;
                }
            }
        }

        $category_info = Category::getCategoryRelatedProgramCount();

        if (isset($category_info) && !empty($category_info)) {
            foreach ($category_info as $each) {
                if (isset($each['relations']) && (isset($each['relations']['assigned_feeds']) || isset($each['relations']['assigned_products']))) {
                    $res = new stdClass();
                    $res->category_name = $each['category_name'];
                    $res->category_slug = $each['slug'];
                    $res->category_id = $each['category_id'];
                    $category_image = '';
                    if (isset($each['feature_image_file']) && $each['feature_image_file'] != '') {
                        $category_image = URL::to('/portal/theme/default/img/' . $each['feature_image_file']);
                    }
                    $res->category_image = $category_image;
                    $res->program_count = 0;
                    if (isset($each['relations']) && (isset($each['relations']['assigned_feeds']) || isset($each['relations']['assigned_products']))) {
                        $channel_count = !empty($each['relations']['assigned_feeds']) ? count($each['relations']['assigned_feeds']) : 0;
                        $product_count = !empty($each['relations']['assigned_products']) ? count($each['relations']['assigned_products']) : 0;
                        $res->program_count = $channel_count + $product_count;
                    }
                    $response['category_info'][] = $res;
                }
            }
        }

        if ($access_token != '') {
            $noti_count = 0;
            $relations = $announce_list_id = [];
            $result = API::fetchUserInfo($access_token);
            foreach ($result as $udata) {
                $uid = $udata['uid'];
                $relations = $udata['relations'];
            }

            if (isset($relations) && !empty($relations)) {
                if (isset($relations)) {
                    foreach ($relations as $key => $value) {
                        if ($key == 'active_usergroup_user_rel') {
                            $agl = UserGroup::getAnnouncementList($value);
                            foreach ($agl as $key3 => $value3) {
                                if (isset($value3['relations']['usergroup_announcement_rel'])) {
                                    foreach ($value3['relations']['usergroup_announcement_rel'] as $key4 => $value4) {
                                        $announce_list_id[] = $value4;
                                    }
                                }
                            }
                        }
                        if ($key == 'user_feed_rel') {
                            $acfl = Program::getAnnouncementList($value);
                            foreach ($acfl as $key6 => $value6) {
                                if (isset($value6['relations']['contentfeed_announcement_rel'])) {
                                    foreach ($value6['relations']['contentfeed_announcement_rel'] as $key7 => $value7) {
                                        $announce_list_id[] = $value7;
                                    }
                                }
                            }
                        }
                        if ($key == 'user_announcement_rel') {
                            if (!empty($value)) {
                                foreach ($value as $key5 => $value5) {
                                    $announce_list_id[] = $value5;
                                }
                            }
                        }
                    }
                }
            }

            $noti_count += Notification::getNotReadNotificationCount($uid);
            $announce_list_id = array_unique($announce_list_id);
            $announcement_dashboard_number = 2;
            $announcement_dashboard_number = Config::get('app.mobile.announcement_dashboard_number');
            $announcements = Announcement::getNotReadAnnouncementForHead($uid, $announce_list_id, 0, $announcement_dashboard_number);
            if (!is_null($announcements) && !empty($announcements)) {
                $noti_count += Announcement::getNotReadAnnouncementCount($uid, $announce_list_id);
            }
            $response['noti_anno_count'] = $noti_count;
        } else {
            $pub_ann_count = Announcement::getAnnouncementsforscrollCount();
            $response['noti_anno_count'] = $pub_ann_count;
        }

        echo json_encode($response);
        exit();
    }

    public function postCatalog()
    {
        //$category_id[] = (int)trim(Input::get('category_id'));
        // $channel_info=Program::getCategoryRelatedFeedAssets($category_id, $sub_program_slugs=[]);
        $s_data = ['relations', 'parents'];
        $filter = "";
        $proval = "list";
        $p_type = "";

        $filter = (int)Input::get('category_id');
        $product_type = Input::get('program_filter');
        if (isset($filter) && !empty($filter)) {
            $proval = 'basic';
            $filter = [$filter];
        }
        if (isset($product_type) && !empty($product_type)) {
            $p_type[] = $product_type;
        }
        $channel_info = $this->catSer->catWithProgram($proval, $filter, $p_type, $s_data);
        if ($channel_info) {
            foreach ($channel_info[0]['programs'] as $each) {
                $program_cover_media = '';
                if ($each['program_cover_media'] != '') {
                    $program_cover_media = URL::to('media_image/' . $each['program_cover_media'] . '?thumb=180x180');
                }
                $post_count = Packet::getPacketsCountUsingSlug($each['program_slug']);
                $program_sellability = 0;
                if (isset($each['program_sellability']) && $each['program_sellability'] == 'yes') {
                    $program_sellability = 1;
                }
                $res = new stdClass();
                $res->program_title = $each['program_title'];
                $res->program_slug = $each['program_slug'];
                $res->program_sellability = $program_sellability;
                $res->program_id = $each['program_id'];
                $res->program_cover_media = $program_cover_media;
                $res->number_of_posts = $post_count;
                $res->program_type = $each['program_type'];
                // $res->variants=$variant;
                $response['catalog_info'][] = $res;
            }
        }

        $response['flag'] = 'Success';
        $response['message'] = 'Catalog information';
        echo json_encode($response);
        exit();
    }

    public function postProduct()
    {
        $program_slug = trim(Input::get('program_slug'));
        $program_details = Program::getAllProgramByIDOrSlug($type = 'all', $program_slug);
        if ($program_details[0]['program_type'] == 'product') {
            $payumoney = Config::get('app.payment.product_payumoney');
            $cod = Config::get('app.payment.product_cod');
        } elseif ($program_details[0]['program_type'] == 'content_feed') {
            $payumoney = Config::get('app.payment.channel_payumoney');
            $cod = Config::get('app.payment.channel_cod');
        }
        $p_detail = $this->mCourseData($program_slug);
        $learn_now = 0;
        if (!empty($p_detail) && $p_detail['buy_status'] != 'disable' && time() < (int)$p_detail['buy_status']['end_time']) {
            $learn_now = 1;
        }
        $inputdata = [
            'sellable_id' => $program_details[0]['program_id'],
            'sellable_type' => $program_details[0]['program_type']
        ];
        $start_learning_free = 0;
        if ($program_details[0]['program_access'] == 'general_access') {
            $start_learning_free = 1;
        }

        $p_phy_details = $this->pricingSer->getPricing($inputdata);
        if (isset($program_details) && !empty($program_details)) {
            foreach ($program_details as $program_detail) {
                $cat_info = '';
                $categories = Category::getFeedRelatedCategory($program_detail['program_id']);
                if (!empty($categories) && is_array($categories)) {
                    foreach ($categories as $category) {
                        $cat_info .= ucfirst(strtolower($category['category_name'])) . ',';
                    }
                }
                $liked_packets = 0;
                $liked_packets = Packet::getLikedPackets($program_detail['program_slug']);
                $packets = Packet::getPacketsUsingSlug($program_slug, 'title_lower');

                $program_cover_media = '';
                if ($program_detail['program_cover_media'] != '') {
                    $program_cover_media = URL::to('media_image/' . $program_detail['program_cover_media'] . '?thumb=400x400');
                }

                $post_count = Packet::getPacketsCountUsingSlug($program_detail['program_slug']);
                $variant_info = [];
                // if(isset($each['variant']) && $each['variant']!='')
                // {
                //     $variant_info=Program::getVariantPrice($each['variant']);
                // }
                // echo "<pre>"; print_r($variant_info);
                $variant = '';
                $temp = [];
                if (!empty($p_phy_details['vertical']) && is_array($p_phy_details['vertical'])) {
                    foreach ($p_phy_details['vertical'] as $var) {
                        $original_price = $currency_symbol = $price = '';
                        /*$variant_info=explode('Rs',$var['name']);
                        $var_title=$original_price='';
                        if(isset($variant_info[0]))
                        {
                            $var_title=$variant_info[0];
                        }
                        if(isset($variant_info[1]))
                        {
                            $original_price=$variant_info[1];
                        }*/
                        foreach ($var['price'] as $price_detail) {
                            $currency_symbol = $price_detail['currency_code'];
                            $original_price = (int)$price_detail['price'];
                            $price = (int)$price_detail['markprice'];
                        }

                        $temp['name'] = $var['title'];
                        $temp['original_price'] = $original_price;
                        $temp['slug'] = $var['slug'];
                        $temp['currenty_symbol'] = $currency_symbol;
                        $temp['price'] = $price;
                        $variant[] = $temp;
                    }
                } elseif (!empty($p_phy_details['subscription']) && is_array($p_phy_details['subscription'])) {
                    foreach ($p_phy_details['subscription'] as $sub) {
                        $original_price = $currency_symbol = $price = '';
                        /*$variant_info=explode('Rs',$var['name']);
                        $var_title=$original_price='';
                        if(isset($variant_info[0]))
                        {
                            $var_title=$variant_info[0];
                        }
                        if(isset($variant_info[1]))
                        {
                            $original_price=$variant_info[1];
                        }*/
                        foreach ($sub['price'] as $price_detail) {
                            $currency_symbol = $price_detail['currency_code'];
                            $original_price = (int)$price_detail['price'];
                            $price = (int)$price_detail['markprice'];
                        }

                        $temp['name'] = $sub['title'];
                        $temp['original_price'] = $original_price;
                        $temp['slug'] = $sub['slug'];
                        $temp['currenty_symbol'] = $currency_symbol;
                        $temp['price'] = $price;
                        $variant[] = $temp;
                    }
                }

                $program_sellability = 0;
                if (isset($packet['program_sellability']) && $packet['program_sellability'] == 'yes') {
                    $program_sellability = 1;
                }

                $res = new stdClass();
                $res->program_title = $packet['program_title'];
                $res->program_slug = $packet['program_slug'];
                $res->program_id = $packet['program_id'];
                $res->program_sellability = $program_sellability;
                $res->program_description = $packet['program_description'];
                $res->program_type = $program_details[0]['program_type'];
                $res->program_categories = rtrim($cat_info, ',');
                $res->program_cover_media = $program_cover_media;
                $res->number_of_posts = $post_count;
                $res->number_of_liked_posts = $liked_packets;
                $res->variants = $variant;
                $res->learn_now = $learn_now;
                $res->cod = $cod;
                $res->payumoney = $payumoney;
                $res->start_learning_free = $start_learning_free;
                $response['program_info'][] = $res;

                $response['packets'] = [];
                if (!empty($packets)) {
                    $new_packets = $packet_ids = [];
                    foreach ($packets as $packet) {
                        $elements_count = 0;

                        if (isset($packet['elements'])) {
                            $elements_count = count($packet['elements']);
                        }

                        $activity_count = 0;

                        if (($activity_count == 0) || ($elements_count != $activity_count)) {
                            $packet_ids[] = (int)$packet['packet_id'];
                        }
                        if ($activity_count == 0) {
                            $new_packets[] = $packet['packet_id'];
                        }
                    }
                    foreach ($packets as $packet) {
                        $new = $favorite = '';

                        $elements_count = 0;

                        if (isset($packet['elements'])) {
                            $elements_count = count($packet['elements']);
                        }

                        if (in_array($packet['packet_id'], $new_packets)) {
                            $new = 1;
                        }

                        $packet_cover_media = '';
                        if ($packet['packet_cover_media'] != '') {
                            $packet_cover_media = URL::to('media_image/' . $packet['packet_cover_media'] . '?thumb=180x180');
                        }
                        $res = new stdClass();
                        $res->packet_id = $packet['packet_id'];
                        $res->packet_title = html_entity_decode($packet['packet_title']);
                        $res->packet_slug = $packet['packet_slug'];
                        $res->feed_slug = $packet['feed_slug'];
                        $res->packet_description = html_entity_decode($packet['packet_description']);
                        $res->created_by_name = $packet['created_by_name'];
                        $res->packet_cover_media = $packet_cover_media;
                        $res->packet_publish_date = $packet['packet_publish_date'];
                        $res->elements_count = $elements_count;
                        $res->new_label = $new;
                        $res->favorite = $favorite;
                        $response['packets'][] = $res;
                    }
                }
            }
            $response['flag'] = 'Success';
            $response['message'] = 'Program details';
        } else {
            $response['flag'] = 'Success';
            $response['message'] = 'No program found';
        }

        echo json_encode($response);
        exit();
    }

    public function mCourseData($slug)
    {

        $p_detail = [
            'basic' => null,
            'subscription' => null,
            'posts_list' => null,
            'related_program' => null
        ];
        $s_data = ['program_categories', 'program_cover_media', 'tabs', 'program_sellability', 'program_access'];
        $r_data = $this->catSer->getCourse($slug, $s_data);
        if (!empty($r_data)) {
            foreach ($r_data as $key => $value) {
                $p_detail['cat_details'] = '';
                if (isset($value['program_categories'][0])) {
                    $p_cat_id = $value['program_categories'][0];
                    $p_detail['cat_details'] = $this->getCategoryDetails($p_cat_id);
                }
                $p_detail['basic'] = $value;
                if (isset($value['tabs'])) {
                    $p_detail['tabs'] = $value['tabs'];
                } else {
                    $p_detail['tabs'] = null;
                }
                //$p_detail['subscription'] = $this->getSubscription($value);
                // $p_detail['posts_list'] = $this->getPostPreview($value);
                $p_detail['buy_status'] = $this->getBuyStatus($value['program_id']);
            }
        }
        return $p_detail;
    }

    public function getBuyStatus($p_id)
    {

        foreach ($this->user_data as $udata) {
            $u_id = $udata['uid'];
        }

        $data = $this->acServ->enrollUserByProduct($u_id, [$p_id]);
        if (!empty($data)) {
            return $data;
        }
        return 'disable';
    }

    private function getCategoryDetails($id)
    {
        return $this->catSer->getCategoryDetails($id);
    }

    public function postSiteAnnouncements()
    {
        $per_page = Config::get('app.mobile.announcements_per_page');
        $page = Input::get('page');
        $skip = $per_page * $page;
        $user_id = 0;
        $announce_list_id = [];
        if (Input::get('user_id')) {
            $user_id = Input::get('user_id');
            $uids = [];
            $uids[] = (int)Input::get('user_id');
            $user_data = User::getUserDetailsUsingUserIDs($uids);
            $relations = $user_data[0]['relations'];

            if (isset($relations)) {
                foreach ($relations as $key => $value) {
                    if ($key == 'active_usergroup_user_rel') {
                        $agl = UserGroup::getAnnouncementList($value);
                        foreach ($agl as $key3 => $value3) {
                            if (isset($value3['relations']['usergroup_announcement_rel'])) {
                                foreach ($value3['relations']['usergroup_announcement_rel'] as $key4 => $value4) {
                                    $announce_list_id[] = $value4;
                                }
                            }
                        }
                    }
                    if ($key == 'user_feed_rel') {
                        $acfl = Program::getAnnouncementList($value);
                        foreach ($acfl as $key6 => $value6) {
                            if (isset($value6['relations']['contentfeed_announcement_rel'])) {
                                foreach ($value6['relations']['contentfeed_announcement_rel'] as $key7 => $value7) {
                                    $announce_list_id[] = $value7;
                                }
                            }
                        }
                    }
                    if ($key == 'user_announcement_rel') {
                        if (!empty($value)) {
                            foreach ($value as $key5 => $value5) {
                                $announce_list_id[] = $value5;
                            }
                        }
                    }
                }
            }
        }

        $pub_announce_ids = API::getPublicAnnouncementIDs();
        $announce_list_id = array_merge($announce_list_id, $pub_announce_ids);

        $announce_list_id = array_unique($announce_list_id);

        $announcements = Announcement::getAnnouncementsforscroll($announce_list_id, $skip, $per_page);

        $response['announcements'] = [];
        if (isset($announcements)) {
            foreach ($announcements as $each) {
                $for_media = [];
                $for_media['forret'] = '';
                $for_media['type'] = '';
                $for_media['file'] = '';
                $for_media['video_cover_image'] = '';
                $new = 1;
                if (isset($each['relations']['active_media_announcement_rel']) && !empty($each['relations']['active_media_announcement_rel'])) {
                    $for_media = API::getMediaDetails($each['relations']['active_media_announcement_rel'][0]);
                }
                // print_r($for_media);
                if (isset($user_id) && $user_id != 0) {
                    if (isset($each['readers']) && in_array($user_id, $each['readers']['user'])) {
                        $new = 0;
                    }
                }

                $created_by_name = '';
                if (isset($each['created_by_name'])) {
                    $created_by_name = $each['created_by_name'];
                }
                $announcement_content = preg_replace_callback("/(<img[^>]*src *= *[\"']?)([^\"']*)/i", function ($matches) {
                    global $rootURL;

                    return $matches[1] . $rootURL . URL::to('/') . $matches['2'];
                }, $each['announcement_content']);

                $find = ['href='];
                $replace = ['link='];

                $announcement_content = str_replace($find, $replace, $announcement_content);

                $res = new stdClass();
                $res->announcement_id = $each['announcement_id'];
                $res->announcement_title = html_entity_decode($each['announcement_title']);
                $res->announcement_type = $each['announcement_type'];
                $res->announcement_media = $for_media['forret'];
                $res->media_type = $for_media['type'];
                $res->file = $for_media['file'];
                $res->video_cover_image = $for_media['video_cover_image'];
                $res->new = $new;
                $res->announcement_publish_date = $each['schedule'];
                $res->announcement_content = html_entity_decode($announcement_content);
                $res->announcement_device = 'both';
                $res->created_by = $each['created_by'];
                $res->created_by_name = $created_by_name;
                $response['announcements'][] = $res;
            }
        }
        $response['flag'] = trans('mobile.success');
        $response['message'] = 'Announcements Information';
        //  print_r($response); die;
        echo json_encode($response);
        exit;
    }

    public function postSamplePost()
    {
        $post_slug = trim(Input::get('packet_slug'));
        $jwplayer_key = Config::get('app.jwplayer.key');
        $element_id = null;
        $element_type = null;
        $page = 0;
        if (Input::get('element_id') && Input::get('element_type') && Input::get('page')) {
            $element_id = Input::get('element_id');
            $element_type = Input::get('element_type');
            $page = Input::get('page');
        }
        $packet = Packet::getPacket($post_slug);

        $first_element = 0;
        if (!empty($packet[0]['elements']) && isset($packet[0]['elements'])) {
            $elements = $packet[0]['elements'];
            $element_count = count($elements);
            $elements = array_values(array_sort($elements, function ($value) {
                return $value['order'];
            }));
            $feed = Program::pluckFeedName($packet[0]['feed_slug']);

            if ($page == 0) {
                $first_element = 1;
                $previous_element_id = $previous_element_type = $next_element_id = $next_element_type = '';
                $element_id = $elements[$page]['id'];
                $element_type = $elements[$page]['type'];
                if ($element_count > 1) {
                    $next_element_id = $elements[$page + 1]['id'];
                    $next_element_type = $elements[$page + 1]['type'];
                }
            } elseif ($page == ($element_count - 1)) {
                $previous_element_id = $elements[$page - 1]['id'];
                $previous_element_type = $elements[$page - 1]['type'];
                $element_id = $elements[$page]['id'];
                $element_type = $elements[$page]['type'];
                $next_element_id = $next_element_type = '';
            } else {
                $previous_element_id = $elements[$page - 1]['id'];
                $previous_element_type = $elements[$page - 1]['type'];
                $element_id = $elements[$page]['id'];
                $element_type = $elements[$page]['type'];
                $next_element_id = $elements[$page + 1]['id'];
                $next_element_type = $elements[$page + 1]['type'];
            }
            $element_detail = API::elementDetails($element_id, $element_type);
        } else {
            $response['flag'] = 'Success';
            $response['message'] = 'No sample posts found';
            echo json_encode($response);
            exit;
        }
        $response['packet'] = [];
        if (!empty($packet)) {
            foreach ($packet as $each) {
                $sequential = $qanda = 0;
                if ($each['sequential_access'] == 'yes') {
                    $sequential = 1;
                }
                if ($each['qanda'] == 'yes') {
                    $qanda = 1;
                }

                $res = new stdClass();
                $res->packet_id = $each['packet_id'];
                $res->packet_title = html_entity_decode($each['packet_title']);
                $res->packet_slug = $each['packet_slug'];
                $res->feed_slug = $each['feed_slug'];
                $res->feed_name = html_entity_decode($feed[0]['program_title']);
                $res->packet_description = html_entity_decode($each['packet_description']);
                $res->packet_publish_date = $each['packet_publish_date'];
                $res->sequential_access = $sequential;
                $res->qanda = $qanda;
                $res->total_ques_public = $each['total_ques_public'];
                $res->total_ques_private = $each['total_ques_private'];
                $response['packet'][] = $res;
                $response['flag'] = trans('mobile.success');
                $response['message'] = 'Packet Information';
            }
        } else {
            $response['flag'] = trans('mobile.success');
            $response['message'] = 'Packet Not found';
        }

        $response['current_element'] = [];
        $total = $obtained_mark = $last_attempt_percentage = 0;
        $event_name = $event_type = $event_cycle = $event_description = $event_type = $speakers = $event_host_name = $location = '';
        $event_id = $event_cycle = $event_host_id = $start_date_label = $start_time_label = $start_time = $end_time = $created_at = $end_date_label = $end_time_label = 0;
        if (isset($element_detail)) {
            // print_r($element_detail); die;
            $res = new stdClass();
            $res->element_id = $element_detail['asset']['id'];
            $res->element_title = html_entity_decode($element_detail['asset']['name']);
            $res->element_description = html_entity_decode($element_detail['asset']['description']);
            //$res->element_short_description = $element_detail['asset']['short_description'];
            $res->element_type = $element_detail['asset']['type'];
            $res->element_asset_type = $element_detail['asset']['asset_type'];
            $res->first_element = $first_element;
            $res->jwplayer_key = $jwplayer_key;
            $liked = $quiz_id = $quiz_attempts = $quiz_end_time = $quiz_start_time = $practice_quiz = $quiz_duration = $total_mark = 0;
            $video_cover_image = $mime_type = $file_size = '';

            foreach ($this->user_data as $u_data) {
                $uid = $u_data['uid'];
            }
            if (isset($element_detail['asset']['users_liked']) && in_array($uid, $element_detail['asset']['users_liked'])) {
                $liked = 1;
            }

            switch ($element_detail['asset']['type']) {
                case 'video':
                    $asset = API::getDAMSAssetsUsingAutoID((int)$element_detail['asset']['id']);
                    $asset = $asset[0];

                    $type = [
                        'element_type' => $element_type,
                    ];
                    $asset_path = array_merge($asset, $type);

                    if ($asset['asset_type'] == 'file') {
                        if (isset($asset['akamai_details'])) {
                            if (isset($asset['akamai_details']['delivery_html5_url'])) {
                                $asset_path = $asset['akamai_details']['delivery_html5_url'];
                                $video_cover_image = URL::to('/media_image/' . $asset['_id']);
                                $mime_type = $asset['mimetype'];
                                $file_size = $asset['file_size'];
                            } elseif (isset($asset['akamai_details']['stream_success_html5'])) {
                                $asset_path = $asset['akamai_details']['stream_success_html5'];
                                $video_cover_image = URL::to('/media_image/' . $asset['_id']);
                                $mime_type = $asset['mimetype'];
                                $file_size = $asset['file_size'];
                            } elseif (!isset($asset['akamai_details']['code']) || $asset['akamai_details']['code'] != 200) {
                                $asset_path = 'Error in syncing the file. Please contact';
                            } else {
                                $asset_path = 'File is being processed please wait.';
                            }
                            //elseif(isset($asset['akamai_details']['code']) && $asset['akamai_details']['code'] == 200 && isset($asset['akamai_details']['stream_success_flash'])){}
                        } else {
                            $asset_path = 'File is not synced with Video Server';
                        }
                    } else {
                        $asset_path = $asset['url'];
                    }
                    break;
                case 'image':
                    $asset = API::getDAMSAssetsUsingAutoID((int)$element_detail['asset']['id']);
                    $asset = $asset[0];
                    if ($element_detail['asset']['asset_type'] == 'file') {
                        $asset_path = URL::to('media_image/' . $element_detail['asset']['_id']);
                    } else {
                        $asset_path = $asset['url'];
                    }
                    break;
                case 'document':
                    $asset = API::getDAMSAssetsUsingAutoID((int)$element_detail['asset']['id']);
                    $asset = $asset[0];
                    if ($element_detail['asset']['asset_type'] == 'file') {
                        $asset_path = URL::to('media_image/' . $element_detail['asset']['_id']);
                    } else {
                        $asset_path = $element_detail['asset']['url'];
                    }
                    break;
                case 'audio':
                    $asset = API::getDAMSAssetsUsingAutoID((int)$element_detail['asset']['id']);
                    $asset = $asset[0];
                    if ($element_detail['asset']['asset_type'] == 'file') {
                        $asset_path = URL::to('media_image/' . $element_detail['asset']['_id']);
                    } else {
                        $asset_path = $element_detail['asset']['url'];
                    }
                    break;
                case 'assessment':
                    $asset = $element_detail['asset'];
                    // print_r($asset['id']); die;
                    $quiz = QuizAttempt::where('quiz_id', '=', (int)$asset['id'])
                        ->where('user_id', '=', (int)$uid)
                        ->orderBy('started_on', 'desc')
                        ->get();

                    $attempts = $quiz->where('status', 'CLOSED');
                    // $attempts = QuizAttempt::where('quiz_id', '=', (int) $element_detail['asset']['id'])
                    // ->where('user_id', '=', (int) $uid)
                    // ->where('status', 'CLOSED')
                    // ->orderBy('started_on', 'desc')
                    // ->get();
                    //echo "<pre>"; print_r($attempts); die;
                    if (!empty($attempts->first())) {
                        $total = $attempts->first()->total_mark;
                        $obtained_mark = $attempts->first()->obtained_mark;
                        $last_attempt_percentage = round(($obtained_mark / $total) * 100) . '%';
                    }
                    $attempted_count = 0;
                    $attempts_left = '';
                    if (!empty($attempts)) {
                        $attempted_count = count($quiz);
                    }
                    if ($asset['attempts'] > 0) {
                        $attempts_left = $asset['attempts'] - $attempted_count;
                    }
                    // $opened_attempt = QuizAttempt::where('quiz_id', '=', (int) $element_detail['asset']['id'])
                    // ->where('user_id', '=',(int)$uid)
                    // ->where('status','OPENED')
                    // ->orderBy('started_on','desc')
                    // ->get();
                    $opened_attempt = $quiz->where('status', 'OPENED');

                    $continue_last_attempt = 0;
                    foreach ($opened_attempt as $attempt) {
                        if ($attempt->status == 'OPENED') {
                            $continue_last_attempt = 1;
                        }
                    }
                    $res->continue_last_attempt = $continue_last_attempt;
                    $asset_path = URL::to('api/quiz/attempt-url');
                    $quiz_id = $element_detail['asset']['id'];
                    $quiz_attempts = $element_detail['asset']['attempts'];
                    $quiz_start_time = strtotime($element_detail['asset']['start_time']);
                    $quiz_end_time = strtotime($element_detail['asset']['end_time']);
                    $quiz_duration = $element_detail['asset']['duration'];
                    $practice_quiz = $element_detail['asset']['practice_quiz'];
                    $total_mark = $element_detail['asset']['total_mark'];
                    $res->the_attempt = $element_detail['asset']['the_attempt'];
                    $res->whether_correct = $element_detail['asset']['whether_correct'];
                    $res->marks = $element_detail['asset']['marks'];
                    $res->rationale = $element_detail['asset']['rationale'];
                    $res->correct_answer = $element_detail['asset']['correct_answer'];
                    $res->attempts_left = $attempts_left;
                    $last_attempt_mark = $obtained_mark;
                    $last_attempt_mark_percentage = $last_attempt_percentage;
                    break;
                case 'event':
                    $event_description = preg_replace_callback("/(<img[^>]*src *= *[\"']?)([^\"']*)/i", function ($matches) {
                        global $rootURL;

                        return $matches[1] . $rootURL . URL::to('/') . $matches['2'];
                    }, $element_detail['asset']['description']);

                    $find = ['href='];
                    $replace = ['link='];

                    $event_description = str_replace($find, $replace, $event_description);

                    $asset = $element_detail['asset'];
                    // print_r($asset); die;
                    $event_id = $element_detail['asset']['id'];
                    $event_name = html_entity_decode($element_detail['asset']['name']);
                    $event_type = $element_detail['asset']['event_type'];
                    $event_cycle = $element_detail['asset']['event_cycle'];
                    $event_description = html_entity_decode($event_description);
                    $event_type = $element_detail['asset']['event_type'];
                    $event_cycle = $element_detail['asset']['event_cycle'];
                    $speakers = $element_detail['asset']['speakers'];
                    $event_host_id = $element_detail['asset']['event_host_id'];
                    $event_host_name = $element_detail['asset']['event_host_name'];
                    $start_date_label = $element_detail['asset']['start_date_label'];
                    $start_time_label = $element_detail['asset']['start_time_label'];
                    $start_time = strtotime($element_detail['asset']['start_time']);
                    $end_date_label = $element_detail['asset']['end_date_label'];
                    $end_time = strtotime($element_detail['asset']['end_time']);
                    $end_time_label = $element_detail['asset']['end_time_label'];
                    $location = $element_detail['asset']['location'];
                    $created_at = $element_detail['asset']['created_at'];
                    $asset_path = '';
                    break;
                default:
                    $asset_path = '';
                    break;
            }
            // $array = array(
            //         'module' => 'element',
            //         'action' => 'view',
            //         'module_name' => html_entity_decode($asset['name']),
            //         'module_id' => (int) $asset['id'],
            //         'element_type' => $element_type,
            //         'packet_id' => (int) $packet[0]['packet_id'],
            //         'packet_name' => html_entity_decode($packet[0]['packet_title']),
            //         'feed_id' => (int) $feed[0]['program_id'],
            //         'feed_name' => html_entity_decode($feed[0]['program_title']),
            //         'url' => 'program/packet/'.$slug,
            //     );
            //          // print_r($array); die;
            //        API::getLogActivity($array, $uid);

            $res->asset_type = $element_type;
            $res->asset = $asset_path;
            $res->video_cover_image = $video_cover_image;
            $res->mime_type = $mime_type;
            $res->file_size = $file_size;
            $res->next_element_id = $next_element_id;
            $res->next_element_type = $next_element_type;
            $res->page = $page;
            $res->quiz_id = $quiz_id;
            $res->quiz_attempts = $quiz_attempts;
            $res->quiz_start_time = $quiz_start_time;
            $res->quiz_end_time = $quiz_end_time;
            $res->quiz_duration = $quiz_duration;
            $res->practice_quiz = $practice_quiz;
            $res->total_mark = $total_mark;
            $res->last_attempt_mark = $obtained_mark;
            $res->last_attempt_mark_percentage = $last_attempt_percentage;
            $res->liked = $liked;
            $res->previous_element_id = $previous_element_id;
            $res->previous_element_type = $previous_element_type;
            $res->event_id = $event_id;
            $res->event_name = $event_name;
            $res->event_type = $event_type;
            $res->event_cycle = $event_cycle;
            $res->event_description = $event_description;
            $res->event_type = $event_type;
            $res->event_cycle = $event_cycle;
            $res->speakers = $speakers;
            $res->event_host_id = $event_host_id;
            $res->event_host_name = $event_host_name;
            $res->start_date_label = $start_date_label;
            $res->start_time_label = $start_time_label;
            $res->start_time = $start_time;
            $res->end_date_label = $end_date_label;
            $res->end_time_label = $end_time_label;
            $res->end_time = $end_time;
            $res->location = $location;
            $res->created_at = $created_at;

            $response['current_element'][] = $res;
        }
        // print_r($response); die;
        echo json_encode($response);
        exit;
    }

    public function postSamplePostTimeline()
    {

        $post_slug = Input::get('packet_slug');
        $packet = Packet::getPacket($post_slug);

        $i = 0;
        if (!empty($packet[0]['elements'])) {
            $response = [];

            $sequential = $page = $element_count = 0;
            if ($packet[0]['sequential_access'] == 'yes') {
                $sequential = 1;
            }
            $response['sequential'] = $sequential;
            $elements = $packet[0]['elements'];
            $element_count = count($elements);
            $response['elements_count'] = $element_count;
            $elements = array_values(array_sort($elements, function ($value) {
                return $value['order'];
            }));

            $element_asset = '';

            foreach ($elements as $element) {
                if ($page == 0) {
                    $previous_element_id = $previous_element_type = $next_element_id = $next_element_type = '';
                    //$element_id=$elements[$page]['id'];
                    //$element_type=$elements[$page]['type'];
                    if ($element_count > 1) {
                        $next_element_id = $elements[$page + 1]['id'];
                        $next_element_type = $elements[$page + 1]['type'];
                    }
                } elseif ($page == ($element_count - 1)) {
                    $previous_element_id = $elements[$page - 1]['id'];
                    $previous_element_type = $elements[$page - 1]['type'];
                    //$element_id=$elements[$page]['id'];
                    //$element_type=$elements[$page]['type'];
                    $next_element_id = $next_element_type = '';
                } else {
                    $previous_element_id = $elements[$page - 1]['id'];
                    $previous_element_type = $elements[$page - 1]['type'];
                    //$element_id=$elements[$page]['id'];
                    //$element_type=$elements[$page]['type'];
                    $next_element_id = $elements[$page + 1]['id'];
                    $next_element_type = $elements[$page + 1]['type'];
                }

                switch ($element['type']) {
                    case 'media':
                        $element_asset = API::getDAMSAssetsUsingAutoID((int)$element['id']);
                        $element_asset = $element_asset[0];
                        $name = $element_asset['name'];
                        $elem_type = $element_asset['type'];
                        // $size='Type:'.$element_asset['type'];
                        $type = [
                            'element_type' => $element['type'],
                        ];
                        $element_asset = array_merge($element_asset, $type);
                        break;

                    case 'assessment':
                        $element_asset = Quiz::getQuizAssetsUsingAutoID($element['id']);
                        $element_asset = $element_asset[0];
                        $name = $element_asset['quiz_name'];
                        $elem_type = $element['type'];
                        // $size='Dur:'.$element_asset['duration'];
                        $class = 'fa-edit';
                        $type = [
                            'element_type' => $element['type'],
                            'id' => $element_asset['quiz_id'],
                        ];
                        $element_asset = array_merge($element_asset, $type);
                        break;

                    case 'event':
                        $element_asset = Event::getEventsAssetsUsingAutoID($element['id']);
                        // print_r($element_asset); die;
                        $element_asset = $element_asset[0];
                        $name = $element_asset['event_name'];
                        $elem_type = 'event';
                        // $size='Type:'.$element_asset['event_type'];
                        $class = 'fa-calendar';
                        break;
                }
                // print_r($element_asset); die;
                $activity = [];
                $element_status = '';
                $element_visible = 'TRUE';
                // $activity = API::pluckElementActivity($uid, $packet[0]['packet_id'], $element['id'], $element['type']);
                // $element_visible = 'FALSE';
                // if (!empty($activity)) {
                //     $element_status = 'WATCHED';
                // } else {
                //     if ($i == 0) {
                //         $element_visible = 'TRUE';
                //     }
                //     $element_status = 'NOTWATCHED';
                //     ++$i;
                // }

                $res = new stdClass();
                $res->element_id = $element['id'];
                $res->element_type = $element['type'];
                $res->element_title = html_entity_decode($name);
                $res->element_status = $element_status;
                $res->element_order = $element['order'];
                $res->element_visible = $element_visible;
                $res->type = $elem_type;
                $res->next_element_id = $next_element_id;
                $res->next_element_type = $next_element_type;
                $res->page = $page;
                $res->previous_element_id = $previous_element_id;
                $res->previous_element_type = $previous_element_type;
                $response['elements'][] = $res;

                $page = $page + 1;
            }
            // print_r($response); die;
            $response['flag'] = 'success';
            $response['message'] = 'Packet Timeline Information.';
        } else {
            $response['flag'] = 'success';
            $response['message'] = 'No Elements found.';
        }
        //print_r($response); die;
        echo json_encode($response);
        exit;
    }

    public function postUserRegistration()
    {
        //$uid_by_username=User::where('username','=',Input::get('username'))->value('uid');
        $uid_by_email = User::where('email', '=', Input::get('email'))->value('uid');
        if (isset($uid_by_email) && !empty($uid_by_email) && $uid_by_email > 0) {
            $response['flag'] = 'fail';
            $response['message'] = trans('mobile.email_exits');
            echo json_encode($response);
            exit;
        } else {
            User::getRegisterUser(Input::all(), $app_registration = 1);
            $this->postUserlogin();

            $response['flag'] = 'Success';
            $response['message'] = 'Registered successfully';
        }
    }

    public function postMyOrders($product_type = 'product', $order_id = '')
    {
        foreach ($this->user_data as $udata) {
            $user_id = $udata['uid'];
        }
        if ($order_id == '') {
            $per_page = Config::get('app.mobile.orders_per_page');
            //$orders=TransactionDetail::OrderInformation($product_type,$user_id,$per_page,$page,$order_id);
            $orders = $this->ordSer->getOrderPagination($user_id);

            if (!isset($orders)) {
                return ["status" => "success", "message" => "No more records"];
            }
            foreach ($orders as $order) {
                $res = new stdClass();
                $res->program_cover_image = '';
                if ($order['items_details']['p_img'] != '') {
                    $res->program_cover_image = URL::to('media_image/' . $order['items_details']['p_img'] . '?thumb=180x180');
                }
                $res->program_slug = $order['items_details']['p_slug'];
                $res->program_type = 'product';
                $res->program_title = $order['items_details']['p_tite'];
                $res->created_at = '';
                $res->updated_at = '';
                $res->variant_title = $order['items_details']['s_title'];
                $res->variant_slug = $order['items_details']['s_slug'];
                $res->item_count = 1;
                $res->original_price = $order['items_details']['price'];
                $res->discounted_price = $order['items_details']['m_price'];
                $res->order_amount = $order['sub_total'];
                $res->discount_amount = $order['discount'];
                $res->net_amount = $order['net_total'];
                $res->order_id = $order['order_id'];
                $res->full_order_id = $order['order_label'];
                $res->order_status = $order['status'];
                $res->payment_type = $order['payment_type'];
                $orders_list['orders_list'][] = $res;
            }

            return json_encode($orders_list);
        } else {
            $order = $this->ordSer->getOrder($order_id);

            $res = new stdClass();
            $addr = [];
            $res->program_cover_image = '';
            if ($order['items_details']['p_img'] != '') {
                $res->program_cover_image = URL::to('media_image/' . $order['items_details']['p_img'] . '?thumb=160x160');
            }
            $res->customer = $order['user_details']['firstname'] . " " . $order['user_details']['lastname'];
            $res->program_slug = $order['items_details']['p_slug'];
            $res->program_type = 'product';
            $res->program_title = $order['items_details']['p_tite'];
            $res->created_at = $order['created_at'];
            $res->updated_at = $order['updated_at'];
            $res->variant_title = $order['items_details']['s_title'];
            $res->variant_slug = $order['items_details']['s_slug'];
            $res->item_count = 1;
            $res->original_price = $order['items_details']['price'];
            $res->discounted_price = $order['items_details']['m_price'];
            $res->order_amount = $order['sub_total'];
            $res->discount_amount = $order['discount'];
            $res->net_amount = $order['net_total'];
            $res->order_id = $order['order_id'];
            $res->full_order_id = $order['order_label'];
            $res->order_status = $order['status'];
            $res->payment_type = $order['payment_type'];
            $addr['firstname'] = $order['address']['fullname'];
            $addr['street'] = $order['address']['address'];
            $addr['post_code'] = $order['address']['post_code'];
            $addr['city'] = $order['address']['city'];
            $addr['country'] = $order['address']['country'];
            $addr['state'] = $order['address']['state'];
            $res->billing_address[] = $addr;
            $res->contact = $order['address']['contact_no'];
            $order_detail['orders_details'][] = $res;

            return json_encode($order_detail);
        }
    }

    public function getBuy($p_slug = null, $s_slug = null)
    {
        foreach ($this->user_data as $udata) {
            $u_id = $udata['uid'];
            $u_email = $udata['email'];
            $u_timezone = $udata['timezone'];
        }
        $data = $this->mOrderData($p_slug, $s_slug);
        if ($data['priceService'] == "free") {
            $data = ["p_slug" => $p_slug,
                "s_slug" => $s_slug,
                "fullname" => "",
                "address" => "",
                "region_state" => "",
                "city" => "",
                "country" => "",
                "post_code" => "",
                "telephone" => "",
                "promo_code" => "",
                "d_hidden" => "",
                "net_total_input" => "0",
                "h_net_total" => "",
                "pay_way" => "FREE"];
            //$u_id = Auth::user()->uid;
            // $u_email = Auth::user()->email;
            $p_data = $this->mOrderData($p_slug, $s_slug);
            $orderID = $this->ordSer->placeOrder($data, $u_id, $p_data);
            $o_data = $this->ordSer->getOrder($orderID);
            Common::sendMail(
                'emails.order',
                ['o_data' => $o_data, 'user_timezone' => $u_timezone],
                "Order Confirmation",
                $u_email
            );
            $url = $this->getSubscribe($p_slug, $s_slug, $o_data);
            return ["status" => "success", "payment" => "paid"];
        }
        return ["status" => "success", "payment" => "pending"];
    }

    public function getSubscribe($p_slug = null, $s_slug = null, $order = null)
    {
        $pay_way = $order['payment_type'];
        foreach ($this->user_data as $udata) {
            $u_id = $udata['uid'];
        }
        if (!empty($p_slug) && !empty($s_slug)) {
            $s_data = [
                's_user' => null,
                's_pgr' => null,
                's_subs' => null,
                's_price' => null
            ];
            $p_data = $this->catSer->getCourse($p_slug);
            $u_data = [
                'u_id' => $u_id,
                'p_id' => $p_data[0]['program_id'],
                'p_type' => $p_data[0]['program_type'],
                'p_slug' => $p_data[0]['program_slug'],
                'p_title' => $p_data[0]['program_title'],
                's_slug' => $s_slug
            ];


            $s_data = $this->pricingSer->subscribeUser($u_data);
            $this->acServ->enrollUser($u_data);
            return;
        } else {
            return 'osummary';
        }
    }

    public function postApplyCoupon()
    {
        foreach ($this->user_data as $udata) {
            $u_id = $udata['uid'];
        }

        $cCode = Input::get('promo_code');
        $price = Input::get('price');
        $discount = $this->promoServ->valPromoCode($cCode, $price, $u_id);

        $data = [];
        if (empty($discount)) {
            $data['status'] = "Failed,Invalid Coupon";
            $data['info'] = "Invalid Coupon";
            return json_encode($data);
        } elseif ($discount == 'promocode_used') {
            $data['status'] = "Failed, Promocode is already used.";
            $data['info'] = "Promocode is already used.";
            return json_encode($data);
        }

        $res = new stdClass();
        $res->unit_price = (int)$price;
        $res->grand_total = (int)$price - (int)$discount;
        $res->discount_availed = (int)$discount;
        $data['info'][] = $res;
        $data['status'] = "Success";
        return json_encode($data);
    }

    public function postPay()
    {
        $u_timezone = 'Asia/Kolkata';
        foreach ($this->user_data as $udata) {
            $u_id = $udata['uid'];
            $u_email = $udata['email'];
            $u_timezone = $udata['timezone'];
        }

        $p_slug = Input::get('feed_slug');
        $s_slug = Input::get('variant_slug');
        $p_title = Input::get('product_title');
        $payment_type = Input::get('payment_type');
        $promo_code = Input::get('promo_code');
        $price = Input::get('price');
        $street = Input::get('street');
        $post_code = Input::get('pincode');
        $city = Input::get('city');
        $country = Input::get('country');
        $state = Input::get('state');
        $phone = Input::get('phone');
        $firstname = Input::get('firstname');
        $email = $u_email;

        $data = ["p_slug" => $p_slug,
            "s_slug" => $s_slug,
            "fullname" => $firstname,
            "address" => $street,
            "region_state" => $state,
            "city" => $city,
            "country" => $country,
            "post_code" => $post_code,
            "telephone" => $phone,
            "promo_code" => $promo_code,
            "d_hidden" => "",
            "net_total_input" => $price,
            "h_net_total" => "",
            "pay_way" => $payment_type];

        //$u_id = Auth::user()->uid;
        //$u_email = Auth::user()->email;
        $p_data = $this->mOrderData($p_slug, $s_slug);
        $orderID = $this->ordSer->placeOrder($data, $u_id, $p_data);
        $o_data = $this->ordSer->getOrder($orderID);
        Common::sendMail(
            'emails.order',
            ['o_data' => $o_data, 'user_timezone' => $u_timezone],
            "Order Details - " . config('app.site_name'),
            $u_email
        );
        if ($payment_type == 'PayUMoney') {
            $order_id = Crypt::encrypt($orderID);

            $payment_url = URL::to('/checkout/payment/' . $order_id);
            return json_encode(['payment_url' => $payment_url]);
        }
        return ["status" => "success", "payment" => "success"];
    }

    private function mOrderData($p_slug, $s_slug)
    {
        foreach ($this->user_data as $udata) {
            $u_id = $udata['uid'];
        }
        $p_data = $this->catSer->getCourse($p_slug);
        $tempdata = [];
        if (!empty($p_data)) {
            foreach ($p_data as $key => $value) {
                if ($value['program_type'] === "product") {
                    $data['sellable_id'] = $value['program_id'];
                    $data['sellable_type'] = $value['program_type'];
                    $s_data = $this->pricingSer->getVerticalBySlug($data, $s_slug);
                } else {
                    $s_data = $this->pricingSer->getSubscriptionDetails($value['program_id'], $value['program_type'], $s_slug);
                }
                $tempdata['p_tite'] = $value['program_title'];
                $tempdata['p_slug'] = $value['program_slug'];
                $tempdata['p_img'] = $value['program_cover_media'];
                $tempdata['s_title'] = $s_data['title'];
                $tempdata['s_slug'] = $s_data['slug'];
                $tempdata['ordered_from'] = 'mobile';
                $duration_type = "Days";
                if (isset($s_data['duration_type'])) {
                    if ($s_data['duration_type'] === "DD") {
                        $duration_type = "Days";
                    } elseif ($s_data['duration_type'] === "MM") {
                        $duration_type = "Months";
                    } elseif ($s_data['duration_type'] === "WW") {
                        $duration_type = "Weeks";
                    } else {
                        $duration_type = "Years";
                    }
                }
                if (isset($s_data['duration_count'])) {
                    $tempdata['s_duration'] = $s_data['duration_count'] . " " . $duration_type;
                }
                if (isset($s_data['price']) && !empty($s_data['price'])) {
                    foreach ($s_data['price'] as $price) {
                        if ($price['currency_code'] === $this->pay_currency) {
                            $tempdata['priceService'] = "paid";

                            $tempdata['price'] = $price['price'];

                            $tempdata['m_price'] = $price['markprice'];
                        }
                    }
                } else {
                    $tempdata['priceService'] = "free";
                    $tempdata['price'] = "0";
                    $tempdata['m_price'] = "0";
                }
                $tempdata['d_addrs'] = $this->ordSer->getDefaultAddress($u_id);
            }
        }
        return $tempdata;
    }

    public function postAssignProduct($product_id = 0)
    {
        foreach ($this->user_data as $udata) {
            $uid = $udata['uid'];
        }
        User::addUserRelation($uid, ['user_feed_rel'], $product_id);
        Program::addFeedRelation($product_id, ['active_user_feed_rel'], $uid);
        return ["flag" => "success", "message" => "Assigned successfully"];
    }

    public function getSocialLogin()
    {
        $provider = 'facebook';
        return \Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        //notice we are not doing any validation, you should do it
        //
        if (Input::has('error')) {
            return redirect('/');
        }

        if (Input::has('error_code')) {
            return redirect('/');
        }
        $user = \Socialite::driver($provider)->user();

        // storing data to our use table
        switch ($provider) {
            case 'facebook':
                $data = [
                    'email' => $user->getEmail(),
                    'firstname' => $user->user['first_name'],
                    'lastname' => $user->user['last_name'],
                    'provider' => $provider
                ];
                break;
            case 'google':
                $data = [
                    'email' => $user->getEmail(),
                    'firstname' => $user->user['name']['givenName'],
                    'lastname' => $user->user['name']['familyName'],
                    'provider' => $provider
                ];
                break;
        }

        User::registerSocialite($data);

        //logging them in
        $authentication = [
            'email' => $user->getEmail()
        ];

        Auth::login(User::firstOrCreate($authentication));
        //after login redirecting to home page
        $uid = Auth::user()->uid;
        $array = [
            'module' => 'general',
            'action' => 'Sign In',
            'module_name' => '',
            'module_id' => '',
            'url' => Request::path(),
        ];
        MyActivity::getInsertActivity($array);
        User::getUpdateLastLogin($uid);
        User::updateSessionID($uid);
        return redirect('/');
    }

    public function getContactUs()
    {
        $contact_info = SiteSetting::module('Contact Us');
        return ["flag" => "success", "company_name" => $contact_info['setting']['company_name'], "address" => $contact_info['setting']['address'],
            'mobile' => $contact_info['setting']['mobile_no'], 'phone' => $contact_info['setting']['phone'], 'email' => $contact_info['setting']['email']];
    }

    private function decodeHttpResponseCode($code = null)
    {
        if ($code !== null && headers_sent() == false) {
            switch ($code) {
                case 100:
                    $text = 'Continue';
                    break;
                case 101:
                    $text = 'Switching Protocols';
                    break;
                case 200:
                    $text = 'OK';
                    break;
                case 201:
                    $text = 'Created';
                    break;
                case 202:
                    $text = 'Accepted';
                    break;
                case 203:
                    $text = 'Non-Authoritative Information';
                    break;
                case 204:
                    $text = 'No Content';
                    break;
                case 205:
                    $text = 'Reset Content';
                    break;
                case 206:
                    $text = 'Partial Content';
                    break;
                case 300:
                    $text = 'Multiple Choices';
                    break;
                case 301:
                    $text = 'Moved Permanently';
                    break;
                case 302:
                    $text = 'Moved Temporarily';
                    break;
                case 303:
                    $text = 'See Other';
                    break;
                case 304:
                    $text = 'Not Modified';
                    break;
                case 305:
                    $text = 'Use Proxy';
                    break;
                case 400:
                    $text = 'Bad Request';
                    break;
                case 401:
                    $text = 'Unauthorized';
                    break;
                case 402:
                    $text = 'Payment Required';
                    break;
                case 403:
                    $text = 'Forbidden';
                    break;
                case 404:
                    $text = 'Not Found';
                    break;
                case 405:
                    $text = 'Method Not Allowed';
                    break;
                case 406:
                    $text = 'Not Acceptable';
                    break;
                case 407:
                    $text = 'Proxy Authentication Required';
                    break;
                case 408:
                    $text = 'Request Time-out';
                    break;
                case 409:
                    $text = 'Conflict';
                    break;
                case 410:
                    $text = 'Gone';
                    break;
                case 411:
                    $text = 'Length Required';
                    break;
                case 412:
                    $text = 'Precondition Failed';
                    break;
                case 413:
                    $text = 'Request Entity Too Large';
                    break;
                case 414:
                    $text = 'Request-URI Too Large';
                    break;
                case 415:
                    $text = 'Unsupported Media Type';
                    break;
                case 500:
                    $text = 'Internal Server Error';
                    break;
                case 501:
                    $text = 'Not Implemented';
                    break;
                case 502:
                    $text = 'Bad Gateway';
                    break;
                case 503:
                    $text = 'Service Unavailable';
                    break;
                case 504:
                    $text = 'Gateway Time-out';
                    break;
                case 505:
                    $text = 'HTTP Version not supported';
                    break;
                default:
                    exit('Unknown http status code "' . htmlentities($code) . '"');
                    break;
            }
            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            header($protocol . ' ' . $code . ' ' . $text);
        }
    }
}
