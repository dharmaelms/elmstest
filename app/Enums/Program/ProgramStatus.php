<?php

namespace App\Enums\Program;

use App\Enums\BaseEnum;

abstract class ProgramStatus extends BaseEnum
{
    const ACTIVE = "ACTIVE";

    const IN_ACTIVE = "IN-ACTIVE";

    const DELETED = "DELETED";
}
