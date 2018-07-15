<?php

namespace App\Model\PostFaq;

/**
 * Interface IPostFaqRepository
 * @package App\Model\PostFaq
 */
interface IPostFaqRepository
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
    public function getPostQuestions($post_ids, $type = 'all', $start = false, $limit = false, $orderByArray, $searchKey);

     /**
     * getUnAnsPostsQusCount
     * @param  int  $ids
     * @return integer
     */
    public function getPostFaqDetailsById($id);

    /**
     * getUpdateFieldByQuestionId
     * @param  int  $questionid
     */
    public function getUpdateFieldByQuestionId($questionid, $fieldname, $fieldvalue);
}
