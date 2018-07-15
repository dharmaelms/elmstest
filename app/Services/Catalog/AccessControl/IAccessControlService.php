<?php

namespace App\Services\Catalog\AccessControl;

/**
 * Interface IAccessControlService
 * @package App\Services\Catalog\AccessControl
 */
interface IAccessControlService
{
    /**
     * @param $e_data
     * @return mixed
     */
    public function enrollUser($e_data);

    public function enrollUserByProduct($u_id, $product_list = null);

    public function unEnrollSubscription($u_id);

    public function updateTransaction($u_id, $p_id, $e_subscription);
}
