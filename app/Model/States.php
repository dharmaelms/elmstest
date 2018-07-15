<?php

namespace App\Model;

use Moloquent;

class States extends Moloquent
{
    protected $collection = 'states';
    public $timestamps = false;

    public static function getStates($country_code)
    {
        return self::where('country_code', '=', $country_code)->value('states');
    }
}
