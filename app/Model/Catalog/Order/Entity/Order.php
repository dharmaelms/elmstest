<?php namespace App\Model\Catalog\Order\Entity;

use Moloquent;
use App\Model\Sequence;

class Order extends Moloquent
{
    protected $collection = "order";

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
        return Sequence::getSequence('order_id');
    }
}
