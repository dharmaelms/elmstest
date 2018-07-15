<?php

namespace App\Exceptions\Quiz;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class QuizAttemptClosedException
 * @package \App\Exceptions\Quiz
 */
class QuizAttemptClosedException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::QUIZ_ATTEMPT_CLOSED, $message);
    }
}
