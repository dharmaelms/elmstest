<?php
namespace App\Model\Catalog\Pricing\Repository;

/**
 * Interface IPricingRepository
 * @package App\Model\Catalog\Pricing\Repository
 */
interface IPricingRepository
{
    /**
     * @param $data
     * @return mixed
     */
    public function store($data);

    /**
     * @param $sel_id
     * @param $sel_type
     * @return mixed
     */
    public function getPrice($sel_id, $sel_type);

    /**
     * @param $sal_id
     * @param $sal_type
     * @param string $repo
     * @return mixed
     */
    public function getSubscription($sal_id, $sal_type, $repo = '');

    /**
     * @param $data
     * @return mixed
     */
    public function getPricing($data);


    /**
     * @param $s_data
     * @param $uid
     * @return mixed
     */
    public function addSubscriptionUser($s_data, $uid);

    /**
     * @param $uid
     * @param $s_list
     * @return mixed
     */
    public function getUserSubscription($uid, $s_list);

    /**
     * @param $data
     * @return mixed
     */
    public function addPrice($data);

    /**
     * @param $id
     * @param $type
     * @param $s_attr
     * @return mixed
     */
    public function getPriceFirst($id, $type, $s_attr);

    /**
     * @return mixed
     */
    public function getPriceList();

    /**
     * @param $price_id
     * @param $vertical
     * @return mixed
     */
    public function addVertical($price_id, $vertical);

    /**
     * @param $price_id
     * @param $vertical
     * @return mixed
     */
    public function updateVertical($price_id, $vertical);

    /**
     * @param $price_id
     * @param $vertical
     * @return mixed
     */
    public function deleteVertical($price_id, $vertical);

    /**
     * @param $price_id
     * @param $vertical
     * @return mixed
     */
    public function addSubscription($price_id, $vertical);

    /**
     * @param $price_id
     * @param $vertical
     * @return mixed
     */
    public function updateSubscription($price_id, $vertical);

    /**
     * @param $price_id
     * @param $vertical
     * @return mixed
     */
    public function deleteSubscription($price_id, $vertical);

    /**
     * @param $program_id
     * @return mixed
     */
    public function getSubscriptionArray($program_id);

    /**
     * @param $data
     * @return mixed
     */
    public function updatePriceID($data);

    /**
     * @param array $filter_params
     * @return mixed
     */
    public function get($filter_params = []);
}
