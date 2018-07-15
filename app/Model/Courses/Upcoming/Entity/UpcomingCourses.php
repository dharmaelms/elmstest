<?php namespace App\Model\Courses\Upcoming\Entity;

use Moloquent;

class UpcomingCourses extends Moloquent
{
    protected $collection = "upcoming_courses";

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
