<?php

namespace App\Enums\RolesAndPermissions;

use App\Enums\BaseEnum;

abstract class Contexts extends BaseEnum
{
    const SYSTEM = "system";

    const PROGRAM = "program";

    const COURSE = "course";

    const BATCH = "batch";
}
