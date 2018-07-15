<?php

namespace App\Services\Catalog\CatList;

use App\Model\Catalog\CatList\Repository\ICatalogListRepository;
use App\Model\Category;
use App\Services\Catalog\Pricing\IPricingService;
use Request;
use URL;

/**
 * Class CatalogService
 * @package App\Services\Catalog\CatList
 */
class CatalogService implements ICatalogService
{
    /**
     * @var ICatalogListRepository
     */
    private $catalog_list_repository;

    /**
     * @var IPricingService
     */
    private $pricing_service;

    public function __construct(
        ICatalogListRepository $catalog_list_repository,
        IPricingService $pricing_service
    ) {
    
        $this->catalog_list_repository = $catalog_list_repository;
        $this->pricing_service = $pricing_service;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryList($c_select = null, $cat_list = null, $g_type = null)
    {
        $d_select = ['category_name', 'category_description', 'slug', 'status', 'children'];
        if (!empty($c_select) && is_array($c_select)) {
            $d_select = array_merge($d_select, $c_select);
        }
        $rs_cat = $this->catalog_list_repository->getAllCategory($d_select, $cat_list, $g_type);

        return $rs_cat;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryPackageList($c_select = null, $cat_list = null, $g_type = null)
    {
        $d_select = ['category_name', 'category_description', 'slug', 'status', 'children'];
        
        if (!empty($c_select) && is_array($c_select)) {
            $d_select = array_merge($d_select, $c_select);
        }

        $rs_cat = $this->catalog_list_repository->getAllPackageCategory($d_select, $cat_list, $g_type);

        return $rs_cat;
    }

    /**
     * {@inheritdoc}
     */
    public function catWithProgram($g_type = null, $cat_list = null, $p_type = [], $s_data = '')
    {

        if (!empty($g_type) && $g_type != "list") {
            if ($g_type === "basic") {
                $c_select = ['relations'];
                $data = $this->getCategoryList($c_select, $cat_list, $g_type);
                $i = 0;
                $tempArray = [];
                foreach ($data as $key => $value) {
                    if (isset($value['relations']) && !empty($value['relations'])) {
                        $tempArray[$i] = array_merge(
                            $value,
                            ['programs' => $this->coutRel($value['relations'], $p_type)
                            ]
                        );
                    } else {
                        $tempArray[$i] = $value;
                    }
                    $i++;
                }

                return $tempArray;
            }
        } else {
            $c_select = ['relations'];
            $data = $this->getCategoryList($c_select, '', $g_type);
            $i = 0;
            $tempArray = [];
            foreach ($data as $key => $value) {
                if (isset($value['relations']) && !empty($value['relations'])) {
                    $tempArray[$i] = array_merge(
                        $value,
                        ['programs' => $this->coutRel($value['relations'], $p_type)
                        ]
                    );
                } else {
                    $tempArray[$i] = $value;
                }
                $i++;
            }

            return $tempArray;
        }
    }

     /**
     * {@inheritdoc}
     */
    public function catWithPackage($g_type = null, $cat_list = null, $p_type = [], $s_data = '')
    {

        if (!empty($g_type) && $g_type != "list") {
            if ($g_type === "basic") {
                $c_select = ['package_ids'];
                $data = $this->getCategoryPackageList($c_select, $cat_list, $g_type);
                $i = 0;
                $tempArray = [];
                foreach ($data as $key => $value) {
                    if (isset($value['package_ids']) && !empty($value['package_ids'])) {
                        $tempArray[$i] = array_merge(
                            $value,
                            ['packages' => $this->coutPackageRel($value['package_ids'])
                            ]
                        );
                    } else {
                        $tempArray[$i] = $value;
                    }
                    $i++;
                }
                return $tempArray;
            }
        } else {
            $c_select = ['package_ids'];
            $data = $this->getCategoryList($c_select, '', $g_type);
            $i = 0;
            $tempArray = [];
            foreach ($data as $key => $value) {
                if (isset($value['package_ids']) && !empty($value['package_ids'])) {
                    $tempArray[$i] = array_merge(
                        $value,
                        ['packages' => $this->coutPackageRel($value['package_ids'])
                        ]
                    );
                } else {
                    $tempArray[$i] = $value;
                }
                $i++;
            }

            return $tempArray;
        }
    }

    private function coutRel($rel, $p_type)
    {

        if (!empty($rel)) {
            $p_array = [];
            $all_prgs = [];
            foreach ($rel as $key => $value) {
                $programs = $this->programList($value, $p_type);

                if (isset($programs[0]) && !empty($programs[0])) {
                    foreach ($programs as $echprg) {
                        $p_array[] = $echprg;
                    }
                }

                $all_prgs = $p_array;
            }
            return $all_prgs;
        } else {
            return null;
        }
    }

    private function coutPackageRel($rel)
    {
        if (!empty($rel)) {
            $p_array = [];
            $all_prgs = [];
            foreach ($rel as $key => $value) {
                $packages = $this->packageList([$value]);
                if (isset($packages[0]) && !empty($packages[0])) {
                    foreach ($packages as $echprg) {
                        $p_array[] = $echprg;
                    }
                }

                $all_prgs = $p_array;
            }
            return $all_prgs;
        } else {
            return null;
        }
    }

    private function programList($p_list, $p_type = [])
    {

        if (!empty($p_list)) {
            $d_select = ['program_id', 'program_title', 'program_slug', 'program_description', 'program_cover_media', 'program_type', 'program_startdate', 'program_enddate', 'program_display_startdate', 'program_display_startdate', 'program_sub_type'];
            $data = $this->catalog_list_repository->getPgms($p_list, $d_select, $p_type);
            $data = $this->attachPriceProgram($data);
            return $data;
        } else {
            return null;
        }
    }

    private function packageList($p_list)
    {
        if (!empty($p_list)) {
            $d_select = ['package_id', 'package_title', 'package_slug', 'package_description', 'package_cover_media', 'package_startdate', 'package_enddate', 'package_display_startdate', 'package_display_startdate'];
            $data = $this->catalog_list_repository->getPackages($p_list, $d_select);
            $data = $this->attachPricePackage($data);
            return $data;
        } else {
            return null;
        }
    }

    private function attachPriceProgram($data)
    {
        if (!empty($data)) {
            $templist = collect();
            foreach ($data as $key => $value) {
                $i_data = ['sellable_id' => $value['program_id'], 'sellable_type' => $value['program_type']];
                /* $vertical variable is commented because in catalog page, program price is not getting displayed and this querie can be reduced $verticals = $this->pricing_service->priceFirst($i_data);*/
                $verticals = collect([]);
                if (!empty($verticals)) {
                    $s = array_merge($value, ['vertical' => $verticals->all()]);
                    $templist->push($s);
                }
            }
            return $templist->all();
        }
    }

    private function attachPricePackage($data)
    {
        if (!empty($data)) {
            $templist = collect();
            foreach ($data as $key => $value) {
                $i_data = ['sellable_id' => $value['package_id'], 'sellable_type' => 'package'];
                /* $vertical variable is commented because in catalog page, program price is not getting displayed and this querie can be reduced $verticals = $this->pricing_service->priceFirst($i_data); */
                $verticals = collect([]);
                if (!empty($verticals)) {
                    $s = array_merge($value, ['vertical' => $verticals->all()]);
                    $templist->push($s);
                }
            }
            return $templist->all();
        }
    }

    //Course Relative
    /**
     * {@inheritdoc}
     */
    public function getCourse($p_slug, $c_select = null)
    {
        $d_select = ['program_id', 'program_title', 'program_slug', 'program_description', 'program_type', 'program_cover_media', 'program_sub_type'];
        if (!empty($c_select) && is_array($c_select)) {
            $d_select = array_merge($d_select, $c_select);
        }
        if (!empty($p_slug)) {
            $rs_data = $this->catalog_list_repository->getPgmBySlug($p_slug, $d_select);
            if (!empty($rs_data)) {
                return $rs_data;
            } else {
                return null;
            }
        } else {
            return "Program Slug Required";
        }
    }

     /**
     * {@inheritdoc}
     */
    public function getPackage($p_slug, $c_select = null)
    {
        $d_select = ['package_id', 'package_title', 'package_slug', 'package_description','package_cover_media'];
        if (!empty($c_select) && is_array($c_select)) {
            $d_select = array_merge($d_select, $c_select);
        }
        if (!empty($p_slug)) {
            $rs_data = $this->catalog_list_repository->getPackageBySlug($p_slug, $d_select);
            if (!empty($rs_data)) {
                return $rs_data;
            } else {
                return null;
            }
        } else {
            return "Program Slug Required";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRelatedCourse($rel)
    {
        $data = $this->catWithProgram("basic", $rel);
        $tempArray = [];
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (isset($value['programs'])) {
                    $tempArray = array_merge($tempArray, $value['programs']);
                }
            }
        }
        return $tempArray;
    }

    public function getRelatedPackage($rel)
    {
        $data = $this->catWithPackage("basic", $rel);
        $tempArray = [];
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (isset($value['packages'])) {
                    $tempArray = array_merge($tempArray, $value['packages']);
                }
            }
        }
        return $tempArray;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostList($p_slug)
    {
        $d_select = ['packet_id', 'packet_title', 'packet_slug', 'feed_slug', 'packet_description', 'packet_cover_media'];
        if (!empty($c_select) && is_array($c_select)) {
            $d_select = array_merge($d_select, $c_select);
        }
        if (!empty($p_slug)) {
            $rs_data = $this->catalog_list_repository->getPackBySlug($p_slug, $d_select);
            if (!empty($rs_data)) {
                return $rs_data;
            } else {
                return null;
            }
        } else {
            return "Packet Slug Required";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryDetails($id)
    {
        $d_select = ['category_name', 'category_description', 'category_id', 'slug'];
        $rs_data = $this->catalog_list_repository->getAllCategory($d_select, [$id]);
        if (!empty($rs_data)) {
            return collect($rs_data)->first();
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchedData($search, $p_type, $start = 0, $limit = 10)
    {
        $s_data = $this->catalog_list_repository->getSearchedPrograms($search, $p_type, $start, $limit);
        $s_data = $this->attachPriceProgram($s_data);
        return $s_data;
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageSearchedData($search, $start = 0, $limit = 10)
    {
        $s_data = $this->catalog_list_repository->getSearchedPackage($search, $start, $limit);
        $s_data = $this->attachPricePackage($s_data);
        return $s_data;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchedCount($search, $p_type)
    {
        return $this->catalog_list_repository->getSearchedProgramsCount($search, $p_type);
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageSearchedCount($search)
    {
        return $this->catalog_list_repository->getSearchedPackageCount($search);
    }

    /**
     * {@inheritdoc}
     */
    public function getSuggestionData($search)
    {
        return $this->catalog_list_repository->getSuggestionData($search);
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageSuggestionData($search)
    {
        return $this->catalog_list_repository->getPackageSuggestionData($search);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllCategoryWithSellable($category_slug = null, $search_string = null, $program_type = [])
    {
        $data = $this->catalog_list_repository->getAllCategoryWithSellable($category_slug);
        $sub_value = $subcategory_list_id = [];
        $sub_cats = collect([]);
        
        /*This is to contruct the array of program and packages for parent categories */
        if (!empty($data)) {
            $category = $data->map(function ($item, $key) {
                $sub_cat_package = $sub_cat_feed = $sub_cat_course =  [];
                if (isset($item->children)) {
                    $subcategory_list_id = array_column($item->children, 'category_id');
                }
                if (isset($item->parents) && !is_null($item->parents)) {
                    $category_parents_id= $item->parents;
                }
                if (isset($item->package_ids) && is_array($item->package_ids)) {
                    $sub_cat_package = $item->package_ids;
                }
                if (isset($item->relations['assigned_feeds']) && is_array($item->relations['assigned_feeds'])) {
                    $sub_cat_feed = $item->relations['assigned_feeds'];
                }
                if (isset($item->relations['assigned_courses']) && is_array($item->relations['assigned_courses'])) {
                    $sub_cat_course = $item->relations['assigned_courses'];
                }
                $sub_cat_program = array_merge($sub_cat_feed, $sub_cat_course);
                $item['category_id']  = $item->category_id;
                $item['subcategory_list_id'] = isset($subcategory_list_id) ? $subcategory_list_id : [];
                $item['parent_ids'] = isset($category_parents_id) ? $category_parents_id : [];
                $item['package_list_ids'] = $sub_cat_package;
                $item['program_list_ids'] = $sub_cat_program;
                return $item;
            });
            /*collecting all the categories program and package id and getting its records */
            $all_cat_program_id = $category->pluck('program_list_ids')->flatten();
            $all_cat_program_details = $this->getSearchProgram($all_cat_program_id, $search_string, $program_type);
            $all_cat_package_id = $category->pluck('package_list_ids')->flatten();
            $all_cat_package_details = $this->getSearchPackage($all_cat_package_id, $search_string, $program_type);
            $all_subcat_ids = $category->pluck('subcategory_list_id')->flatten();
            $all_sub_category_details = $this->catalog_list_repository->getCategoryByIDList($all_subcat_ids);

            /*This is to contruct the array of program and packages for child categories */
            $sub_cats = $all_sub_category_details->map(function ($item, $key) {
                $sub_cat_package = $sub_cat_feed = $sub_cat_course = [];
                if (isset($item->package_ids) && is_array($item->package_ids)) {
                    $sub_cat_package = $item->package_ids;
                }
                if (isset($item->relations['assigned_feeds']) && is_array($item->relations['assigned_feeds'])) {
                    $sub_cat_feed = $item->relations['assigned_feeds'];
                }
                if (isset($item->relations['assigned_courses']) && is_array($item->relations['assigned_courses'])) {
                    $sub_cat_course = $item->relations['assigned_courses'];
                }
                $sub_cat_program = array_merge($sub_cat_feed, $sub_cat_course);
                $item['sub_category_id'] = $item->category_id;
                $item['sub_cat_package_list'] = $sub_cat_package;
                $item['sub_cat_program_list'] = $sub_cat_program;
                return $item;
            });
            /*collecting all the sub-categories program and package id and getting its records */
            $all_subcat_program_id = $sub_cats->pluck('sub_cat_program_list')->flatten();
            $all_subcat_program_details = $this->getSearchProgram($all_subcat_program_id, $search_string, $program_type);
            $all_subcat_package_id = $sub_cats->pluck('sub_cat_package_list')->flatten();
            $all_subcat_package_details = $this->getSearchPackage($all_subcat_package_id, $search_string, $program_type);

            /* Constructing final array for view page*/
            foreach ($category as $key => &$value) {
                $data = is_null($value->parents) ? $data->sortByDesc('created_at') : $data;
                $parent_program_list = collect($all_cat_program_details)->whereIn('program_id', $value->program_list_ids)->toArray();
                $parent_package_list = collect($all_cat_package_details)->whereIn('package_id', $value->package_list_ids)->toArray();
                $sub_category = $all_sub_category_details->whereIn('category_id', $value->subcategory_list_id);

                if (isset($value->children)) {
                    foreach ($sub_cats as $key => &$sub_value) {
                        $program_list = collect($all_subcat_program_details)->whereIn('program_id', $sub_value->sub_cat_program_list)->toArray();
                        $package_list = collect($all_subcat_package_details)->whereIn('package_id', $sub_value->sub_cat_package_list)->toArray();

                        if (is_null($category_slug)) {
                            $program_list_ids = array_unique(array_merge($value->program_list_ids, $sub_value->sub_cat_program_list));
                            $package_list_ids = array_unique(array_merge($value->package_list_ids, $sub_value->sub_cat_package_list));
                        } else {
                            $data[] = $sub_value;
                            $sub_value['program_list'] = $program_list;
                            $sub_value['package_list'] = $package_list;
                            $value['sub_category'] = $sub_category;
                        }
                        $data = is_null($sub_value->parents) ? $data->sortByDesc('created_at') : $data;
                    }/*End of sub-category foreach */
                }

                if (isset($value->parents) && !is_null($value->parents)) {
                    $value['category_parents'] = $this->catalog_list_repository->getCategoryByIDList([$value->parents]);
                }
                $value['program_list'] = $parent_program_list;
                $value['package_list'] = $parent_package_list;
            }/*End of category foreach */
        } else {
            return null;
        }
        /* To get miscellaneous categories getNoCategory() */
        $data = $this->getNoCategory($category_slug, $search_string, $program_type, $data);
        return $data;
    }

    private function getNoCategory($category_slug, $search_string, $program_type, $data)
    {
        $no_category = function () use ($search_string, $program_type, $data) {
            $program_list = $package_list = [];
            $no_program_ids = $this->catalog_list_repository->getNoCategoryProgramIds();
            $no_package_ids = $this->catalog_list_repository->getNoCategoryPackageIds();
            $program_list = $this->getSearchProgram($no_program_ids, $search_string, $program_type);
            $package_list = $this->getSearchPackage($no_package_ids, $search_string, $program_type);

            $category = new Category();
            $category->category_name = trans('catalog/template_two.no_category');
            $category->category_description = trans('catalog/template_two.category_description');
            $category->slug = 'no-category';
            $category->program_list = $program_list;
            $category->package_list = $package_list;
            $collection = collect([$category]);
            return $data->merge($collection);
        };

        if (empty($category_slug)) {
            return $no_category();
        } elseif ($category_slug == 'no-category') {
            return $no_category();
        } else {
            return $data;
        }
    }

    private function getCategoryMappedPrograms($value)
    {
        $temp_program_list = [];
        $function_int = function ($items) {
            return (int)$items;
        };

        if (isset($value->relations['assigned_products']) && is_array($value->relations['assigned_products'])) {
            $temp_program_list = array_merge($temp_program_list, $value->relations['assigned_products']);
        }
        if (isset($value->relations['assigned_feeds']) && is_array($value->relations['assigned_feeds'])) {
            $temp_program_list = array_merge($temp_program_list, $value->relations['assigned_feeds']);
        }
        if (isset($value->relations['assigned_courses']) && is_array($value->relations['assigned_courses'])) {
            $temp_program_list = array_merge($temp_program_list, $value->relations['assigned_courses']);
        }
        return array_map($function_int, $temp_program_list);
    }


    private function getCategoryMappedPackages($value)
    {
        $temp_package_list = [];

        if (isset($value->package_ids) && is_array($value->package_ids)) {
            $temp_package_list = array_merge($temp_package_list, $value->package_ids);
        }

        return $temp_package_list;
    }

    private function getSearchProgram($p_list, $search_string, $p_type)
    {
        $r_data = $this->catalog_list_repository->getProgramSearch($p_list, $search_string, $p_type);
        $data = $this->attachPriceProgram($r_data);
        return $data;
    }

    private function getSearchPackage($p_list, $search_string, $p_type)
    {
        $r_data = $this->catalog_list_repository->getPackageSearch($p_list, $search_string, $p_type);
        $data = $this->attachPricePackage($r_data);
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function replacementForMedia($tab_description)
    {
        $pattern = '/((http|https):\/\/)?('.Request::getHttpHost().')\/(media)\/([a-z0-9]+)/';
        preg_match_all($pattern, $tab_description, $matches);
        $media_ids = last($matches);
        if (!empty($media_ids)) {
            $pattern = [];
            $replacement = [];
            foreach ($media_ids as $_id) {
                $pattern[] = '/(<iframe)(( )([a-zA-Z-]+)=("[a-zA-Z0-9-]*?"))*? src="((http|https):\/\/)?('.Request::getHttpHost().')\/(media)\/('.$_id.')+"(( )([a-zA-Z-]+)=("[a-zA-Z-0-9]+"))*?>(<\/iframe>)/';
                $replacement[] = '<div class="default-img"><img class="need_login" src="'.URL::to('/media_image/'.$_id.'?return=thumbnail&width=300').'"><img src="'.URL::to('portal/theme/default/img/play.jpg').'" class="overlay"/></div><br>';
            }
            $replaced_tab = preg_replace($pattern, $replacement, $tab_description);
        } else {
            $replaced_tab = $tab_description;
        }
        return $replaced_tab;
    }

    public function getCategoryAndSubCategory($parent_id)
    {
        $parent_details = $this->catalog_list_repository->getCategoryByIDList([$parent_id]);
        $sub_categorys = $parent_details->first()->children;
        $sub_category_ids = [];
        if (!empty($sub_categorys)) {
            $sub_category_ids = array_pluck($sub_categorys, 'category_id');
        }
        $parent_details->first()['sub_category'] = $this->catalog_list_repository->getCategoryByIDList($sub_category_ids);
        return $parent_details;
    }
}
