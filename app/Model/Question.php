<?php

namespace App\Model;

use DB;
use Moloquent;
use Schema;

/**
 * Question model
 *
 * @package Assessment
 */
class Question extends Moloquent
{

    /**
     * The table/collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'questions';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'question_id' => 'integer',
    ];

    /**
     * Function generate unique auto incremented id for this collection
     *
     * @param boolean $unique force to set unique index (Default: true)
     * @return integer
     */

    protected $guarded = ["_id"];

    public static function getNextSequence()
    {
        return Sequence::getSequence('question_id');
    }

    /**
     * Extending the query for search functionality using the scope feature
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $searchKey key to search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function scopeSearch($query, $searchKey = null)
    {
        if (!empty($searchKey)) {
            $query->where('question_name', 'like', '%' . $searchKey . '%')
                ->orWhere('question_text', 'like', '%' . $searchKey . '%');
        }
        return $query;
    }

    /**
     * Scope a query to only active sections
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'DELETED');
    }

    /**
     * Function used to update the quiz relation with questions
     * in question collection
     *
     * @param integer $quiz_id quiz id
     * @param array $question_array list of questions should updated with quiz
     * @return boolean|integer
     */
    public static function updateQuizQuestions($quiz_id, $question_array = [0])
    {
        $data = ['$addToSet' => ['quizzes' => $quiz_id]];
        return DB::collection('questions')
            ->whereIn('question_id', $question_array)
            ->update($data, ['multi' => true]);
    }

    /**
     * Function used to remove the quiz relation with questions
     * in question collection
     *
     * @param integer $quiz_id quiz id
     * @param array $question_array list of questions should updated with quiz
     * @return boolean|integer
     */
    public static function removeQuizQuestions($quiz_id, $question_array = [0])
    {
        $data = ['$pull' => ['quizzes' => $quiz_id]];
        return DB::collection('questions')
            ->whereIn('question_id', $question_array)
            ->update($data, ['multi' => true]);
    }

    public static function getQuesionsText($question_id_array = [])
    {
        return self::whereIn('question_id', $question_id_array)
            ->orderby('question_id', 'asc')
            ->get(['question_id', 'question_text'])
            ->toArray();
    }

    public static function getQuizsQuestions($quiz_id = 0)
    {
        return self::where('quizzes', '=', $quiz_id)
            ->get(['question_id']);
    }

    public static function filterQuestion($select_qesids, $selected_qbank, $tags, $qdifficult, $qtype, $qlimit, $randmization = null)
    {
        if (!empty($randmization)) {
            $qbank_questions = Question::orderBy('question_id', 'desc')
                ->where('status', '=', 'ACTIVE')
                ->questionfilter($qdifficult, $qtype, $qlimit, $tags, $randmization)
                ->whereIn('question_id', $selected_qbank->questions)
                ->whereNotIn('question_id', $select_qesids)
                ->get();
            if ($qbank_questions->isEmpty()) {
                return $qbank_questions;
            }
            $question_ids = $qbank_questions->keyBy('question_id');
            $keys = $question_ids->keys()->all();
            $keys_list = $collection = collect($keys);
            if (($rand = count($keys)) > 1) {
                if ($qlimit > 0 && $qlimit < $rand) {
                    $rand = $qlimit;
                } else {
                    if ($rand > 50) {
                        $rand = 50;
                    }
                }
                $keys_list = $collection->random($rand);
            }
            return $qbank_questions = Question::orderBy('question_id', 'desc')
                ->where('status', '=', 'ACTIVE')
                ->whereIn('question_id', $keys_list)
                ->whereNotIn('question_id', $select_qesids)
                ->get();
        }
        return $qbank_questions = Question::where('status', '=', 'ACTIVE')
            ->questionfilter($qdifficult, $qtype, $qlimit, $tags)
            ->whereIn('question_id', $selected_qbank->questions)
            ->whereNotIn('question_id', $select_qesids)
            ->get();
    }

    public function scopeQuestionfilter($query, $qdifficult, $qtype, $qlimit, $tags, $randmization = null)
    {
        $qtype = !is_null($qtype) ? $qtype : 'MCQ';
        if (!empty($qtype)) {
            $query = $query->where('question_type', '=', $qtype);
        }
        if (!empty($qdifficult)) {
            $query = $query->where('difficulty_level', '=', $qdifficult);
        }
        if (!empty($qlimit) && empty($randmization)) {
            $query = $query->limit((int)$qlimit);
        } elseif (empty($randmization)) {
            $query = $query->limit(50);
        }
        if (!empty($tags) && !empty(explode(",", $tags)) && !empty(explode(",", $tags)[0])) {
            $query = $query->whereIn('keywords', explode(',', $tags));
        }
        return $query;
    }

    /*This Method is to fetch all the data's of question : - using in the question bank export */
    public static function questionDetails($qids)
    {
        return self::whereIn('question_id', $qids)->where('status', '!=', 'DELETED')->get(['question_name', 'question_text', 'question_bank'])->toArray();
    }

    public static function questionAllDetails($qids)
    {
        return self::whereIn('question_id', $qids)->where('status', '!=', 'DELETED')->get();
    }
}
