<?php
namespace App\Services\Report;

use App\Model\ChannelCompletionByAll;
use App\Model\ChannelCompletionTillDate;
use App\Model\DimensionChannel;
use App\Model\DimensionDirectUserQuizTillDate;
use App\Model\DimensionQuiz;
use App\Model\DimensionUser;
use App\Model\FactChannelUserQuiz;
use App\Model\Packet;
use App\Model\QuizPerformanceTillDate;

use App\Model\Report\IDimensionUserRepository;
use App\Model\Report\IDimensionChannelUserQuizRepository;
use App\Model\QuizPerformance\Repository\IOverAllQuizPerformanceRepository;
use App\Model\Quiz\IQuizRepository;
use Auth;
use Carbon;
use Exception;

/**
 * Class ReportService
 * @package App\Services\Report
 */
class ReportService implements IReportService
{
    private $content_report_serv;

    private $dim_user_repo;

    private $dim_channel_user_quiz_repo;

    private $quiz_performance_repo;

    private $quiz_reporsitory;
    
    /**
     * ReportService constructor.
     */
    public function __construct(
        ITillContentReportService $content_report_serv,
        IDimensionUserRepository $dim_user_repo,
        IDimensionChannelUserQuizRepository $dim_channel_user_quiz_repo,
        IOverAllQuizPerformanceRepository $quiz_performance_repo,
        IQuizRepository $quiz_reporsitory
    ) {
        $this->content_report_serv = $content_report_serv;
        $this->dim_user_repo = $dim_user_repo;
        $this->dim_channel_user_quiz_repo = $dim_channel_user_quiz_repo;
        $this->quiz_performance_repo = $quiz_performance_repo;
        $this->quiz_reporsitory = $quiz_reporsitory;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelPerformanceTillDate(
        $typeQuizzes,
        $criteria,
        $orderBy
    ) {
        try {
            if (Auth::check()) {
                $user_id = Auth::user()->uid;
                $user_details = $this->dim_user_repo->getSpecificUserDetail($user_id);
                $channel_ids = array_get($user_details, 'channel_ids', []);
                if (!empty($channel_ids) || is_admin_role(Auth::user()->role)) {
                    $channel_user_quiz = $this->dim_channel_user_quiz_repo->getQuizzesByChannel($channel_ids, 0, 0);
                    $quiz_ids = $channel_user_quiz->lists('quiz_ids')->flatten()->all();
                    if (!empty($quiz_ids)) {
                        $users_practice = $users_mock = $avg_practice = $avg_mock = collect();
                        if ($typeQuizzes == 'all' || $typeQuizzes == 'practice') {
                            $users_practice = $this->processChannelPerformance(
                                $user_id,
                                $quiz_ids,
                                $channel_user_quiz,
                                $criteria,
                                true
                            );
                        }
                        if ($typeQuizzes == 'all' || $typeQuizzes == 'mock') {
                            $users_mock = $this->processChannelPerformance(
                                $user_id,
                                $quiz_ids,
                                $channel_user_quiz,
                                $criteria,
                                false
                            );
                        }
                        if ($typeQuizzes == 'all' || $typeQuizzes == 'practice') {
                            $avg_practice = $this->processChannelPerformance(
                                0,
                                $quiz_ids,
                                $channel_user_quiz,
                                $criteria,
                                true
                            );
                        }
                        if ($typeQuizzes == 'all' || $typeQuizzes == 'mock') {
                            $avg_mock = $this->processChannelPerformance(
                                0,
                                $quiz_ids,
                                $channel_user_quiz,
                                $criteria,
                                false
                            );
                        }
                        $msgAjax = [];
                        foreach ($channel_user_quiz as $channel) {
                            $msgAjax['channel_name'][] = html_entity_decode($channel->channel_name);
                            $msgAjax['quiz_score'][] = array_get($users_mock->get($channel->channel_id), 'avg_score', 0);
                            $msgAjax['avg_quiz_scores'][] = array_get($avg_mock->get($channel->channel_id), 'avg_score', 0);
                            $msgAjax['practice_score'][] = array_get($users_practice->get($channel->channel_id), 'avg_score', 0);
                            $msgAjax['avg_practice_scores'][] = array_get($avg_practice->get($channel->channel_id), 'avg_score', 0);
                            $msgAjax['ids'][] = $channel->channel_id;
                        }
                        return $msgAjax;
                    }
                }
            } else {
                return [];
            }
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSpecificChannelPerformanceTillDate(
        $channelID,
        $typeQuizzes,
        $criteria,
        $orderBy
    ) {

        if (!is_numeric($channelID)) {
            return [];
        }
        $channelID = (int)$channelID;
        try {
            $user_id = Auth::user()->uid;
            $channel_user_quiz = $this->dim_channel_user_quiz_repo->getQuizzesByChannel([$channelID], 0, 0);
            $quiz_ids = $channel_user_quiz->lists('quiz_ids')->flatten()->all();
            if (!empty($quiz_ids)) {
                $msgAjax = $this->processQuizzesPerformance($quiz_ids, $user_id, $typeQuizzes, $criteria);
                $msgAjax['title'] = trans('reports.indiv_course_perf', ['course_name' => $channel_user_quiz->first()->channel_name]);
                $msgAjax['channel_name'] = $channel_user_quiz->first()->channel_name;
                return $msgAjax;
            }
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDirectQuizPerformanceTillDate(
        $typeQuizzes,
        $criteria,
        $orderBy
    ) {
        try {
            if (Auth::check()) {
                $user_id = Auth::user()->uid;
                $user_details = $this->dim_user_repo->getSpecificUserDetail($user_id);
                $quiz_ids = array_get($user_details, 'quiz_ids', []);
                if (!empty($quiz_ids)) {
                    return $this->processQuizzesPerformance($quiz_ids, $user_id, $typeQuizzes, $criteria);
                }
            }
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelCompletionTillDate($orderBy = -1)
    {
        try {
            $userId = 0;
            if (Auth::check()) {
                $userId = Auth::user()->uid;
            } else {
                return [];
            }
            $usersChannels = DimensionUser::where('user_id', '=', (int)$userId)->get();
            $usersChannels = isset($usersChannels[0]) ? $usersChannels[0] : [];
            $channelIdsR = isset($usersChannels->channel_ids) ? $usersChannels->channel_ids : [];
            $result = [];
            $channelIdsR = array_map('intval', array_unique($channelIdsR));
            if ($userId > 0 && !empty($channelIdsR)) {
                $result = $this->content_report_serv->prepareUserChannelCompletion($channelIdsR, $userId, 0, 0);
            }
            return [
                'title' => trans('reports.user_channel_compl'),
                'channel_completion_compl' => array_get($result, 'data', []),
                'avg_channel_completion_compl' => array_get($result, 'avg_data', []),
                'channel_name' => array_get($result, 'labels', []),
                'id' => array_get($result, 'ids', [])
            ];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSpecificChannelCompletionTillDate($channelID = 0, $orderBy = -1)
    {
        try {
            $userId = 0;
            if (Auth::check()) {
                $userId = Auth::user()->uid;
            } else {
                return false;
            }
            $result = [];
            $channelID = intval($channelID);
            $result = $this->content_report_serv->prepareUserIndChannelCompletion($channelID, $userId, 0, 0);
            $channelDetails = DimensionChannel::isExist($channelID);
            $channelName = isset($channelDetails->channel_name) ? $channelDetails->channel_name : '';
            $title = trans('reports.indiv_course_comp', ['course_name' => $channelName]);
            $finalOutput = [
                'title' => $title,
                'post_completion' => array_get($result, 'values', []),
                'avg_post_completion' => array_get($result, 'values_avg', []),
                'post_names' => array_get($result, 'labels', []),
                'id' => array_get($result, 'ids', []),
                'channel_name' => $channelName
            ];
            return $finalOutput;
        } catch (Exception $e) {
            return [];
        }
    }

    public function processChannelPerformance($user_id, $quiz_ids, $channel_user_quiz, $criteria, $is_practice)
    {
        $quiz_results = $this->quiz_performance_repo->findUserQuizzesPerformance(
            $user_id,
            $quiz_ids,
            $is_practice,
            $criteria
        );
        $quiz_result = $quiz_results->keyBy('quiz_id');
        $channel_quiz_mapper = collect();
        $channel_user_quiz->map(function ($item) use ($quiz_result, &$channel_quiz_mapper) {
            $channel_quizzes_result = $quiz_result->whereIn('quiz_id', $item->quiz_ids);
            $channel_quiz_mapper[$item->channel_id] = [
                'avg_score' => round($channel_quizzes_result->avg('avg'), 2),
                'avg_speed' => round($channel_quizzes_result->avg('speed'), 2)
            ];
        });
        return $channel_quiz_mapper;
    }

    public function processIndChannelperformance($user_id, $quiz_ids, $criteria, $is_practice)
    {
        $quiz_results = $this->quiz_performance_repo->findUserQuizzesPerformance(
            $user_id,
            $quiz_ids,
            $is_practice,
            $criteria
        );
        return $quiz_results->keyBy('quiz_id');
    }

    public function processQuizzesPerformance($quiz_ids, $user_id, $typeQuizzes, $criteria)
    {
        $msgAjax = [];
        if (!empty($quiz_ids)) {
            $users_practice = $users_mock = $avg_practice = $avg_mock = collect();
            if ($typeQuizzes == 'all' || $typeQuizzes == 'practice') {
                $users_practice = $this->processIndChannelperformance($user_id, $quiz_ids, $criteria, true);
            }
            if ($typeQuizzes == 'all' || $typeQuizzes == 'mock') {
                $users_mock = $this->processIndChannelperformance(0, $quiz_ids, $criteria, false);
            }
            if ($typeQuizzes == 'all' || $typeQuizzes == 'practice') {
                $avg_practice = $this->processIndChannelperformance($user_id, $quiz_ids, $criteria, true);
            }
            if ($typeQuizzes == 'all' || $typeQuizzes == 'mock') {
                $avg_mock = $this->processIndChannelperformance(0, $quiz_ids, $criteria, false);
            }
            $quiz_details = $this->quiz_reporsitory->findQuizzesByQuizids($quiz_ids, ['quiz_id' => 'DESC'])->keyBy('quiz_id');
            foreach ($quiz_details as $quiz_id => $quiz_detail) {
                $msgAjax['quiz_names'][] = html_entity_decode($quiz_detail->quiz_name);
                $msgAjax['ids'][] = $quiz_id;
                if (!is_null(array_get($avg_mock->get($quiz_id), 'avg'))) {
                    $msgAjax['avg_quiz_scores'][] = round(array_get($avg_mock->get($quiz_id), 'avg'), 2);
                    $msgAjax['quiz_scores'][] = round(array_get($users_mock->get($quiz_id), 'avg', 0), 2);
                } elseif (!is_null(array_get($avg_practice->get($quiz_id), 'avg'))) {
                    $msgAjax['avg_quiz_scores'][] = round(array_get($avg_practice->get($quiz_id), 'avg'), 2);
                    $msgAjax['quiz_scores'][] = round(array_get($users_practice->get($quiz_id), 'avg', 0), 2);
                } else {
                    $msgAjax['avg_quiz_scores'][] = 0;
                    $msgAjax['quiz_scores'][] = 0;
                }
            }
        }
        return $msgAjax;
    }
}
