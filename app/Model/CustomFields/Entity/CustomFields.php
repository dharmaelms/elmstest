<?php namespace App\Model\CustomFields\Entity;
use App\Model\Sequence;

use Moloquent;

class CustomFields extends Moloquent
{

    protected $collection = "customfields";

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
    protected $dates = ['created_at', 'updated_at'];

    protected $guarded = ["_id"];

    public static function uniqueId()
    {
        return Sequence::getSequence('custom_fields_id');
    }

    public static function scopeFieldSearch($query, $search = null)
    {
        if ($search != null) {
            $query->where(function ($q) use ($search) {
                $q->where('fieldname', 'like', '%' . $search . '%')
                    ->orWhere('program_type', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }

    public static function scopeGetType($query, $type = 'userfields')
    {
        if ($type == 'userfields') {
            return $query->where('program_type', '=', 'user');
        } elseif ($type == 'channelfields') {
            return $query->where('program_type', '=', 'content_feed')->where('program_sub_type', '=', 'single');
        } elseif ($type == 'packagefields') {
            return $query->where('program_type', '=', 'content_feed')->where('program_sub_type', '=', 'collection');
        } elseif ($type == 'productfields') {
            return $query->where('program_type', '=', 'product');
        } elseif ($type == 'coursefields') {
            return $query->where('program_type', '=', 'course')->where('program_sub_type', '=', 'single');
        } else {
            return $query;
        }
    }

    public static function getUserCustomField($program_type, $program_sub_type)
    {
        return self::where('program_type', '=', $program_type)->where('program_sub_type', '=', $program_sub_type)->get()->toArray();
    }

    public static function getUserCustomFieldArr($program_type, $program_sub_type, $fieldsArr)
    {
        return self::where('program_type', '=', $program_type)->where('program_sub_type', '=', $program_sub_type)->where('status', '=', 'ACTIVE')->get($fieldsArr)->toArray();
    }

    public static function getUserActiveCustomField($program_type, $program_sub_type, $status = 'ACTIVE')
    {
        return self::where('program_type', '=', $program_type)->where('program_sub_type', '=', $program_sub_type)->where('status', '=', $status)->get()->toArray();
    }

    public static function getCustomField($field, $type)
    {
        if ($type == 'userfields') {
            return self::where('program_type', '=', 'user')->where('fieldname', '=', $field)->get()->toArray();
        } elseif ($type == 'channelfields') {
            return self::where('program_type', '=', 'content_feed')->where('program_sub_type', '=', 'single')->where('fieldname', '=', $field)->get()->toArray();
        } elseif ($type == 'packagefields') {
            return self::where('program_type', '=', 'content_feed')->where('program_sub_type', '=', 'collection')->where('fieldname', '=', $field)->get()->toArray();
        } elseif ($type == 'productfields') {
            return self::where('program_type', '=', 'product')->where('fieldname', '=', $field)->get()->toArray();
        } else {
            return self::where('program_type', '=', 'course')->where('program_sub_type', '=', 'single')->where('fieldname', '=', $field)->get()->toArray();
        }
    }
}
