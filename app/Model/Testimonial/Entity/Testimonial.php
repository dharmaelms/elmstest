<?php namespace App\Model\Testimonial\Entity;

use Moloquent;

class Testimonial extends Moloquent
{
    protected $collection = "testimonials";

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

    protected $guarded = ["_id"];
}
