<?php

namespace App\Services\Report;

use App\Model\DimensionChannelUserQuiz;
use App\Model\DimensionUser;
use App\Model\DirectQuizPerformanceByIndividualQuestion;
use App\Model\DirectQuizPerformanceByIndividualQuestionSummary;
use App\Model\OverAllChannelAnalytic;
use App\Model\OverAllQuizPerformance;
use App\Model\QuizPerformanceByIndividualQuestion;
use App\Model\QuizPerformanceByIndividualQuestionSummary;
use App\Model\QuizPerformanceTillDate;
use App\Model\Quiz\IQuizRepository;
use App\Model\QuizAttempt\Repository\IQuizAttemptRepository;
use App\Model\QuizAttemptData\Repository\IQuizAttemptDataRepository;
use Config;
use Exception;
use Log;

/**
 * Class FactTblService
 * @package App\Services\Report
 */
class FactTblService implements IFactTblService
{
    /**
     * @var IMongoBulkInUpService
     */
    private $mongo_service;

    /**
     * @var IQuizAttemptRepository
     */
    private $quiz_attempt_repo;

    /**
     * @var IQuizAttemptDataRepository
     */
    private $attempt_data_repo;

    /**
     * @var IQuizRepository
     */
    private $quiz_repository;

    /**
     * @param IMongoBulkInUpService      $mongo_service
     * @param IQuizAttemptRepository     $quiz_attempt_repo
     * @param IQuizAttemptDataRepository $attempt_data_repo
     * @param IQuizRepository            $quiz_repository
     */
    public function __construct(
        IMongoBulkInUpService $mongo_service,
        IQuizAttemptRepository $quiz_attempt_repo,
        IQuizAttemptDataRepository $attempt_data_repo,
        IQuizRepository $quiz_repository
    ) {

        $this->mongo_service = $mongo_service;
        $this->quiz_attempt_repo = $quiz_attempt_repo;
        $this->attempt_data_repo = $attempt_data_repo;
        $this->quiz_repository = $quiz_repository;
    }

    /**
     *{@inheritdoc}
     */
    public function quizPerformanceByQuestion($startDate, $endDate)
    {
        $cronId = $this->mongo_service->cronLog('quiz performance by question', 'start');
        try {
            $res_test = $this->quiz_attempt_repo->getAttemptDetailsByTime($startDate, $endDate);
            if (empty($res_test)) {
                Log::info('No quiz attempts made those days');
            }
            $filtered = collect();
            $res_test->filter(function ($item) use (&$filtered) {
                $filtered->put($item->user_id.'_'.$item->quiz_id, $item);
            });
            $user_ids = $filtered->lists('user_id')->unique()->toArray();
            $quiz_ids = $filtered->lists('quiz_id')->unique()->toArray();
            $res = DimensionChannelUserQuiz::getChannelDetailsByQuizUser(
                $user_ids,
                $quiz_ids
            );
            $users_details = DimensionUser::getDetailsByUids($user_ids)->keyBy('user_id');
            foreach ($res as $channels_details) {
                foreach ($filtered as $key => $attempt_details) {
                    $user_id = (int)array_get(explode('_', $key), 0, 0);
                    $quiz_id = (int)array_get(explode('_', $key), 1, 0);
                    if (is_null($channels_details->user_ids) ||
                        is_null($channels_details->quiz_ids) ||
                        !in_array($user_id, $channels_details->user_ids) ||
                        !in_array($quiz_id, $channels_details->quiz_ids)) {
                        continue;
                    }
                    $data = [];
                    $attempt_id = $attempt_details->attempt_id;
                    $data['user_name'] = array_get($users_details->get($user_id), 'user_name', '');
                    $data['attempt_id'] = $attempt_id;
                    $data['channel_id'] = $channels_details->channel_id;
                    $data['user_id'] = $user_id;
                    $data['quiz_id'] = $quiz_id;
                    $total_mark = array_get($attempt_details, 'total_mark', 1);
                    $data['time_taken'] = $attempt_details->completed_on->diffForHumans($attempt_details->started_on, true);
                    $data['time_diff'] = $attempt_details->completed_on->timestamp - $attempt_details->started_on->timestamp;
                    $data['score'] = round(($attempt_details->obtained_mark / $total_mark) * 100);
                    $data['mark'] = $attempt_details->obtained_mark;
                    $attempt_data_set = $this->attempt_data_repo->getAttemptData($attempt_id)->toArray();
                    $temp_ques = [];
                    $temp_ques_title = [];
                    foreach ($attempt_data_set as $attempt_data) {
                        $temp_ques[$attempt_data['question_id']] = isset($attempt_data['answer_status']) ? $attempt_data['answer_status'] : '';
                        $temp_ques_title[$attempt_data['question_id']] = isset($attempt_data['question_text']) ? $attempt_data['question_text'] : 'no text';
                    }
                    $data['ques_ans_status'] = $temp_ques;
                    $data['ques_text'] = $temp_ques_title;
                    QuizPerformanceByIndividualQuestion::getInsertData($data);
                }
            }
            Log::info('End quiz performance by individual question completed');
            $this->mongo_service->cronLog('quiz performance by individual question', 'success', $cronId);
        } catch (Exception $e) {
            Log::error('quiz performance by individual question: '.$e->getMessage());
            $this->mongo_service->cronLog(
                'quiz performance by individual question',
                'failed: '.$e->getMessage(),
                $cronId
            );
        }
    }

    /**
     *{@inheritdoc}
     */
    public function quizPerformanceByQuestionSummary($startDate, $endDate)
    {
        $cronId = $this->mongo_service->cronLog('quiz performance by individual question summary', 'start');
        try {
            $data = [];
            $channel_id = 0;
            $bulkUpdate = [];
            $res_test = $this->quiz_attempt_repo->getAttemptDetailsByTime($startDate, $endDate);
            if (empty($res_test)) {
                Log::info('No quiz attempts made those days');
            }
            $filtered = collect();
            $res_test->filter(function ($item) use (&$filtered) {
                $filtered->put($item->user_id.'_'.$item->quiz_id, $item);
            });
            $user_ids = $filtered->lists('user_id')->unique();
            $quiz_ids = $filtered->lists('quiz_id')->unique();
            $res = DimensionChannelUserQuiz::getChannelDetailsByQuizUser(
                $user_ids,
                $quiz_ids
            );
            $all_quiz_details = QuizPerformanceByIndividualQuestion::getDetailsByQuizIds($quiz_ids)
                                ->groupBy('quiz_id');
            foreach ($all_quiz_details as $quiz_id => $each_quiz_detail) {
                $grouped_channels = $each_quiz_detail->groupBy('channel_id');
                foreach ($grouped_channels as $channel_id => $quiz_details) {
                    $temp_quiz_ary = [];
                    $data['channel_id'] = $channel_id;
                    $data['quiz_id'] = $quiz_id;
                    $no_of_user = count($quiz_details) >=1 ? count($quiz_details): 1;
                    $avgscore = 0;
                    $avg_time = 0;
                    $avgmark = 0;
                    if (!empty($quiz_details)) {
                        foreach ($quiz_details as $quiz_detail) {
                            foreach ($quiz_detail['ques_ans_status'] as $key => $ans) {
                                if (array_key_exists($key, $temp_quiz_ary)) {
                                    $temp_quiz_ary[$key] = (int)$temp_quiz_ary[$key] + (($ans == 'CORRECT') ? 1 : 0);
                                } else {
                                    $temp_quiz_ary[$key] = (($ans == 'CORRECT') ? 1 : 0);
                                }
                            }
                            $avgscore += (int)$quiz_detail['score'];
                            $avgmark += (int)$quiz_detail['mark'];
                            $avg_time += (int)$quiz_detail['time_diff'];
                        }
                        foreach ($temp_quiz_ary as $key1 => $ans_score) {
                            $temp_quiz_ary[$key1] = round(($ans_score / $no_of_user) * 100);
                        }
                        $data['avgmark'] = round(($avgmark / $no_of_user), 2);
                        $avgscore = round(($avgscore / $no_of_user));
                        $avg_time = round(($avg_time / $no_of_user));
                        $data['ques_ans_details'] = $temp_quiz_ary;
                        $data['avgscore'] = $avgscore;
                        $data['time_diff'] = $avg_time;
                        $data['update_date'] = time();
                        $bulkUpdate[] = [
                            ['channel_id' => (int) $channel_id, 'quiz_id' => (int) $quiz_id],
                            ['$set' => $data],
                            ['multi' => false, 'upsert' => true]
                        ];
                        if (count($bulkUpdate) > (int)Config::get('app.bulk_insert_limit')) {
                            $res = $this->mongo_service->mongoBulkUpdateProcess(
                                $bulkUpdate,
                                'quiz_performance_by_individual_question_summary'
                            );
                            if ($res) {
                                $bulkUpdate = [];
                            }
                        }
                    }
                }
            }
            if (count($bulkUpdate) >= 1) {
                $this->mongo_service->mongoBulkUpdateProcess(
                    $bulkUpdate,
                    'quiz_performance_by_individual_question_summary'
                );
            }
            $this->mongo_service->cronLog(
                'quiz performance by individual question summary',
                'success',
                $cronId
            );
        } catch (Exception $e) {
            Log::error('quiz performance by individual question summary: '.$e->getMessage());
            $this->mongo_service->cronLog('quiz performance by individual question summary', 'failed: '.$e->getMessage(), $cronId);
        }
    }

    /**
     *{@inheritdoc}
     */
    public function directQuizPerformanceByQuestion($startDate, $endDate)
    {
        $cronId = $this->mongo_service->cronLog('Direct Quiz Performance By Question', 'start');
        try {
            $res_test = $this->quiz_attempt_repo->getAttemptDetailsByTime($startDate, $endDate);
            if (empty($res_test)) {
                Log::info('No quiz attempts made those days');
            }
            $filtered = collect();
            $res_test->filter(function ($item) use (&$filtered) {
                $filtered->put($item->user_id.'_'.$item->quiz_id, $item);
            });
            $user_ids = $filtered->lists('user_id')->unique();
            $users_details = DimensionUser::getDetailsByUids($user_ids)->keyBy('user_id');
            foreach ($filtered as $key => $attempt_details) {
                $user_id = (int)array_get(explode('_', $key), 0, 0);
                $quiz_id = (int)array_get(explode('_', $key), 1, 0);
                if (!is_null($attempt_details)) {
                    $data = [];
                    $attempt_id = $attempt_details->attempt_id;
                    $data['user_name'] = array_get($users_details->get($user_id), 'user_name', '');
                    $data['attempt_id'] = $attempt_id;
                    $data['user_id'] = $user_id;
                    $data['quiz_id'] = $quiz_id;
                    $data['time_taken'] = $attempt_details->completed_on->diffForHumans($attempt_details->started_on, true);
                    $data['mark'] = $attempt_details->obtained_mark;
                    $data['score'] = round(
                        (
                            $attempt_details->obtained_mark /
                            ($attempt_details->total_mark >= 1 ? $attempt_details->total_mark : 1)
                        ) * 100
                    );
                    $attempt_data_set = $this->attempt_data_repo->getAttemptData($attempt_id)->toArray();
                    $temp_ques = [];
                    $temp_ques_title = [];
                    foreach ($attempt_data_set as $attempt_data) {
                        $temp_ques[$attempt_data['question_id']] = isset($attempt_data['answer_status']) ? $attempt_data['answer_status'] : '';
                        $temp_ques_title[$attempt_data['question_id']] = isset($attempt_data['question_text']) ? strip_tags($attempt_data['question_text']) : 'No text';
                    }
                    $data['ques_ans_status'] = $temp_ques;
                    $data['ques_text'] = $temp_ques_title;
                    DirectQuizPerformanceByIndividualQuestion::getInsertData($data);
                }
            }
            $this->mongo_service->cronLog('Direct Quiz Performance By Question', 'success', $cronId);
        } catch (Exception $e) {
            Log::error('Direct Quiz Performance By Question: '.$e->getMessage());
            $this->mongo_service->cronLog(
                'Direct Quiz Performance By Question',
                'failed: '.$e->getMessage(),
                $cronId
            );
        }
    }

    /**
     *{@inheritdoc}
     */
    public function directQuizPerformanceByQuestionSummary($startDate, $endDate)
    {
        $cronId = $this->mongo_service->cronLog('Direct Quiz Performance By QuestionSummary', 'start');
        try {
            $bulkUpdate = [];
            $res_test = $this->quiz_attempt_repo->getAttemptDetailsByTime($startDate, $endDate);
            if (empty($res_test)) {
                Log::info('No quiz attempts made those days');
            }
            $filtered = collect();
            $res_test->filter(function ($item) use (&$filtered) {
                $filtered->put($item->user_id.'_'.$item->quiz_id, $item);
            });
            $quiz_ids = $filtered->lists('quiz_id')->unique();
            $group_quiz_details = DirectQuizPerformanceByIndividualQuestion::getDetailsByQuizIds($quiz_ids)
                                ->groupBy('quiz_id');
            foreach ($group_quiz_details as $quiz_id => $quiz_details) {
                $temp_quiz_ary = [];
                $data = [];
                $data['quiz_id'] = $quiz_id;
                $no_of_user = count($quiz_details) >= 1 ? count($quiz_details) : 1;
                $avgscore = 0;
                $avgmark = 0;
                if (!empty($quiz_details)) {
                    foreach ($quiz_details as $quiz_detail) {
                        foreach ($quiz_detail['ques_ans_status'] as $key => $ans) {
                            if (array_key_exists($key, $temp_quiz_ary)) {
                                $temp_quiz_ary[$key] = (int)$temp_quiz_ary[$key] + (($ans == 'CORRECT') ? 1 : 0);
                            } else {
                                $temp_quiz_ary[$key] = (($ans == 'CORRECT') ? 1 : 0);
                            }
                        }
                        $avgscore += (int)$quiz_detail['score'];
                        $avgmark += (int)$quiz_detail['mark'];
                    }
                    foreach ($temp_quiz_ary as $key1 => $ans_score) {
                        $temp_quiz_ary[$key1] = round(($ans_score / $no_of_user) * 100);
                    }
                    $avgscore = round(($avgscore / $no_of_user));
                    $avgmark = round(($avgmark / $no_of_user), 2);
                    $data['ques_ans_details'] = $temp_quiz_ary;
                    $data['avgscore'] = $avgscore;
                    $data['avgmark'] = $avgmark;
                    $data['update_date'] = time();
                    $bulkUpdate[] = [
                        ['quiz_id' => (int) $quiz_id],
                        ['$set' => $data],
                        ['multi' => false, 'upsert' => true]
                    ];
                    if (count($bulkUpdate) > (int)Config::get('app.bulk_insert_limit')) {
                        $res = $this->mongo_service->mongoBulkUpdateProcess(
                            $bulkUpdate,
                            'direct_quiz_performance_by_individual_question_summary'
                        );
                        if ($res) {
                            $bulkUpdate = [];
                        }
                    }
                }
            }
            if (count($bulkUpdate) >= 1) {
                $res = $this->mongo_service->mongoBulkUpdateProcess(
                    $bulkUpdate,
                    'direct_quiz_performance_by_individual_question_summary'
                );
                if ($res) {
                    $bulkUpdate = [];
                }
            }
            $this->mongo_service->cronLog('Direct Quiz Performance By Question Summary', 'success', $cronId);
        } catch (Exception $e) {
            Log::error('Direct Quiz Performance By Question Summary :'.$e->getMessage());
            $this->mongo_service->cronLog('Direct Quiz Performance By Question Summary', 'failed: '.$e->getMessage(), $cronId);
        }
    }

   /**
     *{@inheritdoc}
     */
    public function addQuizTypeForQPTD()
    {
        try {
            $bulkUpdate = [];
            $quizReports = QuizPerformanceTillDate::getQuizPerfWOQtype();
            $quizIds = $quizReports->lists('quiz_id')->all();
            if (empty($quizIds)) {
                return 'No quizzes';
            }
            $quizIds = array_unique($quizIds);
            $quizDetails = $this->quiz_repository->findQuizzesByQuizids($quizIds);
            $quizDetails = $quizDetails->groupBy('quiz_id');
            $quizReports = $quizReports->groupBy('quiz_id');
            foreach ($quizIds as $quizId) {
                $channelQuizPerf['is_practice'] = isset($quizDetails->get($quizId)[0]->practice_quiz) ?
                    $quizDetails->get($quizId)[0]->practice_quiz : false;
                $tempUpdate = [
                    ['quiz_id' => $quizId],
                    ['$set' => $channelQuizPerf],
                    ['multi' => true, 'upsert' => true]
                ];
                $bulkUpdate[] = $tempUpdate;
            }
            if (count($bulkUpdate) >= 1) {
                $this->mongo_service->mongoBulkUpdateProcess($bulkUpdate, 'quiz_performance_till_date');
            }
            return response()->json('successfully updated');
        } catch (Exception $e) {
            return response()->json($e);
        }
    }

    /**
     *{@inheritdoc}
     */
    public function pastQuizPerformanceByAllInChannelTillDate($startDate, $endDate)
    {
        //TODO
        try {
            $recordsCount = 0;
            $result = $this->quiz_attempt_repo->getMaxAttempts($startDate, $endDate);
            if (!empty($result)) {
                $attemptList = $result->pluck('attempt_id')->all();
                $quizList = $result->pluck('_id.quiz_id')->all();
                $groupedDQA = $result->groupBy('attempt_id');
                $quizDetails = $this->quiz_repository->findQuizzesByQuizids($quizList);
                $attemptDatas = $this->attempt_data_repo->getAttemptDataByIds($attemptList);
                $attemptDetails = $this->quiz_attempt_repo->getAttemptDetailsByIds($attemptList);
                if (!empty($attemptDatas) && !empty($attemptDetails) && !empty($quizDetails)) {
                    $attemptDatasGroup = $attemptDatas->groupBy('attempt_id');
                    $attemptDetailsGroup = $attemptDetails->groupBy('attempt_id');
                    $quizDetailsGroup = $quizDetails->groupBy('quiz_id');
                    foreach ($attemptDatasGroup as $attemptId => $lastAttemptData) {
                        if (empty($lastAttemptData)) {
                            continue;
                        }
                        $lastAttemptData = collect($lastAttemptData);
                        $attempt = array_get($attemptDetailsGroup->get($attemptId), '0', []);

                        $score = round(
                            $attempt['obtained_mark'] / ($attempt['total_mark'] > 1 ? $attempt['total_mark'] : 1),
                            2
                        );
                        $totalQuestions = count($attempt['questions']);
                        $attemptQuesId = $lastAttemptData->where('answer_status', '')->pluck('question_id')->all();
                        $attemptQuesIdTemp = $lastAttemptData->where('status', 'NOT_VIEWED')->pluck('question_id')->all();
                        $forSkip = array_unique(array_merge($attemptQuesIdTemp, $attemptQuesId));
                        $correctQuesId = $lastAttemptData->where('answer_status', 'CORRECT')->pluck('question_id')->all();
                        $incorrectQuesId = $lastAttemptData->where('answer_status', 'INCORRECT')->pluck('question_id')->all();
                        if ($totalQuestions < 1) {
                            $totalQuestions = 1;
                        }
                        $forIncorrect = array_diff($incorrectQuesId, $forSkip);
                        $correctPer = round((count($correctQuesId) / $totalQuestions) * 100, 2);
                        $incorrectPer = round((count($forIncorrect) / $totalQuestions) * 100, 2);
                        if (($correctPer + $incorrectPer) > 0) {
                            $accuracy = $correctPer / ($correctPer + $incorrectPer);
                        } else {
                            $accuracy = 0;
                        }
                        if (!is_null($attempt) &&
                            isset($attempt['completed_on']) &&
                            !empty($attempt['completed_on']) &&
                            !is_string($attempt['completed_on'])
                        ) {
                            $secs = $attempt->started_on->diffInSeconds($attempt->completed_on);
                            $speedTotal = $secs / ($totalQuestions > 1 ? $totalQuestions : 1);
                            $days = intval($speedTotal / 86400);
                            $remainder = $speedTotal % 86400;
                            $hrs = intval($remainder / 3600);
                            $remainder = $remainder % 3600;
                            $min = intval($remainder / 60);
                            $remainder = $remainder % 60;
                            $sec = $remainder;
                        } else {
                            $days = 0;
                            $hrs = 0;
                            $min = 0;
                            $sec = 0;
                            $speedTotal = 0;
                        }
                        $min = ($hrs * 60) + $min;
                        $min = ($min >= 10) ? $min : ('0' . $min);
                        $sec = ($sec >= 10) ? $sec : ('0' . $sec);
                        $speed = $min . ':' . $sec;
                        $userQuizId = $groupedDQA->get($attemptId);
                        $userId = $userQuizId[0]['_id']['user_id'];
                        $quizId = $userQuizId[0]['_id']['quiz_id'];
                        $isPractice = $quizDetailsGroup->get($quizId)[0]['practice_quiz'];
                        $data = [
                            'user_id' => (int)$userId,
                            'quiz_id' => (int)$quizId,
                            'is_practice' => $isPractice,
                            'speed' => $speed,
                            'accuracy' => round($accuracy * 100, 2),
                            'score' => round($score * 100, 2),
                            'speed_h' => (int)$hrs,
                            'speed_m' => (int)$min,
                            'speed_s' => (int)$sec,
                            'speed_total' => (int)$speedTotal
                        ];
                        OverAllQuizPerformance::insertData($data);
                        $recordsCount++;
                    }
                }
            }
            return response()->json($recordsCount . ' records successfully updated');
        } catch (Exception $e) {
            return response()->json($e);
        }
    }

    /**
     *{@inheritdoc}
     */
    public function updateOACA()
    {
        try {
            $channel_list = OverAllChannelAnalytic::getIncompleteCertifiedChannelList();
            $channel_ids = array_flatten($channel_list->toArray());
            $channels_data = OverAllChannelAnalytic::getCompleteCertifiedChannelItems($channel_ids);
            $channels_data = array_get($channels_data, 'result', []);
            $bulk_update = [];
            foreach ($channels_data as $channel_data) {
                $bulk_update[] = [
                    [
                        'channel_id' => array_get($channel_data, '_id', 0),
                        'is_certificate_generated' => 1,
                        'completion' => ['$ne' => 100]
                    ],
                    [
                        '$set' => [
                            'item_details' => array_get($channel_data, 'items', []),
                            'post_completion' => array_get($channel_data, 'post_completion', []),
                            'completion' => array_get($channel_data, 'completion', 100),
                            'updated_at' => time()
                        ]
                    ],
                    ['multi' => true, 'upsert' => false]
                ];
            }
            if ($bulk_update >= 1) {
                $res = $this->mongo_service->mongoBulkUpdateProcess($bulk_update, 'over_all_channel_analytics');
                $res = $this->mongo_service->mongoBulkUpdateProcess($bulk_update, 'channel_completion_till_date');
                Log::info(" updateOACA :: " . array_get($res, 'nModified', 0) .' Records updated');
            }
        } catch (Exception $e) {
            Log::error(" updateOACA :: " . $e->getMessage());
        }
    }
}
