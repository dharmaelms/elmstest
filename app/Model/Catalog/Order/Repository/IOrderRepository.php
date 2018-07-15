<?php
namespace App\Model\Catalog\Order\Repository;

/**
 * Interface IOrderRepository
 * @package App\Model\Catalog\Order\Repository
 */
interface IOrderRepository
{
    /**
     * @param $data
     * @return mixed
     */
    public function createOrder($data);

    /**
     * @param $u_id
     * @param $s_list
     * @return mixed
     */
    public function getUser($u_id, $s_list);

    /**
     * @param $order_id
     * @return mixed
     */
    public function getOrder($order_id);

    /**
     * @param null $u_id
     * @return mixed
     */
    public function getOrderPagination($u_id = null);

    /**
     * @param null $type_filter
     * @param null $date_filter
     * @return mixed
     */
    public function getOrderByFilterPagination($type_filter = null, $date_filter = null);

    /**
     * @param null $type_filter
     * @param null $date_filter
     * @return mixed
     */
    public function getOrderByFilter($type_filter = null, $date_filter = null);

    /**
     * @param $u_data
     * @return mixed
     */
    public function updateOrder($u_data);

    /**
     * @return mixed
     */
    public function migrateOrder();

    /**
     * @param $program_id
     * @param $batch_slug
     * @return mixed
     */
    public function getOrderInPandingLastMinute($program_id, $batch_slug);

    /**
     * Method is used to update slug in Order collection
     *
     * @param string $slug
     * @param string $new_slug
     * @return \App\Model\Catalog\Order\Entity\Order
     */
    public function updateSlug($slug, $new_slug);
}
