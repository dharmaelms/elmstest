<?php

namespace App\Enums\Package;

use App\Enums\BaseEnum;

abstract class PackageStatus extends BaseEnum
{
    const ACTIVE = "ACTIVE";

    const IN_ACTIVE = "IN-ACTIVE";

    const DELETED = "DELETED";
}
