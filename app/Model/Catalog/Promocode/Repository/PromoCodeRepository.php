<?php

namespace App\Model\Catalog\Promocode\Repository;

use App\Model\Catalog\Order\Entity\Order;
use App\Model\Program;
use App\Model\PromoCode;
use App\Model\Package\Entity\Package;

/**
 * Class PromoCodeRepository
 * @package App\Model\Catalog\Promocode\Repository
 */
class PromoCodeRepository implements IPromoCodeRepository
{
    /**
     * {@inheritdoc}
     */
    public function getPromocode($p_code)
    {
        $data = PromoCode::where('promocode', '=', $p_code)
            ->where('status', '=', 'ACTIVE')->first();
        if (!empty($data) && $data) {
            return $data->toArray();
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateRedeem($p_code)
    {
        return PromoCode::where('promocode', $p_code)->increment('redeemed_count');
    }

    /**
     * {@inheritdoc}
     */
    public function isUsedPromocode($p_code, $u_id)
    {
        return Order::where('promo_code', '=', $p_code)->where('user_details.uid', '=', (int)$u_id)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function getPromoCodeList($program_slug)
    {
        $promocode_list = Program::where('program_slug', '=', $program_slug)
            ->first(['promocode']);

        $all_type_promocode_list_id = PromoCode::getPromocodeAllType();

        if (!empty($promocode_list)) {
            if (is_array($promocode_list->promocode)) {
                $int_val = function ($val) {
                    return (int)$val;
                };//convert string to int
                $int_promocode_list = array_map($int_val, $promocode_list->promocode);
                $int_promocode_list = array_merge($int_promocode_list, $all_type_promocode_list_id);
                $time = time();
                return PromoCode::whereIn('id', $int_promocode_list)
                    ->where('start_date', '<=', $time)
                    ->where('end_date', '>=', $time)
                    ->where('status', '=', 'ACTIVE')
                    ->get();
            } elseif (!empty($all_type_promocode_list_id)) {
                $time = time();
                return PromoCode::whereIn('id', $all_type_promocode_list_id)
                    ->where('start_date', '<=', $time)
                    ->where('end_date', '>=', $time)
                    ->where('status', '=', 'ACTIVE')
                    ->get();
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function getPackagePromoCodeList($program_slug)
    {
        $promocode_list = Package::where('package_slug', '=', $program_slug)
            ->first(['promocode']);

        $all_type_promocode_list_id = PromoCode::getPromocodeAllType();

        if (!empty($promocode_list)) {
            if (is_array($promocode_list->promocode)) {
                $int_val = function ($val) {
                    return (int)$val;
                };//convert string to int
                $int_promocode_list = array_map($int_val, $promocode_list->promocode);
                $int_promocode_list = array_merge($int_promocode_list, $all_type_promocode_list_id);
                $time = time();
                return PromoCode::whereIn('id', $int_promocode_list)
                    ->where('start_date', '<=', $time)
                    ->where('end_date', '>=', $time)
                    ->where('status', '=', 'ACTIVE')
                    ->get();
            } elseif (!empty($all_type_promocode_list_id)) {
                $time = time();
                return PromoCode::whereIn('id', $all_type_promocode_list_id)
                    ->where('start_date', '<=', $time)
                    ->where('end_date', '>=', $time)
                    ->where('status', '=', 'ACTIVE')
                    ->get();
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}
