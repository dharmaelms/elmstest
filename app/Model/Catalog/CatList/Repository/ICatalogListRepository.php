<?php
namespace App\Model\Catalog\CatList\Repository;

/**
 * Interface ICatalogListRepository
 * @package App\Model\Catalog\CatList\Repository
 */
interface ICatalogListRepository
{
    /**
     * @param $s_list
     * @param null $catlist
     * @param string $g_type
     * @return mixed
     */
    public function getAllCategory($s_list, $catlist = null, $g_type = '');

    /**
     * @param $s_list
     * @param null $catlist
     * @param string $g_type
     * @return mixed
     */
    public function getAllPackageCategory($s_list, $catlist = null, $g_type = '');

    /**
     * @param null $p_list
     * @param $s_list
     * @param array $p_type
     * @return mixed
     */
    public function getPgms($p_list = null, $s_list, $p_type = []);

    /**
     * @param null $p_list
     * @param $s_list
     * @return mixed
     */
    public function getPackages($p_list = null, $s_list);

    /**
     * @param $slug
     * @param $s_list
     * @return mixed
     */
    public function getPgmBySlug($slug, $s_list);

    /**
     * @param $slug
     * @param $s_list
     * @return mixed
     */
    public function getPackageBySlug($slug, $s_list);

    /**
     * @param $p_slug
     * @param $s_list
     * @return mixed
     */
    public function getPackBySlug($p_slug, $s_list);

    /**
     * @param $search
     * @param $p_type
     * @param $start
     * @param $limit
     * @return mixed
     */
    public function getSearchedPrograms($search, $p_type, $start, $limit);

    /**
     * @param $search
     * @param $start
     * @param $limit
     * @return mixed
     */
    public function getSearchedPackage($search, $start, $limit);

    /**
     * @param $search
     * @param $p_type
     * @return mixed
     */
    public function getSearchedProgramsCount($search, $p_type);

    /**
     * @param $search
     * @param $p_type
     * @return mixed
     */
    public function getSearchedPackageCount($search);

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
     * @param null $category_slug
     * @return mixed
     */
    public function getAllCategoryWithSellable($category_slug = null);

    /**
     * @param $category_list
     * @return mixed
     */
    public function getCategoryByIDList($category_list);

    /**
     * @param null $p_list
     * @param null $search
     * @param array $p_type
     * @return mixed
     */
    public function getProgramSearch($p_list = null, $search = null, $p_type = []);

    /**
     * @param null $p_list
     * @param null $search
     * @return mixed
     */
    public function getPackageSearch($p_list = null, $search = null, $p_type = []);

    /**
     * @return mixed
     */
    public static function getNoCategoryProgramIds();

    /**
     * @return mixed
     */
    public static function getNoCategoryPackageIds();
}
