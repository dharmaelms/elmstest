<?php

namespace App\Enums\RolesAndPermissions;

use App\Enums\BaseEnum;

abstract class SystemRoles extends BaseEnum
{
    const SUPER_ADMIN = "super_admin";

    const SITE_ADMIN = "site_admin";

    const REGISTERED_USER = "registered-user";

    const PROGRAM_ADMIN = "channel-admin";

    const CONTENT_AUTHOR = "content-author";

    const LEARNER = "learner";

    const COURSE_ADMIN = "course-admin";
}
