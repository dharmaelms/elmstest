<?php

namespace App\Model;

use App\Helpers\Quiz\QuizHelper;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Moloquent;
use Schema;

/**
 * Quiz Model.
 */
class Quiz extends Moloquent
{
    /**
     * The table/collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'quizzes';

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
    protected $dates = ['created_at', 'updated_at', 'start_time', 'end_time'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'quiz_id' => 'integer',
        'user_id' => 'integer',
    ];

    /**
     * Adding custom property
     */
    protected $appends = ['beta'];

    /**
     * Function generate unique auto incremented id for this collection.
     *
     * @param bool $unique force to set unique index (Default: true)
     *
     * @return int
     */

    protected $guarded = ["_id"];

    public function add($data)
    {
        $this->quiz_id = self::getNextSequence();
        $this->fill($data);
        $this->created_by = Auth::user()->username;
        $this->created_at = time();
        $this->save();
        return $this;
    }

    public function getBetaAttribute($value)
    {
        if (isset($this->attributes['is_production']) && $this->attributes['is_production'] == 0) {
            return true;
        }
        return false;
    }

    /**
     * this function used to calculate remaining time duration for attempt
     *
     * @param  int $end_time quiz end time
     * @param  int $duration quiz duration
     *
     * @return  Integer duration in seconds
     */
    public static function getDuration($end_time, $duration)
    {
        if (!empty($end_time) && is_object($end_time)) {
            if (Carbon::now()->lt($end_time)) {
                $quiz_time_limit = (int)$end_time->timestamp - Carbon::now()->timestamp;
                if ($quiz_time_limit < $duration * 60) {
                    return $quiz_time_limit;
                }
            }
        } elseif (!empty($end_time) && !is_object($end_time) && $end_time > time()) {
            if ($end_time < Carbon::now()->addMinutes($duration)->timestamp) {
                return Carbon::now()->diffInSeconds(Carbon::createFromTimestamp($end_time));
            }
        }
        return false;
    }

    public static function getNextSequence()
    {
        return Sequence::getSequence('quiz_id');
    }

    /**
     * Extending the query for search functionality using the scope feature.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $searchKey key to search
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function scopeSearch($query, $searchKey = null)
    {
        if (!empty($searchKey)) {
            $query->where('quiz_name', 'like', '%' . $searchKey . '%')
                ->orWhere('quiz_description', 'like', '%' . $searchKey . '%');
        }

        return $query;
    }

    /**
     * Scope a query to only include active quizzes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        $query->where('status', 'ACTIVE');
    }
    /**
     * Scope used to filter quiz based on its type
     *
     * @param $query Eloquent query
     * @param $type Quiz type
     * @return Eloquent
     */
    public static function scopeFilterByType($query, $type)
    {
        switch ($type) {
            case 'GENERAL':
                return $query->whereIn('practice_quiz', [true, false])->where('is_production', 1);
                break;
            case 'PRACTICE':
                return $query->where('type', 'QUESTION_GENERATOR');
                break;
            case 'BETA':
                return $query->where('is_production', 0);
                break;
            case 'SECTION':
                return $query->where('is_sections_enabled', true);
                break;
            case 'TIMED_SECTION':
                return $query->where('is_timed_sections', true);
                break;
            case 'GENERAL_WITH_PRACTICE':
                return $query->Where('practice_quiz', true);
                break;
            case 'GENERAL_WITHOUT_PRATICE':
                return $query->Where('practice_quiz', false);
                break;
            case 'QUESTION_GENERATOR':
                return $query->Where('type', 'QUESTION_GENERATOR');
            default:
                return $query;
                break;
        }
    }

    /**
     * Generate the user list based on the realtions with users like
     * user_quiz, user_group_quiz, user_feeds_quiz & user_group_feed_quiz.
     *
     * @return array
     */
    public static function userQuizRel($unAttempt = false)
    {
        $user = Auth::user()->toArray();
        $seqQuizzes = [];
        // Users
        if (isset($user['relations']['user_quiz_rel']) && !empty($user['relations']['user_quiz_rel'])) {
            $user_quiz = $user['relations']['user_quiz_rel'];
        } else {
            $user_quiz = [];
        }

        // User groups
        if (isset($user['relations']['active_usergroup_user_rel']) && !empty($user['relations']['active_usergroup_user_rel'])) {
            $usergroup = UserGroup::where('status', '=', 'ACTIVE')
                ->whereIn('ugid', $user['relations']['active_usergroup_user_rel'])
                ->get()
                ->toArray();
            $usergroup_quiz = $assigned_feed = [];
            foreach ($usergroup as $value) {
                // Quiz assigned to user group
                if (!empty($value['relations']['usergroup_quiz_rel'])) {
                    $usergroup_quiz = array_merge($usergroup_quiz, $value['relations']['usergroup_quiz_rel']);
                }
                // CF assigned to user group
                if (!empty($value['relations']['usergroup_feed_rel'])) {
                    $assigned_feed = array_merge($assigned_feed, $value['relations']['usergroup_feed_rel']);
                }
                // CF assigned to user group
                if (!empty($value['relations']['usergroup_parent_feed_rel'])) {
                    foreach ($value['relations']['usergroup_parent_feed_rel'] as $id) {
                        $progarm = Program::getProgramDetailsByID($id);
                        if (isset($progarm['child_relations']['active_channel_rel']) && !empty($progarm['child_relations']['active_channel_rel'])) {
                            $assigned_feed = array_merge($assigned_feed, $progarm['child_relations']['active_channel_rel']);
                        }
                    }
                }
            }
        } else {
            $usergroup = $usergroup_quiz = $assigned_feed = [];
        }

        // Content feeds
        $feed = $feed_quiz_list = $feed_quiz = [];

        // CF assigned directly to user
        if (isset($user['relations']['user_feed_rel']) && !empty($user['relations']['user_feed_rel'])) {
            $assigned_feed = array_merge($assigned_feed, $user['relations']['user_feed_rel']);
        }
        // User package relation
        if (isset($user['relations']['user_package_feed_rel']) && !empty($user['relations']['user_package_feed_rel'])) {
            $assigned_feed = array_merge($assigned_feed, $user['relations']['user_package_feed_rel']);
        }
        // User course relation
        if (isset($user['relations']['user_course_rel']) && !empty($user['relations']['user_course_rel'])) {
            $assigned_feed = array_merge($assigned_feed, $user['relations']['user_course_rel']);
        }
        $time = time();
        $feed = Program::where('status', '=', 'ACTIVE')
            ->whereIn('program_id', $assigned_feed)
            ->where('program_startdate', '<=', $time)
            ->where('program_enddate', '>=', $time)
            ->orderby('program_title')
            ->get()
            ->toArray();

        $feed_list = [];
        foreach ($feed as $value) {
            $feed_list[] = $value['program_slug'];
        }

        if (!empty($feed_list)) {
            if ($unAttempt) {
                $packet = Packet::where('status', '=', 'ACTIVE')
                    ->whereIn('feed_slug', $feed_list)
                    ->get();
                $packetSeq = Packet::where('status', '=', 'ACTIVE')
                    ->where('sequential_access', '=', 'yes')
                    ->whereIn('feed_slug', $feed_list)
                    ->get();
                $seqQuizzes = [];
                foreach ($packetSeq as $ps) {
                    foreach ($ps->elements as $ele) {
                        if ($ele['type'] == 'assessment') {
                            $seqQuizzes[] = $ele['id'];
                        }
                    }
                }
            } else {
                $packet = Packet::where('status', '=', 'ACTIVE')
                    ->whereIn('feed_slug', $feed_list)
                    ->get();
            }

            $feed_quiz_list = $feed_quiz = [];
            foreach ($packet as $p) {
                foreach ($p->elements as $value) {
                    if ($value['type'] == 'assessment') {
                        $feed_quiz[] = $value['id'];
                        $feed_quiz_list[$p->feed_slug][] = $value['id'];
                    }
                }
            }
        }

        // Get quiz list for this user
        return [
            'quiz_list' => array_merge($user_quiz, $usergroup_quiz, $feed_quiz),
            'feed_list' => $feed,
            'feed_quiz_list' => $feed_quiz_list,
            'seq_quizzes' => $seqQuizzes,
            'direct_quizzes' => array_merge($user_quiz, $usergroup_quiz),
        ];
    }

    public static function removeQuizRelationForFeed($key, $feed_id, $packet_id)
    {
        self::where('quiz_id', $key)->pull('relations.feed_quiz_rel.' . $feed_id, $packet_id);

        return self::where('quiz_id', $key)->update(['updated_at' => time()]);
    }

    public static function addQuizRelationForFeed($key, $feed_id, $packet_id)
    {
        self::where('quiz_id', $key)->push('relations.feed_quiz_rel.' . $feed_id, $packet_id);

        return self::where('quiz_id', $key)->update(['updated_at' => time()]);
    }

    public static function removeQuizRelation($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('quiz_id', $key)->pull('relations.' . $field, (int)$id);
        }

        return self::where('quiz_id', $key)->update(['updated_at' => time()]);
    }

    public static function addQuizRelation($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('quiz_id', $key)->push('relations.' . $field, (int)$id, true);
        }

        return self::where('quiz_id', $key)->update(['updated_at' => time()]);
    }

    /*for statistic */
    public static function getLastThirtydaysCreatedQuizCount()
    {
        return self::where('status', '=', 'ACTIVE')
            ->where('created_at', '>', strtotime('-30 day', time()))
            ->count();
    }

    /*for UAR*/
    public static function getQuizzWOQus($start = 0, $limit = 3)
    {
        return self::where('status', '=', 'ACTIVE')
            ->where('questions.0', 'exists', false)
            ->orderby('question_bank_id', 'asc')
            ->skip((int)$start)
            ->take((int)$limit)
            ->get(['quiz_name', 'quiz_id'])
            ->toArray();
    }

    public static function getQuizzWOQusCount($start = 0, $limit = 3)
    {
        return self::where('status', '=', 'ACTIVE')
            ->where('questions.0', 'exists', false)
            ->orderby('question_bank_id', 'asc')
            ->count();
    }

    //Added by Sahana
    // Do not delete this function coz i am using it - Cerlin
    public static function getQuizAssetsUsingAutoID($id = 'all')
    {
        if ($id == 'all') {
            return self::get()->toArray();
        } else {
            return self::where('quiz_id', '=', (int)$id)->get()->toArray();
        }
    }

    /*for reports*/
    public static function getCFListForReport($qid = null)
    {
        if (!is_null($qid)) {
            return self::where('quiz_id', '=', (int)$qid)
                ->get(['relations.feed_quiz_rel'])
                ->toArray();
        } else {
            return [];
        }
    }

    public static function getLastDayQuizzes()
    {
        return self::where('status', '=', 'ACTIVE')
            ->get(['quiz_id', 'quiz_name', 'attempts', 'start_time', 'end_time', 'duration', 'total_mark', 'relations'])
            ->toArray();
    }

    public static function getSpecDaysQuiz($start_day = 0, $end_day = 0)
    {
        if ($start_day > 0 && $end_day > 0) {
            return self::where('status', '=', 'ACTIVE')
                ->where(function ($query) use ($start_day, $end_day) {
                    $query->WhereBetween('created_at', [$start_day, $end_day])
                        ->orWhereBetween('updated_at', [$start_day, $end_day]);
                })
                ->where(function ($q) {
                    $q->orWhere('type', 'exists', false)
                        ->orWhere('type', '!=', 'QUESTION_GENERATOR');
                })
                ->get()
                ->toArray();
        }
    }

    public static function getQuizNameByID($ids)
    {
        return self::whereIn('quiz_id', $ids)->get(['quiz_id', 'quiz_name', 'created_by', 'created_at']);
    }

    public static function isQuestionAssignedToQuiz($questionId)
    {
        $assignedQuizzesCount = self::raw(function ($collection) use ($questionId) {
            return $collection->aggregate([
                ["\$match" => ["\$and" => [["status" => ["\$ne" => "DELETED"]], ["questions" => $questionId]]]],
                ["\$group" => ["_id" => null, "count" => ["\$sum" => 1]]]
            ]);
        });
        $assignedQuizzesCount = $assignedQuizzesCount->first();
        return (isset($assignedQuizzesCount->count) ? true : false);
    }

    public static function getQuizByCustomId($id)
    {
        try {
            return Quiz::where("quiz_id", (int)$id)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new \Exception();
        }
    }

    public static function getQuizById($id = null)
    {
        try {
            return Quiz::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new \Exception();
        }
    }

    public static function getQuizSlug($quiz_name)
    {
        $slug = strtolower(stripslashes(trim($quiz_name)));   // Convert all the text to lower case
        $slug = str_replace(' - ', '-', $slug);   // Replace any ' - ' sign with spaces on both sides to '-'
        $slug = str_replace(' & ', '-and-', $slug);   // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace('& ', '-and-', $slug);    // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace("'", '', $slug);  // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace('\\', '', $slug); // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace('/', '-', $slug); // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace(', ', '-', $slug);    // Replace any comma and a space to -
        $slug = str_replace('.com', 'dotcom', $slug); // Remove any dot and a space
        $slug = str_replace('.', '', $slug);  // Remove any dot and a space
        $slug = str_replace('   ', '-', $slug);   // replace space to -
        $slug = str_replace('  ', '-', $slug);    // replace space to -
        $slug = str_replace(' ', '-', $slug); // replace space to -
        $slug = str_replace('!', '', $slug);  // remove !
        $slug = str_replace('#', '', $slug);  // remove #
        $slug = str_replace('$', '', $slug);  // remove $
        $slug = str_replace(':', '', $slug);  // remove :
        $slug = str_replace(';', '', $slug);  // remove ;
        $slug = str_replace('[', '', $slug);  // remove [
        $slug = str_replace(']', '', $slug);  // remove ]
        $slug = str_replace('(', '', $slug);  // remove (
        $slug = str_replace(')', '', $slug);  // remove )
        $slug = str_replace('\n', '', $slug); // remove \n
        $slug = str_replace('\r', '', $slug); // remove \r
        $slug = str_replace('?', '', $slug);  // remove ?
        $slug = str_replace('`', '', $slug);  // remove `
        $slug = str_replace('%', '', $slug);  // remove %
        $slug = str_replace('&#39;', '', $slug);  // remove &#39; = '
        $slug = str_replace('&39;', '', $slug);   // remove &39; = '
        $slug = str_replace('&39', '', $slug);    // remove &39; = '
        $slug = str_replace('&quot;', '-', $slug);
        $slug = str_replace('\"', '-', $slug);
        $slug = str_replace('"', '-', $slug);
        $slug = str_replace('&lt;', '-', $slug);
        $slug = str_replace('&gt;', '-', $slug);
        $slug = str_replace('<', '', $slug);
        $slug = str_replace('>', '', $slug);

        return $slug;
    }

    /**
     * this function is used to get concepts reports
     * @param  integer $attempt_id Quiz attempt id
     * @return array
     */
    public static function getConceptDetails($attempt_id)
    {
        $attempt = QuizAttempt::where('attempt_id', (int)$attempt_id)->where('user_id', '=', Auth::user()->uid)->first();
        if ($attempt->first()) {
            $quiz = Quiz::where('quiz_id', $attempt->quiz_id)->first();
            if ($quiz->first()) {
                $keywordQuestions = self::keywordsGrouping($quiz);
                $keywords = array_keys($keywordQuestions);
                $keywordsDetails = [];
                $keywordsDetails['keywords'] = $keywords;
                foreach ($keywordQuestions as $key => $questions) {
                    $total_questions = count($questions);
                    $attemptData = QuizAttemptData::where('quiz_id', $attempt->quiz_id)
                        ->where('attempt_id', (int)$attempt_id)
                        ->whereIn('question_id', $questions)
                        ->get();
                    $time_taken = self::timeTaken($attemptData);
                    $keywordsDetails['data'][$key]['time_taken'] = $time_taken;
                    $marks = Question::whereIn('question_id', $questions)->where('status', 'ACTIVE')->get();
                    $total_mark = $marks->sum('default_mark');
                    $keywordsDetails['data'][$key]['total_mark'] = QuizHelper::roundOfNumber($total_mark, 2);
                    $obtained_mark = $attemptData->sum('obtained_mark') - $attemptData->sum('obtained_negative_mark');
                    $answered = $attemptData->where('status', 'ANSWERED')->count();
                    $keywordsDetails['data'][$key]['obtained_mark'] = $obtained_mark;
                    $correct = $attemptData->where('answer_status', 'CORRECT')->count();
                    $incorrect = $attemptData->where('answer_status', 'INCORRECT')->count();
                    $skipped = $attemptData->where('answer_status', '')->count();
                    $not_viewed = $attemptData->where('status', 'NOT_VIEWED')->count();
                    $attempt_ques_id = $attemptData->where('answer_status', '')->lists('question_id')->all();
                    $attempt_ques_id_temp = $attemptData->where('status', 'NOT_VIEWED')->lists('question_id')->all();
                    $for_skip = array_unique(array_merge($attempt_ques_id_temp, $attempt_ques_id));
                    $speed = ($correct + $incorrect + $skipped) > 0 ? round($time_taken / ($correct + $incorrect + $skipped)) : 0;
                    $correct_per = round(($correct / $total_questions) * 100, 2);
                    $incorrect_per = round(($incorrect / $total_questions) * 100, 2);
                    $accuracy = '0';
                    if ($correct > 0) {
                        $accuracy = ((($correct_per + $incorrect_per) > 0) ? ($correct_per / ($correct_per + $incorrect_per)) * 100 : 0);
                    }
                    $keywordsDetails['data'][$key]['speed'] = $speed;
                    $keywordsDetails['data'][$key]['accuracy'] = round($accuracy, 2);
                    $keywordsDetails['data'][$key]['marks_percentage'] = round(($obtained_mark / $total_mark) * 100, 2);
                    $keywordsDetails['data'][$key]['correct'] = $correct;
                    $keywordsDetails['data'][$key]['incorrect'] = $incorrect;
                    $keywordsDetails['data'][$key]['skipped'] = count($for_skip);//$skipped + $not_viewed;
                }
            }
        }
        return $keywordsDetails;
    }

    /**
     * this function is used to group
     * @param  Integer $quiz Quiz collection
     *
     * @return array
     */
    public static function keywordsGrouping($quiz)
    {
        $questions = Question::whereIn('question_id', $quiz->questions)->get(['question_id', 'keywords']);
        $keywordsData = [];
        foreach ($questions as $question) {
            foreach ($question->keywords as $keyword) {
                if (!empty($keyword)) {
                    $keywordsData[$keyword][] = $question->question_id;
                }
            }
        }
        return $keywordsData;
    }

    /**
     * this function used to calculate total time spend in each concept
     * @param  array $attemptData Data of the attempts
     * @return Integer            total time spend on each concept
     */
    public static function timeTaken($attemptData)
    {
        $time_spend = 0;
        foreach ($attemptData as $data) {
            if (!empty($data->time_spend)) {
                foreach ($data->time_spend as $value) {
                    $time_spend += $value;
                }
            }
        }
        return $time_spend;
    }

    public static function getQuiz($QuizAttributes, $quiz_id)
    {
        return self::where('quiz_id', '=', (int)$quiz_id)->get($QuizAttributes)->toArray();
    }

    public static function getQuizzesByIds($quiz_ids = [])
    {
        return self::whereIn('quiz_id', $quiz_ids)->get(['quiz_name', 'quiz_id']);
    }

    public static function getIsProductionField($quiz_id)
    {
        return self::Where('quiz_id', '=', $quiz_id)->get(['is_production'])->toArray();
    }

    public static function getOnlyProductionQuiz($quiz_id)
    {
        return self::whereIn('quiz_id', $quiz_id)->where('is_production', '=', 1)->lists('quiz_id')->all();
    }

    public static function getOnlyBetaQuiz($quiz_id)
    {
        return self::whereIn('quiz_id', $quiz_id)->where('is_production', '=', 0)->lists('quiz_id')->all();
    }

    public static function getTotalMarks($question_ids)
    {
        $questions = Question::whereIn('question_id', $question_ids)->get();
        $total = QuizHelper::roundOfNumber($questions->sum('default_mark'), 2);
        return $total;
    }

    /**
     * Overrides dates mutator when field has 0 as value
     * @return array
     */
    public function getDates()
    {
        $date_mutatuor = $this->dates;

        if (isset($this->attributes['end_time'])) {
            $end_time = $this->attributes['end_time'];

            if (!$end_time instanceof \MongoDB\BSON\UTCDateTime && $end_time == 0) {
                $date_mutatuor = array_diff($date_mutatuor, ['end_time']);
            }
        }

        return $date_mutatuor;
    }

    /**
     * copyToDimensionQuizTbl
     * @return array
     */
    public static function copyToDimensionQuizTbl()
    {
        $result = Quiz::raw(function ($table) {
            return $table->aggregate([
                [
                    '$match' => [
                        'status' => 'ACTIVE',
                        '$or' => [
                            [
                                'type' => [
                                    '$exists' => false
                                ]
                            ],
                            [
                                'type' => [
                                    '$ne' => 'QUESTION_GENERATOR'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    '$out' => 'dim_quizzes'
                ]
            ]);
        });
        return $result;
    }
}
