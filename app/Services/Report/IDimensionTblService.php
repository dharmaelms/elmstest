<?php namespace App\Services\Report;

interface IDimensionTblService
{
    /**
     * dimensionUser
     * @return void
     */
    public function dimensionUser();

    /**
     * dimensionChannel
     * @return void
     */
    public function dimensionChannel();

   
    /**
     * dimensionAnnouncement
     * @param  integer $start_date
     * @param  integer $end_date
     * @return void
     */
    public function dimensionAnnouncement($start_date, $end_date);

    /**
     * dimensionChannelUserQuiz
     * @return void
     */
    public function dimensionChannelUserQuiz();
}
