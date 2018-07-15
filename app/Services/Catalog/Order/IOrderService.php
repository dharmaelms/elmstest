<?php

namespace App\Services\Catalog\Order;

/**
 * Interface IOrderService
 * @package App\Services\Catalog\Order
 */
interface IOrderService
{
    /**
     * @param $o_data
     * @param $u_id
     * @param $p_data
     * @return mixed
     */
    public function placeOrder($o_data, $u_id, $p_data);

    /**
     * @param $u_id
     * @param $s_data
     * @return mixed
     */
    public function getUser($u_id, $s_data);

    /**
     * @param $orderID
     * @return mixed
     */
    public function getOrder($orderID);

    /**
     * @param $u_id
     * @return mixed
     */
    public function getOrderPagination($u_id = null);

    /**
     * @param $type_filter
     * @param $date_filter
     * @return mixed
     */
    public function getOrderByFilterPagination($type_filter, $date_filter);

    /**
     * @param $type_filter
     * @param $date_filter
     * @return mixed
     */
    public function getOrderByFilter($type_filter, $date_filter);

    /**
     * @param $u_data
     * @return mixed
     */
    public function updateOrder($u_data);

    /**
     * @param $product_slug
     * @param $batch_slug
     * @return mixed
     */
    public function getOrderInPendingLastMinute($product_slug, $batch_slug);

    /**
     * @param $o_data
     * @param $o_status
     * @param $p_status
     * @return mixed
     */
    public function uoStatus($o_data, $o_status, $p_status);

    /**
     * @param $u_id
     * @return mixed
     */
    public function getDefaultAddress($u_id);

    /**
     * [migrateOrder -order migration]
     * @method migrateOrder
     * @return [type]       [null]
     * @author Rudragoud Patil
     */
    public function migrateOrder();

    /**
     * Method is used to update slug in Order collection
     * @param string $slug
     * @param string $new_slug
     * @return \App\Model\Catalog\Order\Entity\Order
     */
    public function updateSlug($slug, $new_slug);
}
