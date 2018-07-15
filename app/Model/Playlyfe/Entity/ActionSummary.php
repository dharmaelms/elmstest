<?php namespace App\Model\Playlyfe\Entity;

use Moloquent;

class ActionSummary extends Moloquent
{
    protected $collection = "playlyfe_action_summary";

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $dateFormat = "U";

    protected $guarded = ["_id"];
}
