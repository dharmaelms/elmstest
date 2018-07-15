<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\PortalBaseController;
use App\Model\SiteSetting;
use App\Services\Report\IReportService;
use Auth;
use Carbon;
use Exception;
use Input;
use App\Services\ScormActivity\IScormActivityService;
use Timezone;

class ReportController extends PortalBaseController
{

    public $reportService;

    /**
     * @var App\Model\ScormActivity\IScormActivityRepository
     */
    private $scorm_activity_service;

    public function __construct(
        IReportService $reportService,
        IScormActivityService $scorm_activity_service
    ) {
    
        $this->reportService = $reportService;
        $this->scorm_activity_service = $scorm_activity_service;
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
    }

    public function getChannelCompletion($channelID = 0, $orderBy = -1)
    {
        try {
            if (!is_numeric($channelID) || $channelID > 0) {
                return response()->json([]);
            }
            $result = $this->reportService->getChannelCompletionTillDate($orderBy);
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([]);
        }
    }

    public function getSpecificChannelCompletion(
        $channelID = 0,
        $orderBy = -1
    ) {
        try {
            if (!is_numeric($channelID) || $channelID <= 0) {
                return response()->json([]);
            }
            $result = $this->reportService->getSpecificChannelCompletionTillDate(
                $channelID,
                $orderBy
            );
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([]);
        }
    }

    public function getChannelPerformanceTillDate(
        $channelId = 0,
        $typeQuizzes = 'all',
        $criteriaQuizzes = 'score',
        $orderBy = -1
    ) {
        try {
            if ($channelId == 0) {
                $result = $this->reportService->getChannelPerformanceTillDate(
                    $typeQuizzes,
                    $criteriaQuizzes,
                    $orderBy
                );
                return response()->json($result);
            } else {
                return redirect('?chart=performance&channel_id=' . $channelId);
            }
        } catch (Exception $e) {
            return response()->json([]);
        }
    }

    public function getSpecificChannelPerformanceTillDate(
        $channelID = 0,
        $typeQuizzes = 'all',
        $criteriaQuizzes = 'score',
        $orderBy = -1
    ) {
        try {
            if (!is_numeric($channelID)) {
                return response()->json([]);
            }
            $result = $this->reportService->getSpecificChannelPerformanceTillDate(
                $channelID,
                $typeQuizzes,
                $criteriaQuizzes,
                $orderBy
            );
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([]);
        }
    }

    public function getDirectQuizPerformanceTillDate(
        $typeQuizzes = 'mock',
        $criteriaQuizzes = 'score',
        $orderBy = -1
    ) {
        try {
            $result = $this->reportService->getDirectQuizPerformanceTillDate(
                $typeQuizzes,
                $criteriaQuizzes,
                $orderBy
            );
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([]);
        }
    }

    public function getChannelPerformanceCSV(
        $channelId = 0,
        $typeQuizzes = 'all',
        $criteriaQuizzes = 'score',
        $timePeriod = 15,
        $orderBy = -1
    ) {
        try {
            $timePeriod = (int)$timePeriod;
            $result = [];
            if ($channelId == 0) {
                $result = $this->reportService->getChannelPerformanceTillDate(
                    $typeQuizzes,
                    $criteriaQuizzes,
                    $orderBy
                );
            } else {
                return redirect('?chart=performance&channel_id=' . $channelId);
            }
            $data = [];

            $data[] = [trans('reports.course_perf')];
            $data[] = [trans('reports.quiz_type') .' : '. $typeQuizzes, trans('reports.quiz_criteria') .' : '. $criteriaQuizzes];
            $header[] = trans('reports.course_name');
            if ($typeQuizzes == 'mock') {
                $header[] = trans('reports.my_scores_wp') . ' ' . $criteriaQuizzes . "(%)";
                $header[] = trans('reports.avg_compl_wp') . ' ' . trans('reports.avg');
            } elseif ($typeQuizzes == 'practice') {
                $header[] = trans('reports.my_practice_score') . $criteriaQuizzes . "(%)";
                $header[] = trans('reports.avg_practice_score') . ' ' . trans('reports.avg');
            } else {
                $header[] = trans('reports.my_scores_wp') . ' ' . $criteriaQuizzes . "(%)";
                $header[] = trans('reports.avg_compl_wp') . ' ' . trans('reports.avg');
                $header[] = trans('reports.my_practice_score') . $criteriaQuizzes . "(%)";
                $header[] = trans('reports.avg_practice_score') . ' ' . trans('reports.avg');
            }

            $data[] = $header;
            if (isset($result['channel_name']) && count($result['channel_name']) >= 1) {
                foreach ($result['channel_name'] as $key => $channelName) {
                    $tempRow = [];
                    $tempRow[] = $channelName;
                    if ($typeQuizzes == 'mock') {
                        $tempRow[] = isset($result['quiz_score'][$key]) ?
                            $result['quiz_score'][$key] : 0;
                        $tempRow[] = isset($result['avg_quiz_scores'][$key]) ?
                            $result['avg_quiz_scores'][$key] : 0;
                    } elseif ($typeQuizzes == 'practice') {
                        $tempRow[] = isset($result['practice_score'][$key]) ?
                            $result['practice_score'][$key] : 0;
                        $tempRow[] = isset($result['avg_practice_scores'][$key]) ?
                            $result['avg_practice_scores'][$key] : 0;
                    } else {
                        $tempRow[] = isset($result['quiz_score'][$key]) ?
                            $result['quiz_score'][$key] : 0;
                        $tempRow[] = isset($result['avg_quiz_scores'][$key]) ?
                            $result['avg_quiz_scores'][$key] : 0;
                        $tempRow[] = isset($result['practice_score'][$key]) ?
                            $result['practice_score'][$key] : 0;
                        $tempRow[] = isset($result['avg_practice_scores'][$key]) ?
                            $result['avg_practice_scores'][$key] : 0;
                    }
                    $data[] = $tempRow;
                }
            } else {
                $data[] = [trans('reports.no_record_found_in_this_combi')];
            }
            if (!empty($data)) {
                $filename = "usercourseperformance.csv";
                $file_pointer = fopen('php://output', 'w');
                header('Content-type: application/csv');
                header('Content-Disposition: attachment; filename=' . $filename);
                foreach ($data as $row) {
                    fputcsv($file_pointer, $row);
                }
                exit;
            }
            exit();
        } catch (Exception $e) {
            exit();
        }
    }

    public function getDirectQuizPerformanceCSV(
        $typeQuizzes = 'all',
        $criteriaQuizzes = 'score',
        $orderBy = -1
    ) {
        try {
            $result = $this->reportService->getDirectQuizPerformanceTillDate(
                $typeQuizzes,
                $criteriaQuizzes,
                $orderBy
            );
            $data = [];
            $data[] = [trans('reports.direct_quiz_perf')];
            $data[] = [trans('reports.quiz_type') .' : '. $typeQuizzes, trans('reports.quiz_criteria') .' : '. $criteriaQuizzes];
            $header[] = trans('reports.quiz_name');
            $header[] = $criteriaQuizzes . "(%)";
            $header[] = trans('reports.avg');
            $data[] = $header;
            if (isset($result['quiz_names']) && count($result['quiz_names']) >= 1) {
                foreach ($result['quiz_names'] as $key => $quizName) {
                    $tempRow = [];
                    $tempRow[] = $quizName;
                    $tempRow[] = isset($result['quiz_scores'][$key]) ?
                        $result['quiz_scores'][$key] : 0;
                    $tempRow[] = isset($result['avg_quiz_scores'][$key]) ?
                        $result['avg_quiz_scores'][$key] : 0;
                    $data[] = $tempRow;
                }
            } else {
                $data[] = [trans('reports.no_record_found_in_this_combi')];
            }
            if (!empty($data)) {
                $filename = "userdirectquizperformance.csv";
                $file_pointer = fopen('php://output', 'w');
                header('Content-type: application/csv');
                header('Content-Disposition: attachment; filename=' . $filename);
                foreach ($data as $row) {
                    fputcsv($file_pointer, $row);
                }
                exit;
            }
            exit();
        } catch (Exception $e) {
            exit();
        }
    }

    public function getSpecificChannelPerformanceCSV(
        $channelID = 0,
        $typeQuizzes = 'all',
        $criteriaQuizzes = 'score',
        $orderBy = -1
    ) {
        try {
            if (!is_numeric($channelID)) {
                return response()->json([]);
            }
            $result = $this->reportService->getSpecificChannelPerformanceTillDate(
                $channelID,
                $typeQuizzes,
                $criteriaQuizzes,
                $orderBy
            );
            $data = [];
            $fileTitle = "Specific " . trans('reports.c_channel') . " Performance";
            $data[] = [$fileTitle];
            $channelTitle = isset($result['channel_name']) ? $result['channel_name'] : '';
            $data[] = [trans('reports.c_channel') . ' Name : ', $channelTitle];
            $data[] = ['Quiz type :', $typeQuizzes];
            $data[] = ['Quiz Criteria : ', $criteriaQuizzes];
            $header[] = trans('reports.quiz_name');
            $header[] = $criteriaQuizzes . "(%)";
            $header[] = trans('reports.avg');
            $data[] = $header;
            if (isset($result['quiz_names']) && count($result['quiz_names']) >= 1) {
                foreach ($result['quiz_names'] as $key => $channelName) {
                    $tempRow = [];
                    $tempRow[] = $channelName;
                    $tempRow[] = isset($result['quiz_scores'][$key]) ?
                        $result['quiz_scores'][$key] : 0;
                    $tempRow[] = isset($result['avg_quiz_scores'][$key]) ?
                        $result['avg_quiz_scores'][$key] : 0;
                    $data[] = $tempRow;
                }
            } else {
                $data[] = [trans('reports.no_record_found_in_this_combi')];
            }
            if (!empty($data)) {
                $filename = "specificCoursePerformance.csv";
                $file_pointer = fopen('php://output', 'w');
                header('Content-type: application/csv');
                header('Content-Disposition: attachment; filename=' . $filename);
                foreach ($data as $row) {
                    fputcsv($file_pointer, $row);
                }
                exit;
            }
            exit();
        } catch (Exception $e) {
            exit();
        }
    }

    public function getChannelCompletionCSV($channelID = 0, $orderBy = 'desc')
    {
        try {
            if (!is_numeric($channelID) || $channelID > 0) {
                $result = [];
            }
            $result = $this->reportService->getChannelCompletionTillDate($orderBy);
            $header[] = trans('reports.course_name');
            $header[] = trans('reports.my_compl');
            $header[] = trans('reports.avg_compl');
            $filename = "usercoursecompletion.csv";
            $file_pointer = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . $filename);
            fputcsv($file_pointer, [trans('reports.course_compl')]);
            fputcsv($file_pointer, $header);
            if (isset($result['channel_name']) && count($result['channel_name']) >= 1) {
                foreach ($result['channel_name'] as $key => $channelName) {
                    $tempRow = [];
                    $tempRow[] = $channelName;
                    $tempRow[] = isset($result['channel_completion_compl'][$key]) ?
                        $result['channel_completion_compl'][$key] : 0;
                    $tempRow[] = isset($result['avg_channel_completion_compl'][$key]) ?
                        $result['avg_channel_completion_compl'][$key] : 0;
                    fputcsv($file_pointer, $tempRow);
                }
            } else {
                fputcsv($file_pointer, [trans('reports.no_record_found_in_this_combi')]);
            }
            fclose($file_pointer);
            exit();
        } catch (Exception $e) {
            exit();
        }
    }

    public function getSpecificChannelCompletionCSV($channelID = 0, $orderBy = -1)
    {
        try {
            if (!is_numeric($channelID) || $channelID <= 0) {
                $result = [];
            }
            $result = $this->reportService->getSpecificChannelCompletionTillDate(
                $channelID,
                $orderBy
            );
            $filename = "userSpecificCourseCompletion.csv";
            $file_pointer = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . $filename);
            $fileTitle = "Specific " . trans('reports.c_channel') . " Performance";
            $channelTitle = html_entity_decode(array_get($result, 'channel_name', ''));
            fputcsv($file_pointer, [trans('reports.c_channel') . ' Name : ', $channelTitle]);
            $header[] = trans('reports.post_names');
            $header[] = trans('reports.my_compl');
            $header[] = trans('reports.avg_compl');
            fputcsv($file_pointer, $header);
            if (isset($result['post_names']) && count($result['post_names']) >= 1) {
                foreach ($result['post_names'] as $key => $postName) {
                    $tempRow = [];
                    $tempRow[] = $postName;
                    $tempRow[] = isset($result['post_completion'][$key]) ?
                        $result['post_completion'][$key] : 0;
                    $tempRow[] = isset($result['avg_post_completion'][$key]) ?
                        $result['avg_post_completion'][$key] : 0;
                    fputcsv($file_pointer, $tempRow);
                }
            } else {
                fputcsv($file_pointer, [trans('reports.no_record_found_in_this_combi')]);
            }
            fclose($file_pointer);
            exit();
        } catch (Exception $e) {
            exit();
        }
    }

    public function getAjaxScormReports($start, $limit)
    {
        if (SiteSetting::module('General', 'scorm_reports') != 'on') {
            return response()->json([]);
        }
        $scorm_details = $this->scorm_activity_service->getScormDetailsForPortal(Auth::user()->uid, $start, $limit);
            return response()->json($scorm_details);
    }

    public function getExportScormReports()
    {
        $scorm_details = $this->scorm_activity_service->getScormDetailsForPortal(Auth::user()->uid);
        $filename = "ScormReports.csv";
        $file_pointer = fopen('php://output', 'w');
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename=' . $filename);
        $data = [];
        if (!empty($scorm_details)) {
            $header[] = trans('reports.scorm_name');
            $header[] = trans('reports.status');
            $header[] = trans('reports.time_spent');
            $header[] = trans('reports.scores');
            $data[] = $header;
            $data = array_merge($data, $scorm_details);
            foreach ($data as $row) {
                fputcsv($file_pointer, $row);
            }
        } else {
            $data[] =  trans('reports.no_more_records');
            fputcsv($file_pointer, $data);
        }
        fclose($file_pointer);
        exit;
    }
}
