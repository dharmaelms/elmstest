<?php

namespace App\Enums\UserGroup;

use App\Enums\BaseEnum;

abstract class UserGroupStatus extends BaseEnum
{
    const ACTIVE = "ACTIVE";

    const INACTIVE = "IN-ACTIVE";

    const DELETED = "DELETED";
}
