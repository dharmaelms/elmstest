<?php

namespace App\Enums\Category;

use App\Enums\BaseEnum;

abstract class CategoryStatus extends BaseEnum
{
    const ACTIVE = "ACTIVE";

    const INACTIVE = "IN-ACTIVE";

    const DELETED = "DELETED";
}
