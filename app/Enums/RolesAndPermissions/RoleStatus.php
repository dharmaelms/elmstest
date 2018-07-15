<?php

namespace App\Enums\RolesAndPermissions;

use App\Enums\BaseEnum;

abstract class RoleStatus extends BaseEnum
{
    const ACTIVE = "ACTIVE";

    const INACTIVE = "IN-ACTIVE";

    const DELETED = "DELETED";
}