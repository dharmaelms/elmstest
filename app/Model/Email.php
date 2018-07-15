<?php

namespace App\Model;

use Moloquent;

class Email extends Moloquent
{
    protected $table = 'emails';
    public $timestamps = false;

    public static function getEmail($slug = null)
    {
        $email = self::where('slug', '=', $slug)->where('status', '=', 'ACTIVE')->get(['subject', 'body']);

        return $email;
    }
}
