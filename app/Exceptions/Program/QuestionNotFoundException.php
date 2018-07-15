<?php

namespace App\Exceptions\Program;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

class QuestionNotFoundException extends ApplicationException
{
    /**
     * QuestionNotFoundException constructor.
     * @param string $message
     */
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::PROGRAM_QUESTION_NOT_FOUND, $message);
    }
}
