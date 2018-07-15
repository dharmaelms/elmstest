<?php

namespace App\Model;

use Moloquent;

class ManageWebModel extends Moloquent
{
    protected $table = 'manageweb';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    // protected $collection="user";
    public function index()
    {
        echo 'inside the index db';
        // die;
    }

    public static function addManage($addmanagejson)
    {
        $var = self::forceCreate($addmanagejson);

        return $var;
    }

    public static function addManageFaq($addmanagejson)
    {
        $var = self::forceCreate($addmanagejson);

        return $var;
    }

    public static function getSpecficManageFaq($slug)
    {
        $var = self::where('faq_slug', '=', $slug)->take(1)->get();

        return $var;
    }

    public static function updateManageFaq($slug, $addfaqjson)
    {
        $var = self::where('faq_slug', '=', $slug)->update($addfaqjson, ['upsert' => true]);

        return $var;
    }

    public static function getManageFaq()
    {
        $var = self::where('manageweb_area', '=', 'faq')->get();

        return $var;
    }

    public static function addManageNewsletter($addmanagejson)
    {
        $var = self::forceCreate($addmanagejson);

        return $var;
    }

    public static function getManageStaticpageCount()
    {
        return self::where('manageweb_area', '=', 'static page')->count();
    }

    public static function getManageStaticpageSearchCount($search)
    {
        return self::where('manageweb_area', '=', 'static page')->orwhere('static_page_metakey', 'like', '%' . $search . '%')->orWhere('satic_page_title', 'like', '%' . $search . '%')->count();
    }

    public static function getManageStaticpagewithPagenation($start = 0, $limit = 10, $orderby = ['created_at' => 'desc'], $search = null)
    {
        $key = key($orderby);
        $value = $orderby[$key];
        if ($search) {
            return self::where('static_page_metakey', 'like', '%' . $search . '%')->orWhere('satic_page_title', 'like', '%' . $search . '%')->where('manageweb_area', '=', 'static page')->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
        } else {
            return self::where('manageweb_area', '=', 'static page')->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
        }
    }
}
