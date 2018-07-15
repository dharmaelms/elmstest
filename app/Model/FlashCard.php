<?php
namespace App\Model;

use Auth;
use Moloquent;

class FlashCard extends Moloquent
{   

    protected $table = 'flashcard';

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
     * function used to get max id of flash card
     * @return integer
     */
    public static function getMaxId()
    {
        return Sequence::getSequence('card_id');
    }

    /**
     * function to insert new record
     * @param array $paramname description
     * @return bool
     */
    public static function add($data, $column = false)
    {
        if (self::insert($data)) {
            if ($column) {
                return $data[$column];
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     *
     */
    public static function createCards($cards)
    {
        $id = self::getMaxId();
        $slug = self::createSlug($cards['title'], $id);
        return array_merge(['card_id' => $id, 'slug' => $slug], $cards);
    }

    /**
     * function to update
     */
    public static function updateCards($slug, $data)
    {
        return self::where('slug', '=', $slug)->update($data);
    }

    /**
     * function used to get flash card
     */
    public static function findByOne($param, $value)
    {
        return self::where($param, '=', $value)->get();
    }

    /**
     * function used to get question banks
     */
    public static function getQuestionbanks()
    {
        $questionbanks = QuestionBank::orderBy('created_at', 'desc')->where('status', '=', 'ACTIVE')->get(['question_bank_id', 'question_bank_name', 'questions', 'draft_questions']);
        return $questionbanks;
    }

    /**
     * search function for flashcards
     */
    public static function search($whereCondition = 'ALL', $likeCondition = false, $orderBy = 'created_at', $order = 'desc', $filter_params)
    {
        $query = self::LikeConditions($likeCondition)
            ->WhereConditions($whereCondition)
            ->Filter($filter_params)
            ->orderBy($orderBy, $order);
        return $query;
    }

    /**
     * @param $query \Illuminate\Database\Query\Builder
     * @param array $filter_params
     * @return object \Illuminate\Database\Query\Builder
     */
    public function scopeFilter($query, $filter_params = [])
    {
        return $query->when(
            array_has($filter_params, "in_ids"),
            function ($query) use ($filter_params) {
                return $query->whereIn("card_id", $filter_params["in_ids"]);
            }
        )->when(
            array_has($filter_params, "created_by"),
            function ($query) use ($filter_params) {
                return $query->createdBy($filter_params["created_by"]);
            }
        );
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param string|array $username
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeCreatedBy($query, $username)
    {
        if (is_array($username)) {
            return $query->whereIn("created_by", $username);
        } else {
            return $query->where("created_by", $username);
        }
    }

    /**
     * function used to create where conditions
     */
    public static function scopeWhereConditions($query, $status = 'all')
    {
        if ($status != 'ALL') {
            $query->where('status', '=', $status);
        } else {
            $query->whereIn('status', ['ACTIVE', 'INACTIVE']);
        }
        return $query;
    }

    /**
     * function used to create like conditions
     */
    public static function scopeLikeConditions($query, $condition = false)
    {
        if ($condition) {
            $query->orWhere('title', 'like', '%' . $condition . '%')
                ->orWhere('description', 'like', '%' . $condition . '%');
        }
        return $query;
    }

    /**
     * function used to create where conditions
     */
    public static function scopeOrderByConditions($query, $conditions)
    {
        foreach ($conditions as $key => $value) {
            $query->orderBy("'" . $key . "'", "'" . $value . "'");
        }
        return $query;
    }

    /**
     * function used to generate slug which is unique
     */
    public static function createSlug($title, $id = false)
    {

        $slug = strtolower(stripslashes(trim($title)));

        // Convert all the text to lower case
        $slug = str_replace(' - ', '-', $slug);

        // Replace any ' - ' sign with spaces on both sides to '-'
        $slug = str_replace(' & ', '-and-', $slug);

        // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace('& ', '-and-', $slug);

        // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace("'", '', $slug);

        // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace('\\', '', $slug);

        // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace('/', '-', $slug);

        // Replace any ' & ' sign with spaces on both sides to '-and-'
        $slug = str_replace(', ', '-', $slug);

        // Replace any comma and a space to -
        $slug = str_replace('.com', 'dotcom', $slug);

        // Remove any dot and a space
        $slug = str_replace('.', '', $slug);

        // Remove any dot and a space
        $slug = str_replace('   ', '-', $slug);

        // replace space to -
        $slug = str_replace('  ', '-', $slug);

        // replace space to -
        $slug = str_replace(' ', '-', $slug);

        // replace space to -
        $slug = str_replace('!', '', $slug);

        // remove !
        $slug = str_replace('#', '', $slug);

        // remove #
        $slug = str_replace('$', '', $slug);

        // remove $
        $slug = str_replace(':', '', $slug);

        // remove :
        $slug = str_replace(';', '', $slug);

        // remove ;
        $slug = str_replace('[', '', $slug);

        // remove [
        $slug = str_replace(']', '', $slug);

        // remove ]
        $slug = str_replace('(', '', $slug);

        // remove (
        $slug = str_replace(')', '', $slug);

        // remove )
        $slug = str_replace('\n', '', $slug);

        // remove \n
        $slug = str_replace('\r', '', $slug);

        // remove \r
        $slug = str_replace('?', '', $slug);

        // remove ?
        $slug = str_replace('`', '', $slug);

        // remove `
        $slug = str_replace('%', '', $slug);

        // remove %
        $slug = str_replace('&#39;', '', $slug);

        // remove &#39; = '
        $slug = str_replace('&39;', '', $slug);

        // remove &39; = '
        $slug = str_replace('&39', '', $slug);

        // remove &39; = '
        $slug = str_replace('&quot;', '-', $slug);
        $slug = str_replace('\"', '-', $slug);
        $slug = str_replace('"', '-', $slug);
        $slug = str_replace('&lt;', '-', $slug);
        $slug = str_replace('&gt;', '-', $slug);
        $slug = str_replace('<', '', $slug);
        $slug = str_replace('>', '', $slug);

        $exists = false;
        $exists = self::where('slug', '=', $slug)->count();

        if ($exists) {
            if (!$id) {
                $id = self::getMaxId();
            }
            $slug = $slug . '-' . $id;
        }

        return $slug;
    }

    public static function getFCNameByID($ids)
    {
        return self::whereIn('card_id', $ids)->get(['card_id', 'title', 'created_by', 'created_at']);
    }

    /**
     * function used to add relation to packet
     */
    public static function addFlashcardRelation($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('card_id', $key)->push('relations.' . $field, (int)$id, true);
        }
        return self::where('card_id', $key)->update(['updated_at' => time()]);
    }

    public static function getFlashcardsAssetsUsingAutoID($id = 'all')
    {
        if ($id == 'all') {
            return self::get()->toArray();
        } else {
            return self::where('card_id', '=', (int)$id)->get()->toArray();
        }
    }

    public static function removeFlashcardRelation($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('card_id', $key)->pull('relations.' . $field, (int)$id);
        }
        return self::where('card_id', $key)->update(['updated_at' => time()]);
    }
}
