<?php

namespace App\Services\PostFaqAnswer;

use App\Model\PostFaqAnswer\IPostFaqAnswerRepository;

/**
 * class PostFaqAnswerService
 * @package App\Services\PostFaqAnswer
 */
class PostFaqAnswerService implements IPostFaqAnswerService
{
    private $post_faq_ans_repo;
    public function __construct(IPostFaqAnswerRepository $post_faq_ans_repo)
    {
        $this->post_faq_ans_repo = $post_faq_ans_repo;
    }

    /**
     * @inhritdoc
     */
    public function getAnswersByQuestionID($question_id = null, $user_id = null)
    {
        return $this->post_faq_ans_repo->getAnswersByQuestionID($question_id, $user_id);
    }

    /**
     * @inhritdoc
     */
    public function getAnswersByAnswerID($question_id)
    {
        return $this->post_faq_ans_repo->getAnswersByAnswerID($question_id);
    }

    /**
     * @inhritdoc
     */
    public function InsertRecord($insertarr)
    {
        return $this->post_faq_ans_repo->InsertRecord($insertarr);
    }
    /**
     * @inhritdoc
     */
    public function DeleteRecord($question_id)
    {
        return $this->post_faq_ans_repo->DeleteRecord($question_id);
    }
}
