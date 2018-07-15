<?php namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Model\API;
use App\Model\Question;
use App\Model\Quiz;
use App\Model\QuizAttempt;
use App\Model\QuizAttemptData;
use App\Model\QuizReport;
use App\Model\User;
use Crypt;
use Input;
use Session;

/**
 * QuizController (API)
 *
 * Controller will do the assessment actions
 * which are accessed from Mobile device
 */
class QuizController extends Controller
{
    /**
     * Show error page
     *
     * @param string $message message
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    private function showError($message = '')
    {
        return view('api/error')
            ->with('message', $message);
    }

    /**
     * Exit page defined to close the in-app browser
     * when the user redirected to this page in the app
     *
     * @return string
     */
    public function getExit()
    {
        return '';
    }

    /**
     * Default index page
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getIndex()
    {
        return response()
            ->json([
                'flag' => 'failure',
                'message' => 'Bad request'
            ], 400);
    }

    /**
     * Generate the attempt url based on access token & quiz id
     * for the user which expires in 5 minutes
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function postAttemptUrl()
    {
        $access_token = trim(Input::get('access_token'));
        $quiz_id = (int)trim(Input::get('quiz_id'));

        if (empty($access_token) || empty($quiz_id)) {
            return response()
                ->json([
                    'flag' => 'failure',
                    'message' => 'Invalid inputs'
                ], 400);
        }

        $user = User::where('api.access_token', '=', $access_token)
            ->where('status', '=', 'ACTIVE')
            ->first();

        if (empty($user)) {
            return response()
                ->json([
                    'flag' => 'failure',
                    'message' => 'Invalid user'
                ], 401);
        }

        return response()
            ->json([
                'flag' => 'success',
                'start_url' => urlencode(url('api/quiz/start-attempt?data=' . Crypt::encrypt($user->uid . '|' . $quiz_id . '|' . time()))),
                'end_url' => urlencode(url('api/quiz/exit'))
            ]);
    }

    /**
     * Start the users quiz attempt based on the input data
     *
     * @return void
     */
    public function getStartAttempt()
    {
        $input = Input::get('data', Crypt::encrypt('0|0|0'));
        $decrypt = explode('|', Crypt::decrypt($input));

        if (empty($decrypt)) {
            return $this->showError('Error while starting the attempt. Ref: Data issue');
        }

        $user_id = (isset($decrypt[0]) ? (int)$decrypt[0] : 0);
        $quiz_id = (isset($decrypt[1]) ? (int)$decrypt[1] : 0);
        $request_time = (isset($decrypt[2]) ? (int)$decrypt[2] : 0);

        if (empty($user_id) || empty($quiz_id)) {
            return $this->showError('Error while starting the attempt. Ref: Data invalid');
        }

        if (empty($request_time) || ($request_time + 300) < time()) {
            return $this->showError('Link expired');
        }

        $user = User::where('uid', '=', (int)$user_id)
            ->where('status', '=', 'ACTIVE')
            ->firstOrFail();

        // User access permission
        if (!in_array($quiz_id, API::userQuizRel($user->toArray())['quiz_list'])) {
            return $this->showError('You are not assigned to this assessment');
        }

        $quiz = Quiz::where('quiz_id', '=', (int)$quiz_id)
            ->where('status', '=', 'ACTIVE')
            ->firstOrFail();

        // Check for the start date
        if (!empty($quiz->start_time) && $quiz->start_time->timestamp > time()) {
            return $this->showError('Assessment is not opened');
        }

        // Check for the end date
        if (!empty($quiz->end_time) && $quiz->end_time->timestamp < time()) {
            return $this->showError('Assessment is closed');
        }

        // Check questions
        if (count($quiz->questions) == 0) {
            return $this->showError('No questions are assigned to this assessment');
        }

        $attempt = QuizAttempt::where('quiz_id', '=', (int)$quiz_id)
            ->where('user_id', '=', (int)$user->uid)
            ->where('status', '=', 'OPENED')
            ->get();

        if ($attempt->isEmpty()) {
            $attempts = QuizAttempt::where('quiz_id', '=', (int)$quiz_id)
                ->where('user_id', '=', (int)$user->uid)
                ->get();
            // Check for the allowed attempts
            if ($quiz->attempts == 0 || $quiz->attempts > $attempts->count()) {
                $attempt_id = (int)QuizAttempt::getNextSequence();
                $param['attempt_id'] = $attempt_id;
                $param['user_id'] = $user->uid;
                $param['quiz_id'] = $quiz->quiz_id;
                $param['questions'] = call_user_func_array('array_merge', $quiz->page_layout);
                $param['page_layout'] = $quiz->page_layout;
                $param['total_mark'] = $quiz->total_mark;
                $param['obtained_mark'] = 0;
                $param['session_type'] = 'API';
                $param['session_key'] = Session::getId();
                $param['status'] = 'OPENED';
                $param['started_on'] = time();
                $param['completed_on'] = '';
                if (QuizAttempt::insert($param)) {
                    // Session setup for this attempt
                    $data['quiz_id'] = $quiz->quiz_id;
                    $data['user_id'] = $user->uid;
                    $data['attempt'] = $attempt_id;
                    $data['session_type'] = 'API';
                    $data['question_answered'] = [];
                    Session::put('assessment.' . $attempt_id, $data);
                } else {
                    return $this->showError('Issue while starting the attempt');
                }
            } else {
                return $this->showError('No attempt is left to take this quiz');
            }
        } else {
            $attempt_id = $attempt->first()->attempt_id;
            if (Session::has('assessment.' . $attempt_id) === false) {
                $data['quiz_id'] = $quiz->quiz_id;
                $data['user_id'] = $user->uid;
                $data['attempt'] = $attempt_id;
                $data['session_type'] = 'API';
                $data['question_answered'] = QuizAttemptData::where('quiz_id', '=', (int)$quiz->quiz_id)
                    ->where('attempt_id', '=', $attempt_id)
                    ->where('user_id', '=', $user->uid)
                    ->where('status', '=', 'ANSWERED')
                    ->get(['question_id'])
                    ->lists('question_id')
                    ->all();
                Session::put('assessment.' . $attempt_id, $data);
            }
        }

        // Start or continue the attempt
        return redirect('api/quiz/attempt/' . $attempt_id);
    }

    /**
     * Generate the attempt page with questions based on the conditions
     * in the quiz
     *
     * @param integer $attempt_id
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function getAttempt($attempt_id = 0)
    {
        $attempt = QuizAttempt::where('attempt_id', '=', (int)$attempt_id)->first();
        $quiz = Quiz::where('quiz_id', '=', (int)$attempt->quiz_id)->firstOrFail();

        if ($attempt->status == 'OPENED') {
            if (Session::has('assessment.' . $attempt_id) === true) {
                $session = Session::get('assessment.' . $attempt_id);

                $user = User::where('uid', '=', (int)$session['user_id'])
                    ->where('status', '=', 'ACTIVE')
                    ->firstOrFail();

                if (!empty($quiz->end_time) && $quiz->end_time->timestamp < time()) {
                    return redirect('api/quiz/summary/' . $attempt_id);
                }

                if ($quiz->duration != 0 && ($quiz->duration * 60) < (time() - $attempt->started_on->timestamp)) {
                    return redirect('api/quiz/summary/' . $attempt_id);
                }

                $page = Input::get('page', 0);

                if (count($attempt->page_layout) <= $page) {
                    return redirect('api/quiz/summary/' . $attempt_id);
                }

                if (empty($attempt->page_layout[$page])) {
                    return redirect('api/quiz/attempt/' . $attempt_id . '?page=' . ($page + 1));
                }

                $attemptData = QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
                    ->whereIn('question_id', $attempt->page_layout[$page])
                    ->get();

                if ($attemptData->isEmpty()) {
                    $question = Question::whereIn('question_id', $attempt->page_layout[$page])->get();

                    foreach ($question as $value) {
                        $data = [
                            'attempt_data_id' => QuizAttemptData::getNextSequence(),
                            'attempt_id' => (int)$attempt_id,
                            'quiz_id' => (int)$quiz->quiz_id,
                            'user_id' => (int)$user->uid,
                            'question_id' => (int)$value->question_id,
                            'question_type' => $value->question_type,
                            'question_text' => $value->question_text,
                            'question_mark' => $value->default_mark
                        ];

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
                        $data['history'][] = [
                            'status' => 'STARTED',
                            'time' => time()
                        ];

                        QuizAttemptData::insert($data);
                    }

                    // Fetch the attempt data again
                    $attemptData = QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
                        ->whereIn('question_id', $attempt->page_layout[$page])
                        ->get();
                }

                return view('api.quiz.attempt')
                    ->with('quiz', $quiz)
                    ->with('attempt', $attempt)
                    ->with('attemptdata', $attemptData)
                    ->with('page', $page);
            } else {
                return $this->showError('Issue with attempt session');
            }
        } else {
            return $this->showError('This attempt has been closed');
        }
    }

    /**
     * Handles the attempt post submit values like answers
     *
     * @param integer $attempt_id
     * @return void
     */
    public function postAttempt($attempt_id)
    {
        $attempt = QuizAttempt::where('attempt_id', '=', (int)$attempt_id)->first();
        $quiz = Quiz::where('quiz_id', '=', (int)$attempt->quiz_id)->firstOrFail();

        if ($attempt->status == 'OPENED') {
            if (Session::has('assessment.' . $attempt_id) === true) {
                if (!empty($quiz->end_time) && $quiz->end_time->timestamp < time()) {
                    return redirect('api/quiz/summary/' . $attempt_id);
                }

                if ($quiz->duration != 0 && ($quiz->duration * 60) < (time() - $attempt->started_on->timestamp)) {
                    return redirect('api/quiz/summary/' . $attempt_id);
                }

                $question = array_map('intval', Input::get('q', []));

                $page = 0;
                if (Input::has('prev')) {
                    $page = (int)Input::get('prev_page', 0);
                }
                if (Input::has('next')) {
                    $page = (int)Input::get('next_page', 0);
                }

                if (!empty($question)) {
                    $aData = QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
                        ->whereIn('question_id', $question)
                        ->get()
                        ->toArray();
                    $attemptData = [];
                    foreach ($aData as $value) {
                        $attemptData[$value['question_id']] = $value;
                    }

                    foreach ($question as $value) {
                        $update = true;
                        $quizAttemptData = $attemptData[$value];
                        $data = [];

                        if ($quizAttemptData['question_type'] == 'MCQ') {
                            $selected_option = Input::get('q:' . $value, null);
                            if ($selected_option !== null) {
                                $answer_order = $quizAttemptData['answer_order'][$selected_option];
                                $data['user_response'] = $quizAttemptData['answers'][$answer_order]['answer'];
                                $data['history'] = $quizAttemptData['history'];
                                if ($quizAttemptData['status'] == 'STARTED') {
                                    $data['status'] = 'ANSWERED';
                                    $data['history'][] = [
                                        'status' => 'ANSWERED',
                                        'time' => time(),
                                        'answer' => $data['user_response']
                                    ];
                                    // Update the session
                                    Session::push('assessment.' . $attempt_id . '.question_answered', $value);
                                }
                                if ($quizAttemptData['status'] == 'ANSWERED') {
                                    if ($quizAttemptData['user_response'] != $data['user_response']) {
                                        $data['history'][] = [
                                            'status' => 'ANSWERED',
                                            'time' => time(),
                                            'answer' => $data['user_response']
                                        ];
                                    }
                                }
                            } else {
                                $update = false;
                            }
                        }

                        if ($update) {
                            QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
                                ->where('question_id', '=', $value)
                                ->update($data);
                        }
                    }
                    return redirect('api/quiz/attempt/' . (int)$attempt_id . '?page=' . $page);
                }
            } else {
                return $this->showError('Session expired or invalid attempt');
            }
        } else {
            return $this->showError('This attempt has been closed');
        }
    }

    /**
     * Shows the summary of the attempt
     *
     * @param integer $attempt_id
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function getSummary($attempt_id)
    {
        $attempt = QuizAttempt::where('attempt_id', '=', (int)$attempt_id)->first();
        $quiz = Quiz::where('quiz_id', '=', (int)$attempt->quiz_id)->firstOrFail();

        if ($attempt->status == 'OPENED') {
            if (Session::has('assessment.' . $attempt_id) == true) {
                $data = QuizAttemptData::where('quiz_id', '=', (int)$quiz->quiz_id)
                    ->where('attempt_id', '=', (int)$attempt->attempt_id)
                    ->get(['question_id', 'status']);

                $attemptData = [];
                foreach ($data as $value) {
                    if ($value->status == 'ANSWERED') {
                        $attemptData[] = $value->question_id;
                    }
                }

                $message = '';
                // Checking whether current time exceeded the quiz end time
                if (!empty($quiz->end_time) && $quiz->end_time->timestamp < time()) {
                    $message = 'Note: Attempt exceeded the quiz end time';
                }
                // Checking whether the attempt exceeded the quiz duration
                if ($quiz->duration != 0 && ($quiz->duration * 60) < (time() - $attempt->started_on->timestamp)) {
                    $message = 'Note: Attempt exceeded the quiz duration';
                }

                return view('api.quiz.summary')
                    ->with('quiz', $quiz)
                    ->with('attempt', $attempt)
                    ->with('attemptdata', $attemptData)
                    ->with('message', $message);
            } else {
                return $this->showError('Session expired or invalid attempt');
            }
        } else {
            return $this->showError('This attempt has been closed');
        }
    }

    /**
     * Handles the attempt complete submission
     *
     * @param integer $attempt_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function postCloseAttempt($attempt_id)
    {
        $attempt = QuizAttempt::where('attempt_id', '=', (int)$attempt_id)->first();
        $quiz = Quiz::where('quiz_id', '=', (int)$attempt->quiz_id)->firstOrFail();

        if ($attempt->status == 'OPENED') {
            if (Session::has('assessment.' . $attempt_id) == true) {
                $time = time();
                $attemptData = QuizAttemptData::where('quiz_id', '=', (int)$quiz->quiz_id)
                    ->where('attempt_id', '=', (int)$attempt->attempt_id)
                    ->get();

                foreach ($attemptData as $value) {
                    $data = [];
                    if ($value->question_type == 'MCQ') {
                        if ($value->user_response == $value->correct_answer) {
                            $data['obtained_mark'] = (int)$value->question_mark;
                            $data['answer_status'] = 'CORRECT';
                        } else {
                            $data['answer_status'] = 'INCORRECT';
                        }
                        $data['status'] = 'COMPLETED';
                        $data['history'] = $value->history;
                        $data['history'][] = [
                            'status' => 'COMPLETED',
                            'time' => $time
                        ];
                    }

                    QuizAttemptData::where('attempt_id', '=', (int)$attempt_id)
                        ->where('question_id', '=', (int)$value->question_id)
                        ->update($data);
                }

                // Unanswered questions
                $unanswered = array_map('intval', array_diff($attempt->questions, $attemptData->lists('question_id')->all()));
                $unanswered_question = Question::whereIn('question_id', $unanswered)->get();

                $session = Session::get('assessment.' . $attempt_id);

                $user = User::where('uid', '=', (int)$session['user_id'])
                    ->where('status', '=', 'ACTIVE')
                    ->firstOrFail();

                foreach ($unanswered_question as $value) {
                    $data = [
                        'attempt_data_id' => QuizAttemptData::getNextSequence(),
                        'attempt_id' => (int)$attempt_id,
                        'quiz_id' => (int)$quiz->quiz_id,
                        'user_id' => (int)$user->uid,
                        'question_id' => (int)$value->question_id,
                        'question_type' => $value->question_type,
                        'question_text' => $value->question_text,
                        'question_mark' => $value->default_mark
                    ];

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
                    $data['answer_status'] = 'INCORRECT';
                    $data['status'] = 'NOT_VIEWED';

                    QuizAttemptData::insert($data);
                }

                $data = [
                    'obtained_mark' => QuizAttemptData::where('quiz_id', '=', (int)$quiz->quiz_id)
                        ->where('attempt_id', '=', (int)$attempt->attempt_id)
                        ->sum('obtained_mark'),
                    'status' => 'CLOSED',
                    'completed_on' => $time
                ];

                QuizAttempt::where('attempt_id', '=', (int)$attempt->attempt_id)
                    ->update($data);
                // Update the quiz reports
                QuizReport::calAttemptScore($quiz, $user->uid, $attempt_id);
                // Remove attempt details from the session
                Session::forget('assessment.' . $attempt_id);

                return redirect('api/quiz/exit');
            } else {
                return $this->showError('Session expired or Invalid attempt');
            }
        } else {
            return $this->showError('This attempt has been closed');
        }
    }

    public function getBackurl()
    {
        return ["flag" => "success", "message" => "Close in app browser"];
    }
}
