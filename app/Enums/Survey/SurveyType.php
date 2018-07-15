<?php

namespace App\Enums\Survey;

use App\Enums\BaseEnum;

abstract class SurveyType extends BaseEnum
{
    const SINGLE_ANSWER = "MCQ-SINGLE";

    const MULTIPLE_ANSWER = "MCQ-MULTIPLE";

    const RANGE = "RATE-5";

    const DESCRIPTIVE = "DESCRIPTIVE";
}