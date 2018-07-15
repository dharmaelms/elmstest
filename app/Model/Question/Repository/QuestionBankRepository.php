<?php

namespace App\Model\Question\Repository;

use App\Exceptions\Question\QuestionBankNotFoundException;
use App\Model\QuestionBank;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use MongoDB\BSON\ObjectId;

class QuestionBankRepository implements IQuestionBankRepository
{
    /**
     * {@inheritdoc}
     */
    public function add($data)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        try {
            return QuestionBank::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new QuestionBankNotFoundException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveQuestionBanks($start = 0, $orderBy = "created_at", $orderByDir = "desc", $limit = null)
    {
        $query = QuestionBank::where("status", "ACTIVE")
            ->orderBy($orderBy, $orderByDir)
            ->skip((int)$start);
        if (isset($limit)) {
            $query->take((int)$limit);
        }

        return $query->get();
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, $data)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function assignQuestion($questionBankId, $question)
    {
        QuestionBank::raw(function ($collection) use ($questionBankId, $question) {
            $collection->update(
                ["question_bank_id" => $questionBankId],
                ["\$addToSet" => ["questions" => $question->question_id]]
            );
        });
    }

    /**
     * {@inheritdoc}
     */
    public function unassignQuestion($questionBank, $question)
    {
        return (bool)QuestionBank::raw(function ($collection) use (&$questionBank, &$question) {
            $collection->update(
                ["_id" => new ObjectId($questionBank->_id)],
                ["\$pull" => ["questions" => $question->question_id]]
            );
        });
    }
}
