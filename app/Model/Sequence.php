<?php

namespace App\Model;

use Moloquent;

/**
 * Sequence Model
 *
 * @package Event
 */
class Sequence extends Moloquent
{

    /**
     * The table/collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'sequence';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public static function getSequence($primary_key)
    {
        $sequence = Sequence::raw()->findOneAndUpdate(
            ['_id' => $primary_key],
            ['$inc' => ['seq' => 1]],
            [
                'new' => true,
                'upsert' => true,
                'returnDocument' => 2
            ]
        );
        return (int)$sequence->seq;
    }
}
