<?php
namespace App\Http\Controllers\Portal;

use App\Exceptions\Quiz\AttemptNotAllowedException;
use App\Exceptions\Quiz\QuizNotFoundException;
use App\Http\Controllers\AjaxBaseController;
use App\Services\QuizAttempt\IQuizAttemptService;
use Auth;
use Illuminate\Http\Request;
use Log;

/**
 * Class QuizAttemptController
 *
 * @package App\Http\Controllers\Portal
 */
class QuizAttemptController extends AjaxBaseController
{
    /**
     * @var \App\Services\QuizAttempt\IQuizAttemptService
     */
    private $attempt_service;

    /**
     * QuizAttemptController constructor.
     * @param IQuizAttemptService $attempt_service
     */
    public function __construct(IQuizAttemptService $attempt_service)
    {
        $this->attempt_service = $attempt_service;
    }

    /**
     * Method to get attempt id for quiz
     * @param  int $quiz_id quiz primary key
     * @return Response
     */
    public function postStartAttempt($quiz_id)
    {
        try {
            $attempt_id = $this->attempt_service->createAttempt($quiz_id);
            return ['status' => true, 'attempt_id' => $attempt_id];
        } catch (QuizNotFoundException $e) {
            Log::info($quiz_id . ' not found');
            $message = $e->getMessage();
        } catch (AttemptNotAllowedException $e) {
            Log::info('Attempt not allowed');
            $message = $e->getMessage();
        }
        return ['status' => false, 'message' => $message];
    }

    /**
     * Method to save answer
     *
     * @param int $attempt_id
     * @return  Response
     */
    public function postSaveAnswer(Request $request, $attempt_id)
    {
        try {
            $attempt = $this->attempt_service->saveAnswer($request, $attempt_id);
            return ['status' => true, 'attempt' => $attempt];
        } catch (QuizAttemptClosedException $e) {
            Log::error("error in saving answer" . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Method to get next question
     *
     * @param int $attempt_id
     * @return  Response
     */
    public function postNextQuestion(Request $request, $attempt_id)
    {
        try {
            $question = $this->attempt_service->nextQuestion($attempt_id, $request->input('page'), $request->input('section'));
            return ['status' => true, 'question' => $question];
        } catch (QuizAttemptClosedException $e) {
            Log::error("error in saving answer \n" . $e);
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
