<?php namespace App\Model\Catalog\Pricing\Entity;

use Moloquent;
use App\Model\Sequence;

class Price extends Moloquent
{

    protected $collection = "price";

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
        return Sequence::getSequence('price_id');
    }

    /**
     * @param $query
     * @param array $filter_params
     */
    public function scopeFilter($query, $filter_params)
    {
        return $query->when(
            !empty($filter_params["sellable_id"]),
            function ($query) use ($filter_params) {
                return $query->whereIn("sellable_id", $filter_params["sellable_id"]);
            }
        )->when(
            !empty($filter_params["sellable_type"]),
            function ($query) use ($filter_params) {
                return $query->whereIn("sellable_type", $filter_params["sellable_type"]);
            }
        );
    }
}
