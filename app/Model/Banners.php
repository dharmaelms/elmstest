<?php
namespace App\Model;

use Moloquent;

class Banners extends Moloquent
{

    protected $table = 'banners';

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

    public static function getUniqueId()
    {
        return Sequence::getSequence('banners_id');
    }

    public static function getBanners($status)
    {
        if ($status == 'ALL') {
            $banners = self::where('status', '!=', 'DELETED')
                        ->where('_id', '!=', 'id')
                        ->orderBy('sort_order', 'asc')
                        ->paginate(10);
        } else {
            $banners = self::where('status', '=', $status)
                        ->where('_id', '!=', 'id')
                        ->orderBy('sort_order', 'asc')
                        ->paginate(10);
        }

        return $banners;
    }

    public static function getAllBanners($status, $type = 'home')
    {

        if ($status == 'ALL') {
            $banners = self::where('status', '!=', 'DELETED')->where('banner_type', '=', $type)->orderBy('sort_order', 'asc')->get()->toArray();
        } else {
            $banners = self::where('status', '=', $status)->where('banner_type', '=', $type)->where('file_client_name', '!=', '')->orderBy('sort_order', 'asc')->get()->toArray();
        }

        return $banners;
    }

    public static function addBanner($id, $file_client_name, $mobile_portrait, $mobile_landscape, $input)
    {
        $array = [
            'id' => (int)$id,
            'name' => $input['banner_name'],
            'banner_type' => $input['banner_type'],
            'banner_url' => $input['banner_url'],
            'file_client_name' => $file_client_name,
            'mobile_portrait' => $mobile_portrait,
            'mobile_landscape' => $mobile_landscape,
            'description' => $input['description'],
            'mobile_description' => $input['mobile_description'],
            //'sort_order' => (int) $input['sort_order'],
            'status' => $input['status'],
            'created_at' => time(),
        ];
        return self::insert($array);
    }

    public static function getBannerUsingId($id)
    {
        return self::where('id', '=', (int)$id)->get()->toArray();
    }

    public static function updateBanner($id, $file_client_name, $mobile_portrait, $mobile_landscape, $input)
    {

        $updateArray = [
            'name' => $input['banner_name'],
            'banner_type' => $input['banner_type'],
            'banner_url' => $input['banner_url'],
            'file_client_name' => $file_client_name,
            'mobile_portrait' => $mobile_portrait,
            'mobile_landscape' => $mobile_landscape,
            'description' => $input['description'],
            'mobile_description' => $input['mobile_description'],
            //'sort_order' => (int) $input['sort_order'],
            'status' => $input['status'],
            'updated_at' => time(),
        ];
        return self::where('id', '=', (int)$id)
            ->update($updateArray);
    }

    public static function deleteBanner($id, $sort_order, $max_order)
    {
        if ($sort_order != $max_order) {
            $sort_order = $sort_order + 1;
            $nxtorders = self::where('status', '!=', 'DELETED')->whereBetween('sort_order', [$sort_order, $max_order])->orderBy('sort_order', 'asc')->get(['sort_order', 'id'])->toArray();
            foreach ($nxtorders as $nxtorder) {
                self::where('id', '=', $nxtorder['id'])->decrement('sort_order');
            }
        }

        return self::where('id', '=', (int)$id)->update(['status' => 'DELETED']);
    }

    public static function sortBanners($id, $curval, $nextval)
    {
        $curval = (int)$curval;
        $nextval = (int)$nextval;

        if ($curval < $nextval) {
            $curval = $curval + 1;
            $nxtorders = self::where('status', '!=', 'DELETED')->whereBetween('sort_order', [$curval, $nextval])->orderBy('sort_order', 'asc')->get(['sort_order', 'id'])->toArray();
            foreach ($nxtorders as $nxtorder) {
                self::where('id', '=', $nxtorder['id'])->decrement('sort_order');
            }
            return self::where('id', '=', (int)$id)->update(['sort_order' => $nextval]);
        }

        if ($curval > $nextval) {
            $curval = $curval - 1;
            $nxtorders = self::where('status', '!=', 'DELETED')->whereBetween('sort_order', [$nextval, $curval])->orderBy('sort_order', 'asc')->get(['sort_order', 'id'])->toArray();
            foreach ($nxtorders as $nxtorder) {
                self::where('id', '=', $nxtorder['id'])->increment('sort_order');
            }
            return self::where('id', '=', (int)$id)->update(['sort_order' => $nextval]);
        }
    }

    public static function getBannnerInfo($name)
    {
        $banner_info = self::where('name', '=', $name)->get()->toArray();
        return $banner_info[0];
    }
}
