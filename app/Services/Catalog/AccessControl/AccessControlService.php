<?php

namespace App\Services\Catalog\AccessControl;

use App\Model\Catalog\AccessControl\Repository\IAccessControlRepository;
use App\Services\Catalog\Order\IOrderService;

/**
 * Class AccessControlService
 * @package App\Services\Catalog\AccessControl
 */
class AccessControlService implements IAccessControlService
{
    /**
     * @var IAccessControlRepository
     */
    private $access_control_repository;

    /**
     * @var IOrderService
     */
    private $order_service;

    /**
     * AccessControlService constructor.
     * @param IAccessControlRepository $access_control_repository
     * @param IOrderService $order_service
     */
    public function __construct(
        IAccessControlRepository $access_control_repository,
        IOrderService $order_service
    )
    {

        $this->access_control_repository = $access_control_repository;
        $this->order_service = $order_service;
    }

    /**
     * {@inheritdoc}
     */
    public function enrollUser($e_data)
    {
        $s_data = null;
        $user = $this->order_service->getUser($e_data['u_id'], $s_data);
        $this->access_control_repository->enroll($e_data, $user);
        $this->access_control_repository->updateRelation($e_data['u_id'], $e_data['p_id'], $e_data['p_type']);
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function enrollUserByProduct($u_id, $product_list = null)
    {
        $s_data = ['subscription', 'relations'];
        $data = $this->order_service->getUser($u_id, $s_data);
        if (isset($data['subscription'])) {
            if (is_array($product_list)) {
                if (count($product_list) === 1) {
                    foreach ($data['subscription'] as $value) {
                        if (in_array((int)$value['program_id'], $product_list)) {
                            return $value;
                        }
                    }
                    if (isset($data['relations']['user_course_rel']) && !empty($data['relations']['user_course_rel'])) {
                        return $data['relations']['user_course_rel'];
                    }
                    return false;
                } else {
                    //Multiple product
                }
            } else {
                return "product list not an array";
            }
        } else {
            if (isset($data['relations']['user_course_rel']) && !empty($data['relations']['user_course_rel'])) {
                return $data['relations']['user_course_rel'];
            }
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function unEnrollSubscription($u_id)
    {
        $s_data = ['relations', 'subscription'];
        $data = $this->order_service->getUser($u_id, $s_data);
        $e_program = [];
        if (isset($data['subscription'])) {
            $e_program = $this->expiredSubscriptions($data['subscription'], $u_id);
        }
        $this->access_control_repository->unEnrollSubscription($e_program, $u_id);
    }

    /**
     * @param null $s_list
     * @param $u_id
     * @return array
     */
    private function expiredSubscriptions($s_list = null, $u_id)
    {
        $time = time();
        $d_sub = [];
        if (!empty($s_list)) {
            foreach ($s_list as $e_subscription) {
                if ($time > $e_subscription['end_time']) {
                    array_push($d_sub, $e_subscription['program_id']);
                    $this->updateTransaction($u_id, $e_subscription['program_id'], $e_subscription);
                }
            }
        }
        return $d_sub;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTransaction($u_id, $p_id, $e_subscription)
    {
        $this->access_control_repository->updateTransaction($p_id, $u_id, $e_subscription);
    }
}
