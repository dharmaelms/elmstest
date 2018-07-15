<?php

namespace App\Model;

use Moloquent;

class Transaction extends Moloquent
{
    protected $collection = 'transactions';

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
    protected $dates = ['created_at', 'updated_at', 'created_date'];

    public static function uniqueTransactionId()
    {
        return Sequence::getSequence('trans_id');
    }
}
