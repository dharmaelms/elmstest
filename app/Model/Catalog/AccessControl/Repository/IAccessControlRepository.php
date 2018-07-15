<?php

namespace App\Model\Catalog\AccessControl\Repository;

/**
 * Interface IAccessControlRepository
 * @package App\Model\Catalog\AccessControl\Repository
 */
interface IAccessControlRepository
{

    /**
     * @param $p_data
     * @param $u_data
     * @return mixed
     */
    public function enroll($p_data, $u_data);

    /**
     * @param $u_id
     * @param $product_id
     * @return mixed
     */
    public function updateRelation($u_id, $product_id, $p_type = null);

    /**
     * @param $l_pgm_id
     * @param $u_id
     * @return mixed
     */
    public function unEnrollSubscription($l_pgm_id, $u_id);

    /**
     * @param $p_id
     * @param $u_id
     * @param $subscription
     * @return mixed
     */
    public function updateTransaction($p_id, $u_id, $subscription);
}
