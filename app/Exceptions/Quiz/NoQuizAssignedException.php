<?php

namespace App\Exceptions\Quiz;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class NoQuizAssignedException
 * @package \App\Exceptions\Quiz
 */
class NoQuizAssignedException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::NO_QUIZ_ASSIGNED, $message);
    }
}
