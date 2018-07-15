<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminBaseController;
use App\Model\LiveQuestionBanks;
use App\Model\LiveQuestions;
use App\Model\Question;
use App\Model\QuestionBank;
use Auth;
use Request;

/**
 * Flash Cards Controller to create, update, view, manage and delete
 * @author sathishkumar@linkstreet.in
 */
class MigrationController extends AdminBaseController
{

    protected $layout = 'admin.theme.layout.master_layout';

    public function __construct(Request $request)
    {
        $input = $request::input();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request::merge($input);
        $this->theme_path = 'admin.theme';
    }

    /**
     * function used to import questions and question banks to new Database
     */
    public function getImportQuestion()
    {
        $questionbanks = LiveQuestionBanks::all();
        echo '<pre>';
        foreach ($questionbanks as $questionbank) {
            if (!empty($questionbank->questions)) {
                //checking question name exist in old table
                if ($data = LiveQuestionBanks::findQuestionBankByColumn('question_bank_name', $questionbank->question_bank_name)->first()) { //if question bank is exist in old table
                    $qids = [];
                    foreach ($questionbank->questions as $question) {
                        if ($questionData = LiveQuestions::findQuestionByColumn('question_id', $question)->first()) {
                            $question_id = Question::getNextSequence();
                            $qdata = [
                                'question_id' => $question_id,
                                'question_name' => 'Q' . sprintf("%07d", $question_id),
                                'question_text' => $questionData->question_text,
                                'question_text_media' => (!is_null($questionData->question_dam_media_question_text)) ? $questionData->question_dam_media_question_text : [],
                                'question_type' => $questionData->question_type,
                                'keywords' => $questionData->keywords,
                                'default_mark' => (int)$questionData->default_mark,
                                'difficulty_level' => $questionData->difficulty_level,
                                'practice_question' => $questionData->practice_question,
                                'shuffle_answers' => $questionData->shuffle_answers,
                                'answers' => $questionData->answers,
                                'status' => $questionData->status,
                                'question_bank' => $data->question_bank_id,
                                'quizzes' => [],
                                'editor_images' => $questionData->editor_images,
                                'created_by' => Auth::user()->username,
                                'created_at' => time(),
                                'updated_by' => '',
                                'updated_at' => time()
                            ];

                            if (Question::insert($qdata)) {
                                $qids[] = $question_id;
                            } else {
                                echo $questionData->question_id . ' error in insertion <br/>';
                            }
                        }
                    }
                    if (!empty($qids)) {
                        LiveQuestionBanks::addQuestionsToQuestionBank($data->question_bank_id, $qids);
                    }
                } else { //if question bank not exist in old table
                    $question_bank_slug = QuestionBank::getQuestionBankNameSlug($questionbank->question_bank_name);
                    $qbank_id = QuestionBank::getNextSequence();
                    $qbankdata = [
                        'question_bank_id' => $qbank_id,
                        'question_bank_name' => $questionbank->question_bank_name,
                        'question_bank_slug' => $question_bank_slug,
                        'question_bank_description' => $questionbank->question_bank_description,
                        'keywords' => $questionbank->keywords,
                        'questions' => [],
                        'draft_questions' => [],
                        'editor_images' => $questionbank->editor_images,
                        'relations' => $questionbank->relations,
                        'status' => 'ACTIVE',
                        'created_by' => Auth::user()->username,
                        'created_at' => time(),
                        'updated_by' => '',
                        'updated_at' => time()
                    ];
                    $qbank_data = QuestionBank::insert($qbankdata);
                    if ($qbank_data) {
                        if ($qbank = LiveQuestionBanks::findQuestionBankByColumn('question_bank_name', $questionbank->question_bank_name)->first()) { //if question bank is exist in old table
                            $qids = [];
                            foreach ($questionbank->questions as $question) {
                                if ($questionData = LiveQuestions::findQuestionByColumn('question_id', $question)->first()) {
                                    $question_id = Question::getNextSequence();
                                    $qdata = [
                                        'question_id' => $question_id,
                                        'question_name' => 'Q' . sprintf("%07d", $question_id),
                                        'question_text' => $questionData->question_text,
                                        'question_text_media' => (!is_null($questionData->question_dam_media_question_text)) ? $questionData->question_dam_media_question_text : [],
                                        'question_type' => $questionData->question_type,
                                        'keywords' => $questionData->keywords,
                                        'default_mark' => (int)$questionData->default_mark,
                                        'difficulty_level' => $questionData->difficulty_level,
                                        'practice_question' => $questionData->practice_question,
                                        'shuffle_answers' => $questionData->shuffle_answers,
                                        'answers' => $questionData->answers,
                                        'status' => $questionData->status,
                                        'question_bank' => $qbank->question_bank_id,
                                        'quizzes' => [],
                                        'editor_images' => $questionData->editor_images,
                                        'created_by' => Auth::user()->username,
                                        'created_at' => time(),
                                        'updated_by' => '',
                                        'updated_at' => time()
                                    ];
                                    if (Question::insert($qdata)) {
                                        $qids[] = $question_id;
                                    } else {
                                        echo $questionData->question_id . ' error in insertion <br/>';
                                    }
                                }
                            }
                            if (!empty($qids)) {
                                LiveQuestionBanks::addQuestionsToQuestionBank($qbank->question_bank_id, $qids);
                            }
                        }
                    }
                }
            }
        }
        die('inserted');
    }
}
