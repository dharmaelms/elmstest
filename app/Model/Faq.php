<?php

namespace App\Model;

use Moloquent;

class Faq extends Moloquent
{   

    protected $collection = 'faqs';

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

    public static function getMaxFaqId()
    {
        return Sequence::getSequence('faq_id');
    }

    public static function addFaq($addfaqjson)
    {
        return self::insert($addfaqjson);
    }

    public static function getFaq()
    {
        return self::get()->toArray();
    }

    public static function getActiveFaq()
    {
        return self::where('status', '=', 'ACTIVE')
            ->get()->toArray();
    }

    public static function getOneFaq($key = null)
    {
        if ($key) {
            return self::where('faq_id', '=', (int)$key)->get();
        } else {
            return self::take(1)->get();
        }
    }

    public static function getFaqCount()
    {
        return self::count();
    }

    public static function getFaqSearchCount($searchKey = null, $type = 'ACTIVE')
    {
        /* print_r($searchKey);
        die;*/
        if ($searchKey) {
            return self::orwhere('question', 'like', '%' . $searchKey . '%')->orWhere('answer', 'like', '%' . $searchKey . '%')->where('status', '=', $type)->count();
        } else {
            return self::where('status', '=', $type)->count();
        }
    }

    public static function getFaqwithPagenation($type = 'active', $start = 0, $limit = 10, $orderby = ['created_at' => 'desc'], $searchKey = null)
    {
        $key = key($orderby);
        $value = $orderby[$key];
        if ($type == 'active') {
            if ($searchKey) {
                return self::orwhere('question', 'like', '%' . $searchKey . '%')->orWhere('answer', 'like', '%' . $searchKey . '%')->where('status', '=', $type)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            } else {
                return self::where('status', '=', $type)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            }
        } else {
            if ($searchKey) {
                return self::orwhere('question', 'like', '%' . $searchKey . '%')->orWhere('answer', 'like', '%' . $searchKey . '%')->where('status', '=', $type)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            } else {
                return self::where('status', '=', $type)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            }
        }
    }

    public static function updateFaq($key = null, $datajson = [])
    {
        if ($key) {
            return self::where('faq_id', '=', (int)$key)->update($datajson, ['upsert' => true]);
        } else {
            return;
        }
    }

    public static function singleDelete($key = null)
    {
        if ($key) {
            return self::where('faq_id', '=', (int)$key)->delete();
        } else {
            return;
        }
    }
}
