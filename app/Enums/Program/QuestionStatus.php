<?php

namespace App\Enums\Program;

use App\Enums\BaseEnum;

abstract class QuestionStatus extends BaseEnum
{
    const ANSWERED = "ANSWERED";

    const UNANSWERED = "UNANSWERED";

    const DELETED = "DELETED";
}
