<?php
namespace App\Model;

use Moloquent;

class Product extends Moloquent
{
    protected $collection = 'producttype';

    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    public static function getContentFeedCount($status = 'all', $search = null)
    {
        if ($status == 'all') {
            if ($search) {
                return self::where('product_type', 'like', '%' . $search . '%')->count();
            } else {
                return self::where('id', '>', 0)->count();
            }
        } else {
            if ($search) {
                return self::where('product_type', 'like', '%' . $search . '%')->count();
            } else {
                return self::where('id', '>', 0)->count();
            }
        }
    }

    public static function getContentFeedWithTypeAndPagination(
        $status = 'all',
        $start = 0,
        $limit = 10,
        $orderby = ['product_type' => 'desc'],
        $search = null
    )
    {
        $key = key($orderby);
        $value = $orderby[$key];
        if ($status == 'all') {
            if ($search) {
                return self::where('product_type', 'like', '%' . $search . '%')->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            } else {
                return self::where('id', '>', 0)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            }
        } else {
            if ($search) {
                return self::where('attribute_name', 'like', '%' . $search . '%')->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            } else {
                return self::where('id', '>', 0)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            }
        }
    }
}
