<?php

namespace App\Services\PostFaq;

use App\Model\PostFaq\IPostFaqRepository;

/**
 * class PostFaqService
 * @package App\Services\PostFaq
 */
class PostFaqService implements IPostFaqService
{
    private $post_faq_repo;
    public function __construct(IPostFaqRepository $post_faq_repo)
    {
        $this->post_faq_repo = $post_faq_repo;
    }

    /**
     * @inhritdoc
     */
    public function getUnAnsPostsQusCount(array $post_ids, array $date)
    {
        return $this->post_faq_repo->getUnAnsPostsQusCount($post_ids, $date);
    }

    /**
     * @inhritdoc
     */
    public function getPostQuestions($post_ids, $type = 'all', $start = false, $limit = false, $orderByArray = ['created_at' => 'desc'], $searchKey = null)
    {
        return $this->post_faq_repo->getPostQuestions($post_ids, $type, $start, $limit, $orderByArray, $searchKey);
    }

    /**
     * @inhritdoc
     */
    public function getQuestionsByQuestionID($id)
    {
        return $this->post_faq_repo->getPostFaqDetailsById($id);
    }

    /**
     * @param int $questionid
     * @inheritdoc
     */
    public function getUpdateFieldByQuestionId($questionid, $fieldname, $fieldvalue)
    {
        return $this->post_faq_repo->getUpdateFieldByQuestionId($questionid, $fieldname, $fieldvalue);
    }
}
