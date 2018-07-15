<?php

namespace App\Enums\Post;

use App\Enums\BaseEnum;

abstract class PostStatus extends BaseEnum
{
    const ACTIVE = "ACTIVE";

    const IN_ACTIVE = "IN-ACTIVE";

    const DELETED = "DELETED";
}
