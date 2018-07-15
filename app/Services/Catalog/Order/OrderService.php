<?php

namespace App\Services\Catalog\Order;

use App\Model\Catalog\Order\Repository\IOrderRepository;
use App\Services\Catalog\Promocode\IPromoCodeService;

/**
 * Class OrderService
 * @package App\Services\Catalog\Order
 */
class OrderService implements IOrderService
{
    /**
     * @var IOrderRepository
     */
    private $order_repository;

    /**
     * @var IPromoCodeService
     */
    private $promo_code_service;

    /**
     * OrderService constructor.
     * @param IOrderRepository $order_repo
     * @param IPromoCodeService $promo_code_service
     */
    public function __construct(
        IOrderRepository $order_repo,
        IPromoCodeService $promo_code_service
    )
    {
        $this->order_repository = $order_repo;
        $this->promo_code_service = $promo_code_service;
    }

    /**
     * {@inheritdoc}
     */
    public function placeOrder($i_data, $u_id, $p_data)
    {
        $user = $this->getUser($u_id, ['email']);
        $o_data = $this->mOrderData($user, $p_data, $i_data);
        $orderID = $this->order_repository->createOrder($o_data);
        return $orderID;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($u_id, $s_data = null)
    {
        $d_data = ['uid', 'firstname', 'lastname', 'email', 'username', 'timezone', 'mobile'];
        if (!empty($s_data)) {
            $d_data = array_merge($s_data, $d_data);
        }
        $user = $this->order_repository->getUser($u_id, $d_data);

        if (!empty($user)) {
            return array_first($user);
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder($orderID)
    {
        $order = $this->order_repository->getOrder($orderID);
        if (!empty($order)) {
            foreach ($order as $key => $value) {
                return $value;
            }
        } else {
            return null;
        }
    }

    /**
     * @param $u_data
     * @param $p_data
     * @param $s_address
     * @return mixed
     */
    private function mOrderData($u_data, $p_data, $s_address)
    {
        $program_slug = $p_data['p_slug'];
        $temp['user'] = $u_data;
        $temp['items_details'] = $p_data;
        $temp['address'] = [
            'fullname' => $s_address['fullname'],
            'address' => $s_address['address'],
            'state' => $s_address['region_state'],
            'city' => $s_address['city'],
            'country' => $s_address['country'],
            'post_code' => $s_address['post_code'],
            'contact_no' => $s_address['telephone']
        ];
        $temp['payment_type'] = $s_address['pay_way'];
        $temp['promo_code'] = '';
        $temp['sub_total'] = (int)$p_data['price'];
        $temp['net_total'] = (int)($p_data['price']);
        $temp['discount'] = (int)0;

        if (isset($s_address['promo_code'])) {
            $s_address = array_merge($s_address, ['price' => (int)$p_data['price']]);
            $p_temp = $this->mPromoCode($s_address, $u_data['uid'], $program_slug);
            $temp = array_merge($temp, $p_temp);
        }

        if ($s_address['pay_way'] === "COD") {
            $temp['status'] = "Pending";
            $temp['payment_status'] = "NOT-PAID";
        } elseif ($s_address['pay_way'] === "FREE") {
            $temp['status'] = "COMPLETED";
            $temp['payment_status'] = "PAID";
        } else {
            $temp['status'] = "Pending";
            $temp['payment_status'] = "NOT-PAID";
        }
        $temp['currency_code'] = config('app.site_currency');
        return $temp;
    }

    /**
     * @param $s_address
     * @param $uid
     * @param $program_slug
     * @return array
     */
    private function mPromoCode($s_address, $uid, $program_slug)
    {
        $temp['promo_code'] = '';
        if (isset($s_address['promo_code']) && !empty($s_address['promo_code'])) {
            $discount = $this->promo_code_service->valPromoCode($s_address['promo_code'], $program_slug, $s_address['price'], $uid, "order");
            if (!empty($discount)) {
                $temp['promo_code'] = $s_address['promo_code'];
            }
            if ($discount === "promocode_used") {
                return [];
            }
            $temp['sub_total'] = (int)$s_address['price'];
            $temp['net_total'] = (int)(($s_address['price'] - $discount) > 0) ? ($s_address['price'] - $discount) : 0;
            $temp['discount'] = (int)$discount;
            return $temp;
        } else {
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderPagination($u_id = null)
    {
        //dd($this->ordRep->getOrderPagination($u_id)->toArray());
        return $this->order_repository->getOrderPagination($u_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderByFilterPagination($type_filter = null, $date_filter = null)
    {
        return $this->order_repository->getOrderByFilterPagination($type_filter, $date_filter);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderByFilter($type_filter = null, $date_filter = null)
    {
        return $this->order_repository->getOrderByFilter($type_filter, $date_filter);
    }

    /**
     * {@inheritdoc}
     */
    public function updateOrder($u_data)
    {
        if (isset($u_data['order_id']) && !empty($u_data['order_id']) &&
            isset($u_data['order_status']) && !empty($u_data['order_status']) &&
            isset($u_data['payment_status']) && !empty($u_data['payment_status']) &&
            isset($u_data['order_comment'])
        ) {
            $data['order_id'] = $u_data['order_id'];
            $data['status'] = $u_data['order_status'];
            $data['payment_status'] = $u_data['payment_status'];
            $data['comment'] = $u_data['order_comment'];
            $this->order_repository->updateOrder($data);
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderInPendingLastMinute($product_slug, $batch_slug)
    {
        return $this->order_repository->getOrderInPandingLastMinute($product_slug, $batch_slug);
    }

    /**
     * {@inheritdoc}
     */
    public function uoStatus($o_data, $o_status, $p_status)
    {
        if (preg_match("/^ORD[0-9]+/", $o_data['txnid'])) {
            $split = explode("ORD", $o_data['txnid']);
            $o_id = (int)$split[1];
            $data = [
                'order_id' => $o_id,
                'status' => $o_status,
                'payment_status' => $p_status
            ];
            $this->order_repository->updateOrder($data);
            return $this->getOrder($o_id);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultAddress($u_id)
    {
        $user = $this->getUser($u_id, ['email', 'myaddress', 'default_address_id']);
        if (!empty($user['myaddress'])) {
            foreach ($user['myaddress'] as $value) {
                if ($user['default_address_id'] === $value['address_id']) {
                    return $value;
                }
            }
        } else {
            return null;
        }
    }

    /**
     *
     */
    public function migrateOrder()
    {
        $this->order_repository->migrateOrder();
    }

    /**
     * {@inheritdoc}
     */
    public function updateSlug($slug, $new_slug)
    {
        $this->order_repository->updateSlug($slug, $new_slug);
    }
}
