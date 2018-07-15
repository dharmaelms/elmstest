<?php

namespace App\Services\Catalog\CatList;

/**
 * Interface ICatalogService
 * @package App\Services\Catalog\CatList
 */
interface ICatalogService
{
    /**
     * @param $c_select
     * @param $catlist
     * @param $g_type
     * @return mixed
     */
    public function getCategoryList($c_select, $catlist, $g_type);

    /**
     * @param $g_type
     * @param $catlist
     * @param $p_type
     * @param $s_data
     * @return mixed
     */
    public function catWithProgram($g_type = null, $catlist = null, $p_type = [], $s_data = '');

    /**
     * @param $g_type
     * @param $catlist
     * @param $p_type
     * @param $s_data
     * @return mixed
     */
    public function catWithPackage($g_type = null, $catlist = null, $p_type = [], $s_data = '');

    /**
     * @param $p_slug
     * @param null $c_select
     * @return mixed
     */
    public function getCourse($p_slug, $c_select = null);

    /**
     * @param $p_slug
     * @param null $c_select
     * @return mixed
     */
    public function getPackage($p_slug, $c_select = null);

    /**
     * @param $rel
     * @return mixed
     */
    public function getRelatedCourse($rel);

    /**
     * @param $rel
     * @return mixed
     */
    public function getRelatedPackage($rel);

    /**
     * @param $p_slug
     * @return mixed
     */
    public function getPostList($p_slug);

    /**
     * @param $id
     * @return mixed
     */
    public function getCategoryDetails($id);

    /**
     * @param $search
     * @param $p_type
     * @param $start
     * @param $limit
     * @return mixed
     */
    public function getSearchedData($search, $p_type, $start, $limit);

    /**
     * @param $search
     * @param $p_type
     * @param $start
     * @param $limit
     * @return mixed
     */
    public function getPackageSearchedData($search, $start, $limit);

    /**
     * @param $search
     * @param $p_type
     * @return mixed
     */
    public function getSearchedCount($search, $p_type);

    /**
     * @param $search
     * @param $p_type
     * @return mixed
     */
    public function getPackageSearchedCount($search);

    /**
     * @param $search
     * @return mixed
     */
    public function getSuggestionData($search);

    /**
     * @param $search
     * @return mixed
     */
    public function getPackageSuggestionData($search);

    /**
     * @param $category_slug
     * @param $search_string
     * @param $program_type
     * @return mixed
     */
    public function getAllCategoryWithSellable($category_slug = null, $search_string = null, $program_type = []);

    /**
     * replacementForMedia create pattern for replace the akamai video with thumnail image
     * @param  array $tab_description deails are here
     * @return array
     */
    public function replacementForMedia($tab_description);

}
