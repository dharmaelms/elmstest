<?php namespace App\Model\Playlyfe\Entity;

use Moloquent;

class Log extends Moloquent
{
    protected $collection = "playlyfe_log";

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $dateFormat = "U";

    protected $guarded = ["_id"];
}
