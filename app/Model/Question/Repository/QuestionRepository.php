<?php

namespace App\Model\Question\Repository;

use App\Exceptions\Question\QuestionNotFoundException;
use App\Model\Question;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class QuestionRepository
 * @package App\Model\Question\Repository
 */
class QuestionRepository implements IQuestionRepository
{
    /**
     * {@inheritdoc}
     */
    public function add($data)
    {
        $question = new Question();
        $question->question_id = Question::getNextSequence();
        $question->question_name = str_pad((string)$question->question_id, 8, '0', STR_PAD_LEFT);
        $question->fill($data);
        $question->created_at = time();
        $question->save();
        return $question;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        try {
            return Question::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new QuestionNotFoundException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getByMedia($mediaId)
    {
        $questions = Question::raw(function ($collection) use ($mediaId) {
            return $collection->find([
                "\$or" => [
                    ["question_text_media" => "{$mediaId}"],
                    ["answers" => ["\$elemMatch" => ["answer_media" => "{$mediaId}"]]],
                    ["answers" => ["\$elemMatch" => ["rationale_media" => "{$mediaId}"]]]
                ]
            ]);
        });
        return $questions;
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, $data)
    {
        try {
            $question = Question::find($id);
            $question->fill($data);
            $question->updated_at = Carbon::now()->timestamp;
            $question->save();
            return $question;
        } catch (ModelNotFoundException $e) {
            throw new QuestionNotFoundException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        try {
            $question = Question::findOrFail($id);
            $question->status = IQuestionStatus::DELETED;
            $question->save();
            return $question;
        } catch (ModelNotFoundException $e) {
            throw new QuestionNotFoundException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getByAttribute($key, $value)
    {
        \DB::enableQueryLog();
        $questions = Question::raw(function ($collection) use ($key, $value) {
            return $collection->find([
                "{$key}" => $value
            ]);
        });

        return $questions;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestionsText($question_ids)
    {
        return Question::whereIn('question_id', array_map('intval', $question_ids))
            ->orderby('question_id', 'asc')
            ->get(['question_id', 'question_text']);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestions($question_ids)
    {
        return Question::whereIn('question_id', array_map('intval', $question_ids))->active()->get();
    }
}
