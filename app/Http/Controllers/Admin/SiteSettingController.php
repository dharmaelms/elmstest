<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminBaseController;
use App\Model\Common;
use App\Model\SiteSetting;
use App\Model\WebExHost\Repository\IWebExHostRepository;
use App\Services\Playlyfe\IPlaylyfeService;
use Illuminate\Http\Request;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\ManageSite\ManageSitePermission;
use Input;
use Redirect;
use Response;
use Validator;

class SiteSettingController extends AdminBaseController
{
    protected $layout = 'admin.theme.layout.master_layout';
    private $plObj;
    private $webex_repo;

    public function __construct(IPlaylyfeService $plObj, IWebExHostRepository $webex_repo)
    {
        $this->plObj = $plObj;
        $this->theme_path = 'admin.theme';
        $this->webex_repo = $webex_repo;
    }

    public function getIndex()
    {
        if (has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::LIST_SITESETTING) == false) {
            return parent::getAdminError($this->theme_path);
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/sitesetting.manage_site') => 'sitesetting',
            trans('admin/sitesetting.site_configuration') => '',
        ];
        $host_list = $this->webex_repo->getWebExHosts();
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pageicon = 'fa fa-cog fa-fw';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'web')
            ->with('submenu', 'siteconfig');
        $this->layout->pagetitle = trans('admin/sitesetting.site_configuration');
        $this->layout->pagedescription = trans('admin/sitesetting.manag_site_conifiguration');
        $this->layout->content = view('admin.theme.sitesettings.managesitesetting')
            ->with('sitesets', SiteSetting::getSettings()->all())
            ->with('host_list', $host_list);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function postUpdate($module)
    {
        if (has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::EDIT_SITESETTING) == false) {
            return parent::getAdminError($this->theme_path);
        }
        if (!is_null($module)) {
            if ($module == 'contact_us') {
                $rules = [
                    'email' => 'email',
                    'phone' => 'Min:10|Max:20|Regex:/^([0-9-+ ])+$/',
                    'mobile_no' => 'numeric|Regex:/^([0-9]{10})/',
                    // 'address' => 'Required',
                    // 'lat' => 'required_with:address|lat',
                    // 'lng' => 'Required_with:address|lng',
                ];

                $niceNames = [
                    'email' => 'Email',
                    'phone' => 'Phone',
                    'mobile_no' => 'Mobile',
                ];

                // Validator::extend('address', function ($attribute, $value, $parameters) {
                //     if (preg_match('/^[_a-zA-Z0-9-+ _.,@&#$\'\ ]+$/', $value)) {
                //             return true;
                //         }
                //             return false;
                //     });

                Validator::extend('lat', function ($attribute, $value, $parameters) {
                    if (preg_match('/^[0-9-+.]+$/', $value)) {
                        return true;
                    }
                    return false;
                });
                Validator::extend('lng', function ($attribute, $value, $parameters) {
                    if (preg_match('/^[0-9-+.]+$/', $value)) {
                        return true;
                    }
                    return false;
                });

                $messages = [
                    'address' => trans('admin/manageweb.address'),
                    'lat' => trans('admin/manageweb.lat'),
                    'lng' => trans('admin/manageweb.lng'),
                ];

                $social = [];
                for ($i = 0; $i < Input::get('choice_count'); $i++) {
                    if (Input::get('social_media' . $i) || Input::get('url' . $i)) {
                        $rules += [
                            'social_media' . $i => 'required_with:url' . $i,
                            'url' . $i => 'url|required_with:social_media' . $i,
                        ];
                        $messages += [
                            'url' . $i . '.required_with' => 'Please enter a url',
                            'url' . $i . '.url' => 'Please enter a valid url',
                            'social_media' . $i . '.required_with' => 'Social media name is required when url is present.',
                        ];
                        $niceNames += [
                            'social_media' . $i => 'Social media',
                            'url' . $i => 'url'
                        ];
                        $social += [
                            Input::get('social_media' . $i) => Input::get('url' . $i)
                        ];
                    }
                }

                $validation = Validator::make(Input::all(), $rules, $messages, $niceNames);

                if ($validation->fails()) {
                    return redirect('cp/sitesetting#contact_us')
                        ->withInput()->withErrors($validation)->with('contact_us', 'contact_us');
                } else {
                    $file = Input::file('file');
                    $mobile_file = Input::file('mobile_file');

                    if ($file) {
                        $site_logo_name = 'site_logo.' . $file->getClientOriginalExtension();
                        $site_logo_path = config('app.site_logo_path');
                        $file_location = $site_logo_path . 'site_logo.' . $file->getClientOriginalExtension();
                        // File::cleanDirectory($site_logo_path);
                        $file->move($site_logo_path, $file_location);
                        $original_name = $file->getClientOriginalName();
                    } elseif (Input::get('old_file')) {
                        $site_logo_name = Input::get('old_file');
                        $original_name = Input::get('old_name');
                    } else {
                        $site_logo_name = '';
                        $original_name = '';
                    }

                    /*mobile logo */
                    if ($mobile_file) {
                        $mobile_logo_name = 'mobile_logo.' . $mobile_file->getClientOriginalExtension();
                        $mobile_logo_path = config('app.site_logo_path');
                        $mobile_file_location = $mobile_logo_path . 'mobile_logo.' . $mobile_file->getClientOriginalExtension();
                        // File::cleanDirectory($mobile_logo_path);
                        $mobile_file->move($mobile_logo_path, $mobile_file_location);
                        $original_mobile_name = $mobile_file->getClientOriginalName();
                    } elseif (Input::get('old_mobile_file')) {
                        $mobile_logo_name = Input::get('old_mobile_file');
                        $original_mobile_name = Input::get('old_mobile_name');
                    } else {
                        $mobile_logo_name = '';
                        $original_mobile_name = '';
                    }

                    $updatedata = [
                        'site_logo' => $site_logo_name,
                        'logo_original_name' => $original_name,
                        'mobile_logo' => $mobile_logo_name,
                        'mobile_original_name' => $original_mobile_name,
                        'homepage' => Input::get('home_page'),
                        'company_name' => Input::get('company_name'),
                        'email' => Input::get('email'),
                        'address' => trim(Input::get('address')),
                        'lat' => trim(Input::get('lat')),
                        'lng' => trim(Input::get('lng')),
                        'phone' => Input::get('phone'),
                        'mobile_no' => Input::get('mobile_no'),
                        'social_media' => $social
                    ];
                    $res = SiteSetting::updateModule('Contact Us', $updatedata);
                    if ($res) {
                        Input::flush();
                        return redirect('cp/sitesetting#contact_us')
                            ->with('success', trans('admin/sitesetting.contact_us_success'))->with('contact_us', 'contact_us');
                    } else {
                        return redirect('cp/sitesetting#contact_us')
                            ->with('error', trans('admin/sitesetting.contact_us_error'))->with('contact_us', 'contact_us');
                    }
                }
            } elseif ($module == 'mathml') {
                $rules = [
                    'mathml_editor' => 'in:on,off',
                ];
                if (Input::get('mathml_editor') == 'on') {
                    $file = public_path() . '/admin/assets/ckeditor/plugins/ckeditor_wiris/configuration.ini';
                    if (!is_writable($file)) {
                        return Response::json(['status' => 'validation', 'errors' => ['mathml_editor' => html_entity_decode(trans('admin/sitesetting.mathml_warning'))]]);
                    }
                }
                $validation = Validator::make(Input::all(), $rules);
                if ($validation->fails()) {
                    return Response::json(['status' => 'validation', 'errors' => $validation->getMessageBag()->toArray()]);
                } else {
                    $data = [
                        'mathml_editor' => Input::get('mathml_editor'),
                    ];
                    if (SiteSetting::updateModule('MathML', $data)) {
                        return Response::json([
                            'status' => 'success',
                            'message' => 'MathML Settings updated successfully'
                        ]);
                    } else {
                        return Response::json(['status' => 'insert']);
                    }
                }
            } elseif ($module == 'certificates') {
                $rules = [
                    'visibility' => 'required',
                ];
                $validation = Validator::make(Input::all(), $rules);
                if ($validation->fails()) {
                    return Response::json(['status' => 'validation', 'errors' => $validation->getMessageBag()->toArray()]);
                } else {
                    $data = [
                        'visibility' => Input::get('visibility'),
                    ];
                    if (SiteSetting::updateModule('certificates', $data)) {
                        return Response::json([
                            'status' => 'success',
                            'message' => trans('admin/sitesetting.certificates_setting_saved')
                        ]);
                    } else {
                        return Response::json(['status' => 'insert']);
                    }
                }
            } elseif ($module == 'general') {
                $display_per_page = (int)Input::get('display_per_page');
                $my_activities_per_page = (int)Input::get('my_activities');
                if (Input::get('faq')) {
                    $faq = Input::get('faq');
                } else {
                    $faq = 'off';
                }
                if (Input::get('package')) {
                    $package = Input::get('package');
                } else {
                    $package = 'off';
                }
                if (Input::get('static_pages')) {
                    $static_pages = Input::get('static_pages');
                } else {
                    $static_pages = 'off';
                }
                if (Input::get('edit_quiz_till') != '' && Input::get('edit_quiz_till') > 0) {
                    $edit_quiz_till = Input::get('edit_quiz_till');
                } else {
                    $edit_quiz_till = 240;
                }
                if (Input::get('notification')) {
                    $notification = Input::get('notification');
                } else {
                    $notification = 'off';
                }
                if (Input::get('email')) {
                    $email = Input::get('email');
                } else {
                    $email = 'off';
                }
                if (Input::get('watch_now')) {
                    $watch_now = Input::get('watch_now');
                } else {
                    $watch_now = 'off';
                }
                if (Input::get('posts')) {
                    $posts = Input::get('posts');
                } else {
                    $posts = 'off';
                }
                if (Input::get('favorites')) {
                    $favorites = Input::get('favorites');
                } else {
                    $favorites = 'off';
                }

                if (Input::get('sort_by')) {
                    $sort_by = Input::get('sort_by');
                } else {
                    $sort_by = 'updated_at';
                }

                if (Input::get('more_feeds')) {
                    $more_feeds = Input::get('more_feeds');
                } else {
                    $more_feeds = 'off';
                }
                if (Input::get('general_category_feeds')) {
                    $general_category_feeds = Input::get('general_category_feeds');
                } else {
                    $general_category_feeds = 'off';
                }
                
                $scorm_reports = Input::get('scorm_reports', 'off');
                
                $areaImprovement = Input::get('general_area_improve', 'off');
                $default_page_on_login = Input::get('default_page_on_login');
                $site_type = Input::get('site_type');
                $general_ecommerce = Input::get('general_ecommerce');
                $general_language = Input::get('general_language');

                $updatedata = [
                    'products_per_page' => $display_per_page,
                    'faq' => $faq,
                    'static_pages' => $static_pages,
                    'edit_quiz_till' => (int)$edit_quiz_till,
                    'notification' => $notification,
                    'email' => $email,
                    'watch_now' => $watch_now,
                    'package' => $package,
                    'posts' => $posts,
                    'favorites' => $favorites,
                    'sort_by' => $sort_by,
                    'more_feeds' => $more_feeds,
                    'general_category_feeds' => $general_category_feeds,
                    'default_page_on_login' => $default_page_on_login,
                    'site_Type' => $site_type,
                    'ecommerce' => $general_ecommerce,
                    'language' => $general_language,
                    'my_activities' => $my_activities_per_page,
                    'area_improve' => $areaImprovement,
                    'quiz_marics' => [
                        'quiz_speed' => Input::get('quiz_speed', 'off'),
                        'quiz_accuracy' => Input::get('quiz_accuracy', 'off'),
                        'quiz_score' => Input::get('quiz_score', 'off'),
                        'channel_completion' => Input::get('channel_completion', 'off'),
                    ],
                    'events' => Input::get('events', 'off'),
                    'assessments' => Input::get('assessments', 'off'),
                    'moodle_courses' => Input::get('moodle_courses', 'off'),
                    'email_for_add_user' => Input::get('email_for_add_user', 'off'),
                    'scorm_reports' => $scorm_reports,
                ];
                $res = SiteSetting::updateModule('General', $updatedata);
                return redirect('cp/sitesetting#general')
                        ->with('success', trans('admin/sitesetting.general_success'));
            } elseif ($module == 'lmsprogram') {
                $input = Input::all();
                $rules = [
                    'site_url' => 'Required',
                    'wstoken' => 'Required',
                ];
                $validation = Validator::make(Input::all(), $rules);
                if ($validation->fails()) {
                    return redirect('cp/sitesetting#lms')->withInput()->withErrors($validation);
                } else {
                    $lmsdata = [
                        'site_url' => $input['site_url'],
                        'wstoken' => $input['wstoken'],
                        'categoryid' => array_get($input, 'categoryid', 0),
                        'more_batches' => array_get($input, 'more_batches', 'off')
                    ];
                    SiteSetting::updateModule('Lmsprogram', $lmsdata);
                    return redirect('cp/sitesetting#lms')->with('success', trans('admin/sitesetting.lms_settings_success'));
                }
            } elseif ($module == 'category') {
                if (Input::get('categories_feeds_displayed')) {
                    $categories_feeds_displayed = (int)Input::get('categories_feeds_displayed');
                } else {
                    $categories_feeds_displayed = 10;
                }
                $updatedata = [
                    'categories_or_feeds' => $categories_feeds_displayed,
                ];
                $res = SiteSetting::updateModule('Category', $updatedata);
                if ($res) {
                    return redirect('cp/sitesetting')
                        ->with('success', trans('admin/sitesetting.category_success'));
                } else {
                    return redirect('cp/sitesetting')
                        ->with('error', trans('admin/sitesetting.category_error'));
                }
            } elseif ($module == 'search') {
                if (Input::get('simple_search')) {
                    $simple_search = Input::get('simple_search');
                } else {
                    $simple_search = 'off';
                }
                if (Input::get('advanced_search')) {
                    $advanced_search = Input::get('advanced_search');
                } else {
                    $advanced_search = 'off';
                }
                if (Input::get('facet')) {
                    $facet = Input::get('facet');
                } else {
                    $facet = 'off';
                }
                $search_results_per_page = Input::get('search_results_per_page');
                $updatedata = [
                    'results_per_page' => (int)$search_results_per_page,
                    'simple' => $simple_search,
                    'advanced' => $advanced_search,
                    'facet' => $facet,
                ];
                $res = SiteSetting::updateModule('Search', $updatedata);
                if ($res) {
                    return redirect('cp/sitesetting#search')
                        ->with('success', trans('admin/sitesetting.search_success'));
                } else {
                    return redirect('cp/sitesetting#search')
                        ->with('error', trans('admin/sitesetting.search_error'));
                }
            } elseif ($module == 'notifications_announcements') {
                $chars_disp_in_announce_list_page = Input::get('chars_disp_in_announce_list_page');
                $no_of_announce_notifi_popup = Input::get('no_of_announce_notifi_popup');
                $ann_expire_date = Input::get('ann_expire_date');
                $flush_notifications_days_limit = Input::get('flush_notifications_days_limit');
                $updatedata = [
                    'displayed_in_popup' => (int)$no_of_announce_notifi_popup,
                    'chars_announcment_list_page' => (int)$chars_disp_in_announce_list_page,
                    'ann_expire_date' => (int)$ann_expire_date,
                    'flush_notifications_days_limit' => (int)$flush_notifications_days_limit,
                ];
                $res = SiteSetting::updateModule('Notifications and Announcements', $updatedata);
                if ($res) {
                    return redirect('cp/sitesetting#notifications_announcements')
                        ->with('success', trans('admin/sitesetting.ann_noti_success'));
                } else {
                    return redirect('cp/sitesetting#notifications_announcements')
                        ->with('error', trans('admin/sitesetting.ann_noti_error'));
                }
            } elseif ($module == 'library') {
                //TODO: Analyze what the heck this code is doing here?
                echo 'i m inside if statement of ::library';
                echo 'Comes from Module is ::' . $module;
                die;
            } elseif ($module == 'event') {
                $event_assign_to_cf = Input::get('event_assign_to_cf');
                $event_key = Input::get('event_key');
                $event_service_lay_url = Input::get('event_service_lay_url');
                $event_username = Input::get('event_username');
                $event_password = Input::get('event_password');
                $event_app_key = Input::get('event_app_key');
                $event_open_time = Input::get('event_open_time');
                $event_duration = Input::get('event_duration');

                $updatedata = [
                    'event_key' => $event_key,
                    'event_assign_to_cf' => $event_assign_to_cf,
                ];
                if (!is_null($event_service_lay_url) && !empty($event_service_lay_url)) {
                    $updatedata['event_service_lay_url'] = $event_service_lay_url;
                }
                if (!is_null($event_username) && !empty($event_username)) {
                    $updatedata['event_username'] = $event_username;
                }
                if (!is_null($event_password) && !empty($event_password)) {
                    $updatedata['event_password'] = $event_password;
                }
                if (!is_null($event_app_key) && !empty($event_app_key)) {
                    $updatedata['event_app_key'] = $event_app_key;
                }
                if (!is_null($event_open_time) && !empty($event_open_time)) {
                    $updatedata['event_open_time'] = $event_open_time;
                }
                if (!is_null($event_duration) && !empty($event_duration)) {
                    $updatedata['event_duration'] = $event_duration;
                }

                $res = SiteSetting::updateModule('Event', $updatedata);
                if ($res) {
                    return redirect('cp/sitesetting')
                        ->with('success', trans('admin/sitesetting.ann_noti_success'));
                } else {
                    return redirect('cp/sitesetting')
                        ->with('error', trans('admin/sitesetting.ann_noti_error'));
                }
            } elseif ($module == 'assessment') {
                $assessment_key = Input::get('assessment_key');
                $assessment_min_no_of_qus = Input::get('assessment_min_no_of_qus');
                $assessment_assign_quz_to_cf = Input::get('assessment_assign_quz_to_cf');
                $assessment_defult_marks_for_qus = Input::get('assessment_defult_marks_for_qus');
                $assessment_allow_attempts = Input::get('assessment_allow_attempts');
                $updatedata = [
                    'assessment_key' => $assessment_key,
                    'assessment_assign_quz_to_cf' => $assessment_assign_quz_to_cf,
                    'assessment_min_no_of_qus' => (int)$assessment_min_no_of_qus,
                    'assessment_allow_attempts' => (int)$assessment_allow_attempts,
                ];
                if (!is_null($assessment_defult_marks_for_qus) && !empty($assessment_defult_marks_for_qus)) {
                    $updatedata['assessment_defult_marks_for_qus'] = $assessment_defult_marks_for_qus;
                }
                $res = SiteSetting::updateModule('Assessment', $updatedata);
                if ($res) {
                    return redirect('cp/sitesetting')
                        ->with('success', trans('admin/sitesetting.ann_noti_success'));
                } else {
                    return redirect('cp/sitesetting')
                        ->with('error', trans('admin/sitesetting.ann_noti_error'));
                }
            } elseif ($module == 'viewer') {
                $input = Input::all();
                $updatedata = [
                    'box_view' => array_get($input, 'box_view', 'off'),
                    'file_download' => (array_get($input, 'file_download') === 'true') ? true : false,
                    'text_selectable' => (array_get($input, 'text_selectable') === 'true') ? true : false,
                    'session_expires_at' => array_get($input, 'session_expires_at', 60),
                    'box_failure' => array_get($input, 'box_failure', 'download_link')
                ];

                $res = SiteSetting::updateModule('Viewer', $updatedata);
                if ($res) {
                    return redirect('cp/sitesetting#other_integrations')
                        ->with('success', trans('admin/sitesetting.box_success'));
                } else {
                    return redirect('cp/sitesetting#other_integrations')
                        ->with('error', trans('admin/sitesetting.box_error'));
                }
            } elseif ($module == 'bankdetails') {
                $input = Input::all();
                $updatedata = [
                    'bank_details' => array_get($input, 'bank_details', ''),

                ];

                $res = SiteSetting::updateModule('BankDetails', $updatedata);
                if ($res) {
                    return redirect('cp/sitesetting#other_integrations')
                        ->with('success', trans('admin/sitesetting.bank_success'));
                } else {
                    return redirect('cp/sitesetting')
                        ->with('error', trans('admin/sitesetting.bank_error'));
                }
            } elseif ($module == 'UserSetting') {
                $user_nda = Input::get('nda', 'off');
                $updatedata = [
                    'nda_acceptance' =>  $user_nda
                ];

                $res = SiteSetting::updateModule('UserSetting', $updatedata);
                if ($res) {
                    return redirect('cp/sitesetting#usersetting')
                        ->with('success', trans('admin/sitesetting.nda_update_success'));
                } else {
                    return redirect('cp/sitesetting#usersetting')
                        ->with('error', trans('admin/sitesetting.nda_update_error'));
                }
            } elseif ($module == 'LHSMenuSettings') {
                $updatedata = [
                    'programs' => Input::get('programs', 'off'),
                    'my_activity' => Input::get('my_activity', 'off'),
                ];
                $res = SiteSetting::updateModule('LHSMenuSettings', $updatedata);
                if ($res) {
                    return redirect('cp/sitesetting#lhs_menu_settings')
                        ->with('success', trans('admin/sitesetting.lhs_menu_settings_success'));
                } else {
                    return redirect('cp/sitesetting#lhs_menu_settings')
                        ->with('error', trans('admin/sitesetting.lhs_menu_settings_error'));
                }
            } elseif ($module == 'QuizReminders') {
                $rules = [
                    'day_Reminder1' => 'numeric',
                    'day_Reminder2' => 'numeric',
                ];
                $messages = [];
                if (Input::get('status_Reminder1') == "on") {
                    $general1 =  Input::get('general_Reminder1');
                    $general_practice1 = Input::get('general_practice_Reminder1');
                    $question_generator1 = Input::get('question_generator_Reminder1');
                    if(is_null($general1) && is_null($general_practice1) && is_null($question_generator1) )
                    {
                        $rules += [ 'question_generator_Reminder1' => 'required_without_all:general_practice_Reminder1,question_generator_Reminder1' ];
                        $messages += [ 'question_generator_Reminder1.required_without_all' => trans('admin/sitesetting.select_quiz_type') ];
                    } 
                }

                if (Input::get('status_Reminder2')  == "on") {
                    $general2 =  Input::get('general_Reminder2');
                    $general_practice2 = Input::get('general_practice_Reminder2');
                    $question_generator2 = Input::get('question_generator_Reminder2');
                    if(is_null($general2) && is_null($general_practice2) && is_null($question_generator2) )
                    {
                        $rules += [ 'question_generator_Reminder2' => 'required_without_all:general_practice_Reminder2,question_generator_Reminder2' ];
                        $messages += [ 'question_generator_Reminder2.required_without_all' => trans('admin/sitesetting.select_quiz_type') ];
                    }
                }
                
                $validation = Validator::make(Input::all(), $rules, $messages);
                if ($validation->fails()) {
                    return redirect('cp/sitesetting#notifications_announcements;#quiz_reminder')->withInput()->withErrors($validation);
                } else {
                    $quizreminders = [
                       'Reminder1' => [
                        'reminder_status' => Input::get('status_Reminder1', 'on'),
                        'reminder_day' => Input::get('day_Reminder1', 3),
                        'quiz_type' => [
                                'general' => Input::get('general_Reminder1', 'off'),
                                'general_practice' => Input::get('general_practice_Reminder1', 'off'),
                                'question_generator' => Input::get('question_generator_Reminder1', 'off'),
                        ],
                        'notify_by_mail' => Input::get('notify_mail_Reminder1', 'off'),
                       ],
                       'Reminder2' => [
                        'reminder_status' => Input::get('status_Reminder2', 'off'),
                        'reminder_day' => Input::get('day_Reminder2', 1),
                        'quiz_type' => [
                            'general' => Input::get('general_Reminder2', 'off'),
                            'general_practice' => Input::get('general_practice_Reminder2', 'off'),
                            'question_generator' => Input::get('question_generator_Reminder2', 'off'),
                        ],
                        'notify_by_mail' => Input::get('notify_mail_Reminder2', 'off')
                       ]
                    ];
                    SiteSetting::updateModule('QuizReminders', $quizreminders);
                    return redirect('cp/sitesetting#notifications_announcements;#quiz_reminder')->with('success', trans('admin/sitesetting.quiz_reminders_success_msg'));
                }
            } elseif ($module == 'WebexHostStorage') {
                $storage_value = Input::get('webex_host_list', []);
                $res = false;
                foreach($storage_value as $web_id => $storage_limit) {
                    $web_id = (int) $web_id;
                    $storage_limit = (int) $storage_limit;
                    $res = $this->webex_repo->updateStorageLimit($web_id, $storage_limit);
                }
                if ($res) {
                    return redirect('cp/sitesetting#webexhost-list')
                        ->with('success', trans('admin/sitesetting.webex_updated'));
                } else {
                    return redirect('cp/sitesetting#webexhost-list')
                        ->with('error', trans('admin/sitesetting.webex_update_error'));
                }
                
            }
        }
    }

    public function getEdit()
    {
        if (has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::EDIT_SITESETTING) == false) {
            return parent::getAdminError($this->theme_path);
        }
        $module = Input::get('module');
        $setting_name = Input::get('setting_name');
        if (!is_null($module) && !empty($module) && !is_null($setting_name) && !empty($setting_name)) {
            $res = SiteSetting::module($module, $setting_name);
            if (!is_null($res) && !empty($res)) {
                return $res;
            } else {
                return;
            }
        }
    }

    public function postSaveSocialite()
    {
        //Variable initialization
        $i = 0;
        $updateArray = [];
        $input = Input::all();
        $fields = ['soc_enabled', 'soc_register', 'soc_login', 'soc_fb', 'soc_google', 'soc_app', 'landing_page'];
        $dbfields = ['enabled', 'register', 'login', 'facebook', 'google', 'mobile_app', 'landing_page'];

        foreach ($fields as $value) {
            if (isset($input[$value]) && !empty($input[$value])) {
                $updateArray = array_merge($updateArray, [$dbfields[$i] => $input[$value]]);
            } else {
                $updateArray = array_merge($updateArray, [$dbfields[$i] => 'off']);
            }
            $i++;
        }
        SiteSetting::updateModule('Socialite', $updateArray);
        return redirect('cp/sitesetting#socialite')
            ->with('success', 'Socialite updated successfully');
    }

    public function postSaveHomepage($module = null)
    {
        if (!is_null($module)) {
            if ($module == 'upcomingcourses') {
                Input::flash();

                $rules = [
                    'display_name' => 'Required',
                    'records_per_course' => 'required|numeric|min:1',
                    'duration_in_days' => 'required|numeric|min:1',
                ];

                $validation = Validator::make(Input::all(), $rules);

                if ($validation->fails()) {
                    return redirect('cp/sitesetting#homepage;#upcomingcourses')->withInput()->withErrors($validation)->with('homepage', 'homepage')->with('upcomingcourses', 'upcomingcourses');
                } elseif ($validation->passes()) {
                    $enabled = Input::get('enabled') ? "on" : "off";
                    if (Input::get('configuration') == 'automated') {
                        $input_values = [
                            'enabled' => $enabled,
                            'display_name' => Input::get('display_name'),
                            'records_per_course' => Input::get('records_per_course'),
                            'configuration' => Input::get('configuration'),
                            'duration_in_days' => Input::get('duration_in_days'),
                            'type' => Input::get('type'),
                        ];
                    } else {
                        $input_values = [
                            'enabled' => $enabled,
                            'display_name' => Input::get('display_name'),
                            'records_per_course' => Input::get('records_per_course'),
                            'configuration' => Input::get('configuration')
                        ];
                    }

                    $homepage_setting = SiteSetting::module('Homepage')->toArray();
                    array_pull($homepage_setting['setting'], 'UpcomingCourses');
                    array_set($homepage_setting, 'setting.UpcomingCourses', $input_values);
                    SiteSetting::updateModule('Homepage', $homepage_setting['setting']);
                    Input::flush();
                    return redirect('cp/sitesetting#homepage;#upcomingcourses')
                        ->with('success', trans('admin/sitesetting.upcoming_courses_success'));
                }
            } elseif ($module == 'quotes') {
                Input::flash();
                $input = Input::all();
                $rules = [
                    'quotes_label' => 'Required',
                    'quotes_display_no' => 'Required|numeric|min:1',
                    'description_chars' => 'Required|numeric|min:1'
                ];

                $messages = [
                    'quotes_display_no.required' => trans('admin/sitesetting.quotes_display_no_required'),
                    'quotes_display_no.min' => trans('admin/sitesetting.quotes_display_no_min')
                ];

                $validation = Validator::make(Input::all(), $rules, $messages);

                if ($validation->fails()) {
                    return redirect('cp/sitesetting#homepage;#quotes')->withInput()->withErrors($validation)->with('homepage', 'homepage')->with('quotes', 'quotes');
                } elseif ($validation->passes()) {
                    $updatedata = [
                        'label' => $input['quotes_label'],
                        'number_of_quotes_display' => $input['quotes_display_no'],
                        'description_chars' => $input['description_chars'],
                        'quotes_enable' => array_get($input, 'quotes_enable', 'off')
                    ];
                    $homepage_setting = SiteSetting::module('Homepage')->toArray();
                    array_pull($homepage_setting['setting'], 'Quotes');
                    array_set($homepage_setting, 'setting.Quotes', $updatedata);
                    $res = SiteSetting::updateModule('Homepage', $homepage_setting['setting']);
                    Input::flush();
                    if ($res) {
                        return redirect('cp/sitesetting#homepage;#quotes')
                            ->with('success', trans('admin/sitesetting.quotes_success'));
                    } else {
                        return redirect('cp/sitesetting#homepage;#quotes')
                            ->with('error', trans('admin/sitesetting.quotes_error'));
                    }
                }
            } elseif ($module == 'popularcourses') {
                Input::flash();

                $rules = [
                    'display_name' => 'Required',
                    'records_per_course' => 'required|numeric|min:1'
                ];

                $validation = Validator::make(Input::all(), $rules);

                if ($validation->fails()) {
                    return redirect('cp/sitesetting#homepage;#popularcourses')->withInput()->withErrors($validation)->with('homepage', 'homepage')->with('popularcourses', 'popularcourses');
                } elseif ($validation->passes()) {
                    $enabled = Input::get('enabled') ? "on" : "off";
                    $input_values = [
                        'enabled' => $enabled,
                        'display_name' => Input::get('display_name'),
                        'records_per_course' => Input::get('records_per_course')
                    ];

                    $homepage_setting = SiteSetting::module('Homepage')->toArray();
                    array_pull($homepage_setting['setting'], 'PopularCourses');
                    array_set($homepage_setting, 'setting.PopularCourses', $input_values);
                    SiteSetting::updateModule('Homepage', $homepage_setting['setting']);
                    Input::flush();
                    return redirect('cp/sitesetting#homepage;#popularcourses')
                        ->with('success', trans('admin/sitesetting.popular_courses_success'));
                }
            } else {
                return redirect('cp/sitesetting');
            }
        } else {
            return redirect('cp/sitesetting');
        }
    }

    public function getGamificationSettings(Request $req)
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/sitesetting.gamification') => 'gamification',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $title = 'Gamification Settings';
        $this->layout->pagetitle = trans('admin/sitesetting.gamification_settings');
        $this->layout->pageicon = 'fa fa-bar-chart-o';
        $this->layout->pagedescription = trans('admin/sitesetting.gamification_settings');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'web');

        $this->layout->content = view('playlyfe.admin_settings')
            ->with('title', $title);

        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function postGamificationSettings()
    {
        $this->plObj->patchActions(Input::all());
        return redirect('cp/sitesetting#other_integrations')
            ->with('success', trans('admin/sitesetting.gamification_success'));
    }
}
