<?php namespace App\Services\Report;

interface IFactTblService
{

    /**
     * quizPerformanceByQuestion
     * @param  integer $startDate
     * @param  integer $endDate
     * @return void
     */
    public function quizPerformanceByQuestion($startDate, $endDate);

    /**
     * quizPerformanceByQuestionSummary
     * @param  integer $startDate
     * @param  integer $endDate
     * @return void
     */
    public function quizPerformanceByQuestionSummary($startDate, $endDate);

    /**
     * directQuizPerformanceByQuestion
     * @param  integer $startDate
     * @param  integer $endDate
     * @return void
     */
    public function directQuizPerformanceByQuestion($startDate, $endDate);

    /**
     * directQuizPerformanceByQuestionSummary
     * @param  integer $startDate
     * @param  integer $endDate
     * @return void
     */
    public function directQuizPerformanceByQuestionSummary($startDate, $endDate);

    /**
     * addQuizTypeForQPTD
     */
    public function addQuizTypeForQPTD();

    /**
     * pastQuizPerformanceByAllInChannelTillDate
     * @param  integer $startDate
     * @param  integer $endDate
     * @return void
     */
    public function pastQuizPerformanceByAllInChannelTillDate($startDate, $endDate);

    /**
     * updateOACA overall channel analytics table update
     * @return void
     */
    public function updateOACA();
}
