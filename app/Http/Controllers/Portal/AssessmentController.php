<?php

namespace App\Http\Controllers\Portal;

use App;
use App\Exceptions\Authentication\AccessDeniedException;
use App\Exceptions\Quiz\KeywordNotFoundException;
use App\Exceptions\Quiz\NoQuestionsFoundException;
use App\Exceptions\Quiz\QuestionTagMappingNotFoundException;
use App\Exceptions\Quiz\QuizAttemptClosedException;
use App\Helpers\Quiz\QuizHelper;
use App\Http\Controllers\PortalBaseController;
use App\Model\Common;
use App\Model\MyActivity;
use App\Model\OverAllChannelAnalytic;
use App\Model\OverAllQuizPerformance;
use App\Model\Packet;
use App\Model\Program;
use App\Model\Question;
use App\Model\QuestionTagMapping;
use App\Model\Quiz;
use App\Model\QuizAttempt;
use App\Model\QuizAttemptData;
use App\Model\QuizReport;
use App\Model\Section;
use App\Model\SiteSetting;
use App\Model\TransactionDetail;
use App\Model\User;
use App\Model\UserGroup;
use App\Enums\Quiz\CutoffFormatType as QCFT;
use App\Enums\QuizAttempt\QuizAttemptDataStatus;
use App\Services\Question\IQuestionService;
use App\Services\Program\IProgramService;
use App\Services\Quiz\IQuizService;
use App\Services\QuizAttempt\IQuizAttemptService;
use Auth;
use Carbon;
use Exception;
use Illuminate\Support\Collection;
use Input;
use Log;
use Request;
use Response;
use Session;
use Timezone;
use URL;

class AssessmentController extends PortalBaseController
{
    private $question_per_block = 5;

    /**
     * @var App\Services\QuizAttempt\IQuizAttemptService
     */
    private $attemptService;

    private $program_service;

    public function __construct(IProgramService $program_service, IQuizAttemptService $attemptService)
    {
        $this->attemptService = $attemptService;
        $this->program_service = $program_service;
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
        $this->question_per_block = config('app.question_per_block');
        if (empty($this->question_per_block)) {
            $this->question_per_block = 5;
        }
    }

    public function getIndex(IQuizService $quiz_service)
    {
        if (SiteSetting::module('General', 'assessments') != 'on') {
            return parent::getError($this->theme, $this->theme_path, 401);
        }
        $filter = Input::get('filter', 'unattempted');
        try {
            $user_quiz_rel = $quiz_service->getAllQuizzesAssigned();
        } catch (\Exception $e) {
            Log::info($e);
            $user_quiz_rel = ['seq_quizzes' => [], 'quiz_list' => [], 'feed_quiz_list' => [], 'direct_quizzes' => [], 'feed_list' => []];
        }
        $seqQuiz = $user_quiz_rel['seq_quizzes'];
        $quiz_list = $user_quiz_rel['quiz_list'];
        $feedQuizList = $user_quiz_rel['feed_quiz_list'];
        $directQIds = $user_quiz_rel['direct_quizzes'];
        $directChennelQids = array_intersect($directQIds, array_keys($feedQuizList));
        $order_qids = [];
        $quizzes = [];
        $attemptedTest = [];
        // Content feed filter
        $cf_selected = [];
        if (Session::has('assessment_filter.cf')) {
            $cf_selected = Session::get('assessment_filter.cf');
            if (!empty($cf_selected)) {
                $quiz_list = [];
                foreach (Session::get('assessment_filter.cf') as $value) {
                    if (isset($user_quiz_rel['feed_quiz_list'][$value])) {
                        $quiz_list = array_merge($quiz_list, $user_quiz_rel['feed_quiz_list'][$value]);
                    }
                }
            }
        }
        $quiz_list = array_map('intval', array_unique($quiz_list));

        $attempted = QuizAttempt::where('user_id', '=', (int)Auth::user()->uid)
            ->whereIn('quiz_id', $quiz_list)
            ->get();

        $attempt['list'] = $attempt['detail'] = [];
        foreach ($attempted as $value) {
            $attempt['list'][] = (int)$value->quiz_id;
            $attempt['detail'][$value->quiz_id][] = $value;
        }
        $attempt['list'] = array_unique($attempt['list']);
        $count['attempted'] = count($attempt['list']);
        $count['unattempted'] = count(array_diff($quiz_list, $attempt['list']));

        // $filter = Input::get('filter', (($count['unattempted'] > 0) ? 'unattempted' : 'attempted'));
        $start = Input::get('start', 0);
        $limit = 9;
        $quizAnalyticsGrouped = [];
        $quizMatrics = [];
        $myUAQuizIds = [];
        $myUAQuizIds = array_diff($quiz_list, $attempt['list']);
        $nonSquQuizzes = array_diff($myUAQuizIds, $seqQuiz);
        $count['unattempted'] = Quiz::whereIn('quiz_id', $nonSquQuizzes)
            ->where('status', '=', 'ACTIVE')
            ->count();
        $allQuizzes = Quiz::whereIn('quiz_id', $quiz_list)
            ->where('status', '=', 'ACTIVE')
            ->get();
        $bulkAry = $quiz_service->replaceDates($feedQuizList, $allQuizzes);
        $usersQuiz = collect($bulkAry);

        switch ($filter) {
            case 'unattempted':
                $quizzesGroup = $this->orderQuizzesGroupA($nonSquQuizzes, $usersQuiz);
                $quizzes = $quizzesGroup->slice($start, $limit);
                $order_qids = $quizzes->pluck('quiz_id')->all();
                break;

            case 'attempted':
                $attemptedTest = QuizAttempt::where('user_id', '=', (int)Auth::user()->uid)
                    ->whereIn('quiz_id', array_filter($attempt['list']))
                    ->get(['quiz_id', 'started_on']);
                $alterAteempt = [];
                $quizOrderIds = [];
                foreach ($attemptedTest as $attemptedEach) {
                    $temp = [];
                    $temp = [
                        'quiz_id' => $attemptedEach->quiz_id,
                        'started_on' => strtotime($attemptedEach->started_on)
                    ];
                    $alterAteempt[] = $temp;
                }
                if (!empty($alterAteempt)) {
                    $temp = $quizOderCol = collect($alterAteempt);
                    $quizOderCol = $quizOderCol->sortByDesc('started_on');
                    $quizOrderIdsTemp = [];
                    foreach ($quizOderCol as $eachOrderQuizCol) {
                        if (!in_array($eachOrderQuizCol['quiz_id'], $quizOrderIdsTemp)) {
                            $quizOrderIdsTemp[] = $eachOrderQuizCol['quiz_id'];
                        }
                    }
                    $quizOrderIdsTemp = collect($quizOrderIdsTemp);
                    $quizOrderIds = $quizOrderIdsTemp->splice($start, $limit)->toArray();
                } else {
                    $quizOrderIds = [];
                }
                $order_qids = $quizOrderIds;
                /* $quizzes = Quiz::whereIn('quiz_id', array_filter($quizOrderIds))
                                 ->where('status', '=', 'ACTIVE')
                                 ->orderBy('created_at', 'desc')
                                 ->get();  */
                $quizzes = $usersQuiz;
                break;

            default:
                return parent::getError($this->theme, $this->theme_path);
                break;
        }
        if ($filter == "attempted" && !empty($quizzes)) {
            $quizMatrics = SiteSetting::module('General');
            $quizIdsForAnalytics = array_filter($quizzes->pluck('quiz_id')->all());
            $quizAnalytics = OverAllQuizPerformance::getQuizAnalytics(
                $quizIdsForAnalytics,
                (int)Auth::user()->uid
            );
            if (!empty($quizAnalytics)) {
                $quizAnalyticsGrouped = $quizAnalytics->groupBy('quiz_id');
            } else {
                $quizAnalyticsGrouped = [];
            }
        } else {
            $quizAnalytics = [];
        }

        $day_wise_bucket = [];
        if (Request::ajax()) {
            if (!$quizzes->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'data' => view($this->theme_path . '.assessment.quiz_ajax_load', ['quizzes' => $quizzes, 'attempt_detail' => $attempt['detail'], 'filter' => $filter, 'order_qids' => $order_qids, 'quizAnalytics' => $quizAnalyticsGrouped, 'quizMatrics' => $quizMatrics])->render(),
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'data' => 'No more assessment to show',
                ]);
            }
        } else {
            $this->layout->pagetitle = 'Assessment list';
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
            $this->layout->header = view($this->theme_path . '.common.header');
            $this->layout->footer = view($this->theme_path . '.common.footer');
            $this->layout->content = view($this->theme_path . '.assessment.list')
                ->with('quizzes', $quizzes)
                ->with('filter', $filter)
                ->with('count', $count)
                ->with('feeds', $user_quiz_rel['feed_list'])
                ->with('feeds_selected', $cf_selected)
                ->with('order_qids', $order_qids)
                ->with('quizMatrics', $quizMatrics)
                ->with('quizAnalytics', $quizAnalyticsGrouped)
                ->with('attempt_detail', $attempt['detail']);
        }
    }

    public function postIndex()
    {
        $slug = Input::get('cf-slug', []);
        Session::put('assessment_filter.cf', $slug);

        $this->getIndex();
    }

    public function getDetail($quiz_id, IQuizService $quiz_service)
    {
        if (!is_numeric($quiz_id)) {
            return parent::getError($this->theme, $this->theme_path, 404);
        }
        // Checking whether given quiz is available in db
        $quiz = Quiz::where('quiz_id', '=', (int)$quiz_id)
            ->where('status', '=', 'ACTIVE')
            ->first();
        if (is_null($quiz) || empty($quiz)) {
            return parent::getError($this->theme, $this->theme_path, 404);
        }
        //total marks
        $total_marks = (float)$quiz->total_mark;
        try {
            $user_quiz_rel = $quiz_service->getAllQuizzesAssigned();
        } catch (\Exception $e) {
            Log::info($e);
            $user_quiz_rel = ['seq_quizzes' => [], 'quiz_list' => [], 'feed_quiz_list' => [], 'direct_quizzes' => [], 'feed_list' => []];
        }
        $feedQuizList = $user_quiz_rel['feed_quiz_list'];
        $directQIds = $user_quiz_rel['direct_quizzes'];
        $directChennelQids = array_intersect($directQIds, array_keys($feedQuizList));
        $allQuizzes = Quiz::whereIn('quiz_id', [(int)$quiz_id])
            ->where('status', '=', 'ACTIVE')
            ->get();
        $replaceQDate = $quiz_service->replaceDates($feedQuizList, $allQuizzes);
        $program = collect();
        $pack_name = [];
        $pass_criteria = null;
        if (!empty($user_quiz_rel['feed_quiz_list'])) {
            foreach ($user_quiz_rel['feed_quiz_list'] as $key => $value) {
                if (in_array($quiz_id, $value)) {
                    $slugs[] = $key;
                    $res = Packet::getPacketsNameForQuizz($key, $quiz_id);
                    if (isset($res) && !empty($res)) {
                        $pack_name[$key] = $res;
                    }
                }
            }
            if (!empty($slugs)) {
                $program = Program::whereIn('program_slug', $slugs)
                    ->get(['program_id', 'program_title', 'program_slug']);
            }
        }

        if (!in_array((int)$quiz_id, $user_quiz_rel['quiz_list'])) {
            return parent::getError($this->theme, $this->theme_path, 401, 'You are not assigned to this quiz', url('assessment'));
        }
        $attempts = QuizAttempt::where('quiz_id', '=', (int)$quiz_id)
            ->where('user_id', '=', Auth::user()->uid)
            ->where('status', '=', 'CLOSED')
            ->orderBy('started_on')
            ->get();
        $skip_ques = 0;
        $correct_per = 0;
        $incorrect_per = 0;
        $for_skip = [];
        $correc_ques_id = [];
        $for_incorrect = [];
        $sections = null;
        $attempt_pp = null;
        $last_attempt = null;
        $time_spend = '00:00:00';
        $time_speed = '00:00';
        $yet_closed_attempt = null;
        $totalQuestions = 1;
        $last_attempt_data_count = 0;
        $attemptQesCount = 1;
        $requestUrl = '';
        if (Input::has('requestUrl')) {
            $requestUrl = 'requestUrl=' . Input::get('requestUrl');
        }
        if ($quiz->type == 'QUESTION_GENERATOR') {
            $sections = Section::getQuestionInQuiz((int)$quiz_id);
            $attempt_pp = QuizAttempt::where('quiz_id', '=', (int)$quiz_id)
                ->where('user_id', '=', Auth::user()->uid)
                ->first();
            $totalQuestions = 1;
            if (!is_null($attempt_pp)) {
                $last_attempt_data = QuizAttemptData::where('quiz_id', '=', (int)$quiz->quiz_id)
                    ->where('attempt_id', '=', $attempt_pp->attempt_id)
                    ->where('user_id', '=', Auth::user()->uid)
                    ->get();
                $last_attempt_data_count = isset($attempt_pp->total_attempted_questions) ?
                    $attempt_pp->total_attempted_questions : 0;
                if (isset($quiz->is_sections_enabled) && $quiz->is_sections_enabled) {
                    $ques_ids_ary = collect($attempt_pp->section_details)->pluck('page_layout')->all();
                    $ques_ids = call_user_func_array('array_merge', call_user_func_array('array_merge', $ques_ids_ary));
                    $totalQuestions = count($ques_ids);
                } else {
                    $ques_ids = $attempt_pp->questions;
                    $totalQuestions = count($ques_ids);
                }
                if ($totalQuestions < 1) {
                    $totalQuestions = 1;
                }
                $attempt_ques_id = $last_attempt_data->where('answer_status', '')->pluck('question_id')->all();
                $attempt_ques_id_temp = $last_attempt_data
                    ->where('status', 'NOT_VIEWED')->pluck('question_id')->all();
                $for_skip = array_unique(array_merge($attempt_ques_id_temp, $attempt_ques_id));
                $correc_ques_id = $last_attempt_data->where('answer_status', 'CORRECT')->pluck('question_id')->all();
                $incorrec_ques_id = $last_attempt_data
                    ->where('answer_status', 'INCORRECT')->pluck('question_id')->all();
                $for_incorrect = array_diff($incorrec_ques_id, $for_skip);
                $attemptQesCount = count($attempt_ques_id) +
                    count($correc_ques_id) +
                    count($for_incorrect);
                $skip_ques = round((count($for_skip) / ($totalQuestions >= 1 ? $totalQuestions : 1)) * 100, 2);
                $correct_per = round((count($correc_ques_id) / ((count($correc_ques_id) + count($for_incorrect)) >= 1 ? (count($correc_ques_id) + count($for_incorrect)) : 1)) * 100, 2);
                $incorrect_per = round((count($for_incorrect) / (((count($correc_ques_id) + count($for_incorrect)) >= 1 ? (count($correc_ques_id) + count($for_incorrect)) : 1))) * 100, 2);
                $time_spend_ary = array_filter($last_attempt_data->pluck('time_spend')->all());
                if (isset($time_spend_ary) && !empty($time_spend_ary)) {
                    $ques_count_qg = 1;
                    $ques_count_qg = count($time_spend_ary) > 0 ? count($time_spend_ary) : 1;
                    $time_spend_total = call_user_func_array('array_merge', $time_spend_ary);
                    $secs = array_sum($time_spend_total);
                    $num_qus = $ques_count_qg > 0 ? $ques_count_qg : 1;
                    $secs_s = round($secs / $ques_count_qg);
                    $days = intval($secs / 86400);
                    $remainder = $secs % 86400;
                    $hrs = intval($remainder / 3600);
                    $remainder = $remainder % 3600;
                    $min = intval($remainder / 60);
                    $remainder = $remainder % 60;
                    $sec = $remainder;
                    $hrs = ($hrs >= 10) ? $hrs : ('0' . $hrs);
                    $min = ($min >= 10) ? $min : ('0' . $min);
                    $sec = ($sec >= 10) ? $sec : ('0' . $sec);
                    $min_s = intval($secs_s / 60);
                    $sec_s = $secs_s % 60;
                    $min_s = ($min_s >= 10) ? $min_s : ('0' . $min_s);
                    $sec_s = ($sec_s >= 10) ? $sec_s : ('0' . $sec_s);
                    $time_spend = $hrs . ':' . $min . ':' . $sec;
                    $time_speed = $min_s . ':' . $sec_s;
                }
            } else {
                $totalQuestions = count($quiz->questions);
            }
        } else {
            $yet_closed_attempt = QuizAttempt::where('quiz_id', '=', (int)$quiz_id)
                ->where('user_id', '=', Auth::user()->uid)
                ->where('status', '=', 'OPENED')
                ->orderBy('started_on')
                ->first();
            $last_attempt = $attempts->sortBy('attempt_id')->last();
            if (!is_null($last_attempt)) {
                $last_attempt_data = QuizAttemptData::where('quiz_id', '=', $quiz->quiz_id)
                    ->where('attempt_id', '=', $last_attempt->attempt_id)
                    ->where('user_id', '=', Auth::user()->uid)
                    ->get();
                $last_attempt_data_count = count($last_attempt_data);
                if (isset($last_attempt->pass)) {
                    $pass_criteria = $last_attempt->pass;
                } else {
                    $pass_criteria = null;
                }

                if ($quiz->type === "QUESTION_GENERATOR") {
                    $totalQuestions = $last_attempt->total_no_of_questions;
                } else {
                    $totalQuestions = count($last_attempt->questions);
                }
                $attempt_ques_id = $last_attempt_data->where('answer_status', '')->pluck('question_id')->all();
                $attempt_ques_id_temp = $last_attempt_data->where('status', 'NOT_VIEWED')->pluck('question_id')->all();
                $for_skip = array_unique(array_merge($attempt_ques_id_temp, $attempt_ques_id));
                $correc_ques_id = $last_attempt_data->where('answer_status', 'CORRECT')->pluck('question_id')->all();
                $incorrec_ques_id = $last_attempt_data->where('answer_status', 'INCORRECT')->pluck('question_id')->all();
                if ($totalQuestions < 1) {
                    $totalQuestions = 1;
                }
                $for_incorrect = array_diff($incorrec_ques_id, $for_skip);
                $attemptQesCount = count($for_skip) +
                    count($correc_ques_id) +
                    count($for_incorrect);
                $attemptQesCount = $attemptQesCount - count($attempt_ques_id_temp);
                $skip_ques = round((count($for_skip) / $totalQuestions) * 100, 2);
                $correct_per = round((count($correc_ques_id) / $totalQuestions) * 100, 2);
                $incorrect_per = round((count($for_incorrect) / $totalQuestions) * 100, 2);
            } else {
                $skip_ques = 0;
                $correct_per = 0;
                $incorrect_per = 0;
                $last_attempt_data_count = 0;
            }
        }
        $this->layout->pagetitle = 'Assessment | ' . ucwords($quiz->quiz_name);
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->content = view($this->theme_path . '.assessment.detail')
            ->with('quiz', $quiz)
            ->with('attempts', $attempts)
            ->with('attempt_pp', $attempt_pp)
            ->with('last_attempt', $last_attempt)
            ->with('yet_closed_attempt', $yet_closed_attempt)
            ->with('totalQuestions', $totalQuestions)
            ->with('skip_ques', $skip_ques)
            ->with('correct_per', $correct_per)
            ->with('incorrect_per', $incorrect_per)
            ->with('skip_count', count($for_skip))
            ->with('correct_count', count($correc_ques_id))
            ->with('incorrect_count', count($for_incorrect))
            ->with('acuracy', (($correct_per + $incorrect_per) > 0) ? (($correct_per / ($correct_per + $incorrect_per)) * 100) : 0)
            ->with('program', $program)
            ->with('sections', $sections)
            ->with('time_spend', $time_spend)
            ->with('time_speed', $time_speed)
            ->with('pass_criteria', $pass_criteria)
            ->with('last_attempt_data_count', $last_attempt_data_count)
            ->with('replace_qdate', isset($replaceQDate[0]) ? $replaceQDate[0] : [])
            ->with('pack_name', $pack_name)
            ->with('attempt_qes_count', $attemptQesCount)
            ->with('total_marks', $total_marks)
            ->with('requestUrl', $requestUrl);
    }

    public function postStartAttempt($quiz_id, IQuizService $quiz_service)
    {
        $requestUrl = '';
        if (Input::has('requestUrl')) {
            $requestUrl = 'requestUrl=' . Input::get('requestUrl');
        }

        $quiz = Quiz::where('quiz_id', '=', (int)$quiz_id)
            ->where('status', '=', 'ACTIVE')
            ->first();

        if (is_null($quiz) || empty($quiz)) {
            return parent::getError($this->theme, $this->theme_path, 404);
        }
        //section var
        $sec_detail = [];
        // Return
        $return = Input::get('return', 'assessment/detail/' . $quiz_id);
        try {
            $user_quiz_rel = $quiz_service->getAllQuizzesAssigned();
        } catch (\Exception $e) {
            Log::info($e);
            $user_quiz_rel = ['seq_quizzes' => [], 'quiz_list' => [], 'feed_quiz_list' => [], 'direct_quizzes' => [], 'feed_list' => []];
        }
        // User access permission
       
        if (!in_array((int)$quiz_id, $user_quiz_rel['quiz_list'])) {
            return parent::getError($this->theme, $this->theme_path, 401, 'You are not assigned to this quiz', url('assessment'));
        }
        $feedQuizList = $user_quiz_rel['feed_quiz_list'];
        $directQIds = $user_quiz_rel['direct_quizzes'];
        $directChennelQids = array_intersect($directQIds, array_keys($feedQuizList));
        $allQuizzes = Quiz::whereIn('quiz_id', [(int)$quiz_id])
            ->where('status', '=', 'ACTIVE')
            ->get();
        $replaceQDate = $quiz_service->replaceDates($feedQuizList, $allQuizzes);
        $replaceQDate = isset($replaceQDate[0]) ? $replaceQDate[0] : [];
        $isAjax = Request::ajax();
        // Check for the start date
        if (!empty($replaceQDate['start_time']) && Timezone::getTimeStamp($replaceQDate['start_time']) > time()) {
            if ($isAjax) {
                return [ 'status' => false];
            } else {
                return parent::getError($this->theme, $this->theme_path, 401, 'Quiz is not opened', url($return));
            }
        } elseif (empty($replaceQDate) && !empty($quiz->start_time) && $quiz->start_time->timestamp > time()) {
            if ($isAjax) {
                return [ 'status' => false];
            } else {
                return parent::getError($this->theme, $this->theme_path, 401, 'Quiz is not opened', url($return));
            }
        }

        // Check for the end date
        if (!empty($replaceQDate['end_time']) && Timezone::getTimeStamp($replaceQDate['end_time']) < time()) {
            if ($isAjax) {
                return [ 'status' => false];
            } else {
                return parent::getError($this->theme, $this->theme_path, 401, 'Quiz is not opened', url($return));
            }
        } elseif (empty($replaceQDate['end_time']) && !empty($quiz['end_time']) && $quiz['end_time']->timestamp < time()) {
            if ($isAjax) {
                return [ 'status' => false];
            } else {
                return parent::getError($this->theme, $this->theme_path, 401, 'Quiz is closed', url($return));
            }
        }

        $attempt = QuizAttempt::where('quiz_id', '=', (int)$quiz_id)
            ->where('user_id', '=', Auth::user()->uid)
            ->where('status', '=', 'OPENED')
            ->get();
        $new_attempt = false;
        if ($attempt->isEmpty()) {
            $attempts = QuizAttempt::where('quiz_id', '=', (int)$quiz_id)
                ->where('user_id', '=', Auth::user()->uid)
                ->get();
            //Check for the allowed attempts
            if ($quiz->attempts == 0 || $quiz->attempts > $attempts->count()) {
                $attempt_id = (int)QuizAttempt::getNextSequence();
                //is section enabled code
                if ($quiz->is_sections_enabled) {
                    $sec_res = Section::getQuestionInQuiz($quiz_id);
                    if ($quiz->is_timed_sections) {
                        $param['active_section_id'] = $sec_res->first()->section_id;
                    }
                    $temp_ques = [];
                    foreach ($sec_res as $key => $section) {
                        $temp_ques = array_merge($temp_ques, $section->questions);
                        $temp = [];
                        $temp['title'] = $section->title;
                        $temp['total_marks'] = (float)$section->total_marks;
                        $temp['obtain_marks'] = 0;
                        if (isset($quiz->is_timed_sections) && $quiz->is_timed_sections) {
                            $temp['duration'] = $section->duration;
                        }
                        if (isset($section->cut_off_mark) && $section->cut_off_mark >= 0) {
                            $temp['cut_off'] = $section->cut_off_mark;
                            $temp['percentage'] = $section->cut_off;
                        }
                        if (isset($quiz->shuffle_questions) && $quiz->shuffle_questions) {
                            $sect_ques_collection = new Collection($section->questions);
                            //forced page layout as 1 question  per page
                            $temp['page_layout'] = $sect_ques_collection->shuffle()->chunk(1)->toArray();
                        } else {
                            $sect_ques_collection = new Collection($section->questions);
                            //forced page layout as 1 question  per page
                            $temp['page_layout'] = $sect_ques_collection->chunk(1)->toArray();
                        }
                        if ($key == 0) {
                            $temp['started_on'] = time();
                        }
                        $sec_detail[$section->section_id] = $temp;
                    }
                    $questions = array_unique($temp_ques);
                } else {
                    $questions = call_user_func_array('array_merge', $quiz->page_layout);
                    if (isset($quiz->shuffle_questions) && $quiz->shuffle_questions) {
                        $questionCollection = new Collection($questions);
                        //forced page layout as 1 question  per page
                        $questionChunks = $questionCollection->shuffle()->chunk(1)->toArray();
                    } else {
                        $questionsCollection = collect(
                            call_user_func_array('array_merge', $quiz->page_layout)
                        );
                        $questionChunks = $questionsCollection->chunk(1)->toArray();
                    }
                }
                $param['attempt_id'] = $attempt_id;
                $param['user_id'] = Auth::user()->uid;
                $param['quiz_id'] = $quiz->quiz_id;
                $param['questions'] = $questions;
                if ($quiz->is_sections_enabled) {
                    $param['section_details'] = $sec_detail;
                } else {
                    $param['page_layout'] = $questionChunks;
                }
                $param['total_mark'] = (float)$quiz->total_mark;
                $param['obtained_mark'] = 0;
                $param['session_type'] = 'WEB';
                $param['session_key'] = Session::getId();
                $param['status'] = 'OPENED';
                $param['started_on'] = time();
                $param['completed_on'] = '';
                $param['details']['not_viewed'] = $quiz->questions;
                $param['details']['viewed'] = $param['details']['answered'] = $param['details']['reviewed'] = [];
                if (QuizAttempt::insert($param)) {
                    // Session setup for this attempt
                    $new_attempt = true;
                    $data['quiz_id'] = $quiz_id;
                    $data['attempt'] = $attempt_id;
                    $data['question_answered'] = [];
                    $data['return'] = $return;
                    Session::put('assessment.' . $attempt_id, $data);
                    $markReviewList = QuizAttemptData::where('quiz_id', '=', (int)$quiz->quiz_id)
                        ->where('attempt_id', '=', $attempt_id)
                        ->where('user_id', '=', Auth::user()->uid)
                        ->where('mark_review', '=', '1')
                        ->get(['question_id'])
                        ->pluck('question_id')
                        ->all();
                    Session::put('assessment.' . $attempt_id . '.question_review', $markReviewList);
                    // Myactivity
                    if (Input::get('from') != 'myactivity') {
                        MyActivity::getInsertActivity([
                            'module' => 'assessment',
                            'action' => 'start_attempt',
                            'module_name' => $quiz->quiz_name,
                            'module_id' => (int)$quiz->quiz_id,
                            'url' => $return,
                        ]);

                        if (!(boolean)$quiz->practice_quiz) {
                            $userQuizAttempt = ["quiz_id" => $quiz->quiz_id, "attempt_id" => $attempt_id, "started_at" => $param["started_on"]];
                            if (!empty($replaceQDate['start_time'])) {
                                $userQuizAttempt["start_time"] = Timezone::getTimeStamp($replaceQDate['start_time']);
                            } elseif (empty($replaceQDate) && !empty($quiz->start_time)) {
                                $userQuizAttempt["start_time"] = $quiz->start_time->timestamp;
                            }

                            // Check for the end date
                            if (!empty($replaceQDate['end_time']) && Timezone::getTimeStamp($replaceQDate['end_time']) > time()) {
                                $userQuizAttempt["end_time"] = $quiz->end_time->timestamp;
                            } elseif (empty($replaceQDate['end_time']) && !empty($quiz['end_time'])) {
                                $userQuizAttempt["end_time"] = $quiz->end_time->timestamp;
                            }

                            if (isset($replaceQDate['end_time'])
                                && !empty($replaceQDate['end_time'])
                                && $replaceQDate['end_time']
                            ) {
                                if (!empty($quiz->duration) && (empty($replaceQDate['end_time']) || $replaceQDate['end_time'] == 0)) {
                                    $userQuizAttempt["duration"] = $quiz->duration;
                                } elseif (!empty($quiz->duration) && !empty($replaceQDate['end_time'])) {
                                    if ($replaceQDate['end_time'] + $quiz->duration > $replaceQDate['end_time']) {
                                        $userQuizAttempt["duration"] = Timezone::getTimeStamp($replaceQDate['end_time']) - time();
                                    }
                                }
                            } else {
                                if (isset($quiz->duration) && !empty($quiz->duration) && empty($quiz->end_time)) {
                                    $userQuizAttempt["duration"] = $quiz->duration;
                                } elseif (!empty($quiz->duration) && !empty($quiz->end_time)) {
                                    if (Carbon::now()->addMinutes($quiz->duration)->gt($quiz->end_time)) {
                                        $userQuizAttempt["duration"] = $quiz->end_time->timestamp - Carbon::now()->timestamp;
                                    }
                                }
                            }
                            Session::put("userQuizAttempt", $userQuizAttempt);
                        }
                    }
                } else {
                    return parent::getError($this->theme, $this->theme_path, 401, 'Issue while starting the attempt', url($return));
                }
            } else {
                return parent::getError($this->theme, $this->theme_path, 401, 'No attempt is left to take this quiz', url($return));
            }
        } else {
            $attempt_id = $attempt->first()->attempt_id;
            if (Session::has('assessment.' . $attempt_id) === false) {
                $data['quiz_id'] = $quiz_id;
                $data['attempt'] = $attempt_id;
                $data['question_answered'] = QuizAttemptData::where('quiz_id', '=', (int)$quiz->quiz_id)
                    ->where('attempt_id', '=', $attempt_id)
                    ->where('user_id', '=', Auth::user()->uid)
                    ->where('status', '=', 'ANSWERED')
                    ->get(['question_id'])
                    ->pluck('question_id')
                    ->all();
                $data['return'] = $return;
                Session::put('assessment.' . $attempt_id, $data);
                $markReviewList = QuizAttemptData::where('quiz_id', '=', (int)$quiz->quiz_id)
                    ->where('attempt_id', '=', $attempt_id)
                    ->where('user_id', '=', Auth::user()->uid)
                    ->where('mark_review', '=', '1')
                    ->get(['question_id'])
                    ->pluck('question_id')
                    ->all();
                Session::put('assessment.' . $attempt_id . '.question_review', $markReviewList);
                // Myactivity
                if (Input::get('from') != 'myactivity') {
                    MyActivity::getInsertActivity([
                        'module' => 'assessment',
                        'action' => 'continue attempt',
                        'module_name' => $quiz->quiz_name,
                        'module_id' => (int)$quiz->quiz_id,
                        'url' => $return,
                    ]);
                }
            }
        }
        $this->attemptService->createAttempt($quiz->quiz_id);
        if (Request::ajax()) {
            return [ 'status' => true, 'attempt_id' => $attempt_id, 'attempt' => $new_attempt, 'quiz_id' => $quiz->quiz_id ];
        }
        return redirect('assessment/attempt/' . $attempt_id . '?' . $requestUrl);
    }

    public function getAttempt($attempt_id, IQuizService $quiz_service)
    {
        $requestUrl = '';
        if (Input::has('requestUrl')) {
            $requestUrl = 'requestUrl=' . Input::get('requestUrl');
        }
        $attempt = QuizAttempt::where('attempt_id', '=', (int)$attempt_id)->first();
        if (is_null($attempt) || empty($attempt)) {
            return parent::getError($this->theme, $this->theme_path, 404);
        }
        $quiz = Quiz::where('quiz_id', '=', (int)$attempt->quiz_id)->firstOrFail();

        $no_of_quizzes = $this->attemptService->getClosedQuizzes((int)$attempt->quiz_id, Auth::user()->uid)->toArray();
        $no_of_attempts = count($no_of_quizzes) + 1;
        
        if (isset($quiz->is_timed_sections) && $quiz->is_timed_sections && $attempt->status == 'CLOSED') {
            return redirect('assessment/detail/' . $quiz->quiz_id . '?' . $requestUrl);
        }
        $return = Input::get('return', 'assessment/detail/' . $quiz->quiz_id);
        $total_tk = $section_starts_on = 0;
        $last_section = $first_question = $last_question = $section_duration = false;
        $section_ids = [];
        if ($attempt->status == 'OPENED') {
                // Check if session has return url
            $return = Session::get('assessment.' . $attempt_id . '.return', $return);
            try {
                $user_quiz_rel = $quiz_service->getAllQuizzesAssigned();
            } catch (\Exception $e) {
                Log::info($e);
                $user_quiz_rel = ['seq_quizzes' => [], 'quiz_list' => [], 'feed_quiz_list' => [], 'direct_quizzes' => [], 'feed_list' => []];
            }
            $feedQuizList = $user_quiz_rel['feed_quiz_list'];
            $directQIds = $user_quiz_rel['direct_quizzes'];
            $directChennelQids = array_intersect($directQIds, array_keys($feedQuizList));
            $allQuizzes = Quiz::whereIn('quiz_id', [(int)$quiz->quiz_id])
                ->where('status', '=', 'ACTIVE')
                ->get();
            $replaceQDate = $quiz_service->replaceDates($feedQuizList, $allQuizzes);
            $replaceQDate = isset($replaceQDate[0]) ? $replaceQDate[0] : [];
            if (!empty($replaceQDate['end_time']) && Timezone::getTimeStamp($replaceQDate['end_time']) < time()) {
                return redirect('assessment/summary/' . $attempt_id . '?' . $requestUrl);
            } elseif (empty($replaceQDate['end_time'])
                && !empty($quiz->end_time)
                && $quiz->end_time->timestamp < time()
            ) {
                return redirect('assessment/summary/' . $attempt_id . '?' . $requestUrl);
            }


            $page = Input::get('page', 0);
            if ($page === "submit_quiz") {
                if (isset($quiz->is_timed_sections) && $quiz->is_timed_sections) {
                    $this->postCloseAttempt($attempt->attempt_id);
                    if (Request::ajax()) {
                        return ['status'=> true];
                    }
                }
                return redirect('assessment/summary/' . $attempt_id . '?' . $requestUrl);
            }
            //if section enabled
            $sections = Section::getQuestionInQuiz($quiz->quiz_id);
            $section_ids = $sections->pluck('section_id')->all();
            $section_names = $sections->pluck('title')->all();
            $sec_id = (int)Input::get('section', 0);
            if ($quiz->is_sections_enabled) {
                if ($quiz->is_timed_sections) {
                    //for restricting URL navigation
                    if ($sec_id != $attempt->active_section_id || $page >= count($attempt->section_details[$sec_id]['page_layout'])) {
                        return redirect('assessment/attempt/' . $attempt_id . '?page=0&section=' . $attempt->active_section_id . '&' . $requestUrl);
                    }
                    if ($sec_id == last($section_ids)) {
                        $last_section = true;
                    }
                }
                if (!in_array($sec_id, $section_ids)) {
                    if (isset($section_ids[0])) {
                        $sec_id = $section_ids[0];
                    } else {
                        return parent::getError($this->theme, $this->theme_path, 401, 'This attempt has been closed', url($return));
                    }
                }

                if (count($attempt->section_details[$sec_id]['page_layout']) <= $page) {
                    $index_ary = array_search($sec_id, $section_ids);
                    $index_ary++;
                    $sec_id = array_key_exists($index_ary, $section_ids) ? $section_ids[$index_ary] : 0;
                    $page = 0;
                    Input::replace(['page' => $page]);
                    if ($sec_id == 0) {
                        return redirect('assessment/summary/' . $attempt_id . '?' . $requestUrl);
                    }
                    if ($quiz->is_timed_sections && $sec_id !== $attempt->active_section_id) {
                        return redirect('assessment/attempt/' . $attempt_id . '?page=0&section=' . $sec_id . '&' . $requestUrl);
                    }
                }
                if (empty($attempt->section_details[$sec_id]['page_layout'])) {
                    return redirect('assessment/attempt/' . $attempt_id . '?page=' . ($page + 1) . '&' . $requestUrl);
                }
                if ($quiz->is_timed_sections && ($page == 0 || count($attempt->section_details[$sec_id]['page_layout']) == 0)) {
                    $first_question = true;
                }
                if ($quiz->is_timed_sections && count($attempt->section_details[$sec_id]['page_layout']) == $page + 1) {
                    $last_question = true;
                }
            } else {
                if (count($attempt->page_layout) <= $page) {
                    return redirect('assessment/summary/' . $attempt_id . '?' . $requestUrl);
                }

                if (empty($attempt->page_layout[$page])) {
                    return redirect('assessment/attempt/' . $attempt_id . '?page=' . ($page + 1) . '&' . $requestUrl);
                }
            }
            if ($quiz->is_sections_enabled) {
                $attemptdata = QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
                    ->whereIn(
                        'question_id',
                        $attempt->section_details[$sec_id]['page_layout'][$page]
                    )
                    ->get();
            } else {
                $attemptdata = QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
                    ->whereIn('question_id', $attempt->page_layout[$page])
                    ->get();
            }
            if ($attemptdata->isEmpty()) {
                if ($quiz->is_sections_enabled) {
                    $question = Question::whereIn(
                        'question_id',
                        $attempt->section_details[$sec_id]['page_layout'][$page]
                    )
                        ->get();
                } else {
                    $question = Question::whereIn('question_id', $attempt->page_layout[$page])
                        ->get();
                }
                foreach ($question as $value) {
                    $data = [
                        'attempt_id' => (int)$attempt_id,
                        'quiz_id' => (int)$quiz->quiz_id,
                        'user_id' => (int)Auth::user()->uid,
                        'question_id' => (int)$value->question_id,
                        'question_type' => $value->question_type,
                        'question_text' => $value->question_text,
                        'question_mark' => $value->default_mark,
                    ];
                    if ($quiz->is_sections_enabled) {
                        $data['section_id'] = $sec_id;
                    }

                    switch ($value->question_type) {
                        case 'MCQ':
                            $data['answers'] = $value->answers;
                            foreach ($value->answers as $key => $val) {
                                $data['answer_order'][] = $key;
                                if ($val['correct_answer'] == true) {
                                    $data['correct_answer'] = $val['answer'];
                                    $data['rationale'] = $val['rationale'];
                                }
                            }
                            $data['shuffle_answers'] = (!empty($value->shuffle_answers)) ? $value->shuffle_answers : false;
                            if ($data['shuffle_answers'] == true) {
                                shuffle($data['answer_order']);
                            }
                            break;
                    }

                    $data['user_response'] = '';
                    $data['obtained_mark'] = 0;
                    $data['answer_status'] = '';
                    $data['status'] = 'STARTED';
                    $data['mark_review'] = false;
                    $data['history'][] = [
                        'status' => 'STARTED',
                        'time' => time(),
                    ];
                    QuizAttemptData::insert($data);
                }

                // Fetch the attemptdata again
                if ($quiz->is_sections_enabled) {
                    $attemptdata = QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
                        ->whereIn(
                            'question_id',
                            $attempt->section_details[$sec_id]['page_layout'][$page]
                        )
                        ->get();
                } else {
                    $attemptdata = QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
                        ->whereIn('question_id', $attempt->page_layout[$page])
                        ->get();
                }
            } else {
                $total_tk = isset($attemptdata[0]->time_spend) ? array_sum($attemptdata[0]->time_spend) : 0;
            }
            //for time taken ques
            if (Input::get('qes_ids', '') != '') {
                $tk_ques_id_str = array_map('intval', explode(',', Input::get('qes_ids', '')));
                $time_taken = (int)Input::get('time_taken', 0);

                $adata_tk = QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
                    ->whereIn('question_id', $tk_ques_id_str)
                    ->get()
                    ->toArray();
                $attemptdata_tk = [];

                foreach ($adata_tk as $value) {
                    $data_tk = [];
                    $data_tk['time_spend'] = isset($value['time_spend']) ? $value['time_spend'] : [];
                    $data_tk['time_spend'][] = $time_taken;

                    QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
                        ->where('question_id', '=', (int)$value['question_id'])
                        ->update($data_tk);
                }
            }
            $this->layout->pagetitle = 'Assessment | ' . ucwords($quiz->quiz_name);
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->header = view($this->theme_path . '.common.header');
            $this->layout->footer = view($this->theme_path . '.common.footer');
            $newTemplate = config('app.assessment_template');
            if ($newTemplate != "DEFAULT") {
                $newTemplate = true;
            } else {
                $newTemplate = false;
            }
            if (!empty($replaceQDate['end_time'])) {
                $remaining_seconds = Quiz::getDuration(
                    Timezone::getTimeStamp($replaceQDate['end_time']),
                    $quiz->duration
                );
            } else {
                $remaining_seconds = Quiz::getDuration($quiz->end_time, $quiz->duration);
            }
            if ($quiz->is_timed_sections) {
                $duration = $attempt->section_details[$sec_id]['duration'] * 60;
                if (isset($attempt->section_details[$sec_id]['started_on'])) {
                    $section_starts_on = $attempt->section_details[$sec_id]['started_on'];
                } else {
                    $section_starts_on = time();
                    QuizAttempt::where('attempt_id', (int)$attempt->attempt_id)->update(["section_details.$sec_id.started_on" => $section_starts_on]);
                }
                $section_duration = $section_starts_on + $duration;
            }
            $question_id = $attemptdata->first()->question_id;
            $details = $attempt->details;
            $details['not_viewed'] = array_values(array_diff(array_flatten($attempt->details['not_viewed']), array_flatten($attempt->details['viewed']), [$question_id]));
            $details['viewed'] = array_unique(array_merge(array_flatten($attempt->details['viewed']), [$question_id]));
            QuizAttempt::where('attempt_id', (int)$attempt->attempt_id)->update(['details' => $details]);
            $attempt = QuizAttempt::where('attempt_id', '=', (int)$attempt_id)->first();
            $this->layout = view('portal.theme.' . $this->theme . '.layout.full_page_layout');
            $this->layout->pagetitle = 'Attempt | ' . ucwords($quiz->quiz_name);
            if ($newTemplate) {
                $this->layout->content = view($this->theme_path . '.assessment.attempts.attempt')
                    ->with('quiz', $quiz)
                    ->with('attempt', $attempt)
                    ->with('attemptdata', $attemptdata)
                    ->with('total_tk', $total_tk)
                    ->with('section_ids', $section_ids)
                    ->with('section_names', $section_names)
                    ->with('sec_id', $sec_id)
                    ->with('page', $page)
                    ->with('section_details', $sections)
                    ->with('remaining_seconds', $remaining_seconds)
                    ->with('section_duration', $section_duration)
                    ->with('last_section', $last_section)
                    ->with('last_question', $last_question)
                    ->with('first_question', $first_question)
                    ->with('requestUrl', $requestUrl);
            } else {
                $this->layout->content = view($this->theme_path . '.assessment.attempt')
                    ->with('quiz', $quiz)
                    ->with('attempt', $attempt)
                    ->with('attemptdata', $attemptdata)
                    ->with('total_tk', $total_tk)
                    ->with('section_ids', $section_ids)
                    ->with('section_names', $section_names)
                    ->with('sec_id', $sec_id)
                    ->with('page', $page)
                    ->with('section_details', $sections)
                    ->with('remaining_seconds', $remaining_seconds)
                    ->with('section_duration', $section_duration)
                    ->with('last_section', $last_section)
                    ->with('last_question', $last_question)
                    ->with('first_question', $first_question)
                    ->with('requestUrl', $requestUrl)
                    ->with('no_of_attempts', $no_of_attempts);
            }
        } else {
            return redirect($return);
        }
    }

    public function postAttempt($attempt_id, IQuizService $quiz_service)
    {
        $requestUrl = '';
        if (Input::has('requestUrl')) {
            $requestUrl = 'requestUrl=' . Input::get('requestUrl');
        }
        $attempt = QuizAttempt::where('attempt_id', '=', (int)$attempt_id)->first();
        $quiz = Quiz::where('quiz_id', '=', (int)$attempt->quiz_id)->firstOrFail();
        $return = Input::get('return', 'assessment/detail/' . $quiz->quiz_id);
        if ($attempt->status == 'OPENED') {
            if (Session::has('assessment.' . $attempt_id) === true) {
                // Check if session has return url

                $return = Session::get('assessment.' . $attempt_id . '.return', $return);
                
                try {
                    $user_quiz_rel = $quiz_service->getAllQuizzesAssigned();
                } catch (\Exception $e) {
                    Log::info($e);
                    $user_quiz_rel = ['seq_quizzes' => [], 'quiz_list' => [], 'feed_quiz_list' => [], 'direct_quizzes' => [], 'feed_list' => []];
                }
                $feedQuizList = $user_quiz_rel['feed_quiz_list'];
                $directQIds = $user_quiz_rel['direct_quizzes'];
                $directChennelQids = array_intersect($directQIds, array_keys($feedQuizList));
                $allQuizzes = Quiz::whereIn('quiz_id', [(int)$quiz->quiz_id])
                    ->where('status', '=', 'ACTIVE')
                    ->get();
                $replaceQDate = $quiz_service->replaceDates($feedQuizList, $allQuizzes);
                $replaceQDate = isset($replaceQDate[0]) ? $replaceQDate[0] : [];
                if (!empty($replaceQDate['end_time']) && Timezone::getTimeStamp($replaceQDate['end_time']) < time()) {
                    return redirect('assessment/summary/' . $attempt_id . '?' . $requestUrl);
                } elseif (empty($replaceQDate['end_time']) && !empty($quiz->end_time) && $quiz->end_time->timestamp < time()) {
                    return redirect('assessment/summary/' . $attempt_id . '?' . $requestUrl);
                }

                $question = array_map('intval', Input::get('q', []));
                if (Input::get('next_page', 0) === "submit_quiz") {
                    $next_page = "submit_quiz";
                } else {
                    if (Input::has('next')) {
                        $next_page = Input::get('next');
                    } elseif (Input::has('previous')) {
                        $next_page = (int)((Input::get('previous') - 1) < 0) ? 0 : (Input::get('previous') - 1);
                    } else {
                        $next_page = (int)Input::get('next_page', 0);
                    }
                }
                $time_taken_ques = (int)Input::get('ques_time_taken', 0);
                $sec_id = (int)Input::get('section', 0); //if section is enbeled
                if (!empty($question)) {
                    $adata = QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
                        ->whereIn('question_id', $question)
                        ->get()
                        ->toArray();
                    $attemptdata = [];
                    foreach ($adata as $value) {
                        $attemptdata[$value['question_id']] = $value;
                    }

                    foreach ($question as $value) {
                        $update = true;
                        $qattemptdata = $attemptdata[$value];
                        $data = [];
                        $data['time_spend'] = isset($qattemptdata['time_spend']) ? $qattemptdata['time_spend'] : [];
                        $data['time_spend'][] = $time_taken_ques;
                        if ($qattemptdata['question_type'] == 'MCQ') {
                            $selected_option = Input::get('q:' . $value, null);
                            if ($selected_option !== null) {
                                $answer_order = $qattemptdata['answer_order'][$selected_option];
                                $data['user_response'] = $qattemptdata['answers'][$answer_order]['answer'];
                                $data['history'] = array_get($qattemptdata, 'history');

                                if ($qattemptdata['status'] == 'STARTED') {
                                    $data['status'] = 'ANSWERED';
                                    $data['history'][] = [
                                        'status' => 'ANSWERED',
                                        'time' => time(),
                                        'answer' => $data['user_response'],
                                    ];
                                    // Update the session
                                    Session::push('assessment.' . $attempt_id . '.question_answered', $value);
                                    QuizAttempt::where('attempt_id', (int)$attempt->attempt_id)->push('details.answered', $value, true);
                                }
                                if ($qattemptdata['status'] == 'ANSWERED') {
                                    if ($qattemptdata['user_response'] != $data['user_response']) {
                                        $data['history'][] = [
                                            'status' => 'ANSWERED',
                                            'time' => time(),
                                            'answer' => $data['user_response'],
                                        ];
                                    }
                                }
                            }
                        }
                        if (Input::get('reviewed', '') == '') {
                            $data['mark_review'] = false;
                            QuizAttempt::where('attempt_id', (int)$attempt_id)->pull('details.reviewed', (int)$value, true);
                        }
                        if ($update) {
                            QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
                                ->where('question_id', '=', (int)$value)
                                ->update($data);
                        }
                    }
                    return redirect('assessment/attempt/' . (int)$attempt_id . '?page=' . $next_page . '&section=' . $sec_id . '&' . $requestUrl);
                }
            } else {
                return parent::getError($this->theme, $this->theme_path, 401, 'Session expired or invalid attempt', url($return));
            }
        } else {
            if ($quiz->is_timed_sections) {
                return redirect('assessment/detail/' . $quiz->quiz_id . '?' . $requestUrl);
            }
            return parent::getError($this->theme, $this->theme_path, 401, 'This attempt has been closed', url($return));
        }
    }

    public function getSummary($attempt_id, IQuizService $quiz_service)
    {
        $requestUrl = '';
        if (Input::has('requestUrl')) {
            $requestUrl = 'requestUrl=' . Input::get('requestUrl');
        }

        $attempt = QuizAttempt::where('attempt_id', '=', (int)$attempt_id)->first();
        if (is_null($attempt) || empty($attempt)) {
            return parent::getError($this->theme, $this->theme_path, 404);
        }
        $quiz = Quiz::where('quiz_id', '=', (int)$attempt->quiz_id)->firstOrFail();

        $no_of_quizzes = $this->attemptService->getClosedQuizzes((int)$attempt->quiz_id, Auth::user()->uid)->toArray();
        $no_of_attempts = count($no_of_quizzes) + 1;

        $return = Input::get('return', 'assessment/detail/' . $quiz->quiz_id);
        if (isset($quiz->is_timed_sections) && $quiz->is_timed_sections && $attempt->status == 'CLOSED') {
            return redirect('assessment/detail/' . $quiz->quiz_id . '?' . $requestUrl);
        }
        $sections = Section::getQuestionInQuiz($quiz->quiz_id);
        $section_ids = $sections->pluck('section_id')->all();
        $section_names = $sections->pluck('title')->all();
        if ($attempt->status == 'OPENED') {
                // Check if session has return url
            $return = Session::get('assessment.' . $attempt_id . '.return', $return);

            $data = QuizAttemptData::where('quiz_id', '=', (int)$quiz->quiz_id)
                ->where('attempt_id', '=', (int)$attempt->attempt_id)
                ->get(['question_id', 'status']);

            $attemptdata = [];
            foreach ($data as $value) {
                if ($value->status == 'ANSWERED') {
                    $attemptdata[] = $value->question_id;
                }
            }

            $message = '';
            try {
                $user_quiz_rel = $quiz_service->getAllQuizzesAssigned();
            } catch (\Exception $e) {
                Log::info($e);
                $user_quiz_rel = ['seq_quizzes' => [], 'quiz_list' => [], 'feed_quiz_list' => [], 'direct_quizzes' => [], 'feed_list' => []];
            }
            $feedQuizList = $user_quiz_rel['feed_quiz_list'];
            $directQIds = $user_quiz_rel['direct_quizzes'];
            $directChennelQids = array_intersect($directQIds, array_keys($feedQuizList));
            $allQuizzes = Quiz::whereIn('quiz_id', [(int)$quiz->quiz_id])
                ->where('status', '=', 'ACTIVE')
                ->get();
            $replaceQDate = $quiz_service->replaceDates($feedQuizList, $allQuizzes);
            $replaceQDate = isset($replaceQDate[0]) ? $replaceQDate[0] : [];
            if (!empty($replaceQDate['end_time']) && Timezone::getTimeStamp($replaceQDate['end_time']) < time()) {
                $message = 'Note: Attempt exceeded the quiz end time';
            }

            if ($quiz->duration != 0 && ($quiz->duration * 60)
                < (time() - $attempt->started_on->timestamp)
            ) {
                $message = 'Note: Attempt exceeded the quiz duration';
            }
            // $remaining_seconds = Quiz::getDuration($quiz->end_time, $quiz->duration);
            if (!empty($replaceQDate['end_time'])) {
                $remaining_seconds = Quiz::getDuration(
                    Timezone::getTimeStamp($replaceQDate['end_time']),
                    $quiz->duration
                );
            } else {
                $remaining_seconds = Quiz::getDuration($quiz->end_time, $quiz->duration);
            }
            $this->layout = \View::make('portal.theme.' . $this->theme . '.layout.full_page_layout');
            $this->layout->pagetitle = 'Assessment | ' . ucwords($quiz->quiz_name);
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->content = view($this->theme_path . '.assessment.summary')
                ->with('quiz', $quiz)
                ->with('attempt', $attempt)
                ->with('attemptdata', $attemptdata)
                ->with('section_ids', $section_ids)
                ->with('section_names', $section_names)
                ->with('section_details', $sections)
                ->with('message', $message)
                ->with('remaining_seconds', $remaining_seconds)
                ->with('requestUrl', $requestUrl)
                ->with('no_of_attempts', $no_of_attempts);
        } else {
            return parent::getError($this->theme, $this->theme_path, 401, 'This attempt has been closed', url($return));
        }
    }

    public function postCloseAttempt($attempt_id)
    {
        $requestUrl = '';
        if (Input::has('requestUrl')) {
            $requestUrl = 'requestUrl=' . Input::get('requestUrl');
        }
        $attempt = QuizAttempt::where('attempt_id', '=', (int)$attempt_id)->first();
        $quiz = Quiz::where('quiz_id', '=', (int)$attempt->quiz_id)->firstOrFail();
        $return = Input::get('return', 'assessment/detail/' . $quiz->quiz_id);
        $all_sec_flag = true;
        if ($attempt->status == 'OPENED') {
                // Check if session has return url
            $return = Session::get('assessment.' . $attempt_id . '.return', $return);
            $time = time();
            $attemptdata = QuizAttemptData::where('quiz_id', '=', (int)$quiz->quiz_id)
                ->where('attempt_id', '=', (int)$attempt->attempt_id)
                ->get();
            QuizAttemptData::where('attempt_id', (int)$attempt->attempt_id)->update(['status' => QuizAttemptDataStatus::COMPLETED]);
            //Code added by Muniraju N.
            $totalAttemptsAllowed = $quiz->attempts;
            if ($totalAttemptsAllowed !== 1) {
                $quizAttemptQuery = QuizAttempt::where("user_id", Auth::user()->uid)->where("quiz_id", (int)$attempt->quiz_id)->where("status", "CLOSED");
                if ($quizAttemptQuery->count() > 0) {
                    $prevAttemptScore = $quizAttemptQuery->orderBy("completed_on", "desc")->first();
                    $prevAttemptPercentage = round(($prevAttemptScore->obtained_mark / (((float)$prevAttemptScore->total_mark >= 1) ? (float)$prevAttemptScore->total_mark : 1)) * 100);
                }
            }
            //Muniraju code ends here.
            $total = (float)QuizHelper::roundOfNumber($attemptdata->sum('obtained_mark') - $attemptdata->sum('obtained_negative_mark'));
            $data = [
                'obtained_mark' => $total,
                'status' => 'CLOSED',
                'completed_on' => $time,
                'un_attempted_question_count' => $attemptdata->where('answer_status', '')->count(),
                'correct_answer_count' => $attemptdata->where('answer_status', QuizAttemptDataStatus::CORRECT)->count(),
                'in_correct_answer_count' => $attemptdata->where('answer_status', QuizAttemptDataStatus::INCORRECT)->count(),
            ];
            if ($quiz->is_sections_enabled) {
                $section_details = [];
                foreach ($attempt->section_details as $sec_id => $section) {
                    $section_details[$sec_id] = $section;
                    $sections = $attemptdata->whereIn('question_id', array_flatten($section['page_layout']));
                    $total_section_mark = (float) QuizHelper::roundOfNumber($sections->sum('obtained_mark') - $sections->sum('obtained_negative_mark'));
                    $section_details[$sec_id]['obtain_marks'] = $total_section_mark;
                    if ($quiz->pass_criteria == 'QUIZ_AND_SECTIONS') {
                        if (isset($section['cut_off']) && $section['cut_off'] >= 0) {
                            if ($section['cut_off'] <= $total_section_mark) {
                                $section_details[$sec_id]['pass'] = true;
                            } else {
                                $section_details[$sec_id]['pass'] = false;
                                $all_sec_flag = false;
                            }
                        }
                    }
                }
                $data['section_details'] = $section_details;
            }
            if (isset($quiz->cut_off_format) && $quiz->cut_off_format == QCFT::PERCENTAGE) {
                if (($quiz->pass_criteria == 'QUIZ_AND_SECTIONS') && $all_sec_flag &&
                    (float)$quiz->total_mark > 0 &&
                    $quiz->cut_off <= ($total / (float)$quiz->total_mark) *100) {
                    $data['pass'] = true;
                } else if ($quiz->pass_criteria == "QUIZ_ONLY" &&
                    (float)$quiz->total_mark > 0 &&
                    $quiz->cut_off <= ($total / (float)$quiz->total_mark) *100
                ) {
                    $data['pass'] = true;
                } else {
                    $data['pass'] = false;
                }
            } else {
                if (isset($quiz->cut_off_mark) && $quiz->cut_off_mark > 0) {
                    if (($quiz->pass_criteria == 'QUIZ_AND_SECTIONS') && ($quiz->cut_off_mark <= $total) && $all_sec_flag) {
                        $data['pass'] = true;
                    } elseif ($quiz->pass_criteria == "QUIZ_ONLY" && $quiz->cut_off_mark <= $total) {
                        $data['pass'] = true;
                    } else {
                        $data['pass'] = false;
                    }
                } elseif (isset($quiz->pass_criteria) && $quiz->pass_criteria == 'QUIZ_AND_SECTIONS') {
                    $data['pass'] = $all_sec_flag;
                }
            }
            QuizAttempt::where('attempt_id', '=', (int)$attempt->attempt_id)
                ->update($data);

            //Playlyfe integration code starts.
            //Added by Muniraju N.
            if (config('app.playlyfe.enabled')) {
                $processEventFlag = true;
                $currentAttemptPercentage = round(($data["obtained_mark"] / (((float)$attempt->total_mark >= 1 ? (float)$attempt->total_mark : 1))) * 100);
                if (isset($prevAttemptPercentage)) {
                    if ($currentAttemptPercentage > $prevAttemptPercentage) {
                        $percentageRange = [0, 60, 71, 81, 91, 100];
                        for ($i = 0; $i < count($percentageRange) - 1; ++$i) {
                            $currentMinVal = $percentageRange[$i];
                            $nextMinVal = $percentageRange[$i + 1];
                            if ($prevAttemptPercentage >= $currentMinVal && $prevAttemptPercentage < $nextMinVal) {
                                if ($currentAttemptPercentage < $nextMinVal) {
                                    $processEventFlag = false;
                                }
                            } elseif ($prevAttemptPercentage === 100) {
                                $processEventFlag = false;
                            }
                        }
                    } else {
                        $processEventFlag = false;
                    }
                }

                if ($processEventFlag) {
                    $playlyfe = App::make(\App\Services\Playlyfe\IPlaylyfeService::class);
                    $playlyfeEvent = [
                        "type" => "action",
                        "data" => [
                            "user_id" => $attempt->user_id,
                            "action_id" => "quiz_completed",
                            "id" => strval($attempt->quiz_id),
                            "score" => $currentAttemptPercentage
                        ]
                    ];

                    $playlyfe->processEvent($playlyfeEvent);
                }


                //Playlyfe integration code ends.

                // User::saveQuizAttemptStatus([]);

                // Update the quiz reports
                // QuizReport::calAttemptScore($quiz, Auth::user()->uid, (int)$attempt_id); Since we not using
            }
            Session::forget("userQuizAttempt");

            // Myactivity
            if (Input::get('from') != 'myactivity') {
                MyActivity::getInsertActivity([
                    'module' => 'assessment',
                    'action' => 'close attempt',
                    'module_name' => $quiz->quiz_name,
                    'module_id' => (int)$quiz->quiz_id,
                    'url' => $return,
                ]);
                // update the channel completion
            }
            $isPractice = isset($quiz->practice_quiz) ? $quiz->practice_quiz : false;
            $this->putEntryInToOAQP((int)$attempt_id, $isPractice);
            $this->putEntryInToOca($quiz);
            $this->putEntryIntoMyActivity($quiz);
            // Remove attempt details from the session
            Session::forget('assessment.' . $attempt_id);
            if (Request::ajax()) {
                return Response::json(["status" => true]);
            } else {
                return redirect($return . '?' . $requestUrl);
            }
        } else {
            return parent::getError($this->theme, $this->theme_path, 401, 'This attempt has been closed', url($return));
        }
    }

    public function postCloseSection($attempt_id)
    {
        try {
            $attempt = QuizAttempt::where('attempt_id', (int)$attempt_id)->first(['section_details', 'active_section_id']);
            $section_id = (int)$attempt->active_section_id;
            $section_ids = array_keys($attempt->section_details);
            $next = array_search($section_id, $section_ids) + 1;
            if (array_key_exists($next, $section_ids)) {
                $section_id = $data['active_section_id'] = $section_ids[$next];
            }
            $data["section_details.$section_id.completed_on"] = time();
            QuizAttempt::where('attempt_id', (int)$attempt_id)->update($data);
            return response()->json(['status' => true, 'section' => $section_id]);
        } catch (\Exception $e) {
            Log::error('Error in closing section. Message: '.$e->getMessage().' In line'.$e->getLine());
            return response()->json(['status' => false]);
        }
    }

    public function getStarQuiz($action, $quiz_id)
    {
        $this->layout = '';

        Quiz::where('status', '=', 'ACTIVE')
            ->where('quiz_id', '=', (int)$quiz_id)
            ->firstOrFail();

        switch ($action) {
            case 'star':
                Quiz::where('quiz_id', '=', (int)$quiz_id)
                    ->push('users_liked', Auth::user()->uid, true);
                break;

            case 'unstar':
                Quiz::where('quiz_id', '=', (int)$quiz_id)
                    ->pull('users_liked', Auth::user()->uid);
                break;

            default:
                return response()->json([
                    'status' => false,
                    'quiz_id' => (int)$quiz_id,
                ]);
                break;
        }

        return response()->json([
            'status' => true,
            'quiz_id' => (int)$quiz_id,
        ]);
    }

    public function getReport($attempt_id, IQuizService $quiz_service)
    {
        $requestUrl = '';
        if (Input::has('requestUrl')) {
            $requestUrl = 'requestUrl=' . Input::get('requestUrl');
        }
        $attempt = QuizAttempt::where('attempt_id', '=', (int)$attempt_id)
            ->where('user_id', '=', Auth::user()->uid)
            ->firstOrFail();


        $quiz = Quiz::where('quiz_id', '=', (int)$attempt->quiz_id)->firstOrFail();
        $page = Input::get('page', 1);
        $page = $page == 0 ? 1 : $page;
        $ques_ids = collect(isset($attempt->page_layout) ? call_user_func_array('array_merge', $attempt->page_layout) : [])->chunk((int)$this->question_per_block);
        $ques_ids_ary = $ques_ids->get((int)($page - 1));
        if (!empty($ques_ids_ary)) {
            $ques_ids_ary = $ques_ids_ary->toArray();
        } else {
            $ques_ids_ary = [];
        }

        $return = Input::get('return', 'assessment/detail/' . $attempt->quiz_id);
        if (!isset($quiz->type) && $quiz->type != "QUESTION_GENERATOR") {
            // Checking review option enabled for this quiz
            if (!$quiz->review_options['the_attempt']) {
                return parent::getError($this->theme, $this->theme_path, 401, 'You don\'t have access to this report', url($return));
            }
        }
        
        try {
            $user_quiz_rel = $quiz_service->getAllQuizzesAssigned();
        } catch (\Exception $e) {
            Log::info($e);
            $user_quiz_rel = ['seq_quizzes' => [], 'quiz_list' => [], 'feed_quiz_list' => [], 'direct_quizzes' => [], 'feed_list' => []];
        }
        // User access permission
        if (!in_array($attempt->quiz_id, $user_quiz_rel['quiz_list'])) {
            return parent::getError($this->theme, $this->theme_path, 401, 'You are not assigned to this quiz', url($return));
        }

        if (!isset($quiz->type) && $quiz->type != "QUESTION_GENERATOR") {
            $attempts = QuizAttempt::where('quiz_id', '=', (int)$attempt->quiz_id)
                ->where('user_id', '=', Auth::user()->uid)
                ->orderBy('started_on')
                ->get(['attempt_id']);
        } else {
            $attempts = QuizAttempt::where('quiz_id', '=', (int)$attempt->quiz_id)
                ->where('user_id', '=', Auth::user()->uid)
                ->where('status', '!=', 'VIEWED')
                ->orderBy('started_on')
                ->get(['attempt_id']);
        }

        if (!isset($quiz->type) && $quiz->type != "QUESTION_GENERATOR") {
            $attemptdatapagination = QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
                ->orderBy('section_id', 'asc')
                ->paginate((int)$this->question_per_block);

            $attemptdatapagination_order = QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
                ->orderBy('section_id', 'asc')
                ->whereIn('question_id', $ques_ids_ary)->get();
        } else {
            $attemptdatapagination = QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
                ->orderBy('section_id', 'asc')
                ->where('status', '!=', 'VIEWED')
                ->paginate((int)$this->question_per_block);
        }
        $attemptdata = null;
        if (isset($attemptdatapagination_order) && !empty($attemptdatapagination_order)) {
            $attemptdata = $attemptdatapagination_order->keyBy('question_id');
        } else {
            $attemptdata = $attemptdatapagination->keyBy('question_id');
        }
        if (Input::get('from') != 'myactivity') {
            MyActivity::getInsertActivity([
                'module' => 'assessment',
                'action' => 'attempt review',
                'module_name' => $quiz->quiz_name,
                'module_id' => (int)$quiz->quiz_id,
                'url' => Request::path(),
            ]);
        }
        try {
            $section_details = null;
            $active_section = '';
            if (isset($attempt->section_details)) {
                $s_data = Section::getQuestionInQuiz($quiz->quiz_id);#where('quiz_id','=',$quiz->quiz_id)->get();
                if (!empty($s_data)) {
                    $section_details = $s_data->toArray();
                }
                foreach ($section_details as $key => $value) {
                    $active_section = $value['section_id'];
                    break;
                }
                return redirect("assessment/section-with-question/" . $attempt_id . "/" . $active_section . '?' . $requestUrl);
            }

            $active_question = $attemptdatapagination->first();
            if (Input::has('q_id')) {
                $question_id = Input::get('q_id');
                if (!empty($question_id)) {
                    $q = QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
                        ->where('question_id', '=', (int)$question_id)
                        ->first();
                    if (!is_null($q)) {
                        $active_question = $q;
                    }
                }
            } else {
                $start = (int)($page - 1) * config('app.question_per_block');
                $q = QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
                    ->where('question_id', '=', (int)$ques_ids_ary[$start])
                    ->first();
                if (!is_null($q)) {
                    $active_question = $q;
                }
            }
        } catch (Exception $e) {
            return parent::getError($this->theme, $this->theme_path, 401);
        }
        $this->layout = view('portal.theme.' . $this->theme . '.layout.full_page_layout');
        $this->layout->pagetitle = 'Assessment | ' . ucwords($quiz->quiz_name);
        $this->layout->theme = 'portal/theme/' . $this->theme;
        if (isset($quiz->type) && $quiz->type === "QUESTION_GENERATOR") {
            $this->layout->content = view($this->theme_path . '.assessment.review.random_question_generator')
                ->with('quiz', $quiz)
                ->with('attempt', $attempt)
                ->with('attempts', $attempts)
                ->with('attemptdatapagination', $attemptdatapagination)
                ->with('attemptdata', $attemptdata)
                ->with('section_details', $section_details)
                ->with('active_section', $active_section)
                ->with('active_question', $active_question)
                ->with('requestUrl', $requestUrl);
        } else {
            $this->layout->content = view($this->theme_path . '.assessment.review')
                ->with('quiz', $quiz)
                ->with('attempt', $attempt)
                ->with('attempts', $attempts)
                ->with('attemptdatapagination', $attemptdatapagination)
                ->with('attemptdata', $attemptdata)
                ->with('section_details', $section_details)
                ->with('active_section', $active_section)
                ->with('ques_order_ary', $ques_ids_ary)
                ->with('active_question', $active_question)
                ->with('requestUrl', $requestUrl);
        }
    }


    public function getQuestionDetail($attempt_id, $attempt_num = 0)
    {
        if (!is_numeric($attempt_id)) {
            return parent::getError($this->theme, $this->theme_path, 404);
        }
        $attempt = QuizAttempt::where('attempt_id', '=', (int)$attempt_id)
            ->where('user_id', '=', Auth::user()->uid)
            // ->where('status', '=', 'CLOSED')
            ->first();
        if (is_null($attempt) || empty($attempt)) {
            return parent::getError($this->theme, $this->theme_path, 404);
        }
        $quiz = Quiz::where('quiz_id', '=', (int)$attempt->quiz_id)->firstOrFail();
        $return = Input::get('return', 'assessment/detail/' . $attempt->quiz_id);
        if(isset($quiz->type) && $quiz->type != 'QUESTION_GENERATOR') {
            if (!isset($quiz->is_score_display) || !$quiz->is_score_display) {
                return parent::getError($this->theme, $this->theme_path, 401, 'You don\'t have access to this report', url($return));
            }
        }
        $attempts = null;
        if (!isset($quiz->type) && $quiz->type != 'QUESTION_GENERATOR') {
            $attempt = QuizAttempt::where('attempt_id', '=', (int)$attempt_id)
                ->where('user_id', '=', Auth::user()->uid)
                ->where('status', '=', 'CLOSED')
                ->firstOrFail();
            if (Request::ajax()) {
                return ['attempt' => $quiz->review_options['the_attempt']];
            }
            // Checking review option enabled for this quiz
            if (!$quiz->review_options['the_attempt']) {
                return parent::getError($this->theme, $this->theme_path, 401, 'You don\'t have access to this report', url($return));
            }
            try {
                $quiz_service = App::make(App\Services\Quiz\IQuizService::class);
                $user_quiz_rel = $quiz_service->getAllQuizzesAssigned();
            } catch (\Exception $e) {
                Log::info($e);
                $user_quiz_rel = ['seq_quizzes' => [], 'quiz_list' => [], 'feed_quiz_list' => [], 'direct_quizzes' => [], 'feed_list' => []];
            }
            // User access permission
            if (!in_array($attempt->quiz_id, $user_quiz_rel['quiz_list'])) {
                return parent::getError($this->theme, $this->theme_path, 401, 'You are not assigned to this quiz', url($return));
            }
            $attempts = QuizAttempt::where('quiz_id', '=', (int)$attempt->quiz_id)
                ->where('user_id', '=', Auth::user()->uid)
                ->where('status', '=', 'CLOSED')
                ->orderBy('started_on')
                ->get(['attempt_id']);
        }
        $requestUrl = '';
        if (Input::has('requestUrl')) {
            $requestUrl = 'requestUrl=' . Input::get('requestUrl');
        }
        $concepts = Quiz::getConceptDetails((int)$attempt_id);
        $attemptdata = QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
            ->where('question_type', '!=', 'DESCRIPTIVE')
            ->get()
            ->keyBy('question_id');
        $for_skip = [];
        $correc_ques_id = [];
        $for_incorrect = [];
        $correct_per = 0;
        $incorrect_per = 0;
        $skip_ques = 0;
        $accuracy = 0;
        $section_details = [];
        $ques_count_qg = 1;
        $attemptQesCount = 1;
        if ((!is_null($attempt) && isset($attempt->completed_on))
            || (isset($quiz->type) && $quiz->type == 'QUESTION_GENERATOR')
        ) {
            if (isset($quiz->is_sections_enabled) && $quiz->is_sections_enabled) {
                $ques_ids_ary = collect($attempt->section_details)->pluck('page_layout')->all();
                $ques_ids = call_user_func_array('array_merge', call_user_func_array('array_merge', $ques_ids_ary));
            } else {
                $ques_ids = $attempt->questions;
            }
            $attempt_ques_id = $attemptdata->where('answer_status', '')->pluck('question_id')->all();
            $attempt_ques_id_temp = $attemptdata->where('status', 'NOT_VIEWED')->pluck('question_id')->all();
            $for_skip = array_unique(array_merge($attempt_ques_id_temp, $attempt_ques_id));
            $correc_ques_id = $attemptdata->where('answer_status', 'CORRECT')->pluck('question_id')->all();
            $incorrec_ques_id = $attemptdata->where('answer_status', 'INCORRECT')->pluck('question_id')->all();
            $for_incorrect = array_diff($incorrec_ques_id, $for_skip);
            $questionsCount = count($ques_ids) >= 1 ? count($ques_ids) : 1;
            $skip_ques = round((count($for_skip) / $questionsCount) * 100, 2);
            $correct_per = round((count($correc_ques_id) / $questionsCount) * 100, 2);
            $incorrect_per = round((count($for_incorrect) / $questionsCount) * 100, 2);
            $attemptQesCount = count($for_skip) + count($correc_ques_id) + count($for_incorrect);
            $attemptQesCount = $attemptQesCount - count($attempt_ques_id_temp);
            $accuracy = (($correct_per + $incorrect_per) > 0) ?
                ($correct_per / ($correct_per + $incorrect_per)) : 0;
            $quiz_details = [];
            $quiz_details['accuracy'] = ($accuracy * 100);
            if ((isset($quiz->type) && $quiz->type == 'QUESTION_GENERATOR')) {
                $quiz_details['marks'] = 0;
                $quiz_details['marks_per'] = 0;
                $quiz_details['skiped'] = 0;
            } else {
                $quiz_details['marks'] = $attempt->obtained_mark . '/' . (float)$attempt->total_mark;
                $quiz_details['marks_per'] = ($attempt->obtained_mark / (((float)$attempt->total_mark >= 1) ? (float)$attempt->total_mark : 1)) * 100;
                $quiz_details['skiped'] = count($for_skip);
                if (isset($quiz->cut_off_mark) && $quiz->cut_off_mark > 0) {
                    if ($quiz->cut_off_format == QCFT::PERCENTAGE) {
                        $quiz_details['cut_off'] = "<td>" . $quiz->cut_off . "</td>";
                    } else {
                        $quiz_details['cut_off'] = "<td>" . $quiz->cut_off_mark . "</td>";
                    }
                    $quiz_details['pass'] = isset($attempt->pass) ? ($attempt->pass ? "<td><span class='correct-text'><i class='fa fa-check'></i></span></td>" : "<td><span class='wrong-text'><i class='fa fa-close'></i></span></td>") : "<td><span>NA</span></td>";
                    $quiz_details['pass_head'] = isset($attempt->pass) ? $attempt->pass : null;
                } else {
                    $quiz_details['cut_off'] = "<td><span>NA</span></td>";
                    $quiz_details['pass'] = isset($attempt->pass) ? ($attempt->pass ? "<td><span class='correct-text'><i class='fa fa-check'></i></span></td>" : "<td><span class='wrong-text'><i class='fa fa-close'></i></span></td>") : "<td><span>NA</span></td>";
                    $quiz_details['pass_head'] = isset($attempt->pass) ? $attempt->pass : null;
                }
            }
            $quiz_details['correct'] = count($correc_ques_id);
            $quiz_details['incorrect'] = count($for_incorrect);
            if (isset($quiz->type) && $quiz->type == 'QUESTION_GENERATOR') {
                $time_spend_ary = array_filter($attemptdata->pluck('time_spend')->all());
                $time_spend_total = [];
                if (!empty($time_spend_ary)) {
                    $ques_count_qg = count($time_spend_ary) > 0 ? count($time_spend_ary) : 1;
                    $time_spend_total = call_user_func_array('array_merge', $time_spend_ary);
                }
                $secs = array_sum($time_spend_total);
            } else {
                $secs = $attempt->started_on->diffInSeconds($attempt->completed_on);
            }
            if (isset($quiz->duration) && $quiz->duration > 0 && ($quiz->duration * 60) < $secs) {
                $secs = ($quiz->duration * 60);
            }
            $days = intval($secs / 86400);
            $remainder = $secs % 86400;
            $hrs = intval($remainder / 3600);
            $remainder = $remainder % 3600;
            $min = intval($remainder / 60);
            $remainder = $remainder % 60;
            $sec = $remainder;
            if (isset($quiz->type) && $quiz->type == 'QUESTION_GENERATOR') {
                $num_qus = $ques_count_qg > 0 ? $ques_count_qg : 1;
                $sec_speed = ($secs / $num_qus);
            } else {
                $num_qus = (count($attempt->questions) > 0) ? count($attempt->questions) : 1;
                if ($attemptQesCount > 0) {
                    $sec_speed = ($secs / $attemptQesCount);
                } else {
                    $sec_speed = 0;
                }
            }
            $min_s = intval($sec_speed / 60);
            $remainder_s = $sec_speed % 60;
            $sec_s = $remainder_s;
        } else {
            $days = 0;
            $hrs = 0;
            $min = 0;
            $sec = 0;
            $sec_s = 0;
            $min_s = 0;
        }
        $hrs = ($hrs >= 10) ? $hrs : ('0' . $hrs);
        $min = ($min >= 10) ? $min : ('0' . $min);
        $sec = ($sec >= 10) ? $sec : ('0' . $sec);
        $min_s = ($min_s >= 10) ? $min_s : ('0' . $min_s);
        $sec_s = ($sec_s >= 10) ? $sec_s : ('0' . $sec_s);
        $quiz_details['total_time'] = $hrs . ":" . $min . ":" . $sec;
        $quiz_details['speed'] = $min_s . ':' . $sec_s;
        if ($quiz->is_sections_enabled && ((!is_null($attempt) && isset($attempt->completed_on)) || (isset($quiz->type) && $quiz->type == 'QUESTION_GENERATOR'))) {
            if (isset($quiz->is_sections_enabled) && $quiz->is_sections_enabled) {
                $ques_ids_ary = collect($attempt->section_details)->pluck('page_layout')->all();
                $ques_ids = call_user_func_array('array_merge', call_user_func_array('array_merge', $ques_ids_ary));
            } else {
                $ques_ids = $attempt->questions;
            }
            $attempt_ques_id_group = $attemptdata->where('answer_status', '')->groupBy('section_id');//->pluck('question_id')->all();

            $attempt_ques_id_temp_group = $attemptdata->where('status', 'NOT_VIEWED')->groupBy('section_id');//->pluck('question_id')->all();
            $correct_ques_sec = $attemptdata->where('answer_status', 'CORRECT')->groupBy('section_id');
            $incorrect_ques_sec = $attemptdata->where('answer_status', 'INCORRECT')->groupBy('section_id');
            $time_spend = $attemptdata->groupBy('section_id');
            $section_details = [];
            foreach ($attempt->section_details as $sec_key => $section_detail) {
                if (empty($section_detail['page_layout'])) {
                    continue;
                }
                if (isset($time_spend[$sec_key])) {
                    $section_tk_total = array_filter(collect($time_spend[$sec_key])->pluck('time_spend')->all());
                } else {
                    $section_tk_total = [];
                }


                if (!empty($section_tk_total)) {
                    $secs = array_sum(call_user_func_array('array_merge', $section_tk_total));
                } else {
                    $secs = 0;
                }
                $attempt_ques_id = collect(isset($attempt_ques_id_group[$sec_key]) ? $attempt_ques_id_group[$sec_key] : []);
                $attempt_ques = $attempt_ques_id->pluck('question_id')->all();
                $attempt_ques_id_temp = collect(isset($attempt_ques_id_temp_group[$sec_key]) ? $attempt_ques_id_temp_group[$sec_key] : []);
                $attempt_ques_nv = $attempt_ques_id_temp->pluck('question_id')->all();
                $correct_ques = collect(isset($correct_ques_sec[$sec_key]) ? $correct_ques_sec[$sec_key] : []);
                $correct_ques_id = $correct_ques->pluck('question_id')->all();
                $incorrect_ques = collect(isset($incorrect_ques_sec[$sec_key]) ? $incorrect_ques_sec[$sec_key] : []);
                $incorrect_ques_id = $incorrect_ques->pluck('question_id')->all();
                $ques_ids = call_user_func_array('array_merge', $section_detail['page_layout']);
                $speed_sec = ($secs / count($ques_ids));
                $for_skip = array_unique(array_merge($attempt_ques_nv, $attempt_ques));
                $for_incorrect = array_diff($incorrect_ques_id, $for_skip);
                $questionsCount = count($ques_ids) >= 1 ? count($ques_ids) : 1;
                $skip_ques = round((count($for_skip) / $questionsCount) * 100, 2);
                $correct_per = round((count($correct_ques_id) / $questionsCount) * 100, 2);
                $incorrect_per = round((count($for_incorrect) / $questionsCount) * 100, 2);
                $accuracy = (($correct_per + $incorrect_per) > 0) ? ($correct_per / ($correct_per + $incorrect_per)) : 0;
                $temp = [];

                $temp['correct'] = count($correct_ques_id);
                $temp['incorrect'] = count($for_incorrect);
                $temp['accuracy'] = round(($accuracy * 100), 2);
                $temp['title'] = $section_detail['title'];

                if (isset($quiz->is_sections_enabled) && $quiz->is_sections_enabled && (!isset($quiz->type) || $quiz->type != 'QUESTION_GENERATOR')) {
                    $temp['marks'] = $attempt->section_details[$sec_key]['obtain_marks'] . '/' . $attempt->section_details[$sec_key]['total_marks'];
                    $temp['marks_per'] = round((($attempt->section_details[$sec_key]['obtain_marks'] / $attempt->section_details[$sec_key]['total_marks']) * 100), 2);
                    $temp['skiped'] = count($for_skip);
                    if (isset($attempt->section_details[$sec_key]['cut_off']) && isset($attempt->section_details[$sec_key]['pass'])) {
                        $temp['cut_off'] = $attempt->section_details[$sec_key]['cut_off'];
                        $temp['pass'] = $attempt->section_details[$sec_key]['pass'] ? "<td><span class='correct-text'><i class='fa fa-check'></i></span></td>" : "<td><span class='wrong-text'><i class='fa fa-close'></i></span></td>";
                    } else {
                        $temp['cut_off'] = isset($attempt->section_details[$sec_key]['cut_off']) ? $attempt->section_details[$sec_key]['cut_off'] : "<span>NA</span>";
                        $temp['pass'] = "<td><span>NA</span></td>";
                    }
                } else {
                    $temp['marks'] = 0;
                    $temp['marks_per'] = 0;
                    $temp['skiped'] = 0;
                }

                $days = intval($secs / 86400);
                $remainder = $secs % 86400;
                $hrs = intval($remainder / 3600);
                $remainder = $remainder % 3600;
                $min = intval($remainder / 60);
                $remainder = $remainder % 60;
                $sec = $remainder;
                $min_s = intval($speed_sec / 60);
                $remainder_s = $speed_sec % 60;
                $sec_s = $remainder_s;

                $hrs = ($hrs >= 10) ? $hrs : ('0' . $hrs);
                $min = ($min >= 10) ? $min : ('0' . $min);
                $sec = ($sec >= 10) ? $sec : ('0' . $sec);
                $min_s = ($min_s >= 10) ? $min_s : ('0' . $min_s);
                $sec_s = ($sec_s >= 10) ? $sec_s : ('0' . $sec_s);
                $temp['total_time'] = $hrs . ":" . $min . ":" . $sec;

                $temp['speed'] = $min_s . ':' . $sec_s;
                $temp['id'] = $sec_key;
                $section_details[] = $temp;
            }
        }
        $this->layout = view('portal.theme.' . $this->theme . '.layout.full_page_layout');
        $this->layout->pagetitle = trans('assessment.detailed_analytics'). ' | ' . ucwords($quiz->quiz_name);
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->content = view($this->theme_path . '.assessment.questions_detail')
            ->with('quiz', $quiz)
            ->with('attempt', $attempt)
            ->with('attempts', $attempts)
            ->with('quiz_details', $quiz_details)
            ->with('section_details', $section_details)
            ->with('attempt_num', $attempt_num)
            ->with('attemptdata', $attemptdata)
            ->with('concepts', $concepts)
            ->with('requestUrl', $requestUrl);
    }

    public function getSectionWithQuestion($attempt_id, $section_id)
    {
        $requestUrl = '';
        if (Input::has('requestUrl')) {
            $requestUrl = 'requestUrl=' . Input::get('requestUrl');
        }
        $attempt = QuizAttempt::where('attempt_id', '=', (int)$attempt_id)
            ->where('user_id', '=', Auth::user()->uid)
            ->first();
        if (is_null($attempt) || empty($attempt)) {
            return parent::getError($this->theme, $this->theme_path, 401);
        }
        $quiz = Quiz::where('quiz_id', '=', (int)$attempt->quiz_id)->firstOrFail();
        $return = Input::get('return', 'assessment/detail/' . $attempt->quiz_id);
        $page = Input::get('page', 1);
        $page = $page == 0 ? 1 : $page;
        $ques_ids_ary = [];
        if (isset($attempt->section_details[$section_id])) {
            $ques_ids = collect(isset($attempt->section_details[$section_id]['page_layout']) ? call_user_func_array('array_merge', $attempt->section_details[$section_id]['page_layout']) : [])->chunk((int)$this->question_per_block);
            $ques_ids_ary = $ques_ids->get(((int)($page - 1)));
            if (!empty($ques_ids_ary)) {
                $ques_ids_ary = $ques_ids_ary->toArray();
            } else {
                $ques_ids_ary = [];
            }
        } else {
            return parent::getError($this->theme, $this->theme_path, 404);
        }

        if (!isset($quiz->type) && $quiz->type != "QUESTION_GENERATOR") {
            // Checking review option enabled for this quiz
            if (!$quiz->review_options['the_attempt']) {
                return parent::getError($this->theme, $this->theme_path, 401, 'You don\'t have access to this report', url($return));
            }
        }

        if (!isset($quiz->type) && $quiz->type != "QUESTION_GENERATOR") {
            $attempts = QuizAttempt::where('quiz_id', '=', (int)$attempt->quiz_id)
                ->where('user_id', '=', Auth::user()->uid)
                ->orderBy('started_on')
                ->get(['attempt_id']);
        } else {
            $attempts = QuizAttempt::where('quiz_id', '=', (int)$attempt->quiz_id)
                ->where('user_id', '=', Auth::user()->uid)
                ->where('status', '!=', 'VIEWED')
                ->orderBy('started_on')
                ->get(['attempt_id']);
        }

        if (!isset($quiz->type) && $quiz->type != "QUESTION_GENERATOR") {
            /*$attemptdatapagination = QuizAttemptData::where('attempt_id', '=', (int) $attempt_id)
                    ->where('section_id','=',(int)$section_id)
                    ->orderBy('section_id','asc')
                    ->paginate((int)$this->question_per_block);*/
            $attemptdatapagination = QuizAttemptData::
            where('attempt_id', '=', (int)$attempt_id)
                ->where('section_id', '=', (int)$section_id)
                ->orderBy('section_id', 'asc')
                ->paginate((int)$this->question_per_block);

            $attemptdatapagination_order = QuizAttemptData::
            where('attempt_id', '=', (int)$attempt_id)
                ->where('section_id', '=', (int)$section_id)
                ->orderBy('section_id', 'asc')
                ->whereIn('question_id', $ques_ids_ary)->get();
        } else {
            $attemptdatapagination = QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
                ->where('section_id', '=', (int)$section_id)
                ->where('status', '!=', 'VIEWED')
                ->orderBy('section_id', 'asc')
                ->paginate((int)$this->question_per_block);
        }

        $attemptdata = null;
        if (isset($attemptdatapagination_order) && !empty($attemptdatapagination_order)) {
            $active_question = $attemptdatapagination_order->first();
            $attemptdata = $attemptdatapagination_order->keyBy('question_id');
        } else {
            $active_question = $attemptdatapagination->first();
        }

        if (Input::has('q_id')) {
            $question_id = Input::get('q_id');
            if (!empty($question_id)) {
                $q = QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
                    ->where('section_id', '=', (int)$section_id)
                    ->where('question_id', '=', (int)$question_id)
                    ->first();
                if (!is_null($q)) {
                    $active_question = $q;
                }
            }
        } else {
            $start = (int)($page - 1) * config('app.question_per_block');
            $q = QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
                ->where('question_id', '=', (int)$ques_ids_ary[$start])
                ->first();
            if (!is_null($q)) {
                $active_question = $q;
            }
        }

        if (Input::get('from') != 'myactivity') {
            MyActivity::getInsertActivity([
                'module' => 'assessment',
                'action' => 'attempt review',
                'module_name' => $quiz->quiz_name,
                'module_id' => (int)$quiz->quiz_id,
                'url' => Request::path(),
            ]);
        }
        try {
            $section_details = null;
            $active_section = '';
            if (isset($attempt->section_details)) {
                $s_data = Section::getQuestionInQuiz($quiz->quiz_id);
                if (!empty($s_data)) {
                    $section_details = $s_data->toArray();
                }
                foreach ($attemptdatapagination as $key => $value) {
                    $active_section = $value->section_id;
                }
            }
            if (empty($active_section)) {
                $active_section = $section_id;
            }
        } catch (Exception $e) {
            return parent::getError($this->theme, $this->theme_path, 401);
        }
        $this->layout = view('portal.theme.' . $this->theme . '.layout.full_page_layout');
        if (isset($quiz->type) && $quiz->type === "QUESTION_GENERATOR") {
            $this->layout->pagetitle = 'Question generator | ' . ucwords($quiz->quiz_name);
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->content = view($this->theme_path . '.assessment.review.section_random_question_generator')
                ->with('quiz', $quiz)
                ->with('attempt', $attempt)
                ->with('attempts', $attempts)
                ->with('attemptdatapagination', $attemptdatapagination)
                ->with('section_details', $section_details)
                ->with('active_section', $active_section)
                ->with('active_question', $active_question)
                ->with('requestUrl', $requestUrl);
        } else {
            $this->layout->pagetitle = 'Assessment | ' . ucwords($quiz->quiz_name);
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->content = view($this->theme_path . '.assessment.review.section')
                ->with('quiz', $quiz)
                ->with('attempt', $attempt)
                ->with('attempts', $attempts)
                ->with('attemptdatapagination', $attemptdatapagination)
                ->with('section_details', $section_details)
                ->with('active_section', $active_section)
                ->with('ques_order_ary', $ques_ids_ary)
                ->with('attemptdata', $attemptdata)
                ->with('active_question', $active_question)
                ->with('requestUrl', $requestUrl);
        }
    }

    public function postQuestionGenerator($id, IQuizService $quiz_service)
    {
        $requestUrl = '';
        if (Input::has('requestUrl')) {
            $requestUrl = 'requestUrl=' . Input::get('requestUrl');
        }
        $data = [];
        try {
            $questionGenerator = Quiz::getQuizByCustomId($id);
            try {
                $user_quiz_rel = $quiz_service->getAllQuizzesAssigned();
            } catch (\Exception $e) {
                Log::info($e);
                $user_quiz_rel = ['seq_quizzes' => [], 'quiz_list' => [], 'feed_quiz_list' => [], 'direct_quizzes' => [], 'feed_list' => []];
            }
            $feedQuizList = $user_quiz_rel['feed_quiz_list'];
            $directQIds = $user_quiz_rel['direct_quizzes'];
            $directChennelQids = array_intersect($directQIds, array_keys($feedQuizList));
            $allQuizzes = Quiz::whereIn('quiz_id', [(int)$id])
                ->where('status', '=', 'ACTIVE')
                ->get();
            $replaceQDate = $quiz_service->replaceDates($feedQuizList, $allQuizzes);
            $replaceQDate = isset($replaceQDate[0]) ? $replaceQDate[0] : [];
            if (!empty($replaceQDate['start_time']) && Timezone::getTimeStamp($replaceQDate['start_time']) > time() && !$isAjax) {
                $this->getError($this->theme, $this->theme_path, 401);
            }
            $data["question_generator"] = ["_id" => $questionGenerator->_id, "name" => $questionGenerator->quiz_name, "total_question_limit" => $questionGenerator->total_question_limit, "instructions" => $questionGenerator->quiz_description];
            $attempts = QuizAttempt::getAttemptByUser($id, Auth::user()->uid);
            try {
                $questionKeywordMappingData = QuestionTagMapping::getKeywordQuestionsMappingByQuiz($questionGenerator->quiz_id);
            } catch (QuestionTagMappingNotFoundException $e) {
                $questionKeywordMappingData = null;
            }
            if ($attempts->isEmpty()) {
                $quizAttempt = new QuizAttempt();
                $quizAttempt = $quizAttempt->insertQuizAttempt(QuizHelper::formQuizAttemptData($questionGenerator));
            } else {
                $quizAttempt = $attempts->first();
            }

            $data["quiz_attempt_id"] = $quizAttempt->_id;
            $data["quiz_id"] = $quizAttempt->quiz_id;
            $data["type"] = "QUESTION_GENERATOR";

            $data["total_no_of_questions"] = $quizAttempt->total_no_of_questions;
            $data["total_question_limit"] = $questionGenerator->total_question_limit;
            $data["total_attempted_questions"] = $quizAttempt->total_attempted_questions;
            $data["quiz_attempt_id_u"] = $quizAttempt->attempt_id;

            $data["get_active_question"] = false;

            if (isset($questionGenerator->is_sections_enabled) && $questionGenerator->is_sections_enabled) {
                $sections = [];
                foreach ($quizAttempt->sections as $index => $sectionId) {
                    $sectionDetails = $quizAttempt->section_details["{$sectionId}"];
                    if ($sectionDetails["status"] === "OPENED") {
                        if (!empty($sectionDetails["questions"])) {
                            $tmpSectionDetails = null;
                            if (!is_null($questionKeywordMappingData)) {
                                if (isset($questionKeywordMappingData->sections["{$sectionId}"])) {
                                    $sectionKeywordMappingData = $questionKeywordMappingData->sections["{$sectionId}"];
                                    if (isset($sectionKeywordMappingData["keyword_questions"]) && is_array($sectionKeywordMappingData["keyword_questions"]) && !empty($sectionKeywordMappingData["keyword_questions"])) {
                                        $tmpSectionDetails["enable_keyword_search"] = true;
                                    }
                                }
                            }

                            if (isset($quizAttempt->active_section_id) && !empty($quizAttempt->active_section_id)) {
                                if ((int)$sectionId === (int)$quizAttempt->active_section_id) {
                                    $tmpSectionDetails["is_active_section"] = true;

                                    $activeSectionAttemptData = $quizAttempt->section_details["{$sectionId}"];

                                    if (isset($activeSectionAttemptData["active_question_id"]) && is_numeric($activeSectionAttemptData["active_question_id"])) {
                                        $data["get_active_question"] = true;
                                    }

                                    if (isset($tmpSectionDetails["enable_keyword_search"]) && ($tmpSectionDetails["enable_keyword_search"])) {
                                        if (isset($activeSectionAttemptData["active_keyword"]) && (!empty($activeSectionAttemptData["active_keyword"]))) {
                                            $data["active_keyword"] = $activeSectionAttemptData["active_keyword"];
                                        }
                                    }
                                }
                            }

                            $tmpSectionDetails["id"] = $sectionDetails["_id"];
                            $tmpSectionDetails["title"] = $sectionDetails["title"];
                            $sections[] = $tmpSectionDetails;
                        }
                    }
                }
                $data["is_sections_enabled"] = true;
                $data["sections"] = $sections;
            } else {
                if (isset($quizAttempt->active_question_id) && !empty($quizAttempt->active_question_id)) {
                    $data["get_active_question"] = true;
                }

                if (!is_null($questionKeywordMappingData)) {
                    if (isset($questionKeywordMappingData["keyword_questions"]) && is_array($questionKeywordMappingData["keyword_questions"]) && !empty($questionKeywordMappingData["keyword_questions"])) {
                        $data["enable_keyword_search"] = true;
                    }
                }

                if (isset($data["enable_keyword_search"]) && ($data["enable_keyword_search"])) {
                    if (isset($quizAttempt->active_keyword) && !empty($quizAttempt->active_keyword)) {
                        $data["active_keyword"] = $quizAttempt->active_keyword;
                    }
                }

                $data["is_sections_enabled"] = false;
            }
            $data["qg_data_flag"] = true;
            $this->layout->pagetitle = "Assessment | " . ucwords($questionGenerator->quiz_name);
            $this->layout->theme = "portal/theme/{$this->theme}";
            $this->layout->header = view("{$this->theme_path}.common.header");
            $this->layout->footer = view("{$this->theme_path}.common.footer");
            $this->layout->content = view("{$this->theme_path}.assessment.question_generator")
                ->with("data", $data)
                ->with('requestUrl', $requestUrl);
        } catch (QuizAttemptClosedException $e) {
            redirect(URL::to("assessment/detail/{$id}"));
        } catch (AccessDeniedException $e) {
            $this->getError($this->theme, $this->theme_path, 401);
        } catch (Exception $e) {
            $this->getError($this->theme, $this->theme_path, 404);
        }
    }

    public function postQuestionKeywords()
    {
        try {
            $quizId = Request::input("quiz_uid", null);
            $sectionId = Request::input("section_uid", null);
            $keyword = Request::input("keyword", null);
            $quiz = Quiz::getQuizById($quizId);

            $filteredKeywords = [];
            $questionKeywordMapping = QuestionTagMapping::getKeywordQuestionsMappingByQuiz($quiz->quiz_id);

            if (isset($quiz->is_sections_enabled) && ($quiz->is_sections_enabled)) {
                if (is_null($sectionId)) {
                    throw new Exception();
                }
                $section = Section::getSectionById($sectionId);

                $questionKeywordDataBySection = $questionKeywordMapping["sections"]["{$section->section_id}"];

                if (isset($questionKeywordDataBySection["keywords"]) && !empty($questionKeywordDataBySection["keywords"])) {
                    $keywordsCollection = Collection::make($questionKeywordDataBySection["keywords"]);
                }
            } else {
                if (isset($questionKeywordMapping->keywords) && !empty($questionKeywordMapping->keywords)) {
                    $keywordsCollection = Collection::make($questionKeywordMapping->keywords);
                }
            }
            if (isset($keywordsCollection)) {
                $filteredKeywords = $keywordsCollection->filter(function ($collectionKeyword) use ($keyword) {
                    if (isset($keyword) && !empty($keyword)) {
                        return (strpos($collectionKeyword, $keyword) !== false);
                    } else {
                        return true;
                    }
                });

                $filteredKeywords = $filteredKeywords->map(function ($value, $key = null) {
                    $data = new \stdClass();
                    $data->id = $value;
                    $data->text = $value;
                    return $data;
                });

                $filteredKeywords = $filteredKeywords->toArray();
            }
        } catch (Exception $e) {
            Log::error("\"{$e->getMessage()}\" error occurred in file \"{$e->getFile()}\" in line number \"{$e->getLine()}\"");
        } finally {
            return response()->json($filteredKeywords);
        }
    }

    public function postQuestion(IQuestionService $questionService, IQuizService $quiz_service)
    {
        $data = [];
        try {
            $question = null;

            $quizId = Request::input("quiz_uid", null);
            $attemptId = Request::input("attempt_uid", null);
            $keyword = Request::input("keyword", null);
            $quiz = Quiz::getQuizById($quizId);
            
            try {
                $user_quiz_rel = $quiz_service->getAllQuizzesAssigned();
            } catch (\Exception $e) {
                Log::info($e);
                $user_quiz_rel = ['seq_quizzes' => [], 'quiz_list' => [], 'feed_quiz_list' => [], 'direct_quizzes' => [], 'feed_list' => []];
            }
            $feedQuizList = $user_quiz_rel['feed_quiz_list'];
            $directQIds = $user_quiz_rel['direct_quizzes'];
            $directChennelQids = array_intersect($directQIds, array_keys($feedQuizList));
            $allQuizzes = Quiz::whereIn('quiz_id', [(int)$quizId])
                ->where('status', '=', 'ACTIVE')
                ->get();
            $replaceQDate = $quiz_service->replaceDates($feedQuizList, $allQuizzes);
            $replaceQDate = isset($replaceQDate[0]) ? $replaceQDate[0] : [];
            if (!empty($replaceQDate['start_time']) && Timezone::getTimeStamp($replaceQDate['start_time']) > time() && !$isAjax) {
                $this->getError($this->theme, $this->theme_path, 401);
            }
            $attempt = QuizAttempt::getAttemptById($attemptId);

            $currentActiveQuestionId = null;
            $section = null;
            $questionId = null;
            $isLastQuestionInQuiz = false;
            $timeTakenInSeconds = 0;
            $attemptedQuestions = [];
            $unattemptedQuestions = [];
            $getNextQuestion = true;

            if (isset($quiz->is_sections_enabled) && ($quiz->is_sections_enabled)) {
                $isLastQuestionInSection = false;
                $sectionId = Request::input("section_uid", null);
                if (is_null($sectionId)) {
                    throw new Exception();
                }
                $section = Section::getSectionById($sectionId);

                if ((!isset($attempt->active_section_id)) || ($attempt->active_section_id !== $section->section_id)) {
                    $attempt->active_section_id = $section->section_id;
                }

                $attemptSectionDetails = $attempt->section_details;
                if (isset($attemptSectionDetails["{$section->section_id}"]["active_question_id"]) && !empty($attemptSectionDetails["{$section->section_id}"]["active_question_id"])) {
                    $currentActiveQuestionId = (int)$attempt->section_details["{$section->section_id}"]["active_question_id"];

                    $activeQuestionAttemptData = QuizAttemptData::getQuestionAttemptData($attempt->attempt_id, $currentActiveQuestionId);

                    if ($activeQuestionAttemptData->status === "VIEWED") {
                        $getNextQuestion = false;
                        $questionId = $currentActiveQuestionId;
                    }
                }

                if ($getNextQuestion) {
                    if (isset($keyword) && !empty($keyword)) {
                        $unattemptedQuestions = QuizHelper::getNextRandomQuestionByKeyword($quiz, $attempt, $keyword, $section);

                        if (count($unattemptedQuestions) === 1) {
                            $data["is_last_question_in_keyword"] = true;
                        }
                    } else {
                        if (isset($attemptSectionDetails["{$attempt->active_section_id}"]["attempted_questions"])) {
                            $attemptedQuestions = $attemptSectionDetails["{$attempt->active_section_id}"]["attempted_questions"];
                        }
                        $unattemptedQuestions = array_diff($attemptSectionDetails["{$attempt->active_section_id}"]["questions"], $attemptedQuestions);
                    }

                    if (!(is_array($unattemptedQuestions) && !empty($unattemptedQuestions))) {
                        throw new NoQuestionsFoundException();
                    }

                    $questionId = (int)current($unattemptedQuestions);
                    $attemptSectionDetails["{$section->section_id}"]["active_question_id"] = $questionId;

                    if (isset($keyword) && !empty($keyword)) {
                        $attemptSectionDetails["{$section->section_id}"]["active_keyword"] = $keyword;
                    }
                }

                if (((int)$attemptSectionDetails["{$attempt->active_section_id}"]["no_of_questions"] === 1) || (((int)$attemptSectionDetails["{$attempt->active_section_id}"]["no_of_questions"] - (int)$attemptSectionDetails["{$attempt->active_section_id}"]["total_attempted_questions"]) <= 1)) {
                    $isLastQuestionInSection = true;
                }

                $attempt->section_details = $attemptSectionDetails;
            } else {
                if (isset($attempt->active_question_id) && !empty($attempt->active_question_id)) {
                    $currentActiveQuestionId = (int)$attempt->active_question_id;
                    $activeQuestionAttemptData = QuizAttemptData::getQuestionAttemptData($attempt->attempt_id, $currentActiveQuestionId);
                    if ($activeQuestionAttemptData->status === "VIEWED") {
                        $getNextQuestion = false;
                        $questionId = $currentActiveQuestionId;
                    }
                }

                if ($getNextQuestion) {
                    if ($attempt->total_attempted_questions > 0) {
                        $attemptedQuestions = $attempt->attempted_questions;
                    }

                    if (isset($keyword) && !empty($keyword)) {
                        $unattemptedQuestions = QuizHelper::getNextRandomQuestionByKeyword($quiz, $attempt, $keyword, $section);
                        if (count($unattemptedQuestions) === 1) {
                            $data["is_last_question_in_keyword"] = true;
                        }
                    } else {
                        $unattemptedQuestions = array_diff($attempt->questions, $attemptedQuestions);
                    }

                    if (!(is_array($unattemptedQuestions) && !empty($unattemptedQuestions))) {
                        throw new NoQuestionsFoundException();
                    }

                    $questionId = (int)current($unattemptedQuestions);

                    $attempt->active_question_id = $questionId;

                    if (isset($keyword) && !empty($keyword)) {
                        $attempt->active_keyword = $keyword;
                    }
                }
            }

            $totalQuestions = $attempt->total_no_of_questions;
            if ($quiz->total_question_limit < $totalQuestions) {
                $totalQuestions = $quiz->total_question_limit;
            }

            if (($totalQuestions - $attempt->total_attempted_questions) === 1) {
                $isLastQuestionInQuiz = true;
            }

            $question = $questionService->getQuestionByCustomId($questionId);
            if (isset($currentActiveQuestionId) && ($currentActiveQuestionId === $questionId)) {
                if (isset($activeQuestionAttemptData->history) && is_array($activeQuestionAttemptData->history) && !empty($activeQuestionAttemptData)) {
                    $tmpActiveQuestionAttemptHistory = $activeQuestionAttemptData->history;
                    $tmpActiveQuestionAttemptHistory[] = [
                        "status" => "STARTED",
                        "time" => Carbon::now()->timestamp
                    ];
                    $activeQuestionAttemptData->history = $tmpActiveQuestionAttemptHistory;
                    $activeQuestionAttemptData->save();

                    if (isset($activeQuestionAttemptData->time_spend) && is_array($activeQuestionAttemptData->time_spend) && !empty($activeQuestionAttemptData->time_spend)) {
                        $timeTakenInSeconds = array_sum($activeQuestionAttemptData->time_spend);
                    }
                }
                $question->answers = $activeQuestionAttemptData->answers;
            } else {
                $questionAttemptDetails = QuizHelper::formQuestionAttemptData($quiz, $attempt, $question, $section);
                $questionAttemptData = new QuizAttemptData();
                $questionAttemptData->insertQuestionAttemptData($questionAttemptDetails);
                $question->answers = $questionAttemptData->answers;
            }
            $attempt->save();

            $data["data_flag"] = true;
            $data["question"] = $question;
            $data["time_taken_in_seconds"] = $timeTakenInSeconds;
            $data["total_attempted_questions"] = $attempt->total_attempted_questions;
            if (isset($quiz->is_sections_enabled) && ($quiz->is_sections_enabled)) {
                $data["is_last_question_in_section"] = $isLastQuestionInSection;
            }
            $data["is_last_question_in_quiz"] = $isLastQuestionInQuiz;
            Session::flash("quiz_attempt_id", $attempt->_id);
            Session::flash("question_id", $question->question_id);
        } catch (QuizAttemptClosedException $e) {
            $data["data_flag"] = false;
            $data["message"] = "You have attempted all the questions in this question generator.";
        } catch (NoQuestionsFoundException $e) {
            $data["data_flag"] = false;
            $data["message"] = "There are no questions left in selected section or concept.";
        } catch (KeywordNotFoundException $e) {
            $data["data_flag"] = false;
            $data["message"] = "Concept you searched for doesn't exist";
        } catch (Exception $e) {
            $data["data_flag"] = false;
            $data["message"] = "Something went wrong while fetching the question. Please try again later.";
        } finally {
            return view("{$this->theme_path}.assessment._qg_question")
                ->with("data", $data);
        }
    }

    public function postAttemptQuestion(IQuestionService $questionService, IQuizService $quiz_service)
    {
        $data = [];
        try {
            $closeAttempt = false;
            $quizId = Request::input("quiz_uid", null);
            $attemptId = Request::input("attempt_uid", null);
            $quiz = Quiz::getQuizById($quizId);
            try {
                $user_quiz_rel = $quiz_service->getAllQuizzesAssigned();
            } catch (\Exception $e) {
                Log::info($e);
                $user_quiz_rel = ['seq_quizzes' => [], 'quiz_list' => [], 'feed_quiz_list' => [], 'direct_quizzes' => [], 'feed_list' => []];
            }
            $feedQuizList = $user_quiz_rel['feed_quiz_list'];
            $directQIds = $user_quiz_rel['direct_quizzes'];
            $directChennelQids = array_intersect($directQIds, array_keys($feedQuizList));
            $allQuizzes = Quiz::whereIn('_id', [(int)$quizId])
                ->where('status', '=', 'ACTIVE')
                ->get();
            $replaceQDate = $quiz_service->replaceDates($feedQuizList, $allQuizzes);
            $replaceQDate = isset($replaceQDate[0]) ? $replaceQDate[0] : [];
            if (!empty($replaceQDate['start_time']) && Timezone::getTimeStamp($replaceQDate['start_time']) > time() && !$isAjax) {
                $this->getError($this->theme, $this->theme_path, 401);
            }
            $attempt = QuizAttempt::getAttemptById($attemptId);
            $section = null;
            $questionId = Request::get("question_uid", null);
            $questionData = $questionService->getQuestion($questionId);
            if (!$questionData["data_flag"]) {
                throw new Exception;
            }
            $question = $questionData["details"];
            $questionAttemptData = QuizAttemptData::getQuestionAttemptData($attempt->attempt_id, $question->question_id);
            $selectedAnswerIndex = Request::input("selected_answer_index", null);
            if (!isset($questionAttemptData->answers[$selectedAnswerIndex])) {
                $selectedAnswerIndex = null;
            }
            if (!isset($attempt->total_attempted_questions)) {
                $attempt->total_attempted_questions = 0;
            }
            $attempt->total_attempted_questions += 1;
            $data["total_attempted_questions"] = $attempt->total_attempted_questions;

            if (!is_null($selectedAnswerIndex)) {
                $data["is_answer_selected"] = true;
                if ((int)$selectedAnswerIndex === $questionAttemptData->correct_answer_index) {
                    $data["is_answer_correct"] = true;
                    $questionAttemptData->answer_status = "CORRECT";
                    $questionAttemptData->obtained_mark = $questionAttemptData->question_mark;
                } else {
                    $data["is_answer_correct"] = false;
                    $data["incorrect_answer"] = $questionAttemptData->answers[(int)$selectedAnswerIndex]["answer"];
                    $data["incorrect_answer_rationale"] = $questionAttemptData->answers[(int)$selectedAnswerIndex]["rationale"];
                    $questionAttemptData->answer_status = "INCORRECT";
                    $questionAttemptData->obtained_mark = 0;
                }
                $questionAttemptData->status = "ANSWERED";
            } else {
                $data["is_answer_selected"] = false;
                $questionAttemptData->answer_status = "";
                $questionAttemptData->status = "NOT_ANSWERED";
            }

            $data["correct_answer"] = $questionAttemptData->answers[(int)$questionAttemptData->correct_answer_index]["answer"];
            $data["correct_answer_rationale"] = $questionAttemptData->answers[(int)$questionAttemptData->correct_answer_index]["rationale"];

            $questionAttemptData->user_answer_index = $selectedAnswerIndex;
            $questionAttemptData->save();

            if (isset($quiz->is_sections_enabled) && ($quiz->is_sections_enabled)) {
                $sectionId = Request::input("section_uid", null);
                if (is_null($sectionId)) {
                    throw new Exception();
                }
                $section = Section::getSectionById($sectionId);
                $sectionDetails = $attempt->section_details;

                if (isset($sectionDetails["{$section->section_id}"]["attempted_questions"]) && is_array($sectionDetails["{$section->section_id}"]["attempted_questions"])) {
                    array_push($sectionDetails["{$section->section_id}"]["attempted_questions"], $question->question_id);
                } else {
                    $sectionDetails["{$section->section_id}"]["attempted_questions"] = [$question->question_id];
                }

                $sectionDetails["{$section->section_id}"]["total_attempted_questions"] += 1;

                if (isset($sectionDetails["{$section->section_id}"]["active_keyword"]) && !empty($sectionDetails["{$section->section_id}"]["active_keyword"])) {
                    $sectionDetails["{$section->section_id}"]["active_keyword"] = "";
                }

                $sectionDetails["{$section->section_id}"]["active_question_id"] = null;

                $attempt->active_section_id = null;

                if ((int)$sectionDetails["{$section->section_id}"]["total_attempted_questions"] === (int)$sectionDetails["{$section->section_id}"]["no_of_questions"]) {
                    $sectionDetails["{$section->section_id}"]["status"] = "COMPLETED";
                    $isAllSectionsCompleted = true;
                    foreach ($attempt->section_details as $tmpSectionDetails) {
                        if ($tmpSectionDetails["status"] === "OPENED") {
                            if (!empty($tmpSectionDetails["questions"])) {
                                $isAllSectionsCompleted = false;
                                break;
                            }
                        }
                    }
                    if ($isAllSectionsCompleted) {
                        $closeAttempt = true;
                    }
                }
                $attempt->section_details = $sectionDetails;
            } else {
                if (isset($attempt->attempted_questions) && is_array($attempt->attempted_questions)) {
                    $tmpAttemptedQuestions = $attempt->attempted_questions;
                    array_push($tmpAttemptedQuestions, $question->question_id);
                    $attempt->attempted_questions = $tmpAttemptedQuestions;

                    if (isset($attempt->active_keyword) && !empty($attempt->active_keyword)) {
                        $attempt->active_keyword = null;
                    }
                } else {
                    $attempt->attempted_questions = [$question->question_id];
                }

                $attempt->active_question_id = null;

                if (isset($attempt->active_keyword) && !empty($attempt->active_keyword)) {
                    $attempt->active_keyword = null;
                }
            }

            $totalQuestions = (int)$attempt->total_no_of_questions;
            if ((int)$quiz->total_question_limit < $totalQuestions) {
                $totalQuestions = (int)$quiz->total_question_limit;
            }

            if ($totalQuestions <= (int)$attempt->total_attempted_questions) {
                $closeAttempt = true;
            }

            if ($closeAttempt) {
                $this->putEntryIntoMyActivity($quiz);
                $attempt->status = "CLOSED";
                $attemptData = QuizAttemptData::where('attempt_id', $attempt->attempt_id)->get();
                $attempt->correct_answer_count = $attemptData->where('answer_status', 'CORRECT')->count();
                $attempt->in_correct_answer_count = $attemptData->where('answer_status', 'INCORRECT')->count();
                $attempt->un_attempted_question_count = 0;
                $attempt->completed_on = Carbon::now(Auth::user()->timezone)->timestamp;
            }
            $attempt->save();
            $data["data_flag"] = true;
            $isPractice = isset($quiz->practice_quiz) ? $quiz->practice_quiz : false;
            $this->putEntryInToOAQP((int)$attempt->attempt_id, $isPractice);
            $this->putEntryInToOca($quiz);
        } catch (Exception $e) {
            $data["data_flag"] = false;
        } finally {
            return view("{$this->theme_path}.assessment._qg_review_answer")
                ->with("data", $data);
        }
    }

    /**
     * [getClearAnswer - Clear answer to question]
     * @method getClearAnswer
     * @param  [type]         $attemptId [user taking quiz attempt ID]
     * @return [type]                    [redirection load question.]
     * @author Rudragoud Patil
     */
    public function getClearAnswer($attemptId)
    {
        try {
            //data prepare
            $requestUrl = '';
            if (Input::has('requestUrl')) {
                $requestUrl = 'requestUrl=' . Input::get('requestUrl');
            }
            $attemptedId = $attemptId;
            $questionId = Input::get('questionID');
            if (!empty($attemptedId) && !empty($questionId)) {//check for empty
                $quiz_info = QuizAttemptData::getQuestionAttemptData($attemptedId, $questionId);
                QuizAttemptData::where('attempt_id', '=', (int)$attemptedId)
                    ->where('question_id', '=', (int)$questionId)
                    ->update(
                        [
                            'user_response' => '',
                            'answer_status' => '',
                            'status' => 'STARTED'
                        ]
                    );// clear answer in quiz attempt
                $answered_list = array_diff(Session::get('assessment.' . $attemptedId . '.question_answered'), [$questionId]);
                Session::put('assessment.' . $attemptedId . '.question_answered', $answered_list);
                QuizAttempt::where('attempt_id', (int)$attemptedId)->pull('details.answered', (int)$questionId, true);
                if (Input::get('reviewed', '') == '') {
                    $data['mark_review'] = false;
                    QuizAttempt::where('attempt_id', (int)$attemptedId)->pull('details.reviewed', (int)$questionId, true);
                }
                return redirect('assessment/attempt/' . $attemptedId . '?page=' . Input::get('page') . '&section=' . Input::get('section') . '&' . $requestUrl);
            } else {
                echo "Parameter is missing...!!!";
            }
        } catch (Exception $e) {
            if (empty($attemptId)) {
                Log::error('User Portal - Quiz Attempt - Clear Answer - Oops... The user attempt id is missing..!!!');
            } elseif (!Input::has('questionID')) {
                Log::error('User Portal - Quiz Attempt - Clear Answer - Oops... The user question id is missing..!!!');
            }
        }
    }

    public function getMarkReview($attemptId, $page, $section_id, $questionID)
    {
        $this->holdAnswerOnReview($questionID);
        try {
            //data prepare
            $requestUrl = '';
            if (Input::has('requestUrl')) {
                $requestUrl = 'requestUrl=' . Input::get('requestUrl');
            }
            $attemptedId = $attemptId;
            //$questionID = Input::get('questionID');
            if (!empty($attemptedId) && !empty($questionID)) {//check for empty
                $quiz_info = QuizAttemptData::getQuestionAttemptData($attemptedId, $questionID);
                QuizAttemptData::where('attempt_id', '=', (int)$attemptedId)
                    ->where('question_id', '=', (int)$questionID)
                    ->update(
                        [
                            'mark_review' => true
                        ]
                    );// add question to quiz review
                QuizAttempt::where('attempt_id', (int)$attemptedId)->push('details.reviewed', (int)$questionID, true);
                if (Session::has('assessment.' . $attemptedId . '.question_review') &&
                    !empty(Session::get('assessment.' . $attemptedId . '.question_review'))
                ) {
                    $answered_list = array_merge(Session::get('assessment.' . $attemptedId . '.question_review'), [$questionID]);
                    Session::put('assessment.' . $attemptedId . '.question_review', $answered_list);
                } else {
                    $answered_list = [$questionID];
                    Session::put('assessment.' . $attemptedId . '.question_review', $answered_list);
                }
                if (Request::ajax()) {
                    return response()->json(['status' => true]);
                }
                return redirect('assessment/attempt/' . $attemptedId . '?page=' . $page . '&section=' . $section_id . '&' . $requestUrl);
            } else {
                echo "Parameter is missing...!!!";
            }
        } catch (Exception $e) {
            if (empty($attemptId)) {
                Log::error('User Portal - Quiz Attempt - Mark Review Answer - Oops... The user attempt id is missing..!!!');
            } elseif (!Input::has('questionID')) {
                Log::error('User Portal - Quiz Attempt - mark Review Answer - Oops... The user question id is missing..!!!');
            }
            if (Request::ajax()) {
                return response()->json(['status' => false]);
            }
        }
    }

    public function getFinishReview($attemptId, $page, $section_id, $questionID)
    {
        try {
            $this->holdAnswerOnReview($questionID);
            //data prepare
            $requestUrl = '';
            if (Input::has('requestUrl')) {
                $requestUrl = 'requestUrl=' . Input::get('requestUrl');
            }
            $attemptedId = $attemptId;
            //$questionID = Input::get('questionID');
            if (!empty($attemptedId) && !empty($questionID)) {//check for empty
                $quiz_info = QuizAttemptData::getQuestionAttemptData($attemptedId, $questionID);
                QuizAttemptData::where('attempt_id', '=', (int)$attemptedId)
                    ->where('question_id', '=', (int)$questionID)
                    ->update(
                        [
                            'mark_review' => false
                        ]
                    );// add question to quiz review
                QuizAttempt::where('attempt_id', (int)$attemptedId)->pull('details.reviewed', (int)$questionID, true);
                if (Session::has('assessment.' . $attemptedId . '.question_review') &&
                    !empty(Session::get('assessment.' . $attemptedId . '.question_review'))
                ) {
                    $answered_list = array_diff(Session::get('assessment.' . $attemptedId . '.question_review'), [$questionID]);
                    Session::put('assessment.' . $attemptedId . '.question_review', $answered_list);
                }
                if (Request::ajax()) {
                    return response()->json(['status' => true]);
                }
                return redirect('assessment/attempt/' . $attemptedId . '?page=' . $page . '&section=' . $section_id . '&' . $requestUrl);
            } else {
                echo "Parameter is missing...!!!";
            }
        } catch (Exception $e) {
            if (empty($attemptId)) {
                Log::error('User Portal - Quiz Attempt - Finish Review Answer - Oops... The user attempt id is missing..!!!');
            } elseif (!Input::has('questionID')) {
                Log::error('User Portal - Quiz Attempt - Finish Review Answer - Oops... The user question id is missing..!!!');
            }
            if (Request::ajax()) {
                return response()->json(['status' => false]);
            }
        }
    }

    private function holdAnswerOnReview($questionID)
    {
        $answered_option = '';
        if (Input::has("q:" . $questionID)) {
            $answered_option = Input::get("q:" . $questionID);
            Session::put('answered_option', $answered_option);
        }
    }

    private function putEntryInToOAQP($attemptId = 0, $isPractice = false)
    {
        $attemptId = (int)$attemptId;
        if ($attemptId > 0) {
            $attempt = QuizAttempt::where('attempt_id', '=', $attemptId)->first();
            $quizType = $attempt->type;
            $data = [];
            if ($quizType == 'QUESTION_GENERATOR') {
                $percentageCompletion = ($attempt->total_attempted_questions /
                    (($attempt->total_no_of_questions > 1) ?
                        $attempt->total_no_of_questions :
                        1));
                $data = [
                    'user_id' => (int)Auth::user()->uid,
                    'quiz_id' => (int)$attempt->quiz_id,
                    'type' => $quizType,
                    'completion_percentage' => round(($percentageCompletion * 100), 2),
                ];
            } else {
                $last_attempt_data = QuizAttemptData::where('quiz_id', '=', (int)$attempt->quiz_id)
                    ->where('attempt_id', '=', $attemptId)
                    ->where('user_id', '=', (int)Auth::user()->uid)
                    ->get();
                if (empty($last_attempt_data)) {
                    return false;
                }
                $score = (float) QuizHelper::roundOfNumber($attempt->obtained_mark /
                    ((float)$attempt->total_mark > 1 ? (float)$attempt->total_mark : 1), 5);
               
                $totalQuestions = count($attempt->questions);
                $attempt_ques_id = $last_attempt_data->where('answer_status', '')->pluck('question_id')->all();
                $attempt_ques_id_temp = $last_attempt_data->where('status', 'NOT_VIEWED')->pluck('question_id')->all();
                $for_skip = array_unique(array_merge($attempt_ques_id_temp, $attempt_ques_id));
                $correc_ques_id = $last_attempt_data->where('answer_status', 'CORRECT')->pluck('question_id')->all();
                $incorrec_ques_id = $last_attempt_data->where('answer_status', 'INCORRECT')->pluck('question_id')->all();
                if ($totalQuestions < 1) {
                    $totalQuestions = 1;
                }
                $for_incorrect = array_diff($incorrec_ques_id, $for_skip);
                $skip_ques = round((count($for_skip) / $totalQuestions) * 100, 2);
                $correct_per = round((count($correc_ques_id) / $totalQuestions) * 100, 2);
                $incorrect_per = round((count($for_incorrect) / $totalQuestions) * 100, 2);
                if (($correct_per + $incorrect_per) > 0) {
                    $accuracy = $correct_per / ($correct_per + $incorrect_per);
                } else {
                    $accuracy = 0;
                }

                if (!is_null($attempt) &&
                    isset($attempt->completed_on) &&
                    !empty($attempt->completed_on) &&
                    !is_string($attempt->completed_on)
                ) {
                    $secs = $attempt->started_on->diffInSeconds($attempt->completed_on);
                    $speedTotal = $secs / ($totalQuestions > 1 ? $totalQuestions : 1);
                    $days = intval($speedTotal / 86400);
                    $remainder = $speedTotal % 86400;
                    $hrs = intval($remainder / 3600);
                    $remainder = $remainder % 3600;
                    $min = intval($remainder / 60);
                    $remainder = $remainder % 60;
                    $sec = $remainder;
                } else {
                    $days = 0;
                    $hrs = 0;
                    $min = 0;
                    $sec = 0;
                    $speedTotal = 0;
                }
                $min = ($hrs * 60) + $min;
                // $hrs = ($hrs >= 10) ? $hrs : ('0'.$hrs);
                $min = ($min >= 10) ? $min : ('0' . $min);
                $sec = ($sec >= 10) ? $sec : ('0' . $sec);
                $speed = $min . ':' . $sec;
                $data = [
                    'user_id' => (int)Auth::user()->uid,
                    'quiz_id' => (int)$attempt->quiz_id,
                    'is_practice' => $isPractice,
                    'speed' => $speed,
                    'accuracy' => round($accuracy * 100, 2),
                    'score' => round($score * 100, 2),
                    'speed_h' => (int)$hrs,
                    'speed_m' => (int)$min,
                    'speed_s' => (int)$sec,
                    'speed_total' => (int)$speedTotal,
                ];
            }
            if (!empty($data)) {
                $res = OverAllQuizPerformance::insertData($data);
                if ($res) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    public function putEntryInToOca($quiz = [])
    {
        if (empty($quiz)) {
            return false;
        }
        $returnFlag = true;
        $userId = (int)Auth::user()->uid;
        $quizId = (int)$quiz->quiz_id;
        if (isset($quiz->relations['feed_quiz_rel'])) {
            $channelRel = $quiz->relations['feed_quiz_rel'];
            try {
                $userChannelIds =  array_get($this->program_service->getAllProgramsAssignedToUser($userId), 'channel_ids', []);
            } catch (Exception $e) {
                Log::info('No channels are assigned to this user user_id: '.$userId);
                return false;
            }
            if (empty(array_intersect(array_keys($channelRel), $userChannelIds))) {
                return false;
            }
            foreach ($channelRel as $channelId => $specificChannelRel) {
                if (!in_array($channelId, $userChannelIds)) {
                    continue;
                }
                $isViewedEle = false;
                $data = [];
                $completion = 0;
                $postCompletion = [];
                $itemDetails = [];
                $speed = '';
                $accuracy = 0;
                $score = 0;
                $speedSecs = 0;
                $data['user_id'] = $userId;
                $data['channel_id'] = $channelId;
                $chennelDetails = Program::getProgramDetailsByID($channelId);
                if (is_null($chennelDetails)) {
                    return false;
                }
                $chennelSlug = $chennelDetails->program_slug;
                $postCountChannel = Packet::where('feed_slug', '=', $chennelSlug)
                    ->where('status', '!=', 'DELETED')
                    ->count();
                $isExists = OverAllChannelAnalytic::isExists($channelId, $userId);
                if (!is_null($isExists) || !empty($isExists)) {
                    $existsCompletion = $isExists->completion;
                    $existsPostCompletion = $isExists->post_completion;
                    $existsItemDetails = $isExists->item_details;
                }
                if (!empty($userChannelIds) || in_array($channelId, $userChannelIds)) {
                    foreach ($specificChannelRel as $postId) {
                        $postDeatils = Packet::getPacketByID((int)$postId);
                        $postElement = [];
                        $viewedPostItem = [];
                        $countEle = 1;
                        $postComp = 0;
                        if (isset($postDeatils[0]['elements']) && !empty($postDeatils[0]['elements'])) {
                            foreach ($postDeatils[0]['elements'] as $element) {
                                $postElement[] = $element['type'] . '_' . $element['id'];
                            }
                            $countEle = count($postElement);
                        }
                        $postKey = 'p_' . $postId;
                        if (!is_null($isExists) || !empty($isExists)) {
                            if (isset($existsItemDetails[$postKey])) {
                                $tempPostEle = $existsItemDetails[$postKey];
                                if (in_array('assessment_'.$quizId, $tempPostEle)) {
                                    $isViewedEle = true;
                                }
                                $tempPostEle[] = 'assessment_'.$quizId;
                                $tempPostEle = array_unique($tempPostEle);
                                $viewedCount = count(array_intersect($tempPostEle, $postElement));
                                $existsPostCompletion[$postKey] = round(
                                    ($viewedCount / (($countEle > 1) ? $countEle : 1)) * 100,
                                    2
                                );
                                $existsItemDetails[$postKey] = $tempPostEle;
                            } else {
                                $tempPostEle = [];
                                $tempPostEle[] = 'assessment_' . $quizId;
                                $viewedCount = count(array_intersect($tempPostEle, $postElement));
                                $existsPostCompletion[$postKey] = $postCompletion[$postKey] = round(
                                    (($viewedCount / (($countEle > 1) ? $countEle : 1)) * 100),
                                    2
                                );
                                $existsItemDetails[$postKey] = $itemDetails[$postKey] = $tempPostEle;
                            }
                        } else {
                            $tempPostEle = [];
                            $tempPostEle[] = 'assessment_' . $quizId;
                            $viewedCount = count(array_intersect($tempPostEle, $postElement));
                            $postCompletion[$postKey] = round(
                                ($viewedCount / (($countEle > 1) ? $countEle : 1)) * 100,
                                2
                            );
                            $itemDetails[$postKey] = $tempPostEle;
                        }
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
                }
                $channelQuizIds = Quiz::where('relations.feed_quiz_rel.' . $channelId, 'exists', true)
                    ->where('type', '!=', 'QUESTION_GENERATOR')
                    ->get(['quiz_id'])
                    ->pluck('quiz_id')
                    ->all();
                if (!empty($channelQuizIds)) {
                    $agreggationResult = OverAllQuizPerformance::getAgregationValues(
                        $channelQuizIds,
                        $userId
                    );
                    $agreggationResult = $agreggationResult->first();
                    if (!empty($agreggationResult)) {
                        $speed = round($agreggationResult->speed_h) . ':'
                            . round($agreggationResult->speed_m) . ':'
                            . round($agreggationResult->speed_s);
                        $accuracy = round($agreggationResult->accuracy, 2);
                        $score = round($agreggationResult->score, 2);
                        $speedSecs = round($agreggationResult->speed_h) * 60 * 60
                            + round($agreggationResult->speed_m) * 60
                            + round($agreggationResult->speed_s);
                    }
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
                $data['speed'] = $speed;
                $data['accuracy'] = $accuracy;
                $data['score'] = $score;
                $data['speed_secs'] = (int)$speedSecs;
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
                $data = [];
            }
        }
        return $returnFlag;
    }

    public function putEntryIntoMyActivity($quiz = [])
    {
        if (empty($quiz)) {
            return false;
        }
        $returnFlag = true;
        $userId = (int)Auth::user()->uid;
        $quizId = (int)$quiz->quiz_id;
        if (isset($quiz->relations['feed_quiz_rel'])) {
            $channelRel = $quiz->relations['feed_quiz_rel'];
            try {
                $userChannelIds =  array_get($this->program_service->getAllProgramsAssignedToUser($userId), 'channel_ids', []);
            } catch (Exception $e) {
                Log::info('No channels are assigned to this user user_id: '.$userId);
                return false;
            }
            $channelIds = array_intersect(array_keys($channelRel), $userChannelIds);
            if (empty($channelIds)) {
                return false;
            }
            foreach ($channelRel as $channelId => $specificChannelRel) {
                $feedName = Program::pluckFeedNameByID($channelId);
                if (!in_array($channelId, $userChannelIds) || !is_array($specificChannelRel) || empty($specificChannelRel)) {
                    continue;
                }
                foreach ($specificChannelRel as $postId) {
                    $postDeatils = Packet::getPacketByID((int)$postId);
                    if (empty($postDeatils)) {
                        continue;
                    }
                    $packet = $postDeatils[0];
                    $array = [
                        'module' => 'element',
                        'action' => 'attempt_closed',
                        'module_name' => $quiz->quiz_name,
                        'module_id' => (int)$quiz->quiz_id,
                        'element_type' => 'assessment',
                        'packet_id' => (int)$packet['packet_id'],
                        'packet_name' => $packet['packet_title'],
                        'feed_id' => (int)$channelId,
                        'feed_name' => $feedName,
                        'url' => 'assessment/detail/' . $quiz->quiz_id,
                    ];
                    MyActivity::getInsertActivity($array);
                }
            }
        }
        return $returnFlag;
    }


    public function orderQuizzesGroupA($quizIds, $quizData)
    {
        $quizIds = array_filter($quizIds);
        $filteredQ = $quizData->filter(function ($item) use ($quizIds) {
            return in_array($item['quiz_id'], $quizIds);
        });
        $groupABuffer = $filteredQ->filter(function ($item) {
            return Timezone::getTimeStamp($item['end_time']) > time();
        });
        $groupA = $groupABuffer->sortBy('end_time');
        $groupBBuffer = $filteredQ->filter(function ($item) {
            return $item['end_time'] == 0;
        });
        $groupB = $groupBBuffer->sortBy('start_time');
        $groupCBuffer = $filteredQ->filter(function ($item) {
            return (Timezone::getTimeStamp($item['end_time']) < time() && $item['end_time'] != 0);
        });
        $groupC = $groupCBuffer->sortBy('end_time');
        $orderedQuizzes = collect();
        if (!is_null($groupA) && !empty($groupA)) {
            $orderedQuizzes = $orderedQuizzes->merge($groupA);
        }
        if (!is_null($groupB) && !empty($groupB)) {
            $orderedQuizzes = $orderedQuizzes->merge($groupB);
        }
        if (!is_null($groupC) && !empty($groupC)) {
            $orderedQuizzes = $orderedQuizzes->merge($groupC);
        }

        return $orderedQuizzes;
    }
    /**
     * Method to display quiz instructions
     * @param int $quiz_id
     * @param int $attempt_id
     */
    public function getInstructions($quiz_id)
    {
        $requestUrl = Input::get('requestUrl', '');
        $quiz = Quiz::where('quiz_id', (int)$quiz_id)->firstOrFail();
        $this->layout = view('portal.theme.' . $this->theme . '.layout.full_page_layout');
        $this->layout->pagetitle = trans('assessment.instructions'). ' | ' . ucwords($quiz->quiz_name);
        $this->layout->content = view($this->theme_path . '.assessment.instructions')
                                    ->with('quiz', $quiz);
    }
}
