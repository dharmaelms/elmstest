<?php

namespace App\Enums\User;

use App\Enums\BaseEnum;

abstract class UserStatus extends BaseEnum
{
    const ACTIVE = "ACTIVE";

    const INACTIVE = "IN-ACTIVE";

    const DELETED = "DELETED";
}