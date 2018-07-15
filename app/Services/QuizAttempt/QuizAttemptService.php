<?php
namespace App\Services\QuizAttempt;

use App\Enums\QuizAttempt\QuizAttemptDataStatus;
use App\Enums\QuizAttempt\QuizAttemptStatus;
use App\Exceptions\Quiz\AttemptNotAllowedException;
use App\Exceptions\Quiz\QuizAttemptClosedException;
use App\Exceptions\Quiz\QuizNotFoundException;
use App\Helpers\Quiz\AttemptHelper;
use App\Model\Question\Repository\IQuestionRepository;
use App\Model\Quiz\IQuizRepository;
use App\Model\QuizAttempt\Repository\IQuizAttemptRepository;
use App\Model\QuizAttemptData\Repository\IQuizAttemptDataRepository;
use App\Model\Section\Repository\ISectionRepository;
use Auth;
use Log;
use Session;

/**
 * Class QuizAttemptService
 * @package App\Services\QuizAttempt
 */
class QuizAttemptService implements IQuizAttemptService
{

    /**
     * @var App\Model\Quiz\IQuizRepository
     */
    private $quiz_repository;

    /**
     * @var App|Model\Section\Repository\ISectionRepository
     */
    private $section_repository;

    /**
     * @var App\Model\QuizAttempt\Repository\IQuizAttemptRepository
     */
    private $attempt_repository;

    /**
     * @var App\Model\QuizAttemptData\Repository\IQuizAttemptDataRepository
     */
    private $attempt_data_repository;

    /**
     * @var App\Model\Question\Repository\IQuestionRepository description
     */
    private $question_repository;
    /**
     * QuizAttemptService constructor
     *
     * @param IQuestionRepository $question_repository
     * @param IQuizAttemptRepository $attempt_repository
     * @param IQuizAttemptDataRepository $data_repository
     * @param ISectionRepository $section_repository
     * @param IQuizRepository $quiz_repository
     */
    public function __construct(
        IQuestionRepository $question_repository,
        IQuizAttemptDataRepository $data_repository,
        IQuizAttemptRepository $attempt_repository,
        IQuizRepository $quiz_repository,
        ISectionRepository $section_repository
    ) {
        $this->attempt_data_repository = $data_repository;
        $this->attempt_repository = $attempt_repository;
        $this->question_repository = $question_repository;
        $this->quiz_repository = $quiz_repository;
        $this->section_repository = $section_repository;
    }
    /**
     * {@inheritdoc}
     */
    public function createAttempt($quiz_id)
    {
        //1. check quiz is exist or not.
        //2. Is assigned to user or not.
        //3. check end time of the quiz is expired or not.
        //4. check is there any open attempt and no of allowed attempts. if not follow steps 5-11, else 7-11.
        //5. create new attempt with attempt_id. Copy all questions to quiz_attempt_data collection with attempt_id.
        //   check for quiz shuffle questions and questions shuffle answers.
        //6. maintain active question and active section if quiz has sections for resume attempt.
        //7. select active question.
        //8. save answer from the user when user submits the question answer.
        //9. serve next or previous question based on request.
        //10. close the section(for timed sections) or quiz when user submits the section or quiz.
        //    calculate the score before closing the attempt.
        $quiz = $this->quiz_repository->find($quiz_id);
        if ($quiz != null) {
            $this->validateQuiz($quiz->quiz_id);
            $attempts = $this->attempt_repository->findAllAttempts($quiz->quiz_id, Auth::user()->uid);
            $open_attempts = $attempts->where('status', 'OPENED')->first();
            if ($open_attempts != null) {
                $this->copyRemainingQuestions($open_attempts, $quiz);
                return $open_attempts->attempt_id;
            }
            // try {
            //     $this->validateQuizAttempt($quiz, $attempts);
            // } catch (AttemptNotAllowedException $e) {
            //     return $attempt->attempt_id;
            // }
            $attempt = $this->createNewAttempt($quiz);
            $this->copyAttemptQuestions($attempt, $quiz);
            return $attempt->attempt_id;
        } else {
            throw new QuizNotFoundException();
        }
    }

    /**
     * Method to validate quiz and it is expiry
     *
     * @param object $quiz
     */
    public function validateQuiz($quiz_id)
    {
        $this->isQuizAssignedToUser($quiz_id);
        $this->isQuizExpiredOrNot($quiz_id);
    }

    /**
     * {@inheritdoc}
     */
    public function isQuizAssignedToUser($quiz_id)
    {
        // TODO: Get assigned quiz list
        // check the $quiz_id is in quiz list. If not
        // throw exception
    }

    /**
     * {@inheritdoc}
     */
    public function isQuizExpiredOrNot($quiz_id)
    {
        // TODO: Get list of channels as item.
        // check for which channel has max time of display_end_date and compare
        // the current time with it.
        // throw exception if channel end time is expired
    }

    /**
     * {@inheritdoc}
     */
    public function validateQuizAttempt($quiz, $attempts)
    {
        if ($quiz->attempts == 0 || $quiz->attempts > $attempts->count()) {
            return true;
        } else {
            throw new AttemptNotAllowedException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createNewAttempt($quiz)
    {
        $attempt = new \StdClass();
        $attempt->user_id = Auth::user()->uid;
        $attempt->quiz_id = $quiz->quiz_id;
        $attempt->total_mark = $quiz->total_mark;
        $attempt->obtained_mark = 0;
        $attempt->un_attempted_question_count = 0;
        $attempt->correct_answer_count = 0;
        $attempt->incorrect_answer_count = 0;
        $attempt->session_type = 'WEB';
        $attempt->session_key = Session::getId();
        $attempt->status = QuizAttemptStatus::OPENED;
        $attempt->details = ['viewed' => [], 'answered' => [], 'reviewed' => []];
        $attempt->questions = $attempt->details['not_viewed'] = array_flatten(collect($quiz->questions)->chunk(1)->toArray());
        $attempt->started_on = time();
        $attempt->completed_on = '';
        if ($quiz->duration > 0) {
            $attempt->duration = $quiz->duration;
        }
        if ($quiz->is_sections_enabled) {
            $section_details = collect();
            $sections = $this->section_repository->getQuizSections($quiz->quiz_id);
            if (!$sections->isEmpty()) {
                $section_details = $sections->map(function ($section) use ($quiz) {
                    $row = new \StdClass;
                    $row->section_id = $section->section_id;
                    $row->title = $section->title;
                    $row->total_marks = $section->total_marks;
                    $row->obtain_marks = 0;
                    if (array_get($quiz, 'is_timed_sections', false)) {
                        $row->duration = $section->duration;
                    }
                    if (array_get($quiz, 'shuffle_questions', false)) {
                        $row->page_layout = collect(array_flatten($section->questions))->shuffle()->chunk(1)->toArray();
                    } else {
                        $row->page_layout = collect(array_flatten($section->questions))->chunk(1)->toArray();
                    }
                    if (array_get($section, 'cut_off_mark') && $section->cut_off_mark >= 0) {
                        $row->cut_off = $section->cut_off_mark;
                        $row->percentage = $section->cut_off;
                    }
                    return $row;
                });
                $attempt->active_section_id = $sections->first()->section_id;
            }
            $attempt->section_details = $section_details->keyBy('section_id');
        } else {
            if (array_get($quiz, 'shuffle_questions', false)) {
                $attempt->page_layout = collect(array_flatten($quiz->page_layout))->shuffle()->chunk(1)->toArray();
            } else {
                $attempt->page_layout = collect(array_flatten($quiz->page_layout))->chunk(1)->toArray();
            }
        }
        $attempt->attempt_neg_mark = $quiz->attempt_neg_mark;
        $attempt->un_attempt_neg_mark = $quiz->un_attempt_neg_mark;
        return $this->attempt_repository->newAttempt($attempt);
    }

    /**
     * {@inheritdoc}
     */
    public function copyAttemptQuestions($attempt)
    {
        if (isset($attempt->section_details)) {
            $question_ids = collect($attempt->section_details)->pluck('page_layout')->all();
        } else {
            $question_ids = $attempt->page_layout;
        }
        $questions = $this->question_repository->getQuestions(array_flatten($question_ids));
        $this->attempt_data_repository->copyQuestions($attempt, $questions);
    }

    public function copyRemainingQuestions($attempt, $quiz)
    {
        $copied_questions = $this->attempt_data_repository->getAttemptData($attempt->attempt_id);
        $questions = array_diff($quiz->questions, $copied_questions->pluck('question_id')->all());
        if (empty($questions)) {
            return true;
        }
        $questions = $this->question_repository->getQuestions($questions);
        $this->attempt_data_repository->copyQuestions($attempt, $questions);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function saveAnswer($request, $attempt_id)
    {
        $page = $request->input('current_page');
        $next_page = $request->input('next_page');
        $answer = $request->input('answer', '');
        $reviewed = $request->input('reviewed');
        $section = $request->input('section');
        $time_spend = $request->input('timetaken');
        $attempt = $this->attempt_repository->find($attempt_id)->first();
        if (array_get($attempt, 'section_details')) {
            $page_layout = array_flatten($attempt->section_details[$section]['page_layout']);
        } else {
            $page_layout = array_flatten($attempt->page_layout);
        }
        $question_id = $page_layout[$page];
        $quiz = $this->quiz_repository->find($attempt->quiz_id);
        $save_answer = $this->attempt_data_repository->saveAnswer($attempt, $question_id, $next_page, $answer, $reviewed, $time_spend, $section, $quiz);
        $update_attempt = $this->attempt_repository->updateDetails($attempt, $question_id, $answer, $reviewed);
        return ['answer_status' => $save_answer, 'attempt_status' => $update_attempt['status'], 'class' => $update_attempt['class'], 'page' => $page, 'section' => $section];
    }

    /**
     * {@inheritdoc}
     */
    public function nextQuestion($attempt_id, $page, $section)
    {
        $attempt = $this->attempt_repository->find($attempt_id)->first();
        $quiz = $this->quiz_repository->find($attempt->quiz_id);
        if ($quiz->is_sections_enabled) {
            if ($quiz->is_timed_sections) {
                if ($page >= count(array_flatten($attempt->section_details[$section]['page_layout']))) {
                    $page = 0;
                }
            } else {
                if ($page >= count(array_flatten($attempt->section_details[$section]['page_layout']))) {
                    $sections = array_keys($attempt->section_details);
                    if (count($sections) == 1 || $section == last($sections)) {
                        return ['next' => false, 'redirect_summary' => true];
                    } else {
                        $section_key = array_search($section, $sections);
                        $section_key++;
                        if (array_key_exists($section_key, $sections)) {
                            $section = $sections[$section_key];
                        }
                        return ['switch_section' => true, 'section' => $section, 'page' => 0];
                    }
                }
            }
            $page_layout = array_flatten($attempt->section_details[$section]['page_layout']);
        } else {
            if ($page >= count($attempt->page_layout)) {
                return ['next' => false, 'redirect_summary' => true];
            } else {
                $page_layout = array_flatten($attempt->page_layout);
            }
        }
        $question_id = $page_layout[$page];
        if ($attempt->active_section_id != $section) {
            $attempt->active_section_id = $section;
        }
        $details = $attempt->details;
        $details['viewed'] = array_unique(array_merge(array_flatten($attempt->details['viewed']), [$question_id]));
        $details['not_viewed'] = array_diff(array_flatten($details['not_viewed']), array_flatten($details['viewed']));
        $question = $this->attempt_data_repository->getQuestion($attempt_id, $question_id)->first();
        if (is_null($question)) {
            $questions = $this->question_repository->getQuestions([$question_id])->first();
            $this->attempt_data_repository->insertQuestion($questions, $attempt, $section);
            $question = $this->attempt_data_repository->getQuestion($attempt_id, $question_id)->first();
        }
        $data = [];
        $data['question_text'] = $question->question_text;
        if ($question->user_response != '') {
            $key = array_keys(collect($question->answers)->where('answer', $question->user_response)->toArray())[0];
        } else {
            $key = '';
        }
        $data['page'] = $page;
        $data['answers'] = collect(array_values(array_replace(array_flip($question->answer_order), $question->answers)))->pluck('answer')->all();
        $data['user_response'] = $question->user_response != '' ? array_search($key, $question->answer_order) : '';
        $data['mark'] = $question->question_mark;
        $data['review'] = $question->mark_review;
        $data['class'] = AttemptHelper::getQuestionStatus($details, $question_id);
        if ($question->status == QuizAttemptDataStatus::NOT_VIEWED) {
            $question->status = QuizAttemptDataStatus::STARTED;
        }
        $history = $question->history;
        $history[] = [
            'status' => QuizAttemptDataStatus::STARTED,
            'time' => time(),
        ];
        $question->history = $history;
        $question->save();
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getClosedQuizzes($quiz_id, $uid)
    {
        return $this->attempt_repository->getClosedQuizzes($quiz_id, $uid);
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttemptes($quiz_id)
    {
        return $this->attempt_repository->hasAttemptes($quiz_id);
    }
}
