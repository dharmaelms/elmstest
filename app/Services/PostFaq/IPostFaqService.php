<?php

namespace App\Services\PostFaq;

/**
 * Interface IPostFaqService
 * @package App\Services\PostFaq
 */
interface IPostFaqService
{
    /**
     * getUnAnsPostsQusCount
     * @param  array  $post_ids
     * @param  array  $date
     * @return integer
     */
    public function getUnAnsPostsQusCount(array $post_ids, array $date);

    /**
     * @param  array $post_ids
     * @param $filter
     * @param $start
     * @param $limit
     * @param $orderByArray
     * @param $searchKey
     * @return integer
     */
    public function getPostQuestions($post_ids, $type = 'all', $start = false, $limit = false, $orderByArray = ['created_at' => 'desc'], $searchKey = null);

    /**
     * getQuestionsByQuestionID
     * @param  int  $ids
     * @return integer
     */
    public function getQuestionsByQuestionID($id);

    /**
     * getUpdateFieldByQuestionId
     * @param  int  $ids
     * @return integer
     */
    public function getUpdateFieldByQuestionId($questionid, $fieldname, $fieldvalue);

}
