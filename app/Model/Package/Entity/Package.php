<?php

namespace App\Model\Package\Entity;

use Illuminate\Support\Facades\Schema;
use Jenssegers\Mongodb\Eloquent\Model;

use Carbon\Carbon;
use App\Model\User;
use App\Model\UserGroup;
use App\Model\Program;
use App\Model\Category;
use App\Model\Sequence;

class Package extends Model
{
    protected $collection = 'package';

    /**
     * Defines primary key on the model
     * @var string
     */
    protected $primaryKey = 'package_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = ["id"];

    /**
     * The attributes that should not be allowed to auto fill
     *
     * @var array
     */
    protected $guarded = ["_id", "id"];

    /**
     * Function generate unique auto incremented id for this collection.
     *
     * @param bool $unique force to set unique index (Default: true)
     *
     * @return int
     */
    public static function getNextSequence($unique = true)
    {
        return Sequence::getSequence('package_id');
    }

    /**
     * Create many to many relation b/w package and user
     * @return mixed
     */
    public function user()
    {
        return $this->belongsToMany(
            User::class,
            "user_ids",
            "package_ids"
        );
    }

    /**
     * Create many to many relation b/w package and userGroup
     * @return mixed
     */
    public function userGroup()
    {
        return $this->belongsToMany(
            UserGroup::class,
            "user_group_ids",
            "package_ids"
        );
    }

    /* Create many to many relation b/w package and program
     * @return mixed
     */
    public function programs()
    {
        return $this->belongsToMany(
            Program::class,
            "program_ids",
            "package_ids"
        );
    }

    /* Create many to many relation b/w category and package
     * @return mixed
     */
    public function category()
    {
        return $this->belongsToMany(
            Category::class
        );
    }

    public static function getAllPackageByIDOrSlug($slug = '')
    {
        $return_data = [];
        if ($slug != '') {
            $return_data = self::where('package_slug', '=', $slug)
                ->get();
        }
        return $return_data;
    }

    /**
     * @param $query
     * @param array $filter_params
     */
    public function scopeFilter($query, $filter_params)
    {
        return $query->where("status", "!=", "DELETED")->when(
            isset($filter_params["in_ids"]),
            function ($query) use ($filter_params) {
                return $query->whereIn("package_id", $filter_params["in_ids"]);
            }
        )->when(
            isset($filter_params["not_in_ids"]),
            function ($query) use ($filter_params) {
                return $query->whereNotIn("package_id", $filter_params["not_in_ids"]);
            }
        )->when(
            !empty($filter_params["package_slug"]),
            function ($query) use ($filter_params) {
                return $query->whereIn("package_slug", $filter_params["package_slug"]);
            }
        )->when(
            array_has($filter_params, "search_key") && !empty($filter_params["search_key"]),
            function ($query) use ($filter_params) {
                return $query->where(
                    function ($query) use ($filter_params) {
                        return $query->orWhere("package_title", "like", "%{$filter_params["search_key"]}%")
                            ->orWhere("package_shortname", "like", "%{$filter_params["search_key"]}%")
                            ->orWhere("package_description", "like", "%{$filter_params["search_key"]}%");
                    }
                );
            }
        )->when(
            array_has($filter_params, "order_by"),
            function ($query) use ($filter_params) {
                return $query->orderBy(
                    array_get($filter_params, "order_by"),
                    array_has($filter_params, "order_by_dir")? array_get($filter_params, "order_by_dir") : "desc"
                );
            }
        )->when(
            array_has($filter_params, "start") && is_numeric($filter_params["start"]),
            function ($query) use ($filter_params) {
                return $query->skip((int) $filter_params["start"]);
            }
        )->when(
            array_has($filter_params, "limit") && is_numeric($filter_params["limit"]),
            function ($query) use ($filter_params) {
                return $query->take((int) $filter_params["limit"]);
            }
        )->when(
            !empty($filter_params["package_sellability"]),
            function ($query) use ($filter_params) {
                return $query->where("package_sellability", $filter_params["package_sellability"]);
            }
        );
    }
    
    /**
     * Scope a query to only include display active packages
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDisplayActive($query)
    {
        return $query->where('package_display_enddate', '>=', Carbon::today()->timestamp);
    }

    public static function getPackageDetailsByID($id)
    {
        return self::where('package_id', '=', (int)$id)->first();
    }

    public static function getchildrencount($slug)
    {
        $count = 0;
        $program = self::where('package_slug', '=', $slug)->where('status', '!=', 'DELETED')->get()->toArray();
        if (isset($program[0]['program_ids'])) {
            $count = count($program[0]['program_ids']);
        }
        return $count;
    }

    /**
     * Scope a query to only include active packages
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public static function getCatalogPackage()
    {
        $now = time();
        $packages = [];
        $package_list = self::where('package_id', '>', 0)
            ->where('status', '=', 'ACTIVE')
            ->where('package_startdate', '<=', $now)
            ->Where('package_enddate', '>=', $now)
            ->Where('package_display_startdate', '<=', $now)
            ->Where('package_display_enddate', '>=', $now)
            ->where('package_visibility', '=', 'yes')
            ->where('package_sellability', '=', 'yes')
            ->get(['package_id', 'category_ids'])->toArray();
        if (!empty($package_list)) {
            foreach ($package_list as $package) {
                if (empty($package['category_ids'])) {
                    $packages[] = $package['package_id'];
                }
            }
        }
        return $packages;
    }

    public static function getPackage($package_slug)
    {
        return self::where('package_slug', $package_slug)
            ->where('status', '!=', 'DELETED')
            ->get()->toArray();
    }

    /**
     * @return package id
     */
    public static function uniqueProductId()
    {
        $product_id = self::orderBy('package_id', 'desc')->value('package_id');
        if ($product_id != null) {
            return (int)$product_id + 1;
        }

        return 1;
    }

    /**
     * @param $package_slug
     * @return Array
     */
    public static function getActivePackage($package_slug)
    {
        return self::where('package_slug', $package_slug)
            ->where('status', '=', 'ACTIVE')
            ->get()->toArray();
    }

    /**
     * @param int $key
     * @param Array $fieldarr
     * @param int $id
     * @return Boolean
     */
    public static function removeFeedRelation($key, $fieldarr = [], $id = null)
    {
        if ($id) {
            foreach ($fieldarr as $field) {
                self::whereIn('package_id', $key)->pull($field, (int)$id);
            }
        }
        return true;
    }
}
