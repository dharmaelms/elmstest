<?php
namespace App\Model;

use Moloquent;

class LiveQuestions extends Moloquent
{
    protected $table = 'livequestions';

    /**
     * function to get questionbank details in live database
     * @param  [type] $id [question id]
     * @return [type]     [description]
     */
    public static function findQuestionByColumn($name, $value)
    {
        $data = LiveQuestions::where($name, '=', $value)->get();
        return $data;
    }
}
