<?php

namespace App\Enums\Assignment;

use App\Enums\BaseEnum;

/**
 * Define permissions for Assignment module
 * Class Permission
 * @package App\Enums\Assignment
 */
abstract class SubmissionType extends BaseEnum
{
    const YET_TO_REVIEW = "YET_TO_REVIEW";

    const REVIEWED = "REVIEWED";

    const SAVE_AS_DRAFT = "SAVE_AS_DRAFT";

    const LATE_SUBMISSION = "LATE_SUBMISSION";

}
