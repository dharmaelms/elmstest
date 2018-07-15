<?php

namespace App\Model\Category\Repository;

use App\Model\Category;

/**
 * Class CategoryRepository
 * @package App\Model\Category\Repository
 */
class CategoryRepository implements ICategoryRepository
{
    /**
     * {@inheritdoc}
     */
    public function getCategories($channels)
    {
        return Category::whereIn('relations.assigned_feeds', $channels['channel_ids'])
            ->orwhereIn('package_ids', array_get($channels, 'package_ids', []))
            ->orderBy('created_at', 'DESC')
            ->get();
    }
    /**
     * {@inheritdoc}
     */
    public function getPackagesByAttribute($column, $value)
    {
        return Category::whereIn($column, $value)->active()->get();
    }

    public function filter($filter_param)
    {
        return Category::Filter(['program_ids' => $filter_param])->get();
    }

    public function getCategoryDetails($Category)
    {
        return Category::Filter(['category_id' => $Category])->get();
    }
}
