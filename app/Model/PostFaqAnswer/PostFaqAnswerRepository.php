<?php

namespace App\Model\PostFaqAnswer;

use App\Model\PacketFaqAnswers;

/**
 * class PostFaqAnswerRepository
 * @package App\Model\PostFaqAnswer
 */
class PostFaqAnswerRepository implements IPostFaqAnswerRepository
{

    /**
     * @inhritdoc
     */
    public function getAnswersByQuestionID($question_id, $user_id)
    {
        return PacketFaqAnswers::getAnswersByQuestionID($question_id, $user_id);
    }

    /**
     * @inhritdoc
     */
    public function getAnswersByAnswerID($question_id)
    {
        return PacketFaqAnswers::getAnswersByAnswerID($question_id);
    }

    /**
     * @inhritdoc
     */
    public function InsertRecord($insertarr)
    {
        PacketFaqAnswers::insert($insertarr);
    }

    /**
     * @inhritdoc
     */
    public function DeleteRecord($question_id)
    {
        return PacketFaqAnswers::DeleteRecord($question_id);
    }
}
