<?php

namespace App\Exceptions\Quiz;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class QuestionTagMappingNotFoundException
 * @package \App\Exceptions\Quiz
 */
class QuestionTagMappingNotFoundException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::QUESTION_TAG_MAPPING_NOT_FOUND, $message);
    }
}
