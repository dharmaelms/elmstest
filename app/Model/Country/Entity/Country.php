<?php namespace App\Model\Country\Entity;

use Moloquent;

class Country extends Moloquent
{
    protected $collection = "countries";

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
}
