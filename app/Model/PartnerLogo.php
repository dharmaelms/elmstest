<?php
namespace App\Model;

use Moloquent;

class PartnerLogo extends Moloquent
{

    protected $table = 'partner';

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
    protected $dates = ['created_at', 'updated_at', 'modified_at'];

    public static function getUniqueId()
    {
        return Sequence::getSequence('partner_logo_id');
    }

    public static function getPartnerDetails($id)
    {
        return self::where('partner_id', '=', (int)$id)->get()->toArray();
    }

    public static function addPartner($id, $input, $file_location, $file_name, $logo_dimension)
    {
        $array = [
            'partner_id' => (int)$id,
            'partner_name' => trim($input['partner_name']),
            'partner_logopath' => $file_location,
            'partner_logoname' => $file_name,
            'partner_diamension' => $logo_dimension,
            'partner_description' => $input['partner_description'],
            'status' => $input['status'],
            'created_at' => time(),
            'modified_at' => time(),
        ];
        return self::insert($array);
    }

    public static function upadatePartner($id, $input, $file_location, $file_name, $logo_dimension)
    {
        $array = [
            'partner_id' => (int)$id,
            'partner_name' => trim($input['partner_name']),
            'partner_logopath' => $file_location,
            'partner_logoname' => $file_name,
            'partner_diamension' => $logo_dimension,
            'partner_description' => $input['partner_description'],
            'status' => $input['status'],
            'created_at' => time(),
            'modified_at' => time(),
        ];
        return self::where('partner_id', '=', (int)$id)
            ->update($array);
    }

    public static function sortlogos($id, $curval, $nextval)
    {
        $curval = (int)$curval;
        $nextval = (int)$nextval;

        if ($curval < $nextval) {
            $curval = $curval + 1;
            $nxtorders = self::where('status', '!=', 'DELETED')->whereBetween('sort_order', [$curval, $nextval])->orderBy('sort_order', 'asc')->get(['sort_order', 'partner_id'])->toArray();
            foreach ($nxtorders as $nxtorder) {
                self::where('partner_id', '=', $nxtorder['partner_id'])->decrement('sort_order');
            }
            return self::where('partner_id', '=', (int)$id)->update(['sort_order' => $nextval]);
        }

        if ($curval > $nextval) {
            $curval = $curval - 1;
            $nxtorders = self::where('status', '!=', 'DELETED')->whereBetween('sort_order', [$nextval, $curval])->orderBy('sort_order', 'asc')->get(['sort_order', 'partner_id'])->toArray();
            foreach ($nxtorders as $nxtorder) {
                self::where('partner_id', '=', $nxtorder['partner_id'])->increment('sort_order');
            }
            return self::where('partner_id', '=', (int)$id)->update(['sort_order' => $nextval]);
        }
    }

    public static function deletelogo($id, $sort_order, $max_order)
    {
        if ($sort_order != $max_order) {
            $sort_order = $sort_order + 1;
            $nxtorders = self::where('status', '!=', 'DELETED')->whereBetween('sort_order', [$sort_order, $max_order])->orderBy('sort_order', 'asc')->get(['sort_order', 'partner_id'])->toArray();
            foreach ($nxtorders as $nxtorder) {
                self::where('partner_id', '=', $nxtorder['partner_id'])->decrement('sort_order');
            }
        }
        return self::where('partner_id', '=', (int)$id)->delete();
    }

    public static function getFilteredRecords($filter)
    {
        if ($filter == 'ALL') {
            $partners = PartnerLogo::where('status', '!=', 'DELETED')
                        ->where('_id', '!=', 'partner_id')
                        ->orderBy('sort_order', 'asc')
                        ->paginate(10);
        } else {
            $partners = PartnerLogo::where('status', '=', $filter)
                        ->where('_id', '!=', 'partner_id')
                        ->orderBy('sort_order', 'asc')
                        ->paginate(10);
        }
        return $partners;
    }

    public static function getAllPartners($filter)
    {

        if ($filter == 'ALL') {
            $partners = PartnerLogo::where('status', '!=', 'DELETED')->orderBy('sort_order', 'asc')->get()->toArray();
        } else {
            $partners = PartnerLogo::where('status', '=', $filter)->orderBy('sort_order', 'asc')->get()->toArray();
        }

        return $partners;
    }
}
