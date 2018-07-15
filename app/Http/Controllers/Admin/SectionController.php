<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Quiz\QuizHelper;
use App\Http\Controllers\AdminBaseController;
use App\Model\Common;
use App\Model\Question;
use App\Model\QuestionBank;
use App\Model\Quiz;
use App\Model\QuizAttempt;
use App\Model\Section;
use Exception;
use Illuminate\Http\Request;
use Input;
use Log;
use URL;
use Validator;
use Auth;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\Program\ElementType;
use App\Enums\Assessment\AssessmentPermission;
use App\Enums\RolesAndPermissions\PermissionType;
use App\Enums\RolesAndPermissions\Contexts;

class SectionController extends AdminBaseController
{
    protected $layout = 'admin.theme.layout.master_layout';

    public function __construct(Request $request)
    {
        parent::__construct();

        $this->theme_path = 'admin.theme';
    }

    public function getIndex()
    {
    }

    public function getListSection($quiz_id = 0)
    {

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assessment.title_manage_quiz') => 'assessment/list-quiz',
            trans('admin/assessment.manage_section') => ''
        ];
        $attempted = false;
        if ($quiz_id != 0) {
            $quiz = Quiz::where('quiz_id', '=', (int)$quiz_id)->first();
            $sections = Section::where('quiz_id', (int)$quiz_id)->where('status', 'ACTIVE')->get();
            $timed_sections = $remaining_time = false;
            $duration = $quiz->duration;
            if ($quiz->duration > 0) {
                $remaining_time = $quiz->duration - $sections->sum('duration');
            }
            if (isset($quiz->is_timed_sections) && $quiz->is_timed_sections) {
                $timed_sections = true;
            }
            $attempt = QuizAttempt::where('quiz_id', '=', (int)$quiz_id)
                ->get();
            if (!$attempt->isEmpty()) {
                $attempted = true;
            }
            if ($quiz->is_sections_enabled == false) {
                return parent::getAdminError($this->theme_path);
            }
            if (!$quiz->quiz_name) {
                abort(404);
            } else {
                $name = (strlen($quiz->quiz_name) > 40) ? str_limit($quiz->quiz_name, 40) : $quiz->quiz_name;
            }
            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = trans('admin/assessment.add_section') . " '" . $name . "'";
            $this->layout->pageicon = 'fa fa-pencil-square-o';
            $this->layout->pagedescription = trans('admin/assessment.list_of_section');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'assessment')
                ->with('submenu', 'quiz');
            $this->layout->content = view('admin.theme.assessment.list_section')
                ->with('attempted', $attempted)
                ->with('duration', $duration)
                ->with('timed_sections', $timed_sections)
                ->with('remaining_time', $remaining_time)
                ->with('slug', $quiz_id);
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }

    public function getAjaxListSection($quiz_id = 0)
    {
        $start = 0;
        $limit = 10;
        $search = Input::get('search', '');
        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '0') {
                $orderByArray = ['title' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '1') {
                $orderByArray = ['no_of_questions' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '2') {
                $orderByArray = ['total_marks' => $order_by[0]['dir']];
            }
        }
        if (isset($search['value'])) {
            $searchKey = $search['value'];
        } else {
            $searchKey = '';
        }
        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = (int)Input::get('start', 0);
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = (int)Input::get('length', 10);
        }


        $totalRecords = Section::getSectionInQuizCount((int)$quiz_id);
        $filteredRecords = Section::getSectionInQuizCount((int)$quiz_id);
        $filterdata = Section::getSectionInQuizPagenation((int)$quiz_id, (int)$start, (int)$limit, $orderByArray, $searchKey);
        $data = [];
        $timed_sections = false;
        $quiz = Quiz::where('quiz_id', '=', (int)$quiz_id)->first(['is_timed_sections']);
        if (isset($quiz->is_timed_sections) && $quiz->is_timed_sections) {
            $timed_sections = true;
        }
        $attempt = QuizAttempt::where('quiz_id', '=', (int)$quiz_id)
            ->get();
        if (!$attempt->isEmpty()) {
            $attempted = true;
        } else {
            $attempted = false;
        }
        foreach ($filterdata as $each_sec) {
            if ($attempted) {
                $edit = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/assessment.blocked_for_Attempts_quiz') . '" href="#" ><i class="fa fa-edit"></i></a>';
                $delete = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/assessment.blocked_for_Attempts_quiz') . '" href="#" ><i class="fa fa-trash-o"></i></a>';
            } else {
                $edit = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/assessment.edit_section') . '" href="' . URL::to('/cp/section/add-section/' . $quiz_id . '/' . $each_sec['section_id']) . '" ><i class="fa fa-edit"></i></a>';
                $delete = '<a class="btn btn-circle show-tooltip deletemedia" title="' . trans('admin/manageweb.action_delete') . '" href="' . URL::to('/cp/section/delete/' . $quiz_id . '/' . $each_sec['section_id']) . '" ><i class="fa fa-trash-o"></i></a>';
            }

            $temp = [];
            $temp[] = "<div>" . $each_sec['title'] . "</div>";
            $temp[] = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/assessment.manage_question') . '" href="' . URL::to('/cp/section/add-questions/' . $quiz_id . '/' . $each_sec['section_id'] . '/list') . '" >' . $each_sec['no_of_questions'] . '</i></a>';
            if ($timed_sections && isset($each_sec['duration'])) {
                $temp[] = gmdate('H:i', (int)$each_sec['duration'] * 60);
            } elseif ($timed_sections && !isset($each_sec['duration'])) {
                $temp[] = 0;
            }
            $temp[] = $each_sec['total_marks'];
            $temp[] = $edit . $delete;
            $data[] = $temp;
        }

        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ];
        return response()->json($finaldata);
    }

    public function getAddSection($quiz_id = 0, $sec_id = 0)
    {
        $quiz_is_exist = Quiz::where('quiz_id', '=', (int)$quiz_id)->first();

        if ($quiz_is_exist->is_sections_enabled == false) {
            return parent::getAdminError($this->theme_path);
        }

        $attempt = QuizAttempt::where('quiz_id', '=', (int)$quiz_id)
            ->get();
        if (is_null($quiz_is_exist) || !$attempt->isEmpty()) {
            return parent::getAdminError($this->theme_path);
        }
        $quiz_type = "NORMAL";
        if (isset($quiz_is_exist->type) && $quiz_is_exist->type == "QUESTION_GENERATOR") {
            $quiz_type = $quiz_is_exist->type;
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assessment.manage_section') => 'section/list-section/' . $quiz_id,
            trans('admin/assessment.add_section') => ''
        ];
        $duration_flag = $timed_sections = false;
        $remaining_time = 0;
        $quiz = Quiz::where('quiz_id', (int)$quiz_id)->where('status', 'ACTIVE')->first();

        if (isset($quiz->is_timed_sections) && $quiz->is_timed_sections) {
            $timed_sections = true;
            if ($quiz->duration > 0) {
                $duration_flag = true;
                $sections_duration = Section::getSectionsTotalDuration($quiz_id);
                $remaining_time = (int)$quiz->duration - $sections_duration;
            }
        }
        if ($quiz_id != 0) {
            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = trans('admin/assessment.add_section');
            $this->layout->pageicon = 'fa fa-pencil-square-o';
            $this->layout->pagedescription = trans('admin/assessment.list_of_quizzes');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'assessment')
                ->with('submenu', 'quiz');
            if ($sec_id > 0) {
                $section = Section::getSectionOne((int)$sec_id)->toArray();
                if ($timed_sections && isset($section[0]['duration'])) {
                    $remaining_time = (int)$remaining_time + $section[0]['duration'];
                }
                $section = isset($section[0]) ? $section[0] : $section;
                $this->layout->content = view('admin.theme.assessment.add_section')
                    ->with('section', $section)
                    ->with('quiz_type', $quiz_type)
                    ->with('quiz', $quiz)
                    ->with('timed_sections', $timed_sections)
                    ->with('duration_flag', $duration_flag)
                    ->with('remaining_time', $remaining_time)
                    ->with('slug', $quiz_id);
            } else {
                $this->layout->content = view('admin.theme.assessment.add_section')
                    ->with('quiz_type', $quiz_type)
                    ->with('quiz', $quiz)
                    ->with('timed_sections', $timed_sections)
                    ->with('duration_flag', $duration_flag)
                    ->with('remaining_time', $remaining_time)
                    ->with('slug', $quiz_id);
            }
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }

    public function getCreateSection($quiz_id = 0)
    {
        $quiz_id = (int)$quiz_id;
        $quiz_is_exist = Quiz::where('quiz_id', '=', $quiz_id)->first();
        if ($quiz_is_exist->is_sections_enabled == false) {
            return parent::getAdminError($this->theme_path);
        }
        $attempt = QuizAttempt::where('quiz_id', '=', (int)$quiz_id)
            ->get();
        if (is_null($quiz_is_exist) || !$attempt->isEmpty()) {
            $finaldata = [
                'flag' => 'attempted',
            ];
            return response()->json($finaldata);
        }
        $title = trim(strip_tags(Input::get('title')));
        $is_title_exist = Section::getIsNameExist($quiz_id, $title);

        if ($quiz_id != 0 && !$is_title_exist) {
            $data = [
                'quiz_id' => $quiz_id,
                'title' => trim(strip_tags(Input::get('title'))),
                'description' => Input::get('description', ''),
                // 'status' => 'PENDING',
                'status' => 'ACTIVE',
                'keywords' => Input::get('keywords', ''),
                'no_of_questions' => Input::get('no_of_questions', 0),
                'total_marks' => Input::get('total_marks', 0),
                'questions' => [],
                'created_at' => time()
            ];
            $res = Section::sectionInsert($data);
            $sec_id = 0;
            if ($res) {
                $sec_id = Section::max('section_id');
            }

            if (is_null($sec_id) || $sec_id < 0) {
                $finaldata = [
                    'flag' => 'error',
                ];

                return response()->json($finaldata);
            } else {
                $finaldata = [
                    'sec_id' => $sec_id,
                    'flag' => 'success',
                ];
            }
        } else {
            $finaldata = [
                'flag' => 'duplicate',
            ];
        }
        return response()->json($finaldata);
    }

    public function postAddSection(Request $request)
    {
        $next_page = Input::get('next_page', 'list section');
        $sec_id = Input::get('sec_id_hold', 0);
        $quiz_id = Input::get('quiz_id', 0);
        $title = trim(strip_tags(Input::get('title')));
        $is_title_exist = Section::getIsNameExistExceptSec($quiz_id, $title, $sec_id);
        if ($is_title_exist) {
            return redirect('/cp/section/add-section/' . $quiz_id . '/' . $sec_id)
                ->withInput()
                ->with('error', 'Section title exist');
        }
        $duration = 0;
        $quiz = Quiz::where('quiz_id', (int)$quiz_id)->first();
        if ($request->has('duration')) {
            $minutes = explode(':', trim($request->duration));
            $duration = ($minutes[0] * 60) + $minutes[1];
            Validator::extend('duration', function ($attribute, $value) use ($duration) {
                if ($duration == 0) {
                    return false;
                }
                return true;
            });
        }
        Validator::extend('valid_percentage', function ($attribute, $value) use ($quiz) {
            if ($quiz->cut_off_format == 'percentage') {
                if (!empty($value) && $value > 100) {
                     return false;
                }
                return true;
            }
            return true;
        });
        $messages = [
            'duration' => trans('admin/assessment.duration_required'),
            'cut_off.valid_percentage' => trans('admin/assessment.cut_off_max'),
        ];
        $rules = [
            'title' => 'Required|max:512',
            'cut_off' => 'Regex:/^([0-9])+$/|numeric|min:1|valid_percentage',
            'duration' => 'sometimes|duration'
        ];
        $validation = Validator::make(Input::all(), $rules, $messages);
        if ($validation->fails()) {
            if ($sec_id > 0) {
                return redirect('/cp/section/add-section/' . $quiz_id . '/' . $sec_id)
                    ->withInput()
                    ->withErrors($validation);
            }
            return redirect('/cp/section/add-section/' . $quiz_id)
                ->withInput()
                ->withErrors($validation);
        }
        $data = [
            'title' => trim(strip_tags(Input::get('title'))),
            'description' => Input::get('description', ''),
            'keywords' => Input::get('keywords', ''),
            'duration' => $duration,
            'status' => 'ACTIVE',
            'no_of_questions' => Input::get('no_of_questions', 0),
            'total_marks' => Input::get('total_marks', 0),
            'update_at' => time(),
        ];
        if (Input::get('cut_off', '') != '') {
            $data['cut_off'] = (int)Input::get('cut_off');
            $data['cut_off_mark'] = QuizHelper::getCutOffMark($request->cut_off, $request->total_marks, $quiz->cut_off_format);
        }
        Section::sectionUpdate($data, $sec_id);
        switch ($next_page) {
            case 'add_ques_bank':
                return redirect('/cp/section/add-questions/' . $quiz_id . '/' . $sec_id . '/add');
                break;
            default:
                return redirect('/cp/section/list-section/' . $quiz_id);
                break;
        }
    }

    public function getEditSection($section_id = 0)
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assessment.manage_section') => '',
        ];

        if ($section_id != 0) {
            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = trans('admin/assessment.manage_section');
            $this->layout->pageicon = 'fa fa-pencil-square-o';
            $this->layout->pagedescription = trans('admin/assessment.list_of_quizzes');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'assessment')
                ->with('submenu', 'quiz');
            $this->layout->content = view('admin.theme.assessment.edit_section')
                ->with('slug', $section_id);
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }

    public function postEditSection()
    {
        echo "ON the way";
        die;
    }

    public function getDelete($qid = 0, $section_id = 0)
    {
        if (!is_numeric($section_id) || !is_numeric($qid)) {
            abort(404);
        }

        $attempt = QuizAttempt::where('quiz_id', '=', (int)$qid)
            ->get();
        if (!$attempt->isEmpty()) {
            return redirect('cp/section/list-section/' . $qid)
                ->with('error', 'Failed to delte that Section');
        }

        $quiz = Quiz::where('quiz_id', '=', (int)$qid)->firstOrFail();
        if ($quiz->is_sections_enabled == false) {
            return parent::getAdminError($this->theme_path);
        }
        $section = Section::where('section_id', '=', (int)$section_id)->firstOrFail();
        $question = [];
        $question = $section->questions;
        if (!empty($question)) {
            Question::removeQuizQuestions($quiz->quiz_id, $question);
        }
        $data = [
            'questions' => [],
            'status' => 'DELETE',
            'update_at' => time()
        ];


        $res = Section::sectionUpdate($data, $section->section_id);
        $questions_quiz = Question::where('quizzes', '=', (int)$quiz->quiz_id)->get(['question_id', 'default_mark']);
        $ques_ids = $questions_quiz->lists('question_id')->all();
        $total_marks = QuizHelper::roundOfNumber($questions_quiz->sum('default_mark'));
        Quiz::where('quiz_id', '=', $quiz->quiz_id)
            ->update([
                'questions' => $ques_ids,
                'concept_tagging' => 0, //added for concept mapping cron
                // 'page_layout' => $layout,
                'total_mark' => $total_marks,
            ]);

        if ($res) {
            return redirect('cp/section/list-section/' . $quiz->quiz_id)
                ->with('success', 'Successfully delete specified Section');
        } else {
            return redirect('cp/section/list-section/' . $quiz->quiz_id)
                ->with('error', 'Failed to delete that Section');
        }
    }

    public function getAddQuestions($qid = 0, $sec_id = 0, $from = 'list')
    {
        $permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ASSESSMENT,
            PermissionType::ADMIN,
            AssessmentPermission::EDIT_QUIZ,
            null,
            null,
            true
        );

        if (!is_element_accessible(
            get_permission_data($permission_data_with_flag),
            ElementType::ASSESSMENT,
            $qid
        )) {
            return parent::getAdminError();
        }

        if (!is_numeric($sec_id)) {
            abort(404);
        }

        // Checking whether given quiz is available in db
        $quiz = Quiz::where('quiz_id', '=', (int)$qid)->firstOrFail();
        if ($quiz->is_sections_enabled == false) {
            return parent::getAdminError($this->theme_path);
        }
        // $section = Section::getSectionOne((int)$sec_id);
        $section = Section::where('section_id', '=', (int)$sec_id)->get();
        $attempt = QuizAttempt::where('quiz_id', '=', (int)$qid)->count();

        // $limit_questions = isset($quiz->total_question_limit) ? (int)$quiz->total_question_limit : 0;

        if (isset($section[0]) && !empty($section[0])) {
            $section = $section[0];
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/assessment.manage_section') => 'section/list-section/' . $qid,
            trans('admin/assessment.section_questions') => ''
        ];

        $questions = Question::where('status', '=', 'ACTIVE')
            ->whereIn('question_id', array_map('intval', $section->questions))
            ->get(['question_id', 'question_name', 'question_text', 'difficulty_level', 'default_mark'])
            ->toArray();

        // Calcualte to print the questions order same as stored in db
        $qtemp = [];
        foreach ($questions as $value) {
            $qtemp[$value['question_id']] = $value;
        }
        $questions = $qtemp;

        $questionbank = '';
        $qbank_questions = '';
        // Question bank contents
        $select_qesids = [];
        if (Input::has('qbank') || empty($questions)) {
            $questionbank = QuestionBank::orderBy('created_at', 'desc')
                ->where('status', '=', 'ACTIVE')
                ->get(['question_bank_id', 'question_bank_name', 'questions', 'draft_questions']);
            $select_ques = Question::getQuizsQuestions((int)$qid);
            $select_qesids = $select_ques->lists('question_id')->all();
            if (is_numeric(Input::get('qbank')) && Input::get('qbank') != "0") {
                $selected_qbank = QuestionBank::where('question_bank_id', '=', (int)Input::get('qbank'))->first(['questions']);
                $qtype = Input::get('qtype');
                $qdifficult = Input::get('qdifficulty');
                $qlimit = Input::get('qlimit');
                $qrandam = Input::get('qrandam');
                $tags = Input::get('qtags');
                if (Input::has('randmize')) {
                    $randmization = Input::get('randmize');
                } else {
                    $randmization = null;
                }
                $qbank_questions = Question::filterQuestion($select_qesids, $selected_qbank, $tags, $qdifficult, $qtype, $qlimit, $randmization);
            }
        }
        $qb_error = "";
        if (Input::get('qbank') === "" || Input::get('qbank') === "select question bank") {
            $qb_error = "The question bank field is required.";
        }

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = 'Section : ' . $section->title;
        $this->layout->pageicon = 'fa fa-file-text';
        $this->layout->pagedescription = strip_tags($quiz->quiz_description);
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'assessment')
            ->with('submenu', 'quiz');
        $this->layout->content = view('admin.theme.assessment.manage_section_questions')
            ->with('quiz', $quiz)
            ->with('section', $section)
            ->with('attempt', $attempt)
            ->with('questions', $questions)
            ->with('qids', $section->questions)
            ->with('assigned_ques_num', count($section->questions))
            ->with('questionbank', $questionbank)
            ->with('from', $from)
            ->with('sec_id', $sec_id)
            ->with('select_qesids', $select_qesids)
            ->with('qbank_questions', $qbank_questions)
            // ->with('limit_questions', $limit_questions)
            ->with('qb_error', $qb_error);

        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function postAddQuestions($qid = 0, $sec_id = 0, $from = 'list')
    {
        $permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ASSESSMENT,
            PermissionType::ADMIN,
            AssessmentPermission::EDIT_QUIZ,
            null,
            null,
            true
        );

        if (!is_element_accessible(
            get_permission_data($permission_data_with_flag),
            ElementType::ASSESSMENT,
            $qid
        )) {
            return parent::getAdminError();
        }

        if (!is_numeric($qid)) {
            abort(404);
        }
        if (!is_numeric($sec_id)) {
            abort(404);
        }

        $quiz = Quiz::where('quiz_id', '=', (int)$qid)->firstOrFail();
        if ($quiz->is_sections_enabled == false) {
            return parent::getAdminError($this->theme_path);
        }
        $section = Section::where('section_id', '=', (int)$sec_id)->firstOrFail();

        $attempt = QuizAttempt::where('quiz_id', '=', (int)$qid)->count();

        if ($attempt > 0 && !$quiz->beta) {
            return redirect('cp/section/add-questions/' . $quiz->quiz_id . '/' . $section->section_id . '/' . $from);
        }

        $quiz_questions = $section->questions;
        $qb_questions = Input::get('qb_questions');
        $qbank_id = Input::get('_qb', 0);
        if (!empty($qb_questions)) {
            // Converting array values into integer
            $qb_questions = array_map('intval', $qb_questions);
            foreach ($qb_questions as $value) {
                // questions field
                if (!in_array($value, $quiz_questions)) {
                    array_push($quiz_questions, $value);
                }
            }

            // Total marks for this quiz
            $total = Quiz::getTotalMarks($quiz_questions);
            $data = [
                'total_marks' => $total,
                'questions' => $quiz_questions,
                'no_of_questions' => count($quiz_questions),
                'update_at' => time()
            ];
            if (isset($section->cut_off) && $section->cut_off) {
                $data['cut_off_mark'] = QuizHelper::getCutOffMark($section->cut_off, $total, $quiz->cut_off_format);
            }
            $up_res = Section::sectionUpdate($data, (int)$sec_id);

            if ($up_res) {
                // Updating the quiz_id in selected questions quizzes field
                if (is_array($qb_questions) && !empty($qb_questions)) {
                    Question::updateQuizQuestions($quiz->quiz_id, $qb_questions);
                    $questions_quiz = Question::where('quizzes', '=', (int)$quiz->quiz_id)->get(['question_id', 'default_mark']);
                    $ques_ids = $questions_quiz->lists('question_id')->all();
                    $quiz_total = QuizHelper::roundOfNumber($questions_quiz->sum('default_mark'));
                    $quiz_data = [
                        'questions' => array_filter($ques_ids),
                        'concept_tagging' => 0, //added for concept mapping cron
                        'total_mark' => $quiz_total,
                    ];
                    if (isset($quiz->cut_off) && $quiz->cut_off > 0) {
                        $quiz_data['cut_off_mark'] = QuizHelper::getCutOffMark($quiz->cut_off, $quiz_total, $quiz->cut_off_format);
                    }
                    Quiz::where('quiz_id', '=', (int)$quiz->quiz_id)
                        ->update($quiz_data);
                }
                //filter added
                $filter = "&randmize=" . Input::get('randmize')
                    . "&qlimit=" . Input::get('qlimit')
                    . "&qtags=" . Input::get('qtags')
                    . "&qdifficulty=" . Input::get('qdifficulty')
                    . "&qtype=" . Input::get('qtype');
                //dd($filter);
                return redirect('cp/section/add-questions/' . $quiz->quiz_id . '/' . $sec_id . '/' . $from . '?qbank=' . $qbank_id . $filter)
                    ->with('success', trans('admin/assessment.add_question_success'));
            } else {
                return redirect('cp/section/add-questions/' . $quiz->quiz_id . '/' . $sec_id . '/' . $from . '?qbank=' . $qbank_id)
                    ->with('error', trans('admin/assessment.problem_while_adding_question'));
            }
        } else {
            return redirect('cp/section/add-questions/' . $quiz->quiz_id . '/' . $sec_id . '/' . $from . '?qbank=' . $qbank_id)
                ->with('error', trans('admin/assessment.no_ques_selected'));
        }
    }

    /**
     * Adding questions to section from sorted list
     *
     * @param  int $quiz_id
     * @param  int $section_id
     * @param  object $request
     * @return  \Illuminate\Http\Response
     */
    public function postAddQuestionAjax($quiz_id, $section_id, Request $request)
    {
        try {
            //TODO roles and permission
            $ids = json_decode($request->input('ids', []), true);
            if (!empty($ids)) {
                $questions = [];
                $questions = QuizHelper::questionList($ids);
                $save = Section::where('section_id', (int)$section_id)
                    ->where('quiz_id', (int)$quiz_id)
                    ->update(['questions' => $questions]);
                if ($save) {
                    return response()->json(['status' => true]);
                } else {
                    return response()->json(['status' => false]);
                }
            }
        } catch (Exception $e) {
            Log::error('Error ' . $e->getMessage() . ' at line ' . $e->getLine() . ' in file ' . $e->getFile());
        }
        return response()->json(['status' => false]);
    }

    public function getRemoveSectionQuestion($quiz_id = 0, $sec_id = 0, $from = 'list')
    {
        $question = (int)Input::get('question', 0);
        $qbank = (int)Input::get('qbank', 0);

        $permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ASSESSMENT,
            PermissionType::ADMIN,
            AssessmentPermission::EDIT_QUIZ,
            null,
            null,
            true
        );

        if (!is_element_accessible(
            get_permission_data($permission_data_with_flag),
            ElementType::ASSESSMENT,
            $quiz_id
        )) {
            return parent::getAdminError();
        }

        if (!is_numeric($quiz_id) && !is_numeric($question)) {
            abort(404);
        }

        $quiz = Quiz::where('quiz_id', '=', (int)$quiz_id)->firstOrFail();
        if ($quiz->is_sections_enabled == false) {
            return parent::getAdminError($this->theme_path);
        }
        $attempt = QuizAttempt::where('quiz_id', '=', (int)$quiz_id)->count();
        $section = Section::where('section_id', '=', (int)$sec_id)->firstOrFail();

        if ($attempt > 0 && !$quiz->beta) {
            return redirect('cp/section/add-questions/' . $quiz->quiz_id . '/' . $section->section_id . '/' . $from);
        }

        $quiz_questions = $quiz->questions;
        $sec_questions = $section->questions;

        if (!empty($question) && in_array($question, $sec_questions)) {
            if (($key = array_search($question, $sec_questions)) !== false) {
                unset($sec_questions[$key]);
            }
            $sec_questions = array_values($sec_questions);
            if (($key = array_search($question, $quiz_questions)) !== false) {
                unset($quiz_questions[$key]);
            }
            $quiz_questions = array_values($quiz_questions);
        }
        $filter = "&randmize=" . Input::get('randmize')
            . "&qlimit=" . Input::get('qlimit')
            . "&qtags=" . Input::get('qtags')
            . "&qdifficulty=" . Input::get('qdifficulty')
            . "&qtype=" . Input::get('qtype');
        if (Input::has('qbank') && is_numeric(Input::get('qbank'))) {
            $return = 'cp/section/add-questions/' . $quiz->quiz_id . '/' . $section->section_id . '/' . $from . '?qbank=' . $qbank . $filter;
        } else {
            $return = 'cp/section/add-questions/' . $quiz->quiz_id . '/' . $section->section_id . '/' . $from;
        }

        // Total marks for this section
        $section_questions = Question::whereIn('question_id', $sec_questions)->get();
        $questions = Question::whereIn('question_id', $quiz_questions)->get();
        $section_total = QuizHelper::roundOfNumber($section_questions->sum('default_mark'));
        $section_data = [
            'questions' => $sec_questions,
            'no_of_questions' => count($sec_questions),
            'total_marks' => $section_total,
            'update_at' => time()
        ];
        if (isset($section->cut_off) && $section->cut_off) {
            $section_data['cut_off_mark'] = QuizHelper::getCutOffMark($section->cut_off, $section_total, $quiz->cut_off_format);
        }
        $quiz_total = QuizHelper::roundOfNumber($questions->sum('default_mark'));
        $quiz_data = [
            'questions' => $quiz_questions,
            'concept_tagging' => 0, //added for concept mapping cron
            'total_mark' => $quiz_total,
        ];
        if (isset($quiz->cut_off) && $quiz->cut_off > 0) {
            $quiz_data['cut_off_mark'] = QuizHelper::getCutOffMark($quiz->cut_off, $quiz_total, $quiz->cut_off_format);
        }
        if (Section::where('section_id', '=', $section->section_id)
                ->update($section_data) &&
            Quiz::where('quiz_id', '=', (int)$quiz->quiz_id)
                ->update($quiz_data)
        ) {
            // Updating the quiz_id in selected questions quizzes field
            if (!empty($question)) {
                Question::removeQuizQuestions($quiz->quiz_id, [$question]);
            }
            return redirect($return)
                ->with('success', trans('admin/assessment.question_removed_success'));
        } else {
            return redirect($return)
                ->with('error', trans('admin/assessment.problem_while_removing_ques'));
        }
    }
    
    public function postAjaxBulkDeleteQuizQuestions($quiz_id, $sec_id)
    {
        $edit_quiz_permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ASSESSMENT,
            PermissionType::ADMIN,
            AssessmentPermission::EDIT_QUIZ,
            null,
            null,
            true
        );
        $edit_quiz_permission_data = get_permission_data($edit_quiz_permission_data_with_flag);
        if (!is_element_accessible($edit_quiz_permission_data, ElementType::ASSESSMENT, $quiz_id)) {
            return parent::getAdminError();
        }
        
        $selected_ids = array_filter(array_map("intval", explode(',', Input::get("delete-ids"))));
        $quiz = Quiz::where('quiz_id', '=', (int)$quiz_id)->firstOrFail();
        
        if ($quiz->is_sections_enabled) {
            $section = Section::where('section_id', '=', (int)$sec_id)->firstOrFail();
        }
        
        $quiz_questions = $quiz->questions;
        $sec_questions = $section->questions;
        
        $sec_question_ids = array_diff($sec_questions, $selected_ids);
        $sec_delete_ids = array_intersect($selected_ids, $sec_questions);
        
        $quiz_question_ids = array_diff($quiz_questions, $selected_ids);
        $quiz_delete_ids = array_intersect($selected_ids, $quiz_questions);

        /* Total marks for this quiz */
        $sec_total = Quiz::getTotalMarks($sec_question_ids);
        $section_data = [
            'questions' => !empty($sec_question_ids) ? $sec_question_ids : [],
            'no_of_questions' => count($sec_question_ids),
            'total_marks' => $sec_total,
            'update_at' => time()
        ];
        if (isset($section->cut_off) && $section->cut_off) {
            $section_data['cut_off_mark'] = QuizHelper::getCutOffMark($section->cut_off, $sec_total, $quiz->cut_off_format);
        }
        
        $quiz_total = Quiz::getTotalMarks($quiz_question_ids);
        $quiz_data = [
            'questions' => !empty($quiz_question_ids) ? $quiz_question_ids : [],
            'concept_tagging' => 0, //added for concept mapping cron
            'total_mark' => $quiz_total,
        ];
        if (isset($quiz->cut_off) && $quiz->cut_off >= 1) {
            $quiz_data['cut_off_mark'] = QuizHelper::getCutOffMark($quiz->cut_off, $quiz_total, $quiz->cut_off_format);
        }
        
        /* Updating all the tables section, quiz and questions */
        if (Section::where('section_id', '=', $section->section_id)->update($section_data)) {
            Quiz::where('quiz_id', '=', (int)$quiz->quiz_id)->update($quiz_data);
            
            if (!empty($quiz_delete_ids)) {
                 /* Updating the quiz_id in selected questions quizzes field */
                Question::removeQuizQuestions($quiz->quiz_id, $quiz_delete_ids);
            }
        }
        
        return response()
               ->json([
                    'status' => 'success',
                    'message' => trans("admin/assessment.questions_removed_success")
               ]);
    }
}
