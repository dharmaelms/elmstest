<?php

namespace App\Model\Category\Repository;

/**
 * Interface ICategoryRepository
 * @package App\Model\Category\Repository
 */
interface ICategoryRepository
{
    /**
     * Method to get categories for given channels
     * @param array $channels
     * @return \Illuminate\Support\Collection
     */
    public function getCategories($channels);

    /**
     * Method to get packages from categories by using given column
     *
     * @param string $column
     * @param array $value
     * @return \Illuminate\Support\Collection
     */
    public function getPackagesByAttribute($column, $value);

    /**
     * Method to get categories by using given column
     *
     * @param array $filter_param
     * @return \Illuminate\Support\Collection
     */
    public function filter($filter_param);
}
