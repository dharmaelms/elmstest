<?php

namespace App\Model;

use Moloquent;
use App\Model\Package\Entity\Package;

class Category extends Moloquent
{   
    protected $collection = 'categories';

    public $timestamps = false;

    protected $primaryKey = 'category_id';
    
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    public static function addCategory($data)
    {
        self::insert([
            'category_id' => self::getCategoryID(),
            'category_name' => htmlentities($data['category_name'], ENT_QUOTES),
            'category_description' => htmlentities(trim($data['category_desc']), ENT_QUOTES),
            'slug' => self::getCategorySlug($data['category_name']),
            'parents' => null,
            'custom' => true,
            'feature_image_file' => null,
            'created_at' => time(),
            'updated_at' => time(),
            'status' => trim($data['status']),
            'count' => 0,
        ]);

        return 1;
    }

    public static function getChildrencodes($catslug, $cat_id)
    {
        $childrencode = self::where('slug', '=', $catslug)
            ->where('category_id', '=', (int)$cat_id)
            ->value('children');

        $existschildrencode = [];

        if (is_array($childrencode)) {
            foreach ($childrencode as $eachcode) {
                if (isset($eachcode['count'])) {
                    $existschildrencode[] = $eachcode;
                }
            }
        }
        return $existschildrencode;
    }

    public static function getChildrencode($catslug)
    {
        $childrencode = self::where('slug', '=', $catslug)->value('children');

        $existschildrencode = [];

        if (is_array($childrencode)) {
            foreach ($childrencode as $eachcode) {
                if (isset($eachcode['count'])) {
                    $existschildrencode[] = $eachcode;
                }
            }
        }
        return $existschildrencode;
    }

    public static function getChildren($children)
    {
        if (!isset($children)) {
            $children = [];
        }
        return self::whereIn('category_id', array_values($children))
            ->where('status', '=', true)
            ->orderBy('category_name', 'asc')
            ->get(['category_id', 'category_name', 'slug', 'created_at'])
            ->toArray();
    }

    public static function getAdminParents()
    {
        return self::where('_id', '!=', 'category_id')
            ->where('parents', '=', null)
            ->orderBy('updated_at', 'desc')
            ->get(['category_name', 'category_id', 'slug', 'custom', 'status', 'parents', 'category_meta_tag', 'category_meta_description', 'path', 'admin_flag', 'children', 'created_at'])
            ->toArray();
    }

    public static function getSearchParents()
    {
        $categroyArray[''] = 'Select';
        $categories = self::where('_id', '!=', 'category_id')
                ->where('Parents', '=', 'null')
                ->orderBy('CategoryName', 'asc')
                ->get(['CategoryName', 'CategoryID'])
                ->toArray();

        foreach ($categories as $eachcategory) {
            $categroyArray[$eachcategory['CategoryID']] = ucwords(strtolower($eachcategory['CategoryName']));
        }

        return $categroyArray;
    }

    /* @return array children category ids
     * @param
     */

    public static function getSearchChildren($catslug)
    {
        $cat = [];
        $children = self::where('Slug', '=', $catslug)
            ->where('Children', '<>', 'null')
            ->get(['Children', 'CategoryID'])
            ->toArray();
        if (count($children[0]['Children']) > 0) {
            foreach ($children[0]['Children'] as $eachdoc) {
                $cat[] = $eachdoc['CategoryID'];
            }
        }
        $cat[] = $children[0]['CategoryID'];

        return $cat;
    }

    /*  Added by Sujatha for displayin category name in search resuts post migrating to solr */
    public static function getCategoryName($catid)
    {
        if (is_array($catid)) {
            return self::whereIn('category_id', $catid)
                ->get(['category_name', 'category_id'])
                ->toArray();
        } else {
            return self::where('category_id', '=', (int)$catid)
                ->get(['category_name', 'created_at', 'status'])
                ->toArray();
        }
    }

    public static function getAdminChildrencode($catslug)
    {
        $childrencode = self::where('slug', '=', $catslug)
            ->where('children', '!=', 'null')
            ->orderBy('updated_at', 'desc')
            ->value('children');

        $existschildrencode = [];
        if (is_array($childrencode)) {
            foreach ($childrencode as $eachcode) {
                $existschildrencode[] = $eachcode;
            }
        }

        return $existschildrencode;
    }

    public static function getAdminChildren($children, $status, $start, $limit, $orderby, $search)
    {
        if (!isset($children)) {
            $children = [];
        }

        $key = key($orderby);
        $value = $orderby[$key];

        if ($status == 'all') {
            if ($search) {
                $query = self::where('category_name', 'like', '%' . $search . '%')
                    ->whereIn('category_id', array_values($children));
            } else {
                $query = self::whereIn('category_id', array_values($children));
            }
        } elseif ($status == 'EMPTY') {
            if ($search) {
                $query = self::where('category_name', 'like', '%' . $search . '%')
                    ->where('relations.assigned_feeds', '=', null)
                    ->whereIn('category_id', array_values($children));
            } else {
                $query = self::whereIn('category_id', array_values($children))
                    ->where('relations.assigned_feeds', '=', null);
            }
        } else {
            if ($search) {
                $query = self::where('category_name', 'like', '%' . $search . '%')
                    ->where('status', '=', $status)
                    ->whereIn('category_id', array_values($children));
            } else {
                $query = self::whereIn('category_id', array_values($children))
                    ->where('status', '=', $status);
            }
        }

        return $query->where('_id', '!=', 'category_id')->orderBy($key, $value)
            ->skip((int)$start)
            ->take((int)$limit)
            ->get()
            ->toArray();
    }

    public static function getAdminChildrenCount($children, $status, $search)
    {
        if (!isset($children)) {
            $children = [];
        }
        if ($status == 'all') {
            if ($search) {
                return self::where('category_name', 'like', '%' . $search . '%')
                    ->whereIn('category_id', array_values($children))
                    ->count();
            } else {
                return self::whereIn('category_id', array_values($children))->count();
            }
        } elseif ($status == 'EMPTY') {
            if ($search) {
                return self::where('category_name', 'like', '%' . $search . '%')
                    ->where('relations.assigned_feeds', '=', null)
                    ->whereIn('category_id', array_values($children))
                    ->count();
            } else {
                return self::whereIn('category_id', array_values($children))
                    ->where('relations.assigned_feeds', '=', null)
                    ->count();
            }
        } else {
            if ($search) {
                return self::where('category_name', 'like', '%' . $search . '%')
                    ->where('status', '=', $status)
                    ->whereIn('category_id', array_values($children))
                    ->count();
            } else {
                return self::whereIn('category_id', array_values($children))
                    ->where('status', '=', $status)
                    ->count();
            }
        }
    }

    public static function getAdminChildrenForEdit($children)
    {
        if (!isset($children)) {
            $children = [];
        }
        return self::whereIn('category_id', array_values($children))
            ->orderBy('category_name', 'asc')
            ->get(['category_id', 'category_name', 'slug', 'custom', 'status', 'path', 'admin_flag', 'parents', 'children', 'created_at'])
            ->toArray();
    }

    public static function getCategorySlug($catname)
    {
        return str_slug($catname);
    }

    public static function getCategoryID()
    {
        return Sequence::getSequence('category_id');
    }

    public static function getCustomCategoryCodes()
    {
        return self::where('custom', '=', true)
            ->get(['category_id', 'category_name'])
            ->toArray();
    }

    public static function getHeaderCategories()
    {
        return self::where('custom', '=', 'TRUE')
            ->where('Parents', '=', 'null')
            ->where('count', '=', 1)
            ->get(['CategoryID', 'CategoryName', 'Slug'])
            ->toArray();
    }

    public static function deleteParentCategory($id)
    {
        return self::where('category_id', '=', (int)$id)->delete();
    }

    public static function deleteChildCategory($parent_slug, $id)
    {
        self::where('slug', '=', $parent_slug)
            ->pull('children', ['category_id' => (int)$id]);
        self::where('category_id', '=', (int)$id)->delete();
    }

    public static function getFilteredCategoryWithPagination($status = 'all', $start = 0, $limit = 10, $orderby = ['created_at' => 'desc'], $search = null)
    {
        $key = key($orderby);
        $value = $orderby[$key];
        if ($status == 'all') {
            if ($search && $search != '') {
                return self::where('parents', '=', null)
                    ->orwhere('parents', '=', '')
                    ->where('_id', '!=', 'category_id')
                    ->where('category_name', 'like', '%' . $search . '%')
                    ->orderBy($key, $value)
                    ->skip((int)$start)
                    ->take((int)$limit)
                    ->get()
                    ->toArray();
            } else {
                return self::where('parents', '=', null)
                    ->orwhere('parents', '=', '')
                    ->where('_id', '!=', 'category_id')
                    ->orderBy($key, $value)
                    ->skip((int)$start)
                    ->take((int)$limit)->get()->toArray();
            }
        } elseif ($status == 'EMPTY') {
            if ($search) {
                return self::where('parents', '=', null)->orwhere('parents', '=', '')->where('_id', '!=', 'category_id')->where('category_name', 'like', '%' . $search . '%')->where('relations.assigned_feeds', '=', null)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            } else {
                return self::where('parents', '=', null)->orwhere('parents', '=', '')->where('_id', '!=', 'category_id')->where('relations.assigned_feeds', '=', null)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            }
        } else {
            if ($search) {
                return self::where('parents', '=', null)->orwhere('parents', '=', '')->where('_id', '!=', 'category_id')->where('category_name', 'like', '%' . $search . '%')->where('status', '=', $status)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            } else {
                return self::where('parents', '=', null)->orwhere('parents', '=', '')->where('_id', '!=', 'category_id')->where('status', '=', $status)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            }
        }
    }

    public static function getCategoryCount($status = 'all', $search = null)
    {
        if ($status == 'all') {
            if ($search) {
                return self::where('parents', '=', null)
                    ->orwhere('parents', '=', '')
                    ->where('_id', '!=', 'category_id')
                    ->where('category_name', 'like', '%' . $search . '%')
                    ->count();
            } else {
                return self::where('parents', '=', null)
                    ->orwhere('parents', '=', '')
                    ->where('_id', '!=', 'category_id')
                    ->count();
            }
        } elseif ($status == 'EMPTY') {
            if ($search) {
                return self::where('parents', '=', null)
                    ->orwhere('parents', '=', '')
                    ->where('_id', '!=', 'category_id')
                    ->where('category_name', 'like', '%' . $search . '%')
                    ->where('relations.assigned_feeds', '=', null)
                    ->count();
            } else {
                return self::where('parents', '=', null)
                    ->orwhere('parents', '=', '')
                    ->where('_id', '!=', 'category_id')
                    ->where('relations.assigned_feeds', '=', null)
                    ->count();
            }
        } else {
            if ($search) {
                return self::where('parents', '=', null)
                    ->orwhere('parents', '=', '')
                    ->where('_id', '!=', 'category_id')
                    ->where('category_name', 'like', '%' . $search . '%')
                    ->where('status', '=', $status)
                    ->count();
            } else {
                return self::where('parents', '=', null)
                    ->orwhere('parents', '=', '')
                    ->where('status', '=', $status)
                    ->count();
            }
        }
    }

    public static function removeCategoryRelation($key, $fieldarr = [], $id)
    {
        foreach ($fieldarr as $field) {
            self::where('category_id', $key)->pull('relations.' . $field, $id);
        }

        return self::where('category_id', $key)->update(['updated_at' => time()]);
    }

    public static function updateCategoryRelation($key, $arrname, $updateArr, $overwrite = false)
    {
        if ($overwrite) {
            self::where('category_id', $key)->unset('relations.' . $arrname);
            self::where('category_id', $key)->update(['relations.' . $arrname => $updateArr]);
        } else {
            self::where('category_id', $key)->push('relations.' . $arrname, $updateArr, true);
        }

        return self::where('category_id', $key)->update(['updated_at' => time()]);
    }

    public static function updateCategoryFeedRelation($category, $feeds)
    {
        self::where('category_id', '=', (int)$category)->unset('relations.assigned_feeds');

        if ($feeds) {
            $feeds = explode(',', $feeds);
        } else {
            $feeds = [];
        }

        foreach ($feeds as $feed) {
            self::where('category_id', '=', (int)$category)
                ->push('relations.assigned_feeds', (int)$feed, true);

            Program::where('program_id', '=', (int)$feed)
                ->where('program_type', '=', 'content_feed')
                ->push('program_categories', (int)$category, true);
        }

        return;
    }

    public static function getFeedsRelation($cat_id)
    {
        return self::where('category_id', '=', (int)$cat_id)->get()->toArray();
    }

    /* Code to get Category details for Admin iframe starts */
    public static function getAllCategoryCount($status = 'all', $search = null)
    {
        if ($status == 'all') {
            if ($search) {
                return self::where('category_name', 'like', '%' . $search . '%')->count();
            } else {
                return self::where('_id', '!=', 'category_id')->count();
            }
        } else {
            if ($search) {
                return self::where('category_name', 'like', '%' . $search . '%')
                    ->where('status', '=', $status)
                    ->count();
            } else {
                return self::where('status', '=', $status)->count();
            }
        }
    }

    public static function getAllFilteredCategoryWithPagination($status = 'all', $start = 0, $limit = 10, $orderby = ['created_at' => 'desc'], $search = null)
    {
        $key = key($orderby);
        $value = $orderby[$key];
        if ($status == 'all') {
            if ($search) {
                return self::where('category_name', 'like', '%' . $search . '%')->where('parents', '=', null)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            } else {
                return self::orderBy($key, $value)->where('parents', '=', null)->skip((int)$start)->take((int)$limit)->get()->toArray();
            }
        } else {
            if ($search) {
                return self::where('category_name', 'like', '%' . $search . '%')->where('status', '=', $status)->where('parents', '=', null)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            } else {
                return self::where('status', '=', $status)->where('parents', '=', null)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            }
        }
    }
    /* Code to get Category details for Admin iframe ends */

    /* To get Category Informaiton*/
    public static function getCategoyInfo($slug)
    {
        return self::where('slug', '=', $slug)->get()->toArray();
    }

    public static function getCategories()
    {
        return self::orderBy('updated_at', 'desc')
            ->get(['category_name', 'category_id', 'slug', 'custom', 'status', 'parents', 'category_meta_tag', 'category_meta_description', 'path', 'admin_flag', 'children', 'created_at'])
            ->toArray();
    }

    public static function getFeedRelatedCategory($feed_id)
    {
        return self::where('relations.assigned_feeds', '=', (int)$feed_id)->orderBy("created_at", "desc")->get()->toArray();
    }

    public static function getPackageRelatedCategory($feed_id)
    {
        return self::where('package_ids', '=', (int)$feed_id)->orderBy("created_at", "desc")->get()->toArray();
    }

    public static function getProductRelatedCategory($feed_id)
    {
        return self::where('relations.assigned_products', '=', (int)$feed_id)->get()->toArray();
    }

    public static function getCategoriesSlug($parent = '', $category_id = '')
    {
        if ($parent == '' && $category_id == '') {
            $cat_info = self::where('parents', '=', null)->get()->toArray();
        } elseif ($parent != '' && $category_id == '') {
            $cat_info = self::where('parents', '=', (int)$parent)->get()->toArray();
        } elseif ($parent == '' && $category_id != '') {
            $cat_info = self::where('category_id', '!=', (int)$category_id)
                ->where('parents', '=', null)
                ->get()
                ->toArray();
        } else {
            $cat_info = self::where('category_id', '!=', (int)$category_id)
                ->where('parents', '=', (int)$parent)
                ->get()
                ->toArray();
        }
        $cat_slugs = [];

        foreach ($cat_info as $each) {
            $cat_slugs[] = array_get($each, 'slug');
        }

        return $cat_slugs;
    }

    public static function getAssignedFeedsByCategory($channels, $package_ids)
    {
        return self::whereIn('relations.assigned_feeds', $channels)->orwhereIn('package_ids', $package_ids)->orderBy("created_at", "desc")->get()->toArray();
    }

    public static function getChildrenCategories($children)
    {
        $children_ids = [];
        if (!isset($children)) {
            $children = [];
        }
        foreach ($children as $key => $value) {
            $children_ids[] = $value['category_id'];
        }
        $childnames = self::whereIn('category_id', array_values($children_ids))->get(['category_id', 'category_name'])->toArray();

        return $childnames;
    }

    public static function getCategoryWithRelation()
    {
        return self::where('parents', '=', null)
            ->orwhere('parents', '=', '')
            ->where('status', '=', 'ACTIVE')
            ->where('relations', 'exists', true)
            ->orderBy('updated_at', 'desc')
            ->get(['category_name', 'category_id', 'children', 'relations', 'parents'])->toArray();
    }

    public static function getChildrenProgramRelation($id)
    {
        return self::where('status', '=', 'ACTIVE')
            ->where('category_id', (int)$id)
            ->where('relations', 'exists', true)
            ->orderBy('updated_at', 'desc')
            ->get(['category_name', 'category_id', 'children'])->toArray();
    }

    public static function getParentId($id)
    {
        return self::where('category_id', (int)$id)->value('parents');
    }

    public static function getCustomCategoryWithRelation()
    {
        $parents = self::where('parents', '=', null)
            ->orwhere('parents', '=', '')
            ->where('status', '=', 'ACTIVE')
            ->orderBy('updated_at', 'desc')
            ->get()->toArray();
        if (!empty($parents)) {
            foreach ($parents as $key => $parent) {
                $i = $j = $k = $l = 0;
                $m = 0;
                if (!isset($parent['relations']['assigned_feeds']) || empty($parent['relations']['assigned_feeds'])) {
                    $i++;
                }
                if (!isset($parent['relations']['assigned_products']) || empty($parent['relations']['assigned_products'])) {
                    $j++;
                }
                if (!isset($parent['relations']['assigned_courses']) || empty($parent['relations']['assigned_courses'])) {
                    $m++;
                }
                if (!isset($parent['children']) || empty($parent['children'])) {
                    $k++;
                }
                if (isset($parent['children']) && !empty($parent['children'])) {
                    $count = self::getChildernProducts($parent['children']);
                    if ($count == 0) {
                        $l++;
                    }
                }

                if ($i > 0 && $j > 0 && $m > 0 && ($k > 0 || $l > 0)) {
                    unset($parents[$key]);
                }
            }
        }
        return $parents;
    }

    public static function getCustomChildrenProgramRelation($id)
    {
        $parents = self::where('status', '=', 'ACTIVE')
            ->where('category_id', '=', (int)$id)
            ->where('relations', 'exists', true)
            ->orderBy('updated_at', 'desc')
            ->get(['category_name', 'category_id', 'relations'])->toArray();
        foreach ($parents as $key => $parent) {
            $i = $j = 0;
            $k = 0;
            if (!isset($parent['relations']['assigned_feeds']) || empty($parent['relations']['assigned_feeds'])) {
                $i++;
            }
            if (!isset($parent['relations']['assigned_products']) || empty($parent['relations']['assigned_products'])) {
                $j++;
            }
            if (!isset($parent['relations']['assigned_courses']) || empty($parent['relations']['assigned_courses'])) {
                $j++;
            }
            if ($i > 0 && $j > 0 && $k > 0) {
                unset($parents[$key]);
            }
        }
        return $parents;
    }

    public static function getChildernProducts($children)
    {
        $count = 0;
        foreach ($children as $child) {
            $count = $count + self::getListCount($child['category_id']);
        }
        return $count;
    }

    public static function getListCount($id)
    {
        $parents = self::where('category_id', '=', (int)$id)
            ->where('relations', 'exists', true)
            ->get(['category_name', 'category_id', 'relations'])->toArray();
        if (!empty($parents)) {
            foreach ($parents as $key => $parent) {
                $i = $j = 0;
                $k = 0;
                if (!isset($parent['relations']['assigned_feeds']) || empty($parent['relations']['assigned_feeds'])) {
                    $i++;
                }
                if (!isset($parent['relations']['assigned_products']) || empty($parent['relations']['assigned_products'])) {
                    $j++;
                }
                if (!isset($parent['relations']['assigned_courses']) || empty($parent['relations']['assigned_courses'])) {
                    $k++;
                }
                if ($i > 0 && $j > 0 && $k > 0) {
                    unset($parents[$key]);
                }
            }
        }

        return count($parents);
    }

    public static function getCustomContentCount()
    {
        $parents = self::where('category_id', '>', 0)
            ->where('relations', 'exists', true)
            ->where('status', '=', 'ACTIVE')
            ->get(['category_name', 'category_id', 'relations'])->toArray();
        if (!empty($parents)) {
            foreach ($parents as $key => $parent) {
                $i = $j = 0;
                $k = 0;
                if (!isset($parent['relations']['assigned_feeds']) || empty($parent['relations']['assigned_feeds'])) {
                    $i++;
                }
                if (!isset($parent['relations']['assigned_products']) || empty($parent['relations']['assigned_products'])) {
                    $j++;
                }
                if (!isset($parent['relations']['assigned_courses']) || empty($parent['relations']['assigned_courses'])) {
                    $k++;
                }
                if ($i > 0 && $j > 0 && $k > 0) {
                    unset($parents[$key]);
                }
            }
        }

        return $parents;
    }

    public static function getcatname($catid)
    {
        return self::where('category_id', '=', (int)$catid)->value('category_name');
    }

    /* Sandeep - Get Category and related program count*/
    public static function getCategoryRelatedProgramCount()
    {
        return self::where('status', '=', 'ACTIVE')->Where(function ($qry) {
            $qry->orwhere(function ($qery) {
                $qery->where('parents', '=', null)
                    ->orWhere('parents', '=', '');
            });
        })->orderBy('updated_at', 'desc')->get()->toArray();
    }

    public static function getActiveCategories()
    {
        return self::where('status', '=', "ACTIVE")
            ->orderBy('updated_at', 'desc')
            ->get(['category_name', 'category_id', 'slug', 'custom', 'status', 'parents', 'category_meta_tag', 'category_meta_description', 'path', 'admin_flag', 'children', 'created_at'])
            ->toArray();
    }

    public static function getCategorybyID($catid)
    {
        return self::whereIn('category_id', $catid)
            ->where('status', '=', 'ACTIVE')
            ->get(['category_name', 'category_id', 'slug', 'custom', 'status', 'parents', 'category_meta_tag', 'category_meta_description', 'path', 'admin_flag', 'children', 'created_at'])
            ->toArray();
    }

    public static function getCategoryData($search)
    {
        return self::where('category_name', 'like', '%' . $search . '%')
            ->where('status', '!=', 'DELETED')
            ->lists('category_name')
            ->all();
    }


    /* Create many to many relation b/w package and category
     * @return mixed
     */
    public function package()
    {
        return $this->belongsToMany(
            Package::class
        );
    }

    public function scopeFilter($query, $filter_params)
    {
        return $query->when(
            isset($filter_params["category_id"]),
            function ($query) use ($filter_params) {
                if (is_array($filter_params["category_id"])) {
                    return $query->whereIn("category_id", $filter_params["category_id"]);
                } else {
                    return $query->where("category_id", (int) $filter_params["category_id"]);
                }
            }
        )
        ->when(
            isset($filter_params["status"]),
            function ($query) use ($filter_params) {
                return $query->where("status", $filter_params["status"]);
            }
        )
        ->when(
            !empty($filter_params["search_key"]),
            function ($query) use ($filter_params) {
                return $query->Where("category_name", "like", "%{$filter_params["search_key"]}%");
            }
        )->when(
            !empty($filter_params["order_by"]),
            function ($query) use ($filter_params) {
                return $query->orderBy(
                    $filter_params["order_by"],
                    isset($filter_params["order_by_dir"])? $filter_params["order_by_dir"] : "desc"
                );
            }
        )->when(
            isset($filter_params["start"]),
            function ($query) use ($filter_params) {
                return $query->skip((int)$filter_params["start"]);
            }
        )->when(
            isset($filter_params["limit"]),
            function ($query) use ($filter_params) {
                return $query->take((int)$filter_params["limit"]);
            }
        )->when(
            isset($filter_params["program_ids"]),
            function ($query) use ($filter_params) {
                if (is_array($filter_params["program_ids"])) {
                    return $query->whereIn("relations.assigned_feeds", $filter_params["program_ids"]);
                } else {
                    return $query->where("relations.assigned_feeds", (int) $filter_params["program_ids"]);
                }
            }
        );
    }

    /**
     * Scope a query to inly include active categories
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        $query->where('status', 'ACTIVE');
    }
}
