<?php

namespace App\Services\Catalog\Promocode;

use App\Model\Catalog\Promocode\Repository\IPromoCodeRepository;
use App\Model\Program;
use App\Model\Package\Entity\Package;
use Timezone;

/**
 * Class PromoCodeService
 * @package App\Services\Catalog\Promocode
 */
class PromoCodeService implements IPromoCodeService
{

    /**
     * @var IPromoCodeRepository
     */
    private $promo_code_repository;

    /**
     * PromoCodeService constructor.
     * @param IPromoCodeRepository $promo_code_repository
     */
    public function __construct(IPromoCodeRepository $promo_code_repository)
    {
        $this->promo_code_repository = $promo_code_repository;
    }

    /**
     * @param $cCode
     * @param $program_slug
     * @param $price
     * @param $uid
     * @param null $order
     * @param null|string $p_type
     * @return bool|float|null|string
     */
    public function valPromoCode($cCode, $program_slug, $price, $uid, $order = null, $p_type = null)
    {
        $data = $this->promo_code_repository->getPromocode($cCode);
        if ($data) {
            if ($this->valPromoCodeDate($data['start_date'], $data['end_date'])) {
                if ($data['max_redeem_count'] > $data['redeemed_count']) {
                    if ($this->isUsedPromoCode($cCode, $uid)) {
                        return "promocode_used";
                    } else {

                        if (!$this->validatePromoCodeType($program_slug, $data, $price, $p_type)) {
                            return false;
                        }
                        $retData = $this->getNetDiscount($data['discount_type'], $data, $price);
                        if (!empty($order) && $order === "order") {
                            $this->uRedeemCount($cCode);
                        }
                        return $retData;
                    }

                } elseif ($data['max_redeem_count'] == 0) {
                    if (!$this->validatePromoCodeType($program_slug, $data, $price, $p_type)) {
                        return false;
                    }
                    $retData = $this->getNetDiscount($data['discount_type'], $data, $price);
                    if (!empty($order) && $order === "order") {
                        $this->uRedeemCount($cCode);
                    }
                    return $retData;
                } else {
                    return false;
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * @param $p_code
     */
    private function uRedeemCount($p_code)
    {
        $this->promo_code_repository->updateRedeem($p_code);
    }

    /**
     * @param $d_type
     * @param $data
     * @param string $price
     * @return float
     */
    private function getNetDiscount($d_type, $data, $price = '100')
    {
        switch ($d_type) {
            case 'percentage':
                $discount_price = round(($price * $data['discount_value']) / 100);
                break;

            default:
                $discount_price = $data['discount_value'];
                break;
        }

        if (isset($data['maximum_discount_amount'])) {
            if ($data['maximum_discount_amount'] == 0) {
                return $discount_price;
            }
            if ($discount_price > $data['maximum_discount_amount']) {
                return $data['maximum_discount_amount'];
            }
            return $discount_price;
        }
        return $discount_price;
    }

    /**
     * @param $s_time
     * @param $e_time
     * @return bool
     */
    private function valPromoCodeDate($s_time, $e_time)
    {
        $time = time();
        if ($time > Timezone::getTimeStamp($s_time) &&
             $time < Timezone::getTimeStamp($e_time)) {
            return true;
        }
        return false;
    }

    /**
     * @param $promo_code
     * @param $userID
     * @return bool
     */
    private function isUsedPromoCode($promo_code, $userID)
    {
        $data = $this->promo_code_repository->isUsedPromocode($promo_code, $userID);
        if (!empty($data)) {
            return true;
        }
        return false;
    }

    /**
     * @param $program_slug
     * @param $program_data
     * @param $u_id
     * @return array|null
     */
    public function getPromoCodeList($program_slug, $program_data, $u_id)
    {
        $data = $this->promo_code_repository->getPromoCodeList($program_slug);
        if (!empty($data)) {
            $val_promo = function ($value) use ($program_data, $u_id, &$data, $program_slug) {
                $value['offer_price'] = $this->valPromoCode($value['promocode'], $program_slug, $program_data['price'], $u_id);
                if ($value['offer_price'] != 'promocode_used') {
                    return $value;
                }
            };

            $promo_code_list = $data->toArray();
            return array_filter(array_map($val_promo, $promo_code_list));
        } else {
            return null;
        }
    }

    public function getPackagePromoCodeList($program_slug, $program_data, $u_id, $p_type = null)
    {
        $data = $this->promo_code_repository->getPackagePromoCodeList($program_slug);
        if (!empty($data)) {
            $val_promo = function ($value) use ($program_data, $u_id, &$data, $program_slug, $p_type) {
                $value['offer_price'] = $this->valPromoCode($value['promocode'], $program_slug, $program_data['price'], $u_id, null, $p_type);
                if ($value['offer_price'] != 'promocode_used') {
                    return $value;
                }
            };

            $promo_code_list = $data->toArray();
            return array_filter(array_map($val_promo, $promo_code_list));
        } else {
            return null;
        }
    }

    /**
     * @param $program_slug
     * @param $data
     * @param $price
     * @return bool
     */
    private function validatePromoCodeType($program_slug, $data, $price, $p_type)
    {

        if (isset($p_type) && $p_type == "package") {
            $program = Package::where('package_slug', '=', $program_slug)
                ->first();
            if (isset($data['program_type']) && $data['program_type'] == 'all' || (isset($data['program_type']) && $program->program_type == $data['program_type'])) {
                if (isset($data['minimum_order_amount'])) {
                    $minimum_order_amount_flag = ($data['minimum_order_amount'] == 0) ? true : false;
                    if ($minimum_order_amount_flag) {
                        return true;
                    } else {
                        return ($price > $data['minimum_order_amount']) ? true : false;
                    }
                }
                return true;
            } else {
                return false;
            }

        } else {

            $program = Program::where('program_slug', '=', $program_slug)
                ->first();
            if (isset($data['program_type']) && $data['program_type'] == 'all' || (isset($data['program_type']) && $program->program_type == $data['program_type'])) {
                if (isset($data['minimum_order_amount'])) {
                    $minimum_order_amount_flag = ($data['minimum_order_amount'] == 0) ? true : false;
                    if ($minimum_order_amount_flag) {
                        return true;
                    } else {
                        return ($price > $data['minimum_order_amount']) ? true : false;
                    }
                }
                return true;
            } elseif (isset($data['program_type']) && $data['program_type'] == 'package') {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * @param $program_slug
     * @return null
     */
    public function applicablePromoCodeList($program_slug)
    {
        $data = $this->promo_code_repository->getPromoCodeList($program_slug);
        if (!empty($data)) {
            return $data->toArray();
        } else {
            return null;
        }
    }

    public function applicablePackagePromoCodeList($program_slug)
    {
       $data = $this->promo_code_repository->getPackagePromoCodeList($program_slug);
        if (!empty($data)) {
            return $data->toArray();
        } else {
            return null;
        }
    }
}
