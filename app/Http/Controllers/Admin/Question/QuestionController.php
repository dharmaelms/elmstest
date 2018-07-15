<?php

namespace App\Http\Controllers\Admin\Question;

use App\Http\Controllers\AdminBaseController;
use App\Http\Validators\Question\DescriptiveAttributeRules;
use App\Model\Dam;
use App\Model\QuizAttemptData;
use App\Services\Question\IQuestionService;
use App\Traits\AkamaiTokenTrait;
use Auth;
use Breadcrumbs;
use Exception;
use Illuminate\Http\Request;
use Redirect;
use URL;


class QuestionController extends AdminBaseController
{
    use AkamaiTokenTrait;

    private $pageDetails;

    private $question;

    public function __construct(IQuestionService $question)
    {
        $this->question = $question;

        $this->pageDetails = trans("admin/page_details.admin.question");
    }

    public function getAddQuestion()
    {
        $breadcrumbs = Breadcrumbs::render("admin-add-question");
        $data = [
            "pagetitle" => $this->pageDetails["add-question"]["title"],
            "pagedescription" => $this->pageDetails["add-question"]["description"],
            "pageicon" => $this->pageDetails["add-question"]["icon"],
            "breadcrumbs" => $breadcrumbs,
            "mainmenu" => "assessment",
            "submenu" => "add-question",
            "questionbank" => $this->question->getActiveQuestionBanks()
        ];

        return view("admin.theme.assessment.question.descriptive.add", $data);
    }

    public function postAddQuestion(Request $request)
    {
        $validator = DescriptiveAttributeRules::getValidator("add_question", $request->all());
        if (!$validator->fails()) {
            $data = [
                "question_type" => $request->input("_qtype"),
                "question_text" => $request->input("question_text"),
                "question_text_media" => (!is_null($request->input("question_dam_media_question_text"))) ? $request->input("question_dam_media_question_text") : [],
                "difficulty_level" => strtoupper($request->input("difficulty_level", "easy")),
                "default_mark" => (int)$request->input("default_mark"),
                "keywords" => ($request->has("keywords")) ? array_map("trim", explode(',', strip_tags($request->input("keywords")))) : [],
                "status" => $request->input("status"),
                "created_by" => Auth::user()->username
            ];

            $questionBank = (int)$request->input("question_bank");
            $question = $this->question->addQuestion($questionBank, $data);
            if ($request->has("return")) {
                $return = urldecode($request->input("return"));
            } else {
                $return = "cp/assessment/list-questionbank";
            }

            if ($question->status === "DRAFT") {
                return redirect($return)
                    ->with("success", trans("admin/assessment.question_saved_success"));
            } else {
                return redirect("cp/assessment/success-question/{$question->question_id}?qb={$questionBank}");
            }
        } else {
            return Redirect::route("get-add-question")
                ->withInput()
                ->withErrors($validator);
        }
    }

    public function getEditQuestion($questionBankId, $questionId)
    {
        $breadcrumbs = Breadcrumbs::render("admin-edit-question");
        $data = [
            "pagetitle" => $this->pageDetails["edit-question"]["title"],
            "pagedescription" => $this->pageDetails["edit-question"]["description"],
            "pageicon" => $this->pageDetails["edit-question"]["icon"],
            "breadcrumbs" => $breadcrumbs,
            "mainmenu" => "assessment",
            "submenu" => "edit-question",
            "question_bank_id" => $questionBankId,
            "question" => $this->question->getQuestion($questionId)
        ];

        if ($data["question"]["data_flag"] === true) {
            $data["isQuestionAttempted"] = (QuizAttemptData::where('question_id', '=', (int)$data["question"]["details"]->question_id)->count() > 0 ? true : false);
        }

        return view("admin.theme.assessment.question.descriptive.edit", $data);
    }

    public function postEditQuestion(Request $request)
    {
        $validator = DescriptiveAttributeRules::getValidator("edit_question", $request->all());
        if (!$validator->fails()) {
            $data = [
                "question_text" => $request->input("question_text"),
                "question_text_media" => (!is_null($request->input("question_dam_media_question_text"))) ? $request->input("question_dam_media_question_text") : [],
                "default_mark" => $request->input("default_mark"),
                "difficulty_level" => $request->input("difficulty_level"),
                "keywords" => ($request->has("keywords")) ? array_map("trim", explode(',', strip_tags($request->input("keywords")))) : [],
                "status" => "ACTIVE",
                "updated_by" => Auth::user()->username
            ];

            $status = $this->question->updateQuestion($request->input("question_bank_id"), $request->input("question_id"), $data);
            if ($request->has("return")) {
                $return = urldecode($request->input("return"));
            } else {
                $return = "cp/assessment/list-questionbank";
            }
            return redirect($return)
                ->with("success", trans("admin/assessment.ques_update_success"));
        } else {
            return Redirect::route("get-edit-question", ["question_id" => $request->input("question_id")])
                ->withInput()
                ->withErrors($validator);
        }
    }

    public function getMedia($id)
    {
        try {
            $media = Dam::getMediaById($id);

            //getToken method is in AkamaiTokenTrait
            $token = null;
            $asset = [];
            $asset = $media->toArray();
            $token = $this->getToken($asset);

            return view("media.details", ["media" => $media, "token" => $token]);
        } catch (Exception $e) {
        }
    }

    public function getDeleteQuestion(Request $request, $questionBankId, $questionId)
    {
        $status = $this->question->deleteQuestion($questionBankId, $questionId);
        $start = $request->input("start", 0);
        $limit = $request->input("limit", 10);
        $search = $request->input("serach", "");
        $orderBy = $request->input("order_by", "7 desc");
        if ($request->has("return")) {
            $redirectURL = $request->input("return");
        } else {
            $redirectURL = URL::to("cp/assessment/list-questionbank");
        }
        $redirectURL = "{$redirectURL}?start={$start}&limit={$limit}&search={$search}&order_by={$orderBy}";
        if (isset($status["flag"]) && $status["flag"]) {
            return redirect($redirectURL)->with("success", trans("admin/assessment.question_delete_success"));
        } else {
            return redirect($redirectURL)->with("error", trans("admin/assessment.problem_while_deleting_question"));
        }
    }
}
