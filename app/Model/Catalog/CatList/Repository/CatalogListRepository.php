<?php

namespace App\Model\Catalog\CatList\Repository;

use App\Model\Category;
use App\Model\Packet;
use App\Model\Program;
use App\Model\Package\Entity\Package;

class CatalogListRepository implements ICatalogListRepository
{
    public $priceObj = null;

    /**
     * {@inheritdoc}
     */
    public function getAllCategory($s_list, $catlist = null, $g_type = '')
    {
        if (!empty($catlist)) {
            if ($g_type == "basic") {
                if ($catlist[0] == -1) {
                    $pd_list = Program::getCatalogProducts();
                    $ch_list = Program::getCatalogChannels();
                    $cc_list = Program::getCatalogCourses();
                    $feeds = ['assigned_products' => $pd_list, 'assigned_feeds' => $ch_list, 'assigned_courses' => $cc_list];

                    $record = ['category_name' => 'Miscellaneous', 'category_description' => '',
                        'slug' => '', 'status' => 'ACTIVE', 'children' => [],
                        'relations' => $feeds];
                    $data = [$record];
                } else {
                    $data = Category::whereIn('category_id', $catlist)
                        ->where('status', '=', 'ACTIVE')
                        ->orderBy('updated_at', 'desc')
                        ->get($s_list)->toArray();
                }
            } else {
                if ($catlist[0] == -1) {
                    $pd_list = Program::getCatalogProducts();
                    $ch_list = Program::getCatalogChannels();
                    $cc_list = Program::getCatalogCourses();
                    $feeds = ['assigned_products' => $pd_list, 'assigned_feeds' => $ch_list, 'assigned_courses' => $cc_list];
                    $record = ['category_name' => 'Miscellaneous', 'category_description' => '',
                        'slug' => '', 'status' => 'ACTIVE', 'children' => [],
                        'relations' => $feeds];
                    $data = [$record];
                } else {
                    $data = Category::whereIn('category_id', $catlist)
                        ->where('status', '=', 'ACTIVE')
                        ->where(function ($query) {
                            $query->where('parents', '=', null)
                                ->orwhere('parents', '=', '');
                        })
                        ->orderBy('updated_at', 'desc')
                        ->get($s_list)->toArray();
                }
            }
        } else {
            $data = Category::where(function ($query) {
                $query->where('parents', '=', null)
                    ->orwhere('parents', '=', '');
            })
                ->where('status', '=', 'ACTIVE')
                ->orderBy('updated_at', 'desc')
                ->get($s_list)->toArray();
        }

        return $data;
    }

     /**
     * {@inheritdoc}
     */
    public function getAllPackageCategory($s_list, $catlist = null, $g_type = '')
    {
        if (!empty($catlist)) {
            if ($g_type == "basic") {
                if ($catlist[0] == -1) {
                    $package_list = Package::getCatalogPackage();
                    $feeds = ['assigned_package' => $package_list];
                    $record = ['category_name' => 'Miscellaneous', 'category_description' => '',
                        'slug' => '', 'status' => 'ACTIVE', 'children' => [],
                        'relations' => $feeds];
                    $data = [$record];
                } else {

                    $data = Category::whereIn('category_id', $catlist)
                        ->where('status', '=', 'ACTIVE')
                        ->orderBy('updated_at', 'desc')
                        ->get($s_list)->toArray();
                }
            } else {
                if ($catlist[0] == -1) {
                    $package_list = Package::getCatalogPackage();
                    $feeds = ['assigned_package' => $package_list];
                    $record = ['category_name' => 'Miscellaneous', 'category_description' => '',
                        'slug' => '', 'status' => 'ACTIVE', 'children' => [],
                        'relations' => $feeds];
                    $data = [$record];
                } else {
                    $data = Category::whereIn('category_id', $catlist)
                        ->where('status', '=', 'ACTIVE')
                        ->where(function ($query) {
                            $query->where('parents', '=', null)
                                ->orwhere('parents', '=', '');
                        })
                        ->orderBy('updated_at', 'desc')
                        ->get($s_list)->toArray();
                }
            }
        } else {
            $data = Category::where(
                    function ($query) {
                        $query->where('parents', '=', null)
                            ->orwhere('parents', '=', '');
                    }
                )
                ->where('status', '=', 'ACTIVE')
                ->orderBy('updated_at', 'desc')
                ->get($s_list)->toArray();
        }

        return $data;
    }


    /**
     * {@inheritdoc}
     */
    public function getPgms($p_list = null, $s_list, $p_type = [])
    {

        if (!empty($p_list)) {
            if (isset($p_type) && !empty($p_type)) {
                if (!in_array('all', $p_type)) {
                    if (in_array('collection', $p_type)) {
                        return Program::where('program_visibility', '=', 'yes')
                            ->where('status', '=', 'ACTIVE')
                            ->where('program_sellability', '=', 'yes')
                            ->where('program_sub_type', '=', 'collection')
                            ->where('program_type', 'content_feed')
                            ->whereIn('program_id', $p_list)->get($s_list)->toArray();
                    } elseif (in_array('content_feed', $p_type)) {
                        return Program::where('program_visibility', '=', 'yes')
                            ->where('status', '=', 'ACTIVE')
                            ->where('program_sellability', '=', 'yes')
                            ->where('program_sub_type', 'single')
                            ->whereIn('program_type', $p_type)
                            ->whereIn('program_id', $p_list)->get($s_list)->toArray();
                    } elseif (in_array('course', $p_type)) {
                        return Program::where('program_visibility', '=', 'yes')
                            ->where('status', '=', 'ACTIVE')
                            ->where('program_sellability', '=', 'yes')
                            ->where('program_sub_type', 'single')
                            ->whereIn('program_type', $p_type)
                            ->whereIn('program_id', $p_list)->get($s_list)->toArray();
                    } else {
                        return Program::where('program_visibility', '=', 'yes')
                            ->where('status', '=', 'ACTIVE')
                            ->where('program_sellability', '=', 'yes')
                            ->whereIn('program_type', $p_type)
                            ->whereIn('program_id', $p_list)->get($s_list)->toArray();
                    }
                } else {
                    return Program::where('program_visibility', '=', 'yes')
                        ->where('status', '=', 'ACTIVE')
                        ->where('program_sellability', '=', 'yes')
                        ->whereIn('program_id', $p_list)->get($s_list)->toArray();
                }
            } else {
                return Program::where('program_visibility', '=', 'yes')
                    ->where('status', '=', 'ACTIVE')
                    ->where('program_sellability', '=', 'yes')
                    ->whereIn('program_id', $p_list)->get($s_list)->toArray();
            }
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPackages($p_list = null, $s_list)
    {
        if (!empty($p_list)) {
                return Package::where('package_visibility', '=', 'yes')
                    ->where('status', '=', 'ACTIVE')
                    ->where('package_sellability', '=', 'yes')
                    ->whereIn('package_id', $p_list)->get($s_list)->toArray();
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPgmBySlug($slug, $s_list)
    {
        return Program::where('program_slug', '=', $slug)
            ->where('status', '!=', 'DELETED')
            ->get($s_list)->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageBySlug($slug, $s_list)
    {
        return Package::where('package_slug', '=', $slug)
            ->where('status', '!=', 'DELETED')
            ->get($s_list)->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getPackBySlug($p_slug, $s_list)
    {
        return Packet::where('feed_slug', '=', $p_slug)
            ->get($s_list)->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchedPrograms($search, $p_type, $start, $limit)
    {
        $now = time();
        $skip = $limit * $start;
        return Program::where('program_title', 'like', '%' . $search . '%')
            ->orWhere('program_description', 'like', '%' . $search . '%')
            ->orWhere('program_keywords', 'like', '%' . $search . '%')
            ->orWhere('program_slug', 'like', '%' . $search . '%')
            ->where('program_visibility', '=', 'yes')
            ->where('program_sellability', '=', 'yes')
            ->where('status', '=', 'ACTIVE')
            ->Where('program_display_startdate', '<=', $now)
            ->Where('program_display_enddate', '>=', $now)
            ->whereIn('program_type', $p_type)
            ->orderBy('created_at', 'desc')
            ->skip((int)$skip)
            ->take((int)$limit)
            ->get()
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchedPackage($search, $start, $limit)
    {
        $now = time();
        $skip = $limit * $start;
        return Package::where('package_title', 'like', '%' . $search . '%')
            ->orWhere('package_description', 'like', '%' . $search . '%')
            ->orWhere('package_keywords', 'like', '%' . $search . '%')
            ->orWhere('package_slug', 'like', '%' . $search . '%')
            ->where('package_visibility', '=', 'yes')
            ->where('package_sellability', '=', 'yes')
            ->where('status', '=', 'ACTIVE')
            ->Where('package_display_startdate', '<=', $now)
            ->Where('package_display_enddate', '>=', $now)
            ->orderBy('created_at', 'desc')
            ->skip((int)$skip)
            ->take((int)$limit)
            ->get()
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchedProgramsCount($search, $p_type)
    {
        $now = time();

        return Program::where('program_title', 'like', '%' . $search . '%')
            ->orWhere('program_description', 'like', '%' . $search . '%')
            ->orWhere('program_keywords', 'like', '%' . $search . '%')
            ->orWhere('program_slug', 'like', '%' . $search . '%')
            ->where('program_visibility', '=', 'yes')
            ->where('program_sellability', '=', 'yes')
            ->where('status', '=', 'ACTIVE')
            ->Where('program_display_startdate', '<=', $now)
            ->Where('program_display_enddate', '>=', $now)
            ->whereIn('program_type', $p_type)
            ->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchedPackageCount($search)
    {
        $now = time();

         return Package::where('package_title', 'like', '%' . $search . '%')
            ->orWhere('package_description', 'like', '%' . $search . '%')
            ->orWhere('package_keywords', 'like', '%' . $search . '%')
            ->orWhere('package_slug', 'like', '%' . $search . '%')
            ->where('package_visibility', '=', 'yes')
            ->where('package_sellability', '=', 'yes')
            ->where('status', '=', 'ACTIVE')
            ->Where('package_display_startdate', '<=', $now)
            ->Where('package_display_enddate', '>=', $now)
            ->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getSuggestionData($search)
    {
        $now = time();

        return Program::where('program_title', 'like', '%' . $search . '%')
            ->orWhere('program_description', 'like', '%' . $search . '%')
            ->orWhere('program_keywords', 'like', '%' . $search . '%')
            ->orWhere('program_slug', 'like', '%' . $search . '%')
            ->where('program_visibility', '=', 'yes')
            ->where('program_sellability', '=', 'yes')
            ->where('status', '=', 'ACTIVE')
            ->Where('program_display_startdate', '<=', $now)
            ->Where('program_display_enddate', '>=', $now)
            ->lists('program_title')
            ->all();
    }


    /**
     * {@inheritdoc}
     */
    public function getPackageSuggestionData($search)
    {
        $now = time();

        return Package::where('package_title', 'like', '%' . $search . '%')
            ->orWhere('package_description', 'like', '%' . $search . '%')
            ->orWhere('package_keywords', 'like', '%' . $search . '%')
            ->orWhere('package_slug', 'like', '%' . $search . '%')
            ->where('package_visibility', '=', 'yes')
            ->where('package_sellability', '=', 'yes')
            ->where('status', '=', 'ACTIVE')
            ->Where('package_display_startdate', '<=', $now)
            ->Where('package_display_enddate', '>=', $now)
            ->lists('package_title')
            ->all();
    }


    /**
     * {@inheritdoc}
     */
    public function getAllCategoryWithSellable($category_slug = null)
    {
        $now = time();
        
        $program_list = Program::
        where('program_visibility', '=', 'yes')
            ->where('program_sellability', '=', 'yes')
            ->where('status', '=', 'ACTIVE')
            ->Where('program_startdate', '<=', $now)
            ->Where('program_enddate', '>=', $now)
            ->where('program_sub_type', '!=', 'collection')
            ->get(['program_categories']);

        $package_list = Package::
        where('package_visibility', '=', 'yes')
            ->where('package_sellability', '=', 'yes')
            ->where('status', '=', 'ACTIVE')
            ->Where('package_startdate', '<=', $now)
            ->Where('package_enddate', '>=', $now)
            ->get(['category_ids']);
            
        $program_category = $package_category = [];
        $list = $program_list->toArray();
        $program_category = array_column($list, 'program_categories');
        $plist = $package_list->toArray();
        $package_category = array_column($plist, 'category_ids');
        $category_list = array_merge($program_category, $package_category);
        if (!empty($category_list)) {
            $flatten_category_list = array_flatten($category_list);
            if (!empty($flatten_category_list)) {
                $unique_category_list = array_unique($flatten_category_list);
                
                if (!empty($category_slug)) {
                    $category_list = Category::where('slug', '=', $category_slug)
                        ->whereIn('category_id', $unique_category_list)
                        ->where('status', '=', 'ACTIVE')
                        ->where('parents', '=', null)
                        ->get();
                } else {
                    $category_list = Category::whereIn('category_id', $unique_category_list)
                        ->where('status', '=', 'ACTIVE')
                        ->where('parents', '=', null)
                        ->get();
                }

                if ($category_list->isEmpty()) {
                    return Category::where('slug', '=', $category_slug)
                        ->whereIn('category_id', $unique_category_list)
                        ->where('status', '=', 'ACTIVE')
                        ->get();
                } else {
                    return $category_list;
                }
            }
        }
        return collect([]);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryByIDList($category_list)
    {
        return Category::whereIn('category_id', $category_list)
            ->where('status', '=', 'ACTIVE')
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramSearch($p_list = null, $search = null, $p_type = [])
    {
        if (!empty($p_list)) {
            if (isset($p_type) && !empty($p_type)) {
                if (!in_array('all', $p_type)) {
                    $program_list = [];
                    foreach ($p_type as $key => $value) {
                        $data = Program::where('program_visibility', '=', 'yes')
                            ->where('status', '=', 'ACTIVE')
                            ->where('program_sellability', '=', 'yes')
                            ->where('program_display_startdate', '<=', time())
                            ->where('program_display_enddate', '>=', time())
                            ->whereIn('program_id', $p_list)
                            ->orderBy('program_display_startdate', 'asc')
                            ->programType($value, $search)
                            ->get()->toArray();
                        $program_list = array_merge($program_list, $data);
                    }
                    return $program_list;
                } else {
                    if (!empty($search)) {
                        return Program::where('program_visibility', '=', 'yes')
                            ->where('program_title', 'like', "%$search%")
                            ->orWhere('program_description', 'like', "%$search%")
                            ->orWhere('program_keywords', 'like', "%$search%")
                            ->orWhere('program_slug', 'like', "%$search%")
                            ->where('status', '=', 'ACTIVE')
                            ->where('program_display_startdate', '<=', time())
                            ->where('program_display_enddate', '>=', time())
                            ->where('program_sellability', '=', 'yes')
                            ->orderBy('program_display_startdate', 'asc')
                            ->whereIn('program_id', $p_list)->get()->toArray();
                    }
                    return Program::where('program_visibility', '=', 'yes')
                        ->where('status', '=', 'ACTIVE')
                        ->where('program_display_startdate', '<=', time())
                        ->where('program_display_enddate', '>=', time())
                        ->where('program_sellability', '=', 'yes')
                        ->whereIn('program_id', $p_list)
                        ->orderBy('program_display_startdate', 'asc')
                        ->get()->toArray();
                }
            } else {
                return Program::where('program_visibility', '=', 'yes')
                    ->where('status', '=', 'ACTIVE')
                    ->where('program_sellability', '=', 'yes')
                    ->where('program_display_startdate', '<=', time())
                    ->where('program_display_enddate', '>=', time())
                    ->whereIn('program_id', $p_list)
                    ->orderBy('program_display_startdate', 'asc')
                    ->get()->toArray();
            }
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageSearch($p_list = null, $search = null, $p_type = [])
    {
        if (!empty($p_list)) {
            if (isset($p_type) && !empty($p_type)) {
                if (in_array('collection', $p_type) && !empty($search)) {
                    return Package::where('package_visibility', '=', 'yes')
                        ->where('package_title', 'like', "%$search%")
                        ->orWhere('package_description', 'like', "%$search%")
                        ->orWhere('package_keywords', 'like', "%$search%")
                        ->orWhere('package_slug', 'like', "%$search%")
                        ->where('status', '=', 'ACTIVE')
                        ->where('package_display_startdate', '<=', time())
                        ->where('package_display_enddate', '>=', time())
                        ->where('package_sellability', '=', 'yes')
                        ->orderBy('package_display_startdate', 'asc')
                        ->whereIn('package_id', $p_list)->get()->toArray();
                } elseif (in_array('collection', $p_type)) {
                    return Package::where('package_visibility', '=', 'yes')
                        ->where('status', '=', 'ACTIVE')
                        ->where('package_display_startdate', '<=', time())
                        ->where('package_display_enddate', '>=', time())
                        ->where('package_sellability', '=', 'yes')
                        ->whereIn('package_id', $p_list)
                        ->orderBy('package_display_startdate', 'asc')
                        ->get()->toArray();
                } else {
                    return null;
                }
            } else {
                return Package::where('package_visibility', '=', 'yes')
                    ->where('status', '=', 'ACTIVE')
                    ->where('package_display_startdate', '<=', time())
                    ->where('package_display_enddate', '>=', time())
                    ->where('package_sellability', '=', 'yes')
                    ->whereIn('package_id', $p_list)
                    ->orderBy('package_display_startdate', 'asc')
                    ->get()->toArray();
            }
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getNoCategoryProgramIds()
    {
        $program_list = Program::where('program_visibility', '=', 'yes')
            ->where('status', '=', 'ACTIVE')
            ->where(function ($query) {
                $query->where('parent_id', 'exists', false)
                    ->orWhere('parent_id', '=', 0);
            })
            ->where('program_sellability', '=', 'yes')
            ->where('program_display_startdate', '<=', time())
            ->where('program_display_enddate', '>=', time())
            ->where('program_categories', 'size', 0)
            ->orderBy('program_display_startdate', 'asc')
            ->get()->toArray();
        return array_column($program_list, 'program_id');
    }

    /**
     * {@inheritdoc}
     */
    public static function getNoCategoryPackageIds()
    {
        $package_list = Package::where('package_visibility', '=', 'yes')
            ->where('status', '=', 'ACTIVE')
            ->where('package_display_startdate', '<=', time())
            ->where('package_display_enddate', '>=', time())
            ->where(
                function ($query) {
                    $query->where('category_ids', 'exists', false)
                        ->orWhere('category_ids', 'size', 0);
                }
            )
            ->where('package_sellability', '=', 'yes')
            ->Where('category_ids', 'size', 0)
            ->orderBy('package_display_startdate', 'asc')
            ->get()->toArray();
        return array_column($package_list, 'package_id');
    }
}
