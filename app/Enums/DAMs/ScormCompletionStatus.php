<?php

namespace App\Enums\DAMs;

use App\Enums\BaseEnum;

abstract class ScormCompletionStatus extends BaseEnum
{
    const PASSED = "passed";

    const COMPLETED = "completed";

    const FAILED = "failed";

    const INCOMPLETE = "incomplete";

    const BROWSED = "browsed";

    const NOT_ATTEMPTED = "not attempted";
}
