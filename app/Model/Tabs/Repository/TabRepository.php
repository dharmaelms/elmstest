<?php

namespace App\Model\Tabs\Repository;

use App\Model\Program;
use App\Model\Package\Entity\Package;

/**
 * Class TabRepository
 * @package App\Model\Tabs\Repository
 */
class TabRepository implements ITabRepository
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function save($p_id, $tabs)
    {
        Program::where('program_id', '=', (int)$p_id)->update(["tabs" => $tabs]);
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function update()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getTabs($p_id)
    {
        return Program::where('program_id', '=', (int)$p_id)->get(['tabs', 'program_slug'])->toArray();
    }

    /**
     * @param $p_id
     * @return mixed
     */
    public function getPackageTabs($p_id)
    {
        return Package::where('package_id', '=', (int)$p_id)->get(['tabs', 'package_slug'])->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getTabBySlug($p_id, $slug)
    {
        return Program::where('program_id', '=', (int)$p_id)->where('tabs.slug', '=', $slug)->get(['tabs', 'program_slug'])->toArray();
    }

    /**
     * @param $p_id
     * @param $tabs
     * @return mixed
     */
    public function savePackageTab($p_id, $tabs)
    {
        return Package::where('package_id', '=', (int)$p_id)->update(["tabs" => $tabs]);
    }

    /**
     * @param $p_id
     * @param $slug
     * @return mixed
     */
    public function getPackageTabBySlug($p_id, $slug)
    {
        return Package::where('package_id', '=', (int)$p_id)->where('tabs.slug', '=', $slug)->get(['tabs', 'package_slug'])->toArray();
    }
}
