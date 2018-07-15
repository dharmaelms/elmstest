<?php

namespace App\Model;

use App\Exceptions\Question\QuestionBankNotFoundException;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Moloquent;
use Schema;

/**
 * QuestionBank Model
 *
 * @package Assessment
 */
class QuestionBank extends Moloquent
{

    /**
     * The table/collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'questionbanks';

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
        'question_bank_id' => 'integer',
    ];

    /**
     * Function generate unique auto incremented id for this collection
     *
     * @param boolean $unique force to set unique index (Default: true)
     * @return integer
     */
    public static function getNextSequence()
    {
        return Sequence::getSequence('question_bank_id');
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
            $query->where('question_bank_name', 'like', '%' . $searchKey . '%')
                ->orWhere('question_bank_description', 'like', '%' . $searchKey . '%');
        }
        return $query;
    }

    public static function removeQuestion($question_id, $qbank_id = 0)
    {
        return DB::collection('questionbanks')
            ->where('question_bank_id', '=', (int)$qbank_id)
            ->pull('questions', (int)$question_id);
    }

    //added by sahana
    public static function removeQuestionBankRelation($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('question_bank_id', $key)->pull('relations.' . $field, (int)$id);
        }
        return self::where('question_bank_id', $key)->update(['updated_at' => time()]);
    }

    public static function addQuestionBankRelation($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('question_bank_id', $key)->push('relations.' . $field, (int)$id, true);
        }
        return self::where('question_bank_id', $key)->update(['updated_at' => time()]);
    }

    /*for UAR*/
    public static function getQusbankWOQus($start = 0, $limit = 3)
    {
        return self::where('status', '=', 'ACTIVE')
            ->where('questions.0', 'exists', false)
            ->orderby('question_bank_id', 'asc')
            ->skip((int)$start)
            ->take((int)$limit)
            ->get(['question_bank_name', 'question_bank_id'])
            ->toArray();
    }

    public static function getQusbankWOQusCount($start = 0, $limit = 3)
    {
        return self::where('status', '=', 'ACTIVE')
            ->where('questions.0', 'exists', false)
            ->orderby('question_bank_id', 'asc')
            ->count();
    }

    public static function getQuestionBankNameSlug($qb_name, $cat_id = '')
    {
        $slug = strtolower(stripslashes(trim($qb_name)));   // Convert all the text to lower case
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

        // $exists=0;
        // $exists=Category::where('slug','=',$slug)->count();

        // if($exists!=0)
        // {
        //     if($cat_id=='')
        //     {
        //          $cat_id=Category::getCategoryID();
        //     }
        //     $slug=$slug.'-'.$cat_id;
        // }

        return $slug;
    }

    public static function getQuestionBankBySlug($slug)
    {
        try {
            return self::where("question_bank_slug", $slug)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new QuestionBankNotFoundException();
        }
    }

    public static function getQuestionbankDetails($qbid, $status = 'DELETED')
    {
        return self::where('status', '!=', 'DELETED')->whereIn('question_bank_id', $qbid)->get(['question_bank_name', 'question_bank_id']);
    }

    // added for export for questionbank
    public static function getQuestionBank($qbid)
    {
        $QuizAttributes = config('app.QuestionBankFields');
        return self::where('question_bank_id', '=', (int)$qbid)->first();
    }
}
