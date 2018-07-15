<?php

namespace App\Enums\Program;

use App\Enums\BaseEnum;

abstract class ElementType extends BaseEnum
{
    const MEDIA = "media";

    const ASSESSMENT = "assessment";

    const EVENT = "event";

    const FLASHCARD = "flashcard";

    const SURVEY = "survey";
    
    const ASSIGNMENT = 'assignment';
}