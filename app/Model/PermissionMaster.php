<?php

namespace App\Model;

use Moloquent;

class PermissionMaster extends Moloquent
{
    protected $table = 'permissions_master';
    public $timestamps = false;

    public static function getPermissions()
    {
        $data = self::get()->toArray();

        return $data;
    }
}
