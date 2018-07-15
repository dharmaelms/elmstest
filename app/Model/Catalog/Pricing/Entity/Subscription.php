<?php namespace App\Model\Catalog\Pricing\Entity;

use Moloquent;

class Subscription extends Moloquent
{
    protected $collection = "subscription";

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $dateFormat = "U";

    protected $guarded = ["_id"];
}
