<?php

namespace App\Services\Report;

interface ITillContentReportService
{
    /**
     * prepareChannelPerformance
     * @param  array $channel_ids
     * @param  integer $start
     * @param  integer $limit
     * @return array
     */
    public function prepareChannelPerformance(array $channel_ids, $start, $limit);

    /**
     * prepareIndividualChannelPerformance
     * @param  integer $channel_id
     * @param  integer $start
     * @param  integer $limit
     * @return array
     */
    public function prepareIndividualChannelPerformance($channel_id, $start, $limit);

    /**
     * @param  array  $channel_ids
     * @param  int $start
     * @param  int $limit
     * @return array
     */
    public function prepareChannelCompletion(array $channel_ids, $start, $limit);

    /**
     * @param  int $channel_id
     * @param  int $start
     * @param  int $limit
     * @return array
     */
    public function prepareIndividualChannelCompletion($channel_id, $start, $limit);

    /**
     * prepareDirectQuizPerformance
     * @param  array  $quiz_ids
     * @param  integer $start
     * @param  integer $limit
     * @return array
     */
    public function prepareDirectQuizPerformance(array $quiz_ids, $start, $limit);

    /**
     * prepareUserCompletionAndPerformance
     * @param  array   $user_ids
     * @param  array   $channel_ids
     * @param  array   $order_by
     * @param  string   $search
     * @param  integer $start
     * @param  integer $limit
     * @return array
     */
    public function prepareUserCompletionAndPerformance(
        array $user_ids,
        array $channel_ids,
        array $order_by,
        $search,
        $start,
        $limit
    );

    /**
     * prepareUserChannelPerformance
     * @param  array   $channel_ids
     * @param  integer $user_id
     * @param  integer $start
     * @param  integer $limit
     * @return array
     */
    public function prepareUserChannelPerformance(array $channel_ids, $user_id, $start, $limit);

    /**
     * prepareUserChannelCompletion
     * @param  array   $channel_ids
     * @param  integer $user_id
     * @param  integer $start
     * @param  integer $limit
     * @return array
     */
    public function prepareUserChannelCompletion(array $channel_ids, $user_id, $start, $limit);

    /**
     * prepareUserIndChannelPerformance
     * @param  integer $channel_id
     * @param  integer $user_id
     * @param  integer $start
     * @param  integer $limit
     * @return array
     */
    public function prepareUserIndChannelPerformance($channel_id, $user_id, $start, $limit);

    /**
     * prepareUserIndChannelCompletion
     * @param  integer $channel_id
     * @param  integer $user_id
     * @param  integer $start
     * @param  integer $limit
     * @return array
     */
    public function prepareUserIndChannelCompletion($channel_id, $user_id, $start, $limit);

    /**
     * prepareQuizPerformanceByQuestion
     * @param  integer  $start
     * @param  integer  $limit
     * @param  integer  $order_by
     * @param  string  $search_key
     * @param  integer  $quiz_id
     * @param  integer  $channel_id
     * @param  array  $ques_ids
     * @param  integer $user_id
     * @return collection
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
    );

    /**
     * prepareDirectQuizPerformanceByQues
     * @param  integer $start
     * @param  integer $limit
     * @param  array $orderByArray
     * @param  string $searchKey
     * @param  integer $quiz_id
     * @param  array $ques_ids
     * @return collection
     */
    public function prepareDirectQuizPerformanceByQues(
        $start,
        $limit,
        $orderByArray,
        $searchKey,
        $quiz_id,
        $ques_ids
    );

    /**
     * getChannelNameById
     * @param  [type]  $channel_id
     * @param  boolean $retrieve_short_name
     * @return String
     */
    public function getChannelNameById($channel_id, $retrieve_short_name = false);

}
