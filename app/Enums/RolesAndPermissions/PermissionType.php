<?php

namespace App\Enums\RolesAndPermissions;

use App\Enums\BaseEnum;

abstract class PermissionType extends BaseEnum
{
    const ADMIN = "admin";

    const PORTAL = "portal";
}