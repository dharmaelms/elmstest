<?php

/**
 * @author sathishkumar@linkstreet.in
 */

namespace App\Http\Controllers\admin;

use App\Http\Controllers\AdminBaseController;
use App\Model\Question;
use App\Model\QuestionTagMapping;
use App\Model\Quiz;
use App\Model\Section;
use Auth;
use Carbon;
use Input;
use Log;
use Request;
use Response;

class AssessmentMappingController extends AdminBaseController
{

    public function getList()
    {
        Log::info('assessment mapping cron called');
        $this->quizList();
    }

    /**
     * this function used to get quizzes which are all starts in given time
     * @return  string
     */
    public function quizList()
    {
        $quizzes = Quiz::where('status', 'ACTIVE')
            ->where('concept_tagging', 0)
            ->get(['quiz_id', 'questions', 'is_sections_enabled', 'quiz_name']);
        $result = $this->quizMapping($quizzes);
        Log::info($result . ' mapping(s) created');
    }

    /**
     * this function is used to create mapping
     * @param  object $quizzes quiz object
     * @return string
     */
    public function quizMapping($quizzes)
    {
        $count = 0;
        if (!$quizzes->isEmpty()) {
            foreach ($quizzes as $quiz) {
                $this->quizInMapping($quiz->quiz_id);
                if ($quiz->is_sections_enabled) {
                    $this->quizSections($quiz->quiz_id);
                } else {
                    if (!empty($quiz->questions)) {
                        $this->quizQuestionsMapping($quiz->quiz_id, $quiz->questions);
                    }
                }
                Quiz::where('quiz_id', (int)$quiz->quiz_id)->update(['concept_tagging' => 1]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * this function used to get sections in quiz
     * @param integer $quiz_id Quiz Id
     * @return array
     */
    public function quizSections($quiz_id)
    {
        $sections = Section::where('quiz_id', $quiz_id)->where('status', 'ACTIVE')->get(['section_id', 'title', 'questions']);
        if (!$sections->isEmpty()) {
            foreach ($sections as $section) {
                $this->sectionMapping($quiz_id, $section->section_id, $section->questions);
            }
        }
    }

    /**
     * this function is used to create quiz in mapping
     * @param  integer $quiz_id Quiz Id
     * @return boolean
     */
    public function quizInMapping($quiz_id)
    {
        QuestionTagMapping::where('quiz_id', $quiz_id)->delete();
        QuestionTagMapping::insert(['quiz_id' => $quiz_id, 'created_at' => Carbon::now()->timestamp, 'updated_at' => Carbon::now()->timestamp]);
        return true;
    }

    /**
     * this function is used to create mapping for sections
     * @param int $quiz_id
     * @param int $section_id
     * @param array $questions
     * @return void
     */
    public function sectionMapping($quiz_id, $section_id, $questions)
    {
        if (!empty($questions)) {
            foreach ($questions as $question) {
                $this->sectionAndQuestionMapping($quiz_id, $section_id, $question);
            }
        }
    }

    /**
     * this function used to create sections, keywords and its questions
     * @param integer $quiz_id
     * @param integer $section_id
     * @param integer $question_id
     * @return void
     */
    public function sectionAndQuestionMapping($quiz_id, $section_id, $question_id)
    {
        $questionData = $this->questionDetails($question_id);
        if (!$questionData->isEmpty()) {
            if (!empty($questionData[0]->keywords)) {
                $keywords = array_filter($questionData[0]->keywords);
                foreach ($keywords as $keyword) {
                    if (!empty($keyword)) {
                        QuestionTagMapping::where('quiz_id', $quiz_id)->push("sections.$section_id.keywords", $keyword, true);
                        QuestionTagMapping::where('quiz_id', $quiz_id)->push("sections.$section_id.keyword_questions.$keyword", $question_id, true);
                        QuestionTagMapping::where('quiz_id', $quiz_id)->update(['updated_at' => Carbon::now()->timestamp]);
                    }
                }
            }
        }
    }

    /**
     * [QuizQuestionsMapping description]
     * @param integer $quiz_id Quiz Id
     * @param array $questions Questions Array
     * @return void
     */
    public function quizQuestionsMapping($quiz_id, $questions)
    {
        foreach ($questions as $question) {
            $questionData = $this->questionDetails($question);
            if (!$questionData->isEmpty()) {
                if (!empty($questionData[0]->keywords)) {
                    foreach ($questionData[0]->keywords as $keyword) {
                        if (!empty($keyword)) {
                            QuestionTagMapping::where('quiz_id', $quiz_id)->push("keywords", $keyword, true);
                            QuestionTagMapping::where('quiz_id', $quiz_id)->push("keyword_questions.$keyword", $question, true);
                            QuestionTagMapping::where('quiz_id', $quiz_id)->update(['updated_at' => Carbon::now()->timestamp]);
                        }
                    }
                }
            }
        }
    }

    /**
     * [questionDetails description]
     * @param  integer $question_id Question Id
     * @return array
     */
    public function questionDetails($question_id)
    {
        return Question::where('question_id', $question_id)->where('status', 'ACTIVE')->get(['question_id', 'keywords']);
    }

    /**
     * this function is used to insert concept_tagging column in Old quizzes
     * @return string
     */
    public function getUpdateQuiz()
    {
        $quizzes = Quiz::where('concept_tagging', 'exists', false)->get();
        foreach ($quizzes as $key => $quiz) {
            Quiz::where('quiz_id', (int)$quiz->quiz_id)->update(['concept_tagging' => 0]);
        }
        echo 'completed';
    }

    /**
     * this function is used to create mapping for given Quiz ID
     * @param  $quiz_id Quiz Id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUpdateQuizMapping($quiz_id)
    {
        $quiz = Quiz::where('quiz_id', (int)$quiz_id)
            ->where('status', 'ACTIVE')
            ->first();
        if (!empty($quiz)) {
            $this->quizInMapping($quiz->quiz_id);
            if ($quiz->is_sections_enabled) {
                $this->quizSections($quiz->quiz_id);
            } else {
                if (!empty($quiz->questions)) {
                    $this->quizQuestionsMapping($quiz->quiz_id, $quiz->questions);
                }
            }
            if (Request::ajax()) {
                return Response::json(['status' => true, 'message' => 'Concept mapping is created for ' . $quiz->quiz_name]);
            } else {
                Log::info('Mapping created');
            }
        } else {
            return Response::json(['status' => 0, 'message' => 'Quiz not found']);
        }
    }
}
