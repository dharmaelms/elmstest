<?php

namespace App\Services\Report;

use App\Model\Quiz\IQuizRepository;
use App\Model\Report\ITillQuizPerformanceRepository;
use App\Model\Report\IDimensionChannelUserQuizRepository;
use App\Model\Report\IDimensionUserRepository;
use App\Model\ChannelCompletionTillDate\Repository\IChannelCompletionTillDateRepository;
use App\Model\ChannelAnalytic\Repository\IOverAllChannalAnalyticRepository;
use App\Model\QuizPerformance\Repository\IOverAllQuizPerformanceRepository;
use App\Services\DimensionChannel\IDimensionChannelService;
use App\Services\ChannelCompletionTillDate\IChannelCompletionTillDateService;
use App\Services\Post\IPostService;
use App\Services\Question\IQuestionService;
use App\Services\User\IUserService;
use Auth;
use URL;

/**
 * Class PerformanceReportService
 * @package App\Services\Report
 */
class TillContentReportService implements ITillContentReportService
{
    const CORRECT = 'CORRECT';
    const INCORRECT = 'INCORRECT';
    const START_DATE = 'start_date';
    const END_DATE = 'end_date';
    /**
     * @var IDimensionChannelService
     */
    private $dim_channel_service;

    /**
     * @var IChannelCompletionTillDateService
     */
    private $till_report_service;

    /**
     * @var ITillQuizPerformanceRepository
     */
    private $till_quiz_per_repo;

    /**
     * @var IChannelCompletionTillDateRepository
     */
    private $till_chan_com_repo;

    /**
     * @var IDimensionChannelUserQuizService
     */
    private $dim_cha_quiz_service;

    /**
     * @var IQuizRepository
     */
    private $quiz_repo;

    /**
     * @var IPostService
     */
    private $post_service;

    /**
     * @var IOverAllChannalAnalyticRepository
     */
    private $chnl_analytic_repo;

    /**
     * @var IOverAllQuizPerformanceRepository
     */
    private $quiz_perf_repo;

    /**
     * @var IQuestionService
     */
    private $question_service;

    /**
     * @var IDimensionUserRepository
     */
    private $dim_user_repo;

    /**
     *  @var IQuizPerformanceByIndividualQuestionService
     */
    private $ind_quiz_per_service;
    /**
     * @var IQuizPerformanceByIndividualQuestionSummaryService
     */
    private $ind_quiz_per_sum_service;

    /**
     * @var IDirectQuizPerformanceByIndividualQuestionService
     */
    private $dir_quiz_service;
    /**
     * @var IDirectQuizPerformanceByIndividualQuestionSummaryService
     */
    private $dir_quiz_sum_service;
    /**
     * @var IUserService
     */
    private $user_service;


    public function __construct(
        IDimensionChannelService $dim_channel_service,
        IDimensionUserRepository $dim_user_repo,
        IChannelCompletionTillDateService $till_report_service,
        ITillQuizPerformanceRepository $till_quiz_per_repo,
        IDimensionChannelUserQuizService $dim_cha_quiz_service,
        IChannelCompletionTillDateRepository $till_chan_com_repo,
        IQuizRepository $quiz_repo,
        IPostService $post_service,
        IOverAllChannalAnalyticRepository $chnl_analytic_repo,
        IQuestionService $question_service,
        IOverAllQuizPerformanceRepository $quiz_perf_repo,
        IDirectQuizPerformanceByIndividualQuestionService $dir_quiz_service,
        IDirectQuizPerformanceByIndividualQuestionSummaryService $dir_quiz_sum_service,
        IQuizPerformanceByIndividualQuestionService $ind_quiz_per_service,
        IQuizPerformanceByIndividualQuestionSummaryService $ind_quiz_per_sum_service,
        IUserService $user_service
    ) {
        $this->dim_channel_service = $dim_channel_service;
        $this->dim_user_repo = $dim_user_repo;
        $this->dim_cha_quiz_service = $dim_cha_quiz_service;
        $this->till_report_service = $till_report_service;
        $this->till_quiz_per_repo = $till_quiz_per_repo;
        $this->till_chan_com_repo = $till_chan_com_repo;
        $this->quiz_repo = $quiz_repo;
        $this->question_service = $question_service;
        $this->post_service = $post_service;
        $this->chnl_analytic_repo = $chnl_analytic_repo;
        $this->quiz_perf_repo = $quiz_perf_repo;
        $this->ind_quiz_per_service = $ind_quiz_per_service;
        $this->ind_quiz_per_sum_service = $ind_quiz_per_sum_service;
        $this->dir_quiz_service = $dir_quiz_service;
        $this->dir_quiz_sum_service = $dir_quiz_sum_service;
        $this->user_service = $user_service;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareChannelPerformance(array $channel_ids, $start, $limit)
    {
        $labels = [];
        $values = [];
        $ids = [];
        $quiz_count = [];
        $short_names = [];
        $channel_details = $this->dim_cha_quiz_service->getQuizzesByChannel($channel_ids, $start, $limit);
        $channel_ids = $channel_details->lists('channel_id')->all();
        $result = $this->chnl_analytic_repo->findChannelPerformanceOrComp($channel_ids, 0, false);
        $result = $result->keyBy('_id');
        if (!$channel_details->isEmpty()) {
            foreach ($channel_details as $channel_id => $channel_detail) {
                $each_result = $result->get($channel_id);
                $values[] = round(!is_null(array_get($each_result, 'channel_avg', 0)) ? array_get($each_result, 'channel_avg', 0) : 0, 2) ;
                $ids[] = $channel_id;
                $labels[] = isset($channel_detail->channel_name) ?
                    html_entity_decode($channel_detail->channel_name) : '';
                $short_names[] = isset($channel_detail->short_name) ?
                    html_entity_decode($channel_detail->short_name) : '';
                $quiz_count[] = count(
                    isset($channel_detail->quiz_ids) ?
                        $channel_detail->quiz_ids : []
                );
            }
        }
        return [
            'data' => $values,
            'xaxis' => $labels,
            'id' => $ids,
            'quiz_count' => $quiz_count,
            'short_names' => $short_names
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function prepareIndividualChannelPerformance($channel_id, $start, $limit)
    {
        $labels = [];
        $values = [];
        $ids = [];
        $quiz_results =  $this->quiz_perf_repo->findIndChannelPerformance((int)$channel_id, 0, $start);
        if (!empty($quiz_results)) {
            if ($limit > 0) {
                $quiz_results = $quiz_results->slice(0, $limit);
            }
            $quiz_ids = array_pluck($quiz_results, '_id');
            $quiz_details = $this->quiz_repo->getQuizzes($quiz_ids, 0, $limit, ['quiz_id', 'quiz_name'])->keyBy('quiz_id');
            foreach ($quiz_results as $quiz_result) {
                $quiz_detail = $quiz_details->get(array_get($quiz_result, '_id', 0));
                if (is_null($quiz_detail)) {
                    continue;
                }
                $values[] = round(array_get($quiz_result, 'avg_score', 0), 2);
                $labels[] = html_entity_decode($quiz_detail->quiz_name);
                $ids[] = array_get($quiz_result, '_id', 0);
            }
        }
        return [
            'data' => $values,
            'xaxis' => $labels,
            'id' => $ids
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function prepareChannelCompletion(array $channel_ids, $start, $limit)
    {
        $labels = [];
        $values = [];
        $id_list = [];
        $short_names = [];
        $post_row_count = [];
        $channel_details = collect();
        $channel_details = $this->dim_channel_service->getChannelsFullName('', $channel_ids, $limit, $start, 'channel_id');
        $channel_ids = $channel_details->lists('channel_id')->all();
        $channel_details = $channel_details->keyBy('channel_id');
        $result =  $this->chnl_analytic_repo->findChannelPerformanceOrComp(
            $channel_ids,
            0,
            true
        );
        $result = $result->keyBy('_id');
        if (!$channel_details->isEmpty()) {
            foreach ($channel_details as $channel_id => $channel_detail) {
                $labels[] = str_limit(html_entity_decode(isset($channel_detail->channel_name) ? $channel_detail->channel_name : ''), 245);
                $short_names[] = html_entity_decode(isset($channel_detail->short_name) ?
                    $channel_detail->short_name : '');
                $each_result = $result->get($channel_id);
                $values[] = round(!is_null(array_get($each_result, 'channel_avg', 0)) ? array_get($each_result, 'channel_avg', 0) : 0, 2) ;
                $id_list[] = $channel_id;
                $post_row_count[] =  isset($channel_detail->post_count) ? $channel_detail->post_count : 0;
            }
        }
        return [
            'data' => $values,
            'xaxis' => $labels,
            'short_names' => $short_names,
            'id' => $id_list,
            'post_row_count' => $post_row_count,
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function prepareIndividualChannelCompletion($channel_id, $start, $limit)
    {
        $channel_details = $this->dim_channel_service->getChannelsFullName('', [$channel_id], 0, 'channel_id')->first();
        $result = $labels = $values = [];
        if (!empty($channel_details->post_ids)) {
            $result =  $this->chnl_analytic_repo->findIndChannelCompletion(
                $channel_id,
                $channel_details->post_ids,
                $start,
                $limit
            );
        }
        if (!empty($result)) {
            $post_id_ary = [];
            $temp_post_comp = $result;
            $post_keys = array_keys($result);
            foreach ($post_keys as $post_key) {
                $post_id_ary[] = (int)str_replace('post_', '', $post_key);
            }
            if (!empty($post_id_ary)) {
                $posts = $this->post_service->getPacketsUsingIds($post_id_ary);
                foreach ($posts as $spc_post) {
                    $labels[] = html_entity_decode(array_get($spc_post, 'packet_title', ''));
                    $values[] = round(array_get(
                        $temp_post_comp,
                        'post_' . array_get($spc_post, 'packet_id', 0),
                        0
                    ), 2);
                }
            }
        }
        return [
            'data' => $values,
            'xaxis' => $labels
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDirectQuizPerformance(array $quiz_ids, $start, $limit)
    {
        $values = [];
        $labels = [];
        $ids = [];
        $quiz_results = $this->quiz_perf_repo->findDirectQuizPerformance($quiz_ids, $start, $limit);
        if (!empty($quiz_results)) {
            $quiz_ids = array_pluck($quiz_results, '_id');
            $quiz_details = $this->quiz_repo->getQuizzes($quiz_ids, 0, $limit, ['quiz_id', 'quiz_name'])->keyBy('quiz_id');
            foreach ($quiz_results as $quiz_result) {
                $quiz_detail = $quiz_details->get(array_get($quiz_result, '_id', 0));
                if (is_null($quiz_detail)) {
                    continue;
                }
                $values[] = round(array_get($quiz_result, 'avg_score', 0), 2);
                $labels[] = html_entity_decode($quiz_detail->quiz_name);
                $ids[] = array_get($quiz_result, '_id', 0);
            }
        }
        return [
            'data' => $values,
            'xaxis' => $labels,
            'ids' => $ids
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function prepareUserCompletionAndPerformance(
        array $user_ids,
        array $channel_ids,
        array $order_by,
        $search,
        $start,
        $limit
    ) {
        $user_details = collect();
        $data = [];
        if (array_key_exists('user_name', $order_by) || $search != '') {
            $user_details = $this->dim_user_repo->getUsersByChannelids(
                $channel_ids,
                $order_by,
                $search,
                $start,
                $limit
            );
            $user_ids = $user_details->lists('user_id')->all();
            $result_ovca = $this->chnl_analytic_repo->findUsersPerformanceAndCompletion(
                $user_ids,
                $channel_ids,
                $order_by,
                0,
                $limit
            );
            $result_ovca = $result_ovca->map(function ($item) {
                $item['_id'] = (int)$item['_id'];
                return $item;
            });
            $user_details = $user_details->keyBy('user_id');
            $result_ovca = $result_ovca->keyBy('_id');
            foreach ($user_details as $user_id => $user_detail) {
                $user_result = $result_ovca->get($user_id);
                if (is_null($user_result)) {
                    $user_result = [
                        'performance' => 0,
                        'completion' => 0
                    ];
                }
                $data[] = [
                    $user_detail->user_name,
                    '<a title="User ' . trans('admin/reports.channel') . ' Performance" href="' . URL::to('cp/reports/user-performance-report/' . $user_id) . '">' . round($user_result['performance'], 2) . '</a>',
                    '<a title="User ' . trans('admin/reports.channel') . ' Completion" href="' . URL::to('cp/reports/user-completion-report/' . $user_id) . '">' . round($user_result['completion'], 2) . '</a>'
                ];
            }
        } else {
            $user_ids = $this->dim_user_repo->getChannelsUserIds($channel_ids)->all();
            $result_ovca = $this->chnl_analytic_repo->findUsersPerformanceAndCompletion(
                $user_ids,
                $channel_ids,
                $order_by,
                $start,
                $limit
            );
            $result_ovca = $result_ovca->map(function ($item) {
                $item['_id'] = (int)$item['_id'];
                return $item;
            });
            $filtered_user_ids = $result_ovca->lists('_id')->all();
            if (!empty($filtered_user_ids)) {
                $user_details = $this->dim_user_repo->getUserNameListByids($filtered_user_ids, 0, $limit);
            }
            $user_details = $user_details->keyBy('user_id');
            $result_ovca = $result_ovca->keyBy('_id');

            foreach ($result_ovca as $user_id => $user_result) {
                if ($user_id <= 2) {
                    continue;
                }
                $user_detail = $user_details->get($user_id);
                if (is_null($user_detail)) {
                    continue;
                }
                $data[] = [
                    $user_detail->user_name,
                    '<a title="User ' . trans('admin/reports.channel') . ' Performance" href="' . URL::to('cp/reports/user-performance-report/' . $user_id) . '">' . round($user_result['performance'], 2) . '</a>',
                    '<a title="User ' . trans('admin/reports.channel') . ' Completion" href="' . URL::to('cp/reports/user-completion-report/' . $user_id) . '">' . round($user_result['completion'], 2) . '</a>'
                ];
            }
        }
        $total_count = count($this->chnl_analytic_repo->usersPerformanceCount($user_ids, $channel_ids));
        return [
            'data' => $data,
            'total_count' => $total_count
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function prepareUserChannelPerformance(array $channel_ids, $user_id, $start, $limit)
    {
        $data = [];
        $avg_data = [];
        $titles = [];
        $ids = [];
        $channel_details = $this->dim_cha_quiz_service->getQuizzesByChannel($channel_ids, $start, $limit);
        $channel_ids = $channel_details->lists('channel_id')->all();
        if (!empty($channel_ids)) {
            $avg_result = $this->chnl_analytic_repo->findChannelPerformanceOrComp($channel_ids, 0, false);
            $avg_result = $avg_result->keyBy('_id');
            $user_result = $this->chnl_analytic_repo->findUserChannelPerformanceOrComp(
                $channel_ids,
                (int)$user_id,
                false
            );
            $user_result = $user_result->keyBy('_id');
            foreach ($channel_details as $channel_detail) {
                $channel_id = $channel_detail->channel_id;
                $titles[] = html_entity_decode($channel_detail->channel_name);
                $data[] = round(
                    (!is_null($user_result->get($channel_id)) ? $user_result->get($channel_id)['channel_avg'] : 0),
                    2
                );
                $avg_data[] = round(
                    (!is_null($avg_result->get($channel_id)) ? $avg_result->get($channel_id)['channel_avg'] : 0),
                    2
                );
                $ids[] = $channel_id;
            }
        }
        return [
            'data' => $data,
            'avg_data' => $avg_data,
            'labels' => $titles,
            'ids' => $ids
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function prepareUserChannelCompletion(array $channel_ids, $user_id, $start, $limit)
    {
        $titles = [];
        $data = [];
        $avg_data = [];
        $ids = [];
        $channel_details = collect();
        $channel_details = $this->dim_channel_service->getChannelsFullName('', $channel_ids, $limit, $start, 'channel_id');
        $channel_ids = $channel_details->lists('channel_id')->all();
        $channel_details = $channel_details->keyBy('channel_id');
        $avg_result =  $this->chnl_analytic_repo->findChannelPerformanceOrComp(
            $channel_ids,
            0,
            true
        );
        $avg_result = $avg_result->keyBy('_id');
        $user_result = $this->chnl_analytic_repo->findUserChannelPerformanceOrComp(
            $channel_ids,
            (int)$user_id,
            true
        );
        $user_result = $user_result->keyBy('_id');
        if (!$channel_details->isEmpty()) {
            foreach ($channel_details as $channel_id => $channel_detail) {
                $channel_id = $channel_detail->channel_id;
                $titles[] = html_entity_decode($channel_detail->channel_name);
                $data[] = round(
                    (!is_null($user_result->get($channel_id)) ? $user_result->get($channel_id)['channel_avg'] : 0),
                    2
                );
                $avg_data[] = round(
                    (!is_null($avg_result->get($channel_id)) ? $avg_result->get($channel_id)['channel_avg'] : 0),
                    2
                );
                $ids[] = $channel_id;
            }
        }
        return [
            'data' => $data,
            'avg_data' => $avg_data,
            'labels' => $titles,
            'ids' => $ids
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function prepareUserIndChannelPerformance($channel_id, $user_id, $start, $limit)
    {
        $labels = $values = $avg_values = [];
        $ids = [];
        $avg_results =  $this->quiz_perf_repo->findIndChannelPerformance(
            (int)$channel_id,
            0,
            $start
        );
        $user_results =  $this->quiz_perf_repo->findIndChannelPerformance(
            (int)$channel_id,
            (int) $user_id,
            $start
        );
        $user_results = $user_results->keyBy('_id');
        if (!empty($avg_results)) {
            if ($limit > 0) {
                $avg_results = $avg_results->slice(0, $limit);  
            }
            $quiz_ids = array_pluck($avg_results, '_id');
            $quiz_details = $this->quiz_repo->getQuizzes(
                $quiz_ids,
                0,
                $limit,
                ['quiz_id', 'quiz_name']
            )->keyBy('quiz_id');
            foreach ($avg_results as $avg_result) {
                $quiz_id = array_get($avg_result, '_id', 0);
                $quiz_detail = $quiz_details->get($quiz_id);
                if (is_null($quiz_detail)) {
                    continue;
                }
                $user_result = $user_results->get($quiz_id);
                $avg_values[] = round(array_get($avg_result, 'avg_score', 0), 2);
                $values[] = !is_null($user_result) ?
                    round(array_get($user_result, 'avg_score', 0), 2) : 0;
                $labels[] = html_entity_decode($quiz_detail->quiz_name);
                $ids[] = $quiz_id;
            }
        }
        return [
            'data' => $values,
            'avg_data' => $avg_values,
            'labels' => $labels,
            'ids' => $ids
        ];
    }

     /**
     * {@inheritdoc}
     */
    public function prepareUserIndChannelCompletion($channel_id, $user_id, $start, $limit)
    {
        $channel_details = $this->dim_channel_service->getChannelsFullName(
            '',
            [$channel_id],
            0,
            'channel_id'
        )->first();
        $user_result = $avg_result = $labels = $values = $avg_values = [];
        if (!empty($channel_details->post_ids)) {
            $avg_result =  $this->chnl_analytic_repo->findIndChannelCompletion(
                $channel_id,
                $channel_details->post_ids,
                $start,
                $limit
            );
            $user_result = $this->chnl_analytic_repo->getUserChannelCompletionDetails(
                [(int)$channel_id],
                (int)$user_id
            )->first();
            if (!is_null($user_result)) {
                $user_result = $user_result->post_completion;
            }
        }
        if (!empty($avg_result)) {
            $post_id_ary = [];
            $temp_post_comp = $avg_result;
            $post_keys = array_keys($avg_result);
            foreach ($post_keys as $post_key) {
                $post_id_ary[] = (int)str_replace('post_', '', $post_key);
            }
            if (!empty($post_id_ary)) {
                $posts = $this->post_service->getPacketsUsingIds($post_id_ary);
                foreach ($posts as $spc_post) {
                    $labels[] = html_entity_decode(array_get($spc_post, 'packet_title', ''));
                    $avg_values[] = round(
                        array_get($temp_post_comp, 'post_' . array_get($spc_post, 'packet_id', 0), 0),
                        2
                    );
                    $values[] = round(
                        array_get($user_result, 'p_' . array_get($spc_post, 'packet_id', 0), 0),
                        2
                    );
                }
            }
        }
        return [
            'values' => $values,
            'values_avg' => $avg_values,
            'labels' => $labels
        ];
    }

    /**
     * @inheritdoc
     */
    public function getQuizQuestionsDetails($quiz_id)
    {
        $ques_title = [];
        $ques_ids = [];
        $total_mark = 0;
        $quiz_max_time = 'Unlimited';
        $quiz_name = '';
        $quiz_details = $this->quiz_repo->find($quiz_id);
        if (!is_null($quiz_details) && isset($quiz_details->questions)) {
            $quiz_name = $quiz_details->quiz_name;
            $total_mark = $quiz_details->total_mark;
            $quiz_max_time = $quiz_details->duration <= 0 ? 'unlimited ' : $quiz_details->duration . ' Seconds';
            $question_texts = $this->question_service->getQuestionsText($quiz_details->questions);
            if (!empty($question_texts)) {
                foreach ($question_texts as $question_text) {
                    $ques_title[] = strip_tags($question_text->question_text);
                    $ques_ids[] = $question_text->question_id;
                }
            }
        }
        return [
            'quiz_name' => html_entity_decode($quiz_name),
            'total_mark' => $total_mark,
            'quiz_max_time' => $quiz_max_time,
            'ques_title' => $ques_title,
            'ques_ids' => $ques_ids
        ];
    }

    /**
     * @inheritdoc
     */
    public function prepareQuizPerformanceByQuestion(
        $start,
        $limit,
        $order_by,
        $search_key,
        $quiz_id,
        $channel_id,
        $ques_ids,
        $user_id = 0
    ) {
        $totalRecords = $this->ind_quiz_per_service->getSearchCountChannel($quiz_id, $channel_id);
        $filtered_data = $this->ind_quiz_per_service->getChannelQuizPerformanceWithPagination(
            $start,
            $limit,
            $order_by,
            $search_key,
            $quiz_id,
            $channel_id,
            $user_id
        );
        $dataArr = $temp_avg = $ques_text = [];
        $que_flag = 0;
        foreach ($filtered_data as $quiz_perf) {
            $temp = [
                $quiz_perf->user_name,
                $quiz_perf->mark,
                $quiz_perf->score,
                $quiz_perf->time_taken,
            ];
            if (!empty($ques_ids)) {
                foreach ($ques_ids as $ques_key => $ques_id) {
                    if (array_get($quiz_perf->ques_ans_status, $ques_id, '') == self::CORRECT) {
                        $temp[] = ($limit <= 0) ? self::CORRECT : '&#9989;';
                    } elseif (array_get($quiz_perf->ques_ans_status, $ques_id, '') == self::INCORRECT) {
                        $temp[] = ($limit <= 0) ? self::INCORRECT : '&#xd7';
                    } else {
                        $temp[] = ($limit <= 0) ? 'SKIPPED' : '&#45';
                    }
                    if ($que_flag == 0 && $limit <= 0) {
                        $question_texts = $this->question_service->getQuestionsText($ques_ids)->keyBy('question_id');
                        if ($question_texts->has((int)$ques_id)) {
                            $qes_name = $question_texts->get((int)$ques_id);
                            $qes_name = array_get($qes_name, 'question_text', 'Q ' . $ques_key);
                            $ques_text[] = htmlspecialchars_decode(
                                trim(
                                    preg_replace(
                                        '/[\x00-\x1F\x80-\xFF]/',
                                        '',
                                        strip_tags($qes_name)
                                    )
                                ),
                                ENT_QUOTES
                            );
                        } else {
                            $ques_text[] = 'Q ' . ($ques_key + 1);
                        }
                    }
                }
                $que_flag = 1;
            }
            array_push($dataArr, $temp);
        }
        $avg_data = $this->ind_quiz_per_sum_service->getAvgChannelQuesScore($quiz_id, $channel_id);
        if (!is_null($avg_data)) {
            $temp_avg = [];
            $temp_avg = [
                'Avg',
                $avg_data->avgmark,
                $avg_data->avgscore,
                '',
            ];
            if (!empty($ques_ids)) {
                foreach ($ques_ids as $ques_id) {
                    if (isset($avg_data->ques_ans_details[$ques_id])) {
                        $temp_avg[] = ($limit <= 0) ? $avg_data->ques_ans_details[$ques_id] :
                        '<span title= "' . trans('admin/reports.quiz_avg_tooltip') . '">'
                        . $avg_data->ques_ans_details[$ques_id] . '</span>';
                    } else {
                        $temp_avg[] = ($limit <= 0) ? 0 : '<span title= "' . trans('admin/reports.quiz_avg_tooltip') . '">0</span>';
                    }
                }
            } else {
                foreach ($avg_data->ques_ans_details as $ques_ans_avg) {
                    $temp_avg[] = ($limit <= 0) ? $ques_ans_avg :
                    '<span title= "' . trans('admin/reports.quiz_avg_tooltip') . '">' . $ques_ans_avg . '</span>';
                }
            }
            array_push($dataArr, $temp_avg);
        }
        return [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $dataArr,
            'ques_text' => $ques_text
        ];
    }


    /**
     * @inheritdoc
     */
    public function prepareDirectQuizPerformanceByQues(
        $start,
        $limit,
        $orderByArray,
        $searchKey,
        $quiz_id,
        $ques_ids
    ) {
        $totalRecords = $this->dir_quiz_service->getSearchCountQuizUser($quiz_id);
        $filtered_data = $this->dir_quiz_service->getChannelQuizperformanceWithPagenation(
            $start,
            $limit,
            $orderByArray,
            $searchKey,
            $quiz_id
        );
        $dataArr = [];
        $que_flag = 0;
        $ques_text = [];
        foreach ($filtered_data as $quiz_perf) {
            $temp = [
                $quiz_perf->user_name,
                $quiz_perf->mark,
                $quiz_perf->score,
                $quiz_perf->time_taken,
            ];
            if (!empty($ques_ids)) {
                foreach ($ques_ids as $ques_key => $ques_id) {
                    if (array_get($quiz_perf->ques_ans_status, $ques_id, '') == self::CORRECT) {
                        $temp[] = ($limit <= 0) ? self::CORRECT : '&#9989;';
                    } elseif (array_get($quiz_perf->ques_ans_status, $ques_id, '') == self::INCORRECT) {
                        $temp[] = ($limit <= 0) ? self::INCORRECT : '&#xd7';
                    } else {
                        $temp[] = ($limit <= 0) ? 'SKIPPED' : '&#45';
                    }
                    if ($que_flag == 0 && $limit <= 0) {
                        $question_texts = $this->question_service->getQuestionsText($ques_ids)->keyBy('question_id');
                        if ($question_texts->has((int)$ques_id)) {
                            $qes_name = $question_texts->get((int)$ques_id);
                            $qes_name = array_get($qes_name, 'question_text', 'Q ' . $ques_key);
                            $ques_text[] = htmlspecialchars_decode(
                                trim(
                                    preg_replace(
                                        '/[\x00-\x1F\x80-\xFF]/',
                                        '',
                                        strip_tags($qes_name)
                                    )
                                ),
                                ENT_QUOTES
                            );
                        } else {
                            $ques_text[] = 'Q ' . ($ques_key + 1);
                        }
                    }
                }
                $que_flag = 1;
            }
            $dataArr[] = $temp;
        }
        $avg_data = $this->dir_quiz_sum_service->getAvgChannelQuesScore($quiz_id);
        if (isset($avg_data) && !is_null($avg_data)) {
            $temp_avg = [];
            $temp_avg = [
                'Avg',
                $avg_data->avgmark,
                $avg_data->avgscore,
                '',
            ];
            if (!empty($ques_ids)) {
                foreach ($ques_ids as $ques_id) {
                    if (isset($avg_data->ques_ans_details[$ques_id])) {
                        $temp_avg[] = ($limit <= 0) ? $avg_data->ques_ans_details[$ques_id] :
                        '<span title= "' . trans('admin/reports.quiz_avg_tooltip') . '">'
                        . $avg_data->ques_ans_details[$ques_id] . '</span>';
                    } else {
                        $temp_avg[] = ($limit <= 0) ? 0 :
                        '<span title= "' . trans('admin/reports.quiz_avg_tooltip') . '">0</span>';
                    }
                }
            } else {
                foreach ($avg_data->ques_ans_details as $ques_ans_avg) {
                    $temp_avg[] = ($limit <= 0) ? $ques_ans_avg :
                    '<span title= "' . trans('admin/reports.quiz_avg_tooltip') . '">' . $ques_ans_avg . '</span>';
                }
            }
            if (!empty($temp_avg)) {
                $dataArr[] = $temp_avg;
            }
        }
        return [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $dataArr,
            'ques_text' => $ques_text
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function getChannelNameById($channel_id, $retrieve_short_name = false)
    {
        $channel_detail = $this->dim_channel_service->isExist($channel_id);
        if ($retrieve_short_name) {
            if (isset($channel_detail->short_name) && $channel_detail->short_name != '') {
                $channel_full_name = str_limit($channel_detail->channel_name, (int)config('app.char_limit_dropdown'))
                .'('.str_limit($channel_detail->short_name, (int)config('app.char_limit_dropdown')).')';
            } else {
                $channel_full_name = str_limit($channel_detail->channel_name, (int)config('app.char_limit_dropdown'));
            }
            return $channel_full_name;
        } else {
            return isset($channel_detail->channel_name) ? html_entity_decode($channel_detail->channel_name) : '';
        }
    }
}
