<?php
namespace App\Services\Tabs;

interface ITabService
{
    /**
     * @param $data
     * @return mixed
     */
    public function saveTab($data);

    /**
     * @param $data
     * @return mixed
     */
    public function saveEditTab($data);

    /**
     * @param $title
     * @return mixed
     */
    public function mSlug($title);

    /**
     * @param $pid
     * @return mixed
     */
    public function getTabs($pid);

    /**
     * @param $p_id
     * @param $slug
     * @return mixed
     */
    public function deleteTab($p_id, $slug);

    /**
     * @param $pid
     * @param $slug
     * @return mixed
     */
    public function getTabBySlug($pid, $slug);

    /**
     * @param $p_id
     * @param $p_type
     * @param $title
     * @param $c_slug
     * @return mixed
     */
    public function cDuplicate($p_id, $p_type, $title, $c_slug = null);

    /**
     * @param $p_id
     * @param $p_type
     * @param $title
     * @param $c_slug
     * @return mixed
     */
    public function checkDuplicateTab($p_id, $p_type, $title, $c_slug = null);

    /**
     * @param $data
     * @return mixed
     */
    public function savePackageTab($data);

    /**
     * @param $pid
     * @param $slug
     * @return mixed
     */
    public function getPackageTabBySlug($pid, $slug);

    /**
     * @param $data
     * @return mixed
     */
    public function saveEditPackageTab($data);

    /**
     * @param $p_id
     * @param $slug
     * @return mixed
     */
    public function deletePackageTab($p_id, $slug);
}
