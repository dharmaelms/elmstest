<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminBaseController;
use App\Services\Report\IDimensionTblService;
use App\Services\Report\IFactTblService;
use Carbon;
use DB;
use Exception;
use Log;

/**
 * Class ReportTblPopulateController
 * @package app\Http\Controllers\Admin
 */
class ReportTblPopulateController extends AdminBaseController
{
    /**
     * @var IDimensionTblService
     */
    public $dimensionTblService;

    /**
     * @var IFactTblService
     */
    public $factTblService;

    /**
     * ReportTblPopulateController constructor.
     * @param IDimensionTblService $dimensionTblService
     * @param IFactTblService $factTblService
     */
    public function __construct(
        IDimensionTblService $dimensionTblService,
        IFactTblService $factTblService
    ) {
        DB::enableQueryLog();
        $this->dimensionTblService = $dimensionTblService;
        $this->factTblService = $factTblService;
    }
   
    /**
     *
     */
    public function cronDailyReportsTblPopulate()
    {
        try {
            set_time_limit(3600);
            $second = Carbon::today();
            $first = Carbon::yesterday();
            Log::info('Cron - started daily cron populate reports tables');
            $this->dimensionTblService->dimensionUser();
            $this->dimensionTblService->dimensionChannel();
            $this->dimensionTblService->dimensionChannelUserQuiz();
            $this->dimensionTblService->dimensionAnnouncement($first->timestamp, $second->timestamp);
            $this->factTblService->quizPerformanceByQuestion($first->timestamp, $second->timestamp);
            $this->factTblService->quizPerformanceByQuestionSummary($first->timestamp, $second->timestamp);
            $this->factTblService->directQuizPerformanceByQuestion($first->timestamp, $second->timestamp);
            $this->factTblService->directQuizPerformanceByQuestionSummary($first->timestamp, $second->timestamp);
            Log::info('Cron - Successfully done, daily cron populate reports tables');
        } catch (Exception $e) {
            Log::info('Cron - Failed, daily cron populate reports tables' . $e);
        }
    }


    /**
     * @param int $startDate
     * @param int $endDate
     * @return \Illuminate\Http\JsonResponse
     */
    public function populateReportsQuestionsTbl($startDate = 0, $endDate = 0)
    {
        try {
            Log::info('populateReportsQuestionsTbl start');
            $startDate = (int)$startDate;
            $endDate = (int)$endDate;
            if ($startDate > 0 && $endDate > 0) {
                $this->factTblService->quizPerformanceByQuestion($startDate, $endDate);
                $this->factTblService->quizPerformanceByQuestionSummary($startDate, $endDate);
                $this->factTblService->directQuizPerformanceByQuestion($startDate, $endDate);
                $this->factTblService->directQuizPerformanceByQuestionSummary($startDate, $endDate);
            }
            Log::info('populateReportsQuestionsTbl END');
        } catch (Exception $e) {
            Log::info('populateReportsQuestionsTbl start : '.$e->getMessage());
        }
    }

     /**
     * @param $start
     * @param $end
     */
    public function populateReportsBaseTbl($start, $end)
    {
        set_time_limit(3600);
        try {
            Log::info('Cron - manual Base cron is started at. ' . date('h:m:s'));
            $first = Carbon::createFromTimestampUTC($start);
            $second = Carbon::createFromTimestampUTC($end);
            if ($first->gt($second)) {
                Log::info('Cron - Wrong date is given');
            } else {
                $this->dimensionTblService->dimensionUser();
                $this->dimensionTblService->dimensionChannel();
                Log::info('Cron - Successfully Base tables are updated ' . date('h:m:s'));
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * @param $start
     * @param $end
     */
    public function populateReportsDimensionTbl($start, $end)
    {
        set_time_limit(3600);
        try {
            Log::info('Cron - manual dimension cron is started at. ' . date('h:m:s'));
            $first = Carbon::createFromTimestampUTC($start);
            $second = Carbon::createFromTimestampUTC($end);
            if ($first->gt($second)) {
                Log::info('Cron - Wrong date is given');
            } else {
                $this->dimensionTblService->dimensionChannelUserQuiz();
                $this->dimensionTblService->dimensionAnnouncement($first->timestamp, $second->timestamp);
                Log::info('Cron - Successfully dimension tables are updated ' . date('h:m:s'));
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * @param $start
     * @param $end
     */
    public function overwriteDimensionTbl($start, $end)
    {
        set_time_limit(3600);
        try {
            Log::info('Cron - manual dimension Overwrite cron is started at. ' . date('h:m:s'));
            $first = Carbon::createFromTimestampUTC($start);
            $second = Carbon::createFromTimestampUTC($end);
            if ($first->gt($second)) {
                Log::info('Cron - Wrong date is given');
            } else {
                $this->dimensionTblService->dimensionChannelUserQuiz();
                $this->dimensionTblService->dimensionAnnouncement($first->timestamp, $second->timestamp);
                Log::info('Cron - Successfully dimension tables are updated ' . date('h:m:s'));
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
