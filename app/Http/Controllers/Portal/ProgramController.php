<?php

namespace App\Http\Controllers\Portal;

use App;
use App\Enums\DAMs\ScormCompletionStatus;
use App\Enums\Program\ElementType as ELEMENTS;
use App\Http\Controllers\PortalBaseController;
use App\Model\AccessRequest;
use App\Model\Category;
use App\Model\ChannelFaq;
use App\Model\ChannelFaqAnswers;
use App\Model\Common;
use App\Model\Dam;
use App\Model\Email;
use App\Model\Event;
use App\Model\FlashCard;
use App\Model\MyActivity;
use App\Model\Notification;
use App\Model\OverAllChannelAnalytic;
use App\Model\Packet;
use App\Model\PacketFaq;
use App\Model\PacketFaqAnswers;
use App\Model\Program;
use App\Model\Quiz;
use App\Model\QuizAttempt;
use App\Model\SiteSetting;
use App\Model\TransactionDetail;
use App\Model\User;
use App\Model\UserGroup;
use App\Services\DAMS\IDAMsService;
use App\Services\Event\IEventService;
use App\Services\FlashCard\IFlashCardService;
use App\Services\Playlyfe\IPlaylyfeService;
use App\Services\Package\IPackageService;
use App\Services\PostFaqAnswer\IPostFaqAnswerService;
use App\Services\PostFaq\IPostFaqService;
use App\Services\Program\IProgramService;
use App\Services\Assignment\IAssignmentService;
use App\Services\Assignment\IAssignmentAttemptService;
use App\Services\ScormActivity\IScormActivityService;
use App\Services\Survey\ISurveyService;
use App\Services\Survey\ISurveyAttemptDataService;
use App\Services\Survey\ISurveyAttemptService;
use App\Services\Survey\ISurveyQuestionService;
use App\Services\Quiz\IQuizService;
use App\Services\Post\IPostService;
use App\Traits\AkamaiTokenTrait;
use Auth;
use Box\View\Client;
use Config;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\Program\NoProgramAssignedException;
use Input;
use Request;
use Session;
use Timezone;
use URL;
use Log;
use App\Services\Box\IBoxService;
use Exception;

// use App\Events\Program\CourseCompleted;


class ProgramController extends PortalBaseController
{
    //Holds playlyfe instance.
    use AkamaiTokenTrait;

    private $playlyfe;

    /**
     * Box service
     * @var App\Services\Box\IBoxService
     */
    private $box_service;

    /**
     * @var App\Services\Program\IProgramService
     */
    private $program_service;

    /**
     * @var App\Services\Package\IPackageService
     */
    private $package_service;

    /**
     * @var App\Model\ScormActivity\IScormActivityRepository
     */
    private $scorm_activity_service;

    /**
     * @var App\Services\DAMS\IDAMsService
     */
    private $dams_service;

    /**
     * @var App\Services\Quiz\IQuizService
     */
    private $quiz_service;

    /**
     * @var App\Services\Event\IEventService
     */
    private $event_service;
    
    /**
     * @var App\Services\FlashCard\IFlashCardService
     */
    private $flash_card_service;

    private $post_service;
    private $post_faq_answer;
    private $post_faq_service;

    /**
     * @var App\Services\Survey\ISurveyService
     */
    private $survey_service;

    /**
     * @var App\Services\Survey\ISurveyAttemptDataService
     */
    private $survey_attempt_data_service;

    /**
     * @var App\Services\Survey\ISurveyQuestionService
     */
    private $survey_question_service;

    /**
     * @var \App\Services\Survey\ISurveyAttemptService
     */
    private $survey_attempt_service;
    
    /**
     * @var \App\Services\Assignment\IAssignmentService
     */
    private $assignment_service;
    
    /**
     * @var \App\Services\Assignment\IAssignmentAttemptService
     */
    private $assignment_attempt_service;
    
    public function __construct(
        Request $request,
        IPlaylyfeService $playlyfe,
        IBoxService $box_service,
        IProgramService $program_service,
        IScormActivityService $scorm_activity_service,
        IDAMsService $dams_service,
        IQuizService $quiz_service,
        IEventService $event_service,
        IFlashCardService $flash_card_service,
        IPackageService $package_service,
        IPostService $post_service,
        IPostFaqAnswerService $post_faq_ans_service,
        IPostFaqService $post_faq_service,
        ISurveyService $survey_service,
        ISurveyAttemptDataService $survey_attempt_data_service,
        ISurveyQuestionService $survey_question_service,
        ISurveyAttemptService $survey_attempt_service,
        IAssignmentService $assignment_service,
        IAssignmentAttemptService $assignment_attempt_service
    ) {
    
        // Stripping all html tags from the request body
        $input = $request::input();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request::merge($input);
        $this->playlyfe = $playlyfe;
        $this->box_service = $box_service;
        $this->program_service = $program_service;
        $this->package_service = $package_service;
        $this->scorm_activity_service = $scorm_activity_service;
        $this->dams_service = $dams_service;
        $this->quiz_service = $quiz_service;
        $this->event_service = $event_service;
        $this->flash_card_service = $flash_card_service;
        $this->post_service = $post_service;
        $this->post_faq_ans = $post_faq_ans_service;
        $this->post_faq = $post_faq_service;
        $this->survey_service = $survey_service;
        $this->survey_attempt_data_service = $survey_attempt_data_service;
        $this->survey_question_service = $survey_question_service;
        $this->survey_attempt_service = $survey_attempt_service;
        $this->assignment_service = $assignment_service;
        $this->assignment_attempt_service = $assignment_attempt_service;
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
        Session::put('parent_course', $this->getCourseByBatch());
    }

    public function getWhatToWatch()
    {
        if ((SiteSetting::module('LHSMenuSettings', 'programs') != 'on') || 
            (SiteSetting::module('General', 'watch_now') != 'on')) {
            return parent::getError($this->theme, $this->theme_path, 401);
        }
        //$this->layout = view('portal.theme.'.$this->theme.'.layout.one_columnlayout');
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->header = view($this->theme_path . '.common.header');
        //$this->layout->leftsidebar = view($this->theme_path.'.common.leftsidebar',array('action'=>'/'));
        $this->layout->footer = view($this->theme_path . '.common.footer');
        try {
            $sub_program_slugs = $this->program_service->getUserProgramSlugs();
        } catch (\Exception $e) {
            $sub_program_slugs = [];
        }
        
        $category = [];
        $selected_feeds = [];

        if (Input::get('category')) {
            foreach (Input::get('category') as $cat) {
                $category[] = (int)$cat;
            }
        }

        if (Input::get('sub_category')) {
            foreach (Input::get('sub_category') as $sub_cat) {
                $category[] = (int)$sub_cat;
            }
        }
        if (Input::get('feed')) {
            foreach (Input::get('feed') as $feed) {
                $selected_feeds[] = (int)$feed;
            }
        }

        $feed_list = Program::getCategoryRelatedFeedAssets($category, $sub_program_slugs);
        if (Input::get('category') || Input::get('sub_category') && Input::get('feed')) {
            $program_ids = array_pluck($feed_list, 'program_id');
            $selected = array_intersect($selected_feeds, $program_ids);
            if (!empty($selected)) {
                $feed_ids = $selected_feeds;
            } else {
                $feed_ids = $program_ids;
            }
        } else {
            $feed_ids = $selected_feeds;
        }
        $program_slugs = Program::getCategoryRelatedProgramSlugs($category, $feed_ids, $sub_program_slugs);
        $packet_ids = Packet::getPacketIdsUsingSlugs($program_slugs);
        if (Input::get('sort_by')) {
            $sort_by = Input::get('sort_by');
        } else {
            $sort_by = 'new_to_old';
        }
        $records_per_page = SiteSetting::module('General', 'products_per_page', 10);
        $page_no = 0;
        $packets_count = Packet::getPacketsCountUsingIds($packet_ids);

        Session::put('watchnow_packet_ids', $packet_ids);
        Session::put('watchnow_packets_count', $packets_count);
        Session::put('sort_by', $sort_by);

        $orderby = SiteSetting::module('General', 'sort_by');
        $packets = Packet::getPacketsUsingIdsSortBy($packet_ids, $sort_by, $records_per_page, $page_no, $orderby);
        $data = $this->program_service->getProgramDetailsBySlug(array_pluck($packets, 'feed_slug'));
        $program_data = array_get($data, 'program_data', []);
        $package_data = array_get($data, 'package_data', []);
        $favorites = Auth::user()->favourited_packets;
        if (empty($favorites)) {
            $favorites = [];
        }
        Session::put('favorites', $favorites);
        $general = SiteSetting::module('General');
        $this->layout->content = view($this->theme_path . '.programs.what_to_watch')->
            with(['packets' => $packets, 'favorites' => $favorites, 'sort_by' => $sort_by, 
            'content_feeds' => $feed_list, 'cat_ids' => $category, 'feeds' => 1, 
            'cat_filter' => 0, 'feed_ids' => $selected_feeds, 'typeFilter' => 0, 
            'general' => $general, 'program_data' => $program_data, 'package_data' => $package_data]);
    }

    public function getWatchNextRecords()
    {
        $packets = [];
        $packet_ids = [];
        $sort_by = null;
        $records_per_page = SiteSetting::module('General', 'products_per_page', 10);
        $page_no = Input::get('pageno');

        $packet_ids = Session::get('watchnow_packet_ids');
        $sort_by = Session::get('sort_by');
        $packets_count = Session::get('watchnow_packets_count');
        $favorites = Session::get('favorites');

        $orderby = SiteSetting::module('General', 'sort_by');
        $packets = Packet::getPacketsUsingIdsSortBy($packet_ids, $sort_by, $records_per_page, $page_no, $orderby);
        $data = $this->program_service->getProgramDetailsBySlug(array_pluck($packets, 'feed_slug'));
        $program_data = array_get($data, 'program_data', []);
        $package_data = array_get($data, 'package_data', []);
        if (!empty($packets)) {
            return response()->json([
                'status' => true,
                'data' => view($this->theme_path . '.programs.whattowatch_ajax_load', 
                    ['packets' => $packets, 'favorites' => $favorites,
                    'program_data' => $program_data, 'package_data' => $package_data])->render(),
                'count' => count($packets),
            ]);
        } else {
            return response()->json([
                'status' => false,
                'data' => '<div class="col-md-12 center l-gray"><p><strong>
                    ' . trans('pagination.no_more_records') . '</strong></p></div>',
            ]);
        }
    }

    /**
     * User incomplete post from program
     * @return [type] [description]
     */
    public function getIncompletePosts()
    {
        if ((SiteSetting::module('LHSMenuSettings', 'programs') != 'on') ||
            (SiteSetting::module('General', 'watch_now') != 'on')) {
            return parent::getError($this->theme, $this->theme_path, 401);
        }

        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');

        try {
            $sub_program_slugs = $this->program_service->getUserProgramSlugs();
        } catch (\Exception $e) {
            $sub_program_slugs = [];
        }

        $category = [];
        $selected_feeds = [];

        if (Input::get('category')) {
            foreach (Input::get('category') as $cat) {
                $category[] = (int)$cat;
            }
        }

        if (Input::get('sub_category')) {
            foreach (Input::get('sub_category') as $sub_cat) {
                $category[] = (int)$sub_cat;
            }
        }
        if (Input::get('feed')) {
            foreach (Input::get('feed') as $feed) {
                $selected_feeds[] = (int)$feed;
            }
        }

        $feed_list = Program::getCategoryRelatedFeedAssets($category, $sub_program_slugs);
        if (Input::get('category') || Input::get('sub_category') && Input::get('feed')) {
            $program_ids = array_pluck($feed_list, 'program_id');
            $selected = array_intersect($selected_feeds, $program_ids);
            if (!empty($selected)) {
                $feed_ids = $selected_feeds;
            } else {
                $feed_ids = $program_ids;
            }
        } else {
            $feed_ids = $selected_feeds;
        }
        $program_slugs = Program::getCategoryRelatedProgramSlugs($category, $feed_ids, $sub_program_slugs);
        $total_packet_ids = Packet::getPacketIdsUsingSlugs($program_slugs);
        $packet_ids = [];
        if (!empty($total_packet_ids)) {
            $user_channel_act = OverAllChannelAnalytic::getUserAnalytics(Auth::user()->uid);
            $in_comp_packs = $user_channel_act->lists('post_completion')->collapse()->filter(function ($value, $key) {
                if ($value == 100) {
                    return $key;
                }
            });
            $packet_keys = $in_comp_packs->keys();
            $completed_packet_ids = [];
            foreach ($packet_keys as $value) {
                $completed_packet_ids[] = (int) str_replace('p_', '', $value);
            }
            $packet_ids = array_diff($total_packet_ids, $completed_packet_ids);
        }
        if (Input::get('sort_by')) {
            $sort_by = Input::get('sort_by');
        } else {
            $sort_by = 'new_to_old';
        }
        $records_per_page = SiteSetting::module('General', 'products_per_page', 10);
        $page_no = 0;
        $packets_count = Packet::getPacketsCountUsingIds($packet_ids);

        Session::put('incomplete_packet_ids', $packet_ids);
        Session::put('incomplete_packets_count', $packets_count);
        Session::put('sort_by', $sort_by);

        $orderby = SiteSetting::module('General', 'sort_by');
        $packets = Packet::getPacketsUsingIdsSortBy($packet_ids, $sort_by, $records_per_page, $page_no, $orderby);
        $data = $this->program_service->getProgramDetailsBySlug(array_pluck($packets, 'feed_slug'));
        $program_details = array_get($data, 'program_data', []);
        $favorites = Auth::user()->favourited_packets;
        if (empty($favorites)) {
            $favorites = [];
        }
        Session::put('favorites', $favorites);
        $general = SiteSetting::module('General');
        $this->layout->content = view($this->theme_path . '.programs.incomplete')
            ->with(['packets' => $packets, 'favorites' => $favorites, 
                'sort_by' => $sort_by, 'content_feeds' => $feed_list, 'cat_ids' => $category, 
                'feeds' => 1, 'cat_filter' => 0, 'feed_ids' => $selected_feeds, 
                'general' => $general, 'program_details' => $program_details]);
    }

    public function getIncompleteNextRecords()
    {
        $packets = [];
        $packet_ids = [];
        $sort_by = null;
        $records_per_page = SiteSetting::module('General', 'products_per_page', 10);
        $page_no = Input::get('pageno');

        $packet_ids = Session::get('incomplete_packet_ids');
        $sort_by = Session::get('sort_by');
        $packets_count = Session::get('incomplete_packets_count');
        $favorites = Session::get('favorites');

        $orderby = SiteSetting::module('General', 'sort_by');
        $packets = Packet::getPacketsUsingIdsSortBy($packet_ids, $sort_by, $records_per_page, $page_no, $orderby);
        $data = $this->program_service->getProgramDetailsBySlug(array_pluck($packets, 'feed_slug'));
        $program_details = array_get($data, 'program_data', []);
        if (!empty($packets)) {
            return response()->json([
                'status' => true,
                'data' => view($this->theme_path . '.programs.incomplete_ajax_load', 
                    ['packets' => $packets, 'favorites' => $favorites, 
                    'program_details' => $program_details])->render(),
                'count' => count($packets),
            ]);
        } else {
            return response()->json([
                'status' => false,
                'data' => '<div class="col-md-12 center l-gray"><p><strong>' . trans('pagination.no_more_records') . '</strong></p></div>',
            ]);
        }
    }

    public function getPacket($slug = null, $from = null, $element_id = null, $element_type = null)
    {
        $user = Auth::user();
        $requestUrl = '';
        $from = Input::get('from');
        if ($slug == null) {
            return parent::getError($this->theme, $this->theme_path);
        }
        $packet = Packet::getPacket($slug);
        if (empty($packet)) {
            return parent::getError($this->theme, $this->theme_path);
        }
        $packets_feed_slugs = array_pluck($packet, 'feed_slug');
        $packet = $packet[0];
        
        $program_slugs = $this->program_service->getUserProgramSlugs();
        if (count(array_intersect($packets_feed_slugs, $program_slugs)) <= 0) {
            return parent::getError($this->theme, $this->theme_path);
        }
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');

        $packet_expired = Program::isExpired($packet['feed_slug']);
        if ($packet_expired) {
            return redirect('program/packets/' . $packet['feed_slug'] . '/from');
        }

        $inactive = false;

        if ($packet['status'] == 'IN-ACTIVE') {
            $inactive = true;
        }

        $feed = Program::pluckFeedName($packet['feed_slug']);

        if ($from == null) {
            $array = [
                'module' => 'packet',
                'action' => 'view',
                'module_name' => $packet['packet_title'],
                'module_id' => (int)$packet['packet_id'],
                'feed_id' => (int)$feed[0]['program_id'],
                'feed_name' => $feed[0]['program_title'],
                'url' => Request::path(),
            ];
            MyActivity::getInsertActivity($array);
        }

        $elements = [];
        $asset = [];
        $kaltura = '';
        $flag = 0;
        $attempts = [];
        $viewer_session_id = '';
        $file_download = false;
        $viewdEleIds = [];
        $viewdEleTypeIds = [];
        $media = null;
        $userActivityDataForElement = [];
        $posts_data = [];
        $survey_details = [];
        $survey_questions_count = [];
        $survey_attempt_details = [];
        $assignment_details = [];
        $assignment_attempt_details = [];

        $viewdEleTypeIds = $this->post_service->getViewedElementsInPacket(Auth::user()->uid, (int)$feed[0]['program_id'], (int)$packet['packet_id']);
        if (isset($packet['elements']) && !empty($packet['elements'])) {
            $elements = $packet['elements'];
            $elements = array_values(array_sort($elements, function ($value) {
                return $value['order'];
            }));
            if ($element_id == null && $element_type == null) {
                $element_id = $elements[0]['id'];
                $element_type = $elements[0]['type'];
            }
            //code to restrict direct access of an item through url
            $last_element_id = $elements[0]['id'];
            $last_element_type = $elements[0]['type'];
            $display_name = '';
            $present = 0;
            $viewed = 0;
            foreach ($elements as $element) {
                if ($element['id'] == $element_id && $element['type'] == $element_type) {
                    $present = 1;
                    $activity = array_get($viewdEleTypeIds, $last_element_type, []);
                    if (in_array($last_element_id, $activity) || $element['order'] == 1) {
                        $viewed = 1;
                    }
                    if (isset($element['display_name']) && !empty($element['display_name'])) {
                        $display_name = $element['display_name'];
                    }
                }
                $last_element_id = $element['id'];
                $last_element_type = $element['type'];
            }
            $type_media = collect($elements)->where('type', ELEMENTS::MEDIA)->keyBy('id');
            $type_assessment = collect($elements)->where('type', ELEMENTS::ASSESSMENT)->keyBy('id');
            $type_event = collect($elements)->where('type', ELEMENTS::EVENT)->keyBy('id');
            $type_flashcard = collect($elements)->where('type', ELEMENTS::FLASHCARD)->keyBy('id');
            $type_survey = collect($elements)->where('type', ELEMENTS::SURVEY)->keyBy('id');
            $type_assignment = collect($elements)->where('type', ELEMENTS::ASSIGNMENT)->keyBy('id');
            if (!empty($type_media))
            {
                $element_asset = $this->dams_service->getDAMSDataUsingIDS($type_media->keys()->all());
                if (!empty($element_asset)) {
                    foreach ($element_asset as $key => $value) {
                        $type = array_get($value, 'type');
                        $id1 = array_get($value, 'id');
                        $id = "media_".$id1;
                        $display_name = array_get($type_media->get($id1), 'display_name');

                        if (!empty($display_name)) {
                            $posts_data[$id]['name'] = $display_name;
                        } else {
                            $posts_data[$id]['name'] =  array_get($value, 'name');
                        }
                        
                        $posts_data[$id]['type'] = 'Type:'.array_get($value, 'type');
                        
                        if($type == 'video')
                        {
                            $posts_data[$id]['class']  ='fa-play-circle';
                        }
                        elseif($type == 'image')
                        {
                            $posts_data[$id]['class'] = 'fa-picture-o';
                        }
                        elseif($type == 'document')
                        {
                            $posts_data[$id]['class'] = 'fa-file-text-o';
                        }
                        elseif($type == 'scorm')
                        {
                            $posts_data[$id]['class'] = 'fa-film';
                            $posts_data[$id]['type'] = 'Type:'.strtoupper($type);
                        }
                        else
                        {
                            $posts_data[$id]['class'] = 'fa-volume-down';
                        }
                    }
                }
                
            }

            if (!empty($type_assessment)) {   
                $element_asset = $this->quiz_service->getQuizDataUsingIDS($type_assessment->keys()->all());
                if (!empty($element_asset)) {
                    foreach ($element_asset as $key => $value) {
                        $type = trans('assessment.assessment').' '.trans('assessment.question_generator');
                        $id1 = array_get($value, 'quiz_id');
                        $id = "assessment_".$id1;
                        $display_name = array_get($type_assessment->get($id1), 'display_name');
                        if (!empty($display_name)) {
                            $posts_data[$id]['name'] = $display_name;
                        } else {
                            $posts_data[$id]['name'] = array_get($value, 'quiz_name');
                        }
                        if(!isset($value['type']))
                        $type = trans('assessment.assessment').' '.trans('program.general');
                        $posts_data[$id]['type'] = 'Type:'. $type;
                        $posts_data[$id]['class'] = 'fa-edit';
                    }
                }
                 
            }
                  
            if (!empty($type_event)) {
                $element_asset = $this->event_service->getEventsDataUsingIDS($type_event->keys()->all());
                if (!empty($element_asset)) {
                    foreach ($element_asset as $key => $value) {
                        $id1 = array_get($value, 'event_id');
                        $id = "event_".$id1;
                        $display_name = array_get($type_event->get($id1), 'display_name');
                        if (!empty($display_name)) {
                            $posts_data[$id]['name'] = $display_name;
                        } else {
                            $posts_data[$id]['name'] = array_get($value, 'event_name');
                        }
                        $type = array_get($value, 'event_type');
                        $posts_data[$id]['type'] = 'Type:'.$type;
                        $posts_data[$id]['class'] = 'fa-calendar';
                    }
                }
            }
            
            if (!empty($type_flashcard)) {
                $element_asset = $this->flash_card_service->getFlashcardsDataUsingIDS($type_flashcard->keys()->all());
                if (!empty($element_asset)) {
                    foreach ($element_asset as $key => $value) {
                        $id1 = array_get($value, 'card_id');
                        $id = "flashcard_".$id1;
                        $display_name = array_get($type_flashcard->get($id1), 'display_name');
                        if (!empty($display_name)) {
                            $posts_data[$id]['name'] = $display_name;
                        } else {
                            $posts_data[$id]['name'] = array_get($value, 'title');
                        }
                        $posts_data[$id]['type'] = 'Type:'.trans('program.flash_type');
                        $posts_data[$id]['class'] = 'fa-star';
                    }
                }
            }

            if (!empty($type_survey)) {   
                $survey_ids = $type_survey->keys()->all();
                $element_asset = $this->survey_service->getSurveyByIds($survey_ids);
                $survey_details = $element_asset->keyBy('id');
                $survey_attempt_details = $this->survey_attempt_service->getSurveyAttemptBySurveyIdAndUserId(
                                            ["survey_id" => $survey_ids, "user_id" => (int)Auth::user()->uid]
                                        )->keyBy('survey_id');
                if (!empty($element_asset)) {
                    foreach ($element_asset as $key => $value) {
                        $id = "survey_".array_get($value, 'id');
                        $posts_data[$id]['name'] = array_get($value, 'survey_title');
                        $posts_data[$id]['type'] = 'Type:'.trans('survey.survey');
                        $posts_data[$id]['class'] = 'fa-file-text';
                        //Survey Questions count
                        $survey_id = array_get($value, 'id');
                        $survey_question_details = $this->survey_question_service->getQuestionBySurveyId($survey_id);
                        $survey_questions_count[$survey_id] = $survey_question_details->count();
                    }
                }
            } 

            if (!empty($type_assignment))
            {   
                $assignment_ids = $type_assignment->keys()->all();
                $element_asset = $this->assignment_service->getAssignments(["id" => $assignment_ids]);
                $assignment_details = $element_asset->keyBy('id');
                $assignment_attempt_details = $this->assignment_attempt_service->getAllAttempts(["assignment_id" => $assignment_ids, "user_id" => (int)Auth::user()->uid])->keyBy('assignment_id');
                if (!empty($element_asset)) {
                    foreach ($element_asset as $key => $value) {
                        $id = "assignment_".array_get($value, 'id');
                        $posts_data[$id]['name'] = array_get($value, 'name');
                        $posts_data[$id]['type'] = 'Type:'.trans('assignment.assignment');
                        $posts_data[$id]['class'] = 'fa-file-text';

                    }
                }
            } 

            if (($viewed == 0 && $packet['sequential_access'] == 'yes') || $present == 0) {
                $element_id = $elements[0]['id'];
                $element_type = $elements[0]['type'];
            }

            switch ($element_type) {
                case 'media':
                    $mongo_id = Dam::getmongoid($element_id);
                    try {
                        $media = Dam::getMediaById($mongo_id);
                    } catch (Exception $e) {
                    }
                    $asset = Dam::getDAMSAssetsUsingAutoID((int)$element_id);
                    if (is_array($asset) && !empty($asset) && is_array($asset[0]) && !empty($asset[0])) {
                        $asset = $asset[0];
                        $type = [
                            'element_type' => $element_type,
                            'display_name' => $posts_data['media_'.array_get($asset, 'id')]['name'],
                        ];
                        $asset = array_merge($asset, $type);
                        $uniconf_id = Config::get('app.uniconf_id');
                        $kaltura_url = Config::get('app.kaltura_url');
                        $partnerId = Config::get('app.partnerId');
                        $kaltura = $kaltura_url . 'index.php/kwidget/cache_st/1389590657/wid/_' . $partnerId . '/uiconf_id/' . $uniconf_id . '/entry_id/';

                        $box_viewer_settings = SiteSetting::module('Viewer');

                        $box_view_status = array_get($box_viewer_settings->setting, 'box_view', 'off');
                        $file_download = array_get($box_viewer_settings->setting, 'file_download', false);

                        if ($box_view_status == 'on' && $asset['type'] != 'scorm') {
                            $viewer_session_id = $this->viewDocument($media);
                        }
                    }

                    break;
                case 'assessment':
                    $assessment = Quiz::getQuizAssetsUsingAutoID($element_id);
                    if (is_array($assessment) && !empty($assessment) && is_array($assessment[0]) && !empty($assessment[0])) {
                        $assessment = $assessment[0];
                        if (isset($assessment['users_liked'])) {
                            $users_liked = $assessment['users_liked'];
                        } else {
                            $users_liked = [];
                        }
                        $asset = [
                            'id' => $assessment['quiz_id'],
                            'name' => $assessment['quiz_name'],
                            'description' => $assessment['quiz_description'],
                            'users_liked' => $users_liked,
                            'element_type' => $element_type,
                            'display_name' => $posts_data['assessment_'.array_get($assessment, 'quiz_id')]['name'],
                        ];
                        if (isset($assessment['type']) && $assessment['type'] === "QUESTION_GENERATOR") {
                            $asset = array_merge($asset, ["duration" => '', "attempts" => 1]);
                        }
                        $user_quiz_rel = $this->quiz_service->getAllQuizzesAssigned();
                        $feedQuizList = $user_quiz_rel['feed_quiz_list'];
                        $directQIds = $user_quiz_rel['direct_quizzes'];
                        $directChennelQids = array_intersect($directQIds, array_keys($feedQuizList));
                        $allQuizzes = Quiz::whereIn('quiz_id', [(int)$assessment['quiz_id']])
                            ->where('status', '=', 'ACTIVE')
                            ->get();
                        $replaceQDate = $this->quiz_service->replaceDates(
                            $feedQuizList,
                            $allQuizzes
                        );
                        $asset = array_merge($asset, $assessment);
                        if (isset($asset['start_time']) && isset($replaceQDate[0]['start_time'])) {
                            $asset['start_time'] = $replaceQDate[0]['start_time'];
                        }
                        if (isset($asset['end_time']) && isset($replaceQDate[0]['end_time'])) {
                            $asset['end_time'] = $replaceQDate[0]['end_time'];
                        }
                        $attempts = QuizAttempt::where('quiz_id', '=', (int)$asset['quiz_id'])
                            ->where('user_id', '=', Auth::user()->uid)
                            ->orderBy('started_on')
                            ->get();
                    }
                    break;
                case 'event':
                    $event = Event::getEventsAssetsUsingAutoID($element_id);
                    if (is_array($event) && !empty($event) && is_array($event[0]) && !empty($event[0])) {
                        $event = $event[0];
                        if (isset($event['users_liked'])) {
                            $users_liked = $event['users_liked'];
                        } else {
                            $users_liked = [];
                        }
                        $asset = [
                            'id' => $event['event_id'],
                            'name' => $event['event_name'],
                            'description' => $event['event_description'],
                            'users_liked' => $users_liked,
                            'element_type' => $element_type,
                            'display_name' => $posts_data['event_'.array_get($event, 'event_id')]['name'],
                        ];
                        $asset = array_merge($asset, $event);
                    }
                    break;

                case 'flashcard':
                    $flashcard = FlashCard::getFlashcardsAssetsUsingAutoID($element_id);
                    if (is_array($flashcard) && !empty($flashcard) && is_array($flashcard[0]) && !empty($flashcard[0])) {
                        $flashcard = $flashcard[0];
                        if (isset($flashcard['users_liked'])) {
                            $users_liked = $flashcard['users_liked'];
                        } else {
                            $users_liked = [];
                        }
                        $asset = [
                            'id' => $flashcard['card_id'],
                            'name' => $flashcard['title'],
                            'description' => $flashcard['description'],
                            'users_liked' => $users_liked,
                            'element_type' => $element_type,
                            'display_name' => $posts_data['flashcard_'.array_get($flashcard, 'card_id')]['name'],
                        ];
                        $asset = array_merge($asset, $flashcard);
                    }
                    break;

                case 'survey':
                    $surveys = $this->survey_service->getSurveyByIds($element_id);
                    $survey = array_get($surveys, 0);
                    if (!empty($survey)) {
                        $asset = [
                            'id' => $survey->id,
                            'name' => $posts_data['survey_'.$survey->id]['name'],
                            'description' => $survey->description,
                            'element_type' => $element_type,
                        ];
                        $asset = array_merge($asset, $survey->toArray());
                    }
                    break;
                case 'assignment':
                    $assignment = $this->assignment_service->getAssignments(["id" => [(int)$element_id]]);
                    $assignment = array_get($assignment, 0);
                    if (!empty($assignment)) {
                        $asset = [
                            'id' => $assignment->id,
                            'name' => $posts_data['assignment_'.$assignment->id]['name'],
                            'description' => $assignment->description,
                            'element_type' => $element_type,
                        ];
                        $asset = array_merge($asset, $assignment->toArray());
                    }
                    break;         
            }

            if (is_array($asset) && !empty($asset)) {
                if ($from == 'element' || $from == null) {
                    $array = [
                        'module' => 'element',
                        'action' => 'view',
                        'module_name' => $asset['name'],
                        'module_id' => (int)$asset['id'],
                        'element_type' => $asset['element_type'],
                        'packet_id' => (int)$packet['packet_id'],
                        'packet_name' => $packet['packet_title'],
                        'feed_id' => (int)$feed[0]['program_id'],
                        'feed_name' => $feed[0]['program_title'],
                        'url' => 'program/packet/' . $slug . '/from/' . (int)$asset['id'] . '/' . $asset['element_type'],
                    ];
                    if (!is_null($media) && $media->type === "scorm") {
                        $array["action"] = "STARTED";
                        $array["scorm_runtime_activity_data"] = [
                            "user_full_name" => $user->lastname.", ".$user->firstname,
                            "total_time_spent" => 0
                        ];
                    }
                    MyActivity::getInsertActivity($array);
                    if ($asset['element_type'] != 'assessment' && $asset['element_type'] != 'event'  &&
                        !(!is_null($media) && $media->type === "scorm") && ($asset['element_type'] != 'survey') && ($asset['element_type'] != 'assignment')) {
                        $this->putEntryInToOca($array);
                    }
                    $userActivityDataForElement = $array;
                }
            }
        }
        $favorites = Auth::user()->favourited_packets;
        Session::put('packet', $packet);
        if (empty($favorites)) {
            $favorites = [];
        }
        $records_per_page = 9;
        $page_no = 0;

        $public_ques = PacketFaq::getPublicQuestions($packet['packet_id'], $records_per_page, $page_no);

        $user_ques = PacketFaq::getUserQuestions($packet['packet_id'], $records_per_page, $page_no);

        Common::getUserProfilePicture($user_ques);
        $token = null;
        //getToken method is in AkamaiTokenTrait
        $token = $this->getToken($asset);
        $isQuizzesPass = [];
        if (isset($packet['sequential_access']) && isset($packet['quiz_result']) && $packet['sequential_access'] == 'yes' && $packet['quiz_result'] == 'yes') {
            $elementTypes = array_where($elements, function ($key, $value) {
                if ($value['type'] == 'assessment') {
                    return $value;
                }
            });
            $assessmentIds = array_pluck($elementTypes, 'id');
            $closedQuizzes = array_get($viewdEleTypeIds, 'assessment', []);
            foreach ($assessmentIds as $assessmentId) {
                if (in_array($assessmentId, $closedQuizzes)) {
                    $isQuizzesPass[$assessmentId] = $this->isQuizPass($assessmentId);
                } else {
                    $isQuizzesPass[$assessmentId] = false;
                }
            }
        }
        if (!empty($asset) && $element_type != 'assessment') {
            if (isset($viewdEleTypeIds[$element_type])) {
                $temp = $viewdEleTypeIds[$element_type];
                $temp[] = (int)$element_id;
                $viewdEleTypeIds[$element_type] = $temp;
            } else {
                $viewdEleTypeIds[$element_type] = [(int)$element_id];
            }
        }
        
        $this->layout->content = view($this->theme_path . '.programs.packetdetail')
            ->with(
                [
                    'program' => $feed[0],
                    'channel_name' => $feed[0]['program_title'],
                    'packet' => $packet,
                    'elements' => $elements,
                    'posts_data' => $posts_data,
                    'survey_details' => $survey_details,
                    'survey_questions_count' => $survey_questions_count,
                    'survey_attempt_details' => $survey_attempt_details,
                    'assignment_details' => $assignment_details,
                    'assignment_attempt_details' => $assignment_attempt_details,
                    'public_ques' => $public_ques,
                    'user_ques' => $user_ques,
                    'favorites' => $favorites,
                    'asset' => $asset,
                    'kaltura' => $kaltura,
                    'attempts' => $attempts,
                    'inactive' => $inactive,
                    'from' => $from,
                    'viewer_session_id' => $viewer_session_id,
                    'token' => $token,
                    'isQuizzesPass' => $isQuizzesPass,
                    'viewdEleTypeIds' => $viewdEleTypeIds,
                    'file_download' => $file_download,
                    'media' => $media,
                    'userActivityDataForElement' => $userActivityDataForElement
                ]
            );
    }

    public function postUpdateScormRuntimeActivity(\Illuminate\Http\Request $request)
    {
        $user = $request->user();
        $packet_id = (int) $request->input("packet_id");
        $element_id = (int) $request->input("element_id");
        $session_time = (int) $request->input("scorm_activity_data.session_time", 0);
        $lesson_location = $request->input("scorm_activity_data.lesson_location", null);
        $lesson_status = $request->input("scorm_activity_data.lesson_status", ScormCompletionStatus::INCOMPLETE);
        $score_raw = $request->input("scorm_activity_data.score_raw", 0);
        $suspend_data = $request->input("scorm_activity_data.suspend_data", null);
        $exit = $request->input("scorm_activity_data.exit", "");

        try {
            $myActivity = MyActivity::where("user_id", $user->uid)
                ->where("module", "element")
                ->where("packet_id", $packet_id)
                ->where("module_id", $element_id)
                ->firstOrFail();

            $scorm = DAM::where("id", $element_id)
                ->where("status", "ACTIVE")
                ->firstOrFail();

            if ($myActivity->scorm_runtime_activity_data !== null) {
                $scorm_runtime_activity_data = $myActivity->scorm_runtime_activity_data;
                $scorm_runtime_activity_data["total_time_spent"] += $session_time;
                $scorm_runtime_activity_data["entry"] = ($exit === "suspend")? "resume" : "";
                $scorm_runtime_activity_data["lesson_location"] = $lesson_location;
                $scorm_runtime_activity_data["lesson_status"] = $lesson_status;
                $scorm_runtime_activity_data["score_raw"] = $score_raw;
                $scorm_runtime_activity_data["suspend_data"] = $suspend_data;

                if (!empty($scorm["mastery_score"])) {
                    if ($lesson_status === ScormCompletionStatus::PASSED) {
                        $myActivity->action = "view";
                        $this->putEntryInToOca($myActivity->toArray());
                    }
                } elseif (($lesson_status === ScormCompletionStatus::COMPLETED)
                    || ($lesson_status === ScormCompletionStatus::PASSED)) {
                    $myActivity->action = "view";
                    $this->putEntryInToOca($myActivity->toArray());
                }

                $myActivity->scorm_runtime_activity_data = $scorm_runtime_activity_data;
                $myActivity->save();
                
                $scormActivity = $updateScormActivity = [];
                $scormActivity = $this->scorm_activity_service->getScormDetails($user->uid, $packet_id, $element_id);

                $updateScormActivity["user_full_name"] = $myActivity->scorm_runtime_activity_data["user_full_name"];
                $updateScormActivity["total_time_spent"] = $myActivity->scorm_runtime_activity_data["total_time_spent"];
                $updateScormActivity["entry"] = $myActivity->scorm_runtime_activity_data["entry"];
                $updateScormActivity["lesson_location"] = $myActivity->scorm_runtime_activity_data["lesson_location"];
                if (array_get($scormActivity, '0.lesson_status', '') === ScormCompletionStatus::COMPLETED || array_get($scormActivity, '0.lesson_status', '') === ScormCompletionStatus::PASSED) {
                    $updateScormActivity["lesson_status"] = array_get($scormActivity, '0.lesson_status', '');
                } else {
                    $updateScormActivity["lesson_status"] = $myActivity->scorm_runtime_activity_data["lesson_status"];
                    $updateScormActivity["score_raw"] = (int) $myActivity->scorm_runtime_activity_data["score_raw"];
                }
                $updateScormActivity["suspend_data"] = $myActivity->scorm_runtime_activity_data["suspend_data"];

                $scorm_data["user_id"] = (int) $myActivity->user_id;
                $scorm_data["date"] =  time();
                $scorm_data["scorm_name"] = $myActivity->module_name;
                $scorm_data["scorm_id"] = (int) $myActivity->module_id;
                $scorm_data["packet_id"] = (int) $myActivity->packet_id;
                $scorm_data["packet_name"] = $myActivity->packet_name;
                $scorm_data["feed_id"] = (int) $myActivity->feed_id;
                $scorm_data["feed_name"] = $myActivity->feed_name;

                $scorm_data = array_merge($scorm_data, $updateScormActivity);

                if (!empty($scormActivity)) {
                    $this->scorm_activity_service->update(
                        $user->uid,
                        $element_id,
                        $packet_id,
                        $updateScormActivity
                    );
                } else {
                    $this->scorm_activity_service->create($scorm_data);
                }
            }
        } catch (ModelNotFoundException $e) {
            Log::error($e->getTraceAsString());
        }
    }

    public function getNextQuestions()
    {
        $records_per_page = 9;
        $page_no = Input::get('pageno');
        $packet_id = Input::get('packet_id');
        $packet = Session::get('packet');
        $user_ques = PacketFaq::getUserQuestions($packet_id, $records_per_page, $page_no);
        if (!empty($user_ques)) {
            return response()->json([
                'status' => true,
                'data' => view($this->theme_path . '.programs.myquestions_ajax_load', ['user_ques' => $user_ques, 'packet' => $packet])->render(),
                'count' => count($user_ques),
            ]);
        } else {
            return response()->json([
                'status' => false,
                'data' => '<div class="col-md-12 center l-gray"><p><strong>' . trans('pagination.no_more_records') . '</strong></p></div>',
            ]);
        }
    }

    public function getNextFaqs()
    {
        $records_per_page = 9;
        $page_no = Input::get('pageno');
        $packet_id = Input::get('packet_id');
        $packet = Session::get('packet');
        $public_ques = PacketFaq::getPublicQuestions($packet_id, $records_per_page, $page_no);
        Common::getUserProfilePicture($public_ques);
        if (!empty($public_ques)) {
            return response()->json([
                'status' => true,
                'data' => view($this->theme_path . '.programs.faq_ajax_load', ['public_ques' => $public_ques, 'packet' => $packet])->render(),
                'count' => count($public_ques),
            ]);
        } else {
            return response()->json([
                'status' => false,
                'data' => '<div class="col-md-12 center l-gray"><p><strong>' . trans('pagination.no_more_records') . '</strong></p></div>',
            ]);
        }
    }

    public function getQuestion($packet_id, $slug, $feed_slug)
    {
        if (!empty(Input::get('ques'))) {
            $question_id = PacketFaq::getInsert(Input::get('ques'), $packet_id, $slug, $feed_slug);

            //Code added by Muniraju N.
            //Playlyfe integration code starts here.

            $playlyfe = App::make(\App\Services\Playlyfe\IPlaylyfeService::class);

            $playlyfeEvent = [];
            $playlyfeEvent["type"] = "action";
            $playlyfeEvent["data"] = [
                "action_id" => "question_asked",
                "user_id" => Auth::user()->uid,
                "post_id" => $packet_id,
                "question_id" => $question_id
            ];

            $playlyfe->processEvent($playlyfeEvent);

            //Playlyfe integration code ends here.

            $page_no = Input::get('page_no');
            $records_per_page = ($page_no + 1) * 9;
            $packet = Session::get('packet');

            $user_ques = PacketFaq::getUserQuestions($packet_id, $records_per_page, 0);
            Common::getUserProfilePicture($user_ques);

            return response()->json([
                'status' => true,
                'data' => view($this->theme_path . '.programs.myquestions_ajax_load', ['user_ques' => $user_ques, 'packet' => $packet])->render(),
                'message' => trans('program.ask_question_success'),
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Please enter a question',
            ]);
        }
    }

    public function getQuestionLiked($action, $qid, $packet_id)
    {
        $packet_info = Packet::getPacketInfo($packet_id);
        switch ($action) {
            case 'like':
                PacketFaq::updateQALikedCount('true', $qid, $packet_id, $packet_info[0]['packet_slug'], $packet_info[0]['packet_title'], $packet_info[0]['feed_slug']);
                break;

            case 'unlike':
                PacketFaq::updateQALikedCount('false', $qid, $packet_id, $packet_info[0]['packet_slug'], $packet_info[0]['packet_title'], $packet_info[0]['feed_slug']);
                break;

            default:
                return response()->json([
                    'status' => false,
                    'qid' => (int)$qid,
                ]);
                break;
        }

        $like_count = PacketFaq::where('id', '=', (int)$qid)->value('like_count');
        if (($like_count == 1) || ($like_count == 0)) {
            $likes = $like_count . " Like";
        } else {
            $likes = $like_count . " Likes";
        }

        return response()->json([
            'status' => true,
            'qid' => (int)$qid,
            'like_count' => $likes,
        ]);
    }

    public function getQuestionDelete($qid, $userid)
    {
        if (count(PacketFaq::getQuestionsByQuestionID($qid)) == 0) {
            return parent::getError($this->theme, $this->theme_path);
        }

        $result = PacketFaq::getDelete($qid, $userid);
        $page_no = Input::get('page_no');
        $records_per_page = ($page_no + 1) * 9;

        if ($result == true) {
            $packet = Session::get('packet');
            $user_ques = PacketFaq::getUserQuestions($packet['packet_id'], $records_per_page, 0);
            return response()->json([
                'status' => true,
                'data' => view($this->theme_path . '.programs.myquestions_ajax_load', ['user_ques' => $user_ques, 'packet' => $packet])->render(),
                'message' => trans('qanda.delete'),
            ]);
        } else {
            return parent::getError($this->theme, $this->theme_path);
        }
    }

    public function getQuestionEdit($qid, $userid)
    {
        if (count(PacketFaq::getQuestionsByQuestionID($qid)) == 0) {
            return parent::getError($this->theme, $this->theme_path);
        }

        if (!empty(Input::get('edit'))) {
            $result = PacketFaq::getUpdate($qid, Input::get('edit'), $userid);
            if ($result == true) {
                $ques = PacketFaq::where('id', '=', (int)$qid)->value('question');
                return response()->json([
                    'status' => true,
                    'data' => $ques,
                    'message' => trans('qanda.edit'),
                ]);
            } else {
                return parent::getError($this->theme, $this->theme_path);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Please enter a question',
            ]);
        }
    }

    public function getAnswer($id)
    {
        if (!empty(Input::get('ans'))) {
            $question = $this->post_faq->getQuestionsByQuestionID((int)$id)->first()->toArray();
            // Update the total_unanswered questions for packets
            $oldanswers = $this->post_faq_ans->getAnswersByQuestionID($question['id'], $question['user_id']);
            if ($oldanswers->isEmpty()) {
                $this->post_service->DecrementField($question['packet_id'], 'total_ques_unanswered');
            }
            $this->post_faq->getUpdateFieldByQuestionId($question['id'], 'status', 'ANSWERED');
            $insertarr = [
                'id' => PacketFaqAnswers::getUniqueId(),
                'ques_id' => (int)$id,
                'user_id' => Auth::user()->uid,
                'username' => Auth::user()->username,
                'answer' => Input::get('ans'),
                'status' => 'ACTIVE',
                'created_at' => time(),
                'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
            ];
            // Send a notification to the user.
            if (Config::get('app.notifications.packetsfaq.answered')) {
                $notif_msg = trans('notifications.answered', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname]);
                Notification::getInsertNotification($question['user_id'], 'packetfaq', $notif_msg);
            }
            $this->post_faq_ans->InsertRecord($insertarr);
            $answers = $this->post_faq_ans->getAnswersByQuestionID((int)$id)->toArray();
            $answers = Common::getUserProfilePicture($answers);
            
            return response()->json([
                'status' => true,
                'data' => view($this->theme_path . '.programs.myquestion_answers', ['answers' => $answers])->render(),
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Please enter a comment',
            ]);
        }
    }

    public function getMyFeeds()
    {
        if (SiteSetting::module('LHSMenuSettings', 'programs') != 'on') {
            return parent::getError($this->theme, $this->theme_path, 401);
        }
        $general = SiteSetting::module('General');
        $programs = $favorites = $sort_by = $feed_list = $category = $categories = $selected_feeds = $channelAnalyticsGrouped = $other_ids = $others_ids_checked = $noCategorylist = [];
        try {
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->header = view($this->theme_path . '.common.header');
            $this->layout->footer = view($this->theme_path . '.common.footer');
            
            $page_no = 0;
            $records_per_page = array_get($general, 'setting.products_per_page', 10);
            $order_by = $general->setting['sort_by'];
            $category = $selected_feeds = $feed_list = $channelAnalyticsGrouped = $programTypeArray = $other_ids = [];
            $sub_program_slugs = $this->program_service->getUserProgramSlugs();
            $all_feed_list = Program::getCategoryRelatedFeedAssets($category, $sub_program_slugs);
            $all_feed = collect($all_feed_list);
            $package_ids = $all_feed->lists('package_ids');
            $package_ids = array_unique(array_filter(array_flatten($package_ids))); /*Get package categories */
            $categories = Category::getAssignedFeedsByCategory(array_column($all_feed_list, 'program_id'), $package_ids);
            $categories = collect($categories);
            
            $sub_cats = $categories->map(function ($item, $key) {
                return array_pluck(array_get($item, 'children', []), 'category_id');
            })->flatten();
            $sub_cat_name = collect(Category::getCategoryName($sub_cats->toArray()));
            $categories = $categories->map(function ($item, $key) use ($sub_cat_name){
                $sub_cat_details = [];
                $all_channel_ids = array_merge( (isset($item['relations']['assigned_feeds']) ? $item['relations']['assigned_feeds'] : []),
                             (isset($item['relations']['assigned_courses']) ? $item['relations']['assigned_courses'] : []),
                             (isset($item['package_ids']) ? $item['package_ids'] : [])
                            );

                if(isset($all_channel_ids) && !empty($all_channel_ids) && isset($item['children']) && !empty($item['children']) ){
                    $sub_cat_id = array_pluck($item['children'], 'category_id');
                     $sub_cat_keys = $sub_cat_name->groupBy('category_id')->toArray();
                     if(array_intersect($sub_cat_id,  array_keys($sub_cat_keys) )) {
                        $sub_cat_details = $sub_cat_keys;
                    }
                    $item['children'] = $sub_cat_details;
                }
                return $item;
            })->toArray();
            
            /*To provide other option for the channel which does not contain any category , other_ids are cat ids  */
            $other_ids = $noCatid = $this->getChannelsWithoutCategories($all_feed_list, Input::get('other_ids'));
            $noCategorylist = [];
            if (Input::get('other_ids')) {
                $integerIDs = array_map('intval', explode(',', $noCatid));
                $noCategorylist = $all_feed->whereIn('program_id', $integerIDs)->toArray();
            }
            /*above code ends here */

            if ($catId = Input::get('category')) {
                foreach (Input::get('category') as $cat) {
                    $category[] = (int)$cat;
                }
            }

            if ($subCatId = Input::get('sub_category')) {
                foreach (Input::get('sub_category') as $sub_cat) {
                    $category[] = (int)$sub_cat;
                }
            }

            if (Input::get('feed')) {
                foreach (Input::get('feed') as $feed) {
                    $selected_feeds[] = (int)$feed;
                }
            }

            if (Input::get('sort_by')) {
                $sort_by = Input::get('sort_by');
            } else {
                $sort_by = 'new_to_old';
            }

            if (Input::get('other_ids')) {
                $others_ids_checked = true;
            } else {
                $others_ids_checked = false;
            }

            $feed_list = Program::getCategoryRelatedFeedAssets($category, $sub_program_slugs);
            if (Input::get('category') || Input::get('sub_category') || Input::get('other_ids') || Input::get('feed')) {
                $program_ids = array_pluck($feed_list, 'program_id');
                $selected = array_intersect($selected_feeds, $program_ids);
                if (!empty($selected)) {
                    $feed_ids = $selected_feeds;
                } else {
                    $feed_ids = $program_ids;
                }
            } else {
                $feed_ids = $selected_feeds;
            }

            $programs_count = Program::getProgramsCountUsingSlugs($category, $feed_ids, $sub_program_slugs);

            $programs = [];
            /* below code is for to combine noCatlist array with the $programs for displaying   */
            if (!empty($noCatid) && (!empty($catId) || !empty($subCatId))) {
                $feed_ids = array_unique( array_merge($feed_ids, array_pluck($noCategorylist, 'program_id') ) );
                $sub_program_slugs = array_unique( array_merge($sub_program_slugs, array_pluck($noCategorylist, 'program_slug') ) );
            } elseif (!empty($noCatid) && (empty($catId) || empty($subCatId))) {
                $feed_ids = array_unique( array_merge($feed_ids, array_pluck($noCategorylist, 'program_id') ) );
                $sub_program_slugs = array_unique( array_merge($sub_program_slugs, array_pluck($noCategorylist, 'program_slug') ) );
            }
            Session::put('myfeeds_category', $category);
            Session::put('myfeeds_feed_ids', $feed_ids);
            Session::put('myfeeds_sort_by', $sort_by);
            Session::put('myfeeds_sub_program_slugs', $sub_program_slugs);
            Session::put('programs_count', $programs_count);
            Session::put('other_ids', $other_ids);
            $programs = Program::getProgramsSortBy($category, $feed_ids, $sub_program_slugs, $sort_by, $records_per_page, $page_no);
            /*above code ends here */

            // over all channel analytics code;
            if (!empty($programs)) {
                $channelAnalyticsGrouped = OverAllChannelAnalytic::getChannelAnalytics(
                    array_filter(array_pluck($programs, 'program_id')),
                    (int)Auth::user()->uid
                )->keyBy('channel_id');
            }
            $favorites = array_get(Auth::user(), 'favourited_packets', []);
            Session::put('favorites', $favorites);
            
        $programs = $this->program_service->getAllDetailsOfProgram($programs, $order_by, array_pluck($programs, 'program_slug'))->toArray();
        } catch (NoProgramAssignedException $e) {
            Log::info('No program is assigned to this user');
        }
        $this->layout->content = view($this->theme_path . '.programs.my_feeds')
        ->with(['programs' => $programs,
            'favorites' => $favorites, 
            'sort_by' => $sort_by, 
            'content_feeds' => $feed_list, 
            'cat_ids' => $category, 
            'feeds' => 0, 
            'cat_filter' => 1, 
            'feed_ids' => $selected_feeds, 
            'channelAnalytics' => $channelAnalyticsGrouped, 
            'other_ids' => true, 
            'others_ids_checked' => $others_ids_checked, 
            'noCategorylist' => $noCategorylist, 
            'typeFilter' => 1, 
            'categories' => $categories, 
            'general' => $general
        ]);
    }

    /**
     * [getCourseByBatch list parent course]
     * @method getCourseByBatch
     * @param  [type]           $programs [list of programs]
     * @return [type]                     [list parent course]
     */
    private function getCourseByBatch($programs = null)
    {
        $parent_ids = [];
        $parent_programs = [];

        if (empty($programs)) {
            $user = Auth::user();

            $user_array = $user->toArray();

            if (isset($user_array['relations']) && isset($user_array['relations']['user_course_rel'])) {
                $progarm_ids_list = $user_array['relations']['user_course_rel'];

                $programs = Program::whereIn('program_id', $progarm_ids_list)->get();
            }
        }

        if (!empty($programs)) {
            array_where($programs, function ($key, $value) use (&$parent_ids) {
                if ($value['program_type'] == 'course' &&
                    isset($value['parent_id']) &&
                    !empty($value['parent_id'])
                ) {
                    $parent_ids[] = $value['parent_id'];
                }
            });
        }

        if (!empty($parent_ids)) {
            $parent_programs = Program::whereIn('program_id', $parent_ids)
                ->get(['program_title', 'program_id'])
                ->toArray();
        }

        return $parent_programs;
    }

    public function getFeedsNextRecords()
    {
        $category = $feed_ids = $sub_program_slugs = $channelAnalyticsGrouped = [];
        $sort_by = null;
        $general = SiteSetting::module('General');
        $records_per_page = array_get($general, 'setting.products_per_page', 10);
        $page_no = Input::get('pageno');
        $favorites = Session::get('favorites');
        $category = Session::get('myfeeds_category');
        $feed_ids = Session::get('myfeeds_feed_ids');
        $sub_program_slugs = Session::get('myfeeds_sub_program_slugs');
        $sort_by = Session::get('myfeeds_sort_by');
        $programs_count = Session::get('programs_count');
        $order_by = $general->setting['sort_by'];
        
        $programs = Program::getProgramsSortBy($category, $feed_ids, $sub_program_slugs, $sort_by, $records_per_page, $page_no);
        $programs = $this->program_service->getAllDetailsOfProgram($programs, $order_by, array_pluck($programs, 'program_slug'))->toArray();
        // over all channel analytics code;
        if (!empty($programs)) {
            $channelAnalyticsGrouped = OverAllChannelAnalytic::getChannelAnalytics(
                array_filter(array_pluck($programs, 'program_id')),
                (int)Auth::user()->uid
            )->keyBy('channel_id');
        }
        if (!empty($programs)) {
            return response()->json([
                'status' => true,
                'data' => view($this->theme_path . '.programs.myfeeds_ajax_load', ['programs' => $programs, 'favorites' => $favorites, 'channelAnalytics' => $channelAnalyticsGrouped, 'general' => $general])->render(),
                'count' => count($programs),
            ]);
        } else {
            return response()->json([
                'status' => false,
                'data' => '<div class="col-md-12 center l-gray"><p><strong>' . trans('pagination.no_more_records') . '</strong></p></div>',
            ]);
        }
    }

    public function getFavourites()
    {
        if ((SiteSetting::module('LHSMenuSettings', 'programs') != 'on') || (SiteSetting::module('General', 'favorites') != 'on')) {
            return parent::getError($this->theme, $this->theme_path, 401);
        }

        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $favorites = Auth::user()->favourited_packets;

        if (empty($favorites)) {
            $favorites = [];
        }

        $expired_packets = [];

        $sub_program_slugs = TransactionDetail::getProgramIds(Auth::user()->uid, 'true', 'all');
        $expired_feeds = Program::getExpiredFeedSlugs($sub_program_slugs);
        $expired_packets = Packet::getPacketIdsUsingSlugs($expired_feeds);

        $category = [];
        $selected_feeds = [];
        $parentCat = [];

        if (Input::get('category')) {
            foreach (Input::get('category') as $cat) {
                $category[] = (int)$cat;
            }
        }

        if (Input::get('sub_category')) {
            foreach (Input::get('sub_category') as $sub_cat) {
                $category[] = (int)$sub_cat;
            }
        }

        if (Input::get('feed')) {
            foreach (Input::get('feed') as $feed) {
                $selected_feeds[] = (int)$feed;
            }
        }

        $feed_list = Program::getCategoryRelatedFeedAssets($category, $sub_program_slugs);
        if (Input::get('category') || Input::get('sub_category') && Input::get('feed')) {
            $program_ids = array_pluck($feed_list, 'program_id');
            $selected = array_intersect($selected_feeds, $program_ids);
            if (!empty($selected)) {
                $feed_ids = $selected_feeds;
            } else {
                $feed_ids = $program_ids;
            }
        } else {
            $feed_ids = $selected_feeds;
        }

        $program_slugs = Program::getCategoryRelatedProgramSlugs($category, $feed_ids, $sub_program_slugs);
        $packet_ids = Packet::getPacketIdsUsingSlugs($program_slugs);
        // $favorites = array_intersect($packet_ids, $favorites);
        if (Input::get('sort_by')) {
            $sort_by = Input::get('sort_by');
        } else {
            $sort_by = 'new_to_old';
        }
        $records_per_page = SiteSetting::module('General', 'products_per_page', 10);
        $page_no = 0;
        $packets_count = Packet::getPacketsCountUsingIds($favorites);

        Session::put('favorites', $favorites);
        Session::put('expired_packets', $expired_packets);
        Session::put('favorites_packets_count', $packets_count);
        Session::put('sort_by', $sort_by);

        $orderby = SiteSetting::module('General', 'sort_by');
        $packets = Packet::getPacketsUsingIdsSortBy($favorites, $sort_by, $records_per_page, $page_no, $orderby);

        $array_packets = MyActivity::getNewCompletedPackets($packets);
        $this->layout->content = view($this->theme_path . '.programs.favourites')->with(['packets' => $packets, 'favorites' => $favorites, 'array_packets' => $array_packets, 'expired_packets' => $expired_packets, 'sort_by' => $sort_by, 'content_feeds' => $feed_list, 'cat_ids' => $category, 'feeds' => 0, 'cat_filter' => 1, 'feed_ids' => $selected_feeds, 'typeFilter' => 0]);
    }

    public function getFavNextRecords()
    {
        $packets = [];
        $favorites = [];
        $sort_by = null;
        $array_packets = [];
        $expired_packets = [];
        $records_per_page = SiteSetting::module('General', 'products_per_page', 10);
        $page_no = Input::get('pageno');

        $sort_by = Session::get('sort_by');
        $packets_count = Session::get('favorites_packets_count');
        $favorites = Session::get('favorites');
        $expired_packets = Session::get('expired_packets');

        $orderby = SiteSetting::module('General', 'sort_by');
        $packets = Packet::getPacketsUsingIdsSortBy($favorites, $sort_by, $records_per_page, $page_no, $orderby);
        $array_packets = MyActivity::getNewCompletedPackets($packets);

        if (!empty($packets)) {
            return response()->json([
                'status' => true,
                'data' => view($this->theme_path . '.programs.favourites_ajax_load', ['packets' => $packets, 'favorites' => $favorites, 'expired_packets' => $expired_packets, 'array_packets' => $array_packets])->render(),
                'count' => count($packets),
            ]);
        } else {
            return response()->json([
                'status' => false,
                'data' => '<div class="col-md-12 center l-gray"><p><strong>' . trans('pagination.no_more_records') . '</strong></p></div>',
            ]);
        }
    }

    public function getPackets($feed_slug = null, $from = null)
    {
        if ($feed_slug == null) {
            return parent::getError($this->theme, $this->theme_path);
        }

        $lms_menu_settings = SiteSetting::module('LHSMenuSettings');
        $user_id = Auth::user()->uid;
        $subscribed_feeds = $this->program_service->getUserProgramSlugs();
        $program = Program::getFeedArray($feed_slug);
        if ((empty($program) || !in_array($feed_slug, $subscribed_feeds)) && !Auth::user()->super_admin) {
            return parent::getError($this->theme, $this->theme_path);
        }

        if (isset($program[0]['program_id']) && $program[0]['program_id'] != 0) {
            $channelAnalytics = OverAllChannelAnalytic::getChannelAnalytics(
                array_filter([$program[0]['program_id']]),
                (int)Auth::user()->uid
            );
        } else {
            $channelAnalytics = [];
        }

        $this->layout->theme = 'portal/theme/' . $this->theme;
        //$this->layout->leftsidebar = view($this->theme_path.'.common.leftsidebar');
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        //$program=Program::getFeedArray($feed_slug);
        $program = $program[0];
        $now = time();

        if (Timezone::getTimeStamp($program['program_startdate']) > $now) {
            $channel_status = "coming_soon";
        } elseif (Timezone::getTimeStamp($program['program_enddate']) < $now) {
            $channel_status = "expired";
        } elseif ($program['status'] == 'IN-ACTIVE') {
            $channel_status = "inactive";
        } else {
            $channel_status = false;
        }

        $categories = Category::getFeedRelatedCategory($program['program_id']);
        if (empty($categories)) {
            $categories = [];
        }
        $packets_count = Packet::getPacketsCountUsingSlug($feed_slug);
        $liked_packets = Packet::getLikedPackets($feed_slug);
        $sort_by = SiteSetting::module('General', 'sort_by');
        $packets = Packet::getPacketsUsingSlug($feed_slug, $sort_by);

        $favorites = Auth::user()->favourited_packets;
        if (empty($favorites)) {
            $favorites = [];
        }

        if ($from == null) {
            $array = [
                'module' => 'contentfeed',
                'action' => 'view',
                'module_name' => $program['program_title'],
                'module_id' => (int)$program['program_id'],
                'url' => Request::path(),
            ];
            MyActivity::getInsertActivity($array);
        }

        if (Input::get('tab_enabled')) {
            $tab_enabled = Input::get('tab_enabled');
        } else {
            $tab_enabled = 'posts';
        }

        Session::put('tab_enabled', $tab_enabled);


        //Channel question code starts here
        $page_no = 0;
        $records_per_page = SiteSetting::module('General', 'products_per_page', 10);
        if ($records_per_page == '') {
            $records_per_page = 10;
        }
        $filter = 'all';

        $questions = ChannelFaq::getChannelQuestions($program['program_id'], $filter, $page_no, $records_per_page);
        Common::getUserProfilePicture($questions);
        $questions_count = ChannelFaq::getChannelQuestionsCount($program['program_id']);
        //Channel question code ends here
        $this->layout->content = view($this->theme_path . '.programs.feed_packets')->with(['program' => $program, 'favorites' => $favorites, 'packets_count' => $packets_count, 'liked_packets' => $liked_packets, 'packets' => $packets, 'categories' => $categories, 'other_channel' => 0, 'channel_status' => $channel_status, 'questions' => $questions, 'questions_count' => $questions_count, 'channelAnalytics' => $channelAnalytics, 'lms_menu_settings' => $lms_menu_settings]);
    }

    public function getPacketFavourited($action, $packet_id)
    {
        $packet_info = Packet::getPacketInfo($packet_id);
        switch ($action) {
            case 'favourite':
                Packet::updateFavouriteCount($packet_id, 'true', $packet_info[0]['packet_slug'], $packet_info[0]['feed_slug']);
                break;

            case 'unfavourite':
                Packet::updateFavouriteCount($packet_id, 'false', $packet_info[0]['packet_slug'], $packet_info[0]['feed_slug']);
                break;

            default:
                return response()->json([
                    'status' => false,
                    'packet_id' => (int)$packet_id,
                ]);
                break;
        }
        $userID = Auth::user()->uid;
        $playlyfeEvent = [
            "type" => "action",
            "data" => [
                "user_id" => $userID,
                "action_id" => "favorite"
            ]
        ];

        $this->playlyfe->processEvent($playlyfeEvent);
        return response()->json([
            'status' => true,
            'packet_id' => (int)$packet_id,
        ]);
    }

    public function getElementLiked($action, $element_id, $element_type, $packet_id)
    {
        $packet_info = Packet::getPacketInfo($packet_id);
        switch ($action) {
            case 'star':
                Packet::updateElementLikedCount('true', $element_id, $element_type, $packet_id, $packet_info[0]['packet_slug'], $packet_info[0]['feed_slug']);
                break;

            case 'unstar':
                Packet::updateElementLikedCount('false', $element_id, $element_type, $packet_id, $packet_info[0]['packet_slug'], $packet_info[0]['feed_slug']);
                break;

            default:
                return response()->json([
                    'status' => false,
                    'packet_id' => (int)$element_id,
                ]);
                break;
        }

        return response()->json([
            'status' => true,
            'element_id' => (int)$element_id,
        ]);
    }

    public function getMoreFeeds()
    {
        if ((SiteSetting::module('LHSMenuSettings', 'programs') != 'on') || (SiteSetting::module('General', 'more_feeds') != 'on')) {
            return parent::getError($this->theme, $this->theme_path, 401);
        }
        $category = [];
        $selected_feeds = [];
        $parentCat = [];

        if (Input::get('category')) {
            foreach (Input::get('category') as $cat) {
                $category[] = (int)$cat;
            }
        }

        if (Input::get('sub_category')) {
            foreach (Input::get('sub_category') as $sub_cat) {
                $category[] = (int)$sub_cat;
            }
        }
        if (Input::get('feed')) {
            foreach (Input::get('feed') as $feed) {
                $selected_feeds[] = (int)$feed;
            }
        }
        User::getUserSubscribedFeedIds();

        Session::put('category', $category);
        Session::put('selected_feeds', $selected_feeds);

        $sub_feed_ids = Session::get('sub_feed_ids');

        $records_per_page = SiteSetting::module('General', 'products_per_page', 10);
        $page_number = 0;

        if (Input::get('sort_by')) {
            $sort_by = Input::get('sort_by');
        } else {
            $sort_by = 'new_to_old';
        }
        Session::put('sort_by', $sort_by);

        $content_feeds = Program::getAllPrograms($type = 'content_feed', $slug = '', $category, $sub_feed_ids, $records_per_page, $page_number, $selected_feeds, $sort_by, 'general_access');

        $feed_list = Program::getCategoryRelatedFeeds($category, $sub_feed_ids);

        $categories = Category::getCategoryWithRelation();
        if (is_array($categories) && !empty($categories)) {
            foreach ($categories as $catkey => $value) {
                if (!empty($value['relations']) && (array_key_exists("assigned_feeds", $value['relations']) && !empty($value['relations']['assigned_feeds']))) {
                    if (array_key_exists("children", $value) && !empty($value['children'])) {
                        $subCat = array_pull($value, "children");
                        foreach ($subCat as $key => $val) {
                            $subCatName = Category::getCategoryName($val['category_id']);
                            $subCatName = $subCatName[0];
                            $categories[$catkey]['children'][$key] = ['category_id' => $val['category_id'], 'category_name' => html_entity_decode($subCatName['category_name'])];
                        }
                    }
                } else {
                    unset($categories[$catkey]); // unset the category which has empty relations && relations.assigned_feeds
                }
            }
        }

        $this->layout->theme = 'portal/theme/' . $this->theme;
        //$this->layout->leftsidebar = view($this->theme_path.'.common.leftsidebar');
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->content = view($this->theme_path . '.programs.more_feeds_list', ['more_feeds' => $content_feeds, 'content_feeds' => $feed_list, 'cat_ids' => $category, 'feeds' => 0, 'cat_filter' => 1, 'feed_ids' => $selected_feeds, 'sort_by' => $sort_by, 'typeFilter' => 0, 'categories' => $categories]);
    }

    public function getFeedDetail($feed_slug, $from = null)
    {
        $this->layout->theme = 'portal/theme/' . $this->theme;
        //$this->layout->leftsidebar = view($this->theme_path.'.common.leftsidebar');
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');

        $feed_info = Program::getAllPrograms($type = 'content_feed', $feed_slug);
        $feed_info = $feed_info[0];
        $cat_feed_info = Category::getFeedRelatedCategory($feed_info['program_id']);
        $packets_count = Packet::getPacketsCountUsingSlug($feed_slug);
        $sort_by = SiteSetting::module('General', 'sort_by');
        $packets = Packet::getPacketsUsingSlug($feed_slug, $sort_by);
        $liked_packets = 0;

        if ($from == null) {
            $array = [
                'module' => 'contentfeed',
                'action' => 'view',
                'module_name' => $feed_info['program_title'],
                'module_id' => (int)$feed_info['program_id'],
                'url' => Request::path(),
            ];
            MyActivity::getInsertActivity($array);
        }

        $questions = [];

        $this->layout->content = view($this->theme_path . '.programs.feed_packets')->with(['program' => $feed_info, 'cat_feed_info' => $cat_feed_info, 'packets_count' => $packets_count, 'packets' => $packets, 'other_channel' => 1, 'liked_packets' => $liked_packets, 'channel_status' => false, 'questions' => $questions]);
    }

    public function postNextRecords()
    {
        $category = $sub_feed_ids = [];
        $sub_feed_ids = Session::get('sub_feed_ids');
        $category = Session::get('category');
        $selected_feeds = Session::get('selected_feeds');
        $sort_by = Session::get('sort_by');

        $records_per_page = SiteSetting::module('General', 'products_per_page', 10);
        $page_number = Input::get('pageno');

        $content_feeds = Program::getAllPrograms($type = 'content_feed', $slug = '', $category, $sub_feed_ids, $records_per_page, $page_number, $selected_feeds, $sort_by, 'general_access');
        $more_feeds_count = Program::getAllProgramsCount($type = 'content_feed', $category, $sub_feed_ids, $selected_feeds, $sort_by);

        $total_page_num = ($more_feeds_count / $records_per_page);

        // if($page_number>$total_page_num)
        // {
        //  return 0;
        // }
        if (empty($content_feeds)) {
            return response()->json([
                'status' => false
            ]);
        }
        $output = '';
        $output .= "<div class='col-md-12'>
              <div class='xs-margin'></div>";

        if (isset($content_feeds)) {
            foreach ($content_feeds as $feed) {
                $output .= "<div>
                  <h3 class='page-title-small'><a href=''>" . $feed['program_title'] . "</a></h3>
                </div>
                <div class='md-margin cf-info'>
                  <div class='row'>
                    <div class='col-md-3 col-sm-4 col-xs-11 xs-margin'>";
                if (empty($feed['program_cover_media'])) {
                    $output .= "<img src='" . asset('portal/theme/default/img/default_packet.jpg') . "' title='" . $feed['program_title'] . "' class='packet-img img-responsive center-align' alt='" . $feed['program_title'] . "'>";
                } else {
                    $output .= "<img src='" . URL::to('media_image/' . $feed['program_cover_media']) . "' title='" . $feed['program_title'] . "' class='packet-img img-responsive center-align' alt='" . $feed['program_title'] . "'>";
                }
                $output .= '</div>';
                $packet_count = Program::getPacketsCount($feed['program_slug']);
                $category = '';
                $cat_feed_info = Category::getFeedRelatedCategory($feed['program_id']);
                $output .= "<div class='cl-lg-8 col-md-7 col-sm-8 col-xs-11'>";
                $output .= '<p>' . $feed['program_description'] . '</p>';
                foreach ($cat_feed_info as $info) {
                    $category .= html_entity_decode($info['category_name']) . ',';
                }
                $output .= "<table class='sm-margin'>";
                if ($category != '') {
                    $output .= "<tr>
                            <td width='140px'><strong>Category</strong></td>
                            <td>" . ucwords(strtolower(trim($category, ','))) . '</td>
                          </tr>';
                }
                if (isset($feed['program_startdate'])) {
                    $output .= "<tr>
                            <td width='140px'><strong>Start Date</strong></td>
                            <td>" . Timezone::convertFromUTC('@' . $feed['program_startdate'], Auth::user()->timezone) . '</td>
                          </tr>';
                }
                if (isset($feed['program_enddate'])) {
                    $output .= "<tr>
                            <td width='140px'><strong>End Date</strong></td>
                            <td>" . Timezone::convertFromUTC('@' . $feed['program_enddate'], Auth::user()->timezone) . '</td>
                          </tr>';
                }
                if (isset($packet_count)) {
                    $output .= "<tr>
                            <td width='140px'><strong>No. of " . trans('program.packets') . '</strong></td>
                            <td>' . $packet_count . '</td>
                          </tr>';
                }
                $output .= "<tr>
                          <td width='140px'><strong>Status</strong></td>
                          <td>Available</td>
                        </tr>
                      </table>
                      <p>
                       <a href='" . URL::to('program/feed-detail/' . $feed['program_slug']) . "' class='btn red-sunglo btn-sm xs-margin'>More Info</a>&nbsp;&nbsp;&nbsp;";
                if (isset($feed['relations']['access_request_pending']) && in_array(Auth::user()->uid, $feed['relations']['access_request_pending'])) {
                    $output .= '<b> Your request for this content feed is in process </b>';
                } else {
                    $output .= "<a  href='" . URL::to('program/feed-access-request/' . $feed['program_id']) . "' onClick=button_clicked(" . $feed['program_id'] . ") id='" . $feed['program_id'] . "' class='btn red-sunglo btn-sm xs-margin'>Request Access</a>";
                }
                $output .= '</p>
                    </div>
                  </div>
                </div>';
            }
        }
        $output .= '</div>';

        return response()->json([
            'status' => true,
            'data' => $output,
        ]);
    }

    /*More feed access request */
    public function getFeedAccessRequest($id, $from = null)
    {
        $feed_details = Program::getProgramDetailsByID($id);
        AccessRequest::insertAccessRequests($id, $feed_details['program_title'], $feed_details['program_slug']);
        $site_name = config('app.site_name');

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
        $headers .= 'From:' . $site_name . "\r\n";
        $name = 'Feed Access Request';
        $user_name = Auth::user()->username;
        $sender_name = Auth::user()->firstname . ' ' . Auth::user()->lastname;
        $support_email = config('mail.from.address');
        $phone_number = '';
        $base_url = config('app.url');
        $login_url = '<a href="' . $base_url . '/auth/login">Click here to login</a>';
        $to = $user_email = Auth::user()->email;
        $user_id = Auth::user()->uid;

        if ($from == null) {
            $array = [
                'module' => 'contentfeed',
                'action' => 'RequestAccess',
                'module_name' => $feed_details['program_title'],
                'module_id' => (int)$id,
                'url' => URL::to('program/more-feeds'),
            ];
            MyActivity::getInsertActivity($array);
        }

        $creater_details = User::where('username', '=', $feed_details['created_by'])->get()->toArray();

        $message = 'Access Request for' . $feed_details['program_title'];
        Notification::getInsertNotification((int)$user_id, $from_module = 'More ' . trans('program.programs'), $message);
        $message = 'Access Request for' . $feed_details['program_title'] . ' from ' . $user_name;
        Notification::getInsertNotification((int)$creater_details[0]['uid'], $from_module = 'More ' . trans('program.programs'), $message);

        $email_details = Email::getEmail('channel-access-request-from-user');

        $body = $subject = '';
        $subject = $email_details[0]['subject'];
        $body = $email_details[0]['body'];

        $subject_find = ['<SITE NAME>'];
        $subject_replace = [$site_name];
        $subject = str_replace($subject_find, $subject_replace, $subject);

        $find = ['<USERNAME>', '<SITE NAME>', '<EMAIL>', '<CONTENT FEED NAME>', '<SUPPORT EMAIL>'];
        $replace = [$sender_name, $site_name, $user_email, $feed_details['program_title'], $support_email];
        $body = str_replace($find, $replace, $body);

        Common::sendMailHtml($body, $subject, $to = $user_email, $headers, $cc = null, $bcc = null); // Sending mail to user who Request for Access


        $body = $subject = $to = '';
        $email_details = Email::getEmail('channel-access-request-for-admin');
        $subject = $email_details[0]['subject'];
        $body = $email_details[0]['body'];

        $subject_find = ['<SITE NAME>'];
        $subject_replace = [$site_name];
        $subject = str_replace($subject_find, $subject_replace, $subject);

        $find = ['<ADMIN>', '<SITE NAME>', '<EMAIL>', '<CONTENT FEED NAME>', '<NAME>', '<LOGIN URL>', '<SUPPORT EMAIL>'];
        $replace = [$feed_details['created_by_name'], $site_name, $user_email, $feed_details['program_title'], $sender_name, $login_url, $support_email];
        $body = str_replace($find, $replace, $body);

        Common::sendMailHtml($body, $subject, $to = $creater_details[0]['email'], $headers, $cc = null, $bcc = null); // Sending mail to user who Request for Access

        $success = 'Your request for channel is successful and it will be processed shortly.';

        return redirect('program/more-feeds')->with('success', $success);
    }

    private function viewDocument($media = '')
    {
        $embed_url = '';
        if ($media && $media->type == "document" && $media->asset_type == 'file') {
            try {
                $document_id = array_get($media, 'box_details.document_id', null);
                if (is_null($document_id)) {
                    $document_id = $this->box_service->uploadToBox($media)->document_id;
                }
                $embed_url = $this->box_service->getEmbedUrl((int)$document_id);
            } catch (Exception $e) {
                Log::error('BOX: errors '.$e->getMessage());
            }
        }
        return $embed_url;
    }

    /**Display channel category wise for my course tab **/
    public function getCategoryChannel()
    {
        try {
            if ((SiteSetting::module('LHSMenuSettings', 'programs') != 'on') || (SiteSetting::module('General', 'general_category_feeds') != 'on')) {
                return parent::getError($this->theme, $this->theme_path, 401);
            }
            $general = SiteSetting::module('General');
            $user_enrollment = $this->getUserCategoryChannel();
            $this->layout->content = view($this->theme_path . '.programs.category_channels')
                ->with('user_enrollment', $user_enrollment)
                ->with('cat_filter', 1)
                ->with('general', $general);
        } catch (NoProgramAssignedException $e) {
            $this->layout->content = view($this->theme_path . '.programs.category_channels')
               ->with('error', trans('program.no_channels'));
        }
        $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->header = view($this->theme_path . '.common.header');
            $this->layout->footer = view($this->theme_path . '.common.footer');
    }

    public function getUserCategoryChannel()
    {
        $page_no = Input::get('pageno', 0);
        if (Request::ajax()) {
            $page_no = Input::get('pageno');
            $data = $this->program_service->getUserEnrollmentsAndCategories(Auth::user()->uid, $page_no);
             return response()->json([
                'status' => true,
                'data' => view($this->theme_path . '.programs.category_channel_ajax_load', ['user_enrollment' => $data])->render(),
                'count' => count($data['catergory_with_program'])
             ]);
        } else {
             return $this->program_service->getUserEnrollmentsAndCategories(Auth::user()->uid, $page_no);
        }
    }

    /** Nayan - Enrolling user to specific product **/
    public function postEnrollUserToProduct()
    {
        $uid = (int)Auth::user()->uid;
        $id = (int)Input::get('product_id');
        $existing_relation = TransactionDetail::where('program_id', $id)
            ->where('requested_by_user', $uid)
            ->value('id');
        if (!isset($existing_relation) && empty($existing_relation) && $id > 0) {
            $feed_details = Program::getProgramDetailsByID($id);
            $req_id = AccessRequest::insertAccessRequests($id, $feed_details['program_title'], $feed_details['program_slug']);
            AccessRequest::grantAccess($req_id, "FREE", "ecommerce", $feed_details['program_type']);
        }
        return "success";
    }


    //Channel forum starts here

    public function getChannelNextQuestions($program_id)
    {
        if (Request::ajax()) {
            $page_no = 0;
            $records_per_page = SiteSetting::module('General', 'products_per_page', 10);
            if ($records_per_page == '') {
                $records_per_page = 10;
            }

            $filter = (Input::get('filter')) ? Input::get('filter') : 'all';

            if (preg_match('/^[0-9]+$/', Input::get('page_no'))) {
                $page_no = Input::get('page_no');
            }

            $questions = ChannelFaq::getChannelQuestions($program_id, $filter, $page_no, $records_per_page);
            Common::getUserProfilePicture($questions);

            if (!empty($questions)) {
                return response()->json([
                    'status' => true,
                    'data' => view($this->theme_path . '.programs.channelquestions_ajax_load', ['questions' => $questions, 'program_id' => $program_id])->render(),
                    'count' => count($questions),
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'data' => '<div class="col-md-12 center l-gray"><p><strong>' . trans('pagination.no_more_records') . '</strong></p></div>',
                ]);
            }
        } else {
            return parent::getError($this->theme, $this->theme_path);
        }
    }

    public function getQuestionChannel($program_id, $program_slug)
    {
        if (Request::ajax()) {
            if (!empty(Input::get('ques'))) {
                ChannelFaq::getInsert(Input::get('ques'), $program_id, $program_slug);


                $records_per_page = SiteSetting::module('General', 'products_per_page', 10);
                if ($records_per_page == '') {
                    $records_per_page = 10;
                }
                $page_no = preg_match('/^[0-9]+$/', Input::get('page_no'));
                $records_per_page = ($page_no + 1) * $records_per_page;
                $page_no = 0;

                $filter = (Input::get('filter')) ? Input::get('filter') : 'all';
                $questions = ChannelFaq::getChannelQuestions($program_id, $filter, $page_no, $records_per_page);
                Common::getUserProfilePicture($questions);
                return response()->json([
                    'status' => true,
                    'data' => view($this->theme_path . '.programs.channelquestions_ajax_load', ['questions' => $questions, 'program_id' => $program_id])->render(),
                    'message' => trans('program.ask_question_success'),
                ]);
            } elseif (Input::get('filter') && !Input::get('ques_submit')) {
                $records_per_page = SiteSetting::module('General', 'products_per_page', 10);
                if ($records_per_page == '') {
                    $records_per_page = 10;
                }
                $page_no = preg_match('/^[0-9]+$/', Input::get('page_no'));
                $records_per_page = ($page_no + 1) * $records_per_page;
                $skip = 0;

                $filter = Input::get('filter');

                $questions = ChannelFaq::getChannelQuestions($program_id, $filter, $skip, $records_per_page);
                Common::getUserProfilePicture($questions);
                return response()->json([
                    'status' => true,
                    'data' => view($this->theme_path . '.programs.channelquestions_ajax_load', ['questions' => $questions, 'program_id' => $program_id])->render(),
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Please enter a question',
                ]);
            }
        } else {
            return parent::getError($this->theme, $this->theme_path);
        }
    }

    public function getAnswerChannel($question_id)
    {
        if (Request::ajax()) {
            if (!empty(Input::get('ans'))) {
                $parent_id = 0;

                $userid = ChannelFaq::where('id', '=', (int)$question_id)->value('user_id');

                ChannelFaqAnswers::getInsert($question_id, Input::get('ans'), $parent_id, $userid);

                $answers = ChannelFaqAnswers::getAnswersByQuestionID($question_id);
                Common::getUserProfilePicture($answers);
                return response()->json([
                    'status' => true,
                    'data' => view($this->theme_path . '.programs.channelquestion_answers', ['answers' => $answers, 'question_id' => $question_id])->render(),
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Please enter a comment',
                ]);
            }
        } else {
            return parent::getError($this->theme, $this->theme_path);
        }
    }

    public function getChannelQuestionEdit($question_id)
    {
        if (Request::ajax()) {
            if (ChannelFaq::getQuestionsCount($question_id) == 0) {
                return parent::getError($this->theme, $this->theme_path);
            }

            if (!empty(Input::get('edit'))) {
                $result = ChannelFaq::getUpdate($question_id, Input::get('edit'));
                if ($result == true) {
                    return response()->json([
                        'status' => true,
                        'data' => Input::get('edit'),
                        'message' => trans('qanda.edit'),
                    ]);
                } else {
                    return parent::getError($this->theme, $this->theme_path);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Please enter a question',
                ]);
            }
        } else {
            return parent::getError($this->theme, $this->theme_path);
        }
    }

    public function getChannelQuestionDelete($program_id, $question_id)
    {
        if (Request::ajax()) {
            /*if (ChannelFaq::getQuestionsCount($question_id) == 0) {
                return parent::getError($this->theme, $this->theme_path);
            }*/

            $result = ChannelFaq::getDelete($question_id);
            $records_per_page = SiteSetting::module('General', 'products_per_page', 10);
            if ($records_per_page == '') {
                $records_per_page = 10;
            }
            $page_no = preg_match('/^[0-9]+$/', Input::get('page_no'));
            $records_per_page = ($page_no + 1) * $records_per_page;
            $page_no = 0;

            $filter = (Input::get('filter')) ? Input::get('filter') : 'all';

            if ($result == true) {
                $questions = ChannelFaq::getChannelQuestions($program_id, $filter, $page_no, $records_per_page);
                return response()->json([
                    'status' => true,
                    'data' => view($this->theme_path . '.programs.channelquestions_ajax_load', ['questions' => $questions, 'program_id' => $program_id])->render(),
                ]);
            } else {
                return parent::getError($this->theme, $this->theme_path);
            }
        } else {
            return parent::getError($this->theme, $this->theme_path);
        }
    }

    public function getChannelQuestionHide($program_id, $question_id)
    {
        if (Request::ajax()) {
            $type = Input::get('type');
            $result = ChannelFaq::getHideQuestion($question_id, $type);
            $records_per_page = SiteSetting::module('General', 'products_per_page', 10);
            if ($records_per_page == '') {
                $records_per_page = 10;
            }
            $page_no = preg_match('/^[0-9]+$/', Input::get('page_no'));
            $records_per_page = ($page_no + 1) * $records_per_page;
            $page_no = 0;

            $filter = (Input::get('filter')) ? Input::get('filter') : 'all';

            if ($result) {
                $questions = ChannelFaq::getChannelQuestions($program_id, $filter, $page_no, $records_per_page);
                return response()->json([
                    'status' => true,
                    'data' => view($this->theme_path . '.programs.channelquestions_ajax_load', ['questions' => $questions, 'program_id' => $program_id])->render(),
                ]);
            } else {
                return parent::getError($this->theme, $this->theme_path);
            }
        } else {
            return parent::getError($this->theme, $this->theme_path);
        }
    }

    public function getChannelQuestionLiked($action, $question_id)
    {
        if (Request::ajax()) {
            $result = ChannelFaq::updateQALikedCount($action, $question_id);

            $like_count = ChannelFaq::getLikedCount($question_id);
            if (($like_count == 1) || ($like_count == 0)) {
                $likes = $like_count . " Like";
            } else {
                $likes = $like_count . " Likes";
            }

            return response()->json([
                'status' => true,
                'qid' => (int)$question_id,
                'like_count' => $likes,
            ]);
        } else {
            return parent::getError($this->theme, $this->theme_path);
        }
    }

    public function getChannelAnswerDelete($question_id, $answer_id)
    {
        if (Request::ajax()) {
            $result = ChannelFaqAnswers::getDelete($answer_id);

            $userid = ChannelFaq::getUserId($question_id);
            $oldanswers = ChannelFaqAnswers::getAdminAnswers($question_id, $userid);

            if (empty($oldanswers)) {
                ChannelFaq::where('id', '=', (int)$question_id)->update(['status' => 'UNANSWERED']);
            }

            $answers = ChannelFaqAnswers::getAnswersByQuestionID($question_id);
            return response()->json([
                'status' => true,
                'data' => view($this->theme_path . '.programs.channelquestion_answers', ['answers' => $answers, 'question_id' => $question_id])->render(),
            ]);
        } else {
            return parent::getError($this->theme, $this->theme_path);
        }
    }

    public function getChannelAnswerHide($question_id, $answer_id)
    {
        if (Request::ajax()) {
            $type = Input::get('type');
            $result = ChannelFaqAnswers::getHideAnswer($answer_id, $type);

            $answers = ChannelFaqAnswers::getAnswersByQuestionID($question_id);
            return response()->json([
                'status' => true,
                'data' => view($this->theme_path . '.programs.channelquestion_answers', ['answers' => $answers, 'question_id' => $question_id])->render(),
            ]);
        } else {
            return parent::getError($this->theme, $this->theme_path);
        }
    }

    public function getMyPackages()
    {
        if (SiteSetting::module('General', 'package') != 'on') {
            return parent::getError($this->theme, $this->theme_path, 401);
        }
        $userid = Auth::user()->uid;
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->content = view($this->theme_path . '.programs.listpack')->with('userid', $userid);
    }

    public function putEntryInToOca($inputData = [])
    {
        if (empty($inputData)) {
            return false;
        }

        $returnFlag = false;
        $isViewedEle = false;
        $data = [];
        $completion = 0;
        $postCompletion = [];
        $itemDetails = [];
        $userId = (int)Auth::user()->uid;
        $viewedElement = $inputData['element_type'] . '_' . $inputData['module_id'];
        $postKey = 'p_' . $inputData['packet_id'];
        $channelId = (int)$inputData['feed_id'];
        $postDeatils = Packet::getPacketByID((int)$inputData['packet_id']);
        $chennelSlug = $postDeatils[0]['feed_slug'];
        $countEle = 1;
        $postElement = [];
        if (isset($postDeatils[0]['elements']) && !empty($postDeatils[0]['elements'])) {
            foreach ($postDeatils[0]['elements'] as $element) {
                $postElement[] = $element['type'] . '_' . $element['id'];
            }
            $countEle = count($postElement);
        }
        $postCountChannel = Packet::where('feed_slug', '=', $chennelSlug)
            ->where('status', '!=', 'DELETED')
            ->count();

        $isExists = OverAllChannelAnalytic::isExists($channelId, $userId);
        if (!is_null($isExists) || !empty($isExists)) {
            $existsCompletion = $isExists->completion;
            $existsPostCompletion = $isExists->post_completion;
            $existsItemDetails = $isExists->item_details;
        }
        if (!is_null($isExists) || !empty($isExists)) {
            if (isset($existsItemDetails[$postKey])) {
                $tempPostEleRaw = $existsItemDetails[$postKey];
                $tempPostEle = array_unique($tempPostEleRaw);
                if (in_array($viewedElement, $tempPostEle)) {
                    $isViewedEle = true;
                }
                $tempPostEle[] = $viewedElement;
                $tempPostEle = array_unique($tempPostEle);
                $viewedCount = count(array_intersect($tempPostEle, $postElement));
                $existsPostCompletion[$postKey] = round(
                    ($viewedCount / (($countEle > 1) ? $countEle : 1)) * 100,
                    2
                );
                $existsItemDetails[$postKey] = $tempPostEle;
            } else {
                $tempPostEle = [];
                $tempPostEle[] = $viewedElement;
                $viewedCount = count(array_intersect($tempPostEle, $postElement));

                $existsPostCompletion[$postKey] = $postCompletion[$postKey] = round(
                    (($viewedCount / (($countEle > 1) ? $countEle : 1)) * 100),
                    2
                );
                $existsItemDetails[$postKey] = $itemDetails[$postKey] = $tempPostEle;
            }
        } else {
            $tempPostEle = [];
            $tempPostEle[] = $viewedElement;
            $viewedCount = count(array_intersect($tempPostEle, $postElement));
            $postCompletion[$postKey] = round(
                ($viewedCount / (($countEle > 1) ? $countEle : 1)) * 100,
                2
            );
            $itemDetails[$postKey] = $tempPostEle;
        }
        if (!is_null($isExists) || !empty($isExists)) {
            $completion = round(
                (array_sum(array_values($existsPostCompletion))) /
                (($postCountChannel > 1) ? $postCountChannel : 1),
                2
            );
        } else {
            $completion = round(
                (array_sum(array_values($postCompletion))) /
                (($postCountChannel > 1) ? $postCountChannel : 1),
                2
            );
        }

        $data['user_id'] = $userId;
        $data['channel_id'] = $channelId;
        $data['post_count'] = $postCountChannel;
        if (!is_null($isExists) || !empty($isExists)) {
            $data['item_details'] = $existsItemDetails;
            $data['post_completion'] = $existsPostCompletion;
            $data['completion'] = $completion;
        } else {
            $data['item_details'] = $itemDetails;
            $data['post_completion'] = $postCompletion;
            $data['completion'] = $completion;
        }
        if (!is_null($isExists) || !empty($isExists)) {
            $data['updated_at'] = time();
            if ($data['completion'] >= 100) {
                if (isset($isExists->completed_at) && !empty($isExists->completed_at) && !$isViewedEle) {
                    $data['completed_at'] = $isExists->completed_at;
                    $data['completed_at'][] = time();
                } else {
                    $data['completed_at'] = [time()];
                }
            }
            $res = OverAllChannelAnalytic::updateData(
                $data,
                $data['channel_id'],
                $data['user_id']
            );
            if (!$res) {
                $returnFlag = false;
            }
        } else {
            $data['created_at'] = time();
            if ($data['completion'] >= 100) {
                $data['completed_at'] = [time()];
            }
            $res = OverAllChannelAnalytic::insertData($data);
            if (!$res) {
                $returnFlag = false;
            }
        }
        return $returnFlag;
    }

    private function getChannelsWithoutCategories($all_feed_list, $noCatid)
    {
        $other_ids = null;
        foreach ($all_feed_list as $key => $val) {
            if (array_key_exists("program_categories", $val) && empty($val['program_categories'])) {
                $other_ids .= $val['program_id'] . ",";
            }
        }
        $other_ids = rtrim($other_ids, ',');
        return $other_ids;
    }

    /* listProgramBasedOnType function is used for sending the program array and to filter based on the types */
    public function listProgramBasedOnType($typeSelectedArr = null, $programArr)
    {
        if (is_array($typeSelectedArr) && !empty($typeSelectedArr)) {
            $cnt = count($typeSelectedArr);
            $pcnt = count($programArr);
            $program_type = null;
            $program_sub_type = null;
            $final_data = [];
            foreach ($typeSelectedArr as $key => $val) {
                /* setting the value of program_type and program_sub_type */
                switch ($val) {
                    case "channel":
                        $program_type = 'content_feed';
                        $program_sub_type = 'single';
                        break;
                    case "course":
                        $program_type = 'course';
                        $program_sub_type = 'single';
                        break;
                    case "package":
                        $program_type = 'content_feed';
                        $program_sub_type = 'collection';

                        break;
                    default:
                        break;
                }
                $this->filterandFormFinalData($final_data, $programArr, $program_type, $program_sub_type);
            }
            return $final_data;
        }
    }

    public function filterandFormFinalData(&$final_data, $programArr, $program_type, $program_sub_type)
    {
        if (is_array($programArr)) {
            for ($i = 0; $i < count($programArr); $i++) {
                if ($programArr[$i]['program_type'] == $program_type && $programArr[$i]['program_sub_type'] == $program_sub_type) {
                    $final_data[] = $programArr[$i];
                }
            }
        }
    }

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

    public function isQuizPass($quiz_id)
    {
        if (!is_numeric($quiz_id)) {
            return false;
        }
        $isPassCriteria = Quiz::where('quiz_id', '=', (int)$quiz_id)
            ->where('pass_criteria', 'exists', true)
            ->first();
        if (is_null($isPassCriteria)) {
            return true;
        }
        $passedAttempts = QuizAttempt::where('quiz_id', '=', (int)$quiz_id)
            ->where('user_id', '=', Auth::user()->uid)
            ->where('status', '=', 'CLOSED')
            ->where('pass', '=', true)
            ->get();
        if ($passedAttempts->count() > 0) {
            return true;
        } else {
            return false;
        }
    }
}
