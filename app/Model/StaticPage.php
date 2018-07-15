<?php

namespace App\Model;

use Cache;
use Moloquent;

class StaticPage extends Moloquent
{

    protected $collection = 'staticpages';

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

    public static function getMaxStaticpaggeId()
    {
        return Sequence::getSequence('staticpagge_id');
    }

    public static function addStaticPage($addstaticpagejson)
    {
        return self::insert($addstaticpagejson);
    }

    public static function getStaticPage()
    {
        return self::get()->toArray();
    }

    public static function getOnlyActivePage()
    {
        return self::where('status', '=', 'ACTIVE')->get();
    }

    public static function getOneStaticPage($key = null)
    {
        if ($key) {
            return self::where('staticpagge_id', '=', (int)$key)->get();
        } else {
            return self::take(1)->get();
        }
    }

    public static function getStaticPageCount()
    {
        return self::count();
    }

    public static function getStaticPageSearchCount($searchKey = null, $type = 'ACTIVE')
    {
        if ($searchKey) {
            return self::orwhere('title', 'like', '%' . $searchKey . '%')->orWhere('metakey', 'like', '%' . $searchKey . '%')->orWhere('meta_description', 'like', '%' . $searchKey . '%')->where('status', '=', $type)->count();
        } else {
            return self::where('status', '=', $type)->count();
        }
    }

    public static function getStaticPagewithPagenation($type = 'ACTIVE', $start = 0, $limit = 10, $orderby = ['created_at' => 'desc'], $searchKey = null)
    {
        $key = key($orderby);
        $value = $orderby[$key];
        if ($type == 'ACTIVE') {
            if ($searchKey) {
                return self::orwhere('title', 'like', '%' . $searchKey . '%')->orWhere('metakey', 'like', '%' . $searchKey . '%')->orWhere('meta_description', 'like', '%' . $searchKey . '%')->where('status', '=', $type)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            } else {
                return self::where('status', '=', $type)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            }
        } elseif ($searchKey) {
            return self::orwhere('title', 'like', '%' . $searchKey . '%')->orWhere('metakey', 'like', '%' . $searchKey . '%')->orWhere('meta_description', 'like', '%' . $searchKey . '%')->where('status', '=', $type)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
        } else {
            return self::where('status', '=', $type)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
        }
    }

    public static function updateStaticPage($key = null, $datajson = [])
    {
        if ($key) {
            $slug = self::where('staticpagge_id', '=', (int)$key)
                ->value('slug');
            if (Cache::has($slug)) {
                Cache::pull($slug);
            }

            return self::where('staticpagge_id', '=', (int)$key)->update($datajson, ['upsert' => true]);
        } else {
            return;
        }
    }

    public static function singleDelete($key = null)
    {
        if ($key) {
            return self::where('staticpagge_id', '=', (int)$key)->delete();
        } else {
            return;
        }
    }

    public static function getTitleExist($slug = null)
    {
        if ($slug) {
            return self::raw()->findOne(['slug' => $slug]);
        } else {
            return;
        }
    }

    public static function getOneStaticPageforSlug($key = null)
    {
        if ($key) {
            return self::where('slug', '=', $key)->get()->toArray();
        } else {
            return self::take(1)->get();
        }
    }
}
