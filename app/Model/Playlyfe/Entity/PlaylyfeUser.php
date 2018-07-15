<?php namespace App\Model\Playlyfe\Entity;

use Moloquent;

class PlaylyfeUser extends Moloquent
{
    protected $collection = "playlyfe_users";

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $guarded = ["_id"];

    protected $dateFormat = "U";
}
