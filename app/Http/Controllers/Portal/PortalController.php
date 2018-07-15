<?php

namespace App\Http\Controllers\Portal;

use App\Exceptions\Dams\MediaNotFoundException;
use App\Http\Controllers\PortalBaseController;
use App\Model\Announcement;
use App\Model\Common;
use App\Model\Dams\Repository\IDamsRepository;
use App\Model\Event;
use App\Model\EventReport\EventsHistory;
use App\Model\EventReport\EventsAttendeeHistory;
use App\Model\Faq;
use App\Model\PartnerLogo;
use App\Model\SiteSetting;
use App\Model\StaticPage;
use App\Services\Courses\Popular\IPopularService;
use App\Services\Courses\Upcoming\IUpcomingService;
use App\Services\DeletedEventsRecordings\IDeletedEventsRecordingsService;
use App\Services\Testimonial\ITestimonialService;
use Auth;
use Cache;
use Carbon\Carbon;
use Config;
use DB;
use Illuminate\Support\Facades\Artisan;
use Log;
use Session;
use Webex;

/**
 * Class PortalController
 * @package App\Http\Controllers\Portal
 */
class PortalController extends PortalBaseController
{
    /**
     * @var ITestimonialService
     */
    protected $testimonialService;

    /**
     * @var IUpcomingService
     */
    protected $upcomingService;

    /**
     * @var IPopularService
     */
    protected $popularService;

    /**
     * @var IDamsRepository
     */
    protected $dams_repository;

    /**
     * @var IDeletedEventsRecordingsService
     */
    private $deletedEventsRecordingsService;

    /**
     * PortalController constructor.
     * @param ITestimonialService $testimonial
     * @param IUpcomingService $upcomingService
     * @param IPopularService $popularService
     */
    public function __construct(
        ITestimonialService $testimonial,
        IUpcomingService $upcomingService,
        IPopularService $popularService,
        IDamsRepository $dams_repository,
        IDeletedEventsRecordingsService $deletedEventsRecordingsService
    )
    {
        parent::__construct();
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
        $this->testimonialService = $testimonial;
        $this->upcomingService = $upcomingService;
        $this->popularService = $popularService;
        $this->dams_repository = $dams_repository;
        $this->deletedEventsRecordingsService = $deletedEventsRecordingsService;
    }

    /**
     * @param null $slug
     */
    public function getIndex($slug = null)
    {
        /**
         * Testimonial Start
         */
        $testimonials = [];
        $quotes = SiteSetting::module('Homepage', 'Quotes');
        if ($quotes['quotes_enable'] == 'on') {
            $type = 'home_page';
            $selected_fields = ['name', 'diamension', 'short_description'];
            $limit = $quotes['number_of_quotes_display'];
            $testimonials = $this->testimonialService->getQuotesByPage($type, $selected_fields, $limit);
            $testimonials = $testimonials->toArray();
        }

        /* SAML LOGIN */
        if (config('app.enable_saml') === 'on' && !Auth::check()) {
            require_once(public_path() . "/samlauth.php");
        }

        /**
         * Testimonial End
         */
        if (is_null($slug)) {
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->header = view($this->theme_path . '.common.header');
            $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar', ['action' => '/']);
            $this->layout->footer = view($this->theme_path . '.common.footer');
            $announcements = Announcement::getAnnouncementsforAll(Config::get('app.announcenet_no_display_home'));

            $carry_media = [];
            $carry_cf = [];
            foreach ($announcements as $key => $value) {
                if (isset($value['media_assigned']) && !empty($value['media_assigned']) && $value['media_assigned'] != 'yet to fix') {
                    $for_media = $this->getMediaDetails($value['media_assigned'], '_id');
                    $carry_media[$key]['media_4_home'] = $for_media;
                } elseif (isset($value['relations']['active_media_announcement_rel']) && !empty($value['relations']['active_media_announcement_rel'])) {
                    $for_media = $this->getMediaDetails($value['relations']['active_media_announcement_rel'][0]);
                    $carry_media[$key]['media_4_home'] = $for_media;
                }
                /* if(isset($value['relations']['active_media_announcement_rel']) && !empty($value['relations']['active_media_announcement_rel']))
                {
               $for_media= $this->getMediaDetails($value['relations']['active_media_announcement_rel'][0]);
               $carry_media[$key]['media_4_home'] = $for_media;
                }*/
                if (isset($value['relations']['active_contentfeed_announcement_rel']) && !empty($value['relations']['active_contentfeed_announcement_rel'])) {
                    foreach ($value['relations']['active_contentfeed_announcement_rel'] as $key_cf => $value_cf) {
                        $temp_cf[] = $value_cf;
                    }
                    if (!empty($temp_cf)) {
                        $carry_cf[$key]['cf_4_home'] = $this->getCFTitlesAry($temp_cf);
                    }
                }
            }

            $this->layout = view('portal.theme.default.layout.one_columnlayout_frontend');
            // $res=Announcement::getAnnouncementsforAll(Config::get('app.announcenet_no_display_home'));

            //Upcoming and popular courses data
            $SiteSetting = SiteSetting::module('Homepage')->toArray();

            if (config('app.ecommerce') && isset($SiteSetting['setting']) && !empty($SiteSetting['setting'])) {
                if ($SiteSetting['setting']['UpcomingCourses']['enabled'] == 'on') {
                    $upcoming_courses = $this->upcomingService->getUpcomingCourses(0, (int)$SiteSetting['setting']['UpcomingCourses']['records_per_course']);
                } else {
                    $upcoming_courses = [];
                }
                if ($SiteSetting['setting']['PopularCourses']['enabled'] == 'on') {
                    $popular_courses = $this->popularService->getPopularCourses(0, (int)$SiteSetting['setting']['PopularCourses']['records_per_course']);
                } else {
                    $popular_courses = [];
                }
            } else {
                $upcoming_courses = [];
                $popular_courses = [];
            }

            Session::flash('menubar', false);
            $this->layout->header = view($this->theme_path . '.common.header');
            $this->layout->footer = view($this->theme_path . '.common.footer');
           if (config('app.ecommerce') != true && !Auth::check()) {
                $this->layout->content = view($this->theme_path . '.common.home_new')
                    ->with('announcements', $announcements)
                    ->with('carry_cf', $carry_cf)
                    ->with('carry_media', $carry_media)
                    ->with('testimonials', $testimonials)
                    ->with('SiteSetting', $SiteSetting)
                    ->with('upcoming_courses', $upcoming_courses)
                    ->with('popular_courses', $popular_courses);
            } else {
                $this->layout->content = view($this->theme_path . '.common.home')
                    ->with('announcements', $announcements)
                    ->with('carry_cf', $carry_cf)
                    ->with('carry_media', $carry_media)
                    ->with('testimonials', $testimonials)
                    ->with('SiteSetting', $SiteSetting)
                    ->with('upcoming_courses', $upcoming_courses)
                    ->with('popular_courses', $popular_courses);
            }
        } else {
            if ($slug == 'faq') {
                $faq_chk = SiteSetting::module('General', 'faq');
                if ($faq_chk != 'on') {
                    return redirect('/');
                }
                $faqs = Faq::getActiveFaq();
                if (empty($faqs)) {
                    return redirect('/');
                }
                $crumbs = [
                    'Home' => '/',
                    'Faq' => ''
                ];
                $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
                $this->layout->pagetitle = 'Faq';
                $this->layout->metakeys = 'Faq';
                $this->layout->metadescription = 'Faq';
                $this->layout->content = view($this->theme_path . '.managesite.staticpage')->with('faqs', $faqs)
                    ->with('testimonials', $testimonials);
            } elseif ($slug == 'home') {
                return redirect('/');
            } else {
                $static_page_chk = SiteSetting::module('General', 'static_pages');
                if ($static_page_chk != 'on') {
                    return redirect('/');
                }
                if (Cache::has($slug)) {
                    $page = Cache::get($slug);
                } else {
                    Cache::forever($slug, StaticPage::getOneStaticPageforSlug($slug));
                    // $page = StaticPage::getOneStaticPageforSlug($slug);
                    $page = Cache::get($slug);
                }
                if (!empty($page)) {
                    $this->layout->pagetitle = $page[0]['title'];
                    $crumbs = [
                        'Home' => '/',
                        $page[0]['title'] => ''
                    ];
                    $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
                    $this->layout->metakeys = $page[0]['metakey'];
                    $this->layout->metadescription = $page[0]['meta_description'];
                    $this->layout->content = view($this->theme_path . '.managesite.staticpage')
                        ->with('page_title', $page[0]['title'])
                        ->with('staticpage', $page[0])
                        ->with('testimonials', $testimonials);
                } else {
                    return parent::getError($this->theme, $this->theme_path);
                }
            }
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->header = view($this->theme_path . '.common.header');
            $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar', ['action' => '/']);
            $this->layout->footer = view($this->theme_path . '.common.footer');
        }
    }

    /**
     * @param null $key
     * @param null $_id
     *
     * @return string|\Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    protected function getMediaDetails($key = null, $_id = null)
    {
        $query_field = null;
        if (!is_null($key) && $_id == '_id') {
            $query_field = '_id';
        } elseif (!is_null($key)) {
            $query_field = 'id';
        } else {
            return '';
        }

        try {
            $media = $this->dams_repository->getMedia($key, $query_field);
        } catch (MediaNotFoundException $e) {
            return trans('admin/exception'.$e->getcode());
        }
        $forret = '';
        $uniconf_id = Config::get('app.uniconf_id');
        $kaltura_url = Config::get('app.kaltura_url');
        $partnerId = Config::get('app.partnerId');

        $kaltura = $kaltura_url . 'index.php/kwidget/cache_st/1389590657/wid/_' . $partnerId . '/uiconf_id/' . $uniconf_id . '/entry_id/';

        return view('portal.theme.default.announcement.viewmedia')
            ->with('kaltura', $kaltura)
            ->with('media', $media);
    }

    protected function getCFTitlesAry($cf_ids = [])
    {
        $titles = [];
        foreach ($cf_ids as $key => $value) {
            $titles[] = Program::getCFTitleID($value);
        }

        return $titles;
    }

    public function getAllTestimonials()
    {
        $type = '';
        $selected_fields = ['name', 'diamension', 'short_description'];
        $limit = '';
        $testimonials = $this->testimonialService->getQuotesByPage($type, $selected_fields, $limit);

        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->content = view($this->theme_path . '.common.all_testimonial')
            ->with('testimonials', $testimonials);
        $this->layout->footer = view($this->theme_path . '.common.footer');
    }

    /**
     *
     */
    public function getPartnerLogo()
    {
        $filter = "ACTIVE";
        $partners = PartnerLogo::getFilteredRecords($filter);
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->content = view($this->theme_path . '.common.partner_logo')
            ->with('partners', $partners);
        $this->layout->footer = view($this->theme_path . '.common.footer');
    }

    /**
     * @param string $curr_fromdate
     * @param string $curr_todate
     */
    public function getWebinarCron($curr_fromdate = '', $curr_todate = '')
    {
        Log::info("Webinar cron Called");

        // WebEx instance object
        $webex = new Webex(
            config('app.webex_servicelayer_url'),
            config('app.webex_appkey'),
            config('app.webex_username'),
            config('app.webex_password')
        );

        $events = Event::where('event_type', 'live')
            ->whereBetween('end_time' , [Carbon::now()->subDays(2)->timestamp, Carbon::now()->timestamp])
            ->Where('recordings.0' , 'exists', false)
            ->Active()
            ->get([
                'session_key',
                'session_type',
                'webex_host_username',
                'webex_host_password',
                'event_id',
                'event_name',
                'event_host_name'
            ]);
        if (!$events->isEmpty()) {
            $events->each(function ($event)  use($webex){
                Log::info('Event cron updating '. $event->event_name. '(' . $event->event_id . ')');
                $param = [];
                $param['sessionKey'] = $event->session_key;
                $param['hostUsername'] = $event->webex_host_username;
                $param['hostPassword'] = $event->webex_host_password;
                $response = $webex->recording("read", $param);
                $recordings = array_get($response, 'data.recording', []);
                $i = 0;
                $recording_details = $recording_ids = [];
                $deletedRecordingds = $this->deletedEventsRecordingsService->getEventDetails($event->event_id);
                if (!empty($deletedRecordingds)) {
                    $existing_recordings =  collect($deletedRecordingds->recordings)->toArray();
                    $recording_ids = array_column($existing_recordings, 'display_id');
                }
                foreach ($recordings as $key => $value) {
                    $i++;
                    if (!empty($recording_ids)) {
                        while (1) {
                            if (!in_array($i, $recording_ids)) {
                                break;
                            }
                            $i++;
                        }
                    } 
                    $value['display_name'] = trans('admin/event.recording').''.$i;
                    $value['display_id'] = $i;
                    $recording_details[] = $value;
                }
                if (array_get($response,'status', false)) {
                    if(array_get($response, 'data.recording')) {
                        Event::where('event_id', (int)$event->event_id)->update([
                            'recordings' => $recording_details,
                            'recording_downloaded' => false,
                            'recording_uploaded' => false
                        ]);
                        Event::where('event_id', (int)$event->event_id)->update(['cron_flag' => 1]);
                        Log::info("Event $event->event_name updated with recordings");
                    }
                } else {
                    if (array_get($response,"error.0") == "Sorry, no record found") {
                        Event::where('event_id', (int)$event->event_id)->update(['cron_flag' => 1]);
                        Log::info('No recording found for '.$event->event_name);
                    } 
                    Log::info($response);
                }
            });
        } else {
            Log::info("No event found on recording and report cron");
        }
        if (config('app.webex_record_download')) {
            Artisan::call('webex:download');
        }
        if (config('app.gdrive.service')) {
            Artisan::call('gdrive:upload');
        }
        exit(0);
    }
    
    public function simpleLogin()
    {
        if (!Auth::check()) {
            $this->layout = \View::make('portal.theme.' . $this->theme . '.layout.login_page_layout');
            $this->layout->content = view($this->theme_path . '.common.simple_login');
        } else {
            return redirect('/dashboard');
        }
    }
}
