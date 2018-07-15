<?php
namespace App\Model\QuizAttemptData\Repository;

use App\Enums\QuizAttempt\QuizAttemptDataStatus;
use App\Helpers\Quiz\QuizHelper;
use App\Model\QuizAttemptData;
use DB;
use Log;

/**
 * class QuizAttemptDataRepository
 * @package  App\Model\QuizAttemptData|Repository
 */
class QuizAttemptDataRepository implements IQuizAttemptDataRepository
{
    /**
     * {@inheritdoc}
     */
    public function getAttemptData($attempt_id)
    {
        return QuizAttemptData::where('attempt_id', (int)$attempt_id)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestion($attempt_id, $question_id, $section_id = '')
    {
        if (!empty($section_id)) {
            return QuizAttemptData::where('attempt_id', (int)$attempt_id)
                            ->where('section_id', (int)$section_id)
                            ->where('question_id', $question_id)
                            ->get();
        } else {
            return QuizAttemptData::where('attempt_id', (int)$attempt_id)
                            ->where('question_id', $question_id)
                            ->get();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function copyQuestions($attempt, $questions)
    {
        if (isset($attempt->section_details)) {
            collect($attempt->section_details)->each(function ($section, $section_id) use ($questions, $attempt) {
                //sorting questions based on page_layout before insert to preserve order of page_layout
                $questions = collect(array_replace(array_flip(array_map('intval', array_flatten(is_object($section) ? $section->page_layout : $section['page_layout']))),
                    $questions->whereIn('question_id', array_flatten(is_object($section) ? $section->page_layout : $section['page_layout']))
                              ->groupBy('question_id')
                              ->toArray()
                ));
                $questions->chunk(20)
                          ->each(function ($question) use ($attempt, $section_id) {
                              $this->insertQuestion($question, $attempt, $section_id);
                          });
            });
        } else {
            //sorting questions based on page_layout before insert to preserve order of page_layout
            $questions = collect(array_replace(array_flip(array_map('intval', array_flatten($attempt->page_layout))),
                $questions->groupBy('question_id')
                          ->toArray()
            ));
            $questions->chunk(20)
                      ->each(function ($question) use ($attempt) {
                          $this->insertQuestion($question, $attempt);
                      });

        }
        QuizAttemptData::where('attempt_id', $attempt->attempt_id)->where('question_id', null)->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function insertQuestion($questions, $attempt, $section_id = false)
    {
        $record = $questions->map(function ($question) use ($attempt, $section_id) {
            $question = $question[0];
            $data = new \StdClass;
            $data->attempt_id = $attempt->attempt_id;
            $data->quiz_id = $attempt->quiz_id;
            if ($section_id) {
                $data->section_id = $section_id;
            }
            $data->user_id = $attempt->user_id;
            $data->question_id = $question['question_id'];
            $data->question_type = $question['question_type'];
            $data->question_text = $question['question_text'];
            $data->question_mark = (float)$question['default_mark'];
            $data->shuffle_answers = array_get($question, 'shuffle_answers', false);
            $answers = collect($question['answers']);
            $data->answers = $question['answers'];
            if ($data->shuffle_answers) {
                $data->answer_order = $answers->keys()->shuffle()->toArray();
            } else {
                $data->answer_order = $answers->keys()->toArray();
            }
            $data->correct_answer = $answers->where('correct_answer', true)->first()['answer'];
            $data->rationale = $answers->where('correct_answer', true)->first()['rationale'];
            $data->user_response = $data->answer_status = '';
            $data->obtained_mark = 0;
            $data->obtained_negative_mark = QuizHelper::roundOfNumber(
                ($attempt->un_attempt_neg_mark/100) * $data->question_mark
            );
            $data->status = QuizAttemptDataStatus::NOT_VIEWED;
            $data->history = [];
            $data->mark_review = false;
            return $data;
        });
        DB::collection('quiz_attempt_data')->raw(function ($collection) use ($record) {
            return $collection->insertMany(array_values($record->toArray()), ['continueOnError' => true]);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function saveAnswer($attempt, $question, $next_page, $answer, $reviewed, $time_spend, $section)
    {
        $data = $this->getQuestion($attempt->attempt_id, $question)->first();
        if ($answer != '') {
            $user_response = $data->answers[$data->answer_order[$answer]];
            $data->user_response = $user_response['answer'];
        } else {
            $data->user_response = '';
        }
        if ($data->correct_answer == $data->user_response) {
            $data->obtained_mark = (float)$data->question_mark;
            $data->obtained_negative_mark = 0;
            $data->answer_status = QuizAttemptDataStatus::CORRECT;
        } else {
            $data->obtained_mark = 0;
            $percentage = empty($data->user_response) ? $attempt->un_attempt_neg_mark :$attempt->attempt_neg_mark;
            $data->default_negative_mark_percentage = $percentage;
            $data->obtained_negative_mark = (float)QuizHelper::roundOfNumber(
                ($percentage / 100) * ($data->question_mark)
            );
            $data->answer_status = !empty($data->user_response) ? QuizAttemptDataStatus::INCORRECT :  '';
        }
        $history = $data->history;
        $history[] = [
            'status' => 'ANSWERED',
            'time' => time(),
            'answer' => $data->user_response,
        ];
        $data->history = $history;
        $data->mark_review = $reviewed == 'true' ? true : false;
        $data->time_spend = array_merge(array_get($data, 'time_spend', []), [(int)$time_spend]);
        $data->status = QuizAttemptDataStatus::ANSWERED;
        return $data->save();
    }

    /**
     * {@inheritdoc}
     */
    public function getAttemptDataByIds($attempt_ids)
    {
        return QuizAttemptData::whereIn('attempt_id', $attempt_ids)
            ->get();
    }
}
