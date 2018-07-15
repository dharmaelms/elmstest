<?php

namespace App\Helpers\Quiz;

use App\Exceptions\Authentication\AccessDeniedException;
use App\Exceptions\Quiz\KeywordNotFoundException;
use App\Exceptions\Quiz\NoQuestionsFoundException;
use App\Exceptions\Quiz\QuizAttemptClosedException;
use App\Exceptions\Quiz\QuizNotFoundException;
use App\Model\QuestionTagMapping;
use App\Enums\Quiz\CutoffFormatType as QCFT;
use App\Model\Quiz;
use App\Model\QuizAttempt;
use App\Model\Section;
use Auth;
use MongoDB\BSON\ObjectId;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Session;
use Timezone;

/**
 * Class QuizHelper
 * @package App\Helpers\Quiz
 */
class QuizHelper
{
    /**
     * @param array $input
     * @return array
     */
    public static function getQuestionGeneratorData($input)
    {
        $data = [
            "quiz_name" => trim(strip_tags($input["r-q-g-name"])),
            "slug" => Quiz::getQuizSlug($input['r-q-g-name']),
            "quiz_description" => $input["r-q-g-instructions"],
            "keywords" => ($input->has("r-q-g-keywords")) ? array_map("trim", explode(',', strip_tags($input["r-q-g-keywords"]))) : [],
            "question_per_page" => 1,
            "type" => "QUESTION_GENERATOR",
            "concept_tagging" => 0, // added for concept cron mapping
            "total_question_limit" => $input["r-q-g-total-question-limit"],
            "is_sections_enabled" => ($input["r-q-g-enable-sections"] === "TRUE") ? true : false,
            "start_time" => (int)$input["r-q-g-display-start-date"],
            "end_time" => (int)$input["r-q-g-display-end-date"],
            "status" => "ACTIVE",
            'is_production' => (int)($input['r-q-g-is_production'] === "is_beta") ? 0 : 1,
        ];

        if (!$data["is_sections_enabled"]) {
            $data["questions"] = [];
        }

        return $data;
    }

    /**
     * @param \Jenssegers\Mongodb\Collection $quiz
     * @return array
     */
    public static function formQuizAttemptData($quiz)
    {
        $data = [];
        $data["attempt_id"] = QuizAttempt::getNextSequence();
        $data["user_id"] = Auth::user()->uid;
        $quiz_id = (int)$quiz->quiz_id;
        $data["quiz_id"] = $quiz_id;
        if (isset($quiz->type) && ($quiz->type == 'QUESTION_GENERATOR')) {
            $data["type"] = 'QUESTION_GENERATOR';
        }
        if (isset($quiz->is_sections_enabled) && $quiz->is_sections_enabled) {
            $sections = Section::getSectionInQuiz($quiz_id);
            $quizAttemptSections = [];
            $quizAttemptSectionDetails = [];
            $totalQuizQuestions = 0;
            foreach ($sections as $index => $section) {
                $quizAttemptSections[] = $section->section_id;
                $questionCollection = new Collection($section->questions);
                $totalQuizQuestions += $questionCollection->count();
                $shuffledQuestions = $questionCollection->shuffle();

                $tmpArray1["_id"] = new ObjectId($section->_id);
                $tmpArray1["title"] = $section->title;
                $tmpArray1["no_of_questions"] = (int)$section->no_of_questions;
                $tmpArray1["total_attempted_questions"] = 0;
                $tmpArray1["questions"] = $shuffledQuestions->toArray();
                $tmpArray1["page_layout"] = $shuffledQuestions->chunk(
                    (isset($quiz->question_per_page) ? $quiz->question_per_page : 1)
                )->toArray();
                $tmpArray1["status"] = "OPENED";
                $quizAttemptSectionDetails[$section->section_id] = $tmpArray1;
            }
            $data["sections"] = $quizAttemptSections;
            $data["section_details"] = $quizAttemptSectionDetails;
        } else {
            $questionCollection = new Collection($quiz->questions);
            $totalQuizQuestions = $questionCollection->count();
            $shuffledQuestions = $questionCollection->shuffle();
            $data["questions"] = $shuffledQuestions->toArray();
            $data["page_layout"] = $shuffledQuestions->chunk(
                (isset($quiz->question_per_page) ? $quiz->question_per_page : 1)
            )->toArray();
        }
        $data["total_no_of_questions"] = $totalQuizQuestions;
        $data["total_attempted_questions"] = 0;
        $data["session_type"] = 'WEB';
        $data["session_key"] = Session::getId();
        $data["status"] = "OPENED";
        $data["started_on"] = Carbon::now(Auth::user()->timezone)->timestamp;
        return $data;
    }

    /**
     * @param $quiz
     * @param $attempt
     * @param $question
     * @param \Jenssegers\Mongodb\Collection $section
     * @return array
     */
    public static function formQuestionAttemptData($quiz, $attempt, $question, $section = null)
    {
        $data["user_id"] = Auth::user()->uid;
        $data["quiz_id"] = $quiz->quiz_id;
        $data["attempt_id"] = $attempt->attempt_id;
        if (isset($quiz->is_sections_enabled) && ($quiz->is_sections_enabled)) {
            $data["section_id"] = $section->section_id;
        }
        if (isset($quiz->type) && ($quiz->type == 'QUESTION_GENERATOR')) {
            $data["type"] = 'QUESTION_GENERATOR';
        }
        $data["question_id"] = $question->question_id;
        $data["question_type"] = $question->question_type;
        $data["question_text"] = $question->question_text;
        $data["question_mark"] = $question->default_mark;
        if ($question->shuffle_answers) {
            $data["answers"] = Collection::make($question->answers)->shuffle()->toArray();
        } else {
            $data["answers"] = $question->answers;
        }
        $correctAnswerIndex = null;
        foreach ($data["answers"] as $index => $answer) {
            if ($answer["correct_answer"]) {
                $correctAnswerIndex = $index;
            }
        }
        $data["correct_answer_index"] = $correctAnswerIndex;
        $data["status"] = "VIEWED";
        $data["history"] = [
            [
                "status" => "STARTED",
                "time" => Carbon::now()->timestamp
            ]
        ];

        return $data;
    }

    /**
     * @param $quiz
     * @param $attempt
     * @param $keyword
     * @param null $section
     * @return array
     * @throws KeywordNotFoundException
     * @throws NoQuestionsFoundException
     * @throws \Exception
     */
    public static function getNextRandomQuestionByKeyword($quiz, $attempt, $keyword, $section = null)
    {
        if (
            ($quiz instanceof Quiz) &&
            ($attempt instanceof QuizAttempt) &&
            (!isset($section) || ($section instanceof Section))
        ) {
            $attemptedQuestions = [];
            $keywordQuestions = [];

            $questionKeywordMapping = QuestionTagMapping::getKeywordQuestionsMappingByQuiz($quiz->quiz_id);
            if (isset($section)) {
                $questionKeywordMappingData = $questionKeywordMapping->sections["{$section->section_id}"];
                if (
                    isset($questionKeywordMappingData["keywords"]) &&
                    in_array($keyword, $questionKeywordMappingData["keywords"])
                ) {
                    if (isset($attempt->section_details["{$section->section_id}"]["attempted_questions"])) {
                        $attemptedQuestions = $attempt->section_details["{$section->section_id}"]["attempted_questions"];
                    }
                    if (isset($questionKeywordMappingData["keyword_questions"]["{$keyword}"])) {
                        $keywordQuestions = $questionKeywordMappingData["keyword_questions"]["{$keyword}"];
                    }
                } else {
                    throw new KeywordNotFoundException();
                }
            } else {
                if (isset($questionKeywordMapping->keywords) && in_array($keyword, $questionKeywordMapping->keywords)) {
                    if (isset($attempt->attempted_questions)) {
                        $attemptedQuestions = $attempt->attempted_questions;
                    }
                    if (isset($questionKeywordMapping->keyword_questions["{$keyword}"])) {
                        $keywordQuestions = $questionKeywordMapping->keyword_questions["{$keyword}"];
                    }
                } else {
                    throw new KeywordNotFoundException();
                }
            }
            if (is_array($keywordQuestions) && !empty($keywordQuestions)) {
                return array_diff($keywordQuestions, $attemptedQuestions);
            } else {
                throw new NoQuestionsFoundException();
            }
        } else {
            throw new \Exception();
        }
    }

    /**
     * Method to create page layout from multiple/associative array
     *
     * @param array $questions
     * @return array
     */
    public static function pageList($questions)
    {
        foreach ($questions as $value) {
            if (!empty($value)) {
                if (count($value) > 1) {
                    foreach ($value as $id) {
                        $page[][] = $id;
                    }
                } else {
                    $page[] = $value;
                }
            }
        }
        return $page;
    }

    /**
     * Method to create single array
     *
     * @param array $questions
     * @return array
     */
    public static function questionList($questions)
    {
        return array_map('current', self::pageList($questions));
    }

    /**
     * Method to format given value
     * @param  float $number
     * @param  int $precision
     * @param  string $dec_point
     * @param  string $thousands_sep
     * @return float $number
     */
    public static function roundOfNumber($number, $precision = 2, $dec_point = '.', $thousands_sep = '')
    {
        $replace = str_pad('.', $precision+1, '0', STR_PAD_RIGHT);
        $number = str_replace($replace, '', number_format($number, $precision, $dec_point, $thousands_sep));
        return (float)$number;
    }

    /**
     * Method to calculate percentage out of total_mark and cut_off
     * @param int $cut_off
     * @param int $total_mark
     * @return int
     */
    public static function getCutOffMark($cut_off, $total_mark, $type = QCFT::PERCENTAGE)
    {
        if ($type == QCFT::PERCENTAGE) {
            return (int)round(($cut_off/100) * $total_mark);
        }
        return (int)$cut_off;
    }
}
