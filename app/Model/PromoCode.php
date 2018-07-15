<?php

namespace App\Model;

use Auth;
use Moloquent;
use Timezone;

class PromoCode extends Moloquent
{
    protected $table = 'promocodes';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'start_date', 'end_date'];

    public static function getUniqueId()
    {
        return Sequence::getSequence('promo_code_id');
    }

    public static function insertPromocode($input)
    {
        $id = self::getUniqueId();
        self::insert([
            'id' => (int)$id,
            'promotype' => $input['promotype'],
            'promocode' => $input['promocode'],
            'start_date' => (int)Timezone::convertToUTC($input['start_date'], Auth::user()->timezone, 'U'),
            'end_date' => ((int)Timezone::convertToUTC($input['end_date'], Auth::user()->timezone, 'U') + 24 * 60 * 60) - 1,
            'max_redeem_count' => (int)$input['max_redeem_count'],
            'redeemed_count' => 0,
            'discount_type' => $input['discount_type'],
            'discount_value' => $input['discount_value'],
            'program_type' => $input['product_type'],
            'minimum_order_amount' => (int)$input['minimum_order_amount'],
            'maximum_discount_amount' => (int)$input['maximum_discount_amount'],
            'terms_and_conditions' => $input['terms_and_conditions'],
            'status' => $input['status'],
            'created_at' => time(),
            'created_by' => Auth::user()->username,
            'updated_at' => time(),
            'updated_by' => Auth::user()->username
        ]);

        return $id;
    }

    public static function getPromoCodes($status)
    {
        if ($status == 'ALL') {
            $promocodes = self::where('status', '!=', 'DELETED')->orderBy('created_at', 'desc')->paginate(10);
        } else {
            $promocodes = self::where('status', '=', $status)->orderBy('created_at', 'desc')->paginate(10);
        }

        return $promocodes;
    }

    public static function getAllPromoCode()
    {
        return self::where('status', '=', 'ACTIVE')->get()->toArray();
    }

    public static function deletePromoCode($id)
    {
        return self::where('id', '=', (int)$id)->update(['status' => 'DELETED']);
    }

    public static function generatePromocode()
    {
        $exist = 1;
        while ($exist > 0) {
            $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $res = "";
            for ($i = 0; $i < 10; $i++) {
                $res .= $chars[mt_rand(0, strlen($chars) - 1)];
            }
            $exist = self::where('promocode', '=', $res)->count();
        }
        return $res;
    }

    public static function getPromoCodeUsingId($id)
    {
        return self::where('id', '=', (int)$id)->get()->toArray();
    }

    public static function updatePromocode($id, $input)
    {
        return self::where('id', '=', (int)$id)
            ->update([
                'end_date' => ((int)Timezone::convertToUTC($input['end_date'], Auth::user()->timezone, 'U') + 24 * 60 * 60) - 1,
                'max_redeem_count' => (int)$input['max_redeem_count'],
                'minimum_order_amount' => (int)$input['minimum_order_amount'],
                'maximum_discount_amount' => (int)$input['maximum_discount_amount'],
                'terms_and_conditions' => $input['terms_and_conditions'],
                'status' => $input['status'],
                'updated_at' => time(),
                'updated_by' => Auth::user()->username
            ]);
    }

    public static function decrementPromocodeByOne($promocode)
    {
        return self::where('promocode', $promocode)->decrement('redeemed_count', 1);
    }

    public static function incrementPromocodeByOne($promocode)
    {
        return self::where('promocode', $promocode)->increment('redeemed_count', 1);
    }

    public static function getPromocodeCount($relfilter, $promocode_id, $program_type, $search, $orderby)
    {
        if ($relfilter == 'ALL') {
            return self::where('status', '!=', 'DELETED')
                ->PromoSearch($search)
                ->ProgramTypePromocode($program_type)
                ->GetOrderBy($orderby)
                ->count();
        } else {
            return self::where('status', '=', $relfilter)
                ->PromoSearch($search)
                ->ProgramTypePromocode($program_type)
                ->GetOrderBy($orderby)
                ->count();
        }
    }

    public static function getPromocode($start = 0, $limit = 10, $relfilter, $promocode_id, $program_type, $search, $orderby)
    {

        if ($relfilter === 'ALL') {
            return self::where('status', '!=', 'DELETED')
                ->PromoSearch($search)
                ->ProgramTypePromocode($program_type)
                ->GetOrderBy($orderby)
                ->GetByPagination($start, $limit)
                ->GetAsArray();
        } else {
            return self::where('status', '=', $relfilter)
                ->PromoSearch($search)
                ->ProgramTypePromocode($program_type)
                ->GetOrderBy($orderby)
                ->GetByPagination($start, $limit)
                ->GetAsArray();
        }
    }

    public static function scopeProgramTypePromocode($query, $program_type)
    {
        switch ($program_type) {
            case 'content_feed':
                return $query->where('program_type', 'content_feed');
                break;

            case 'product':
                return $query->where('program_type', 'product');
                break;

            case 'course':
                return $query->where('program_type', 'course');
                break;

            case 'package':
                return $query->where('program_type', 'package');
                break;

            default:
                return $query;
                break;
        }
    }

    public static function scopePromoSearch($query, $search = null)
    {
        if ($search != null) {
            $query->where(function ($q) use ($search) {
                $q->where('promocode', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }

    public static function scopeGetOrderBy($query, $orderby = ['created_at' => 'desc'])
    {
        $key = key($orderby);
        $value = $orderby[$key];

        return $query->orderBy($key, $value);
    }

    public static function scopeGetByPagination($query, $start = 0, $limit = 10)
    {
        return $query->skip((int)$start)->take((int)$limit);
    }

    public static function scopeGetAsArray($query)
    {
        return $query->get()->toArray();
    }

    public static function getPromocodeAllType()
    {
        $all_type_promocode_list = self::where('program_type', '=', 'all')->get(['id']);
        $all_type_promocode_list_id = [];

        if (!empty($all_type_promocode_list)) {
            foreach ($all_type_promocode_list as $key => $value) {
                $all_type_promocode_list_id[] = $value->id;
            }
        }

        return $all_type_promocode_list_id;
    }

    public static function getAvialablePromocode($status, $program_type)
    {
        return self::PromocodeStatus($status)
            ->ProgramTypePromocode($program_type)
            ->get();
    }

    public static function scopePromocodeStatus($query, $status)
    {
        if ($status == 'ALL') {
            $query->where('status', '!=', 'DELETED');
        } else {
            $query->where('status', $status);
        }
        return $query;
    }
}
