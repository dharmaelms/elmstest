<?php

namespace App\Enums\User;

use App\Enums\BaseEnum;

abstract class EnrollmentStatus extends BaseEnum
{
    const ENROLLED = "ENROLLED";

    const UNENROLLED = "UNENROLLED";

    const DELETED = "DELETED";
}
