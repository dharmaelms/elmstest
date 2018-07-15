<?php
namespace App\Http\Controllers\Admin;

use App\Enums\RolesAndPermissions\Contexts;
use App\Http\Controllers\AdminBaseController;
use App\Model\Common;
use App\Model\FlashCard;
use App\Model\Question;
use App\Model\QuestionBank;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\FlashCard\FlashCardPermission;
use App\Enums\Program\ChannelPermission;
use App\Enums\Program\ElementType;
use App\Enums\RolesAndPermissions\PermissionType;
use App\Services\Program\IProgramService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ApplicationException;
use App\Enums\Course\CoursePermission;
use Auth;
use Input;
use Request;
use Response;
use Validator;
use View;

/**
 * Flash Cards Controller to create, update, view, manage and delete
 * @author sathishkumar@linkstreet.in
 */
class FlashCardsController extends AdminBaseController
{

    protected $layout = 'admin.theme.layout.master_layout';
    /**
     * @var IProgramService
     */
    private $programService;

    /**
     * FlashCardsController constructor.
     * @param Request $request
     * @param IProgramService $programService
     */
    public function __construct(Request $request, IProgramService $programService)
    {
        parent::__construct();
        $input = $request::input();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request::merge($input);
        $this->theme_path = 'admin.theme';
        $this->programService = $programService;
    }

    public function getIndex()
    {
        $this->getList();
    }

    /**
     * Add flash card
     * @return  Response
     */
    public function getAdd()
    {
        if (!has_admin_permission(ModuleEnum::FLASHCARD, FlashcardPermission::ADD_FLASHCARD)) {
            return parent::getAdminError($this->theme_path);
        }
        $crumbs = [trans('admin/dashboard.dashboard') => 'cp', trans('admin/flashcards.manage_flashcards') => 'flashcards', trans('admin/flashcards.add_flashcards') => '',];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pageicon = 'fa fa-flag';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')->with('mainmenu', 'flashcard');
        $this->layout->pagetitle = trans('admin/flashcards.add_flashcards');
        $this->layout->pagedescription = trans('admin/flashcards.add_new_flashcards');
        $this->layout->content = view('admin.theme.flashcards.add');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * Validate and insert/update flash card.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function postAdd()
    {
        if (!has_admin_permission(ModuleEnum::FLASHCARD, FlashcardPermission::ADD_FLASHCARD)) {
            return parent::getAdminError($this->theme_path);
        }
        $validation = $this->validateCard();

        if ($validation->fails()) {
            return Response::json(['fail' => true, 'errors' => $validation->getMessageBag()->toArray()]);
        } else {
            $data = ['title' => Input::get('name'), 'description' => Input::get('description'), 'cards' => $this->createCards(Input::get('slides')), 'created_by' => Auth::user()->username, 'created_at' => time(), 'updated_by' => '', 'updated_at' => time(), 'status' => Input::get('status')];
            $cardsData = FlashCard::createCards($data);
            if ($response = FlashCard::add($cardsData, 'slug')) {
                return Response::json(['success' => true, 'message' => 'Flash card set created successfully', 'url' => '/cp/flashcards/list#created']);
            }
        }
    }

    /**
     * function to edit flash cards
     * @param $slug
     * @return  response
     */
    public function getEdit($slug = false)
    {
        if (!$slug) {
            abort(404);
        }
        if (Input::get('post_slug') != '') {
            $flashcards = FlashCard::findByOne('card_id', (int)$slug)->first();
        } else {
            $flashcards = FlashCard::findByOne('slug', $slug)->first();
        }
        $edit_with_flag = $this->roleService->hasPermission(
            $this->request->user()->uid,
            ModuleEnum::FLASHCARD,
            PermissionType::ADMIN,
            FlashcardPermission::EDIT_FLASHCARD,
            null,
            null,
            true
        );
        $edit_permission = get_permission_data($edit_with_flag);
        if (get_permission_flag($edit_with_flag) && !is_element_accessible($edit_permission, ElementType::FLASHCARD, $flashcards->card_id)) {
            return parent::getAdminError($this->theme_path);
        }
        if (isset($flashcards)) {
            $crumbs = [trans('admin/dashboard.dashboard') => 'cp', trans('admin/flashcards.manage_flashcards') => 'flashcards', trans('admin/flashcards.edit_flashcards') => '',];
            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pageicon = 'fa fa-flag';
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')->with('mainmenu', 'flashcard');
            $this->layout->pagetitle = trans('admin/flashcards.edit_flashcards');
            $this->layout->pagedescription = trans('admin/flashcards.edit_flashcards');
            $this->layout->content = view('admin.theme.flashcards.edit', ['flashcards' => $flashcards]);
            $this->layout->footer = view('admin.theme.common.footer');
        } else {
            abort(404);
        }
    }

    /**
     * Validate flash card.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function postEdit($slug)
    {
        if (Input::get('post_slug') != '') {
            $flashcards = FlashCard::findByOne('card_id', (int)$slug)->first();
        } else {
            $flashcards = FlashCard::findByOne('slug', $slug)->first();
        }

        $edit_with_flag = $this->roleService->hasPermission(
            $this->request->user()->uid,
            ModuleEnum::FLASHCARD,
            PermissionType::ADMIN,
            FlashcardPermission::EDIT_FLASHCARD,
            null,
            null,
            true
        );
        $edit_permission = get_permission_data($edit_with_flag);
        if (get_permission_flag($edit_with_flag) && !is_element_accessible($edit_permission, ElementType::FLASHCARD, $flashcards->card_id)) {
            return parent::getAdminError($this->theme_path);
        }
        $validation = $this->validateCard();
        if ($validation->fails()) {
            return Response::json(['fail' => true, 'errors' => $validation->getMessageBag()->toArray()]);
        } else {
            $url = '/cp/flashcards/list#updated';
            if (Input::get('post_slug') != '') {
                $slug = $flashcards->slug;
                $url = '/cp/contentfeedmanagement/elements/' . Input::get('post_slug');
            }
            $data = ['title' => Input::get('name'), 'description' => Input::get('description'), 'cards' => $this->createCards(Input::get('slides')), 'updated_by' => Auth::user()->username, 'updated_at' => time(), 'status' => Input::get('status')];
            if (FlashCard::updateCards($slug, $data)) {
                return Response::json(['success' => true, 'message' => 'Flash card set created successfully', 'url' => $url]);
            }
        }
    }

    /**
     * function to view flash cards
     * @param  $slug string
     * @return view
     */
    public function getView($slug)
    {
        $flashcards = FlashCard::findByOne('slug', $slug)->first();
        if (isset($flashcards)) {
            $view_with_flag = $this->roleService->hasPermission(
                $this->request->user()->uid,
                ModuleEnum::FLASHCARD,
                PermissionType::ADMIN,
                FlashcardPermission::VIEW_FLASHCARD,
                null,
                null,
                true
            );
            $view_permission = get_permission_data($view_with_flag);
            if (get_permission_flag($view_with_flag) && !is_element_accessible($view_permission, ElementType::FLASHCARD, $flashcards->card_id)) {
                return parent::getAdminError($this->theme_path);
            }
            $crumbs = [trans('admin/dashboard.dashboard') => 'cp', trans('admin/flashcards.manage_flashcards') => 'flashcards', trans('admin/flashcards.view_flashcards') => '',];
            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pageicon = 'fa fa-flag';
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')->with('mainmenu', 'flashcard');
            $this->layout->pagetitle = trans('admin/flashcards.view_flashcards');
            $this->layout->pagedescription = trans('admin/flashcards.view_flashcards');
            $this->layout->content = view('admin.theme.flashcards.view', ['flashcards' => $flashcards]);
            $this->layout->footer = view('admin.theme.common.footer');
        } else {
            abort(404);
        }
    }

    /**
     * delete flashcard
     * @param  id flashcard id
     * @return \Illuminate\Http\JsonResponse
     */
    public function postDelete()
    {
        if (Input::get('slug') != '') {
            $slug = Input::get('slug');
            if ($flashcard = FlashCard::findByOne('slug', $slug)->first()) {
                $view_with_flag = $this->roleService->hasPermission(
                    $this->request->user()->uid,
                    ModuleEnum::FLASHCARD,
                    PermissionType::ADMIN,
                    FlashcardPermission::DELETE_FLASHCARD,
                    null,
                    null,
                    true
                );
                $view_permission = get_permission_data($view_with_flag);
                if (get_permission_flag($view_with_flag) && !is_element_accessible($view_permission, ElementType::FLASHCARD, $flashcard->card_id)) {
                    return Response::json([
                        'status' => 'failure',
                        'message' => '501 permission denied'
                    ]);
                }
                $data = ['status' => 'INACTIVE'];
                if (FlashCard::updateCards($slug, $data)) {
                    return Response::json([
                        'status' => 'success',
                        'message' => $flashcard->title . ' deleted successfully'
                    ]);
                }
            }
        }
        return Response::json([
            'status' => 'failure',
            'message' => $flashcard->title . ' delete action failed'
        ]);
    }

    /**
     * Manage page for flashcards
     */
    public function getList()
    {
        if (!has_admin_permission(ModuleEnum::FLASHCARD, FlashcardPermission::LIST_FLASHCARD)) {
            return parent::getAdminError($this->theme_path);
        }
        $crumbs = [trans('admin/dashboard.dashboard') => 'cp', trans('admin/flashcards.manage_flashcards') => '',];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pageicon = 'fa fa-flag';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')->with('mainmenu', 'flashcard');
        $this->layout->pagetitle = trans('admin/flashcards.manage_flashcards');
        $this->layout->pagedescription = trans('admin/flashcards.manage_flashcards');
        $this->layout->content = view('admin.theme.flashcards.list');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getListIframe()
    {
        $this->layout->pagetitle = '';
        $this->layout->pageicon = '';
        $this->layout->pagedescription = '';
        $this->layout->header = '';
        $this->layout->sidebar = '';
        $this->layout->footer = '';
        $this->layout->content = view('admin.theme.flashcards.list-iframe');
    }

    public function getListAjax()
    {
        $count = 10;
        $page = 1;
        $flashcards = collect([]);

        $list_with_flag = $this->roleService->hasPermission(
            $this->request->user()->uid,
            ModuleEnum::FLASHCARD,
            PermissionType::ADMIN,
            FlashcardPermission::LIST_FLASHCARD,
            null,
            null,
            true
        );

        if (get_permission_flag($list_with_flag)) {
            $where = 'ACTIVE';
            $like = false;
            $orderBy = 'created_at';
            $order = 'desc';
            if (Input::get('itemsPerPage') != '') {
                $count = Input::get('itemsPerPage');
            }
            if (Input::get('column') != '') {
                $orderBy = Input::get('column');
            }
            if (Input::get('order') != '') {
                $order = Input::get('order');
            }
            if (Input::get('page') != '') {
                $page = Input::get('page');
            }
            if (Input::get('status') != '') {
                $where = Input::get('status');
            }
            if (Input::get('search') != '') {
                $like = Input::get('search');
            }

            $list_permission = get_permission_data($list_with_flag);
            $filter_params = has_system_level_access($list_permission)? [] :
                ["in_ids" => get_user_accessible_elements($list_permission, ElementType::FLASHCARD)];
            $flashcards = FlashCard::search($where, $like, $orderBy, $order, $filter_params)
                ->paginate((int)$count, ['card_id', 'title', 'slug', 'cards', 'created_at', 'created_by', 'status']);

        }

        $this->layout->pageicon = 'fa fa-flag';
        $this->layout->pagetitle = trans('admin/flashcards.view_flashcards');
        $this->layout->pagedescription = trans('admin/flashcards.view_flashcards');
        return view(
            'admin.theme.flashcards.list-ajax',
            ['flashcards' => $flashcards, 'count' => $count, 'page' => $page]
        );
    }

    public function getListAjaxIframe()
    {
        $count = 10;
        $page = 1;
        $flashcards = new LengthAwarePaginator(collect([]), 0, 10);

        $program_type = $this->request->get("program_type", null);
        $program_slug = $this->request->get("program_slug", null);
        $post_slug = $this->request->get("post_slug", null);
        try {
            $program = $this->programService->getProgramBySlug($program_type, $program_slug);
            $this->programService->getProgramPostBySlug(
                $program_type,
                $program_slug,
                $post_slug
            );

            $permission_data_with_flag = $this->roleService->hasPermission(
                $this->request->user()->uid,
                ModuleEnum::CHANNEL,
                PermissionType::ADMIN,
                ChannelPermission::MANAGE_CHANNEL_POST,
                Contexts::PROGRAM,
                $program->program_id,
                true
            );

            $course_post_permission = has_admin_permission(
                ModuleEnum::COURSE,
                CoursePermission::MANAGE_COURSE_POST
            );

            $where = 'ACTIVE';
            $like = false;
            $orderBy = 'created_at';
            $order = 'desc';
            if (Input::get('itemsPerPage') != '') {
                $count = Input::get('itemsPerPage');
            }
            if (Input::get('page') != '') {
                $page = Input::get('page');
            }
            if (Input::get('search') != '') {
                $value = Input::get('search');
                $like = $value;
            }
            
            if ($course_post_permission && $program_type == "course") {
                $filter_params = [];
            } elseif (get_permission_flag($permission_data_with_flag)) {
                $list_permission = get_permission_data($permission_data_with_flag);
                $filter_params = has_system_level_access($list_permission)? [] :
                    ["in_ids" => get_user_accessible_elements($list_permission, ElementType::FLASHCARD)];
            }

            $flashcards = FlashCard::search($where, $like, $orderBy, $order, $filter_params)
                    ->paginate(
                        (int)$count,
                        ['card_id', 'title', 'slug', 'cards', 'created_at', 'created_by', 'status']
                    );

        } catch (ApplicationException $e) {
            Log::error($e->getTraceAsString());
        }


        $this->layout->pageicon = 'fa fa-flag';
        $this->layout->pagetitle = trans('admin/flashcards.view_flashcards');
        $this->layout->pagedescription = trans('admin/flashcards.view_flashcards');
        return view(
            'admin.theme.flashcards.list-ajax-iframe',
            ['flashcards' => $flashcards, 'count' => $count, 'page' => $page]
        );
    }

    /**
     * function used to render new cards
     * @return \View
     */
    public function getNewCard()
    {
        $count = Input::get('count');
        if (!is_numeric($count)) {
            abort(404);
        }
        return view('admin.theme.flashcards.card', ['count' => $count]);
    }

    /**
     * function used to display question bank and questions
     * @return \View
     */
    public function getQuestions()
    {
        $this->layout->pageicon = 'fa fa-flag';
        $this->layout->pagetitle = trans('admin/flashcards.view_flashcards');
        $this->layout->pagedescription = trans('admin/flashcards.view_flashcards');
        return view('admin.theme.flashcards.questionbanks', ['questionbanks' => FlashCard::getQuestionbanks()]);
    }

    /**
     * function used to get questions list by question bank id
     * @return \View
     */
    public function getQuestionsList($qid)
    {
        // $qbank = Input::get('questionBankId');
        if (!is_numeric($qid)) {
            abort(404);
        }
        $this->layout->pageicon = '';
        $this->layout->pagetitle = '';
        $questionIds = QuestionBank::where('question_bank_id', '=', (int)$qid)->first();
        $conditions = '';
        $count = 10;
        if (Input::get('itemsPerPage') != '') {
            $count = Input::get('itemsPerPage');
        }
        $page = Input::get('page') ? Input::get('page') : 1;
        $columns = ['question_id', 'question_text', 'question_type'];
        if (Input::get('questionType') != '') {
            $questions = Question::orderBy('question_id', 'desc')
                ->where('status', '=', 'ACTIVE')
                ->where('question_type', '=', Input::get('questionType'))
                ->whereIn('question_id', $questionIds->questions)
                ->paginate((int)$count, $columns);
        } else {
            $questions = Question::orderBy('question_id', 'desc')
                ->where('status', '=', 'ACTIVE')
                ->whereIn('question_id', $questionIds->questions)
                ->paginate((int)$count, $columns);
        }
        return view('admin.theme.flashcards.questions-list', ['questions' => $questions]);
    }

    public function postQuestionCreateCards()
    {
        if (Input::get('questions') == '') {
            abort(404);
        } else {
            $count = Input::get('count');
            foreach (Input::get('questions') as $key => $value) {
                $ids[] = (int)$value;
            }
            $questions = Question::orderBy('question_id', 'desc')
                ->where('status', '=', 'ACTIVE')
                ->whereIn('question_id', $ids)
                ->get();
            $this->layout->pageicon = 'fa fa-flag';
            $this->layout->pagetitle = trans('admin/flashcards.view_flashcards');
            $this->layout->pagedescription = trans('admin/flashcards.view_flashcards');
            return view('admin.theme.flashcards.questions-cards', ['questions' => $questions, 'count' => $count]);
        }
    }

    /**
     * Action used to import CSV page for Flashcards set I
     */
    public function getImport()
    {
        if (!has_admin_permission(ModuleEnum::FLASHCARD, FlashCardPermission::IMPORT_FLASHCARD)) {
            return parent::getAdminError($this->theme_path);
        }
        $crumbs = [trans('admin/dashboard.dashboard') => 'cp', trans('admin/flashcards.manage_flashcards') => 'flashcards', trans('admin/flashcards.import_flashcards') => '',];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pageicon = 'fa fa-flag';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')->with('mainmenu', 'flashcard');
        $this->layout->pagetitle = trans('admin/flashcards.import_flashcards');
        $this->layout->pagedescription = trans('admin/flashcards.import_flashcards');
        $this->layout->content = view('admin.theme.flashcards.import');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * Action used to validate CSV file and create/update Flashcards set I
     */
    public function postImport()
    {
        if (!has_admin_permission(ModuleEnum::FLASHCARD, FlashCardPermission::IMPORT_FLASHCARD)) {
            return parent::getAdminError($this->theme_path);
        }
        ini_set('max_execution_time', 300);
        $file = Input::file('file');
        $count = 0;
        $successCount = 0;
        $executed = [];
        $error = [];
        $messages = [];
        $title = [];
        $description = [];
        $cards = [];
        $slides = [];
        if (Input::hasFile('csvfile')) {
            $extension = strtolower($file->getClientOriginalExtension());
        } else {
            $extension = '';
        }
        $fileValidation = Validator::make(
            [
                'file' => $file,
                'extension' => $extension,
            ],
            [
                'file' => 'required',
                'extension' => 'in:csv',
            ],
            $messages
        );
        if ($fileValidation->fails()) {
            return Response::json(['failure' => true, 'message' => $fileValidation->getMessageBag()->toArray()]);
        } else {
            if (($fileopen = fopen($file, "r")) !== false) {
                while (($row = fgetcsv($fileopen, 0, ',', '"')) !== false) {
                    $count++;
                    if ($count == 1) {
                        continue;
                    }
                    if (!empty($row[0])) {
                        $title[$count] = $row[0];
                    }
                    if (!empty($row[1])) {
                        $description[$count] = $row[1];
                    }
                    if (!empty($row[2])) {
                        $cards[$count]['front'] = $row[2];
                    }
                    if (!empty($row[3])) {
                        $cards[$count]['back'] = $row[3];
                    }
                }
            }
            $rules = [
                'title' => 'required|min:2|max:512',
                'cards' => 'required',
            ];
            $messages = [
                'cards.required' => 'Front and back is required'
            ];
            $data = [
                'title' => $title[2],
                'cards' => $cards[2],
            ];

            $validateCsv = Validator::make($data, $rules, $messages);
            if ($validateCsv->fails()) {
                return Response::json(['failure' => true, 'message' => $validateCsv->getMessageBag()->toArray()]);
            } else {
                $desc = reset($description);
                $i = 0;
                $numItems = count($cards);
                $iterationCount = 0;
                foreach ($cards as $key => $card) {
                    $i++;
                    if (isset($description[$key])) {
                        $desc = $description[$key];
                    }
                    if (isset($title[$key])) {
                        if (isset($old_name)) {
                            $name = $title[$key];
                            $data = [
                                'title' => $old_name,
                                'description' => $desc,
                                'cards' => $this->createCards($slides),
                                'created_by' => Auth::user()->username,
                                'created_at' => time(),
                                'updated_by' => '',
                                'updated_at' => time(),
                                'status' => 'ACTIVE'
                            ];
                            if ($result = FlashCard::findByOne('title', $data['title'])->first()) {
                                $data['cards'] = $this->createCards((array_merge($result->cards, $slides)));
                                if (FlashCard::updateCards($result->slug, $data)) {
                                    $successCount++;
                                }
                            } else {
                                $cardsData = FlashCard::createCards($data);
                                if ($response = FlashCard::add($cardsData, 'slug')) {
                                    $successCount++;
                                }
                            }
                            $slides = [];
                        }
                        $old_name = $title[$key];
                    }
                    if (++$iterationCount == $numItems) {
                        $slides[$i] = $card;
                        $data = [
                            'title' => $old_name,
                            'description' => $desc,
                            'cards' => $this->createCards($slides),
                            'created_by' => Auth::user()->username,
                            'created_at' => time(),
                            'updated_by' => '',
                            'updated_at' => time(),
                            'status' => 'ACTIVE'
                        ];
                        if ($result = FlashCard::findByOne('title', $data['title'])->first()) {
                            $data['cards'] = $this->createCards((array_merge($result->cards, $slides)));
                            if (FlashCard::updateCards($result->slug, $data)) {
                                $successCount++;
                            }
                        } else {
                            $cardsData = FlashCard::createCards($data);
                            if ($response = FlashCard::add($cardsData, 'slug')) {
                                $successCount++;
                            }
                        }
                    }
                    $slides[$i] = $card;
                }
                // return Response::json(array('success' => true, 'successCount' => $successCount));
            }
        }
        die('done');
    }

    /**
     * action used to display question banks
     * @param  Response
     * @return View
     */
    public function postQuestionBanks()
    {
        return view('admin.theme.flashcards.questionbanks', ['questionbanks' => FlashCard::getQuestionbanks()]);
    }

    /**
     * function to create array from slides
     * @param  $cards array of cards
     * @return  array
     */
    public function createCards($cards)
    {
        ksort($cards);
        return array_filter(array_merge([0], array_values($cards)));
    }

    /**
     * this function used to validate data
     */
    public function validateCard()
    {
        $input = Input::all();
        $rules = ['name' => 'unique:flashcards|required|min:3|max:512'];
        foreach (Input::get('slides') as $row => $slide) {
            foreach ($slide as $key => $card) {
                $rules["slides.$row.front"] = 'required';
                $rules["slides.$row.back"] = 'required';
            }
        }

        return Validator::make($input, $rules);
    }
}
