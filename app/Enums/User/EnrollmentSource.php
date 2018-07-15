<?php

namespace App\Enums\User;

use App\Enums\BaseEnum;

abstract class EnrollmentSource extends BaseEnum
{
    const USER_GROUP = "USER_GROUP";

    const SUBSCRIPTION = "SUBSCRIPTION";

    const DIRECT_ENROLLMENT = "DIRECT_ENROLLMENT";
}
