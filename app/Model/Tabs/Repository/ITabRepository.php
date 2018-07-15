<?php
namespace App\Model\Tabs\Repository;

/**
 * Interface ITabRepository
 * @package App\Model\Tabs\Repository
 */
interface ITabRepository
{
    /**
     * @param $p_id
     * @param $tabs
     * @return mixed
     */
    public function save($p_id, $tabs);

    /**
     * @return mixed
     */
    public function update();

    /**
     * @param $p_id
     * @return mixed
     */
    public function getTabs($p_id);

    /**
     * @param $p_id
     * @param $slug
     * @return mixed
     */
    public function getTabBySlug($p_id, $slug);

    /**
     * @param $p_id
     * @return mixed
     */
    public function getPackageTabs($p_id);

    /**
     * @param $p_id
     * @param $tabs
     * @return mixed
     */
    public function savePackageTab($p_id, $tabs);

    /**
     * @param $p_id
     * @param $slug
     * @return mixed
     */
    public function getPackageTabBySlug($p_id, $slug);
}
