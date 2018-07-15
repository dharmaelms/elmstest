<?php
namespace App\Model;

use Moloquent;

class NewsLetter extends Moloquent
{
    protected $collection = 'newsletters';

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

    public static function getMaxNewsletterId()
    {
        return (((int)self::max('newsletter_id')) + 1);
    }

    public static function addNewsLetter($addnewsletterjson)
    {
        return NewsLetter::forceCreate($addnewsletterjson);
    }

    public static function getNewsLetter()
    {
        return NewsLetter::get();
    }

    public static function getOneNewsLetter($key = null)
    {
        if ($key) {
            return NewsLetter::where('newsletter_id', '=', (int)$key)->get();
        } else {
            return NewsLetter::take(1)->get();
        }
    }

    public static function getNewsLetterCount()
    {
        return NewsLetter::count();
    }

    public static function getNewsLetterSearchCount($searchKey = null, $type = "ACTIVE")
    {
        if ($searchKey) {
            return NewsLetter::orwhere('email_id', 'like', '%' . $searchKey . '%')->orWhere('user_status', 'like', '%' . $searchKey . '%')->orWhere('subscribed_on', 'like', '%' . $searchKey . '%')->where('status', '=', $type)->count();
        } else {
            return NewsLetter::where('status', '=', $type)->count();
        }
    }

    public static function getNewsLetterwithPagenation($type = "ACTIVE", $start = 0, $limit = 10, $orderby = ['created_at' => 'desc'], $searchKey = null)
    {
        $key = key($orderby);
        $value = $orderby[$key];
        if ($type == "ACTIVE") {
            if ($searchKey) {
                return NewsLetter::orwhere('email_id', 'like', '%' . $searchKey . '%')->orWhere('user_status', 'like', '%' . $searchKey . '%')->orWhere('subscribed_on', 'like', '%' . $searchKey . '%')->where('status', '=', $type)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            } else {
                return NewsLetter::where('status', '=', $type)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            }
        } elseif ($searchKey) {
            return NewsLetter::orWhere('email_id', 'like', '%' . $searchKey . '%')->orWhere('user_status', 'like', '%' . $searchKey . '%')->orWhere('subscribed_on', 'like', '%' . $searchKey . '%')->where('status', '=', $type)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
        } else {
            return NewsLetter::where('status', '=', $type)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
        }
    }

    public static function updateNewsLetter($key = null, $datajson)
    {
        if ($key) {
            return NewsLetter::where('newsletter_id', '=', (int)$key)->update($datajson, ['upsert' => true]);
        } else {
            return null;
        }
    }

    public static function singleDelete($key = null)
    {
        if ($key) {
            return NewsLetter::where('newsletter_id', '=', (int)$key)->delete();
        } else {
            return null;
        }
    }
}
