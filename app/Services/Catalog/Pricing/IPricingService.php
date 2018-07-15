<?php
namespace App\Services\Catalog\Pricing;

/**
 * Interface IPricingService
 * @package App\Services\Catalog\Pricing
 */
interface IPricingService
{
    /**
     * @param $data
     * @return mixed
     */
    public function addPrice($data);

    /**
     * @param $data
     * @return mixed
     */
    public function listVertical($data);

    /**
     * @param $data
     * @param $slug
     * @return mixed
     */
    public function getVerticalBySlug($data, $slug);

    /**
     * @param $data
     * @return mixed
     */
    public function priceFirst($data);

    /**
     * @param $p_data
     * @param $i_data
     * @param null $more_data
     * @return mixed
     */
    public function addVertical($p_data, $i_data, $more_data = null);

    /**
     * @param $p_data
     * @param $i_data
     * @param null $more_data
     * @return mixed
     */
    public function updateVertical($p_data, $i_data, $more_data = null);

    /**
     * @param $p_data
     * @param $i_data
     * @return mixed
     */
    public function deleteVertical($p_data, $i_data);

    /**
     * @param $p_data
     * @param $i_data
     * @return mixed
     */
    public function addSubscriptions($p_data, $i_data);

    /**
     * @param $p_data
     * @param $i_data
     * @return mixed
     */
    public function updateSubscription($p_data, $i_data);

    /**
     * @param $p_data
     * @param $i_data
     * @return mixed
     */
    public function deleteSubscriptions($p_data, $i_data);

    /**
     * @param $sal_id
     * @param $sal_type
     * @return mixed
     */
    public function getPriceList($sal_id, $sal_type);

    /**
     * @param $sal_id
     * @param $sal_type
     * @param $title
     * @param null $c_slug
     * @return mixed
     */
    public function checkDubSubscription($sal_id, $sal_type, $title, $c_slug = null);

    /**
     * @param $data
     * @return mixed
     */
    public function getPricing($data);

    /**
     * @param $sellable_id
     * @param $sellable_type
     * @param $slug
     * @return mixed
     */
    public function getSubscriptionDetails($sellable_id, $sellable_type, $slug);

    /**
     * @param $s_data
     * @return mixed
     */
    public function subscribeUser($s_data, $p_type = null);

    /**
     * @param $program_id
     * @return mixed
     */
    public function getSubscriptionArray($program_id);

    /**
     * @param array $filter_params
     * @return mixed
     */
    public function getPricingDetails($filter_params = []);
}
