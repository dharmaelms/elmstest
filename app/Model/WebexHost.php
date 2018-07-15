<?php

namespace App\Model;

use Moloquent;

/**
 * WebexHost Model
 *
 * @package Event
 */
class WebexHost extends Moloquent
{
    /**
     * The table/collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'webex_hosts';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'webex_host_id' => 'integer',
    ];

    /**
     * Function generate unique auto incremented id for this collection
     *
     * @param boolean $unique force to set unique index (Default: true)
     * @return integer
     */
    public static function getNextSequence()
    {
        return Sequence::getSequence('webex_host_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }
}
