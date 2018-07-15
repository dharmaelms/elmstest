<?php
namespace App\Traits;

use MongoDB\Operation\FindOneAndUpdate;
/**
 * Class GetNextSequence
 * @package App\Traits
 */
trait GetNextSequence
{

    /**
     * @return int
     */
    public static function getSequence($primary_key)
    {   
        $model = new static;
        $seq = $model->raw()->findOneAndUpdate(
            ['_id' => $primary_key],
            ['$inc' => ['seq' => 1]],
            [
                'new' => true,
                'upsert' => true,
                'returnDocument' => FindOneAndUpdate::RETURN_DOCUMENT_AFTER
            ]
        );
        return $seq->seq;
    }   

}