<?php

namespace App\Http\Middleware;

use App\Model\QuizAttempt;
use App\Model\QuizAttemptData;
use Carbon\Carbon;
use Closure;
use Log;
use Session;
use Exception;


class QuizMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            if (Session::has("quiz_attempt_id")) {
                $questionAttemptData = null;
                $quizAttemptId = Session::get("quiz_attempt_id");
                $questionId = Session::get("question_id");
                $quizAttempt = QuizAttempt::getAttemptById($quizAttemptId);
                $questionAttemptData = QuizAttemptData::getQuestionAttemptData($quizAttempt->attempt_id, $questionId);

                if (isset($questionAttemptData) && isset($questionAttemptData->history) && is_array($questionAttemptData->history) && !empty($questionAttemptData->history)) {
                    $tmpQuestionAttemptHistory = $questionAttemptData->history;
                    $lastAction = end($tmpQuestionAttemptHistory);
                    $lastActionKey = key($tmpQuestionAttemptHistory);
                    if ($lastAction["status"] === "STARTED") {
                        $tmpQuestionAttemptHistory[$lastActionKey + 1] = [
                            "status" => "VIEWED",
                            "time" => Carbon::now()->timestamp
                        ];
                        $questionAttemptData->history = $tmpQuestionAttemptHistory;

                        $timeTakenInSeconds = (int)$tmpQuestionAttemptHistory[$lastActionKey + 1]["time"] - (int)$lastAction["time"];
                        if (isset($questionAttemptData->time_spend) && is_array($questionAttemptData->time_spend)) {
                            $tmpTimeSpentArray = $questionAttemptData->time_spend;
                            $tmpTimeSpentArray[] = $timeTakenInSeconds;
                            $questionAttemptData->time_spend = $tmpTimeSpentArray;
                        } else {
                            $questionAttemptData->time_spend = [$timeTakenInSeconds];
                        }

                        $questionAttemptData->save();
                    }
                }
            }
        } catch (Exception $e) {
            Log::error("Error message:" . $e->getMessage() . "|File Path:" . $e->getFile() . "|" . $e->getLine());
        } finally {
            return $next($request);
        }
    }
}
