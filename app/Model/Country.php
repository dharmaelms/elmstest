<?php

namespace App\Model;

use Moloquent;

class Country extends Moloquent
{
    protected $collection = 'countries';
    public $timestamps = false;

    public static function getCountries()
    {
        return self::get()->toArray();
    }

    public static function getCountry($country_code)
    {
        return self::where('country_code', '=', $country_code)->value('country_name');
    }
}
