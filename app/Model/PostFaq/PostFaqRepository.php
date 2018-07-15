<?php

namespace App\Model\PostFaq;

use App\Model\PacketFaq;

/**
 * class PostFaqRepository
 * @package App\Model\PostFaq
 */
class PostFaqRepository implements IPostFaqRepository
{
    /**
     * @inhritdoc
     */
    public function getUnAnsPostsQusCount(array $post_ids, array $date)
    {
        $query = PacketFaq::where('status', '=', 'UNANSWERED')
                    ->whereBetween('created_at', $date);
        if (!empty($post_ids)) {
            $query->whereIn('packet_id', $post_ids);
        }
        return $query->count();
    }

    /**
     * @inhritdoc
     */
    public function getPostQuestions($post_ids, $type, $start, $limit, $orderByArray, $searchKey)
    {
        return PacketFaq::getQuestionWithTypeAndPagination($post_ids, $type, $start, $limit, $orderByArray, $searchKey);
    }

    /**
     * @inhritdoc
     */
    public function getPostFaqDetailsById($id)
    {
        return PacketFaq::getQuestionsByQuestionID($id);
    }

    /**
     * @inhritdoc
     */
    public function getUpdateFieldByQuestionId($questionid, $fieldname, $fieldvalue)
    {
        return PacketFaq::getUpdateFieldByQuestionId($questionid, $fieldname, $fieldvalue);
    }
}
