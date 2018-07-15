<?php
namespace App\Services\Catalog\Promocode;

/**
 * Interface IPromoCodeService
 * @package App\Services\Catalog\Promocode
 */
interface IPromoCodeService
{
    /**
     * @param $cCode
     * @param $program_slug
     * @param $price
     * @param $uid
     * @param null $order
     * @param null | string $p_type
     * @return mixed
     */
    public function valPromoCode($cCode, $program_slug, $price, $uid, $order = null, $p_type = null);

    /**
     * @param $program_slug
     * @param $program_data
     * @param $u_id
     * @return mixed
     */
    public function getPromoCodeList($program_slug, $program_data, $u_id);

    /**
     * @param $program_slug
     * @param $program_data
     * @param $u_id
     * @return mixed
     */
    public function getPackagePromoCodeList($program_slug, $program_data, $u_id, $p_type = null);

    /**
     * @param $program_slug
     * @return mixed
     */
    public function applicablePromoCodeList($program_slug);

    /**
     * @param $program_slug
     * @return mixed
     */
    public function applicablePackagePromoCodeList($program_slug);

}
