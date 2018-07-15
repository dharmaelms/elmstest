<?php

namespace App\Services\Question;

interface IQuestionService
{
    public function addQuestion($questionBank, $data);

    public function getQuestion($id);

    public function getQuestionsByMedia($mediaId);

    public function updateQuestion($questionBankId, $questionId, $data);

    public function deleteQuestion($questionBankId, $questionId);

    public function addQuestionBank($data);

    public function getQuestionBank($id);

    public function getActiveQuestionBanks($start = 0, $orderBy = "created_at", $orderByDir = "desc", $limit = null);

    public function getQuestionByCustomId($customId);

    /**
     * getQuestionsText
     * @param  array $question_ids
     * @return mixed
     */
    public function getQuestionsText($question_ids);
}
